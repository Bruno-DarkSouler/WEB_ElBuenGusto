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
    // CRÍTICO: Esta consulta DEBE calcular minutos_desde_preparacion correctamente
    $stmt = $conexion->prepare("
        SELECT p.id, p.numero_pedido, p.fecha_pedido, p.total, p.estado,
               GROUP_CONCAT(CONCAT(pi.cantidad, 'x ', pr.nombre) SEPARATOR ', ') as productos,
               
               -- Fecha cuando entró en preparación
               (SELECT fecha_cambio 
                FROM seguimiento_pedidos 
                WHERE pedido_id = p.id 
                AND estado_nuevo = 'en_preparacion' 
                ORDER BY fecha_cambio DESC 
                LIMIT 1) as fecha_en_preparacion,
               
               -- Minutos transcurridos desde que entró en preparación
               TIMESTAMPDIFF(MINUTE, 
                   COALESCE(
                       (SELECT fecha_cambio 
                        FROM seguimiento_pedidos 
                        WHERE pedido_id = p.id 
                        AND estado_nuevo = 'en_preparacion' 
                        ORDER BY fecha_cambio DESC 
                        LIMIT 1),
                       p.fecha_pedido
                   ), 
                   NOW()
               ) as minutos_desde_preparacion
               
        FROM pedidos p
        LEFT JOIN pedido_items pi ON p.id = pi.pedido_id
        LEFT JOIN productos pr ON pi.producto_id = pr.id
        WHERE p.usuario_id = ? 
        AND p.estado NOT IN ('entregado', 'cancelado')
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
            'productos' => $row['productos'],
            'fecha_en_preparacion' => $row['fecha_en_preparacion'],
            'minutos_desde_preparacion' => (int)$row['minutos_desde_preparacion'] // ⬅️ CRÍTICO: convertir a int
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