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
            obtenerConfiguracion();
            break;
        case 'POST':
        case 'PUT':
            actualizarConfiguracion();
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

function obtenerConfiguracion() {
    global $conexion;
    
    $sql = "SELECT * FROM configuracion WHERE id = 1 LIMIT 1";
    $resultado = $conexion->query($sql);

    if (!$resultado || $resultado->num_rows == 0) {
        throw new Exception('No se encontró configuración');
    }

    $fila = $resultado->fetch_assoc();

    echo json_encode([
        'success' => true,
        'configuracion' => [
            'nombre_local'  => $fila['valor'] ?? '',
            'email_contacto'=> $fila['email_contacto'] ?? '',
            'telefono'      => $fila['telefono'] ?? '',
            'direccion'     => $fila['direccion'] ?? '',
            'descripcion'   => $fila['descripcion'] ?? '',
        ]
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


function actualizarConfiguracion() {
    global $conexion;

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        $data = $_POST;
    }

    if (empty($data)) {
        throw new Exception('No se recibieron datos');
    }

    $stmt = $conexion->prepare("
        UPDATE configuracion 
        SET 
            valor = ?, 
            email_contacto = ?, 
            telefono = ?, 
            direccion = ?, 
            descripcion = ?, 
            fecha_modificacion = NOW()
        WHERE id = 1
    ");

    $stmt->bind_param(
        "sssss",
        $data['nombre_local'],
        $data['email_contacto'],
        $data['telefono'],
        $data['direccion'],
        $data['descripcion']
    );

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Configuración actualizada'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar'
        ], JSON_UNESCAPED_UNICODE);
    }

    exit;
}

?>