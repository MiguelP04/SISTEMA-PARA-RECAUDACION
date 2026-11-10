<?php
/**
 * Panel principal de SISCOIR.
 *
 * Flujo:
 * 1. Carga la configuración común y las funciones de autenticación.
 * 2. Exige una sesión válida antes de mostrar el contenido.
 * 3. Prepara los datos del usuario autenticado.
 * 4. Presenta el encabezado, el hero, las tarjetas y el pie de página.
 */

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';

requiereLogin();

// Datos del usuario guardados durante el inicio de sesión.
$nombre = $_SESSION['nombre_completo'] ?? $_SESSION['username'] ?? '';
$usuario = $_SESSION['username'] ?? '';
$rol = $_SESSION['rol'] ?? '';

// Texto visible del rol y permiso para las opciones administrativas.
$rolesVisibles = [
    'admin' => 'Administrador',
    'usuario' => 'Usuario',
];
$rolVisible = $rolesVisibles[$rol] ?? ucfirst($rol);
$esAdmin = $rol === 'admin';
$paginaActiva = 'inicio';

// Iniciales que se muestran en el avatar del menú de usuario.
$nombreParaIniciales = trim($nombre) !== '' ? $nombre : $usuario;
$partesNombre = preg_split('/\s+/', trim($nombreParaIniciales)) ?: [];
$iniciales = '';

foreach (array_slice($partesNombre, 0, 2) as $parte) {
    $iniciales .= function_exists('mb_substr')
        ? mb_substr($parte, 0, 1, 'UTF-8')
        : substr($parte, 0, 1);
}

$iniciales = function_exists('mb_strtoupper')
    ? mb_strtoupper($iniciales, 'UTF-8')
    : strtoupper($iniciales);

if ($iniciales === '') {
    $iniciales = 'U';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>SISCOIR - Panel principal</title>

    <!-- Estilos base, estructura del sistema y estilos del panel. -->
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css?v=3">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <!-- Encabezado oficial y barra de navegación. -->
    <?php require __DIR__ . '/views/layouts/app-header.php'; ?>

    <main class="dashboard-main">
        <div class="dashboard-workspace">

            <!-- Bienvenida y acciones principales. -->
            <section class="dashboard-hero" aria-labelledby="dashboard-title">
                <div class="dashboard-hero__content">
                    <p class="dashboard-hero__eyebrow">Panel principal</p>

                    <h1 id="dashboard-title" class="dashboard-hero__title">
                        Bienvenido de nuevo,
                        <span><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></span>
                    </h1>

                    <p class="dashboard-hero__status">
                        <span class="dashboard-hero__status-dot" aria-hidden="true"></span>
                        Sesión Activa - Aduana Principal Las Piedras - Paraguaná
                    </p>
                </div>

                <div class="dashboard-hero__actions">
                    <button class="action-button action-button--primary" type="button" disabled>
                        Nueva planilla
                    </button>

                    <button class="action-button action-button--secondary" type="button" disabled>
                        Consultar planilla
                    </button>
                </div>
            </section>

            <!-- Tarjetas de servicios principales. -->
            <?php require __DIR__ . '/views/dashboard/cards.php'; ?>

        </div>
    </main>

    <!-- Pie de página institucional. -->
    <?php require __DIR__ . '/views/layouts/app-footer.php'; ?>
</body>
</html>
