<?php
/**
 * Menú desplegable del usuario autenticado.
 *
 * Este archivo se conserva como componente independiente para poder
 * reutilizarlo en las páginas internas que compartan el mismo header.
 *
 * Variables opcionales:
 * - $nombre: nombre completo del usuario.
 * - $usuario: nombre de usuario.
 * - $rolVisible: texto del rol para mostrar.
 * - $iniciales: iniciales del avatar.
 */
$nombre = $nombre ?? ($_SESSION['nombre_completo'] ?? $_SESSION['username'] ?? '');
$usuario = $usuario ?? ($_SESSION['username'] ?? '');
$rolVisible = $rolVisible ?? 'Usuario';
$iniciales = $iniciales ?? strtoupper(substr($usuario, 0, 2));
?>
<details class="profile-menu">
    <summary class="profile-menu__trigger" aria-label="Abrir menú de usuario">
        <span class="profile-avatar" aria-hidden="true">
            <?= htmlspecialchars($iniciales, ENT_QUOTES, 'UTF-8') ?>
        </span>

        <span class="profile-info">
            <strong class="profile-info__name">
                <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>
            </strong>
            <small class="profile-info__role">
                <?= htmlspecialchars($rolVisible, ENT_QUOTES, 'UTF-8') ?>
            </small>
        </span>

        <svg
            class="profile-chevron"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="2"
            stroke="currentColor"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="m19.5 8.25-7.5 7.5-7.5-7.5"
            />
        </svg>
    </summary>

    <div class="profile-menu__panel">
        <div class="profile-menu__identity">
            <strong><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></strong>
            <span><?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <span class="profile-menu__item is-disabled" aria-disabled="true">
            Preguntas de seguridad
        </span>

        <a class="profile-menu__item profile-menu__item--logout" href="logout.php">
            <svg
                class="profile-menu__icon"
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
                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"
                />
            </svg>
            Cerrar sesión
        </a>
    </div>
</details>
