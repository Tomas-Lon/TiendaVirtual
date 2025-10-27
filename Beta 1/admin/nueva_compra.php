<?php
session_start();
require_once '../includes/LayoutManager.php';
require_once '../config/database.php';

// Verificar autenticación
if (!isset($_SESSION['empleado_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$pdo = getConnection();

// Calcular fecha de entrega: exactamente 5 días hábiles desde hoy (sin festivos)
function fechaEntrega5Habiles() {
    $date = new DateTime();
    $added = 0;
    while ($added < 5) {
        $date->modify('+1 day');
        $dow = (int)$date->format('N'); // 1=lunes .. 7=domingo
        if ($dow <= 5) { // lunes-viernes
            $added++;
        }
    }
    return $date->format('Y-m-d');
}
$fechaEntregaPermitida = fechaEntrega5Habiles();
// Para admin: fecha mínima permitida es mañana (puede elegir cualquier fecha a partir de entonces)
$fechaMinimaAdmin = (new DateTime())->modify('+1 day')->format('Y-m-d');

// Clase para manejar el carrito de compras
class CarritoManager {
    public static function procesarPedido($pdo, $productos, $cliente_id, $descuento = 0) {
        try {
            $pdo->beginTransaction();
            
            // Crear el pedido principal acorde al esquema (pedidos)
            $numero_documento = 'PED-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $subtotal = array_sum(array_column($productos, 'subtotal'));
            $descuento_total = floatval($descuento);
            $descuento_porcentaje = $subtotal > 0 ? round(($descuento_total / $subtotal) * 100, 2) : 0.0;
            $total_neto = $subtotal - $descuento_total;
            $impuestos_total = $total_neto * 0.16; // Ajustar si IVA es 19%
            $total_con_iva = $total_neto + $impuestos_total;

            $sql_pedido = "INSERT INTO pedidos (
                                numero_documento, cliente_id, empleado_id, estado,
                                fecha_pedido, subtotal, descuento_porcentaje, descuento_total, impuestos_total, total,
                                observaciones, created_at, updated_at
                            ) VALUES (
                                ?, ?, ?, 'confirmado', NOW(), ?, ?, ?, ?, ?, NULL, NOW(), NOW()
                            )";
            $stmt_pedido = $pdo->prepare($sql_pedido);
            $stmt_pedido->execute([
                $numero_documento,
                $cliente_id,
                isset($_SESSION['empleado_id']) ? $_SESSION['empleado_id'] : null,
                $subtotal,
                $descuento_porcentaje,
                $descuento_total,
                $impuestos_total,
                $total_con_iva
            ]);
            $pedido_id = $pdo->lastInsertId();
            
            // Insertar detalles del pedido
            foreach ($productos as $producto) {
                $sql_detalle = "INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario, subtotal) 
                               VALUES (?, ?, ?, ?, ?)";
                $stmt_detalle = $pdo->prepare($sql_detalle);
                $stmt_detalle->execute([
                    $pedido_id,
                    intval($producto['id']),
                    intval($producto['cantidad']),
                    floatval($producto['precio']),
                    floatval($producto['subtotal'])
                ]);
            }
            
            $pdo->commit();
            return ['success' => true, 'pedido_id' => $pedido_id];
            
        } catch (Exception $e) {
            $pdo->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

// Funciones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    try {
    switch ($_POST['action']) {
            case 'buscar_productos':
                $search = trim($_POST['search'] ?? '');
                $grupo_id = $_POST['grupo_id'] ?? '';
                
                // Si no hay criterios de búsqueda, devolver productos limitados
                if (empty($search) && empty($grupo_id)) {
                    $sql = "SELECT p.id, p.codigo, p.descripcion, p.precio, 
                                   0 as stock_disponible,
                                   g.nombre as grupo_nombre
                            FROM productos p 
                            LEFT JOIN grupos_productos g ON p.grupo_id = g.id
                            ORDER BY p.descripcion 
                            LIMIT 20";
                    $params = [];
                } else {
                    $where_conditions = [];
                    $params = [];
                    
                    if (!empty($search)) {
                        $where_conditions[] = "(p.codigo LIKE ? OR p.descripcion LIKE ?)";
                        $params[] = "%{$search}%";
                        $params[] = "%{$search}%";
                    }
                    
                    if (!empty($grupo_id)) {
                        $where_conditions[] = "p.grupo_id = ?";
                        $params[] = $grupo_id;
                    }
                    
                    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
                    
                    $sql = "SELECT p.id, p.codigo, p.descripcion, p.precio, 
                                   0 as stock_disponible,
                                   g.nombre as grupo_nombre
                            FROM productos p 
                            LEFT JOIN grupos_productos g ON p.grupo_id = g.id
                            {$where_clause}
                            ORDER BY p.descripcion 
                            LIMIT 50";
                }
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $response = ['success' => true, 'productos' => $productos];
                break;

            case 'get_direcciones_cliente':
                $clienteId = intval($_POST['cliente_id'] ?? 0);
                if (!$clienteId) { throw new Exception('Cliente inválido'); }
                $stmt = $pdo->prepare("SELECT id, direccion, ciudad, departamento, codigo_postal FROM direcciones_clientes WHERE cliente_id = ? ORDER BY id DESC");
                $stmt->execute([$clienteId]);
                $dirs = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
                $response = ['success' => true, 'direcciones' => $dirs];
                break;
                
            case 'crear_pedido':
                $cliente_id = intval($_POST['cliente_id'] ?? 0);
                $productos_json = $_POST['productos'] ?? '';
                $posted_subtotal = floatval($_POST['subtotal'] ?? 0);
                $descuento_porcentaje = max(0.0, min(100.0, floatval($_POST['descuento_porcentaje'] ?? 0)));
                $observaciones = trim($_POST['observaciones'] ?? '');
                $fecha_entrega = $_POST['fecha_entrega'] ?? null;
                $metodo_pago = $_POST['metodo_pago'] ?? 'contado';
                $direccion_entrega_id = intval($_POST['direccion_entrega_id'] ?? 0);
                $persona_recibe = trim($_POST['persona_recibe'] ?? '');
                
                $productos = json_decode($productos_json, true);
                
                if (!$cliente_id || empty($productos)) {
                    throw new Exception('Datos incompletos para crear el pedido');
                }
                
                // Verificar sesión de empleado
                if (!isset($_SESSION['empleado_id'])) {
                    throw new Exception('Sesión de empleado requerida');
                }
                
                // Admin: Enforce minimum delivery date = tomorrow; allow any later date
                $fecha_minima_admin = (new DateTime())->modify('+1 day')->format('Y-m-d');
                if (empty($fecha_entrega) || $fecha_entrega < $fecha_minima_admin) {
                    $fecha_entrega = $fecha_minima_admin;
                }

                $pdo->beginTransaction();
                
                // Generar número único
                $numero_documento = 'PED-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                
                // Preparar datos de productos desde DB (precio y grupo), y descuentos por grupo del cliente
                $ids = array_map(function($p){ return intval($p['id']); }, $productos);
                $ids = array_values(array_unique(array_filter($ids)));
                if (empty($ids)) throw new Exception('Productos inválidos');

                $in = implode(',', array_fill(0, count($ids), '?'));
                $stmtProd = $pdo->prepare("SELECT id, precio, grupo_id FROM productos WHERE id IN ($in)");
                $stmtProd->execute($ids);
                $prodsDb = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
                $mapProd = [];
                foreach ($prodsDb as $r) { $mapProd[intval($r['id'])] = ['precio'=>floatval($r['precio']), 'grupo_id'=>intval($r['grupo_id'])]; }

                $stmtDisc = $pdo->prepare("SELECT grupo_id, porcentaje_descuento FROM descuentos_clientes WHERE cliente_id = ? AND activo = 1");
                $stmtDisc->execute([$cliente_id]);
                $discRows = $stmtDisc->fetchAll(PDO::FETCH_ASSOC);
                $discMap = [];
                foreach ($discRows as $d) { $discMap[intval($d['grupo_id'])] = floatval($d['porcentaje_descuento']); }

                // Calcular totales aplicando descuento por grupo y luego descuento de cabecera
                $subtotal = 0.0; $descuento_total = 0.0; $impuestos_total = 0.0; $detalle_calc = [];
                $IVA_PCT = 0.16;
                foreach ($productos as $prod) {
                    $pid = intval($prod['id']);
                    $qty = max(1, intval($prod['cantidad'] ?? 0));
                    if (!isset($mapProd[$pid])) throw new Exception('Producto no encontrado: '.$pid);
                    $precioUnit = $mapProd[$pid]['precio'];
                    $grupoId = $mapProd[$pid]['grupo_id'];
                    $bruto = $qty * $precioUnit;
                    $subtotal += $bruto;
                    $grpPct = $discMap[$grupoId] ?? 0.0;
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
                    $detalle_calc[] = [
                        'id'=>$pid,
                        'cantidad'=>$qty,
                        'precio'=>$precioUnit,
                        'bruto'=>$bruto,
                        'desc_pct'=>$effPct,
                        'desc_monto'=>$lineDesc,
                        'neto'=>$lineNet,
                        'iva_monto'=>$lineIVA,
                        'grp_pct'=>$grpPct,
                        'hdr_pct'=>$hdrPct
                    ];
                }
                $total_neto = $subtotal - $descuento_total;
                $total_con_iva = $total_neto + $impuestos_total;
                
        // Crear pedido (estado válido según esquema: borrador|confirmado|...)
    $sql = "INSERT INTO pedidos (numero_documento, cliente_id, empleado_id, estado, 
                       fecha_pedido, fecha_entrega_estimada, subtotal, 
                       descuento_porcentaje, descuento_total, impuestos_total, total, observaciones, 
                       created_at, updated_at) 
            VALUES (?, ?, ?, 'confirmado', NOW(), ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                
                $stmt = $pdo->prepare($sql);
                // Componer observaciones con persona que recibe si fue indicada
                if ($persona_recibe !== '') {
                    $observaciones = "Recibe: " . $persona_recibe . (strlen($observaciones) ? ("\n" . $observaciones) : '');
                }

                $sql = "INSERT INTO pedidos (numero_documento, cliente_id, empleado_id, estado, 
                       fecha_pedido, fecha_entrega_estimada, subtotal, 
                       descuento_porcentaje, descuento_total, impuestos_total, total, direccion_entrega_id, observaciones, 
                       created_at, updated_at) 
            VALUES (?, ?, ?, 'confirmado', NOW(), ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([
                    $numero_documento, $cliente_id, $_SESSION['empleado_id'],
                    $fecha_entrega, $subtotal, $descuento_porcentaje, $descuento_total, $impuestos_total,
                    $total_con_iva, $direccion_entrega_id ?: null, $observaciones
                ]);
                
                if (!$result) {
                    throw new Exception('Error al insertar pedido: ' . implode(', ', $stmt->errorInfo()));
                }
                
                $pedido_id = $pdo->lastInsertId();
                
                // Insertar detalles en la tabla correcta (detalle_pedidos) con descuentos
                $sql_detalle = "INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario, descuento_porcentaje, descuento_monto, subtotal) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt_detalle = $pdo->prepare($sql_detalle);
                foreach ($detalle_calc as $dl) {
                    $result_detalle = $stmt_detalle->execute([
                        $pedido_id,
                        $dl['id'],
                        $dl['cantidad'],
                        $dl['precio'],
                        $dl['desc_pct'],
                        $dl['desc_monto'],
                        $dl['bruto']
                    ]);
                    
                    if (!$result_detalle) {
                        throw new Exception('Error al insertar detalle del pedido');
                    }
                }

                // Crear Orden de Venta asociada al pedido
                // Mapear método de pago UI -> enum de DB
                $metodo_pago_db = 'efectivo';
                switch ($metodo_pago) {
                    case 'contado':
                        $metodo_pago_db = 'efectivo';
                        break;
                    case 'transferencia':
                        $metodo_pago_db = 'transferencia';
                        break;
                    case 'credito_30':
                    case 'credito_60':
                        $metodo_pago_db = 'credito';
                        break;
                    default:
                        $metodo_pago_db = 'efectivo';
                }

                // Calcular fecha de vencimiento según método
                $fecha_vencimiento = null;
                if (!empty($fecha_entrega)) {
                    // si viene fecha de entrega podríamos usarla como base; normalmente se usa fecha_emision
                }
                if ($metodo_pago === 'credito_30') {
                    $fecha_vencimiento = (new DateTime())->modify('+30 days')->format('Y-m-d');
                } elseif ($metodo_pago === 'credito_60') {
                    $fecha_vencimiento = (new DateTime())->modify('+60 days')->format('Y-m-d');
                } else {
                    // contado/transferencia: una semana opcional o NULL
                    $fecha_vencimiento = (new DateTime())->modify('+7 days')->format('Y-m-d');
                }

                $sql_ov = "INSERT INTO orden_venta (
                                numero_documento, pedido_id, cliente_id, empleado_id,
                                fecha_emision, fecha_vencimiento,
                                subtotal, descuento_total, impuestos_total, total,
                                estado, metodo_pago, direccion_facturacion_id, observaciones,
                                created_at, updated_at
                           ) VALUES (
                                ?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, 'pendiente', ?, NULL, ?, NOW(), NOW()
                           )";
                $stmt_ov = $pdo->prepare($sql_ov);
                $result_ov = $stmt_ov->execute([
                    $numero_documento,
                    $pedido_id,
                    $cliente_id,
                    $_SESSION['empleado_id'],
                    $fecha_vencimiento,
                    $subtotal, // Subtotal bruto (sin descuento)
                    $descuento_total,
                    $impuestos_total,
                    $total_con_iva,
                    $metodo_pago_db,
                    $observaciones
                ]);

                if (!$result_ov) {
                    throw new Exception('Error al crear la orden de venta');
                }

                $orden_venta_id = $pdo->lastInsertId();

                // Insertar detalle de la orden de venta
                $sql_dov = "INSERT INTO detalle_orden_venta (
                                orden_venta_id, producto_id, cantidad, precio_unitario,
                                descuento_porcentaje, descuento_monto,
                                impuesto_porcentaje, impuesto_monto, subtotal
                            ) VALUES (
                                ?, ?, ?, ?, ?, ?, ?, ?, ?
                            )";
                $stmt_dov = $pdo->prepare($sql_dov);

                foreach ($detalle_calc as $dl) {
                    $cantidad = $dl['cantidad'];
                    $precio_unit = $dl['precio'];
                    $bruto = $dl['bruto'];
                    $desc_pct = $dl['desc_pct'];
                    $desc_monto = $dl['desc_monto'];
                    $iva_pct = 16.0;
                    $iva_monto = $dl['iva_monto'];

                    $ok = $stmt_dov->execute([
                        $orden_venta_id,
                        $dl['id'],
                        $cantidad,
                        $precio_unit,
                        $desc_pct,
                        $desc_monto,
                        $iva_pct,
                        $iva_monto,
                        $bruto
                    ]);

                    if (!$ok) {
                        throw new Exception('Error al insertar detalle de la orden de venta');
                    }
                }
                
                $pdo->commit();
                
                $response = [
                    'success' => true, 
                    'message' => 'Pedido y orden de venta creados exitosamente',
                    'pedido_id' => $pedido_id,
                    'numero_documento' => $numero_documento
                ];
                break;

            case 'crear_cotizacion':
                // Crear una cotización (usa la tabla pedidos con estado borrador)
                $cliente_id = intval($_POST['cliente_id'] ?? 0);
                $productos_json = $_POST['productos'] ?? '';
                $posted_subtotal = floatval($_POST['subtotal'] ?? 0);
                $descuento_porcentaje = max(0.0, min(100.0, floatval($_POST['descuento_porcentaje'] ?? 0)));
                $observaciones = trim($_POST['observaciones'] ?? '');
                $direccion_entrega_id = intval($_POST['direccion_entrega_id'] ?? 0);
                $persona_recibe = trim($_POST['persona_recibe'] ?? '');

                $productos = json_decode($productos_json, true);

                if (!$cliente_id || empty($productos)) {
                    throw new Exception('Datos incompletos para crear la cotización');
                }

                if (!isset($_SESSION['empleado_id'])) {
                    throw new Exception('Sesión de empleado requerida');
                }

                $pdo->beginTransaction();

                // Número de cotización
                $numero_documento = 'COT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

                // Preparar datos de productos y descuentos por grupo
                $ids = array_map(function($p){ return intval($p['id']); }, $productos);
                $ids = array_values(array_unique(array_filter($ids)));
                if (empty($ids)) throw new Exception('Productos inválidos');

                $in = implode(',', array_fill(0, count($ids), '?'));
                $stmtProd = $pdo->prepare("SELECT id, precio, grupo_id FROM productos WHERE id IN ($in)");
                $stmtProd->execute($ids);
                $prodsDb = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
                $mapProd = [];
                foreach ($prodsDb as $r) { $mapProd[intval($r['id'])] = ['precio'=>floatval($r['precio']), 'grupo_id'=>intval($r['grupo_id'])]; }

                $stmtDisc = $pdo->prepare("SELECT grupo_id, porcentaje_descuento FROM descuentos_clientes WHERE cliente_id = ? AND activo = 1");
                $stmtDisc->execute([$cliente_id]);
                $discRows = $stmtDisc->fetchAll(PDO::FETCH_ASSOC);
                $discMap = [];
                foreach ($discRows as $d) { $discMap[intval($d['grupo_id'])] = floatval($d['porcentaje_descuento']); }

                $subtotal = 0.0; $descuento_total = 0.0; $impuestos_total = 0.0; $detalle_calc = [];
                $IVA_PCT = 0.16;
                foreach ($productos as $prod) {
                    $pid = intval($prod['id']);
                    $qty = max(1, intval($prod['cantidad'] ?? 0));
                    if (!isset($mapProd[$pid])) throw new Exception('Producto no encontrado: '.$pid);
                    $precioUnit = $mapProd[$pid]['precio'];
                    $grupoId = $mapProd[$pid]['grupo_id'];
                    $bruto = $qty * $precioUnit;
                    $subtotal += $bruto;
                    $grpPct = $discMap[$grupoId] ?? 0.0;
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
                    $detalle_calc[] = [
                        'id'=>$pid,
                        'cantidad'=>$qty,
                        'precio'=>$precioUnit,
                        'bruto'=>$bruto,
                        'desc_pct'=>$effPct,
                        'desc_monto'=>$lineDesc
                    ];
                }
                $total_neto = $subtotal - $descuento_total;
                $total_con_iva = $total_neto + $impuestos_total;

                // Insertar cabecera como pedido en estado borrador (representa la cotización)
                if ($persona_recibe !== '') {
                    $observaciones = "Recibe: " . $persona_recibe . (strlen($observaciones) ? ("\n" . $observaciones) : '');
                }

                $sql_cot = "INSERT INTO pedidos (
                                numero_documento, cliente_id, empleado_id, estado,
                                fecha_pedido, fecha_entrega_estimada, subtotal,
                                descuento_porcentaje, descuento_total, impuestos_total, total,
                                direccion_entrega_id, observaciones, pedido_template, nombre_template,
                                created_at, updated_at
                            ) VALUES (
                                ?, ?, ?, 'borrador', NOW(), NULL, ?, ?, ?, ?, ?, ?, ?, 0, NULL, NOW(), NOW()
                            )";
                $stmt_cot = $pdo->prepare($sql_cot);
                $ok_head = $stmt_cot->execute([
                    $numero_documento,
                    $cliente_id,
                    $_SESSION['empleado_id'],
                    $subtotal,
                    $descuento_porcentaje,
                    $descuento_total,
                    $impuestos_total,
                    $total_con_iva,
                    $direccion_entrega_id ?: null,
                    $observaciones
                ]);
                if (!$ok_head) {
                    throw new Exception('Error al crear cabecera de cotización');
                }

                $cotizacion_id = $pdo->lastInsertId();

                // Detalle de la cotización (detalle_pedidos)
                $sql_detalle = "INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario, descuento_porcentaje, descuento_monto, subtotal)
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt_det = $pdo->prepare($sql_detalle);
                foreach ($detalle_calc as $dl) {
                    $ok_det = $stmt_det->execute([
                        $cotizacion_id,
                        $dl['id'],
                        $dl['cantidad'],
                        $dl['precio'],
                        $dl['desc_pct'],
                        $dl['desc_monto'],
                        $dl['bruto']
                    ]);
                    if (!$ok_det) {
                        throw new Exception('Error al insertar detalle de la cotización');
                    }
                }

                $pdo->commit();
                $response = [
                    'success' => true,
                    'message' => 'Cotización creada exitosamente',
                    'cotizacion_id' => $cotizacion_id,
                    'numero_documento' => $numero_documento
                ];
                break;
                
            default:
                throw new Exception('Acción no válida');
        }
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        $response = ['success' => false, 'message' => $e->getMessage()];
    }
    
    echo json_encode($response);
    exit;
}

// Obtener datos para el formulario
try {
    $clientes_stmt = $pdo->query("SELECT id, nombre, email FROM clientes WHERE activo = 1 ORDER BY nombre");
    $clientes = $clientes_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $grupos_stmt = $pdo->query("SELECT id, nombre FROM grupos_productos WHERE activo = 1 ORDER BY nombre");
    $grupos = $grupos_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error al cargar datos: " . $e->getMessage());
}

// Contenido de la página
ob_start();
?>

<div class="row">
    <!-- Panel principal -->
    <div class="col-md-8">
        <!-- Información del Cliente -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-user"></i> Información del Cliente</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="cliente_id" class="form-label fw-semibold">Cliente *</label>
                        <select class="form-select" id="cliente_id" required>
                            <option value="">Seleccione un cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= $cliente['id'] ?>" data-email="<?= htmlspecialchars($cliente['email']) ?>">
                                    <?= htmlspecialchars($cliente['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="cliente_email" class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" id="cliente_email" readonly>
                    </div>
                    <div class="col-md-8">
                        <label for="direccion_entrega" class="form-label fw-semibold">Dirección de Entrega *</label>
                        <select class="form-select" id="direccion_entrega">
                            <option value="">Seleccione una dirección</option>
                        </select>
                        <small class="text-muted">Se cargan las direcciones del cliente seleccionado.</small>
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_entrega" class="form-label fw-semibold">Fecha de Entrega</label>
                        <input type="date" class="form-control" id="fecha_entrega" value="<?= $fechaMinimaAdmin ?>" min="<?= $fechaMinimaAdmin ?>">
                        <small class="text-muted">Disponible desde: <?= date('d/m/Y', strtotime($fechaMinimaAdmin)) ?></small>
                    </div>
                    <div class="col-md-6">
                        <label for="persona_recibe" class="form-label fw-semibold">Persona que recibe</label>
                        <input type="text" class="form-control" id="persona_recibe" placeholder="Nombre de quien recibe el pedido">
                    </div>
                </div>
            </div>
        </div>

        <!-- Búsqueda de Productos y carga vía CSV -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-search"></i> Búsqueda / Importación de Productos</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="search_producto" class="form-label fw-semibold">Buscar</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                            <input type="text" class="form-control" id="search_producto" placeholder="Código o descripción">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="filtro_grupo" class="form-label fw-semibold">Grupo</label>
                        <select class="form-select" id="filtro_grupo">
                            <option value="">Todos</option>
                            <?php foreach ($grupos as $grupo): ?>
                                <option value="<?= $grupo['id'] ?>"><?= htmlspecialchars($grupo['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="cantidad" class="form-label fw-semibold">Cantidad</label>
                        <input type="number" class="form-control form-control-sm text-center" id="cantidad" value="1" min="1">
                    </div>
                </div>

                <div class="row g-3 align-items-end mt-2">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Importar desde CSV</label>
                        <form action="ajax/procesar_csv.php" class="dropzone border-2 border-dashed p-3 text-center" id="dzCsvAdmin">
                            <div class="dz-message text-muted">
                                <i class="fas fa-file-csv"></i> Suelta el archivo CSV aquí o haz clic
                            </div>
                        </form>
                        <small class="text-muted">Formato: código,cantidad. Máx 2MB. Separador , o ;</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Agregar por código</label>
                        <div class="input-group">
                            <input type="text" id="addByCodeInputAdmin" class="form-control" placeholder="Código exacto">
                            <input type="number" id="addByCodeQtyAdmin" class="form-control" style="max-width:110px" value="1" min="1">
                            <button class="btn btn-outline-primary" type="button" id="addByCodeBtnAdmin"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </div>
                
                <!-- Resultados de búsqueda -->
                <div id="productos_encontrados" class="mt-3"></div>
            </div>
        </div>

        <!-- Carrito de Productos -->
        <div class="card carrito-card shadow-sm">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Productos en el Pedido</h5>
                <button type="button" class="btn btn-light btn-sm" onclick="limpiarCarrito()" id="btn_limpiar" style="display:none;">
                    <i class="fas fa-trash text-danger"></i> Limpiar
                </button>
            </div>
            <div class="card-body">
                <div id="carrito_productos">
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                        <p class="mb-0">No hay productos agregados</p>
                        <small>Use el buscador para agregar productos</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Resumen del Pedido -->
    <div class="col-md-4">
        <div class="card sticky-top resumen-card shadow">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-calculator"></i> Resumen del Pedido</h5>
            </div>
            <div class="card-body">
                <!-- Estadísticas -->
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <div class="h5 mb-0 text-primary" id="total_productos">0</div>
                            <small class="text-muted">Productos</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <div class="h5 mb-0 text-info" id="cantidad_total">0</div>
                            <small class="text-muted">Cantidad</small>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Cálculos financieros -->
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <strong id="subtotal" class="text-primary">$0.00</strong>
                </div>
                
                <div class="d-flex justify-content-between mb-2">
                    <span>Descuento:</span>
                    <div class="input-group input-group-sm" style="width: 120px;">
                        <input type="number" class="form-control" id="descuento_porcentaje" value="0" min="0" max="100" onchange="calcularTotales()">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total:</strong>
                    <strong class="text-success fs-4" id="total_pagar">$0.00</strong>
                </div>

                <!-- Información de impuestos -->
                <div class="alert alert-info p-2 mb-3">
                    <small>
                        <div class="d-flex justify-content-between">
                            <span>IVA (16%):</span>
                            <span id="iva_total">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Base gravable:</span>
                            <span id="base_gravable">$0.00</span>
                        </div>
                    </small>
                </div>

                <!-- Campos adicionales -->
                <div class="mb-3">
                    <label for="observaciones" class="form-label fw-semibold">Observaciones</label>
                    <textarea class="form-control" id="observaciones" rows="3" placeholder="Notas del pedido..."></textarea>
                </div>

                <!-- Fecha de entrega movida al bloque de información del cliente -->

                <div class="mb-3">
                    <label for="metodo_pago" class="form-label fw-semibold">Método de Pago</label>
                    <select class="form-select" id="metodo_pago">
                        <option value="contado">Contado</option>
                        <option value="credito_30">Crédito 30 días</option>
                        <option value="credito_60">Crédito 60 días</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                </div>

                <!-- Botones de acción -->
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <button type="button" class="btn btn-outline-info w-100" onclick="previsualizarPedido()">
                            <i class="fas fa-eye"></i> Vista Previa
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-outline-warning w-100" onclick="imprimirPedido()">
                            <i class="fas la-print"></i> Imprimir
                        </button>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-secondary" onclick="crearCotizacion()" id="btn_cotizacion" disabled
                            title="Seleccione un cliente y agregue productos para habilitar">
                        <i class="fas fa-file-invoice-dollar"></i> Guardar como Cotización
                    </button>
                    <button type="button" class="btn btn-primary btn-lg" onclick="crearPedido()" id="btn_crear" disabled
                            title="Seleccione un cliente y agregue productos para habilitar">
                        <i class="fas fa-check-circle"></i> Generar Pedido
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Estilos CSS personalizados
$additionalCSS = '
<style>
.sticky-top { 
    top: 20px; 
}

.carrito-card {
    border-left: 4px solid #28a745;
    background: linear-gradient(145deg, #f8f9fa 0%, #ffffff 100%);
}

.resumen-card {
    background: linear-gradient(145deg, #fff3cd 0%, #f8f9fa 100%);
    border: 1px solid #ffc107;
}

.producto-carrito {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 12px;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.producto-carrito:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,123,255,0.15);
}

.search-results {
    border: 1px solid #ddd;
    border-radius: 8px;
    max-height: 300px;
    overflow-y: auto;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.search-result-item {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: all 0.2s ease;
}

.search-result-item:hover { 
    background-color: #f8f9fa; 
    transform: translateX(5px);
}

.search-result-item:last-child { 
    border-bottom: none; 
}

.btn-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(45deg, #0056b3, #004085);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,123,255,0.3);
}

.alert {
    border-radius: 8px;
}

.card {
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,0.125);
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.toast-custom {
    min-width: 300px;
}
.dropzone { background:#f8f9fa; border:2px dashed #6c757d; border-radius:8px; }
.dropzone .dz-message { color:#6c757d; }
</style>';

// JavaScript funcional y limpio
$additionalJS = '
<script>
class CarritoManager {
    constructor() {
        this.carrito = [];
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.calcularTotales();
        // Cargar algunos productos iniciales
        this.buscarProductos();
    }
    
    bindEvents() {
        const clienteSelect = document.getElementById("cliente_id");
        clienteSelect.addEventListener("change", () => {
            const selectedOption = clienteSelect.options[clienteSelect.selectedIndex];
            const email = selectedOption ? selectedOption.getAttribute("data-email") || "" : "";
            document.getElementById("cliente_email").value = email;
            this.cargarDirecciones();
            this.verificarFormulario();
        });
        
        const searchInput = document.getElementById("search_producto");
        searchInput.addEventListener("input", (e) => {
            if (e.target.value.length >= 2) {
                this.buscarProductos();
            } else {
                document.getElementById("productos_encontrados").innerHTML = "";
            }
        });
        
        const grupoSelect = document.getElementById("filtro_grupo");
        grupoSelect.addEventListener("change", () => {
            this.buscarProductos();
        });
        
        searchInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                this.buscarProductos();
            }
        });

        // Quick-add by code (admin)
        document.getElementById("addByCodeBtnAdmin").addEventListener("click", async () => {
            const code = document.getElementById("addByCodeInputAdmin").value.trim();
            const qty = parseInt(document.getElementById("addByCodeQtyAdmin").value) || 1;
            if (!code) { alert("Ingrese un código"); return; }
            try {
                const fd = new FormData();
                fd.append("codigo", code);
                const res = await fetch("ajax/buscar_por_codigo.php", { method: "POST", body: fd, credentials: "same-origin" });
                const data = await res.json();
                if (data.success && data.producto) {
                    this.agregarProducto(data.producto.id, data.producto.codigo, data.producto.descripcion, data.producto.precio);
                    // Ajustar cantidad agregada si es >1
                    if (qty > 1) {
                        const idx = this.carrito.findIndex(i => i.id === data.producto.id);
                        if (idx >= 0) {
                            this.carrito[idx].cantidad += (qty - 1);
                            this.carrito[idx].subtotal = this.carrito[idx].cantidad * this.carrito[idx].precio;
                            this.actualizarCarrito();
                        }
                    }
                } else {
                    alert(data.message || "Producto no encontrado");
                }
            } catch (err) {
                console.error(err);
                alert("Error al buscar el producto");
            }
        });

        // Dropzone CSV (admin)
        if (window.Dropzone) {
            Dropzone.autoDiscover = false;
            const dz = new Dropzone("#dzCsvAdmin", {
                paramName: "file",
                maxFilesize: 2,
                acceptedFiles: ".csv,text/csv",
                maxFiles: 1,
                timeout: 60000
            });

            dz.on("success", (file, res) => {
                try {
                    const r = (typeof res === "string") ? JSON.parse(res) : res;
                    if (r.success && Array.isArray(r.items)) {
                        r.items.forEach(it => {
                            this.agregarProducto(it.id, it.codigo, it.descripcion, it.precio);
                            const qty = parseInt(it.cantidad) || 1;
                            if (qty > 1) {
                                const idx = this.carrito.findIndex(i => i.id === it.id);
                                if (idx >= 0) {
                                    this.carrito[idx].cantidad += (qty - 1);
                                    this.carrito[idx].subtotal = this.carrito[idx].cantidad * this.carrito[idx].precio;
                                }
                            }
                        });
                        this.actualizarCarrito();
                        if (r.unknown && r.unknown.length) alert("Códigos no encontrados: " + r.unknown.join(", "));
                        if (r.warnings && r.warnings.length) console.warn("CSV warnings:", r.warnings);
                    } else {
                        alert((r && r.message) ? r.message : "Error al procesar CSV");
                    }
                } catch (e) {
                    console.error(e);
                    alert("Respuesta inválida del servidor");
                }
                dz.removeAllFiles(true);
            });

            dz.on("error", (file, message) => {
                console.error("Dropzone error:", message);
                alert(typeof message === "string" ? message : (message?.message || "Error al subir CSV"));
            });
        }
    }
    async cargarDirecciones() {
        const clienteId = document.getElementById("cliente_id").value;
        const select = document.getElementById("direccion_entrega");
        const prevVal = select.value || "";
        select.disabled = true;
        select.innerHTML = "<option value=\"\">Cargando...</option>";
        if (!clienteId) {
            select.innerHTML = "<option value=\"\">Seleccione un cliente</option>";
            select.disabled = false;
            this.verificarFormulario();
            return;
        }
        try {
            const fd = new FormData();
            fd.append("action","get_direcciones_cliente");
            fd.append("cliente_id", clienteId);
            const res = await fetch("nueva_compra.php", { method:"POST", body: fd, credentials: "same-origin" });
            const data = await res.json();
            if (data.success && Array.isArray(data.direcciones)) {
                if (data.direcciones.length === 0) {
                    select.innerHTML = "<option value=\"\">Sin direcciones. Registre una en el perfil del cliente.</option>";
                } else {
                    let html = "<option value=\"\">Seleccione una dirección</option>";
                    data.direcciones.forEach(d => {
                        const labelParts = [d.direccion, d.ciudad || "", d.departamento || "", d.codigo_postal ? ("CP " + d.codigo_postal) : ""]
                            .filter(Boolean)
                            .join(" - ");
                        html += `<option value="${d.id}">${this.escapeHtml(labelParts)}</option>`;
                    });
                    select.innerHTML = html;
                    // Mantener selección previa si aplica; si solo hay una, seleccionarla automáticamente
                    const hasPrev = Array.from(select.options).some(o => o.value === prevVal);
                    if (hasPrev && prevVal) {
                        select.value = prevVal;
                    } else if (data.direcciones.length === 1) {
                        select.value = String(data.direcciones[0].id);
                    }
                }
            } else {
                select.innerHTML = "<option value=\"\">No se pudieron cargar las direcciones</option>";
            }
        } catch (e) {
            console.error(e);
            select.innerHTML = "<option value=\"\">Error al cargar direcciones</option>";
        } finally {
            select.disabled = false;
            this.verificarFormulario();
        }
    }
    
    async buscarProductos() {
        const search = document.getElementById("search_producto").value.trim();
        const grupo_id = document.getElementById("filtro_grupo").value;
        
        // Permitir búsqueda incluso sin criterios (mostrará productos limitados)
        try {
            const formData = new FormData();
            formData.append("action", "buscar_productos");
            formData.append("search", search);
            formData.append("grupo_id", grupo_id);
            
            const response = await fetch("nueva_compra.php", {
                method: "POST",
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarProductos(data.productos);
            } else {
                this.mostrarError("Error: " + (data.message || "No se pudieron cargar los productos"));
            }
        } catch (error) {
            console.error("Error:", error);
            this.mostrarError("Error al buscar productos: " + error.message);
        }
    }
    
    mostrarProductos(productos) {
        const container = document.getElementById("productos_encontrados");
        
        if (productos.length === 0) {
            container.innerHTML = "<div class=\"alert alert-info mb-0\">No se encontraron productos</div>";
            return;
        }
        
        let html = "<div class=\"search-results\">";
        productos.forEach(producto => {
            const codigoEscaped = this.escapeHtml(producto.codigo);
            const descripcionEscaped = this.escapeHtml(producto.descripcion);
            const grupoEscaped = this.escapeHtml(producto.grupo_nombre || "Sin grupo");
            
            html += "<div class=\"search-result-item\" onclick=\"carritoManager.agregarProducto(" + producto.id + ", \'" + codigoEscaped + "\', \'" + descripcionEscaped + "\', " + producto.precio + ")\">";
            html += "<div class=\"d-flex justify-content-between align-items-start\">";
            html += "<div>";
            html += "<strong>" + descripcionEscaped + "</strong>";
            html += "<br><small class=\"text-muted\">Código: " + codigoEscaped + "</small>";
            html += "<br><small class=\"text-info\">Grupo: " + grupoEscaped + "</small>";
            html += "</div>";
            html += "<div class=\"text-end\">";
            html += "<div class=\"fw-bold text-primary\">$" + this.formatNumber(producto.precio) + "</div>";
            html += "<small class=\"text-muted\">Stock: " + producto.stock_disponible + "</small>";
            html += "</div>";
            html += "</div>";
            html += "</div>";
        });
        html += "</div>";
        
        container.innerHTML = html;
    }
    
    agregarProducto(id, codigo, descripcion, precio) {
        const cantidad = parseInt(document.getElementById("cantidad").value) || 1;
        
        const existeIndex = this.carrito.findIndex(item => item.id === id);
        
        if (existeIndex >= 0) {
            this.carrito[existeIndex].cantidad += cantidad;
            this.carrito[existeIndex].subtotal = this.carrito[existeIndex].cantidad * this.carrito[existeIndex].precio;
        } else {
            this.carrito.push({
                id: id,
                codigo: codigo,
                descripcion: descripcion,
                precio: parseFloat(precio),
                cantidad: cantidad,
                subtotal: cantidad * parseFloat(precio)
            });
        }
        
        this.actualizarCarrito();
        this.mostrarToast("✓ " + descripcion + " agregado al carrito", "success");
        
        // Limpiar búsqueda
        document.getElementById("search_producto").value = "";
        document.getElementById("productos_encontrados").innerHTML = "";
    }
    
    eliminarProducto(index) {
        if (confirm("¿Eliminar este producto del pedido?")) {
            this.carrito.splice(index, 1);
            this.actualizarCarrito();
        }
    }
    
    actualizarCantidad(index, nuevaCantidad) {
        const cantidad = parseInt(nuevaCantidad);
        if (cantidad < 1) return;
        
        this.carrito[index].cantidad = cantidad;
        this.carrito[index].subtotal = this.carrito[index].cantidad * this.carrito[index].precio;
        
        this.actualizarCarrito();
    }
    
    actualizarCarrito() {
        const container = document.getElementById("carrito_productos");
        const btnLimpiar = document.getElementById("btn_limpiar");
        
        if (this.carrito.length === 0) {
            container.innerHTML = "<div class=\"text-center py-5 text-muted\">" +
                "<i class=\"fas fa-shopping-cart fa-3x mb-3\"></i>" +
                "<p class=\"mb-0\">No hay productos agregados</p>" +
                "<small>Use el buscador para agregar productos</small>" +
                "</div>";
            btnLimpiar.style.display = "none";
        } else {
            let html = "";
            this.carrito.forEach((producto, index) => {
                html += "<div class=\"producto-carrito\">";
                html += "<div class=\"d-flex justify-content-between align-items-start mb-2\">";
                html += "<div>";
                html += "<strong>" + this.escapeHtml(producto.descripcion) + "</strong>";
                html += "<br><small class=\"text-muted\">Código: " + this.escapeHtml(producto.codigo) + "</small>";
                html += "</div>";
                html += "<button type=\"button\" class=\"btn btn-sm btn-outline-danger\" onclick=\"carritoManager.eliminarProducto(" + index + ")\" title=\"Eliminar\">";
                html += "<i class=\"fas fa-trash\"></i>";
                html += "</button>";
                html += "</div>";
                html += "<div class=\"row g-2 align-items-center\">";
                html += "<div class=\"col-4\">";
                html += "<label class=\"form-label small\">Cantidad</label>";
                html += "<input type=\"number\" class=\"form-control form-control-sm text-center\" value=\"" + producto.cantidad + "\" min=\"1\" onchange=\"carritoManager.actualizarCantidad(" + index + ", this.value)\">";
                html += "</div>";
                html += "<div class=\"col-4\">";
                html += "<label class=\"form-label small\">Precio Unit.</label>";
                html += "<div class=\"form-control-plaintext small\">$" + this.formatNumber(producto.precio) + "</div>";
                html += "</div>";
                html += "<div class=\"col-4\">";
                html += "<label class=\"form-label small\">Subtotal</label>";
                html += "<div class=\"fw-bold text-primary\">$" + this.formatNumber(producto.subtotal) + "</div>";
                html += "</div>";
                html += "</div>";
                html += "</div>";
            });
            container.innerHTML = html;
            btnLimpiar.style.display = "inline-block";
        }
        
        this.calcularTotales();
    }
    
    calcularTotales() {
        const totalProductos = this.carrito.length;
        const cantidadTotal = this.carrito.reduce((sum, item) => sum + item.cantidad, 0);
        const subtotal = this.carrito.reduce((sum, item) => sum + item.subtotal, 0);
        
        const descuentoPorcentaje = parseFloat(document.getElementById("descuento_porcentaje").value) || 0;
        const montoDescuento = subtotal * (descuentoPorcentaje / 100);
        const totalPagar = subtotal - montoDescuento;
        
        const iva = totalPagar * 0.16;
        const baseGravable = totalPagar - iva;
        
        document.getElementById("total_productos").textContent = totalProductos;
        document.getElementById("cantidad_total").textContent = cantidadTotal;
        document.getElementById("subtotal").textContent = "$" + this.formatNumber(subtotal);
        document.getElementById("total_pagar").textContent = "$" + this.formatNumber(totalPagar);
        document.getElementById("iva_total").textContent = "$" + this.formatNumber(iva);
        document.getElementById("base_gravable").textContent = "$" + this.formatNumber(baseGravable);
        
        this.verificarFormulario();
    }
    
    verificarFormulario() {
        const clienteSeleccionado = document.getElementById("cliente_id").value;
        const direccionSeleccionada = document.getElementById("direccion_entrega").value;
        const tieneProductos = this.carrito.length > 0;
        
        const btnCrear = document.getElementById("btn_crear");
        const btnCot = document.getElementById("btn_cotizacion");
        const shouldEnable = clienteSeleccionado && direccionSeleccionada && tieneProductos;
        btnCrear.disabled = !shouldEnable;
        if (btnCot) btnCot.disabled = !shouldEnable;
        
        // Actualizar título del botón
        if (shouldEnable) {
            btnCrear.title = "Crear el pedido con los productos seleccionados";
        } else if (!clienteSeleccionado && !tieneProductos) {
            btnCrear.title = "Seleccione un cliente y agregue productos para habilitar";
        } else if (clienteSeleccionado && !direccionSeleccionada) {
            btnCrear.title = "Seleccione una dirección de entrega para habilitar";
        } else if (!clienteSeleccionado) {
            btnCrear.title = "Seleccione un cliente para habilitar";
        } else if (!tieneProductos) {
            btnCrear.title = "Agregue productos al carrito para habilitar";
        }
    }
    
    async crearCotizacion() {
        const clienteId = document.getElementById("cliente_id").value;
        const observaciones = document.getElementById("observaciones").value;
        const direccionEntregaId = document.getElementById("direccion_entrega").value;
        const personaRecibe = document.getElementById("persona_recibe").value;
        const subtotal = this.carrito.reduce((sum, item) => sum + item.subtotal, 0);
        const descuentoPorcentaje = parseFloat(document.getElementById("descuento_porcentaje").value) || 0;
        
        if (!clienteId || !direccionEntregaId || this.carrito.length === 0) {
            alert("Debe seleccionar un cliente, una dirección y agregar al menos un producto");
            return;
        }
        
        const btnCot = document.getElementById("btn_cotizacion");
        const original = btnCot.innerHTML;
        btnCot.disabled = true;
        btnCot.innerHTML = "<i class=\"fas fa-spinner fa-spin\"></i> Guardando...";
        try {
            const formData = new FormData();
            formData.append("action", "crear_cotizacion");
            formData.append("cliente_id", clienteId);
            formData.append("subtotal", subtotal);
            formData.append("descuento_porcentaje", descuentoPorcentaje);
            formData.append("observaciones", observaciones);
            formData.append("direccion_entrega_id", direccionEntregaId);
            formData.append("persona_recibe", personaRecibe);
            formData.append("productos", JSON.stringify(this.carrito));
            const response = await fetch("nueva_compra.php", { method: "POST", body: formData });
            const data = await response.json();
            if (data.success) {
                this.mostrarToast("¡Cotización creada! Número: " + data.numero_documento, "success");
            } else {
                this.mostrarToast("Error: " + (data.message || "No se pudo crear la cotización"), "error");
            }
        } catch (e) {
            console.error(e);
            this.mostrarToast("Error al crear la cotización", "error");
        } finally {
            btnCot.disabled = false;
            btnCot.innerHTML = original;
        }
    }

    async crearPedido() {
        const clienteId = document.getElementById("cliente_id").value;
        const observaciones = document.getElementById("observaciones").value;
        const fechaEntrega = document.getElementById("fecha_entrega").value;
        const direccionEntregaId = document.getElementById("direccion_entrega").value;
        const personaRecibe = document.getElementById("persona_recibe").value;
        const metodoPago = document.getElementById("metodo_pago").value;
        const subtotal = this.carrito.reduce((sum, item) => sum + item.subtotal, 0);
        const descuentoPorcentaje = parseFloat(document.getElementById("descuento_porcentaje").value) || 0;
        
        if (!clienteId || !direccionEntregaId || this.carrito.length === 0) {
            alert("Debe seleccionar un cliente, una dirección y agregar al menos un producto");
            return;
        }
        
        const btnCrear = document.getElementById("btn_crear");
        const textoOriginal = btnCrear.innerHTML;
        btnCrear.disabled = true;
        btnCrear.innerHTML = "<i class=\"fas fa-spinner fa-spin\"></i> Procesando...";
        
        try {
            const formData = new FormData();
            formData.append("action", "crear_pedido");
            formData.append("cliente_id", clienteId);
            formData.append("subtotal", subtotal);
            formData.append("descuento_porcentaje", descuentoPorcentaje);
            formData.append("observaciones", observaciones);
            formData.append("fecha_entrega", fechaEntrega);
            formData.append("direccion_entrega_id", direccionEntregaId);
            formData.append("persona_recibe", personaRecibe);
            formData.append("metodo_pago", metodoPago);
            formData.append("productos", JSON.stringify(this.carrito));
            
            const response = await fetch("nueva_compra.php", {
                method: "POST",
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarToast("¡Pedido creado exitosamente! Número: " + data.numero_documento, "success");
                this.limpiarFormulario();
            } else {
                this.mostrarToast("Error: " + data.message, "error");
            }
        } catch (error) {
            console.error("Error:", error);
            this.mostrarToast("Error al procesar la solicitud", "error");
        } finally {
            btnCrear.disabled = false;
            btnCrear.innerHTML = textoOriginal;
        }
    }
    
    limpiarFormulario() {
        this.carrito = [];
        document.getElementById("cliente_id").value = "";
        document.getElementById("cliente_email").value = "";
        document.getElementById("observaciones").value = "";
        // Reset delivery date back to the allowed date (min attribute)
        const fechaInput = document.getElementById("fecha_entrega");
        fechaInput.value = fechaInput.min || fechaInput.value;
        document.getElementById("descuento_porcentaje").value = "0";
        this.actualizarCarrito();
    }
    
    limpiarCarrito() {
        if (this.carrito.length > 0 && confirm("¿Limpiar todo el carrito?")) {
            this.carrito = [];
            this.actualizarCarrito();
        }
    }
    
    previsualizarPedido() {
        if (this.carrito.length === 0) {
            alert("No hay productos en el carrito");
            return;
        }
        
        const cliente = document.getElementById("cliente_id").options[document.getElementById("cliente_id").selectedIndex].text;
        const subtotal = this.carrito.reduce((sum, item) => sum + item.subtotal, 0);
        const descuento = parseFloat(document.getElementById("descuento_porcentaje").value) || 0;
        const montoDescuento = subtotal * (descuento / 100);
        const total = subtotal - montoDescuento;
        
        let preview = "VISTA PREVIA DEL PEDIDO\\n\\n";
        preview += "Cliente: " + cliente + "\\n";
        preview += "Fecha: " + new Date().toLocaleDateString() + "\\n\\n";
        preview += "PRODUCTOS:\\n";
        
        this.carrito.forEach(producto => {
            preview += "• " + producto.descripcion + " x" + producto.cantidad + " = $" + this.formatNumber(producto.subtotal) + "\\n";
        });
        
        preview += "\\nSubtotal: $" + this.formatNumber(subtotal);
        if (descuento > 0) {
            preview += "\\nDescuento (" + descuento + "%): -$" + this.formatNumber(montoDescuento);
        }
        preview += "\\nTOTAL: $" + this.formatNumber(total);
        
        alert(preview);
    }
    
    imprimirPedido() {
        if (this.carrito.length === 0) {
            alert("No hay productos para imprimir");
            return;
        }
        
        const cliente = document.getElementById("cliente_id").options[document.getElementById("cliente_id").selectedIndex].text;
        const fecha = new Date().toLocaleDateString("es-CO");
        const subtotal = this.carrito.reduce((sum, item) => sum + item.subtotal, 0);
        const descuento = parseFloat(document.getElementById("descuento_porcentaje").value) || 0;
        const montoDescuento = subtotal * (descuento / 100);
        const total = subtotal - montoDescuento;
        
        const printWindow = window.open("", "_blank");
        let html = "<html><head>";
        html += "<title>Cotización - SolTecnInd</title>";
        html += "<style>";
        html += "body { font-family: Arial, sans-serif; margin: 20px; }";
        html += ".header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }";
        html += "table { width: 100%; border-collapse: collapse; margin-top: 20px; }";
        html += "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
        html += "th { background-color: #f5f5f5; font-weight: bold; }";
        html += ".total { text-align: right; margin-top: 20px; font-size: 18px; font-weight: bold; }";
        html += ".company { color: #007bff; font-size: 24px; font-weight: bold; }";
        html += "</style>";
        html += "</head><body>";
        html += "<div class=\"header\">";
        html += "<div class=\"company\">SolTecnInd</div>";
        html += "<h2>Cotización</h2>";
        html += "<p>Fecha: " + fecha + "</p>";
        html += "</div>";
        html += "<p><strong>Cliente:</strong> " + this.escapeHtml(cliente) + "</p>";
        html += "<table>";
        html += "<thead>";
        html += "<tr>";
        html += "<th>Código</th>";
        html += "<th>Descripción</th>";
        html += "<th>Cantidad</th>";
        html += "<th>Precio Unit.</th>";
        html += "<th>Subtotal</th>";
        html += "</tr>";
        html += "</thead>";
        html += "<tbody>";
        
        this.carrito.forEach(producto => {
            html += "<tr>";
            html += "<td>" + this.escapeHtml(producto.codigo) + "</td>";
            html += "<td>" + this.escapeHtml(producto.descripcion) + "</td>";
            html += "<td>" + producto.cantidad + "</td>";
            html += "<td>$" + this.formatNumber(producto.precio) + "</td>";
            html += "<td>$" + this.formatNumber(producto.subtotal) + "</td>";
            html += "</tr>";
        });
        
        html += "</tbody>";
        html += "</table>";
        html += "<div class=\"total\">";
        html += "<p>Subtotal: $" + this.formatNumber(subtotal) + "</p>";
        
        if (descuento > 0) {
            html += "<p>Descuento (" + descuento + "%): -$" + this.formatNumber(montoDescuento) + "</p>";
        }
        
        html += "<p><strong>TOTAL: $" + this.formatNumber(total) + "</strong></p>";
        html += "</div>";
        html += "</body>";
        html += "</html>";
        
        printWindow.document.write(html);
        printWindow.document.close();
        printWindow.print();
    }
    
    mostrarError(mensaje) {
        document.getElementById("productos_encontrados").innerHTML = "<div class=\"alert alert-warning mb-0\">" + mensaje + "</div>";
    }
    
    mostrarToast(mensaje, tipo = "info") {
        const toastContainer = this.getOrCreateToastContainer();
        
        const toastId = "toast_" + Date.now();
        const colorClass = tipo === "success" ? "bg-success" : tipo === "error" ? "bg-danger" : "bg-info";
        
        const toastHTML = "<div id=\"" + toastId + "\" class=\"toast toast-custom align-items-center text-white " + colorClass + " border-0\" role=\"alert\">" +
            "<div class=\"d-flex\">" +
            "<div class=\"toast-body\">" + mensaje + "</div>" +
            "<button type=\"button\" class=\"btn-close btn-close-white me-2 m-auto\" onclick=\"document.getElementById(\'" + toastId + "\').remove()\"></button>" +
            "</div>" +
            "</div>";
        
        toastContainer.insertAdjacentHTML("beforeend", toastHTML);
        
        setTimeout(() => {
            const toastElement = document.getElementById(toastId);
            if (toastElement) {
                toastElement.remove();
            }
        }, 5000);
    }
    
    getOrCreateToastContainer() {
        let container = document.getElementById("toast-container");
        if (!container) {
            container = document.createElement("div");
            container.id = "toast-container";
            container.className = "toast-container";
            document.body.appendChild(container);
        }
        return container;
    }
    
    formatNumber(num) {
        return parseFloat(num).toLocaleString("es-CO", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    escapeHtml(text) {
        if (!text) return "";
        const map = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            "\"": "&quot;",
            "\'": "&#039;"
        };
        return String(text).replace(/[&<>"\']/g, function(m) { return map[m]; });
    }
}

// Variables y funciones globales
let carritoManager;

function calcularTotales() {
    carritoManager.calcularTotales();
}

function limpiarCarrito() {
    carritoManager.limpiarCarrito();
}

function crearPedido() {
    if (!carritoManager) {
        alert("Error: Sistema no inicializado correctamente. Recarga la página.");
        return;
    }
    
    carritoManager.crearPedido();
}

function crearCotizacion() {
    if (!carritoManager) {
        alert("Error: Sistema no inicializado correctamente. Recarga la página.");
        return;
    }
    carritoManager.crearCotizacion();
}

function previsualizarPedido() {
    carritoManager.previsualizarPedido();
}

function imprimirPedido() {
    carritoManager.imprimirPedido();
}

// Inicialización
document.addEventListener("DOMContentLoaded", function() {
    carritoManager = new CarritoManager();
});
</script>';

// Renderizar la página
// Incluir Dropzone desde CDN en el layout si es necesario
$dropzoneIncludes = "\n<link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css\" crossorigin=\"anonymous\" />\n<script src=\"https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js\" crossorigin=\"anonymous\"></script>\n";

LayoutManager::renderAdminPage('Nueva Compra', $dropzoneIncludes . $content, $additionalCSS, $additionalJS);
?>
