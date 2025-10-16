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

// Variables globales
let datosOriginales = {};
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

// Función para cambiar foto de perfil
function cambiarFoto(input) {
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('fotoPerfilGrande').src = e.target.result;
            document.getElementById('avatarUsuario').src = e.target.result;
            showNotification('Foto de perfil actualizada', 'success');
        };
        reader.readAsDataURL(file);
    }
}

// Función para guardar datos originales
function guardarDatosOriginales() {
    datosOriginales = {
        nombreCompleto: document.getElementById('nombreCompleto').value,
        email: document.getElementById('email').value,
        telefono: document.getElementById('telefono').value,
        fechaNacimiento: document.getElementById('fechaNacimiento').value,
        direccion: document.getElementById('direccion').value,
        comidaFavorita: document.getElementById('comidaFavorita').value,
        horarioPreferido: document.getElementById('horarioPreferido').value,
        notificaciones: document.getElementById('notificaciones').checked,
        recordatorios: document.getElementById('recordatorios').checked,
        newsletter: document.getElementById('newsletter').checked
    };
}

// Función para cancelar cambios
function cancelarCambios() {
    document.getElementById('nombreCompleto').value = datosOriginales.nombreCompleto;
    document.getElementById('email').value = datosOriginales.email;
    document.getElementById('telefono').value = datosOriginales.telefono;
    document.getElementById('fechaNacimiento').value = datosOriginales.fechaNacimiento;
    document.getElementById('direccion').value = datosOriginales.direccion;
    document.getElementById('comidaFavorita').value = datosOriginales.comidaFavorita;
    document.getElementById('horarioPreferido').value = datosOriginales.horarioPreferido;
    document.getElementById('notificaciones').checked = datosOriginales.notificaciones;
    document.getElementById('recordatorios').checked = datosOriginales.recordatorios;
    document.getElementById('newsletter').checked = datosOriginales.newsletter;
    document.getElementById('nuevaPassword').value = '';
    document.getElementById('confirmarPassword').value = '';
    
    showNotification('Cambios cancelados', 'info');
}






//funcion Direccion ALL

// Función para eliminar dirección
function eliminarDireccion(id) {
    if (confirm('¿Estás seguro de que quieres eliminar esta dirección?')) {
        showNotification('Dirección eliminada correctamente', 'success');
        // Aquí iría la lógica para eliminar la dirección
    }
}
// Función para editar dirección
function editarDireccion(id) {
    document.getElementById('modalDireccion').classList.remove('hidden');
    // Aquí se cargarían los datos de la dirección para editar
}
// Función para agregar dirección
function agregarDireccion() {
    document.getElementById('modalDireccion').classList.remove('hidden');
}
function cerrarModalDireccion() {
    document.getElementById('modalDireccion').classList.add('hidden');
    document.getElementById('formDireccion').reset();
}




// Función para repetir pedido
function repetirPedido(id) {
    showNotification(`Pedido #${id} agregado al carrito`, 'success');
}
// Función para ver todos los pedidos
function verTodosLosPedidos() {
    document.getElementById('verTodosLosPedidos').classList.remove('hidden');
}
function cerrarModalPedidos() {
    document.getElementById('verTodosLosPedidos').classList.add('hidden');
    document.getElementById('formDireccion').reset();
}


// Formatear número de tarjeta
document.getElementById('numeroTarjeta').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    let formattedValue = value.replace(/(\d{4})(?=\d)/g, '$1 ');
    e.target.value = formattedValue;
});

// Formatear fecha de vencimiento
document.getElementById('vencimiento').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0,2) + '/' + value.substring(2,4);
    }
    e.target.value = value;
});

// Formatear CVV
document.getElementById('cvv').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
});

// Event listeners para formularios
document.getElementById('perfilForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validar contraseñas si se ingresaron
    const nuevaPassword = document.getElementById('nuevaPassword').value;
    const confirmarPassword = document.getElementById('confirmarPassword').value;
    
    if (nuevaPassword && nuevaPassword !== confirmarPassword) {
        showNotification('Las contraseñas no coinciden', 'error');
        return;
    }

    // Validar email
    const email = document.getElementById('email').value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showNotification('Ingrese un email válido', 'error');
        return;
    }

    // Guardar cambios
    guardarDatosOriginales();
    showNotification('Perfil actualizado correctamente', 'success');
    
    // Actualizar nombre en header
    document.getElementById('nombreUsuario').textContent = document.getElementById('nombreCompleto').value;
});

document.getElementById('formTarjeta').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const numeroTarjeta = document.getElementById('numeroTarjeta').value.replace(/\s/g, '');
    const vencimiento = document.getElementById('vencimiento').value;
    const cvv = document.getElementById('cvv').value;
    const nombreTitular = document.getElementById('nombreTitular').value;

    if (numeroTarjeta.length !== 16) {
        showNotification('Número de tarjeta inválido', 'error');
        return;
    }

    if (!/^\d{2}\/\d{2}$/.test(vencimiento)) {
        showNotification('Fecha de vencimiento inválida', 'error');
        return;
    }

    if (cvv.length !== 3) {
        showNotification('CVV inválido', 'error');
        return;
    }

    if (!nombreTitular.trim()) {
        showNotification('Ingrese el nombre del titular', 'error');
        return;
    }

    cerrarModalTarjeta();
    showNotification('Tarjeta agregada correctamente', 'success');
});

document.getElementById('formDireccion').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const direccionCompleta = document.getElementById('direccionCompleta').value;
    const ciudad = document.getElementById('ciudad').value;
    const codigoPostal = document.getElementById('codigoPostal').value;

    if (!direccionCompleta.trim() || !ciudad.trim() || !codigoPostal.trim()) {
        showNotification('Complete todos los campos obligatorios', 'error');
        return;
    }

    cerrarModalDireccion();
    showNotification('Dirección agregada correctamente', 'success');
});

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    guardarDatosOriginales();
});

// perfil.js
function cambiarFoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
            document.getElementById('fotoPerfilGrande').src = e.target.result;
        }

        reader.readAsDataURL(input.files[0]);
    }
}