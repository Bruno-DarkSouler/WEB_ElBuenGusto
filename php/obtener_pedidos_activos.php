<?php
include 'conexion.php';
session_start();

header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Consulta de pedidos activos (NO entregados ni cancelados)
    $stmt = $conexion->prepare("
        SELECT p.id, p.numero_pedido, p.fecha_pedido, p.total, p.estado,
               GROUP_CONCAT(CONCAT(pi.cantidad, 'x ', pr.nombre) SEPARATOR ', ') as productos
        FROM pedidos p
        LEFT JOIN pedido_items pi ON p.id = pi.pedido_id
        LEFT JOIN productos pr ON pi.producto_id = pr.id
        WHERE p.usuario_id = ? AND p.estado NOT IN ('entregado', 'cancelado')
        GROUP BY p.id
        ORDER BY p.fecha_pedido DESC
    ");
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pedidos = [];
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = [
            'id' => $row['id'],
            'numero_pedido' => $row['numero_pedido'],
            'fecha_pedido' => $row['fecha_pedido'],
            'total' => $row['total'],
            'estado' => $row['estado'],
            'productos' => $row['productos']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'pedidos' => $pedidos
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>