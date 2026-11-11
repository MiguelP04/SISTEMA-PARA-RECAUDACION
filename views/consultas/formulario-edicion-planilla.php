<?php
$nroFormulario = (string) ($formulario['nro_formulario'] ?? '');
$rifConsignatario = trim((string) ($planilla['rif_consignatario'] ?? ''));
$nombreConsignatario = trim((string) ($planilla['nombre_consignatario'] ?? ''));
$rifAgente = trim((string) ($planilla['rif_agente'] ?? ''));
$nombreAgente = trim((string) ($planilla['nombre_agente'] ?? ''));
?>

<?php if (!$editarRamos): ?>
    <section class="queries-alert queries-alert--warning" role="status">
        <strong>Este N° de formulario no permite editar los ramos.</strong>
        <span>
            El número supera el límite de 10 caracteres de la tabla de impuestos.
            Puedes modificar los datos de cabecera, pero no los ramos.
        </span>
    </section>
<?php endif; ?>

<form class="registry-form queries-edit-form" action="actualizar_planilla.php" method="post" novalidate>
    <input type="hidden" name="nro_formulario" value="<?= $escapar($nroFormulario) ?>">

    <section class="registry-section" aria-labelledby="edit-planilla-data-title">
        <div class="registry-section__heading">
            <div>
                <p class="registry-section__eyebrow">Datos principales</p>
                <h2 id="edit-planilla-data-title">Información de la planilla</h2>
            </div>

            <p class="registry-section__hint">
                Los campos marcados con <span aria-hidden="true">*</span> son obligatorios.
            </p>
        </div>

        <div class="registry-grid">
            <div class="registry-field">
                <label for="nro_formulario">N° Formulario</label>
                <input
                    id="nro_formulario"
                    type="text"
                    value="<?= $escapar($nroFormulario) ?>"
                    readonly
                >
            </div>

            <div class="registry-field">
                <label for="codigo_aduana">Aduana <span aria-hidden="true">*</span></label>
                <select id="codigo_aduana" name="codigo_aduana" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($catalogos['aduanas'] as $aduana): ?>
                        <option
                            value="<?= $escapar($aduana['codigo_aduana']) ?>"
                            <?= (string) ($formulario['codigo_aduana'] ?? '') === (string) $aduana['codigo_aduana'] ? 'selected' : '' ?>
                        >
                            <?= $escapar($aduana['codigo_aduana'] . ' - ' . $aduana['nombre_de_la_aduana']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="registry-field">
                <label for="cod_de_forma">Forma <span aria-hidden="true">*</span></label>
                <select id="cod_de_forma" name="cod_de_forma" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($catalogos['formas'] as $forma): ?>
                        <?php $formaVisible = (string) $forma['formas']; ?>
                        <option
                            value="<?= $escapar($formaVisible) ?>"
                            <?= (string) ($formulario['cod_de_forma'] ?? '') === $formaVisible ? 'selected' : '' ?>
                        >
                            <?= $escapar($formaVisible . ' - ' . $forma['denominacion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="registry-field">
                <label for="ano_declaracion">Año <span aria-hidden="true">*</span></label>
                <input
                    id="ano_declaracion"
                    name="ano_declaracion"
                    type="number"
                    value="<?= $escapar($formulario['ano_declaracion'] ?? '') ?>"
                    min="1900"
                    max="2100"
                    step="1"
                    required
                >
            </div>

            <div class="registry-field">
                <label for="nro_declaracion">N° Declaración <span aria-hidden="true">*</span></label>
                <input
                    id="nro_declaracion"
                    name="nro_declaracion"
                    type="text"
                    value="<?= $escapar($formulario['nro_declaracion'] ?? '') ?>"
                    maxlength="22"
                    autocomplete="off"
                    required
                >
            </div>

            <div class="registry-field">
                <label for="fecha_emision">Fecha de emisión <span aria-hidden="true">*</span></label>
                <input
                    id="fecha_emision"
                    name="fecha_emision"
                    type="date"
                    value="<?= $escapar($formulario['fecha_emision'] ?? '') ?>"
                    required
                >
            </div>

            <div class="registry-field">
                <label for="fecha_contabilidad_liq">Fecha de liquidación <span aria-hidden="true">*</span></label>
                <input
                    id="fecha_contabilidad_liq"
                    name="fecha_contabilidad_liq"
                    type="date"
                    value="<?= $escapar($formulario['fecha_contabilidad_liq'] ?? '') ?>"
                    required
                >
            </div>

            <div class="registry-field">
                <label for="fecha_de_notificacion">Fecha de notificación <span aria-hidden="true">*</span></label>
                <input
                    id="fecha_de_notificacion"
                    name="fecha_de_notificacion"
                    type="date"
                    value="<?= $escapar($formulario['fecha_de_notificacion'] ?? '') ?>"
                    required
                >
            </div>

            <div class="registry-field">
                <label for="fecha_vencimiento">Fecha de vencimiento <span aria-hidden="true">*</span></label>
                <input
                    id="fecha_vencimiento"
                    name="fecha_vencimiento"
                    type="date"
                    value="<?= $escapar($formulario['fecha_vencimiento'] ?? '') ?>"
                    required
                >
            </div>

            <div class="registry-field">
                <label for="nro_resolucion">N° Resolución</label>
                <input
                    id="nro_resolucion"
                    name="nro_resolucion"
                    type="text"
                    value="<?= $escapar($formulario['nro_resolucion'] ?? '') ?>"
                    maxlength="40"
                    autocomplete="off"
                >
            </div>

            <div class="registry-field">
                <label for="nro_liquidacion">N° Liquidación <span aria-hidden="true">*</span></label>
                <input
                    id="nro_liquidacion"
                    name="nro_liquidacion"
                    type="text"
                    value="<?= $escapar($formulario['nro_liquidacion'] ?? '') ?>"
                    maxlength="5"
                    autocomplete="off"
                    required
                >
            </div>

            <div class="registry-field">
                <label for="tipo_de_planilla">Tipo de planilla <span aria-hidden="true">*</span></label>
                <select id="tipo_de_planilla" name="tipo_de_planilla" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($catalogos['tipos_liquidacion'] as $tipo): ?>
                        <option
                            value="<?= $escapar($tipo['tipo_de_liquidacion']) ?>"
                            <?= (string) ($formulario['tipo_de_planilla'] ?? '') === (string) $tipo['tipo_de_liquidacion'] ? 'selected' : '' ?>
                        >
                            <?= $escapar($tipo['tipo_de_liquidacion'] . ' - ' . $tipo['descripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </section>

    <section class="registry-section" aria-labelledby="edit-contributors-title">
        <div class="registry-section__heading">
            <div>
                <p class="registry-section__eyebrow">Contribuyentes</p>
                <h2 id="edit-contributors-title">Consignatario y agente aduanal</h2>
            </div>

            <p class="registry-section__hint">Los RIF no se modifican desde esta pantalla.</p>
        </div>

        <div class="contributor-grid">
            <div class="contributor-panel">
                <div class="contributor-panel__heading">
                    <div>
                        <p class="contributor-panel__eyebrow">Consignatario</p>
                        <h3>Datos del importador</h3>
                    </div>

                    <span class="contributor-panel__badge">RIF</span>
                </div>

                <div class="registry-field">
                    <label for="rif_consignatario">RIF del consignatario</label>
                    <input
                        id="rif_consignatario"
                        type="text"
                        value="<?= $escapar($rifConsignatario !== '' ? $rifConsignatario : 'Sin RIF') ?>"
                        readonly
                    >
                </div>

                <p class="contributor-selected">
                    <strong><?= $escapar($nombreConsignatario !== '' ? $nombreConsignatario : 'No encontrado') ?></strong>
                    <span><?= $escapar($rifConsignatario !== '' ? $rifConsignatario : 'Sin RIF registrado') ?></span>
                </p>
            </div>

            <div class="contributor-panel">
                <div class="contributor-panel__heading">
                    <div>
                        <p class="contributor-panel__eyebrow">Agente aduanal</p>
                        <h3>Datos del agente</h3>
                    </div>

                    <span class="contributor-panel__badge">RIF</span>
                </div>

                <div class="registry-field">
                    <label for="rif_agente">RIF del agente</label>
                    <input
                        id="rif_agente"
                        type="text"
                        value="<?= $escapar($rifAgente !== '' ? $rifAgente : 'Sin RIF') ?>"
                        readonly
                    >
                </div>

                <p class="contributor-selected">
                    <strong><?= $escapar($nombreAgente !== '' ? $nombreAgente : 'No encontrado') ?></strong>
                    <span><?= $escapar($rifAgente !== '' ? $rifAgente : 'Sin RIF registrado') ?></span>
                </p>
            </div>
        </div>
    </section>

    <section class="registry-section" aria-labelledby="edit-ramos-title">
        <div class="registry-section__heading">
            <div>
                <p class="registry-section__eyebrow">Detalle de pago</p>
                <h2 id="edit-ramos-title">Ramos de ingreso</h2>
            </div>

            <?php if ($editarRamos): ?>
                <button class="registry-button registry-button--secondary" type="button" id="agregar-ramo">
                    Agregar ramo
                </button>
            <?php endif; ?>
        </div>

        <?php if ($editarRamos): ?>
            <?php if (empty($ramosFormulario)): ?>
                <p class="queries-empty-state" id="ramos-vacio">
                    Esta planilla no tiene ramos registrados. Puedes guardar solo los datos de cabecera o agregar un ramo.
                </p>
            <?php endif; ?>

            <div class="ramos-table-wrapper">
                <table class="ramos-table">
                    <thead>
                        <tr>
                            <th scope="col">Ramo de ingreso</th>
                            <th scope="col">Forma de impuesto</th>
                            <th scope="col">Monto</th>
                            <th scope="col"><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody id="ramos-body">
                        <?php foreach ($ramosFormulario as $indice => $ramo): ?>
                            <?php $indice = (int) $indice; ?>
                            <tr class="ramo-row">
                                <td>
                                    <select name="ramos[<?= $escapar($indice) ?>][cod_concepto]" aria-label="Ramo de ingreso" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($catalogos['tipos_impuesto'] as $concepto): ?>
                                            <option
                                                value="<?= $escapar($concepto['cod_concepto']) ?>"
                                                <?= (string) ($ramo['cod_concepto'] ?? '') === (string) $concepto['cod_concepto'] ? 'selected' : '' ?>
                                            >
                                                <?= $escapar($concepto['cod_concepto'] . ' - ' . $concepto['descripcion_concepto']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="ramos[<?= $escapar($indice) ?>][cod_forma]" aria-label="Forma de impuesto" required>
                                        <option value="">Seleccionar...</option>
                                        <?php foreach ($catalogos['formas_impuesto'] as $formaImpuesto): ?>
                                            <option
                                                value="<?= $escapar($formaImpuesto['cod_forma']) ?>"
                                                <?= (string) ($ramo['cod_forma'] ?? '') === (string) $formaImpuesto['cod_forma'] ? 'selected' : '' ?>
                                            >
                                                <?= $escapar($formaImpuesto['cod_forma'] . ' - ' . $formaImpuesto['descripcion']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input
                                        name="ramos[<?= $escapar($indice) ?>][monto]"
                                        type="number"
                                        value="<?= $escapar($ramo['monto'] ?? '') ?>"
                                        min="0"
                                        step="0.01"
                                        inputmode="decimal"
                                        aria-label="Monto del ramo"
                                        data-ramo-monto
                                        required
                                    >
                                </td>
                                <td class="ramos-table__action">
                                    <button class="ramo-remove" type="button" aria-label="Eliminar ramo">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="ramos-footer">
                <p class="ramos-footer__note">
                    El total se calcula automáticamente a partir de los ramos agregados.
                </p>

                <p class="ramos-footer__total">
                    <span>Total</span>
                    <strong>Bs <?= number_format($montoFormulario, 2, ',', '.') ?></strong>
                </p>
            </div>
        <?php else: ?>
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
        <?php endif; ?>
    </section>

    <div class="registry-actions">
        <a class="registry-button registry-button--ghost" href="consulta_planilla.php?nro=<?= urlencode($nroFormulario) ?>">
            Cancelar
        </a>

        <button class="registry-button registry-button--primary" type="submit">
            Guardar cambios
        </button>
    </div>
</form>

<?php if ($editarRamos): ?>
    <template id="ramo-template">
        <tr class="ramo-row">
            <td>
                <select name="ramos[__INDICE__][cod_concepto]" aria-label="Ramo de ingreso" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($catalogos['tipos_impuesto'] as $concepto): ?>
                        <option value="<?= $escapar($concepto['cod_concepto']) ?>">
                            <?= $escapar($concepto['cod_concepto'] . ' - ' . $concepto['descripcion_concepto']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <select name="ramos[__INDICE__][cod_forma]" aria-label="Forma de impuesto" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($catalogos['formas_impuesto'] as $formaImpuesto): ?>
                        <option
                            value="<?= $escapar($formaImpuesto['cod_forma']) ?>"
                            <?= $formaImpuesto['cod_forma'] === 'PG' ? 'selected' : '' ?>
                        >
                            <?= $escapar($formaImpuesto['cod_forma'] . ' - ' . $formaImpuesto['descripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <input
                    name="ramos[__INDICE__][monto]"
                    type="number"
                    value=""
                    min="0"
                    step="0.01"
                    inputmode="decimal"
                    aria-label="Monto del ramo"
                    data-ramo-monto
                    required
                >
            </td>
            <td class="ramos-table__action">
                <button class="ramo-remove" type="button" aria-label="Eliminar ramo">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </td>
        </tr>
    </template>
<?php endif; ?>
