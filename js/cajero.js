// Global variables
let selectedProducts = {};
let deliveryPrice = 0;
let currentCustomer = null;
let orderCounter = 1001;
let subtotal = 0;

// Sample products database
const products = [
    // Minutas
    { id: 1, name: 'Milanesa con Papas', description: 'Milanesa de carne con guarnición de papas fritas', price: 850, category: 'minutas', available: true, cookTime: 25 },
    { id: 2, name: 'Suprema a la Napolitana', description: 'Suprema de pollo con jamón, queso y salsa', price: 920, category: 'minutas', available: true, cookTime: 30 },
    { id: 3, name: 'Bife de Chorizo', description: 'Bife de chorizo a la parrilla con guarnición', price: 1200, category: 'minutas', available: true, cookTime: 20 },
    { id: 4, name: 'Pollo al Horno', description: '1/4 de pollo al horno con papas', price: 780, category: 'minutas', available: false, cookTime: 35 },

    // Pastas
    { id: 5, name: 'Ravioles con Salsa', description: 'Ravioles caseros con salsa bolognesa', price: 720, category: 'pastas', available: true, cookTime: 15 },
    { id: 6, name: 'Ñoquis con Tuco', description: 'Ñoquis de papa con salsa de tomate', price: 650, category: 'pastas', available: true, cookTime: 12 },
    { id: 7, name: 'Tallarines con Crema', description: 'Tallarines con salsa de crema y jamón', price: 690, category: 'pastas', available: true, cookTime: 15 },

    // Empanadas
    { id: 8, name: 'Empanadas de Carne (x6)', description: 'Media docena de empanadas de carne cortada a cuchillo', price: 480, category: 'empanadas', available: true, cookTime: 20 },
    { id: 9, name: 'Empanadas de Jamón y Queso (x6)', description: 'Media docena de empanadas de jamón y queso', price: 450, category: 'empanadas', available: true, cookTime: 20 },
    { id: 10, name: 'Empanadas de Pollo (x6)', description: 'Media docena de empanadas de pollo', price: 460, category: 'empanadas', available: true, cookTime: 20 },

    // Bebidas
    { id: 11, name: 'Coca Cola 1.5L', description: 'Gaseosa Coca Cola botella 1.5 litros', price: 320, category: 'bebidas', available: true, cookTime: 0 },
    { id: 12, name: 'Agua Mineral 500ml', description: 'Agua mineral sin gas', price: 180, category: 'bebidas', available: true, cookTime: 0 },
    { id: 13, name: 'Cerveza Quilmes 473ml', description: 'Cerveza Quilmes lata 473ml', price: 280, category: 'bebidas', available: true, cookTime: 0 },

    // Guisos
    { id: 14, name: 'Guiso de Lentejas', description: 'Guiso casero de lentejas con chorizo', price: 580, category: 'guisos', available: true, cookTime: 30 },
    { id: 15, name: 'Locro', description: 'Locro criollo tradicional', price: 620, category: 'guisos', available: true, cookTime: 25 },

    // Tartas
    { id: 16, name: 'Tarta de Jamón y Queso', description: 'Tarta casera de jamón y queso', price: 890, category: 'tartas', available: true, cookTime: 40 },
    { id: 17, name: 'Tarta de Verdura', description: 'Tarta de acelga y ricota', price: 820, category: 'tartas', available: true, cookTime: 40 },

    // Postres
    { id: 18, name: 'Flan Casero', description: 'Flan casero con dulce de leche', price: 250, category: 'postres', available: true, cookTime: 0 },
    { id: 19, name: 'Tiramisu', description: 'Tiramisu individual', price: 320, category: 'postres', available: true, cookTime: 0 },

    // Embutidos
    { id: 20, name: 'Jamón Crudo (100g)', description: 'Jamón crudo premium por 100 gramos', price: 450, category: 'embutidos', available: true, cookTime: 0 },
    { id: 21, name: 'Queso Provolone (100g)', description: 'Queso provolone por 100 gramos', price: 380, category: 'embutidos', available: true, cookTime: 0 }
];

// Sample customer database
const customers = [
    { phone: '1112345678', name: 'Juan Pérez', email: 'juan@email.com', address: 'Av. Corrientes 1234, CABA', isVip: false },
    { phone: '1187654321', name: 'María García', email: 'maria@email.com', address: 'San Martín 567, San Isidro', isVip: true },
    { phone: '1145678901', name: 'Carlos López', email: 'carlos@email.com', address: 'Belgrano 890, Vicente López', isVip: false }
];

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);
    loadProducts();
    loadActiveOrders();
    setupEventListeners();
    setMinScheduleTime();
    setupCategoryTabs();
});

// Update current time
function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleString('es-AR', {
        weekday: 'short',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    document.getElementById('currentTime').textContent = timeString;
}

// Set minimum schedule time to current time + 30 minutes
function setMinScheduleTime() {
    const now = new Date();
    now.setMinutes(now.getMinutes() + 30); // Add 30 minutes
    
    // Format for datetime-local input
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    
    const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
    document.getElementById('scheduleTime').min = minDateTime;
    document.getElementById('scheduleTime').value = minDateTime;
    
    // Actualizar el texto de entrega programada en el resumen
    const formattedTime = now.toLocaleString('es-AR', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit' });
    document.getElementById('deliveryTime').textContent = formattedTime;
}

// Setup event listeners
function setupEventListeners() {
    // Phone input with customer search
    document.getElementById('customerPhone').addEventListener('input', function(e) {
        const phone = e.target.value.replace(/\D/g, '');
        
        if (phone.length >= 8) {
            searchCustomer(phone);
            document.getElementById('searchAlert').style.display = 'none';
        } else if (phone.length > 0) {
            document.getElementById('searchAlert').style.display = 'block';
            clearCustomerData();
        } else {
            document.getElementById('searchAlert').style.display = 'none';
            clearCustomerData();
        }
    });

    // Schedule time change
    document.getElementById('scheduleTime').addEventListener('change', function(e) {
        const selectedTime = new Date(e.target.value);
        const formattedTime = selectedTime.toLocaleString('es-AR', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit' });
        document.getElementById('deliveryTime').textContent = formattedTime;
    });

    // Order type change
    document.getElementById('orderType').addEventListener('change', function(e) {
        const scheduleGroup = document.getElementById('scheduleGroup');
        const scheduleDisplay = document.getElementById('scheduleDisplay');
        if (e.target.value === 'programado') {
            scheduleGroup.style.display = 'block';
            document.getElementById('scheduleTime').required = true;
            scheduleDisplay.style.display = 'flex'; // Muestra el div del resumen
        } else {
            scheduleGroup.style.display = 'none';
            document.getElementById('scheduleTime').required = false;
            scheduleDisplay.style.display = 'none'; // Oculta el div del resumen
        }
    });

    // Delivery zone change
    document.getElementById('deliveryZone').addEventListener('change', function(e) {
        const selectedOption = e.target.options[e.target.selectedIndex];
        deliveryPrice = selectedOption.dataset.price ? parseInt(selectedOption.dataset.price) : 0;
        updateOrderSummary();
    });
    
    // Form submission
    document.getElementById('phoneOrderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar que haya productos seleccionados
        if (Object.keys(selectedProducts).length === 0) {
            alert('Debe seleccionar al menos un producto para realizar el pedido');
            return;
        }
        
        // Aquí iría el código para procesar el pedido
        alert('Pedido procesado correctamente');
        clearForm();
    });
}

// Load products into the grid
function loadProducts(category = 'all') {
    const productsGrid = document.getElementById('productsGrid');
    productsGrid.innerHTML = '';
    
    const filteredProducts = category === 'all' 
        ? products 
        : products.filter(product => product.category === category);
    
    if (filteredProducts.length === 0) {
        productsGrid.innerHTML = '<p class="no-products">No hay productos disponibles en esta categoría</p>';
        return;
    }
    
    filteredProducts.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = `product-card ${!product.available ? 'unavailable' : ''}`;
        productCard.dataset.id = product.id;
        
        productCard.innerHTML = `
            <div class="product-info">
                <h4>${product.name}</h4>
                <p class="product-description">${product.description}</p>
                <div class="product-meta">
                    <span class="product-price">$${product.price}</span>
                    ${product.cookTime > 0 ? `<span class="cook-time">⏱️ ${product.cookTime} min</span>` : ''}
                </div>
            </div>
            <div class="product-actions">
                <button type="button" class="btn-quantity decrease" ${!product.available ? 'disabled' : ''}>-</button>
                <span class="quantity">0</span>
                <button type="button" class="btn-quantity increase" ${!product.available ? 'disabled' : ''}>+</button>
            </div>
        `;
        
        productsGrid.appendChild(productCard);
        
        // Add event listeners for quantity buttons
        if (product.available) {
            const increaseBtn = productCard.querySelector('.increase');
            const decreaseBtn = productCard.querySelector('.decrease');
            const quantitySpan = productCard.querySelector('.quantity');
            
            increaseBtn.addEventListener('click', () => {
                const currentQty = parseInt(quantitySpan.textContent);
                const newQty = currentQty + 1;
                quantitySpan.textContent = newQty;
                updateSelectedProducts(product, newQty);
            });
            
            decreaseBtn.addEventListener('click', () => {
                const currentQty = parseInt(quantitySpan.textContent);
                if (currentQty > 0) {
                    const newQty = currentQty - 1;
                    quantitySpan.textContent = newQty;
                    updateSelectedProducts(product, newQty);
                }
            });
        }
    });
}

// Setup category tabs
function setupCategoryTabs() {
    const tabs = document.querySelectorAll('.category-tab');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            tab.classList.add('active');
            
            // Load products for selected category
            const category = tab.dataset.category;
            loadProducts(category);
        });
    });
}

// Update selected products
function updateSelectedProducts(product, quantity) {
    if (quantity > 0) {
        selectedProducts[product.id] = {
            ...product,
            quantity
        };
    } else {
        delete selectedProducts[product.id];
    }
    
    updateOrderSummary();
}

// Update order summary
function updateOrderSummary() {
    const orderItemsContainer = document.getElementById('orderItems');
    const subtotalElement = document.getElementById('subtotal');
    const deliveryPriceElement = document.getElementById('deliveryPrice');
    const totalPriceElement = document.getElementById('totalPrice');
    
    // Calculate subtotal
    subtotal = Object.values(selectedProducts).reduce(
        (sum, product) => sum + (product.price * product.quantity), 0
    );
    
    // Update order items display
    if (Object.keys(selectedProducts).length === 0) {
        orderItemsContainer.innerHTML = `
            <p style="text-align: center; color: #666; font-style: italic;">
                No hay productos seleccionados
            </p>
        `;
    } else {
        orderItemsContainer.innerHTML = Object.values(selectedProducts)
            .map(product => `
                <div class="order-item">
                    <div class="item-details">
                        <span class="item-name">${product.name}</span>
                        <span class="item-price">$${product.price} x ${product.quantity}</span>
                    </div>
                    <span class="item-total">$${product.price * product.quantity}</span>
                </div>
            `)
            .join('');
    }
    
    // Update summary
    subtotalElement.textContent = `$${subtotal}`;
    deliveryPriceElement.textContent = `$${deliveryPrice}`;
    totalPriceElement.textContent = `$${subtotal + deliveryPrice}`;
}

// Search for existing customer
function searchCustomer(phone) {
    const customer = customers.find(c => c.phone.includes(phone));
    
    if (customer) {
        currentCustomer = customer;
        document.getElementById('customerName').value = customer.name;
        document.getElementById('customerEmail').value = customer.email || '';
        document.getElementById('deliveryAddress').value = customer.address || '';
    }
}

// Clear customer data
function clearCustomerData() {
    currentCustomer = null;
    document.getElementById('customerName').value = '';
    document.getElementById('customerEmail').value = '';
    document.getElementById('deliveryAddress').value = '';
}

// Load active orders
function loadActiveOrders() {
    // This would normally fetch from a database
    const activeOrdersList = document.getElementById('activeOrdersList');
    
    // Sample active orders
    const activeOrders = [
        { id: 1000, customer: 'Juan Pérez', time: '14:30', items: 3, total: 2450, status: 'preparacion' },
        { id: 999, customer: 'María García', time: '14:15', items: 2, total: 1800, status: 'enviado' },
        { id: 998, customer: 'Carlos López', time: '13:45', items: 4, total: 3200, status: 'listo' }
    ];
    
    if (activeOrders.length === 0) {
        activeOrdersList.innerHTML = '<p class="no-orders">No hay pedidos activos</p>';
        return;
    }
    
    activeOrdersList.innerHTML = activeOrders.map(order => `
        <div class="order-card status-${order.status}">
            <div class="order-header">
                <span class="order-id">#${order.id}</span>
                <span class="order-time">${order.time}</span>
            </div>
            <div class="order-customer">${order.customer}</div>
            <div class="order-details">
                <span>${order.items} items</span>
                <span class="order-total">$${order.total}</span>
            </div>
            <div class="order-status">
                ${getStatusLabel(order.status)}
            </div>
        </div>
    `).join('');
}

// Get status label
function getStatusLabel(status) {
    const statusLabels = {
        'recibido': '🔵 Recibido',
        'preparacion': '🟠 En preparación',
        'listo': '🟢 Listo para envío',
        'enviado': '🟣 En camino'
    };
    
    return statusLabels[status] || status;
}

// Clear form
function clearForm() {
    document.getElementById('phoneOrderForm').reset();
    clearCustomerData();
    selectedProducts = {};
    updateOrderSummary();
    
    // Reset product quantities
    document.querySelectorAll('.product-card .quantity').forEach(qty => {
        qty.textContent = '0';
    });
    
    // Hide schedule fields
    document.getElementById('scheduleGroup').style.display = 'none';
    document.getElementById('scheduleDisplay').style.display = 'none';
}