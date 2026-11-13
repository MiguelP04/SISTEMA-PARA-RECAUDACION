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

<form class="registry-form" action="registro_agente.php" method="post" novalidate>
    <section class="registry-section" aria-labelledby="agente-data-title">
        <div class="registry-section__heading">
            <div>
                <p class="registry-section__eyebrow">Datos fiscales</p>
                <h2 id="agente-data-title">Información del agente aduanal</h2>
            </div>

            <p class="registry-section__hint">
                Los campos marcados con <span aria-hidden="true">*</span> son obligatorios.
            </p>
        </div>

        <div class="registry-grid">
            <?php require __DIR__ . '/../contribuyentes/campos-agente.php'; ?>
        </div>
    </section>

    <div class="registry-actions">
        <a class="registry-button registry-button--ghost" href="dashboard.php">
            Cancelar
        </a>

        <button class="registry-button registry-button--primary" type="submit">
            Registrar agente aduanal
        </button>
    </div>
</form>
