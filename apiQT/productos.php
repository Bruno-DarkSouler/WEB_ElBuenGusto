<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once './config.php';

try {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conexion->connect_error) {
        throw new Exception('Error de conexión: ' . $conexion->connect_error);
    }
    
    $conexion->set_charset('utf8mb4');
    
    $query = "SELECT 
                p.id, 
                p.nombre, 
                p.descripcion, 
                p.precio, 
                p.imagen, 
                p.categoria_id, 
                c.nombre as categoria_nombre,
                p.ingredientes, 
                p.tiempo_preparacion, 
                p.disponible, 
                p.valoracion_promedio, 
                p.total_valoraciones, 
                p.activo
              FROM productos p
              LEFT JOIN categorias c ON p.categoria_id = c.id
              WHERE p.activo = 1
              ORDER BY c.nombre, p.nombre";
    
    $resultado = $conexion->query($query);
    
    if (!$resultado) {
        throw new Exception('Error en la consulta: ' . $conexion->error);
    }
    
    $productos = array();
    
    while ($row = $resultado->fetch_assoc()) {
        $productos[] = array(
            'id' => (int)$row['id'],
            'nombre' => $row['nombre'],
            'descripcion' => $row['descripcion'],
            'precio' => (float)$row['precio'],
            'imagen' => $row['imagen'],
            'categoria_id' => (int)$row['categoria_id'],
            'categoria_nombre' => $row['categoria_nombre'],
            'ingredientes' => $row['ingredientes'],
            'tiempo_preparacion' => (int)$row['tiempo_preparacion'],
            'disponible' => (bool)$row['disponible'],
            'valoracion_promedio' => (float)$row['valoracion_promedio'],
            'total_valoraciones' => (int)$row['total_valoraciones'],
            'activo' => (bool)$row['activo']
        );
    }
    
    $conexion->close();
    
    echo json_encode($productos);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('error' => $e->getMessage()));
}
?>