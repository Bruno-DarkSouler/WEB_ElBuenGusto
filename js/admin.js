let selectedProducts = {};
        let deliveryPrice = 0;

        function openPhoneOrderModal() {
            document.getElementById('phoneOrderModal').style.display = 'block';
        }

        function closePhoneOrderModal() {
            document.getElementById('phoneOrderModal').style.display = 'none';
            resetForm();
        }

        function resetForm() {
            document.getElementById('phoneOrderForm').reset();
            selectedProducts = {};
            deliveryPrice = 0;
            updateOrderSummary();
            
            // Reset product cards
            document.querySelectorAll('.product-card').forEach(card => {
                card.classList.remove('selected');
                card.querySelector('.quantity-control').style.display = 'none';
            });
        }

        function toggleScheduleTime() {
            const orderType = document.getElementById('orderType').value;
            const scheduleGroup = document.getElementById('scheduleTimeGroup');
            
            if (orderType === 'programado') {
                scheduleGroup.style.display = 'block';
                document.getElementById('scheduleTime').required = true;
            } else {
                scheduleGroup.style.display = 'none';
                document.getElementById('scheduleTime').required = false;
            }
        }

        function searchCustomer() {
            const phone = document.getElementById('customerPhone').value;
            if (phone.length >= 10) {
                // Simulate customer search
                // In real app, this would query the database
                console.log('Searching customer with phone:', phone);
            }
        }

        function updateDeliveryPrice() {
            const zoneSelect = document.getElementById('deliveryZone');
            const selectedOption = zoneSelect.options[zoneSelect.selectedIndex];
            deliveryPrice = selectedOption.dataset.price ? parseInt(selectedOption.dataset.price) : 0;
            updateOrderSummary();
        }

        function filterProducts() {
            const category = document.getElementById('categoryFilter').value;
            const products = document.querySelectorAll('.product-card');
            
            products.forEach(product => {
                if (!category || product.dataset.category === category) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        }

        function selectProduct(id, name, price) {
            const card = event.currentTarget;
            const quantityControl = card.querySelector('.quantity-control');
            
            if (selectedProducts[id]) {
                // Deselect product
                delete selectedProducts[id];
                card.classList.remove('selected');
                quantityControl.style.display = 'none';
            } else {
                // Select product
                selectedProducts[id] = {
                    name: name,
                    price: price,
                    quantity: 1
                };
                card.classList.add('selected');
                quantityControl.style.display = 'flex';
            }
            
            updateOrderSummary();
        }

        function changeQuantity(id, change) {
            event.stopPropagation();
            
            if (selectedProducts[id]) {
                selectedProducts[id].quantity += change;
                
                if (selectedProducts[id].quantity <= 0) {
                    delete selectedProducts[id];
                    const card = document.querySelector(`[onclick*="${id}"]`);
                    card.classList.remove('selected');
                    card.querySelector('.quantity-control').style.display = 'none';
                } else {
                    const quantitySpan = event.target.parentNode.querySelector('.quantity');
                    quantitySpan.textContent = selectedProducts[id].quantity;
                }
                
                updateOrderSummary();
            }
        }

        function updateOrderSummary() {
            const orderItems = document.getElementById('orderItems');
            const subtotalElement = document.getElementById('subtotal');
            const deliveryElement = document.getElementById('deliveryPrice');
            const totalElement = document.getElementById('totalPrice');
            
            let subtotal = 0;
            let itemsHtml = '';
            
            Object.entries(selectedProducts).forEach(([id, product]) => {
                const itemTotal = product.price * product.quantity;
                subtotal += itemTotal;
                
                itemsHtml += `
                    <div class="summary-line">
                        <span>${product.name} x${product.quantity}</span>
                        <span>${itemTotal}</span>
                    </div>
                `;
            });
            
            orderItems.innerHTML = itemsHtml;
            subtotalElement.textContent = `${subtotal}`;
            deliveryElement.textContent = `${deliveryPrice}`;
            totalElement.textContent = `${subtotal + deliveryPrice}`;
        }

        // Form submission handler
        document.getElementById('phoneOrderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (Object.keys(selectedProducts).length === 0) {
                alert('Por favor selecciona al menos un producto');
                return;
            }
            
            // Collect form data
            const orderData = {
                customer: {
                    phone: document.getElementById('customerPhone').value,
                    name: document.getElementById('customerName').value,
                    email: document.getElementById('customerEmail').value,
                    address: document.getElementById('deliveryAddress').value
                },
                orderType: document.getElementById('orderType').value,
                scheduleTime: document.getElementById('scheduleTime').value,
                paymentMethod: document.getElementById('paymentMethod').value,
                deliveryZone: document.getElementById('deliveryZone').value,
                products: selectedProducts,
                comments: document.getElementById('orderComments').value,
                subtotal: Object.values(selectedProducts).reduce((sum, p) => sum + (p.price * p.quantity), 0),
                deliveryPrice: deliveryPrice,
                total: Object.values(selectedProducts).reduce((sum, p) => sum + (p.price * p.quantity), 0) + deliveryPrice
            };
            
            console.log('Order data:', orderData);
            
            // Show success message
            alert('✅ Pedido registrado exitosamente!\n\nNúmero de pedido: #' + Math.floor(Math.random() * 10000));
            
            // Close modal and reset form
            closePhoneOrderModal();
        });

        // Quick action functions
        function toggleLocalStatus() {
            if (confirm('¿Deseas cambiar el estado del local?')) {
                alert('Estado del local actualizado');
            }
        }

        function manageProducts() {
            alert('Redirigiendo a gestión de productos...');
        }

        function viewReports() {
            alert('Abriendo panel de reportes...');
        }

        function manageUsers() {
            alert('Abriendo gestión de usuarios...');
        }

        // Navigation handler
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all links
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                
                // Add active class to clicked link
                this.classList.add('active');
                
                // Here you would typically load the corresponding content
                console.log('Navigating to:', this.textContent.trim());
            });
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('phoneOrderModal');
            if (event.target === modal) {
                closePhoneOrderModal();
            }
        }

        // Set minimum datetime for scheduled orders
        function setMinScheduleTime() {
            const now = new Date();
            now.setMinutes(now.getMinutes() + 30); // Minimum 30 minutes from now
            const minDateTime = now.toISOString().slice(0, 16);
            document.getElementById('scheduleTime').min = minDateTime;
        }

        // Initialize minimum schedule time
        setMinScheduleTime();

        // Real-time clock for admin panel
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleString('es-AR', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            
            // You can add a clock element to display current time
            console.log('Current time:', timeString);
        }

        // Update clock every second
        setInterval(updateClock, 1000);

        // Simulate real-time data updates
        function simulateRealTimeUpdates() {
            // This would typically connect to a WebSocket or use Server-Sent Events
            // to receive real-time updates from the backend
            
            setInterval(() => {
                // Update dashboard cards with new data
                const activeOrdersCard = document.querySelector('.dashboard-card:nth-child(3) .card-value');
                if (activeOrdersCard) {
                    const currentValue = parseInt(activeOrdersCard.textContent);
                    const variation = Math.floor(Math.random() * 3) - 1; // -1, 0, or 1
                    activeOrdersCard.textContent = Math.max(0, currentValue + variation);
                }
            }, 30000); // Update every 30 seconds
        }

        // Start real-time updates simulation
        simulateRealTimeUpdates();

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + N = New phone order
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                openPhoneOrderModal();
            }
            
            // Escape = Close modal
            if (e.key === 'Escape') {
                closePhoneOrderModal();
            }
        });

        // Form auto-save (save to localStorage as draft)
        function autoSaveForm() {
            const formData = {
                customerPhone: document.getElementById('customerPhone').value,
                customerName: document.getElementById('customerName').value,
                customerEmail: document.getElementById('customerEmail').value,
                deliveryAddress: document.getElementById('deliveryAddress').value,
                orderType: document.getElementById('orderType').value,
                scheduleTime: document.getElementById('scheduleTime').value,
                paymentMethod: document.getElementById('paymentMethod').value,
                deliveryZone: document.getElementById('deliveryZone').value,
                orderComments: document.getElementById('orderComments').value,
                selectedProducts: selectedProducts
            };
            
            // In a real app, you might want to save to localStorage or send to server
            console.log('Auto-saving form data...', formData);
        }

        // Auto-save every 30 seconds when modal is open
        let autoSaveInterval;

        document.getElementById('phoneOrderModal').addEventListener('DOMNodeInserted', function() {
            if (this.style.display === 'block') {
                autoSaveInterval = setInterval(autoSaveForm, 30000);
            }
        });

        document.getElementById('phoneOrderModal').addEventListener('DOMNodeRemoved', function() {
            if (autoSaveInterval) {
                clearInterval(autoSaveInterval);
            }
        });
        