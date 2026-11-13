<?php

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/consignatarios.php';
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
$paginaActiva = 'consultas';
$consultaActiva = 'agentes_consignatarios';

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

$tiposEntidad = [
    'consignatario' => [
        'etiqueta' => 'Consignatario',
        'registro' => 'registro_consignatario.php',
    ],
    'agente' => [
        'etiqueta' => 'Agente aduanal',
        'registro' => 'registro_agente.php',
    ],
];

$tipo = strtolower(trim((string) ($_GET['tipo'] ?? 'consignatario')));
$errores = tomarFlashContribuyente('contribuyente_errores') ?? [];

if (!isset($tiposEntidad[$tipo])) {
    $tipo = 'consignatario';
    $errores[] = 'El tipo de contribuyente indicado no es válido.';
}

$rifSolicitado = trim((string) ($_GET['rif'] ?? ''));

if ($rifSolicitado === '' && isset($_GET['rif_prefijo'], $_GET['rif_numero'])) {
    $rifSolicitado = (string) $_GET['rif_prefijo'] . (string) $_GET['rif_numero'];
}

$rif = normalizarRifContribuyente($rifSolicitado);
$buscado = $rifSolicitado !== '';
$registro = null;
$resumen = [
    'total_registros' => 0,
    'total' => 0,
    'monto' => 0.0,
    'pagadas' => 0,
    'pendientes' => 0,
];
$modoEdicion = isset($_GET['editar']) && $_GET['editar'] === '1';
$formularioFlash = tomarFlashContribuyente('contribuyente_formulario');
$formulario = [];
$catalogos = ['tipos' => [], 'ciudades' => []];

$rifPrefijo = strtoupper(substr($rif, 0, 1));
$rifNumero = substr($rif, 1);

if (!preg_match('/^[VEJGPC]$/', $rifPrefijo)) {
    $rifPrefijo = 'J';
    $rifNumero = '';
}

if ($buscado) {
    if (!validarRifContribuyente($rif)) {
        $errores[] = 'El RIF debe tener un prefijo válido y entre 7 y 9 dígitos.';
    } else {
        $registro = $tipo === 'agente'
            ? buscarAgente($conn, $rif)
            : buscarConsignatario($conn, $rif);

        if ($registro !== null) {
            $resumen = obtenerResumenPlanillasContribuyente($conn, $tipo, $rif);
        }
    }
}

if ($modoEdicion) {
    if ($registro === null) {
        $modoEdicion = false;
    } elseif (!$esAdmin) {
        $modoEdicion = false;
        $errores[] = 'No tienes permisos para editar este registro.';
    }
}

if ($modoEdicion) {
    if ($tipo === 'agente') {
        $formularioBase = [
            'rif_agentes' => (string) $registro['rif_agentes'],
            'nombre_agente' => (string) $registro['nombre_agente'],
            'siglas_agente' => (string) $registro['siglas_agente'],
            'representante' => (string) $registro['representante'],
            'telefono' => (string) $registro['telefono'],
            'domicilio_fiscal' => (string) $registro['domicilio_fiscal'],
            'numero_registro_agente_aduanal' => (string) $registro['numero_registro_agente_aduanal'],
        ];
    } else {
        $formularioBase = [
            'rif_consignatario' => (string) $registro['rif_consignatario'],
            'nombre_consignatario' => (string) $registro['nombre_consignatario'],
            'direccion' => (string) $registro['direccion'],
            'telefono' => (string) $registro['telefono'],
            'siglas' => (string) $registro['siglas'],
            'representante' => (string) $registro['representante'],
            'tipo_contribuyente' => (string) $registro['tipo_contribuyente'],
            'cod_ciudad' => (string) $registro['cod_ciudad'],
            'referencia' => (string) $registro['referencia'],
            'zona_postal' => (string) $registro['zona_postal'],
            'cir' => (string) $registro['cir'],
        ];
        $catalogos = obtenerCatalogosConsignatario($conn);
    }

    $formulario = is_array($formularioFlash)
        ? array_merge($formularioBase, $formularioFlash)
        : $formularioBase;
}

$mensajesOk = [
    'registrado' => 'Registro creado correctamente.',
    'actualizado' => 'Registro actualizado correctamente.',
    'eliminado' => 'Registro eliminado correctamente.',
];
$mensajeOk = '';

if (isset($_GET['ok'], $mensajesOk[$_GET['ok']])) {
    $mensajeOk = $mensajesOk[$_GET['ok']];
}

if (isset($_GET['error']) && $_GET['error'] === 'permiso') {
    $errores[] = 'No tienes permisos para realizar esa acción.';
}

$mensajeNoEncontrado = $buscado
    && empty($errores)
    && $registro === null
    && validarRifContribuyente($rif);

$etiquetaTipo = $tiposEntidad[$tipo]['etiqueta'];
$archivoRegistro = $tiposEntidad[$tipo]['registro'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>SISCOIR - Consultar agentes y consignatarios</title>

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
                    <p class="queries-hero__eyebrow">Consultas / Agentes y consignatarios</p>

                    <h1 id="queries-title" class="queries-hero__title">
                        Consultar agente o consignatario
                    </h1>

                    <p class="queries-hero__text">
                        Busca un agente aduanal o un consignatario por su RIF para revisar sus
                        datos y el resumen de planillas asociadas.
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

            <?php require __DIR__ . '/views/consultas/busqueda-agentes-consignatarios.php'; ?>

            <?php if ($mensajeNoEncontrado): ?>
                <section class="queries-alert queries-alert--warning" role="status">
                    <strong>No se encontró un <?= $escapar(strtolower($etiquetaTipo)) ?> con el RIF <?= $escapar($rif) ?>.</strong>
                    <span>Puedes registrarlo con los datos precargados.</span>

                    <div class="queries-alert__actions">
                        <a
                            class="registry-button registry-button--primary"
                            href="<?= $escapar($archivoRegistro) ?>?rif=<?= urlencode($rif) ?>"
                        >
                            Registrar <?= $escapar(strtolower($etiquetaTipo)) ?>
                        </a>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($registro !== null && !$modoEdicion): ?>
                <?php require __DIR__ . '/views/consultas/detalle-contribuyente.php'; ?>
            <?php endif; ?>

            <?php if ($registro !== null && $modoEdicion): ?>
                <?php require __DIR__ . '/views/consultas/formulario-edicion-contribuyente.php'; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php require __DIR__ . '/views/layouts/app-footer.php'; ?>

    <script src="assets/js/consignatarios.js?v=1" defer></script>
</body>
</html>
