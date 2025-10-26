<?php
include 'conexion.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
    exit;
}

$pedido_id = isset($_GET['pedido_id']) ? (int)$_GET['pedido_id'] : 0;
$user_id = $_SESSION['user_id'];

if ($pedido_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de pedido inválido']);
    exit;
}

try {
    $conexion->begin_transaction();
    
    // Obtener datos del pedido original
    $stmt = $conexion->prepare("
        SELECT direccion_entrega, telefono_contacto, zona_delivery_id 
        FROM pedidos 
        WHERE id = ? AND usuario_id = ?
    ");
    $stmt->bind_param("ii", $pedido_id, $user_id);
    $stmt->execute();
    $pedido_original = $stmt->get_result()->fetch_assoc();
    
    if (!$pedido_original) {
        throw new Exception('Pedido no encontrado');
    }
    
    // Obtener items del pedido
    $stmt = $conexion->prepare("
        SELECT pi.producto_id, pi.cantidad, p.precio
        FROM pedido_items pi
        INNER JOIN productos p ON pi.producto_id = p.id
        WHERE pi.pedido_id = ? AND p.disponible = 1
    ");
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if (empty($items)) {
        throw new Exception('No hay productos disponibles');
    }
    
    // Calcular totales
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['precio'] * $item['cantidad'];
    }
    
    // Obtener precio delivery
    $stmt = $conexion->prepare("SELECT precio_delivery FROM zonas_delivery WHERE id = ?");
    $stmt->bind_param("i", $pedido_original['zona_delivery_id']);
    $stmt->execute();
    $precio_delivery = $stmt->get_result()->fetch_assoc()['precio_delivery'];
    
    $total = $subtotal + $precio_delivery;
    
    // Crear nuevo pedido
    $numero_pedido = 'PED' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    $estado = 'en_preparacion';
    $tipo_pedido = 'inmediato';
    $metodo_pago = 'digital';
    
    $stmt = $conexion->prepare("
        INSERT INTO pedidos 
        (numero_pedido, usuario_id, tipo_pedido, direccion_entrega, 
         telefono_contacto, metodo_pago, estado, subtotal, precio_delivery, 
         total, zona_delivery_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        "sisssssdddi",
        $numero_pedido, $user_id, $tipo_pedido,
        $pedido_original['direccion_entrega'],
        $pedido_original['telefono_contacto'],
        $metodo_pago, $estado, $subtotal, $precio_delivery,
        $total, $pedido_original['zona_delivery_id']
    );
    $stmt->execute();
    $nuevo_pedido_id = $conexion->insert_id;
    
    // Insertar items
    $stmt = $conexion->prepare("
        INSERT INTO pedido_items (pedido_id, producto_id, cantidad, precio_unitario, precio_total)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    foreach ($items as $item) {
        $precio_total = $item['precio'] * $item['cantidad'];
        $stmt->bind_param(
            "iiidd",
            $nuevo_pedido_id,
            $item['producto_id'],
            $item['cantidad'],
            $item['precio'],
            $precio_total
        );
        $stmt->execute();
    }
    
    // Seguimiento
    $stmt = $conexion->prepare("
        INSERT INTO seguimiento_pedidos 
        (pedido_id, estado_nuevo, usuario_cambio_id, comentarios)
        VALUES (?, ?, ?, 'Pedido repetido')
    ");
    $stmt->bind_param("isi", $nuevo_pedido_id, $estado, $user_id);
    $stmt->execute();
    
    $conexion->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Pedido creado exitosamente',
        'numero_pedido' => $numero_pedido
    ]);
    
} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>