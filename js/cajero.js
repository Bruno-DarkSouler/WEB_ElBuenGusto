 // Global variables
 let selectedProducts = {};
 let deliveryPrice = 0;
 let currentCustomer = null;
 let orderCounter = 1001;

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
        const formattedTime = selectedTime.toLocaleString('es-AR', { hour: '2-digit', minute: '2-digit' });
        document.getElementById('deliveryTime').textContent = formattedTime;
    });

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

 }