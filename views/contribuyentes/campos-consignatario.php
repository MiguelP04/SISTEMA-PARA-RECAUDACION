<?php
$rifCompleto = (string) ($formulario['rif_consignatario'] ?? '');
$rifPrefijo = (string) ($formulario['rif_prefijo'] ?? ($rifCompleto !== '' ? substr($rifCompleto, 0, 1) : 'J'));
$rifNumero = (string) ($formulario['rif_numero'] ?? ($rifCompleto !== '' ? substr($rifCompleto, 1) : ''));
$tipoSeleccionado = (string) ($formulario['tipo_contribuyente'] ?? '');
$ciudadSeleccionada = (string) ($formulario['cod_ciudad'] ?? '');
$zonaSeleccionada = (string) ($formulario['zona_postal'] ?? '');
$obligatorio = $rifBloqueado ? '' : ' required';
?>

<div class="registry-field">
    <label for="rif_consignatario">RIF <span aria-hidden="true">*</span></label>

    <?php if ($rifBloqueado): ?>
        <input
            id="rif_consignatario"
            type="text"
            value="<?= $escapar($rifCompleto) ?>"
            readonly
        >
    <?php else: ?>
        <div class="rif-input-group">
            <select
                id="rif_consignatario_prefijo"
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
                id="rif_consignatario"
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
    <label for="nombre_consignatario">Nombre o razón social <span aria-hidden="true">*</span></label>
    <input
        id="nombre_consignatario"
        name="nombre_consignatario"
        type="text"
        value="<?= $escapar($formulario['nombre_consignatario'] ?? '') ?>"
        maxlength="80"
        autocomplete="off"
        required
    >
</div>

<div class="registry-field">
    <label for="tipo_contribuyente">Tipo de contribuyente <span aria-hidden="true">*</span></label>
    <select id="tipo_contribuyente" name="tipo_contribuyente" required>
        <option value="">Seleccionar...</option>
        <?php foreach ($catalogos['tipos'] as $tipo): ?>
            <option
                value="<?= $escapar($tipo['id_tipo_contribuyente']) ?>"
                <?= $tipoSeleccionado === (string) $tipo['id_tipo_contribuyente'] ? 'selected' : '' ?>
            >
                <?= $escapar($tipo['desc_tipo_contribuyente']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="registry-field">
    <label for="telefono">Teléfono<?= $rifBloqueado ? '' : ' <span aria-hidden="true">*</span>' ?></label>
    <input
        id="telefono"
        name="telefono"
        type="text"
        value="<?= $escapar($formulario['telefono'] ?? '') ?>"
        maxlength="12"
        inputmode="tel"
        autocomplete="off"
        <?= $obligatorio ?>
    >
</div>

<div class="registry-field">
    <label for="siglas">Siglas</label>
    <input
        id="siglas"
        name="siglas"
        type="text"
        value="<?= $escapar($formulario['siglas'] ?? '') ?>"
        maxlength="50"
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
        maxlength="50"
        autocomplete="off"
        <?= $obligatorio ?>
    >
</div>

<div class="registry-field registry-field--span-2">
    <label for="direccion">Dirección fiscal<?= $rifBloqueado ? '' : ' <span aria-hidden="true">*</span>' ?></label>
    <textarea
        id="direccion"
        name="direccion"
        maxlength="120"
        rows="3"
        <?= $obligatorio ?>
    ><?= $escapar($formulario['direccion'] ?? '') ?></textarea>
</div>

<div class="registry-field">
    <label for="cod_ciudad">Ciudad</label>
    <select id="cod_ciudad" name="cod_ciudad" data-ciudad>
        <option value="">Seleccionar...</option>
        <?php foreach ($catalogos['ciudades'] as $ciudad): ?>
            <option
                value="<?= $escapar($ciudad['codigo']) ?>"
                data-zona-postal="<?= $escapar($ciudad['zona_postal']) ?>"
                <?= $ciudadSeleccionada === (string) $ciudad['codigo'] ? 'selected' : '' ?>
            >
                <?= $escapar(trim($ciudad['nombre']) . ' - ' . trim($ciudad['abreviatura']) . ' (' . trim($ciudad['estado']) . ')') ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="registry-field">
    <label for="zona_postal">Zona postal</label>
    <input
        id="zona_postal"
        name="zona_postal"
        type="number"
        value="<?= $escapar($zonaSeleccionada) ?>"
        min="0"
        max="9999"
        step="1"
        inputmode="numeric"
        data-zona-postal
    >
</div>

<div class="registry-field">
    <label for="cir">CIR</label>
    <input
        id="cir"
        name="cir"
        type="text"
        value="<?= $escapar($formulario['cir'] ?? '') ?>"
        maxlength="1"
        autocomplete="off"
        data-cir
    >
</div>

<div class="registry-field registry-field--span-2">
    <label for="referencia">Referencia</label>
    <input
        id="referencia"
        name="referencia"
        type="text"
        value="<?= $escapar($formulario['referencia'] ?? '') ?>"
        maxlength="70"
        autocomplete="off"
    >
</div>
