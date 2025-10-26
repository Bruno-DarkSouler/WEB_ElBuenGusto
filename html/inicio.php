<?php
session_start();

require_once '../php/conexion.php';

// Obtener información del usuario
$usuario_nombre = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Usuario';
$usuario_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
$usuario_telefono = isset($_SESSION['user_telefono']) ? $_SESSION['user_telefono'] : '';
$telefono = isset($_SESSION['user_telefono']) ? $_SESSION['user_telefono'] : '';

// ============ AGREGAR ESTA SECCIÓN ============
// Obtener datos completos del usuario (incluyendo estado VIP)
$user = ['vip' => 0]; // Valor por defecto

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt_user = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    
    if ($result_user->num_rows > 0) {
        $user = $result_user->fetch_assoc();
    }
    $stmt_user->close();
}
// ============ FIN DE LA SECCIÓN A AGREGAR ============

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

        // ====== AQUÍ VAN LOS NUEVOS CASOS ======
        case 'get_direcciones':
            try {
                if (!isset($_SESSION['user_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
                    exit;
                }
                
                $query = "SELECT * FROM direcciones_cliente 
                          WHERE usuario_id = ? 
                          ORDER BY es_favorita DESC, id DESC";
                
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $resultado = $stmt->get_result();
                
                $direcciones = [];
                while ($row = $resultado->fetch_assoc()) {
                    $direcciones[] = $row;
                }
                
                echo json_encode(['success' => true, 'direcciones' => $direcciones]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error al obtener direcciones: ' . $e->getMessage()]);
            }
            exit;

        case 'guardar_direccion':
            try {
                if (!isset($_SESSION['user_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
                    exit;
                }
                
                $data = json_decode(file_get_contents('php://input'), true);
                
                // Si se marca como favorita, quitar favorita de las demás
                if ($data['es_favorita']) {
                    $stmt = $conexion->prepare("UPDATE direcciones_cliente SET es_favorita = 0 WHERE usuario_id = ?");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                }
                
                $stmt = $conexion->prepare("INSERT INTO direcciones_cliente 
                    (usuario_id, alias, direccion, codigo_postal, instrucciones, es_favorita) 
                    VALUES (?, ?, ?, ?, ?, ?)");
                
                $stmt->bind_param("issssi",
                    $_SESSION['user_id'],
                    $data['alias'],
                    $data['direccion'],
                    $data['codigo_postal'],
                    $data['instrucciones'],
                    $data['es_favorita']
                );
                
                $stmt->execute();
                $id = $conexion->insert_id;
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Dirección guardada exitosamente',
                    'direccion_id' => $id
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error al guardar dirección: ' . $e->getMessage()]);
            }
            exit;
        // ====== FIN DE LOS NUEVOS CASOS ======
            
case 'procesar_pedido':
    // DEBUG: Log para ver si llega aquí
    error_log("=== PROCESANDO PEDIDO ===");
    error_log("Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NO EXISTE'));
    
    // Log del input recibido
    $input_raw = file_get_contents('php://input');
    error_log("Input recibido: " . $input_raw);
    // VALIDAR QUE EL USUARIO ESTÉ LOGUEADO
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false, 
            'message' => 'Debe iniciar sesión para realizar un pedido'
        ]);
        exit;
    }
    
    // Obtener datos del POST
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    // VALIDAR DATOS REQUERIDOS
    $camposRequeridos = ['nombre', 'telefono', 'email', 'direccion', 'zona_id', 'tipo_pedido', 'metodo_pago', 'productos'];
    foreach ($camposRequeridos as $campo) {
        if (!isset($data[$campo]) || empty($data[$campo])) {
            echo json_encode([
                'success' => false, 
                'message' => "El campo {$campo} es obligatorio"
            ]);
            exit;
        }
    }
    
    try {
        $conexion->begin_transaction();
        
        // Generar número de pedido único
        $numero_pedido = 'PED' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        
        // Determinar estado según tipo de pedido
        $estado = ($data['tipo_pedido'] === 'programado') ? 'pendiente' : 'en_preparacion';
        
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
        
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar pedido: " . $stmt->error);
        }
        
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
            
            if (!$stmt_items->execute()) {
                throw new Exception("Error al insertar item: " . $stmt_items->error);
            }
        }
        
        // Insertar seguimiento
        $stmt_seguimiento = $conexion->prepare("INSERT INTO seguimiento_pedidos 
            (pedido_id, estado_anterior, estado_nuevo, usuario_cambio_id, comentarios) 
            VALUES (?, NULL, ?, ?, ?)");
        
        $comentario_seguimiento = 'Pedido creado por el cliente';
        $stmt_seguimiento->bind_param("isis", $pedido_id, $estado, $_SESSION['user_id'], $comentario_seguimiento);
        
        if (!$stmt_seguimiento->execute()) {
            throw new Exception("Error al insertar seguimiento: " . $stmt_seguimiento->error);
        }
        
        $conexion->commit();
        
        // ===== ENVIAR FACTURA POR EMAIL =====
        require_once '../php/enviar_factura.php';

        $factura_data = [
            'numero_pedido' => $numero_pedido,
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'telefono' => $data['telefono'],
            'direccion' => $data['direccion'],
            'tipo_pedido' => $data['tipo_pedido'],
            'metodo_pago' => $data['metodo_pago'],
            'productos' => [],
            'subtotal' => $data['subtotal'],
            'precio_delivery' => $data['precio_delivery'],
            'total' => $data['total'],
            'comentarios' => $data['comentarios']
        ];

        // Obtener nombres de productos
        foreach ($data['productos'] as $prod) {
            $stmt_prod = $conexion->prepare("SELECT nombre FROM productos WHERE id = ?");
            $stmt_prod->bind_param("i", $prod['id']);
            $stmt_prod->execute();
            $nombre_prod = $stmt_prod->get_result()->fetch_assoc()['nombre'];
            
            $factura_data['productos'][] = [
                'nombre' => $nombre_prod,
                'cantidad' => $prod['cantidad'],
                'precio' => $prod['precio']
            ];
        }

        $resultado_email = enviarFactura($factura_data);
        error_log("Resultado envío email: " . json_encode($resultado_email));
        // ===== FIN ENVÍO FACTURA =====

        echo json_encode([
            'success' => true, 
            'message' => 'Pedido creado exitosamente',
            'numero_pedido' => $numero_pedido,
            'pedido_id' => $pedido_id
        ]);
        
    } catch (Exception $e) {
        $conexion->rollback();
        error_log("Error en procesar_pedido: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Error al procesar pedido: ' . $e->getMessage()
        ]);
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
    <title>El Buen Gusto - Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/cabecera.css">
    <link rel="stylesheet" href="../css/inicio.css">
    <link rel="stylesheet" href="../css/direccion.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/pedido.css">
    <link rel="stylesheet" href="../css/notifications.css">
</head>
<body>
    <!-- Header con navegación -->
    <header class="header">
        <div class="navbar">
            <div class="logo-section">
                <img src="../img/isotipo_sm.png" alt="El Buen Gusto" class="logo">
                <h1 class="brand-name">El Buen Gusto</h1>
            </div>
            
            <div class="search-section">
                <div class="search-container">
                    <input type="text" id="searchInput" placeholder="Buscar..." class="search-input">
                    <button class="search-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="user-section">
                <span class="welcome-text">Bienvenido, <span id="userName"><?php echo htmlspecialchars($usuario_nombre); ?></span></span>
                <button class="cart-btn" onclick="toggleCart()" title="Carrito">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                    </svg>
                    <span class="cart-count" id="cartCount">0</span>
                </button>
                <button class="profile-btn" onclick="window.location.href='perfil.php'" title="Perfil">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                        <path fill-rule="evenodd" d="M14 14s-1-1.5-6-1.5S2 14 2 14s1-4 6-4 6 4 6 4z"/>
                    </svg>
                </button>
                <button class="logout-btn" onclick="logout()" title="Cerrar sesión">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
                        <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
                    </svg>
                    <span class="logout-text">Salir</span>
                </button>
            </div>
        </div>
    </header>
    <!-- Menu inferior para celulares -->
    <div id="menu_inferior">
        <a href="../html/inicio.php">
            <div class="contenedor_seccion">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi icono_navegacion bi-house-door" viewBox="0 0 16 16">
                    <path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4z"/>
                </svg>
                <span>Inicio</span>
            </div>
        </a>
        <a href="./perfil.php">
            <div class="contenedor_seccion">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi icono_navegacion bi-person-fill" viewBox="0 0 16 16">
                    <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                </svg>
                <span>Perfil</span>
            </div>
        </a>
        
        <?php
            if(isset($_SESSION["rol"]) && $_SESSION["rol"] == "repartidor"){
            echo"
            <a href=\"../admin/repartidor.php\">
                <div class=\"contenedor_seccion\">
                    <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"25\" height=\"25\" fill=\"currentColor\" class=\"bi icono_navegacion bi-bicycle\" viewBox=\"0 0 16 16\">
                        <path d=\"M4 4.5a.5.5 0 0 1 .5-.5H6a.5.5 0 0 1 0 1v.5h4.14l.386-1.158A.5.5 0 0 1 11 4h1a.5.5 0 0 1 0 1h-.64l-.311.935.807 1.29a3 3 0 1 1-.848.53l-.508-.812-2.076 3.322A.5.5 0 0 1 8 10.5H5.959a3 3 0 1 1-1.815-3.274L5 5.856V5h-.5a.5.5 0 0 1-.5-.5m1.5 2.443-.508.814c.5.444.85 1.054.967 1.743h1.139zM8 9.057 9.598 6.5H6.402zM4.937 9.5a2 2 0 0 0-.487-.877l-.548.877zM3.603 8.092A2 2 0 1 0 4.937 10.5H3a.5.5 0 0 1-.424-.765zm7.947.53a2 2 0 1 0 .848-.53l1.026 1.643a.5.5 0 1 1-.848.53z\"/>
                    </svg>
                    <span>Repartos</span>
                </div>
            </a>
            ";
            }elseif(isset($_SESSION["rol"]) && $_SESSION["rol"] == "cajero"){
                echo "
                <a href=\"../admin/cajero.html\">
                    <div class=\"contenedor_seccion\">
                        <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"25\" height=\"25\" fill=\"currentColor\" class=\"bi icono_navegacion bi-cash-coin\" viewBox=\"0 0 16 16\">
                            <path fill-rule=\"evenodd\" d=\"M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8m5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0\"/>
                            <path d=\"M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195z\"/>
                            <path d=\"M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083q.088-.517.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1z\"/>
                            <path d=\"M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 6 6 0 0 1 3.13-1.567\"/>
                        </svg>
                        <span>Cajero</span>
                    </div>
                </a>
                ";
            }elseif(isset($_SESSION["rol"]) && $_SESSION["rol"] == "cocinero"){
                echo "
                <a href=\"../admin/cocinero.php\">
                    <div class=\"contenedor_seccion\">
                        <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"25\" height=\"25\" fill=\"currentColor\" class=\"bi icono_navegacion bi-fork-knife\" viewBox=\"0 0 16 16\">
                            <path d=\"M13 .5c0-.276-.226-.506-.498-.465-1.703.257-2.94 2.012-3 8.462a.5.5 0 0 0 .498.5c.56.01 1 .13 1 1.003v5.5a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5zM4.25 0a.25.25 0 0 1 .25.25v5.122a.128.128 0 0 0 .256.006l.233-5.14A.25.25 0 0 1 5.24 0h.522a.25.25 0 0 1 .25.238l.233 5.14a.128.128 0 0 0 .256-.006V.25A.25.25 0 0 1 6.75 0h.29a.5.5 0 0 1 .498.458l.423 5.07a1.69 1.69 0 0 1-1.059 1.711l-.053.022a.92.92 0 0 0-.58.884L6.47 15a.971.971 0 1 1-1.942 0l.202-6.855a.92.92 0 0 0-.58-.884l-.053-.022a1.69 1.69 0 0 1-1.059-1.712L3.462.458A.5.5 0 0 1 3.96 0z\"/>
                        </svg>
                        <span>Cocina</span>
                    </div>
                </a>
                ";
            }elseif(isset($_SESSION["rol"]) && $_SESSION["rol"] == "administrador"){
                echo "
                <a href=\"../admin/admin.php\">
                    <div class=\"contenedor_seccion\">
                        <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"25\" height=\"25\" fill=\"currentColor\" class=\"bi icono_navegacion bi-file-earmark-spreadsheet\" viewBox=\"0 0 16 16\">
                            <path d=\"M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V9H3V2a1 1 0 0 1 1-1h5.5zM3 12v-2h2v2zm0 1h2v2H4a1 1 0 0 1-1-1zm3 2v-2h3v2zm4 0v-2h3v1a1 1 0 0 1-1 1zm3-3h-3v-2h3zm-7 0v-2h3v2z\"/>
                        </svg>
                        <span>Admin</span>
                    </div>
                </a>
                ";
            }
        ?>
    </div>

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
                                <input type="tel" id="phone" name="phone" required placeholder="+54 11 1234-5678" value="<?php echo htmlspecialchars($telefono); ?>">
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
    
    <!-- Opciones de dirección -->
    <div class="address-options" style="margin-bottom: 20px;">
        <button type="button" class="address-option-btn active" onclick="mostrarDireccionesGuardadas()">
            📋 Mis Direcciones
        </button>
        <button type="button" class="address-option-btn" onclick="mostrarNuevaDireccion()">
            ➕ Nueva Dirección
        </button>
    </div>

    <!-- Lista de direcciones guardadas -->
    <div id="direccionesGuardadas" class="direcciones-container">
        <p class="loading-text">Cargando direcciones...</p>
    </div>

    <!-- Formulario nueva dirección (oculto por defecto) -->
    <div id="nuevaDireccionForm" style="display: none;">
        <div class="form-group">
            <label for="alias">Alias <span class="required">*</span></label>
            <select id="alias" name="alias">
                <option value="Casa">🏠 Casa</option>
                <option value="Trabajo">💼 Trabajo</option>
                <option value="Otro">📍 Otro</option>
            </select>
        </div>

        <div class="form-group">
            <label for="address">Dirección Completa <span class="required">*</span></label>
            <input type="text" id="address" name="address" required placeholder="Calle, número, barrio">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="codigo_postal">Código Postal</label>
                <input type="text" id="codigo_postal" name="codigo_postal" placeholder="Ej: 1520">
            </div>
            <div class="form-group">
                <label for="references">Referencias</label>
                <input type="text" id="references" name="references" placeholder="Portero, timbre, etc.">
            </div>
        </div>

        <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
            <input type="checkbox" id="guardar_direccion" name="guardar_direccion" style="width: auto;">
            <label for="guardar_direccion" style="margin: 0;">Guardar esta dirección para futuros pedidos</label>
        </div>

        <div class="form-group" id="favorita_group" style="display: none; margin-left: 30px;">
            <input type="checkbox" id="es_favorita" name="es_favorita" style="width: auto;">
            <label for="es_favorita" style="margin: 0;">⭐ Marcar como dirección favorita</label>
        </div>
    </div>

    <div class="form-group">
        <label for="zona">Zona de Entrega <span class="required">*</span></label>
        <select id="zona" name="zona" required onchange="actualizarPrecioDelivery()">
            <option value="">Seleccione una zona</option>
            <!-- Las zonas se cargarán dinámicamente -->
        </select>
        <div class="info-text">Costo de envío: $<span id="costoEnvio">0</span></div>
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

                        <?php if ($user['vip']): ?>
                        <div class="payment-method" onclick="selectPaymentMethod('efectivo')">
                            <input type="radio" name="payment_method" value="efectivo" id="efectivo">
                            <div class="payment-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#4CAF50" viewBox="0 0 16 16">
                                    <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/>
                                </svg>
                            </div>
                            <div class="flex items-center gap-2">
                                <span>Efectivo</span>
                                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">⭐ VIP</span>
                            </div>
                        </div>
                        <?php endif; ?>
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


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="../js/cabecera_header.js"></script>
    <script src="../js/pedido.js"></script>
    <script src="../js/inicio.js"></script>
    <script src="../js/direccion.js"></script>
    <script src="../js/notifications.js"></script>
</body>
</html>