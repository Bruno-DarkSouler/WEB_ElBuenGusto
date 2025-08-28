<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener datos del formulario
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validaciones básicas
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email y contraseña son obligatorios']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'El email no es válido']);
        exit;
    }
    
    // Buscar usuario en la base de datos
    $stmt = $conexion->prepare("SELECT id, nombre, apellido, email, contraseña, rol, activo FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Email o contraseña incorrectos']);
        exit;
    }
    
    $usuario = $resultado->fetch_assoc();
    
    // Verificar si el usuario está activo
    if ($usuario['activo'] != 1) {
        echo json_encode(['success' => false, 'message' => 'Usuario desactivado. Contacte al administrador']);
        exit;
    }
    
    // Verificar contraseña
    if (password_verify($password, $usuario['contraseña'])) {
        // Login exitoso - crear sesión
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_name'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
        $_SESSION['user_email'] = $usuario['email'];
        $_SESSION['user_rol'] = $usuario['rol'];
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login exitoso',
            'usuario' => [
                'id' => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'apellido' => $usuario['apellido'],
                'email' => $usuario['email'],
                'rol' => $usuario['rol']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email o contraseña incorrectos']);
    }
    
    $stmt->close();
    cerrarConexion($conexion);
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>