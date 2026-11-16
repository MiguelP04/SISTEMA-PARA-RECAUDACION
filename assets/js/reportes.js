(() => {
    const selectorModulo = '#reportes-modulo';
    const reducirMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let modulo = document.querySelector(selectorModulo);

    const inicializarFechas = (raiz = document) => {
        raiz.querySelectorAll('[data-date-display]').forEach((input) => {
            const display = document.getElementById(input.dataset.dateDisplay);

            if (!display) {
                return;
            }

            const actualizarFecha = () => {
                const partes = input.value.split('-');

                display.textContent = partes.length === 3
                    ? `${partes[2]}/${partes[1]}/${partes[0]}`
                    : "";
            };

            input.addEventListener('input', actualizarFecha);
            input.addEventListener('change', actualizarFecha);
            actualizarFecha();
        });
    };

    if (!modulo) {
        inicializarFechas();
        return;
    }

    inicializarFechas(modulo);

    let controlador = null;
    let solicitudActual = 0;

    const establecerCarga = (activo) => {
        if (!modulo) {
            return;
        }

        modulo.classList.toggle('is-loading', activo);
        modulo.setAttribute('aria-busy', activo ? 'true' : 'false');
    };

    const obtenerUrl = (url) => new URL(url, window.location.href);

    const reemplazarModulo = (html) => {
        const plantilla = document.createElement('template');
        plantilla.innerHTML = html.trim();

        const nuevoModulo = plantilla.content.firstElementChild;

        if (!nuevoModulo || nuevoModulo.id !== 'reportes-modulo') {
            throw new Error('Respuesta de reportes no válida.');
        }

        modulo.replaceWith(nuevoModulo);
        modulo = nuevoModulo;
        inicializarFechas(modulo);
    };

    const enfocar = (destino) => {
        const titulo = destino === 'resumen'
            ? document.getElementById('report-summary-title')
            : document.getElementById('report-filters-title');
        const objetivo = titulo
            || document.getElementById('report-filters-title')
            || modulo;

        if (!objetivo) {
            return;
        }

        objetivo.setAttribute('tabindex', '-1');
        objetivo.focus({ preventScroll: true });
        objetivo.scrollIntoView({
            behavior: reducirMovimiento ? 'auto' : 'smooth',
            block: 'start',
        });
    };

    const cargarModulo = async (url, opciones = {}) => {
        const { push = true, focus = null } = opciones;
        const idSolicitud = ++solicitudActual;

        controlador?.abort();
        controlador = new AbortController();
        establecerCarga(true);

        try {
            const respuesta = await fetch(url.toString(), {
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                signal: controlador.signal,
            });

            if (respuesta.status === 401) {
                window.location.assign('index.php');
                return;
            }

            if (respuesta.redirected && respuesta.url.includes('index.php')) {
                window.location.assign('index.php');
                return;
            }

            if (!respuesta.ok) {
                throw new Error('No se pudo cargar el reporte.');
            }

            const html = await respuesta.text();

            if (idSolicitud !== solicitudActual) {
                return;
            }

            reemplazarModulo(html);

            if (push) {
                window.history.pushState({}, '', url.toString());
            }

            if (focus) {
                enfocar(focus);
            }
        } catch (error) {
            if (error.name === 'AbortError' || idSolicitud !== solicitudActual) {
                return;
            }

            window.location.assign(url.toString());
        } finally {
            if (idSolicitud === solicitudActual) {
                establecerCarga(false);
            }
        }
    };

    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0) {
            return;
        }

        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const enlace = event.target.closest('a.report-card, a.report-filters__reset');

        if (!enlace || !modulo.contains(enlace)) {
            return;
        }

        const url = obtenerUrl(enlace.href);

        if (url.origin !== window.location.origin) {
            return;
        }

        event.preventDefault();
        cargarModulo(url, { focus: 'filtros' });
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('.report-filters__form');

        if (!form || !modulo.contains(form)) {
            return;
        }

        event.preventDefault();

        const datos = new FormData(form);
        datos.set('consultar', '1');

        const url = obtenerUrl(form.action || window.location.href);
        url.search = new URLSearchParams(datos).toString();

        cargarModulo(url, { focus: 'resumen' });
    });

    window.addEventListener('popstate', () => {
        if (!window.location.pathname.endsWith('/reportes.php')) {
            return;
        }

        cargarModulo(obtenerUrl(window.location.href), { push: false });
    });
})();
