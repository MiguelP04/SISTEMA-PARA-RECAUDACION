(() => {
    const form = document.querySelector('.registry-form');

    if (!form) {
        return;
    }

    const fechaEmision = document.getElementById('fecha_emision');
    const fechaLiquidacion = document.getElementById('fecha_contabilidad_liq');
    const fechaNotificacion = document.getElementById('fecha_de_notificacion');
    const fechaVencimiento = document.getElementById('fecha_vencimiento');
    const formaSelect = document.getElementById('cod_de_forma');

    const sumarDias = (fecha, dias) => {
        const partes = fecha.split('-').map(Number);
        const fechaObjeto = new Date(partes[0], partes[1] - 1, partes[2]);
        fechaObjeto.setDate(fechaObjeto.getDate() + dias);

        const ano = fechaObjeto.getFullYear();
        const mes = String(fechaObjeto.getMonth() + 1).padStart(2, '0');
        const dia = String(fechaObjeto.getDate()).padStart(2, '0');

        return `${ano}-${mes}-${dia}`;
    };

    const calcularVencimiento = () => {
        if (!fechaEmision || !fechaVencimiento || !formaSelect) {
            return;
        }

        if (!fechaEmision.value) {
            fechaVencimiento.value = '';
            return;
        }

        const dias = formaSelect.value === '81' ? 5 : 0;
        fechaVencimiento.value = sumarDias(fechaEmision.value, dias);
    };

    if (fechaEmision && fechaLiquidacion && fechaNotificacion) {
        fechaEmision.addEventListener('change', () => {
            fechaLiquidacion.value = fechaEmision.value;
            fechaNotificacion.value = fechaEmision.value;
            calcularVencimiento();
        });
    }

    if (formaSelect) {
        formaSelect.addEventListener('change', calcularVencimiento);
    }

    const tbody = document.getElementById('ramos-body');
    const plantilla = document.getElementById('ramo-template');
    const agregarRamo = document.getElementById('agregar-ramo');
    const totalDisplay = document.querySelector('.ramos-footer__total strong');
    let siguienteIndice = 0;

    const actualizarIndice = () => {
        if (!tbody) {
            return;
        }

        tbody.querySelectorAll('input[name*="[monto]"]').forEach((input) => {
            const coincidencia = input.name.match(/ramos\[(\d+)\]/);

            if (coincidencia) {
                siguienteIndice = Math.max(siguienteIndice, Number(coincidencia[1]) + 1);
            }
        });
    };

    const actualizarBotonEliminar = () => {
        if (!tbody) {
            return;
        }

        const filas = tbody.querySelectorAll('.ramo-row');
        filas.forEach((fila) => {
            const boton = fila.querySelector('.ramo-remove');

            if (boton) {
                boton.disabled = filas.length === 1;
            }
        });
    };

    const formatearMonto = (monto) => new Intl.NumberFormat('es-VE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(monto);

    const actualizarTotal = () => {
        if (!tbody || !totalDisplay) {
            return;
        }

        let total = 0;

        tbody.querySelectorAll('[data-ramo-monto]').forEach((input) => {
            const valor = Number.parseFloat(String(input.value).replace(',', '.'));

            if (Number.isFinite(valor)) {
                total += valor;
            }
        });

        totalDisplay.textContent = `Bs ${formatearMonto(total)}`;
    };

    const agregarFilaRamo = () => {
        if (!tbody || !plantilla) {
            return;
        }

        const html = plantilla.innerHTML.replace(/__INDICE__/g, String(siguienteIndice));
        tbody.insertAdjacentHTML('beforeend', html);
        siguienteIndice += 1;
        actualizarBotonEliminar();

        const fila = tbody.lastElementChild;
        const primerCampo = fila ? fila.querySelector('select') : null;

        if (primerCampo) {
            primerCampo.focus();
        }
    };

    actualizarIndice();
    actualizarBotonEliminar();
    actualizarTotal();

    if (agregarRamo) {
        agregarRamo.addEventListener('click', agregarFilaRamo);
    }

    if (tbody) {
        tbody.addEventListener('click', (event) => {
            const boton = event.target.closest('.ramo-remove');

            if (!boton) {
                return;
            }

            const filas = tbody.querySelectorAll('.ramo-row');

            if (filas.length > 1) {
                const fila = boton.closest('.ramo-row');

                if (fila) {
                    fila.remove();
                    actualizarBotonEliminar();
                    actualizarTotal();
                }
            }
        });

        tbody.addEventListener('input', (event) => {
            if (event.target.matches('[data-ramo-monto]')) {
                actualizarTotal();
            }
        });
    }

    const cerrarResultados = (contenedor) => {
        if (contenedor) {
            contenedor.classList.remove('is-open');
            contenedor.innerHTML = '';
        }
    };

    const mostrarMensajeResultados = (contenedor, mensaje) => {
        contenedor.innerHTML = '';
        const parrafo = document.createElement('p');
        parrafo.className = 'contributor-result--empty';
        parrafo.textContent = mensaje;
        contenedor.appendChild(parrafo);
        contenedor.classList.add('is-open');
    };

    const actualizarSeleccionado = (input, contribuyente) => {
        if (!input) {
            return;
        }

        const panel = input.closest('.contributor-panel');
        const detalle = panel ? panel.querySelector('.contributor-selected') : null;

        if (!detalle) {
            return;
        }

        detalle.innerHTML = '';

        const nombre = document.createElement('strong');
        nombre.textContent = contribuyente.nombre;

        const rif = document.createElement('span');
        rif.textContent = contribuyente.rif;

        detalle.appendChild(nombre);
        detalle.appendChild(rif);
    };

    const limpiarSeleccionado = (input) => {
        if (!input) {
            return;
        }

        const panel = input.closest('.contributor-panel');
        const detalle = panel ? panel.querySelector('.contributor-selected') : null;

        if (!detalle) {
            return;
        }

        detalle.innerHTML = '';

        const texto = document.createElement('span');
        texto.textContent = 'Selecciona una coincidencia de la lista.';
        detalle.appendChild(texto);
    };

    const buscarContribuyente = async (input) => {
        const contenedor = document.getElementById(input.dataset.resultados);
        const tipo = input.dataset.tipo;
        const termino = input.value.trim();

        if (!contenedor || !tipo) {
            return;
        }

        if (termino.length < 2) {
            cerrarResultados(contenedor);
            return;
        }

        if (input._busquedaControlador) {
            input._busquedaControlador.abort();
        }

        input._busquedaControlador = new AbortController();
        mostrarMensajeResultados(contenedor, 'Buscando coincidencias...');

        try {
            const parametros = new URLSearchParams({
                tipo,
                q: termino,
            });

            const respuesta = await fetch(`buscar_rif.php?${parametros.toString()}`, {
                headers: {
                    Accept: 'application/json',
                },
                signal: input._busquedaControlador.signal,
            });

            if (!respuesta.ok) {
                throw new Error('Error en la búsqueda');
            }

            const datos = await respuesta.json();
            const resultados = Array.isArray(datos.resultados) ? datos.resultados : [];

            if (resultados.length === 0) {
                mostrarMensajeResultados(contenedor, 'Sin coincidencias registradas.');
                return;
            }

            contenedor.innerHTML = '';

            resultados.forEach((resultado) => {
                const boton = document.createElement('button');
                boton.type = 'button';
                boton.className = 'contributor-result';

                const nombre = document.createElement('strong');
                nombre.textContent = resultado.nombre;

                const rif = document.createElement('span');
                rif.textContent = resultado.rif;

                boton.appendChild(nombre);
                boton.appendChild(rif);

                boton.addEventListener('click', () => {
                    input.value = resultado.rif;
                    actualizarSeleccionado(input, resultado);
                    cerrarResultados(contenedor);
                    input.focus();
                });

                contenedor.appendChild(boton);
            });

            contenedor.classList.add('is-open');
        } catch (error) {
            if (error.name !== 'AbortError') {
                mostrarMensajeResultados(contenedor, 'No se pudo realizar la búsqueda.');
            }
        }
    };

    document.querySelectorAll('[data-contributor-search]').forEach((input) => {
        const contenedor = document.getElementById(input.dataset.resultados);
        let temporizador = null;

        input.addEventListener('input', () => {
            limpiarSeleccionado(input);

            if (temporizador) {
                window.clearTimeout(temporizador);
            }

            temporizador = window.setTimeout(() => {
                buscarContribuyente(input);
            }, 260);
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                cerrarResultados(contenedor);
            }
        });
    });

    document.addEventListener('click', (event) => {
        document.querySelectorAll('[data-contributor-search]').forEach((input) => {
            if (input.contains(event.target) || event.target.closest('.contributor-result')) {
                return;
            }

            cerrarResultados(document.getElementById(input.dataset.resultados));
        });
    });
})();
