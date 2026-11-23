<?php
/**
 * Contenido dinámico del módulo de reportes.
 *
 * Agrupa el catálogo, los filtros y el resumen para que la página completa
 * y las consultas AJAX compartan exactamente el mismo marcado.
 *
 * Variables disponibles:
 * - $tiposReporte, $tipo, $reporte, $fechaDesde, $fechaHasta.
 * - $consultado, $esReportePeriodo, $errores, $resumen.
 */
?>
<div id="reportes-modulo" class="reportes-modulo">
    <?php require __DIR__ . '/catalogo.php'; ?>

    <?php require __DIR__ . ($esReportePeriodo
        ? '/filtros-diario.php'
        : '/filtros.php'
    ); ?>

    <?php if ($consultado && empty($errores) && $resumen !== null): ?>
        <div class="reportes-modulo__resultado" aria-live="polite">
            <?php require __DIR__ . ($esReportePeriodo
                ? '/resumen-diario.php'
                : '/resumen.php'
            ); ?>
        </div>
    <?php endif; ?>
</div>
