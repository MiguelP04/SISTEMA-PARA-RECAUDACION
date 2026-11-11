<?php

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/consultas.php';

requiereLogin();

if (!esAdminConsulta()) {
    header('Location: consulta_planilla.php?error=permiso');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: consulta_planilla.php');
    exit;
}

$nro = trim((string) ($_POST['nro_formulario'] ?? ''));

if (!validarNumeroPlanillaConsulta($nro)) {
    guardarFlashConsulta('consulta_planilla_errores', [
        'El N° de formulario no es válido.',
    ]);
    header('Location: consulta_planilla.php');
    exit;
}

$planilla = buscarPlanillaConsulta($conn, $nro);

if ($planilla === null) {
    header('Location: consulta_planilla.php?error=no_encontrada');
    exit;
}

$campos = [
    'codigo_aduana',
    'cod_de_forma',
    'ano_declaracion',
    'nro_declaracion',
    'fecha_emision',
    'fecha_contabilidad_liq',
    'fecha_de_notificacion',
    'fecha_vencimiento',
    'nro_resolucion',
    'nro_liquidacion',
    'tipo_de_planilla',
];

$datosPost = [];

foreach ($campos as $campo) {
    $datosPost[$campo] = trim((string) ($_POST[$campo] ?? ''));
}

$datosPost['nro_formulario'] = $nro;
$datosPost['monto2'] = (float) ($planilla['monto2'] ?? 0);

$ramosPost = isset($_POST['ramos']) && is_array($_POST['ramos']) ? $_POST['ramos'] : [];

foreach ($ramosPost as $indice => $ramo) {
    if (!is_array($ramo)) {
        unset($ramosPost[$indice]);
    }
}

$ramosPost = array_values($ramosPost);
$editarRamos = strlen($nro) <= 10;

if (!$editarRamos) {
    $ramosPost = [];
}

$catalogos = obtenerCatalogosPlanilla($conn);
$teniaRamos = (int) ($planilla['cantidad_ramos'] ?? 0) > 0;
$validacion = validarEdicionPlanillaConsulta(
    $datosPost,
    $ramosPost,
    $catalogos,
    $nro,
    $editarRamos,
    $teniaRamos
);

if (!empty($validacion['errores'])) {
    guardarFlashConsulta('consulta_planilla_errores', $validacion['errores']);
    guardarFlashConsulta('consulta_planilla_formulario', $datosPost);
    guardarFlashConsulta('consulta_planilla_ramos', $ramosPost);
    header('Location: consulta_planilla.php?nro=' . urlencode($nro) . '&editar=1');
    exit;
}

$formaOriginal = (int) ($planilla['cod_de_forma'] ?? 0);
$formaNueva = (int) ($validacion['datos']['cod_de_forma'] ?? 0);

if ($formaNueva === $formaOriginal) {
    $validacion['datos']['cod_periodo'] = (string) ($planilla['cod_periodo'] ?? '');
}

try {
    actualizarPlanillaConsulta(
        $conn,
        $nro,
        $validacion['datos'],
        $validacion['ramos'],
        $editarRamos
    );

    header('Location: consulta_planilla.php?nro=' . urlencode($nro) . '&ok=actualizada');
    exit;
} catch (Throwable $error) {
    guardarFlashConsulta('consulta_planilla_errores', [
        'No se pudo actualizar la planilla. Intenta nuevamente.',
    ]);
    guardarFlashConsulta('consulta_planilla_formulario', $datosPost);
    guardarFlashConsulta('consulta_planilla_ramos', $ramosPost);
    header('Location: consulta_planilla.php?nro=' . urlencode($nro) . '&editar=1');
    exit;
}
