<?php
/**
 * Contenido dinámico del módulo de reportes.
 *
 * Agrupa el catálogo, los filtros y el resumen para que la página completa
 * y las consultas AJAX compartan exactamente el mismo marcado.
 *
 * Variables disponibles:
 * - $tiposReporte, $tipo, $reporte, $fechaDesde, $fechaHasta.
 * - $consultado, $esReporteDiario, $errores, $resumen.
 */
?>
<div id="reportes-modulo" class="reportes-modulo">
    <?php require __DIR__ . '/catalogo.php'; ?>

    <?php require __DIR__ . ($esReporteDiario
        ? '/filtros-diario.php'
        : '/filtros.php'
    ); ?>

    <?php if ($consultado && empty($errores) && $resumen !== null): ?>
        <div class="reportes-modulo__resultado" aria-live="polite">
            <?php require __DIR__ . ($esReporteDiario
                ? '/resumen-diario.php'
                : '/resumen.php'
            ); ?>
        </div>
    <?php endif; ?>
</div>
