<?php
/**
 * Vista del formulario de inicio de sesión.
 *
 * Responsabilidades:
 * - Mostrar el formulario de acceso.
 * - Conservar el nombre de usuario después de un intento fallido.
 * - Mostrar el mensaje de error que prepare el controlador.
 *
 * Esta vista solo presenta la estructura HTML y los mensajes que prepare
 * el controlador.
 *
 * Variables opcionales:
 * - $error: mensaje de error para mostrar al usuario.
 * - $usuario: nombre de usuario ingresado previamente.
 *
 * Campos que envía el formulario:
 * - usuario: nombre de usuario.
 * - password: contraseña escrita por el usuario.
 */
$error = $error ?? '';
$usuario = $usuario ?? '';
?>

<!-- Contenedor que centra la tarjeta en la pantalla de escritorio. -->
<main class="login-page">
    <div class="login-shell">

        <!-- Tarjeta principal del formulario -->
        <section class="login-card" aria-labelledby="login-title">

            <!-- Línea amarilla superior del diseño -->
            <div class="login-card__accent" aria-hidden="true"></div>

            <!-- Logo institucional -->
            <img
                class="login-card__logo"
                src="assets/img/seniat-logo.png"
                alt="Logo del SENIAT"
            >

            <!-- Título y subtítulo del sistema -->
            <h1 id="login-title" class="login-card__title">
                Sistema Interno de Gestión - SISCOIR
            </h1>

            <p class="login-card__subtitle">
                División de Recaudación - Acceso Restringido
            </p>

            <!-- Mensaje de error enviado desde el controlador -->
            <?php if (!empty($error)): ?>
                <div class="login-alert" role="alert" aria-live="polite">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <!-- Formulario de acceso.
                 Se envía por POST a index.php para que el controlador
                 procese las credenciales. -->
            <form class="login-form" method="POST" action="index.php">

                <!-- Campo de usuario.
                     El valor se conserva con htmlspecialchars para evitar
                     que el texto escrito se interprete como HTML. -->
                <div class="field">
                    <label class="field__label" for="usuario">
                        Usuario
                    </label>

                    <div class="field__control">
                        <svg
                            class="field__icon"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                            />
                        </svg>

                        <input
                            class="field__input"
                            type="text"
                            id="usuario"
                            name="usuario"
                            value="<?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?>"
                            autocomplete="username"
                            autocapitalize="none"
                            spellcheck="false"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- Campo de contraseña -->
                <div class="field">
                    <label class="field__label" for="password">
                        Contraseña
                    </label>

                    <div class="field__control">
                        <svg
                            class="field__icon"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 0-6-6c-3.314 0-6 2.686-6 6 0 1.657.672 3.157 1.757 4.243L4.5 21h4.5v-3h3v-3h3l1.243-1.243A5.985 5.985 0 0 1 21.75 8.25Z"
                            />
                        </svg>

                        <input
                            class="field__input field__input--password"
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            required
                        >

                        <!-- Botón para mostrar u ocultar la contraseña -->
                        <button
                            class="password-toggle"
                            type="button"
                            data-password-toggle
                            aria-controls="password"
                            aria-pressed="false"
                            aria-label="Mostrar contraseña"
                        >
                            <!-- Ojo abierto: contraseña oculta -->
                            <svg
                                class="password-toggle__icon"
                                data-eye-open
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                />
                            </svg>

                            <!-- Ojo cerrado: contraseña visible -->
                            <svg
                                class="password-toggle__icon is-hidden"
                                data-eye-closed
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Botón para enviar el formulario -->
                <button class="login-button" type="submit">
                    Iniciar sesión
                </button>

                <!-- Enlace de recuperación de contraseña. -->
                <a class="forgot-link" href="recuperar.php">
                    ¿Olvidó su contraseña?
                </a>
            </form>

            <!-- Separador y footer del formulario -->
            <div class="login-card__divider" aria-hidden="true"></div>

            <p class="login-card__footer">
                <svg
                    class="login-card__footer-icon"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.8"
                    stroke="currentColor"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"
                    />
                </svg>
                <span>Aduana Principal Las Piedras - Paraguaná</span>
            </p>
        </section>

        <!-- Nota de seguridad inferior -->
        <p class="security-note">
            <svg
                class="security-note__icon"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.8"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"
                />
            </svg>
            <span>Uso exclusivo de personal autorizado. Su acceso puede ser auditado.</span>
        </p>

    </div>
</main>
