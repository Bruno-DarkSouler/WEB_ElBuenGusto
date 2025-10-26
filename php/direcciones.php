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
$direccion_id = $_POST['direccion_id'] ?? '';
$alias = $_POST['etiquetaDireccion'] ?? '';
$direccion = $_POST['direccionCompleta'] ?? '';
$codigo_postal = $_POST['codigoPostal'] ?? '';
$instrucciones = $_POST['instrucciones'] ?? '';

try {
    if (!empty($direccion_id)) {
        // EDITAR dirección existente
        $stmt = $conexion->prepare("UPDATE direcciones_cliente 
                                    SET alias = ?, direccion = ?, codigo_postal = ?, instrucciones = ? 
                                    WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ssssii", $alias, $direccion, $codigo_postal, $instrucciones, $direccion_id, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Dirección actualizada exitosamente.']);
        } else {
            throw new Exception('Error al actualizar la dirección.');
        }
    } else {
        // AGREGAR nueva dirección
        $stmt = $conexion->prepare("INSERT INTO direcciones_cliente 
                                    (usuario_id, alias, direccion, codigo_postal, instrucciones, es_favorita) 
                                    VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("issss", $user_id, $alias, $direccion, $codigo_postal, $instrucciones);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Dirección agregada exitosamente.']);
        } else {
            throw new Exception('Error al agregar la dirección.');
        }
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conexion->close();
?>