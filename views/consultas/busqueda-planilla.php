<section class="queries-search" aria-labelledby="planilla-search-title">
    <div class="queries-search__intro">
        <p class="queries-search__eyebrow">Búsqueda exacta</p>
        <h2 id="planilla-search-title">N° de formulario</h2>
        <p class="queries-search__hint">
            Ingresa el número completo de la planilla. La búsqueda no utiliza listados generales.
        </p>
    </div>

    <form class="queries-search__form" action="consulta_planilla.php" method="get">
        <label class="visually-hidden" for="nro_planilla">N° de formulario</label>

        <input
            class="queries-search__input"
            id="nro_planilla"
            name="nro"
            type="text"
            value="<?= $escapar($nro) ?>"
            maxlength="20"
            inputmode="numeric"
            pattern="[0-9]{1,20}"
            placeholder="Ej. 2600388968"
            autocomplete="off"
            autofocus
            required
        >

        <button class="registry-button registry-button--primary" type="submit">
            Consultar
        </button>

        <?php if ($nro !== ''): ?>
            <a class="registry-button registry-button--secondary" href="consulta_planilla.php">
                Limpiar
            </a>
        <?php endif; ?>
    </form>
</section>
