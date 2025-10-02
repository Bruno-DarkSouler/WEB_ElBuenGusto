<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'conexion.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        listarPromociones();
        break;
    case 'POST':
        crearPromocion();
        break;
    case 'PUT':
        parse_str(file_get_contents("php://input"), $_PUT);
        actualizarPromocion($_PUT);
        break;
    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DELETE);
        eliminarPromocion($_DELETE['id']);
        break;
}

function listarPromociones() {
    global $conexion;
    
    $sql = "SELECT * FROM promociones ORDER BY fecha_inicio DESC";
    $resultado = $conexion->query($sql);
    
    $promociones = [];
    while($fila = $resultado->fetch_assoc()) {
        $promociones[] = $fila;
    }
    
    echo json_encode(['success' => true, 'promociones' => $promociones]);
}

function crearPromocion() {
    global $conexion;
    
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $tipo = $_POST['tipo'];
    $valor = floatval($_POST['valor']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $monto_minimo = isset($_POST['monto_minimo']) ? floatval($_POST['monto_minimo']) : 0;
    
    $stmt = $conexion->prepare("INSERT INTO promociones (nombre, descripcion, tipo, valor, fecha_inicio, fecha_fin, activa, monto_minimo) 
                                VALUES (?, ?, ?, ?, ?, ?, 1, ?)");
    $stmt->bind_param("sssdssd", $nombre, $descripcion, $tipo, $valor, $fecha_inicio, $fecha_fin, $monto_minimo);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Promoción creada exitosamente', 'promocion_id' => $stmt->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear la promoción']);
    }
    
    $stmt->close();
}

function actualizarPromocion($data) {
    global $conexion;
    
    $id = intval($data['id']);
    $nombre = trim($data['nombre']);
    $descripcion = trim($data['descripcion']);
    $tipo = $data['tipo'];
    $valor = floatval($data['valor']);
    $fecha_inicio = $data['fecha_inicio'];
    $fecha_fin = $data['fecha_fin'];
    $activa = isset($data['activa']) ? intval($data['activa']) : 1;
    $monto_minimo = isset($data['monto_minimo']) ? floatval($data['monto_minimo']) : 0;
    
    $stmt = $conexion->prepare("UPDATE promociones 
                                SET nombre = ?, descripcion = ?, tipo = ?, valor = ?, fecha_inicio = ?, fecha_fin = ?, activa = ?, monto_minimo = ? 
                                WHERE id = ?");
    $stmt->bind_param("sssdssidi", $nombre, $descripcion, $tipo, $valor, $fecha_inicio, $fecha_fin, $activa, $monto_minimo, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Promoción actualizada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la promoción']);
    }
    
    $stmt->close();
}

function eliminarPromocion($id) {
    global $conexion;
    
    $stmt = $conexion->prepare("UPDATE promociones SET activa = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Promoción eliminada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la promoción']);
    }
    
    $stmt->close();
}

cerrarConexion($conexion);
?>