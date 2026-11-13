<?php

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/consignatarios.php';
require_once __DIR__ . '/src/agentes.php';

requiereLogin();

$tipo = strtolower(trim((string) ($_POST['tipo'] ?? '')));
$rif = normalizarRifContribuyente((string) ($_POST['rif'] ?? ''));
$tiposValidos = ['consignatario', 'agente'];

if (!in_array($tipo, $tiposValidos, true) || !validarRifContribuyente($rif)) {
    header('Location: consulta_agentes_consignatarios.php');
    exit;
}

$urlDetalle = 'consulta_agentes_consignatarios.php?tipo='
    . urlencode($tipo)
    . '&rif='
    . urlencode($rif);

if (!esAdminContribuyente()) {
    header('Location: ' . $urlDetalle . '&error=permiso');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $urlDetalle);
    exit;
}

$registro = $tipo === 'agente'
    ? buscarAgente($conn, $rif)
    : buscarConsignatario($conn, $rif);

if ($registro === null) {
    header('Location: consulta_agentes_consignatarios.php?tipo=' . urlencode($tipo));
    exit;
}

$resumen = obtenerResumenPlanillasContribuyente($conn, $tipo, $rif);

if ($resumen['total_registros'] > 0) {
    guardarFlashContribuyente('contribuyente_errores', [
        'No se puede eliminar el registro porque tiene planillas asociadas.',
    ]);
    header('Location: ' . $urlDetalle);
    exit;
}

try {
    if ($tipo === 'agente') {
        eliminarAgente($conn, $rif);
    } else {
        eliminarConsignatario($conn, $rif);
    }

    header('Location: consulta_agentes_consignatarios.php?tipo=' . urlencode($tipo) . '&ok=eliminado');
    exit;
} catch (Throwable $error) {
    guardarFlashContribuyente('contribuyente_errores', [
        'No se pudo eliminar el registro. Verifica que no tenga planillas asociadas.',
    ]);
    header('Location: ' . $urlDetalle);
    exit;
}
