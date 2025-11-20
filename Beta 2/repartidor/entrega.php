<?php
session_start();

// Verificar que el usuario esté logueado y sea repartidor
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'empleado' || $_SESSION['cargo'] !== 'repartidor') {
    header('Location: ../index.php');
    exit();
}

require_once '../config/database.php';

$entrega_id = $_GET['id'] ?? null;
if (!$entrega_id) {
    header('Location: dashboard.php');
    exit();
}

try {
    $pdo = getConnection();
    
    // Obtener datos del envío
    $stmt = $pdo->prepare("
        SELECT e.*, 
               c.nombre as cliente_nombre, c.email as cliente_email, c.telefono,
               p.numero_documento,
               d.direccion, d.ciudad, d.contacto_receptor, d.documento_receptor
        FROM envios e
        INNER JOIN pedidos p ON e.pedido_id = p.id
        INNER JOIN clientes c ON p.cliente_id = c.id
        LEFT JOIN direcciones_clientes d ON e.direccion_entrega_id = d.id
        WHERE e.id = ? AND e.repartidor_id = ?
    ");
    $stmt->execute([$entrega_id, $_SESSION['empleado_id']]);
    $entrega = $stmt->fetch();
    
    if (!$entrega) {
        header('Location: dashboard.php');
        exit();
    }
    
} catch (PDOException $e) {
    error_log("Error en entrega.php: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Formulario de Entrega - SolTecnInd</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        body { 
            background-color: #f8f9fa; 
            padding-bottom: 50px;
            -webkit-user-select: none;
            -webkit-touch-callout: none;
        }
        .form-section { 
            background: white; 
            padding: 15px; 
            margin-bottom: 15px; 
            border-radius: 10px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.1); 
        }
        .section-title { 
            color: #27ae60; 
            border-bottom: 3px solid #27ae60; 
            padding-bottom: 10px; 
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        .form-label { font-weight: 600; color: #333; }
        .form-control, .form-select {
            font-size: 16px; /* Evita zoom en iOS */
            touch-action: manipulation;
        }
        #canvas-firma { 
            border: 2px solid #ddd; 
            border-radius: 5px; 
            background: white; 
            cursor: crosshair;
            width: 100%;
            height: 200px;
            touch-action: none;
            -webkit-user-select: none;
        }
        .signature-container { margin-top: 10px; }
        #photo-preview { 
            max-width: 100%; 
            max-height: 400px; 
            width: 100%;
            object-fit: contain;
            border-radius: 8px; 
            margin-top: 10px; 
        }
        .camera-button { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-size: 1.1rem;
            padding: 12px;
        }
        .camera-button:hover { background: linear-gradient(135deg, #764ba2 0%, #667eea 100%); }
        .btn-primary { background-color: #27ae60; border-color: #27ae60; }
        .btn-primary:hover { background-color: #229954; }
        .info-box { 
            background: #e8f5e9; 
            border-left: 4px solid #27ae60; 
            padding: 12px; 
            border-radius: 5px; 
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        .loading-spinner { 
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255,255,255,0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 9999;
            text-align: center;
        }
        .navbar { padding: 0.75rem 1rem; }
        .btn-lg { font-size: 1.1rem; padding: 14px; }
        
        /* Mejoras para dispositivos móviles */
        @media (max-width: 768px) {
            .container { padding-left: 10px; padding-right: 10px; }
            .form-section { padding: 12px; margin-bottom: 12px; }
            .section-title { font-size: 1.1rem; }
            .btn { padding: 10px 16px; }
            #canvas-firma { height: 180px; }
        }
        
        /* Prevenir el bounce en iOS */
        html, body {
            overscroll-behavior-y: contain;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><i class="fas fa-arrow-left"></i> Volver</a>
            <span class="navbar-text text-light">Formulario de Entrega</span>
        </div>
    </nav>

    <div class="container">
        <!-- Información de la entrega -->
        <div class="form-section">
            <h3 class="section-title"><i class="fas fa-box"></i> Información de Entrega</h3>
            
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Cliente:</strong> <?php echo htmlspecialchars($entrega['cliente_nombre']); ?></p>
                    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($entrega['telefono']); ?></p>
                </div>
                <div class="col-md-6">
                    <?php if ($entrega['numero_documento']): ?>
                        <p><strong>Pedido:</strong> <?php echo htmlspecialchars($entrega['numero_documento']); ?></p>
                    <?php endif; ?>
                    <?php if ($entrega['fecha_programada']): ?>
                        <p><strong>Fecha Programada:</strong> <?php echo date('d/m/Y', strtotime($entrega['fecha_programada'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <p><strong>Dirección:</strong> <?php echo htmlspecialchars($entrega['direccion']); ?></p>
            <p><strong>Ciudad:</strong> <?php echo htmlspecialchars($entrega['ciudad']); ?></p>
        </div>

        <!-- Formulario de entrega -->
        <form id="entregaForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="entrega_id" value="<?php echo $entrega['id']; ?>">
            <input type="hidden" id="firma_data" name="firma_data">
            <input type="hidden" id="foto_data" name="foto_data">
            <input type="hidden" id="ubicacion_lat" name="ubicacion_lat">
            <input type="hidden" id="ubicacion_lng" name="ubicacion_lng">

            <!-- Paso 1: Datos del receptor -->
            <div class="form-section step-1">
                <h3 class="section-title"><i class="fas fa-user-check"></i> Datos de Quién Recibe</h3>
                
                <div class="info-box">
                    <i class="fas fa-info-circle"></i> Completa los datos de la persona que recibe el paquete
                </div>

                <div class="mb-3">
                    <label for="receptor_nombre" class="form-label">Nombre de quien recibe *</label>
                    <input type="text" class="form-control" id="receptor_nombre" name="receptor_nombre" required placeholder="Nombre completo">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="receptor_documento" class="form-label">Documento (Opcional)</label>
                        <input type="text" class="form-control" id="receptor_documento" name="receptor_documento" placeholder="CC/NIT">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="receptor_email" class="form-label">Email (Opcional)</label>
                        <input type="email" class="form-control" id="receptor_email" name="receptor_email" placeholder="email@ejemplo.com">
                    </div>
                </div>
            </div>

            <!-- Paso 2: Foto de evidencia -->
            <div class="form-section step-2">
                <h3 class="section-title"><i class="fas fa-camera"></i> Foto de Evidencia</h3>
                
                <div class="info-box">
                    <i class="fas fa-lightbulb"></i> <strong>Importante:</strong> Toma una foto del cliente con el paquete o del paquete entregado como evidencia
                </div>

                <div class="mb-3">
                    <button type="button" class="btn camera-button text-white w-100 mb-3" id="btnCapturarFoto">
                        <i class="fas fa-camera fa-lg"></i> <span class="ms-2">Abrir Cámara</span>
                    </button>
                    
                    <input type="file" id="fotoInput" accept="image/*" capture="environment" style="display:none;">
                    
                    <div id="photoPreviewContainer" class="text-center">
                        <img id="photo-preview" class="img-fluid" style="display:none;">
                        <div class="mt-2">
                            <button type="button" class="btn btn-danger" id="btnEliminarFoto" style="display:none;">
                                <i class="fas fa-trash"></i> Eliminar y Tomar Otra
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paso 3: Firma del cliente -->
            <div class="form-section step-3">
                <h3 class="section-title"><i class="fas fa-pen-fancy"></i> Firma del Receptor *</h3>
                
                <div class="info-box">
                    <i class="fas fa-info-circle"></i> <strong>Requerido:</strong> Solicita al cliente o receptor que firme con su dedo en el área blanca de abajo
                </div>

                <div class="signature-container">
                    <label class="form-label">
                        <i class="fas fa-signature"></i> Área de Firma (Dibuja con tu dedo)
                    </label>
                    <div style="border: 3px dashed #27ae60; border-radius: 8px; padding: 5px; background: #f8f9fa;">
                        <canvas id="canvas-firma"></canvas>
                    </div>
                    <div class="mt-2 d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-hand-pointer"></i> Usa tu dedo para firmar
                        </small>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="btnLimpiarFirma">
                            <i class="fas fa-eraser"></i> Limpiar Firma
                        </button>
                    </div>
                </div>
            </div>

            <!-- Paso 4: Ubicación y observaciones -->
            <div class="form-section step-4">
                <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> Ubicación y Observaciones</h3>
                
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-map-pin"></i> Ubicación GPS (Recomendada)
                    </label>
                    <button type="button" class="btn btn-outline-primary w-100" id="btnObtenerUbicacion">
                        <i class="fas fa-location-crosshairs"></i> Obtener Mi Ubicación Actual
                    </button>
                    <div id="ubicacionInfo" class="alert alert-success mt-2" style="display:none;">
                        <i class="fas fa-check-circle"></i> <strong>Ubicación obtenida</strong>
                        <div class="small mt-1" id="ubicacionText"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="observaciones" class="form-label">
                        <i class="fas fa-comment-dots"></i> Observaciones (Opcional)
                    </label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3" placeholder="Ejemplo: Entregado al vecino del apartamento 302, Cliente solicitó dejar en portería, etc."></textarea>
                    <small class="text-muted">Agrega cualquier detalle importante sobre la entrega</small>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="form-section d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg shadow" id="btnGuardar">
                    <i class="fas fa-check-circle fa-lg"></i> <strong>Completar Entrega</strong>
                </button>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>

        <!-- Spinner de carga -->
        <div class="loading-spinner" id="loadingSpinner">
            <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Procesando...</span>
            </div>
            <h5 class="mt-3 mb-2">Procesando Entrega...</h5>
            <p class="text-muted">Por favor espera un momento</p>
        </div>
    </div>

    <!-- Modal de cámara -->
    <video id="cameraVideo" style="display:none; max-width:100%;" autoplay playsinline></video>
    <canvas id="canvasCapture" style="display:none;"></canvas>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    
    <script>
        let signaturePad;
        let fotoCapturada = false;
        let firmaCapturada = false;

        // Inicializar Signature Pad
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('canvas-firma');
            
            // Configurar canvas para dispositivos móviles
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                
                // Si ya existe signature pad, limpiar y recrear
                if (signaturePad) {
                    const data = signaturePad.toData();
                    signaturePad.clear();
                    if (data && data.length > 0) {
                        signaturePad.fromData(data);
                    }
                }
            }
            
            resizeCanvas();
            
            // Inicializar SignaturePad con configuración mejorada
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)',
                minWidth: 1,
                maxWidth: 3,
                throttle: 0,
                velocityFilterWeight: 0.7
            });
            
            // Evitar scroll al firmar en móviles
            canvas.addEventListener('touchstart', function(e) {
                if (e.target === canvas) {
                    e.preventDefault();
                }
            }, { passive: false });
            
            canvas.addEventListener('touchmove', function(e) {
                if (e.target === canvas) {
                    e.preventDefault();
                }
            }, { passive: false });
            
            // Redimensionar en orientación
            window.addEventListener('resize', resizeCanvas);
            window.addEventListener('orientationchange', function() {
                setTimeout(resizeCanvas, 100);
            });
            
            // Precargar datos del receptor si existen
            <?php if (!empty($entrega['contacto_receptor'])): ?>
                document.getElementById('receptor_nombre').value = '<?php echo addslashes($entrega['contacto_receptor']); ?>';
            <?php endif; ?>
            <?php if (!empty($entrega['documento_receptor'])): ?>
                document.getElementById('receptor_documento').value = '<?php echo addslashes($entrega['documento_receptor']); ?>';
            <?php endif; ?>
            
            // Obtener ubicación automáticamente al cargar
            obtenerUbicacion();
            
            // Eventos
            document.getElementById('btnCapturarFoto').addEventListener('click', capturarFoto);
            document.getElementById('btnLimpiarFirma').addEventListener('click', limpiarFirma);
            document.getElementById('btnObtenerUbicacion').addEventListener('click', obtenerUbicacion);
            document.getElementById('fotoInput').addEventListener('change', procesarFoto);
            document.getElementById('btnEliminarFoto').addEventListener('click', eliminarFoto);
            document.getElementById('entregaForm').addEventListener('submit', guardarEntrega);
        });

        // Capturar foto
        function capturarFoto() {
            document.getElementById('fotoInput').click();
        }

        // Procesar foto seleccionada
        function procesarFoto(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validar tamaño (máximo 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('La foto es demasiado grande. Por favor selecciona una imagen menor a 5MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    // Comprimir imagen si es muy grande
                    const maxWidth = 1200;
                    const maxHeight = 1200;
                    let width = img.width;
                    let height = img.height;

                    if (width > maxWidth || height > maxHeight) {
                        if (width > height) {
                            height = (height / width) * maxWidth;
                            width = maxWidth;
                        } else {
                            width = (width / height) * maxHeight;
                            height = maxHeight;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    // Convertir a base64 con calidad reducida
                    const compressedImage = canvas.toDataURL('image/jpeg', 0.8);
                    
                    const preview = document.getElementById('photo-preview');
                    preview.src = compressedImage;
                    preview.style.display = 'block';
                    document.getElementById('btnEliminarFoto').style.display = 'inline-block';
                    document.getElementById('foto_data').value = compressedImage;
                    fotoCapturada = true;
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        // Eliminar foto
        function eliminarFoto() {
            document.getElementById('photo-preview').style.display = 'none';
            document.getElementById('btnEliminarFoto').style.display = 'none';
            document.getElementById('fotoInput').value = '';
            document.getElementById('foto_data').value = '';
            fotoCapturada = false;
        }

        // Limpiar firma
        function limpiarFirma() {
            signaturePad.clear();
            firmaCapturada = false;
        }

        // Obtener ubicación GPS
        function obtenerUbicacion() {
            if (!navigator.geolocation) {
                alert('Geolocalización no disponible en tu dispositivo');
                return;
            }

            const btn = document.getElementById('btnObtenerUbicacion');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Obteniendo...';

            // Opciones de geolocalización mejoradas para móviles
            const options = {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            };

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const accuracy = position.coords.accuracy;
                    
                    document.getElementById('ubicacion_lat').value = lat;
                    document.getElementById('ubicacion_lng').value = lng;
                    
                    document.getElementById('ubicacionInfo').style.display = 'block';
                    document.getElementById('ubicacionText').innerHTML = `
                        <strong>Coordenadas:</strong> ${lat.toFixed(6)}, ${lng.toFixed(6)}<br>
                        <small>Precisión: ${Math.round(accuracy)}m</small>
                    `;
                    
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Ubicación Obtenida ✓';
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-success');
                },
                function(error) {
                    let errorMsg = 'Error desconocido';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMsg = 'Permiso de ubicación denegado. Por favor habilita los permisos de ubicación.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMsg = 'Ubicación no disponible. Verifica tu GPS.';
                            break;
                        case error.TIMEOUT:
                            errorMsg = 'Tiempo de espera agotado. Intenta nuevamente.';
                            break;
                    }
                    alert('Error al obtener ubicación: ' + errorMsg);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-location-crosshairs"></i> Reintentar Ubicación';
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-primary');
                },
                options
            );
        }

        // Guardar entrega
        function guardarEntrega(event) {
            event.preventDefault();

            // Validaciones
            const receptorNombre = document.getElementById('receptor_nombre').value.trim();
            if (!receptorNombre) {
                alert('Por favor ingresa el nombre de quien recibe');
                document.getElementById('receptor_nombre').focus();
                return;
            }

            if (!fotoCapturada) {
                if (!confirm('⚠️ No has capturado una foto.\n\n¿Deseas continuar sin foto de evidencia?')) {
                    return;
                }
            }

            if (signaturePad.isEmpty()) {
                alert('⚠️ Por favor captura la firma del cliente antes de continuar.');
                document.getElementById('canvas-firma').scrollIntoView({ behavior: 'smooth' });
                return;
            }

            // Validar ubicación
            const lat = document.getElementById('ubicacion_lat').value;
            const lng = document.getElementById('ubicacion_lng').value;
            if (!lat || !lng) {
                if (!confirm('⚠️ No se ha obtenido la ubicación GPS.\n\n¿Deseas continuar sin ubicación?')) {
                    return;
                }
            }

            // Capturar firma en alta calidad
            document.getElementById('firma_data').value = signaturePad.toDataURL('image/png');
            firmaCapturada = true;

            // Mostrar spinner y bloquear interfaz
            const spinner = document.getElementById('loadingSpinner');
            spinner.style.display = 'block';
            document.getElementById('btnGuardar').disabled = true;

            // Enviar datos
            const formData = new FormData(document.getElementById('entregaForm'));

            fetch('ajax/procesar_entrega.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                spinner.style.display = 'none';
                
                if (data.success) {
                    // Mostrar mensaje de éxito
                    alert('✅ ENTREGA COMPLETADA EXITOSAMENTE\n\n' +
                          '📄 Código: ' + (data.codigo_qr || 'Generado') + '\n' +
                          '📧 Comprobante enviado al cliente\n\n' +
                          'Redirigiendo al dashboard...');
                    
                    // Redirigir después de 1 segundo
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1000);
                } else {
                    alert('❌ ERROR: ' + (data.message || 'Error desconocido'));
                    document.getElementById('btnGuardar').disabled = false;
                }
            })
            .catch(error => {
                spinner.style.display = 'none';
                console.error('Error:', error);
                alert('❌ Error de conexión:\n' + error.message + '\n\nPor favor verifica tu conexión e intenta nuevamente.');
                document.getElementById('btnGuardar').disabled = false;
            });
        }
        
        // Prevenir pérdida de datos al salir
        window.addEventListener('beforeunload', function(e) {
            if (fotoCapturada || !signaturePad.isEmpty()) {
                e.preventDefault();
                e.returnValue = '¿Estás seguro de salir? Perderás los datos capturados.';
                return e.returnValue;
            }
        });
    </script>
</body>
</html>
