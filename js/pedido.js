function selectDeliveryOption(type) {
    // Limpiar selecciones previas
    document.querySelectorAll('.delivery-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Seleccionar opción actual
    const radioId = (type === 'inmediato') ? 'immediate' : 'scheduled';
    const radioElement = document.querySelector(`#${radioId}`);
    
    if (radioElement) {
        radioElement.checked = true;
        const parentOption = radioElement.closest('.delivery-option');
        if (parentOption) {
            parentOption.classList.add('selected');
        }
    }
    
    // Mostrar/ocultar campos de programación
    const scheduleFields = document.getElementById('scheduleFields');
    if (type === 'programado') {
        scheduleFields.style.display = 'block';
        scheduleFields.classList.add('active');
        
        // Establecer fecha mínima como hoy
        const today = new Date().toISOString().split('T')[0];
        const deliveryDate = document.getElementById('delivery_date');
        deliveryDate.setAttribute('min', today);
        deliveryDate.required = true;
        document.getElementById('delivery_time').required = true;
    } else {
        scheduleFields.style.display = 'none';
        scheduleFields.classList.remove('active');
        document.getElementById('delivery_date').required = false;
        document.getElementById('delivery_time').required = false;
    }
}

function selectPaymentMethod(method) {
    // Limpiar selecciones previas
    document.querySelectorAll('.payment-method').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Seleccionar opción actual
    const radioElement = document.querySelector(`#${method}`);
    if (radioElement) {
        radioElement.checked = true;
        const parentOption = radioElement.closest('.payment-method');
        if (parentOption) {
            parentOption.classList.add('selected');
        }
    }
}

// Validación y envío del formulario
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkoutForm');
    
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar que hay productos en el carrito
            if (typeof cart === 'undefined' || cart.length === 0) {
                alert('No hay productos en el carrito');
                return;
            }
            
            // Validar campos requeridos
            const requiredFields = ['name', 'phone', 'email', 'address', 'zona'];
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
            
            if (!isValid) {
                alert('Por favor complete todos los campos obligatorios');
                return;
            }
            
            // Validar tipo de entrega
            const deliveryType = document.querySelector('input[name="delivery_type"]:checked');
            if (!deliveryType) {
                alert('Por favor seleccione un tipo de entrega');
                return;
            }
            
            // Validar método de pago
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            if (!paymentMethod) {
                alert('Por favor seleccione un método de pago');
                return;
            }
            
            // Si es entrega programada, validar fecha y hora
            if (deliveryType.value === 'programado') {
                const date = document.getElementById('delivery_date').value;
                const time = document.getElementById('delivery_time').value;
                
                if (!date || !time) {
                    alert('Por favor complete la fecha y hora de entrega');
                    return;
                }
            }
            
            // Validar zona seleccionada
            if (typeof zonaSeleccionada === 'undefined' || !zonaSeleccionada) {
                alert('Por favor seleccione una zona de entrega');
                return;
            }
            
            // Preparar datos del pedido
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const precioDelivery = zonaSeleccionada.precio;
            const total = subtotal + precioDelivery;
            
            const pedidoData = {
                nombre: document.getElementById('name').value,
                telefono: document.getElementById('phone').value,
                email: document.getElementById('email').value,
                direccion: document.getElementById('address').value + 
                           (document.getElementById('floor').value ? ', ' + document.getElementById('floor').value : '') +
                           (document.getElementById('references').value ? ' - ' + document.getElementById('references').value : ''),
                zona_id: zonaSeleccionada.id,
                tipo_pedido: deliveryType.value,
                fecha_entrega: deliveryType.value === 'programado' ? document.getElementById('delivery_date').value : null,
                hora_entrega: deliveryType.value === 'programado' ? document.getElementById('delivery_time').value : null,
                metodo_pago: paymentMethod.value,
                comentarios: document.getElementById('comments').value,
                productos: cart.map(item => ({
                    id: item.id,
                    cantidad: item.quantity,
                    precio: item.price
                })),
                subtotal: subtotal,
                precio_delivery: precioDelivery,
                total: total
            };
            
            // Enviar pedido al servidor
            fetch('inicio.php?action=procesar_pedido', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(pedidoData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Crear contenedor de alerta bonita
                    const modal = document.createElement('div');
                    modal.className = 'pedido-exito-modal';
                    modal.innerHTML = `
                        <div class="pedido-exito-content">
                            <h2>✅ Pedido creado exitosamente</h2>
                            <p><strong>Número de pedido:</strong> ${data.numero_pedido}</p>
                            <p><strong>Cliente:</strong> ${pedidoData.nombre}</p>
                            <p><strong>Dirección:</strong> ${pedidoData.direccion}</p>
                            <p><strong>Total:</strong> $${pedidoData.total.toLocaleString()}</p>
                            <p>Recibirás una confirmación por email y podrás seguir el estado de tu pedido en tiempo real.</p>
                            <button id="cerrarExito" class="btn-exito">Aceptar</button>
                        </div>
                    `;
                    document.body.appendChild(modal);
            
                    // Estilos del modal
                    const style = document.createElement('style');
                    style.textContent = `
                        .pedido-exito-modal {
                            position: fixed;
                            top: 0; left: 0; right: 0; bottom: 0;
                            background: rgba(0,0,0,0.6);
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            z-index: 9999;
                            animation: fadeIn 0.3s ease;
                        }
                        .pedido-exito-content {
                            background: #fff;
                            color: #333;
                            padding: 25px 30px;
                            border-radius: 12px;
                            text-align: center;
                            width: 90%;
                            max-width: 400px;
                            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                            animation: popIn 0.4s ease;
                        }
                        .pedido-exito-content h2 {
                            color: #28a745;
                            margin-bottom: 10px;
                        }
                        .pedido-exito-content p {
                            margin: 5px 0;
                            font-size: 15px;
                        }
                        .btn-exito {
                            margin-top: 15px;
                            padding: 10px 20px;
                            background-color: #28a745;
                            color: white;
                            border: none;
                            border-radius: 8px;
                            cursor: pointer;
                            transition: background 0.3s ease;
                        }
                        .btn-exito:hover {
                            background-color: #218838;
                        }
                        @keyframes fadeIn { from {opacity:0;} to {opacity:1;} }
                        @keyframes popIn { from {transform:scale(0.8);} to {transform:scale(1);} }
                    `;
                    document.head.appendChild(style);
            
                    // Cerrar modal al hacer clic en "Aceptar"
                    document.getElementById('cerrarExito').addEventListener('click', () => {
                        document.body.removeChild(modal);
            
                        // Limpiar carrito y recargar
                        cart = [];
                        localStorage.removeItem('cart');
                        if (typeof updateCartDisplay === 'function') updateCartDisplay();
                        cerrar_modal();
                        setTimeout(() => window.location.reload(), 1500);
                    });
                } else {
                    showNotification('Error al procesar el pedido: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error al procesar el pedido. Intente nuevamente.', 'error');
            });
            
        });
    }
    
    // Establecer fecha mínima al cargar
    const today = new Date().toISOString().split('T')[0];
    const deliveryDateInput = document.getElementById('delivery_date');
    if (deliveryDateInput) {
        deliveryDateInput.setAttribute('min', today);
    }
});

function abrir_modal() {
    const modal = document.getElementById("modal-compra");
    modal.style.display = "flex";
    const cartSidebar = document.getElementById('cartSidebar');
    const cartOverlay = document.getElementById('cartOverlay');
    
    if (cartSidebar && cartOverlay) {
        cartSidebar.classList.remove('open');
        cartOverlay.classList.remove('active');
    }
}

function cerrar_modal() {
    const modal = document.getElementById("modal-compra");
    modal.style.display = "none";
}