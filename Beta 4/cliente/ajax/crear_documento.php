<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? '') !== 'cliente') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
$action = $input['action'] ?? '';
$items = $input['items'] ?? null; // optional; if not provided, use session cart
$descuento_porcentaje = isset($input['descuento_porcentaje']) ? floatval($input['descuento_porcentaje']) : 0.0;
$descuento_porcentaje = max(0.0, min(100.0, $descuento_porcentaje));
$observaciones = isset($input['observaciones']) ? trim((string)$input['observaciones']) : null;
$fecha_entrega = isset($input['fecha_entrega']) ? trim((string)$input['fecha_entrega']) : '';
$direccion_entrega_id = isset($input['direccion_entrega_id']) ? (int)$input['direccion_entrega_id'] : null;
$persona_recibe = isset($input['persona_recibe']) ? trim((string)$input['persona_recibe']) : null;

require_once '../../config/database.php';
$pdo = getConnection();

try {
    // Reglas de fecha para cliente: min = 5 días hábiles; max = +1 mes calendario
    $fechaMinCliente = (function(){
        $d = new DateTime(); $a=0; while($a<5){ $d->modify('+1 day'); $dow=(int)$d->format('N'); if($dow<=5){$a++;} } return $d->format('Y-m-d');
    })();
    $fechaMaxCliente = (new DateTime())->modify('+1 month')->format('Y-m-d');
    if (empty($fecha_entrega) || $fecha_entrega < $fechaMinCliente) { $fecha_entrega = $fechaMinCliente; }
    if ($fecha_entrega > $fechaMaxCliente) { $fecha_entrega = $fechaMaxCliente; }

    if (!in_array($action, ['crear_cotizacion','crear_pedido','confirmar_pedido_existente'], true)) {
        throw new Exception('Acción inválida');
    }

    // Construir mapa producto_id => cantidad
    $map = [];
    if (is_array($items) && count($items) > 0) {
        foreach ($items as $row) {
            $pid = (int)($row['producto_id'] ?? 0);
            $qty = (int)($row['cantidad'] ?? 0);
            if ($pid > 0 && $qty > 0) {
                $map[$pid] = ($map[$pid] ?? 0) + $qty;
            }
        }
    } else {
        // caer al carrito de sesión
        $sessionCart = $_SESSION['carrito'] ?? [];
        if (!is_array($sessionCart) || empty($sessionCart)) {
            throw new Exception('No hay productos en la selección');
        }
        foreach ($sessionCart as $pid => $qty) {
            $pid = (int)$pid; $qty = (int)$qty;
            if ($pid > 0 && $qty > 0) $map[$pid] = $qty;
        }
    }

    if (empty($map)) {
        throw new Exception('Productos/cantidades inválidas');
    }

    // Validar y traer productos
    $ids = array_keys($map);
    $place = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, codigo, descripcion, precio FROM productos WHERE id IN ($place)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) throw new Exception('Productos no encontrados');

    // Aplicar descuentos por grupo del cliente + descuento de encabezado
    $cliente_id = (int)($_SESSION['cliente_id'] ?? 0);
    if ($cliente_id <= 0) throw new Exception('Cliente no identificado');

    // Mapear grupo por producto
    $stmtGrp = $pdo->prepare("SELECT id, grupo_id FROM productos WHERE id IN ($place)");
    $stmtGrp->execute($ids);
    $grpRows = $stmtGrp->fetchAll(PDO::FETCH_ASSOC);
    $mapGrupo = [];
    foreach ($grpRows as $gr) { $mapGrupo[(int)$gr['id']] = (int)$gr['grupo_id']; }

    $discMap = [];
    $stmtDisc = $pdo->prepare("SELECT grupo_id, porcentaje_descuento FROM descuentos_clientes WHERE cliente_id = ? AND activo = 1");
    $stmtDisc->execute([$cliente_id]);
    foreach ($stmtDisc->fetchAll(PDO::FETCH_ASSOC) as $dr) { $discMap[(int)$dr['grupo_id']] = (float)$dr['porcentaje_descuento']; }

    $subtotal = 0.0; $descuento_total = 0.0; $impuestos_total = 0.0;
    $detalle = [];
    $IVA_PCT = 0.19;
    foreach ($rows as $r) {
        $pid = (int)$r['id'];
        $qty = (float)$map[$pid];
        $precio = (float)$r['precio'];
        $grupoId = (int)($mapGrupo[$pid] ?? 0);
        $bruto = $qty * $precio;
        $subtotal += $bruto;
        $grpPct = (float)($discMap[$grupoId] ?? 0.0);
        $grpAmt = $bruto * ($grpPct/100.0);
        $resto = $bruto - $grpAmt;
        $hdrPct = $descuento_porcentaje;
        $hdrAmt = $resto * ($hdrPct/100.0);
        $lineDesc = $grpAmt + $hdrAmt;
        $lineNet = $bruto - $lineDesc;
        $lineIVA = $lineNet * $IVA_PCT;
        $impuestos_total += $lineIVA;
        $descuento_total += $lineDesc;
        $effPct = $bruto > 0 ? round(($lineDesc/$bruto)*100, 4) : 0.0;
        $detalle[] = [
            'producto_id' => $pid,
            'cantidad' => $qty,
            'precio' => $precio,
            'subtotal' => $bruto,
            'desc_pct' => $effPct,
            'desc_monto' => $lineDesc
        ];
    }
    $neto = $subtotal - $descuento_total;
    $iva_pct = 0.19;
    $impuestos_total = $neto * $iva_pct;
    $total = $neto + $impuestos_total;

    // $cliente_id ya calculado arriba

    $pdo->beginTransaction();

    if ($action === 'crear_cotizacion') {
        $numero = 'COT-' . date('Ymd') . '-' . str_pad((string)rand(1,9999), 4, '0', STR_PAD_LEFT);
        
        // Agregar persona_recibe a observaciones si está presente
        $obs_completas = $observaciones;
        if ($persona_recibe) {
            $obs_completas .= ($obs_completas ? "\n" : "") . "Persona que recibe: " . $persona_recibe;
        }
        
    $sql = "INSERT INTO pedidos (numero_documento, cliente_id, empleado_id, estado, fecha_pedido, fecha_entrega_estimada, direccion_entrega_id, subtotal, descuento_porcentaje, descuento_total, impuestos_total, total, observaciones, created_at, updated_at) VALUES (?,?,NULL,'borrador',NOW(),?,?, ?,?,?,?,?,?,NOW(),NOW())";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([$numero, $cliente_id, $fecha_entrega, $direccion_entrega_id, $subtotal, $descuento_porcentaje, $descuento_total, $impuestos_total, $total, $obs_completas]);
        if (!$ok) throw new Exception('No se pudo crear la cotización');
        $pedido_id = (int)$pdo->lastInsertId();

        $stmtDet = $pdo->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario, descuento_porcentaje, descuento_monto, subtotal) VALUES (?,?,?,?,?,?,?)");
        foreach ($detalle as $d) {
            $ok = $stmtDet->execute([$pedido_id, $d['producto_id'], $d['cantidad'], $d['precio'], $d['desc_pct'], $d['desc_monto'], $d['subtotal']]);
            if (!$ok) throw new Exception('No se pudo insertar el detalle');
        }

        $pdo->commit();
        echo json_encode(['success'=>true,'numero_documento'=>$numero,'pedido_id'=>$pedido_id]);
        exit();
    }

    if ($action === 'crear_pedido') {
        $numero = 'PED-' . date('Ymd') . '-' . str_pad((string)rand(1,9999), 4, '0', STR_PAD_LEFT);
        
        // Agregar persona_recibe a observaciones si está presente
        $obs_completas = $observaciones;
        if ($persona_recibe) {
            $obs_completas .= ($obs_completas ? "\n" : "") . "Persona que recibe: " . $persona_recibe;
        }
        
    $sql = "INSERT INTO pedidos (numero_documento, cliente_id, empleado_id, estado, fecha_pedido, fecha_entrega_estimada, direccion_entrega_id, subtotal, descuento_porcentaje, descuento_total, impuestos_total, total, observaciones, created_at, updated_at) VALUES (?,?,NULL,'confirmado',NOW(),?,?, ?,?,?,?,?,?,NOW(),NOW())";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([$numero, $cliente_id, $fecha_entrega, $direccion_entrega_id, $subtotal, $descuento_porcentaje, $descuento_total, $impuestos_total, $total, $obs_completas]);
        if (!$ok) throw new Exception('No se pudo crear el pedido');
        $pedido_id = (int)$pdo->lastInsertId();

        $stmtDet = $pdo->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario, descuento_porcentaje, descuento_monto, subtotal) VALUES (?,?,?,?,?,?,?)");
        foreach ($detalle as $d) {
            $ok = $stmtDet->execute([$pedido_id, $d['producto_id'], $d['cantidad'], $d['precio'], $d['desc_pct'], $d['desc_monto'], $d['subtotal']]);
            if (!$ok) throw new Exception('No se pudo insertar el detalle');
        }

        // Opcional: limpiar carrito virtual
        $_SESSION['carrito'] = [];
        $_SESSION['cart_count'] = 0;

        $pdo->commit();
        echo json_encode(['success'=>true,'numero_documento'=>$numero,'pedido_id'=>$pedido_id]);
        exit();
    }

    // Confirmar/actualizar una cotización existente: reemplaza detalle, actualiza totales, cambia COT->PED y estado
    if ($action === 'confirmar_pedido_existente') {
        $pedido_id = isset($input['pedido_id']) ? (int)$input['pedido_id'] : 0;
        if ($pedido_id <= 0) throw new Exception('Pedido inválido');

        // Verificar pertenencia y estado
        $stmtCheck = $pdo->prepare('SELECT id, numero_documento, estado FROM pedidos WHERE id = ? AND cliente_id = ? LIMIT 1');
        $stmtCheck->execute([$pedido_id, $cliente_id]);
        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        if (!$row) throw new Exception('Cotización no encontrada');
        if ($row['estado'] !== 'borrador') throw new Exception('Solo se pueden confirmar cotizaciones en estado borrador');

        // Build nuevo número manteniendo la parte posterior si viene con prefijo COT-
        $oldNum = (string)$row['numero_documento'];
        if (stripos($oldNum, 'COT-') === 0) {
            $baseNumero = 'PED-' . substr($oldNum, 4);
        } else {
            // si no tiene prefijo, generar nuevo base
            $baseNumero = 'PED-' . date('Ymd') . '-' . str_pad((string)rand(1,9999),4,'0',STR_PAD_LEFT);
        }

        // Asegurar unicidad: si ya existe otro pedido con ese número, añadir sufijo incremental
        $numeroNuevo = $baseNumero;
        $suffix = 0;
        $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM pedidos WHERE numero_documento = ? AND id != ?');
        while (true) {
            $checkStmt->execute([$numeroNuevo, $pedido_id]);
            $cnt = (int)$checkStmt->fetchColumn();
            if ($cnt === 0) break;
            $suffix++;
            $numeroNuevo = $baseNumero . '-' . $suffix;
            // seguridad: no bucle infinito
            if ($suffix > 1000) {
                throw new Exception('No se pudo generar un número de documento único');
            }
        }

        // Actualizar cabecera
        $obs_completas = $observaciones;
        if ($persona_recibe) {
            $obs_completas .= ($obs_completas ? "\n" : "") . "Persona que recibe: " . $persona_recibe;
        }

        $sqlUpd = "UPDATE pedidos SET numero_documento = ?, estado = 'confirmado', fecha_entrega_estimada = ?, direccion_entrega_id = ?, subtotal = ?, descuento_porcentaje = ?, descuento_total = ?, impuestos_total = ?, total = ?, observaciones = ?, updated_at = NOW() WHERE id = ?";
        $stmtUpd = $pdo->prepare($sqlUpd);
        $ok = $stmtUpd->execute([$numeroNuevo, $fecha_entrega, $direccion_entrega_id, $subtotal, $descuento_porcentaje, $descuento_total, $impuestos_total, $total, $obs_completas, $pedido_id]);
        if (!$ok) throw new Exception('No se pudo actualizar la cabecera del pedido');

        // Reemplazar detalle: eliminar existentes e insertar nuevos
        $stmtDel = $pdo->prepare('DELETE FROM detalle_pedidos WHERE pedido_id = ?');
        $stmtDel->execute([$pedido_id]);
        $stmtDet = $pdo->prepare('INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario, descuento_porcentaje, descuento_monto, subtotal) VALUES (?,?,?,?,?,?,?)');
        foreach ($detalle as $d) {
            $ok = $stmtDet->execute([$pedido_id, $d['producto_id'], $d['cantidad'], $d['precio'], $d['desc_pct'], $d['desc_monto'], $d['subtotal']]);
            if (!$ok) throw new Exception('No se pudo insertar el detalle');
        }

        // Limpiar carrito virtual
        $_SESSION['carrito'] = [];
        $_SESSION['cart_count'] = 0;

        $pdo->commit();
        echo json_encode(['success' => true, 'numero_documento' => $numeroNuevo, 'pedido_id' => $pedido_id]);
        exit();
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
