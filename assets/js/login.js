/* =========================================================
   LOGIN
   Funcionalidad visual del formulario de acceso.
   ========================================================= */

const passwordInput = document.getElementById("password");
const passwordToggle = document.querySelector("[data-password-toggle]");
const eyeOpen = document.querySelector("[data-eye-open]");
const eyeClosed = document.querySelector("[data-eye-closed]");

if (passwordInput && passwordToggle && eyeOpen && eyeClosed) {
    passwordToggle.addEventListener("click", () => {
        const isVisible = passwordInput.type === "text";

        // Cambia el tipo de campo para mostrar u ocultar la contraseña.
        passwordInput.type = isVisible ? "password" : "text";

        // Actualiza el estado accesible del botón.
        passwordToggle.setAttribute("aria-pressed", String(!isVisible));
        passwordToggle.setAttribute(
            "aria-label",
            isVisible ? "Mostrar contraseña" : "Ocultar contraseña"
        );

        // Alterna los iconos de ojo abierto y ojo cerrado.
        eyeOpen.classList.toggle("is-hidden", !isVisible);
        eyeClosed.classList.toggle("is-hidden", isVisible);

        // Mantiene el foco en el campo después de pulsar el ojo.
        passwordInput.focus({ preventScroll: true });
        const end = passwordInput.value.length;
        passwordInput.setSelectionRange(end, end);
    });
}
