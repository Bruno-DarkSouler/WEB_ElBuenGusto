<?php
header('Content-Type: application/json');
require_once 'conexion.php';

$sql = "SELECT COUNT(*) as total FROM usuarios";
$resultado = $conexion->query($sql);
$row = $resultado->fetch_assoc();

echo json_encode([
    'success' => true,
    'mensaje' => 'Conectado a buengusto',
    'usuarios' => $row['total']
]);
?>