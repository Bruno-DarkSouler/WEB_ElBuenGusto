<?php
include '../php/conexion.php';
session_start();

// ================== INFO DEL USUARIO ==================
if (!isset($_SESSION['user_id'])) {
    echo "Debes iniciar sesión.";
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Usuario';
$user_email = $_SESSION['user_email'] ?? '';
$telefono = $_SESSION['user_telefono'] ?? '';

// ================== CONSULTA DE USUARIO ==================
$stmt_user = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
} else {
    echo "Usuario no encontrado.";
    exit;
}

// Sobrescribe con el valor real del teléfono desde la base de datos
$telefono = $user['telefono'] ?? '';

// ================== PETICIONES AJAX ==================
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

        case 'get_direcciones':
            try {
                if (!isset($_SESSION['user_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado', 'direcciones' => []]);
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
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'direcciones' => []]);
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
                if (isset($data['es_favorita']) && $data['es_favorita']) {
                    $stmt = $conexion->prepare("UPDATE direcciones_cliente SET es_favorita = 0 WHERE usuario_id = ?");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                }
                
                $stmt = $conexion->prepare("INSERT INTO direcciones_cliente 
                    (usuario_id, alias, direccion, codigo_postal, instrucciones, es_favorita) 
                    VALUES (?, ?, ?, ?, ?, ?)");
                
                $es_favorita = isset($data['es_favorita']) ? $data['es_favorita'] : 0;
                
                $stmt->bind_param("issssi",
                    $_SESSION['user_id'],
                    $data['alias'],
                    $data['direccion'],
                    $data['codigo_postal'],
                    $data['instrucciones'],
                    $es_favorita
                );
                
                $stmt->execute();
                $id = $conexion->insert_id;
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Dirección guardada exitosamente',
                    'direccion_id' => $id
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
        case 'cancelar_pedido':
            try {
                if (!isset($_SESSION['user_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
                    exit;
                }
                
                $data = json_decode(file_get_contents('php://input'), true);
                $pedido_id = $data['pedido_id'];
                $user_id = $_SESSION['user_id'];
                
                $conexion->begin_transaction();
                
                // Obtener datos del pedido incluyendo cuándo entró en preparación
                $query = "SELECT p.*, p.fecha_pedido, p.metodo_pago, p.total, p.estado,
                        (SELECT fecha_cambio FROM seguimiento_pedidos 
                        WHERE pedido_id = p.id AND estado_nuevo = 'en_preparacion' 
                        ORDER BY fecha_cambio DESC LIMIT 1) as fecha_en_preparacion,
                        TIMESTAMPDIFF(MINUTE, 
                            COALESCE(
                                (SELECT fecha_cambio FROM seguimiento_pedidos 
                                WHERE pedido_id = p.id AND estado_nuevo = 'en_preparacion' 
                                ORDER BY fecha_cambio DESC LIMIT 1),
                                p.fecha_pedido
                            ), 
                            NOW()
                        ) as minutos_desde_preparacion
                        FROM pedidos p
                        WHERE p.id = ? AND p.usuario_id = ?";
                
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("ii", $pedido_id, $user_id);
                $stmt->execute();
                $pedido = $stmt->get_result()->fetch_assoc();
                
                if (!$pedido) {
                    throw new Exception('Pedido no encontrado');
                }
                
                // Validar según el estado
                $puede_cancelar = false;
                $motivo_rechazo = '';
                
                if ($pedido['estado'] == 'pendiente') {
                    // SIEMPRE puede cancelar si está pendiente
                    $puede_cancelar = true;
                } 
                elseif ($pedido['estado'] == 'en_preparacion') {
                    // Solo puede cancelar si pasaron menos de 7 minutos desde que entró en preparación
                    $minutos = (int)$pedido['minutos_desde_preparacion'];
                    
                    if ($minutos <= 7) {
                        $puede_cancelar = true;
                    } else {
                        $motivo_rechazo = "Han pasado {$minutos} minutos desde que entró en preparación (máximo 7 minutos)";
                    }
                } 
                else {
                    // Estados no cancelables: confirmado, listo, en_camino, entregado, cancelado
                    $motivo_rechazo = "El pedido está en estado '{$pedido['estado']}' y no se puede cancelar";
                }
                
                if (!$puede_cancelar) {
                    throw new Exception($motivo_rechazo ?: 'No se puede cancelar este pedido');
                }
                // Actualizar estado a cancelado
                $stmt = $conexion->prepare("UPDATE pedidos SET estado = 'cancelado', updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("i", $pedido_id);
                $stmt->execute();
                
                // Registrar en seguimiento
                $stmt = $conexion->prepare(
                    "INSERT INTO seguimiento_pedidos (pedido_id, estado_anterior, estado_nuevo, usuario_cambio_id, comentarios)
                    VALUES (?, ?, 'cancelado', ?, 'Pedido cancelado por el cliente dentro del tiempo permitido')"
                );
                $stmt->bind_param("isi", $pedido_id, $pedido['estado'], $user_id);
                $stmt->execute();
                
                $conexion->commit();
                
                // Mensaje según método de pago
                $mensaje_reembolso = '';
                if ($pedido['metodo_pago'] === 'digital') {
                    $mensaje_reembolso = ' El reembolso de $' . number_format($pedido['total'], 0, ',', '.') . ' será procesado en 24-48 horas hábiles.';
                } elseif ($pedido['metodo_pago'] === 'efectivo') {
                    $mensaje_reembolso = ' No se realizó ningún cargo ya que el pago era en efectivo.';
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Pedido cancelado exitosamente.' . $mensaje_reembolso,
                    'monto_reembolso' => ($pedido['metodo_pago'] === 'digital') ? $pedido['total'] : 0,
                    'metodo_pago' => $pedido['metodo_pago']
                ]);
                
            } catch (Exception $e) {
                if (isset($conexion)) {
                    $conexion->rollback();
                }
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
case 'procesar_pedido':
    // VALIDAR QUE EL USUARIO ESTÉ LOGUEADO
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false, 
            'message' => 'Debe iniciar sesión para realizar un pedido'
        ]);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }

    // VALIDAR DATOS REQUERIDOS
    $camposRequeridos = ['nombre', 'telefono', 'email', 'direccion', 'zona_id', 'tipo_pedido', 'metodo_pago', 'productos'];
    foreach ($camposRequeridos as $campo) {
        if (!isset($data[$campo]) || (empty($data[$campo]) && $data[$campo] !== 0)) {
            echo json_encode([
                'success' => false, 
                'message' => "El campo {$campo} es obligatorio"
            ]);
            exit;
        }
    }

    try {
        $conexion->begin_transaction();
        
        $numero_pedido = 'PED' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $estado = ($data['tipo_pedido'] === 'programado') ? 'pendiente' : 'en_preparacion';
        $fecha_entrega = ($data['tipo_pedido'] === 'programado') ? $data['fecha_entrega'] . ' ' . $data['hora_entrega'] : null;
        // Validar método de pago
        $metodo_pago = $data['metodo_pago'];

        // Si es efectivo, verificar que el usuario sea VIP
        if ($metodo_pago === 'efectivo') {
            $stmt_vip = $conexion->prepare("SELECT vip FROM usuarios WHERE id = ?");
            $stmt_vip->bind_param("i", $_SESSION['user_id']);
            $stmt_vip->execute();
            $es_vip = $stmt_vip->get_result()->fetch_assoc()['vip'];
            
            if (!$es_vip) {
                throw new Exception('El pago en efectivo solo está disponible para clientes VIP');
            }
        }

        $stmt = $conexion->prepare("INSERT INTO pedidos 
            (numero_pedido, usuario_id, tipo_pedido, fecha_entrega_programada, 
             direccion_entrega, telefono_contacto, metodo_pago, estado, 
             subtotal, precio_delivery, total, zona_delivery_id, comentarios_cliente) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "sissssssdddis",
            $numero_pedido,
            $_SESSION['user_id'],  // ⚠️ CORREGIDO: era $user_id
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

        $stmt_seguimiento = $conexion->prepare("INSERT INTO seguimiento_pedidos 
            (pedido_id, estado_anterior, estado_nuevo, usuario_cambio_id, comentarios) 
            VALUES (?, NULL, ?, ?, ?)");

        $comentario_seguimiento = 'Pedido creado por el cliente';
        $stmt_seguimiento->bind_param("isis", $pedido_id, $estado, $_SESSION['user_id'], $comentario_seguimiento);  // ⚠️ CORREGIDO
        
        if (!$stmt_seguimiento->execute()) {
            throw new Exception("Error al insertar seguimiento: " . $stmt_seguimiento->error);
        }

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

// ================== CONSULTA DE DIRECCIONES ==================
$stmt_dir = $conexion->prepare("SELECT * FROM direcciones_cliente WHERE usuario_id = ?");
$stmt_dir->bind_param("i", $user_id);
$stmt_dir->execute();
$result_dir = $stmt_dir->get_result();
$direcciones = $result_dir->fetch_all(MYSQLI_ASSOC);

// ================== ESTADÍSTICAS DEL CLIENTE ==================
// Total de pedidos
$stmt_pedidos = $conexion->prepare("SELECT COUNT(*) as total_pedidos FROM pedidos WHERE usuario_id = ? AND estado != 'cancelado'");
$stmt_pedidos->bind_param("i", $user_id);
$stmt_pedidos->execute();
$result_pedidos = $stmt_pedidos->get_result();
$total_pedidos = $result_pedidos->fetch_assoc()['total_pedidos'];

// Total gastado
// Total gastado (solo pedidos entregados, excluyendo cancelados)
$stmt_gastado = $conexion->prepare("SELECT SUM(total) as total_gastado FROM pedidos WHERE usuario_id = ? AND estado = 'entregado'");$stmt_gastado->bind_param("i", $user_id);
$stmt_gastado->execute();
$result_gastado = $stmt_gastado->get_result();
$total_gastado = $result_gastado->fetch_assoc()['total_gastado'] ?? 0;

// Total de reseñas/calificaciones
$stmt_resenas = $conexion->prepare("SELECT COUNT(*) as total_resenas FROM calificaciones WHERE usuario_id = ?");
$stmt_resenas->bind_param("i", $user_id);
$stmt_resenas->execute();
$result_resenas = $stmt_resenas->get_result();
$total_resenas = $result_resenas->fetch_assoc()['total_resenas'];

// ================== CATEGORÍAS PARA COMIDA FAVORITA ==================
$stmt_categorias = $conexion->prepare("SELECT id, nombre FROM categorias WHERE activa = 1 ORDER BY nombre");
$stmt_categorias->execute();
$result_categorias = $stmt_categorias->get_result();
$categorias = $result_categorias->fetch_all(MYSQLI_ASSOC);

// ================== PEDIDOS RECIENTES ==================
$stmt_pedidos_recientes = $conexion->prepare("
    SELECT p.id, p.numero_pedido, p.fecha_pedido, p.total, p.estado,
           GROUP_CONCAT(CONCAT(pi.cantidad, 'x ', pr.nombre) SEPARATOR ', ') as productos
    FROM pedidos p
    LEFT JOIN pedido_items pi ON p.id = pi.pedido_id
    LEFT JOIN productos pr ON pi.producto_id = pr.id
    WHERE p.usuario_id = ? AND p.estado = 'entregado'
    GROUP BY p.id
    ORDER BY p.fecha_pedido DESC
    LIMIT 10
");
$stmt_pedidos_recientes->bind_param("i", $user_id);
$stmt_pedidos_recientes->execute();
$result_pedidos_recientes = $stmt_pedidos_recientes->get_result();
$pedidos_recientes = $result_pedidos_recientes->fetch_all(MYSQLI_ASSOC);


// ================== SEGUIMIENTO DE PEDIDOS==================
// ================== SEGUIMIENTO DE PEDIDOS==================
$stmt_activos = $conexion->prepare("
    SELECT p.id, p.numero_pedido, p.fecha_pedido, p.total, p.estado, p.metodo_pago,
           GROUP_CONCAT(CONCAT(pi.cantidad, 'x ', pr.nombre) SEPARATOR ', ') as productos,
           (SELECT fecha_cambio FROM seguimiento_pedidos 
            WHERE pedido_id = p.id AND estado_nuevo = 'en_preparacion' 
            ORDER BY fecha_cambio DESC LIMIT 1) as fecha_en_preparacion,
           TIMESTAMPDIFF(MINUTE, 
               COALESCE(
                   (SELECT fecha_cambio FROM seguimiento_pedidos 
                    WHERE pedido_id = p.id AND estado_nuevo = 'en_preparacion' 
                    ORDER BY fecha_cambio DESC LIMIT 1),
                   p.fecha_pedido
               ), 
               NOW()
           ) as minutos_desde_preparacion
    FROM pedidos p
    LEFT JOIN pedido_items pi ON p.id = pi.pedido_id
    LEFT JOIN productos pr ON pi.producto_id = pr.id
    WHERE p.usuario_id = ? AND p.estado NOT IN ('entregado', 'cancelado')
    GROUP BY p.id
    ORDER BY p.fecha_pedido DESC
");
$stmt_activos->bind_param("i", $user_id);
$stmt_activos->execute();
$result_activos = $stmt_activos->get_result();
$pedidos_activos = $result_activos->fetch_all(MYSQLI_ASSOC);


// Producto más pedido
$stmt_producto_fav = $conexion->prepare("
    SELECT pr.nombre, SUM(pi.cantidad) as total_pedido
    FROM pedido_items pi
    INNER JOIN productos pr ON pi.producto_id = pr.id
    INNER JOIN pedidos p ON pi.pedido_id = p.id
    WHERE p.usuario_id = ? AND p.estado = 'entregado'
    GROUP BY pi.producto_id
    ORDER BY total_pedido DESC
    LIMIT 1
");
$stmt_producto_fav->bind_param("i", $user_id);
$stmt_producto_fav->execute();
$result_producto_fav = $stmt_producto_fav->get_result();
$producto_favorito = $result_producto_fav->fetch_assoc();

// Último pedido
$stmt_ultimo = $conexion->prepare("
    SELECT fecha_pedido, total 
    FROM pedidos 
    WHERE usuario_id = ? AND estado = 'entregado'
    ORDER BY fecha_pedido DESC 
    LIMIT 1
");
$stmt_ultimo->bind_param("i", $user_id);
$stmt_ultimo->execute();
$result_ultimo = $stmt_ultimo->get_result();
$ultimo_pedido = $result_ultimo->fetch_assoc();

// Días desde el último pedido
$dias_ultima_compra = null;
if ($ultimo_pedido) {
    $fecha_ultimo = new DateTime($ultimo_pedido['fecha_pedido']);
    $hoy = new DateTime();
    $diferencia = $hoy->diff($fecha_ultimo);
    $dias_ultima_compra = $diferencia->days;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - El Buen Gusto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/perfil.js"></script>
    <link rel="stylesheet" href="../css/perfil.css">
    <link rel="stylesheet" href="../css/inicio.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/direccion.css">
    <link rel="stylesheet" href="../css/pedido.css">
    <link rel="stylesheet" href="../css/cabecera.css">
    <link rel="stylesheet" href="../css/notifications.css">
    <link href="https://fonts.googleapis.com/css2?family=Averia+Serif+Libre:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="font-averia bg-primary-cream min-h-screen">
<header class="header">
        <div class="navbar">
            <div class="logo-section">
                <img src="../img/isotipo_sm.png" alt="El Buen Gusto" class="logo">
                <h1 class="brand-name">El Buen Gusto</h1>
            </div>
            
            <div class="search-section" style="display:none;">
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
                <span class="welcome-text">Bienvenido, <span id="userName"><?php echo htmlspecialchars($user['nombre']); ?></span></span>
                <button class="cart-btn" onclick="toggleCart()" title="Carrito">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                    </svg>
                    <span class="cart-count" id="cartCount">0</span>
                </button>
                <button class="profile-btn" onclick="window.location.href='inicio.php'" title="Inicio">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4z"/>
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

<!-- Notificaciones -->
<div id="notifications" class="fixed m-[1rem] top-51 right-4 z-40 space-y-2"></div>

<!-- Contenido Principal -->
<main class="max-w-7xl mx-auto px-4 py-6 pb-20 md:pb-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Información Personal -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg">
            <div class="bg-primary-red text-white p-6 rounded-t-xl">
                <h2 class="text-2xl font-bold">👤 Mi Perfil</h2>
                <p class="text-primary-cream">Información personal y preferencias</p>
            </div>

            <div class="p-6">
                <form id="perfilForm" class="space-y-6">
                    <!-- Foto de Perfil -->
                    <div class="flex items-center gap-6">
                        <div class="relative">
                        <img id="fotoPerfilGrande" 
                            src="<?php echo !empty($user['foto_perfil']) ? htmlspecialchars($user['foto_perfil']) : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iNDAiIGN5PSI0MCIgcj0iNDAiIGZpbGw9IiNmNWViZDIiLz4KPHN2ZyB4PSIxNiIgeT0iMTYiIHdpZHRoPSI0OCIgaGVpZ2h0PSI0OCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIj4KPHBhdGggZD0iTTIwIDIxdi0yYTQgNCAwIDAgMC00LTRIOGE0IDQgMCAwIDAtNCA0djIiIHN0cm9rZT0iIzUwMzIxNCIgc3Ryb2tlLWdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KPGNpcmNsZSBjeD0iMTIiIGN5PSI3IiByPSI0IiBzdHJva2U9IiM1MDMyMTQiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+Cjwvc3ZnPgo8L3N2Zz4='; ?>" 
                            class="w-20 h-20 rounded-full border-4 border-primary-cream object-cover">
                        <input type="file" id="inputFoto" class="hidden" accept="image/*" onchange="cambiarFoto(this)">
                            <button type="button" onclick="document.getElementById('inputFoto').click()" class="absolute bottom-0 right-0 bg-primary-red text-white p-2 rounded-full hover:bg-red-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 013-3h4a3 3 0 013 3v1"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </button>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-primary-brown"><?php echo $user['nombre'] . ' ' . $user['apellido']; ?></h3>
                            <p class="text-gray-600">Cliente desde: <?php echo date('F Y', strtotime($user['fecha_registro'])); ?></p>
                            <div class="flex items-center gap-2 mt-1">
                                <?php if ($user['vip']): ?>
                                    <span class="text-primary-red font-medium">⭐ Cliente VIP</span>
                                <?php else: ?>
                                    <span class="text-primary-red font-medium">Cliente</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Campos del Formulario -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre *</label>
                            <input type="text" id="nombre" value="<?php echo $user['nombre']; ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Apellido *</label>
                            <input type="text" id="apellido" value="<?php echo $user['apellido']; ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono *</label>
                            <input type="tel" id="telefono" value="<?php echo $user['telefono']; ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                            <input type="email" id="email" value="<?php echo $user['email']; ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dirección Principal *</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <select id="direccionPrincipalSelect" name="direccionPrincipal" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                                    <?php if (!empty($direcciones)): ?>
                                        <?php foreach ($direcciones as $row): ?>
                                            <option value="<?= $row['id'] ?>" <?= $row['es_favorita'] == 1 ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($row['alias']) ?> - <?= htmlspecialchars($row['direccion']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option disabled>No tenés direcciones guardadas</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Preferencias -->
                    <div>
                        <h4 class="text-lg font-bold text-primary-brown mb-4">🎯 Preferencias</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Seleccioná tu categoría de comida favorita</label>
                                <select id="comidaFavorita" name="comidaFavorita" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                                    <option value="">Seleccionar...</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?= $categoria['id'] ?>" 
                                            <?= (isset($user['comida_favorita']) && $user['comida_favorita'] == $categoria['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($categoria['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="todo" <?= (isset($user['comida_favorita']) && $user['comida_favorita'] == 'todo') ? 'selected' : '' ?>>
                                        Me gusta todo
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" id="notificaciones" checked class="mr-3 w-4 h-4 text-primary-red">
                                <span class="text-sm text-gray-700">Recibir notificaciones de ofertas y promociones</span>
                            </label>
                        </div>
                    </div>

                    <!-- Seguridad -->
                    <div>
                        <h4 class="text-lg font-bold text-primary-brown mb-4">🔒 Seguridad</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nueva Contraseña</label>
                                <input type="password" id="nuevaPassword" placeholder="Dejar vacío para no cambiar"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Contraseña</label>
                                <input type="password" id="confirmarPassword" placeholder="Confirmar nueva contraseña"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-4 pt-6">
                        <button type="button" onclick="cancelarCambios()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-3 rounded-lg transition-colors">
                            Cancelar Cambios
                        </button>
                        <button type="submit" class="flex-1 bg-primary-red hover:bg-red-600 text-white py-3 rounded-lg transition-colors">
                            💾 Guardar Cambios
                        </button>
                    </div>

                    <!-- Producto más pedido -->
                    <?php if ($producto_favorito): ?>
                    <div class="bg-yellow-50 p-3 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">❤️ Tu producto favorito:</p>
                        <p class="font-bold text-gray-800"><?php echo htmlspecialchars($producto_favorito['nombre']); ?></p>
                        <p class="text-xs text-gray-500">Lo pediste <?php echo $producto_favorito['total_pedido']; ?> veces</p>
                    </div>
                    <?php endif; ?>

                    <!-- Última compra -->
                    <?php if ($ultimo_pedido): ?>
                    <div class="bg-blue-50 p-3 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">🕐 Última compra:</p>
                        <p class="font-bold text-gray-800">
                            <?php 
                                $fecha = new DateTime($ultimo_pedido['fecha_pedido']);
                                echo $fecha->format('d/m/Y H:i'); 
                            ?>
                        </p>
                        <p class="text-xs text-gray-500">
                            $<?php echo number_format($ultimo_pedido['total'], 0, ',', '.'); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- Cantidad de direcciones guardadas -->
                    <div class="bg-green-50 p-3 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">📍 Direcciones guardadas:</p>
                        <p class="font-bold text-gray-800">
                            <?php echo count($direcciones); ?> dirección<?php echo count($direcciones) != 1 ? 'es' : ''; ?>
                        </p>
                        <?php if (count($direcciones) == 0): ?>
                            <p class="text-xs text-orange-600 mt-1">¡Agregá tu primera dirección!</p>
                        <?php elseif (count($direcciones) >= 3): ?>
                            <p class="text-xs text-green-600 mt-1">¡Bien organizado! ✅</p>
                        <?php else: ?>
                            <p class="text-xs text-blue-600 mt-1">Podés agregar más si querés</p>
                        <?php endif; ?>
                    </div>

                    <!-- Reseñas del cliente -->
                    <div class="bg-purple-50 p-3 rounded-lg cursor-pointer hover:bg-purple-100 transition-colors" onclick="verMisResenas()">
                        <p class="text-sm text-gray-600 mb-1">⭐ Mis Reseñas:</p>
                        <p class="font-bold text-gray-800">
                            <?php echo $total_resenas; ?> reseña<?php echo $total_resenas != 1 ? 's' : ''; ?> realizadas
                        </p>
                        <?php if ($total_resenas == 0): ?>
                            <p class="text-xs text-orange-600 mt-1">Aún no has dejado reseñas 📝</p>
                        <?php elseif ($total_resenas >= 10): ?>
                            <p class="text-xs text-green-600 mt-1">¡Eres un reviewer top! 🌟</p>
                        <?php elseif ($total_resenas >= 5): ?>
                            <p class="text-xs text-blue-600 mt-1">¡Gracias por tu feedback! 💬</p>
                        <?php else: ?>
                            <p class="text-xs text-purple-600 mt-1">Clic para ver tus reseñas 👆</p>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Panel Lateral -->
        <div class="space-y-6">
            
            <!-- Estadísticas del Cliente -->
            <div class="bg-white rounded-xl shadow-lg">
                <div class="bg-green-600 text-white p-4 rounded-t-xl">
                    <h3 class="text-lg font-bold">📊 Mis Estadísticas</h3>
                </div>
                <div class="p-4 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Pedidos Totales:</span>
                        <span class="font-bold text-green-600"><?php echo $total_pedidos; ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Gastado Total:</span>
                        <span class="font-bold text-green-600">$<?php echo number_format($total_gastado, 0, ',', '.'); ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Reseñas Dadas:</span>
                        <span class="font-bold text-green-600"><?php echo $total_resenas; ?></span>
                    </div>
                </div>
            </div>

            <!-- Direcciones -->
            <div class="bg-white rounded-xl shadow-lg mb-6">
                <div class="bg-purple-600 text-white p-4 rounded-t-xl">
                    <h3 class="text-lg font-bold">📍 Mis Direcciones</h3>
                </div>
                <!-- Contenedor de cards con scroll -->
                <div class="p-4 space-y-3 max-h-[16rem] overflow-y-auto">
                    <?php if (!empty($direcciones)): ?>
                        <?php foreach ($direcciones as $row): ?>
                            <div class="p-3 border border-gray-200 rounded-lg">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <p class="font-medium">
                                                <?= htmlspecialchars($row['alias']) == 'Casa' ? '🏠 ' : '🏢 ' ?><?= htmlspecialchars($row['alias']) ?>
                                            </p>
                                            <?php if ($row['es_favorita'] == 1): ?>
                                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Principal</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-sm text-gray-600"><?= htmlspecialchars($row['direccion']) ?></p>
                                        <p class="text-xs text-gray-500 mt-1">Código Postal: <?= htmlspecialchars($row['codigo_postal']) ?></p>
                                        <p class="text-sm text-gray-600"><?= htmlspecialchars($row['instrucciones']) ?></p>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <button onclick="editarDireccion(<?= $row['id'] ?>)" class="text-blue-500 hover:bg-blue-100 p-1 rounded">
                                            ✏️
                                        </button>
                                        <button onclick="eliminarDireccion(<?= $row['id'] ?>)" class="text-red-500 hover:bg-red-100 p-1 rounded">
                                            🗑️
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-600 text-sm">No tenés direcciones guardadas todavía.</p>
                    <?php endif; ?>
                </div>
                <!-- Botón fijo y destacado -->
                <div class="p-4">
                    <button onclick="agregarDireccion()" class="w-full bg-purple-600 text-white font-medium py-3 rounded-xl hover:bg-purple-700 transition-colors shadow-md">
                        + Agregar Dirección
                    </button>
                </div>
            </div>



            <!-- Historial de Pedidos Recientes -->
            <div class="bg-white rounded-xl shadow-lg">
                <div class="bg-orange-600 text-white p-4 rounded-t-xl">
                    <h3 class="text-lg font-bold">📦 Pedidos entregados</h3>
                </div>
                <div class="p-4 space-y-3 max-h-[20rem] overflow-y-auto">
                    <?php if (!empty($pedidos_recientes)): ?>
                        <?php foreach ($pedidos_recientes as $pedido): ?>
                            <div class="p-3 border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="font-medium text-primary-brown">Pedido #<?= htmlspecialchars($pedido['numero_pedido']) ?></p>
                                        <p class="text-sm text-gray-600">
                                            <?php 
                                                $fecha = new DateTime($pedido['fecha_pedido']);
                                                echo $fecha->format('d/m/Y H:i');
                                            ?>
                                        </p>
                                    </div>
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                        Entregado
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-700 mb-2 line-clamp-2" title="<?= htmlspecialchars($pedido['productos']) ?>">
                                    <?= htmlspecialchars($pedido['productos']) ?>
                                </p>
                                
                                <div class="flex justify-between items-center mt-2 pt-2 border-t border-gray-100">
                                    <span class="font-bold text-primary-red">
                                        $<?= number_format($pedido['total'], 0, ',', '.') ?>
                                    </span>
                                    <button onclick="repetirPedido(<?= $pedido['id'] ?>)" 
                                            class="text-primary-red hover:bg-red-100 px-3 py-1 rounded text-xs font-medium transition-colors">
                                        🔄 Repetir
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <p class="text-gray-500">No tenés pedidos entregados todavía</p>
                            <p class="text-sm text-gray-400 mt-2">¡Hacé tu primer pedido!</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (count($pedidos_recientes) > 0): ?>
                <div class="p-4 border-t border-gray-200">
                    <button onclick="verTodosLosPedidos()" 
                            class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg transition-colors font-medium">
                        Ver Todos los Pedidos
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <!-- seccion pedidos activos -->
            <div class="bg-white rounded-xl shadow-lg">
                <div class="bg-blue-600 text-white p-4 rounded-t-xl flex justify-between items-center">
                    <h3 class="text-lg font-bold">🚚 Mis Pedidos Activos</h3>
                    <span id="statusIndicator" class="text-xs opacity-70">●</span>
                </div>
                
                <div id="pedidosActivosContainer" class="p-4 space-y-3 max-h-[20rem] overflow-y-auto">
                    <?php if (!empty($pedidos_activos)): ?>
                        <?php foreach ($pedidos_activos as $pedido): ?>
                            <?php
                            // Calcular si puede cancelar
                            $puede_cancelar = false;
                            $minutos_restantes = 0;
                            
                            // Si está pendiente, siempre puede cancelar
                            if ($pedido['estado'] == 'pendiente') {
                                $puede_cancelar = true;
                                $minutos_restantes = 7; // Mostrar 7 minutos disponibles
                            } 
                            // Si está en_preparacion, verificar los 7 minutos
                            elseif ($pedido['estado'] == 'en_preparacion') {
                                $minutos_desde_prep = (int)$pedido['minutos_desde_preparacion'];
                                
                                if ($minutos_desde_prep <= 7) {
                                    $puede_cancelar = true;
                                    $minutos_restantes = 7 - $minutos_desde_prep;
                                }
                            }
                                
                                // Determinar color y emoji según estado
                                $estados = [
                                    'pendiente' => ['color' => 'orange', 'emoji' => '⏳', 'texto' => 'Pendiente'],
                                    'confirmado' => ['color' => 'green', 'emoji' => '✅', 'texto' => 'Confirmado'],
                                    'en_preparacion' => ['color' => 'blue', 'emoji' => '👨‍🍳', 'texto' => 'En Preparación'],
                                    'listo' => ['color' => 'green', 'emoji' => '🍽️', 'texto' => 'Listo'],
                                    'en_camino' => ['color' => 'orange', 'emoji' => '🚚', 'texto' => 'En Camino']
                                ];
                                $info = $estados[$pedido['estado']] ?? ['color' => 'gray', 'emoji' => '❓', 'texto' => $pedido['estado']];
                            ?>
                            
                            <div class="p-4 border-2 border-<?= $info['color'] ?>-200 rounded-lg hover:shadow-md transition-shadow bg-<?= $info['color'] ?>-50">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="font-medium text-primary-brown">Pedido #<?= htmlspecialchars($pedido['numero_pedido']) ?></p>
                                        <p class="text-sm text-gray-600">
                                            <?php 
                                                $fecha = new DateTime($pedido['fecha_pedido']);
                                                echo $fecha->format('d/m/Y H:i');
                                            ?>
                                        </p>
                                    </div>
                                    <span class="text-xs bg-<?= $info['color'] ?>-100 text-<?= $info['color'] ?>-800 px-3 py-1 rounded-full font-medium">
                                        <?= $info['emoji'] ?> <?= $info['texto'] ?>
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-700 mb-2 line-clamp-2" title="<?= htmlspecialchars($pedido['productos']) ?>">
                                    <?= htmlspecialchars($pedido['productos']) ?>
                                </p>
                                
                                <div class="flex justify-between items-center mt-3 pt-2 border-t border-gray-200">
                                    <span class="font-bold text-primary-red text-lg">
                                        $<?= number_format($pedido['total'], 0, ',', '.') ?>
                                    </span>
                                    
                                    <?php if ($pedido['estado'] == 'en_camino'): ?>
                                        <span class="text-xs text-orange-600 font-medium animate-pulse">
                                            🔥 ¡Llega pronto!
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Botón de cancelar -->
                                <?php if ($puede_cancelar): ?>
                                    <div class="mt-3 pt-2 border-t border-gray-200">
                                        <button 
                                            onclick="cancelarPedido(<?= $pedido['id'] ?>, '<?= htmlspecialchars($pedido['numero_pedido']) ?>', <?= $pedido['total'] ?>)"
                                            class="w-full bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            <?php if ($pedido['estado'] == 'pendiente'): ?>
                                                Cancelar Pedido (disponible)
                                            <?php else: ?>
                                                Cancelar Pedido (<?= $minutos_restantes ?> min restantes)
                                            <?php endif; ?>
                                        </button>
                                    </div>
                                <?php elseif ($pedido['estado'] == 'en_preparacion' && $minutos_desde_prep > 7): ?>
                                    <div class="mt-3 pt-2 border-t border-gray-200">
                                        <p class="text-xs text-gray-500 text-center">⏱️ Tiempo de cancelación agotado (más de 7 min en preparación)</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <div class="text-gray-400 mb-2">
                                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500">No tenés pedidos activos</p>
                            <p class="text-sm text-gray-400 mt-1">¡Hacé tu pedido ahora!</p>
                        </div>
                    <?php endif; ?>
                </div>  
            </div>
        </div>

        <div id="verTodosLosPedidos" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[85vh] flex flex-col">
                <div class="bg-primary-red text-white p-6 rounded-t-2xl flex-shrink-0">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold">📦 Todos tus Pedidos Entregados</h3>
                        <button onclick="cerrarModalPedidos()" class="text-white hover:bg-red-600 p-2 rounded-full transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-6 space-y-3 overflow-y-auto flex-grow">
                    <?php if (!empty($pedidos_recientes)): ?>
                        <?php foreach ($pedidos_recientes as $pedido): ?>
                            <div class="p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="font-medium text-primary-brown">Pedido #<?= htmlspecialchars($pedido['numero_pedido']) ?></p>
                                        <p class="text-sm text-gray-600">
                                            <?php 
                                                $fecha = new DateTime($pedido['fecha_pedido']);
                                                echo $fecha->format('d \d\e F, Y - H:i');
                                            ?>
                                        </p>
                                    </div>
                                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                        Entregado
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-700 mb-3">
                                    <?= htmlspecialchars($pedido['productos']) ?>
                                </p>
                                
                                <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                                    <span class="font-bold text-primary-red text-lg">
                                        $<?= number_format($pedido['total'], 0, ',', '.') ?>
                                    </span>
                                    <button onclick="repetirPedido(<?= $pedido['id'] ?>)" 
                                            class="text-primary-red hover:bg-red-100 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                        🔄 Repetir Pedido
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <p class="text-gray-500 text-lg">No tenés pedidos entregados</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="modalDireccion" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4">
                <div class="bg-primary-red text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold">📍 Agregar / Editar Dirección</h3>
                        <button type="button" onclick="cerrarModalDireccion()" class="text-white hover:bg-red-600 p-2 rounded-full">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <form id="formDireccion" method="POST" action="../php/direcciones.php" class="space-y-4">
                        <!-- Campo oculto para editar -->
                        <input type="hidden" name="direccion_id" id="direccion_id">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Etiqueta</label>
                            <select name="etiquetaDireccion" id="etiquetaDireccion" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                                <option value="Casa">🏠 Casa</option>
                                <option value="Trabajo">🏢 Trabajo</option>
                                <option value="Otro">📍 Otro</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dirección Completa</label>
                            <textarea name="direccionCompleta" id="direccionCompleta" rows="2" placeholder="Calle, número, piso, departamento"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Código Postal</label>
                                <input type="text" name="codigoPostal" id="codigoPostal" placeholder="1642"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Instrucciones de Entrega</label>
                            <textarea name="instrucciones" id="instrucciones" rows="2" placeholder="Timbre, piso, referencias..."
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-red focus:border-transparent"></textarea>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" onclick="cerrarModalDireccion()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg">
                                Cancelar
                            </button>
                            <button type="submit" class="flex-1 bg-primary-red hover:bg-red-600 text-white py-2 rounded-lg">
                                Guardar Dirección
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal de Mis Reseñas -->
<div id="modalMisResenas" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full mx-4 max-h-[85vh] flex flex-col">
        <div class="bg-purple-600 text-white p-6 rounded-t-2xl flex-shrink-0">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold">⭐ Mis Reseñas</h3>
                    <p class="text-sm opacity-90">Todas tus calificaciones y comentarios</p>
                </div>
                <button onclick="cerrarModalResenas()" class="text-white hover:bg-purple-700 p-2 rounded-full transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="p-6 space-y-4 overflow-y-auto flex-grow">
            <?php
            // Obtener todas las reseñas del usuario
            $stmt_resenas_detalle = $conexion->prepare("
                SELECT c.*, p.numero_pedido, p.fecha_pedido, p.total,
                       u.nombre as nombre_repartidor
                FROM calificaciones c
                INNER JOIN pedidos p ON c.pedido_id = p.id
                LEFT JOIN usuarios u ON c.repartidor_id = u.id
                WHERE c.usuario_id = ?
                ORDER BY c.fecha_calificacion DESC
            ");
            $stmt_resenas_detalle->bind_param("i", $user_id);
            $stmt_resenas_detalle->execute();
            $result_resenas_detalle = $stmt_resenas_detalle->get_result();
            
            if ($result_resenas_detalle->num_rows > 0):
                while ($resena = $result_resenas_detalle->fetch_assoc()):
                    $estrellas_comida = str_repeat('⭐', $resena['calificacion_comida']);
                    $estrellas_delivery = str_repeat('⭐', $resena['calificacion_delivery']);
            ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="font-medium text-gray-800">Pedido #<?= htmlspecialchars($resena['numero_pedido']) ?></p>
                            <p class="text-sm text-gray-500">
                                <?php 
                                    $fecha = new DateTime($resena['fecha_pedido']);
                                    echo $fecha->format('d/m/Y H:i');
                                ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full">
                                Reseñado el <?php 
                                    $fecha_cal = new DateTime($resena['fecha_calificacion']);
                                    echo $fecha_cal->format('d/m/Y');
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="bg-orange-50 p-2 rounded">
                            <p class="text-xs text-gray-600 mb-1">🍽️ Comida</p>
                            <p class="text-lg"><?= $estrellas_comida ?></p>
                            <p class="text-xs text-gray-500"><?= $resena['calificacion_comida'] ?>/5</p>
                        </div>
                        <div class="bg-blue-50 p-2 rounded">
                            <p class="text-xs text-gray-600 mb-1">🚚 Delivery</p>
                            <p class="text-lg"><?= $estrellas_delivery ?></p>
                            <p class="text-xs text-gray-500"><?= $resena['calificacion_delivery'] ?>/5</p>
                        </div>
                    </div>
                    
                    <?php if (!empty($resena['comentario'])): ?>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">💬 Tu comentario:</p>
                        <p class="text-sm text-gray-700 italic">"<?= htmlspecialchars($resena['comentario']) ?>"</p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($resena['nombre_repartidor']): ?>
                    <p class="text-xs text-gray-500 mt-2">
                        🚴 Repartidor: <?= htmlspecialchars($resena['nombre_repartidor']) ?>
                    </p>
                    <?php endif; ?>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="text-center py-12">
                    <div class="text-gray-400 mb-4">
                        <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-lg mb-2">Aún no has dejado reseñas</p>
                    <p class="text-sm text-gray-400">Cuando completes un pedido, podrás calificarlo y dejar tu opinión</p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($result_resenas_detalle->num_rows > 0): ?>
        <div class="bg-gray-50 p-4 rounded-b-2xl flex-shrink-0 text-center">
            <p class="text-sm text-gray-600">
                <strong>Promedio general:</strong> 
                <?php
                    $stmt_promedio = $conexion->prepare("
                        SELECT AVG((calificacion_comida + calificacion_delivery) / 2) as promedio
                        FROM calificaciones
                        WHERE usuario_id = ?
                    ");
                    $stmt_promedio->bind_param("i", $user_id);
                    $stmt_promedio->execute();
                    $result_promedio = $stmt_promedio->get_result();
                    $promedio = $result_promedio->fetch_assoc()['promedio'];
                    echo number_format($promedio, 1);
                ?> ⭐
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>
    </div>
</main>

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

    <div id="modal-compra" class="body" style="display: none;">
        <div class="container2" style="max-height: 90vh; overflow-y: auto;">
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
                                <input type="text" id="name" name="name" required placeholder="Ingrese su nombre completo" value="<?php echo htmlspecialchars($user_name); ?>">
                            </div>
                            <div class="form-group">
                                <label for="phone">Teléfono <span class="required">*</span></label>
                                <input type="tel" id="phone" name="phone" required placeholder="+54 11 1234-5678" value="<?php echo htmlspecialchars($telefono); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email2" name="email" required placeholder="su.email@ejemplo.com" value="<?php echo htmlspecialchars($user_email); ?>">
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

                        <?php if ($user['es_vip']): ?>
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
</body>
<script>
document.addEventListener('DOMContentLoaded', () => {

    const perfilForm = document.getElementById('perfilForm');
    const nombreInput = document.getElementById('nombre');
    const apellidoInput = document.getElementById('apellido');
    const emailInput = document.getElementById('email');
    const telefonoInput = document.getElementById('telefono');
    const direccionSelect = document.getElementById('direccionPrincipalSelect');
    const comidaFavoritaSelect = document.getElementById('comidaFavorita');
    const nuevaPasswordInput = document.getElementById('nuevaPassword');
    const confirmarPasswordInput = document.getElementById('confirmarPassword');

    // Guardar los valores iniciales para el botón Cancelar
    const initialValues = {
        nombre: nombreInput.value,
        apellido: apellidoInput.value,
        email: emailInput.value,
        telefono: telefonoInput.value,
        direccion: direccionSelect.value,
        comidaFavorita: comidaFavoritaSelect.value,
        nuevaPassword: '',
        confirmarPassword: ''
    };

    perfilForm.addEventListener('submit', function(event) {
        event.preventDefault();

        // Validar contraseñas si se ingresaron
        if (nuevaPasswordInput.value || confirmarPasswordInput.value) {
            if (nuevaPasswordInput.value !== confirmarPasswordInput.value) {
                alert('❌ Las contraseñas no coinciden');
                return;
            }
            if (nuevaPasswordInput.value.length < 6) {
                alert('❌ La contraseña debe tener al menos 6 caracteres');
                return;
            }
        }

        // Crear FormData con los nombres correctos que espera el PHP
        const formData = new URLSearchParams({
            nombre: nombreInput.value,
            apellido: apellidoInput.value,
            email: emailInput.value,
            telefono: telefonoInput.value,
            direccionPrincipal: direccionSelect.value,
            comidaFavorita: comidaFavoritaSelect.value,
            nuevaPassword: nuevaPasswordInput.value,
            confirmarPassword: confirmarPasswordInput.value
        });

        console.log('Enviando datos:', Object.fromEntries(formData)); // Debug

        // Enviar datos
        fetch('../php/procesar_perfil.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success){
                // Actualizar el nombre en el header
                document.getElementById('userName').textContent = nombreInput.value;
                alert('✅ Datos guardados exitosamente');
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Ocurrió un error al enviar los datos');
        });
    });

    // Función para cancelar cambios
    window.cancelarCambios = function() {
        nombreInput.value = initialValues.nombre;
        apellidoInput.value = initialValues.apellido;
        emailInput.value = initialValues.email;
        telefonoInput.value = initialValues.telefono;
        direccionSelect.value = initialValues.direccion;
        comidaFavoritaSelect.value = initialValues.comidaFavorita;
        nuevaPasswordInput.value = '';
        confirmarPasswordInput.value = '';
    };

});
</script>

<script>
// ============ FUNCIONES PARA DIRECCIONES ============

function agregarDireccion() {
    // Limpiar el formulario
    document.getElementById('formDireccion').reset();
    document.getElementById('direccion_id').value = '';
    
    // Mostrar el modal
    document.getElementById('modalDireccion').classList.remove('hidden');
    document.getElementById('modalDireccion').classList.add('flex');
}

function editarDireccion(id) {
    // Hacer petición AJAX para obtener los datos de la dirección
    fetch(`../php/obtener_direccion.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Llenar el formulario con los datos
                document.getElementById('direccion_id').value = data.direccion.id;
                document.getElementById('etiquetaDireccion').value = data.direccion.alias;
                document.getElementById('direccionCompleta').value = data.direccion.direccion;
                document.getElementById('codigoPostal').value = data.direccion.codigo_postal;
                document.getElementById('instrucciones').value = data.direccion.instrucciones || '';
                
                // Mostrar el modal
                document.getElementById('modalDireccion').classList.remove('hidden');
                document.getElementById('modalDireccion').classList.add('flex');
            } else {
                alert('❌ Error al cargar la dirección');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error al cargar la dirección');
        });
}

function eliminarDireccion(id) {
    if(!confirm('¿Estás seguro de que querés eliminar esta dirección?')) {
        return;
    }
    
    fetch('../php/eliminar_direccion.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `direccion_id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('✅ Dirección eliminada exitosamente');
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error al eliminar la dirección');
    });
}

function cerrarModalDireccion() {
    document.getElementById('modalDireccion').classList.add('hidden');
    document.getElementById('modalDireccion').classList.remove('flex');
    document.getElementById('formDireccion').reset();
}

// Manejar el submit del formulario de direcciones
document.getElementById('formDireccion').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('../php/direcciones.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('✅ Dirección guardada exitosamente');
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error al guardar la dirección');
    });
});
</script>

<script>
function verTodosLosPedidos() {
    document.getElementById('verTodosLosPedidos').classList.remove('hidden');
    document.getElementById('verTodosLosPedidos').classList.add('flex');
}

function cerrarModalPedidos() {
    document.getElementById('verTodosLosPedidos').classList.add('hidden');
    document.getElementById('verTodosLosPedidos').classList.remove('flex');
}

function repetirPedido(pedidoId) {
    if(confirm('¿Querés repetir este pedido? Se agregarán los productos al carrito.')) {
        // Aquí puedes implementar la lógica para repetir el pedido
        fetch(`../php/repetir_pedido.php?pedido_id=${pedidoId}`)
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('✅ Productos agregados al carrito');
                    // Recargar o actualizar el carrito
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error al repetir el pedido');
            });
    }
}
</script>

<script>
function repetirPedido(pedidoId) {
    if(confirm('¿Querés repetir este pedido? Se creará un nuevo pedido inmediato con los mismos productos.')) {
        console.log('Enviando pedido ID:', pedidoId);
        
        fetch(`../php/repetir_pedido.php?pedido_id=${pedidoId}`)
            .then(response => {
                console.log('Status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Respuesta del servidor:', text);
                
                try {
                    const data = JSON.parse(text);
                    
                    // CORRECCIÓN: verificar success === true
                    if(data.success === true) {
                        // Mostrar notificación de éxito
                        if (typeof showNotification === 'function') {
                            showNotification(`✅ Pedido #${data.numero_pedido} creado exitosamente`, 'success');
                        } else {
                            alert(`✅ Pedido #${data.numero_pedido} creado exitosamente`);
                        }
                        
                        // Recargar la página después de 1.5 segundos
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                        
                    } else {
                        // Error del servidor
                        const mensaje = data.message || 'Error desconocido';
                        if (typeof showNotification === 'function') {
                            showNotification('❌ Error: ' + mensaje, 'error');
                        } else {
                            alert('❌ Error: ' + mensaje);
                        }
                        console.error('Error del servidor:', mensaje);
                    }
                } catch (e) {
                    console.error('Error al parsear JSON:', e);
                    console.error('Respuesta cruda:', text);
                    
                    if (typeof showNotification === 'function') {
                        showNotification('❌ Error en la respuesta del servidor', 'error');
                    } else {
                        alert('❌ Error en la respuesta del servidor');
                    }
                }
            })
            .catch(error => {
                console.error('Error de red:', error);
                if (typeof showNotification === 'function') {
                    showNotification('❌ Error al repetir el pedido', 'error');
                } else {
                    alert('❌ Error al repetir el pedido');
                }
            });
    }
}
</script>



<script>
// Variables globales
let ultimoEstado = null;
let actualizacionInterval;

// Función para obtener info de estado
function obtenerInfoEstado(estado) {
    const estados = {
        'pendiente': { color: 'orange', emoji: '⏳', texto: 'Pendiente' },
        'confirmado': { color: 'green', emoji: '✅', texto: 'Confirmado' },
        'en_preparacion': { color: 'blue', emoji: '👨‍🍳', texto: 'En Preparación' },
        'listo': { color: 'green', emoji: '🍽️', texto: 'Listo' },
        'en_camino': { color: 'orange', emoji: '🚚', texto: 'En Camino' }
    };
    return estados[estado] || { color: 'gray', emoji: '❓', texto: estado };
}

// Función para formatear fecha
function formatearFecha(fecha) {
    const date = new Date(fecha);
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const año = date.getFullYear();
    const horas = String(date.getHours()).padStart(2, '0');
    const minutos = String(date.getMinutes()).padStart(2, '0');
    return `${dia}/${mes}/${año} ${horas}:${minutos}`;
}

// Función para actualizar pedidos activos (VERSIÓN CORREGIDA)
function actualizarPedidosActivos() {
    const statusIndicator = document.getElementById('statusIndicator');
    
    // Mostrar que está actualizando
    if (statusIndicator) {
        statusIndicator.style.color = '#FFA500';
    }
    
    fetch('../php/obtener_pedidos_activos.php')
        .then(response => response.json())
        .then(data => {
            // 🔍 DEBUG: Ver qué datos llegan
            console.log('📦 Datos recibidos:', data);
            if (data.pedidos && data.pedidos.length > 0) {
                console.table(data.pedidos.map(p => ({
                    id: p.id,
                    numero: p.numero_pedido,
                    estado: p.estado,
                    minutos: p.minutos_desde_preparacion,
                    fecha_prep: p.fecha_en_preparacion
                })));
            }
            
            if (data.success) {
                const container = document.getElementById('pedidosActivosContainer');
                
                if (data.pedidos && data.pedidos.length > 0) {
                    // Verificar si hubo cambios de estado
                    const estadoActual = JSON.stringify(data.pedidos.map(p => ({ 
                        id: p.id, 
                        estado: p.estado,
                        minutos: p.minutos_desde_preparacion || 0
                    })));
                    
                    if (ultimoEstado !== estadoActual) {
                        // Hubo cambio de estado
                        if (ultimoEstado !== null) {
                            // Mostrar notificación
                            if (typeof showNotification === 'function') {
                                showNotification('🔔 Tu pedido cambió de estado', 'info');
                            }
                            
                            // Efecto visual de actualización
                            container.style.opacity = '0.5';
                            setTimeout(() => {
                                container.style.opacity = '1';
                            }, 200);
                        }
                        
                        ultimoEstado = estadoActual;
                        
                        // Actualizar HTML
                        let html = '';
                        data.pedidos.forEach(pedido => {
                            const info = obtenerInfoEstado(pedido.estado);
                            
                            // ⚡ CALCULAR SI PUEDE CANCELAR (LÓGICA COMPLETA)
                            let puede_cancelar = false;
                            let minutos_restantes = 0;
                            let mensaje_tiempo = '';
                            
                            // CRÍTICO: Obtener minutos desde preparación
                            const minutos_desde_prep = parseInt(pedido.minutos_desde_preparacion || 0);
                            
                            // Si está pendiente, siempre puede cancelar
                            if (pedido.estado === 'pendiente') {
                                puede_cancelar = true;
                                minutos_restantes = 7;
                                mensaje_tiempo = 'Cancelar Pedido (disponible)';
                            } 
                            // Si está en_preparacion, verificar los 7 minutos
                            else if (pedido.estado === 'en_preparacion') {
                                if (minutos_desde_prep <= 7) {
                                    puede_cancelar = true;
                                    minutos_restantes = 7 - minutos_desde_prep;
                                    mensaje_tiempo = `Cancelar Pedido (${minutos_restantes} min restantes)`;
                                } else {
                                    mensaje_tiempo = `⏱️ Tiempo de cancelación agotado (más de 7 min en preparación)`;
                                }
                            }
                            
                            html += `
                                <div class="p-4 border-2 border-${info.color}-200 rounded-lg hover:shadow-md transition-shadow bg-${info.color}-50">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <p class="font-medium text-primary-brown">Pedido #${pedido.numero_pedido}</p>
                                            <p class="text-sm text-gray-600">${formatearFecha(pedido.fecha_pedido)}</p>
                                        </div>
                                        <span class="text-xs bg-${info.color}-100 text-${info.color}-800 px-3 py-1 rounded-full font-medium">
                                            ${info.emoji} ${info.texto}
                                        </span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-700 mb-2 line-clamp-2" title="${pedido.productos}">
                                        ${pedido.productos}
                                    </p>
                                    
                                    <div class="flex justify-between items-center mt-3 pt-2 border-t border-gray-200">
                                        <span class="font-bold text-primary-red text-lg">
                                            $${parseFloat(pedido.total).toLocaleString('es-AR', { minimumFractionDigits: 0 })}
                                        </span>
                                        ${pedido.estado === 'en_camino' ? '<span class="text-xs text-orange-600 font-medium animate-pulse">🔥 ¡Llega pronto!</span>' : ''}
                                    </div>
                                    
                                    ${puede_cancelar ? `
                                        <div class="mt-3 pt-2 border-t border-gray-200">
                                            <button 
                                                onclick="cancelarPedido(${pedido.id}, '${pedido.numero_pedido}', ${pedido.total})"
                                                class="w-full bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                ${mensaje_tiempo}
                                            </button>
                                        </div>
                                    ` : (pedido.estado === 'en_preparacion' ? `
                                        <div class="mt-3 pt-2 border-t border-gray-200">
                                            <p class="text-xs text-gray-500 text-center">${mensaje_tiempo}</p>
                                        </div>
                                    ` : '')}
                                </div>
                            `;
                        });
                        container.innerHTML = html;
                    }
                } else {
                    // No hay pedidos activos
                    container.innerHTML = `
                        <div class="text-center py-8">
                            <div class="text-gray-400 mb-2">
                                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500">No tenés pedidos activos</p>
                            <p class="text-sm text-gray-400 mt-1">¡Hacé tu pedido ahora!</p>
                        </div>
                    `;
                }
                
                // Indicador verde (actualización exitosa)
                if (statusIndicator) {
                    statusIndicator.style.color = '#4CAF50';
                }
            }
        })
        .catch(error => {
            console.error('Error al actualizar pedidos:', error);
            // Indicador rojo (error)
            if (statusIndicator) {
                statusIndicator.style.color = '#f44336';
            }
        });
}

// Iniciar actualización automática
document.addEventListener('DOMContentLoaded', function() {
    // Primera actualización inmediata
    setTimeout(actualizarPedidosActivos, 500);
    
    // 🔧 CORREGIDO: Actualizar cada 5 segundos (no cada 1 segundo)
    actualizacionInterval = setInterval(actualizarPedidosActivos, 5000);
});

// Limpiar interval al salir de la página
window.addEventListener('beforeunload', function() {
    if (actualizacionInterval) {
        clearInterval(actualizacionInterval);
    }
});
</script>

<script>
function cambiarFoto(input) {
    console.log('📸 Iniciando cambio de foto...');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        console.log('📁 Archivo seleccionado:', file.name, 'Tamaño:', file.size);
        
        // Validar tipo
        if (!file.type.match('image.*')) {
            alert('❌ Por favor seleccioná una imagen válida');
            return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const fotoBase64 = e.target.result;
            console.log('✅ Imagen convertida a base64, longitud:', fotoBase64.length);
            
            // Actualizar la vista previa
            document.getElementById('fotoPerfilGrande').src = fotoBase64;
            console.log('👁️ Vista previa actualizada');
            
            // Guardar en la base de datos
            console.log('💾 Enviando foto al servidor...');
            
            fetch('../php/subir_foto_perfil.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'foto_base64=' + encodeURIComponent(fotoBase64)
            })
            .then(response => {
                console.log('📡 Respuesta recibida, status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('📄 Respuesta del servidor:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('📦 JSON parseado:', data);
                    
                    if (data.success) {
                        console.log('✅ Foto guardada exitosamente');
                        if (typeof showNotification === 'function') {
                            showNotification('✅ Foto de perfil actualizada', 'success');
                        } else {
                            alert('✅ Foto de perfil actualizada');
                        }
                    } else {
                        console.error('❌ Error del servidor:', data.message);
                        alert('❌ Error: ' + data.message);
                    }
                } catch (e) {
                    console.error('❌ Error al parsear JSON:', e);
                    console.error('Respuesta cruda:', text);
                    alert('❌ Error en la respuesta del servidor');
                }
            })
            .catch(error => {
                console.error('❌ Error de red:', error);
                alert('❌ Error al subir la foto');
            });
        };
        
        reader.onerror = function(error) {
            console.error('❌ Error al leer el archivo:', error);
            alert('❌ Error al leer la imagen');
        };
        
        reader.readAsDataURL(file);
    } else {
        console.log('❌ No se seleccionó ningún archivo');
    }
}
// Funciones para modal de reseñas
function verMisResenas() {
    const modal = document.getElementById('modalMisResenas');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function cerrarModalResenas() {
    const modal = document.getElementById('modalMisResenas');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}
</script>



<!-- ANTES de cerrar </body> -->
<script src="../js/notifications.js"></script>
<script src="../js/cabecera_header.js"></script>
<script src="../js/direccion.js"></script>
<script src="../js/pedido.js"></script>
<script src="../js/perfil.js"></script>
</html>