<?php
include 'conexion.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Verificar si se recibió una imagen en base64
if (isset($_POST['foto_base64'])) {
    $foto_base64 = $_POST['foto_base64'];
    
    // Validar que sea una imagen válida
    if (!preg_match('/^data:image\/(png|jpg|jpeg|gif);base64,/', $foto_base64)) {
        echo json_encode(['success' => false, 'message' => 'Formato de imagen inválido']);
        exit;
    }
    
    // Actualizar en la base de datos
    $stmt = $conexion->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
    $stmt->bind_param("si", $foto_base64, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Foto actualizada exitosamente',
            'foto' => $foto_base64
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la foto']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No se recibió ninguna foto']);
}

$conexion->close();
?>