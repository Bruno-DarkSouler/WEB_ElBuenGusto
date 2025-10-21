// Variables globales
let pedidosActivos = [];
let repartidoresDisponibles = [];
let resenasRecientes = [];
let estadisticas = {};
let intervaloActualizacion = null;

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    inicializarPanel();
    
    // Actualizar datos cada 30 segundos
    // Actualizar datos cada 30 segundos
    intervaloActualizacion = setInterval(() => {
        cargarDatos();
        }, 30000);
    });
    // Función principal de inicialización
    // Función principal de inicialización
function inicializarPanel() {
    cargarDatos();
}

// Cargar todos los datos
function cargarDatos() {
    cargarPedidos();
    cargarRepartidores();
    cargarResenas();
    cargarEstadisticas();
}

// Cargar pedidos activos
function cargarPedidos() {
    fetch('cocinero.php?action=get_pedidos_activos')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                pedidosActivos = data.pedidos;
                renderPedidos();
                actualizarContador();
            } else {
                mostrarError('Error al cargar pedidos: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error de conexión al cargar pedidos');
        });
}

// Cargar repartidores
function cargarRepartidores() {
    fetch('cocinero.php?action=get_repartidores')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                repartidoresDisponibles = data.repartidores;
                renderRepartidores();
            } else {
                mostrarError('Error al cargar repartidores: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error de conexión al cargar repartidores');
        });
}
    // Cargar reseñas recientes
function cargarResenas() {
    fetch('cocinero.php?action=get_resenas_recientes')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resenasRecientes = data.resenas;
                renderResenas();
            } else {
                mostrarError('Error al cargar reseñas: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error de conexión al cargar reseñas');
        });
}

// Cargar estadísticas
function cargarEstadisticas() {
    fetch('cocinero.php?action=get_estadisticas')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                estadisticas = data.estadisticas;
                renderEstadisticas();
            } else {
                mostrarError('Error al cargar estadísticas: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error de conexión al cargar estadísticas');
        });
}

// Renderizar pedidos
function renderPedidos() {
    const container = document.getElementById('pedidos-container');
    
    if (pedidosActivos.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <p>No hay pedidos pendientes en este momento</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = '';
    
    pedidosActivos.forEach(pedido => {
        const card = crearTarjetaPedido(pedido);
        container.appendChild(card);
    });
}

// Crear tarjeta de pedido
function crearTarjetaPedido(pedido) {
    const card = document.createElement('div');
    card.className = 'pedido-card';
    card.setAttribute('data-pedido-id', pedido.id);
    
    const horaIngreso = formatearHora(pedido.fecha_pedido);
    const tiempoTranscurrido = calcularTiempoTranscurrido(pedido.fecha_pedido);
    
    let tiempoPreparacionReal = '';
    if (pedido.tiempos_estados && pedido.tiempos_estados.en_preparacion && pedido.tiempos_estados.listo) {
        const inicio = new Date(pedido.tiempos_estados.en_preparacion);
        const fin = new Date(pedido.tiempos_estados.listo);
        const minutos = Math.round((fin - inicio) / 60000);
        tiempoPreparacionReal = `
            <div class="tiempo-item">
                <span>✅</span>
                <span>Preparado en: ${minutos} min</span>
            </div>
        `;
    }
    
    card.innerHTML = `
        <div class="pedido-header">
            <div class="pedido-info">
                <h3>#${pedido.numero_pedido}</h3>
                <p>${pedido.cliente_nombre} ${pedido.cliente_apellido} • ${pedido.telefono_contacto}</p>
                <p class="direccion">📍 ${pedido.direccion_entrega}</p>
                ${pedido.zona_nombre ? `<p class="direccion">Zona: ${pedido.zona_nombre}</p>` : ''}
            </div>
            <div>
                <div class="estado-badge estado-${pedido.estado}">
                    ${getEstadoTexto(pedido.estado)}
                </div>
                <div class="tipo-pedido">
                    ${pedido.tipo_pedido === 'inmediato' ? '⚡ Inmediato' : '📅 Programado'}
                </div>
                ${pedido.tipo_pedido === 'programado' ? `
                    <div class="tipo-pedido" style="margin-top: 4px;">
                        🕐 ${formatearFechaHora(pedido.fecha_entrega_programada)}
                    </div>
                ` : ''}
            </div>
        </div>

        <div class="pedido-items">
            <h4>Productos:</h4>
            ${pedido.items.map(item => `
                <div class="item">
                    <div class="item-nombre">${item.cantidad}x ${item.producto_nombre}</div>
                    ${item.condimentos && item.condimentos.length > 0 ? 
                        `<div class="item-condimentos">Condimentos: ${item.condimentos.join(', ')}</div>` 
                        : ''}
                </div>
            `).join('')}
        </div>

        <div class="tiempo-info">
            <div class="tiempo-item">
                <span>🕐</span>
                <span>Ingresó: ${horaIngreso}</span>
            </div>
            <div class="tiempo-item">
                <span>⏱️</span>
                <span>Tiempo estimado: ${pedido.tiempo_estimado} min</span>
            </div>
            <div class="tiempo-item">
                <span>⏳</span>
                <span>Transcurrido: ${tiempoTranscurrido} min</span>
            </div>
            ${tiempoPreparacionReal}
        </div>

        ${pedido.comentarios_cliente ? `
            <div class="item" style="background: #fff3cd; border-left-color: #ffc107;">
                <div class="item-nombre">💬 Comentarios del cliente:</div>
                <div class="item-condimentos">${pedido.comentarios_cliente}</div>
            </div>
        ` : ''}

        <div class="acciones">
            ${pedido.estado === 'confirmado' ? `
                <button class="btn btn-iniciar" onclick="cambiarEstadoPedido(${pedido.id}, 'en_preparacion')">
                    🔥 Iniciar Preparación
                </button>
            ` : ''}
            
            ${pedido.estado === 'en_preparacion' ? `
                <button class="btn btn-listo" onclick="cambiarEstadoPedido(${pedido.id}, 'listo')">
                    ✅ Marcar como Listo
                </button>
            ` : ''}

            ${pedido.estado === 'listo' ? `
                <select class="select-repartidor" onchange="if(this.value) asignarRepartidor(${pedido.id}, this.value)">
                    <option value="">Asignar repartidor</option>
                    ${repartidoresDisponibles
                        .filter(r => r.disponible)
                        .map(r => `<option value="${r.id}">${r.nombre} ${r.apellido}</option>`)
                        .join('')}
                </select>
            ` : ''}
        </div>

        <div class="pedido-total">
            <p>Total: $${parseFloat(pedido.total).toLocaleString('es-AR', {minimumFractionDigits: 2})}</p>
        </div>
    `;
    
    return card;
}

// Renderizar repartidores
function renderRepartidores() {
    const container = document.getElementById('repartidores-container');
    
    if (repartidoresDisponibles.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: #666;">No hay repartidores registrados</p>';
        return;
    }
    
    container.innerHTML = '';
    
    repartidoresDisponibles.forEach(repartidor => {
        const div = document.createElement('div');
        div.className = 'repartidor';
        div.innerHTML = `
            <div class="repartidor-info">
                <div class="status-dot ${repartidor.disponible ? 'status-disponible' : 'status-ocupado'}"></div>
                <span class="repartidor-nombre">${repartidor.nombre} ${repartidor.apellido}</span>
            </div>
            <span class="status-badge ${repartidor.disponible ? 'badge-disponible' : 'badge-ocupado'}">
                ${repartidor.disponible ? 'Disponible' : 'Ocupado'}
            </span>
        `;
        container.appendChild(div);
    });
}

// Renderizar reseñas
function renderResenas() {
    const container = document.getElementById('resenas-container');
    
    if (resenasRecientes.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: #666;">No hay reseñas recientes</p>';
        return;
    }
    
    container.innerHTML = '';
    
    resenasRecientes.forEach(resena => {
        const div = document.createElement('div');
        div.className = 'resena';
        div.innerHTML = `
            <div class="resena-header">
                <span class="pedido-numero">#${resena.numero_pedido}</span>
                <div class="estrellas">
                    ${generarEstrellas(resena.calificacion_comida)}
                </div>
            </div>
            <p class="resena-comentario">${resena.comentario || 'Sin comentarios'}</p>
            <p class="resena-cliente">${resena.nombre} ${resena.apellido}</p>
        `;
        container.appendChild(div);
    });
}

// Renderizar estadísticas
function renderEstadisticas() {
    const container = document.getElementById('estadisticas-container');
    
    container.innerHTML = `
        <div class="stat-item">
            <span class="stat-label">Pedidos completados:</span>
            <span class="stat-value">${estadisticas.pedidos_completados || 0}</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Tiempo promedio:</span>
            <span class="stat-value">${estadisticas.tiempo_promedio || 0} min</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Calificación promedio:</span>
            <span class="stat-value">${estadisticas.calificacion_promedio || 0} ⭐</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Eficiencia del día:</span>
            <span class="stat-value">${estadisticas.eficiencia || 0}%</span>
        </div>
    `;
}

// Cambiar estado del pedido
function cambiarEstadoPedido(pedidoId, nuevoEstado) {
    if (!confirm('¿Confirmar cambio de estado del pedido?')) {
        return;
    }
    
    fetch('cocinero.php?action=cambiar_estado', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            pedido_id: pedidoId,
            nuevo_estado: nuevoEstado
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('✅ Estado actualizado correctamente');
            cargarDatos();
        } else {
            mostrarError('Error al actualizar estado: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError('Error de conexión al actualizar estado');
    });
}

// Asignar repartidor
function asignarRepartidor(pedidoId, repartidorId) {
    const repartidor = repartidoresDisponibles.find(r => r.id == repartidorId);
    
    if (!confirm(`¿Asignar pedido a ${repartidor.nombre} ${repartidor.apellido}?`)) {
        return;
    }
    
    fetch('cocinero.php?action=asignar_repartidor', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            pedido_id: pedidoId,
            repartidor_id: repartidorId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion(`✅ Pedido asignado a ${repartidor.nombre}`);
            cargarDatos();
        } else {
            mostrarError('Error al asignar repartidor: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError('Error de conexión al asignar repartidor');
    });
}

// Funciones auxiliares
function getEstadoTexto(estado) {
    const estados = {
        'confirmado': 'Confirmado',
        'en_preparacion': 'En Preparación',
        'listo': 'Listo'
    };
    return estados[estado] || estado;
}

function formatearHora(fecha) {
    const date = new Date(fecha);
    return date.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
}

function formatearFechaHora(fecha) {
    const date = new Date(fecha);
    return date.toLocaleString('es-AR', { 
        day: '2-digit', 
        month: '2-digit', 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

function calcularTiempoTranscurrido(fechaInicio) {
    const inicio = new Date(fechaInicio);
    const ahora = new Date();
    const diff = ahora - inicio;
    return Math.round(diff / 60000);
}

function generarEstrellas(calificacion) {
    let html = '';
    for (let i = 1; i <= 5; i++) {
        const clase = i <= calificacion ? 'estrella' : 'estrella vacia';
        html += `<span class="${clase}">★</span>`;
    }
    return html;
}

function actualizarContador() {
    document.getElementById('pedidos-count').textContent = pedidosActivos.length;
}

function mostrarNotificacion(mensaje) {
    const notif = document.createElement('div');
    notif.className = 'notificacion';
    notif.textContent = mensaje;
    document.body.appendChild(notif);
    
    setTimeout(() => {
        notif.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        notif.classList.remove('show');
        setTimeout(() => {
            if (document.body.contains(notif)) {
                document.body.removeChild(notif);
            }
        }, 300);
    }, 3000);
}

function mostrarError(mensaje) {
    console.error(mensaje);
    alert(mensaje);
}

function logout() {
    if (confirm('¿Está seguro que desea cerrar sesión?')) {
        clearInterval(intervaloActualizacion);
        
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