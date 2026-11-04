<?php
/**
 * Encabezado principal de SISCOIR.
 *
 * Contiene:
 * - La franja oficial con los logos institucionales.
 * - La barra azul de navegación.
 * - El bloque del usuario autenticado.
 *
 * Variables opcionales:
 * - $nombre: nombre completo del usuario.
 * - $usuario: nombre de usuario.
 * - $rolVisible: texto del rol para mostrar.
 * - $esAdmin: true cuando el usuario es administrador.
 * - $iniciales: iniciales del avatar.
 * - $paginaActiva: identificador de la página actual.
 */
$nombre = $nombre ?? ($_SESSION['nombre_completo'] ?? $_SESSION['username'] ?? '');
$usuario = $usuario ?? ($_SESSION['username'] ?? '');
$rolVisible = $rolVisible ?? 'Usuario';
$esAdmin = $esAdmin ?? false;
$iniciales = $iniciales ?? strtoupper(substr($usuario, 0, 2));
$paginaActiva = $paginaActiva ?? 'inicio';
?>

<header class="app-header">

    <!-- Franja oficial con logos y badge institucional -->
    <div class="official-bar">
        <img
            class="official-bar__image"
            src="assets/img/navbar.png"
            alt="Ministerio de Economía y Finanzas, SENIAT y aniversario 200 años"
            draggable="false"
        >
    </div>

    <!-- Barra principal de navegación -->
    <div class="main-navbar">
        <div class="main-navbar__inner">

            <!-- Identidad del sistema -->
            <a class="brand" href="dashboard.php" aria-label="Ir al inicio de SISCOIR">
                <img
                    class="brand__logo"
                    src="assets/img/seniat-logo-navbar.png"
                    alt="SENIAT"
                >
                <span class="brand__divider" aria-hidden="true"></span>
                <span class="brand__name">SISCOIR</span>
            </a>

            <!-- Navegación principal -->
            <nav class="main-navbar__nav" aria-label="Navegación principal">
                <a
                    class="main-navbar__link<?= $paginaActiva === 'inicio' ? ' is-active' : '' ?>"
                    href="dashboard.php"
                    <?= $paginaActiva === 'inicio' ? 'aria-current="page"' : '' ?>
                >
                    Inicio
                </a>

                <span class="main-navbar__link is-disabled" aria-disabled="true">
                    Consultas
                </span>

                <span class="main-navbar__link is-disabled" aria-disabled="true">
                    Registros
                </span>

                <span class="main-navbar__link is-disabled" aria-disabled="true">
                    Reportes
                </span>

                <?php if ($esAdmin): ?>
                    <span class="main-navbar__link is-disabled" aria-disabled="true">
                        Administración
                    </span>
                <?php endif; ?>
            </nav>

            <!-- Notificaciones y menú del usuario -->
            <div class="main-navbar__actions">
                <span class="notification-button" role="img" aria-label="Notificaciones">
                    <svg
                        class="notification-button__icon"
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
                            d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"
                        />
                    </svg>
                </span>

                <?php require __DIR__ . '/profile-menu.php'; ?>
            </div>

        </div>
    </div>
</header>
