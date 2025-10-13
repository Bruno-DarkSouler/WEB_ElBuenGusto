<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$productos = [
    // MINUTAS (categoría 1)
    [
        "id" => 1,
        "nombre" => "Milanesa con papas fritas",
        "descripcion" => "Milanesa de carne con guarnición de papas fritas caseras",
        "precio" => 2500.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 1,
        "categoria_nombre" => "Minutas",
        "ingredientes" => "Carne, pan rallado, papas",
        "tiempo_preparacion" => 25,
        "disponible" => true
    ],
    [
        "id" => 2,
        "nombre" => "Milanesa napolitana",
        "descripcion" => "Milanesa con jamón, queso y salsa de tomate",
        "precio" => 2800.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 1,
        "categoria_nombre" => "Minutas",
        "ingredientes" => "Carne, jamón, queso, tomate",
        "tiempo_preparacion" => 30,
        "disponible" => true
    ],
    [
        "id" => 3,
        "nombre" => "Suprema de pollo",
        "descripcion" => "Pechuga de pollo con ensalada",
        "precio" => 2300.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 1,
        "categoria_nombre" => "Minutas",
        "ingredientes" => "Pollo, lechuga, tomate",
        "tiempo_preparacion" => 25,
        "disponible" => true
    ],
    [
        "id" => 4,
        "nombre" => "Bife de chorizo",
        "descripcion" => "Bife de 300g con guarnición a elección",
        "precio" => 3500.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 1,
        "categoria_nombre" => "Minutas",
        "ingredientes" => "Carne de res",
        "tiempo_preparacion" => 20,
        "disponible" => true
    ],
    [
        "id" => 5,
        "nombre" => "Lomito completo",
        "descripcion" => "Lomito con jamón, queso, huevo y verduras",
        "precio" => 3200.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 1,
        "categoria_nombre" => "Minutas",
        "ingredientes" => "Carne, jamón, queso, huevo",
        "tiempo_preparacion" => 30,
        "disponible" => true
    ],

    // PASTAS (categoría 2)
    [
        "id" => 6,
        "nombre" => "Ravioles de ricota",
        "descripcion" => "Ravioles caseros con salsa a elección",
        "precio" => 1800.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 2,
        "categoria_nombre" => "Pastas",
        "ingredientes" => "Masa, ricota, salsa",
        "tiempo_preparacion" => 15,
        "disponible" => true
    ],
    [
        "id" => 7,
        "nombre" => "Sorrentinos de jamón y queso",
        "descripcion" => "Sorrentinos con salsa fileto",
        "precio" => 2000.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 2,
        "categoria_nombre" => "Pastas",
        "ingredientes" => "Masa, jamón, queso, tomate",
        "tiempo_preparacion" => 15,
        "disponible" => true
    ],
    [
        "id" => 8,
        "nombre" => "Ñoquis de papa",
        "descripcion" => "Ñoquis caseros con salsa bolognesa",
        "precio" => 1700.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 2,
        "categoria_nombre" => "Pastas",
        "ingredientes" => "Papa, harina, carne picada",
        "tiempo_preparacion" => 20,
        "disponible" => true
    ],
    [
        "id" => 9,
        "nombre" => "Lasagna de carne",
        "descripcion" => "Lasagna con carne picada y bechamel",
        "precio" => 2200.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 2,
        "categoria_nombre" => "Pastas",
        "ingredientes" => "Pasta, carne, bechamel",
        "tiempo_preparacion" => 25,
        "disponible" => true
    ],
    [
        "id" => 10,
        "nombre" => "Canelones de verdura",
        "descripcion" => "Canelones rellenos de espinaca y ricota",
        "precio" => 1900.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 2,
        "categoria_nombre" => "Pastas",
        "ingredientes" => "Pasta, espinaca, ricota",
        "tiempo_preparacion" => 20,
        "disponible" => true
    ],

    // GUISOS (categoría 3)
    [
        "id" => 11,
        "nombre" => "Guiso de lentejas",
        "descripcion" => "Guiso casero de lentejas con chorizo",
        "precio" => 1500.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 3,
        "categoria_nombre" => "Guisos",
        "ingredientes" => "Lentejas, chorizo, verduras",
        "tiempo_preparacion" => 35,
        "disponible" => true
    ],
    [
        "id" => 12,
        "nombre" => "Locro",
        "descripcion" => "Locro tradicional argentino",
        "precio" => 2000.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 3,
        "categoria_nombre" => "Guisos",
        "ingredientes" => "Maíz, porotos, carne, chorizo",
        "tiempo_preparacion" => 40,
        "disponible" => true
    ],
    [
        "id" => 13,
        "nombre" => "Guiso de mondongo",
        "descripcion" => "Mondongo con garbanzos y papas",
        "precio" => 1800.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 3,
        "categoria_nombre" => "Guisos",
        "ingredientes" => "Mondongo, garbanzos, papa",
        "tiempo_preparacion" => 45,
        "disponible" => true
    ],
    [
        "id" => 14,
        "nombre" => "Carbonada",
        "descripcion" => "Guiso de carne con frutas",
        "precio" => 1700.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 3,
        "categoria_nombre" => "Guisos",
        "ingredientes" => "Carne, durazno, papa, choclo",
        "tiempo_preparacion" => 35,
        "disponible" => true
    ],
    [
        "id" => 15,
        "nombre" => "Guiso carrero",
        "descripcion" => "Guiso de arroz con carne",
        "precio" => 1600.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 3,
        "categoria_nombre" => "Guisos",
        "ingredientes" => "Arroz, carne, verduras",
        "tiempo_preparacion" => 30,
        "disponible" => true
    ],

    // TARTAS (categoría 4)
    [
        "id" => 16,
        "nombre" => "Tarta de jamón y queso",
        "descripcion" => "Tarta casera con jamón y queso",
        "precio" => 1800.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 4,
        "categoria_nombre" => "Tartas",
        "ingredientes" => "Masa, jamón, queso",
        "tiempo_preparacion" => 30,
        "disponible" => true
    ],
    [
        "id" => 17,
        "nombre" => "Tarta de verduras",
        "descripcion" => "Tarta de espinaca y acelga",
        "precio" => 1600.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 4,
        "categoria_nombre" => "Tartas",
        "ingredientes" => "Masa, espinaca, acelga, huevo",
        "tiempo_preparacion" => 30,
        "disponible" => true
    ],
    [
        "id" => 18,
        "nombre" => "Tarta de pollo",
        "descripcion" => "Tarta con pollo desmenuzado",
        "precio" => 1900.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 4,
        "categoria_nombre" => "Tartas",
        "ingredientes" => "Masa, pollo, cebolla",
        "tiempo_preparacion" => 30,
        "disponible" => true
    ],
    [
        "id" => 19,
        "nombre" => "Tarta de atún",
        "descripcion" => "Tarta con atún y vegetales",
        "precio" => 2000.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 4,
        "categoria_nombre" => "Tartas",
        "ingredientes" => "Masa, atún, tomate, cebolla",
        "tiempo_preparacion" => 30,
        "disponible" => true
    ],
    [
        "id" => 20,
        "nombre" => "Tarta caprese",
        "descripcion" => "Tarta de tomate, mozzarella y albahaca",
        "precio" => 2100.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 4,
        "categoria_nombre" => "Tartas",
        "ingredientes" => "Masa, tomate, mozzarella, albahaca",
        "tiempo_preparacion" => 30,
        "disponible" => true
    ],

    // EMPANADAS (categoría 5)
    [
        "id" => 21,
        "nombre" => "Empanadas de carne (docena)",
        "descripcion" => "Docena de empanadas de carne cortada a cuchillo",
        "precio" => 3000.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 5,
        "categoria_nombre" => "Empanadas",
        "ingredientes" => "Masa, carne, cebolla, especias",
        "tiempo_preparacion" => 25,
        "disponible" => true
    ],
    [
        "id" => 22,
        "nombre" => "Empanadas de pollo (docena)",
        "descripcion" => "Docena de empanadas de pollo con verduras",
        "precio" => 2800.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 5,
        "categoria_nombre" => "Empanadas",
        "ingredientes" => "Masa, pollo, cebolla, morrón",
        "tiempo_preparacion" => 25,
        "disponible" => true
    ],
    [
        "id" => 23,
        "nombre" => "Empanadas de jamón y queso (docena)",
        "descripcion" => "Docena de empanadas de jamón y queso",
        "precio" => 2700.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 5,
        "categoria_nombre" => "Empanadas",
        "ingredientes" => "Masa, jamón, queso",
        "tiempo_preparacion" => 25,
        "disponible" => true
    ],
    [
        "id" => 24,
        "nombre" => "Empanadas de verdura (docena)",
        "descripcion" => "Docena de empanadas de verdura",
        "precio" => 2500.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 5,
        "categoria_nombre" => "Empanadas",
        "ingredientes" => "Masa, acelga, cebolla, queso",
        "tiempo_preparacion" => 25,
        "disponible" => true
    ],
    [
        "id" => 25,
        "nombre" => "Empanadas árabes (docena)",
        "descripcion" => "Docena de empanadas de carne con limón",
        "precio" => 3200.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 5,
        "categoria_nombre" => "Empanadas",
        "ingredientes" => "Masa, carne, limón, especias",
        "tiempo_preparacion" => 25,
        "disponible" => true
    ],

    // POSTRES (categoría 6)
    [
        "id" => 26,
        "nombre" => "Flan casero",
        "descripcion" => "Flan casero con dulce de leche",
        "precio" => 800.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 6,
        "categoria_nombre" => "Postres",
        "ingredientes" => "Huevo, leche, azúcar",
        "tiempo_preparacion" => 5,
        "disponible" => true
    ],
    [
        "id" => 27,
        "nombre" => "Tiramisu",
        "descripcion" => "Postre italiano con café",
        "precio" => 1200.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 6,
        "categoria_nombre" => "Postres",
        "ingredientes" => "Mascarpone, café, vainillas",
        "tiempo_preparacion" => 5,
        "disponible" => true
    ],
    [
        "id" => 28,
        "nombre" => "Panqueques con dulce de leche",
        "descripcion" => "3 panqueques rellenos",
        "precio" => 900.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 6,
        "categoria_nombre" => "Postres",
        "ingredientes" => "Harina, huevo, dulce de leche",
        "tiempo_preparacion" => 10,
        "disponible" => true
    ],
    [
        "id" => 29,
        "nombre" => "Arroz con leche",
        "descripcion" => "Arroz con leche casero",
        "precio" => 700.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 6,
        "categoria_nombre" => "Postres",
        "ingredientes" => "Arroz, leche, azúcar, canela",
        "tiempo_preparacion" => 5,
        "disponible" => true
    ],
    [
        "id" => 30,
        "nombre" => "Ensalada de frutas",
        "descripcion" => "Ensalada de frutas frescas de estación",
        "precio" => 850.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 6,
        "categoria_nombre" => "Postres",
        "ingredientes" => "Frutas variadas",
        "tiempo_preparacion" => 5,
        "disponible" => true
    ],

    // BEBIDAS (categoría 7)
    [
        "id" => 31,
        "nombre" => "Coca Cola 2.25L",
        "descripcion" => "Gaseosa Coca Cola",
        "precio" => 1200.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 7,
        "categoria_nombre" => "Bebidas",
        "ingredientes" => "",
        "tiempo_preparacion" => 2,
        "disponible" => true
    ],
    [
        "id" => 32,
        "nombre" => "Sprite 2.25L",
        "descripcion" => "Gaseosa Sprite",
        "precio" => 1200.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 7,
        "categoria_nombre" => "Bebidas",
        "ingredientes" => "",
        "tiempo_preparacion" => 2,
        "disponible" => true
    ],
    [
        "id" => 33,
        "nombre" => "Fanta 2.25L",
        "descripcion" => "Gaseosa Fanta",
        "precio" => 1200.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 7,
        "categoria_nombre" => "Bebidas",
        "ingredientes" => "",
        "tiempo_preparacion" => 2,
        "disponible" => true
    ],
    [
        "id" => 34,
        "nombre" => "Agua mineral 2L",
        "descripcion" => "Agua mineral sin gas",
        "precio" => 600.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 7,
        "categoria_nombre" => "Bebidas",
        "ingredientes" => "",
        "tiempo_preparacion" => 2,
        "disponible" => true
    ],
    [
        "id" => 35,
        "nombre" => "Cerveza Quilmes 1L",
        "descripcion" => "Cerveza Quilmes (solo mayores de 18)",
        "precio" => 1000.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 7,
        "categoria_nombre" => "Bebidas",
        "ingredientes" => "",
        "tiempo_preparacion" => 2,
        "disponible" => true
    ],

    // EMBUTIDOS (categoría 8)
    [
        "id" => 36,
        "nombre" => "Jamón cocido (100g)",
        "descripcion" => "Jamón cocido premium",
        "precio" => 450.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 8,
        "categoria_nombre" => "Embutidos",
        "ingredientes" => "Cerdo",
        "tiempo_preparacion" => 2,
        "disponible" => true
    ],
    [
        "id" => 37,
        "nombre" => "Jamón crudo (100g)",
        "descripcion" => "Jamón crudo importado",
        "precio" => 850.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 8,
        "categoria_nombre" => "Embutidos",
        "ingredientes" => "Cerdo curado",
        "tiempo_preparacion" => 2,
        "disponible" => true
    ],
    [
        "id" => 38,
        "nombre" => "Salame (100g)",
        "descripcion" => "Salame tandilero",
        "precio" => 600.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 8,
        "categoria_nombre" => "Embutidos",
        "ingredientes" => "Carne de cerdo",
        "tiempo_preparacion" => 2,
        "disponible" => true
    ],
    [
        "id" => 39,
        "nombre" => "Queso provolone (100g)",
        "descripcion" => "Queso provolone",
        "precio" => 500.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 8,
        "categoria_nombre" => "Embutidos",
        "ingredientes" => "Leche de vaca",
        "tiempo_preparacion" => 2,
        "disponible" => true
    ],
    [
        "id" => 40,
        "nombre" => "Mortadela (100g)",
        "descripcion" => "Mortadela con aceitunas",
        "precio" => 350.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 8,
        "categoria_nombre" => "Embutidos",
        "ingredientes" => "Carne de cerdo",
        "tiempo_preparacion" => 2,
        "disponible" => true
    ],

    // COMIDAS RÁPIDAS (categoría 9)
    [
        "id" => 41,
        "nombre" => "Hamburguesa completa",
        "descripcion" => "Hamburguesa con todos los ingredientes",
        "precio" => 2000.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 9,
        "categoria_nombre" => "Comidas Rápidas",
        "ingredientes" => "Pan, carne, lechuga, tomate, queso",
        "tiempo_preparacion" => 15,
        "disponible" => true
    ],
    [
        "id" => 42,
        "nombre" => "Pancho completo",
        "descripcion" => "Pancho con papas fritas",
        "precio" => 1500.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 9,
        "categoria_nombre" => "Comidas Rápidas",
        "ingredientes" => "Pan, salchicha, salsas",
        "tiempo_preparacion" => 10,
        "disponible" => true
    ],
    [
        "id" => 43,
        "nombre" => "Pizza muzarella",
        "descripcion" => "Pizza casera con muzarella",
        "precio" => 2500.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 9,
        "categoria_nombre" => "Comidas Rápidas",
        "ingredientes" => "Masa, tomate, queso",
        "tiempo_preparacion" => 20,
        "disponible" => true
    ],
    [
        "id" => 44,
        "nombre" => "Sandwich de milanesa",
        "descripcion" => "Sandwich con milanesa y guarnición",
        "precio" => 1800.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 9,
        "categoria_nombre" => "Comidas Rápidas",
        "ingredientes" => "Pan, milanesa, lechuga, tomate",
        "tiempo_preparacion" => 15,
        "disponible" => true
    ],
    [
        "id" => 45,
        "nombre" => "Papas fritas grandes",
        "descripcion" => "Porción grande de papas fritas",
        "precio" => 1200.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 9,
        "categoria_nombre" => "Comidas Rápidas",
        "ingredientes" => "Papas",
        "tiempo_preparacion" => 10,
        "disponible" => true
    ],

    // OTROS (categoría 10)
    [
        "id" => 46,
        "nombre" => "Pollo al espiedo entero",
        "descripcion" => "Pollo al espiedo para llevar",
        "precio" => 3500.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 10,
        "categoria_nombre" => "Otros",
        "ingredientes" => "Pollo",
        "tiempo_preparacion" => 30,
        "disponible" => true
    ],
    [
        "id" => 47,
        "nombre" => "Costillas al horno",
        "descripcion" => "Costillas de cerdo al horno",
        "precio" => 4000.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 10,
        "categoria_nombre" => "Otros",
        "ingredientes" => "Costillas de cerdo",
        "tiempo_preparacion" => 40,
        "disponible" => true
    ],
    [
        "id" => 48,
        "nombre" => "Matambre a la pizza",
        "descripcion" => "Matambre con salsa y queso",
        "precio" => 3200.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 10,
        "categoria_nombre" => "Otros",
        "ingredientes" => "Matambre, salsa, queso",
        "tiempo_preparacion" => 35,
        "disponible" => true
    ],
    [
        "id" => 49,
        "nombre" => "Bondiola al horno",
        "descripcion" => "Bondiola de cerdo al horno",
        "precio" => 3800.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 10,
        "categoria_nombre" => "Otros",
        "ingredientes" => "Bondiola de cerdo",
        "tiempo_preparacion" => 45,
        "disponible" => true
    ],
    [
        "id" => 50,
        "nombre" => "Vacío a la parrilla",
        "descripcion" => "Vacío para 4 personas",
        "precio" => 5000.0,
        "imagen" => "hamburguesa.jpeg",
        "categoria_id" => 10,
        "categoria_nombre" => "Otros",
        "ingredientes" => "Carne vacuna",
        "tiempo_preparacion" => 35,
        "disponible" => true
    ]
];

echo json_encode($productos);
?>