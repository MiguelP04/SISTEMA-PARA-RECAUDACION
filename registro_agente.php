<?php

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/agentes.php';

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
$registroActivo = 'agentes';

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

$rifInicial = normalizarRifContribuyente((string) ($_GET['rif'] ?? ''));

$formulario = [
    'rif_prefijo' => validarRifContribuyente($rifInicial) ? substr($rifInicial, 0, 1) : 'J',
    'rif_numero' => validarRifContribuyente($rifInicial) ? substr($rifInicial, 1) : '',
    'nombre_agente' => '',
    'siglas_agente' => '',
    'representante' => '',
    'telefono' => '',
    'domicilio_fiscal' => '',
    'numero_registro_agente_aduanal' => '',
];

$campos = array_keys($formulario);
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datosPost = array_intersect_key($_POST, array_flip($campos));
    $formulario = array_merge($formulario, $datosPost);
    $validacion = validarAgente($conn, $_POST, false);
    $errores = $validacion['errores'];

    if (empty($errores)) {
        try {
            insertarAgente($conn, $validacion['datos']);

            header(
                'Location: consulta_agentes_consignatarios.php?tipo=agente&rif='
                . urlencode($validacion['datos']['rif_agentes'])
                . '&ok=registrado'
            );
            exit;
        } catch (Throwable $error) {
            $errores[] = 'No se pudo registrar el agente aduanal. Verifica que el RIF no esté duplicado.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>SISCOIR - Registro de agente aduanal</title>

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
                    <p class="registry-hero__eyebrow">Registros / Agentes aduanales</p>

                    <h1 id="registry-title" class="registry-hero__title">
                        Nuevo agente aduanal
                    </h1>

                    <p class="registry-hero__text">
                        Registra los datos fiscales de un agente aduanal para asociarlo
                        posteriormente a las planillas emitidas.
                    </p>
                </div>

                <a class="registry-hero__back" href="consulta_agentes_consignatarios.php?tipo=agente">
                    Consultar registros
                </a>
            </section>

            <?php require __DIR__ . '/views/registros/formulario-agente.php'; ?>
        </div>
    </main>

    <?php require __DIR__ . '/views/layouts/app-footer.php'; ?>

    <script src="assets/js/consignatarios.js?v=1" defer></script>
</body>
</html>
