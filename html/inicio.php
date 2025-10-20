<?php
session_start();

// Verificar si hay sesión activa
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.html');
    exit;
}

require_once '../php/conexion.php';

// Obtener información del usuario
$usuario_nombre = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Usuario';
$usuario_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';

// ========== MANEJO DE PETICIONES AJAX ==========
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_productos':
            try {
                $query = "SELECT p.*, c.nombre as categoria_nombre 
                          FROM productos p 
                          INNER JOIN categorias c ON p.categoria_id = c.id 
                          WHERE p.activo = 1 AND p.disponible = 1
                          ORDER BY c.id, p.nombre";
                
                $resultado = $conexion->query($query);
                
                $productos = [];
                while ($row = $resultado->fetch_assoc()) {
                    $productos[] = [
                        'id' => $row['id'],
                        'nombre' => $row['nombre'],
                        'descripcion' => $row['descripcion'],
                        'precio' => $row['precio'],
                        'imagen' => $row['imagen'],
                        'categoria_id' => $row['categoria_id'],
                        'categoria_nombre' => strtolower($row['categoria_nombre']),
                        'ingredientes' => $row['ingredientes'],
                        'valoracion_promedio' => $row['valoracion_promedio']
                    ];
                }
                
                echo json_encode(['success' => true, 'productos' => $productos]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error al obtener productos: ' . $e->getMessage()]);
            }
            exit;
            
        case 'get_zonas':
            try {
                $query = "SELECT id, nombre, precio_delivery, tiempo_estimado 
                          FROM zonas_delivery 
                          WHERE activa = 1 
                          ORDER BY nombre";
                
                $resultado = $conexion->query($query);
                
                $zonas = [];
                while ($row = $resultado->fetch_assoc()) {
                    $zonas[] = $row;
                }
                
                echo json_encode(['success' => true, 'zonas' => $zonas]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error al obtener zonas: ' . $e->getMessage()]);
            }
            exit;
            
        case 'procesar_pedido':
            // Obtener datos del POST
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
                exit;
            }
            
            try {
                $conexion->begin_transaction();
                
                // Generar número de pedido único
                $numero_pedido = 'PED' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
                
                // Determinar estado según tipo de pedido
                $estado = ($data['tipo_pedido'] === 'programado') ? 'pendiente' : 'confirmado';
                
                // Insertar pedido
                $stmt = $conexion->prepare("INSERT INTO pedidos 
                    (numero_pedido, usuario_id, tipo_pedido, fecha_entrega_programada, 
                     direccion_entrega, telefono_contacto, metodo_pago, estado, 
                     subtotal, precio_delivery, total, zona_delivery_id, comentarios_cliente) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $fecha_entrega = ($data['tipo_pedido'] === 'programado') 
                    ? $data['fecha_entrega'] . ' ' . $data['hora_entrega'] 
                    : null;
                
                $metodo_pago = 'digital'; // Ambos métodos son digitales
                
                $stmt->bind_param(
                    "sissssssdddis",
                    $numero_pedido,
                    $_SESSION['user_id'],
                    $data['tipo_pedido'],
                    $fecha_entrega,
                    $data['direccion'],
                    $data['telefono'],
                    $metodo_pago,
                    $estado,
                    $data['subtotal'],
                    $data['precio_delivery'],
                    $data['total'],
                    $data['zona_id'],
                    $data['comentarios']
                );
                
                $stmt->execute();
                $pedido_id = $conexion->insert_id;
                
                // Insertar items del pedido
                $stmt_items = $conexion->prepare("INSERT INTO pedido_items 
                    (pedido_id, producto_id, cantidad, precio_unitario, precio_total) 
                    VALUES (?, ?, ?, ?, ?)");
                
                foreach ($data['productos'] as $producto) {
                    $precio_total = $producto['precio'] * $producto['cantidad'];
                    $stmt_items->bind_param(
                        "iiidd",
                        $pedido_id,
                        $producto['id'],
                        $producto['cantidad'],
                        $producto['precio'],
                        $precio_total
                    );
                    $stmt_items->execute();
                }
                
                // Insertar seguimiento
                $stmt_seguimiento = $conexion->prepare("INSERT INTO seguimiento_pedidos 
                    (pedido_id, estado_anterior, estado_nuevo, usuario_cambio_id, comentarios) 
                    VALUES (?, NULL, ?, ?, ?)");
                
                $comentario_seguimiento = 'Pedido creado por el cliente';
                $stmt_seguimiento->bind_param("isis", $pedido_id, $estado, $_SESSION['user_id'], $comentario_seguimiento);
                $stmt_seguimiento->execute();
                
                $conexion->commit();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Pedido creado exitosamente',
                    'numero_pedido' => $numero_pedido,
                    'pedido_id' => $pedido_id
                ]);
                
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['success' => false, 'message' => 'Error al procesar pedido: ' . $e->getMessage()]);
            }
            exit;
    }
}

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>El Buen Gusto - Inicio</title>
    <link rel="stylesheet" href="../css/inicio.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/pedido.css">
</head>
<body>
    <!-- Header con navegación -->
    <header class="header">
        <div class="navbar">
            <div class="logo-section">
                <img src="../img/isotipo_sm.png" alt="El Buen Gusto" class="logo">
                <h1>El Buen Gusto</h1>
            </div>
            
            <div class="search-section">
                <div class="search-container">
                    <input type="text" id="searchInput" placeholder="Buscar productos..." class="search-input">
                    <button class="search-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="user-section">
                <span class="welcome-text">Bienvenido, <span id="userName"><?php echo htmlspecialchars($usuario_nombre); ?></span></span>
                <button class="cart-btn" onclick="toggleCart()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                    </svg>
                    <span class="cart-count" id="cartCount">0</span>
                </button>
                <button class="profile-btn" onclick="window.location.href='perfil.html'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                        <path fill-rule="evenodd" d="M14 14s-1-1.5-6-1.5S2 14 2 14s1-4 6-4 6 4 6 4z"/>
                    </svg>
                    
                </button>
                <button class="logout-btn" onclick="logout()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
                        <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
                    </svg>
                    Salir
                </button>
            </div>
        </div>
    </header>

    <!-- Carrito desplegable -->
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h3>Mi Carrito</h3>
            <button class="close-cart" onclick="toggleCart()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </button>
        </div>
        <div class="cart-items" id="cartItems">
            <p class="empty-cart">Tu carrito está vacío</p>
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <strong>Total: $<span id="cartTotal">0</span></strong>
            </div>
            <button class="checkout-btn" onclick="abrirModalPedido()">Finalizar Pedido</button>
        </div>
    </div>

    <!-- Overlay para el carrito -->
    <div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>

    <!-- Contenido principal -->
    <main class="main-content">
        <!-- Filtros de categorías -->
        <section class="categories-section">
            <h2>Nuestras Especialidades</h2>
            <div class="categories-filter">
                <button class="category-btn active" data-category="all">Todos</button>
                <button class="category-btn" data-category="minutas">Minutas</button>
                <button class="category-btn" data-category="pastas">Pastas</button>
                <button class="category-btn" data-category="guisos">Guisos</button>
                <button class="category-btn" data-category="tartas">Tartas</button>
                <button class="category-btn" data-category="empanadas">Empanadas</button>
                <button class="category-btn" data-category="postres">Postres</button>
                <button class="category-btn" data-category="bebidas">Bebidas</button>
                <button class="category-btn" data-category="embutidos">Embutidos</button>
            </div>
        </section>

        <!-- Productos (se cargarán dinámicamente) -->
        <section class="products-section">
            <div class="products-grid" id="productsGrid">
                <!-- Los productos se cargarán aquí con JavaScript -->
            </div>
        </section>
    </main>

    <!--modal-compra-->
    <div id="modal-compra" class="body" style="display: none;">
        <div class="container" style="max-height: 90vh; overflow-y: auto;">
            <div class="header">
                <button class="close-modal" onclick="cerrar_modal()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </button>
                <h1>🍽️ Finalizar Pedido</h1>
                <p>Complete sus datos para procesar la entrega</p>
            </div>

            <div class="form-container">
                <form id="checkoutForm">
                    <!-- Información Personal -->
                    <div class="section">
                        <h2 class="section-title">
                            <span class="icon">👤</span>
                            Información Personal
                        </h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Nombre Completo <span class="required">*</span></label>
                                <input type="text" id="name" name="name" required placeholder="Ingrese su nombre completo" value="<?php echo htmlspecialchars($usuario_nombre); ?>">
                            </div>
                            <div class="form-group">
                                <label for="phone">Teléfono <span class="required">*</span></label>
                                <input type="tel" id="phone" name="phone" required placeholder="+54 11 1234-5678">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required placeholder="su.email@ejemplo.com" value="<?php echo htmlspecialchars($usuario_email); ?>">
                            <div class="info-text">Se enviará la factura a este email</div>
                        </div>
                    </div>

                    <!-- Dirección de Entrega -->
                    <div class="section">
                        <h2 class="section-title">
                            <span class="icon">📍</span>
                            Dirección de Entrega
                        </h2>
                        
                        <div class="form-group">
                            <label for="address">Dirección Completa <span class="required">*</span></label>
                            <input type="text" id="address" name="address" required placeholder="Calle, número, barrio">
                        </div>

                        <div class="form-group">
                            <label for="zona">Zona de Entrega <span class="required">*</span></label>
                            <select id="zona" name="zona" required onchange="actualizarPrecioDelivery()">
                                <option value="">Seleccione una zona</option>
                                <!-- Las zonas se cargarán dinámicamente -->
                            </select>
                            <div class="info-text">Costo de envío: $<span id="costoEnvio">0</span></div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="floor">Piso/Departamento</label>
                                <input type="text" id="floor" name="floor" placeholder="Ej: 2°B">
                            </div>
                            <div class="form-group">
                                <label for="references">Referencias</label>
                                <input type="text" id="references" name="references" placeholder="Portero, timbre, etc.">
                            </div>
                        </div>
                    </div>

                    <!-- Tipo de Entrega -->
                    <div class="section">
                        <h2 class="section-title">
                            <span class="icon">🚚</span>
                            Tipo de Entrega
                        </h2>

                        <div class="current-time">
                            <strong>Horarios de atención:</strong> 11:00 - 15:00 hs | 19:00 - 23:00 hs
                        </div>

                        <div class="delivery-options">
                            <div class="delivery-option" onclick="selectDeliveryOption('inmediato')">
                                <input type="radio" name="delivery_type" value="inmediato" id="immediate" required>
                                <div class="delivery-option-title">🕐 Entrega Inmediata</div>
                                <div class="delivery-option-desc">En el próximo turno disponible (solo en horario de atención)</div>
                            </div>

                            <div class="delivery-option" onclick="selectDeliveryOption('programado')">
                                <input type="radio" name="delivery_type" value="programado" id="scheduled">
                                <div class="delivery-option-title">📅 Programar Entrega</div>
                                <div class="delivery-option-desc">Elija fecha y hora específica</div>
                            </div>
                        </div>

                        <div class="schedule-fields" id="scheduleFields">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="delivery_date">Fecha de Entrega</label>
                                    <input type="date" id="delivery_date" name="delivery_date">
                                </div>
                                <div class="form-group">
                                    <label for="delivery_time">Hora de Entrega</label>
                                    <select id="delivery_time" name="delivery_time">
                                        <option value="">Seleccionar hora</option>
                                        <option value="11:00">11:00</option>
                                        <option value="11:30">11:30</option>
                                        <option value="12:00">12:00</option>
                                        <option value="12:30">12:30</option>
                                        <option value="13:00">13:00</option>
                                        <option value="13:30">13:30</option>
                                        <option value="14:00">14:00</option>
                                        <option value="14:30">14:30</option>
                                        <option value="19:00">19:00</option>
                                        <option value="19:30">19:30</option>
                                        <option value="20:00">20:00</option>
                                        <option value="20:30">20:30</option>
                                        <option value="21:00">21:00</option>
                                        <option value="21:30">21:30</option>
                                        <option value="22:00">22:00</option>
                                        <option value="22:30">22:30</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Método de Pago -->
                    <div class="section">
                        <h2 class="section-title">
                            <span class="icon">💳</span>
                            Método de Pago
                        </h2>
                        
                        <div class="payment-methods">
                            <div class="payment-method" onclick="selectPaymentMethod('mercadopago')">
                                <input type="radio" name="payment_method" value="mercadopago" id="mercadopago" required>
                                <div class="payment-icon"><img class="metodo-pago" src="../img/mp-logo.png" alt="MP"></div>
                                <div>Mercado Pago</div>
                            </div>

                            <div class="payment-method" onclick="selectPaymentMethod('cuenta_dni')">
                                <input type="radio" name="payment_method" value="cuenta_dni" id="cuenta_dni">
                                <div class="payment-icon"><img class="metodo-pago" src="../img/cuenta-dni-logo.png" alt="DNI"></div>
                                <div>Cuenta DNI</div>
                            </div>
                        </div>
                    </div>

                    <!-- Comentarios -->
                    <div class="section">
                        <h2 class="section-title">
                            <span class="icon">💬</span>
                            Comentarios Especiales
                        </h2>
                        
                        <div class="form-group">
                            <label for="comments">Instrucciones adicionales</label>
                            <textarea id="comments" name="comments" rows="3" placeholder="Ej: Sin sal, extra salsa, tocar timbre suavemente..."></textarea>
                        </div>
                    </div>

                    <!-- Resumen del Pedido -->
                    <div class="order-summary">
                        <h3 style="margin-bottom: 15px; color: #C81E2D;">📋 Resumen del Pedido</h3>
                        
                        <div class="summary-row">
                            <span>Subtotal productos:</span>
                            <span>$<span id="resumenSubtotal">0</span></span>
                        </div>
                        <div class="summary-row">
                            <span>Costo de envío:</span>
                            <span>$<span id="resumenDelivery">0</span></span>
                        </div>
                        <div class="summary-row summary-total">
                            <span>TOTAL A PAGAR:</span>
                            <span>$<span id="resumenTotal">0</span></span>
                        </div>
                    </div>

                    <!-- Botón de Envío -->
                    <button type="submit" class="submit-btn">
                        ✅ Confirmar Pedido
                    </button>

                    <div class="info-text" style="text-align: center; margin-top: 15px;">
                        Al confirmar el pedido, recibirá actualizaciones en tiempo real del estado de su orden.
                        <br><strong>Tiempo límite para cancelar:</strong> 5 minutos después de confirmar.
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include '../php/footer.php'; ?>

    <script src="../js/pedido.js"></script>
    <script src="../js/inicio.js"></script>
</body>
</html>