<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$metodo = $_SERVER['REQUEST_METHOD'];
$input = file_get_contents('php://input');
$data = json_decode($input, true);

switch($metodo) {
    case 'GET':
        obtenerDatosCajero();
        break;
    case 'POST':
        if (isset($data['accion'])) {
            switch($data['accion']) {
                case 'buscar_cliente':
                    buscarCliente($data);
                    break;
                case 'registrar_cliente':
                    registrarCliente($data);
                    break;
                case 'crear_pedido':
                    crearPedido($data);
                    break;
                default:
                    echo json_encode(['error' => 'Acción no válida']);
            }
        } else {
            obtenerDatosCajero();
        }
        break;
    default:
        echo json_encode(['error' => 'Método no permitido']);
}

function obtenerDatosCajero() {
    $response = [
        "productos" => getProductos(),
        "clientes" => getClientes(),
        "pedidos_pendientes" => getPedidosPendientes(),
        "estadisticas_dia" => [
            "total_ventas" => 45000,
            "cantidad_pedidos" => 15,
            "pedidos_completados" => 10,
            "pedidos_en_proceso" => 3,
            "pedidos_cancelados" => 2
        ],
        "horario_apertura" => [
            "turno1_inicio" => "11:00",
            "turno1_fin" => "15:00",
            "turno2_inicio" => "19:00",
            "turno2_fin" => "23:00"
        ],
        "metodos_pago" => [
            "Mercado Pago",
            "Cuenta DNI",
            "Efectivo (solo clientes VIP)"
        ]
    ];
    
    echo json_encode($response);
}

function buscarCliente($data) {
    $busqueda = $data['busqueda'] ?? '';
    $clientes = getClientes();
    
    $resultados = array_filter($clientes, function($cliente) use ($busqueda) {
        $nombreCompleto = $cliente['nombre'] . ' ' . $cliente['apellido'];
        return stripos($nombreCompleto, $busqueda) !== false || 
               stripos($cliente['telefono'], $busqueda) !== false ||
               stripos($cliente['email'], $busqueda) !== false;
    });
    
    echo json_encode([
        'success' => true,
        'clientes' => array_values($resultados),
        'total' => count($resultados)
    ]);
}

function registrarCliente($data) {
    // En producción, aquí se guardaría en la base de datos
    $nuevoCliente = [
        'id' => rand(100, 9999),
        'nombre' => $data['nombre'],
        'apellido' => $data['apellido'],
        'email' => $data['email'],
        'telefono' => $data['telefono'],
        'direccion' => $data['direccion'],
        'activo' => true,
        'fecha_registro' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode([
        'success' => true,
        'cliente' => $nuevoCliente,
        'mensaje' => 'Cliente registrado exitosamente'
    ]);
}

function crearPedido($data) {
    $numeroPedido = 'P' . date('YmdHis') . rand(100, 999);
    
    $pedido = [
        'pedido_id' => rand(10000, 99999),
        'numero_pedido' => $numeroPedido,
        'usuario_id' => $data['usuario_id'],
        'tipo_pedido' => $data['tipo_pedido'],
        'fecha_entrega_programada' => $data['fecha_entrega'] ?? null,
        'direccion_entrega' => $data['direccion'],
        'telefono_contacto' => $data['telefono'],
        'metodo_pago' => $data['metodo_pago'],
        'estado' => 'confirmado',
        'subtotal' => $data['subtotal'],
        'precio_delivery' => $data['costo_delivery'],
        'total' => $data['total'],
        'items' => $data['items'],
        'comentarios' => $data['comentarios'] ?? '',
        'fecha_creacion' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode([
        'success' => true,
        'pedido' => $pedido,
        'mensaje' => 'Pedido creado exitosamente'
    ]);
}

function getProductos() {
    // Retornar todos los productos con categorías
    return [
        // MINUTAS
        ["id" => 1, "nombre" => "Milanesa con papas fritas", "descripcion" => "Milanesa de carne con papas", "precio" => 2500.0, "categoria_id" => 1, "categoria_nombre" => "Minutas", "disponible" => true, "tiempo_preparacion" => 25],
        ["id" => 2, "nombre" => "Milanesa napolitana", "descripcion" => "Milanesa con jamón y queso", "precio" => 2800.0, "categoria_id" => 1, "categoria_nombre" => "Minutas", "disponible" => true, "tiempo_preparacion" => 30],
        ["id" => 3, "nombre" => "Suprema de pollo", "descripcion" => "Pechuga de pollo", "precio" => 2300.0, "categoria_id" => 1, "categoria_nombre" => "Minutas", "disponible" => true, "tiempo_preparacion" => 25],
        ["id" => 4, "nombre" => "Bife de chorizo", "descripcion" => "Bife de 300g", "precio" => 3500.0, "categoria_id" => 1, "categoria_nombre" => "Minutas", "disponible" => true, "tiempo_preparacion" => 20],
        ["id" => 5, "nombre" => "Lomito completo", "descripcion" => "Lomito con todo", "precio" => 3200.0, "categoria_id" => 1, "categoria_nombre" => "Minutas", "disponible" => true, "tiempo_preparacion" => 30],
        
        // PASTAS
        ["id" => 6, "nombre" => "Ravioles de ricota", "descripcion" => "Ravioles caseros", "precio" => 1800.0, "categoria_id" => 2, "categoria_nombre" => "Pastas", "disponible" => true, "tiempo_preparacion" => 15],
        ["id" => 7, "nombre" => "Sorrentinos", "descripcion" => "Sorrentinos con fileto", "precio" => 2000.0, "categoria_id" => 2, "categoria_nombre" => "Pastas", "disponible" => true, "tiempo_preparacion" => 15],
        ["id" => 8, "nombre" => "Ñoquis de papa", "descripcion" => "Ñoquis caseros", "precio" => 1700.0, "categoria_id" => 2, "categoria_nombre" => "Pastas", "disponible" => true, "tiempo_preparacion" => 20],
        ["id" => 9, "nombre" => "Lasagna de carne", "descripcion" => "Lasagna con bechamel", "precio" => 2200.0, "categoria_id" => 2, "categoria_nombre" => "Pastas", "disponible" => true, "tiempo_preparacion" => 25],
        ["id" => 10, "nombre" => "Canelones de verdura", "descripcion" => "Canelones de espinaca", "precio" => 1900.0, "categoria_id" => 2, "categoria_nombre" => "Pastas", "disponible" => true, "tiempo_preparacion" => 20],
        
        // GUISOS
        ["id" => 11, "nombre" => "Guiso de lentejas", "descripcion" => "Guiso casero", "precio" => 1500.0, "categoria_id" => 3, "categoria_nombre" => "Guisos", "disponible" => true, "tiempo_preparacion" => 35],
        ["id" => 12, "nombre" => "Locro", "descripcion" => "Locro tradicional", "precio" => 2000.0, "categoria_id" => 3, "categoria_nombre" => "Guisos", "disponible" => true, "tiempo_preparacion" => 40],
        ["id" => 13, "nombre" => "Guiso de mondongo", "descripcion" => "Mondongo con garbanzos", "precio" => 1800.0, "categoria_id" => 3, "categoria_nombre" => "Guisos", "disponible" => true, "tiempo_preparacion" => 45],
        ["id" => 14, "nombre" => "Carbonada", "descripcion" => "Guiso con frutas", "precio" => 1700.0, "categoria_id" => 3, "categoria_nombre" => "Guisos", "disponible" => true, "tiempo_preparacion" => 35],
        
        // TARTAS
        ["id" => 16, "nombre" => "Tarta de jamón y queso", "descripcion" => "Tarta casera", "precio" => 1800.0, "categoria_id" => 4, "categoria_nombre" => "Tartas", "disponible" => true, "tiempo_preparacion" => 30],
        ["id" => 17, "nombre" => "Tarta de verduras", "descripcion" => "Tarta de espinaca", "precio" => 1600.0, "categoria_id" => 4, "categoria_nombre" => "Tartas", "disponible" => true, "tiempo_preparacion" => 30],
        ["id" => 18, "nombre" => "Tarta de pollo", "descripcion" => "Tarta con pollo", "precio" => 1900.0, "categoria_id" => 4, "categoria_nombre" => "Tartas", "disponible" => true, "tiempo_preparacion" => 30],
        
        // EMPANADAS
        ["id" => 21, "nombre" => "Empanadas de carne (docena)", "descripcion" => "12 empanadas de carne", "precio" => 3000.0, "categoria_id" => 5, "categoria_nombre" => "Empanadas", "disponible" => true, "tiempo_preparacion" => 25],
        ["id" => 22, "nombre" => "Empanadas de pollo (docena)", "descripcion" => "12 empanadas de pollo", "precio" => 2800.0, "categoria_id" => 5, "categoria_nombre" => "Empanadas", "disponible" => true, "tiempo_preparacion" => 25],
        ["id" => 23, "nombre" => "Empanadas de J&Q (docena)", "descripcion" => "12 empanadas de jamón y queso", "precio" => 2700.0, "categoria_id" => 5, "categoria_nombre" => "Empanadas", "disponible" => true, "tiempo_preparacion" => 25],
        
        // POSTRES
        ["id" => 26, "nombre" => "Flan casero", "descripcion" => "Flan con dulce de leche", "precio" => 800.0, "categoria_id" => 6, "categoria_nombre" => "Postres", "disponible" => true, "tiempo_preparacion" => 5],
        ["id" => 27, "nombre" => "Tiramisu", "descripcion" => "Postre italiano", "precio" => 1200.0, "categoria_id" => 6, "categoria_nombre" => "Postres", "disponible" => true, "tiempo_preparacion" => 5],
        ["id" => 28, "nombre" => "Panqueques con DDL", "descripcion" => "3 panqueques", "precio" => 900.0, "categoria_id" => 6, "categoria_nombre" => "Postres", "disponible" => true, "tiempo_preparacion" => 10],
        
        // BEBIDAS
        ["id" => 31, "nombre" => "Coca Cola 2.25L", "descripcion" => "Gaseosa", "precio" => 1200.0, "categoria_id" => 7, "categoria_nombre" => "Bebidas", "disponible" => true, "tiempo_preparacion" => 2],
        ["id" => 32, "nombre" => "Sprite 2.25L", "descripcion" => "Gaseosa", "precio" => 1200.0, "categoria_id" => 7, "categoria_nombre" => "Bebidas", "disponible" => true, "tiempo_preparacion" => 2],
        ["id" => 34, "nombre" => "Agua mineral 2L", "descripcion" => "Agua sin gas", "precio" => 600.0, "categoria_id" => 7, "categoria_nombre" => "Bebidas", "disponible" => true, "tiempo_preparacion" => 2],
        
        // EMBUTIDOS
        ["id" => 36, "nombre" => "Jamón cocido (100g)", "descripcion" => "Jamón premium", "precio" => 450.0, "categoria_id" => 8, "categoria_nombre" => "Embutidos", "disponible" => true, "tiempo_preparacion" => 2],
        ["id" => 37, "nombre" => "Jamón crudo (100g)", "descripcion" => "Jamón importado", "precio" => 850.0, "categoria_id" => 8, "categoria_nombre" => "Embutidos", "disponible" => true, "tiempo_preparacion" => 2],
        ["id" => 38, "nombre" => "Salame (100g)", "descripcion" => "Salame tandilero", "precio" => 600.0, "categoria_id" => 8, "categoria_nombre" => "Embutidos", "disponible" => true, "tiempo_preparacion" => 2],
        
        // COMIDAS RÁPIDAS
        ["id" => 41, "nombre" => "Hamburguesa completa", "descripcion" => "Hamburguesa con todo", "precio" => 2000.0, "categoria_id" => 9, "categoria_nombre" => "Comidas Rápidas", "disponible" => true, "tiempo_preparacion" => 15],
        ["id" => 42, "nombre" => "Pancho completo", "descripcion" => "Pancho con papas", "precio" => 1500.0, "categoria_id" => 9, "categoria_nombre" => "Comidas Rápidas", "disponible" => true, "tiempo_preparacion" => 10],
        ["id" => 43, "nombre" => "Pizza muzarella", "descripcion" => "Pizza casera", "precio" => 2500.0, "categoria_id" => 9, "categoria_nombre" => "Comidas Rápidas", "disponible" => true, "tiempo_preparacion" => 20],
        
        // OTROS
        ["id" => 46, "nombre" => "Pollo al espiedo entero", "descripcion" => "Pollo completo", "precio" => 3500.0, "categoria_id" => 10, "categoria_nombre" => "Otros", "disponible" => true, "tiempo_preparacion" => 30],
        ["id" => 47, "nombre" => "Costillas al horno", "descripcion" => "Costillas de cerdo", "precio" => 4000.0, "categoria_id" => 10, "categoria_nombre" => "Otros", "disponible" => true, "tiempo_preparacion" => 40],
        ["id" => 48, "nombre" => "Matambre a la pizza", "descripcion" => "Matambre con salsa", "precio" => 3200.0, "categoria_id" => 10, "categoria_nombre" => "Otros", "disponible" => true, "tiempo_preparacion" => 35]
    ];
}

function getClientes() {
    return [
        [
            'id' => 1,
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan.perez@example.com',
            'telefono' => '3511234567',
            'direccion' => 'Av. Colón 1234, Centro',
            'activo' => true
        ],
        [
            'id' => 2,
            'nombre' => 'María',
            'apellido' => 'González',
            'email' => 'maria.gonzalez@example.com',
            'telefono' => '3519876543',
            'direccion' => 'Calle Lima 567, Nueva Córdoba',
            'activo' => true
        ],
        [
            'id' => 3,
            'nombre' => 'Carlos',
            'apellido' => 'Rodríguez',
            'email' => 'carlos.rodriguez@example.com',
            'telefono' => '3515555555',
            'direccion' => 'Av. Vélez Sarsfield 2345, Güemes',
            'activo' => true
        ],
        [
            'id' => 4,
            'nombre' => 'Ana',
            'apellido' => 'Martínez',
            'email' => 'ana.martinez@example.com',
            'telefono' => '3517777777',
            'direccion' => 'Rondeau 890, Centro',
            'activo' => true
        ],
        [
            'id' => 5,
            'nombre' => 'Pedro',
            'apellido' => 'López',
            'email' => 'pedro.lopez@example.com',
            'telefono' => '3513333333',
            'direccion' => 'Chacabuco 456, Alberdi',
            'activo' => true
        ]
    ];
}

function getPedidosPendientes() {
    return [
        [
            'pedido_id' => 12345,
            'numero_pedido' => 'P20231015191500',
            'cliente_nombre' => 'Juan Pérez',
            'telefono' => '3511234567',
            'hora_pedido' => '19:15:00',
            'estado' => 'confirmado',
            'total' => 10300
        ],
        [
            'pedido_id' => 12346,
            'numero_pedido' => 'P20231015192000',
            'cliente_nombre' => 'María González',
            'telefono' => '3519876543',
            'hora_pedido' => '19:20:00',
            'estado' => 'en_preparacion',
            'total' => 8500
        ],
        [
            'pedido_id' => 12347,
            'numero_pedido' => 'P20231015192500',
            'cliente_nombre' => 'Carlos Rodríguez',
            'telefono' => '3515555555',
            'hora_pedido' => '19:25:00',
            'estado' => 'listo',
            'total' => 6200
        ]
    ];
}
?>