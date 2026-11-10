<?php
/**
 * Módulo de generación de reportes.
 *
 * La pantalla muestra el catálogo visual de reportes, los filtros del
 * reporte habilitado y un resumen. El detalle completo se genera bajo
 * demanda en los archivos PDF y Excel.
 */

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/reportes.php';

requiereLogin();

// Datos del usuario autenticado para el encabezado.
$nombre = $_SESSION['nombre_completo'] ?? $_SESSION['username'] ?? '';
$usuario = $_SESSION['username'] ?? '';
$rol = $_SESSION['rol'] ?? '';

$rolesVisibles = [
    'admin' => 'Administrador',
    'usuario' => 'Usuario',
];
$rolVisible = $rolesVisibles[$rol] ?? ucfirst($rol);
$esAdmin = $rol === 'admin';
$paginaActiva = 'reportes';

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

$fechaDesde = trim($_GET['desde'] ?? date('Y-m-d'));
$fechaHasta = trim($_GET['hasta'] ?? date('Y-m-d'));
$consultado = isset($_GET['consultar']);
$errores = [];
$resumen = null;

if ($consultado) {
    if (!validarFechaReporte($fechaDesde) || !validarFechaReporte($fechaHasta)) {
        $errores[] = 'Debes indicar una fecha inicial y una fecha final válidas.';
    } elseif ($fechaDesde > $fechaHasta) {
        $errores[] = 'La fecha desde no puede ser posterior a la fecha hasta.';
    }

    if (empty($errores)) {
        $resumen = obtenerResumenReporte($conn, $fechaDesde, $fechaHasta);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>SISCOIR - Reportes</title>

    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/reportes.css">
</head>
<body>
    <?php require __DIR__ . '/views/layouts/app-header.php'; ?>

    <main class="reports-main">
        <div class="reports-workspace">

            <section class="reports-hero" aria-labelledby="reports-title">
                <p class="reports-hero__eyebrow">Reportes</p>

                <h1 id="reports-title" class="reports-hero__title">
                    Centro de Reportes
                </h1>

                <p class="reports-hero__text">
                    Selecciona el tipo de planilla que deseas generar. Por ahora está disponible
                    el reporte de planillas pagadas; los demás se incorporarán progresivamente.
                </p>
            </section>

            <?php require __DIR__ . '/views/reportes/catalogo.php'; ?>

            <?php require __DIR__ . '/views/reportes/filtros.php'; ?>

            <?php if ($consultado && empty($errores) && $resumen !== null): ?>
                <?php require __DIR__ . '/views/reportes/resumen.php'; ?>
            <?php endif; ?>

        </div>
    </main>

    <?php require __DIR__ . '/views/layouts/app-footer.php'; ?>

    <script src="assets/js/reportes.js" defer></script>
</body>
</html>
