<?php
/**
 * Exportador del reporte de planillas por fecha de cancelación.
 *
 * Recibe desde, hasta y formato (pdf|excel). El detalle completo se
 * consulta en este archivo y se envía directamente como descarga.
 */

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/reportes.php';

requiereLogin();

require_once __DIR__ . '/libs/fpdf/fpdf.php';
require_once __DIR__ . '/src/reporte_pdf.php';

$formato = strtolower(trim($_GET['formato'] ?? 'pdf'));
$fechaDesde = trim($_GET['desde'] ?? '');
$fechaHasta = trim($_GET['hasta'] ?? '');

if (!in_array($formato, ['pdf', 'excel'], true)) {
    http_response_code(400);
    exit('Formato de descarga no válido.');
}

if (!validarFechaReporte($fechaDesde) || !validarFechaReporte($fechaHasta)) {
    http_response_code(400);
    exit('El rango de fechas no es válido.');
}

if ($fechaDesde > $fechaHasta) {
    http_response_code(400);
    exit('La fecha desde no puede ser posterior a la fecha hasta.');
}

$resultado = obtenerDetalleReporte($conn, $fechaDesde, $fechaHasta);

$nombreArchivo = 'reporte_planillas_cancelacion_'
    . $fechaDesde . '_a_' . $fechaHasta;

if ($formato === 'pdf') {
    $pdf = new ReportePlanillasPDF('L', 'mm', 'Letter');
    $pdf->fechaDesde = $fechaDesde;
    $pdf->fechaHasta = $fechaHasta;

    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 16);
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 7);

    $aduanaActual = null;
    $formaActual = null;
    $formaCodigoActual = '';
    $totalAduana = 0.0;
    $cantidadForma = 0;
    $subtotalForma = 0.0;
    $totalGeneral = 0.0;
    $nuevaAduana = false;

    while ($fila = $resultado->fetch_assoc()) {
        $datos = formatearFilaReporte($fila);

        if ($datos['codigo_aduana'] !== $aduanaActual) {
            if ($aduanaActual !== null) {
                $pdf->tablaActiva = false;
                $pdf->cierreAduana(
                    'Total forma ' . $formaCodigoActual,
                    'Subtotal forma ' . $formaCodigoActual,
                    $cantidadForma,
                    $subtotalForma,
                    'Total aduana ' . $aduanaActual,
                    $totalAduana
                );
            }

            $aduanaActual = $datos['codigo_aduana'];
            $formaActual = null;
            $cantidadForma = 0;
            $subtotalForma = 0.0;
            $totalAduana = 0.0;
            $nuevaAduana = true;
        }

        if ($datos['denominacion_forma'] !== $formaActual) {
            if ($formaActual !== null) {
                $pdf->tablaActiva = false;
                $pdf->cierreForma(
                    'Total forma ' . $formaCodigoActual,
                    'Subtotal forma ' . $formaCodigoActual,
                    $cantidadForma,
                    $subtotalForma
                );
            }

            $formaActual = $datos['denominacion_forma'];
            $formaCodigoActual = $datos['forma_codigo'];
            $cantidadForma = 0;
            $subtotalForma = 0.0;

            $pdf->iniciarTablaForma(
                $nuevaAduana,
                $aduanaActual,
                $datos['nombre_aduana'],
                $formaActual
            );
            $nuevaAduana = false;
        }

        $pdf->fila([
            textoReportePdf($datos['nro_declaracion']),
            textoReportePdf($datos['nro_formulario']),
            textoReportePdf($datos['fecha_liquidacion']),
            textoReportePdf($datos['nro_liquidacion']),
            textoReportePdf($datos['contribuyente']),
            textoReportePdf($datos['monto']),
            textoReportePdf($datos['tipo_de_planilla']),
            textoReportePdf($datos['fecha_vencimiento']),
            textoReportePdf($datos['fecha_cancelacion']),
            textoReportePdf($datos['dias_mora']),
        ]);

        $cantidadForma++;
        $subtotalForma += $datos['monto_numero'];
        $totalAduana += $datos['monto_numero'];
        $totalGeneral += $datos['monto_numero'];
    }

    if ($aduanaActual !== null) {
        $pdf->tablaActiva = false;
        $pdf->cierreAduana(
            'Total forma ' . $formaCodigoActual,
            'Subtotal forma ' . $formaCodigoActual,
            $cantidadForma,
            $subtotalForma,
            'Total aduana ' . $aduanaActual,
            $totalAduana
        );
        $pdf->totalGeneral($totalGeneral);
    } else {
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(
            0,
            10,
            textoReportePdf('No se encontraron registros para el rango de fechas seleccionado.'),
            1,
            1,
            'C'
        );
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
echo '.grupo--forma{background:#ffffff;color:#1a1f53;border-left:4px solid #1a1f53;font-size:11pt;}';
echo '.subtotal{background:#f5f7fb;font-weight:bold;}';
echo '.total{background:#ffc60a;color:#1a1f53;font-weight:bold;}';
echo '.texto{mso-number-format:"\@";}';
echo '</style></head><body>';
echo '<h2>Reporte de Planillas por Fecha de Cancelación</h2>';
echo '<p><strong>Desde:</strong> ' . htmlspecialchars(formatearFechaReporte($fechaDesde), ENT_QUOTES, 'UTF-8');
echo ' &nbsp;&nbsp; <strong>Hasta:</strong> ' . htmlspecialchars(formatearFechaReporte($fechaHasta), ENT_QUOTES, 'UTF-8') . '</p>';

echo '<table>';
echo '<thead><tr>';
echo '<th>Nro. Confrontación</th>';
echo '<th>Formulario</th>';
echo '<th>Fecha Liquidación</th>';
echo '<th>Nro Liquidación</th>';
echo '<th>Contribuyente</th>';
echo '<th>Monto Total</th>';
echo '<th>Tipo Planilla</th>';
echo '<th>Fecha Vencimiento</th>';
echo '<th>Fecha Cancelación</th>';
echo '<th>Días Mora</th>';
echo '</tr></thead><tbody>';

$aduanaActual = null;
$formaActual = null;
$formaCodigoActual = '';
$totalAduana = 0.0;
$totalForma = 0.0;
$totalGeneral = 0.0;
$hayFilas = false;

while ($fila = $resultado->fetch_assoc()) {
    $datos = formatearFilaReporte($fila);
    $hayFilas = true;

    if ($datos['codigo_aduana'] !== $aduanaActual) {
        if ($aduanaActual !== null) {
            echo '<tr class="subtotal"><td colspan="9">Subtotal forma ' . htmlspecialchars($formaCodigoActual, ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>Bs ' . htmlspecialchars(formatearMontoReporte($totalForma), ENT_QUOTES, 'UTF-8') . '</td></tr>';
            echo '<tr class="subtotal"><td colspan="9">Total aduana ' . htmlspecialchars($aduanaActual, ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>Bs ' . htmlspecialchars(formatearMontoReporte($totalAduana), ENT_QUOTES, 'UTF-8') . '</td></tr>';
        }

        $aduanaActual = $datos['codigo_aduana'];
        $formaActual = null;
        $totalAduana = 0.0;

        echo '<tr class="grupo"><td colspan="10">Aduana: '
            . htmlspecialchars($aduanaActual . ' - ' . $datos['nombre_aduana'], ENT_QUOTES, 'UTF-8')
            . '</td></tr>';
    }

    if ($datos['denominacion_forma'] !== $formaActual) {
        if ($formaActual !== null) {
            echo '<tr class="subtotal"><td colspan="9">Subtotal forma ' . htmlspecialchars($formaCodigoActual, ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>Bs ' . htmlspecialchars(formatearMontoReporte($totalForma), ENT_QUOTES, 'UTF-8') . '</td></tr>';
        }

        $formaActual = $datos['denominacion_forma'];
        $formaCodigoActual = $datos['forma_codigo'];
        $totalForma = 0.0;

        echo '<tr class="grupo grupo--forma"><td colspan="10">'
            . htmlspecialchars($formaActual, ENT_QUOTES, 'UTF-8')
            . '</td></tr>';
    }

    echo '<tr>';
    echo '<td class="texto">' . htmlspecialchars($datos['nro_declaracion'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['nro_formulario'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['fecha_liquidacion'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['nro_liquidacion'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars($datos['contribuyente'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['monto'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['tipo_de_planilla'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['fecha_vencimiento'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['fecha_cancelacion'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="texto">' . htmlspecialchars($datos['dias_mora'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '</tr>';

    $totalForma += $datos['monto_numero'];
    $totalAduana += $datos['monto_numero'];
    $totalGeneral += $datos['monto_numero'];
}

if ($hayFilas) {
    echo '<tr class="subtotal"><td colspan="9">Subtotal forma ' . htmlspecialchars($formaCodigoActual, ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>Bs ' . htmlspecialchars(formatearMontoReporte($totalForma), ENT_QUOTES, 'UTF-8') . '</td></tr>';
    echo '<tr class="subtotal"><td colspan="9">Total aduana ' . htmlspecialchars((string) $aduanaActual, ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>Bs ' . htmlspecialchars(formatearMontoReporte($totalAduana), ENT_QUOTES, 'UTF-8') . '</td></tr>';

    echo '<tr class="total"><td colspan="9">TOTAL GENERAL</td>';
    echo '<td>Bs ' . htmlspecialchars(formatearMontoReporte($totalGeneral), ENT_QUOTES, 'UTF-8') . '</td></tr>';
} else {
    echo '<tr><td colspan="10">No se encontraron registros para el rango de fechas seleccionado.</td></tr>';
}

echo '</tbody></table>';
echo '</body></html>';

$resultado->free();
exit;
