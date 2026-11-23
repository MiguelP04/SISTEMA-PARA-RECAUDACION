<?php
/**
 * Exportación PDF de reportes de planillas agrupados por aduana y forma.
 *
 * El PDF conserva las 12 columnas de Planillas Pagadas. El Excel plano se
 * genera en src/exportar_reporte_planillas_excel.php.
 */

/**
 * Genera y envía la descarga del reporte.
 *
 * @param mysqli $conn Conexión activa.
 * @param string $formato Formato solicitado: pdf o excel.
 * @param string $fechaDesde Fecha inicial.
 * @param string $fechaHasta Fecha final.
 * @param string $tipo Tipo de reporte: diario, semanal, mensual, anual o liquidado.
 * @param mysqli_result|null $resultado Detalle ya consultado; se obtiene si es null.
 * @param string $titulo Título visible del reporte.
 * @param string|null $nombreArchivo Nombre base del archivo; se calcula si es null.
 * @return void
 */
function exportarReporteDiario(
    mysqli $conn,
    string $formato,
    string $fechaDesde,
    string $fechaHasta,
    string $tipo = 'diario',
    ?mysqli_result $resultado = null,
    string $titulo = 'Reporte de Planillas Pagadas',
    ?string $nombreArchivo = null
): void {
    $resultado ??= obtenerDetalleReporteDiario($conn, $fechaDesde, $fechaHasta);

    $nombreArchivo ??= 'reporte_planillas_pagadas_' . $tipo . '_'
        . $fechaDesde . '_a_' . $fechaHasta;

    if ($formato === 'excel') {
        require_once __DIR__ . '/exportar_reporte_planillas_excel.php';

        exportarReportePlanillasExcel(
            $resultado,
            $fechaDesde,
            $fechaHasta,
            $nombreArchivo,
            $titulo
        );
        return;
    }

    if ($formato === 'pdf') {
        $pdf = new ReportePlanillasPDF('L', 'mm', 'Letter');
        $pdf->fechaDesde = $fechaDesde;
        $pdf->fechaHasta = $fechaHasta;
        $pdf->titulo = $titulo;

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
            $datos = formatearFilaReporteDiario($fila);

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
        return;
    }
}
