<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

// Debug: ver qué está llegando
file_put_contents('login_debug.log', 
    "=== " . date('Y-m-d H:i:s') . " ===\n" .
    "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n" .
    "POST: " . print_r($_POST, true) . "\n" .
    "GET: " . print_r($_GET, true) . "\n" .
    "RAW INPUT: " . file_get_contents('php://input') . "\n\n",
    FILE_APPEND
);

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido. Método recibido: ' . $_SERVER['REQUEST_METHOD']]);
    exit;
}

// Verificar datos
if (empty($_POST['email']) || empty($_POST['password'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Faltan datos',
        'debug' => [
            'post_keys' => array_keys($_POST),
            'email_isset' => isset($_POST['email']),
            'password_isset' => isset($_POST['password'])
        ]
    ]);
    exit;
}

require_once 'conexion.php';

$email = trim($_POST['email']);
$password = $_POST['password'];

// Buscar usuario - AGREGADO: telefono en la consulta
$stmt = $conexion->prepare("SELECT id, nombre, apellido, email, telefono, contraseña, rol, activo FROM usuarios WHERE email = ?");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error en consulta: ' . $conexion->error]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    exit;
}

$usuario = $resultado->fetch_assoc();

if ($usuario['activo'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Usuario desactivado']);
    exit;
}

if (!password_verify($password, $usuario['contraseña'])) {
    echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
    exit;
}

// Login exitoso - AGREGADO: telefono en la sesión
$_SESSION['user_id'] = $usuario['id'];
$_SESSION['user_name'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
$_SESSION['user_email'] = $usuario['email'];
$_SESSION['user_telefono'] = $usuario['telefono']; // ← LÍNEA AGREGADA
$_SESSION['user_rol'] = $usuario['rol'];

// Usar rutas absolutas desde la raíz del servidor
$redirect_url = '/html/inicio.php';
if ($usuario['rol'] === 'administrador' || $usuario['email'] === 'admin@elbuengusto.com') {
    $redirect_url = '/admin/admin.php';
}
if ($usuario['rol'] === 'cocinero'){
    $redirect_url = '/admin/cocinero.php';
}
if ($usuario['rol'] === 'repartidor'){
    $redirect_url = '/admin/repartidor.php';
}
if ($usuario['rol'] === 'cajero'){
    $redirect_url = '/admin/cajero.php';
}

echo json_encode([
    'success' => true,
    'message' => 'Login exitoso',
    'redirect' => $redirect_url,
    'usuario' => [
        'nombre' => $usuario['nombre'],
        'rol' => $usuario['rol']
    ]
]);

$stmt->close();
$conexion->close();