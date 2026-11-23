<?php

require_once __DIR__ . '/reportes_diario.php';

function obtenerAduanasReporte(mysqli $conn): array
{
    $aduanas = [];
    $sql = "SELECT codigo_aduana, nombre_de_la_aduana
            FROM aduanas
            ORDER BY
                CASE codigo_aduana
                    WHEN '5005' THEN 1
                    WHEN '5105' THEN 2
                    WHEN '5505' THEN 3
                    WHEN '5705' THEN 4
                    WHEN '5805' THEN 5
                    WHEN '5905' THEN 6
                    ELSE 99
                END,
                codigo_aduana";

    $resultado = $conn->query($sql);

    if ($resultado === false) {
        return $aduanas;
    }

    while ($fila = $resultado->fetch_assoc()) {
        $codigo = trim((string) ($fila['codigo_aduana'] ?? ''));

        if ($codigo === '') {
            continue;
        }

        $aduanas[$codigo] = [
            'codigo' => $codigo,
            'nombre' => trim((string) ($fila['nombre_de_la_aduana'] ?? $codigo)),
        ];
    }

    $resultado->free();

    return $aduanas;
}

function obtenerFormasReporte(mysqli $conn): array
{
    $formas = [];
    $sql = "SELECT codigo_de_forma, formas, denominacion
            FROM tformas
            WHERE formas IN ('80', '81', '86')
            ORDER BY CAST(formas AS UNSIGNED)";

    $resultado = $conn->query($sql);

    if ($resultado === false) {
        return $formas;
    }

    while ($fila = $resultado->fetch_assoc()) {
        $codigo = trim((string) ($fila['formas'] ?? ''));

        if ($codigo === '') {
            continue;
        }

        $formas[$codigo] = [
            'codigo' => $codigo,
            'codigo_interno' => (string) ($fila['codigo_de_forma'] ?? ''),
            'denominacion' => trim((string) ($fila['denominacion'] ?? '')),
        ];
    }

    $resultado->free();

    return $formas;
}

function obtenerCondicionesFiltrosLiquidado(string $aduana, string $forma): array
{
    $condiciones = [];
    $parametros = [];

    if ($aduana !== '') {
        $condiciones[] = 'p.codigo_aduana = ?';
        $parametros[] = $aduana;
    }

    if ($forma !== '') {
        $condiciones[] = 'tf.formas = ?';
        $parametros[] = $forma;
    }

    $sql = $condiciones === [] ? '' : ' AND ' . implode(' AND ', $condiciones);

    return [$sql, $parametros];
}

function obtenerResumenReporteLiquidado(
    mysqli $conn,
    string $fechaDesde,
    string $fechaHasta,
    string $aduana = '',
    string $forma = ''
): array {
    [$filtrosSql, $parametrosFiltros] = obtenerCondicionesFiltrosLiquidado($aduana, $forma);

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
            WHERE p.fecha_contabilidad_liq BETWEEN ? AND ?{$filtrosSql}
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
    $tipos = 'ss' . str_repeat('s', count($parametrosFiltros));
    $parametros = array_merge([$fechaDesde, $fechaHasta], $parametrosFiltros);
    $stmt->bind_param($tipos, ...$parametros);
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

function obtenerDetalleReporteLiquidado(
    mysqli $conn,
    string $fechaDesde,
    string $fechaHasta,
    string $aduana = '',
    string $forma = ''
): mysqli_result {
    [$filtrosSql, $parametrosFiltros] = obtenerCondicionesFiltrosLiquidado($aduana, $forma);
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
            WHERE p.fecha_contabilidad_liq BETWEEN ? AND ?{$filtrosSql}
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
    $tipos = 'ss' . str_repeat('s', count($parametrosFiltros));
    $parametros = array_merge([$fechaDesde, $fechaHasta], $parametrosFiltros);
    $stmt->bind_param($tipos, ...$parametros);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $stmt->close();

    return $resultado;
}
