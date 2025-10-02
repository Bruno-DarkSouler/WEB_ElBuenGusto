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
        listarZonas();
        break;
    case 'POST':
        crearZona();
        break;
    case 'PUT':
        parse_str(file_get_contents("php://input"), $_PUT);
        actualizarZona($_PUT);
        break;
    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DELETE);
        eliminarZona($_DELETE['id']);
        break;
}

function listarZonas() {
    global $conexion;
    
    $sql = "SELECT * FROM zonas_delivery ORDER BY precio_delivery ASC";
    $resultado = $conexion->query($sql);
    
    $zonas = [];
    while($fila = $resultado->fetch_assoc()) {
        $zonas[] = $fila;
    }
    
    echo json_encode(['success' => true, 'zonas' => $zonas]);
}

function crearZona() {
    global $conexion;
    
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio_delivery = floatval($_POST['precio_delivery']);
    $tiempo_estimado = intval($_POST['tiempo_estimado']);
    $activa = isset($_POST['activa']) ? 1 : 0;
    
    $stmt = $conexion->prepare("INSERT INTO zonas_delivery (nombre, descripcion, precio_delivery, tiempo_estimado, activa) 
                                VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdii", $nombre, $descripcion, $precio_delivery, $tiempo_estimado, $activa);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Zona creada exitosamente', 'zona_id' => $stmt->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear la zona']);
    }
    
    $stmt->close();
}

function actualizarZona($data) {
    global $conexion;
    
    $id = intval($data['id']);
    $nombre = trim($data['nombre']);
    $descripcion = trim($data['descripcion']);
    $precio_delivery = floatval($data['precio_delivery']);
    $tiempo_estimado = intval($data['tiempo_estimado']);
    $activa = isset($data['activa']) ? intval($data['activa']) : 0;
    
    $stmt = $conexion->prepare("UPDATE zonas_delivery 
                                SET nombre = ?, descripcion = ?, precio_delivery = ?, tiempo_estimado = ?, activa = ? 
                                WHERE id = ?");
    $stmt->bind_param("ssdiii", $nombre, $descripcion, $precio_delivery, $tiempo_estimado, $activa, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Zona actualizada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la zona']);
    }
    
    $stmt->close();
}

function eliminarZona($id) {
    global $conexion;
    
    $stmt = $conexion->prepare("UPDATE zonas_delivery SET activa = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Zona eliminada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la zona']);
    }
    
    $stmt->close();
}

cerrarConexion($conexion);
?>