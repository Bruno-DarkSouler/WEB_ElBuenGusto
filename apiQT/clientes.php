<?php
header('Content-Type: application/json; charset=utf-8');
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
                u.id, 
                u.nombre, 
                u.apellido, 
                u.email, 
                u.telefono,
                u.activo,
                COALESCE(
                    (SELECT dc.direccion 
                     FROM direcciones_cliente dc 
                     WHERE dc.usuario_id = u.id AND dc.es_favorita = 1 
                     LIMIT 1),
                    (SELECT dc.direccion 
                     FROM direcciones_cliente dc 
                     WHERE dc.usuario_id = u.id 
                     LIMIT 1),
                    'Sin dirección'
                ) as direccion
              FROM usuarios u
              WHERE u.rol = 'cliente' AND u.activo = 1
              ORDER BY u.nombre, u.apellido";
    
    $resultado = $conexion->query($query);
    
    if (!$resultado) {
        throw new Exception('Error en la consulta: ' . $conexion->error);
    }
    
    $clientes = array();
    
    while ($row = $resultado->fetch_assoc()) {
        $clientes[] = array(
            'id' => (int)$row['id'],
            'nombre' => $row['nombre'],
            'apellido' => $row['apellido'],
            'email' => $row['email'],
            'telefono' => $row['telefono'],
            'direccion' => $row['direccion'],
            'activo' => (bool)$row['activo']
        );
    }
    
    $conexion->close();
    
    echo json_encode($clientes, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('error' => $e->getMessage()), JSON_UNESCAPED_UNICODE);
}
?>
