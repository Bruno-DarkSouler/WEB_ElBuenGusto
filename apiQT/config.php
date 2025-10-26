<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'elbuengusto');
define('DB_PASS', 'Rotiseria2025');
define('DB_NAME', 'elbuengusto_elbuengusto');

// Horarios de atención
define('HORA_APERTURA_1', '11:00');
define('HORA_CIERRE_1', '15:00');
define('HORA_APERTURA_2', '19:00');
define('HORA_CIERRE_2', '23:00');

// Configuración de delivery
define('PRECIO_DELIVERY_ZONA1', 200);  // Hasta 1.5km
define('PRECIO_DELIVERY_ZONA2', 350);  // 1.5-3km
define('PRECIO_DELIVERY_ZONA3', 500);  // 3-5km
define('PRECIO_DELIVERY_ZONA4', 700);  // 5-7km
define('PRECIO_DELIVERY_ZONA5', 1000); // 7km+

// Configuración de pedidos
define('TIEMPO_CANCELACION_INMEDIATO', 5); // minutos
define('DIAS_ADELANTO_PROGRAMADO', 7);
define('EDAD_MINIMA', 16);
define('EDAD_ALCOHOL', 18);

// Dirección del local
define('DIRECCION_SUCURSAL', 'Cerrito 3966, Córdoba');

// CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejo de sesiones
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>