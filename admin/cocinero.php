<?php
session_start();

// Verificar si hay sesión activa y es cocinero
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'cocinero') {
    header('Location: ../html/inicio.php');
    exit;
}

require_once '../php/conexion.php';



$usuario_nombre = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Cocinero';

// Manejar peticiones AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_pedidos_activos':
            try {
                $query = "SELECT p.*, u.nombre as cliente_nombre, u.apellido as cliente_apellido,
                          z.nombre as zona_nombre, z.precio_delivery
                          FROM pedidos p
                          LEFT JOIN usuarios u ON p.usuario_id = u.id
                          LEFT JOIN zonas_delivery z ON p.zona_delivery_id = z.id
                          WHERE p.estado IN ('en_preparacion', 'listo', 'pendiente')
                          AND p.activo = 1
                          ORDER BY 
                              CASE p.estado
                                  WHEN 'pendiente' THEN 1
                                  WHEN 'en_preparacion' THEN 2
                                  WHEN 'listo' THEN 3
                              END,
                              CASE 
                                  WHEN p.tipo_pedido = 'inmediato' THEN 1
                                  WHEN p.tipo_pedido = 'programado' THEN 2
                              END,
                              p.fecha_pedido ASC";
                
                $resultado = $conexion->query($query);
                $pedidos = [];
                
                while ($pedido = $resultado->fetch_assoc()) {
                    // Obtener items del pedido
                    $query_items = "SELECT pi.*, pr.nombre as producto_nombre, pr.tiempo_preparacion
                                   FROM pedido_items pi
                                   INNER JOIN productos pr ON pi.producto_id = pr.id
                                   WHERE pi.pedido_id = ?";
                    $stmt_items = $conexion->prepare($query_items);
                    $stmt_items->bind_param("i", $pedido['id']);
                    $stmt_items->execute();
                    $items_resultado = $stmt_items->get_result();
                    
                    $items = [];
                    $tiempo_preparacion_max = 0;
                    
                    while ($item = $items_resultado->fetch_assoc()) {
                        // Obtener condimentos del item
                        $query_condimentos = "SELECT c.nombre
                                            FROM item_condimentos ic
                                            INNER JOIN condimentos c ON ic.condimento_id = c.id
                                            WHERE ic.pedido_item_id = ?";
                        $stmt_cond = $conexion->prepare($query_condimentos);
                        $stmt_cond->bind_param("i", $item['id']);
                        $stmt_cond->execute();
                        $cond_resultado = $stmt_cond->get_result();
                        
                        $condimentos = [];
                        while ($condimento = $cond_resultado->fetch_assoc()) {
                            $condimentos[] = $condimento['nombre'];
                        }
                        
                        $item['condimentos'] = $condimentos;
                        $items[] = $item;
                        
                        if ($item['tiempo_preparacion'] > $tiempo_preparacion_max) {
                            $tiempo_preparacion_max = $item['tiempo_preparacion'];
                        }
                    }
                    
                    $pedido['items'] = $items;
                    $pedido['tiempo_estimado'] = $tiempo_preparacion_max;
                    
                    // Calcular tiempos de estados
                    $query_tiempos = "SELECT estado_nuevo, fecha_cambio 
                                     FROM seguimiento_pedidos 
                                     WHERE pedido_id = ? 
                                     ORDER BY fecha_cambio ASC";
                    $stmt_tiempos = $conexion->prepare($query_tiempos);
                    $stmt_tiempos->bind_param("i", $pedido['id']);
                    $stmt_tiempos->execute();
                    $tiempos_resultado = $stmt_tiempos->get_result();
                    
                    $tiempos = [];
                    while ($tiempo = $tiempos_resultado->fetch_assoc()) {
                        $tiempos[$tiempo['estado_nuevo']] = $tiempo['fecha_cambio'];
                    }
                    $pedido['tiempos_estados'] = $tiempos;
                    
                    $pedidos[] = $pedido;
                }
                
                echo json_encode(['success' => true, 'pedidos' => $pedidos]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'get_repartidores':
            try {
                $query = "SELECT id, nombre, apellido, estado_disponibilidad
                          FROM usuarios
                          WHERE rol = 'repartidor' AND activo = 1
                          ORDER BY nombre";
                
                $resultado = $conexion->query($query);
                $repartidores = [];
                
                while ($repartidor = $resultado->fetch_assoc()) {
                    // Verificar si tiene pedidos activos
                    $query_pedidos = "SELECT COUNT(*) as pedidos_activos
                                     FROM pedidos
                                     WHERE repartidor_id = ?
                                     AND estado IN ('en_camino')
                                     AND activo = 1";
                    $stmt = $conexion->prepare($query_pedidos);
                    $stmt->bind_param("i", $repartidor['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $datos = $result->fetch_assoc();
                    
                    $repartidor['disponible'] = ($repartidor['estado_disponibilidad'] == 1 && $datos['pedidos_activos'] == 0);
                    $repartidores[] = $repartidor;
                }
                
                echo json_encode(['success' => true, 'repartidores' => $repartidores]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'get_resenas_recientes':
            try {
                $query = "SELECT c.*, p.numero_pedido, u.nombre, u.apellido
                          FROM calificaciones c
                          INNER JOIN pedidos p ON c.pedido_id = p.id
                          INNER JOIN usuarios u ON c.usuario_id = u.id
                          ORDER BY c.fecha_calificacion DESC
                          LIMIT 5";
                
                $resultado = $conexion->query($query);
                $resenas = [];
                
                while ($resena = $resultado->fetch_assoc()) {
                    $resenas[] = $resena;
                }
                
                echo json_encode(['success' => true, 'resenas' => $resenas]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'get_estadisticas':
            try {
                $hoy = date('Y-m-d');
                
                // Pedidos completados hoy
                $query_completados = "SELECT COUNT(*) as total FROM pedidos 
                                     WHERE estado = 'entregado' 
                                     AND DATE(fecha_pedido) = ?";
                $stmt = $conexion->prepare($query_completados);
                $stmt->bind_param("s", $hoy);
                $stmt->execute();
                $completados = $stmt->get_result()->fetch_assoc()['total'];
                
                // Tiempo promedio de preparación
                $query_tiempo = "SELECT AVG(TIMESTAMPDIFF(MINUTE, t1.fecha_cambio, t2.fecha_cambio)) as tiempo_promedio
                                FROM seguimiento_pedidos t1
                                INNER JOIN seguimiento_pedidos t2 ON t1.pedido_id = t2.pedido_id
                                WHERE t1.estado_nuevo = 'en_preparacion'
                                AND t2.estado_nuevo = 'listo'
                                AND DATE(t1.fecha_cambio) = ?";
                $stmt = $conexion->prepare($query_tiempo);
                $stmt->bind_param("s", $hoy);
                $stmt->execute();
                $tiempo_promedio = $stmt->get_result()->fetch_assoc()['tiempo_promedio'];
                
                // Calificación promedio
                $query_calificacion = "SELECT AVG(c.calificacion_comida) as promedio
                                      FROM calificaciones c
                                      INNER JOIN pedidos p ON c.pedido_id = p.id
                                      WHERE DATE(p.fecha_pedido) = ?";
                $stmt = $conexion->prepare($query_calificacion);
                $stmt->bind_param("s", $hoy);
                $stmt->execute();
                $calificacion = $stmt->get_result()->fetch_assoc()['promedio'];
                
                // Eficiencia (pedidos a tiempo vs total)
                $query_total = "SELECT COUNT(*) as total FROM pedidos 
                               WHERE DATE(fecha_pedido) = ?
                               AND estado IN ('entregado', 'en_camino', 'listo', 'en_preparacion')";
                $stmt = $conexion->prepare($query_total);
                $stmt->bind_param("s", $hoy);
                $stmt->execute();
                $total = $stmt->get_result()->fetch_assoc()['total'];
                
                $eficiencia = $total > 0 ? round(($completados / $total) * 100) : 0;
                
                echo json_encode([
                    'success' => true,
                    'estadisticas' => [
                        'pedidos_completados' => $completados,
                        'tiempo_promedio' => round($tiempo_promedio ?? 0),
                        'calificacion_promedio' => round($calificacion ?? 0, 1),
                        'eficiencia' => $eficiencia
                    ]
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'cambiar_estado':
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $pedido_id = $data['pedido_id'];
                $nuevo_estado = $data['nuevo_estado'];
                
                $conexion->begin_transaction();
                
                // Obtener estado actual
                $query = "SELECT estado FROM pedidos WHERE id = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("i", $pedido_id);
                $stmt->execute();
                $estado_actual = $stmt->get_result()->fetch_assoc()['estado'];
                
                // Actualizar estado del pedido
                $query = "UPDATE pedidos SET estado = ? WHERE id = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("si", $nuevo_estado, $pedido_id);
                $stmt->execute();
                
                // Registrar en seguimiento
                $query = "INSERT INTO seguimiento_pedidos 
                         (pedido_id, estado_anterior, estado_nuevo, usuario_cambio_id, comentarios)
                         VALUES (?, ?, ?, ?, ?)";
                $stmt = $conexion->prepare($query);
                $comentario = "Estado cambiado por cocinero";
                $stmt->bind_param("issis", $pedido_id, $estado_actual, $nuevo_estado, $_SESSION['user_id'], $comentario);
                $stmt->execute();
                
                $conexion->commit();
                
                echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'asignar_repartidor':
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $pedido_id = $data['pedido_id'];
                $repartidor_id = $data['repartidor_id'];
                
                $conexion->begin_transaction();
                
                // Asignar repartidor y cambiar estado a en_camino
                $query = "UPDATE pedidos SET repartidor_id = ?, estado = 'en_camino' WHERE id = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("ii", $repartidor_id, $pedido_id);
                $stmt->execute();
                
                // Registrar en seguimiento
                $query = "INSERT INTO seguimiento_pedidos 
                         (pedido_id, estado_anterior, estado_nuevo, usuario_cambio_id, comentarios)
                         VALUES (?, 'listo', 'en_camino', ?, 'Pedido asignado a repartidor')";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("ii", $pedido_id, $_SESSION['user_id']);
                $stmt->execute();
                
                $conexion->commit();
                
                echo json_encode(['success' => true, 'message' => 'Repartidor asignado correctamente']);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
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
    <title>Panel de Cocinero - El Buen Gusto</title>
    <link rel="stylesheet" href="../css/notifications.css">
    <link rel="stylesheet" href="../css/cocinero.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="header-icon">
                    👨‍🍳
                </div>
                <div class="header-info">
                    <h1>Panel de Cocina</h1>
                    <p>Bienvenido, <?php echo htmlspecialchars($usuario_nombre); ?></p>
                </div>
            </div>
            <div class="header-stats">
                <p>Pedidos activos</p>
                <p id="pedidos-count">0</p>
            </div>
            <button class="logout-btn" onclick="logout()">
                Cerrar Sesión
            </button>
        </div>

        <div class="main-content">
            <!-- Layout principal -->
            <div class="main-layout">
                <!-- Columna de pedidos -->
                <div class="pedidos-section">
                    <h2>Cola de Pedidos</h2>
                    <div id="pedidos-container">
                        <div class="loading">Cargando pedidos...</div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="sidebar">
                    <!-- Repartidores disponibles -->
                    <div class="sidebar-card">
                        <h3>Repartidores</h3>
                        <div id="repartidores-container">
                            <div class="loading">Cargando...</div>
                        </div>
                    </div>

                    <!-- Reseñas recientes -->
                    <div class="sidebar-card">
                        <h3>Reseñas Recientes</h3>
                        <div id="resenas-container">
                            <div class="loading">Cargando...</div>
                        </div>
                    </div>

                    <!-- Estadísticas del día -->
                    <div class="sidebar-card">
                        <h3>Estadísticas del Día</h3>
                        <div id="estadisticas-container">
                            <div class="loading">Cargando...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/cocinero.js"></script>
    <script>
        // Ejecutar cron cada minuto mientras el panel esté abierto
        setInterval(function() {
            fetch('../php/cron_pedidos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.procesados > 0) {
                    console.log(`✅ Cron: ${data.procesados} pedido(s) programado(s) procesado(s)`);
                    // Recargar datos si se procesaron pedidos
                    cargarDatos();
                }
            })
            .catch(error => {
                console.error('Error en cron automático:', error);
            });
        }, 60000); // 60000 ms = 1 minuto

        // Ejecutar una vez al cargar la página
        fetch('../php/cron_pedidos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  console.log('✅ Cron inicial ejecutado');
              }
          });
    </script>
    <script>
        // Ejecutar cron cada 30 segundos
        setInterval(function() {
            fetch('../php/cron_pedidos_programados.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.procesados > 0) {
                    console.log(`✅ ${data.procesados} pedido(s) activado(s) automáticamente`);
                    cargarDatos(); // Recargar pedidos
                }
            })
            .catch(error => console.error('Error en cron:', error));
        }, 30000); // Cada 30 segundos

        // Ejecutar inmediatamente al cargar
        fetch('../php/cron_pedidos_programados.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => response.json())
          .then(data => {
              if (data.success && data.procesados > 0) {
                  console.log('✅ Cron ejecutado al inicio');
                  cargarDatos();
              }
          });
    </script>
    <script src="../js/notifications.js"></script>
</body>
</html>