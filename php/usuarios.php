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
            obtenerUsuario($_GET['id']);
        } else {
            listarUsuarios();
        }
        break;
    case 'POST':
        crearUsuario();
        break;
    case 'PUT':
        parse_str(file_get_contents("php://input"), $_PUT);
        actualizarUsuario($_PUT);
        break;
    case 'DELETE':
        parse_str(file_get_contents("php://input"), $_DELETE);
        cambiarEstadoUsuario($_DELETE['id'], $_DELETE['accion']);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        break;
}

function listarUsuarios() {
    global $conexion;
    
    $rol = isset($_GET['rol']) ? $_GET['rol'] : null;
    $estado = isset($_GET['estado']) ? $_GET['estado'] : null;
    
    $sql = "SELECT id, nombre, apellido, email, telefono, rol, activo, estado_disponibilidad, fecha_registro 
            FROM usuarios 
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if ($rol) {
        $sql .= " AND rol = ?";
        $params[] = $rol;
        $types .= "s";
    }
    
    if ($estado === 'activos') {
        $sql .= " AND activo = 1";
    } elseif ($estado === 'inactivos') {
        $sql .= " AND activo = 0";
    }
    
    $sql .= " ORDER BY fecha_registro DESC";
    
    $stmt = $conexion->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $usuarios = [];
    while($fila = $resultado->fetch_assoc()) {
        $usuarios[] = $fila;
    }
    
    echo json_encode(['success' => true, 'usuarios' => $usuarios]);
    $stmt->close();
}

function obtenerUsuario($id) {
    global $conexion;
    
    $stmt = $conexion->prepare("SELECT id, nombre, apellido, email, telefono, direccion, rol, activo, estado_disponibilidad, fecha_registro 
                                FROM usuarios 
                                WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        echo json_encode(['success' => true, 'usuario' => $usuario]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }
    
    $stmt->close();
}

function crearUsuario() {
    global $conexion;
    
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';
    $rol = $_POST['rol'];
    $password = $_POST['password'];
    
    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($email) || empty($telefono) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email no válido']);
        return;
    }
    
    // Verificar si el email ya existe
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
        $stmt->close();
        return;
    }
    $stmt->close();
    
    // Hash de la contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, apellido, email, telefono, direccion, contraseña, rol, activo, estado_disponibilidad) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1)");
    $stmt->bind_param("sssssss", $nombre, $apellido, $email, $telefono, $direccion, $password_hash, $rol);
    
    if ($stmt->execute()) {
        $usuario_id = $stmt->insert_id;
        echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente', 'usuario_id' => $usuario_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear el usuario: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function actualizarUsuario($data) {
    global $conexion;
    
    $id = intval($data['id']);
    $nombre = trim($data['nombre']);
    $apellido = trim($data['apellido']);
    $email = trim($data['email']);
    $telefono = trim($data['telefono']);
    $direccion = isset($data['direccion']) ? trim($data['direccion']) : '';
    $rol = $data['rol'];
    
    // Verificar si el email ya existe en otro usuario
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El email ya está en uso por otro usuario']);
        $stmt->close();
        return;
    }
    $stmt->close();
    
    // Si se proporciona una nueva contraseña
    if (isset($data['password']) && !empty($data['password'])) {
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $conexion->prepare("UPDATE usuarios 
                                    SET nombre = ?, apellido = ?, email = ?, telefono = ?, direccion = ?, rol = ?, contraseña = ? 
                                    WHERE id = ?");
        $stmt->bind_param("sssssssi", $nombre, $apellido, $email, $telefono, $direccion, $rol, $password_hash, $id);
    } else {
        $stmt = $conexion->prepare("UPDATE usuarios 
                                    SET nombre = ?, apellido = ?, email = ?, telefono = ?, direccion = ?, rol = ? 
                                    WHERE id = ?");
        $stmt->bind_param("ssssssi", $nombre, $apellido, $email, $telefono, $direccion, $rol, $id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuario actualizado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario']);
    }
    
    $stmt->close();
}

function cambiarEstadoUsuario($id, $accion) {
    global $conexion;
    
    $nuevo_estado = ($accion === 'activar') ? 1 : 0;
    
    $stmt = $conexion->prepare("UPDATE usuarios SET activo = ? WHERE id = ?");
    $stmt->bind_param("ii", $nuevo_estado, $id);
    
    if ($stmt->execute()) {
        $mensaje = ($accion === 'activar') ? 'Usuario activado' : 'Usuario desactivado';
        echo json_encode(['success' => true, 'message' => $mensaje]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al cambiar el estado del usuario']);
    }
    
    $stmt->close();
}

cerrarConexion($conexion);
?>