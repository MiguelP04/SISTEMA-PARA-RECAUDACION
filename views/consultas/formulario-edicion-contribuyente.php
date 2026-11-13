<?php
$rifBloqueado = true;
$rifFormulario = $tipo === 'agente'
    ? (string) $formulario['rif_agentes']
    : (string) $formulario['rif_consignatario'];
?>

<form
    class="registry-form queries-edit-form"
    action="actualizar_contribuyente.php"
    method="post"
    novalidate
>
    <input type="hidden" name="tipo" value="<?= $escapar($tipo) ?>">
    <input type="hidden" name="rif" value="<?= $escapar($rifFormulario) ?>">

    <section class="registry-section" aria-labelledby="contribuyente-edit-title">
        <div class="registry-section__heading">
            <div>
                <p class="registry-section__eyebrow">Edición administrativa</p>
                <h2 id="contribuyente-edit-title">Datos del <?= $escapar(strtolower($etiquetaTipo)) ?></h2>
            </div>

            <p class="registry-section__hint">
                El RIF no se puede modificar. Los campos marcados con
                <span aria-hidden="true">*</span> son obligatorios.
            </p>
        </div>

        <div class="registry-grid">
            <?php if ($tipo === 'agente'): ?>
                <?php require __DIR__ . '/../contribuyentes/campos-agente.php'; ?>
            <?php else: ?>
                <?php require __DIR__ . '/../contribuyentes/campos-consignatario.php'; ?>
            <?php endif; ?>
        </div>
    </section>

    <div class="registry-actions">
        <a
            class="registry-button registry-button--ghost"
            href="consulta_agentes_consignatarios.php?tipo=<?= urlencode($tipo) ?>&rif=<?= urlencode($rifFormulario) ?>"
        >
            Cancelar
        </a>

        <button class="registry-button registry-button--primary" type="submit">
            Guardar cambios
        </button>
    </div>
</form>
