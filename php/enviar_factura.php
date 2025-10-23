<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

function enviarFactura($pedido_data) {
    $mail = new PHPMailer(true);
    
    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'rotiseriaelbuengusto3966@gmail.com'; // TU EMAIL DE GMAIL
        $mail->Password = 'olxuogbrwtngzwed'; // CONTRASEÑA DE APLICACIÓN
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        
        // Configuración del email
        $mail->setFrom('tuempresa@gmail.com', 'El Buen Gusto');
        $mail->addAddress($pedido_data['email'], $pedido_data['nombre']);
        
        // Contenido
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Factura - Pedido #{$pedido_data['numero_pedido']}";
        
        // HTML del email
        $mail->Body = generarHTMLFactura($pedido_data);
        
        $mail->send();
        return ['success' => true, 'message' => 'Factura enviada correctamente'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Error al enviar: {$mail->ErrorInfo}"];
    }
}

function generarHTMLFactura($data) {
    $productos_html = '';
    foreach ($data['productos'] as $producto) {
        $subtotal = $producto['precio'] * $producto['cantidad'];
        $productos_html .= "
        <tr>
            <td style='padding: 10px; border-bottom: 1px solid #ddd;'>{$producto['nombre']}</td>
            <td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>{$producto['cantidad']}</td>
            <td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: right;'>$" . number_format($producto['precio'], 2) . "</td>
            <td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: right;'>$" . number_format($subtotal, 2) . "</td>
        </tr>";
    }
    
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f5f5f5; }
            .container { max-width: 600px; margin: 20px auto; background-color: white; border-radius: 10px; overflow: hidden; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
            .header { background-color: #C81E2D; color: white; padding: 30px; text-align: center; }
            .content { padding: 30px; }
            .info-box { background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { background-color: #503214; color: white; padding: 12px; text-align: left; }
            .total-row { background-color: #f0f0f0; font-weight: bold; }
            .footer { background-color: #503214; color: #F5EBD2; padding: 20px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🍽️ El Buen Gusto</h1>
                <p>Factura de Pedido</p>
            </div>
            
            <div class='content'>
                <div class='info-box'>
                    <h2 style='color: #C81E2D; margin-top: 0;'>Pedido #{$data['numero_pedido']}</h2>
                    <p><strong>Cliente:</strong> {$data['nombre']}</p>
                    <p><strong>Email:</strong> {$data['email']}</p>
                    <p><strong>Teléfono:</strong> {$data['telefono']}</p>
                    <p><strong>Dirección:</strong> {$data['direccion']}</p>
                    <p><strong>Tipo:</strong> " . ucfirst($data['tipo_pedido']) . "</p>
                    <p><strong>Método de pago:</strong> " . ucfirst($data['metodo_pago']) . "</p>
                </div>
                
                <h3 style='color: #503214;'>Detalle del Pedido</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th style='text-align: center;'>Cant.</th>
                            <th style='text-align: right;'>Precio</th>
                            <th style='text-align: right;'>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$productos_html}
                        <tr>
                            <td colspan='3' style='padding: 10px; text-align: right;'><strong>Subtotal:</strong></td>
                            <td style='padding: 10px; text-align: right;'>$" . number_format($data['subtotal'], 2) . "</td>
                        </tr>
                        <tr>
                            <td colspan='3' style='padding: 10px; text-align: right;'><strong>Delivery:</strong></td>
                            <td style='padding: 10px; text-align: right;'>$" . number_format($data['precio_delivery'], 2) . "</td>
                        </tr>
                        <tr class='total-row'>
                            <td colspan='3' style='padding: 15px; text-align: right; font-size: 1.2em;'><strong>TOTAL:</strong></td>
                            <td style='padding: 15px; text-align: right; font-size: 1.2em; color: #C81E2D;'><strong>$" . number_format($data['total'], 2) . "</strong></td>
                        </tr>
                    </tbody>
                </table>
                
                " . ($data['comentarios'] ? "<div style='margin-top: 20px; padding: 15px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 5px;'><strong>Comentarios:</strong> {$data['comentarios']}</div>" : "") . "
            </div>
            
            <div class='footer'>
                <p><strong>¡Gracias por tu compra!</strong></p>
                <p>El Buen Gusto - Cerrito 3966, Buenos Aires</p>
                <p>📞 +54 11 6216-5019 | ✉️ contacto@elbuengusto.com</p>
            </div>
        </div>
    </body>
    </html>";
    
    return $html;
}
?>