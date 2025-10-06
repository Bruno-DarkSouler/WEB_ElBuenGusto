<?php
header('Content-Type: application/json');

// Datos de ejemplo para el cajero
$cajero = [
    "pedidos_pendientes" => [
        [
            "pedido_id" => 12345,
            "cliente" => "Juan Pérez",
            "telefono" => "1122334455",
            "hora_pedido" => "19:15:00",
            "estado" => "confirmado",
            "total" => 10300
        ],
        [
            "pedido_id" => 12346,
            "cliente" => "María González",
            "telefono" => "1155667788",
            "hora_pedido" => "19:20:00",
            "estado" => "en preparación",
            "total" => 8500
        ],
        [
            "pedido_id" => 12347,
            "cliente" => "Carlos Rodríguez",
            "telefono" => "1199887766",
            "hora_pedido" => "19:25:00",
            "estado" => "listo",
            "total" => 6200
        ]
    ],
    "metodos_pago" => [
        "Mercado Pago",
        "Cuenta DNI",
        "Efectivo (solo clientes VIP)"
    ],
    "estadisticas_dia" => [
        "total_ventas" => 45000,
        "cantidad_pedidos" => 15,
        "pedidos_completados" => 10,
        "pedidos_en_proceso" => 3,
        "pedidos_cancelados" => 2
    ],
    "horario_apertura" => [
        "almuerzo" => "11:00 - 15:00",
        "cena" => "19:00 - 23:00"
    ]
];

echo json_encode($cajero);
?>