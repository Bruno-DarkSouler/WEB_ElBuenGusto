// Configuración de Tailwind
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: '#C81E2D',
                secondary: '#F5EBD2',
                tertiary: '#503214'
            }
        }
    }
}

// Variables globales
const API_BASE = '../php/';
let currentSection = 'metricas';
let usuarios = [];
let productos = [];
let categorias = [];
let promociones = [];
let zonas = [];

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    verificarSesion();
    cargarCategorias();
    actualizarReloj();
    setInterval(actualizarReloj, 60000);
    
    // Inicializar gráficos después de cargar la página
    setTimeout(initializeCharts, 500);
});

// Verificar sesión
async function verificarSesion() {
    try {
        const response = await fetch(API_BASE + 'check_session.php');
        const data = await response.json();
        
        if (!data.success) {
            window.location.href = '../html/login.html';
            return;
        }
        
        if (!data.user.is_admin) {
            window.location.href = '../index.html';
            return;
        }
        
        // AGREGAR ESTA LÍNEA:
        cargarMetricas();
        
    } catch (error) {
        console.error('Error al verificar sesión:', error);
        window.location.href = '../html/login.html';
    }
}

// Actualizar reloj
function actualizarReloj() {
    const ahora = new Date();
    document.getElementById('currentTime').textContent = 
        ahora.toLocaleDateString('es-AR') + ' ' + ahora.toLocaleTimeString('es-AR', {hour: '2-digit', minute:'2-digit'});
}

// Navegación entre secciones
function showSection(sectionId, event) {
    // Ocultar todas las secciones
    document.querySelectorAll('.section').forEach(section => {
        section.classList.add('hidden');
    });
    
    // Mostrar la sección seleccionada
    document.getElementById(sectionId).classList.remove('hidden');
    
    // Actualizar navegación activa
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active', 'bg-[#AD1926]', 'text-white');
        link.classList.add('text-indigo-100');
    });
    
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active', 'bg-[#AD1926]', 'text-white');
        event.currentTarget.classList.remove('text-indigo-100');
    }
    
    // Actualizar título
    const titulos = {
        'metricas': 'Métricas & Dashboard',
        'usuarios': 'Gestión de Usuarios',
        'productos': 'Productos & Precios',
        'reportes': 'Reportes Financieros',
        'configuracion': 'Configuración del Local',
        'pagos': 'Métodos de Pago',
    };
    
    document.getElementById('pageTitle').textContent = titulos[sectionId];
    currentSection = sectionId;
    
    // Cargar datos según la sección
    switch(sectionId) {
        case 'usuarios':
            cargarUsuarios();
            break;
        case 'productos':
            cargarProductos();
            break;
        case 'reportes':
            cargarReportes();
            break;
        case 'configuracion':
            cargarConfiguracion();
            break;
        case 'pagos':
            cargarMetodosPago();
            break;
    }
}

// Toast notifications
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');
    
    toastMessage.textContent = message;
    
    if (type === 'success') {
        toastIcon.innerHTML = '<div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center"><span class="text-white text-sm">✓</span></div>';
    } else {
        toastIcon.innerHTML = '<div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center"><span class="text-white text-sm">✕</span></div>';
    }
    
    toast.classList.remove('hidden');
    
    setTimeout(() => {
        hideToast();
    }, 4000);
}

function hideToast() {
    document.getElementById('toast').classList.add('hidden');
}

// ============ USUARIOS ============
async function cargarUsuarios() {
    try {
        const response = await fetch(API_BASE + 'usuarios.php');
        const data = await response.json();
        
        if (data.success) {
            usuarios = data.usuarios;
            renderizarUsuarios();
        }
    } catch (error) {
        console.error('Error al cargar usuarios:', error);
        showToast('Error al cargar usuarios', 'error');
    }
}

function renderizarUsuarios() {
    const tbody = document.querySelector('#usuarios table tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    usuarios.forEach(usuario => {
        const tr = document.createElement('tr');
        tr.classList.add('hover:bg-gray-50');
        tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="h-10 w-10 flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-primary flex items-center justify-center">
                            <span class="text-white font-medium text-sm">${usuario.nombre.charAt(0)}${usuario.apellido.charAt(0)}</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${usuario.nombre} ${usuario.apellido}</div>
                        <div class="text-sm text-gray-500">${usuario.email}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getRolColor(usuario.rol)}">${getRolNombre(usuario.rol)}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${getPermisosNombre(usuario.rol)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatearFecha(usuario.fecha_registro)}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${usuario.activo == 1 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                    ${usuario.activo == 1 ? 'Activo' : 'Suspendido'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button onclick="editUser(${usuario.id})" class="text-indigo-600 hover:text-indigo-900">Editar</button>
                <button onclick="toggleUser(${usuario.id}, ${usuario.activo})" class="${usuario.activo == 1 ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'}">
                    ${usuario.activo == 1 ? 'Suspender' : 'Activar'}
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function getRolColor(rol) {
    const colores = {
        'administrador': 'bg-purple-100 text-purple-800',
        'cajero': 'bg-blue-100 text-blue-800',
        'cocinero': 'bg-green-100 text-green-800',
        'repartidor': 'bg-orange-100 text-orange-800',
        'cliente': 'bg-gray-100 text-gray-800'
    };
    return colores[rol] || 'bg-gray-100 text-gray-800';
}

function getRolNombre(rol) {
    const nombres = {
        'administrador': 'Administrador',
        'cajero': 'Cajero',
        'cocinero': 'Cocinero',
        'repartidor': 'Repartidor',
        'cliente': 'Cliente'
    };
    return nombres[rol] || rol;
}

function getPermisosNombre(rol) {
    const permisos = {
        'administrador': 'Total',
        'cajero': 'Pedidos + Clientes',
        'cocinero': 'Pedidos + Cocina',
        'repartidor': 'Solo Entregas',
        'cliente': 'Pedidos Online'
    };
    return permisos[rol] || 'Sin permisos';
}

function formatearFecha(fecha) {
    const date = new Date(fecha);
    const hoy = new Date();
    const ayer = new Date(hoy);
    ayer.setDate(ayer.getDate() - 1);
    
    if (date.toDateString() === hoy.toDateString()) {
        return 'Hoy ' + date.toLocaleTimeString('es-AR', {hour: '2-digit', minute: '2-digit'});
    } else if (date.toDateString() === ayer.toDateString()) {
        return 'Ayer ' + date.toLocaleTimeString('es-AR', {hour: '2-digit', minute: '2-digit'});
    } else {
        return date.toLocaleDateString('es-AR');
    }
}

async function toggleUser(id, estado) {
    const accion = estado == 1 ? 'desactivar' : 'activar';
    
    if (!confirm(`¿Está seguro de ${accion} este usuario?`)) return;
    
    try {
        const params = new URLSearchParams({id, accion});
        
        const response = await fetch(API_BASE + 'usuarios.php', {
            method: 'DELETE',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: params.toString()
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message);
            cargarUsuarios();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error al cambiar estado del usuario:', error);
        showToast('Error al cambiar estado del usuario', 'error');
    }
}

// ============ PRODUCTOS ============
async function cargarCategorias() {
    try {
        const response = await fetch(API_BASE + 'categorias.php');
        const data = await response.json();
        
        if (data.success) {
            categorias = data.categorias;
        }
    } catch (error) {
        console.error('Error al cargar categorías:', error);
    }
}


async function cargarProductos() {
    try {
        const response = await fetch(API_BASE + 'productos.php');
        const data = await response.json();
        
        if (data.success) {
            productos = data.productos;
            renderizarProductos();
        }
    } catch (error) {
        console.error('Error al cargar productos:', error);
        showToast('Error al cargar productos', 'error');
    }
}

function renderizarProductos() {
    const tbody = document.querySelector('#productos table tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    productos.forEach(producto => {
        const costo = parseFloat(producto.precio) * 0.4;
        const margen = calcularMargen(producto.precio, costo);
        
        const tr = document.createElement('tr');
        tr.classList.add('hover:bg-gray-50');
        tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="h-10 w-10 flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                            ${producto.imagen ? `<img src="${producto.imagen}" class="h-10 w-10 rounded-full object-cover">` : '🍽️'}
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${producto.nombre}</div>
                        <div class="text-sm text-gray-500">${producto.descripcion || ''}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${producto.categoria_nombre}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-gray-900">$${parseFloat(producto.precio).toFixed(2)}</span>
                    <button onclick="editPrice(${producto.id}, ${producto.precio})" class="text-xs text-indigo-600 hover:text-indigo-800">✏️</button>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$${costo.toFixed(2)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">${margen}%</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${producto.disponible == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${producto.disponible == 1 ? 'Activo' : 'Sin Stock'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button onclick="editProduct(${producto.id})" class="text-indigo-600 hover:text-indigo-900">Editar</button>
                <button onclick="toggleProduct(${producto.id}, ${producto.disponible})" class="${producto.disponible == 1 ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'}">
                    ${producto.disponible == 1 ? 'Desactivar' : 'Activar'}
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}


function calcularMargen(precio, costo) {
    const ganancia = precio - costo;
    return ((ganancia / costo) * 100).toFixed(0);
}

async function toggleProduct(id, estado) {
    try {
        const producto = productos.find(p => p.id === id);
        const nuevaDisponibilidad = estado == 1 ? 0 : 1;
        
        const params = new URLSearchParams({
            id, 
            nombre: producto.nombre,
            descripcion: producto.descripcion,
            precio: producto.precio,
            categoria_id: producto.categoria_id,
            ingredientes: producto.ingredientes,
            tiempo_preparacion: producto.tiempo_preparacion,
            disponible: nuevaDisponibilidad
        });
        
        const response = await fetch(API_BASE + 'productos.php', {
            method: 'PUT',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: params.toString()
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message);
            cargarProductos();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error al cambiar estado del producto:', error);
        showToast('Error al cambiar estado del producto', 'error');
    }
}

function editPrice(id, precioActual) {
    const nuevoPrecio = prompt('Nuevo precio:', precioActual);
    if (nuevoPrecio && !isNaN(nuevoPrecio)) {
        const producto = productos.find(p => p.id === id);
        actualizarProducto(id, {
            nombre: producto.nombre,
            descripcion: producto.descripcion,
            precio: nuevoPrecio,
            categoria_id: producto.categoria_id,
            ingredientes: producto.ingredientes,
            tiempo_preparacion: producto.tiempo_preparacion,
            disponible: producto.disponible
        });
    }
}

async function actualizarProducto(id, datos) {
    try {
        const params = new URLSearchParams({...datos, id});
        
        const response = await fetch(API_BASE + 'productos.php', {
            method: 'PUT',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: params.toString()
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Producto actualizado exitosamente');
            cargarProductos();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error al actualizar producto:', error);
        showToast('Error al actualizar producto', 'error');
    }
}

function applyBulkPriceUpdate() {
    const porcentaje = prompt('Ingrese el porcentaje de aumento (ej: 10 para 10%):');
    if (porcentaje && !isNaN(porcentaje)) {
        if (confirm(`¿Está seguro de aplicar un ${porcentaje}% de aumento a todos los productos?`)) {
            showToast('Función de actualización masiva en desarrollo');
        }
    }
}


// ============ REPORTES ============
async function cargarReportes() {
    try {
        const fechaDesde = document.getElementById('dateFrom')?.value || '2025-09-01';
        const fechaHasta = document.getElementById('dateTo')?.value || '2025-09-10';
        
        const response = await fetch(`${API_BASE}reportes.php?accion=resumen&fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}`);
        const data = await response.json();
        
        if (data.success) {
            actualizarResumenFinanciero(data.resumen);
        }
        
        cargarReporteDetallado();
    } catch (error) {
        console.error('Error al cargar reportes:', error);
        showToast('Error al cargar reportes', 'error');
    }
}

function actualizarResumenFinanciero(resumen) {
    const cards = document.querySelectorAll('#reportes .grid .bg-white');
    if (cards.length >= 4) {
        // Ingresos Brutos
        cards[0].querySelector('dd').textContent = `$${Number(resumen.ingresos_brutos || 0).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        
        // Total Delivery
        cards[1].querySelector('dd').textContent = `$${Number(resumen.total_delivery || 0).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        
        // Ganancia por Pedidos
        cards[2].querySelector('dd').textContent = `$${Number(resumen.ganancia_pedidos || 0).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        
        // Precio Promedio
        cards[3].querySelector('dd').textContent = `$${Number(resumen.precio_promedio || 0).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    }
}

async function cargarReporteDetallado() {
    try {
        const fechaDesde = document.getElementById('dateFrom')?.value || '2025-09-01';
        const fechaHasta = document.getElementById('dateTo')?.value || '2025-09-10';
        
        const response = await fetch(`${API_BASE}reportes.php?accion=detallado&fecha_desde=${fechaDesde}&fecha_hasta=${fechaHasta}`);
        const data = await response.json();
        
        if (data.success) {
            renderizarReporteDetallado(data.detalle);
        }
    } catch (error) {
        console.error('Error al cargar reporte detallado:', error);
    }
}

function renderizarReporteDetallado(detalle) {
    const tbody = document.querySelector('#reportes table tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    detalle.forEach(dia => {
        const tr = document.createElement('tr');
        tr.classList.add('hover:bg-gray-50');

        // Formatear fecha
        const fechaFormateada = new Date(dia.fecha + 'T00:00:00').toLocaleDateString('es-AR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });

        tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${fechaFormateada}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${dia.pedidos}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">$${Number(dia.ingresos).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${Number(dia.delivery).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">$${Number(dia.ganancia_pedidos).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">$${Number(dia.precio_promedio).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${dia.margen}%</td>
        `;
        tbody.appendChild(tr);
    });
}

function updateFinancialReport() {
    cargarReportes();
}


function exportReport(tipo) {
    if (tipo === 'pdf') {
        window.print(); // Abre el diálogo de impresión
    } else if (tipo === 'excel') {
        showToast('Función de exportación a Excel en desarrollo');
    }
}
// ============ CONFIGURACIÓN ============
async function cargarConfiguracion() {
    try {
        const response = await fetch(API_BASE + 'configuracion.php');
        const data = await response.json();
        
        if (data.success && data.configuracion) {
            const c = data.configuracion;

            const nombreLocal = document.querySelector('input[name="nombre_local"]');
            const telefono = document.querySelector('input[name="telefono"]');
            const emailContacto = document.querySelector('input[name="email_contacto"]');
            const direccion = document.querySelector('input[name="direccion"]');
            const descripcion = document.querySelector('textarea[name="descripcion"]');

            if (nombreLocal) nombreLocal.value = c.nombre_local || "";
            if (telefono) telefono.value = c.telefono || "";
            if (emailContacto) emailContacto.value = c.email_contacto || "";
            if (direccion) direccion.value = c.direccion || "";
            if (descripcion) descripcion.value = c.descripcion || "";
        } else {
            showToast('No se pudo cargar la configuración', 'error');
        }

    } catch (error) {
        console.error('Error al cargar configuración:', error);
        showToast('Error al cargar configuración', 'error');
    }
}


function abrir_opciones() {
    var opciones = document.getElementById("opciones");
    opciones.classList.remove('hidden');
    opciones.classList.add('flex', 'fixed', 'top-0', 'z-60', 'absolute');
    f24d15e7758cf2f2fa4fc4878adebb07c467c09d
}

async function saveConfiguration() {
    try {

        const config = {
            nombre_local: document.querySelector('input[name="nombre_local"]').value,
            telefono: document.querySelector('input[name="telefono"]').value,
            email_contacto: document.querySelector('input[name="email_contacto"]').value,
            direccion: document.querySelector('input[name="direccion"]').value,
            descripcion: document.querySelector('textarea[name="descripcion"]').value
        };
        
        const response = await fetch(API_BASE + 'configuracion.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(config)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Configuración guardada exitosamente');
        } else {
            showToast(data.message, 'error');
        }

    } catch (error) {
        console.error('Error al guardar configuración:', error);
        showToast('Error al guardar configuración', 'error');
    }
}


// ============ MÉTODOS DE PAGO ============
async function cargarMetodosPago() {
    try {
        const response = await fetch(API_BASE + 'metodos_pago.php');
        const data = await response.json();
        
        if (data.success) {
            showToast('Métodos de pago cargados');
        }
    } catch (error) {
        console.error('Error al cargar métodos de pago:', error);
        showToast('Error al cargar métodos de pago', 'error');
    }
}

function configurePayment(metodo) {
    showToast(`Configurando método de pago: ${metodo}`);
}

function togglePayment(metodo) {
    showToast(`Cambiando estado del método de pago: ${metodo}`);
}


// ============ MÉTRICAS ============
async function cargarMetricas() {
    try {
        const response = await fetch(API_BASE + 'reportes.php?accion=metricas');
        const data = await response.json();
        
        if (data.success) {
            const metricas = data.metricas;
            
            const cards = document.querySelectorAll('#metricas .grid .bg-white');
            if (cards.length >= 4) {
                const ventasElement = cards[0].querySelector('.text-2xl');
                ventasElement.textContent = `$${Number(metricas.ventas_hoy || 0).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                
                const pedidosElement = cards[1].querySelector('.text-2xl');
                pedidosElement.textContent = metricas.pedidos_hoy || 0;
                
                const empleadosElement = cards[2].querySelector('.text-2xl');
                empleadosElement.textContent = metricas.empleados_activos || 0;
                
                const gastoPromedioElement = cards[3].querySelector('.text-2xl');
                gastoPromedioElement.textContent = `$${Number(metricas.gasto_promedio || 0).toLocaleString('es-AR', {minimumFractionDigits: 0, maximumFractionDigits: 0})}`;
            }
        }
        
        // Cargar pedidos activos
        await cargarPedidosActivos();
    } catch (error) {
        console.error('Error al cargar métricas:', error);
        showToast('Error al cargar métricas', 'error');
    }
}

// Inicializar gráficos
function initializeCharts() {
    if (typeof Chart === 'undefined') return;
    
    // Gráfico de ventas de la semana
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Ventas',
                    data: [11400, 13050, 12800, 14200, 15600, 18900, 14550],
                    borderColor: '#C81E2D',
                    backgroundColor: 'rgba(200, 30, 45, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Gráfico de ingresos mensuales (Reportes)
    const monthlyCtx = document.getElementById('monthlyRevenueChart');
    if (monthlyCtx) {
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep'],
                datasets: [{
                    label: 'Ingresos',
                    data: [125000, 135000, 142000, 138000, 155000, 148000, 162000, 158000, 145600],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Costos',
                    data: [52000, 56000, 59000, 57000, 64000, 61000, 67000, 65000, 58400],
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Gráfico de distribución de ingresos
    const breakdownCtx = document.getElementById('revenueBreakdownChart');
    if (breakdownCtx) {
        new Chart(breakdownCtx, {
            type: 'doughnut',
            data: {
                labels: ['Local', 'Delivery', 'Takeaway'],
                datasets: [{
                    data: [127100, 18500, 0],
                    backgroundColor: ['#6366f1', '#C81E2D', '#10b981']
                }]
            },
            options: {
                responsive: true,
            }
        });
    }
}

// Menu móvil
function abrir_opciones() {
    const menu = document.getElementById('opciones');
    menu.classList.toggle('hidden');
    menu.classList.toggle('flex');
    menu.classList.add('fixed');
    menu.classList.add('top-0');
    menu.classList.add('z-50');
}

// ============ FUNCIONES MODALES ============

// Modal Usuario
function showUserModal(id = null) {
    const modal = document.getElementById('modalUsuario');
    const titulo = document.getElementById('tituloModalUsuario');
    const form = document.getElementById('formUsuario');
    
    if (!modal || !titulo || !form) {
        showToast('Error: Modal no encontrado', 'error');
        return;
    }
    
    form.reset();
    
    if (id) {
        titulo.textContent = 'Editar Usuario';
        const usuario = usuarios.find(u => u.id === id);
        if (usuario) {
            document.getElementById('usuario_id').value = usuario.id;
            document.getElementById('usuario_nombre').value = usuario.nombre;
            document.getElementById('usuario_apellido').value = usuario.apellido;
            document.getElementById('usuario_email').value = usuario.email;
            document.getElementById('usuario_telefono').value = usuario.telefono;
            document.getElementById('usuario_rol').value = usuario.rol;
            document.getElementById('usuario_password').required = false;
        }
    } else {
        titulo.textContent = 'Nuevo Usuario';
        document.getElementById('usuario_id').value = '';
        document.getElementById('usuario_password').required = true;
    }
    
    modal.classList.remove('hidden');
}
function cerrarModalUsuario() {
    const modal = document.getElementById('modalUsuario');
    if (modal) {
        modal.classList.add('hidden');
    }
}

async function guardarUsuario(event) {
    event.preventDefault();
    
    const form = document.getElementById('formUsuario');
    const id = document.getElementById('usuario_id').value;
    
    try {
        let response;
        
        if (id) {
            // Para edición
            const data = new URLSearchParams();
            data.append('id', id);
            data.append('nombre', form.nombre.value);
            data.append('apellido', form.apellido.value);
            data.append('email', form.email.value);
            data.append('telefono', form.telefono.value);
            data.append('rol', form.rol.value);
            
            // Solo agregar password si tiene valor
            if (form.password.value.trim() !== '') {
                data.append('password', form.password.value);
            }
            
            response = await fetch(API_BASE + 'usuarios.php', {
                method: 'PUT',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: data.toString()
            });
        } else {
            // Para creación
            const formData = new FormData(form);
            response = await fetch(API_BASE + 'usuarios.php', {
                method: 'POST',
                body: formData
            });
        }
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message);
            cerrarModalUsuario();
            cargarUsuarios();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error al guardar usuario:', error);
        showToast('Error al guardar usuario', 'error');
    }
}

// Modal Producto
function showProductModal(id = null) {
    const modal = document.getElementById('modalProducto');
    const titulo = document.getElementById('tituloModalProducto');
    const form = document.getElementById('formProducto');
    const selectCategoria = document.getElementById('producto_categoria');
    
    if (!modal || !titulo || !form || !selectCategoria) {
        showToast('Error: Modal no encontrado', 'error');
        return;
    }
    
    form.reset();
    
    // Cargar categorías en el select
    if (categorias.length > 0) {
        selectCategoria.innerHTML = '<option value="">Seleccione una categoría</option>';
        categorias.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.nombre;
            selectCategoria.appendChild(option);
        });
    }
    
    if (id) {
        titulo.textContent = 'Editar Producto';
        const producto = productos.find(p => p.id === id);
        if (producto) {
            document.getElementById('producto_id').value = producto.id;
            document.getElementById('producto_nombre').value = producto.nombre;
            document.getElementById('producto_descripcion').value = producto.descripcion || '';
            document.getElementById('producto_precio').value = producto.precio;
            document.getElementById('producto_categoria').value = producto.categoria_id;
            document.getElementById('producto_ingredientes').value = producto.ingredientes || '';
            document.getElementById('producto_tiempo').value = producto.tiempo_preparacion || 20;
            document.getElementById('producto_disponible').checked = producto.disponible == 1;
        }
    } else {
        titulo.textContent = 'Nuevo Producto';
        document.getElementById('producto_id').value = '';
        document.getElementById('producto_disponible').checked = true;
    }
    
    modal.classList.remove('hidden');
}

function cerrarModalProducto() {
    const modal = document.getElementById('modalProducto');
    if (modal) {
        modal.classList.add('hidden');
    }
}

async function guardarProducto(event) {
    event.preventDefault();
    
    const form = document.getElementById('formProducto');
    const formData = new FormData(form);
    const id = document.getElementById('producto_id').value;
    
    try {
        let response;
        
        if (id) {
            formData.append('_method', 'PUT');
            response = await fetch(API_BASE + 'productos.php', {
                method: 'POST',
                body: formData
            });
        } else {
            response = await fetch(API_BASE + 'productos.php', {
                method: 'POST',
                body: formData
            });
        }
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message);
            cerrarModalProducto();
            cargarProductos();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error al guardar producto:', error);
        showToast('Error al guardar producto', 'error');
    }
}
// Función para eliminar una promoción
async function deletePromotion(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta promoción?')) {
        return;
    }

    try {
        // Enviar petición DELETE a promociones.php
        const response = await fetch(API_BASE + 'promociones.php', {
            method: 'DELETE',
            // Para DELETE, enviamos el ID en el cuerpo como JSON
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message);
            // Recargar la tabla de promociones después de eliminar
            cargarPromociones();
        } else {
            showToast(data.message || 'Error al eliminar promoción', 'error');
        }
    } catch (error) {
        console.error('Error al eliminar promoción:', error);
        showToast('Error de conexión al eliminar promoción', 'error');
    }
}

// Actualizar funciones existentes para usar modales
function editUser(id) {
    showUserModal(id);
}

function editProduct(id) {
    showProductModal(id);
}

// ============ PEDIDOS ACTIVOS ============
let pedidosActivosGlobal = [];

async function cargarPedidosActivos() {
    try {
        const response = await fetch(API_BASE + 'pedidos_activos.php');
        const data = await response.json();
        
        if (data.success) {
            pedidosActivosGlobal = data.pedidos;
            renderizarPedidosActivos(data.pedidos.slice(0, 5)); // Solo 5 para el dashboard
        }
    } catch (error) {
        console.error('Error al cargar pedidos activos:', error);
        showToast('Error al cargar pedidos activos', 'error');
    }
}

function renderizarPedidosActivos(pedidos) {
    const tbody = document.getElementById('pedidosActivosTabla');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (pedidos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-4 text-center text-gray-500">No hay pedidos activos</td></tr>';
        return;
    }
    
    pedidos.forEach(pedido => {
        const tr = document.createElement('tr');
        tr.classList.add('hover:bg-gray-50', 'cursor-pointer');
        tr.onclick = () => verDetallePedido(pedido.id);
        
        tr.innerHTML = `
            <td class="px-4 py-3 text-sm font-medium text-gray-900">${pedido.numero_pedido}</td>
            <td class="px-4 py-3 text-sm text-gray-600">${pedido.nombre_cliente || 'Cliente'}</td>
            <td class="px-4 py-3">
                <span class="px-2 py-1 text-xs font-semibold rounded-full ${getEstadoColor(pedido.estado)}">
                    ${getEstadoNombre(pedido.estado)}
                </span>
            </td>
            <td class="px-4 py-3 text-sm font-medium text-gray-900">$${formatearPrecio(pedido.total)}</td>
        `;
        tbody.appendChild(tr);
    });
}

function verTodosPedidos() {
    const modal = document.getElementById('modalTodosPedidos');
    modal.classList.remove('hidden');
    renderizarTodosPedidos(pedidosActivosGlobal);
    
    // Agregar event listeners después de abrir el modal
    const buscarInput = document.getElementById('buscarPedido');
    const filtroEstado = document.getElementById('filtroEstado');
    
    if (buscarInput) {
        buscarInput.removeEventListener('input', filtrarPedidos); // Evitar duplicados
        buscarInput.addEventListener('input', filtrarPedidos);
    }
    
    if (filtroEstado) {
        filtroEstado.removeEventListener('change', filtrarPedidos); // Evitar duplicados
        filtroEstado.addEventListener('change', filtrarPedidos);
    }
}

function cerrarModalTodosPedidos() {
    const modal = document.getElementById('modalTodosPedidos');
    modal.classList.add('hidden');
}

function renderizarTodosPedidos(pedidos) {
    const tbody = document.getElementById('todosPedidosTabla');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (pedidos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No hay pedidos activos</td></tr>';
        return;
    }
    
    pedidos.forEach(pedido => {
        const tr = document.createElement('tr');
        tr.classList.add('hover:bg-gray-50');
        
        const fecha = new Date(pedido.fecha_pedido);
        const fechaFormateada = fecha.toLocaleDateString('es-AR', { 
            day: '2-digit', 
            month: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        tr.innerHTML = `
            <td class="px-6 py-4 text-sm font-medium text-gray-900">${pedido.numero_pedido}</td>
            <td class="px-6 py-4 text-sm text-gray-600">${pedido.nombre_cliente || 'Cliente'}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${fechaFormateada}</td>
            <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs font-semibold rounded-full ${getEstadoColor(pedido.estado)}">
                    ${getEstadoNombre(pedido.estado)}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">${pedido.tipo_pedido === 'inmediato' ? 'Inmediato' : 'Programado'}</td>
            <td class="px-6 py-4 text-sm font-medium text-gray-900">$${formatearPrecio(pedido.total)}</td>
            <td class="px-6 py-4 text-sm text-gray-600">${pedido.nombre_repartidor || 'Sin asignar'}</td>
        `;
        tbody.appendChild(tr);
    });
}

function getEstadoColor(estado) {
    const colores = {
        'en_preparacion': 'bg-yellow-100 text-yellow-800',
        'listo': 'bg-blue-100 text-blue-800',
        'en_camino': 'bg-purple-100 text-purple-800',
        'entregado': 'bg-green-100 text-green-800'
    };
    return colores[estado] || 'bg-gray-100 text-gray-800';
}

function getEstadoNombre(estado) {
    const nombres = {
        'en_preparacion': 'En Preparación',
        'listo': 'Listo',
        'en_camino': 'En Camino',
        'entregado': 'Entregado'
    };
    return nombres[estado] || estado;
}

function formatearPrecio(precio) {
    return Number(precio).toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Filtros del modal

function filtrarPedidos() {
    const busqueda = document.getElementById('buscarPedido')?.value.toLowerCase() || '';
    const estadoFiltro = document.getElementById('filtroEstado')?.value || '';
    
    const pedidosFiltrados = pedidosActivosGlobal.filter(pedido => {
        const coincideBusqueda = pedido.numero_pedido.toLowerCase().includes(busqueda) ||
                                (pedido.nombre_cliente && pedido.nombre_cliente.toLowerCase().includes(busqueda));
        
        const coincideEstado = !estadoFiltro || pedido.estado === estadoFiltro;
        
        return coincideBusqueda && coincideEstado;
    });
    
    renderizarTodosPedidos(pedidosFiltrados);
}

// Llamar al cargar métricas
async function cargarMetricas() {
    try {
        const response = await fetch(API_BASE + 'reportes.php?accion=metricas');
        const data = await response.json();
        
        if (data.success) {
            const metricas = data.metricas;
            
            const cards = document.querySelectorAll('#metricas .grid .bg-white');
            if (cards.length >= 4) {
                const ventasElement = cards[0].querySelector('.text-2xl');
                ventasElement.textContent = `$${Number(metricas.ventas_hoy || 0).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                
                const pedidosElement = cards[1].querySelector('.text-2xl');
                pedidosElement.textContent = metricas.pedidos_hoy || 0;
                
                const empleadosElement = cards[2].querySelector('.text-2xl');
                empleadosElement.textContent = metricas.empleados_activos || 0;
                
                const gastoPromedioElement = cards[3].querySelector('.text-2xl');
                gastoPromedioElement.textContent = `$${Number(metricas.gasto_promedio || 0).toLocaleString('es-AR', {minimumFractionDigits: 0, maximumFractionDigits: 0})}`;
            }
        }
        
        // Cargar pedidos activos
        await cargarPedidosActivos();
    } catch (error) {
        console.error('Error al cargar métricas:', error);
        showToast('Error al cargar métricas', 'error');
    }
}

// Función para cerrar la sesión (utilizando fetch/AJAX)
function cerrarSesion() {
    // Llama al script PHP que destruye la sesión
    fetch(API_BASE + 'logout.php', { method: 'POST' })
        .then(response => {
            // El servidor PHP ya destruyó la sesión. Ahora redirigimos al usuario.
            // Usamos '../html/login.html' porque es la ruta de redirección que usas en 'verificarSesion()'.
            window.location.href = '../index.html'; 
        })
        .catch(error => {
            console.error('Error al cerrar sesión:', error);
            // Puedes usar tu función de notificación aquí si la tienes:
            // showToast('Error al cerrar sesión', 'error'); 
            
            // Y por seguridad, redirigir igualmente
            window.location.href = '../html/login.html';
        });
}