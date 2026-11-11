(() => {
    const tbody = document.getElementById('ramos-body');
    const plantilla = document.getElementById('ramo-template');
    const agregarRamo = document.getElementById('agregar-ramo');
    const totalDisplay = document.querySelector('.ramos-footer__total strong');
    let siguienteIndice = 0;

    const formatearMonto = (monto) => new Intl.NumberFormat('es-VE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(monto);

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

        const avisoVacio = document.getElementById('ramos-vacio');

        if (avisoVacio) {
            avisoVacio.remove();
        }

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

    document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const mensaje = form.dataset.confirmDelete || '¿Confirmas que deseas eliminar este registro?';

            if (!window.confirm(mensaje)) {
                event.preventDefault();
            }
        });
    });

    const banco = document.getElementById('cod_banco');
    const agencia = document.getElementById('cod_agencia');

    if (!banco || !agencia) {
        return;
    }

    const cargarAgencias = async (codBanco) => {
        agencia.innerHTML = '';

        const opcionInicial = document.createElement('option');
        opcionInicial.value = '';

        if (!codBanco) {
            opcionInicial.textContent = 'Selecciona un banco primero';
            agencia.appendChild(opcionInicial);
            return;
        }

        opcionInicial.textContent = 'Cargando agencias...';
        agencia.appendChild(opcionInicial);

        try {
            const respuesta = await fetch(`get_agencias.php?cod_banco=${encodeURIComponent(codBanco)}`, {
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!respuesta.ok) {
                throw new Error('No se pudieron cargar las agencias');
            }

            const datos = await respuesta.json();
            const agencias = Array.isArray(datos) ? datos : [];

            agencia.innerHTML = '';

            if (agencias.length === 0) {
                const opcionVacia = document.createElement('option');
                opcionVacia.value = '';
                opcionVacia.textContent = 'Sin agencias registradas';
                agencia.appendChild(opcionVacia);
                return;
            }

            const opcionSeleccion = document.createElement('option');
            opcionSeleccion.value = '';
            opcionSeleccion.textContent = 'Seleccionar...';
            agencia.appendChild(opcionSeleccion);

            agencias.forEach((item) => {
                const opcion = document.createElement('option');
                opcion.value = item.cod_agencia;
                opcion.textContent = `${item.cod_agencia} - ${String(item.nombre_agencia || '').trim()}`;
                agencia.appendChild(opcion);
            });
        } catch (error) {
            agencia.innerHTML = '';

            const opcionError = document.createElement('option');
            opcionError.value = '';
            opcionError.textContent = 'No se pudieron cargar las agencias';
            agencia.appendChild(opcionError);
        }
    };

    banco.addEventListener('change', () => {
        cargarAgencias(banco.value);
    });

    const fechaCancelacion = document.getElementById('fecha_cancelacion');
    const fechaContabilidad = document.getElementById('fecha_contabilidad_rec');
    const fechaEntrega = document.getElementById('fecha_de_entreguese');

    if (fechaCancelacion) {
        fechaCancelacion.addEventListener('change', () => {
            if (!fechaCancelacion.value) {
                return;
            }

            if (fechaContabilidad && !fechaContabilidad.value) {
                fechaContabilidad.value = fechaCancelacion.value;
            }

            if (fechaEntrega && !fechaEntrega.value) {
                fechaEntrega.value = fechaCancelacion.value;
            }
        });
    }
})();
