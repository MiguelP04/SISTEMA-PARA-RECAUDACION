<?php

require_once __DIR__ . '/registros.php';

function validarNumeroPlanillaConsulta(string $nro): bool
{
    $nro = trim($nro);

    return $nro !== '' && strlen($nro) <= 20 && ctype_digit($nro);
}

function esAdminConsulta(): bool
{
    return ($_SESSION['rol'] ?? '') === 'admin';
}

function guardarFlashConsulta(string $clave, array $datos): void
{
    $_SESSION[$clave] = $datos;
}

function tomarFlashConsulta(string $clave): ?array
{
    if (!isset($_SESSION[$clave]) || !is_array($_SESSION[$clave])) {
        unset($_SESSION[$clave]);

        return null;
    }

    $datos = $_SESSION[$clave];
    unset($_SESSION[$clave]);

    return $datos;
}

function buscarPlanillaConsulta(mysqli $conn, string $nro): ?array
{
    $sql = "SELECT
                p.*,
                (p.anulada + 0) AS anulada_num,
                c.nombre_consignatario,
                ag.nombre_agente,
                a.nombre_de_la_aduana,
                tf.formas AS forma_codigo,
                tf.denominacion AS forma_denominacion,
                tl.descripcion AS tipo_planilla_descripcion,
                b.nombre_banco,
                age.nombre_agencia,
                (SELECT COUNT(*) FROM impuestos_a_pagar i WHERE i.nro_formulario = p.nro_formulario) AS cantidad_ramos,
                (SELECT COALESCE(SUM(i.monto), 0) FROM impuestos_a_pagar i WHERE i.nro_formulario = p.nro_formulario) AS monto_ramos
            FROM planillas_emitidas p
            LEFT JOIN consignatarios c ON c.rif_consignatario = p.rif_consignatario
            LEFT JOIN agentes_aduanales ag ON ag.rif_agentes = p.rif_agente
            LEFT JOIN aduanas a ON a.codigo_aduana = p.codigo_aduana
            LEFT JOIN tformas tf ON tf.codigo_de_forma = p.cod_de_forma
            LEFT JOIN tipo_de_liquidacion tl ON tl.tipo_de_liquidacion = p.tipo_de_planilla
            LEFT JOIN bancos b ON b.cod_banco = p.cod_banco
            LEFT JOIN agencias age ON age.cod_banco = p.cod_banco AND age.cod_agencia = p.cod_agencia
            WHERE p.nro_formulario = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $nro);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();
    $resultado->free();
    $stmt->close();

    return $fila ?: null;
}

function obtenerRamosPlanillaConsulta(mysqli $conn, string $nro): array
{
    $sql = "SELECT
                i.cod_concepto,
                i.cod_forma,
                i.monto,
                ti.descripcion_concepto,
                ti.ramo,
                ti.siglas,
                fi.descripcion AS forma_descripcion
            FROM impuestos_a_pagar i
            LEFT JOIN tipos_de_impuestos ti ON ti.cod_concepto = i.cod_concepto
            LEFT JOIN formas_de_impuestos fi ON fi.cod_forma = i.cod_forma
            WHERE i.nro_formulario = ?
            ORDER BY ti.orden, i.cod_concepto, i.cod_forma";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $nro);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $ramos = [];

    while ($fila = $resultado->fetch_assoc()) {
        $fila['monto'] = (float) $fila['monto'];
        $ramos[] = $fila;
    }

    $resultado->free();
    $stmt->close();

    return $ramos;
}

function obtenerEstadoPlanillaConsulta(array $planilla): array
{
    $anulada = (int) ($planilla['anulada_num'] ?? 0) === 1;
    $pagada = validarFechaPlanilla((string) ($planilla['fecha_cancelacion'] ?? ''));

    if ($anulada) {
        return [
            'clave' => 'anulada',
            'texto' => 'Anulada',
            'modificador' => 'anulada',
        ];
    }

    if ($pagada) {
        return [
            'clave' => 'pagada',
            'texto' => 'Pagada',
            'modificador' => 'pagada',
        ];
    }

    return [
        'clave' => 'pendiente',
        'texto' => 'Pendiente',
        'modificador' => 'pendiente',
    ];
}

function formatearFechaConsulta(?string $fecha): string
{
    $fechaFormateada = formatearFechaPlanilla($fecha);

    return $fechaFormateada !== '' ? $fechaFormateada : '—';
}

function formatearFechaHoraConsulta(?string $fecha): string
{
    if ($fecha === null || trim($fecha) === '' || $fecha === '0000-00-00 00:00:00') {
        return '—';
    }

    $marcaTiempo = strtotime($fecha);

    return $marcaTiempo === false ? '—' : date('d/m/Y H:i', $marcaTiempo);
}

function formatearMontoConsulta(mixed $monto): string
{
    return number_format((float) $monto, 2, ',', '.');
}

function fechaParaInputConsulta(?string $fecha): string
{
    if ($fecha === null || $fecha === '' || $fecha === '0000-00-00' || !validarFechaPlanilla($fecha)) {
        return '';
    }

    return $fecha;
}

function codigoFormaVisibleConsulta(mixed $codigo): string
{
    $mapa = [
        19 => '80',
        20 => '81',
        21 => '86',
    ];

    return $mapa[(int) $codigo] ?? (string) $codigo;
}

function obtenerBancosConsulta(mysqli $conn): array
{
    $resultado = $conn->query('SELECT cod_banco, nombre_banco FROM bancos ORDER BY cod_banco');

    if ($resultado === false) {
        throw new RuntimeException('No se pudieron cargar los bancos.');
    }

    $bancos = [];

    while ($fila = $resultado->fetch_assoc()) {
        $bancos[] = $fila;
    }

    $resultado->free();

    return $bancos;
}

function obtenerAgenciasConsulta(mysqli $conn, int $codBanco): array
{
    $stmt = $conn->prepare(
        'SELECT cod_agencia, nombre_agencia
         FROM agencias
         WHERE cod_banco = ?
         ORDER BY cod_agencia'
    );
    $stmt->bind_param('i', $codBanco);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $agencias = [];

    while ($fila = $resultado->fetch_assoc()) {
        $agencias[] = $fila;
    }

    $resultado->free();
    $stmt->close();

    return $agencias;
}

function validarRecaudacionConsulta(mysqli $conn, array $datos): array
{
    $errores = [];
    $codBanco = filter_var($datos['cod_banco'] ?? '', FILTER_VALIDATE_INT);
    $codAgencia = filter_var($datos['cod_agencia'] ?? '', FILTER_VALIDATE_INT);

    if ($codBanco === false || $codBanco === null) {
        $errores[] = 'Debes seleccionar un banco válido.';
        $codBanco = 0;
    } else {
        $stmt = $conn->prepare('SELECT 1 FROM bancos WHERE cod_banco = ? LIMIT 1');
        $stmt->bind_param('i', $codBanco);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $errores[] = 'El banco seleccionado no existe.';
        }

        $stmt->close();
    }

    if ($codAgencia === false || $codAgencia === null) {
        $errores[] = 'Debes seleccionar una agencia válida.';
        $codAgencia = 0;
    } else {
        $stmt = $conn->prepare(
            'SELECT 1 FROM agencias WHERE cod_banco = ? AND cod_agencia = ? LIMIT 1'
        );
        $stmt->bind_param('ii', $codBanco, $codAgencia);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $errores[] = 'La agencia seleccionada no pertenece al banco indicado.';
        }

        $stmt->close();
    }

    $fechaCancelacion = trim((string) ($datos['fecha_cancelacion'] ?? ''));
    $fechaContabilidad = trim((string) ($datos['fecha_contabilidad_rec'] ?? ''));
    $fechaEntrega = trim((string) ($datos['fecha_de_entreguese'] ?? ''));

    if (!validarFechaPlanilla($fechaCancelacion)) {
        $errores[] = 'Debes indicar una fecha de cancelación válida.';
    }

    if ($fechaContabilidad === '') {
        $fechaContabilidad = $fechaCancelacion;
    }

    if (!validarFechaPlanilla($fechaContabilidad)) {
        $errores[] = 'Debes indicar una fecha de contabilidad válida.';
    }

    if ($fechaEntrega === '') {
        $fechaEntrega = $fechaCancelacion;
    }

    if (!validarFechaPlanilla($fechaEntrega)) {
        $errores[] = 'Debes indicar una fecha de entrega válida.';
    }

    return [
        'errores' => array_values(array_unique($errores)),
        'datos' => [
            'cod_banco' => (int) $codBanco,
            'cod_agencia' => (int) $codAgencia,
            'fecha_cancelacion' => $fechaCancelacion,
            'fecha_contabilidad_rec' => $fechaContabilidad,
            'fecha_de_entreguese' => $fechaEntrega,
        ],
    ];
}

function actualizarRecaudacionConsulta(mysqli $conn, string $nro, array $datos): void
{
    $codBanco = (int) $datos['cod_banco'];
    $codAgencia = (int) $datos['cod_agencia'];
    $fechaCancelacion = (string) $datos['fecha_cancelacion'];
    $fechaContabilidad = (string) $datos['fecha_contabilidad_rec'];
    $fechaEntrega = (string) $datos['fecha_de_entreguese'];

    $stmt = $conn->prepare(
        'UPDATE planillas_emitidas
         SET cod_banco = ?, cod_agencia = ?, fecha_cancelacion = ?, fecha_contabilidad_rec = ?, fecha_de_entreguese = ?
         WHERE nro_formulario = ?'
    );
    $stmt->bind_param(
        'iissss',
        $codBanco,
        $codAgencia,
        $fechaCancelacion,
        $fechaContabilidad,
        $fechaEntrega,
        $nro
    );
    $stmt->execute();
    $stmt->close();
}

function validarEdicionPlanillaConsulta(
    array $datos,
    array $ramos,
    array $catalogos,
    string $nroOriginal,
    bool $editarRamos,
    bool $teniaRamos
): array {
    $errores = [];
    $normalizado = [
        'nro_formulario' => $nroOriginal,
        'codigo_aduana' => trim((string) ($datos['codigo_aduana'] ?? '')),
        'cod_de_forma' => 0,
        'cod_periodo' => '',
        'ano_declaracion' => trim((string) ($datos['ano_declaracion'] ?? '')),
        'nro_declaracion' => trim((string) ($datos['nro_declaracion'] ?? '')),
        'fecha_emision' => trim((string) ($datos['fecha_emision'] ?? '')),
        'fecha_contabilidad_liq' => trim((string) ($datos['fecha_contabilidad_liq'] ?? '')),
        'fecha_de_notificacion' => trim((string) ($datos['fecha_de_notificacion'] ?? '')),
        'fecha_vencimiento' => trim((string) ($datos['fecha_vencimiento'] ?? '')),
        'nro_resolucion' => trim((string) ($datos['nro_resolucion'] ?? '')),
        'nro_liquidacion' => trim((string) ($datos['nro_liquidacion'] ?? '')),
        'tipo_de_planilla' => trim((string) ($datos['tipo_de_planilla'] ?? '')),
        'monto2' => (float) ($datos['monto2'] ?? 0),
    ];

    $aduanas = array_column($catalogos['aduanas'], 'codigo_aduana');

    if (!in_array($normalizado['codigo_aduana'], $aduanas, true)) {
        $errores[] = 'Debes seleccionar una aduana válida.';
    }

    $mapaFormas = [];

    foreach ($catalogos['formas'] as $forma) {
        $mapaFormas[(string) $forma['formas']] = $forma;
    }

    $formaSeleccionada = trim((string) ($datos['cod_de_forma'] ?? ''));

    if (!isset($mapaFormas[$formaSeleccionada])) {
        $errores[] = 'Debes seleccionar una forma válida.';
    } else {
        $normalizado['cod_de_forma'] = (int) $mapaFormas[$formaSeleccionada]['codigo_de_forma'];
        $normalizado['cod_periodo'] = in_array($formaSeleccionada, ['80', '81'], true) ? '08' : '07';
    }

    if (!preg_match('/^\d{4}$/', $normalizado['ano_declaracion'])) {
        $errores[] = 'El año de la declaración debe tener cuatro dígitos.';
    } else {
        $ano = (int) $normalizado['ano_declaracion'];

        if ($ano < 1900 || $ano > 2100) {
            $errores[] = 'El año de la declaración no es válido.';
        }
    }

    if ($normalizado['nro_declaracion'] === '') {
        $errores[] = 'Debes indicar el número de declaración.';
    } elseif (strlen($normalizado['nro_declaracion']) > 22) {
        $errores[] = 'El número de declaración no puede superar los 22 caracteres.';
    }

    foreach ([
        'fecha_emision' => 'fecha de emisión',
        'fecha_contabilidad_liq' => 'fecha de liquidación',
        'fecha_de_notificacion' => 'fecha de notificación',
        'fecha_vencimiento' => 'fecha de vencimiento',
    ] as $campo => $etiqueta) {
        if (!validarFechaPlanilla($normalizado[$campo])) {
            $errores[] = 'Debes indicar una ' . $etiqueta . ' válida.';
        }
    }

    if (
        validarFechaPlanilla($normalizado['fecha_emision'])
        && validarFechaPlanilla($normalizado['fecha_vencimiento'])
        && $normalizado['fecha_vencimiento'] < $normalizado['fecha_emision']
    ) {
        $errores[] = 'La fecha de vencimiento no puede ser anterior a la fecha de emisión.';
    }

    if (strlen($normalizado['nro_resolucion']) > 40) {
        $errores[] = 'El número de resolución no puede superar los 40 caracteres.';
    }

    if ($normalizado['nro_liquidacion'] === '') {
        $errores[] = 'Debes indicar el número de liquidación.';
    } elseif (strlen($normalizado['nro_liquidacion']) > 5) {
        $errores[] = 'El número de liquidación no puede superar los 5 caracteres.';
    }

    $tiposPlanilla = array_column($catalogos['tipos_liquidacion'], 'tipo_de_liquidacion');

    if (!in_array($normalizado['tipo_de_planilla'], $tiposPlanilla, true)) {
        $errores[] = 'Debes seleccionar un tipo de planilla válido.';
    }

    $ramosNormalizados = [];

    if ($editarRamos) {
        $conceptos = array_column($catalogos['tipos_impuesto'], 'cod_concepto');
        $formasImpuesto = array_column($catalogos['formas_impuesto'], 'cod_forma');
        $combinaciones = [];

        foreach ($ramos as $ramo) {
            if (!is_array($ramo)) {
                continue;
            }

            $concepto = trim((string) ($ramo['cod_concepto'] ?? ''));
            $forma = trim((string) ($ramo['cod_forma'] ?? ''));
            $montoTexto = trim((string) ($ramo['monto'] ?? ''));

            if ($concepto === '' && $forma === '' && $montoTexto === '') {
                continue;
            }

            $montoTexto = str_replace(',', '.', $montoTexto);

            if ($concepto === '' || !in_array($concepto, $conceptos, true)) {
                $errores[] = 'Debes seleccionar un ramo de ingreso válido en todos los impuestos.';
            }

            if ($forma === '' || !in_array($forma, $formasImpuesto, true)) {
                $errores[] = 'Debes seleccionar una forma de impuesto válida en todos los ramos.';
            }

            if ($montoTexto === '' || !is_numeric($montoTexto)) {
                $errores[] = 'Todos los ramos deben tener un monto numérico.';
                continue;
            }

            $monto = round((float) $montoTexto, 2);

            if ($monto < 0) {
                $errores[] = 'Los montos de los ramos no pueden ser negativos.';
            }

            $clave = $concepto . '|' . $forma;

            if (isset($combinaciones[$clave])) {
                $errores[] = 'No se puede repetir el mismo ramo con la misma forma de impuesto.';
            }

            $combinaciones[$clave] = true;
            $ramosNormalizados[] = [
                'cod_concepto' => $concepto,
                'cod_forma' => $forma,
                'monto' => $monto,
            ];
        }

        if (empty($ramosNormalizados) && $teniaRamos) {
            $errores[] = 'Debes agregar al menos un ramo de ingreso.';
        }

        if (!empty($ramosNormalizados)) {
            $normalizado['monto2'] = round(array_sum(array_column($ramosNormalizados, 'monto')), 2);
        }
    }

    return [
        'errores' => array_values(array_unique($errores)),
        'datos' => $normalizado,
        'ramos' => $ramosNormalizados,
    ];
}

function actualizarPlanillaConsulta(
    mysqli $conn,
    string $nro,
    array $datos,
    array $ramos,
    bool $editarRamos
): void {
    $codigoAduana = (string) $datos['codigo_aduana'];
    $codDeForma = (int) $datos['cod_de_forma'];
    $codPeriodo = (string) $datos['cod_periodo'];
    $anoDeclaracion = (string) $datos['ano_declaracion'];
    $nroDeclaracion = (string) $datos['nro_declaracion'];
    $fechaEmision = (string) $datos['fecha_emision'];
    $fechaContabilidadLiq = (string) $datos['fecha_contabilidad_liq'];
    $fechaNotificacion = (string) $datos['fecha_de_notificacion'];
    $fechaVencimiento = (string) $datos['fecha_vencimiento'];
    $nroResolucion = (string) $datos['nro_resolucion'];
    $nroLiquidacion = (string) $datos['nro_liquidacion'];
    $tipoPlanilla = (string) $datos['tipo_de_planilla'];
    $monto2 = (float) $datos['monto2'];

    $conn->begin_transaction();

    try {
        $sql = 'UPDATE planillas_emitidas
                SET codigo_aduana = ?, cod_de_forma = ?, cod_periodo = ?,
                    ano_declaracion = ?, nro_declaracion = ?,
                    fecha_emision = ?, fecha_contabilidad_liq = ?, fecha_de_notificacion = ?, fecha_vencimiento = ?,
                    nro_resolucion = ?, nro_liquidacion = ?, tipo_de_planilla = ?, monto2 = ?
                WHERE nro_formulario = ?';

        $stmt = $conn->prepare($sql);
        $tipos = 'si' . str_repeat('s', 10) . 'ds';
        $stmt->bind_param(
            $tipos,
            $codigoAduana,
            $codDeForma,
            $codPeriodo,
            $anoDeclaracion,
            $nroDeclaracion,
            $fechaEmision,
            $fechaContabilidadLiq,
            $fechaNotificacion,
            $fechaVencimiento,
            $nroResolucion,
            $nroLiquidacion,
            $tipoPlanilla,
            $monto2,
            $nro
        );
        $stmt->execute();
        $stmt->close();

        if ($editarRamos) {
            $stmt = $conn->prepare('DELETE FROM impuestos_a_pagar WHERE nro_formulario = ?');
            $stmt->bind_param('s', $nro);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare(
                'INSERT INTO impuestos_a_pagar (nro_formulario, cod_concepto, cod_forma, monto)
                 VALUES (?, ?, ?, ?)'
            );

            foreach ($ramos as $ramo) {
                $concepto = (string) $ramo['cod_concepto'];
                $forma = (string) $ramo['cod_forma'];
                $monto = (float) $ramo['monto'];
                $stmt->bind_param('sssd', $nro, $concepto, $forma, $monto);
                $stmt->execute();
            }

            $stmt->close();
        }

        $conn->commit();
    } catch (Throwable $error) {
        $conn->rollback();
        throw $error;
    }
}

function eliminarPlanillaConsulta(mysqli $conn, string $nro): void
{
    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare('DELETE FROM impuestos_a_pagar WHERE nro_formulario = ?');
        $stmt->bind_param('s', $nro);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare('DELETE FROM planillas_emitidas WHERE nro_formulario = ?');
        $stmt->bind_param('s', $nro);
        $stmt->execute();
        $eliminadas = $stmt->affected_rows;
        $stmt->close();

        if ($eliminadas === 0) {
            throw new RuntimeException('La planilla no existe.');
        }

        $conn->commit();
    } catch (Throwable $error) {
        $conn->rollback();
        throw $error;
    }
}
