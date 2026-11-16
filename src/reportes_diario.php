<?php
/**
 * Lógica de datos del reporte diario de planillas pagadas.
 *
 * Restaura el comportamiento del reporte anterior: detalle por aduana y
 * forma, resumen por aduana/forma y total general. Reutiliza los helpers
 * de formato y la conexión de src/reportes.php.
 */

/**
 * Convierte una fila de la consulta en valores listos para mostrar o exportar.
 *
 * @param array $fila Fila obtenida de obtenerDetalleReporteDiario().
 * @return array Valores formateados para el reporte.
 */
function formatearFilaReporteDiario(array $fila): array
{
    $rif = trim((string) ($fila['rif_consignatario'] ?? ''));
    $nombre = trim((string) ($fila['nombre_consignatario'] ?? ''));

    if ($rif !== '' && $nombre !== '') {
        $contribuyente = $rif . ' - ' . $nombre;
    } elseif ($nombre !== '') {
        $contribuyente = $nombre;
    } else {
        $contribuyente = $rif;
    }

    return [
        'codigo_aduana' => (string) ($fila['codigo_aduana'] ?? ''),
        'nombre_aduana' => (string) ($fila['nombre_de_la_aduana'] ?? $fila['codigo_aduana'] ?? ''),
        'cod_de_forma' => (string) ($fila['cod_de_forma'] ?? ''),
        'forma_codigo' => trim((string) ($fila['forma_codigo'] ?? '')),
        'denominacion_forma' => trim((string) ($fila['denominacion_forma'] ?? '')),
        'tipo_de_planilla' => (string) ($fila['tipo_de_planilla'] ?? ''),
        'nro_declaracion' => (string) ($fila['nro_declaracion'] ?? ''),
        'nro_formulario' => (string) ($fila['nro_formulario'] ?? ''),
        'fecha_liquidacion' => formatearFechaReporte($fila['fecha_contabilidad_liq'] ?? null),
        'nro_liquidacion' => (string) ($fila['nro_liquidacion'] ?? ''),
        'contribuyente' => $contribuyente,
        'monto' => formatearMontoReporte($fila['monto2'] ?? 0),
        'monto_numero' => (float) ($fila['monto2'] ?? 0),
        'fecha_vencimiento' => formatearFechaReporte($fila['fecha_vencimiento'] ?? null),
        'fecha_cancelacion' => formatearFechaReporte($fila['fecha_cancelacion'] ?? null),
        'dias_mora' => $fila['dias_mora'] === null ? 'N/A' : (string) (int) $fila['dias_mora'],
    ];
}

/**
 * Obtiene el resumen de planillas agrupado por aduana y denominación.
 *
 * @param mysqli $conn Conexión activa.
 * @param string $fechaDesde Fecha inicial.
 * @param string $fechaHasta Fecha final.
 * @return array Resumen con total, monto y detalle por aduana y forma.
 */
function obtenerResumenReporteDiario(
    mysqli $conn,
    string $fechaDesde,
    string $fechaHasta
): array {
    $sql = "SELECT
                p.codigo_aduana,
                a.nombre_de_la_aduana,
                p.cod_de_forma,
                COALESCE(NULLIF(TRIM(tf.formas), ''), CAST(p.cod_de_forma AS CHAR)) AS forma_codigo,
                COALESCE(NULLIF(TRIM(tf.denominacion), ''), 'Sin denominación registrada') AS denominacion_forma,
                COUNT(*) AS total,
                COALESCE(SUM(p.monto2), 0) AS monto
            FROM planillas_emitidas p
            LEFT JOIN aduanas a ON a.codigo_aduana = p.codigo_aduana
            LEFT JOIN tformas tf ON tf.codigo_de_forma = p.cod_de_forma
            WHERE p.fecha_cancelacion BETWEEN ? AND ?
              AND p.fecha_cancelacion > '0000-00-00'
              AND COALESCE(p.anulada, 0) <> 1
            GROUP BY
                p.codigo_aduana,
                a.nombre_de_la_aduana,
                p.cod_de_forma,
                tf.formas,
                tf.denominacion
              ORDER BY
                  CASE p.codigo_aduana
                      WHEN '5005' THEN 1
                      WHEN '5105' THEN 2
                      WHEN '5505' THEN 3
                      WHEN '5705' THEN 4
                      WHEN '5805' THEN 5
                      WHEN '5905' THEN 6
                      ELSE 99
                  END,
                  p.codigo_aduana,
                  CAST(COALESCE(NULLIF(TRIM(tf.formas), ''), p.cod_de_forma) AS UNSIGNED),
                  denominacion_forma";

    $stmt = $conn->prepare($sql);
    enlazarParametrosReporte($stmt, $fechaDesde, $fechaHasta);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $resumen = [
        'total' => 0,
        'monto' => 0.0,
        'por_aduana' => [],
    ];
    $indicesAduana = [];

    while ($fila = $resultado->fetch_assoc()) {
        $fila['total'] = (int) $fila['total'];
        $fila['monto'] = (float) $fila['monto'];
        $fila['nombre_de_la_aduana'] = $fila['nombre_de_la_aduana']
            ?? $fila['codigo_aduana'];
        $fila['forma_codigo'] = trim((string) $fila['forma_codigo']);
        $fila['denominacion_forma'] = trim((string) $fila['denominacion_forma']);

        $codigoAduana = (string) $fila['codigo_aduana'];

        if (!isset($indicesAduana[$codigoAduana])) {
            $indicesAduana[$codigoAduana] = count($resumen['por_aduana']);
            $resumen['por_aduana'][] = [
                'codigo_aduana' => $codigoAduana,
                'nombre_de_la_aduana' => $fila['nombre_de_la_aduana'],
                'total' => 0,
                'monto' => 0.0,
                'por_forma' => [],
            ];
        }

        $indiceAduana = $indicesAduana[$codigoAduana];
        $resumen['por_aduana'][$indiceAduana]['total'] += $fila['total'];
        $resumen['por_aduana'][$indiceAduana]['monto'] += $fila['monto'];
        $resumen['por_aduana'][$indiceAduana]['por_forma'][] = $fila;

        $resumen['total'] += $fila['total'];
        $resumen['monto'] += $fila['monto'];
    }

    $resultado->free();
    $stmt->close();

    return $resumen;
}

/**
 * Obtiene el detalle completo de planillas para exportar a PDF o Excel.
 *
 * @param mysqli $conn Conexión activa.
 * @param string $fechaDesde Fecha inicial.
 * @param string $fechaHasta Fecha final.
 * @return mysqli_result Resultado ordenado por aduana y tipo de planilla.
 */
function obtenerDetalleReporteDiario(
    mysqli $conn,
    string $fechaDesde,
    string $fechaHasta
): mysqli_result {
    $sql = "SELECT
                p.codigo_aduana,
                a.nombre_de_la_aduana,
                p.cod_de_forma,
                COALESCE(NULLIF(TRIM(tf.formas), ''), CAST(p.cod_de_forma AS CHAR)) AS forma_codigo,
                COALESCE(NULLIF(TRIM(tf.denominacion), ''), 'Sin denominación registrada') AS denominacion_forma,
                p.tipo_de_planilla,
                p.nro_declaracion,
                p.nro_formulario,
                p.fecha_contabilidad_liq,
                p.nro_liquidacion,
                p.rif_consignatario,
                c.nombre_consignatario,
                p.monto2,
                p.fecha_vencimiento,
                p.fecha_cancelacion,
                CASE
                    WHEN p.fecha_vencimiento > '0000-00-00'
                        THEN GREATEST(DATEDIFF(p.fecha_cancelacion, p.fecha_vencimiento), 0)
                    ELSE NULL
                END AS dias_mora
            FROM planillas_emitidas p
            LEFT JOIN consignatarios c
                ON c.rif_consignatario = p.rif_consignatario
            LEFT JOIN aduanas a
                ON a.codigo_aduana = p.codigo_aduana
            LEFT JOIN tformas tf
                ON tf.codigo_de_forma = p.cod_de_forma
            WHERE p.fecha_cancelacion BETWEEN ? AND ?
              AND p.fecha_cancelacion > '0000-00-00'
              AND COALESCE(p.anulada, 0) <> 1
            ORDER BY
                  CASE p.codigo_aduana
                      WHEN '5005' THEN 1
                      WHEN '5105' THEN 2
                      WHEN '5505' THEN 3
                      WHEN '5705' THEN 4
                      WHEN '5805' THEN 5
                      WHEN '5905' THEN 6
                      ELSE 99
                  END,
                  p.codigo_aduana,
                  CAST(COALESCE(NULLIF(TRIM(tf.formas), ''), p.cod_de_forma) AS UNSIGNED),
                  denominacion_forma,
                  p.tipo_de_planilla,
                  p.nro_formulario";

    $stmt = $conn->prepare($sql);
    enlazarParametrosReporte($stmt, $fechaDesde, $fechaHasta);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $stmt->close();

    return $resultado;
}
