<?php

function validarFechaPlanilla(string $fecha): bool
{
    $fecha = trim($fecha);

    if ($fecha === '') {
        return false;
    }

    $fechaObjeto = DateTime::createFromFormat('!Y-m-d', $fecha);
    $errores = DateTime::getLastErrors();

    if ($fechaObjeto === false) {
        return false;
    }

    if ($errores !== false && ($errores['warning_count'] > 0 || $errores['error_count'] > 0)) {
        return false;
    }

    return $fechaObjeto->format('Y-m-d') === $fecha;
}

function formatearFechaPlanilla(?string $fecha): string
{
    if ($fecha === null || $fecha === '' || $fecha === '0000-00-00') {
        return '';
    }

    $marcaTiempo = strtotime($fecha);

    return $marcaTiempo === false ? '' : date('d/m/Y', $marcaTiempo);
}

function normalizarRifPlanilla(string $rif): string
{
    return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', trim($rif)) ?? '');
}

function obtenerFilasPlanilla(mysqli $conn, string $sql): array
{
    $resultado = $conn->query($sql);

    if ($resultado === false) {
        throw new RuntimeException('No se pudieron cargar los datos del formulario.');
    }

    $filas = [];

    while ($fila = $resultado->fetch_assoc()) {
        $filas[] = $fila;
    }

    $resultado->free();

    return $filas;
}

function obtenerCatalogosPlanilla(mysqli $conn): array
{
    return [
        'aduanas' => obtenerFilasPlanilla(
            $conn,
            'SELECT codigo_aduana, nombre_de_la_aduana
             FROM aduanas
             ORDER BY codigo_aduana'
        ),
        'tipos_liquidacion' => obtenerFilasPlanilla(
            $conn,
            "SELECT tipo_de_liquidacion, descripcion
             FROM tipo_de_liquidacion
             WHERE TRIM(tipo_de_liquidacion) <> ''
             ORDER BY tipo_de_liquidacion"
        ),
        'formas' => obtenerFilasPlanilla(
            $conn,
            'SELECT codigo_de_forma, formas, siglas, denominacion
             FROM tformas
             WHERE codigo_de_forma IN (19, 20, 21)
             ORDER BY codigo_de_forma'
        ),
        'formas_impuesto' => obtenerFilasPlanilla(
            $conn,
            "SELECT cod_forma, descripcion
             FROM formas_de_impuestos
             ORDER BY (cod_forma = 'PG') DESC, cod_forma"
        ),
        'tipos_impuesto' => obtenerFilasPlanilla(
            $conn,
            'SELECT cod_concepto, descripcion_concepto
             FROM tipos_de_impuestos
             ORDER BY orden'
        ),
    ];
}

function existeContribuyentePlanilla(mysqli $conn, string $tipo, string $rif): bool
{
    if ($tipo === 'consignatario') {
        $sql = 'SELECT 1 FROM consignatarios WHERE rif_consignatario = ? LIMIT 1';
    } elseif ($tipo === 'agente') {
        $sql = 'SELECT 1 FROM agentes_aduanales WHERE rif_agentes = ? LIMIT 1';
    } else {
        return false;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $rif);
    $stmt->execute();
    $stmt->store_result();
    $existe = $stmt->num_rows > 0;
    $stmt->close();

    return $existe;
}

function obtenerContribuyentePlanilla(mysqli $conn, string $tipo, string $rif): ?array
{
    if ($tipo === 'consignatario') {
        $sql = 'SELECT rif_consignatario AS rif, nombre_consignatario AS nombre
                FROM consignatarios
                WHERE rif_consignatario = ?
                LIMIT 1';
    } elseif ($tipo === 'agente') {
        $sql = 'SELECT rif_agentes AS rif, nombre_agente AS nombre
                FROM agentes_aduanales
                WHERE rif_agentes = ?
                LIMIT 1';
    } else {
        return null;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $rif);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();
    $resultado->free();
    $stmt->close();

    if (!$fila) {
        return null;
    }

    return [
        'rif' => (string) $fila['rif'],
        'nombre' => (string) $fila['nombre'],
    ];
}

function buscarContribuyentesPlanilla(
    mysqli $conn,
    string $tipo,
    string $termino,
    string $letra
): array {
    $termino = trim($termino);
    $letra = strtoupper(trim($letra));
    $longitudTermino = function_exists('mb_strlen') ? mb_strlen($termino) : strlen($termino);

    if ($longitudTermino < 2) {
        return [];
    }

    if (!in_array($letra, ['V', 'E', 'J', 'G', 'C'], true)) {
        $letra = '';
    }

    if ($tipo === 'consignatario') {
        $tabla = 'consignatarios';
        $columnaRif = 'rif_consignatario';
        $columnaNombre = 'nombre_consignatario';
    } elseif ($tipo === 'agente') {
        $tabla = 'agentes_aduanales';
        $columnaRif = 'rif_agentes';
        $columnaNombre = 'nombre_agente';
    } else {
        return [];
    }

    $sql = "SELECT {$columnaRif} AS rif, {$columnaNombre} AS nombre
            FROM {$tabla}
            WHERE TRIM({$columnaRif}) <> ''
              AND ({$columnaRif} LIKE ? OR {$columnaNombre} LIKE ?)";

    if ($letra !== '') {
        $sql .= " AND {$columnaRif} LIKE ?";
    }

    $sql .= " ORDER BY {$columnaRif} ASC LIMIT 12";

    $like = '%' . $termino . '%';
    $likeLetra = $letra . '%';
    $stmt = $conn->prepare($sql);

    if ($letra !== '') {
        $stmt->bind_param('sss', $like, $like, $likeLetra);
    } else {
        $stmt->bind_param('ss', $like, $like);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();
    $contribuyentes = [];

    while ($fila = $resultado->fetch_assoc()) {
        $contribuyentes[] = [
            'rif' => (string) $fila['rif'],
            'nombre' => (string) $fila['nombre'],
        ];
    }

    $resultado->free();
    $stmt->close();

    return $contribuyentes;
}

function validarRegistroPlanilla(
    array $datos,
    array $ramos,
    array $catalogos,
    mysqli $conn
): array {
    $errores = [];

    $normalizado = [
        'nro_formulario' => trim((string) ($datos['nro_formulario'] ?? '')),
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
        'rif_consignatario' => normalizarRifPlanilla((string) ($datos['rif_consignatario'] ?? '')),
        'rif_agente' => normalizarRifPlanilla((string) ($datos['rif_agente'] ?? '')),
        'monto2' => 0.0,
    ];

    if ($normalizado['rif_consignatario'] === '') {
        $normalizado['rif_consignatario'] = normalizarRifPlanilla(
            (string) ($datos['rif_consignatario_tipo'] ?? '')
            . (string) ($datos['rif_consignatario_numero'] ?? '')
        );
    }

    if ($normalizado['rif_agente'] === '') {
        $normalizado['rif_agente'] = normalizarRifPlanilla(
            (string) ($datos['rif_agente_tipo'] ?? '')
            . (string) ($datos['rif_agente_numero'] ?? '')
        );
    }

    $nroFormulario = $normalizado['nro_formulario'];

    if ($nroFormulario === '' || !ctype_digit($nroFormulario)) {
        $errores[] = 'El número de formulario debe contener solo dígitos.';
    } elseif (strlen($nroFormulario) > 10) {
        $errores[] = 'El número de formulario no puede superar los 10 caracteres.';
    } else {
        $stmt = $conn->prepare('SELECT 1 FROM planillas_emitidas WHERE nro_formulario = ? LIMIT 1');
        $stmt->bind_param('s', $nroFormulario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errores[] = 'Ya existe una planilla registrada con ese número de formulario.';
        }

        $stmt->close();
    }

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

    if ($normalizado['rif_consignatario'] === '' || strlen($normalizado['rif_consignatario']) > 15) {
        $errores[] = 'El RIF del consignatario no es válido.';
    } elseif (!existeContribuyentePlanilla($conn, 'consignatario', $normalizado['rif_consignatario'])) {
        $errores[] = 'El consignatario indicado no existe.';
    }

    if ($normalizado['rif_agente'] === '' || strlen($normalizado['rif_agente']) > 15) {
        $errores[] = 'El RIF del agente aduanal no es válido.';
    } elseif (!existeContribuyentePlanilla($conn, 'agente', $normalizado['rif_agente'])) {
        $errores[] = 'El agente aduanal indicado no existe.';
    }

    $conceptos = array_column($catalogos['tipos_impuesto'], 'cod_concepto');
    $formasImpuesto = array_column($catalogos['formas_impuesto'], 'cod_forma');
    $ramosNormalizados = [];
    $combinaciones = [];

    foreach ($ramos as $ramo) {
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

    if (empty($ramosNormalizados)) {
        $errores[] = 'Debes agregar al menos un ramo de ingreso.';
    }

    $normalizado['monto2'] = round(array_sum(array_column($ramosNormalizados, 'monto')), 2);

    return [
        'errores' => array_values(array_unique($errores)),
        'datos' => $normalizado,
        'ramos' => $ramosNormalizados,
    ];
}

function registrarPlanilla(mysqli $conn, array $datos, array $ramos): void
{
    $nroFormulario = (string) $datos['nro_formulario'];
    $codOrgLiq = '01';
    $codPeriodo = (string) $datos['cod_periodo'];
    $codDl = '1116';
    $rifConsignatario = (string) $datos['rif_consignatario'];
    $dias = '0';
    $fechaEmision = (string) $datos['fecha_emision'];
    $fechaCancelacion = '0000-00-00';
    $codBanco = 999;
    $codAgencia = 999;
    $rifAgente = (string) $datos['rif_agente'];
    $nroDeclaracion = (string) $datos['nro_declaracion'];
    $anoDeclaracion = (string) $datos['ano_declaracion'];
    $bl = '';
    $fechaContabilidadLiq = (string) $datos['fecha_contabilidad_liq'];
    $nroLiquidacion = (string) $datos['nro_liquidacion'];
    $codigoAduana = (string) $datos['codigo_aduana'];
    $fechaContabilidadRec = '0000-00-00';
    $tipoDePlanilla = (string) $datos['tipo_de_planilla'];
    $controlDeCopias = '0';
    $monto2 = (float) $datos['monto2'];
    $fechaDeNotificacion = (string) $datos['fecha_de_notificacion'];
    $codDeForma = (int) $datos['cod_de_forma'];
    $fechaDeEntreguese = '0000-00-00';
    $anulada = 0;
    $fechaAnulacion = '0000-00-00';
    $fechaRecalmac = '0000-00-00';
    $valorCif = 0.0;
    $pesoDisp = 0.0;
    $cantidadDispon = '0';
    $fechaSist = date('Y-m-d');
    $horaSist = date('H:i:s');
    $codStatus = 'DP';
    $codNuevo = 'DP';
    $fechaProvidencia = '0000-00-00';
    $valorFob = 0.0;
    $nroResolucion = (string) $datos['nro_resolucion'];
    $tipoForma = '';
    $fechaVencimiento = (string) $datos['fecha_vencimiento'];

    $conn->begin_transaction();

    try {
        $sql = 'INSERT INTO planillas_emitidas (
                    nro_formulario, cod_org_liq, cod_periodo, cod_dl, rif_consignatario,
                    dias, fecha_emision, fecha_cancelacion, cod_banco, cod_agencia,
                    rif_agente, nro_declaracion, ano_declaracion, bl, fecha_contabilidad_liq,
                    nro_liquidacion, codigo_aduana, fecha_contabilidad_rec, tipo_de_planilla, control_de_copias,
                    monto2, fecha_de_notificacion, cod_de_forma, fecha_de_entreguese, anulada,
                    fecha_anulacion, fecha_recalmac, valor_cif, peso_disp, cantidad_dispon,
                    fecha_sist, hora_sist, cod_status, cod_nuevo, fecha_providencia,
                    valor_fob, nro_resolucion, tipo_forma, fecha_vencimiento
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $conn->prepare($sql);
        $tipos = 'ssssssssiissssssssssdsisissddssssssdsss';

        $stmt->bind_param(
            $tipos,
            $nroFormulario,
            $codOrgLiq,
            $codPeriodo,
            $codDl,
            $rifConsignatario,
            $dias,
            $fechaEmision,
            $fechaCancelacion,
            $codBanco,
            $codAgencia,
            $rifAgente,
            $nroDeclaracion,
            $anoDeclaracion,
            $bl,
            $fechaContabilidadLiq,
            $nroLiquidacion,
            $codigoAduana,
            $fechaContabilidadRec,
            $tipoDePlanilla,
            $controlDeCopias,
            $monto2,
            $fechaDeNotificacion,
            $codDeForma,
            $fechaDeEntreguese,
            $anulada,
            $fechaAnulacion,
            $fechaRecalmac,
            $valorCif,
            $pesoDisp,
            $cantidadDispon,
            $fechaSist,
            $horaSist,
            $codStatus,
            $codNuevo,
            $fechaProvidencia,
            $valorFob,
            $nroResolucion,
            $tipoForma,
            $fechaVencimiento
        );

        $stmt->execute();
        $stmt->close();

        $stmtRamo = $conn->prepare(
            'INSERT INTO impuestos_a_pagar (nro_formulario, cod_concepto, cod_forma, monto)
             VALUES (?, ?, ?, ?)'
        );

        foreach ($ramos as $ramo) {
            $concepto = (string) $ramo['cod_concepto'];
            $forma = (string) $ramo['cod_forma'];
            $monto = (float) $ramo['monto'];
            $stmtRamo->bind_param('sssd', $nroFormulario, $concepto, $forma, $monto);
            $stmtRamo->execute();
        }

        $stmtRamo->close();
        $conn->commit();
    } catch (Throwable $error) {
        $conn->rollback();
        throw $error;
    }
}
