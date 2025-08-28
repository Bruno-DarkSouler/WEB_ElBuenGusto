<?php
session_start();

// Configurar el tipo de contenido como JSON
header('Content-Type: application/json');

// Verificar si existe una sesión activa
if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])) {
    // Usuario está logueado
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'nombre' => $_SESSION['user_name'],
            'email' => isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '',
            'telefono' => isset($_SESSION['user_telefono']) ? $_SESSION['user_telefono'] : ''
        ],
        'message' => 'Sesión activa'
    ]);
} else {
    // Usuario no está logueado
    echo json_encode([
        'success' => false,
        'message' => 'No hay sesión activa'
    ]);
}
?>