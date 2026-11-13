(() => {
    document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const mensaje = form.dataset.confirmDelete || '¿Confirmas que deseas eliminar este registro?';

            if (!window.confirm(mensaje)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-rif-numero]').forEach((input) => {
        input.addEventListener('input', () => {
            const limpio = input.value.replace(/\D/g, '').slice(0, 9);

            if (input.value !== limpio) {
                input.value = limpio;
            }
        });
    });

    const ciudad = document.querySelector('[data-ciudad]');
    const zonaPostal = document.querySelector('[data-zona-postal]');

    if (ciudad && zonaPostal) {
        const sugerirZona = () => {
            const opcion = ciudad.options[ciudad.selectedIndex];
            const zona = opcion ? opcion.dataset.zonaPostal : '';

            if (zona) {
                zonaPostal.value = zona;
            }
        };

        if (zonaPostal.value === '') {
            sugerirZona();
        }

        ciudad.addEventListener('change', sugerirZona);
    }

    document.querySelectorAll('[data-cir]').forEach((input) => {
        input.addEventListener('input', () => {
            input.value = input.value.toUpperCase().slice(0, 1);
        });
    });
})();
