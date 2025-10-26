<?php
    header("content-type:application/json");
    
    $json = file_get_contents("php://input");

    $datos = json_decode($json, true);

    print_r($datos["id_pedido"]);
?>