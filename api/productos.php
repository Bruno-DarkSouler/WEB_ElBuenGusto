<?php
header('Content-Type: application/json');

$productos = [
    [
        "id" => 1,
        "nombre" => "Milanesa con papas",
        "descripcion" => "Deliciosa milanesa de carne con papas fritas caseras",
        "precio" => 2500.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 1,
        "disponible" => true
    ],
    [
        "id" => 2,
        "nombre" => "Empanadas (docena)",
        "descripcion" => "Docena de empanadas de carne jugosas",
        "precio" => 3000.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 5,
        "disponible" => true
    ],
    [
        "id" => 3,
        "nombre" => "Tarta de jamón y queso",
        "descripcion" => "Tarta casera de jamón y queso con masa crocante",
        "precio" => 1800.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 4,
        "disponible" => true
    ],
    [
        "id" => 4,
        "nombre" => "Pizza muzzarella",
        "descripcion" => "Pizza a la piedra con abundante muzzarella",
        "precio" => 3500.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 10,
        "disponible" => true
    ],
    [
        "id" => 5,
        "nombre" => "Pollo al horno",
        "descripcion" => "Pollo al horno con papas y verduras",
        "precio" => 4200.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 1,
        "disponible" => false
    ]
];

echo json_encode($productos);
?>