<?php
/**
 * Exportador de los reportes de planillas.
 *
 * Recibe tipo, desde, hasta y formato (pdf|excel). El detalle completo se
 * consulta en este archivo y se envía directamente como descarga.
 */

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/reportes.php';

requiereLogin();

require_once __DIR__ . '/libs/fpdf/fpdf.php';
require_once __DIR__ . '/src/reporte_pdf.php';

$formatoParametro = $_GET['formato'] ?? 'pdf';
$tipoParametro = $_GET['tipo'] ?? '';
$fechaDesdeParametro = $_GET['desde'] ?? '';
$fechaHastaParametro = $_GET['hasta'] ?? '';
$aduanaParametro = $_GET['aduana'] ?? '';
$formaParametro = $_GET['forma'] ?? '';
$formato = is_string($formatoParametro) ? strtolower(trim($formatoParametro)) : '';
$tipo = is_string($tipoParametro) ? trim($tipoParametro) : '';
$fechaDesde = is_string($fechaDesdeParametro) ? trim($fechaDesdeParametro) : '';
$fechaHasta = is_string($fechaHastaParametro) ? trim($fechaHastaParametro) : '';
$aduanaSeleccionada = is_string($aduanaParametro) ? trim($aduanaParametro) : '';
$formaSeleccionada = is_string($formaParametro) ? trim($formaParametro) : '';
$reporte = obtenerTipoReporte($tipo);

if (!in_array($formato, ['pdf', 'excel'], true)) {
    http_response_code(400);
    exit('Formato de descarga no válido.');
}

if ($reporte === null) {
    http_response_code(400);
    exit('Tipo de reporte no válido.');
}

if (!validarFechaReporte($fechaDesde) || !validarFechaReporte($fechaHasta)) {
    http_response_code(400);
    exit('El rango de fechas no es válido.');
}

if ($fechaDesde > $fechaHasta) {
    http_response_code(400);
    exit('La fecha desde no puede ser posterior a la fecha hasta.');
}

if (esReportePlanillasPagadasPeriodo($tipo)) {
    require_once __DIR__ . '/src/reportes_diario.php';
    require_once __DIR__ . '/src/reporte_pdf_diario.php';
    require_once __DIR__ . '/src/exportar_reporte_diario.php';
    require_once __DIR__ . '/src/exportar_reporte_planillas_excel.php';

    $resultado = obtenerDetalleReporteDiario($conn, $fechaDesde, $fechaHasta);
    $nombreArchivo = $reporte['archivo'] . '_' . $fechaDesde . '_a_' . $fechaHasta;

    if ($formato === 'excel') {
        exportarReportePlanillasExcel(
            $resultado,
            $fechaDesde,
            $fechaHasta,
            $nombreArchivo,
            $reporte['titulo']
        );
        exit;
    }

    exportarReporteDiario($conn, 'pdf', $fechaDesde, $fechaHasta, $tipo, $resultado);
    exit;
}

if ($tipo === 'liquidado') {
    require_once __DIR__ . '/src/reportes_diario.php';
    require_once __DIR__ . '/src/reportes_liquidado.php';
    require_once __DIR__ . '/src/reporte_pdf_diario.php';
    require_once __DIR__ . '/src/exportar_reporte_diario.php';
    require_once __DIR__ . '/src/exportar_reporte_planillas_excel.php';

    $aduanasReporte = obtenerAduanasReporte($conn);
    $formasReporte = obtenerFormasReporte($conn);

    if ($aduanaSeleccionada !== '' && !isset($aduanasReporte[$aduanaSeleccionada])) {
        http_response_code(400);
        exit('La aduana seleccionada no es válida.');
    }

    if ($formaSeleccionada !== '' && !isset($formasReporte[$formaSeleccionada])) {
        http_response_code(400);
        exit('El tipo de forma seleccionado no es válido.');
    }

    $resultado = obtenerDetalleReporteLiquidado(
        $conn,
        $fechaDesde,
        $fechaHasta,
        $aduanaSeleccionada,
        $formaSeleccionada
    );

    $nombreArchivo = $reporte['archivo'] . '_' . $fechaDesde . '_a_' . $fechaHasta;

    if ($formato === 'excel') {
        exportarReportePlanillasExcel(
            $resultado,
            $fechaDesde,
            $fechaHasta,
            $nombreArchivo,
            $reporte['titulo']
        );
        exit;
    }

    exportarReporteDiario(
        $conn,
        'pdf',
        $fechaDesde,
        $fechaHasta,
        'liquidado',
        $resultado,
        'Reporte de Planillas Liquidadas',
        $nombreArchivo
    );
    exit;
}

$resultado = obtenerDetalleReporte($conn, $tipo, $fechaDesde, $fechaHasta);

$nombreArchivo = $reporte['archivo'] . '_' . $fechaDesde . '_a_' . $fechaHasta;

if ($formato === 'excel') {
    require_once __DIR__ . '/src/exportar_reporte_planillas_excel.php';

    exportarReportePlanillasExcel(
        $resultado,
        $fechaDesde,
        $fechaHasta,
        $nombreArchivo,
        $reporte['titulo']
    );
    exit;
}

if ($formato === 'pdf') {
    $pdf = new ReporteDetallePDF('L', 'mm', 'Letter');
    $pdf->titulo = $reporte['titulo'];
    $pdf->etiquetaTotal = $reporte['total'];
    $pdf->fechaDesde = $fechaDesde;
    $pdf->fechaHasta = $fechaHasta;

    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 16);
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 7);

    $grupoActual = null;
    $totalGeneral = 0.0;
    $hayFilas = false;

    while ($fila = $resultado->fetch_assoc()) {
        $datos = formatearFilaReporte($fila);
        $hayFilas = true;

        if ($datos['grupo'] !== $grupoActual) {
            $grupoActual = $datos['grupo'];
            $pdf->iniciarGrupo($grupoActual);
        }

        $pdf->fila([
            textoReportePdf($datos['nro_declaracion']),
            textoReportePdf($datos['nro_formulario']),
            textoReportePdf($datos['fecha_liquidacion']),
            textoReportePdf($datos['rif_consignatario']),
            textoReportePdf($datos['nombre_consignatario']),
            textoReportePdf($datos['fecha_pago']),
            textoReportePdf($datos['monto']),
            textoReportePdf($datos['tipo_forma']),
            textoReportePdf($datos['concepto']),
            textoReportePdf($datos['codigo_aduana']),
            textoReportePdf($datos['fecha_vencimiento']),
            textoReportePdf($datos['estatus']),
        ]);

        $totalGeneral += $datos['monto_numero'];
    }

    if ($hayFilas) {
        $pdf->totalGeneral($totalGeneral);
    } else {
        $pdf->sinRegistros();
    }

    $resultado->free();

    $pdf->Output('D', $nombreArchivo . '.pdf');
    exit;
}
