<?php
/**
 * Catálogo visual de reportes disponibles y futuros.
 *
 * Solo el reporte de planillas pagadas está habilitado. Las demás
 * tarjetas son informativas y no tienen acciones asociadas.
 */

$iconosCatalogo = [
    'check' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
    'documento' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H5.625A1.875 1.875 0 0 0 3.75 4.125v15.75A1.875 1.875 0 0 0 5.625 21.75h12.75a1.875 1.875 0 0 0 1.875-1.875V14.25Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h3m-3 3h6m-6 3h4.5" />',
    'reloj' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
    'alerta' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />',
    'calculadora' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.75h10.5A2.25 2.25 0 0 1 19.5 6v12a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 18V6a2.25 2.25 0 0 1 2.25-2.25Zm0 3h10.5m-9 3h.008v.008H8.25V9.75Zm3.75 0h.008v.008H12V9.75Zm3.75 0h.008v.008h-.008V9.75ZM8.25 13.5h.008v.008H8.25V13.5Zm3.75 0h.008v.008H12V13.5Zm3.75 0h.008v.008h-.008V13.5ZM8.25 17.25h.008v.008H8.25v-.008Zm3.75 0h.008v.008H12v-.008Zm3.75 0h.008v.008h-.008v-.008Z" />',
    'grafico' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />',
];

$catalogoReportes = [
    [
        'titulo' => 'Planillas',
        'reportes' => [
            [
                'titulo' => 'Planillas pagadas',
                'descripcion' => 'Planillas canceladas dentro del rango de fechas seleccionado.',
                'icono' => 'check',
                'disponible' => true,
            ],
            [
                'titulo' => 'Planillas liquidadas',
                'descripcion' => 'Planillas registradas por fecha de liquidación.',
                'icono' => 'documento',
                'disponible' => false,
            ],
            [
                'titulo' => 'Planillas sin pagar',
                'descripcion' => 'Planillas emitidas que aún no tienen fecha de cancelación.',
                'icono' => 'reloj',
                'disponible' => false,
            ],
        ],
    ],
    [
        'titulo' => 'Derechos pendientes',
        'reportes' => [
            [
                'titulo' => 'Derechos pendientes',
                'descripcion' => 'Obligaciones vencidas pendientes de gestión de cobranza.',
                'icono' => 'alerta',
                'disponible' => false,
            ],
            [
                'titulo' => 'Derechos pendientes pagados',
                'descripcion' => 'Derechos cancelados después de su fecha de vencimiento.',
                'icono' => 'check',
                'disponible' => false,
            ],
            [
                'titulo' => 'Derechos pendientes sin pagar',
                'descripcion' => 'Derechos vencidos que continúan sin cancelación.',
                'icono' => 'reloj',
                'disponible' => false,
            ],
        ],
    ],
    [
        'titulo' => 'Contabilidad y cobranzas',
        'reportes' => [
            [
                'titulo' => 'Auxi contable liquidadas',
                'descripcion' => 'Detalle contable de planillas liquidadas por concepto.',
                'icono' => 'calculadora',
                'disponible' => false,
            ],
            [
                'titulo' => 'Auxi contable autoliquidadas',
                'descripcion' => 'Detalle contable de planillas autoliquidadas por concepto.',
                'icono' => 'calculadora',
                'disponible' => false,
            ],
            [
                'titulo' => 'Gestión de cobranzas',
                'descripcion' => 'Resumen mensual de gestión de cobranza por impuesto.',
                'icono' => 'grafico',
                'disponible' => false,
            ],
        ],
    ],
];
?>
<section class="report-catalog" aria-labelledby="report-catalog-title">
    <div class="report-catalog__header">
        <div>
            <h2 id="report-catalog-title" class="report-catalog__title">Tipos de reportes</h2>
            <p class="report-catalog__text">
                Espacio reservado para los reportes de planillas. Por ahora solo está habilitado
                el reporte de planillas pagadas.
            </p>
        </div>
    </div>

    <?php foreach ($catalogoReportes as $grupo): ?>
        <div class="report-catalog__group">
            <h3 class="report-catalog__group-title">
                <?= htmlspecialchars($grupo['titulo'], ENT_QUOTES, 'UTF-8') ?>
            </h3>

            <div class="report-catalog__grid">
                <?php foreach ($grupo['reportes'] as $reporte): ?>
                    <?php $disponible = !empty($reporte['disponible']); ?>

                    <article
                        class="report-card<?= $disponible ? ' report-card--available' : ' is-disabled' ?>"
                        <?= $disponible ? '' : 'aria-disabled="true"' ?>
                    >
                        <span class="report-card__icon" aria-hidden="true">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <?= $iconosCatalogo[$reporte['icono']] ?? $iconosCatalogo['documento'] ?>
                            </svg>
                        </span>

                        <div class="report-card__content">
                            <div class="report-card__heading">
                                <h4 class="report-card__title">
                                    <?= htmlspecialchars($reporte['titulo'], ENT_QUOTES, 'UTF-8') ?>
                                </h4>

                                <span class="report-card__badge<?= $disponible ? ' report-card__badge--available' : '' ?>">
                                    <?= $disponible ? 'Disponible' : 'Próximamente' ?>
                                </span>
                            </div>

                            <p class="report-card__description">
                                <?= htmlspecialchars($reporte['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</section>
