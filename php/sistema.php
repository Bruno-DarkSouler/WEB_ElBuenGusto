<?php
    session_start();

    $conexion = new mysqli("localhost", "root", "", "calera");

    $consulta = $conexion->prepare("SELECT * FROM usuarios WHERE id <= ?;");
    $consulta->bind_param("i", $valor);

    $valor = 6;

    $consulta->execute();
    $resultado = $consulta->get_result();
    
    while($fila = $resultado->fetch_assoc()){
        echo $fila["nombre"];
    }
?>