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
require_once __DIR__ . '/src/reportes_diario.php';

$esAjax = strtolower(trim((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''))) === 'xmlhttprequest';

if ($esAjax && !estaAutenticado()) {
    http_response_code(401);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Sesión no válida.');
}

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

$tiposReporte = obtenerTiposReporte();
$tipoParametro = $_GET['tipo'] ?? 'pagado';
$tipo = is_string($tipoParametro) ? trim($tipoParametro) : '';
$reporte = obtenerTipoReporte($tipo);

if ($reporte === null) {
    http_response_code(400);
    exit('Tipo de reporte no válido.');
}

$fechaDesdeParametro = $_GET['desde'] ?? date('Y-m-d');
$fechaHastaParametro = $_GET['hasta'] ?? date('Y-m-d');
$fechaDesde = is_string($fechaDesdeParametro) ? trim($fechaDesdeParametro) : '';
$fechaHasta = is_string($fechaHastaParametro) ? trim($fechaHastaParametro) : '';
$consultado = isset($_GET['consultar']);
$esReporteDiario = $tipo === 'diario';
$errores = [];
$resumen = null;

if ($consultado) {
    if (!validarFechaReporte($fechaDesde) || !validarFechaReporte($fechaHasta)) {
        $errores[] = 'Debes indicar una fecha inicial y una fecha final válidas.';
    } elseif ($fechaDesde > $fechaHasta) {
        $errores[] = 'La fecha desde no puede ser posterior a la fecha hasta.';
    }

    if (empty($errores)) {
        $resumen = $esReporteDiario
            ? obtenerResumenReporteDiario($conn, $fechaDesde, $fechaHasta)
            : obtenerResumenReporte($conn, $tipo, $fechaDesde, $fechaHasta);
    }
}

if ($esAjax) {
    header('Content-Type: text/html; charset=utf-8');
    require __DIR__ . '/views/reportes/modulo.php';
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>SISCOIR - Reportes</title>

    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css?v=4">
    <link rel="stylesheet" href="assets/css/reportes.css?v=3">
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
                    Selecciona el reporte que deseas generar, indica el rango de fechas
                    y descarga el detalle en PDF o Excel.
                </p>
            </section>

            <?php require __DIR__ . '/views/reportes/modulo.php'; ?>

        </div>
    </main>

    <?php require __DIR__ . '/views/layouts/app-footer.php'; ?>

    <script src="assets/js/reportes.js?v=2" defer></script>
</body>
</html>
