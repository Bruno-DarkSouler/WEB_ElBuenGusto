<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'conexion.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        obtenerMetodosPago();
        break;
    case 'POST':
    case 'PUT':
        actualizarMetodosPago();
        break;
}

function obtenerMetodosPago() {
    global $conexion;
    
    // Obtener configuración de métodos de pago
    $stmt = $conexion->prepare("SELECT * FROM configuracion WHERE clave LIKE 'metodo_pago_%'");
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $metodos = [];
    while($fila = $resultado->fetch_assoc()) {
        $metodos[$fila['clave']] = json_decode($fila['valor'], true);
    }
    
    echo json_encode(['success' => true, 'metodos' => $metodos]);
    $stmt->close();
}

function actualizarMetodosPago() {
    global $conexion;
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $metodo = $data['metodo'];
    $activo = isset($data['activo']) ? $data['activo'] : 0;
    $comision = isset($data['comision']) ? floatval($data['comision']) : 0;
    
    $config_valor = json_encode([
        'activo' => $activo,
        'comision' => $comision
    ]);
    
    $clave = 'metodo_pago_' . $metodo;
    
    // Verificar si existe
    $stmt = $conexion->prepare("SELECT id FROM configuracion WHERE clave = ?");
    $stmt->bind_param("s", $clave);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        // Actualizar
        $stmt_update = $conexion->prepare("UPDATE configuracion SET valor = ? WHERE clave = ?");
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
    
    echo json_encode(['success' => true, 'message' => 'Método de pago actualizado']);
}

cerrarConexion($conexion);
?>