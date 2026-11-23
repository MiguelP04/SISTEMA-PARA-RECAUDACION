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
require_once __DIR__ . '/src/reportes_liquidado.php';

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

$rangoPredeterminado = obtenerRangoPredeterminadoReporte($tipo);
$fechaDesdeParametro = $_GET['desde'] ?? $rangoPredeterminado[0];
$fechaHastaParametro = $_GET['hasta'] ?? $rangoPredeterminado[1];
$fechaDesde = is_string($fechaDesdeParametro) ? trim($fechaDesdeParametro) : '';
$fechaHasta = is_string($fechaHastaParametro) ? trim($fechaHastaParametro) : '';
$consultado = isset($_GET['consultar']);
$esReportePeriodo = esReportePlanillasPagadasPeriodo($tipo);
$esReporteLiquidado = $tipo === 'liquidado';
$aduanaSeleccionada = '';
$formaSeleccionada = '';
$aduanasReporte = [];
$formasReporte = [];

if ($esReporteLiquidado) {
    $aduanasReporte = obtenerAduanasReporte($conn);
    $formasReporte = obtenerFormasReporte($conn);

    $aduanaParametro = $_GET['aduana'] ?? '';
    $formaParametro = $_GET['forma'] ?? '';
    $aduanaSeleccionada = is_string($aduanaParametro) ? trim($aduanaParametro) : '';
    $formaSeleccionada = is_string($formaParametro) ? trim($formaParametro) : '';
}

$errores = [];
$resumen = null;

if ($consultado) {
    if (!validarFechaReporte($fechaDesde) || !validarFechaReporte($fechaHasta)) {
        $errores[] = 'Debes indicar una fecha inicial y una fecha final válidas.';
    } elseif ($fechaDesde > $fechaHasta) {
        $errores[] = 'La fecha desde no puede ser posterior a la fecha hasta.';
    }

    if ($esReporteLiquidado && $aduanaSeleccionada !== '' && !isset($aduanasReporte[$aduanaSeleccionada])) {
        $errores[] = 'La aduana seleccionada no es válida.';
    }

    if ($esReporteLiquidado && $formaSeleccionada !== '' && !isset($formasReporte[$formaSeleccionada])) {
        $errores[] = 'El tipo de forma seleccionado no es válido.';
    }

    if (empty($errores)) {
        if ($esReportePeriodo) {
            $resumen = obtenerResumenReporteDiario($conn, $fechaDesde, $fechaHasta);
        } elseif ($esReporteLiquidado) {
            $resumen = obtenerResumenReporteLiquidado(
                $conn,
                $fechaDesde,
                $fechaHasta,
                $aduanaSeleccionada,
                $formaSeleccionada
            );
        } else {
            $resumen = obtenerResumenReporte($conn, $tipo, $fechaDesde, $fechaHasta);
        }
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
    <link rel="stylesheet" href="assets/css/reportes.css?v=6">
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
