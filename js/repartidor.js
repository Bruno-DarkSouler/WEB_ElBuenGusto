// Variables globales
let isAvailable = false;
let pedidos = [];
let historial = [];

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    init();
});

function init() {
    cargarDisponibilidad();
    cargarDatos();
    
    // Actualizar datos cada 30 segundos
    setInterval(() => {
        cargarDatos();
    }, 30000);
}

// Cargar disponibilidad actual
function cargarDisponibilidad() {
    fetch('repartidor.php?action=get_disponibilidad')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                isAvailable = data.disponible;
                actualizarEstadoBoton();
            }
        })
        .catch(error => console.error('Error:', error));
}

// Función para alternar estado
function toggleStatus() {
    isAvailable = !isAvailable;
    
    fetch('repartidor.php?action=toggle_disponibilidad', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            disponible: isAvailable
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            actualizarEstadoBoton();
            showNotification(isAvailable ? 'Estado cambiado a Disponible' : 'Estado cambiado a No Disponible', 'success');
        } else {
            isAvailable = !isAvailable; // Revertir
            showNotification('Error al cambiar estado', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        isAvailable = !isAvailable; // Revertir
        showNotification('Error de conexión', 'error');
    });
}

function actualizarEstadoBoton() {
    const statusButton = document.getElementById('statusToggle');
    const statusText = document.getElementById('statusText');
    
    if (isAvailable) {
        statusButton.className = 'px-4 py-2 rounded-full text-sm font-bold bg-green-500 text-white status-animation';
        statusText.textContent = 'Disponible';
    } else {
        statusButton.className = 'px-4 py-2 rounded-full text-sm font-bold bg-gray-500 text-white';
        statusText.textContent = 'No Disponible';
    }
}

// Cargar todos los datos
function cargarDatos() {
    cargarPedidos();
    cargarHistorial();
    cargarEstadisticas();
}

// Cargar pedidos asignados
function cargarPedidos() {
    fetch('repartidor.php?action=get_pedidos_asignados')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                pedidos = data.pedidos;
                renderPedidos();
            } else {
                console.error('Error:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Cargar historial
function cargarHistorial() {
    fetch('repartidor.php?action=get_historial')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                historial = data.historial;
                renderHistorial();
            } else {
                console.error('Error:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Cargar estadísticas
function cargarEstadisticas() {
    fetch('repartidor.php?action=get_estadisticas')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const stats = data.estadisticas;
                document.getElementById('entregasHoy').textContent = stats.entregas_hoy;
                document.getElementById('gananciaHoy').textContent = '$' + stats.ganancia_hoy.toLocaleString();
                document.getElementById('promedioReseñas').textContent = stats.promedio_resenas;
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Renderizar pedidos
function renderPedidos() {
    const pendientes = pedidos.filter(p => p.estado === 'listo');
    const enCamino = pedidos.filter(p => p.estado === 'en_camino');
    
    document.getElementById('pendingCount').textContent = pendientes.length;
    document.getElementById('enCaminoCount').textContent = enCamino.length;
    
    // Renderizar pendientes
    const pendientesContainer = document.getElementById('pedidosPendientesContainer');
    pendientesContainer.innerHTML = pendientes.length > 0 ? pendientes.map(pedido => `
        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-300 fade-in">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h4 class="font-bold text-primary-brown">Pedido #${pedido.numero_pedido}</h4>
                    <p class="text-gray-600">${pedido.cliente_nombre} ${pedido.cliente_apellido}</p>
                </div>
                <span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm font-medium">
                    Listo para retirar
                </span>
            </div>
            
            <div class="space-y-2 text-sm text-gray-700 mb-4">
                <p><strong>📍 Dirección:</strong> ${pedido.direccion_entrega}</p>
                <p><strong>📞 Teléfono:</strong> ${pedido.telefono_contacto}</p>
                ${pedido.zona_nombre ? `<p><strong>🗺️ Zona:</strong> ${pedido.zona_nombre}</p>` : ''}
                <p><strong>💰 Total:</strong> $${parseFloat(pedido.total).toLocaleString('es-AR', {minimumFractionDigits: 2})}</p>
            </div>
            
            <div class="mb-4">
                <p class="font-medium text-primary-brown mb-2">Items del pedido:</p>
                <ul class="text-sm text-gray-600">
                    ${pedido.items.map(item => `<li>• ${item}</li>`).join('')}
                </ul>
            </div>
            
            ${pedido.comentarios_cliente ? `
                <div class="mb-4 bg-yellow-50 p-3 rounded-lg border-l-4 border-yellow-400">
                    <p class="font-medium text-yellow-800 mb-1">💬 Comentarios del cliente:</p>
                    <p class="text-sm text-yellow-700">${pedido.comentarios_cliente}</p>
                </div>
            ` : ''}
            
            <button onclick="marcarEnCamino(${pedido.id})" 
                    class="w-full bg-primary-red hover:bg-red-600 text-white py-2 px-4 rounded-lg transition-colors duration-300 font-medium">
                🚚 Salir a Entregar
            </button>
        </div>
    `).join('') : '<p class="text-gray-500 text-center py-8">No hay entregas pendientes</p>';
    
    // Renderizar en camino
    const enCaminoContainer = document.getElementById('pedidosEnCaminoContainer');
    enCaminoContainer.innerHTML = enCamino.length > 0 ? enCamino.map(pedido => `
        <div class="border border-blue-200 rounded-lg p-4 bg-blue-50 fade-in">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h4 class="font-bold text-primary-brown">Pedido #${pedido.numero_pedido}</h4>
                    <p class="text-gray-600">${pedido.cliente_nombre} ${pedido.cliente_apellido}</p>
                </div>
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                    En Camino
                </span>
            </div>
            
            <div class="space-y-2 text-sm text-gray-700 mb-4">
                <p><strong>📍 Dirección:</strong> ${pedido.direccion_entrega}</p>
                <p><strong>📞 Teléfono:</strong> ${pedido.telefono_contacto}</p>
                <p><strong>💰 Total:</strong> $${parseFloat(pedido.total).toLocaleString('es-AR', {minimumFractionDigits: 2})}</p>
            </div>
            
            ${pedido.comentarios_cliente ? `
                <div class="mb-4 bg-yellow-50 p-3 rounded-lg border-l-4 border-yellow-400">
                    <p class="font-medium text-yellow-800 mb-1">💬 Comentarios:</p>
                    <p class="text-sm text-yellow-700">${pedido.comentarios_cliente}</p>
                </div>
            ` : ''}
            
            <button onclick="marcarEntregado(${pedido.id})" 
                    class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-colors duration-300 font-medium">
                ✅ Marcar como Entregado
            </button>
        </div>
    `).join('') : '<p class="text-gray-500 text-center py-8">No hay entregas en camino</p>';
}

// Función para marcar pedido como en camino
function marcarEnCamino(pedidoId) {
    customConfirm('¿Confirmar que sales a entregar este pedido?', () => {
        ejecutarMarcarEnCamino(pedidoId);
    });
}

function ejecutarMarcarEnCamino(pedidoId) {
    
    fetch('repartidor.php?action=marcar_en_camino', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            pedido_id: pedidoId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Pedido marcado como "En Camino"', 'success');
            cargarDatos();
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

// Función para marcar pedido como entregado
function marcarEntregado(pedidoId) {
    customConfirm('¿Confirmar que el pedido fue entregado al cliente?', () => {
        ejecutarMarcarEntregado(pedidoId);
    });
}

function ejecutarMarcarEntregado(pedidoId) {
    
    fetch('repartidor.php?action=marcar_entregado', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            pedido_id: pedidoId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Pedido entregado correctamente. El cliente debe confirmar la recepción.', 'success');
            cargarDatos();
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

// Función para renderizar historial
function renderHistorial() {
    const historialContainer = document.getElementById('historialContainer');
    historialContainer.innerHTML = historial.length > 0 ? historial.map(pedido => `
        <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
            <div>
                <p class="font-medium text-gray-800">Pedido #${pedido.numero_pedido} - ${pedido.cliente_nombre} ${pedido.cliente_apellido}</p>
                <p class="text-sm text-gray-600">${pedido.direccion_entrega}</p>
                <p class="text-sm text-green-600">Entregado${pedido.horaEntrega ? ' a las ' + pedido.horaEntrega : ''}</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-green-600">$${parseFloat(pedido.total).toLocaleString('es-AR', {minimumFractionDigits: 2})}</p>
                ${pedido.reseñas ? `
                <button onclick="verReseñas(${pedido.id})" 
                        class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-full mt-1">
                    Ver Reseña
                </button>
                ` : '<p class="text-xs text-gray-500 mt-1">Sin reseña aún</p>'}
            </div>
        </div>
    `).join('') : '<p class="text-gray-500 text-center py-4">No hay entregas completadas hoy</p>';
}

// Función para ver reseñas
function verReseñas(pedidoId) {
    const pedido = historial.find(p => p.id == pedidoId);
    if (!pedido || !pedido.reseñas) {
        showNotification('No hay reseñas disponibles para este pedido', 'info');
        return;
    }
    
    const modal = document.getElementById('reviewModal');
    const content = document.getElementById('reviewContent');
    
    content.innerHTML = `
        <div class="space-y-4">
            <div class="text-center">
                <h4 class="text-lg font-bold text-primary-brown">Pedido #${pedido.numero_pedido}</h4>
                <p class="text-gray-600">${pedido.cliente_nombre} ${pedido.cliente_apellido}</p>
            </div>
            
            ${pedido.reseñas.entrega ? `
            <div class="bg-blue-50 p-4 rounded-lg">
                <h5 class="font-bold text-blue-800 mb-2">Reseña del Servicio de Entrega</h5>
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-yellow-500">${'⭐'.repeat(pedido.reseñas.entrega.puntuacion)}</span>
                    <span class="text-gray-600">(${pedido.reseñas.entrega.puntuacion}/5)</span>
                </div>
                ${pedido.reseñas.entrega.comentario ? `
                    <p class="text-gray-700">"${pedido.reseñas.entrega.comentario}"</p>
                ` : '<p class="text-gray-500 italic">Sin comentarios</p>'}
            </div>
            ` : '<p class="text-gray-500 text-center">Aún no hay reseñas para este pedido</p>'}
        </div>
    `;
    
    modal.classList.remove('hidden');
}

// Función para cerrar modal de reseñas
function closeReviewModal() {
    document.getElementById('reviewModal').classList.add('hidden');
}

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    if (type === 'success') {
        notify.success(message);
    } else if (type === 'error') {
        notify.error(message);
    } else {
        notify.info(message);
    }
}
// Función de logout
function logout() {
    if (customConfirm('¿Está seguro que desea cerrar sesión?')) {
        fetch('../php/logout.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(() => {
            window.location.href = '../index.html';
        })
        .catch(() => {
            window.location.href = '../index.html';
        });
    }
}
