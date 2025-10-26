// Variables globales
let direccionesGuardadas = [];
let direccionSeleccionada = null;

// Cargar direcciones al abrir el modal
async function cargarDireccionesGuardadas() {
    try {
        const response = await fetch('?action=get_direcciones');
        const data = await response.json();
        
        if (data.success) {
            direccionesGuardadas = data.direcciones;
            mostrarDireccionesEnLista();
        } else {
            console.error('Error al cargar direcciones:', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Mostrar direcciones en la lista
function mostrarDireccionesEnLista() {
    const container = document.getElementById('direccionesGuardadas');
    
    if (direccionesGuardadas.length === 0) {
        container.innerHTML = `
            <div class="no-direcciones">
                <div class="no-direcciones-icon">📍</div>
                <p>No tienes direcciones guardadas</p>
                <p style="font-size: 14px; color: #999;">Agrega una nueva dirección para continuar</p>
            </div>
        `;
        // Mostrar automáticamente el formulario de nueva dirección
        mostrarNuevaDireccion();
        return;
    }
    
    let html = '';
    direccionesGuardadas.forEach(dir => {
        const favoritaIcon = dir.es_favorita ? '<span class="favorita-icon">⭐</span>' : '';
        const selectedClass = direccionSeleccionada && direccionSeleccionada.id === dir.id ? 'selected' : '';
        
        html += `
            <div class="direccion-card ${selectedClass}" onclick="seleccionarDireccion(${dir.id})">
                ${favoritaIcon}
                <div class="alias-badge">${dir.alias || 'Dirección'}</div>
                <div class="direccion-text">${dir.direccion}</div>
                ${dir.codigo_postal ? `<div class="direccion-detalles">CP: ${dir.codigo_postal}</div>` : ''}
                ${dir.instrucciones ? `<div class="direccion-detalles">📝 ${dir.instrucciones}</div>` : ''}
                <div class="check-icon">✓</div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Seleccionar una dirección
function seleccionarDireccion(id) {
    direccionSeleccionada = direccionesGuardadas.find(d => d.id === id);
    mostrarDireccionesEnLista();
    
    // Actualizar el campo de dirección en el formulario (si es necesario)
    if (direccionSeleccionada) {
        document.getElementById('address').value = direccionSeleccionada.direccion;
        document.getElementById('references').value = direccionSeleccionada.instrucciones || '';
    }
}

// Mostrar sección de direcciones guardadas
function mostrarDireccionesGuardadas() {
    document.getElementById('direccionesGuardadas').style.display = 'grid';
    document.getElementById('nuevaDireccionForm').style.display = 'none';
    
    // Actualizar botones
    document.querySelectorAll('.address-option-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelectorAll('.address-option-btn')[0].classList.add('active');
}

// Mostrar formulario de nueva dirección
function mostrarNuevaDireccion() {
    document.getElementById('direccionesGuardadas').style.display = 'none';
    document.getElementById('nuevaDireccionForm').style.display = 'block';
    direccionSeleccionada = null;
    
    // Actualizar botones
    document.querySelectorAll('.address-option-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelectorAll('.address-option-btn')[1].classList.add('active');
}

// Mostrar/ocultar opción de favorita
document.getElementById('guardar_direccion')?.addEventListener('change', function() {
    const favoritaGroup = document.getElementById('favorita_group');
    if (this.checked) {
        favoritaGroup.style.display = 'block';
    } else {
        favoritaGroup.style.display = 'none';
        document.getElementById('es_favorita').checked = false;
    }
});

// Guardar nueva dirección
async function guardarNuevaDireccion() {
    const guardar = document.getElementById('guardar_direccion').checked;
    
    if (!guardar) return null;
    
    const direccionData = {
        alias: document.getElementById('alias').value,
        direccion: document.getElementById('address').value,
        codigo_postal: document.getElementById('codigo_postal').value || null,
        instrucciones: document.getElementById('references').value || null,
        es_favorita: document.getElementById('es_favorita').checked ? 1 : 0
    };
    
    try {
        const response = await fetch('?action=guardar_direccion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(direccionData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarNotificacion('Dirección guardada exitosamente', 'success');
            await cargarDireccionesGuardadas();
            return data.direccion_id;
        } else {
            console.error('Error al guardar dirección:', data.message);
            return null;
        }
    } catch (error) {
        console.error('Error:', error);
        return null;
    }
}

// Modificar la función abrirModalPedido existente
const abrirModalPedidoOriginal = window.abrirModalPedido;
window.abrirModalPedido = async function() {
    if (abrirModalPedidoOriginal) {
        abrirModalPedidoOriginal();
    }
    
    // Cargar direcciones guardadas
    await cargarDireccionesGuardadas();
};
