<?php
$rifCompleto = (string) ($formulario['rif_agentes'] ?? '');
$rifPrefijo = (string) ($formulario['rif_prefijo'] ?? ($rifCompleto !== '' ? substr($rifCompleto, 0, 1) : 'J'));
$rifNumero = (string) ($formulario['rif_numero'] ?? ($rifCompleto !== '' ? substr($rifCompleto, 1) : ''));
$obligatorio = $rifBloqueado ? '' : ' required';
?>

<div class="registry-field">
    <label for="rif_agentes">RIF <span aria-hidden="true">*</span></label>

    <?php if ($rifBloqueado): ?>
        <input
            id="rif_agentes"
            type="text"
            value="<?= $escapar($rifCompleto) ?>"
            readonly
        >
    <?php else: ?>
        <div class="rif-input-group">
            <select
                id="rif_agentes_prefijo"
                name="rif_prefijo"
                aria-label="Prefijo del RIF"
                required
            >
                <?php foreach (['V', 'E', 'J', 'G', 'P', 'C'] as $prefijo): ?>
                    <option value="<?= $prefijo ?>" <?= $rifPrefijo === $prefijo ? 'selected' : '' ?>>
                        <?= $prefijo ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input
                id="rif_agentes"
                name="rif_numero"
                type="text"
                value="<?= $escapar($rifNumero) ?>"
                maxlength="9"
                inputmode="numeric"
                pattern="[0-9]{7,9}"
                placeholder="123456789"
                autocomplete="off"
                data-rif-numero
                required
            >
        </div>
    <?php endif; ?>
</div>

<div class="registry-field">
    <label for="nombre_agente">Nombre o razón social <span aria-hidden="true">*</span></label>
    <input
        id="nombre_agente"
        name="nombre_agente"
        type="text"
        value="<?= $escapar($formulario['nombre_agente'] ?? '') ?>"
        maxlength="50"
        autocomplete="off"
        required
    >
</div>

<div class="registry-field">
    <label for="numero_registro_agente_aduanal">N° de registro</label>
    <input
        id="numero_registro_agente_aduanal"
        name="numero_registro_agente_aduanal"
        type="text"
        value="<?= $escapar($formulario['numero_registro_agente_aduanal'] ?? '') ?>"
        maxlength="4"
        autocomplete="off"
    >
</div>

<div class="registry-field">
    <label for="siglas_agente">Siglas</label>
    <input
        id="siglas_agente"
        name="siglas_agente"
        type="text"
        value="<?= $escapar($formulario['siglas_agente'] ?? '') ?>"
        maxlength="40"
        autocomplete="off"
    >
</div>

<div class="registry-field">
    <label for="representante">Representante<?= $rifBloqueado ? '' : ' <span aria-hidden="true">*</span>' ?></label>
    <input
        id="representante"
        name="representante"
        type="text"
        value="<?= $escapar($formulario['representante'] ?? '') ?>"
        maxlength="40"
        autocomplete="off"
        <?= $obligatorio ?>
    >
</div>

<div class="registry-field">
    <label for="telefono">Teléfono<?= $rifBloqueado ? '' : ' <span aria-hidden="true">*</span>' ?></label>
    <input
        id="telefono"
        name="telefono"
        type="text"
        value="<?= $escapar($formulario['telefono'] ?? '') ?>"
        maxlength="10"
        inputmode="tel"
        autocomplete="off"
        <?= $obligatorio ?>
    >
</div>

<div class="registry-field registry-field--span-2">
    <label for="domicilio_fiscal">Domicilio fiscal<?= $rifBloqueado ? '' : ' <span aria-hidden="true">*</span>' ?></label>
    <textarea
        id="domicilio_fiscal"
        name="domicilio_fiscal"
        maxlength="100"
        rows="3"
        <?= $obligatorio ?>
    ><?= $escapar($formulario['domicilio_fiscal'] ?? '') ?></textarea>
</div>
