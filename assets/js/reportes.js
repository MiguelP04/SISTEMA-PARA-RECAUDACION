document.querySelectorAll("[data-date-display]").forEach((input) => {
    const display = document.getElementById(input.dataset.dateDisplay);

    if (!display) {
        return;
    }

    const actualizarFecha = () => {
        const partes = input.value.split("-");

        display.textContent = partes.length === 3
            ? `${partes[2]}/${partes[1]}/${partes[0]}`
            : "";
    };

    input.addEventListener("input", actualizarFecha);
    input.addEventListener("change", actualizarFecha);
    actualizarFecha();
});
