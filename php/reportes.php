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
    
    // Costos estimados (60% del subtotal como ejemplo)
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
    
    // Métricas de hoy
    $fecha_hoy = date('Y-m-d');
    
    $stmt = $conexion->prepare("SELECT 
                                COUNT(*) as pedidos_hoy,
                                SUM(total) as ventas_hoy,
                                AVG(total) as ticket_promedio
                                FROM pedidos 
                                WHERE DATE(fecha_pedido) = ? 
                                AND estado IN ('confirmado', 'en_preparacion', 'listo', 'en_camino', 'entregado') 
                                AND activo = 1");
    $stmt->bind_param("s", $fecha_hoy);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $metricas_hoy = $resultado->fetch_assoc();
    $stmt->close();
    
    // Empleados activos
    $stmt = $conexion->prepare("SELECT COUNT(*) as empleados_activos 
                                FROM usuarios 
                                WHERE rol IN ('cajero', 'cocinero', 'repartidor', 'administrador') 
                                AND activo = 1");
    $stmt->execute();
    $resultado = $stmt->get_result();
    $empleados = $resultado->fetch_assoc();
    $stmt->close();
    
    // Productos más vendidos (última semana)
    $fecha_semana = date('Y-m-d', strtotime('-7 days'));
    $stmt = $conexion->prepare("SELECT p.nombre, SUM(pi.cantidad) as total_vendido
                                FROM pedido_items pi
                                INNER JOIN productos p ON pi.producto_id = p.id
                                INNER JOIN pedidos ped ON pi.pedido_id = ped.id
                                WHERE DATE(ped.fecha_pedido) >= ?
                                AND ped.estado = 'entregado'
                                GROUP BY p.id
                                ORDER BY total_vendido DESC
                                LIMIT 5");
    $stmt->bind_param("s", $fecha_semana);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $productos_top = [];
    while($fila = $resultado->fetch_assoc()) {
        $productos_top[] = $fila;
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'metricas' => [
            'pedidos_hoy' => intval($metricas_hoy['pedidos_hoy']),
            'ventas_hoy' => floatval($metricas_hoy['ventas_hoy']),
            'ticket_promedio' => floatval($metricas_hoy['ticket_promedio']),
            'empleados_activos' => intval($empleados['empleados_activos']),
            'productos_top' => $productos_top
        ]
    ]);
}

cerrarConexion($conexion);
?>