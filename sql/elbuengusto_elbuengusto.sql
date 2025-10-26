-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 26-10-2025 a las 01:25:01
-- Versión del servidor: 10.11.14-MariaDB-cll-lve-log
-- Versión de PHP: 8.4.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `elbuengusto_elbuengusto`
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

--
-- Volcado de datos para la tabla `calificaciones`
--

INSERT INTO `calificaciones` (`id`, `pedido_id`, `usuario_id`, `calificacion_comida`, `calificacion_delivery`, `comentario`, `repartidor_id`, `fecha_calificacion`) VALUES
(1, 1, 5, 5, 4, 'Muy buena comida y delivery rápido', 4, '2025-10-15 20:54:59'),
(3, 15, 9, 4, 4, 'Me gusto el servicio pero mas que nada la comida', 11, '2025-10-21 19:24:35'),
(4, 4, 9, 5, 3, 'GRACIAS DIOS', 11, '2025-10-21 19:55:54'),
(5, 18, 9, 2, 2, 'no me gusto', 11, '2025-10-23 04:21:37'),
(6, 16, 9, 3, 4, 'top', 11, '2025-10-23 07:06:35'),
(7, 39, 9, 5, 5, '', 11, '2025-10-23 07:06:42'),
(8, 38, 9, 4, 4, 'yugguyjfytfy', 11, '2025-10-24 14:13:16'),
(9, 47, 9, 4, 5, 'top', 11, '2025-10-24 14:18:30'),
(25, 56, 16, 5, 5, '¡Todo perfecto! La comida llegó caliente y el repartidor muy amable.', 4, '2025-10-23 18:45:20'),
(26, 59, 16, 4, 5, 'Muy buena comida, el delivery fue rapidísimo.', 1, '2025-10-20 18:50:56'),
(28, 75, 17, 5, 5, 'El repartidor estaba guapo', 21, '2025-10-25 23:14:04');

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
(1, 'Minutas', 'Comidas rápidas y sencillas como hamburguesas, milanesas, sándwiches', 1),
(2, 'Pastas', 'Variedades de pastas: ravioles, ñoquis, tallarines', 1),
(3, 'Guisos', 'Platos de cocción lenta, ideales para temporada fría', 1),
(4, 'Tartas', 'Tartas saladas y dulces', 1),
(5, 'Empanadas', 'Empanadas de distintos sabores', 1),
(6, 'Postres', 'Dulces como flanes, budines y tortas', 1),
(7, 'Bebidas', 'Gaseosas, jugos, aguas y bebidas calientes', 1),
(8, 'Embutidos', 'Fiambres y embutidos por porción de 100 gramos', 1),
(9, 'Otros', 'Productos variados o especiales', 1);

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
(2, 'Salsa de tomate', 'salsa', 1),
(3, 'Mayonesa', 'salsa', 1),
(4, 'Mostaza', 'salsa', 1),
(5, 'Pimienta', 'especias', 1),
(6, 'Orégano', 'especias', 1),
(7, 'Ají molido', 'especias', 1);

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones_cliente`
--

CREATE TABLE `direcciones_cliente` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `alias` varchar(50) DEFAULT NULL COMMENT 'Casa, Trabajo, etc.',
  `direccion` text NOT NULL,
  `codigo_postal` varchar(20) DEFAULT NULL,
  `instrucciones` text DEFAULT NULL,
  `es_favorita` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Direcciones guardadas por el cliente';

--
-- Volcado de datos para la tabla `direcciones_cliente`
--

INSERT INTO `direcciones_cliente` (`id`, `usuario_id`, `alias`, `direccion`, `codigo_postal`, `instrucciones`, `es_favorita`) VALUES
(1, 5, 'Casa', 'Calle Falsa 123', NULL, NULL, 1),
(2, 6, 'Casa', 'Av. Siempre Viva 742', NULL, NULL, 1),
(3, 6, 'Trabajo', 'Oficina Central, Piso 3', NULL, NULL, 0),
(4, 16, 'Casa', 'ffsefsefsefs', '1520', 'dsadad', 1),
(5, 16, 'Casa', 'dawsdsasd', '1609', 'dsadadsa', 0),
(12, 17, 'Casa', 'Calle Falsa 123', '123', 'Dejelo en el buzon', 0),
(13, 9, 'Casa', 'cerrito 3916', '1606', '', 0),
(14, 22, 'Trabajo', 'Ader 332', '1456', 'El timbre de abajo', 0),
(15, 22, 'Casa', 'Ader 352', '1456', 'El timbre de arriba', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item_condimentos`
--

CREATE TABLE `item_condimentos` (
  `id` int(11) NOT NULL,
  `pedido_item_id` int(11) NOT NULL,
  `condimento_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Condimentos seleccionados para cada item';

--
-- Volcado de datos para la tabla `item_condimentos`
--

INSERT INTO `item_condimentos` (`id`, `pedido_item_id`, `condimento_id`) VALUES
(1, 1, 1),
(2, 1, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `numero_pedido` varchar(20) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo_pedido` enum('inmediato','programado') NOT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `fecha_entrega_programada` datetime DEFAULT NULL,
  `direccion_entrega` text NOT NULL,
  `telefono_contacto` varchar(20) NOT NULL,
  `metodo_pago` enum('digital','efectivo') DEFAULT 'digital',
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

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `numero_pedido`, `usuario_id`, `tipo_pedido`, `fecha_pedido`, `updated_at`, `fecha_entrega_programada`, `direccion_entrega`, `telefono_contacto`, `metodo_pago`, `estado`, `subtotal`, `precio_delivery`, `total`, `zona_delivery_id`, `repartidor_id`, `cajero_id`, `comentarios_cliente`, `activo`) VALUES
(1, 'PED001', 5, 'inmediato', '2025-10-15 20:54:59', NULL, NULL, 'Calle Falsa 123', '1144556677', 'digital', 'en_camino', 1200.00, 150.00, 1350.00, 1, 4, 2, 'Caliente por favor', 1),
(4, 'PED41172', 9, 'programado', '2025-10-21 04:26:17', '2025-10-21 15:15:38', '2025-10-21 11:00:00', 'Cerrito 3916', '+541162165019', 'digital', 'entregado', 14800.00, 7200.00, 22000.00, 2, 11, NULL, 'Mucha leche', 1),
(15, 'PED89214', 9, 'inmediato', '2025-10-20 22:40:21', NULL, NULL, 'Cerrito 3966', '+541162165019', 'digital', 'entregado', 10500.00, 5500.00, 16000.00, 1, 11, NULL, 'asdasd', 1),
(16, 'PED18655', 9, 'programado', '2025-10-21 06:17:26', '2025-10-21 18:03:19', '2025-10-21 11:00:00', 'Cerrito 3916', '+541162165019', 'digital', 'entregado', 11200.00, 5500.00, 16700.00, 1, 11, NULL, 'aaaaaa', 1),
(17, 'PED93830', 9, 'inmediato', '2025-10-23 03:46:05', NULL, NULL, 'Cerrito 3916', '+541162165019', 'digital', 'cancelado', 4200.00, 5500.00, 9700.00, 1, 11, NULL, 'mucha sal', 1),
(18, 'PED24715', 9, 'inmediato', '2025-10-23 04:17:09', NULL, NULL, 'Cerrito 3916', '+541162165019', 'digital', 'entregado', 7700.00, 5500.00, 13200.00, 1, 11, NULL, 'achuuuuuuuuuuuuuuuuuuuuuuuuuuu', 1),
(19, 'PED53328', 9, 'inmediato', '2025-10-23 04:23:07', NULL, NULL, 'Cerrito 3916', '+541162165019', 'digital', 'entregado', 16800.00, 7200.00, 24000.00, 2, 11, NULL, 'adasdas', 1),
(38, 'PED10627', 9, 'inmediato', '2025-10-23 06:46:08', NULL, NULL, 'Cerrito 3916', '+541162165019', 'digital', 'entregado', 2800.00, 5500.00, 8300.00, 1, 11, NULL, 'a', 1),
(39, 'PED63883', 9, 'inmediato', '2025-10-23 06:46:09', NULL, NULL, 'Cerrito 3916', '+541162165019', 'digital', 'entregado', 2800.00, 5500.00, 8300.00, 1, 11, NULL, 'a', 1),
(47, 'PED10923', 9, 'programado', '2025-10-24 14:11:06', '2025-10-24 14:11:22', '2025-10-24 11:00:00', 'Cerrito 3916', '+541162165019', 'digital', 'entregado', 7700.00, 7200.00, 14900.00, 2, 11, NULL, 'dfgxfd', 1),
(48, 'PED89760', 9, 'programado', '2025-10-23 14:18:58', '2025-10-24 05:24:47', '2025-10-23 13:00:00', 'Cerrito 3916', '+541162165019', 'digital', 'en_camino', 10500.00, 5500.00, 16000.00, 1, 11, NULL, 'sal', 1),
(49, 'PED94574', 9, 'programado', '2025-10-23 14:19:26', '2025-10-24 05:24:47', '2025-10-23 13:00:00', 'Cerrito 3916', '+541162165019', 'digital', 'en_preparacion', 12800.00, 7200.00, 20000.00, 2, NULL, NULL, 'sada', 1),
(50, 'PED45120', 9, 'programado', '2025-10-23 14:19:55', '2025-10-24 05:24:47', '2025-10-23 13:00:00', 'Cerrito 3916', '+541162165019', 'digital', 'entregado', 11600.00, 5500.00, 17100.00, 1, 11, NULL, 'sdas', 1),
(51, 'PED04487', 12, 'inmediato', '2025-10-24 05:29:10', NULL, NULL, 'lol', '8888888888', 'digital', 'listo', 3500.00, 5500.00, 9000.00, 1, NULL, NULL, '', 1),
(52, 'PED71371', 17, 'inmediato', '2025-10-24 22:05:28', NULL, NULL, 'Avenida Siempre Viva 123', '8888888888', 'digital', 'listo', 16800.00, 5500.00, 22300.00, 1, NULL, NULL, 'Regalan cerveza?', 1),
(53, 'PED94994', 17, 'inmediato', '2025-10-24 22:06:27', NULL, NULL, 'Avenida Siempre Viva 123', '8888888888', 'digital', 'listo', 3400.00, 5500.00, 8900.00, 1, NULL, NULL, 'a', 1),
(54, 'PED32055', 16, 'inmediato', '2025-10-25 06:05:04', NULL, NULL, 'dwadawdaw', '1152623562', 'digital', 'entregado', 7700.00, 5500.00, 13200.00, 1, NULL, NULL, 'dawdwad', 1),
(55, 'PED12345', 16, 'inmediato', '2025-10-20 06:33:11', NULL, NULL, 'Cerrito 3916', '+541162165019', 'digital', 'entregado', 8200.00, 5500.00, 13700.00, 1, 11, NULL, NULL, 1),
(56, 'PED54321', 16, 'inmediato', '2025-10-22 06:33:11', NULL, NULL, 'Cerrito 3916', '+541162165019', 'digital', 'entregado', 12100.00, 5500.00, 17600.00, 1, 11, NULL, NULL, 1),
(57, 'PED98765', 16, 'inmediato', '2025-10-24 06:33:11', NULL, NULL, 'Cerrito 3916', '+541162165019', 'digital', 'entregado', 6200.00, 5500.00, 11700.00, 1, 11, NULL, NULL, 1),
(59, 'PED28164', 16, 'inmediato', '2025-10-25 06:56:50', NULL, NULL, 'dwadawdaw', '1152623562', 'digital', 'entregado', 7700.00, 5500.00, 13200.00, 1, NULL, NULL, NULL, 1),
(62, 'PED63777', 16, 'inmediato', '2025-10-25 07:31:32', NULL, NULL, 'dwadawdaw', '1152623562', 'digital', 'entregado', 7700.00, 5500.00, 13200.00, 1, NULL, NULL, NULL, 1),
(73, 'PED21713', 16, 'inmediato', '2025-10-25 18:33:55', NULL, NULL, 'fesfefse - fesfsefsef', '1158253782', 'digital', 'entregado', 3500.00, 5500.00, 9000.00, 1, 14, NULL, 'fsefsefse', 1),
(74, 'PED99855', 16, 'inmediato', '2025-10-25 22:23:22', NULL, NULL, 'ffsefsefsefs - dsadad', '1158253782', 'digital', 'entregado', 7700.00, 7200.00, 14900.00, 2, 21, NULL, 'Wilmaaaaa putaaqaaa', 1),
(75, 'PED49853', 17, 'inmediato', '2025-10-25 22:55:45', NULL, NULL, 'Avenida Siempre Viva 123', '8888888888', 'digital', 'entregado', 4200.00, 8800.00, 13000.00, 3, 21, NULL, 'Ana Maria', 1),
(76, 'PED47730', 17, 'programado', '2025-10-25 22:59:42', '2025-10-25 23:00:24', '2025-10-25 20:00:00', 'Calle Falsa 123 - Dejelo en el buzon', '8888888888', 'digital', 'cancelado', 5100.00, 7200.00, 12300.00, 2, 21, NULL, 'Ahorita pofavo', 1),
(77, 'PED33862', 22, 'inmediato', '2025-10-26 03:51:06', NULL, NULL, 'Ader 332 - El timbre de abajo', '123321123321', 'digital', 'entregado', 3500.00, 5500.00, 9000.00, 1, 21, NULL, '', 1),
(78, 'PED91990', 22, 'inmediato', '2025-10-26 04:05:18', NULL, NULL, 'Ader 352 - El timbre de arriba', '123321123321', 'digital', 'listo', 7700.00, 7200.00, 14900.00, 2, NULL, NULL, 'JAJAJAJAJAJJA', 1);

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

--
-- Volcado de datos para la tabla `pedido_items`
--

INSERT INTO `pedido_items` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio_unitario`, `precio_total`) VALUES
(1, 1, 1, 1, 1200.00, 1200.00),
(5, 4, 10, 1, 2400.00, 2400.00),
(6, 4, 8, 4, 3100.00, 12400.00),
(17, 15, 1, 3, 3500.00, 10500.00),
(18, 16, 4, 4, 2800.00, 11200.00),
(19, 17, 3, 1, 4200.00, 4200.00),
(20, 18, 3, 1, 4200.00, 4200.00),
(21, 18, 1, 1, 3500.00, 3500.00),
(22, 19, 4, 6, 2800.00, 16800.00),
(41, 38, 4, 1, 2800.00, 2800.00),
(42, 39, 4, 1, 2800.00, 2800.00),
(54, 47, 3, 1, 4200.00, 4200.00),
(55, 47, 1, 1, 3500.00, 3500.00),
(56, 48, 1, 1, 3500.00, 3500.00),
(57, 48, 3, 1, 4200.00, 4200.00),
(58, 48, 4, 1, 2800.00, 2800.00),
(59, 49, 2, 4, 3200.00, 12800.00),
(60, 50, 5, 4, 2900.00, 11600.00),
(61, 51, 1, 1, 3500.00, 3500.00),
(62, 52, 3, 4, 4200.00, 16800.00),
(63, 53, 7, 1, 3400.00, 3400.00),
(64, 54, 3, 1, 4200.00, 4200.00),
(65, 54, 1, 1, 3500.00, 3500.00),
(66, 55, 42, 2, 2000.00, 4000.00),
(67, 55, 19, 1, 4200.00, 4200.00),
(68, 56, 1, 2, 3500.00, 7000.00),
(69, 56, 5, 1, 2900.00, 2900.00),
(70, 56, 6, 1, 2600.00, 2600.00),
(71, 56, 29, 2, 800.00, 1600.00),
(72, 57, 39, 1, 2800.00, 2800.00),
(73, 57, 43, 1, 1200.00, 1200.00),
(74, 57, 24, 1, 1200.00, 1200.00),
(75, 57, 32, 1, 1200.00, 1200.00),
(78, 59, 3, 1, 4200.00, 4200.00),
(79, 59, 1, 1, 3500.00, 3500.00),
(84, 62, 3, 1, 4200.00, 4200.00),
(85, 62, 1, 1, 3500.00, 3500.00),
(97, 73, 1, 1, 3500.00, 3500.00),
(98, 74, 3, 1, 4200.00, 4200.00),
(99, 74, 1, 1, 3500.00, 3500.00),
(100, 75, 3, 1, 4200.00, 4200.00),
(101, 76, 6, 1, 2600.00, 2600.00),
(102, 76, 15, 1, 2500.00, 2500.00),
(103, 77, 1, 1, 3500.00, 3500.00),
(104, 78, 1, 1, 3500.00, 3500.00),
(105, 78, 3, 1, 4200.00, 4200.00);

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
(1, 'Milanesa Napolitana', 'Milanesa de pollo con jamón y queso ', 3500.00, 'https://media.istockphoto.com/id/1205601529/es/foto/milanesa-argentina-con-salsa-de-tomate-y-primer-plano-de-queso-derretido.jpg?s=612x612&w=0&k=20&c=FoOlEKki5z0pMOx6xByxYjSVJjHZ2MM5_rEmAnDJIjE=', 1, 'Carne, pan rallado, jamón, queso, salsa de tomate', 25, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(2, 'Suprema Maryland', 'Suprema de pollo con salsa especial', 3200.00, 'https://media.istockphoto.com/id/1387191843/es/foto/filete-de-pollo.jpg?s=612x612&w=0&k=20&c=NohWQF7c_Ubwk9DXbqbOS9_wLILW5PZKBtsLyeoxI2I=', 1, 'Pechuga de pollo, pan rallado, salsa Maryland', 20, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(3, 'Bife de Chorizo', 'Bife de chorizo con guarnición', 4200.00, 'https://media.istockphoto.com/id/587207508/es/foto/filete-a-la-parrilla-en-rodajas-ribeye-con-mantequilla-de-hierbas.jpg?s=612x612&w=0&k=20&c=FpPGPX-jIkIIORr1L40LE-YozmaWbiGlAPeni5qGNhg=', 1, 'Carne de res, condimentos', 15, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(4, 'Pollo al Horno', 'Medio pollo al horno con especias', 2800.00, 'https://media.istockphoto.com/id/1452768554/es/foto/pollo-asado-y-verduras.jpg?s=612x612&w=0&k=20&c=f7A73Mj9sB1fsie8pFSLo0pxxIEs6RLOv3P6D1Dank0=', 1, 'Pollo, limón, hierbas', 45, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(5, 'Ravioles de Ricota', 'Ravioles caseros con salsa a elección', 2900.00, 'https://media.istockphoto.com/id/1423025529/es/foto/vista-superior-de-tortelloni-o-tortelli-balanzoni-pasta-verde-italiana-con-relleno-de.jpg?s=612x612&w=0&k=20&c=CbjyMbS3MzKiAW6Gk8rt_2hXkHXdQKJl-PAVGEm5dqI=', 2, 'Masa, ricota, espinaca, salsa', 25, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(6, 'Ñoquis de Papa', 'Ñoquis tradicionales con tuco o crema', 2600.00, 'https://media.istockphoto.com/id/1969677546/es/foto/potato-gnocchi-with-fresh-tomatoes-sauce-typical-italian-food-cr2.jpg?s=612x612&w=0&k=20&c=uzUhFdAakjXz3Ux0Xl16ysGQrMi2PfzIKb03wOB3PcU=', 2, 'Papa, harina, huevo, salsa', 30, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(7, 'Lasagna Casera', 'Lasagna de carne con bechamel', 3400.00, 'https://media.istockphoto.com/id/168246587/es/foto/lasa%C3%B1a-de-primavera.jpg?s=612x612&w=0&k=20&c=K29Gf8b0JCbNiLNFpwvXpXVTySP_2VzxRFUe6eSQvTc=', 2, 'Pasta, carne, bechamel, queso', 35, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(8, 'Sorrentinos', 'Sorrentinos de jamón y queso', 3100.00, 'https://media.istockphoto.com/id/1248306669/es/foto/sorrentinos-pasta-rellena-con-salsa-sobre-mesa-de-madera.jpg?s=612x612&w=0&k=20&c=VV4FRdPpTTXKuSlGiucEXLY2F7DqtW1Km7p4Y3NpjOA=', 2, 'Masa, jamón, mozzarella, salsa', 25, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(9, 'Canelones de Verdura', 'Canelones rellenos de verdura', 2800.00, 'https://media.istockphoto.com/id/119446439/es/foto/canel%C3%B3n-ricota-y-espinaca.jpg?s=612x612&w=0&k=20&c=RgMEFSEqrqh3bu26AS-UScJSw8CmVj2GAgM2Ntv9iXg=', 2, 'Pasta, acelga, ricota, salsa blanca', 30, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(10, 'Guiso de Lentejas', 'Guiso tradicional de lentejas con chorizo', 2400.00, 'https://media.istockphoto.com/id/1081696632/es/foto/adasi-guiso-de-lentejas-persas-deliciosa-cocina-%C3%A1rabe.jpg?s=612x612&w=0&k=20&c=Y-PZca5kyUQGdo1OXxl2xUWBxfDC34FTRmXKc1tGsWs=', 3, 'Lentejas, chorizo, zanahoria, cebolla', 40, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(11, 'Estofado de Carne', 'Estofado de carne con papas y verduras', 3200.00, 'https://media.istockphoto.com/id/172441917/es/foto/estofado-de-hidromasaje-con-champi%C3%B1ones-y-papas.jpg?s=612x612&w=0&k=20&c=tKd_uCtiBxIHtC6pWrqN1CezB2lhSZ1JLsGuoFU4Vk0=', 3, 'Carne, papa, zanahoria, cebolla, vino', 45, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(12, 'Locro Criollo', 'Locro tradicional argentino', 3500.00, 'https://media.istockphoto.com/id/1313364837/es/foto/taz%C3%B3n-del-argentino-locro.jpg?s=612x612&w=0&k=20&c=4YJcerpiMJ2RLWzU1U2ohsKPd0UC3Qa8YK4lRxyWnsQ=', 3, 'Maíz, porotos, carne, chorizo, panceta', 60, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(13, 'Carbonada', 'Carbonada con zapallo y frutas', 2900.00, 'https://media.istockphoto.com/id/1254119522/es/foto/carbonada-servida-en-la-comida-t%C3%ADpica-de-calabaza-de-la-gastronom%C3%ADa-argentina-chile-bolivia-y.jpg?s=612x612&w=0&k=20&c=5fJP64wCGP5IKbJHIX-xIfQOF29943R1NltWp7ooyA4=', 3, 'Carne, zapallo, durazno, papa, choclo', 50, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(14, 'Mondongo', 'Mondongo casero tradicional', 3300.00, 'https://media.istockphoto.com/id/535489158/es/foto/cocina-de-madrid.jpg?s=612x612&w=0&k=20&c=oTjKCldKChOJWMr7FkyyMd0dRLcmkXiDTQPswi4KPew=', 3, 'Mondongo, garbanzos, chorizo colorado', 90, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(15, 'Tarta de Jamón y Queso', 'Tarta clásica de jamón y queso', 2500.00, 'https://media.istockphoto.com/id/461801551/es/foto/quiche-lorraine-porci%C3%B3n.jpg?s=612x612&w=0&k=20&c=HSYhh42vWyEtFHnLommcOW4AxKtImXz1Stsj0LpjeeU=', 4, 'Masa, jamón, queso, huevo, crema', 40, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(16, 'Tarta de Verdura', 'Tarta de acelga y ricota', 2200.00, 'https://media.istockphoto.com/id/535719897/es/foto/pastel-con-espinacas-y-queso-feta.jpg?s=612x612&w=0&k=20&c=XY9wufclgUgKC-5i7BgeACoAaDvaS9SKrWwUwmG_cBk=', 4, 'Masa, acelga, ricota, huevo', 40, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(17, 'Tarta de Atún', 'Tarta de atún con cebolla y morrón', 2600.00, 'https://media.istockphoto.com/id/2098845234/es/foto/empanada-gallega-rellena-de-at%C3%BAn-o-carne-y-verduras-de-cerca-en-la-tabla-horizontal.jpg?s=612x612&w=0&k=20&c=UtfQMkE-dcXOzQiXKCGhThhcid_6CiB-Ibz5QvagI0U=', 4, 'Masa, atún, cebolla, morrón, huevo', 40, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(18, 'Tarta Pascualina', 'Tarta de espinaca y huevo', 2400.00, 'https://media.istockphoto.com/id/1463863208/es/foto/tarta-casera-de-feta-de-espinacas-quiche.jpg?s=612x612&w=0&k=20&c=rjT9sUW_qzlwLO92eJT9HFJD1R8WM6rYoicYPKpZERU=', 4, 'Masa, espinaca, huevo duro, ricota', 45, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(19, 'Empanadas de Carne', 'Empanadas criollas (por docena)', 4500.00, 'https://media.istockphoto.com/id/1171946922/es/foto/empanadas.jpg?s=612x612&w=0&k=20&c=Lo5Ybk5FbjjvdJcE12fSandBIdZI45P0wEVSQRzvuT8=', 5, 'Carne, cebolla, huevo, aceitunas, masa', 30, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(20, 'Empanadas de Pollo', 'Empanadas de pollo (por docena)', 4200.00, 'https://media.istockphoto.com/id/1437638745/es/foto/primer-plano-de-sabrosas-empanadas-de-pollo.jpg?s=612x612&w=0&k=20&c=w2NtFmVIeE1r23_qRwumyxV2p43Mafemuj7DMFksq80=', 5, 'Pollo, cebolla, morrón, masa', 30, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(21, 'Empanadas de Jamón y Queso', 'Empanadas de jamón y queso (por docena)', 4000.00, 'https://media.istockphoto.com/id/152137708/es/foto/jam%C3%B3n-y-queso-empanada-primer-plano.jpg?s=612x612&w=0&k=20&c=ziMRxdoCLnocQBLIptsdjAEtqHB4cAGOTqWW4pA1Des=', 5, 'Jamón, queso, masa', 25, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(22, 'Empanadas de Verdura', 'Empanadas de verdura (por docena)', 3800.00, 'https://www.cronica.com.ar/__export/1745442686992/sites/cronica/img/2025/04/23/37ec640d-6407-4947-ac25-9940bdee4846.jpg_1110719059.jpg', 5, 'Acelga, cebolla, queso, masa', 30, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(23, 'Empanadas de Carne Picante', 'Empanadas picantes (por docena)', 4700.00, 'https://media.istockphoto.com/id/1999053536/es/foto/empanadas-tradicionales-de-carne-argentina.jpg?s=612x612&w=0&k=20&c=or3Zwu4J4STFJJe7tt2VF3K6LOGsmHkfX0mfDFC4ghA=', 5, 'Carne, cebolla, ají picante, masa', 30, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(24, 'Flan Casero', 'Flan con dulce de leche y crema', 1200.00, 'https://media.istockphoto.com/id/463121717/es/foto/pudim-de-leite.jpg?s=612x612&w=0&k=20&c=mt-CMEvnV0ds7XuXnsttnY5r5aBm8gVw6WznUwCumGw=', 6, 'Huevos, leche, azúcar, dulce de leche', 60, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(25, 'Tiramisú', 'Tiramisú italiano tradicional', 1800.00, 'https://media.istockphoto.com/id/956986120/es/foto/postre-italiano-tiramis%C3%BA-con-queso-mascarpone-y-caf%C3%A9-espresso.jpg?s=612x612&w=0&k=20&c=OwlnQfxLK0eRLaHJcOUhBo-4v42pV4DME7FvcABHuHk=', 6, 'Mascarpone, café, vainillas, cacao', 30, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(26, 'Brownie', 'Brownie de chocolate con nueces', 1400.00, 'https://media.istockphoto.com/id/168731372/es/foto/casero-fresco-brownie-de-chocolate.jpg?s=612x612&w=0&k=20&c=6k5bqfgWqv3nUyTjoZilyi-917RLsdkMOoGITjulEqw=', 6, 'Chocolate, harina, huevos, nueces', 35, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(27, 'Cheesecake', 'Cheesecake con frutos rojos', 2000.00, 'https://media.istockphoto.com/id/1225491381/es/foto/tarta-de-queso-con-salsa-de-caramelo.jpg?s=612x612&w=0&k=20&c=wqwzAmxX93-QKgFt93PMq_EEYaSx95FkYeK3DaINjlI=', 6, 'Queso crema, galletas, frutos rojos', 240, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(28, 'Postre Balcarce', 'Postre Balcarce tradicional', 1600.00, 'https://www.cronica.com.ar/__export/1755190805834/sites/cronica/img/2025/08/14/torta_balcarce.jpg_683177495.jpg', 6, 'Bizcochuelo, dulce de leche, merengue', 90, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(29, 'Coca Cola 1.5L', 'Gaseosa Coca Cola 1.5 litros', 800.00, 'https://colanta.vtexassets.com/arquivos/ids/158639-1200-auto?v=638549392810570000&width=1200&height=auto&aspect=true', 7, NULL, 0, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(30, 'Agua Mineral 500ml', 'Agua sin gas', 500.00, 'https://media.istockphoto.com/id/1413583669/es/foto/botella-de-pl%C3%A1stico-de-agua-fr%C3%ADa-con-gotas-de-condensaci%C3%B3n-y-dos-cubitos-de-hielo-sobre-la.jpg?s=612x612&w=0&k=20&c=V84bb5K0NifoythA3MxllNsiog4wlXpg4dGJPcGQMLo=', 7, NULL, 0, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(31, 'Jugo de Naranja Natural', 'Jugo exprimido de naranja', 900.00, 'https://media.istockphoto.com/id/1225546255/es/foto/toma-de-jugo-de-naranja-fresco-en-un-vaso.jpg?s=612x612&w=0&k=20&c=nQd2QGhKLsc757sUArqLGho6mcrNgT6AtS8vdA1tFUs=', 7, 'Naranjas frescas', 5, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(32, 'Cerveza Quilmes 1L', 'Cerveza Quilmes litro', 1200.00, 'https://masonlineprod.vtexassets.com/arquivos/ids/272997-1200-auto?v=638875152644030000&width=1200&height=auto&aspect=true', 7, NULL, 0, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(33, 'Vino Tinto 750ml', 'Vino tinto de la casa', 1500.00, 'https://www.casa-segal.com/wp-content/uploads/2022/05/toro-clasico-tinto-750-ml-ofertas-en-mendoza-vinos-casa-segal-min.jpg', 7, NULL, 0, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(34, 'Jamón Cocido (kg)', 'Jamón cocido de primera calidad', 2800.00, 'https://media.istockphoto.com/id/1297622822/es/foto/pieza-de-jam%C3%B3n-de-1-kg-y-cuchillo-de-hoja-larga-afilada-sobre-una-tabla-de-cortar-sobre-mesa.jpg?s=612x612&w=0&k=20&c=xxbNHFa-74uHI_SgOn0_HJL-hcjBLsGg7FxSQcNxVvc=', 8, NULL, 0, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(35, 'Salame (kg)', 'Salame argentino', 3500.00, 'https://media.istockphoto.com/id/528296204/es/foto/fiambres-salame.jpg?s=612x612&w=0&k=20&c=jy35wPbHvIhgOjAZjDH678tZy5nmbnFRuG1dnQmAUXk=', 8, NULL, 0, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(36, 'Mortadela (kg)', 'Mortadela tradicional', 1800.00, 'https://media.istockphoto.com/id/907610468/es/foto/mortadela-salchicha-italiana-tradicional.jpg?s=612x612&w=0&k=20&c=03WP8Y5S6GPMuEmgSiCoImvIwnVTBufc5cHOjkUq204=', 8, NULL, 0, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(37, 'Queso Fresco (kg)', 'Queso crema untable', 2200.00, 'https://dfwblobstorage.blob.core.windows.net/ewcmediacontainer/eatwisconsincheese/media/content/cheesemasters-2019/quesofresco-header_3.jpg', 8, NULL, 0, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(38, 'Chorizo Colorado (kg)', 'Chorizo colorado parrillero', 2600.00, 'https://media.istockphoto.com/id/531034467/es/foto/chorizo.jpg?s=612x612&w=0&k=20&c=n3ckcOW06Z8ZqPIqv4sRZM1BigBqfOKL4OCpzwQPHTQ=', 8, NULL, 0, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(39, 'Hamburguesa Completa', 'Hamburguesa con papas fritas', 2800.00, 'https://media.istockphoto.com/id/1309352410/es/foto/hamburguesa-con-queso-con-tomate-y-lechuga-en-tabla-de-madera.jpg?s=612x612&w=0&k=20&c=HaSLXFFns4_IHfbvWY7_FX7tlccVjl0s0BrlqaLHOTE=', 9, 'Carne molida, pan, lechuga, tomate, queso, papas', 15, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(40, 'Pancho Completo', 'Pancho con papas fritas', 1500.00, 'https://viapais.com.ar/resizer/v2/KYHQYO3JBBDXLDH7SU5KITF7PM.jpg?quality=75&smart=true&auth=431d86ba6fd6f184c40f0fcd20bbba3608253d1c7916e71a2944c8da6868bade&width=980&height=640', 9, 'Salchicha, pan, salsas, papas', 10, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(41, 'Lomito Completo', 'Lomito con jamón, queso y huevo', 3200.00, 'https://pedidos.estoyresto.com.ar/wp-content/uploads/2025/01/lomo-completo.jpg', 9, 'Lomo, jamón, queso, huevo, pan', 15, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(42, 'Pizza Muzzarella', 'Pizza muzzarella individual', 2000.00, 'https://media.istockphoto.com/id/2023102269/es/foto/pizza-cubierta-con-albahaca-fresca-tomate-y-queso-encima-de-una-fuente-de-madera.jpg?s=612x612&w=0&k=20&c=AIgYPEdLfPu1C864eDlI42t_oedR3Evx3CzD9fE1teY=', 9, 'Masa, muzzarella, salsa', 20, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(43, 'Papas Fritas', 'Porción de papas fritas grandes', 1200.00, 'https://media.istockphoto.com/id/1443993866/es/foto/patatas-fritas-con-salsa-de-tomate-y-salsa-de-c%C3%B3ctel.jpg?s=612x612&w=0&k=20&c=33ts-FF9XW_jytzGoxw0mXvuMSi8oc3nYEdLaXu2VaU=', 9, 'Papas', 10, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(44, 'Pan Casero', 'Pan casero del día', 800.00, 'https://media.istockphoto.com/id/1320305161/es/foto/pan-de-masa-madre-en-rodajas-hecho-de-levadura-silvestre-cocinar-alimentos-saludables.jpg?s=612x612&w=0&k=20&c=JzzCG5qvAiPGF2NxyBR0xzlrwwmdrgPiTjBaXn4GS_4=', 10, 'Harina, levadura, sal', 120, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(45, 'Ensalada Rusa (kg)', 'Ensalada rusa casera', 1500.00, 'https://media.istockphoto.com/id/1151789416/es/foto/ensalada-tradicional-rusa-olivier-con-verduras-y-carne.jpg?s=612x612&w=0&k=20&c=u0WjE22PRQqS9tRyVwiYmq0_VovY7puck-KiYR6R0kQ=', 10, 'Papa, zanahoria, arvejas, mayonesa', 30, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(46, 'Pickles Caseros', 'Pickles variados en frasco', 1000.00, 'https://media.istockphoto.com/id/1582573020/es/foto/pepinos-encurtidos-caseros-con-ajo-r%C3%A1bano-picante-y-eneldo-en-frasco-de-vidrio-sobre-fondo-gris.jpg?s=612x612&w=0&k=20&c=mdCRD2r0biz09_wQJpCwPZldgjCCeJpPdi66-T1TmwM=', 10, 'Verduras mixtas, vinagre', 0, 1, 0.00, 0, 1, '2025-10-16 02:18:57'),
(47, 'Matambre Arrollado (kg)', 'Matambre arrollado casero', 4500.00, 'https://ninina.com/cdn/shop/products/NININA9-4205.jpg?v=1605831222&width=713', 10, 'Matambre, huevo, zanahoria, morrones', 90, 1, 0.00, 0, 1, '2025-10-16 02:18:57');

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
(1, 'Descuento 10%', '10% de descuento en productos seleccionados', 'descuento_porcentaje', 10.00, '2025-10-01', '2025-10-31', 1, 0.00),
(2, 'Promo empanadas', '3x2 en empanadas', 'descuento_fijo', 150.00, '2025-10-10', '2025-10-20', 1, 300.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguimiento_pedidos`
--

CREATE TABLE `seguimiento_pedidos` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `estado_anterior` enum('pendiente','confirmado','en_preparacion','listo','en_camino','entregado','cancelado') DEFAULT NULL,
  `estado_nuevo` enum('pendiente','confirmado','en_preparacion','listo','en_camino','entregado','cancelado') NOT NULL,
  `usuario_cambio_id` int(11) DEFAULT NULL,
  `fecha_cambio` timestamp NOT NULL DEFAULT current_timestamp(),
  `comentarios` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historial de cambios de estado del pedido';

--
-- Volcado de datos para la tabla `seguimiento_pedidos`
--

INSERT INTO `seguimiento_pedidos` (`id`, `pedido_id`, `estado_anterior`, `estado_nuevo`, `usuario_cambio_id`, `fecha_cambio`, `comentarios`) VALUES
(1, 1, 'pendiente', 'confirmado', 2, '2025-10-15 20:54:59', 'Pedido confirmado por cajero'),
(2, 1, 'confirmado', 'en_preparacion', 3, '2025-10-15 20:54:59', 'Cocinero comenzó preparación'),
(5, 4, NULL, 'pendiente', 9, '2025-10-21 04:26:17', 'Pedido creado por el cliente'),
(16, 1, 'en_preparacion', 'listo', 10, '2025-10-20 22:39:46', 'Estado cambiado por cocinero'),
(17, 1, 'listo', 'en_camino', 10, '2025-10-20 22:39:51', 'Pedido asignado a repartidor'),
(18, 15, NULL, 'confirmado', 9, '2025-10-20 22:40:21', 'Pedido creado por el cliente'),
(19, 15, 'en_preparacion', 'listo', 10, '2025-10-21 14:02:36', 'Estado cambiado por cocinero'),
(20, 15, 'listo', 'en_camino', 10, '2025-10-21 14:02:40', 'Pedido asignado a repartidor'),
(21, 4, 'pendiente', 'en_preparacion', 1, '2025-10-21 15:15:38', 'Pedido programado activado automáticamente'),
(22, 16, NULL, 'pendiente', 9, '2025-10-21 06:17:26', 'Pedido creado por el cliente'),
(23, 16, 'pendiente', 'en_preparacion', 1, '2025-10-21 18:03:19', 'Pedido programado activado automáticamente'),
(24, 4, 'en_preparacion', 'listo', 10, '2025-10-21 18:28:59', 'Estado cambiado por cocinero'),
(25, 15, 'en_camino', 'entregado', 11, '2025-10-21 19:23:28', 'Pedido entregado al cliente'),
(26, 16, 'en_preparacion', 'listo', 10, '2025-10-21 19:52:43', 'Estado cambiado por cocinero'),
(27, 4, 'listo', 'en_camino', 10, '2025-10-21 19:52:46', 'Pedido asignado a repartidor'),
(28, 4, 'en_camino', 'entregado', 11, '2025-10-21 19:53:28', 'Pedido entregado al cliente'),
(29, 17, NULL, 'en_preparacion', 9, '2025-10-23 03:46:06', 'Pedido creado por el cliente'),
(30, 17, 'en_preparacion', 'listo', 10, '2025-10-23 03:46:56', 'Estado cambiado por cocinero'),
(31, 17, 'listo', 'en_camino', 10, '2025-10-23 03:46:59', 'Pedido asignado a repartidor'),
(32, 17, 'en_camino', 'entregado', 11, '2025-10-23 03:47:19', 'Pedido entregado al cliente'),
(33, 17, 'entregado', 'cancelado', 9, '2025-10-23 03:48:46', 'Cliente reportó no recepción. Motivo: Pedido en mal estado'),
(34, 18, NULL, 'en_preparacion', 9, '2025-10-23 04:17:09', 'Pedido creado por el cliente'),
(35, 18, 'en_preparacion', 'listo', 10, '2025-10-23 04:19:29', 'Estado cambiado por cocinero'),
(36, 18, 'listo', 'en_camino', 10, '2025-10-23 04:19:33', 'Pedido asignado a repartidor'),
(37, 18, 'en_camino', 'entregado', 11, '2025-10-23 04:20:03', 'Pedido entregado al cliente'),
(38, 19, NULL, 'en_preparacion', 9, '2025-10-23 04:23:07', 'Pedido creado por el cliente'),
(39, 19, 'en_preparacion', 'listo', 10, '2025-10-23 04:23:23', 'Estado cambiado por cocinero'),
(40, 19, 'listo', 'en_camino', 10, '2025-10-23 04:24:59', 'Pedido asignado a repartidor'),
(41, 19, 'en_camino', 'entregado', 11, '2025-10-23 04:25:11', 'Pedido entregado al cliente'),
(42, 16, 'listo', 'en_camino', 10, '2025-10-23 05:21:06', 'Pedido asignado a repartidor'),
(61, 38, NULL, 'en_preparacion', 9, '2025-10-23 06:46:08', 'Pedido creado por el cliente'),
(62, 39, NULL, 'en_preparacion', 9, '2025-10-23 06:46:09', 'Pedido creado por el cliente'),
(71, 38, 'en_preparacion', 'listo', 10, '2025-10-23 07:04:45', 'Estado cambiado por cocinero'),
(72, 16, 'en_camino', 'entregado', 11, '2025-10-23 07:05:17', 'Pedido entregado al cliente'),
(73, 39, 'en_preparacion', 'listo', 10, '2025-10-23 07:05:56', 'Estado cambiado por cocinero'),
(74, 39, 'listo', 'en_camino', 10, '2025-10-23 07:05:59', 'Pedido asignado a repartidor'),
(75, 39, 'en_camino', 'entregado', 11, '2025-10-23 07:06:18', 'Pedido entregado al cliente'),
(76, 47, NULL, 'pendiente', 9, '2025-10-24 14:11:06', 'Pedido creado por el cliente'),
(77, 47, 'pendiente', 'en_preparacion', 1, '2025-10-24 14:11:22', 'Pedido programado activado automáticamente'),
(78, 47, 'en_preparacion', 'listo', 10, '2025-10-24 14:12:10', 'Estado cambiado por cocinero'),
(79, 38, 'listo', 'en_camino', 10, '2025-10-24 14:12:14', 'Pedido asignado a repartidor'),
(80, 38, 'en_camino', 'entregado', 11, '2025-10-24 14:12:41', 'Pedido entregado al cliente'),
(81, 47, 'listo', 'en_camino', 10, '2025-10-24 14:14:56', 'Pedido asignado a repartidor'),
(82, 47, 'en_camino', 'entregado', 11, '2025-10-24 14:15:15', 'Pedido entregado al cliente'),
(83, 48, NULL, 'pendiente', 9, '2025-10-24 14:18:58', 'Pedido creado por el cliente'),
(84, 49, NULL, 'pendiente', 9, '2025-10-24 14:19:26', 'Pedido creado por el cliente'),
(85, 50, NULL, 'pendiente', 9, '2025-10-24 14:19:55', 'Pedido creado por el cliente'),
(86, 48, 'pendiente', 'en_preparacion', 1, '2025-10-24 05:24:47', 'Pedido programado activado automáticamente'),
(87, 49, 'pendiente', 'en_preparacion', 1, '2025-10-24 05:24:47', 'Pedido programado activado automáticamente'),
(88, 50, 'pendiente', 'en_preparacion', 1, '2025-10-24 05:24:47', 'Pedido programado activado automáticamente'),
(89, 51, NULL, 'en_preparacion', 12, '2025-10-24 05:29:10', 'Pedido creado por el cliente'),
(90, 51, 'en_preparacion', 'listo', 13, '2025-10-24 05:34:11', 'Estado cambiado por cocinero'),
(91, 50, 'en_preparacion', 'listo', 13, '2025-10-24 05:34:37', 'Estado cambiado por cocinero'),
(92, 50, 'listo', 'en_camino', 13, '2025-10-24 05:34:44', 'Pedido asignado a repartidor'),
(93, 48, 'en_preparacion', 'listo', 13, '2025-10-24 05:35:27', 'Estado cambiado por cocinero'),
(94, 50, 'en_camino', 'entregado', 11, '2025-10-24 05:37:01', 'Pedido entregado al cliente'),
(95, 48, 'listo', 'en_camino', 10, '2025-10-24 05:38:04', 'Pedido asignado a repartidor'),
(96, 52, NULL, 'en_preparacion', 17, '2025-10-24 22:05:28', 'Pedido creado por el cliente'),
(97, 53, NULL, 'en_preparacion', 17, '2025-10-24 22:06:27', 'Pedido creado por el cliente'),
(98, 54, NULL, 'en_preparacion', 16, '2025-10-25 06:05:04', 'Pedido creado por el cliente'),
(99, 55, NULL, 'en_preparacion', 9, '2025-10-20 06:33:11', 'Pedido creado por el cliente'),
(100, 55, 'en_preparacion', 'listo', 10, '2025-10-20 06:33:11', 'Estado cambiado por cocinero'),
(101, 55, 'listo', 'en_camino', 10, '2025-10-20 06:33:11', 'Pedido asignado a repartidor'),
(102, 55, 'en_camino', 'entregado', 11, '2025-10-20 06:33:11', 'Pedido entregado al cliente'),
(103, 56, NULL, 'en_preparacion', 9, '2025-10-22 06:33:11', 'Pedido creado por el cliente'),
(104, 56, 'en_preparacion', 'listo', 10, '2025-10-22 06:33:11', 'Estado cambiado por cocinero'),
(105, 56, 'listo', 'en_camino', 10, '2025-10-22 06:33:11', 'Pedido asignado a repartidor'),
(106, 56, 'en_camino', 'entregado', 11, '2025-10-22 06:33:11', 'Pedido entregado al cliente'),
(107, 57, NULL, 'en_preparacion', 9, '2025-10-24 06:33:11', 'Pedido creado por el cliente'),
(108, 57, 'en_preparacion', 'listo', 10, '2025-10-24 06:33:11', 'Estado cambiado por cocinero'),
(109, 57, 'listo', 'en_camino', 10, '2025-10-24 06:33:11', 'Pedido asignado a repartidor'),
(110, 57, 'en_camino', 'entregado', 11, '2025-10-24 06:33:11', 'Pedido entregado al cliente'),
(112, 59, NULL, 'en_preparacion', 16, '2025-10-25 06:56:50', 'Pedido repetido'),
(115, 62, NULL, 'en_preparacion', 16, '2025-10-25 07:31:32', 'Pedido repetido'),
(126, 73, NULL, 'en_preparacion', 16, '2025-10-25 18:33:55', 'Pedido creado por el cliente'),
(127, 73, 'en_preparacion', 'listo', 13, '2025-10-25 22:23:18', 'Estado cambiado por cocinero'),
(128, 74, NULL, 'en_preparacion', 16, '2025-10-25 22:23:22', 'Pedido creado por el cliente'),
(129, 73, 'listo', 'en_camino', 13, '2025-10-25 22:26:03', 'Pedido asignado a repartidor'),
(130, 74, 'en_preparacion', 'listo', 13, '2025-10-25 22:26:24', 'Estado cambiado por cocinero'),
(131, 74, 'listo', 'en_camino', 13, '2025-10-25 22:26:30', 'Pedido asignado a repartidor'),
(132, 73, 'en_camino', 'entregado', 14, '2025-10-25 22:28:08', 'Pedido entregado al cliente'),
(133, 74, 'en_camino', 'entregado', 21, '2025-10-25 22:29:15', 'Pedido entregado al cliente'),
(134, 52, 'en_preparacion', 'listo', 13, '2025-10-25 22:47:43', 'Estado cambiado por cocinero'),
(135, 53, 'en_preparacion', 'listo', 13, '2025-10-25 22:48:12', 'Estado cambiado por cocinero'),
(136, 75, NULL, 'en_preparacion', 17, '2025-10-25 22:55:45', 'Pedido creado por el cliente'),
(137, 75, 'en_preparacion', 'listo', 13, '2025-10-25 22:56:05', 'Estado cambiado por cocinero'),
(138, 76, NULL, 'pendiente', 17, '2025-10-25 22:59:42', 'Pedido creado por el cliente'),
(139, 76, 'pendiente', 'en_preparacion', 1, '2025-10-25 23:00:24', 'Pedido programado activado automáticamente - Hora de entrega alcanzada'),
(140, 76, 'en_preparacion', 'listo', 13, '2025-10-25 23:01:32', 'Estado cambiado por cocinero'),
(141, 75, 'listo', 'en_camino', 13, '2025-10-25 23:01:59', 'Pedido asignado a repartidor'),
(142, 76, 'listo', 'en_camino', 13, '2025-10-25 23:02:09', 'Pedido asignado a repartidor'),
(143, 75, 'en_camino', 'entregado', 21, '2025-10-25 23:02:15', 'Pedido entregado al cliente'),
(144, 76, 'en_camino', 'entregado', 21, '2025-10-25 23:21:32', 'Pedido entregado al cliente'),
(145, 76, 'entregado', 'cancelado', 17, '2025-10-25 23:21:49', 'Cliente reportó no recepción. Motivo: Pedido incompleto'),
(146, 77, NULL, 'en_preparacion', 22, '2025-10-26 03:51:06', 'Pedido creado por el cliente'),
(147, 77, 'en_preparacion', 'listo', 13, '2025-10-26 04:01:54', 'Estado cambiado por cocinero'),
(148, 78, NULL, 'en_preparacion', 22, '2025-10-26 04:05:18', 'Pedido creado por el cliente'),
(149, 78, 'en_preparacion', 'listo', 13, '2025-10-26 04:06:59', 'Estado cambiado por cocinero'),
(150, 77, 'listo', 'en_camino', 13, '2025-10-26 04:22:14', 'Pedido asignado a repartidor'),
(151, 77, 'en_camino', 'entregado', 21, '2025-10-26 04:22:50', 'Pedido entregado al cliente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `foto_perfil` text DEFAULT NULL,
  `telefono` varchar(20) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `rol` enum('cliente','cajero','cocinero','repartidor','administrador') DEFAULT 'cliente',
  `estado_disponibilidad` tinyint(1) DEFAULT 1 COMMENT 'Para repartidores',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `vip` tinyint(1) NOT NULL DEFAULT 0,
  `comida_favorita` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Usuarios del sistema con diferentes roles';

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `foto_perfil`, `telefono`, `contraseña`, `rol`, `estado_disponibilidad`, `activo`, `fecha_registro`, `vip`, `comida_favorita`) VALUES
(1, 'Bruno', 'Pavon', 'admin@rotiseria.com', NULL, '1122334455', 'admin123', 'administrador', 1, 1, '2025-10-15 20:54:59', 0, NULL),
(2, 'María', 'Gomez', 'cajero1@rotiseria.com', NULL, '1166677788', 'cajero123', 'cajero', 1, 1, '2025-10-15 20:54:59', 0, NULL),
(3, 'Juan', 'Lopez', 'cocinero1@rotiseria.com', NULL, '1177788999', 'cocinero123', 'cocinero', 1, 1, '2025-10-15 20:54:59', 0, NULL),
(4, 'Ana', 'Martinez', 'repartidor1@rotiseria.com', NULL, '1199988777', 'repartidor123', 'repartidor', 1, 1, '2025-10-15 20:54:59', 0, NULL),
(5, 'Carlos', 'Perez', 'cliente1@gmail.com', NULL, '1144556677', 'cliente123', 'cliente', 1, 1, '2025-10-15 20:54:59', 0, NULL),
(6, 'Lucía', 'Diaz', 'cliente2@gmail.com', NULL, '1144557788', 'cliente456', 'cliente', 1, 1, '2025-10-15 20:54:59', 0, NULL),
(7, 'admin', 'admin', 'admin@system.com', NULL, '01162165019', '$2y$10$GyN32IT5ITcIrsq3RzkqM.y7zLRQsigGbCFFzp43jZGzTWFyynPWG', 'administrador', 1, 1, '2025-10-15 20:59:56', 0, NULL),
(9, 'LUCIANO', 'CAMPANELLI', 'luchocampanelli1@gmail.com', 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAYGBgYHBgcICAcKCwoLCg8ODAwODxYQERAREBYiFRkVFRkVIh4kHhweJB42KiYmKjY+NDI0PkxERExfWl98fKcBBgYGBgcGBwgIBwoLCgsKDw4MDA4PFhAREBEQFiIVGRUVGRUiHiQeHB4kHjYqJiYqNj40MjQ+TERETF9aX3x8p//CABEIAyUCHgMBIgACEQEDEQH/xAAtAAEBAQEBAQEBAAAAAAAAAAAABAUDAgEGBwEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEAMQAAAC/VERamFKYUphSmFKYUphSmFKYUphSmFKYUphSmFKYUphSmFKYUphSmFKYUphSmFKYUphSmFKYUphSmFKYUphSmFKYUpKxNTMUgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAlqlqE1MxSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACWqWoTUzFIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAJapahNTMUgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAlqlqE1MxSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACWqWoTUzFIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAJapahNTMUgAAAAAAAAAAAAAAAAAAAAAAAMfwa2b70j8t+rx9gAAAAAlqlqE1MxSAAAAAAAAAAAAAAAAAAAAAAcxhe948ewGOfNnx7AAAAAJapahNTMUgAAAAAAAAAAAAAAAAAAAAA+YX3dABGeHPTAAAAAAJapahNTMUgAAAAAAAAAAAAAAAAAAAAZFsp70cTbB4OEfPaAAAAAAAJapahNTMUgAAAAAAAAAAAAAAAAAAAc/uKdNkPz12nhm5hx/pT2AAAAAAACWqWoTUzFIAAAAAAAABnmgyvhrPn0AGYabIGuyfJsPz9hqPPoATS/DjtAAkrwyzQAAAAAAAACWqWoTUzFIAAAAAAMvhTaZP3X+GLx/SeDj7z9YyfmuMf5sjI1Mr0azh3AIvl2UfPmuMjluD89bqeD19xuRvOfQzbMzZAAAAAAAAAJapahNTMUgAAAAAAZWqMSi3LPvTVyT7pxSmyQnbO56xm7IZGszjSAytXKNUBwxzsez7793mHx/RTHvti7QAAAAAAAABLVLUJqZikAAAAAAACWoSV42wZfCPROc/b2etDpKXIbhl2ci0HDhy0j7n+KTP+etkAxKumeboPmTrjK864ws7U2j8913JCti6JSfD6zPppAAAlqlqE1MxSAAAAAAAADx+Z/R/lDru8fzJLpfpPx5x/oH4yo2NZin3ZmpAMm6EddF8MvVzdII4Da/L3/DW6/k/1R6AzdLGNX1Pnnvxs5RrRRfQey+XRwzW7AABLVLUJqZikAAAAAAACanEOPbUgNb8htYZ+k/nf9K/m5+hw+Fx/Q3DmVuHcc+mQduerxOzO0TH87Qg6ePyZYyqj9BP+X/flQOWb1zC3YxNsAAIojxsdQAABLVLUJqZikAAAAAAADH2M80MjryPv3nsn435+z8H4be3uJA8bAAyGqegYu1yhNPx7/MkuV+q/GH7L8dz+n6dpRH6QGUQmvZP2PTC9GtkUahBeAAAAEtUtQmpmKQAAAAAAAARZn6DFO+jOKgML7YX5vHWM2/piFWmADI1/hzk46x+N5ftvzB+Y/USfSzZg2wDKjo1j14/JfsTN0sfYAAAAAAJapahNTMUgAAAAAAAAZOtkFrrmGxk9PZw6z7IAAAABka8FB3AAAPhkbGbpGPdVjlNPTMNUAAAAAEtUtQmpmKQAAAAAAAAMnW5H3A4/qTOn0ORcZRqscbDHGwxvZrPn0AYu1MUs3SAAOGf8Gn1Rk8/3bGVq5RqgAAAAAlqlqE1MxSAAAAAAAAfD6l7GB+jxuhz2fHsyNLM0zq59AB8+jD1u2KbQAJZNXidmRrghJZtfIPOp5tOgGTrZBrgAAAAAlqlqE1MxSAAAAAAABLUIPGjknOSjSLPHv4YFEtA0+Aq6ZOkTcfPM2cHe/In64AAEfqrFNnH57pzjk+G5ka+SawPGb41z6AAAAACWqWoTUzFIAAAAAAAAM67jnE36PI1wDG2eWUdNb59MrSzdUxarOhjeKO5aAZhpgYW6Bimb+hg1jP0sXcM3SyOR72QAAAAAAlqlqE1MxSAAAAAAAACG5zMy2PwbIHz6MlrQk32D9GZfnYHDuADKumNAkK2dMU+unA86nP0Ym7gb5gbWfqgAAAAAAEtUtQmpmKQAAAAAAAAMTprjj2GBRV6M7roZR383fDho5vE2GR3NAAyifrZYc/z9O2fnO/TXPuR68muDJ1snWMrVydYAAAAAAAlqlqE1MxSAAAAAAAB8+5R60wAZ2iJasu0i57AyGuMnntDG962OeaPFBPz2PoBka+doHzN+6J6Bka+PsGVq5GuAAAAAAAS1S1CamYpAAAAAAAAydbJNYAADN0hj6XaAvZP01MxpGTregAAAIiXXlqAMjXlnOOxnaIAAAAAABLVLUJqZikAAAAAAAHKXzcewAAAAAAAAAEkJoxetMAAflv1MRalqAAAAAAAJapahNTMUgAAAAAAAydbI1wAAAAAAAAADj2AAAADJ1srVAAAAAAAJapahNTMUgAAAAAAAy9TK1QAAAAAAAAAAAAAADK1cvTPoAAAAAAJapahNTMUgAAAAAAA85WvMUsfYAAAAAAAAAAAAAAMnWyNcAAAAAAAlqlqE1MxSAAAAAAAADxj7YkrjjNgAAAAAAAAAAAA4GdsZ+gAAAAAAAS1S1CamYpAAAAAAAAAA8+hj7HGI0wAAAAAAAAAAMbR4F4AAAAAAAJapahNTMUgAAAAAAAAAAfm9Kw42SRmu+fQAAAAAAAA5ZJ92gAAAAAAAAlqlqE1MxSAAAAAAAAAADJoy9Ys+fRh7nnFNx8+gAAzDTYnY1WP9NdkQm3n3WGVqgAAAAAAAABLVLUJqZikAAAAAAAAAAGRr5eeWbEsBJTqdzG66nw4uPw88NLqY+p0GRr5OsAMrVyTWAAAAAAAAAABLVLUJqZikAAAAAAAAAACegZGt9AAAAAH5jen7lTJ1jJcqjRAAAAAAAAAABLVLUJqZikAAAAAAAAAAAAAAAAAHzK1pDL38zkNjz6AAAAAAAAAAAJapahNTMUgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAlqlqE1MxSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACWqWoTUzFIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAJapahNTMUgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAlqlqE1MxSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACWqWoTUzFIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAJapahNTMUgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAlqlqE1MxSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACWqWoTUzFJEWvz+oWOA7uP06sKk1HDLNtm1ndw9nRjdTUZGuAAAAAAAAAAAAAAAAAAAAAAS1S1CSscPzv6nJOkHDuQW/KCazxaZ9eZ1Junio8c+/E+28qzI7+Opw3sjfJlQlVCVUJVQlVCVUJVQlVCVUJVQlVCVUJVQlVCVUJVQlVCVUJVQlVCVUJVQlqAADz6D59AD59D59B8D6AAAAAAAAAAAAAAAAAAAAAAAAAD//EAAL/2gAMAwEAAgADAAAAIRAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAAICAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAMAAKAAAAAAFAAAAAAAAAAAAAAAAAAAAAAMAAOAAAAAAAFAAAAAAAAAAAAAAAAAAAAAGAAMAAAAAAAAFAAAAAAAAAAAAAAAAAAAACAMCAAAAAAAAAFAAAAAAAAAAACAADACCAFAABIAAAAAAAAAFAAAAAAABFNMIMKAAMAEBBCKAAAAAAAAAAFAAAAAAAEDNJBAAEAAADJAJCAAAAAAAAAAFAAAAAAAAAAFNAACANAIAIAEIGNDAAAAAAFAAAAAAAAAPKGKAAAPICCPJABOODDEAAAAFAAAAAAAABHGBNACFJCIMIIANCAACEAAAAFAAAAAAAABFAIFMAHAJBEJIAOBBAIAAAAAFAAAAAAAAAAJAGJOAAFELKCAGOIAAAAAAAFAAAAAAAAABPHOAAAAADAAAAIINAAAAAAAFAAAAAAAAAEABBABCCAECAADIOBAAAAAAAFAAAAAAAAACAAFCAAIAABCBGMABAAAAAAAFAAAAAAAAEIGBALKPBAAABEIAAHAAAAAAAFAAAAAAAAAFIAIIEADAAAABPAJAAAAAAAAFAAAAAAAAABFAEBPEAADADJAKIAAAAAAAAFAAAAAAAAAAAHHBJBAAGIPHAAAAAAAAAAAFAAAAAAAABIAAKEMAMFKAIIACCAAAAAAAAFAAAAAAAAFAAAEMCAMAAAAAABKAAAAAAAAFAAAAAAAAFAAAAAAAAAADEAAFAAAAAAAAAFAAAAAAAAKAAAAAAAAAAIAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAIAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAKAAAAAAAAFAAAAAAAAAEAAAAAAAAAAAAABIAAAAAAAAFAAAAAAAAAAEBAAAAAAAAAAACAAAAAAAAAFAAAAAAAAAAAHBCAAAAAAAACAAAAAAAAAAFAAAAAAAAAAAJELCAAADBBCIAAAAAAAAAAFAAAAAAAAAAAABMIGAEAAFAAAAAAAAAAAAFAAAAAAAAAAAEAAAAAADCPAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAJACAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFACBCCABCCCAAAAAAAAAAAAAAAAAAAAAAAAJNMINOBPGEAAAAAAAAAAAAAAAAAAAAAAAAAIIAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAP/8QAAv/aAAwDAQACAAMAAAAQ4888888888888888888888888888888880o88888888888888888888888888888888Uo88888888888888888888888888888888Uo88888888888888888888888888888888Uo88888888888888888888888888888888Uo88888888888888888888888888888888Uo88888888888888888888888888888888Uo8888888888888888888888884wc88888Uo88888888888888888888884sc4888888Uo8888888888888888888888g84M888888Uo8888888888888888888888U408888888Uo88888888888888888888scso88888888Uo8888888880w080w80084c8sc88888888Uo8888888o4kM84I88oM8M48M888888888Uo88888888UYI8IcI888kMUo4888888888Uo88888888s00YA0E84wEcE8sY0M008888Uo888888888wcY4E08ssc4U48o0QA0Y888Uo88888888oswQUw044ccMgI8wU88kU888Uo88888888soU8w88s8U4skk8oc8Uc8888Uo8888888888U8UoIc8oME0U8IcU888888Uo888888888s04s88888c8888UY0888888Uo8888888884oU4w0w88s088s4wc888888Uo8888888844YY008s888c880c84888888Uo88888888sc8soAcs8888cQA80s888888Uo888888888MU8Y8Uc8888MsYME0888888Uo8888888888E88M0s88w40gA8k8888888Uo888888888wsAYkY084I08k80Q8888888Uo88888888sc8sUs8Mckc8Yg8cc8888888Uo88888888o88884wgM8884U8008888888Uo88888888U88888888884s88s88888888Uo88888888c8888888888c8888U8888888Uo88888888U888888888888888c8888888Uo88888888Yw88888888888888c8888888Uo888888888M08888888888888U8888888Uo8888888888sQ88888888888s88888888Uo88888888888kY8888888884c88888888Uo88888888888c8s888848wUc888888888Uo88888888888U0sY00M88888888888888Uo888888888888M88888I0M88888888888Uo8888888888888888888c088888888888Uo88888888888888888888888888888888Uo88888888888888888888888888888888Uo88888888888888888888888888888888Uo88888888888888888888888888888888Uo88888888888888888888888888888888Uo88888888888888888888888888888888Uo88888888888888888888888888888888Uo88888888888888888888888888888888Uo808880804w8888888888888888888888Uso8YQAUs0UYMMMMMMMMMMMMMMM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fIoLbY7kwoAqwFWg/wCBSQaJKyLlelNnGUmvKvso0mVATjgyP/ihBoGgaBoGgaBoGgaBoGgaBoGgaBoGgajOK4gldDrRlc6nallJ5lXcDCluc3uQc7oq1BCUFk6uFPdSiwF0nCr7ij3l2KSEObByiig4m5Gp51rV9FsaxSNAQKciI8cS8aUEkNOPvCaYieQuM5tqwMbIZBryrobatVa0ygecZMYoPObMad/Q9zzALGy58tycq2/Fv9qufeznXqKC6D3TR+S6iEzd5rO1SkeTTvdxjKaPzGMg0qpTN1QxJ6Vg64CYRURpsXmWTYoL17WNtDCoy5jmaYqRAwDFmwVC1l/JDjV3s3IjPapXMYvMjfkVdlKuEGD1qQk4c+2vt9aNc3NKNvLUkkkkkkkkkkkkkkkkkkkkktDYHcPCmrlkJzgaq/UG5UqLSxN4hy3r9eK+Ks/xosKDslJD0axNsitLsfJSNa48uJUMxiu5ybNqORSXB1U1soYTBOVbIsBvVoZlHCo5ZU3RICTStUJZMriAF2h5UPczQci6OJY1ee3WOG1IdxCeh5n6poTwZkLqAVDQqGnD17CaAQEFN53UJqDSlCEI0r0rCKhpwgMqhpUNPMP/xAAqEAEAAgEDAwMFAQEBAQEAAAABABExIUFREEBhIHGBMFBgkaGxwdHw8f/aAAgBAQABPxD8FQgT2N0Rn7G169evXr169evXr169evXr169evXr169evXr169et77Xr169evXr169evXr169evXr169evXr169esbhSGdlevxvwSzjfglnG/BLON+CWcb8Es434JZxvwSzjfglnG/BLON+CWcb8Es434JZxvudokUMIxti9io4rFGytYKA+scb7hZVgKloAys3BruPMyBvZZ18MpcryvheEIZjHiJo+uON9vsX26uVcBuojNu0UPM7a7/PlZXl6iZeQ0Aaqxe5Tnb6+cb7dZfTfKYPdRl6rfHGQAAAoD0ViBPnC/MmO4Jvrhxvtt4SniAtWPAd582/SF/wByCn88bVM32HRxvttmXGw54eGASNQCgAwHorl5RF/5Bnk/nuxzjfbL1dofdS8Tbv8AKq19Frnk64nQbU7vxw9lON9rt3SjxHM5hQ3QvDlu97l0AIIlidD8uN2P+rsR8Ngw9ns+ON9qu52N/wCAbrYjyjpiv07M296nuPvolyVSqQAL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', '01162165019', '$2y$10$i51lcdxeyTc2n/.NVo6WAeDOKojGS.0hY12tl9yfo4UhBtEfl0nVq', 'cliente', 1, 1, '2025-10-21 04:25:12', 0, NULL),
(10, 'Juan Carlos', 'Perez', 'cocinero20@rotiseria.com', NULL, '01162165019', '$2y$10$gncQw4dGqdGgvPfbXiPPTu6ZUQV7UeyHLjNRyEDXNXUlDkvOu1pRO', 'cocinero', 1, 1, '2025-10-21 04:28:16', 0, NULL),
(11, 'Martin', 'Silvero', 'repartidor@rotiseria.com', NULL, '01162165019', '$2y$10$Zi7ZiL0b8GqV11SE7sbRi.wFlB47.PJ8GZ9by7wENIpOeotCNisc2', 'repartidor', 1, 1, '2025-10-20 23:12:28', 0, NULL),
(12, 'Quico', 'Federico', 'qf@gmail.com', NULL, '8888888888', '$2y$10$/mQN4FV1f.DodmmlkSfrvOvaAjb/zGwZLwEO4Ba4iB6ZwTJ7PrG.i', 'cliente', 1, 1, '2025-10-24 05:25:56', 0, NULL),
(13, 'La Chilindrina', 'Ramon', 'lcr@gmail.com', NULL, '8888888888', '$2y$10$4huI7ytzoXNKLjntpuOmX.5z/tj.ez0r.EAQKCyUzHBshJMJ4o2jm', 'cocinero', 1, 1, '2025-10-24 05:30:33', 0, NULL),
(14, 'Profesor', 'Jirafales', 'pf@gmail.com', NULL, '8888888888', '$2y$10$3Gd2FZ3.tOYevhSXLnqCGukWXx1.OQHK3fV87621S4eO./.n9Zm1a', 'repartidor', 1, 1, '2025-10-24 05:37:13', 0, NULL),
(15, 'Senior', 'Barriga', 'sb@gmail.com', NULL, '8888888888', '$2y$10$cln.ScTVPBGuwy0dYJJIk.0XV82Ww32DnP1NcsiVTMVLiXuPaGJGm', 'repartidor', 1, 1, '2025-10-24 05:37:51', 0, NULL);
INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `foto_perfil`, `telefono`, `contraseña`, `rol`, `estado_disponibilidad`, `activo`, `fecha_registro`, `vip`, `comida_favorita`) VALUES
(16, 'Jheysmar', 'Mendieta', 'b3140269@gmail.com', 'data:image/png;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/4RPsRXhpZgAATU0AKgAAAAgACwEAAAMAAAABDkAACAEBAAMAAAABCrAABQECAAMAAAADAAAIngENAAcAAAAACAAIAAEPAAIAAAAHAAAIpAEQAAIAAAAIAAAIrAExAAIAAAAfAAAItAEyAAIAAAAUAAAI1AITAAMAAAABAAEACIdpAAQAAAABAAAI6OocAAcAAAgMAAAAkgAAAAAc6gAAAAgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIAAgACEhVQVdFSQAAQ0xULUwyOQBDTFQtTDI5IDEwLjAuMC4yMDEoQzQzMkU4UjFQMykAADIwMjI6MDc6MDcgMTM6MjE6NDcAACuCmgAFAAAAAQAAEv6CnQAFAAAAAQAAEwaIIgADAAAAAQACAACIJwADAAAAAQBAAACQAAAHAAAABDAyMTCQAwACAAAAFAAAEw6QBAACAAAAFAAAEyKRAQAHAAAABAECAwCRAgAFAAAAAQAAEzaSAQAFAAAAAQAAEz6SAgAFAAAAAQAAE0aSAwAFAAAAAQAAE06SBAAFAAAAAQAAE1aSBQAFAAAAAQAAE16SBwADAAAAAQAFAACSCAADAAAAAQABAACSCQADAAAAAQAYAACSCgAFAAAAAQAAE2aSfAAHAAAAZAAAE26SkAACAAAABwAAE9KSkQACAAAAAzY5AACSkgACAAAAAzY5AACgAAAHAAAABDAxMDCgAQADAAAAAQABAACgAgAEAAAAAQAADkCgAwAEAAAAAQAACrCiFwADAAAAAQACAACjAAAHAAAAAQMAAACjAQAHAAAAAQEAAACkAQADAAAAAQABAACkAgADAAAAAQAAAACkAwADAAAAAQAAAACkBAAFAAAAAQAAE9qkBQADAAAAAQAbAACkBgADAAAAAQAAAACkBwADAAAAAQAAAACkCAADAAAAAQAAAACkCQADAAAAAQAAAACkCgADAAAAAQAAAACkCwAHAAAABGlwcACkDAADAAAAAQAAAADqHAAHAAAIDAAACvLqHQAJAAAAAQAAEDwAAAAAHOoAAAAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABMS0AO5rKAAAAALQAAABkMjAyMjowNzowNyAxMzoyMTo0NwAyMDIyOjA3OjA3IDEzOjIxOjQ3AAAAAF8AAABkAASP3QAAJxAAAACpAAAAZAAAAAAAAAABAAAAAAAAAAoAAACpAAAAZAAAFcwAAAPoIyMjIwoAAACuyDMBAAABAAAAAAAAAAAAAAAAAAAAAABBAAAA/////////////////////////////////////////////////////////////////////////////////////zY5ODY3MgAAAAAAZAAAAGQAAP/hCeFodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvADw/eHBhY2tldCBiZWdpbj0n77u/JyBpZD0nVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkJz8+DQo8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIj48cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPjxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSJ1dWlkOmZhZjViZGQ1LWJhM2QtMTFkYS1hZDMxLWQzM2Q3NTE4MmYxYiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIj48eG1wOkNyZWF0ZURhdGU+MjAyMi0wNy0wN1QxMzoyMTo0Ny42OTA8L3htcDpDcmVhdGVEYXRlPjx4bXA6Q3JlYXRvclRvb2w+Q0xULUwyOSAxMC4wLjAuMjAxKEM0MzJFOFIxUDMpPC94bXA6Q3JlYXRvclRvb2w+PC9yZGY6RGVzY3JpcHRpb24+PC9yZGY6UkRGPjwveDp4bXBtZXRhPg0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8P3hwYWNrZXQgZW5kPSd3Jz8+/9sAQwABAQEBAQEBAQEBAQEBAgIDAgICAgIEAwMCAwUEBQUFBAQEBQYHBgUFBwYEBAYJBgcICAgICAUGCQoJCAoHCAgI/9sAQwEBAQECAgIEAgIECAUEBQgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgI/8AAEQgDhwS0AwEiAAIRAQMRAf/EAB8AAAEFAQEBAQEBAAAAAAAAAAABAgMEBQYHCAkKC//EALUQAAIBAwMCBAMFBQQEAAABfQECAwAEEQUSITFBBhNRYQcicRQygZGhCCNCscEVUtHwJDNicoIJChYXGBkaJSYnKCkqNDU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6g4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2drh4uPk5ebn6Onq8fLz9PX29/j5+v/EAB8BAAMBAQEBAQEBAQEAAAAAAAABAgMEBQYHCAkKC//EALURAAIBAgQEAwQHBQQEAAECdwABAgMRBAUhMQYSQVEHYXETIjKB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', '1158253782', '$2y$10$.NFDyN7HUSr9oLlD.FNMMefPLGiNM8edg5/ZKUHEz/cceoaVgXH92', 'cliente', 1, 1, '2025-10-24 06:12:36', 1, '7');
INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `foto_perfil`, `telefono`, `contraseña`, `rol`, `estado_disponibilidad`, `activo`, `fecha_registro`, `vip`, `comida_favorita`) VALUES
(17, 'Homero', 'Simpson', 'hs@gmail.com', NULL, '8888888888', '$2y$10$ce3VaNuJMglU1/4dmSJbAOE3nsifhwyKn1V5pNiCtc61NVKVjT2AW', 'cliente', 1, 1, '2025-10-24 20:39:00', 0, NULL),
(20, 'Jhomar', 'Mendieta Mamani', 'Jhomar@gmail.com', NULL, '1158253782', '$2y$10$WZaJxIOAZ8jT8kOlv0MRbusoa3IhwCUOyXK1fBJM6dmR4lb3JoKie', 'cliente', 1, 1, '2025-10-25 08:19:06', 0, NULL),
(21, 'Bart', 'Simpson', 'bs@gmail.com', NULL, '1111111111', '$2y$10$4mIHGCRNM.6pw7Kq0Y6PYOcvpVut6IHEt/OTDc/ncsipxsK/UkhEa', 'repartidor', 1, 1, '2025-10-25 22:25:04', 0, NULL),
(22, 'Bruno', 'Fornasar', 'brunofornasar@gmail.com', NULL, '123321123321', '$2y$10$gN01OReLcy7ySXw0K4sEH./TYOXHLBebsKpUWAp.dKTJWkKnTnHmm', 'cliente', 1, 1, '2025-10-26 03:47:33', 0, NULL);

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
(1, 'Centro', 'Zona céntrica de la ciudad', 5500.00, 30, 1),
(2, 'Norte', 'Barrio norte', 7200.00, 40, 1),
(3, 'Sur', 'Zona sur', 8800.00, 35, 1);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `condimentos`
--
ALTER TABLE `condimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `direcciones_cliente`
--
ALTER TABLE `direcciones_cliente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `item_condimentos`
--
ALTER TABLE `item_condimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `promociones`
--
ALTER TABLE `promociones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `seguimiento_pedidos`
--
ALTER TABLE `seguimiento_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=152;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `zonas_delivery`
--
ALTER TABLE `zonas_delivery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD CONSTRAINT `calificaciones_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `calificaciones_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `calificaciones_ibfk_3` FOREIGN KEY (`repartidor_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `direcciones_cliente`
--
ALTER TABLE `direcciones_cliente`
  ADD CONSTRAINT `direcciones_cliente_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `item_condimentos`
--
ALTER TABLE `item_condimentos`
  ADD CONSTRAINT `item_condimentos_ibfk_1` FOREIGN KEY (`pedido_item_id`) REFERENCES `pedido_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_condimentos_ibfk_2` FOREIGN KEY (`condimento_id`) REFERENCES `condimentos` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`zona_delivery_id`) REFERENCES `zonas_delivery` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `pedidos_ibfk_3` FOREIGN KEY (`repartidor_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `pedidos_ibfk_4` FOREIGN KEY (`cajero_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD CONSTRAINT `pedido_items_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pedido_items_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Filtros para la tabla `seguimiento_pedidos`
--
ALTER TABLE `seguimiento_pedidos`
  ADD CONSTRAINT `seguimiento_pedidos_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `seguimiento_pedidos_ibfk_2` FOREIGN KEY (`usuario_cambio_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
