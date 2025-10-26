<?php
    //mail.elbuengusto.shop
    //465
    //pedidos@elbuengusto.shop
    //rotiseria123

    // Cargar el autoloader de Composer para incluir PHPMailer
    require '../vendor/autoload.php';

    // Importar las clases de PHPMailer en el espacio de nombres global
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    // --- CONFIGURACIÓN DE TU SERVIDOR Y CUENTA DE CORREO ---
    // Reemplaza estos valores con la información que obtuviste de cPanel.
    $HOST_SMTP      = 'mail.elbuengusto.shop';   // El servidor SMTP (ej. mail.tudominio.com)
    $USUARIO_SMTP   = 'pedidos@elbuengusto.shop'; // El nombre de usuario completo (ej. pedidos@tudominio.com)
    $PASSWORD_SMTP  = 'rotiseria123';   // La contraseña de la cuenta de correo
    $PUERTO_SMTP    = 465;                    // El puerto SMTP (465 para SSL, 587 para TLS)
    $ENCRIPTACION   = PHPMailer::ENCRYPTION_SMTPS; // O PHPMailer::ENCRYPTION_STARTTLS para el puerto 587

    // --- CONFIGURACIÓN DEL EMAIL DE PRUEBA ---
    $DESTINATARIO   = 'brunofornasar@gmail.com'; // Tu correo personal
    $NOMBRE_REMITENTE = 'Tu App de Pedidos';
    $ASUNTO         = 'Prueba de Envío con PHPMailer desde Cpanel';
    $CUERPO_HTML    = '<h1>Hola!</h1><p>Este es un correo de prueba enviado exitosamente usando PHPMailer con configuración SMTP de cPanel.</p>';
    $CUERPO_TEXTO   = 'Hola! Este es un correo de prueba enviado exitosamente usando PHPMailer con configuración SMTP de cPanel.';
    // --------------------------------------------------------

    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor (Server settings)
        $mail->isSMTP();                                            // Usar SMTP
        $mail->Host       = $HOST_SMTP;                             // Servidor SMTP
        $mail->SMTPAuth   = true;                                   // Habilitar autenticación SMTP
        $mail->Username   = $USUARIO_SMTP;                          // Usuario SMTP
        $mail->Password   = $PASSWORD_SMTP;                         // Contraseña SMTP
        $mail->SMTPSecure = $ENCRIPTACION;                          // Encriptación TLS o SSL
        $mail->Port       = $PUERTO_SMTP;                           // Puerto a usar

        // Remitente (Recipients)
        $mail->setFrom($USUARIO_SMTP, $NOMBRE_REMITENTE);
        $mail->addAddress($DESTINATARIO);                           // Añadir un destinatario

        // Contenido (Content)
        $mail->isHTML(true);                                        // Establecer formato de email a HTML
        $mail->Subject = $ASUNTO;
        $mail->Body    = $CUERPO_HTML;
        $mail->AltBody = $CUERPO_TEXTO;                             // Texto plano para clientes sin soporte HTML
        
        // Configuración opcional para caracteres especiales (ej. acentos, ñ)
        $mail->CharSet = 'UTF-8';

        $mail->send();
        echo 'El mensaje se ha enviado correctamente.';
    } catch (Exception $e) {
        echo "El mensaje no pudo ser enviado. Error de PHPMailer: {$mail->ErrorInfo}";
    }

?>
