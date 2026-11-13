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

$nro = trim((string) ($_GET['nro'] ?? $_POST['nro_formulario'] ?? ''));

if (!validarNumeroPlanillaConsulta($nro)) {
    header('Location: consulta_planilla.php?error=no_encontrada');
    exit;
}

$planilla = buscarPlanillaConsulta($conn, $nro);

if ($planilla === null) {
    header('Location: consulta_planilla.php?error=no_encontrada');
    exit;
}

if (validarFechaPlanilla((string) ($planilla['fecha_cancelacion'] ?? ''))) {
    header('Location: editar_recaudacion.php?nro=' . urlencode($nro));
    exit;
}

$modo = 'recaudar';
$bancos = obtenerBancosConsulta($conn);
$agencias = [];
$errores = [];
$formulario = [
    'nro_formulario' => $nro,
    'cod_banco' => '',
    'cod_agencia' => '',
    'fecha_cancelacion' => '',
    'fecha_contabilidad_rec' => '',
    'fecha_de_entreguese' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formulario = [
        'nro_formulario' => $nro,
        'cod_banco' => trim((string) ($_POST['cod_banco'] ?? '')),
        'cod_agencia' => trim((string) ($_POST['cod_agencia'] ?? '')),
        'fecha_cancelacion' => trim((string) ($_POST['fecha_cancelacion'] ?? '')),
        'fecha_contabilidad_rec' => trim((string) ($_POST['fecha_contabilidad_rec'] ?? '')),
        'fecha_de_entreguese' => trim((string) ($_POST['fecha_de_entreguese'] ?? '')),
    ];

    $validacion = validarRecaudacionConsulta($conn, $formulario);
    $errores = $validacion['errores'];

    if (empty($errores)) {
        try {
            actualizarRecaudacionConsulta($conn, $nro, $validacion['datos']);
            header('Location: consulta_planilla.php?nro=' . urlencode($nro) . '&ok=recaudada');
            exit;
        } catch (Throwable $error) {
            $errores[] = 'No se pudo registrar la recaudación. Intenta nuevamente.';
        }
    }
}

if ($formulario['cod_banco'] !== '') {
    $agencias = obtenerAgenciasConsulta($conn, (int) $formulario['cod_banco']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>SISCOIR - Registrar recaudación</title>

    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css?v=4">
    <link rel="stylesheet" href="assets/css/registros.css?v=2">
    <link rel="stylesheet" href="assets/css/consultas.css?v=2">
</head>
<body>
    <?php require __DIR__ . '/views/layouts/app-header.php'; ?>

    <main class="queries-main">
        <div class="queries-workspace">
            <section class="queries-hero" aria-labelledby="collection-title">
                <div>
                    <p class="queries-hero__eyebrow">Consultas / Planillas / Recaudación</p>

                    <h1 id="collection-title" class="queries-hero__title">
                        Registrar recaudación
                    </h1>

                    <p class="queries-hero__text">
                        Planilla N° <?= $escapar($nro) ?> por un monto de
                        Bs <?= $escapar(formatearMontoConsulta($planilla['monto2'] ?? 0)) ?>.
                    </p>
                </div>

                <a class="queries-hero__back" href="consulta_planilla.php?nro=<?= urlencode($nro) ?>">
                    Volver a la planilla
                </a>
            </section>

            <?php if (!empty($errores)): ?>
                <section class="queries-alert queries-alert--error" role="alert">
                    <strong>Revisa los datos antes de guardar:</strong>

                    <ul>
                        <?php foreach ($errores as $error): ?>
                            <li><?= $escapar($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>

            <?php require __DIR__ . '/views/consultas/formulario-recaudacion.php'; ?>
        </div>
    </main>

    <?php require __DIR__ . '/views/layouts/app-footer.php'; ?>

    <script src="assets/js/consultas.js?v=1" defer></script>
</body>
</html>
