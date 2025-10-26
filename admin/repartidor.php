<?php
session_start();

// Verificar si hay sesión activa y es repartidor
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'repartidor') {
    header('Location: ../html/inicio.php');
    exit;
}

require_once '../php/conexion.php';

$usuario_nombre = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Repartidor';
$repartidor_id = $_SESSION['user_id'];

// Manejar peticiones AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_pedidos_asignados':
            try {
                // Pedidos listos para recoger (estado: listo, asignados al repartidor)
                $query = "SELECT p.*, u.nombre as cliente_nombre, u.apellido as cliente_apellido,
                          z.nombre as zona_nombre, z.precio_delivery
                          FROM pedidos p
                          LEFT JOIN usuarios u ON p.usuario_id = u.id
                          LEFT JOIN zonas_delivery z ON p.zona_delivery_id = z.id
                          WHERE p.repartidor_id = ? 
                          AND p.estado IN ('listo', 'en_camino')
                          AND p.activo = 1
                          ORDER BY p.estado ASC, p.fecha_pedido ASC";
                
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("i", $repartidor_id);
                $stmt->execute();
                $resultado = $stmt->get_result();
                
                $pedidos = [];
                while ($pedido = $resultado->fetch_assoc()) {
                    // Obtener items del pedido
                    $query_items = "SELECT pi.*, pr.nombre as producto_nombre
                                   FROM pedido_items pi
                                   INNER JOIN productos pr ON pi.producto_id = pr.id
                                   WHERE pi.pedido_id = ?";
                    $stmt_items = $conexion->prepare($query_items);
                    $stmt_items->bind_param("i", $pedido['id']);
                    $stmt_items->execute();
                    $items_resultado = $stmt_items->get_result();
                    
                    $items = [];
                    while ($item = $items_resultado->fetch_assoc()) {
                        $items[] = $item['cantidad'] . 'x ' . $item['producto_nombre'];
                    }
                    
                    $pedido['items'] = $items;
                    $pedidos[] = $pedido;
                }
                
                echo json_encode(['success' => true, 'pedidos' => $pedidos]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'get_historial':
            try {
                $hoy = date('Y-m-d');
                
                $query = "SELECT p.*, u.nombre as cliente_nombre, u.apellido as cliente_apellido,
                        c.calificacion_delivery, c.comentario as comentario_delivery,
                        c.calificacion_comida
                        FROM pedidos p
                        LEFT JOIN usuarios u ON p.usuario_id = u.id
                        LEFT JOIN calificaciones c ON p.id = c.pedido_id
                        WHERE p.repartidor_id = ? 
                        AND p.estado = 'entregado'
                        AND DATE(p.fecha_pedido) = ?
                        AND p.activo = 1
                        ORDER BY p.fecha_pedido DESC";
                
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("is", $repartidor_id, $hoy);
                $stmt->execute();
                $resultado = $stmt->get_result();
                
                $historial = [];
                while ($pedido = $resultado->fetch_assoc()) {
                    // Obtener hora de entrega del seguimiento
                    $query_entrega = "SELECT fecha_cambio 
                                    FROM seguimiento_pedidos 
                                    WHERE pedido_id = ? AND estado_nuevo = 'entregado'
                                    ORDER BY fecha_cambio DESC LIMIT 1";
                    $stmt_entrega = $conexion->prepare($query_entrega);
                    $stmt_entrega->bind_param("i", $pedido['id']);
                    $stmt_entrega->execute();
                    $hora_entrega = $stmt_entrega->get_result()->fetch_assoc();
                    
                    $pedido['horaEntrega'] = null;
                    if ($hora_entrega) {
                        $fecha = new DateTime($hora_entrega['fecha_cambio']);
                        $pedido['horaEntrega'] = $fecha->format('H:i');
                    }
                    
                    // Armar objeto de reseñas solo si existe calificación
                    $pedido['tiene_resena'] = false;
                    if ($pedido['calificacion_delivery'] || $pedido['calificacion_comida']) {
                        $pedido['tiene_resena'] = true;
                        $pedido['resena'] = [
                            'delivery' => [
                                'puntuacion' => $pedido['calificacion_delivery'] ?? 0,
                                'comentario' => $pedido['comentario_delivery'] ?? ''
                            ],
                            'comida' => [
                                'puntuacion' => $pedido['calificacion_comida'] ?? 0
                            ]
                        ];
                    }
                    
                    // Limpiar campos innecesarios
                    unset($pedido['calificacion_delivery']);
                    unset($pedido['comentario_delivery']);
                    unset($pedido['calificacion_comida']);
                    
                    $historial[] = $pedido;
                }
                
                echo json_encode(['success' => true, 'historial' => $historial]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'get_estadisticas':
            try {
                $hoy = date('Y-m-d');
                
                // DEBUG: Verificar repartidor_id
                error_log("DEBUG - Repartidor ID: " . $repartidor_id);
                error_log("DEBUG - Fecha hoy: " . $hoy);

                // 1. ENTREGAS COMPLETADAS HOY - Solo del día actual
                $query_entregas = "SELECT COUNT(*) as total FROM pedidos 
                                WHERE repartidor_id = ? 
                                AND estado = 'entregado' 
                                AND DATE(fecha_pedido) = ?";
                $stmt = $conexion->prepare($query_entregas);
                $stmt->bind_param("is", $repartidor_id, $hoy);
                $stmt->execute();
                $entregas = $stmt->get_result()->fetch_assoc()['total'];
                
                // 2. GANANCIA DEL DÍA - Suma del precio_delivery de pedidos entregados HOY
                $query_ganancia = "SELECT COALESCE(SUM(precio_delivery), 0) as ganancia FROM pedidos 
                                WHERE repartidor_id = ? 
                                AND estado = 'entregado' 
                                AND DATE(fecha_pedido) = ?";
                $stmt = $conexion->prepare($query_ganancia);
                $stmt->bind_param("is", $repartidor_id, $hoy);
                $stmt->execute();
                $ganancia = $stmt->get_result()->fetch_assoc()['ganancia'];
                
                // 3. PROMEDIO DE RESEÑAS - De TODOS los pedidos del repartidor (histórico completo)
                $query_resenas = "SELECT AVG(c.calificacion_delivery) as promedio
                                FROM calificaciones c
                                INNER JOIN pedidos p ON c.pedido_id = p.id
                                WHERE p.repartidor_id = ?";
                $stmt = $conexion->prepare($query_resenas);
                $stmt->bind_param("i", $repartidor_id);
                $stmt->execute();
                $promedio = $stmt->get_result()->fetch_assoc()['promedio'] ?? 0;
                
                // 4. Pedidos pendientes (sin cambios)
                $query_pendientes = "SELECT COUNT(*) as total FROM pedidos 
                                    WHERE repartidor_id = ? 
                                    AND estado IN ('listo', 'en_camino')
                                    AND activo = 1";
                $stmt = $conexion->prepare($query_pendientes);
                $stmt->bind_param("i", $repartidor_id);
                $stmt->execute();
                $pendientes = $stmt->get_result()->fetch_assoc()['total'];
                // DEBUG: Mostrar valores antes de enviar
                error_log("DEBUG - Entregas: " . $entregas);
                error_log("DEBUG - Ganancia: " . $ganancia);
                error_log("DEBUG - Promedio: " . $promedio);
                
                echo json_encode([
                    'success' => true,
                    'estadisticas' => [
                        'entregas_hoy' => (int)$entregas,
                        'ganancia_hoy' => round($ganancia, 2),
                        'promedio_resenas' => round($promedio, 1),
                        'pendientes' => $pendientes
                    ]
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'marcar_en_camino':
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $pedido_id = $data['pedido_id'];
                
                $conexion->begin_transaction();
                
                // Verificar que el pedido esté asignado al repartidor
                $query = "SELECT estado FROM pedidos WHERE id = ? AND repartidor_id = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("ii", $pedido_id, $repartidor_id);
                $stmt->execute();
                $resultado = $stmt->get_result();
                
                if ($resultado->num_rows == 0) {
                    throw new Exception('Pedido no encontrado o no asignado');
                }
                
                $pedido = $resultado->fetch_assoc();
                
                if ($pedido['estado'] !== 'listo') {
                    throw new Exception('El pedido no está en estado listo');
                }
                
                // Actualizar estado a en_camino
                $query = "UPDATE pedidos SET estado = 'en_camino' WHERE id = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("i", $pedido_id);
                $stmt->execute();
                
                // Registrar en seguimiento
                $query = "INSERT INTO seguimiento_pedidos 
                         (pedido_id, estado_anterior, estado_nuevo, usuario_cambio_id, comentarios)
                         VALUES (?, 'listo', 'en_camino', ?, 'Repartidor salió a entregar')";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("ii", $pedido_id, $repartidor_id);
                $stmt->execute();
                
                $conexion->commit();
                
                echo json_encode(['success' => true, 'message' => 'Pedido marcado como en camino']);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'marcar_entregado':
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $pedido_id = $data['pedido_id'];
                
                $conexion->begin_transaction();
                
                // Verificar que el pedido esté en camino y asignado al repartidor
                $query = "SELECT estado FROM pedidos WHERE id = ? AND repartidor_id = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("ii", $pedido_id, $repartidor_id);
                $stmt->execute();
                $resultado = $stmt->get_result();
                
                if ($resultado->num_rows == 0) {
                    throw new Exception('Pedido no encontrado o no asignado');
                }
                
                $pedido = $resultado->fetch_assoc();
                
                if ($pedido['estado'] !== 'en_camino') {
                    throw new Exception('El pedido no está en camino');
                }
                
                // Actualizar estado a entregado
                $query = "UPDATE pedidos SET estado = 'entregado' WHERE id = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("i", $pedido_id);
                $stmt->execute();
                
                // Registrar en seguimiento
                $query = "INSERT INTO seguimiento_pedidos 
                         (pedido_id, estado_anterior, estado_nuevo, usuario_cambio_id, comentarios)
                         VALUES (?, 'en_camino', 'entregado', ?, 'Pedido entregado al cliente')";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("ii", $pedido_id, $repartidor_id);
                $stmt->execute();
                
                $conexion->commit();
                
                echo json_encode(['success' => true, 'message' => 'Pedido entregado correctamente']);
            } catch (Exception $e) {
                $conexion->rollback();
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'toggle_disponibilidad':
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $disponible = $data['disponible'] ? 1 : 0;
                
                $query = "UPDATE usuarios SET estado_disponibilidad = ? WHERE id = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("ii", $disponible, $repartidor_id);
                $stmt->execute();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Disponibilidad actualizada',
                    'disponible' => $disponible
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;
            
        case 'get_disponibilidad':
            try {
                $query = "SELECT estado_disponibilidad FROM usuarios WHERE id = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("i", $repartidor_id);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $usuario = $resultado->fetch_assoc();
                
                echo json_encode([
                    'success' => true,
                    'disponible' => $usuario['estado_disponibilidad'] == 1
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
            exit;

            case 'verificar_pedidos_entregados':
            try {
                // Buscar pedidos entregados pero no confirmados por el cliente
                $query = "SELECT p.id, p.numero_pedido, p.usuario_id
                         FROM pedidos p
                         LEFT JOIN calificaciones c ON p.id = c.pedido_id
                         WHERE p.estado = 'entregado'
                         AND p.activo = 1
                         AND c.id IS NULL
                         AND TIMESTAMPDIFF(MINUTE, 
                             (SELECT fecha_cambio FROM seguimiento_pedidos 
                              WHERE pedido_id = p.id AND estado_nuevo = 'entregado' 
                              ORDER BY fecha_cambio DESC LIMIT 1), 
                             NOW()) <= 30";
                
                $resultado = $conexion->query($query);
                $pedidos_pendientes = [];
                
                while ($row = $resultado->fetch_assoc()) {
                    $pedidos_pendientes[] = $row;
                }
                
                echo json_encode([
                    'success' => true,
                    'pedidos' => $pedidos_pendientes
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
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
    <title>Panel Repartidor - El Buen Gusto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-brown': 'rgb(80, 50, 20)',
                        'primary-cream': 'rgb(245, 235, 210)',
                        'primary-red': 'rgb(200, 30, 45)'
                    },
                    fontFamily: {
                        'averia': ['"Averia Serif Libre"', 'serif']
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="../css/notifications.css">
    <link rel="stylesheet" href="../css/repartidor.css">
    <link href="https://fonts.googleapis.com/css2?family=Averia+Serif+Libre:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="font-averia bg-primary-cream min-h-screen">
    <header class="bg-primary-red shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="text-3xl"><img class="h-[7vh]" src="../img/isotipo_sm.png" alt="isotipo"></div>
                    <div>
                        <h1 class="text-white text-2xl font-bold">El Buen Gusto</h1>
                        <p class="text-primary-cream text-sm">Panel Repartidor</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-white">Estado:</span>
                        <button id="statusToggle" onclick="toggleStatus()" 
                                class="px-4 py-2 rounded-full text-sm font-bold transition-all duration-300">
                            <span id="statusText">Cargando...</span>
                        </button>
                    </div>
                    <div class="flex items-center gap-3 text-white">
                        <div class="w-10 h-10 bg-primary-cream rounded-full flex items-center justify-center text-primary-brown font-bold">
                            <?php 
                            $iniciales = '';
                            $nombres = explode(' ', $usuario_nombre);
                            foreach ($nombres as $nombre) {
                                $iniciales .= strtoupper(substr($nombre, 0, 1));
                            }
                            echo $iniciales;
                            ?>
                        </div>
                        <div>
                            <p class="text-sm font-bold"><?php echo htmlspecialchars($usuario_nombre); ?></p>
                            <p class="text-xs text-primary-cream">Repartidor</p>
                        </div>
                    </div>
                    <button onclick="logout()" class="bg-primary-brown hover:bg-opacity-80 text-white px-4 py-2 rounded-lg transition-colors duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>
    <div id="notifications" class="fixed top-20 right-4 z-40 space-y-2"></div>
    <main class="max-w-7xl mx-auto px-4 py-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 border-4 border-primary-red">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-primary-brown text-sm font-medium">Entregas Hoy</p>
                        <p class="text-3xl font-bold text-primary-red" id="entregasHoy">0</p>
                    </div>
                    <div class="text-4xl">📦</div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 border-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-primary-brown text-sm font-medium">Ganancia Hoy</p>
                        <p class="text-3xl font-bold text-green-600" id="gananciaHoy">$0</p>
                    </div>
                    <div class="text-4xl">💰</div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 border-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-primary-brown text-sm font-medium">Promedio Reseñas</p>
                        <p class="text-3xl font-bold text-blue-600" id="promedioReseñas">0</p>
                    </div>
                    <div class="text-4xl">⭐</div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-xl shadow-lg">
                <div class="bg-primary-red text-white p-6 rounded-t-xl">
                    <h2 class="text-xl font-bold flex items-center gap-3">
                        🚚 Entregas Pendientes
                        <span id="pendingCount" class="bg-white text-primary-red px-3 py-1 rounded-full text-sm">0</span>
                    </h2>
                </div>
                <div id="pedidosPendientesContainer" class="p-6 space-y-4 max-h-96 overflow-y-auto">
                    <p class="text-gray-500 text-center py-8">No hay entregas pendientes</p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-lg">
                <div class="bg-blue-600 text-white p-6 rounded-t-xl">
                    <h2 class="text-xl font-bold flex items-center gap-3">
                        🛣️ En Camino
                        <span id="enCaminoCount" class="bg-white text-blue-600 px-3 py-1 rounded-full text-sm">0</span>
                    </h2>
                </div>
                <div id="pedidosEnCaminoContainer" class="p-6 space-y-4 max-h-96 overflow-y-auto">
                    <p class="text-gray-500 text-center py-8">No hay entregas en camino</p>
                </div>
            </div>
        </div>
        <div class="mt-8 bg-white rounded-xl shadow-lg">
            <div class="bg-green-600 text-white p-6 rounded-t-xl">
                <h2 class="text-xl font-bold flex items-center gap-3">
                    ✅ Entregas Completadas Hoy
                </h2>
            </div>
            
            <div class="p-6">
                <div id="historialContainer" class="space-y-3 max-h-64 overflow-y-auto">
                    <p class="text-gray-500 text-center py-4">No hay entregas completadas hoy</p>
                </div>
            </div>
        </div>
        <div id="reviewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 max-h-96 overflow-y-auto">
                <div class="bg-primary-red text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold">📝 Reseñas del Pedido</h3>
                        <button onclick="closeReviewModal()" class="text-white hover:bg-red-600 p-2 rounded-full">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div id="reviewContent" class="p-6">
                </div>
            </div>
        </div>
    </main>
    <script src="../js/repartidor.js"></script>
    <script src="../js/notifications.js"></script>
</body>
</html>