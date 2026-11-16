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
 * - $consultaActiva: identificador de la consulta actual.
 * - $registroActivo: identificador del registro actual.
 */
$nombre = $nombre ?? ($_SESSION['nombre_completo'] ?? $_SESSION['username'] ?? '');
$usuario = $usuario ?? ($_SESSION['username'] ?? '');
$rolVisible = $rolVisible ?? 'Usuario';
$esAdmin = $esAdmin ?? false;
$iniciales = $iniciales ?? strtoupper(substr($usuario, 0, 2));
$paginaActiva = $paginaActiva ?? 'inicio';
$consultaActiva = $consultaActiva ?? '';
$registroActivo = $registroActivo ?? '';
?>

<header class="app-header">

    <!-- Franja oficial con logos y badge institucional -->
    <div class="official-bar">
        <div class="official-bar__group">
            <div class="official-bar__left">
                <img
                    class="official-bar__piece official-bar__piece--ministerio"
                    src="assets/img/navbar-pieces/ministerio-left.png"
                    alt="Ministerio del Poder Popular de Economía y Finanzas"
                    draggable="false"
                >
                <img
                    class="official-bar__piece official-bar__piece--seniat"
                    src="assets/img/seniat-logo.png"
                    alt="SENIAT"
                    draggable="false"
                >
            </div>
            <img
                class="official-bar__piece official-bar__piece--badge"
                src="assets/img/navbar-pieces/badge-right.png"
                alt="Aniversario 200 años"
                draggable="false"
            >
        </div>
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

                <details class="nav-menu<?= $paginaActiva === 'consultas' ? ' is-active' : '' ?>">
                    <summary
                        class="main-navbar__link nav-menu__trigger<?= $paginaActiva === 'consultas' ? ' is-active' : '' ?>"
                        <?= $paginaActiva === 'consultas' ? 'aria-current="page"' : '' ?>
                    >
                        Consultas

                        <svg
                            class="nav-menu__chevron"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                        </svg>
                    </summary>

                    <div class="nav-menu__panel">
                        <a
                            class="nav-menu__item<?= $paginaActiva === 'consultas' && $consultaActiva === 'planillas' ? ' is-current' : '' ?>"
                            href="consulta_planilla.php"
                        >
                            <span>
                                <strong>Planillas</strong>
                                <small>Consultar por N° de formulario</small>
                            </span>
                        </a>

                        <a
                            class="nav-menu__item<?= $paginaActiva === 'consultas' && $consultaActiva === 'agentes_consignatarios' ? ' is-current' : '' ?>"
                            href="consulta_agentes_consignatarios.php"
                        >
                            <span>
                                <strong>Agentes/Consignatarios</strong>
                                <small>Consultar por RIF</small>
                            </span>
                        </a>
                    </div>
                </details>

                <details class="nav-menu<?= $paginaActiva === 'registros' ? ' is-active' : '' ?>">
                    <summary
                        class="main-navbar__link nav-menu__trigger<?= $paginaActiva === 'registros' ? ' is-active' : '' ?>"
                        <?= $paginaActiva === 'registros' ? 'aria-current="page"' : '' ?>
                    >
                        Registros

                        <svg
                            class="nav-menu__chevron"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                        </svg>
                    </summary>

                    <div class="nav-menu__panel">
                        <a
                            class="nav-menu__item<?= $paginaActiva === 'registros' && $registroActivo === 'planillas' ? ' is-current' : '' ?>"
                            href="registro_planilla.php"
                        >
                            <span>
                                <strong>Planillas</strong>
                                <small>Registro manual de planilla</small>
                            </span>
                        </a>

                        <a
                            class="nav-menu__item<?= $paginaActiva === 'registros' && $registroActivo === 'consignatarios' ? ' is-current' : '' ?>"
                            href="registro_consignatario.php"
                        >
                            <span>
                                <strong>Consignatarios</strong>
                                <small>Registro de consignatario</small>
                            </span>
                        </a>

                        <a
                            class="nav-menu__item<?= $paginaActiva === 'registros' && $registroActivo === 'agentes' ? ' is-current' : '' ?>"
                            href="registro_agente.php"
                        >
                            <span>
                                <strong>Agentes aduanales</strong>
                                <small>Registro de agente aduanal</small>
                            </span>
                        </a>
                    </div>
                </details>

                <a
                    class="main-navbar__link<?= $paginaActiva === 'reportes' ? ' is-active' : '' ?>"
                    href="reportes.php"
                    <?= $paginaActiva === 'reportes' ? 'aria-current="page"' : '' ?>
                >
                    Reportes
                </a>

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

<script>
(() => {
    const menus = Array.from(document.querySelectorAll('.nav-menu'));

    if (menus.length === 0) {
        return;
    }

    menus.forEach((menu) => {
        menu.addEventListener('toggle', () => {
            if (!menu.open) {
                return;
            }

            menus.forEach((otroMenu) => {
                if (otroMenu !== menu) {
                    otroMenu.open = false;
                }
            });
        });
    });

    document.addEventListener('click', (event) => {
        menus.forEach((menu) => {
            if (menu.open && !menu.contains(event.target)) {
                menu.open = false;
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        menus.forEach((menu) => {
            if (!menu.open) {
                return;
            }

            menu.open = false;

            const trigger = menu.querySelector('summary');

            if (trigger) {
                trigger.focus();
            }
        });
    });
})();
</script>
