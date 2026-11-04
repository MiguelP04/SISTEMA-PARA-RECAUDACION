<?php
/**
 * Vista de recuperación de contraseña.
 *
 * Responsabilidades:
 * - Mostrar el paso de identificación del usuario.
 * - Mostrar las preguntas de seguridad cuando exista una solicitud vigente.
 * - Presentar los mensajes preparados por recuperar.php.
 *
 * Variables opcionales:
 * - $paso: "usuario" o "preguntas".
 * - $error: mensaje de error para mostrar.
 * - $usuario: nombre de usuario conservado en el formulario.
 * - $preguntas: preguntas de seguridad del usuario.
 * - $aviso: mensaje informativo para el usuario.
 */
$paso = $paso ?? 'usuario';
$error = $error ?? '';
$usuario = $usuario ?? '';
$preguntas = $preguntas ?? [];
$aviso = $aviso ?? '';
?>

<!-- Contenedor que centra la tarjeta de recuperación. -->
<main class="login-page">
    <div class="login-shell recovery-shell">

        <section class="login-card recovery-card" aria-labelledby="recovery-title">

            <!-- Línea amarilla superior del diseño -->
            <div class="login-card__accent" aria-hidden="true"></div>

            <!-- Logo institucional -->
            <img
                class="login-card__logo"
                src="assets/img/seniat-logo.png"
                alt="Logo del SENIAT"
            >

            <!-- Título y descripción del paso actual -->
            <p class="recovery-step">
                <?= $paso === 'preguntas' ? 'Paso 2 de 2' : 'Paso 1 de 2' ?>
            </p>

            <h1 id="recovery-title" class="login-card__title">
                Recuperar contraseña
            </h1>

            <p class="login-card__subtitle">
                <?= $paso === 'preguntas'
                    ? 'Responda las preguntas de seguridad configuradas en su cuenta.'
                    : 'Ingrese su nombre de usuario para comenzar.' ?>
            </p>

            <!-- Aviso informativo, por ejemplo cuando vence la solicitud -->
            <?php if (!empty($aviso)): ?>
                <div class="login-alert login-alert--info" role="status" aria-live="polite">
                    <?= htmlspecialchars($aviso, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <!-- Mensaje de error enviado desde el controlador -->
            <?php if (!empty($error)): ?>
                <div class="login-alert" role="alert" aria-live="polite">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if ($paso === 'usuario'): ?>

                <!-- Paso 1: nombre de usuario -->
                <form class="login-form recovery-form" method="POST" action="recuperar.php">
                    <input type="hidden" name="accion" value="identificar">

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

                    <button class="login-button" type="submit">
                        Continuar
                    </button>
                </form>

            <?php else: ?>

                <!-- Paso 2: preguntas de seguridad -->
                <p class="recovery-user">
                    Usuario: <strong><?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?></strong>
                </p>

                <form class="login-form recovery-form" method="POST" action="recuperar.php">
                    <input type="hidden" name="accion" value="verificar">

                    <?php foreach ($preguntas as $indice => $pregunta): ?>
                        <?php
                        $campo = (string) ($pregunta['campo'] ?? '');
                        $texto = (string) ($pregunta['texto'] ?? '');
                        ?>

                        <?php if ($campo !== '' && $texto !== ''): ?>
                            <div class="field">
                                <label class="field__label" for="respuesta_<?= htmlspecialchars($campo, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') ?>
                                </label>

                                <input
                                    class="field__input field__input--plain"
                                    type="text"
                                    id="respuesta_<?= htmlspecialchars($campo, ENT_QUOTES, 'UTF-8') ?>"
                                    name="respuestas[<?= htmlspecialchars($campo, ENT_QUOTES, 'UTF-8') ?>]"
                                    autocomplete="off"
                                    required
                                    <?= $indice === 0 ? 'autofocus' : '' ?>
                                >
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <button class="login-button" type="submit">
                        Verificar respuestas
                    </button>

                    <a class="recovery-link" href="recuperar.php?reiniciar=1">
                        Intentar con otro usuario
                    </a>
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
