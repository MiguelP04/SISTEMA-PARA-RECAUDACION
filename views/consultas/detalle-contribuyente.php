<?php
$rifRegistro = $tipo === 'agente'
    ? (string) $registro['rif_agentes']
    : (string) $registro['rif_consignatario'];
$nombreRegistro = limpiarTextoContribuyente(
    $tipo === 'agente' ? $registro['nombre_agente'] : $registro['nombre_consignatario']
);
$totalRegistros = (int) ($resumen['total_registros'] ?? 0);
$totalVigentes = (int) ($resumen['total'] ?? 0);
$anuladas = max(0, $totalRegistros - $totalVigentes);
$puedeEliminar = $totalRegistros === 0;
?>

<section class="queries-panel" aria-labelledby="contribuyente-detail-title">
    <header class="queries-panel__heading">
        <div>
            <p class="queries-panel__eyebrow"><?= $escapar($etiquetaTipo) ?> encontrado</p>
            <h2 id="contribuyente-detail-title" class="queries-panel__title">
                <?= $escapar($nombreRegistro !== '' ? $nombreRegistro : 'Sin nombre registrado') ?>
            </h2>
            <p class="queries-panel__subtitle">RIF <?= $escapar($rifRegistro) ?></p>
        </div>

        <span class="queries-status queries-status--entidad">
            <?= $escapar($etiquetaTipo) ?>
        </span>
    </header>

    <div class="queries-summary queries-summary--four">
        <div class="queries-summary__item">
            <span>Planillas vigentes</span>
            <strong><?= $escapar($totalVigentes) ?></strong>
        </div>

        <div class="queries-summary__item">
            <span>Monto emitido</span>
            <strong>Bs <?= $escapar(formatearMontoContribuyente($resumen['monto'] ?? 0)) ?></strong>
        </div>

        <div class="queries-summary__item">
            <span>Pagadas</span>
            <strong><?= $escapar($resumen['pagadas'] ?? 0) ?></strong>
        </div>

        <div class="queries-summary__item">
            <span>Pendientes</span>
            <strong><?= $escapar($resumen['pendientes'] ?? 0) ?></strong>
        </div>
    </div>

    <?php if ($anuladas > 0): ?>
        <p class="queries-panel__note">
            El resumen excluye <?= $escapar($anuladas) ?> planilla<?= $anuladas === 1 ? '' : 's' ?> anulada<?= $anuladas === 1 ? '' : 's' ?>.
        </p>
    <?php endif; ?>

    <div class="queries-detail-grid">
        <?php if ($tipo === 'consignatario'): ?>
            <section class="queries-detail-group" aria-labelledby="contribuyente-data-title">
                <h3 id="contribuyente-data-title">Datos del consignatario</h3>

                <dl class="queries-detail-list">
                    <div>
                        <dt>RIF</dt>
                        <dd><?= $escapar($rifRegistro) ?></dd>
                    </div>

                    <div>
                        <dt>Tipo de contribuyente</dt>
                        <dd><?= $escapar(trim((string) ($registro['desc_tipo_contribuyente'] ?? '')) !== '' ? $registro['desc_tipo_contribuyente'] : '—') ?></dd>
                    </div>

                    <div>
                        <dt>Teléfono</dt>
                        <dd><?= $escapar(limpiarTextoContribuyente($registro['telefono'] ?? '') !== '' ? $registro['telefono'] : '—') ?></dd>
                    </div>

                    <div>
                        <dt>Representante</dt>
                        <dd><?= $escapar(limpiarTextoContribuyente($registro['representante'] ?? '') !== '' ? $registro['representante'] : '—') ?></dd>
                    </div>

                    <div>
                        <dt>Siglas</dt>
                        <dd><?= $escapar(limpiarTextoContribuyente($registro['siglas'] ?? '') !== '' ? $registro['siglas'] : '—') ?></dd>
                    </div>

                    <div>
                        <dt>CIR</dt>
                        <dd><?= $escapar(trim((string) ($registro['cir'] ?? '')) !== '' ? $registro['cir'] : '—') ?></dd>
                    </div>

                    <div>
                        <dt>Ciudad</dt>
                        <dd><?= $escapar(trim((string) ($registro['nombre_ciudad'] ?? '')) !== '' ? $registro['nombre_ciudad'] : '—') ?></dd>
                    </div>

                    <div>
                        <dt>Zona postal</dt>
                        <dd><?= $escapar((int) ($registro['zona_postal'] ?? 0) > 0 ? $registro['zona_postal'] : '—') ?></dd>
                    </div>
                </dl>
            </section>

            <section class="queries-detail-group" aria-labelledby="contribuyente-address-title">
                <h3 id="contribuyente-address-title">Ubicación y referencia</h3>

                <dl class="queries-detail-list queries-detail-list--single">
                    <div>
                        <dt>Dirección fiscal</dt>
                        <dd><?= $escapar(limpiarTextoContribuyente($registro['direccion'] ?? '') !== '' ? $registro['direccion'] : '—') ?></dd>
                    </div>

                    <div>
                        <dt>Referencia</dt>
                        <dd><?= $escapar(limpiarTextoContribuyente($registro['referencia'] ?? '') !== '' ? $registro['referencia'] : '—') ?></dd>
                    </div>
                </dl>
            </section>
        <?php else: ?>
            <section class="queries-detail-group" aria-labelledby="contribuyente-data-title">
                <h3 id="contribuyente-data-title">Datos del agente aduanal</h3>

                <dl class="queries-detail-list">
                    <div>
                        <dt>RIF</dt>
                        <dd><?= $escapar($rifRegistro) ?></dd>
                    </div>

                    <div>
                        <dt>N° de registro</dt>
                        <dd><?= $escapar(trim((string) ($registro['numero_registro_agente_aduanal'] ?? '')) !== '' ? $registro['numero_registro_agente_aduanal'] : '—') ?></dd>
                    </div>

                    <div>
                        <dt>Teléfono</dt>
                        <dd><?= $escapar(limpiarTextoContribuyente($registro['telefono'] ?? '') !== '' ? $registro['telefono'] : '—') ?></dd>
                    </div>

                    <div>
                        <dt>Representante</dt>
                        <dd><?= $escapar(limpiarTextoContribuyente($registro['representante'] ?? '') !== '' ? $registro['representante'] : '—') ?></dd>
                    </div>

                    <div>
                        <dt>Siglas</dt>
                        <dd><?= $escapar(limpiarTextoContribuyente($registro['siglas_agente'] ?? '') !== '' ? $registro['siglas_agente'] : '—') ?></dd>
                    </div>
                </dl>
            </section>

            <section class="queries-detail-group" aria-labelledby="contribuyente-address-title">
                <h3 id="contribuyente-address-title">Domicilio fiscal</h3>

                <dl class="queries-detail-list queries-detail-list--single">
                    <div>
                        <dt>Domicilio</dt>
                        <dd><?= $escapar(limpiarTextoContribuyente($registro['domicilio_fiscal'] ?? '') !== '' ? $registro['domicilio_fiscal'] : '—') ?></dd>
                    </div>
                </dl>
            </section>
        <?php endif; ?>
    </div>

    <div class="queries-actions">
        <a class="registry-button registry-button--secondary" href="consulta_agentes_consignatarios.php?tipo=<?= urlencode($tipo) ?>">
            Nueva consulta
        </a>

        <?php if ($esAdmin): ?>
            <a
                class="registry-button registry-button--secondary"
                href="consulta_agentes_consignatarios.php?tipo=<?= urlencode($tipo) ?>&rif=<?= urlencode($rifRegistro) ?>&editar=1"
            >
                Editar registro
            </a>

            <?php if ($puedeEliminar): ?>
                <form
                    class="queries-inline-form"
                    action="eliminar_contribuyente.php"
                    method="post"
                    data-confirm-delete="¿Eliminar el <?= $escapar(strtolower($etiquetaTipo)) ?> <?= $escapar($rifRegistro) ?>? Esta acción no se puede deshacer."
                >
                    <input type="hidden" name="tipo" value="<?= $escapar($tipo) ?>">
                    <input type="hidden" name="rif" value="<?= $escapar($rifRegistro) ?>">

                    <button class="registry-button registry-button--danger" type="submit">
                        Eliminar registro
                    </button>
                </form>
            <?php else: ?>
                <button class="registry-button registry-button--danger" type="button" disabled>
                    Eliminar registro
                </button>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if (!$puedeEliminar && $esAdmin): ?>
        <p class="queries-delete-note">
            No se puede eliminar este registro porque tiene <?= $escapar($totalRegistros) ?>
            planilla<?= $totalRegistros === 1 ? '' : 's' ?> asociada<?= $totalRegistros === 1 ? '' : 's' ?>.
        </p>
    <?php endif; ?>
</section>
