<?php
header('Content-Type: application/json');

// Datos de ejemplo para la confirmación del pedido
$confirmacion = [
    "pedido_id" => 12345,
    "cliente" => [
        "nombre" => "Juan Pérez",
        "telefono" => "1122334455",
        "direccion" => "Av. Siempreviva 742"
    ],
    "estado" => "confirmado",
    "fecha_hora" => "2023-10-15 19:30:00",
    "metodo_pago" => "Mercado Pago",
    "tiempo_estimado" => "30-40 minutos",
    "items" => [
        [
            "id" => 1,
            "nombre" => "Milanesa con papas",
            "precio" => 2500,
            "cantidad" => 2,
            "subtotal" => 5000
        ],
        [
            "id" => 2,
            "nombre" => "Empanadas (docena)",
            "precio" => 3000,
            "cantidad" => 1,
            "subtotal" => 3000
        ],
        [
            "id" => 3,
            "nombre" => "Tarta de jamón y queso",
            "precio" => 1800,
            "cantidad" => 1,
            "subtotal" => 1800
        ]
    ],
    "subtotal" => 9800,
    "costo_envio" => 500,
    "total" => 10300,
    "comentarios" => "Entregar en la puerta principal"
];

echo json_encode($confirmacion);
?>