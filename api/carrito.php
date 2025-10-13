<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Inicializar carrito en sesión si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$metodo = $_SERVER['REQUEST_METHOD'];
$input = file_get_contents('php://input');
$data = json_decode($input, true);

switch($metodo) {
    case 'GET':
        obtenerCarrito();
        break;
    case 'POST':
        if (isset($data['accion'])) {
            switch($data['accion']) {
                case 'agregar':
                    agregarProducto($data);
                    break;
                case 'modificar':
                    modificarCantidad($data);
                    break;
                case 'eliminar':
                    eliminarProducto($data);
                    break;
                case 'limpiar':
                    limpiarCarrito();
                    break;
                default:
                    echo json_encode(['error' => 'Acción no válida']);
            }
        } else {
            echo json_encode(['error' => 'No se especificó acción']);
        }
        break;
    default:
        echo json_encode(['error' => 'Método no permitido']);
}

function obtenerCarrito() {
    // Devolver carrito con información completa de productos
    $carritoCompleto = [];
    
    // Productos de ejemplo (en producción vendría de la BD)
    $productos = getProductos();
    
    foreach ($_SESSION['carrito'] as $item) {
        foreach ($productos as $producto) {
            if ($producto['id'] == $item['id']) {
                $carritoCompleto[] = [
                    'id' => $producto['id'],
                    'nombre' => $producto['nombre'],
                    'descripcion' => $producto['descripcion'],
                    'precio' => $producto['precio'],
                    'imagen' => $producto['imagen'],
                    'ingredientes' => $producto['ingredientes'] ?? '',
                    'tiempo_preparacion' => $producto['tiempo_preparacion'] ?? 20,
                    'cantidad' => $item['cantidad'],
                    'precio_total' => $producto['precio'] * $item['cantidad'],
                    'disponible' => $producto['disponible']
                ];
                break;
            }
        }
    }
    
    echo json_encode($carritoCompleto);
}

function agregarProducto($data) {
    $productoId = $data['producto_id'];
    $cantidad = $data['cantidad'] ?? 1;
    
    // Verificar si el producto ya existe en el carrito
    $encontrado = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['id'] == $productoId) {
            $item['cantidad'] += $cantidad;
            $encontrado = true;
            break;
        }
    }
    
    if (!$encontrado) {
        $_SESSION['carrito'][] = [
            'id' => $productoId,
            'cantidad' => $cantidad
        ];
    }
    
    echo json_encode([
        'success' => true, 
        'mensaje' => 'Producto agregado al carrito',
        'carrito_count' => count($_SESSION['carrito'])
    ]);
}

function modificarCantidad($data) {
    $productoId = $data['producto_id'];
    $cantidad = $data['cantidad'];
    
    if ($cantidad <= 0) {
        eliminarProducto($data);
        return;
    }
    
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['id'] == $productoId) {
            $item['cantidad'] = $cantidad;
            echo json_encode([
                'success' => true,
                'mensaje' => 'Cantidad actualizada'
            ]);
            return;
        }
    }
    
    echo json_encode(['error' => 'Producto no encontrado en el carrito']);
}

function eliminarProducto($data) {
    $productoId = $data['producto_id'];
    
    $_SESSION['carrito'] = array_filter($_SESSION['carrito'], function($item) use ($productoId) {
        return $item['id'] != $productoId;
    });
    
    // Reindexar array
    $_SESSION['carrito'] = array_values($_SESSION['carrito']);
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Producto eliminado del carrito',
        'carrito_count' => count($_SESSION['carrito'])
    ]);
}

function limpiarCarrito() {
    $_SESSION['carrito'] = [];
    echo json_encode([
        'success' => true,
        'mensaje' => 'Carrito limpiado',
        'carrito_count' => 0
    ]);
}

function getProductos() {
    // Productos completos - en producción esto vendría de la BD
    return [
        ["id" => 1, "nombre" => "Milanesa con papas fritas", "descripcion" => "Milanesa de carne con papas", "precio" => 2500.0, "imagen" => "hamburguesa.jpeg", "ingredientes" => "Carne, pan rallado, papas", "tiempo_preparacion" => 25, "disponible" => true],
        ["id" => 2, "nombre" => "Milanesa napolitana", "descripcion" => "Milanesa con jamón y queso", "precio" => 2800.0, "imagen" => "hamburguesa.jpeg", "ingredientes" => "Carne, jamón, queso", "tiempo_preparacion" => 30, "disponible" => true],
        ["id" => 3, "nombre" => "Suprema de pollo", "descripcion" => "Pechuga de pollo con ensalada", "precio" => 2300.0, "imagen" => "hamburguesa.jpeg", "ingredientes" => "Pollo, lechuga, tomate", "tiempo_preparacion" => 25, "disponible" => true],
        ["id" => 21, "nombre" => "Empanadas de carne (docena)", "descripcion" => "Docena de empanadas de carne", "precio" => 3000.0, "imagen" => "hamburguesa.jpeg", "ingredientes" => "Masa, carne, cebolla", "tiempo_preparacion" => 25, "disponible" => true],
        ["id" => 16, "nombre" => "Tarta de jamón y queso", "descripcion" => "Tarta casera", "precio" => 1800.0, "imagen" => "hamburguesa.jpeg", "ingredientes" => "Masa, jamón, queso", "tiempo_preparacion" => 30, "disponible" => true],
        // Agregar más productos según sea necesario
    ];
}
?>