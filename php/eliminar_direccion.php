<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include 'conexion.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no iniciada.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$direccion_id = $_POST['direccion_id'] ?? 0;

try {
    // Verificar que la dirección pertenece al usuario antes de eliminar
    $stmt = $conexion->prepare("DELETE FROM direcciones_cliente WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $direccion_id, $user_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Dirección eliminada exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la dirección.']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conexion->close();
?>