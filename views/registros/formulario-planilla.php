<?php
$escapar = static fn ($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
?>

<?php if ($registroExitoso !== ''): ?>
    <section class="registry-alert registry-alert--success" role="status">
        <div>
            <strong>Planilla registrada correctamente.</strong>
            <span>Formulario N° <?= $escapar($registroExitoso) ?> guardado con sus ramos de ingreso.</span>
        </div>

        <a class="registry-button registry-button--secondary" href="registro_planilla.php">
            Registrar otra planilla
        </a>
    </section>
<?php else: ?>
    <?php if (!empty($errores)): ?>
        <section class="registry-alert registry-alert--error" role="alert">
            <strong>Revisa los datos antes de guardar:</strong>

            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= $escapar($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <form class="registry-form" action="registro_planilla.php" method="post" novalidate>
        <section class="registry-section" aria-labelledby="planilla-data-title">
            <div class="registry-section__heading">
                <div>
                    <p class="registry-section__eyebrow">Datos principales</p>
                    <h2 id="planilla-data-title">Información de la planilla</h2>
                </div>

                <p class="registry-section__hint">
                    Los campos marcados con <span aria-hidden="true">*</span> son obligatorios.
                </p>
            </div>

            <div class="registry-grid">
                <div class="registry-field">
                    <label for="nro_formulario">N° Formulario <span aria-hidden="true">*</span></label>
                    <input
                        id="nro_formulario"
                        name="nro_formulario"
                        type="text"
                        value="<?= $escapar($formulario['nro_formulario']) ?>"
                        maxlength="10"
                        inputmode="numeric"
                        pattern="[0-9]{1,10}"
                        autocomplete="off"
                        required
                    >
                </div>

                <div class="registry-field">
                    <label for="codigo_aduana">Aduana <span aria-hidden="true">*</span></label>
                    <select id="codigo_aduana" name="codigo_aduana" required>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($catalogos['aduanas'] as $aduana): ?>
                            <option
                                value="<?= $escapar($aduana['codigo_aduana']) ?>"
                                <?= $formulario['codigo_aduana'] === (string) $aduana['codigo_aduana'] ? 'selected' : '' ?>
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
                                <?= $formulario['cod_de_forma'] === $formaVisible ? 'selected' : '' ?>
                            >
                                <?= $escapar($formaVisible) ?>
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
                        value="<?= $escapar($formulario['ano_declaracion']) ?>"
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
                        value="<?= $escapar($formulario['nro_declaracion']) ?>"
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
                        value="<?= $escapar($formulario['fecha_emision']) ?>"
                        required
                    >
                </div>

                <div class="registry-field">
                    <label for="fecha_contabilidad_liq">Fecha de liquidación <span aria-hidden="true">*</span></label>
                    <input
                        id="fecha_contabilidad_liq"
                        name="fecha_contabilidad_liq"
                        type="date"
                        value="<?= $escapar($formulario['fecha_contabilidad_liq']) ?>"
                        required
                    >
                </div>

                <div class="registry-field">
                    <label for="fecha_de_notificacion">Fecha de notificación <span aria-hidden="true">*</span></label>
                    <input
                        id="fecha_de_notificacion"
                        name="fecha_de_notificacion"
                        type="date"
                        value="<?= $escapar($formulario['fecha_de_notificacion']) ?>"
                        required
                    >
                </div>

                <div class="registry-field">
                    <label for="fecha_vencimiento">Fecha de vencimiento <span aria-hidden="true">*</span></label>
                    <input
                        id="fecha_vencimiento"
                        name="fecha_vencimiento"
                        type="date"
                        value="<?= $escapar($formulario['fecha_vencimiento']) ?>"
                        required
                    >
                </div>

                <div class="registry-field">
                    <label for="nro_resolucion">N° Resolución</label>
                    <input
                        id="nro_resolucion"
                        name="nro_resolucion"
                        type="text"
                        value="<?= $escapar($formulario['nro_resolucion']) ?>"
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
                        value="<?= $escapar($formulario['nro_liquidacion']) ?>"
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
                                <?= $formulario['tipo_de_planilla'] === (string) $tipo['tipo_de_liquidacion'] ? 'selected' : '' ?>
                            >
                                <?= $escapar($tipo['tipo_de_liquidacion'] . ' - ' . $tipo['descripcion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </section>

        <section class="registry-section" aria-labelledby="contributors-title">
            <div class="registry-section__heading">
                <div>
                    <p class="registry-section__eyebrow">Contribuyentes</p>
                    <h2 id="contributors-title">Consignatario y agente aduanal</h2>
                </div>

                <p class="registry-section__hint">
                    Busca por RIF o por nombre y selecciona el registro correcto.
                </p>
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

                    <div class="registry-field contributor-search">
                        <label for="consignatario_rif">RIF o nombre <span aria-hidden="true">*</span></label>

                        <input
                            id="consignatario_rif"
                            name="rif_consignatario"
                            type="text"
                            value="<?= $escapar($formulario['rif_consignatario']) ?>"
                            placeholder="Ej. J123456789 o razón social"
                            autocomplete="off"
                            data-contributor-search
                            data-tipo="consignatario"
                            data-resultados="consignatario_resultados"
                            required
                        >

                        <div class="contributor-results" id="consignatario_resultados" aria-live="polite"></div>
                    </div>

                    <p class="contributor-selected" id="consignatario_seleccionado">
                        <?php if ($consignatarioSeleccionado !== null): ?>
                            <strong><?= $escapar($consignatarioSeleccionado['nombre']) ?></strong>
                            <span><?= $escapar($consignatarioSeleccionado['rif']) ?></span>
                        <?php else: ?>
                            <span>Sin consignatario seleccionado.</span>
                        <?php endif; ?>
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

                    <div class="registry-field contributor-search">
                        <label for="agente_rif">RIF o nombre <span aria-hidden="true">*</span></label>

                        <input
                            id="agente_rif"
                            name="rif_agente"
                            type="text"
                            value="<?= $escapar($formulario['rif_agente']) ?>"
                            placeholder="Ej. J123456789 o razón social"
                            autocomplete="off"
                            data-contributor-search
                            data-tipo="agente"
                            data-resultados="agente_resultados"
                            required
                        >

                        <div class="contributor-results" id="agente_resultados" aria-live="polite"></div>
                    </div>

                    <p class="contributor-selected" id="agente_seleccionado">
                        <?php if ($agenteSeleccionado !== null): ?>
                            <strong><?= $escapar($agenteSeleccionado['nombre']) ?></strong>
                            <span><?= $escapar($agenteSeleccionado['rif']) ?></span>
                        <?php else: ?>
                            <span>Sin agente aduanal seleccionado.</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </section>

        <section class="registry-section" aria-labelledby="ramos-title">
            <div class="registry-section__heading">
                <div>
                    <p class="registry-section__eyebrow">Detalle de pago</p>
                    <h2 id="ramos-title">Ramos de ingreso</h2>
                </div>

                <button class="registry-button registry-button--secondary" type="button" id="agregar-ramo">
                    Agregar ramo
                </button>
            </div>

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
                    <strong>Bs <?= number_format($montoTotal, 2, ',', '.') ?></strong>
                </p>
            </div>
        </section>

        <div class="registry-actions">
            <a class="registry-button registry-button--ghost" href="dashboard.php">
                Cancelar
            </a>

            <button class="registry-button registry-button--primary" type="submit">
                Registrar planilla
            </button>
        </div>
    </form>

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
