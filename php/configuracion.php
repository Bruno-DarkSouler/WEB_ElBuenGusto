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
        obtenerConfiguracion();
        break;
    case 'POST':
    case 'PUT':
        actualizarConfiguracion();
        break;
}

function obtenerConfiguracion() {
    global $conexion;
    
    $sql = "SELECT * FROM configuracion";
    $resultado = $conexion->query($sql);
    
    $config = [];
    while($fila = $resultado->fetch_assoc()) {
        $config[$fila['clave']] = $fila['valor'];
    }
    
    echo json_encode(['success' => true, 'configuracion' => $config]);
}

function actualizarConfiguracion() {
    global $conexion;
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    
    foreach ($data as $clave => $valor) {
        // Verificar si la clave existe
        $stmt = $conexion->prepare("SELECT id FROM configuracion WHERE clave = ?");
        $stmt->bind_param("s", $clave);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            // Actualizar
            $stmt_update = $conexion->prepare("UPDATE configuracion SET valor = ?, fecha_modificacion = NOW() WHERE clave = ?");
            $stmt_update->bind_param("ss", $valor, $clave);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            // Insertar
            $stmt_insert = $conexion->prepare("INSERT INTO configuracion (clave, valor) VALUES (?, ?)");
            $stmt_insert->bind_param("ss", $clave, $valor);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        $stmt->close();
    }
    
    echo json_encode(['success' => true, 'message' => 'Configuración actualizada exitosamente']);
}

cerrarConexion($conexion);
?>