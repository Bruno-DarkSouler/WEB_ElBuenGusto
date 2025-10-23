// Variables globales
let isAvailable = false;
let pedidos = [];
let historial = [];

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Iniciando aplicación repartidor');
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
    console.log('📡 Cargando disponibilidad...');
    fetch('repartidor.php?action=get_disponibilidad')
        .then(response => response.json())
        .then(data => {
            console.log('✅ Disponibilidad:', data);
            if (data.success) {
                isAvailable = data.disponible;
                actualizarEstadoBoton();
            }
        })
        .catch(error => console.error('❌ Error disponibilidad:', error));
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
            isAvailable = !isAvailable;
            showNotification('Error al cambiar estado', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        isAvailable = !isAvailable;
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
    console.log('📊 Cargando datos...');
    cargarPedidos();
    cargarHistorial();
    cargarEstadisticas();
}

// Cargar pedidos asignados
function cargarPedidos() {
    console.log('📦 Cargando pedidos asignados...');
    fetch('repartidor.php?action=get_pedidos_asignados')
        .then(response => response.json())
        .then(data => {
            console.log('✅ Pedidos recibidos:', data);
            if (data.success) {
                pedidos = data.pedidos;
                renderPedidos();
            } else {
                console.error('❌ Error en pedidos:', data.message);
            }
        })
        .catch(error => {
            console.error('❌ Error fetch pedidos:', error);
        });
}

// Cargar historial
function cargarHistorial() {
    console.log('📜 Cargando historial...');
    fetch('repartidor.php?action=get_historial')
        .then(response => response.json())
        .then(data => {
            console.log('✅ Historial recibido:', data);
            if (data.success) {
                historial = data.historial;
                renderHistorial();
            } else {
                console.error('❌ Error en historial:', data.message);
            }
        })
        .catch(error => {
            console.error('❌ Error fetch historial:', error);
        });
}

// Cargar estadísticas
function cargarEstadisticas() {
    console.log('📈 Cargando estadísticas...');
    fetch('repartidor.php?action=get_estadisticas')
        .then(response => response.json())
        .then(data => {
            console.log('✅ Estadísticas recibidas:', data);
            if (data.success) {
                const stats = data.estadisticas;
                document.getElementById('entregasHoy').textContent = stats.entregas_hoy;
                document.getElementById('gananciaHoy').textContent = '$' + parseFloat(stats.ganancia_hoy).toLocaleString('es-AR', {minimumFractionDigits: 2});
                document.getElementById('promedioReseñas').textContent = stats.promedio_resenas > 0 ? stats.promedio_resenas : 'Sin reseñas';
            } else {
                console.error('❌ Error en estadísticas:', data.message);
            }
        })
        .catch(error => {
            console.error('❌ Error fetch estadísticas:', error);
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
            notify.success('Pedido marcado como entregado. Esperando confirmación del cliente...');
            cargarDatos();
        } else {
            notify.error('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        notify.error('Error de conexión');
    });
}

// Función para renderizar historial
function renderHistorial() {
    const historialContainer = document.getElementById('historialContainer');
    
    console.log('📋 Renderizando historial:', historial);
    
    if (historial.length === 0) {
        historialContainer.innerHTML = '<p class="text-gray-500 text-center py-4">No hay entregas completadas hoy</p>';
        return;
    }
    
    historialContainer.innerHTML = historial.map(pedido => `
        <div class="flex justify-between items-center p-4 bg-green-50 rounded-lg border-l-4 border-green-500 hover:shadow-md transition-shadow">
            <div class="flex-1">
                <p class="font-medium text-gray-800">Pedido #${pedido.numero_pedido} - ${pedido.cliente_nombre} ${pedido.cliente_apellido}</p>
                <p class="text-sm text-gray-600 mt-1">📍 ${pedido.direccion_entrega}</p>
                <p class="text-sm text-green-600 font-medium mt-1">
                    ✅ Entregado${pedido.horaEntrega ? ' a las ' + pedido.horaEntrega : ''}
                </p>
            </div>
            <div class="text-right ml-4">
                <p class="font-bold text-green-600 text-lg">$${parseFloat(pedido.total).toLocaleString('es-AR', {minimumFractionDigits: 2})}</p>
                <p class="text-xs text-gray-500 mt-1">Ganancia: $${parseFloat(pedido.precio_delivery).toLocaleString('es-AR', {minimumFractionDigits: 2})}</p>
                ${pedido.tiene_resena ? `
                <button onclick="verResena(${pedido.id})" 
                        class="text-sm bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-full mt-2 transition-colors">
                    ⭐ Ver Reseña
                </button>
                ` : '<p class="text-xs text-gray-400 mt-2 italic">Sin reseña aún</p>'}
            </div>
        </div>
    `).join('');
}

// Función para ver reseña
function verResena(pedidoId) {
    const pedido = historial.find(p => p.id == pedidoId);
    if (!pedido || !pedido.tiene_resena) {
        showNotification('No hay reseñas disponibles para este pedido', 'info');
        return;
    }
    
    const modal = document.getElementById('reviewModal');
    const content = document.getElementById('reviewContent');
    
    content.innerHTML = `
        <div class="space-y-4">
            <div class="text-center border-b pb-4">
                <h4 class="text-lg font-bold text-primary-brown">Pedido #${pedido.numero_pedido}</h4>
                <p class="text-gray-600">${pedido.cliente_nombre} ${pedido.cliente_apellido}</p>
                <p class="text-sm text-gray-500 mt-1">Total: $${parseFloat(pedido.total).toLocaleString('es-AR', {minimumFractionDigits: 2})}</p>
            </div>
            
            ${pedido.resena.delivery.puntuacion > 0 ? `
            <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                <h5 class="font-bold text-blue-800 mb-2">🚚 Reseña del Servicio de Entrega</h5>
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-yellow-500 text-xl">${'⭐'.repeat(pedido.resena.delivery.puntuacion)}${'☆'.repeat(5 - pedido.resena.delivery.puntuacion)}</span>
                    <span class="text-gray-600 font-medium">(${pedido.resena.delivery.puntuacion}/5)</span>
                </div>
                ${pedido.resena.delivery.comentario ? `
                    <p class="text-gray-700 italic">"${pedido.resena.delivery.comentario}"</p>
                ` : '<p class="text-gray-500 text-sm italic">Sin comentarios</p>'}
            </div>
            ` : ''}
            
            ${pedido.resena.comida.puntuacion > 0 ? `
            <div class="bg-orange-50 p-4 rounded-lg border-l-4 border-orange-500">
                <h5 class="font-bold text-orange-800 mb-2">🍽️ Calificación de la Comida</h5>
                <div class="flex items-center gap-2">
                    <span class="text-yellow-500 text-xl">${'⭐'.repeat(pedido.resena.comida.puntuacion)}${'☆'.repeat(5 - pedido.resena.comida.puntuacion)}</span>
                    <span class="text-gray-600 font-medium">(${pedido.resena.comida.puntuacion}/5)</span>
                </div>
            </div>
            ` : ''}
            
            ${!pedido.resena.delivery.puntuacion && !pedido.resena.comida.puntuacion ? `
                <p class="text-gray-500 text-center py-4">No hay reseñas disponibles</p>
            ` : ''}
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
    customConfirm('¿Estás seguro que deseas cerrar sesión?', () => {
        localStorage.removeItem('cart');

        fetch('../php/logout.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            window.location.href = '../index.html';
        })
        .catch(error => {
            console.log('Cerrando sesión...');
            window.location.href = '../index.html';
        });
    });
}