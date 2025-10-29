<?php
session_start();
require_once 'conexion.php';

// Headers JSON
header('Content-Type: application/json; charset=utf-8');

// Verificar sesión
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'administrador') {
    echo json_encode([
        'success' => false, 
        'message' => 'No autorizado'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($method) {
        case 'GET':
            obtenerMetodosPago();
            break;
        case 'POST':
        case 'PUT':
            actualizarMetodosPago();
            break;
        default:
            throw new Exception('Método no permitido');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

function obtenerMetodosPago() {
    global $conexion;
    
    // Obtener configuración de métodos de pago
    $stmt = $conexion->prepare("SELECT * FROM configuracion WHERE clave LIKE 'metodo_pago_%'");
    
    if (!$stmt) {
        throw new Exception('Error al preparar consulta: ' . $conexion->error);
    }
    
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $metodos = [];
    while($fila = $resultado->fetch_assoc()) {
        $metodos[$fila['clave']] = json_decode($fila['valor'], true);
    }
    
    // Si no hay métodos configurados, devolver estructura básica
    if (empty($metodos)) {
        $metodos = [
            'metodo_pago_efectivo' => [
                'activo' => 1,
                'comision' => 0
            ],
            'metodo_pago_digital' => [
                'activo' => 1,
                'comision' => 3.2
            ]
        ];
    }
    
    echo json_encode([
        'success' => true, 
        'metodos' => $metodos
    ], JSON_UNESCAPED_UNICODE);
    
    $stmt->close();
    $conexion->close();
    exit;
}

function actualizarMetodosPago() {
    global $conexion;
    
    // Obtener datos
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    if (empty($data) || !isset($data['metodo'])) {
        throw new Exception('Datos incompletos');
    }
    
    $metodo = $data['metodo'];
    $activo = isset($data['activo']) ? (int)$data['activo'] : 0;
    $comision = isset($data['comision']) ? floatval($data['comision']) : 0;
    
    $config_valor = json_encode([
        'activo' => $activo,
        'comision' => $comision
    ], JSON_UNESCAPED_UNICODE);
    
    $clave = 'metodo_pago_' . $metodo;
    
    $conexion->begin_transaction();
    
    try {
        // Verificar si existe
        $stmt = $conexion->prepare("SELECT id FROM configuracion WHERE clave = ?");
        $stmt->bind_param("s", $clave);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            // Actualizar
            $stmt_update = $conexion->prepare("UPDATE configuracion SET valor = ?, fecha_modificacion = NOW() WHERE clave = ?");
            $stmt_update->bind_param("ss", $config_valor, $clave);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            // Insertar
            $descripcion = "Configuración del método de pago: " . $metodo;
            $stmt_insert = $conexion->prepare("INSERT INTO configuracion (clave, valor, descripcion) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("sss", $clave, $config_valor, $descripcion);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        
        $stmt->close();
        $conexion->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Método de pago actualizado'
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        $conexion->rollback();
        throw $e;
    }
    
    $conexion->close();
    exit;
}
?>