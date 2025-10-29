<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'conexion.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : 'resumen';

switch($accion) {
    case 'resumen':
        obtenerResumen();
        break;
    case 'detallado':
        obtenerReporteDetallado();
        break;
    case 'metricas':
        obtenerMetricas();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}

function obtenerResumen() {
    global $conexion;
    
    $fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : date('Y-m-01');
    $fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : date('Y-m-d');
    
    // Ingresos totales
    $stmt = $conexion->prepare("SELECT SUM(total) as ingresos_brutos, COUNT(*) as total_pedidos 
                                FROM pedidos 
                                WHERE DATE(fecha_pedido) BETWEEN ? AND ? 
                                AND estado = 'entregado' 
                                AND activo = 1");
    $stmt->bind_param("ss", $fecha_desde, $fecha_hasta);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $ingresos = $resultado->fetch_assoc();
    $stmt->close();
    
    // Delivery
    $stmt = $conexion->prepare("SELECT SUM(precio_delivery) as total_delivery 
                                FROM pedidos 
                                WHERE DATE(fecha_pedido) BETWEEN ? AND ? 
                                AND estado = 'entregado' 
                                AND activo = 1");
    $stmt->bind_param("ss", $fecha_desde, $fecha_hasta);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $delivery = $resultado->fetch_assoc();
    $stmt->close();
    
    // Costos estimados (40% del subtotal)
    $costos_estimados = ($ingresos['ingresos_brutos'] - $delivery['total_delivery']) * 0.4;
    $ganancia_neta = $ingresos['ingresos_brutos'] - $costos_estimados;
    
    echo json_encode([
        'success' => true,
        'resumen' => [
            'ingresos_brutos' => floatval($ingresos['ingresos_brutos']),
            'total_pedidos' => intval($ingresos['total_pedidos']),
            'total_delivery' => floatval($delivery['total_delivery']),
            'costos_estimados' => floatval($costos_estimados),
            'ganancia_neta' => floatval($ganancia_neta),
            'fecha_desde' => $fecha_desde,
            'fecha_hasta' => $fecha_hasta
        ]
    ]);
}

function obtenerReporteDetallado() {
    global $conexion;
    
    $fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : date('Y-m-01');
    $fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : date('Y-m-d');
    
    $stmt = $conexion->prepare("SELECT DATE(fecha_pedido) as fecha, 
                                COUNT(*) as total_pedidos,
                                SUM(total) as ingresos,
                                SUM(precio_delivery) as delivery,
                                SUM(subtotal) as subtotal
                                FROM pedidos 
                                WHERE DATE(fecha_pedido) BETWEEN ? AND ? 
                                AND estado = 'entregado' 
                                AND activo = 1
                                GROUP BY DATE(fecha_pedido)
                                ORDER BY fecha DESC");
    $stmt->bind_param("ss", $fecha_desde, $fecha_hasta);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $detalle = [];
    while($fila = $resultado->fetch_assoc()) {
        $costos = $fila['subtotal'] * 0.4;
        $ganancia = $fila['ingresos'] - $costos;
        $margen = ($fila['ingresos'] > 0) ? ($ganancia / $fila['ingresos']) * 100 : 0;
        
        $detalle[] = [
            'fecha' => $fila['fecha'],
            'pedidos' => intval($fila['total_pedidos']),
            'ingresos' => floatval($fila['ingresos']),
            'delivery' => floatval($fila['delivery']),
            'costos' => floatval($costos),
            'ganancia' => floatval($ganancia),
            'margen' => round($margen, 2)
        ];
    }
    $stmt->close();
    
    echo json_encode(['success' => true, 'detalle' => $detalle]);
}

function obtenerMetricas() {
    global $conexion;
    
    $fecha_hoy = date('Y-m-d');
    
    // 1. PEDIDOS PROCESADOS (TODOS los pedidos, no solo de hoy)
    $stmt = $conexion->prepare("SELECT COUNT(*) as pedidos_hoy
                                FROM pedidos 
                                WHERE estado != 'cancelado'
                                AND activo = 1");
    $stmt->execute();
    $resultado = $stmt->get_result();
    $pedidos_data = $resultado->fetch_assoc();
    $stmt->close();
    
    // 2. INGRESOS HOY (suma del PRECIO TOTAL de pedidos entregados hoy)
    $stmt = $conexion->prepare("SELECT COALESCE(SUM(total), 0) as ventas_hoy
                                FROM pedidos 
                                WHERE DATE(fecha_pedido) = ? 
                                AND estado = 'entregado'
                                AND activo = 1");
    $stmt->bind_param("s", $fecha_hoy);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $ventas_data = $resultado->fetch_assoc();
    $stmt->close();
    
    // 3. GASTO PROMEDIO (ingresos del día / cantidad de pedidos del día)
    $stmt = $conexion->prepare("SELECT 
                                COUNT(*) as cantidad_pedidos,
                                COALESCE(SUM(total), 0) as total_ingresos
                                FROM pedidos 
                                WHERE DATE(fecha_pedido) = ? 
                                AND estado = 'entregado'
                                AND activo = 1");
    $stmt->bind_param("s", $fecha_hoy);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $gasto_data = $resultado->fetch_assoc();
    $stmt->close();
    
    // Calcular el promedio (evitar división por cero)
    $gasto_promedio = 0;
    if ($gasto_data['cantidad_pedidos'] > 0) {
        $gasto_promedio = $gasto_data['total_ingresos'] / $gasto_data['cantidad_pedidos'];
    }
    
    // 4. Empleados activos
    $stmt = $conexion->prepare("SELECT COUNT(*) as empleados_activos 
                                FROM usuarios 
                                WHERE rol IN ('cajero', 'cocinero', 'repartidor', 'administrador') 
                                AND activo = 1");
    $stmt->execute();
    $resultado = $stmt->get_result();
    $empleados = $resultado->fetch_assoc();
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'metricas' => [
            'pedidos_hoy' => intval($pedidos_data['pedidos_hoy']),
            'ventas_hoy' => floatval($ventas_data['ventas_hoy']),
            'gasto_promedio' => floatval($gasto_promedio),
            'empleados_activos' => intval($empleados['empleados_activos'])
        ]
    ]);
}


cerrarConexion($conexion);
?>