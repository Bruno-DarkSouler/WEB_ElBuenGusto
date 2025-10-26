<?php
    require_once("../php/sistema.php");

    $objetivo = $_GET["objetivo"];
    $resultado = consultaSelect($conexion, "SELECT `id`, `nombre`, `descripcion`, `precio`, `imagen`, `categoria_id`, `ingredientes`, `tiempo_preparacion`, `disponible`, `valoracion_promedio` FROM `productos` WHERE nombre LIKE CONCAT('%', ?, '%');", "s", [$objetivo]);
    echo json_encode($resultado);
?>