<?php
// Este archivo se ejecuta automáticamente cada minuto desde cocinero.php
// También puede ejecutarse con cron job: * * * * * php /ruta/al/proyecto/php/cron_pedidos.php

// Configurar para que funcione tanto con llamadas web como CLI
if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json');
}

require_once 'conexion.php';

try {
    // Obtener hora actual
    $ahora = new DateTime();
    $pedidos_procesados = 0;
    
    // Buscar pedidos programados que deben pasar a en_preparación
    $query = "SELECT p.id, p.numero_pedido, p.fecha_entrega_programada, 
              MAX(pr.tiempo_preparacion) as tiempo_preparacion
              FROM pedidos p
              INNER JOIN pedido_items pi ON p.id = pi.pedido_id
              INNER JOIN productos pr ON pi.producto_id = pr.id
              WHERE p.tipo_pedido = 'programado'
              AND p.estado = 'pendiente'
              AND p.activo = 1
              AND p.fecha_entrega_programada IS NOT NULL
              GROUP BY p.id, p.numero_pedido, p.fecha_entrega_programada";
    
    $resultado = $conexion->query($query);
    
    if ($resultado) {
        while ($pedido = $resultado->fetch_assoc()) {
            $fecha_entrega = new DateTime($pedido['fecha_entrega_programada']);
            $tiempo_preparacion = intval($pedido['tiempo_preparacion'] ?? 30);
            
            // Calcular cuando debe iniciar la preparación
            // Restar el tiempo de preparación + 5 minutos de margen
            $inicio_preparacion = clone $fecha_entrega;
            $inicio_preparacion->modify("-{$tiempo_preparacion} minutes");
            $inicio_preparacion->modify("-5 minutes"); // 5 minutos de margen
            
            // Si la hora de entrega programada ya pasó, cambiar a en_preparacion
            if ($ahora >= $fecha_entrega) {
                // Iniciar transacción
                $conexion->begin_transaction();
                
                try {
                    // Cambiar estado a en_preparacion
                    $stmt = $conexion->prepare("UPDATE pedidos SET estado = 'en_preparacion', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->bind_param("i", $pedido['id']);
                    $stmt->execute();
                    
                    // Registrar en seguimiento (usuario_id = 1 para procesos automáticos del sistema)
                    $comentario = ($ahora >= $fecha_entrega) 
                        ? 'Pedido programado activado automáticamente - Hora de entrega alcanzada'
                        : 'Pedido programado activado automáticamente - Listo para preparar';
                    
                    $stmt = $conexion->prepare("INSERT INTO seguimiento_pedidos 
                        (pedido_id, estado_anterior, estado_nuevo, usuario_cambio_id, comentarios)
                        VALUES (?, 'pendiente', 'en_preparacion', 1, ?)");
                    $stmt->bind_param("is", $pedido['id'], $comentario);
                    $stmt->execute();
                    
                    $conexion->commit();
                    $pedidos_procesados++;
                    
                    error_log("✅ Pedido #{$pedido['numero_pedido']} pasó a preparación automáticamente");
                    
                } catch (Exception $e) {
                    $conexion->rollback();
                    error_log("❌ Error procesando pedido #{$pedido['numero_pedido']}: " . $e->getMessage());
                }
            }
        }
    }
    
    // Responder con JSON si es llamada web
    if (php_sapi_name() !== 'cli') {
        echo json_encode([
            'success' => true,
            'procesados' => $pedidos_procesados,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo "Cron ejecutado: {$pedidos_procesados} pedidos procesados\n";
    }
    
} catch (Exception $e) {
    error_log("❌ Error en cron_pedidos: " . $e->getMessage());
    
    if (php_sapi_name() !== 'cli') {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

$conexion->close();