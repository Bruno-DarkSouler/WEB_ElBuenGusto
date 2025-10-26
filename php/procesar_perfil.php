<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include '../php/conexion.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no iniciada.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Obtener datos del formulario
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$email = $_POST['email'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$direccionPrincipal = $_POST['direccionPrincipal'] ?? '';
$comidaFavorita = $_POST['comidaFavorita'] ?? '';
$nuevaPassword = $_POST['nuevaPassword'] ?? '';
$confirmarPassword = $_POST['confirmarPassword'] ?? '';

// Validar contraseñas
if ($nuevaPassword !== $confirmarPassword) {
    echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
    exit;
}

// Actualizar datos del usuario
try {
    if ($nuevaPassword !== '') {
        $hashed_password = password_hash($nuevaPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios 
                SET nombre=?, apellido=?, email=?, telefono=?, direccion=?, contraseña=?, comida_favorita=? 
                WHERE id=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssssi", $nombre, $apellido, $email, $telefono, $direccionPrincipal, $hashed_password, $comidaFavorita, $user_id);
    } else {
        $sql = "UPDATE usuarios 
                SET nombre=?, apellido=?, email=?, telefono=?, direccion=?, comida_favorita=? 
                WHERE id=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssssi", $nombre, $apellido, $email, $telefono, $direccionPrincipal, $comidaFavorita, $user_id);
    }

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }
    $stmt->close();

    
    $direccionPrincipal = intval($direccionPrincipal);

    // Poner todas las direcciones como no favoritas
    $stmt_reset = $conexion->prepare("UPDATE direcciones_cliente SET es_favorita=0 WHERE usuario_id=?");
    $stmt_reset->bind_param("i", $user_id);
    $stmt_reset->execute();
    $stmt_reset->close();

    // Poner la dirección seleccionada como favorita
    $stmt_fav = $conexion->prepare("UPDATE direcciones_cliente SET es_favorita=1 WHERE id=? AND usuario_id=?");
    $stmt_fav->bind_param("ii", $direccionPrincipal, $user_id);
    $stmt_fav->execute();
    $stmt_fav->close();

    // ============================

    echo json_encode(['success' => true, 'message' => 'Datos actualizados exitosamente.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
}

$conexion->close();
?>
