<?php
/**
 * Consultas y utilidades para el módulo de reportes.
 *
 * Los reportes trabajan sobre planillas_emitidas, usan monto2 como monto
 * principal y excluyen las planillas anuladas.
 */

/**
 * Devuelve la definición de los reportes disponibles.
 *
 * @return array<string, array<string, string>>
 */
function obtenerTiposReporte(): array
{
    return [
        'liquidado' => [
            'titulo' => 'Planillas Liquidadas',
            'descripcion' => 'Planillas registradas por fecha de liquidación.',
            'icono' => 'documento',
            'condicion' => 'p.fecha_contabilidad_liq BETWEEN ? AND ?',
            'orden' => 'p.nro_formulario',
            'etiqueta_desde' => 'Fecha de liquidación desde',
            'etiqueta_hasta' => 'Fecha de liquidación hasta',
            'total' => 'TOTAL GENERAL',
            'archivo' => 'reporte_planillas_liquidadas',
        ],
        'pagado' => [
            'titulo' => 'Planillas Pagadas',
            'descripcion' => 'Planillas canceladas dentro del rango de fechas seleccionado.',
            'icono' => 'check',
            'condicion' => "p.fecha_cancelacion BETWEEN ? AND ? AND p.fecha_cancelacion > '0000-00-00'",
            'orden' => 'p.nro_formulario',
            'etiqueta_desde' => 'Fecha de pago desde',
            'etiqueta_hasta' => 'Fecha de pago hasta',
            'total' => 'TOTAL GENERAL RECAUDADO',
            'archivo' => 'reporte_planillas_pagadas',
        ],
        'diario' => [
            'titulo' => 'Planillas pagadas (diario)',
            'descripcion' => 'Reporte diario de planillas canceladas con detalle por aduana y forma.',
            'icono' => 'check',
            'condicion' => "p.fecha_cancelacion BETWEEN ? AND ? AND p.fecha_cancelacion > '0000-00-00'",
            'orden' => 'p.nro_formulario',
            'etiqueta_desde' => 'Fecha desde',
            'etiqueta_hasta' => 'Fecha hasta',
            'total' => 'TOTAL GENERAL',
            'archivo' => 'reporte_planillas_cancelacion',
        ],
        'derechos_pendientes' => [
            'titulo' => 'Derechos Pendientes',
            'descripcion' => 'Derechos vencidos registrados en el período de contabilidad seleccionado.',
            'icono' => 'alerta',
            'condicion' => 'p.fecha_vencimiento < CURDATE() AND p.fecha_contabilidad_liq BETWEEN ? AND ?',
            'orden' => 'p.fecha_vencimiento, p.nro_formulario',
            'etiqueta_desde' => 'Período de contabilidad desde',
            'etiqueta_hasta' => 'Período de contabilidad hasta',
            'total' => 'TOTAL GENERAL',
            'archivo' => 'reporte_derechos_pendientes',
        ],
        'derechos_pendientes_pagados' => [
            'titulo' => 'Derechos Pendientes Pagados',
            'descripcion' => 'Derechos cancelados después de su fecha de vencimiento.',
            'icono' => 'check',
            'condicion' => "p.fecha_cancelacion > p.fecha_vencimiento AND p.fecha_cancelacion IS NOT NULL AND p.fecha_cancelacion <> '0000-00-00' AND p.fecha_emision BETWEEN ? AND ?",
            'orden' => 'p.nro_formulario',
            'etiqueta_desde' => 'Período de emisión desde',
            'etiqueta_hasta' => 'Período de emisión hasta',
            'total' => 'TOTAL GENERAL RECAUDADO',
            'archivo' => 'reporte_derechos_pendientes_pagados',
        ],
    ];
}

/**
 * Obtiene la definición de un reporte por su identificador.
 *
 * @param string $tipo Identificador del reporte.
 * @return array<string, string>|null
 */
function obtenerTipoReporte(string $tipo): ?array
{
    $tipos = obtenerTiposReporte();

    return $tipos[$tipo] ?? null;
}

/**
 * Comprueba que una fecha tenga el formato Y-m-d y sea real.
 *
 * @param string $fecha Fecha recibida por GET.
 * @return bool True si la fecha es válida.
 */
function validarFechaReporte(string $fecha): bool
{
    $fechaObjeto = DateTime::createFromFormat('Y-m-d', $fecha);
    $errores = DateTime::getLastErrors();

    if ($fechaObjeto === false) {
        return false;
    }

    if ($errores !== false && ($errores['warning_count'] > 0 || $errores['error_count'] > 0)) {
        return false;
    }

    return $fechaObjeto->format('Y-m-d') === $fecha;
}

/**
 * Formatea una fecha para mostrarla en pantalla o en un archivo.
 *
 * @param string|null $fecha Fecha en formato Y-m-d.
 * @return string Fecha en d/m/Y o N/A si no es válida.
 */
function formatearFechaReporte(?string $fecha): string
{
    if ($fecha === null || $fecha === '' || $fecha === '0000-00-00') {
        return 'N/A';
    }

    $marcaTiempo = strtotime($fecha);

    return $marcaTiempo === false ? 'N/A' : date('d/m/Y', $marcaTiempo);
}

/**
 * Formatea un monto con el formato monetario del sistema.
 *
 * @param mixed $monto Valor numérico.
 * @return string Monto con separador de miles y coma decimal.
 */
function formatearMontoReporte(mixed $monto): string
{
    return number_format((float) $monto, 2, ',', '.');
}

/**
 * Devuelve el grupo de contribuyente usado por los reportes.
 *
 * @param string $rif RIF del consignatario.
 * @return string Grupo visible del contribuyente.
 */
function obtenerGrupoContribuyente(string $rif): string
{
    $primeraLetra = strtoupper(substr(trim($rif), 0, 1));

    if ($primeraLetra === 'G') {
        return 'Sector Público';
    }

    if ($primeraLetra === 'J') {
        return 'Sector Privado';
    }

    if ($primeraLetra === 'V' || $primeraLetra === 'E') {
        return 'Natural';
    }

    return 'Otro';
}

/**
 * Devuelve la expresión SQL que clasifica el RIF del contribuyente.
 *
 * @return string Expresión CASE para ordenar y agrupar.
 */
function obtenerCasoGrupoContribuyente(): string
{
    return "CASE
                WHEN UPPER(c.rif_consignatario) LIKE 'G%' THEN 1
                WHEN UPPER(c.rif_consignatario) LIKE 'J%' THEN 2
                WHEN UPPER(c.rif_consignatario) LIKE 'V%' OR UPPER(c.rif_consignatario) LIKE 'E%' THEN 3
                ELSE 4
            END";
}

/**
 * Convierte una fila del reporte en valores listos para mostrar o exportar.
 *
 * @param array $fila Fila obtenida de obtenerDetalleReporte().
 * @return array Valores formateados para el reporte.
 */
function formatearFilaReporte(array $fila): array
{
    $rif = trim((string) ($fila['rif_consignatario'] ?? ''));
    $nombre = trim((string) ($fila['nombre_consignatario'] ?? ''));

    return [
        'grupo' => obtenerGrupoContribuyente($rif),
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
        'codigo_aduana' => (string) ($fila['codigo_aduana'] ?? ''),
        'fecha_vencimiento' => formatearFechaReporte($fila['fecha_vencimiento'] ?? null),
        'estatus' => (string) ($fila['cod_status'] ?? ''),
    ];
}

/**
 * Enlaza las fechas del reporte a una sentencia preparada.
 *
 * @param mysqli_stmt $stmt Sentencia preparada.
 * @param string $fechaDesde Fecha inicial.
 * @param string $fechaHasta Fecha final.
 * @return void
 */
function enlazarParametrosReporte(
    mysqli_stmt $stmt,
    string $fechaDesde,
    string $fechaHasta
): void {
    $stmt->bind_param('ss', $fechaDesde, $fechaHasta);
}

/**
 * Obtiene el resumen del reporte agrupado por tipo de contribuyente.
 *
 * @param mysqli $conn Conexión activa.
 * @param string $tipo Tipo de reporte.
 * @param string $fechaDesde Fecha inicial.
 * @param string $fechaHasta Fecha final.
 * @return array Resumen con total, monto y detalle por grupo.
 */
function obtenerResumenReporte(
    mysqli $conn,
    string $tipo,
    string $fechaDesde,
    string $fechaHasta
): array {
    $reporte = obtenerTipoReporte($tipo);
    $resumen = [
        'total' => 0,
        'monto' => 0.0,
        'por_grupo' => [],
    ];

    if ($reporte === null) {
        return $resumen;
    }

    $casoGrupo = obtenerCasoGrupoContribuyente();
    $sql = "SELECT
                {$casoGrupo} AS grupo_numero,
                COUNT(*) AS total,
                COALESCE(SUM(p.monto2), 0) AS monto
            FROM planillas_emitidas p
            LEFT JOIN consignatarios c
                ON c.rif_consignatario = p.rif_consignatario
            WHERE {$reporte['condicion']}
              AND COALESCE(p.anulada, 0) <> 1
            GROUP BY grupo_numero
            ORDER BY grupo_numero";

    $stmt = $conn->prepare($sql);
    enlazarParametrosReporte($stmt, $fechaDesde, $fechaHasta);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $nombresGrupo = [
        1 => 'Sector Público',
        2 => 'Sector Privado',
        3 => 'Natural',
        4 => 'Otro',
    ];

    while ($fila = $resultado->fetch_assoc()) {
        $grupoNumero = (int) $fila['grupo_numero'];
        $total = (int) $fila['total'];
        $monto = (float) $fila['monto'];

        $resumen['por_grupo'][] = [
            'grupo' => $nombresGrupo[$grupoNumero] ?? 'Otro',
            'total' => $total,
            'monto' => $monto,
        ];
        $resumen['total'] += $total;
        $resumen['monto'] += $monto;
    }

    $resultado->free();
    $stmt->close();

    return $resumen;
}

/**
 * Obtiene el detalle completo de planillas para exportar a PDF o Excel.
 *
 * @param mysqli $conn Conexión activa.
 * @param string $tipo Tipo de reporte.
 * @param string $fechaDesde Fecha inicial.
 * @param string $fechaHasta Fecha final.
 * @return mysqli_result Resultado ordenado por grupo de contribuyente.
 */
function obtenerDetalleReporte(
    mysqli $conn,
    string $tipo,
    string $fechaDesde,
    string $fechaHasta
): mysqli_result {
    $reporte = obtenerTipoReporte($tipo);

    if ($reporte === null) {
        throw new InvalidArgumentException('Tipo de reporte no válido.');
    }

    $casoGrupo = obtenerCasoGrupoContribuyente();
    $sql = "SELECT
                p.nro_declaracion,
                p.nro_formulario,
                p.fecha_contabilidad_liq,
                c.rif_consignatario,
                c.nombre_consignatario,
                p.fecha_cancelacion,
                p.monto2,
                p.cod_de_forma,
                p.tipo_de_planilla,
                p.codigo_aduana,
                p.fecha_vencimiento,
                p.cod_status
            FROM planillas_emitidas p
            LEFT JOIN consignatarios c
                ON c.rif_consignatario = p.rif_consignatario
            WHERE {$reporte['condicion']}
              AND COALESCE(p.anulada, 0) <> 1
            ORDER BY
                {$casoGrupo},
                {$reporte['orden']}";

    $stmt = $conn->prepare($sql);
    enlazarParametrosReporte($stmt, $fechaDesde, $fechaHasta);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $stmt->close();

    return $resultado;
}
