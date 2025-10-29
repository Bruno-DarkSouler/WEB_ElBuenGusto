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

// En reportes.php, reemplaza la función obtenerResumen() por esta:

function obtenerResumen() {
    global $conexion;
    
    $fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : date('Y-m-01');
    $fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : date('Y-m-d');
    
    // 1. Total recaudado por delivery (suma de precio_delivery de todos los pedidos entregados)
    $stmt = $conexion->prepare("SELECT COALESCE(SUM(precio_delivery), 0) as total_delivery 
                                FROM pedidos 
                                WHERE DATE(fecha_pedido) BETWEEN ? AND ? 
                                AND estado = 'entregado' 
                                AND activo = 1");
    $stmt->bind_param("ss", $fecha_desde, $fecha_hasta);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $delivery = $resultado->fetch_assoc();
    $stmt->close();
    
    // 2. Ganancia por pedidos (suma del subtotal, sin delivery)
    $stmt = $conexion->prepare("SELECT COALESCE(SUM(subtotal), 0) as ganancia_pedidos 
                                FROM pedidos 
                                WHERE DATE(fecha_pedido) BETWEEN ? AND ? 
                                AND estado = 'entregado' 
                                AND activo = 1");
    $stmt->bind_param("ss", $fecha_desde, $fecha_hasta);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $ganancia = $resultado->fetch_assoc();
    $stmt->close();
    
    // 3. Ingreso bruto (ganancia por pedidos + delivery)
    $ingresos_brutos = $ganancia['ganancia_pedidos'] + $delivery['total_delivery'];
    
    // 4. Precio promedio de productos (suma de precios / cantidad de productos)
    $stmt = $conexion->prepare("SELECT 
                                    COALESCE(AVG(precio), 0) as precio_promedio
                                FROM productos 
                                WHERE disponible = 1 AND activo = 1");
    $stmt->execute();
    $resultado = $stmt->get_result();
    $precio_prom = $resultado->fetch_assoc();
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'resumen' => [
            'ingresos_brutos' => floatval($ingresos_brutos),
            'total_delivery' => floatval($delivery['total_delivery']),
            'ganancia_pedidos' => floatval($ganancia['ganancia_pedidos']),
            'precio_promedio' => floatval($precio_prom['precio_promedio']),
            'fecha_desde' => $fecha_desde,
            'fecha_hasta' => $fecha_hasta
        ]
    ]);
}

// Reemplaza la función obtenerReporteDetallado() por esta:

function obtenerReporteDetallado() {
    global $conexion;
    
    $fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : date('Y-m-01');
    $fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : date('Y-m-d');
    
    $stmt = $conexion->prepare("SELECT 
                                DATE(fecha_pedido) as fecha, 
                                COUNT(*) as total_pedidos,
                                COALESCE(SUM(subtotal), 0) as ganancia_pedidos,
                                COALESCE(SUM(precio_delivery), 0) as delivery,
                                COALESCE(SUM(total), 0) as ingresos_brutos
                                FROM pedidos 
                                WHERE DATE(fecha_pedido) BETWEEN ? AND ? 
                                AND estado = 'entregado' 
                                AND activo = 1
                                GROUP BY DATE(fecha_pedido)
                                ORDER BY fecha DESC");
    $stmt->bind_param("ss", $fecha_desde, $fecha_hasta);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    // Obtener precio promedio (es el mismo para todos los días)
    $stmt_precio = $conexion->prepare("SELECT COALESCE(AVG(precio), 0) as precio_promedio
                                       FROM productos 
                                       WHERE disponible = 1 AND activo = 1");
    $stmt_precio->execute();
    $resultado_precio = $stmt_precio->get_result();
    $precio_prom = $resultado_precio->fetch_assoc();
    $precio_promedio = floatval($precio_prom['precio_promedio']);
    $stmt_precio->close();
    
    $detalle = [];
    while($fila = $resultado->fetch_assoc()) {
        // Calcular porcentaje que representa la ganancia respecto al ingreso bruto
        $margen = ($fila['ingresos_brutos'] > 0) ? 
                  (($fila['ganancia_pedidos'] / $fila['ingresos_brutos']) * 100) : 0;
        
        $detalle[] = [
            'fecha' => $fila['fecha'],
            'pedidos' => intval($fila['total_pedidos']),
            'ingresos' => floatval($fila['ingresos_brutos']),
            'delivery' => floatval($fila['delivery']),
            'ganancia_pedidos' => floatval($fila['ganancia_pedidos']),
            'precio_promedio' => $precio_promedio,
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