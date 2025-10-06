        <?php
        header('Content-Type: application/json');

        $carrito = [
            [
                "id" => 1,
                "nombre" => "Milanesa con papas",
                "precio" => 2500,
                "cantidad" => 2
            ],
            [
                "id" => 2,
                "nombre" => "Empanadas (docena)",
                "precio" => 3000,
                "cantidad" => 1
            ],
            [
                "id" => 3,
                "nombre" => "Tarta de jamón y queso",
                "precio" => 1800,
                "cantidad" => 1
            ]
        ];

        echo json_encode($carrito);
        ?>
