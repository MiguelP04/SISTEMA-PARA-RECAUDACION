<?php

function esAdminContribuyente(): bool
{
    return ($_SESSION['rol'] ?? '') === 'admin';
}

function normalizarRifContribuyente(string $rif): string
{
    $rif = strtoupper(trim($rif));
    $rif = preg_replace('/\s+/', '', $rif);

    return $rif ?? '';
}

function validarRifContribuyente(string $rif): bool
{
    return preg_match('/^[VEJGPC]\d{7,9}$/', $rif) === 1;
}

function limpiarTextoContribuyente(?string $texto): string
{
    $texto = trim((string) $texto);
    $texto = preg_replace('/\s+/u', ' ', $texto);

    return $texto ?? '';
}

function longitudContribuyente(string $texto): int
{
    return function_exists('mb_strlen')
        ? mb_strlen($texto, 'UTF-8')
        : strlen($texto);
}

function guardarFlashContribuyente(string $clave, array $datos): void
{
    $_SESSION[$clave] = $datos;
}

function tomarFlashContribuyente(string $clave): ?array
{
    if (!isset($_SESSION[$clave]) || !is_array($_SESSION[$clave])) {
        unset($_SESSION[$clave]);

        return null;
    }

    $datos = $_SESSION[$clave];
    unset($_SESSION[$clave]);

    return $datos;
}

function obtenerResumenPlanillasContribuyente(
    mysqli $conn,
    string $tipo,
    string $rif
): array {
    if (!in_array($tipo, ['consignatario', 'agente'], true)) {
        throw new InvalidArgumentException('Tipo de contribuyente no válido.');
    }

    $columna = $tipo === 'agente' ? 'rif_agente' : 'rif_consignatario';
    $sql = "SELECT
                COUNT(*) AS total_registros,
                COALESCE(SUM(CASE WHEN COALESCE(anulada, 0) <> 1 THEN 1 ELSE 0 END), 0) AS total,
                COALESCE(SUM(CASE WHEN COALESCE(anulada, 0) <> 1 THEN monto2 ELSE 0 END), 0) AS monto,
                COALESCE(SUM(CASE
                    WHEN COALESCE(anulada, 0) <> 1
                     AND fecha_cancelacion > '0000-00-00'
                    THEN 1 ELSE 0
                END), 0) AS pagadas,
                COALESCE(SUM(CASE
                    WHEN COALESCE(anulada, 0) <> 1
                     AND (fecha_cancelacion IS NULL OR fecha_cancelacion = '0000-00-00')
                    THEN 1 ELSE 0
                END), 0) AS pendientes
            FROM planillas_emitidas
            WHERE {$columna} = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $rif);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();
    $resultado->free();
    $stmt->close();

    return [
        'total_registros' => (int) ($fila['total_registros'] ?? 0),
        'total' => (int) ($fila['total'] ?? 0),
        'monto' => (float) ($fila['monto'] ?? 0),
        'pagadas' => (int) ($fila['pagadas'] ?? 0),
        'pendientes' => (int) ($fila['pendientes'] ?? 0),
    ];
}

function formatearMontoContribuyente(mixed $monto): string
{
    return number_format((float) $monto, 2, ',', '.');
}
