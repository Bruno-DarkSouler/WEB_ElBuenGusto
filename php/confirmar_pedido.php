<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'procesar_pedido':
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
                
$metodo_pago = ($data['metodo_pago'] === 'mercadopago' || $data['metodo_pago'] === 'cuenta_dni') ? 'digital' : 'efectivo';
                
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
            
        case 'verificar_pedidos_pendientes':
            // Este endpoint verifica si hay pedidos pendientes de confirmar
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'pedidos' => []]);
                exit;
            }
            
            try {
                $query = "SELECT id, numero_pedido FROM pedidos 
                          WHERE usuario_id = ? 
                          AND estado = 'pendiente' 
                          AND activo = 1 
                          ORDER BY created_at DESC 
                          LIMIT 5";
                
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $resultado = $stmt->get_result();
                
                $pedidos = [];
                while ($row = $resultado->fetch_assoc()) {
                    $pedidos[] = $row;
                }
                
                echo json_encode(['success' => true, 'pedidos' => $pedidos]);
            } catch (Exception $e) {
                error_log("Error verificando pedidos: " . $e->getMessage());
                echo json_encode(['success' => false, 'pedidos' => []]);
            }
            exit;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            exit;
    }
}

// Si no hay action, devolver error
echo json_encode(['success' => false, 'message' => 'No se especificó ninguna acción']);
$conexion->close();
?>