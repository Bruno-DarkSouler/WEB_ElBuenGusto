<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validaciones básicas
    if (empty($nombre) || empty($apellido) || empty($email) || empty($telefono) || empty($direccion) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
        exit;
    }
    
    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
        exit;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'El email no es válido']);
        exit;
    }
    
    // Verificar si el email ya existe
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
        exit;
    }
    
    // Encriptar contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar usuario en la base de datos
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, apellido, email, telefono, direccion, contraseña, rol) VALUES (?, ?, ?, ?, ?, ?, 'cliente')");
    $stmt->bind_param("ssssss", $nombre, $apellido, $email, $telefono, $direccion, $password_hash);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuario registrado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar usuario: ' . $conexion->error]);
    }
    
    $stmt->close();
    cerrarConexion($conexion);
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>