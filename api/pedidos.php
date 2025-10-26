<?php
    header("content-type: application/json");

    $productos = [
        "pendientes" => [
        ["id" => 1, "nombre" => "Estebanquito", "precio" => 20, "direccion" => "Avenida Siempre Viva", "telefono" => "11111111", "distancia" => 1.3, "tiempo" => 11],
        ["id" => 2, "nombre" => "Empanadas", "precio" => 30, "direccion" => "Avenida Siempre Viva", "telefono" => "22222222", "distancia" => 2.3, "tiempo" => 22]
        ],
        "en_camino" => [
        ["id" => 1, "nombre" => "Estebanquito", "precio" => 20, "direccion" => "Avenida Siempre Viva", "telefono" => "11111111"],
        ["id" => 2, "nombre" => "Empanadas", "precio" => 30, "direccion" => "Avenida Siempre Viva", "telefono" => "22222222"]
        ],
        "entregados" => [
        ["id" => 1, "nombre" => "Estebanquito", "precio" => 20, "direccion" => "Avenida Siempre Viva", "hora" => "11"],
        ["id" => 2, "nombre" => "Empanadas", "precio" => 30, "direccion" => "Avenida Siempre Viva", "hora" => "22"]
        ]
];

    echo json_encode($productos);
?>