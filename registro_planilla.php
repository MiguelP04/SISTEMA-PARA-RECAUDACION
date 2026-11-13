<?php

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/registros.php';

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
$paginaActiva = 'registros';
$registroActivo = 'planillas';

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

$catalogos = obtenerCatalogosPlanilla($conn);
$hoy = date('Y-m-d');

$formulario = [
    'nro_formulario' => '',
    'codigo_aduana' => '',
    'cod_de_forma' => '',
    'ano_declaracion' => date('Y'),
    'nro_declaracion' => '',
    'fecha_emision' => $hoy,
    'fecha_contabilidad_liq' => $hoy,
    'fecha_de_notificacion' => $hoy,
    'fecha_vencimiento' => date('Y-m-d', strtotime($hoy . ' +5 days')),
    'nro_resolucion' => '',
    'nro_liquidacion' => '',
    'tipo_de_planilla' => '',
    'rif_consignatario' => '',
    'rif_agente' => '',
];

$ramosFormulario = [
    [
        'cod_concepto' => '',
        'cod_forma' => 'PG',
        'monto' => '',
    ],
];

$errores = [];
$registroExitoso = '';

if (isset($_GET['registrada'], $_GET['nro'])) {
    $nroRegistrado = trim((string) $_GET['nro']);

    if (preg_match('/^\d{1,10}$/', $nroRegistrado)) {
        $registroExitoso = $nroRegistrado;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datosPost = array_intersect_key($_POST, $formulario);
    $formulario = array_merge($formulario, $datosPost);
    $ramosPost = isset($_POST['ramos']) && is_array($_POST['ramos']) ? $_POST['ramos'] : [];

    foreach ($ramosPost as $indice => $ramo) {
        if (!is_array($ramo)) {
            unset($ramosPost[$indice]);
        }
    }

    $ramosPost = array_values($ramosPost);

    if (!empty($ramosPost)) {
        $ramosFormulario = $ramosPost;
    }

    $validacion = validarRegistroPlanilla($_POST, $ramosPost, $catalogos, $conn);
    $errores = $validacion['errores'];

    if ($validacion['datos']['rif_consignatario'] !== '') {
        $formulario['rif_consignatario'] = $validacion['datos']['rif_consignatario'];
    }

    if ($validacion['datos']['rif_agente'] !== '') {
        $formulario['rif_agente'] = $validacion['datos']['rif_agente'];
    }

    if (empty($errores)) {
        try {
            registrarPlanilla($conn, $validacion['datos'], $validacion['ramos']);

            header('Location: registro_planilla.php?registrada=1&nro=' . urlencode($validacion['datos']['nro_formulario']));
            exit;
        } catch (Throwable $error) {
            $errores[] = 'No se pudo registrar la planilla. Intenta nuevamente.';
        }
    }
}

$consignatarioSeleccionado = $formulario['rif_consignatario'] !== ''
    ? obtenerContribuyentePlanilla($conn, 'consignatario', $formulario['rif_consignatario'])
    : null;

$agenteSeleccionado = $formulario['rif_agente'] !== ''
    ? obtenerContribuyentePlanilla($conn, 'agente', $formulario['rif_agente'])
    : null;

$montoTotal = 0.0;

foreach ($ramosFormulario as $ramo) {
    $monto = str_replace(',', '.', trim((string) ($ramo['monto'] ?? '')));

    if (is_numeric($monto)) {
        $montoTotal += (float) $monto;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>SISCOIR - Registro de planilla</title>

    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css?v=4">
    <link rel="stylesheet" href="assets/css/registros.css?v=2">
</head>
<body>
    <?php require __DIR__ . '/views/layouts/app-header.php'; ?>

    <main class="registry-main">
        <div class="registry-workspace">
            <section class="registry-hero" aria-labelledby="registry-title">
                <div>
                    <p class="registry-hero__eyebrow">Registros / Planillas</p>

                    <h1 id="registry-title" class="registry-hero__title">
                        Nueva planilla
                    </h1>

                    <p class="registry-hero__text">
                        Registra una planilla emitida con sus datos de declaración, contribuyentes
                        y ramos de ingreso.
                    </p>
                </div>

                <a class="registry-hero__back" href="dashboard.php">
                    Volver al inicio
                </a>
            </section>

            <?php require __DIR__ . '/views/registros/formulario-planilla.php'; ?>
        </div>
    </main>

    <?php require __DIR__ . '/views/layouts/app-footer.php'; ?>

    <script src="assets/js/registro-planilla.js?v=1" defer></script>
</body>
</html>
