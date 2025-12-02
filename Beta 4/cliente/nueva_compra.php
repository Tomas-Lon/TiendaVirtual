<?php
session_start();

if (!isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? '') !== 'cliente') {
    header('Location: ../index.php');
    exit();
}

require_once '../includes/LayoutManager.php';
require_once '../config/database.php';

// Reglas de fecha de entrega para Cliente: mínimo 5 días hábiles, máximo +1 mes calendario
function fechaEntrega5HabilesCliente() {
    $date = new DateTime();
    $added = 0;
    while ($added < 5) {
        $date->modify('+1 day');
        $dow = (int)$date->format('N'); // 1=lunes .. 7=domingo
        if ($dow <= 5) { $added++; }
    }
    return $date->format('Y-m-d');
}

$fechaMinCliente = fechaEntrega5HabilesCliente();
$fechaMaxCliente = (new DateTime())->modify('+1 month')->format('Y-m-d');

// Cargar grupos de productos para el filtro (paridad con admin)
$grupos = [];
try {
    $pdo = getConnection();
    $grupos_stmt = $pdo->query("SELECT id, nombre FROM grupos_productos WHERE activo = 1 ORDER BY nombre");
    $grupos = $grupos_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $grupos = [];
}

// Endpoints AJAX para el cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'get_direcciones_cliente') {
        try {
            $pdo = getConnection();
            $cliente_id = $_SESSION['cliente_id'] ?? 0;
            if ($cliente_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Cliente inválido']);
                exit;
            }
            $stmt = $pdo->prepare("SELECT id, nombre, direccion, ciudad, departamento, codigo_postal, contacto_receptor, documento_receptor FROM direcciones_clientes WHERE cliente_id = ? ORDER BY id DESC");
            $stmt->execute([$cliente_id]);
            $dirs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'direcciones' => $dirs]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'buscar_productos') {
        try {
            $pdo = getConnection();
            $search = trim($_POST['search'] ?? '');
            $grupo_id = $_POST['grupo_id'] ?? '';

            if ($search === '' && $grupo_id === '') {
                $sql = "SELECT p.id, p.codigo, p.descripcion, p.precio, 0 as stock_disponible, g.nombre as grupo_nombre
                        FROM productos p LEFT JOIN grupos_productos g ON p.grupo_id = g.id
                        ORDER BY p.descripcion LIMIT 20";
                $params = [];
            } else {
                $where = [];
                $params = [];
                if ($search !== '') {
                    $where[] = "(p.codigo LIKE ? OR p.descripcion LIKE ?)";
                    $params[] = "%{$search}%";
                    $params[] = "%{$search}%";
                }
                if ($grupo_id !== '') {
                    $where[] = "p.grupo_id = ?";
                    $params[] = $grupo_id;
                }
                $where_clause = $where ? ("WHERE " . implode(" AND ", $where)) : '';
                $sql = "SELECT p.id, p.codigo, p.descripcion, p.precio, 0 as stock_disponible, g.nombre as grupo_nombre
                        FROM productos p LEFT JOIN grupos_productos g ON p.grupo_id = g.id
                        {$where_clause}
                        ORDER BY p.descripcion LIMIT 50";
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'productos' => $productos]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}

ob_start();
?>

<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-file-import text-primary me-2"></i>Nueva Compra desde CSV</h2>
        <div>
            <a href="productos.php" class="btn btn-outline-secondary">
                <i class="fas fa-box-open"></i> Ver Catálogo
            </a>
        </div>
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
                            <form action="ajax/procesar_csv.php" class="dropzone border-2 border-dashed p-3 text-center" id="dzCsv">
                                <div class="dz-message text-muted">
                                    <i class="fas fa-file-csv"></i> Suelta el archivo CSV aquí o haz clic
                                </div>
                            </form>
                            <small class="text-muted">Formato: código,cantidad. Máx 2MB. Separador , o ;</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Agregar por código</label>
                            <div class="input-group">
                                <input type="text" id="addByCodeInput" class="form-control" placeholder="Código exacto">
                                <input type="number" id="addByCodeQty" class="form-control" style="max-width:110px" value="1" min="1">
                                <button class="btn btn-outline-primary" type="button" id="addByCodeBtn"><i class="fas fa-plus"></i></button>
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
            <!-- Resumen -->
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-warning"><strong>Resumen</strong></div>
                <div class="card-body">
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
                
                    
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong class="text-success fs-4" id="total_pagar">$0.00</strong>
                    </div>

                    <div class="alert alert-info p-2 mb-3">
                        <small>
                            <div class="d-flex justify-content-between">
                                <span>IVA (19%):</span>
                                <span id="iva_total">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Base gravable:</span>
                                <span id="base_gravable">$0.00</span>
                            </div>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="direccion_entrega" class="form-label fw-semibold">Dirección de Entrega *</label>
                        <select class="form-select" id="direccion_entrega" required>
                            <option value="">Cargando direcciones...</option>
                        </select>
                        <small class="text-muted">Selecciona dónde quieres recibir tu pedido.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="persona_recibe" class="form-label fw-semibold">Persona que recibe</label>
                        <input type="text" class="form-control" id="persona_recibe" placeholder="Nombre de quien recibe">
                        <small class="text-muted">Se autocompleta con datos de la dirección.</small>
                    </div>

                    <div class="mb-3">
                        <label for="observaciones" class="form-label fw-semibold">Observaciones</label>
                        <textarea class="form-control" id="observaciones" rows="3" placeholder="Notas del pedido..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="fecha_entrega" class="form-label fw-semibold">Fecha de Entrega</label>
                        <input type="date" class="form-control" id="fecha_entrega" value="<?= $fechaMinCliente ?>" min="<?= $fechaMinCliente ?>" max="<?= $fechaMaxCliente ?>">
                        <small class="text-muted">Disponible entre <?= date('d/m/Y', strtotime($fechaMinCliente)) ?> y <?= date('d/m/Y', strtotime($fechaMaxCliente)) ?></small>
                    </div>

                    <div class="d-grid gap-2 mt-2">
                        <button class="btn btn-secondary" id="btnCotizar" type="button" disabled>
                            <i class="fas fa-file-invoice-dollar"></i> Guardar como Cotización
                        </button>
                        <button class="btn btn-primary btn-lg" id="btnGenerarPedido" type="button" disabled>
                            <i class="fas fa-check-circle"></i> Generar Pedido
                        </button>
                    </div>

                    <div class="d-grid mt-3">
                        <button class="btn btn-outline-primary" id="btnToCart" type="button" disabled>
                            <i class="fas fa-save"></i> Guardar selección
                        </button>
                        <div class="small text-muted mt-2">Tu selección queda guardada incluso si cambias de panel.</div>
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

// ---------- Funciones utilitarias ----------
function formatCurrency(n){ return (Number(n)||0).toLocaleString('es-CO',{minimumFractionDigits:2}); }
function escapeHtml(text){ return text?.replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])); }

function updateStats(){
    let items=cart.size, qty=0;
    cart.forEach(v=>{ qty += v.cantidad; });
    document.getElementById('statItems').textContent = items;
    document.getElementById('statQty').textContent = qty;
}

function renderCart(){
    const c=document.getElementById('cartContainer'), b=document.getElementById('btnClearAll'), btn=document.getElementById('btnToCart');
    const btnCot=document.getElementById('btnCotizar');
    const btnPed=document.getElementById('btnGenerarPedido');
    if(cart.size===0){
        c.innerHTML=`<div class="text-center py-5 text-muted"><i class="fas fa-shopping-cart fa-3x mb-3"></i><p>No hay productos agregados</p></div>`;
        b.style.display='none'; btn.disabled=true; if(btnCot) btnCot.disabled=true; if(btnPed) btnPed.disabled=true;
    } else {
        let html='';
        cart.forEach((p,id)=>{
            html+=`
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
            </div>`;
        });
        c.innerHTML=html; b.style.display='inline-block'; btn.disabled=false; if(btnCot) btnCot.disabled=false; if(btnPed) btnPed.disabled=false;
    }
    updateStats();
    calcularTotales();
}

function removeItem(id){ cart.delete(id); renderCart(); }
function updateQty(id,q){ const p=cart.get(id); if(p){ p.cantidad=Math.max(1,parseInt(q)||1); renderCart(); } }
function mergeItems(items){
    items.forEach(it=>{
        const id=Number(it.id), ex=cart.get(id);
        const qty=Math.max(1,parseInt(it.cantidad)||1);
        if(ex){ ex.cantidad+=qty; } else { cart.set(id,{...it,cantidad:qty,precio:Number(it.precio)}); }
    });
    renderCart();
}

// Cargar direcciones del cliente
async function cargarDireccionesCliente(){
    const select=document.getElementById('direccion_entrega');
    select.disabled=true;
    select.innerHTML='<option value="">Cargando...</option>';
    try{
        const fd=new FormData();
        fd.append('action','get_direcciones_cliente');
        const res=await fetch('nueva_compra.php',{method:'POST',body:fd});
        const data=await res.json();
        if(data.success && Array.isArray(data.direcciones)){
            if(data.direcciones.length===0){
                select.innerHTML='<option value="">Sin direcciones. Ve a tu perfil para agregar una.</option>';
            } else {
                let html='<option value="">Seleccione una dirección</option>';
                data.direcciones.forEach(d=>{
                    const nombre = d.nombre ? d.nombre + ' - ' : '';
                    const parts=[nombre + d.direccion, d.ciudad||'', d.departamento||'', d.codigo_postal?('CP '+d.codigo_postal):''].filter(Boolean).join(' - ');
                    html+=`<option value="${d.id}" data-contacto="${escapeHtml(d.contacto_receptor||'')}" data-documento="${escapeHtml(d.documento_receptor||'')}">${escapeHtml(parts)}</option>`;
                });
                select.innerHTML=html;
                if(data.direcciones.length===1){
                    select.value=String(data.direcciones[0].id);
                    actualizarReceptor();
                }
                // Si estamos cargando un pedido para editar, aplicar direccion pendiente
                if (window._pendingDireccionId) {
                    const pid = String(window._pendingDireccionId);
                    const has = Array.from(select.options).some(o=>o.value===pid);
                    if (has) {
                        select.value = pid;
                        actualizarReceptor();
                        window._pendingDireccionId = null;
                    }
                }
            }
        } else {
            select.innerHTML='<option value="">No se pudieron cargar las direcciones</option>';
        }
    }catch(e){
        console.error(e);
        select.innerHTML='<option value="">Error al cargar direcciones</option>';
    }finally{
        select.disabled=false;
    }
}

// Support: if a pedido_id was requested to load, allow setting pending direccion once options are populated
// (cargarDireccionesCliente will set options then this code can sync)
if (!window._pendingDireccionId) window._pendingDireccionId = null;

// Cargar una cotización para editar/confirmar
async function loadPedidoForEdit(pedidoId){
    if (!pedidoId) return;
    try{
        const res = await fetch(`ajax/get_pedido.php?id=${pedidoId}`, { credentials: 'same-origin' });
        const data = await res.json();
        if (!data.success) return alert(data.message || 'No se pudo cargar la cotización');

        // Poner items en el carrito
        cart.clear();
        if (Array.isArray(data.detalle)){
            data.detalle.forEach(d=>{
                const id = Number(d.producto_id);
                cart.set(id, { id, codigo: d.codigo || '', descripcion: d.descripcion || '', precio: Number(d.precio)||0, cantidad: Number(d.cantidad)||1 });
            });
        }
        renderCart();

        // Cabecera
        const pk = data.pedido;
        if (pk) {
            document.getElementById('observaciones').value = pk.observaciones || '';
            document.getElementById('persona_recibe').value = (pk.observaciones && pk.observaciones.match(/Persona que recibe:\s*(.*)/)) ? (pk.observaciones.match(/Persona que recibe:\s*(.*)/)[1]) : '';
            if (pk.fecha_entrega_estimada) document.getElementById('fecha_entrega').value = pk.fecha_entrega_estimada;
            // Defer direccion set until select is populated
            window._pendingDireccionId = pk.direccion_entrega_id || null;
        }

        // Mark editing mode
        window._editingPedidoId = Number(pedidoId);
        // Change button text so user knows we'll confirm existing
        const btn = document.getElementById('btnGenerarPedido');
        if (btn) btn.innerHTML = '<i class="fas fa-check-circle"></i> Confirmar Pedido';

    }catch(e){ console.error('Error loading pedido:', e); alert('Error cargando la cotización'); }
}

// Actualizar datos del receptor al cambiar dirección
function actualizarReceptor(){
    const select=document.getElementById('direccion_entrega');
    const option=select.options[select.selectedIndex];
    if(option && option.value){
        document.getElementById('persona_recibe').value=option.dataset.contacto||'';
    } else {
        document.getElementById('persona_recibe').value='';
    }
}

// Verificar formulario
function verificarFormulario(){
    const dir=document.getElementById('direccion_entrega').value;
    const tiene=cart.size>0;
    const ok=!!dir && tiene;
    document.getElementById('btnCotizar').disabled=!ok;
    document.getElementById('btnGenerarPedido').disabled=!ok;
}

function getItemsArray(){
    const items=[]; cart.forEach(v=>items.push({producto_id:v.id, cantidad:v.cantidad}));
    return items;
}
// Búsqueda de productos
document.getElementById('search_producto').addEventListener('input', (e)=>{
    if(e.target.value.trim().length>=2){ buscarProductos(); } else { document.getElementById('productos_encontrados').innerHTML=''; }
});
document.getElementById('filtro_grupo').addEventListener('change', buscarProductos);
document.getElementById('search_producto').addEventListener('keypress', (e)=>{ if(e.key==='Enter'){ buscarProductos(); }});

async function buscarProductos(){
    const search = document.getElementById('search_producto').value.trim();
    const grupo_id = document.getElementById('filtro_grupo').value;
    try{
        const fd = new FormData();
        fd.append('action','buscar_productos');
        fd.append('search', search);
        fd.append('grupo_id', grupo_id);
        const res = await fetch('nueva_compra.php', { method:'POST', body: fd });
        const data = await res.json();
        if(data.success){ mostrarProductos(data.productos||[]); }
        else { document.getElementById('productos_encontrados').innerHTML = '<div class="alert alert-warning mb-0">'+(data.message||'Sin resultados')+'</div>'; }
    }catch(err){ console.error(err); document.getElementById('productos_encontrados').innerHTML = '<div class="alert alert-warning mb-0">Error al buscar productos</div>'; }
}

function mostrarProductos(productos){
    const container = document.getElementById('productos_encontrados');
    if(!productos || productos.length===0){ container.innerHTML='<div class="alert alert-info mb-0">No se encontraron productos</div>'; return; }
    let html = '<div class="card"><div class="card-header bg-light d-flex justify-content-between align-items-center" style="cursor:pointer" onclick="toggleResultados()"><span><i class="fas fa-list me-2"></i><strong>Resultados de búsqueda</strong></span><i class="fas fa-chevron-down" id="iconToggleResultados"></i></div><div class="search-results show" id="listaResultados">';
    productos.forEach(p=>{
        const codigo = escapeHtml(p.codigo);
        const desc = escapeHtml(p.descripcion);
        const grupo = escapeHtml(p.grupo_nombre||'Sin grupo');
        html += `<div class="search-result-item" onclick="agregarDesdeBusqueda(${p.id}, '${codigo}', '${desc}', ${p.precio})"><div class="d-flex justify-content-between align-items-start"><div><strong>${desc}</strong><br><small class="text-muted">Código: ${codigo} | U.Med: ${escapeHtml(p.unidad_medida||'und')}</small><br><small class="text-info">Grupo: ${grupo}</small></div><div class="text-end"><div class="fw-bold text-primary">$${formatCurrency(p.precio)}</div><small class="text-muted">Stock: ${p.stock_disponible||0}</small></div></div></div>`;
    });
    html += '</div></div>';
    container.innerHTML = html;
}

function toggleResultados(){ const lista=document.getElementById('listaResultados'); const icon=document.getElementById('iconToggleResultados'); if(lista && icon){ if(lista.classList.contains('show')){ lista.classList.remove('show'); icon.classList.remove('fa-chevron-down'); icon.classList.add('fa-chevron-up'); } else { lista.classList.add('show'); icon.classList.remove('fa-chevron-up'); icon.classList.add('fa-chevron-down'); } } }

function agregarDesdeBusqueda(id, codigo, descripcion, precio){
    const qty = Math.max(1, parseInt(document.getElementById('cantidad').value)||1);
    const ex = cart.get(id);
    if(ex){ ex.cantidad += qty; }
    else { cart.set(id, { id, codigo, descripcion, precio:Number(precio), cantidad:qty }); }
    renderCart();
    document.getElementById('search_producto').value='';
    document.getElementById('productos_encontrados').innerHTML='';
}

// Cálculos financieros
function calcularTotales(){
    const subtotal = Array.from(cart.values()).reduce((s,it)=> s + (it.cantidad*it.precio), 0);
    const neto = subtotal;
    const iva = neto * 0.19;
    const total = neto + iva;
    document.getElementById('subtotal').textContent = '$'+formatCurrency(subtotal);
    document.getElementById('iva_total').textContent = '$'+formatCurrency(iva);
    document.getElementById('base_gravable').textContent = '$'+formatCurrency(neto);
    document.getElementById('total_pagar').textContent = '$'+formatCurrency(total);
}

async function persistSelectionSilently(){
    if(cart.size===0) return;
    try{
        await fetch('ajax/guardar_carrito.php',{
            method:'POST',
            credentials:'same-origin',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ items: getItemsArray() })
        });
        // No UI disruption; silent persistence
    }catch(e){ console.error('No se pudo guardar automáticamente el carrito', e); }
}

// ---------- Dropzones ----------
new Dropzone('#dzCsv',{
    paramName:'file', maxFilesize:2, acceptedFiles:'.csv,text/csv', maxFiles:1,
    dictDefaultMessage:'Suelta el archivo CSV aquí o haz clic',
    timeout:60000,
    success:function(file,res){
        try{
            const r=typeof res==='string'?JSON.parse(res):res;
            if(r && r.success){
                if(Array.isArray(r.items)) {
                    mergeItems(r.items);
                    // Guardar inmediatamente la selección en el carrito de sesión
                    persistSelectionSilently();
                }
                if(r.unknown && r.unknown.length) alert('Códigos no encontrados: '+r.unknown.join(', '));
                if(r.warnings && r.warnings.length) console.warn('CSV warnings:', r.warnings);
            } else {
                alert((r && r.message) ? r.message : 'Error al procesar CSV');
            }
        }catch(e){ console.error(e); alert('Respuesta inválida del servidor'); }
        this.removeAllFiles(true);
    },
    error:function(file, message){
        console.error('Dropzone error:', message);
        alert(typeof message==='string'?message:(message?.message||'Error al subir CSV'));
    }
});

// ---------- Acciones ----------
document.getElementById('btnClearAll').addEventListener('click',()=>{ if(confirm('¿Limpiar todo?')){ cart.clear(); renderCart(); } });

document.getElementById('addByCodeBtn').addEventListener('click',async ()=>{
    const code=document.getElementById('addByCodeInput').value.trim();
    let qty=parseInt(document.getElementById('addByCodeQty').value)||1;
    if(!code) return alert('Ingrese un código');
    try{
        const fd=new FormData(); fd.append('codigo',code);
        const res=await fetch('ajax/buscar_por_codigo.php',{method:'POST',body:fd,credentials:'same-origin'});
        const data=await res.json();
        if(data.success && data.producto){ mergeItems([{...data.producto,cantidad:qty}]); }
        else alert(data.message||'Producto no encontrado');
    }catch(e){ console.error(e); alert('Error al buscar producto'); }
});

document.getElementById('btnToCart').addEventListener('click',async ()=>{
    if(cart.size===0) return;
    const items=[]; cart.forEach(v=>items.push({producto_id:v.id,cantidad:v.cantidad}));
    try{
        const res=await fetch('ajax/guardar_carrito.php',{
            method:'POST',credentials:'same-origin',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({items})
        });
        const data=await res.json();
        if(data.success){ alert('Selección guardada. Puedes salir y regresar cuando quieras.'); }
        else alert(data.message||'No se pudo guardar el carrito');
    }catch(e){ console.error(e); alert('Error al guardar el carrito'); }
});

// Evento para actualizar receptor al cambiar dirección
document.getElementById('direccion_entrega').addEventListener('change', actualizarReceptor);

// Crear cotización desde el cliente
document.getElementById('btnCotizar').addEventListener('click', async ()=>{
    if(cart.size===0) return alert('No hay productos para cotizar');
    const direccion_id = document.getElementById('direccion_entrega').value;
    if(!direccion_id) return alert('Debes seleccionar una dirección de entrega');
    
    const btn = document.getElementById('btnCotizar');
    const original = btn.innerHTML; btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Creando...';
    try{
        const items = getItemsArray();
        const observaciones = document.getElementById('observaciones').value;
        const fecha_entrega = document.getElementById('fecha_entrega').value;
        const persona_recibe = document.getElementById('persona_recibe').value;
        const res = await fetch('ajax/crear_documento.php',{
            method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ action:'crear_cotizacion', items, observaciones, fecha_entrega, direccion_entrega_id: direccion_id, persona_recibe })
        });
        const data = await res.json();
        if(data.success){
            alert('¡Cotización creada! Número: '+data.numero_documento);
        } else {
            alert(data.message||'No se pudo crear la cotización');
        }
    }catch(e){ console.error(e); alert('Error al crear la cotización'); }
    finally{ btn.disabled=false; btn.innerHTML=original; }
});

// Generar pedido desde el cliente
document.getElementById('btnGenerarPedido').addEventListener('click', async ()=>{
    if(cart.size===0) return alert('No hay productos para generar pedido');
    const direccion_id = document.getElementById('direccion_entrega').value;
    if(!direccion_id) return alert('Debes seleccionar una dirección de entrega');
    
    const btn = document.getElementById('btnGenerarPedido');
    const original = btn.innerHTML; btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Enviando...';
    try{
        const items = getItemsArray();
        const observaciones = document.getElementById('observaciones').value;
        const fecha_entrega = document.getElementById('fecha_entrega').value;
        const persona_recibe = document.getElementById('persona_recibe').value;

        if (window._editingPedidoId) {
            // Confirmar y actualizar cotización existente
            const payload = {
                action: 'confirmar_pedido_existente',
                pedido_id: window._editingPedidoId,
                items: items,
                observaciones: observaciones,
                fecha_entrega: fecha_entrega,
                direccion_entrega_id: direccion_id,
                persona_recibe: persona_recibe,
                descuento_porcentaje: parseFloat(document.getElementById('descuento_porcentaje')?.value)||0
            };
            const res = await fetch('ajax/crear_documento.php',{ method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
            const data = await res.json();
            if (data.success){
                alert('¡Pedido confirmado! Número: '+data.numero_documento);
                window.location.href = 'pedidos.php';
            } else {
                alert(data.message||'No se pudo confirmar la cotización');
            }
        } else {
            const res = await fetch('ajax/crear_documento.php',{
                method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'},
                body: JSON.stringify({ action:'crear_pedido', items, observaciones, fecha_entrega, direccion_entrega_id: direccion_id, persona_recibe })
            });
            const data = await res.json();
            if(data.success){
                alert('¡Pedido generado! Número: '+data.numero_documento);
            } else {
                alert(data.message||'No se pudo generar el pedido');
            }
        }
    }catch(e){ console.error(e); alert('Error al generar el pedido'); }
    finally{ btn.disabled=false; btn.innerHTML=original; }
});

// Cargar carrito persistido de la sesión al abrir
(async function preloadFromSession(){
    try{
        const res = await fetch('ajax/get_virtual_cart.php', { credentials:'same-origin' });
        const data = await res.json();
        if(data.success && Array.isArray(data.items) && data.items.length){
            mergeItems(data.items);
        } else {
            renderCart();
        }
    }catch(e){ console.error(e); renderCart(); }
})();

// Cargar direcciones al inicio
cargarDireccionesCliente();

// Inicial: cargar algunos productos sugeridos
buscarProductos();

// Si la URL incluye ?pedido_id=..., cargar la cotización para editar/confirmar
try{
    const _params = new URLSearchParams(window.location.search);
    const _pid = _params.get('pedido_id');
    if (_pid) loadPedidoForEdit(_pid);
}catch(e){ /* no-op en navegadores antiguos */ }
</script>

<?php
$content = ob_get_clean();
// Estilos mínimos para resultados de búsqueda paridad
$additionalCSS = '<style>
.search-results{max-height:300px;overflow-y:auto;background:white;transition:max-height 0.3s ease, opacity 0.3s ease}
.search-results:not(.show){max-height:0;overflow:hidden;opacity:0}
.search-result-item{padding:12px 15px;border-bottom:1px solid #eee;cursor:pointer}
.search-result-item:hover{background-color:#f8f9fa}
.dropzone{background:#f8f9fa;border:2px dashed #6c757d;border-radius:8px}
.dropzone .dz-message{color:#6c757d}
</style>';
LayoutManager::renderAdminPage('Nueva Compra', $content, $additionalCSS);
?>
