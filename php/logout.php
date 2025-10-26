<?php
// Iniciar sesión de forma segura
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    // Destruir todas las variables de sesión
    $_SESSION = array();
    
    // Si se desea destruir la sesión completamente, también hay que borrar la cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Finalmente, destruir la sesión
    session_destroy();
    
    // Verificar si es una petición AJAX
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        // Respuesta JSON para peticiones AJAX
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ]);
    } else {
        // Redirección directa para peticiones normales
        header('Location: ../index.php');
        exit();
    }
    
} catch (Exception $e) {
    // En caso de error, siempre responder exitosamente para el logout
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Sesión cerrada'
        ]);
    } else {
        header('Location: ../index.php');
        exit();
    }
}
?>