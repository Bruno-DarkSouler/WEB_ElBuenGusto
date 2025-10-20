-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-10-2025 a las 02:24:20
-- Versión del servidor: 10.4.24-MariaDB
-- Versión de PHP: 8.1.6

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Calificaciones separadas para comida y delivery';

--
-- Volcado de datos para la tabla `calificaciones`
--

INSERT INTO `calificaciones` (`id`, `pedido_id`, `usuario_id`, `calificacion_comida`, `calificacion_delivery`, `comentario`, `repartidor_id`, `fecha_calificacion`) VALUES
(1, 1, 5, 5, 4, 'Muy buena comida y delivery rápido', 4, '2025-10-15 20:54:59'),
(2, 2, 6, 4, 5, 'Excelente atención, puntualidad', 4, '2025-10-15 20:54:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Categorías: Minutas, Pastas, Guisos, Bebidas';

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `activa`) VALUES
(1, 'Minutas', 'Comidas rápidas y sencillas como hamburguesas, milanesas, sándwiches', 1),
(2, 'Pastas', 'Variedades de pastas: ravioles, ñoquis, tallarines', 1),
(3, 'Guisos', 'Platos de cocción lenta, ideales para temporada fría', 1),
(4, 'Tartas', 'Tartas saladas y dulces', 1),
(5, 'Empanadas', 'Empanadas de distintos sabores', 1),
(6, 'Postres', 'Dulces como flanes, budines y tortas', 1),
(7, 'Bebidas', 'Gaseosas, jugos, aguas y bebidas calientes', 1),
(8, 'Embutidos', 'Fiambres y embutidos por porción de 100 gramos', 1),
(9, 'Otros', 'Productos variados o especiales', 1),
(10, 'Panificados y Ensaladas', 'Pan casero, ensaladas y otros acompañamientos', 1); -- 👈 categoría agregada
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `condimentos`
--

CREATE TABLE `condimentos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `tipo` enum('sal','salsa','especias','otros') DEFAULT 'otros',
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Condimentos disponibles: sal, salsas, especias';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Configuración del sistema';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Direcciones guardadas por el cliente';

--
-- Volcado de datos para la tabla `direcciones_cliente`
--

INSERT INTO `direcciones_cliente` (`id`, `usuario_id`, `alias`, `direccion`, `es_favorita`) VALUES
(1, 5, 'Casa', 'Calle Falsa 123', 1),
(2, 6, 'Casa', 'Av. Siempre Viva 742', 1),
(3, 6, 'Trabajo', 'Oficina Central, Piso 3', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item_condimentos`
--

CREATE TABLE `item_condimentos` (
  `id` int(11) NOT NULL,
  `pedido_item_id` int(11) NOT NULL,
  `condimento_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Condimentos seleccionados para cada item';

--
-- Volcado de datos para la tabla `item_condimentos`
--

INSERT INTO `item_condimentos` (`id`, `pedido_item_id`, `condimento_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 2, 5);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Pedidos con seguimiento completo del flujo';

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `numero_pedido`, `usuario_id`, `tipo_pedido`, `fecha_pedido`, `fecha_entrega_programada`, `direccion_entrega`, `telefono_contacto`, `metodo_pago`, `estado`, `subtotal`, `precio_delivery`, `total`, `zona_delivery_id`, `repartidor_id`, `cajero_id`, `comentarios_cliente`, `activo`) VALUES
(1, 'PED001', 5, 'inmediato', '2025-10-15 20:54:59', NULL, 'Calle Falsa 123', '1144556677', 'digital', 'en_preparacion', '1200.00', '150.00', '1350.00', 1, 4, 2, 'Caliente por favor', 1),
(2, 'PED002', 6, 'programado', '2025-10-15 20:54:59', '2025-10-15 20:00:00', 'Av. Siempre Viva 742', '1144557788', 'efectivo', 'pendiente', '1800.00', '200.00', '2000.00', 2, 4, NULL, 'Sin salsa', 1),
(3, 'PED12584', 8, 'programado', '2025-10-15 22:22:04', '2025-10-23 14:30:00', 'Cerrito 3916', '+541162165019', 'digital', 'pendiente', '4500.00', '8800.00', '13300.00', 3, NULL, NULL, 'sin mucha sal', 1);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Items específicos de cada pedido';

--
-- Volcado de datos para la tabla `pedido_items`
--

INSERT INTO `pedido_items` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio_unitario`, `precio_total`) VALUES
(1, 1, 1, 1, '1200.00', '1200.00'),
(2, 2, 3, 2, '1500.00', '3000.00'),
(3, 3, 3, 1, '1500.00', '1500.00'),
(4, 3, 7, 2, '1500.00', '3000.00');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Productos disponibles en la rotisería';

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `imagen`, `categoria_id`, `ingredientes`, `tiempo_preparacion`, `disponible`, `valoracion_promedio`, `total_valoraciones`, `activo`, `fecha_creacion`) VALUES
(1, 'Milanesa Napolitana', 'Milanesa de ternera con jamón, queso y salsa', '3500.00', 'https://media.istockphoto.com/id/1205601529/es/foto/milanesa-argentina-con-salsa-de-tomate-y-primer-plano-de-queso-derretido.jpg?s=612x612&w=0&k=20&c=FoOlEKki5z0pMOx6xByxYjSVJjHZ2MM5_rEmAnDJIjE=', 1, 'Carne, pan rallado, jamón, queso, salsa de tomate', 25, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(2, 'Suprema Maryland', 'Suprema de pollo con salsa especial', '3200.00', 'https://media.istockphoto.com/id/1387191843/es/foto/filete-de-pollo.jpg?s=612x612&w=0&k=20&c=NohWQF7c_Ubwk9DXbqbOS9_wLILW5PZKBtsLyeoxI2I=', 1, 'Pechuga de pollo, pan rallado, salsa Maryland', 20, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(3, 'Bife de Chorizo', 'Bife de chorizo con guarnición', '4200.00', 'https://media.istockphoto.com/id/587207508/es/foto/filete-a-la-parrilla-en-rodajas-ribeye-con-mantequilla-de-hierbas.jpg?s=612x612&w=0&k=20&c=FpPGPX-jIkIIORr1L40LE-YozmaWbiGlAPeni5qGNhg=', 1, 'Carne de res, condimentos', 15, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(4, 'Pollo al Horno', 'Medio pollo al horno con especias', '2800.00', 'https://media.istockphoto.com/id/1452768554/es/foto/pollo-asado-y-verduras.jpg?s=612x612&w=0&k=20&c=f7A73Mj9sB1fsie8pFSLo0pxxIEs6RLOv3P6D1Dank0=', 1, 'Pollo, limón, hierbas', 45, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(5, 'Ravioles de Ricota', 'Ravioles caseros con salsa a elección', '2900.00', 'https://media.istockphoto.com/id/1423025529/es/foto/vista-superior-de-tortelloni-o-tortelli-balanzoni-pasta-verde-italiana-con-relleno-de.jpg?s=612x612&w=0&k=20&c=CbjyMbS3MzKiAW6Gk8rt_2hXkHXdQKJl-PAVGEm5dqI=', 2, 'Masa, ricota, espinaca, salsa', 25, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(6, 'Ñoquis de Papa', 'Ñoquis tradicionales con tuco o crema', '2600.00', 'https://media.istockphoto.com/id/1969677546/es/foto/potato-gnocchi-with-fresh-tomatoes-sauce-typical-italian-food-cr2.jpg?s=612x612&w=0&k=20&c=uzUhFdAakjXz3Ux0Xl16ysGQrMi2PfzIKb03wOB3PcU=', 2, 'Papa, harina, huevo, salsa', 30, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(7, 'Lasagna Casera', 'Lasagna de carne con bechamel', '3400.00', 'https://media.istockphoto.com/id/168246587/es/foto/lasa%C3%B1a-de-primavera.jpg?s=612x612&w=0&k=20&c=K29Gf8b0JCbNiLNFpwvXpXVTySP_2VzxRFUe6eSQvTc=', 2, 'Pasta, carne, bechamel, queso', 35, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(8, 'Sorrentinos', 'Sorrentinos de jamón y queso', '3100.00', 'https://media.istockphoto.com/id/1248306669/es/foto/sorrentinos-pasta-rellena-con-salsa-sobre-mesa-de-madera.jpg?s=612x612&w=0&k=20&c=VV4FRdPpTTXKuSlGiucEXLY2F7DqtW1Km7p4Y3NpjOA=', 2, 'Masa, jamón, mozzarella, salsa', 25, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(9, 'Canelones de Verdura', 'Canelones rellenos de verdura', '2800.00', 'https://media.istockphoto.com/id/119446439/es/foto/canel%C3%B3n-ricota-y-espinaca.jpg?s=612x612&w=0&k=20&c=RgMEFSEqrqh3bu26AS-UScJSw8CmVj2GAgM2Ntv9iXg=', 2, 'Pasta, acelga, ricota, salsa blanca', 30, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(10, 'Guiso de Lentejas', 'Guiso tradicional de lentejas con chorizo', '2400.00', 'https://media.istockphoto.com/id/1081696632/es/foto/adasi-guiso-de-lentejas-persas-deliciosa-cocina-%C3%A1rabe.jpg?s=612x612&w=0&k=20&c=Y-PZca5kyUQGdo1OXxl2xUWBxfDC34FTRmXKc1tGsWs=', 3, 'Lentejas, chorizo, zanahoria, cebolla', 40, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(11, 'Estofado de Carne', 'Estofado de carne con papas y verduras', '3200.00', 'https://media.istockphoto.com/id/172441917/es/foto/estofado-de-hidromasaje-con-champi%C3%B1ones-y-papas.jpg?s=612x612&w=0&k=20&c=tKd_uCtiBxIHtC6pWrqN1CezB2lhSZ1JLsGuoFU4Vk0=', 3, 'Carne, papa, zanahoria, cebolla, vino', 45, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(12, 'Locro Criollo', 'Locro tradicional argentino', '3500.00', 'https://media.istockphoto.com/id/1313364837/es/foto/taz%C3%B3n-del-argentino-locro.jpg?s=612x612&w=0&k=20&c=4YJcerpiMJ2RLWzU1U2ohsKPd0UC3Qa8YK4lRxyWnsQ=', 3, 'Maíz, porotos, carne, chorizo, panceta', 60, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(13, 'Carbonada', 'Carbonada con zapallo y frutas', '2900.00', 'https://media.istockphoto.com/id/1254119522/es/foto/carbonada-servida-en-la-comida-t%C3%ADpica-de-calabaza-de-la-gastronom%C3%ADa-argentina-chile-bolivia-y.jpg?s=612x612&w=0&k=20&c=5fJP64wCGP5IKbJHIX-xIfQOF29943R1NltWp7ooyA4=', 3, 'Carne, zapallo, durazno, papa, choclo', 50, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(14, 'Mondongo', 'Mondongo casero tradicional', '3300.00', 'https://media.istockphoto.com/id/535489158/es/foto/cocina-de-madrid.jpg?s=612x612&w=0&k=20&c=oTjKCldKChOJWMr7FkyyMd0dRLcmkXiDTQPswi4KPew=', 3, 'Mondongo, garbanzos, chorizo colorado', 90, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(15, 'Tarta de Jamón y Queso', 'Tarta clásica de jamón y queso', '2500.00', 'https://media.istockphoto.com/id/461801551/es/foto/quiche-lorraine-porci%C3%B3n.jpg?s=612x612&w=0&k=20&c=HSYhh42vWyEtFHnLommcOW4AxKtImXz1Stsj0LpjeeU=', 4, 'Masa, jamón, queso, huevo, crema', 40, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(16, 'Tarta de Verdura', 'Tarta de acelga y ricota', '2200.00', 'https://media.istockphoto.com/id/535719897/es/foto/pastel-con-espinacas-y-queso-feta.jpg?s=612x612&w=0&k=20&c=XY9wufclgUgKC-5i7BgeACoAaDvaS9SKrWwUwmG_cBk=', 4, 'Masa, acelga, ricota, huevo', 40, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(17, 'Tarta de Atún', 'Tarta de atún con cebolla y morrón', '2600.00', 'https://media.istockphoto.com/id/2098845234/es/foto/empanada-gallega-rellena-de-at%C3%BAn-o-carne-y-verduras-de-cerca-en-la-tabla-horizontal.jpg?s=612x612&w=0&k=20&c=UtfQMkE-dcXOzQiXKCGhThhcid_6CiB-Ibz5QvagI0U=', 4, 'Masa, atún, cebolla, morrón, huevo', 40, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(18, 'Tarta Pascualina', 'Tarta de espinaca y huevo', '2400.00', 'https://media.istockphoto.com/id/1463863208/es/foto/tarta-casera-de-feta-de-espinacas-quiche.jpg?s=612x612&w=0&k=20&c=rjT9sUW_qzlwLO92eJT9HFJD1R8WM6rYoicYPKpZERU=', 4, 'Masa, espinaca, huevo duro, ricota', 45, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(19, 'Empanadas de Carne', 'Empanadas criollas (por docena)', '4500.00', 'https://media.istockphoto.com/id/1171946922/es/foto/empanadas.jpg?s=612x612&w=0&k=20&c=Lo5Ybk5FbjjvdJcE12fSandBIdZI45P0wEVSQRzvuT8=', 5, 'Carne, cebolla, huevo, aceitunas, masa', 30, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(20, 'Empanadas de Pollo', 'Empanadas de pollo (por docena)', '4200.00', 'https://media.istockphoto.com/id/1437638745/es/foto/primer-plano-de-sabrosas-empanadas-de-pollo.jpg?s=612x612&w=0&k=20&c=w2NtFmVIeE1r23_qRwumyxV2p43Mafemuj7DMFksq80=', 5, 'Pollo, cebolla, morrón, masa', 30, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(21, 'Empanadas de Jamón y Queso', 'Empanadas de jamón y queso (por docena)', '4000.00', 'https://media.istockphoto.com/id/152137708/es/foto/jam%C3%B3n-y-queso-empanada-primer-plano.jpg?s=612x612&w=0&k=20&c=ziMRxdoCLnocQBLIptsdjAEtqHB4cAGOTqWW4pA1Des=', 5, 'Jamón, queso, masa', 25, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(22, 'Empanadas de Verdura', 'Empanadas de verdura (por docena)', '3800.00', 'https://www.cronica.com.ar/__export/1745442686992/sites/cronica/img/2025/04/23/37ec640d-6407-4947-ac25-9940bdee4846.jpg_1110719059.jpg', 5, 'Acelga, cebolla, queso, masa', 30, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(23, 'Empanadas de Carne Picante', 'Empanadas picantes (por docena)', '4700.00', 'https://media.istockphoto.com/id/1999053536/es/foto/empanadas-tradicionales-de-carne-argentina.jpg?s=612x612&w=0&k=20&c=or3Zwu4J4STFJJe7tt2VF3K6LOGsmHkfX0mfDFC4ghA=', 5, 'Carne, cebolla, ají picante, masa', 30, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(24, 'Flan Casero', 'Flan con dulce de leche y crema', '1200.00', 'https://media.istockphoto.com/id/463121717/es/foto/pudim-de-leite.jpg?s=612x612&w=0&k=20&c=mt-CMEvnV0ds7XuXnsttnY5r5aBm8gVw6WznUwCumGw=', 6, 'Huevos, leche, azúcar, dulce de leche', 60, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(25, 'Tiramisú', 'Tiramisú italiano tradicional', '1800.00', 'https://media.istockphoto.com/id/956986120/es/foto/postre-italiano-tiramis%C3%BA-con-queso-mascarpone-y-caf%C3%A9-espresso.jpg?s=612x612&w=0&k=20&c=OwlnQfxLK0eRLaHJcOUhBo-4v42pV4DME7FvcABHuHk=', 6, 'Mascarpone, café, vainillas, cacao', 30, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(26, 'Brownie', 'Brownie de chocolate con nueces', '1400.00', 'https://media.istockphoto.com/id/168731372/es/foto/casero-fresco-brownie-de-chocolate.jpg?s=612x612&w=0&k=20&c=6k5bqfgWqv3nUyTjoZilyi-917RLsdkMOoGITjulEqw=', 6, 'Chocolate, harina, huevos, nueces', 35, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(27, 'Cheesecake', 'Cheesecake con frutos rojos', '2000.00', 'https://media.istockphoto.com/id/1225491381/es/foto/tarta-de-queso-con-salsa-de-caramelo.jpg?s=612x612&w=0&k=20&c=wqwzAmxX93-QKgFt93PMq_EEYaSx95FkYeK3DaINjlI=', 6, 'Queso crema, galletas, frutos rojos', 240, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(28, 'Postre Balcarce', 'Postre Balcarce tradicional', '1600.00', 'https://www.cronica.com.ar/__export/1755190805834/sites/cronica/img/2025/08/14/torta_balcarce.jpg_683177495.jpg', 6, 'Bizcochuelo, dulce de leche, merengue', 90, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(29, 'Coca Cola 1.5L', 'Gaseosa Coca Cola 1.5 litros', '800.00', 'https://colanta.vtexassets.com/arquivos/ids/158639-1200-auto?v=638549392810570000&width=1200&height=auto&aspect=true', 7, NULL, 0, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(30, 'Agua Mineral 500ml', 'Agua sin gas', '500.00', 'https://media.istockphoto.com/id/1413583669/es/foto/botella-de-pl%C3%A1stico-de-agua-fr%C3%ADa-con-gotas-de-condensaci%C3%B3n-y-dos-cubitos-de-hielo-sobre-la.jpg?s=612x612&w=0&k=20&c=V84bb5K0NifoythA3MxllNsiog4wlXpg4dGJPcGQMLo=', 7, NULL, 0, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(31, 'Jugo de Naranja Natural', 'Jugo exprimido de naranja', '900.00', 'https://media.istockphoto.com/id/1225546255/es/foto/toma-de-jugo-de-naranja-fresco-en-un-vaso.jpg?s=612x612&w=0&k=20&c=nQd2QGhKLsc757sUArqLGho6mcrNgT6AtS8vdA1tFUs=', 7, 'Naranjas frescas', 5, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(32, 'Cerveza Quilmes 1L', 'Cerveza Quilmes litro', '1200.00', 'https://masonlineprod.vtexassets.com/arquivos/ids/272997-1200-auto?v=638875152644030000&width=1200&height=auto&aspect=true', 7, NULL, 0, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(33, 'Vino Tinto 750ml', 'Vino tinto de la casa', '1500.00', 'https://www.casa-segal.com/wp-content/uploads/2022/05/toro-clasico-tinto-750-ml-ofertas-en-mendoza-vinos-casa-segal-min.jpg', 7, NULL, 0, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(34, 'Jamón Cocido (kg)', 'Jamón cocido de primera calidad', '2800.00', 'https://media.istockphoto.com/id/1297622822/es/foto/pieza-de-jam%C3%B3n-de-1-kg-y-cuchillo-de-hoja-larga-afilada-sobre-una-tabla-de-cortar-sobre-mesa.jpg?s=612x612&w=0&k=20&c=xxbNHFa-74uHI_SgOn0_HJL-hcjBLsGg7FxSQcNxVvc=', 8, NULL, 0, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(35, 'Salame (kg)', 'Salame argentino', '3500.00', 'https://media.istockphoto.com/id/528296204/es/foto/fiambres-salame.jpg?s=612x612&w=0&k=20&c=jy35wPbHvIhgOjAZjDH678tZy5nmbnFRuG1dnQmAUXk=', 8, NULL, 0, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(36, 'Mortadela (kg)', 'Mortadela tradicional', '1800.00', 'https://media.istockphoto.com/id/907610468/es/foto/mortadela-salchicha-italiana-tradicional.jpg?s=612x612&w=0&k=20&c=03WP8Y5S6GPMuEmgSiCoImvIwnVTBufc5cHOjkUq204=', 8, NULL, 0, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(37, 'Queso Fresco (kg)', 'Queso crema untable', '2200.00', 'https://dfwblobstorage.blob.core.windows.net/ewcmediacontainer/eatwisconsincheese/media/content/cheesemasters-2019/quesofresco-header_3.jpg', 8, NULL, 0, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(38, 'Chorizo Colorado (kg)', 'Chorizo colorado parrillero', '2600.00', 'https://media.istockphoto.com/id/531034467/es/foto/chorizo.jpg?s=612x612&w=0&k=20&c=n3ckcOW06Z8ZqPIqv4sRZM1BigBqfOKL4OCpzwQPHTQ=', 8, NULL, 0, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(39, 'Hamburguesa Completa', 'Hamburguesa con papas fritas', '2800.00', 'https://media.istockphoto.com/id/1309352410/es/foto/hamburguesa-con-queso-con-tomate-y-lechuga-en-tabla-de-madera.jpg?s=612x612&w=0&k=20&c=HaSLXFFns4_IHfbvWY7_FX7tlccVjl0s0BrlqaLHOTE=', 9, 'Carne molida, pan, lechuga, tomate, queso, papas', 15, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(40, 'Pancho Completo', 'Pancho con papas fritas', '1500.00', 'https://viapais.com.ar/resizer/v2/KYHQYO3JBBDXLDH7SU5KITF7PM.jpg?quality=75&smart=true&auth=431d86ba6fd6f184c40f0fcd20bbba3608253d1c7916e71a2944c8da6868bade&width=980&height=640', 9, 'Salchicha, pan, salsas, papas', 10, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(41, 'Lomito Completo', 'Lomito con jamón, queso y huevo', '3200.00', 'https://pedidos.estoyresto.com.ar/wp-content/uploads/2025/01/lomo-completo.jpg', 9, 'Lomo, jamón, queso, huevo, pan', 15, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(42, 'Pizza Muzzarella', 'Pizza muzzarella individual', '2000.00', 'https://media.istockphoto.com/id/2023102269/es/foto/pizza-cubierta-con-albahaca-fresca-tomate-y-queso-encima-de-una-fuente-de-madera.jpg?s=612x612&w=0&k=20&c=AIgYPEdLfPu1C864eDlI42t_oedR3Evx3CzD9fE1teY=', 9, 'Masa, muzzarella, salsa', 20, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(43, 'Papas Fritas', 'Porción de papas fritas grandes', '1200.00', 'https://media.istockphoto.com/id/1443993866/es/foto/patatas-fritas-con-salsa-de-tomate-y-salsa-de-c%C3%B3ctel.jpg?s=612x612&w=0&k=20&c=33ts-FF9XW_jytzGoxw0mXvuMSi8oc3nYEdLaXu2VaU=', 9, 'Papas', 10, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(44, 'Pan Casero', 'Pan casero del día', '800.00', 'https://media.istockphoto.com/id/1320305161/es/foto/pan-de-masa-madre-en-rodajas-hecho-de-levadura-silvestre-cocinar-alimentos-saludables.jpg?s=612x612&w=0&k=20&c=JzzCG5qvAiPGF2NxyBR0xzlrwwmdrgPiTjBaXn4GS_4=', 10, 'Harina, levadura, sal', 120, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(45, 'Ensalada Rusa (kg)', 'Ensalada rusa casera', '1500.00', 'https://media.istockphoto.com/id/1151789416/es/foto/ensalada-tradicional-rusa-olivier-con-verduras-y-carne.jpg?s=612x612&w=0&k=20&c=u0WjE22PRQqS9tRyVwiYmq0_VovY7puck-KiYR6R0kQ=', 10, 'Papa, zanahoria, arvejas, mayonesa', 30, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(46, 'Pickles Caseros', 'Pickles variados en frasco', '1000.00', 'https://media.istockphoto.com/id/1582573020/es/foto/pepinos-encurtidos-caseros-con-ajo-r%C3%A1bano-picante-y-eneldo-en-frasco-de-vidrio-sobre-fondo-gris.jpg?s=612x612&w=0&k=20&c=mdCRD2r0biz09_wQJpCwPZldgjCCeJpPdi66-T1TmwM=', 10, 'Verduras mixtas, vinagre', 0, 1, '0.00', 0, 1, '2025-10-16 02:18:57'),
(47, 'Matambre Arrollado (kg)', 'Matambre arrollado casero', '4500.00', 'https://ninina.com/cdn/shop/products/NININA9-4205.jpg?v=1605831222&width=713', 10, 'Matambre, huevo, zanahoria, morrones', 90, 1, '0.00', 0, 1, '2025-10-16 02:18:57');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Promociones y ofertas especiales';

--
-- Volcado de datos para la tabla `promociones`
--

INSERT INTO `promociones` (`id`, `nombre`, `descripcion`, `tipo`, `valor`, `fecha_inicio`, `fecha_fin`, `activa`, `monto_minimo`) VALUES
(1, 'Descuento 10%', '10% de descuento en productos seleccionados', 'descuento_porcentaje', '10.00', '2025-10-01', '2025-10-31', 1, '0.00'),
(2, 'Promo empanadas', '3x2 en empanadas', 'descuento_fijo', '150.00', '2025-10-10', '2025-10-20', 1, '300.00');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Historial de cambios de estado del pedido';

--
-- Volcado de datos para la tabla `seguimiento_pedidos`
--

INSERT INTO `seguimiento_pedidos` (`id`, `pedido_id`, `estado_anterior`, `estado_nuevo`, `usuario_cambio_id`, `fecha_cambio`, `comentarios`) VALUES
(1, 1, 'pendiente', 'confirmado', 2, '2025-10-15 20:54:59', 'Pedido confirmado por cajero'),
(2, 1, 'confirmado', 'en_preparacion', 3, '2025-10-15 20:54:59', 'Cocinero comenzó preparación'),
(3, 2, 'pendiente', 'confirmado', 2, '2025-10-15 20:54:59', 'Pedido programado confirmado'),
(4, 3, NULL, 'pendiente', 8, '2025-10-15 22:22:04', 'Pedido creado por el cliente');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Usuarios del sistema con diferentes roles';

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `telefono`, `direccion`, `contraseña`, `rol`, `estado_disponibilidad`, `activo`, `fecha_registro`) VALUES
(1, 'Bruno', 'Pavon', 'admin@rotiseria.com', '1122334455', 'Sucursal Principal', 'admin123', 'administrador', 1, 1, '2025-10-15 20:54:59'),
(2, 'María', 'Gomez', 'cajero1@rotiseria.com', '1166677788', 'Sucursal Principal', 'cajero123', 'cajero', 1, 1, '2025-10-15 20:54:59'),
(3, 'Juan', 'Lopez', 'cocinero1@rotiseria.com', '1177788999', 'Sucursal Principal', 'cocinero123', 'cocinero', 1, 1, '2025-10-15 20:54:59'),
(4, 'Ana', 'Martinez', 'repartidor1@rotiseria.com', '1199988777', 'Sucursal Principal', 'repartidor123', 'repartidor', 1, 1, '2025-10-15 20:54:59'),
(5, 'Carlos', 'Perez', 'cliente1@gmail.com', '1144556677', 'Calle Falsa 123', 'cliente123', 'cliente', 1, 1, '2025-10-15 20:54:59'),
(6, 'Lucía', 'Diaz', 'cliente2@gmail.com', '1144557788', 'Av. Siempre Viva 742', 'cliente456', 'cliente', 1, 1, '2025-10-15 20:54:59'),
(7, 'admin', 'admin', 'admin@system.com', '01162165019', 'Cerrito 3966', '$2y$10$GyN32IT5ITcIrsq3RzkqM.y7zLRQsigGbCFFzp43jZGzTWFyynPWG', 'administrador', 1, 1, '2025-10-15 20:59:56'),
(8, 'LUCIANO', 'CAMPANELLI', 'luchocampanelli1@gmail.com', '01162165019', 'Cerrito 3916', '$2y$10$FH5OaWhe/gv0HWSkfPsp.eW4ghfFi6YnWc.iC9RsEQZs4GcIXnIkG', 'cliente', 1, 1, '2025-10-15 21:06:58');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Zonas de entrega con precios diferenciados';

--
-- Volcado de datos para la tabla `zonas_delivery`
--

INSERT INTO `zonas_delivery` (`id`, `nombre`, `descripcion`, `precio_delivery`, `tiempo_estimado`, `activa`) VALUES
(1, 'Centro', 'Zona céntrica de la ciudad', '5500.00', 30, 1),
(2, 'Norte', 'Barrio norte', '7200.00', 40, 1),
(3, 'Sur', 'Zona sur', '8800.00', 35, 1);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `item_condimentos`
--
ALTER TABLE `item_condimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
