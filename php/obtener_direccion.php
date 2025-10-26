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
$direccion_id = $_GET['id'] ?? 0;

try {
    // Obtener la dirección asegurando que pertenece al usuario
    $stmt = $conexion->prepare("SELECT * FROM direcciones_cliente WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $direccion_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $direccion = $result->fetch_assoc();
        echo json_encode(['success' => true, 'direccion' => $direccion]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Dirección no encontrada.']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conexion->close();
?>