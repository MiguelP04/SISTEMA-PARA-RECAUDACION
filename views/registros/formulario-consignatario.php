<?php $rifBloqueado = false; ?>

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

<form class="registry-form" action="registro_consignatario.php" method="post" novalidate>
    <section class="registry-section" aria-labelledby="consignatario-data-title">
        <div class="registry-section__heading">
            <div>
                <p class="registry-section__eyebrow">Datos fiscales</p>
                <h2 id="consignatario-data-title">Información del consignatario</h2>
            </div>

            <p class="registry-section__hint">
                Los campos marcados con <span aria-hidden="true">*</span> son obligatorios.
            </p>
        </div>

        <div class="registry-grid">
            <?php require __DIR__ . '/../contribuyentes/campos-consignatario.php'; ?>
        </div>
    </section>

    <div class="registry-actions">
        <a class="registry-button registry-button--ghost" href="dashboard.php">
            Cancelar
        </a>

        <button class="registry-button registry-button--primary" type="submit">
            Registrar consignatario
        </button>
    </div>
</form>
