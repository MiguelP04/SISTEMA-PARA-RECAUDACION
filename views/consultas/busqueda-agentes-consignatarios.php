<section class="queries-search" aria-labelledby="contribuyente-search-title">
    <div class="queries-search__intro">
        <p class="queries-search__eyebrow">Búsqueda exacta</p>
        <h2 id="contribuyente-search-title">RIF del contribuyente</h2>
        <p class="queries-search__hint">
            Selecciona el tipo de registro e ingresa el RIF completo. La búsqueda no utiliza listados generales.
        </p>
    </div>

    <form
        class="queries-search__form queries-search__form--entity"
        action="consulta_agentes_consignatarios.php"
        method="get"
    >
        <label class="visually-hidden" for="tipo_entidad">Tipo de contribuyente</label>

        <select class="queries-search__select" id="tipo_entidad" name="tipo" required>
            <?php foreach ($tiposEntidad as $clave => $entidad): ?>
                <option value="<?= $escapar($clave) ?>" <?= $tipo === $clave ? 'selected' : '' ?>>
                    <?= $escapar($entidad['etiqueta']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="queries-search__rif">
            <label class="visually-hidden" for="rif_prefijo">Prefijo del RIF</label>

            <select
                class="queries-search__prefix"
                id="rif_prefijo"
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

            <label class="visually-hidden" for="rif_numero">Número del RIF</label>

            <input
                class="queries-search__input queries-search__input--rif"
                id="rif_numero"
                name="rif_numero"
                type="text"
                value="<?= $escapar($rifNumero) ?>"
                maxlength="9"
                inputmode="numeric"
                pattern="[0-9]{7,9}"
                placeholder="Ej. 001230726"
                autocomplete="off"
                data-rif-numero
                autofocus
                required
            >
        </div>

        <button class="registry-button registry-button--primary" type="submit">
            Consultar
        </button>

        <?php if ($buscado): ?>
            <a class="registry-button registry-button--secondary" href="consulta_agentes_consignatarios.php?tipo=<?= urlencode($tipo) ?>">
                Limpiar
            </a>
        <?php endif; ?>
    </form>
</section>
