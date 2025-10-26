<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cajero - Pedidos Telefónicos | Rotisería</title>
    <link rel="stylesheet" href="../css/cajero.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1>📞 Panel Cajero - Pedidos Telefónicos</h1>
            <div class="status-indicator">
                <div class="time-display" id="currentTime"></div>
                <div class="local-status status-open" id="localStatus">
                    <span>🟢</span>
                    <span>ABIERTO</span>
                </div>
            </div>
        </header>
        <main>
        <div class="main-layout">
            <!-- Phone Order Form -->
            <div class="phone-form-container">
                <div class="form-header">
                    <h2>Nuevo Pedido Telefónico</h2>
                    <p>Complete los datos del cliente y seleccione los productos</p>
                </div>

                <!-- Customer Search Alert -->
                <div id="searchAlert" class="alert alert-warning" style="display: none;">
                    🔍 Ingrese al menos 8 dígitos para buscar cliente existente
                </div>

                <form id="phoneOrderForm">
                    <!-- Customer Information -->
                    <div class="form-group">
                        <label class="form-label required" for="customerPhone">Teléfono del Cliente</label>
                        <input 
                            type="tel" 
                            id="customerPhone" 
                            class="form-control phone-input" 
                            placeholder="Ej: 11-1234-5678" 
                            required
                        >
                        <small style="color: #666;">Formato: Código de área + número</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="customerName">Nombre Completo</label>
                        <input 
                            type="text" 
                            id="customerName" 
                            class="form-control" 
                            placeholder="Nombre y apellido del cliente" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="customerEmail">Email (Opcional)</label>
                        <input 
                            type="email" 
                            id="customerEmail" 
                            class="form-control" 
                            placeholder="email@cliente.com"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="deliveryAddress">Dirección de Entrega</label>
                        <textarea 
                            id="deliveryAddress" 
                            class="form-control" 
                            rows="3" 
                            placeholder="Calle número" 
                            required
                        ></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="deliveryDetails">Detalles (Opcional)</label>
                        <textarea 
                            id="deliveryDetails" 
                            class="form-control" 
                            rows="2" 
                            placeholder="piso, depto. Referencias adicionales..." 
                        ></textarea>
                    </div>
                    
                    <!-- Order Details -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label required" for="orderType">Tipo de Pedido</label>
                            <select id="orderType" class="form-control" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="inmediato">Inmediato</option>
                                <option value="programado">Programado</option>
                            </select>
                        </div>

                        <div class="form-group" id="scheduleGroup" style="display: none;">
                            <label class="form-label" for="scheduleTime">Hora de Entrega</label>
                            <input 
                                type="datetime-local" 
                                id="scheduleTime" 
                                class="form-control"
                            >
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label required" for="paymentMethod">Método de Pago</label>
                            <select id="paymentMethod" class="form-control" required>
                                <option value="">Seleccionar método</option>
                                <option value="digital">💳 Digital (MP, Cuenta DNI)</option>
                                <option value="efectivo">💵 Efectivo (Solo autorizados)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label required" for="deliveryZone">Zona de Entrega</label>
                            <select id="deliveryZone" class="form-control" required>
                                <option value="">Seleccionar zona</option>
                                <option value="1" data-price="200" data-time="20-25">Centro - $2000 (20-25min)</option>
                                <option value="2" data-price="300" data-time="25-30">Norte - $3000 (25-30min)</option>
                                <option value="3" data-price="400" data-time="30-40">Sur - $4000 (30-40min)</option>
                                <option value="4" data-price="500" data-time="40-50">Periférico - $5000 (40-50min)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Product Selection -->
                    <div class="product-section">
                        <h3 style="color: var(--tertiary-color); margin-bottom: 1rem;">🍽️ Selección de Productos</h3>
                        
                        <!-- Category Filter -->
                        <div class="category-tabs" id="categoryTabs">
                            <button type="button" class="category-tab active" data-category="all">Todos</button>
                            <button type="button" class="category-tab" data-category="minutas">Minutas</button>
                            <button type="button" class="category-tab" data-category="pastas">Pastas</button>
                            <button type="button" class="category-tab" data-category="guisos">Guisos</button>
                            <button type="button" class="category-tab" data-category="empanadas">Empanadas</button>
                            <button type="button" class="category-tab" data-category="tartas">Tartas</button>
                            <button type="button" class="category-tab" data-category="postres">Postres</button>
                            <button type="button" class="category-tab" data-category="bebidas">Bebidas</button>
                            <button type="button" class="category-tab" data-category="embutidos">Embutidos</button>
                        </div>

                        <div class="products-grid" id="productsGrid">
                            <!-- Products will be loaded here -->
                        </div>
                    </div>

                    <!-- Comments -->
                    <div class="form-group">
                        <label class="form-label" for="orderComments">Comentarios Especiales</label>
                        <textarea 
                            id="orderComments" 
                            class="form-control" 
                            rows="2" 
                            placeholder="Instrucciones especiales, sin sal, extra salsa, etc."
                        ></textarea>
                    </div>

                    <!-- Form Actions -->
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="button" class="btn btn-secondary" onclick="clearForm()">
                           Limpiar
                        </button>
                        <button type="submit" class="btn btn-primary btn-block">
                           Confirmar Pedido Telefónico
                        </button>
                    </div>
                </form>
            </div>

            <!-- Orders Sidebar -->
            <div class="orders-sidebar">
                <!-- Current Order Summary -->
                <div class="current-order">
                    <div class="section-title">Resumen del Pedido Actual</div>
                    
                    <div id="orderItems" style="margin-bottom: 1rem;">
                        <p style="text-align: center; color: #666; font-style: italic;">
                            No hay productos seleccionados
                        </p>
                    </div>
                    <div id="scheduleDisplay" class="summary-line" style="display: none;">
                        <span>Entrega programada:</span>
                        <span id="deliveryTime"></span>
                    </div>
                    <div class="order-summary">
                        <div class="summary-line">
                            <span>Subtotal:</span>
                            <span id="subtotal">$0</span>
                        </div>
                        <div class="summary-line">
                            <span>Delivery:</span>
                            <span id="deliveryPrice">$0</span>
                        </div>
                        <div class="summary-line summary-total">
                            <span>TOTAL:</span>
                            <span id="totalPrice">$0</span>
                        </div>
                    </div>
                </div>

                <!-- Active Orders -->
                <div class="active-orders">
                    <div class="section-title">Pedidos Activos (Telefono)</div>
                    <div id="activeOrdersList">
                        <!-- Active orders will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    </main>
    </body>
    <script src="../js/cajero.js"></script>
