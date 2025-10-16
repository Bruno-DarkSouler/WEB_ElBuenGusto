<?php
include '../php/conexion.php';

session_start();
$user_id = $_SESSION['user_id'];

$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$email = $_POST['email'];
$telefono = $_POST['telefono'];
$direccionPrincipal = $_POST['direccionPrincipal'];
$nuevaPassword = $_POST['nuevaPassword'];
$confirmarPassword = $_POST['confirmarPassword'];
$daltonismo = $_POST['daltonismo']; // Obtener el valor de daltonismo

// Depuración: Verificar si el campo daltonismo se está recibiendo
error_log('Daltonismo: ' . $daltonismo);

// Validar contraseñas
if ($nuevaPassword !== $confirmarPassword) {
    echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
    exit;
}

// Actualizar la información del usuario
$sql = "UPDATE usuarios SET
            nombre = '$nombre',
            apellido = '$apellido',
            email = '$email',
            telefono = '$telefono',
            direccion = '$direccionPrincipal',
            daltonismo = '$daltonismo'"; // Incluir daltonismo en la actualización

if ($nuevaPassword !== '') {
    $hashed_password = password_hash($nuevaPassword, PASSWORD_DEFAULT);
    $sql .= ", password = '$hashed_password'";
}

$sql .= " WHERE id = $user_id";

if ($conexion->query($sql) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'Datos actualizados exitosamente.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar los datos: ' . $conexion->error]);
}
?>