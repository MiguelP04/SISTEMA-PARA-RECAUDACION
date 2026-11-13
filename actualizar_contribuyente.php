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

if ($tipo === 'agente') {
    $registro = buscarAgente($conn, $rif);

    if ($registro === null) {
        header('Location: consulta_agentes_consignatarios.php?tipo=agente');
        exit;
    }

    $validacion = validarAgente($conn, $_POST, true, $rif);
} else {
    $registro = buscarConsignatario($conn, $rif);

    if ($registro === null) {
        header('Location: consulta_agentes_consignatarios.php?tipo=consignatario');
        exit;
    }

    $catalogos = obtenerCatalogosConsignatario($conn);
    $validacion = validarConsignatario($conn, $_POST, $catalogos, true, $rif);
}

if (!empty($validacion['errores'])) {
    guardarFlashContribuyente('contribuyente_errores', $validacion['errores']);
    guardarFlashContribuyente('contribuyente_formulario', $_POST);
    header('Location: ' . $urlDetalle . '&editar=1');
    exit;
}

try {
    if ($tipo === 'agente') {
        actualizarAgente($conn, $rif, $validacion['datos']);
    } else {
        actualizarConsignatario($conn, $rif, $validacion['datos']);
    }

    header('Location: ' . $urlDetalle . '&ok=actualizado');
    exit;
} catch (Throwable $error) {
    guardarFlashContribuyente('contribuyente_errores', [
        'No se pudo actualizar el registro. Intenta nuevamente.',
    ]);
    guardarFlashContribuyente('contribuyente_formulario', $_POST);
    header('Location: ' . $urlDetalle . '&editar=1');
    exit;
}
