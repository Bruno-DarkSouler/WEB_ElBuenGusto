<?php
// Configuración de la base de datos
$servidor = "localhost";
$usuario = "elbuengusto";
$password = "Rotiseria2025";
$base_datos = "elbuengusto_elbuengusto";

// Crear conexión
$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Configurar charset para caracteres especiales
$conexion->set_charset("utf8");

// Función para cerrar la conexión
function cerrarConexion($conexion) {
    $conexion->close();
}
?>