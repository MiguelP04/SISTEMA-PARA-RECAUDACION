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
$formato = is_string($formatoParametro) ? strtolower(trim($formatoParametro)) : '';
$tipo = is_string($tipoParametro) ? trim($tipoParametro) : '';
$fechaDesde = is_string($fechaDesdeParametro) ? trim($fechaDesdeParametro) : '';
$fechaHasta = is_string($fechaHastaParametro) ? trim($fechaHastaParametro) : '';
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

if ($tipo === 'diario') {
    require_once __DIR__ . '/src/reportes_diario.php';
    require_once __DIR__ . '/src/reporte_pdf_diario.php';
    require_once __DIR__ . '/src/exportar_reporte_diario.php';

    exportarReporteDiario($conn, $formato, $fechaDesde, $fechaHasta);
    exit;
}

$resultado = obtenerDetalleReporte($conn, $tipo, $fechaDesde, $fechaHasta);

$nombreArchivo = $reporte['archivo'] . '_' . $fechaDesde . '_a_' . $fechaHasta;

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

// Exportación compatible con Excel (.xls).
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.xls"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

echo "\xEF\xBB\xBF";
echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
echo '<head><meta charset="utf-8"><style>';
echo 'table{border-collapse:collapse;font-family:Arial,sans-serif;font-size:10pt;}';
echo 'th{background:#1a1f53;color:#ffffff;border:1px solid #8f8f8f;padding:5px;}';
echo 'td{border:1px solid #8f8f8f;padding:4px;}';
echo '.grupo{background:#eef1f7;font-weight:bold;}';
echo '.total{background:#ffc60a;color:#1a1f53;font-weight:bold;}';
echo '.texto{mso-number-format:"\@";}';
echo '</style></head><body>';
echo '<h2>' . htmlspecialchars($reporte['titulo'], ENT_QUOTES, 'UTF-8') . '</h2>';
echo '<p><strong>Desde:</strong> ' . htmlspecialchars(formatearFechaReporte($fechaDesde), ENT_QUOTES, 'UTF-8');
echo ' &nbsp;&nbsp; <strong>Hasta:</strong> ' . htmlspecialchars(formatearFechaReporte($fechaHasta), ENT_QUOTES, 'UTF-8') . '</p>';

echo '<table>';
echo '<thead><tr>';
echo '<th>Nro. Confrontación</th>';
echo '<th>Nro. Formulario</th>';
echo '<th>Fecha Liq.</th>';
echo '<th>RIF Consignatario</th>';
echo '<th>Nombre Consignatario</th>';
echo '<th>Fecha Pago</th>';
echo '<th>Monto</th>';
echo '<th>Tipo Forma</th>';
echo '<th>Concepto</th>';
echo '<th>Cód. Aduana</th>';
echo '<th>Fecha Venc.</th>';
echo '<th>Estatus</th>';
echo '</tr></thead><tbody>';

$grupoActual = null;
$totalGeneral = 0.0;
$hayFilas = false;

while ($fila = $resultado->fetch_assoc()) {
    $datos = formatearFilaReporte($fila);
    $hayFilas = true;

    if ($datos['grupo'] !== $grupoActual) {
        $grupoActual = $datos['grupo'];
        echo '<tr class="grupo"><td colspan="12">Contribuyente: '
            . htmlspecialchars($grupoActual, ENT_QUOTES, 'UTF-8')
            . '</td></tr>';
    }

    echo '<tr>';
    echo '<td class="texto">' . htmlspecialchars($datos['nro_declaracion'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['nro_formulario'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['fecha_liquidacion'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['rif_consignatario'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars($datos['nombre_consignatario'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['fecha_pago'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['monto'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['tipo_forma'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['concepto'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['codigo_aduana'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['fecha_vencimiento'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['estatus'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '</tr>';

    $totalGeneral += $datos['monto_numero'];
}

if (!$hayFilas) {
    echo '<tr><td colspan="12">No se encontraron registros para el rango de fechas seleccionado.</td></tr>';
}

echo '</tbody></table>';

if ($hayFilas) {
    echo '<h3>' . htmlspecialchars($reporte['total'], ENT_QUOTES, 'UTF-8') . ': Bs. '
        . htmlspecialchars(formatearMontoReporte($totalGeneral), ENT_QUOTES, 'UTF-8')
        . '</h3>';
}

echo '</body></html>';

$resultado->free();
exit;
