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

try {
    eliminarPlanillaConsulta($conn, $nro);
    header('Location: consulta_planilla.php?ok=eliminada');
    exit;
} catch (Throwable $error) {
    guardarFlashConsulta('consulta_planilla_errores', [
        'No se pudo eliminar la planilla. Intenta nuevamente.',
    ]);
    header('Location: consulta_planilla.php?nro=' . urlencode($nro));
    exit;
}
