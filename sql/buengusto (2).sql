-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-10-2025 a las 21:09:15
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `buengusto`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificaciones`
--

CREATE TABLE `calificaciones` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `calificacion_comida` int(11) NOT NULL COMMENT 'Del 1 al 5',
  `calificacion_delivery` int(11) NOT NULL COMMENT 'Del 1 al 5',
  `comentario` text DEFAULT NULL,
  `repartidor_id` int(11) DEFAULT NULL COMMENT 'Para calificar al repartidor',
  `fecha_calificacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Calificaciones separadas para comida y delivery';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Categorías: Minutas, Pastas, Guisos, Bebidas';

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `activa`) VALUES
(1, 'Minutas', 'Comidas rápidas y platos individuales', 1),
(2, 'Pastas', 'Pastas frescas y caseras', 1),
(3, 'Guisos', 'Guisos tradicionales', 1),
(4, 'Tartas', 'Tartas saladas', 1),
(5, 'Empanadas', 'Empanadas de diferentes sabores', 1),
(6, 'Postres', 'Postres caseros', 1),
(7, 'Bebidas', 'Bebidas y gaseosas', 1),
(8, 'Embutidos', 'Fiambres y embutidos por peso', 1),
(9, 'Comidas Rápidas', 'Hamburguesas, panchos, etc', 1),
(10, 'Otros', 'Otros productos', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `condimentos`
--

CREATE TABLE `condimentos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `tipo` enum('sal','salsa','especias','otros') DEFAULT 'otros',
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Condimentos disponibles: sal, salsas, especias';

--
-- Volcado de datos para la tabla `condimentos`
--

INSERT INTO `condimentos` (`id`, `nombre`, `tipo`, `activo`) VALUES
(1, 'Sal', 'sal', 1),
(2, 'Ketchup', 'salsa', 1),
(3, 'Mostaza', 'salsa', 1),
(4, 'Mayonesa', 'salsa', 1),
(5, 'Chimichurri', 'salsa', 1),
(6, 'Salsa Golf', 'salsa', 1),
(7, 'Orégano', 'especias', 1),
(8, 'Pimienta', 'especias', 1),
(9, 'Ají Molido', 'especias', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `clave` varchar(50) NOT NULL,
  `valor` text NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Configuración del sistema';

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `clave`, `valor`, `descripcion`, `fecha_modificacion`) VALUES
(1, 'nombre_local', 'El Buen Gusto', 'Nombre del local', '2025-09-30 19:19:01'),
(2, 'direccion_local', 'Av. Corrientes 1234, CABA', 'Dirección del local', '2025-09-30 19:19:01'),
(3, 'telefono_local', '+54 11 1234-5678', 'Teléfono principal', '2025-09-30 19:19:01'),
(4, 'email_local', 'info@elbuengusto.com', 'Email de contacto', '2025-09-30 19:19:01'),
(5, 'horario_apertura', '11:00', 'Hora de apertura', '2025-09-30 19:19:01'),
(6, 'horario_cierre', '23:00', 'Hora de cierre', '2025-09-30 19:19:01'),
(7, 'horario_apertura_noche', '19:00', 'Hora de apertura turno noche', '2025-09-30 19:19:01'),
(8, 'horario_cierre_noche', '23:00', 'Hora de cierre turno noche', '2025-09-30 19:19:01'),
(9, 'dias_laborables', 'Lunes,Martes,Miércoles,Jueves,Viernes,Sábado', 'Días que abre el local', '2025-09-30 19:19:01'),
(10, 'metodo_pago_efectivo', '{\"activo\":1,\"comision\":0}', 'Configuración pago en efectivo', '2025-09-30 19:19:01'),
(11, 'metodo_pago_digital', '{\"activo\":1,\"comision\":3.2}', 'Configuración pago digital', '2025-09-30 19:19:01'),
(12, 'metodo_pago_tarjeta', '{\"activo\":1,\"comision\":2.8}', 'Configuración pago con tarjeta', '2025-09-30 19:19:01'),
(13, 'metodo_pago_transferencia', '{\"activo\":0,\"comision\":0}', 'Configuración pago por transferencia', '2025-09-30 19:19:01'),
(14, 'tiempo_cancelacion_inmediato', '5', 'Minutos para cancelar pedido inmediato', '2025-09-30 19:19:01'),
(15, 'edad_minima', '16', 'Edad mínima para usar el sistema', '2025-09-30 19:19:01'),
(16, 'edad_alcohol', '18', 'Edad mínima para comprar alcohol', '2025-09-30 19:19:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones_cliente`
--

CREATE TABLE `direcciones_cliente` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `alias` varchar(50) DEFAULT NULL COMMENT 'Casa, Trabajo, etc.',
  `direccion` text NOT NULL,
  `es_favorita` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Direcciones guardadas por el cliente';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item_condimentos`
--

CREATE TABLE `item_condimentos` (
  `id` int(11) NOT NULL,
  `pedido_item_id` int(11) NOT NULL,
  `condimento_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Condimentos seleccionados para cada item';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `numero_pedido` varchar(20) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo_pedido` enum('inmediato','programado') NOT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_entrega_programada` datetime DEFAULT NULL,
  `direccion_entrega` text NOT NULL,
  `telefono_contacto` varchar(20) NOT NULL,
  `metodo_pago` enum('digital','efectivo') DEFAULT 'efectivo',
  `estado` enum('pendiente','confirmado','en_preparacion','listo','en_camino','entregado','cancelado') DEFAULT 'pendiente',
  `subtotal` decimal(8,2) NOT NULL,
  `precio_delivery` decimal(6,2) DEFAULT 0.00,
  `total` decimal(8,2) NOT NULL,
  `zona_delivery_id` int(11) DEFAULT NULL,
  `repartidor_id` int(11) DEFAULT NULL,
  `cajero_id` int(11) DEFAULT NULL COMMENT 'Si fue tomado por cajero',
  `comentarios_cliente` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Pedidos con seguimiento completo del flujo';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_items`
--

CREATE TABLE `pedido_items` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(8,2) NOT NULL,
  `precio_total` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Items específicos de cada pedido';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(8,2) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `categoria_id` int(11) NOT NULL,
  `ingredientes` text DEFAULT NULL,
  `tiempo_preparacion` int(11) DEFAULT 20 COMMENT 'En minutos',
  `disponible` tinyint(1) DEFAULT 1,
  `valoracion_promedio` decimal(3,2) DEFAULT 0.00,
  `total_valoraciones` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Productos disponibles en la rotisería';

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `imagen`, `categoria_id`, `ingredientes`, `tiempo_preparacion`, `disponible`, `valoracion_promedio`, `total_valoraciones`, `activo`, `fecha_creacion`) VALUES
(1, 'Milanesa con Papas Fritas', 'Milanesa de carne con guarnición de papas fritas caseras', 850.00, NULL, 1, 'Carne, pan rallado, papas, aceite', 25, 1, 0.00, 0, 1, '2025-09-30 19:19:01'),
(2, 'Empanadas de Carne (x6)', 'Media docena de empanadas de carne cortada a cuchillo', 480.00, NULL, 5, 'Carne, cebolla, huevo, aceitunas, especias', 20, 1, 0.00, 0, 1, '2025-09-30 19:19:01'),
(3, 'Empanadas de Jamón y Queso (x6)', 'Media docena de empanadas de jamón y queso', 450.00, NULL, 5, 'Jamón, queso, masa', 20, 1, 0.00, 0, 1, '2025-09-30 19:19:01'),
(4, 'Ñoquis con Salsa', 'Ñoquis caseros con salsa a elección', 720.00, NULL, 2, 'Papa, harina, huevo, salsa', 30, 1, 0.00, 0, 1, '2025-09-30 19:19:01'),
(5, 'Ravioles de Ricota', 'Ravioles rellenos de ricota con salsa', 780.00, NULL, 2, 'Masa, ricota, salsa', 30, 1, 0.00, 0, 1, '2025-09-30 19:19:01'),
(6, 'Guiso de Lentejas', 'Guiso tradicional de lentejas con chorizo', 650.00, NULL, 3, 'Lentejas, chorizo, verduras', 35, 1, 0.00, 0, 1, '2025-09-30 19:19:01'),
(7, 'Tarta de Verduras', 'Tarta casera de verduras', 380.00, NULL, 4, 'Masa, acelga, cebolla, huevo', 40, 1, 0.00, 0, 1, '2025-09-30 19:19:01'),
(8, 'Coca Cola 1.5L', 'Gaseosa Coca Cola 1.5 litros', 320.00, NULL, 7, 'Gaseosa', 0, 1, 0.00, 0, 1, '2025-09-30 19:19:01'),
(9, 'Agua Mineral 500ml', 'Agua mineral sin gas', 150.00, NULL, 7, 'Agua', 0, 1, 0.00, 0, 1, '2025-09-30 19:19:01'),
(10, 'Flan Casero', 'Flan casero con dulce de leche', 280.00, NULL, 6, 'Leche, huevos, azúcar, dulce de leche', 60, 1, 0.00, 0, 1, '2025-09-30 19:19:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promociones`
--

CREATE TABLE `promociones` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('descuento_porcentaje','descuento_fijo') NOT NULL,
  `valor` decimal(6,2) DEFAULT NULL COMMENT 'Porcentaje o monto',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `monto_minimo` decimal(8,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Promociones y ofertas especiales';

--
-- Volcado de datos para la tabla `promociones`
--

INSERT INTO `promociones` (`id`, `nombre`, `descripcion`, `tipo`, `valor`, `fecha_inicio`, `fecha_fin`, `activa`, `monto_minimo`) VALUES
(1, '2x1 Empanadas Viernes', 'Por cada docena de empanadas, llevá otra gratis todos los viernes', 'descuento_porcentaje', 50.00, '2025-09-01', '2025-12-31', 1, 0.00),
(2, '15% Descuento Delivery', '15% de descuento en pedidos de delivery superiores a $1000', 'descuento_porcentaje', 15.00, '2025-09-01', '2025-09-30', 1, 1000.00),
(3, 'Combo Familiar', 'Milanesa + empanadas + bebida con descuento', 'descuento_fijo', 200.00, '2025-09-01', '2025-09-30', 1, 1500.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguimiento_pedidos`
--

CREATE TABLE `seguimiento_pedidos` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `estado_anterior` enum('pendiente','confirmado','en_preparacion','listo','en_camino','entregado','cancelado') DEFAULT NULL,
  `estado_nuevo` enum('pendiente','confirmado','en_preparacion','listo','en_camino','entregado','cancelado') NOT NULL,
  `usuario_cambio_id` int(11) NOT NULL,
  `fecha_cambio` timestamp NOT NULL DEFAULT current_timestamp(),
  `comentarios` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historial de cambios de estado del pedido';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `direccion` text DEFAULT NULL,
  `contraseña` varchar(255) NOT NULL,
  `rol` enum('cliente','cajero','cocinero','repartidor','administrador') DEFAULT 'cliente',
  `estado_disponibilidad` tinyint(1) DEFAULT 1 COMMENT 'Para repartidores',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Usuarios del sistema con diferentes roles';

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `telefono`, `direccion`, `contraseña`, `rol`, `estado_disponibilidad`, `activo`, `fecha_registro`) VALUES
(2, 'LUCIANO', 'CAMPANELLI', 'luchocampanelli1@gmail.com', '01162165019', 'Cerrito 3916', '$2y$10$rA6rqmxX.W6VQXFCj4s2r.wvPjg7VW14tZ8R6WnAzNYV2trK81dmO', 'cliente', 1, 1, '2025-10-01 02:36:13'),
(3, 'Admin', 'Sistema', 'admin@elbuengusto.com', '01162165019', 'Cerrito 3916', '$2y$10$hjiSZ7GYg.gS7/waGriPhOxb/m13i/UTTMGot.N5v8DTQn8GOwSOy', 'administrador', 1, 1, '2025-10-01 16:51:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zonas_delivery`
--

CREATE TABLE `zonas_delivery` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_delivery` decimal(6,2) NOT NULL,
  `tiempo_estimado` int(11) DEFAULT 30 COMMENT 'En minutos',
  `activa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Zonas de entrega con precios diferenciados';

--
-- Volcado de datos para la tabla `zonas_delivery`
--

INSERT INTO `zonas_delivery` (`id`, `nombre`, `descripcion`, `precio_delivery`, `tiempo_estimado`, `activa`) VALUES
(1, 'Zona Centro (0-2km)', 'Centro, Microcentro, San Telmo', 200.00, 20, 1),
(2, 'Zona Norte (2-4km)', 'Palermo, Recoleta, Belgrano', 300.00, 30, 1),
(3, 'Zona Sur (4-6km)', 'La Boca, Barracas, San Cristóbal', 400.00, 40, 1),
(4, 'Zona Oeste (4-6km)', 'Caballito, Flores, Almagro', 350.00, 35, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pedido_id` (`pedido_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `repartidor_id` (`repartidor_id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `condimentos`
--
ALTER TABLE `condimentos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indices de la tabla `direcciones_cliente`
--
ALTER TABLE `direcciones_cliente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `item_condimentos`
--
ALTER TABLE `item_condimentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_condimentos_index_0` (`pedido_item_id`,`condimento_id`),
  ADD KEY `condimento_id` (`condimento_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_pedido` (`numero_pedido`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `zona_delivery_id` (`zona_delivery_id`),
  ADD KEY `repartidor_id` (`repartidor_id`),
  ADD KEY `cajero_id` (`cajero_id`);

--
-- Indices de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `promociones`
--
ALTER TABLE `promociones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `seguimiento_pedidos`
--
ALTER TABLE `seguimiento_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `usuario_cambio_id` (`usuario_cambio_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `zonas_delivery`
--
ALTER TABLE `zonas_delivery`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `condimentos`
--
ALTER TABLE `condimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `direcciones_cliente`
--
ALTER TABLE `direcciones_cliente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `item_condimentos`
--
ALTER TABLE `item_condimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `promociones`
--
ALTER TABLE `promociones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `seguimiento_pedidos`
--
ALTER TABLE `seguimiento_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `zonas_delivery`
--
ALTER TABLE `zonas_delivery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD CONSTRAINT `calificaciones_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `calificaciones_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `calificaciones_ibfk_3` FOREIGN KEY (`repartidor_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `direcciones_cliente`
--
ALTER TABLE `direcciones_cliente`
  ADD CONSTRAINT `direcciones_cliente_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `item_condimentos`
--
ALTER TABLE `item_condimentos`
  ADD CONSTRAINT `item_condimentos_ibfk_1` FOREIGN KEY (`pedido_item_id`) REFERENCES `pedido_items` (`id`),
  ADD CONSTRAINT `item_condimentos_ibfk_2` FOREIGN KEY (`condimento_id`) REFERENCES `condimentos` (`id`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`zona_delivery_id`) REFERENCES `zonas_delivery` (`id`),
  ADD CONSTRAINT `pedidos_ibfk_3` FOREIGN KEY (`repartidor_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `pedidos_ibfk_4` FOREIGN KEY (`cajero_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD CONSTRAINT `pedido_items_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `pedido_items_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `seguimiento_pedidos`
--
ALTER TABLE `seguimiento_pedidos`
  ADD CONSTRAINT `seguimiento_pedidos_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `seguimiento_pedidos_ibfk_2` FOREIGN KEY (`usuario_cambio_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
