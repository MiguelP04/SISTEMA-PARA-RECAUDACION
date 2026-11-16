<?php
/**
 * Documento PDF de detalle de planillas.
 *
 * Reproduce la estructura de los reportes anteriores: tabla horizontal de
 * doce columnas, agrupación por tipo de contribuyente y total general.
 *
 * Requiere que libs/fpdf/fpdf.php se haya cargado antes de este archivo.
 */

/**
 * Convierte texto UTF-8 al juego de caracteres de las fuentes estándar de FPDF.
 *
 * @param string $texto Texto original.
 * @return string Texto compatible con FPDF.
 */
function textoReportePdf(string $texto): string
{
    if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    }

    return utf8_decode($texto);
}

class ReporteDetallePDF extends FPDF
{
    public string $fechaDesde = '';
    public string $fechaHasta = '';
    public string $titulo = 'Reporte de Planillas';
    public string $etiquetaTotal = 'TOTAL GENERAL';
    public bool $tablaActiva = false;
    public string $grupoActivo = '';

    private array $widths = [25, 20, 18, 20, 45, 18, 18, 15, 20, 18, 18, 15];
    private array $aligns = ['L', 'L', 'L', 'L', 'L', 'L', 'R', 'L', 'L', 'L', 'L', 'L'];

    public function Header(): void
    {
        $this->Image(__DIR__ . '/../assets/img/seniat-logo.png', 10, 5, 40);
        $this->Image(
            __DIR__ . '/../assets/img/navbar-pieces/badge-right.png',
            $this->GetPageWidth() - 48,
            3,
            38
        );

        $this->SetY(12);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, textoReportePdf($this->titulo), 0, 1, 'C');

        $this->SetLineWidth(0.6);
        $this->Line(10, 28, $this->GetPageWidth() - 10, 28);

        $this->SetY(30);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(
            100,
            3,
            textoReportePdf(
                "Gerencia de Aduana Principal Las Piedras Paraguaná\n"
                . "División de Recaudación\n"
                . "Área de Contabilidad Fiscal"
            ),
            0,
            'L'
        );

        $this->SetXY($this->GetPageWidth() - 50, 30);
        $this->SetFont('Arial', '', 8);
        $this->Cell(40, 5, textoReportePdf('Fecha: ' . date('d/m/Y')), 'TRL', 2, 'L');
        $this->SetX($this->GetPageWidth() - 50);
        $this->Cell(
            40,
            5,
            textoReportePdf('Pág. ' . $this->PageNo() . ' de {nb}'),
            'BRL',
            1,
            'L'
        );

        $this->SetLineWidth(0.6);
        $this->Line(10, 44, $this->GetPageWidth() - 10, 44);

        $this->SetY(46);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(
            0,
            10,
            textoReportePdf(
                'Desde: ' . formatearFechaReporte($this->fechaDesde)
                . '    Hasta: ' . formatearFechaReporte($this->fechaHasta)
            ),
            0,
            1,
            'C'
        );

        $this->Ln(5);

        if ($this->tablaActiva) {
            if ($this->grupoActivo !== '') {
                $this->imprimirTituloGrupo();
            }

            $this->imprimirCabeceraTabla();
        }
    }

    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(
            0,
            10,
            textoReportePdf('Página ' . $this->PageNo() . '/{nb}'),
            0,
            0,
            'C'
        );
    }

    public function iniciarGrupo(string $grupo): void
    {
        $this->tablaActiva = false;
        $this->CheckPageBreak(28);

        $this->grupoActivo = $grupo;
        $this->imprimirTituloGrupo();
        $this->imprimirCabeceraTabla();
        $this->tablaActiva = true;
    }

    public function fila(array $datos): void
    {
        $this->Row($datos);
    }

    public function totalGeneral(float $monto): void
    {
        $this->tablaActiva = false;
        $this->Ln(5);
        $this->CheckPageBreak(12);
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(200, 200, 200);
        $this->SetTextColor(0, 0, 0);

        $this->Cell(
            array_sum($this->widths) - 50,
            10,
            textoReportePdf($this->etiquetaTotal),
            1,
            0,
            'C',
            true
        );
        $this->Cell(
            50,
            10,
            textoReportePdf('Bs. ' . formatearMontoReporte($monto)),
            1,
            1,
            'R',
            true
        );
    }

    public function sinRegistros(): void
    {
        $this->tablaActiva = false;
        $this->CheckPageBreak(12);
        $this->SetFont('Arial', '', 9);
        $this->Cell(
            array_sum($this->widths),
            10,
            textoReportePdf('No se encontraron registros para el rango de fechas seleccionado.'),
            1,
            1,
            'C'
        );
    }

    private function imprimirTituloGrupo(): void
    {
        $this->Ln(2);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(
            0,
            10,
            textoReportePdf('Contribuyente: ' . $this->grupoActivo),
            0,
            1,
            'L'
        );
        $this->Ln(1);
    }

    private function imprimirCabeceraTabla(): void
    {
        $this->SetFont('Arial', 'B', 7);
        $this->SetFillColor(200, 200, 200);
        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(143, 143, 143);
        $this->SetLineWidth(0.3);

        $this->Row([
            textoReportePdf('Nro. Confrontación'),
            textoReportePdf('Nro. Formulario'),
            textoReportePdf('Fecha Liq.'),
            'RIF Consignatario',
            'Nombre Consignatario',
            textoReportePdf('Fecha Pago'),
            'Monto',
            textoReportePdf('Tipo Forma'),
            textoReportePdf('Concepto'),
            textoReportePdf('Cód. Aduana'),
            textoReportePdf('Fecha Venc.'),
            'Estatus',
        ]);

        $this->SetFont('Arial', '', 7);
    }

    private function Row(array $datos): void
    {
        $lineas = 0;

        for ($i = 0; $i < count($datos); $i++) {
            $lineas = max($lineas, $this->NbLines($this->widths[$i], (string) $datos[$i]));
        }

        $alto = 5 * $lineas;
        $this->CheckPageBreak($alto);

        for ($i = 0; $i < count($datos); $i++) {
            $ancho = $this->widths[$i];
            $alineacion = $this->aligns[$i] ?? 'L';
            $x = $this->GetX();
            $y = $this->GetY();

            $this->Rect($x, $y, $ancho, $alto);
            $this->MultiCell($ancho, 5, (string) $datos[$i], 0, $alineacion);
            $this->SetXY($x + $ancho, $y);
        }

        $this->Ln($alto);
    }

    private function CheckPageBreak(float $alto): void
    {
        if ($this->GetY() + $alto > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    private function NbLines(float $ancho, string $texto): int
    {
        $anchoCaracteres = &$this->CurrentFont['cw'];

        if ($ancho == 0) {
            $ancho = $this->w - $this->rMargin - $this->x;
        }

        $anchoMaximo = ($ancho - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $texto = str_replace("\r", '', $texto);
        $cantidad = strlen($texto);

        if ($cantidad > 0 && $texto[$cantidad - 1] === "\n") {
            $cantidad--;
        }

        $separador = -1;
        $i = 0;
        $j = 0;
        $largo = 0;
        $lineas = 1;

        while ($i < $cantidad) {
            $caracter = $texto[$i];

            if ($caracter === "\n") {
                $i++;
                $separador = -1;
                $j = $i;
                $largo = 0;
                $lineas++;
                continue;
            }

            if ($caracter === ' ') {
                $separador = $i;
            }

            $largo += $anchoCaracteres[$caracter];

            if ($largo > $anchoMaximo) {
                if ($separador === -1) {
                    if ($i === $j) {
                        $i++;
                    }
                } else {
                    $i = $separador + 1;
                }

                $separador = -1;
                $j = $i;
                $largo = 0;
                $lineas++;
            } else {
                $i++;
            }
        }

        return $lineas;
    }
}
