CREATE TABLE `calificaciones` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `calificacion_comida` int(11) NOT NULL COMMENT 'Del 1 al 5',
  `calificacion_delivery` int(11) NOT NULL COMMENT 'Del 1 al 5',
  `comentario` text DEFAULT null,
  `repartidor_id` int(11) DEFAULT null COMMENT 'Para calificar al repartidor',
  `fecha_calificacion` timestamp NOT NULL DEFAULT (current_timestamp())
);

CREATE TABLE `categorias` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT null,
  `activa` tinyint(1) DEFAULT 1
);

CREATE TABLE `condimentos` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `tipo` ENUM ('sal', 'salsa', 'especias', 'otros') DEFAULT 'otros',
  `activo` tinyint(1) DEFAULT 1
);

CREATE TABLE `configuracion` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `clave` varchar(50) NOT NULL,
  `valor` text NOT NULL,
  `descripcion` text DEFAULT null,
  `fecha_modificacion` timestamp NOT NULL DEFAULT (current_timestamp())
);

CREATE TABLE `direcciones_cliente` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `alias` varchar(50) DEFAULT null COMMENT 'Casa, Trabajo, etc.',
  `direccion` text NOT NULL,
  `es_favorita` tinyint(1) DEFAULT 0
);

CREATE TABLE `item_condimentos` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `pedido_item_id` int(11) NOT NULL,
  `condimento_id` int(11) NOT NULL
);

CREATE TABLE `pedidos` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `numero_pedido` varchar(20) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo_pedido` ENUM ('inmediato', 'programado') NOT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT (current_timestamp()),
  `fecha_entrega_programada` datetime DEFAULT null,
  `direccion_entrega` text NOT NULL,
  `telefono_contacto` varchar(20) NOT NULL,
  `metodo_pago` ENUM ('digital', 'efectivo') DEFAULT 'efectivo',
  `estado` ENUM ('pendiente', 'confirmado', 'en_preparacion', 'listo', 'en_camino', 'entregado', 'cancelado') DEFAULT 'pendiente',
  `subtotal` decimal(8,2) NOT NULL,
  `precio_delivery` decimal(6,2) DEFAULT 0,
  `total` decimal(8,2) NOT NULL,
  `zona_delivery_id` int(11) DEFAULT null,
  `repartidor_id` int(11) DEFAULT null,
  `cajero_id` int(11) DEFAULT null COMMENT 'Si fue tomado por cajero',
  `comentarios_cliente` text DEFAULT null,
  `activo` tinyint(1) DEFAULT 1
);

CREATE TABLE `pedido_items` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(8,2) NOT NULL,
  `precio_total` decimal(8,2) NOT NULL
);

CREATE TABLE `productos` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT null,
  `precio` decimal(8,2) NOT NULL,
  `imagen` varchar(255) DEFAULT null,
  `categoria_id` int(11) NOT NULL,
  `ingredientes` text DEFAULT null,
  `tiempo_preparacion` int(11) DEFAULT 20 COMMENT 'En minutos',
  `disponible` tinyint(1) DEFAULT 1,
  `valoracion_promedio` decimal(3,2) DEFAULT 0,
  `total_valoraciones` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT (current_timestamp())
);

CREATE TABLE `promociones` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT null,
  `tipo` ENUM ('descuento_porcentaje', 'descuento_fijo') NOT NULL,
  `valor` decimal(6,2) DEFAULT null COMMENT 'Porcentaje o monto',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `monto_minimo` decimal(8,2) DEFAULT 0
);

CREATE TABLE `seguimiento_pedidos` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `estado_anterior` ENUM ('pendiente', 'confirmado', 'en_preparacion', 'listo', 'en_camino', 'entregado', 'cancelado') DEFAULT null,
  `estado_nuevo` ENUM ('pendiente', 'confirmado', 'en_preparacion', 'listo', 'en_camino', 'entregado', 'cancelado') NOT NULL,
  `usuario_cambio_id` int(11) NOT NULL,
  `fecha_cambio` timestamp NOT NULL DEFAULT (current_timestamp()),
  `comentarios` text DEFAULT null
);

CREATE TABLE `usuarios` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `direccion` text DEFAULT null,
  `contraseña` varchar(255) NOT NULL,
  `rol` ENUM ('cliente', 'cajero', 'cocinero', 'repartidor', 'administrador') DEFAULT 'cliente',
  `estado_disponibilidad` tinyint(1) DEFAULT 1 COMMENT 'Para repartidores',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT (current_timestamp())
);

CREATE TABLE `zonas_delivery` (
  `id` int(11) PRIMARY KEY NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT null,
  `precio_delivery` decimal(6,2) NOT NULL,
  `tiempo_estimado` int(11) DEFAULT 30 COMMENT 'En minutos',
  `activa` tinyint(1) DEFAULT 1
);

ALTER TABLE `calificaciones` COMMENT = 'Calificaciones separadas para comida y delivery';

ALTER TABLE `categorias` COMMENT = 'Categorías: Minutas, Pastas, Guisos, Bebidas';

ALTER TABLE `condimentos` COMMENT = 'Condimentos disponibles: sal, salsas, especias';

ALTER TABLE `configuracion` COMMENT = 'Configuración del sistema';

ALTER TABLE `direcciones_cliente` COMMENT = 'Direcciones guardadas por el cliente';

ALTER TABLE `item_condimentos` COMMENT = 'Condimentos seleccionados para cada item';

ALTER TABLE `pedidos` COMMENT = 'Pedidos con seguimiento completo del flujo';

ALTER TABLE `pedido_items` COMMENT = 'Items específicos de cada pedido';

ALTER TABLE `productos` COMMENT = 'Productos disponibles en la rotisería';

ALTER TABLE `promociones` COMMENT = 'Promociones y ofertas especiales';

ALTER TABLE `seguimiento_pedidos` COMMENT = 'Historial de cambios de estado del pedido';

ALTER TABLE `usuarios` COMMENT = 'Usuarios del sistema con diferentes roles';

ALTER TABLE `zonas_delivery` COMMENT = 'Zonas de entrega con precios diferenciados';

ALTER TABLE `calificaciones` ADD CONSTRAINT `calificaciones_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`);

ALTER TABLE `calificaciones` ADD CONSTRAINT `calificaciones_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `calificaciones` ADD CONSTRAINT `calificaciones_ibfk_3` FOREIGN KEY (`repartidor_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `direcciones_cliente` ADD CONSTRAINT `direcciones_cliente_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `item_condimentos` ADD CONSTRAINT `item_condimentos_ibfk_1` FOREIGN KEY (`pedido_item_id`) REFERENCES `pedido_items` (`id`);

ALTER TABLE `item_condimentos` ADD CONSTRAINT `item_condimentos_ibfk_2` FOREIGN KEY (`condimento_id`) REFERENCES `condimentos` (`id`);

ALTER TABLE `pedidos` ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `pedidos` ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`zona_delivery_id`) REFERENCES `zonas_delivery` (`id`);

ALTER TABLE `pedidos` ADD CONSTRAINT `pedidos_ibfk_3` FOREIGN KEY (`repartidor_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `pedidos` ADD CONSTRAINT `pedidos_ibfk_4` FOREIGN KEY (`cajero_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `pedido_items` ADD CONSTRAINT `pedido_items_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`);

ALTER TABLE `pedido_items` ADD CONSTRAINT `pedido_items_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

ALTER TABLE `productos` ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

ALTER TABLE `seguimiento_pedidos` ADD CONSTRAINT `seguimiento_pedidos_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`);

ALTER TABLE `seguimiento_pedidos` ADD CONSTRAINT `seguimiento_pedidos_ibfk_2` FOREIGN KEY (`usuario_cambio_id`) REFERENCES `usuarios` (`id`);
