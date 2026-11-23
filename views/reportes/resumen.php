<?php
/**
 * Resumen del reporte consultado.
 *
 * No muestra el detalle de las planillas: solo totales y accesos de descarga.
 *
 * Variables disponibles:
 * - $reporte, $tipo, $resumen, $fechaDesde, $fechaHasta.
 */
$fechaDesdeTexto = formatearFechaReporte($fechaDesde);
$fechaHastaTexto = formatearFechaReporte($fechaHasta);
$hayResultados = $resumen['total'] > 0;
$etiquetaTotal = $reporte['total'] === 'TOTAL GENERAL RECAUDADO'
    ? 'Total general recaudado'
    : 'Total general';

$parametrosExportacion = [
    'tipo' => $tipo,
    'desde' => $fechaDesde,
    'hasta' => $fechaHasta,
];

if ($esReporteLiquidado) {
    if ($aduanaSeleccionada !== '') {
        $parametrosExportacion['aduana'] = $aduanaSeleccionada;
    }

    if ($formaSeleccionada !== '') {
        $parametrosExportacion['forma'] = $formaSeleccionada;
    }
}

$urlPdf = 'reporte_exportar.php?' . http_build_query(
    $parametrosExportacion + ['formato' => 'pdf']
);
$urlExcel = 'reporte_exportar.php?' . http_build_query(
    $parametrosExportacion + ['formato' => 'excel']
);
?>
<section class="report-summary" aria-labelledby="report-summary-title">
    <div class="report-summary__header">
        <div>
            <p class="report-summary__eyebrow">Resultado de la consulta</p>
            <h2 id="report-summary-title" class="report-summary__title">
                <?= htmlspecialchars('Reporte de ' . $reporte['titulo'], ENT_QUOTES, 'UTF-8') ?>
            </h2>
        </div>

        <div class="report-summary__range">
            <span>Desde: <?= htmlspecialchars($fechaDesdeTexto, ENT_QUOTES, 'UTF-8') ?></span>
            <span>Hasta: <?= htmlspecialchars($fechaHastaTexto, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>

    <div class="report-summary__stats">
        <article class="report-stat">
            <p class="report-stat__label">Total de planillas</p>
            <p class="report-stat__value">
                <?= number_format((int) $resumen['total'], 0, ',', '.') ?>
            </p>
        </article>

        <article class="report-stat">
            <p class="report-stat__label"><?= htmlspecialchars($etiquetaTotal, ENT_QUOTES, 'UTF-8') ?></p>
            <p class="report-stat__value report-stat__value--currency">
                Bs <?= htmlspecialchars(formatearMontoReporte($resumen['monto']), ENT_QUOTES, 'UTF-8') ?>
            </p>
        </article>
    </div>

    <div class="report-summary__breakdown">
        <h3 class="report-summary__breakdown-title">
            <?= $esReporteLiquidado ? 'Totales por aduana y forma' : 'Totales por tipo de contribuyente' ?>
        </h3>

        <?php if ($esReporteLiquidado && !empty($resumen['por_aduana'])): ?>
            <div class="report-breakdown">
                <?php foreach ($resumen['por_aduana'] as $aduana): ?>
                    <div class="report-breakdown__group">
                        <div class="report-breakdown__row report-breakdown__row--aduana">
                            <div class="report-breakdown__name">
                                <strong><?= htmlspecialchars($aduana['codigo_aduana'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span><?= htmlspecialchars($aduana['nombre_de_la_aduana'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>

                            <div class="report-breakdown__count">
                                <?= number_format((int) $aduana['total'], 0, ',', '.') ?> planillas
                            </div>

                            <div class="report-breakdown__amount">
                                Bs <?= htmlspecialchars(formatearMontoReporte($aduana['monto']), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        </div>

                        <?php foreach ($aduana['por_forma'] as $forma): ?>
                            <div class="report-breakdown__row report-breakdown__row--forma">
                                <div class="report-breakdown__name report-breakdown__name--forma">
                                    <span><?= htmlspecialchars($forma['denominacion_forma'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>

                                <div class="report-breakdown__count">
                                    <?= number_format((int) $forma['total'], 0, ',', '.') ?> planillas
                                </div>

                                <div class="report-breakdown__amount">
                                    Bs <?= htmlspecialchars(formatearMontoReporte($forma['monto']), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (!$esReporteLiquidado && !empty($resumen['por_grupo'])): ?>
            <div class="report-breakdown">
                <?php foreach ($resumen['por_grupo'] as $grupo): ?>
                    <div class="report-breakdown__row report-breakdown__row--grupo">
                        <div class="report-breakdown__name">
                            <strong><?= htmlspecialchars($grupo['grupo'], ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>

                        <div class="report-breakdown__count">
                            <?= number_format((int) $grupo['total'], 0, ',', '.') ?> planillas
                        </div>

                        <div class="report-breakdown__amount">
                            Bs <?= htmlspecialchars(formatearMontoReporte($grupo['monto']), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="report-summary__empty">
                No se encontraron planillas para el rango de fechas seleccionado.
            </p>
        <?php endif; ?>
    </div>

    <div class="report-summary__actions">
        <?php if ($hayResultados): ?>
            <a class="report-export-button report-export-button--pdf" href="<?= htmlspecialchars($urlPdf, ENT_QUOTES, 'UTF-8') ?>">
                Descargar PDF
            </a>

            <a class="report-export-button report-export-button--excel" href="<?= htmlspecialchars($urlExcel, ENT_QUOTES, 'UTF-8') ?>">
                Descargar Excel
            </a>
        <?php else: ?>
            <span class="report-export-button report-export-button--pdf is-disabled" aria-disabled="true">
                Descargar PDF
            </span>

            <span class="report-export-button report-export-button--excel is-disabled" aria-disabled="true">
                Descargar Excel
            </span>
        <?php endif; ?>
    </div>
</section>
