<?php
session_start();
require_once 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

// Verificar sesión de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'administrador') {
    echo json_encode([
        'success' => false, 
        'message' => 'No autorizado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $sql = "SELECT 
                p.id,
                p.numero_pedido,
                p.fecha_pedido,
                p.tipo_pedido,
                p.estado,
                p.total,
                CONCAT(u.nombre, ' ', u.apellido) as nombre_cliente,
                CONCAT(r.nombre, ' ', r.apellido) as nombre_repartidor
            FROM pedidos p
            LEFT JOIN usuarios u ON p.usuario_id = u.id
            LEFT JOIN usuarios r ON p.repartidor_id = r.id
            WHERE p.activo = 1 
            AND p.estado IN ('en_preparacion', 'listo', 'en_camino', 'entregado')
            ORDER BY 
                CASE p.estado
                    WHEN 'en_preparacion' THEN 1
                    WHEN 'listo' THEN 2
                    WHEN 'en_camino' THEN 3
                    WHEN 'entregado' THEN 4
                END,
                p.fecha_pedido DESC";
    
    $stmt = $conexion->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . $conexion->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $pedidos = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'pedidos' => $pedidos
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener pedidos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>