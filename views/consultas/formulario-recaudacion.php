<?php
$tituloFormulario = $modo === 'editar' ? 'Datos de la recaudación' : 'Datos del pago';
$textoBoton = $modo === 'editar' ? 'Actualizar recaudación' : 'Registrar recaudación';
?>

<form class="registry-form" method="post" novalidate>
    <input type="hidden" name="nro_formulario" value="<?= $escapar($formulario['nro_formulario']) ?>">

    <section class="registry-section" aria-labelledby="collection-form-title">
        <div class="registry-section__heading">
            <div>
                <p class="registry-section__eyebrow">Recaudación</p>
                <h2 id="collection-form-title"><?= $escapar($tituloFormulario) ?></h2>
            </div>

            <p class="registry-section__hint">
                Planilla N° <?= $escapar($formulario['nro_formulario']) ?>
            </p>
        </div>

        <div class="registry-grid">
            <div class="registry-field">
                <label for="cod_banco">Banco <span aria-hidden="true">*</span></label>
                <select id="cod_banco" name="cod_banco" required>
                    <option value="">Seleccionar banco...</option>
                    <?php foreach ($bancos as $banco): ?>
                        <option
                            value="<?= $escapar($banco['cod_banco']) ?>"
                            <?= (string) $formulario['cod_banco'] === (string) $banco['cod_banco'] ? 'selected' : '' ?>
                        >
                            <?= $escapar($banco['cod_banco'] . ' - ' . trim($banco['nombre_banco'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="registry-field">
                <label for="cod_agencia">Agencia <span aria-hidden="true">*</span></label>
                <select id="cod_agencia" name="cod_agencia" required>
                    <?php if (empty($agencias)): ?>
                        <option value="">
                            <?= $formulario['cod_banco'] !== '' ? 'Sin agencias registradas' : 'Selecciona un banco primero' ?>
                        </option>
                    <?php else: ?>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($agencias as $agencia): ?>
                            <option
                                value="<?= $escapar($agencia['cod_agencia']) ?>"
                                <?= (string) $formulario['cod_agencia'] === (string) $agencia['cod_agencia'] ? 'selected' : '' ?>
                            >
                                <?= $escapar($agencia['cod_agencia'] . ' - ' . trim($agencia['nombre_agencia'])) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="registry-field">
                <label for="fecha_cancelacion">Fecha de cancelación <span aria-hidden="true">*</span></label>
                <input
                    id="fecha_cancelacion"
                    name="fecha_cancelacion"
                    type="date"
                    value="<?= $escapar($formulario['fecha_cancelacion']) ?>"
                    required
                >
            </div>

            <div class="registry-field">
                <label for="fecha_contabilidad_rec">Fecha de contabilidad</label>
                <input
                    id="fecha_contabilidad_rec"
                    name="fecha_contabilidad_rec"
                    type="date"
                    value="<?= $escapar($formulario['fecha_contabilidad_rec']) ?>"
                >
            </div>

            <div class="registry-field">
                <label for="fecha_de_entreguese">Fecha de entrega</label>
                <input
                    id="fecha_de_entreguese"
                    name="fecha_de_entreguese"
                    type="date"
                    value="<?= $escapar($formulario['fecha_de_entreguese']) ?>"
                >
            </div>
        </div>
    </section>

    <div class="registry-actions">
        <a class="registry-button registry-button--ghost" href="consulta_planilla.php?nro=<?= urlencode($formulario['nro_formulario']) ?>">
            Cancelar
        </a>

        <button class="registry-button registry-button--primary" type="submit">
            <?= $escapar($textoBoton) ?>
        </button>
    </div>
</form>
