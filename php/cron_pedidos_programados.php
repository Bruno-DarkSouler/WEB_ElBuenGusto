<?php
/**
 * Script para actualizar automáticamente los pedidos programados
 */

date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once 'conexion.php';

if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json');
}

try {
    $ahora = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
    $fecha_hora_actual = $ahora->format('Y-m-d H:i:s');
    
    $conexion->begin_transaction();
    
    // Buscar pedidos programados cuya hora de entrega YA PASÓ
    $query = "SELECT id, numero_pedido, fecha_entrega_programada 
              FROM pedidos 
              WHERE tipo_pedido = 'programado'
              AND estado = 'pendiente'
              AND activo = 1
              AND fecha_entrega_programada IS NOT NULL
              AND fecha_entrega_programada <= NOW()";
    
    $resultado = $conexion->query($query);
    $pedidos_actualizados = 0;
    
    while ($pedido = $resultado->fetch_assoc()) {
        // Actualizar a en_preparacion
        $update = $conexion->prepare("UPDATE pedidos SET estado = 'en_preparacion', updated_at = NOW() WHERE id = ?");
        $update->bind_param("i", $pedido['id']);
        $update->execute();
        
        // Registrar seguimiento
        $seguimiento = $conexion->prepare("INSERT INTO seguimiento_pedidos (pedido_id, estado_anterior, estado_nuevo, usuario_cambio_id, comentarios) VALUES (?, 'pendiente', 'en_preparacion', 1, 'Pedido programado activado automáticamente')");
        $seguimiento->bind_param("i", $pedido['id']);
        $seguimiento->execute();
        
        $pedidos_actualizados++;
        error_log("✅ Pedido #{$pedido['numero_pedido']} activado automáticamente");
    }
    
    $conexion->commit();
    
    echo json_encode([
        'success' => true,
        'procesados' => $pedidos_actualizados,
        'timestamp' => $fecha_hora_actual
    ]);
    
} catch (Exception $e) {
    $conexion->rollback();
    error_log("❌ Error en cron: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conexion->close();
?>