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
    
    const container = document.getElementById('notifications');
    if (container) {
        container.appendChild(notification);
    }
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Guardar los valores originales del perfil
function guardarDatosOriginales() {
    datosOriginales = {
        nombreCompleto: document.getElementById('nombreCompleto')?.value || '',
        email: document.getElementById('email')?.value || '',
        telefono: document.getElementById('telefono')?.value || '',
        fechaNacimiento: document.getElementById('fechaNacimiento')?.value || '',
        direccion: document.getElementById('direccion')?.value || '',
        comidaFavorita: document.getElementById('comidaFavorita')?.value || '',
        horarioPreferido: document.getElementById('horarioPreferido')?.value || '',
        notificaciones: document.getElementById('notificaciones')?.checked || false,
        recordatorios: document.getElementById('recordatorios')?.checked || false,
        newsletter: document.getElementById('newsletter')?.checked || false
    };
}

// Cancelar cambios
function cancelarCambios() {
    if (!datosOriginales) return;
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

// Funciones para direcciones
function eliminarDireccion(id) {
    if (confirm('¿Estás seguro de que quieres eliminar esta dirección?')) {
        showNotification('Dirección eliminada correctamente', 'success');
    }
}

function editarDireccion(id) {
    const modal = document.getElementById('modalDireccion');
    if (modal) modal.classList.remove('hidden');
}

function agregarDireccion() {
    const modal = document.getElementById('modalDireccion');
    if (modal) modal.classList.remove('hidden');
}

function cerrarModalDireccion() {
    const modal = document.getElementById('modalDireccion');
    if (modal) modal.classList.add('hidden');
    const form = document.getElementById('formDireccion');
    if (form) form.reset();
}


function verTodosLosPedidos() {
    const modal = document.getElementById('verTodosLosPedidos');
    if (modal) modal.classList.remove('hidden');
}

function cerrarModalPedidos() {
    const modal = document.getElementById('verTodosLosPedidos');
    if (modal) modal.classList.add('hidden');
}
// Event listeners seguros
document.addEventListener('DOMContentLoaded', function() {
    guardarDatosOriginales();

    const perfilForm = document.getElementById('perfilForm');
    if (perfilForm) {
        perfilForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const nuevaPassword = document.getElementById('nuevaPassword').value;
            const confirmarPassword = document.getElementById('confirmarPassword').value;
            
            if (nuevaPassword && nuevaPassword !== confirmarPassword) {
                showNotification('Las contraseñas no coinciden', 'error');
                return;
            }

            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showNotification('Ingrese un email válido', 'error');
                return;
            }

            guardarDatosOriginales();
            showNotification('Perfil actualizado correctamente', 'success');

            const nombreUsuario = document.getElementById('nombreUsuario');
            const nombreCompleto = document.getElementById('nombreCompleto');
            if (nombreUsuario && nombreCompleto) {
                nombreUsuario.textContent = nombreCompleto.value;
            }
        });
    }

    const formDireccion = document.getElementById('formDireccion');
    if (formDireccion) {
        formDireccion.addEventListener('submit', function(e) {
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
    }
});


// ========== FUNCIONES PARA CARGAR PRODUCTOS Y ZONAS EN PERFIL.PHP ==========

// Cargar productos
function cargarProductos() {
    fetch('?action=get_productos')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('✅ Productos cargados:', data.productos.length);
            } else {
                console.error('❌ Error al cargar productos:', data.message);
                showNotification('Error al cargar productos', 'error');
            }
        })
        .catch(error => {
            console.error('❌ Error:', error);
            showNotification('Error al cargar productos', 'error');
        });
}

// Cargar zonas de delivery
function cargarZonasDelivery() {
    fetch('?action=get_zonas')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const selectZona = document.getElementById('zona');
                if (selectZona) {
                    selectZona.innerHTML = '<option value="">Seleccione una zona</option>';
                    
                    data.zonas.forEach(zona => {
                        const option = document.createElement('option');
                        option.value = zona.id;
                        option.textContent = `${zona.nombre} - $${parseFloat(zona.precio_delivery).toLocaleString()}`;
                        option.setAttribute('data-precio', zona.precio_delivery);
                        selectZona.appendChild(option);
                    });
                    
                    console.log('✅ Zonas cargadas:', data.zonas.length);
                }
            } else {
                console.error('❌ Error al cargar zonas:', data.message);
            }
        })
        .catch(error => {
            console.error('❌ Error:', error);
        });
}

// Actualizar precio de delivery
function actualizarPrecioDelivery() {
    const selectZona = document.getElementById('zona');
    const selectedOption = selectZona.options[selectZona.selectedIndex];
    
    if (selectedOption.value) {
        const precioDelivery = parseFloat(selectedOption.getAttribute('data-precio'));
        document.getElementById('costoEnvio').textContent = precioDelivery.toLocaleString();
        document.getElementById('resumenDelivery').textContent = precioDelivery.toLocaleString();
        
        window.zonaSeleccionada = {
            id: selectedOption.value,
            precio: precioDelivery
        };
        
        actualizarResumenPedido();
    } else {
        document.getElementById('costoEnvio').textContent = '0';
        document.getElementById('resumenDelivery').textContent = '0';
        window.zonaSeleccionada = null;
        actualizarResumenPedido();
    }
}

// Actualizar resumen del pedido
function actualizarResumenPedido() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const subtotal = cart.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    const delivery = window.zonaSeleccionada ? window.zonaSeleccionada.precio : 0;
    const total = subtotal + delivery;
    
    document.getElementById('resumenSubtotal').textContent = subtotal.toLocaleString();
    document.getElementById('resumenDelivery').textContent = delivery.toLocaleString();
    document.getElementById('resumenTotal').textContent = total.toLocaleString();
}

console.log('✅ perfil.js cargado con funciones de productos y zonas');

// ========== FUNCIONES DEL CARRITO ==========

let cart = [];
let zonaSeleccionada = null;

// Cargar carrito desde localStorage
function loadCartFromStorage() {
    const savedCart = localStorage.getItem('cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
        updateCartDisplay();
    }
}

// Guardar carrito en localStorage
function saveCartToStorage() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

// Toggle del carrito
function toggleCart() {
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');
    
    if (cartSidebar && cartOverlay) {
        cartSidebar.classList.toggle('open');
        cartOverlay.classList.toggle('active');
    }
}

// Agregar al carrito
function addToCart(id, name, price, image) {
    const existingItem = cart.find(item => item.id === id);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: id,
            name: name,
            price: parseFloat(price),
            image: image,
            quantity: 1
        });
    }
    
    updateCartDisplay();
    saveCartToStorage();
    showNotification(`${name} agregado al carrito`, 'success');
}

// Eliminar del carrito
function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    updateCartDisplay();
    saveCartToStorage();
}

// Actualizar cantidad
function updateQuantity(id, change) {
    const item = cart.find(item => item.id === id);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            removeFromCart(id);
        } else {
            updateCartDisplay();
            saveCartToStorage();
        }
    }
}

// Actualizar visualización del carrito
function updateCartDisplay() {
    const cartItems = document.getElementById('cartItems');
    const cartCount = document.getElementById('cartCount');
    const cartTotal = document.getElementById('cartTotal');
    
    if (!cartItems || !cartCount || !cartTotal) return;
    
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.textContent = totalItems;
    
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    cartTotal.textContent = total.toLocaleString();
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="empty-cart">Tu carrito está vacío</p>';
    } else {
        cartItems.innerHTML = cart.map(item => `
            <div class="cart-item">
                <img src="${item.image}" alt="${item.name}">
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">$${item.price.toLocaleString()}</div>
                    <div class="cart-item-quantity">
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                        <span>${item.quantity}</span>
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                        <button class="quantity-btn" onclick="removeFromCart(${item.id})" style="margin-left: 10px; background-color: #C81E2D; color: white;">🗑</button>
                    </div>
                </div>
            </div>
        `).join('');
    }
}

// Abrir modal de pedido
function abrirModalPedido() {
    if (cart.length === 0) {
        showNotification('Tu carrito está vacío. Agrega productos antes de finalizar el pedido.', 'error');
        return;
    }
    
    const modal = document.getElementById("modal-compra");
    if (modal) {
        modal.style.display = "flex";
    }
    
    // Cargar zonas de delivery
    cargarZonasDelivery();
    
    // Cargar direcciones guardadas
    setTimeout(() => {
        if (typeof cargarDireccionesGuardadas === 'function') {
            cargarDireccionesGuardadas();
        }
    }, 300);
    
    // Actualizar resumen del pedido
    actualizarResumenPedido();
    
    // Cerrar carrito
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');
    if (cartSidebar) cartSidebar.classList.remove('open');
    if (cartOverlay) cartOverlay.classList.remove('active');
}

// Cerrar modal
function cerrar_modal() {
    const modal = document.getElementById("modal-compra");
    if (modal) {
        modal.style.display = "none";
    }
}

// Logout
function logout() {
    if (confirm('¿Estás seguro que deseas cerrar sesión?')) {
        localStorage.removeItem('cart');
        
        fetch('../php/logout.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(() => {
            window.location.href = '../index.php';
        })
        .catch(() => {
            window.location.href = '../index.php';
        });
    }
}

// Cerrar carrito al hacer clic fuera
document.addEventListener('click', function(event) {
    const cartSidebar = document.getElementById('cartSidebar');
    const cartBtn = document.querySelector('.cart-btn');
    
    if (cartSidebar && cartBtn) {
        if (!cartSidebar.contains(event.target) && !cartBtn.contains(event.target)) {
            if (cartSidebar.classList.contains('open')) {
                toggleCart();
            }
        }
    }
});

// Inicializar carrito cuando carga la página
document.addEventListener('DOMContentLoaded', function() {
    loadCartFromStorage();
    console.log('✅ Carrito inicializado con', cart.length, 'productos');
});

console.log('✅ Funciones del carrito cargadas');