<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'conexion.php';

// Verificar que el usuario esté logueado y sea administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        listarCategorias();
        break;
    case 'POST':
        crearCategoria();
        break;
    case 'PUT':
        parse_str(file_get_contents("php://input"), $_PUT);
        actualizarCategoria($_PUT);
        break;
    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DELETE);
        eliminarCategoria($_DELETE['id']);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        break;
}

function listarCategorias() {
    global $conexion;
    
    $sql = "SELECT * FROM categorias WHERE activa = 1 ORDER BY nombre";
    $resultado = $conexion->query($sql);
    
    $categorias = [];
    while($fila = $resultado->fetch_assoc()) {
        $categorias[] = $fila;
    }
    
    echo json_encode(['success' => true, 'categorias' => $categorias]);
}

function crearCategoria() {
    global $conexion;
    
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    
    if (empty($nombre)) {
        echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
        return;
    }
    
    $stmt = $conexion->prepare("INSERT INTO categorias (nombre, descripcion, activa) VALUES (?, ?, 1)");
    $stmt->bind_param("ss", $nombre, $descripcion);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Categoría creada exitosamente', 'categoria_id' => $stmt->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear la categoría']);
    }
    
    $stmt->close();
}

function actualizarCategoria($data) {
    global $conexion;
    
    $id = intval($data['id']);
    $nombre = trim($data['nombre']);
    $descripcion = trim($data['descripcion']);
    
    $stmt = $conexion->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nombre, $descripcion, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Categoría actualizada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la categoría']);
    }
    
    $stmt->close();
}

function eliminarCategoria($id) {
    global $conexion;
    
    // Verificar si hay productos en esta categoría
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM productos WHERE categoria_id = ? AND activo = 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();
    
    if ($fila['total'] > 0) {
        echo json_encode(['success' => false, 'message' => 'No se puede eliminar una categoría con productos activos']);
        $stmt->close();
        return;
    }
    $stmt->close();
    
    // Soft delete
    $stmt = $conexion->prepare("UPDATE categorias SET activa = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Categoría eliminada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la categoría']);
    }
    
    $stmt->close();
}

cerrarConexion($conexion);
?>