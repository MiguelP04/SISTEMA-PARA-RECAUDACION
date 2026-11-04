<?php
/**
 * Vista del formulario de nueva contraseña.
 *
 * Responsabilidades:
 * - Mostrar los campos de nueva contraseña y confirmación.
 * - Presentar el mensaje de error preparado por restablecer.php.
 * - Mostrar el resultado final cuando la contraseña se actualiza.
 *
 * Variables opcionales:
 * - $error: mensaje de error para mostrar.
 * - $exito: true cuando la contraseña ya fue actualizada.
 * - $usuario: nombre del usuario que recupera su cuenta.
 */
$error = $error ?? '';
$exito = $exito ?? false;
$usuario = $usuario ?? '';
?>

<!-- Contenedor que centra la tarjeta de restablecimiento. -->
<main class="login-page">
    <div class="login-shell recovery-shell recovery-shell--compact">

        <section class="login-card recovery-card" aria-labelledby="reset-title">

            <!-- Línea amarilla superior del diseño -->
            <div class="login-card__accent" aria-hidden="true"></div>

            <!-- Logo institucional -->
            <img
                class="login-card__logo"
                src="assets/img/seniat-logo.png"
                alt="Logo del SENIAT"
            >

            <?php if ($exito): ?>

                <!-- Resultado correcto del cambio de contraseña -->
                <h1 id="reset-title" class="login-card__title">
                    Contraseña actualizada
                </h1>

                <p class="login-card__subtitle">
                    La contraseña de <strong><?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?></strong>
                    fue actualizada correctamente.
                </p>

                <div class="login-alert login-alert--success" role="status" aria-live="polite">
                    Ya puede iniciar sesión con su nueva contraseña.
                </div>

                <a class="login-button recovery-button-link" href="index.php">
                    Volver al inicio de sesión
                </a>

            <?php else: ?>

                <!-- Formulario para definir la nueva contraseña -->
                <p class="recovery-step">Paso 2 de 2</p>

                <h1 id="reset-title" class="login-card__title">
                    Restablecer contraseña
                </h1>

                <p class="login-card__subtitle">
                    Defina una nueva contraseña para
                    <strong><?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?></strong>.
                </p>

                <!-- Mensaje de error enviado desde el controlador -->
                <?php if (!empty($error)): ?>
                    <div class="login-alert" role="alert" aria-live="polite">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form class="login-form recovery-form" method="POST" action="restablecer.php">

                    <!-- Campo de nueva contraseña -->
                    <div class="field">
                        <label class="field__label" for="password">
                            Nueva contraseña
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
                                autocomplete="new-password"
                                minlength="8"
                                required
                                autofocus
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

                        <p class="recovery-hint">Use al menos 8 caracteres.</p>
                    </div>

                    <!-- Campo de confirmación -->
                    <div class="field">
                        <label class="field__label" for="confirmar_password">
                            Confirmar contraseña
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
                                id="confirmar_password"
                                name="confirmar_password"
                                autocomplete="new-password"
                                minlength="8"
                                required
                            >

                            <!-- Botón para mostrar u ocultar la confirmación -->
                            <button
                                class="password-toggle"
                                type="button"
                                data-password-toggle
                                aria-controls="confirmar_password"
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

                    <button class="login-button" type="submit">
                        Guardar contraseña
                    </button>
                </form>

            <?php endif; ?>

            <!-- Separador y enlace para volver al login -->
            <div class="login-card__divider" aria-hidden="true"></div>

            <a class="forgot-link" href="index.php">
                Volver al inicio de sesión
            </a>
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
