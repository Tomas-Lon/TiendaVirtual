/**
 * Sistema de Reportes JavaScript - Versión Final
 * 
 * Clase ES6 que maneja toda la interfaz de usuario del sistema de reportes
 * Incluye AJAX, manejo de errores, exportación y visualización de datos
 * 
 * @author Sistema de Tienda Virtual
 * @version 2.0
 */

class SistemaReportes {
    constructor() {
        this.datosActuales = null;
        this.tipoActual = null;
        this.baseUrl = window.location.href;
        this.init();
    }

    /**
     * Inicializa el sistema de reportes
     */
    init() {
        console.log('🚀 Sistema de Reportes v2.0 - Iniciado correctamente');
        this.configurarEventos();
        this.cargarEstadisticasGenerales(); // Cargar estadísticas al iniciar
    }

    /**
     * Configura los event listeners
     */
    configurarEventos() {
        // Event listener para cambios de tipo de reporte
        const tipoSelect = document.getElementById('tipo_reporte');
        if (tipoSelect) {
            tipoSelect.addEventListener('change', () => {
                this.mostrarCamposEspecificos();
            });
        }
    }

    /**
     * Muestra campos específicos según el tipo de reporte
     */
    mostrarCamposEspecificos() {
        const tipo = document.getElementById('tipo_reporte').value;
        const camposTrimestral = document.getElementById('campos_trimestral');
        
        if (camposTrimestral) {
            camposTrimestral.style.display = tipo === 'trimestral' ? 'block' : 'none';
        }
    }

    /**
     * Carga las estadísticas generales del mes actual
     */
    async cargarEstadisticasGenerales() {
        try {
            const fechaInicio = new Date();
            fechaInicio.setDate(1); // Primer día del mes
            const fechaFin = new Date(); // Hoy
            
            const formData = new FormData();
            formData.append('action', 'estadisticas_generales');
            formData.append('fecha_inicio', fechaInicio.toISOString().split('T')[0]);
            formData.append('fecha_fin', fechaFin.toISOString().split('T')[0]);

            const response = await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                this.mostrarEstadisticasGenerales(data.data);
            }
        } catch (error) {
            console.error('Error al cargar estadísticas generales:', error);
        }
    }

    /**
     * Muestra las estadísticas generales en el dashboard
     */
    mostrarEstadisticasGenerales(stats) {
        document.getElementById('stat_pedidos').textContent = this.formatNumber(stats.total_pedidos || 0);
        document.getElementById('stat_ventas').textContent = '$' + this.formatNumber(stats.ventas_totales || 0);
        document.getElementById('stat_clientes').textContent = this.formatNumber(stats.clientes_unicos || 0);
        document.getElementById('stat_promedio').textContent = '$' + this.formatNumber(stats.promedio_venta || 0);
    }

    async generarReporte() {
        const tipo = document.getElementById('tipo_reporte').value;
        this.tipoActual = tipo;

        this.mostrarLoading(true);

        try {
            switch (tipo) {
                case 'productos':
                    await this.generarReporteProductos();
                    break;
                case 'clientes':
                    await this.generarReporteClientes();
                    break;
                case 'estados':
                    await this.generarReporteEstados();
                    break;
                case 'empleados':
                    await this.generarReporteEmpleados();
                    break;
                case 'ventas_diarias':
                    await this.generarReporteVentasDiarias();
                    break;
                case 'trimestral':
                    await this.generarReporteTrimestral();
                    break;
                default:
                    this.mostrarError('Tipo de reporte no válido');
            }
        } catch (error) {
            this.mostrarError('Error al generar reporte: ' + error.message);
        } finally {
            this.mostrarLoading(false);
        }
    }

    async generarReporteProductos() {
        const formData = new FormData();
        formData.append('action', 'productos_mas_vendidos');
        formData.append('fecha_inicio', document.getElementById('fecha_inicio').value);
        formData.append('fecha_fin', document.getElementById('fecha_fin').value);
        formData.append('limite', document.getElementById('limite').value);

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            this.mostrarReporteProductos(data.data);
            this.datosActuales = { data: data.data, tipo: 'productos' };
        } else {
            this.mostrarError(data.error || 'Error al generar reporte de productos');
        }
    }

    async generarReporteClientes() {
        const formData = new FormData();
        formData.append('action', 'clientes_top');
        formData.append('fecha_inicio', document.getElementById('fecha_inicio').value);
        formData.append('fecha_fin', document.getElementById('fecha_fin').value);
        formData.append('limite', document.getElementById('limite').value);

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            this.mostrarReporteClientes(data.data);
            this.datosActuales = { data: data.data, tipo: 'clientes' };
        } else {
            this.mostrarError(data.error || 'Error al generar reporte de clientes');
        }
    }

    async generarReporteEstados() {
        const formData = new FormData();
        formData.append('action', 'estados_pedidos');
        formData.append('fecha_inicio', document.getElementById('fecha_inicio').value);
        formData.append('fecha_fin', document.getElementById('fecha_fin').value);

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            this.mostrarReporteEstados(data.data);
            this.datosActuales = { data: data.data, tipo: 'estados' };
        } else {
            this.mostrarError(data.error || 'Error al generar reporte de estados');
        }
    }

    async generarReporteEmpleados() {
        const formData = new FormData();
        formData.append('action', 'ventas_empleados');
        formData.append('fecha_inicio', document.getElementById('fecha_inicio').value);
        formData.append('fecha_fin', document.getElementById('fecha_fin').value);

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            this.mostrarReporteEmpleados(data.data);
            this.datosActuales = { data: data.data, tipo: 'empleados' };
        } else {
            this.mostrarError(data.error || 'Error al generar reporte de empleados');
        }
    }

    async generarReporteVentasDiarias() {
        const formData = new FormData();
        formData.append('action', 'ventas_diarias');
        formData.append('fecha_inicio', document.getElementById('fecha_inicio').value);
        formData.append('fecha_fin', document.getElementById('fecha_fin').value);

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            this.mostrarReporteVentasDiarias(data.data);
            this.datosActuales = { data: data.data, tipo: 'ventas_diarias' };
        } else {
            this.mostrarError(data.error || 'Error al generar reporte de ventas diarias');
        }
    }

    async generarReporteTrimestral() {
        const formData = new FormData();
        formData.append('action', 'reporte_trimestral');
        formData.append('año', document.getElementById('año_trimestre').value);
        formData.append('trimestre', document.getElementById('numero_trimestre').value);

        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            this.mostrarReporteTrimestral(data.data);
            this.datosActuales = { data: data.data, tipo: 'trimestral' };
        } else {
            this.mostrarError(data.error || 'Error al generar reporte trimestral');
        }
    }

    mostrarReporteProductos(datos) {
        document.getElementById('titulo_reporte').innerHTML = 
            '<i class="fas fa-box"></i> Productos Más Vendidos';

        if (!datos || datos.length === 0) {
            this.mostrarSinDatos();
            return;
        }

        let html = `
            <div class="table-container">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Categoría</th>
                            <th>Cantidad Vendida</th>
                            <th>Ingresos</th>
                            <th>Precio Promedio</th>
                            <th>Pedidos</th>
                            <th>Clientes</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        datos.forEach((producto, index) => {
            html += `
                <tr>
                    <td><span class="badge bg-warning">${index + 1}</span></td>
                    <td><code>${producto.codigo}</code></td>
                    <td>
                        <strong>${producto.descripcion}</strong>
                        ${producto.categoria ? '<br><small class="text-muted">' + producto.categoria + '</small>' : ''}
                    </td>
                    <td><span class="badge bg-info">${producto.categoria || 'Sin categoría'}</span></td>
                    <td><span class="badge bg-primary">${producto.cantidad_vendida}</span></td>
                    <td class="fw-bold text-success">$${this.formatNumber(producto.ingresos_totales)}</td>
                    <td>$${this.formatNumber(producto.precio_promedio)}</td>
                    <td><span class="badge bg-secondary">${producto.pedidos_incluidos || 0}</span></td>
                    <td><span class="badge bg-dark">${producto.clientes_distintos || 0}</span></td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        document.getElementById('contenido_reporte').innerHTML = html;
        this.actualizarInfoReporte(datos.length + ' productos encontrados');
    }

    mostrarReporteClientes(datos) {
        document.getElementById('titulo_reporte').innerHTML = 
            '<i class="fas fa-users"></i> Mejores Clientes';

        if (!datos || datos.length === 0) {
            this.mostrarSinDatos();
            return;
        }

        let html = `
            <div class="table-container">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Total Pedidos</th>
                            <th>Total Gastado</th>
                            <th>Promedio Gasto</th>
                            <th>Última Compra</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        datos.forEach((cliente, index) => {
            html += `
                <tr>
                    <td><span class="badge bg-warning">${index + 1}</span></td>
                    <td><strong>${cliente.nombre}</strong></td>
                    <td>${cliente.email}</td>
                    <td>${cliente.telefono || 'N/A'}</td>
                    <td><span class="badge bg-primary">${cliente.total_pedidos}</span></td>
                    <td class="fw-bold text-success">$${this.formatNumber(cliente.total_gastado)}</td>
                    <td>$${this.formatNumber(cliente.promedio_gasto)}</td>
                    <td><small>${this.formatFecha(cliente.ultima_compra)}</small></td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        document.getElementById('contenido_reporte').innerHTML = html;
        this.actualizarInfoReporte(datos.length + ' clientes encontrados');
    }

    mostrarReporteEstados(datos) {
        document.getElementById('titulo_reporte').innerHTML = 
            '<i class="fas fa-chart-pie"></i> Estadísticas Generales';

        let html = `
            <div class="row">
                <div class="col-md-3">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                            <h4>${datos.total_pedidos || 0}</h4>
                            <p>Total Pedidos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                            <h4>$${this.formatNumber(datos.ventas_totales || 0)}</h4>
                            <p>Ventas Totales</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-2x mb-2"></i>
                            <h4>$${this.formatNumber(datos.promedio_venta || 0)}</h4>
                            <p>Promedio por Venta</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <h4>${datos.clientes_unicos || 0}</h4>
                            <p>Clientes Únicos</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('contenido_reporte').innerHTML = html;
        this.actualizarInfoReporte('Estadísticas del período seleccionado');
    }

    mostrarReporteEmpleados(datos) {
        document.getElementById('titulo_reporte').innerHTML = 
            '<i class="fas fa-user-tie"></i> Reporte de Ventas por Empleado';

        if (!datos || datos.length === 0) {
            document.getElementById('contenido_reporte').innerHTML = 
                '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay datos de empleados para mostrar en el período seleccionado.</div>';
            return;
        }

        let html = '<div class="table-container"><table class="table table-striped table-hover">';
        html += '<thead class="table-dark">';
        html += '<tr>';
        html += '<th><i class="fas fa-hashtag"></i> #</th>';
        html += '<th><i class="fas fa-user"></i> Empleado</th>';
        html += '<th><i class="fas fa-shopping-cart"></i> Total Ventas</th>';
        html += '<th><i class="fas fa-dollar-sign"></i> Ventas Totales</th>';
        html += '<th><i class="fas fa-chart-bar"></i> Promedio Venta</th>';
        html += '<th><i class="fas fa-percentage"></i> Participación</th>';
        html += '</tr>';
        html += '</thead><tbody>';

        const totalVentas = datos.reduce((sum, emp) => sum + (parseFloat(emp.ventas_totales) || 0), 0);

        datos.forEach((empleado, index) => {
            const participacion = totalVentas > 0 ? ((parseFloat(empleado.ventas_totales) || 0) / totalVentas * 100).toFixed(1) : 0;
            html += '<tr>';
            html += `<td><span class="badge bg-primary">${index + 1}</span></td>`;
            html += `<td><strong>${empleado.empleado || 'N/A'}</strong></td>`;
            html += `<td><span class="badge bg-info">${empleado.total_ventas || 0}</span></td>`;
            html += `<td><strong class="text-success">$${this.formatNumber(empleado.ventas_totales || 0)}</strong></td>`;
            html += `<td>$${this.formatNumber(empleado.promedio_venta || 0)}</td>`;
            html += `<td><div class="progress" style="height: 20px;"><div class="progress-bar" style="width: ${participacion}%">${participacion}%</div></div></td>`;
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        
        document.getElementById('contenido_reporte').innerHTML = html;
        this.actualizarInfoReporte(`Total de empleados: ${datos.length}`);
    }

    mostrarReporteVentasDiarias(datos) {
        document.getElementById('titulo_reporte').innerHTML = 
            '<i class="fas fa-calendar-day"></i> Reporte de Ventas Diarias';

        if (!datos || datos.length === 0) {
            document.getElementById('contenido_reporte').innerHTML = 
                '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay datos de ventas diarias para mostrar en el período seleccionado.</div>';
            return;
        }

        let html = '<div class="table-container"><table class="table table-striped table-hover">';
        html += '<thead class="table-dark">';
        html += '<tr>';
        html += '<th><i class="fas fa-calendar"></i> Fecha</th>';
        html += '<th><i class="fas fa-shopping-cart"></i> Pedidos</th>';
        html += '<th><i class="fas fa-dollar-sign"></i> Ventas Totales</th>';
        html += '<th><i class="fas fa-chart-bar"></i> Promedio</th>';
        html += '<th><i class="fas fa-users"></i> Clientes</th>';
        html += '<th><i class="fas fa-arrow-down"></i> Mín</th>';
        html += '<th><i class="fas fa-arrow-up"></i> Máx</th>';
        html += '</tr>';
        html += '</thead><tbody>';

        datos.forEach((dia) => {
            html += '<tr>';
            html += `<td><strong>${this.formatFecha(dia.fecha)}</strong></td>`;
            html += `<td><span class="badge bg-primary">${dia.total_pedidos || 0}</span></td>`;
            html += `<td><strong class="text-success">$${this.formatNumber(dia.ventas_totales)}</strong></td>`;
            html += `<td>$${this.formatNumber(dia.promedio_venta)}</td>`;
            html += `<td><span class="badge bg-info">${dia.clientes_unicos || 0}</span></td>`;
            html += `<td class="text-muted">$${this.formatNumber(dia.venta_minima)}</td>`;
            html += `<td class="text-muted">$${this.formatNumber(dia.venta_maxima)}</td>`;
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        
        document.getElementById('contenido_reporte').innerHTML = html;
        this.actualizarInfoReporte(`Período analizado: ${datos.length} días`);
    }

    mostrarReporteTrimestral(datos) {
        const periodo = datos.periodo;
        document.getElementById('titulo_reporte').innerHTML = 
            `<i class="fas fa-chart-area"></i> Reporte Trimestral Q${periodo.trimestre} ${periodo.año}`;

        if (!datos || !periodo) {
            document.getElementById('contenido_reporte').innerHTML = 
                '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay datos trimestrales para mostrar.</div>';
            return;
        }

        let html = '<div class="row">';
        
        // Resumen Ejecutivo
        html += '<div class="col-12 mb-4">';
        html += '<div class="card border-primary">';
        html += '<div class="card-header bg-primary text-white">';
        html += '<h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Resumen Ejecutivo</h5>';
        html += '</div>';
        html += '<div class="card-body">';
        
        if (datos.resumen) {
            html += '<div class="row text-center">';
            html += '<div class="col-md-3">';
            html += `<h4 class="text-primary">${this.formatNumber(datos.resumen.total_pedidos || 0)}</h4>`;
            html += '<p class="text-muted mb-0">Pedidos Totales</p>';
            html += '</div>';
            html += '<div class="col-md-3">';
            html += `<h4 class="text-success">$${this.formatNumber(datos.resumen.ventas_totales)}</h4>`;
            html += '<p class="text-muted mb-0">Ventas Totales</p>';
            html += '</div>';
            html += '<div class="col-md-3">';
            html += `<h4 class="text-info">${this.formatNumber(datos.resumen.clientes_unicos || 0)}</h4>`;
            html += '<p class="text-muted mb-0">Clientes Únicos</p>';
            html += '</div>';
            html += '<div class="col-md-3">';
            html += `<h4 class="text-warning">$${this.formatNumber(datos.resumen.promedio_venta)}</h4>`;
            html += '<p class="text-muted mb-0">Venta Promedio</p>';
            html += '</div>';
            html += '</div>';
        }
        
        html += '</div></div></div>';

        // Ventas Mensuales del Trimestre
        if (datos.ventas_mensuales && datos.ventas_mensuales.length > 0) {
            html += '<div class="col-md-6 mb-4">';
            html += '<div class="card border-info">';
            html += '<div class="card-header bg-info text-white">';
            html += '<h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Ventas Mensuales</h6>';
            html += '</div>';
            html += '<div class="card-body">';
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm">';
            html += '<thead><tr>';
            html += '<th>Mes</th><th>Pedidos</th><th>Ventas</th>';
            html += '</tr></thead><tbody>';
            
            datos.ventas_mensuales.forEach(mes => {
                html += '<tr>';
                html += `<td><strong>${mes.nombre_mes}</strong></td>`;
                html += `<td><span class="badge bg-primary">${mes.total_pedidos}</span></td>`;
                html += `<td class="text-success">$${this.formatNumber(mes.ventas_totales)}</td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table></div></div></div></div>';
        }

        // Crecimiento Comparativo
        if (datos.crecimiento) {
            html += '<div class="col-md-6 mb-4">';
            html += '<div class="card border-success">';
            html += '<div class="card-header bg-success text-white">';
            html += '<h6 class="mb-0"><i class="fas fa-trending-up me-2"></i>Crecimiento vs Trimestre Anterior</h6>';
            html += '</div>';
            html += '<div class="card-body">';
            html += '<div class="row text-center">';
            
            const crecimientos = [
                { label: 'Pedidos', valor: datos.crecimiento.pedidos, icon: 'shopping-cart' },
                { label: 'Ventas', valor: datos.crecimiento.ventas, icon: 'dollar-sign' },
                { label: 'Promedio', valor: datos.crecimiento.promedio, icon: 'chart-bar' },
                { label: 'Clientes', valor: datos.crecimiento.clientes, icon: 'users' }
            ];
            
            crecimientos.forEach(item => {
                const color = item.valor >= 0 ? 'success' : 'danger';
                const icon = item.valor >= 0 ? 'arrow-up' : 'arrow-down';
                html += '<div class="col-6 mb-2">';
                html += `<i class="fas fa-${item.icon} text-${color}"></i>`;
                html += `<h6 class="text-${color} mb-0">${item.valor}%</h6>`;
                html += `<small class="text-muted">${item.label}</small>`;
                html += '</div>';
            });
            
            html += '</div></div></div></div>';
        }

        // Top 5 Productos del Trimestre
        if (datos.top_productos && datos.top_productos.length > 0) {
            html += '<div class="col-12 mb-4">';
            html += '<div class="card border-warning">';
            html += '<div class="card-header bg-warning text-dark">';
            html += '<h6 class="mb-0"><i class="fas fa-trophy me-2"></i>Top 5 Productos del Trimestre</h6>';
            html += '</div>';
            html += '<div class="card-body">';
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm">';
            html += '<thead><tr>';
            html += '<th>#</th><th>Producto</th><th>Cantidad</th><th>Ingresos</th>';
            html += '</tr></thead><tbody>';
            
            datos.top_productos.slice(0, 5).forEach((producto, index) => {
                html += '<tr>';
                html += `<td><span class="badge bg-warning text-dark">${index + 1}</span></td>`;
                html += `<td><strong>${producto.descripcion}</strong></td>`;
                html += `<td><span class="badge bg-info">${producto.cantidad_vendida}</span></td>`;
                html += `<td class="text-success">$${this.formatNumber(producto.ingresos_totales)}</td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table></div></div></div></div>';
        }

        html += '</div>'; // Cierre del row principal
        
        document.getElementById('contenido_reporte').innerHTML = html;
        this.actualizarInfoReporte(`Análisis de ${periodo.dias_transcurridos} días de operación`);
    }

    async exportar(formato) {
        if (!this.datosActuales) {
            this.mostrarError('No hay datos para exportar. Genere un reporte primero.');
            return;
        }

        this.mostrarLoading(true);

        try {
            const formData = new FormData();
            formData.append('action', formato === 'csv' ? 'exportar_csv' : 'exportar_pdf');
            formData.append('tipo_reporte', this.tipoActual);
            formData.append('fecha_inicio', document.getElementById('fecha_inicio').value);
            formData.append('fecha_fin', document.getElementById('fecha_fin').value);
            formData.append('limite', document.getElementById('limite').value);

            if (this.tipoActual === 'trimestral') {
                formData.append('año', document.getElementById('año_trimestre').value);
                formData.append('trimestre', document.getElementById('numero_trimestre').value);
            }

            const response = await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                // Verificar si la respuesta es realmente un archivo
                const contentType = response.headers.get('content-type');
                
                if (contentType && (contentType.includes('application/pdf') || contentType.includes('text/csv') || contentType.includes('application/octet-stream'))) {
                    // Es un archivo, proceder con la descarga
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `reporte_${this.tipoActual}_${new Date().toISOString().split('T')[0]}.${formato}`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    console.log(`✅ Archivo ${formato.toUpperCase()} descargado exitosamente`);
                } else {
                    // Puede ser una respuesta JSON con error
                    const text = await response.text();
                    try {
                        const jsonResponse = JSON.parse(text);
                        this.mostrarError(jsonResponse.error || 'Error al exportar el archivo');
                    } catch {
                        this.mostrarError('Error en el formato de respuesta del servidor');
                    }
                }
            } else {
                this.mostrarError(`Error del servidor: ${response.status} ${response.statusText}`);
            }
        } catch (error) {
            console.error('Error en exportación:', error);
            this.mostrarError('Error al exportar: ' + error.message);
        } finally {
            this.mostrarLoading(false);
        }
    }

    refrescarDatos() {
        if (this.tipoActual) {
            this.generarReporte();
        }
    }

    mostrarSinDatos() {
        document.getElementById('contenido_reporte').innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No se encontraron datos</h5>
                <p class="text-muted">Intente cambiar los filtros de fecha</p>
            </div>
        `;
    }

    /**
     * Actualiza los datos del dashboard
     */
    async refrescarDatos() {
        this.mostrarLoading(true);
        try {
            await this.cargarEstadisticasGenerales();
            // También regenerar el reporte actual si existe
            if (this.tipoActual) {
                await this.generarReporte();
            }
        } catch (error) {
            this.mostrarError('Error al actualizar los datos');
        } finally {
            this.mostrarLoading(false);
        }
    }

    mostrarError(mensaje) {
        document.getElementById('contenido_reporte').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> ${mensaje}
            </div>
        `;
    }

    mostrarLoading(mostrar) {
        document.getElementById('loading').style.display = mostrar ? 'flex' : 'none';
    }

    actualizarInfoReporte(info) {
        document.getElementById('info_reporte').textContent = info;
    }

    formatNumber(num) {
        return parseFloat(num || 0).toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    formatFecha(fecha) {
        if (!fecha) return 'N/A';
        return new Date(fecha).toLocaleDateString('es-CO');
    }
}

// Inicializar el sistema
const reportes = new SistemaReportes();
