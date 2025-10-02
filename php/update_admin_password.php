<?php
header('Content-Type: application/json');
require_once 'conexion.php';

// Contraseña en texto plano que queremos hashear
$password = 'admin123';

// Generar hash bcrypt
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Actualizar la contraseña del usuario admin
$stmt = $conexion->prepare("UPDATE usuarios SET contraseña = ? WHERE email = 'admin@elbuengusto.com'");
$stmt->bind_param("s", $hashed_password);

$result = $stmt->execute();

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Contraseña actualizada correctamente',
        'hashed_password' => $hashed_password
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar la contraseña: ' . $conexion->error
    ]);
}

$stmt->close();
cerrarConexion($conexion);
?>