<?php
$nroPlanilla = (string) $planilla['nro_formulario'];
$estadoClave = (string) ($estado['clave'] ?? 'pendiente');
$estadoTexto = (string) ($estado['texto'] ?? 'Pendiente');
$estadoModificador = (string) ($estado['modificador'] ?? 'pendiente');
$estaPagada = $estadoClave === 'pagada';

$rifConsignatario = trim((string) ($planilla['rif_consignatario'] ?? ''));
$nombreConsignatario = trim((string) ($planilla['nombre_consignatario'] ?? ''));
$rifAgente = trim((string) ($planilla['rif_agente'] ?? ''));
$nombreAgente = trim((string) ($planilla['nombre_agente'] ?? ''));
$nombreAduana = trim((string) ($planilla['nombre_de_la_aduana'] ?? ''));
$codigoAduana = trim((string) ($planilla['codigo_aduana'] ?? ''));
$formaCodigo = trim((string) ($planilla['forma_codigo'] ?? ''));
$formaDenominacion = trim((string) ($planilla['forma_denominacion'] ?? ''));
$tipoPlanillaDescripcion = trim((string) ($planilla['tipo_planilla_descripcion'] ?? ''));
$nombreBanco = trim((string) ($planilla['nombre_banco'] ?? ''));
$nombreAgencia = trim((string) ($planilla['nombre_agencia'] ?? ''));
$cantidadRamos = (int) ($planilla['cantidad_ramos'] ?? 0);
$montoRamos = (float) ($planilla['monto_ramos'] ?? 0);
$anulada = (int) ($planilla['anulada_num'] ?? 0) === 1;
?>

<section class="queries-panel" aria-labelledby="planilla-detail-title">
    <header class="queries-panel__heading">
        <div>
            <p class="queries-panel__eyebrow">Planilla encontrada</p>
            <h2 id="planilla-detail-title" class="queries-panel__title">
                N° <?= $escapar($nroPlanilla) ?>
            </h2>
            <p class="queries-panel__subtitle">
                Registrada el <?= $escapar(formatearFechaHoraConsulta($planilla['system_date'] ?? '')) ?>
            </p>
        </div>

        <span class="queries-status queries-status--<?= $escapar($estadoModificador) ?>">
            <?= $escapar($estadoTexto) ?>
        </span>
    </header>

    <div class="queries-summary">
        <div class="queries-summary__item">
            <span>Monto total</span>
            <strong>Bs <?= $escapar(formatearMontoConsulta($planilla['monto2'] ?? 0)) ?></strong>
        </div>

        <div class="queries-summary__item">
            <span>Ramos registrados</span>
            <strong><?= $escapar($cantidadRamos) ?></strong>
        </div>

        <div class="queries-summary__item">
            <span>Total de ramos</span>
            <strong>Bs <?= $escapar(formatearMontoConsulta($montoRamos)) ?></strong>
        </div>
    </div>

    <div class="queries-detail-grid">
        <section class="queries-detail-group" aria-labelledby="planilla-data-title">
            <h3 id="planilla-data-title">Datos de la planilla</h3>

            <dl class="queries-detail-list">
                <div>
                    <dt>Aduana</dt>
                    <dd>
                        <?= $escapar($codigoAduana !== '' ? $codigoAduana : '—') ?>
                        <?php if ($nombreAduana !== ''): ?>
                            <span><?= $escapar($nombreAduana) ?></span>
                        <?php endif; ?>
                    </dd>
                </div>

                <div>
                    <dt>Forma</dt>
                    <dd>
                        <?= $escapar($formaCodigo !== '' ? $formaCodigo : codigoFormaVisibleConsulta($planilla['cod_de_forma'] ?? '')) ?>
                        <?php if ($formaDenominacion !== ''): ?>
                            <span><?= $escapar($formaDenominacion) ?></span>
                        <?php endif; ?>
                    </dd>
                </div>

                <div>
                    <dt>Tipo de planilla</dt>
                    <dd>
                        <?= $escapar($planilla['tipo_de_planilla'] ?? '—') ?>
                        <?php if ($tipoPlanillaDescripcion !== ''): ?>
                            <span><?= $escapar($tipoPlanillaDescripcion) ?></span>
                        <?php endif; ?>
                    </dd>
                </div>

                <div>
                    <dt>Año</dt>
                    <dd><?= $escapar($planilla['ano_declaracion'] ?? '—') ?></dd>
                </div>

                <div>
                    <dt>N° Declaración</dt>
                    <dd><?= $escapar($planilla['nro_declaracion'] ?? '—') ?></dd>
                </div>

                <div>
                    <dt>N° Liquidación</dt>
                    <dd><?= $escapar($planilla['nro_liquidacion'] ?? '—') ?></dd>
                </div>

                <div>
                    <dt>N° Resolución</dt>
                    <dd>
                        <?= $escapar(trim((string) ($planilla['nro_resolucion'] ?? '')) !== '' ? $planilla['nro_resolucion'] : '—') ?>
                    </dd>
                </div>

                <div>
                    <dt>Fecha de emisión</dt>
                    <dd><?= $escapar(formatearFechaConsulta($planilla['fecha_emision'] ?? '')) ?></dd>
                </div>

                <div>
                    <dt>Fecha de liquidación</dt>
                    <dd><?= $escapar(formatearFechaConsulta($planilla['fecha_contabilidad_liq'] ?? '')) ?></dd>
                </div>

                <div>
                    <dt>Fecha de notificación</dt>
                    <dd><?= $escapar(formatearFechaConsulta($planilla['fecha_de_notificacion'] ?? '')) ?></dd>
                </div>

                <div>
                    <dt>Fecha de vencimiento</dt>
                    <dd><?= $escapar(formatearFechaConsulta($planilla['fecha_vencimiento'] ?? '')) ?></dd>
                </div>
            </dl>
        </section>

        <section class="queries-detail-group" aria-labelledby="planilla-contributors-title">
            <h3 id="planilla-contributors-title">Contribuyentes</h3>

            <dl class="queries-detail-list">
                <div>
                    <dt>RIF del consignatario</dt>
                    <dd><?= $escapar($rifConsignatario !== '' ? $rifConsignatario : '—') ?></dd>
                </div>

                <div>
                    <dt>Consignatario</dt>
                    <dd><?= $escapar($nombreConsignatario !== '' ? $nombreConsignatario : 'No encontrado') ?></dd>
                </div>

                <div>
                    <dt>RIF del agente</dt>
                    <dd><?= $escapar($rifAgente !== '' ? $rifAgente : '—') ?></dd>
                </div>

                <div>
                    <dt>Agente aduanal</dt>
                    <dd><?= $escapar($nombreAgente !== '' ? $nombreAgente : 'No encontrado') ?></dd>
                </div>
            </dl>
        </section>

        <section class="queries-detail-group queries-detail-group--wide" aria-labelledby="planilla-recaudacion-title">
            <h3 id="planilla-recaudacion-title">Recaudación y registro</h3>

            <dl class="queries-detail-list queries-detail-list--four">
                <div>
                    <dt>Fecha de cancelación</dt>
                    <dd><?= $escapar(formatearFechaConsulta($planilla['fecha_cancelacion'] ?? '')) ?></dd>
                </div>

                <div>
                    <dt>Fecha de contabilidad</dt>
                    <dd><?= $escapar(formatearFechaConsulta($planilla['fecha_contabilidad_rec'] ?? '')) ?></dd>
                </div>

                <div>
                    <dt>Fecha de entrega</dt>
                    <dd><?= $escapar(formatearFechaConsulta($planilla['fecha_de_entreguese'] ?? '')) ?></dd>
                </div>

                <div>
                    <dt>Anulada</dt>
                    <dd><?= $anulada ? 'Sí' : 'No' ?></dd>
                </div>

                <div>
                    <dt>Banco</dt>
                    <dd><?= $escapar($nombreBanco !== '' ? $nombreBanco : 'Sin recaudación') ?></dd>
                </div>

                <div>
                    <dt>Agencia</dt>
                    <dd><?= $escapar($nombreAgencia !== '' ? $nombreAgencia : 'Sin recaudación') ?></dd>
                </div>

                <div>
                    <dt>Fecha de sistema</dt>
                    <dd><?= $escapar(formatearFechaConsulta($planilla['fecha_sist'] ?? '')) ?></dd>
                </div>

                <div>
                    <dt>Hora de sistema</dt>
                    <dd><?= $escapar($planilla['hora_sist'] ?? '—') ?></dd>
                </div>

                <div>
                    <dt>Estado interno</dt>
                    <dd><?= $escapar($planilla['cod_status'] ?? '—') ?></dd>
                </div>

                <div>
                    <dt>Código nuevo</dt>
                    <dd><?= $escapar($planilla['cod_nuevo'] ?? '—') ?></dd>
                </div>

                <?php if ($anulada): ?>
                    <div>
                        <dt>Fecha de anulación</dt>
                        <dd><?= $escapar(formatearFechaConsulta($planilla['fecha_anulacion'] ?? '')) ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
        </section>
    </div>

    <section class="queries-ramos" aria-labelledby="planilla-ramos-title">
        <div class="queries-ramos__heading">
            <div>
                <p class="queries-panel__eyebrow">Detalle de pago</p>
                <h3 id="planilla-ramos-title">Ramos de ingreso</h3>
            </div>

            <span><?= $escapar($cantidadRamos) ?> ramo<?= $cantidadRamos === 1 ? '' : 's' ?></span>
        </div>

        <?php if (empty($ramos)): ?>
            <p class="queries-empty-state">Esta planilla no tiene ramos registrados.</p>
        <?php else: ?>
            <div class="queries-table-wrapper">
                <table class="queries-table">
                    <thead>
                        <tr>
                            <th scope="col">Ramo de ingreso</th>
                            <th scope="col">Forma de impuesto</th>
                            <th scope="col">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ramos as $ramo): ?>
                            <tr>
                                <td>
                                    <strong><?= $escapar($ramo['cod_concepto'] ?? '') ?></strong>
                                    <span><?= $escapar($ramo['descripcion_concepto'] ?? 'Sin descripción') ?></span>
                                </td>
                                <td>
                                    <strong><?= $escapar($ramo['cod_forma'] ?? '') ?></strong>
                                    <span><?= $escapar($ramo['forma_descripcion'] ?? 'Sin descripción') ?></span>
                                </td>
                                <td class="queries-table__monto">
                                    Bs <?= $escapar(formatearMontoConsulta($ramo['monto'] ?? 0)) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <div class="queries-actions">
        <a class="registry-button registry-button--secondary" href="consulta_planilla.php">
            Nueva consulta
        </a>

        <?php if ($esAdmin): ?>
            <a
                class="registry-button registry-button--secondary"
                href="consulta_planilla.php?nro=<?= urlencode($nroPlanilla) ?>&editar=1"
            >
                Editar planilla
            </a>

            <form
                class="queries-inline-form"
                action="eliminar_planilla.php"
                method="post"
                data-confirm-delete="¿Eliminar la planilla N° <?= $escapar($nroPlanilla) ?>? Esta acción no se puede deshacer."
            >
                <input type="hidden" name="nro_formulario" value="<?= $escapar($nroPlanilla) ?>">

                <button class="registry-button registry-button--danger" type="submit">
                    Eliminar planilla
                </button>
            </form>
        <?php endif; ?>

        <?php if ($estaPagada): ?>
            <a
                class="registry-button registry-button--primary"
                href="editar_recaudacion.php?nro=<?= urlencode($nroPlanilla) ?>"
            >
                Editar recaudación
            </a>
        <?php else: ?>
            <a
                class="registry-button registry-button--primary"
                href="recaudar_planilla.php?nro=<?= urlencode($nroPlanilla) ?>"
            >
                Recaudar
            </a>
        <?php endif; ?>
    </div>
</section>
