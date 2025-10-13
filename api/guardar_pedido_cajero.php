<?php
// guardar_pedido_cajero.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once './config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

try {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conexion->connect_error) {
        throw new Exception('Error de conexión: ' . $conexion->connect_error);
    }
    
    $conexion->set_charset('utf8mb4');
    
    // Generar número de pedido único
    $numeroPedido = 'P' . date('YmdHis') . rand(1000, 9999);
    
    // Obtener o crear usuario del cliente
    $nombre = $conexion->real_escape_string($data['cliente']['nombre']);
    $apellido = $conexion->real_escape_string($data['cliente']['apellido']);
    $email = $conexion->real_escape_string($data['cliente']['email']);
    $telefono = $conexion->real_escape_string($data['cliente']['telefono']);
    $direccion = $conexion->real_escape_string($data['cliente']['direccion']);
    
    // Buscar si el cliente existe
    $queryUsuario = "SELECT id FROM usuarios WHERE email = '$email' LIMIT 1";
    $resultUsuario = $conexion->query($queryUsuario);
    
    if ($resultUsuario->num_rows > 0) {
        $usuarioRow = $resultUsuario->fetch_assoc();
        $usuarioId = $usuarioRow['id'];
    } else {
        // Crear nuevo usuario cliente
        $contrasena = password_hash('cliente123', PASSWORD_DEFAULT);
        $queryInsertUsuario = "INSERT INTO usuarios (nombre, apellido, email, telefono, direccion, contraseña, rol, activo) 
                             VALUES ('$nombre', '$apellido', '$email', '$telefono', '$direccion', '$contrasena', 'cliente', 1)";
        
        if (!$conexion->query($queryInsertUsuario)) {
            throw new Exception('Error al crear cliente: ' . $conexion->error);
        }
        $usuarioId = $conexion->insert_id;
    }
    
    // Insertar pedido
    $tipoPedido = $conexion->real_escape_string($data['tipo_pedido']);
    $direccionEntrega = $conexion->real_escape_string($data['direccion_entrega']);
    $metodoPago = $conexion->real_escape_string($data['metodo_pago']);
    $subtotal = floatval($data['subtotal']);
    $precioDelivery = floatval($data['precio_delivery']);
    $total = floatval($data['total']);
    $cajerpoId = intval($data['cajero_id'] ?? 1);
    $comentarios = $conexion->real_escape_string($data['comentarios'] ?? '');
    
    $fechaEntrega = NULL;
    if ($tipoPedido === 'programado' && isset($data['fecha_entrega'])) {
        $fechaEntrega = $conexion->real_escape_string($data['fecha_entrega']);
    }
    
    $queryPedido = "INSERT INTO pedidos 
                   (numero_pedido, usuario_id, tipo_pedido, fecha_entrega_programada, 
                    direccion_entrega, telefono_contacto, metodo_pago, estado, 
                    subtotal, precio_delivery, total, cajero_id, comentarios_cliente, activo) 
                   VALUES 
                   ('$numeroPedido', $usuarioId, '$tipoPedido', " . ($fechaEntrega ? "'$fechaEntrega'" : "NULL") . ", 
                    '$direccionEntrega', '$telefono', '$metodoPago', 'confirmado', 
                    $subtotal, $precioDelivery, $total, $cajerpoId, '$comentarios', 1)";
    
    if (!$conexion->query($queryPedido)) {
        throw new Exception('Error al crear pedido: ' . $conexion->error);
    }
    
    $pedidoId = $conexion->insert_id;
    
    // Insertar items del pedido
    if (isset($data['items']) && is_array($data['items'])) {
        foreach ($data['items'] as $item) {
            $productoId = intval($item['id']);
            $cantidad = intval($item['cantidad']);
            $precioUnitario = floatval($item['precio']);
            $precioTotal = floatval($item['precioTotal']);
            
            $queryItem = "INSERT INTO pedido_items 
                        (pedido_id, producto_id, cantidad, precio_unitario, precio_total) 
                        VALUES 
                        ($pedidoId, $productoId, $cantidad, $precioUnitario, $precioTotal)";
            
            if (!$conexion->query($queryItem)) {
                throw new Exception('Error al agregar item: ' . $conexion->error);
            }
        }
    }
    
    $conexion->close();
    
    echo json_encode([
        'success' => true,
        'pedido_id' => $pedidoId,
        'numero_pedido' => $numeroPedido,
        'mensaje' => 'Pedido creado exitosamente'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>