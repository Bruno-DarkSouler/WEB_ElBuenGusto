<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (!$data) {
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

try {
    // Generar número de pedido
    $numeroPedido = 'P' . date('YmdHis') . rand(100, 999);
    $pedidoId = rand(10000, 99999);
    
    // Calcular tiempo estimado basado en los productos
    $tiempoEstimado = 30;
    if (isset($data['items']) && count($data['items']) > 0) {
        $tiempoMax = 0;
        foreach ($data['items'] as $item) {
            $tiempoMax = max($tiempoMax, 20); // Tiempo base por producto
        }
        $tiempoEstimado = $tiempoMax + (count($data['items']) * 5);
    }
    
    // Preparar respuesta
    $response = [
        'estado' => 'confirmado',
        'pedido_id' => $pedidoId,
        'numero_pedido' => $numeroPedido,
        'cliente' => [
            'nombre' => $data['cliente']['nombre'] . ' ' . $data['cliente']['apellido'],
            'telefono' => $data['cliente']['telefono'],
            'email' => $data['cliente']['email'],
            'direccion' => $data['cliente']['direccion']
        ],
        'tipo_pedido' => $data['tipo_pedido'],
        'fecha_hora' => $data['fecha_entrega'] ?? date('Y-m-d H:i:s'),
        'metodo_pago' => $data['metodo_pago'],
        'tiempo_estimado' => $tiempoEstimado . ' minutos',
        'items' => $data['items'],
        'subtotal' => $data['subtotal'],
        'costo_envio' => $data['costo_envio'],
        'total' => $data['total'],
        'comentarios' => $data['comentarios'] ?? '',
        'mensaje' => '¡Pedido confirmado exitosamente! Recibirás una factura en tu email.',
        'horarios_atencion' => [
            'turno1' => '11:00 - 15:00',
            'turno2' => '19:00 - 23:00'
        ]
    ];
    
    // En producción, aquí se guardaría en la base de datos
    // guardarPedidoEnBD($response);
    
    // Enviar email (simulado)
    // enviarEmailConfirmacion($data['cliente']['email'], $response);
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Error al procesar el pedido: ' . $e->getMessage()
    ]);
}

function guardarPedidoEnBD($pedido) {
    // Aquí implementarías la lógica para guardar en la base de datos
    return true;
}

function enviarEmailConfirmacion($email, $pedido) {
    // Aquí implementarías el envío de email con PHPMailer
    return true;
}
?>