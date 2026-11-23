<?php
/**
 * Lógica de datos de los reportes de planillas pagadas por período.
 *
 * Restaura el comportamiento del reporte anterior: detalle por aduana y
 * forma, resumen por aduana/forma y total general. Reutiliza los helpers
 * de formato y la conexión de src/reportes.php.
 */

/**
 * Devuelve los ramos de ingreso solicitados para el reporte.
 *
 * @return array<int, array<string, string>> Catálogo fijo de ramos.
 */
function obtenerRamosPlanillasPagadas(): array
{
    static $ramos = null;

    if ($ramos === null) {
        $ramos = [
            ['codigo' => '31', 'clave' => 'ramo_31', 'etiqueta' => 'Derechos de almacenaje'],
            ['codigo' => '13', 'clave' => 'ramo_13', 'etiqueta' => 'Habilitaciones'],
            ['codigo' => '10', 'clave' => 'ramo_10', 'etiqueta' => 'IVA'],
            ['codigo' => '01', 'clave' => 'ramo_01', 'etiqueta' => 'Impuesto de importación'],
            ['codigo' => '22', 'clave' => 'ramo_22', 'etiqueta' => 'Intereses de mora'],
            ['codigo' => '23', 'clave' => 'ramo_23', 'etiqueta' => 'Multas'],
            ['codigo' => '12', 'clave' => 'ramo_12', 'etiqueta' => 'Servicios de aduana'],
            ['codigo' => '04', 'clave' => 'ramo_04', 'etiqueta' => 'Impuesto de bultos postales'],
            ['codigo' => '30', 'clave' => 'ramo_30', 'etiqueta' => 'Bandas de garantía'],
            ['codigo' => '28', 'clave' => 'ramo_28', 'etiqueta' => 'Timbres fiscales'],
            ['codigo' => '56', 'clave' => 'ramo_56', 'etiqueta' => 'Impuesto sobre cigarrillos'],
            ['codigo' => '32', 'clave' => 'ramo_32', 'etiqueta' => 'Impuesto PVP alcoholes'],
            ['codigo' => '33', 'clave' => 'ramo_33', 'etiqueta' => 'Impuesto interno sobre alcoholes'],
            ['codigo' => '34', 'clave' => 'ramo_34', 'etiqueta' => 'Expedición al público de alcoholes'],
        ];
    }

    return $ramos;
}

/**
 * Devuelve las columnas SQL que resumen los ramos por formulario.
 *
 * @return string Expresiones SUM(CASE...) para el SELECT.
 */
function obtenerColumnasSqlRamosPlanillasPagadas(): string
{
    $columnas = [];

    foreach (obtenerRamosPlanillasPagadas() as $ramo) {
        $columnas[] = "COALESCE(SUM(CASE WHEN cod_concepto = '{$ramo['codigo']}' THEN monto ELSE 0 END), 0) AS {$ramo['clave']}";
    }

    return implode(",\n                ", $columnas);
}

/**
 * Devuelve los códigos de ramos usados en el filtro SQL.
 *
 * @return string Lista de códigos entre comillas simples.
 */
function obtenerCodigosSqlRamosPlanillasPagadas(): string
{
    $codigos = array_column(obtenerRamosPlanillasPagadas(), 'codigo');

    return "'" . implode("', '", $codigos) . "'";
}

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
    $ramos = [];

    foreach (obtenerRamosPlanillasPagadas() as $ramo) {
        $ramos[$ramo['clave']] = (float) ($fila[$ramo['clave']] ?? 0);
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
        'rif_consignatario' => $rif,
        'nombre_consignatario' => $nombre,
        'fecha_pago' => formatearFechaReporte($fila['fecha_cancelacion'] ?? null),
        'monto' => formatearMontoReporte($fila['monto2'] ?? 0),
        'monto_numero' => (float) ($fila['monto2'] ?? 0),
        'tipo_forma' => (string) ($fila['cod_de_forma'] ?? ''),
        'concepto' => (string) ($fila['tipo_de_planilla'] ?? ''),
        'fecha_vencimiento' => formatearFechaReporte($fila['fecha_vencimiento'] ?? null),
        'estatus' => (string) ($fila['cod_status'] ?? ''),
        'ramos' => $ramos,
    ];
}

/**
 * Obtiene el resumen de planillas agrupado por aduana y forma.
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
    $columnasRamos = obtenerColumnasSqlRamosPlanillasPagadas();
    $codigosRamos = obtenerCodigosSqlRamosPlanillasPagadas();
    $seleccionRamos = [];

    foreach (obtenerRamosPlanillasPagadas() as $ramo) {
        $seleccionRamos[] = "COALESCE(r.{$ramo['clave']}, 0) AS {$ramo['clave']}";
    }

    $seleccionRamosSql = implode(",\n                ", $seleccionRamos);

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
                p.rif_consignatario,
                c.nombre_consignatario,
                p.rif_agente,
                ag.nombre_agente,
                p.nro_resolucion,
                p.fecha_de_notificacion,
                p.fecha_cancelacion,
                p.cod_banco,
                p.cod_agencia,
                p.monto2,
                p.cod_status,
                p.fecha_vencimiento,
                {$seleccionRamosSql}
            FROM planillas_emitidas p
            LEFT JOIN consignatarios c
                ON c.rif_consignatario = p.rif_consignatario
            LEFT JOIN agentes_aduanales ag
                ON ag.rif_agentes = p.rif_agente
            LEFT JOIN aduanas a
                ON a.codigo_aduana = p.codigo_aduana
            LEFT JOIN tformas tf
                ON tf.codigo_de_forma = p.cod_de_forma
            LEFT JOIN (
                SELECT
                    nro_formulario,
                    {$columnasRamos}
                FROM impuestos_a_pagar
                WHERE cod_concepto IN ({$codigosRamos})
                GROUP BY nro_formulario
            ) r ON r.nro_formulario = p.nro_formulario
            WHERE p.fecha_cancelacion BETWEEN ? AND ?
              AND p.fecha_cancelacion > '0000-00-00'
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
