// Variables globales
let cart = [];
let cartTotal = 0;
let productos = [];
let zonas = [];
let zonaSeleccionada = null;

// Inicialización cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Verificar si el usuario está logueado
    checkUserSession();
    
    // Inicializar eventos
    setupEventListeners();
    
    // Cargar carrito desde localStorage
    loadCartFromStorage();
    
    // Cargar productos desde la base de datos
    
    // Cargar zonas de delivery
    cargarZonas();
}
// Verificar si hay pedidos pendientes de confirmar
function verificarPedidosPendientes() {
    if (!window.location.pathname.includes('confirmar_pedido.php')) {
        fetch('../html/confirmar_pedido.php?action=verificar_pedidos_pendientes')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.pedidos.length > 0) {
                    // Redirigir a confirmación
                    window.location.href = 'confirmar_pedido.php';
                }
            })
            .catch(error => console.error('Error:', error));
    }
}

// Verificar cada 15 segundos
setInterval(verificarPedidosPendientes, 15000);
// Verificar al cargar
verificarPedidosPendientes();

function checkUserSession() {
    fetch('../php/check_session.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('userName').textContent = data.user.nombre;
            } else {
                window.location.href = '../index.html';
            }
        })
        .catch(error => {
            console.error('Error verificando sesión:', error);
            window.location.href = '../index.html';
        });
}

function cargarZonas() {
    fetch('inicio.php?action=get_zonas')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                zonas = data.zonas;
                const selectZona = document.getElementById('zona');
                selectZona.innerHTML = '<option value="">Seleccione una zona</option>';
                
                zonas.forEach(zona => {
                    const option = document.createElement('option');
                    option.value = zona.id;
                    option.textContent = `${zona.nombre} - $${parseFloat(zona.precio_delivery).toLocaleString()}`;
                    option.setAttribute('data-precio', zona.precio_delivery);
                    selectZona.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function setupEventListeners() {
    // Búsqueda de productos
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', handleSearch);
    
    // Filtros de categorías
    const categoryButtons = document.querySelectorAll('.category-btn');
    categoryButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            categoryButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filterProducts(this.dataset.category);
        });
    });
}

function handleSearch() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        const productName = card.querySelector('.product-name').textContent.toLowerCase();
        const productDescription = card.querySelector('.product-description').textContent.toLowerCase();
        
        if (productName.includes(searchTerm) || productDescription.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function filterProducts(category) {
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        if (category === 'all' || card.dataset.category === category) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
    
    document.getElementById('searchInput').value = '';
}

function toggleCart() {
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');
    
    cartSidebar.classList.toggle('open');
    cartOverlay.classList.toggle('active');
}

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
    showNotification(`${name} agregado al carrito`);
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    updateCartDisplay();
    saveCartToStorage();
}

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

function updateCartDisplay() {
    const cartItems = document.getElementById('cartItems');
    const cartCount = document.getElementById('cartCount');
    const cartTotal = document.getElementById('cartTotal');
    
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

function saveCartToStorage() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function loadCartFromStorage() {
    const savedCart = localStorage.getItem('cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
        updateCartDisplay();
    }
}

function abrirModalPedido() {
    // Verificar que hay productos en el carrito
    if (cart.length === 0) {
        showNotification('Tu carrito está vacío. Agrega productos antes de finalizar el pedido.', 'error');
        return;
    }
    
    const modal = document.getElementById("modal-compra");
    modal.style.display = "flex";
    
    // Actualizar resumen del pedido
    actualizarResumenPedido();
    
    // Cerrar carrito
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');
    cartSidebar.classList.remove('open');
    cartOverlay.classList.remove('active');
}

function cerrar_modal() {
    const modal = document.getElementById("modal-compra");
    modal.style.display = "none";
}

function actualizarPrecioDelivery() {
    const selectZona = document.getElementById('zona');
    const selectedOption = selectZona.options[selectZona.selectedIndex];
    
    if (selectedOption.value) {
        const precioDelivery = parseFloat(selectedOption.getAttribute('data-precio'));
        document.getElementById('costoEnvio').textContent = precioDelivery.toLocaleString();
        zonaSeleccionada = {
            id: selectedOption.value,
            precio: precioDelivery
        };
    } else {
        document.getElementById('costoEnvio').textContent = '0';
        zonaSeleccionada = null;
    }
    
    actualizarResumenPedido();
}

function actualizarResumenPedido() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const delivery = zonaSeleccionada ? zonaSeleccionada.precio : 0;
    const total = subtotal + delivery;
    
    document.getElementById('resumenSubtotal').textContent = subtotal.toLocaleString();
    document.getElementById('resumenDelivery').textContent = delivery.toLocaleString();
    document.getElementById('resumenTotal').textContent = total.toLocaleString();
}

function logout() {
    customConfirm('¿Estás seguro que deseas cerrar sesión?', () => {
        // Si el usuario confirma, se ejecuta esto:
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


function showNotification(message, type = 'success') {
    if (type === 'success') {
        notify.success(message);
    } else if (type === 'error') {
        notify.error(message);
    } else {
        notify.info(message);
    }
}

function atajoCarrito(e){
    if((e.code === "KeyC" || e.code === "KeyV" || e.code === "KeyX" || e.code === "KeyF" || e.code === "KeyD") && e.ctrlKey){
        toggleCart();
    }
}

document.addEventListener('click', function(event) {
    const cartSidebar = document.getElementById('cartSidebar');
    const cartBtn = document.querySelector('.cart-btn');
    
    if (!cartSidebar.contains(event.target) && !cartBtn.contains(event.target)) {
        if (cartSidebar.classList.contains('open')) {
            toggleCart();
        }
    }
});

document.addEventListener('keyup', atajoCarrito,false);

document.getElementById('cartSidebar').addEventListener('click', function(event) {
    event.stopPropagation();
});