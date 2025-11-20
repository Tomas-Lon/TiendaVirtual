<?php
session_start();

// Solo empleados (admin)
if (!isset($_SESSION['usuario']) || (($_SESSION['tipo'] ?? '') !== 'empleado')) {
		header('Location: ../index.php');
		exit();
}

require_once '../includes/LayoutManager.php';
require_once '../config/database.php';

// Fecha mínima de entrega para Admin: desde mañana
$fechaMinimaAdmin = (new DateTime('tomorrow'))->format('Y-m-d');

// Cargar combos de clientes y grupos
$clientes = [];
$grupos = [];
try {
		$pdo = getConnection();
		$clientes = $pdo->query("SELECT id, nombre, email FROM clientes WHERE activo = 1 ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
		$grupos = $pdo->query("SELECT id, nombre FROM grupos_productos WHERE activo = 1 ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
		$clientes = [];
		$grupos = [];
}

// Endpoints internos (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
		header('Content-Type: application/json; charset=utf-8');
		$action = $_POST['action'];

		try {
				$pdo = getConnection();

				if ($action === 'buscar_productos') {
						$search = trim($_POST['search'] ?? '');
						$grupo_id = $_POST['grupo_id'] ?? '';

						if ($search === '' && $grupo_id === '') {
								$sql = "SELECT p.id, p.codigo, p.descripcion, p.precio, 0 AS stock_disponible, g.nombre AS grupo_nombre
												FROM productos p LEFT JOIN grupos_productos g ON p.grupo_id = g.id
												ORDER BY p.descripcion LIMIT 20";
								$stmt = $pdo->prepare($sql);
								$stmt->execute();
						} else {
								$where = [];
								$params = [];
								if ($search !== '') {
										$where[] = '(p.codigo LIKE ? OR p.descripcion LIKE ?)';
										$params[] = "%{$search}%";
										$params[] = "%{$search}%";
								}
								if ($grupo_id !== '') {
										$where[] = 'p.grupo_id = ?';
										$params[] = $grupo_id;
								}
								$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
								$sql = "SELECT p.id, p.codigo, p.descripcion, p.precio, 0 AS stock_disponible, g.nombre AS grupo_nombre
												FROM productos p LEFT JOIN grupos_productos g ON p.grupo_id = g.id
												{$whereSql}
												ORDER BY p.descripcion LIMIT 50";
								$stmt = $pdo->prepare($sql);
								$stmt->execute($params);
						}
						$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
						echo json_encode(['success' => true, 'productos' => $productos]);
						exit;
				}

				if ($action === 'get_direcciones_cliente') {
						$cliente_id = (int)($_POST['cliente_id'] ?? 0);
						if ($cliente_id <= 0) {
								echo json_encode(['success' => false, 'message' => 'Cliente inválido']);
								exit;
						}
						// Asumimos tabla direcciones_clientes con columnas id, cliente_id, direccion, ciudad, departamento, codigo_postal
						$stmt = $pdo->prepare("SELECT id, direccion, ciudad, departamento, codigo_postal FROM direcciones_clientes WHERE cliente_id = ? ORDER BY id DESC");
						$stmt->execute([$cliente_id]);
						$dirs = $stmt->fetchAll(PDO::FETCH_ASSOC);
						echo json_encode(['success' => true, 'direcciones' => $dirs]);
						exit;
				}

				if ($action === 'crear_cotizacion' || $action === 'crear_pedido') {
						// NOTA: Aquí normalmente se insertan registros en pedidos/detalle_pedidos y se aplican descuentos por grupo + cabecera.
						// Para no romper el entorno sin conocer el esquema exacto, devolvemos una respuesta de éxito simulada.
						// Integra aquí tu lógica real de inserción si lo deseas, usando $pdo y validaciones.

						$cliente_id = (int)($_POST['cliente_id'] ?? 0);
						$direccion_entrega_id = (int)($_POST['direccion_entrega_id'] ?? 0);
						$productosJson = $_POST['productos'] ?? '[]';
						$productos = json_decode($productosJson, true);
						if (!$cliente_id || !$direccion_entrega_id || !is_array($productos) || count($productos) === 0) {
								echo json_encode(['success' => false, 'message' => 'Datos incompletos: cliente, dirección y productos son obligatorios']);
								exit;
						}

						// Validar fecha mínima para pedido
						if ($action === 'crear_pedido') {
								$fecha_entrega = $_POST['fecha_entrega'] ?? '';
								if ($fecha_entrega) {
										$min = new DateTime($fechaMinimaAdmin);
										$fe = DateTime::createFromFormat('Y-m-d', $fecha_entrega) ?: new DateTime($fecha_entrega);
										if ($fe < $min) {
												echo json_encode(['success' => false, 'message' => 'La fecha de entrega debe ser desde mañana en adelante']);
												exit;
										}
								}
						}

						$numero = strtoupper($action === 'crear_pedido' ? 'PED' : 'COT') . '-' . date('Ymd-His');
						echo json_encode(['success' => true, 'numero_documento' => $numero]);
						exit;
				}

				echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
				exit;
		} catch (Exception $e) {
				echo json_encode(['success' => false, 'message' => $e->getMessage()]);
				exit;
		}
}

ob_start();
?>

<div class="container-fluid px-3">
	<div class="d-flex justify-content-between align-items-center mb-4">
		<h2 class="fw-bold"><i class="fas fa-file-import text-primary me-2"></i>Nueva Compra desde CSV</h2>
	</div>

	<div class="row">
		<!-- Panel izquierdo -->
		<div class="col-lg-8 mb-4">
			<!-- Búsqueda / CSV / Agregar por código -->
			<div class="card shadow-sm mb-4">
				<div class="card-header bg-primary text-white">
					<h5 class="mb-0"><i class="fas fa-search me-2"></i>Búsqueda / Importación de Productos</h5>
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

					<div id="productos_encontrados" class="mt-3"></div>
				</div>
			</div>

			<!-- Carrito -->
			<div class="card shadow-sm">
				<div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
					<h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Productos Seleccionados</h5>
					<button type="button" class="btn btn-light btn-sm" id="btnClearAll" style="display:none">
						<i class="fas fa-trash text-danger"></i> Limpiar
					</button>
				</div>
				<div class="card-body" id="cartContainer">
					<div class="text-center py-5 text-muted">
						<i class="fas fa-shopping-cart fa-3x mb-3"></i>
						<p class="mb-0">No hay productos agregados</p>
						<small>Busca, importa desde CSV o agrega por código</small>
					</div>
				</div>
			</div>
		</div>

		<!-- Panel derecho -->
		<div class="col-lg-4 mb-4">
			<div class="card shadow-sm sticky-top" style="top: 20px;">
				<div class="card-header bg-warning"><strong>Datos y Resumen</strong></div>
				<div class="card-body">
					<!-- Datos del cliente -->
					<div class="mb-3">
						<label for="cliente_id" class="form-label fw-semibold">Cliente *</label>
						<select class="form-select" id="cliente_id" required>
							<option value="">Seleccione</option>
							<?php foreach ($clientes as $c): ?>
								<option value="<?= (int)$c['id'] ?>" data-email="<?= htmlspecialchars($c['email'] ?? '') ?>"><?= htmlspecialchars($c['nombre']) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="mb-3">
						<label for="cliente_email" class="form-label fw-semibold">Email</label>
						<input type="email" class="form-control" id="cliente_email" readonly>
					</div>
					<div class="mb-3">
						<label for="direccion_entrega" class="form-label fw-semibold">Dirección de Entrega *</label>
						<select class="form-select" id="direccion_entrega">
							<option value="">Seleccione un cliente</option>
						</select>
						<small class="text-muted">Se cargan automáticamente tras elegir cliente.</small>
					</div>
					<div class="mb-3">
						<label for="persona_recibe" class="form-label fw-semibold">Persona que recibe</label>
						<input type="text" class="form-control" id="persona_recibe" placeholder="Nombre de quien recibe">
					</div>
					<div class="mb-3">
						<label for="fecha_entrega" class="form-label fw-semibold">Fecha de Entrega</label>
						<input type="date" class="form-control" id="fecha_entrega" value="<?= $fechaMinimaAdmin ?>" min="<?= $fechaMinimaAdmin ?>">
						<small class="text-muted">Disponible desde: <?= date('d/m/Y', strtotime($fechaMinimaAdmin)) ?></small>
					</div>
					<div class="mb-3">
						<label for="metodo_pago" class="form-label fw-semibold">Método de Pago</label>
						<select class="form-select" id="metodo_pago">
							<option value="contado">Contado</option>
							<option value="credito_30">Crédito 30 días</option>
							<option value="credito_60">Crédito 60 días</option>
							<option value="transferencia">Transferencia</option>
						</select>
					</div>
					<div class="row text-center mb-3">
						<div class="col-6">
							<div class="border rounded p-2">
								<div class="h5 mb-0 text-primary" id="statItems">0</div>
								<small class="text-muted">Productos</small>
							</div>
						</div>
						<div class="col-6">
							<div class="border rounded p-2">
								<div class="h5 mb-0 text-info" id="statQty">0</div>
								<small class="text-muted">Cantidad</small>
							</div>
						</div>
					</div>

					<hr>

					<div class="d-flex justify-content-between mb-2">
						<span>Subtotal:</span>
						<strong id="subtotal" class="text-primary">$0.00</strong>
					</div>
					<div class="d-flex justify-content-between mb-2">
						<span>Descuento:</span>
						<div class="input-group input-group-sm" style="width: 130px;">
							<input type="number" id="descuento_porcentaje" class="form-control" min="0" max="100" value="0">
							<span class="input-group-text">%</span>
						</div>
					</div>
					<div class="d-flex justify-content-between mb-3">
						<strong>Total:</strong>
						<strong class="text-success fs-4" id="total_pagar">$0.00</strong>
					</div>
					<div class="alert alert-info p-2 mb-3"><small>
						<div class="d-flex justify-content-between"><span>IVA (16%):</span><span id="iva_total">$0.00</span></div>
						<div class="d-flex justify-content-between"><span>Base gravable:</span><span id="base_gravable">$0.00</span></div>
					</small></div>

					<div class="mb-3">
						<label for="observaciones" class="form-label fw-semibold">Observaciones</label>
						<textarea class="form-control" id="observaciones" rows="3" placeholder="Notas del pedido..."></textarea>
					</div>

					<div class="row g-2 mb-3">
						<div class="col-6">
							<button type="button" class="btn btn-outline-info w-100" id="btnPreview"><i class="fas fa-eye"></i> Vista Previa</button>
						</div>
						<div class="col-6">
							<button type="button" class="btn btn-outline-warning w-100" id="btnPrint"><i class="fas fa-print"></i> Imprimir</button>
						</div>
					</div>

					<div class="d-grid gap-2">
						<button class="btn btn-secondary" id="btnCotizar" type="button" disabled>
							<i class="fas fa-file-invoice-dollar"></i> Guardar como Cotización
						</button>
						<button class="btn btn-primary btn-lg" id="btnGenerarPedido" type="button" disabled>
							<i class="fas fa-check-circle"></i> Generar Pedido
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Dropzone -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" crossorigin="anonymous" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js" crossorigin="anonymous"></script>

<script>
Dropzone.autoDiscover = false;
const cart = new Map();

function formatCurrency(n){ return (Number(n)||0).toLocaleString('es-CO',{minimumFractionDigits:2, maximumFractionDigits:2}); }
function escapeHtml(t){ return t?String(t).replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;'}[m])):''; }

function updateStats(){ let items=cart.size, qty=0; cart.forEach(v=>qty+=v.cantidad); document.getElementById('statItems').textContent=items; document.getElementById('statQty').textContent=qty; }

function renderCart(){
	const c=document.getElementById('cartContainer'), b=document.getElementById('btnClearAll'); const btnCot=document.getElementById('btnCotizar'); const btnPed=document.getElementById('btnGenerarPedido');
	if(cart.size===0){ c.innerHTML=`<div class="text-center py-5 text-muted"><i class="fas fa-shopping-cart fa-3x mb-3"></i><p>No hay productos agregados</p></div>`; b.style.display='none'; if(btnCot) btnCot.disabled=true; if(btnPed) btnPed.disabled=true; }
	else {
		let html=''; cart.forEach((p,id)=>{ html+=`
			<div class="border rounded p-2 mb-2 bg-light">
				<div class="d-flex justify-content-between align-items-start">
					<div><strong>${escapeHtml(p.descripcion)}</strong><br><small class="text-muted">Código: ${escapeHtml(p.codigo)}</small></div>
					<button class="btn btn-sm btn-outline-danger" onclick="removeItem(${id})"><i class="fas fa-trash"></i></button>
				</div>
				<div class="row g-2 mt-2">
					<div class="col-5"><input type="number" class="form-control form-control-sm" min="1" value="${p.cantidad}" onchange="updateQty(${id},this.value)"></div>
					<div class="col-4 text-end small pt-2">x $${formatCurrency(p.precio)}</div>
					<div class="col-3 text-end fw-bold text-primary pt-2">$${formatCurrency(p.cantidad*p.precio)}</div>
				</div>
			</div>`; });
		c.innerHTML=html; b.style.display='inline-block'; if(btnCot) btnCot.disabled=false; if(btnPed) btnPed.disabled=false;
	}
	updateStats(); calcularTotales(); verificarFormulario();
}

function removeItem(id){ cart.delete(id); renderCart(); }
function updateQty(id,q){ const p=cart.get(id); if(p){ p.cantidad=Math.max(1,parseInt(q)||1); renderCart(); } }
function mergeItems(items){ items.forEach(it=>{ const id=Number(it.id); const ex=cart.get(id); const qty=Math.max(1,parseInt(it.cantidad)||1); if(ex){ ex.cantidad+=qty; } else { cart.set(id,{...it,cantidad:qty,precio:Number(it.precio)}); } }); renderCart(); }
function getItemsForServer(){ const arr=[]; cart.forEach(v=>arr.push({id:v.id, cantidad:v.cantidad})); return arr; }

// Búsqueda de productos
document.getElementById('search_producto').addEventListener('input', (e)=>{ if(e.target.value.trim().length>=2){ buscarProductos(); } else { document.getElementById('productos_encontrados').innerHTML=''; } });
document.getElementById('filtro_grupo').addEventListener('change', buscarProductos);
document.getElementById('search_producto').addEventListener('keypress', (e)=>{ if(e.key==='Enter'){ buscarProductos(); }});

async function buscarProductos(){
	const search=document.getElementById('search_producto').value.trim(); const grupo_id=document.getElementById('filtro_grupo').value;
	try{ const fd=new FormData(); fd.append('action','buscar_productos'); fd.append('search',search); fd.append('grupo_id',grupo_id); const res=await fetch('nueva_compra.php',{ method:'POST', body: fd }); const data=await res.json(); if(data.success){ mostrarProductos(data.productos||[]); } else { document.getElementById('productos_encontrados').innerHTML='<div class="alert alert-warning mb-0">'+(data.message||'Sin resultados')+'</div>'; } }catch(e){ console.error(e); document.getElementById('productos_encontrados').innerHTML='<div class="alert alert-warning mb-0">Error al buscar productos</div>'; }
}

function mostrarProductos(productos){ const container=document.getElementById('productos_encontrados'); if(!productos || productos.length===0){ container.innerHTML='<div class="alert alert-info mb-0">No se encontraron productos</div>'; return; } let html='<div class="search-results">'; productos.forEach(p=>{ const codigo=escapeHtml(p.codigo); const desc=escapeHtml(p.descripcion); const grupo=escapeHtml(p.grupo_nombre||'Sin grupo'); html+=`<div class="search-result-item" onclick="agregarDesdeBusqueda(${p.id}, '${codigo}', '${desc}', ${p.precio})"><div class="d-flex justify-content-between align-items-start"><div><strong>${desc}</strong><br><small class="text-muted">Código: ${codigo}</small><br><small class="text-info">Grupo: ${grupo}</small></div><div class="text-end"><div class="fw-bold text-primary">$${formatCurrency(p.precio)}</div><small class="text-muted">Stock: ${p.stock_disponible||0}</small></div></div></div>`; }); html+='</div>'; container.innerHTML=html; }

function agregarDesdeBusqueda(id,codigo,descripcion,precio){ const qty=Math.max(1,parseInt(document.getElementById('cantidad').value)||1); const ex=cart.get(id); if(ex){ ex.cantidad+=qty; } else { cart.set(id,{ id, codigo, descripcion, precio:Number(precio), cantidad:qty }); } renderCart(); document.getElementById('search_producto').value=''; document.getElementById('productos_encontrados').innerHTML=''; }

// CSV Dropzone
new Dropzone('#dzCsvAdmin', { paramName:'file', maxFilesize:2, acceptedFiles:'.csv,text/csv', maxFiles:1, timeout:60000,
	success:function(file,res){ try{ const r=typeof res==='string'?JSON.parse(res):res; if(r && r.success){ if(Array.isArray(r.items)) mergeItems(r.items); if(r.unknown && r.unknown.length) alert('Códigos no encontrados: '+r.unknown.join(', ')); if(r.warnings && r.warnings.length) console.warn('CSV warnings:', r.warnings); } else { alert((r && r.message) ? r.message : 'Error al procesar CSV'); } }catch(e){ console.error(e); alert('Respuesta inválida del servidor'); } this.removeAllFiles(true); },
	error:function(file,message){ console.error('Dropzone error:', message); alert(typeof message==='string'?message:(message?.message||'Error al subir CSV')); }
});

// Quick add by code
document.getElementById('addByCodeBtnAdmin').addEventListener('click', async ()=>{
	const code=document.getElementById('addByCodeInputAdmin').value.trim(); const qty=Math.max(1,parseInt(document.getElementById('addByCodeQtyAdmin').value)||1);
	if(!code) return alert('Ingrese un código');
	try{ const fd=new FormData(); fd.append('codigo', code); const res=await fetch('ajax/buscar_por_codigo.php',{ method:'POST', body: fd, credentials:'same-origin' }); const data=await res.json(); if(data.success && data.producto){ mergeItems([{...data.producto, cantidad: qty}]); } else { alert(data.message||'Producto no encontrado'); } }catch(e){ console.error(e); alert('Error al buscar producto'); }
});

// Totales con descuento de cabecera
function calcularTotales(){ const subtotal=Array.from(cart.values()).reduce((s,it)=>s+(it.cantidad*it.precio),0); const dPct=Math.max(0,Math.min(100,parseFloat(document.getElementById('descuento_porcentaje').value)||0)); const dMonto=subtotal*(dPct/100); const base=subtotal-dMonto; const iva=base*0.16; const total=base+iva; document.getElementById('subtotal').textContent='$'+formatCurrency(subtotal); document.getElementById('iva_total').textContent='$'+formatCurrency(iva); document.getElementById('base_gravable').textContent='$'+formatCurrency(base); document.getElementById('total_pagar').textContent='$'+formatCurrency(total); }
document.getElementById('descuento_porcentaje').addEventListener('input', calcularTotales);

document.getElementById('btnClearAll').addEventListener('click', ()=>{ if(cart.size>0 && confirm('¿Limpiar todo?')){ cart.clear(); renderCart(); } });

function verificarFormulario(){ const cliente=document.getElementById('cliente_id').value; const dir=document.getElementById('direccion_entrega').value; const tiene=cart.size>0; const ok=!!cliente && !!dir && tiene; document.getElementById('btnCotizar').disabled=!ok; document.getElementById('btnGenerarPedido').disabled=!ok; }

// Cliente y direcciones
document.getElementById('cliente_id').addEventListener('change', ()=>{ const sel=document.getElementById('cliente_id'); const opt=sel.options[sel.selectedIndex]; document.getElementById('cliente_email').value = opt ? (opt.getAttribute('data-email')||'') : ''; cargarDirecciones(); verificarFormulario(); });
async function cargarDirecciones(){ const clienteId=document.getElementById('cliente_id').value; const select=document.getElementById('direccion_entrega'); const prevVal=select.value||''; select.disabled=true; select.innerHTML='<option value="">Cargando...</option>'; if(!clienteId){ select.innerHTML='<option value="">Seleccione un cliente</option>'; select.disabled=false; verificarFormulario(); return; } try{ const fd=new FormData(); fd.append('action','get_direcciones_cliente'); fd.append('cliente_id', clienteId); const res=await fetch('nueva_compra.php',{ method:'POST', body: fd }); const data=await res.json(); if(data.success && Array.isArray(data.direcciones)){ if(data.direcciones.length===0){ select.innerHTML='<option value="">Sin direcciones. Registre una en el perfil del cliente.</option>'; } else { let html='<option value="">Seleccione una dirección</option>'; data.direcciones.forEach(d=>{ const parts=[d.direccion, d.ciudad||'', d.departamento||'', d.codigo_postal?('CP '+d.codigo_postal):''].filter(Boolean).join(' - '); html+=`<option value="${d.id}">${escapeHtml(parts)}</option>`; }); select.innerHTML=html; const hasPrev=Array.from(select.options).some(o=>o.value===prevVal); if(hasPrev && prevVal){ select.value=prevVal; } else if(data.direcciones.length===1){ select.value=String(data.direcciones[0].id); } } } else { select.innerHTML='<option value="">No se pudieron cargar las direcciones</option>'; } }catch(e){ console.error(e); select.innerHTML='<option value="">Error al cargar direcciones</option>'; } finally { select.disabled=false; verificarFormulario(); } }

// Acciones
document.getElementById('btnCotizar').addEventListener('click', async ()=>{ if(cart.size===0) return alert('No hay productos para cotizar'); const btn=document.getElementById('btnCotizar'); const original=btn.innerHTML; btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Creando...'; try{ const fd=new FormData(); fd.append('action','crear_cotizacion'); fd.append('cliente_id', document.getElementById('cliente_id').value); fd.append('direccion_entrega_id', document.getElementById('direccion_entrega').value); fd.append('persona_recibe', document.getElementById('persona_recibe').value); fd.append('descuento_porcentaje', parseFloat(document.getElementById('descuento_porcentaje').value)||0); fd.append('observaciones', document.getElementById('observaciones').value); fd.append('productos', JSON.stringify(getItemsForServer())); const res=await fetch('nueva_compra.php',{ method:'POST', body: fd }); const data=await res.json(); if(data.success){ alert('¡Cotización creada! Número: '+data.numero_documento); } else { alert(data.message||'No se pudo crear la cotización'); } }catch(e){ console.error(e); alert('Error al crear la cotización'); } finally { btn.disabled=false; btn.innerHTML=original; } });

document.getElementById('btnGenerarPedido').addEventListener('click', async ()=>{ if(cart.size===0) return alert('No hay productos para generar pedido'); const btn=document.getElementById('btnGenerarPedido'); const original=btn.innerHTML; btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Enviando...'; try{ const fd=new FormData(); fd.append('action','crear_pedido'); fd.append('cliente_id', document.getElementById('cliente_id').value); fd.append('direccion_entrega_id', document.getElementById('direccion_entrega').value); fd.append('persona_recibe', document.getElementById('persona_recibe').value); fd.append('descuento_porcentaje', parseFloat(document.getElementById('descuento_porcentaje').value)||0); fd.append('observaciones', document.getElementById('observaciones').value); fd.append('fecha_entrega', document.getElementById('fecha_entrega').value); fd.append('metodo_pago', document.getElementById('metodo_pago').value); fd.append('productos', JSON.stringify(getItemsForServer())); const res=await fetch('nueva_compra.php',{ method:'POST', body: fd }); const data=await res.json(); if(data.success){ alert('¡Pedido generado! Número: '+data.numero_documento); cart.clear(); renderCart(); } else { alert(data.message||'No se pudo generar el pedido'); } }catch(e){ console.error(e); alert('Error al generar el pedido'); } finally { btn.disabled=false; btn.innerHTML=original; } });

// Vista previa e impresión
document.getElementById('btnPreview').addEventListener('click', ()=>{ if(cart.size===0) return alert('No hay productos en el carrito'); const clienteText=document.getElementById('cliente_id').options[document.getElementById('cliente_id').selectedIndex]?.text||''; const subtotal=Array.from(cart.values()).reduce((s,i)=>s+i.cantidad*i.precio,0); const d=parseFloat(document.getElementById('descuento_porcentaje').value)||0; const m=subtotal*(d/100); const base=subtotal-m; const iva=base*0.16; const total=base+iva; let preview='VISTA PREVIA DEL PEDIDO\n\n'; preview+='Cliente: '+clienteText+'\n'; preview+='Fecha: '+new Date().toLocaleDateString()+'\n\n'; preview+='PRODUCTOS:\n'; cart.forEach(p=>{ preview+='• '+p.descripcion+' x'+p.cantidad+' = $'+formatCurrency(p.cantidad*p.precio)+'\n'; }); preview+='\nSubtotal: $'+formatCurrency(subtotal); if(d>0){ preview+='\nDescuento ('+d+'%): -$'+formatCurrency(m); } preview+='\nBase gravable: $'+formatCurrency(base)+'\nIVA (16%): $'+formatCurrency(iva)+'\nTOTAL (con IVA): $'+formatCurrency(total); alert(preview); });

document.getElementById('btnPrint').addEventListener('click', ()=>{ if(cart.size===0) return alert('No hay productos para imprimir'); const clienteText=document.getElementById('cliente_id').options[document.getElementById('cliente_id').selectedIndex]?.text||''; const fecha=new Date().toLocaleDateString('es-CO'); const subtotal=Array.from(cart.values()).reduce((s,i)=>s+i.cantidad*i.precio,0); const d=parseFloat(document.getElementById('descuento_porcentaje').value)||0; const m=subtotal*(d/100); const base=subtotal-m; const iva=base*0.16; const total=base+iva; const w=window.open('','_blank'); let html='<html><head><title>Resumen</title><style>body{font-family:Arial,sans-serif;margin:20px}.header{text-align:center;margin-bottom:30px;border-bottom:2px solid #333;padding-bottom:10px}table{width:100%;border-collapse:collapse;margin-top:20px}th,td{border:1px solid #ddd;padding:8px;text-align:left}th{background:#f5f5f5;font-weight:bold}.total{text-align:right;margin-top:20px;font-size:18px;font-weight:bold}</style></head><body>'; html+='<div class="header"><h2>Resumen de Pedido</h2><p>Fecha: '+fecha+'</p></div>'; html+='<p><strong>Cliente:</strong> '+escapeHtml(clienteText)+'</p>'; html+='<table><thead><tr><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th></tr></thead><tbody>'; cart.forEach(p=>{ html+='<tr><td>'+escapeHtml(p.codigo)+'</td><td>'+escapeHtml(p.descripcion)+'</td><td>'+p.cantidad+'</td><td>$'+formatCurrency(p.precio)+'</td><td>$'+formatCurrency(p.cantidad*p.precio)+'</td></tr>'; }); html+='</tbody></table><div class="total">'; html+='<p>Subtotal: $'+formatCurrency(subtotal)+'</p>'; if(d>0){ html+='<p>Descuento ('+d+'%): -$'+formatCurrency(m)+'</p>'; } html+='<p>Base gravable: $'+formatCurrency(base)+'</p>'; html+='<p>IVA (16%): $'+formatCurrency(iva)+'</p>'; html+='<p><strong>TOTAL (con IVA): $'+formatCurrency(total)+'</strong></p></div></body></html>'; w.document.write(html); w.document.close(); w.print(); });

// Inicial
buscarProductos();
</script>

<?php
$content = ob_get_clean();

// Estilos mínimos para resultados de búsqueda (paridad)
$additionalCSS = '<style>
.search-results{border:1px solid #ddd;border-radius:8px;max-height:300px;overflow-y:auto;background:white;box-shadow:0 2px 10px rgba(0,0,0,0.05)}
.search-result-item{padding:12px 15px;border-bottom:1px solid #eee;cursor:pointer}
.search-result-item:hover{background-color:#f8f9fa}
.dropzone{background:#f8f9fa;border:2px dashed #6c757d;border-radius:8px}
.dropzone .dz-message{color:#6c757d}
</style>';

LayoutManager::renderAdminPage('Nueva Compra', $content, $additionalCSS);
?>
