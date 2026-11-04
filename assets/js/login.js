/**
 * Control visual de los campos de contraseña.
 *
 * Responsabilidades:
 * - Alternar la visibilidad de cada contraseña al pulsar su botón.
 * - Alternar los iconos de ojo abierto y ojo cerrado.
 * - Actualizar los atributos de accesibilidad del botón.
 * - Devolver el foco al campo después de cada cambio.
 *
 * Cada botón indica el campo que controla mediante aria-controls.
 * Así el mismo script funciona en el login y en el restablecimiento.
 */

document.querySelectorAll("[data-password-toggle]").forEach((toggle) => {
    // Obtiene el campo asociado al botón desde aria-controls.
    const inputId = toggle.getAttribute("aria-controls");
    const input = inputId ? document.getElementById(inputId) : null;

    // Los dos iconos deben estar dentro del mismo botón.
    const eyeOpen = toggle.querySelector("[data-eye-open]");
    const eyeClosed = toggle.querySelector("[data-eye-closed]");

    // Si falta algún elemento, este botón no activa el comportamiento.
    if (!input || !eyeOpen || !eyeClosed) {
        return;
    }

    toggle.addEventListener("click", () => {
        // El campo está visible cuando su tipo es "text".
        const isVisible = input.type === "text";

        // Cambia el tipo para mostrar u ocultar los caracteres.
        input.type = isVisible ? "password" : "text";

        // Actualiza el estado accesible del botón para lectores de pantalla.
        toggle.setAttribute("aria-pressed", String(!isVisible));
        toggle.setAttribute(
            "aria-label",
            isVisible ? "Mostrar contraseña" : "Ocultar contraseña"
        );

        // Alterna los iconos: uno se muestra y el otro se oculta.
        eyeOpen.classList.toggle("is-hidden", !isVisible);
        eyeClosed.classList.toggle("is-hidden", isVisible);

        // Devuelve el foco al campo sin mover la página.
        input.focus({ preventScroll: true });

        // Coloca el cursor al final del texto para no interrumpir la escritura.
        const end = input.value.length;
        input.setSelectionRange(end, end);
    });
});
