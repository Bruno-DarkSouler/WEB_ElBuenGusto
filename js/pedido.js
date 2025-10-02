function selectDeliveryOption(type) {
    // Limpiar selecciones previas
    document.querySelectorAll('.delivery-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Seleccionar opción actual
    document.querySelector(`#${type}`).checked = true;
    document.querySelector(`#${type}`).closest('.delivery-option').classList.add('selected');
    
    // Mostrar/ocultar campos de programación
    const scheduleFields = document.getElementById('scheduleFields');
    if (type === 'scheduled') {
        scheduleFields.classList.add('active');
        // Establecer fecha mínima como hoy
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('delivery_date').setAttribute('min', today);
    } else {
        scheduleFields.classList.remove('active');
    }
}

function selectPaymentMethod(method) {
    // Limpiar selecciones previas
    document.querySelectorAll('.payment-method').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Seleccionar opción actual
    document.querySelector(`#${method}`).checked = true;
    document.querySelector(`#${method}`).closest('.payment-method').classList.add('selected');
}

// Validación y envío del formulario
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validaciones básicas
    const requiredFields = ['name', 'phone', 'email', 'address'];
    let isValid = true;
    
    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (!input.value.trim()) {
            input.style.borderColor = '#C81E2D';
            isValid = false;
        } else {
            input.style.borderColor = '#e0e0e0';
        }
    });
    
    // Validar tipo de entrega
    if (!document.querySelector('input[name="delivery_type"]:checked')) {
        alert('Por favor seleccione un tipo de entrega');
        isValid = false;
    }
    
    // Validar método de pago
    if (!document.querySelector('input[name="payment_method"]:checked')) {
        alert('Por favor seleccione un método de pago');
        isValid = false;
    }
    
    // Si es entrega programada, validar fecha y hora
    const deliveryType = document.querySelector('input[name="delivery_type"]:checked')?.value;
    if (deliveryType === 'scheduled') {
        const date = document.getElementById('delivery_date').value;
        const time = document.getElementById('delivery_time').value;
        
        if (!date || !time) {
            alert('Por favor complete la fecha y hora de entrega');
            isValid = false;
        }
    }
    
    if (isValid) {
        // Aquí iría la lógica de envío del formulario
        alert('¡Pedido confirmado! Recibirá una confirmación por email y podrá seguir el estado de su pedido en tiempo real.');
        // window.location.href = '/pedido-confirmado';
    }
});

// Establecer fecha mínima
const today = new Date().toISOString().split('T')[0];
document.getElementById('delivery_date').setAttribute('min', today);

function abrir_modal(){
    const modal = document.getElementById("modal-compra");
    modal.style.display= "flex";
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');
    
    cartSidebar.classList.toggle('open');
    cartOverlay.classList.toggle('active');
}
function cerrar_modal(){
    const modal = document.getElementById("modal-compra");
    modal.style.display= "none";
}