<?php
/**
 * Panel principal de SISCOIR.
 *
 * Flujo:
 * 1. Carga la configuración común y las funciones de autenticación.
 * 2. Exige una sesión válida antes de mostrar el contenido.
 * 3. Presenta los datos básicos del usuario autenticado.
 */

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';

requiereLogin();

// Datos del usuario guardados durante el inicio de sesión.
$nombre = $_SESSION['nombre_completo'] ?? $_SESSION['username'] ?? '';
$usuario = $_SESSION['username'] ?? '';
$rol = $_SESSION['rol'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>SISCOIR - Panel principal</title>

    <!-- Estilos base y estilos del panel. -->
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <main class="dashboard-page">
        <section class="dashboard-card" aria-labelledby="dashboard-title">

            <!-- Línea amarilla superior. -->
            <div class="dashboard-card__accent" aria-hidden="true"></div>

            <!-- Encabezado institucional. -->
            <header class="dashboard-header">
                <img
                    class="dashboard-logo"
                    src="assets/img/seniat-logo.png"
                    alt="Logo del SENIAT"
                >

                <div>
                    <h1 id="dashboard-title" class="dashboard-title">
                        SISCOIR
                    </h1>
                    <p class="dashboard-subtitle">Panel principal</p>
                </div>
            </header>

            <!-- Contenido del usuario autenticado. -->
            <div class="dashboard-content">
                <p class="dashboard-welcome">
                    Bienvenido,
                    <strong><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></strong>
                </p>

                <dl class="dashboard-data">
                    <div class="dashboard-data__item">
                        <dt>Usuario</dt>
                        <dd><?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?></dd>
                    </div>

                    <div class="dashboard-data__item">
                        <dt>Rol</dt>
                        <dd><?= htmlspecialchars($rol, ENT_QUOTES, 'UTF-8') ?></dd>
                    </div>
                </dl>

                <a class="dashboard-logout" href="logout.php">
                    Cerrar sesión
                </a>
            </div>
        </section>
    </main>
</body>
</html>
