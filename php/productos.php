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
        if (isset($_GET['id'])) {
            obtenerProducto($_GET['id']);
        } else {
            listarProductos();
        }
        break;
    case 'POST':
        crearProducto();
        break;
    case 'PUT':
        parse_str(file_get_contents("php://input"), $_PUT);
        actualizarProducto($_PUT);
        break;
    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DELETE);
        eliminarProducto($_DELETE['id']);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        break;
}

function listarProductos() {
    global $conexion;
    
    $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;
    $estado = isset($_GET['estado']) ? $_GET['estado'] : null;
    
    $sql = "SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            INNER JOIN categorias c ON p.categoria_id = c.id 
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if ($categoria) {
        $sql .= " AND p.categoria_id = ?";
        $params[] = $categoria;
        $types .= "i";
    }
    
    if ($estado === 'activos') {
        $sql .= " AND p.disponible = 1 AND p.activo = 1";
    } elseif ($estado === 'inactivos') {
        $sql .= " AND (p.disponible = 0 OR p.activo = 0)";
    }
    
    $sql .= " ORDER BY p.nombre";
    
    $stmt = $conexion->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $productos = [];
    while($fila = $resultado->fetch_assoc()) {
        $productos[] = $fila;
    }
    
    echo json_encode(['success' => true, 'productos' => $productos]);
    $stmt->close();
}

function obtenerProducto($id) {
    global $conexion;
    
    $stmt = $conexion->prepare("SELECT p.*, c.nombre as categoria_nombre 
                                FROM productos p 
                                INNER JOIN categorias c ON p.categoria_id = c.id 
                                WHERE p.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $producto = $resultado->fetch_assoc();
        echo json_encode(['success' => true, 'producto' => $producto]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    }
    
    $stmt->close();
}

function crearProducto() {
    global $conexion;
    
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $categoria_id = intval($_POST['categoria_id']);
    $ingredientes = trim($_POST['ingredientes']);
    $tiempo_preparacion = intval($_POST['tiempo_preparacion']);
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    
    // Manejo de imagen
    $imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen = subirImagen($_FILES['imagen']);
        if (!$imagen) {
            echo json_encode(['success' => false, 'message' => 'Error al subir la imagen']);
            return;
        }
    }
    
    $stmt = $conexion->prepare("INSERT INTO productos (nombre, descripcion, precio, imagen, categoria_id, ingredientes, tiempo_preparacion, disponible, activo) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("ssdsisii", $nombre, $descripcion, $precio, $imagen, $categoria_id, $ingredientes, $tiempo_preparacion, $disponible);
    
    if ($stmt->execute()) {
        $producto_id = $stmt->insert_id;
        echo json_encode(['success' => true, 'message' => 'Producto creado exitosamente', 'producto_id' => $producto_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear el producto: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function actualizarProducto($data) {
    global $conexion;
    
    $id = intval($data['id']);
    $nombre = trim($data['nombre']);
    $descripcion = trim($data['descripcion']);
    $precio = floatval($data['precio']);
    $categoria_id = intval($data['categoria_id']);
    $ingredientes = trim($data['ingredientes']);
    $tiempo_preparacion = intval($data['tiempo_preparacion']);
    $disponible = isset($data['disponible']) ? 1 : 0;
    
    $stmt = $conexion->prepare("UPDATE productos 
                                SET nombre = ?, descripcion = ?, precio = ?, categoria_id = ?, 
                                    ingredientes = ?, tiempo_preparacion = ?, disponible = ? 
                                WHERE id = ?");
    $stmt->bind_param("ssdisiii", $nombre, $descripcion, $precio, $categoria_id, $ingredientes, $tiempo_preparacion, $disponible, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Producto actualizado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el producto']);
    }
    
    $stmt->close();
}

function eliminarProducto($id) {
    global $conexion;
    
    // Soft delete
    $stmt = $conexion->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Producto eliminado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el producto']);
    }
    
    $stmt->close();
}

function subirImagen($file) {
    $target_dir = "../uploads/productos/";
    
    // Crear directorio si no existe
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $nuevo_nombre = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $nuevo_nombre;
    
    // Verificar si es una imagen real
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }
    
    // Verificar tamaño (max 5MB)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    // Permitir ciertos formatos
    if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif", "webp"])) {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return "uploads/productos/" . $nuevo_nombre;
    }
    
    return false;
}

cerrarConexion($conexion);
?>