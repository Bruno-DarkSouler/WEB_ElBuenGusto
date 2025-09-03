tailwind.config = {
    theme: {
        extend: {
            colors: {
                'primary-brown': 'rgb(80, 50, 20)',
                'primary-cream': 'rgb(245, 235, 210)',
                'primary-red': 'rgb(200, 30, 45)'
            },
            fontFamily: {
                'averia': ['"Averia Serif Libre"', 'serif']
            }
        }
    }
}
// Estado del repartidor
let isAvailable = false;
let pedidos = [
    {
        id: 1,
        cliente: "María González",
        direccion: "Av. Libertador 1234, San Isidro",
        telefono: "11-1234-5678",
        total: 12500,
        items: ["2x Pollo Entero", "1x Pizza Muzzarella", "2x Coca Cola"],
        estado: "listo",
        tiempoEstimado: "15 min",
        distancia: "2.3 km",
        reseñas: {
            entrega: { puntuacion: 5, comentario: "Llegó súper rápido y caliente!" }
        }
    },
    {
        id: 2,
        cliente: "Carlos Ruiz",
        direccion: "Mitre 567, Vicente López",
        telefono: "11-8765-4321",
        total: 8700,
        items: ["1x Empanadas x12", "1x Flan Casero"],
        estado: "listo",
        tiempoEstimado: "20 min",
        distancia: "1.8 km"
    },
    {
        id: 3,
        cliente: "Ana Torres",
        direccion: "Belgrano 890, Villa Adelina",
        telefono: "11-5555-1234",
        total: 15300,
        items: ["4x Milanesas", "1x Tarta J&Q", "3x Agua"],
        estado: "enCamino",
        tiempoEstimado: "10 min",
        distancia: "0.9 km"
    }
];

let historial = [
    {
        id: 10,
        cliente: "Luis Pérez",
        direccion: "San Martín 123",
        total: 9500,
        horaEntrega: "14:30",
        reseñas: {
            entrega: { puntuacion: 4, comentario: "Muy buen servicio" }
        }
    },
    {
        id: 11,
        cliente: "Sofia Mendez",
        direccion: "Rivadavia 456",
        total: 7200,
        horaEntrega: "13:15",
        reseñas: {
            entrega: { puntuacion: 5, comentario: "Excelente atención!" }
        }
    }
];

// Función para alternar estado
function toggleStatus() {
    isAvailable = !isAvailable;
    const statusButton = document.getElementById('statusToggle');
    const statusText = document.getElementById('statusText');
    
    if (isAvailable) {
        statusButton.className = 'px-4 py-2 rounded-full text-sm font-bold bg-green-500 text-white status-animation';
        statusText.textContent = 'Disponible';
        console.log("1")
    } else {
        statusButton.className = 'px-4 py-2 rounded-full text-sm font-bold bg-gray-500 text-white';
        statusText.textContent = 'No Disponible';
        console.log("0")
    }
    
    showNotification(isAvailable ? 'Estado cambiado a Disponible' : 'Estado cambiado a No Disponible');
}

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500'
    };
    
    notification.className = `${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg notification-slide`;
    notification.innerHTML = `
        <div class="flex items-center gap-3">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:bg-black hover:bg-opacity-20 p-1 rounded">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    
    document.getElementById('notifications').appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Función para marcar pedido como en camino
function marcarEnCamino(pedidoId) {
    const pedido = pedidos.find(p => p.id === pedidoId);
    if (pedido) {
        pedido.estado = 'enCamino';
        renderPedidos();
        showNotification(`Pedido #${pedidoId} marcado como "En Camino"`, 'success');
    }
}

// Función para marcar pedido como entregado
function marcarEntregado(pedidoId) {
    const pedido = pedidos.find(p => p.id === pedidoId);
    if (pedido) {
        pedido.estado = 'entregado';
        pedido.horaEntrega = new Date().toLocaleTimeString('es-AR', {hour: '2-digit', minute:'2-digit'});
        
        // Mover al historial
        historial.unshift(pedido);
        pedidos = pedidos.filter(p => p.id !== pedidoId);
        
        // Actualizar estadísticas
        updateStats();
        renderPedidos();
        renderHistorial();
        
        showNotification(`Pedido #${pedidoId} entregado correctamente`, 'success');
    }
}

// Función para ver reseñas
function verReseñas(pedidoId) {
    const pedido = [...pedidos, ...historial].find(p => p.id === pedidoId);
    if (pedido && pedido.reseñas) {
        const modal = document.getElementById('reviewModal');
        const content = document.getElementById('reviewContent');
        
        content.innerHTML = `
            <div class="space-y-4">
                <div class="text-center">
                    <h4 class="text-lg font-bold text-primary-brown">Pedido #${pedido.id}</h4>
                    <p class="text-gray-600">${pedido.cliente}</p>
                </div>
                
                ${pedido.reseñas.entrega ? `
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h5 class="font-bold text-blue-800 mb-2">Reseña del Servicio de Entrega</h5>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-yellow-500">${'⭐'.repeat(pedido.reseñas.entrega.puntuacion)}</span>
                        <span class="text-gray-600">(${pedido.reseñas.entrega.puntuacion}/5)</span>
                    </div>
                    <p class="text-gray-700">"${pedido.reseñas.entrega.comentario}"</p>
                </div>
                ` : '<p class="text-gray-500 text-center">Aún no hay reseñas para este pedido</p>'}
            </div>
        `;
        
        modal.classList.remove('hidden');
    }
}

// Función para cerrar modal de reseñas
function closeReviewModal() {
    document.getElementById('reviewModal').classList.add('hidden');
}

// Función para renderizar pedidos
function renderPedidos() {
    const pendientes = pedidos.filter(p => p.estado === 'listo');
    const enCamino = pedidos.filter(p => p.estado === 'enCamino');
    
    // Actualizar contadores
    document.getElementById('pendingCount').textContent = pendientes.length;
    document.getElementById('enCaminoCount').textContent = enCamino.length;
    document.getElementById('pedidosPendientes').textContent = pendientes.length;
    
    // Renderizar pendientes
    const pendientesContainer = document.getElementById('pedidosPendientesContainer');
    pendientesContainer.innerHTML = pendientes.length > 0 ? pendientes.map(pedido => `
        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-300 fade-in">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h4 class="font-bold text-primary-brown">Pedido #${pedido.id}</h4>
                    <p class="text-gray-600">${pedido.cliente}</p>
                </div>
                <span class="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm font-medium">
                    Listo
                </span>
            </div>
            
            <div class="space-y-2 text-sm text-gray-700 mb-4">
                <p><strong>📍 Dirección:</strong> ${pedido.direccion}</p>
                <p><strong>📞 Teléfono:</strong> ${pedido.telefono}</p>
                <p><strong>🕒 Tiempo est.:</strong> ${pedido.tiempoEstimado}</p>
                <p><strong>📏 Distancia:</strong> ${pedido.distancia}</p>
                <p><strong>💰 Total:</strong> $${pedido.total.toLocaleString()}</p>
            </div>
            
            <div class="mb-4">
                <p class="font-medium text-primary-brown mb-2">Items del pedido:</p>
                <ul class="text-sm text-gray-600">
                    ${pedido.items.map(item => `<li>• ${item}</li>`).join('')}
                </ul>
            </div>
            
            <div class="flex gap-2">
                <button onclick="marcarEnCamino(${pedido.id})" 
                        class="flex-1 bg-primary-red hover:bg-red-600 text-white py-2 px-4 rounded-lg transition-colors duration-300">
                    🚚 Salir a Entregar
                </button>
                ${pedido.reseñas ? `
                <button onclick="verReseñas(${pedido.id})" 
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg transition-colors duration-300">
                    📝
                </button>
                ` : ''}
            </div>
        </div>
    `).join('') : '<p class="text-gray-500 text-center py-8">No hay entregas pendientes</p>';
    
    // Renderizar en camino
    const enCaminoContainer = document.getElementById('pedidosEnCaminoContainer');
    enCaminoContainer.innerHTML = enCamino.length > 0 ? enCamino.map(pedido => `
        <div class="border border-blue-200 rounded-lg p-4 bg-blue-50 fade-in">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h4 class="font-bold text-primary-brown">Pedido #${pedido.id}</h4>
                    <p class="text-gray-600">${pedido.cliente}</p>
                </div>
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                    En Camino
                </span>
            </div>
            
            <div class="space-y-2 text-sm text-gray-700 mb-4">
                <p><strong>📍 Dirección:</strong> ${pedido.direccion}</p>
                <p><strong>📞 Teléfono:</strong> ${pedido.telefono}</p>
                <p><strong>💰 Total:</strong> $${pedido.total.toLocaleString()}</p>
            </div>
            
            <button onclick="marcarEntregado(${pedido.id})" 
                    class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-colors duration-300">
                ✅ Marcar como Entregado
            </button>
        </div>
    `).join('') : '<p class="text-gray-500 text-center py-8">No hay entregas en camino</p>';
}

// Función para renderizar historial
function renderHistorial() {
    const historialContainer = document.getElementById('historialContainer');
    historialContainer.innerHTML = historial.length > 0 ? historial.map(pedido => `
        <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
            <div>
                <p class="font-medium text-gray-800">Pedido #${pedido.id} - ${pedido.cliente}</p>
                <p class="text-sm text-gray-600">${pedido.direccion}</p>
                <p class="text-sm text-green-600">Entregado a las ${pedido.horaEntrega}</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-green-600">$${pedido.total.toLocaleString()}</p>
                ${pedido.reseñas ? `
                <button onclick="verReseñas(${pedido.id})" 
                        class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-full mt-1">
                    Ver Reseña
                </button>
                ` : ''}
            </div>
        </div>
    `).join('') : '<p class="text-gray-500 text-center py-4">No hay entregas completadas hoy</p>';
}

// Función para actualizar estadísticas
function updateStats() {
    const entregasHoy = historial.length;
    const gananciaHoy = historial.reduce((total, pedido) => total + (pedido.total * 0.1), 0); // 10% por entrega
    const promedioReseñas = historial
        .filter(p => p.reseñas && p.reseñas.entrega)
        .reduce((avg, p, _, arr) => avg + p.reseñas.entrega.puntuacion / arr.length, 0);
    
    document.getElementById('entregasHoy').textContent = entregasHoy;
    document.getElementById('gananciaHoy').textContent = `$${gananciaHoy.toLocaleString()}`;
    document.getElementById('promedioReseñas').textContent = promedioReseñas.toFixed(1);
}

// Inicializar la aplicación
function init() {
    renderPedidos();
    renderHistorial();
    updateStats();
    
    // Simular nuevos pedidos cada 30 segundos (para demostración)
    setInterval(() => {
        if (Math.random() > 0.7) { // 30% de probabilidad
            const nuevoId = Math.max(...pedidos.map(p => p.id)) + 1;
            pedidos.push({
                id: nuevoId,
                cliente: "Cliente Nuevo",
                direccion: "Dirección Nueva 123",
                telefono: "11-0000-0000",
                total: Math.floor(Math.random() * 15000) + 5000,
                items: ["Producto Nuevo"],
                estado: "listo",
                tiempoEstimado: "20 min",
                distancia: "1.5 km"
            });
            renderPedidos();
            showNotification('¡Nuevo pedido asignado!', 'success');
        }
    }, 30000);
}

// Inicializar cuando se carga la página
document.addEventListener('DOMContentLoaded', init);