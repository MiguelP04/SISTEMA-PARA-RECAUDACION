<?php
/**
 * Documento PDF del reporte de planillas por fecha de cancelación.
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

class ReportePlanillasPDF extends FPDF
{
    private const ALTO_CABECERA_TABLA = 8;
    private const ALTO_BLOQUE_ADUANA = 15;
    private const ALTO_BLOQUE_FORMA = 15;
    private const ANCHO_ETIQUETA_RESUMEN = 45;

    public string $fechaDesde = '';
    public string $fechaHasta = '';
    public bool $tablaActiva = false;
    public string $tituloAduanaActiva = '';
    public string $tituloFormaActiva = '';

    private array $widths = [30, 22, 20, 19, 60, 24, 16, 21, 21, 15];
    private array $aligns = ['L', 'L', 'L', 'L', 'L', 'R', 'L', 'L', 'L', 'C'];

    public function Header(): void
    {
        $this->Image(__DIR__ . '/../assets/img/seniat-logo.png', 10, 6, 35);
        $this->Image(
            __DIR__ . '/../assets/img/navbar-pieces/badge-right.png',
            $this->GetPageWidth() - 32,
            6,
            22
        );

        $this->SetY(10);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(
            0,
            8,
            textoReportePdf('Reporte de Planillas por Fecha de Cancelación'),
            0,
            1,
            'C'
        );

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

        $this->SetXY($this->GetPageWidth() - 60, 30);
        $this->SetFont('Arial', '', 8);
        $this->Cell(50, 5, textoReportePdf('Fecha: ' . date('d/m/Y')), 'TRL', 2, 'L');
        $this->SetX($this->GetPageWidth() - 60);
        $this->Cell(
            50,
            5,
            textoReportePdf('Pág. ' . $this->PageNo() . ' de {nb}'),
            'BRL',
            1,
            'L'
        );

        $this->SetLineWidth(0.6);
        $this->Line(10, 44, $this->GetPageWidth() - 10, 44);

        $this->SetY(46);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(
            0,
            6,
            textoReportePdf(
                'Desde: ' . formatearFechaReporte($this->fechaDesde)
                . '    Hasta: ' . formatearFechaReporte($this->fechaHasta)
            ),
            0,
            1,
            'C'
        );

        $this->Ln(2);

        if ($this->tablaActiva) {
            if ($this->tituloAduanaActiva !== '') {
                $this->imprimirTituloAduana();
            }

            if ($this->tituloFormaActiva !== '') {
                $this->imprimirTituloForma();
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

    public function imprimirCabeceraTabla(): void
    {
        if ($this->GetY() + self::ALTO_CABECERA_TABLA > $this->PageBreakTrigger) {
            $tablaActiva = $this->tablaActiva;
            $this->tablaActiva = false;
            $this->AddPage($this->CurOrientation);
            $this->tablaActiva = $tablaActiva;
        }

        $etiquetas = [
            "Nro.\nConfrontación",
            'Formulario',
            "Fecha\nLiquidación",
            "Nro\nLiquidación",
            'Contribuyente',
            "Monto\nTotal",
            "Tipo\nPlanilla",
            "Fecha\nVencimiento",
            "Fecha\nCancelación",
            "Días\nMora",
        ];

        $this->SetFont('Arial', 'B', 7);
        $this->SetFillColor(26, 31, 83);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(143, 143, 143);
        $this->SetLineWidth(0.3);

        $xInicial = $this->GetX();
        $yInicial = $this->GetY();
        $alto = self::ALTO_CABECERA_TABLA;

        foreach ($etiquetas as $indice => $etiqueta) {
            $ancho = $this->widths[$indice];
            $this->Rect($xInicial, $yInicial, $ancho, $alto, 'DF');
            $this->SetXY($xInicial, $yInicial);
            $this->MultiCell($ancho, 4, textoReportePdf($etiqueta), 0, 'C');
            $xInicial += $ancho;
        }

        $this->SetXY($this->lMargin, $yInicial + $alto);
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(0, 0, 0);
    }

    public function iniciarTablaForma(
        bool $nuevaAduana,
        string $codigoAduana,
        string $nombreAduana,
        string $denominacion
    ): void {
        $this->tablaActiva = false;

        $alto = self::ALTO_BLOQUE_FORMA + self::ALTO_CABECERA_TABLA + 6;
        if ($nuevaAduana) {
            $alto += self::ALTO_BLOQUE_ADUANA;
            $this->tituloAduanaActiva = 'Aduana: ' . $codigoAduana . ' - ' . $nombreAduana;
        }

        $this->CheckPageBreak($alto);

        if ($nuevaAduana) {
            $this->imprimirTituloAduana();
        }

        $this->tituloFormaActiva = $denominacion;
        $this->imprimirTituloForma();
        $this->imprimirCabeceraTabla();
        $this->tablaActiva = true;
    }

    private function imprimirTituloAduana(): void
    {
        $this->Ln(3);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 7, textoReportePdf($this->tituloAduanaActiva), 0, 1, 'L');
    }

    private function imprimirTituloForma(): void
    {
        $this->SetFont('Arial', 'B', 9);
        $this->MultiCell(0, 5, textoReportePdf($this->tituloFormaActiva), 0, 'L');
        $this->Ln(1);
    }

    public function subtotal(string $etiqueta, float $monto): void
    {
        $this->CheckPageBreak(7);
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(238, 241, 247);
        $this->SetTextColor(0, 0, 0);

        $this->Cell(
            array_sum($this->widths) - 40,
            7,
            textoReportePdf($etiqueta),
            1,
            0,
            'C',
            true
        );
        $this->Cell(
            40,
            7,
            textoReportePdf('Bs ' . formatearMontoReporte($monto)),
            1,
            1,
            'R',
            true
        );

        $this->SetFont('Arial', '', 7);
    }

    public function totalForma(string $etiqueta, int $cantidad): void
    {
        $this->CheckPageBreak(7);
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(238, 241, 247);
        $this->SetTextColor(0, 0, 0);

        $this->Cell(
            array_sum($this->widths) - 40,
            7,
            textoReportePdf($etiqueta),
            1,
            0,
            'C',
            true
        );

        $cantidadTexto = $this->cantidadPlanillas($cantidad);

        $this->Cell(
            40,
            7,
            textoReportePdf($cantidadTexto),
            1,
            1,
            'R',
            true
        );

        $this->SetFont('Arial', '', 7);
    }

    public function cierreForma(
        string $etiquetaTotal,
        string $etiquetaSubtotal,
        int $cantidad,
        float $monto
    ): void {
        $this->CheckPageBreak(7);
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(238, 241, 247);
        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(143, 143, 143);

        $anchoBloque = array_sum($this->widths) / 2;
        $anchoValor = $anchoBloque - self::ANCHO_ETIQUETA_RESUMEN;

        $this->imprimirBloqueResumen(
            $etiquetaTotal,
            $this->cantidadPlanillas($cantidad),
            $anchoValor
        );
        $this->imprimirBloqueResumen(
            $etiquetaSubtotal,
            'Bs ' . formatearMontoReporte($monto),
            $anchoValor,
            true
        );

        $this->SetFont('Arial', '', 7);
    }

    public function cierreAduana(
        string $etiquetaTotal,
        string $etiquetaSubtotal,
        int $cantidad,
        float $montoForma,
        string $etiquetaAduana,
        float $montoAduana
    ): void {
        $this->CheckPageBreak(7);
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(238, 241, 247);
        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(143, 143, 143);

        $anchoBloque = array_sum($this->widths) / 3;
        $anchoValor = $anchoBloque - self::ANCHO_ETIQUETA_RESUMEN;

        $this->imprimirBloqueResumen(
            $etiquetaTotal,
            $this->cantidadPlanillas($cantidad),
            $anchoValor
        );
        $this->imprimirBloqueResumen(
            $etiquetaSubtotal,
            'Bs ' . formatearMontoReporte($montoForma),
            $anchoValor
        );
        $this->imprimirBloqueResumen(
            $etiquetaAduana,
            'Bs ' . formatearMontoReporte($montoAduana),
            $anchoValor,
            true
        );

        $this->SetFont('Arial', '', 7);
    }

    private function imprimirBloqueResumen(
        string $etiqueta,
        string $valor,
        float $anchoValor,
        bool $cerrarFila = false
    ): void {
        $this->Cell(
            self::ANCHO_ETIQUETA_RESUMEN,
            7,
            textoReportePdf($etiqueta . ':'),
            'LTB',
            0,
            'L',
            true
        );
        $this->Cell(
            $anchoValor,
            7,
            textoReportePdf($valor),
            $cerrarFila ? 'TRB' : 'TB',
            $cerrarFila ? 1 : 0,
            'C',
            true
        );
    }

    private function cantidadPlanillas(int $cantidad): string
    {
        return number_format($cantidad, 0, ',', '.')
            . ($cantidad === 1 ? ' planilla' : ' planillas');
    }

    public function totalGeneral(float $monto): void
    {
        $this->Ln(2);
        $this->CheckPageBreak(9);
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(255, 198, 10);
        $this->SetTextColor(26, 31, 83);

        $this->Cell(
            array_sum($this->widths) - 50,
            8,
            textoReportePdf('TOTAL GENERAL'),
            1,
            0,
            'C',
            true
        );
        $this->Cell(
            50,
            8,
            textoReportePdf('Bs ' . formatearMontoReporte($monto)),
            1,
            1,
            'R',
            true
        );

        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 7);
    }

    public function fila(array $datos): void
    {
        $this->Row($datos);
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
