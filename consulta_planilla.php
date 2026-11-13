<?php

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/consultas.php';

requiereLogin();

$nombre = $_SESSION['nombre_completo'] ?? $_SESSION['username'] ?? '';
$usuario = $_SESSION['username'] ?? '';
$rol = $_SESSION['rol'] ?? '';

$rolesVisibles = [
    'admin' => 'Administrador',
    'usuario' => 'Usuario',
];
$rolVisible = $rolesVisibles[$rol] ?? ucfirst($rol);
$esAdmin = $rol === 'admin';
$paginaActiva = 'consultas';
$consultaActiva = 'planillas';

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

$escapar = static fn ($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$mensajesOk = [
    'actualizada' => 'Planilla actualizada correctamente.',
    'eliminada' => 'Planilla eliminada correctamente.',
    'recaudada' => 'Recaudación registrada correctamente.',
    'recaudacion_actualizada' => 'Recaudación actualizada correctamente.',
];
$mensajeOk = '';

if (isset($_GET['ok'], $mensajesOk[$_GET['ok']])) {
    $mensajeOk = $mensajesOk[$_GET['ok']];
}

$errores = tomarFlashConsulta('consulta_planilla_errores') ?? [];
$mensajesError = [
    'no_encontrada' => 'No se encontró una planilla con el N° de formulario indicado.',
    'permiso' => 'No tienes permisos para realizar esa acción.',
];

if (isset($_GET['error'], $mensajesError[$_GET['error']])) {
    $errores[] = $mensajesError[$_GET['error']];
}

$formularioFlash = tomarFlashConsulta('consulta_planilla_formulario');
$ramosFlash = tomarFlashConsulta('consulta_planilla_ramos');

$nro = trim((string) ($_GET['nro'] ?? ''));
$buscado = $nro !== '';
$planilla = null;
$ramos = [];
$estado = null;
$modoEdicion = isset($_GET['editar']) && $_GET['editar'] === '1';

if ($buscado) {
    if (!validarNumeroPlanillaConsulta($nro)) {
        $errores[] = 'El N° de formulario debe contener solo dígitos y tener hasta 20 caracteres.';
    } else {
        $planilla = buscarPlanillaConsulta($conn, $nro);

        if ($planilla === null) {
            $errores[] = 'No se encontró una planilla con el N° de formulario indicado.';
        } else {
            $ramos = obtenerRamosPlanillaConsulta($conn, $nro);
            $estado = obtenerEstadoPlanillaConsulta($planilla);
        }
    }
}

if ($modoEdicion && !$esAdmin) {
    $modoEdicion = false;
    $errores[] = 'No tienes permisos para editar planillas.';
}

$formulario = [];
$ramosFormulario = [];
$editarRamos = false;
$montoFormulario = 0.0;

if ($modoEdicion && $planilla !== null) {
    $nroPlanilla = (string) $planilla['nro_formulario'];
    $editarRamos = strlen($nroPlanilla) <= 10;
    $formaVisible = trim((string) ($planilla['forma_codigo'] ?? ''));

    if ($formaVisible === '') {
        $formaVisible = codigoFormaVisibleConsulta($planilla['cod_de_forma'] ?? '');
    }

    $formularioBase = [
        'nro_formulario' => $nroPlanilla,
        'codigo_aduana' => (string) $planilla['codigo_aduana'],
        'cod_de_forma' => $formaVisible,
        'ano_declaracion' => (string) $planilla['ano_declaracion'],
        'nro_declaracion' => (string) $planilla['nro_declaracion'],
        'fecha_emision' => fechaParaInputConsulta($planilla['fecha_emision'] ?? ''),
        'fecha_contabilidad_liq' => fechaParaInputConsulta($planilla['fecha_contabilidad_liq'] ?? ''),
        'fecha_de_notificacion' => fechaParaInputConsulta($planilla['fecha_de_notificacion'] ?? ''),
        'fecha_vencimiento' => fechaParaInputConsulta($planilla['fecha_vencimiento'] ?? ''),
        'nro_resolucion' => (string) $planilla['nro_resolucion'],
        'nro_liquidacion' => (string) $planilla['nro_liquidacion'],
        'tipo_de_planilla' => (string) $planilla['tipo_de_planilla'],
    ];

    $formulario = is_array($formularioFlash)
        ? array_merge($formularioBase, $formularioFlash)
        : $formularioBase;

    $ramosFormulario = is_array($ramosFlash) ? $ramosFlash : $ramos;

    foreach ($ramosFormulario as $ramo) {
        $monto = str_replace(',', '.', trim((string) ($ramo['monto'] ?? '')));

        if (is_numeric($monto)) {
            $montoFormulario += (float) $monto;
        }
    }
}

$catalogos = obtenerCatalogosPlanilla($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>SISCOIR - Consultar planilla</title>

    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css?v=4">
    <link rel="stylesheet" href="assets/css/registros.css?v=2">
    <link rel="stylesheet" href="assets/css/consultas.css?v=2">
</head>
<body>
    <?php require __DIR__ . '/views/layouts/app-header.php'; ?>

    <main class="queries-main">
        <div class="queries-workspace">
            <section class="queries-hero" aria-labelledby="queries-title">
                <div>
                    <p class="queries-hero__eyebrow">Consultas / Planillas</p>

                    <h1 id="queries-title" class="queries-hero__title">
                        Consultar planilla
                    </h1>

                    <p class="queries-hero__text">
                        Busca una planilla por su número de formulario para revisar su detalle,
                        sus ramos de ingreso y el estado de recaudación.
                    </p>
                </div>

                <a class="queries-hero__back" href="dashboard.php">
                    Volver al inicio
                </a>
            </section>

            <?php if ($mensajeOk !== ''): ?>
                <section class="queries-alert queries-alert--success" role="status">
                    <strong><?= $escapar($mensajeOk) ?></strong>
                </section>
            <?php endif; ?>

            <?php if (!empty($errores)): ?>
                <section class="queries-alert queries-alert--error" role="alert">
                    <strong>Revisa la consulta:</strong>

                    <ul>
                        <?php foreach ($errores as $error): ?>
                            <li><?= $escapar($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php require __DIR__ . '/views/consultas/busqueda-planilla.php'; ?>

            <?php if ($planilla !== null): ?>
                <?php if ($modoEdicion): ?>
                    <?php require __DIR__ . '/views/consultas/formulario-edicion-planilla.php'; ?>
                <?php else: ?>
                    <?php require __DIR__ . '/views/consultas/detalle-planilla.php'; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php require __DIR__ . '/views/layouts/app-footer.php'; ?>

    <script src="assets/js/consultas.js?v=1" defer></script>
</body>
</html>
