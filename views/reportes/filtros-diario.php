<?php
/**
 * Filtros de los reportes de planillas pagadas por período.
 *
 * Variables disponibles:
 * - $reporte, $tipo, $fechaDesde, $fechaHasta, $errores.
 */
?>
<section class="report-filters" aria-labelledby="report-filters-title">
    <div class="report-filters__heading">
        <h2 id="report-filters-title" class="report-filters__title">
            <?= htmlspecialchars($reporte['titulo'], ENT_QUOTES, 'UTF-8') ?>
        </h2>
        <p class="report-filters__text">
            <?= htmlspecialchars($reporte['descripcion'], ENT_QUOTES, 'UTF-8') ?>
        </p>
    </div>

    <?php if (!empty($errores)): ?>
        <div class="report-alert" role="alert">
            <?php foreach ($errores as $error): ?>
                <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form class="report-filters__form" method="get" action="reportes.php">
        <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>">

        <div class="report-filters__grid">
            <div class="report-field">
                <label class="report-field__label" for="reporte-desde">
                    <?= htmlspecialchars($reporte['etiqueta_desde'], ENT_QUOTES, 'UTF-8') ?>
                </label>
                <div class="report-field__date">
                    <input
                        class="report-field__input"
                        type="date"
                        id="reporte-desde"
                        name="desde"
                        value="<?= htmlspecialchars($fechaDesde, ENT_QUOTES, 'UTF-8') ?>"
                        data-date-display="reporte-desde-texto"
                        required
                    >
                    <span class="report-field__date-value" id="reporte-desde-texto" aria-hidden="true">
                        <?= htmlspecialchars(formatearFechaReporte($fechaDesde), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
            </div>

            <div class="report-field">
                <label class="report-field__label" for="reporte-hasta">
                    <?= htmlspecialchars($reporte['etiqueta_hasta'], ENT_QUOTES, 'UTF-8') ?>
                </label>
                <div class="report-field__date">
                    <input
                        class="report-field__input"
                        type="date"
                        id="reporte-hasta"
                        name="hasta"
                        value="<?= htmlspecialchars($fechaHasta, ENT_QUOTES, 'UTF-8') ?>"
                        data-date-display="reporte-hasta-texto"
                        required
                    >
                    <span class="report-field__date-value" id="reporte-hasta-texto" aria-hidden="true">
                        <?= htmlspecialchars(formatearFechaReporte($fechaHasta), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
            </div>

            <div class="report-filters__actions">
                <button class="report-button report-button--primary" type="submit" name="consultar" value="1">
                    Consultar
                </button>

                <a class="report-filters__reset" href="reportes.php?tipo=<?= urlencode($tipo) ?>">
                    Limpiar
                </a>
            </div>
        </div>
    </form>
</section>
