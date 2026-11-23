<?php
/**
 * Catálogo visual de reportes disponibles.
 *
 * Variables disponibles:
 * - $tiposReporte, $tipo.
 */

$iconosCatalogo = [
    'check' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
    'documento' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H5.625A1.875 1.875 0 0 0 3.75 4.125v15.75A1.875 1.875 0 0 0 5.625 21.75h12.75a1.875 1.875 0 0 0 1.875-1.875V14.25Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h3m-3 3h6m-6 3h4.5" />',
    'alerta' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />',
];

$gruposCatalogo = [
    [
        'titulo' => 'Planillas',
        'tipos' => ['liquidado', 'pagado'],
    ],
    [
        'titulo' => 'Planillas pagadas por período',
        'tipos' => ['diario', 'semanal', 'mensual', 'anual'],
        'clase_grid' => 'report-catalog__grid--periodos',
    ],
    [
        'titulo' => 'Derechos pendientes',
        'tipos' => ['derechos_pendientes', 'derechos_pendientes_pagados'],
    ],
];

$totalReportes = count($tiposReporte);
?>
<section class="report-catalog" aria-labelledby="report-catalog-title">
    <div class="report-catalog__header">
        <div>
            <h2 id="report-catalog-title" class="report-catalog__title">Tipos de reportes</h2>
            <p class="report-catalog__text">
                <?= number_format($totalReportes, 0, ',', '.') ?> reportes disponibles para generar en PDF o Excel.
            </p>
        </div>
    </div>

    <?php foreach ($gruposCatalogo as $grupo): ?>
        <div class="report-catalog__group">
            <h3 class="report-catalog__group-title">
                <?= htmlspecialchars($grupo['titulo'], ENT_QUOTES, 'UTF-8') ?>
            </h3>

            <div class="report-catalog__grid<?= !empty($grupo['clase_grid']) ? ' ' . htmlspecialchars($grupo['clase_grid'], ENT_QUOTES, 'UTF-8') : '' ?>">
                <?php foreach ($grupo['tipos'] as $tipoReporte): ?>
                    <?php
                    $reporteCatalogo = $tiposReporte[$tipoReporte] ?? null;

                    if ($reporteCatalogo === null) {
                        continue;
                    }

                    $activo = $tipo === $tipoReporte;
                    ?>
                    <a
                        class="report-card report-card--available<?= $activo ? ' is-selected' : '' ?>"
                        href="reportes.php?tipo=<?= urlencode($tipoReporte) ?>"
                        <?= $activo ? 'aria-current="page"' : '' ?>
                    >
                        <span class="report-card__icon" aria-hidden="true">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <?= $iconosCatalogo[$reporteCatalogo['icono']] ?? $iconosCatalogo['documento'] ?>
                            </svg>
                        </span>

                        <div class="report-card__content">
                            <div class="report-card__heading">
                                <h4 class="report-card__title">
                                    <?= htmlspecialchars($reporteCatalogo['titulo'], ENT_QUOTES, 'UTF-8') ?>
                                </h4>

                                <span class="report-card__badge report-card__badge--available">
                                    <?= $activo ? 'Seleccionado' : 'Disponible' ?>
                                </span>
                            </div>

                            <p class="report-card__description">
                                <?= htmlspecialchars($reporteCatalogo['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</section>
