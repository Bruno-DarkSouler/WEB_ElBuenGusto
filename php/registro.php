<?php
// Limpiar cualquier output previo
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'conexion.php';

// Limpiar buffer
ob_clean();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validaciones básicas
    if (empty($nombre) || empty($apellido) || empty($email) || empty($telefono) || empty($password)) {
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
    
    // Insertar usuario en la base de datos (SIN dirección)
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, apellido, email, telefono, contraseña, rol) VALUES (?, ?, ?, ?, ?, 'cliente')");
    $stmt->bind_param("sssss", $nombre, $apellido, $email, $telefono, $password_hash);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuario registrado exitosamente'], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar usuario: ' . $conexion->error], JSON_UNESCAPED_UNICODE);
    }
    
    $stmt->close();
    $conexion->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

// Limpiar y enviar
ob_end_flush();
?>