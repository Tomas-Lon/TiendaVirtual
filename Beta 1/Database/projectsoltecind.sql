-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-08-2025 a las 00:10:20
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `projectsoltecind`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `numero_documento` varchar(50) NOT NULL,
  `tipo_documento` enum('CC','NIT','CE','TI') DEFAULT 'CC',
  `email` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion_principal` text DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `contacto_principal` varchar(255) DEFAULT NULL,
  `empleado_asignado` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `numero_documento`, `tipo_documento`, `email`, `telefono`, `direccion_principal`, `ciudad`, `contacto_principal`, `empleado_asignado`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Constructora Bogotá S.A.S.', '900123456-7', 'NIT', 'compras@constructorabogota.com', '6013456789', 'Calle 72 # 10-15', 'Bogotá', 'Ing. Pedro Martínez', 2, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(2, 'Hidráulicos del Norte Ltda.', '800234567-8', 'NIT', 'gerencia@hidraulicosdelnorte.com', '6014567890', 'Carrera 15 # 85-30', 'Bogotá', 'Arq. Laura Gómez', 3, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(3, 'Instalaciones García Hermanos', '79123456789', 'CC', 'instalacionesgarcia@gmail.com', '3001234567', 'Calle 45 # 20-18', 'Medellín', 'Miguel García', 2, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(4, 'Suministros Industriales Cali S.A.S.', '900345678-9', 'NIT', 'ventas@suministrosindustrialescali.com', '6015678901', 'Avenida 6N # 23-45', 'Cali', 'Ing. Carmen Torres', 4, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(5, 'Plomería Profesional Ltda.', '800456789-0', 'NIT', 'contacto@plomeriaprofesional.com', '6016789012', 'Carrera 30 # 50-25', 'Bucaramanga', 'Técnico Juan Pérez', 3, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(6, 'Construcciones Valle del Cauca', '900567890-1', 'NIT', 'proyectos@construccionesvalle.com', '6017890123', 'Calle 100 # 15-40', 'Cali', 'Arq. Sebastián Ruiz', 2, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(7, 'Roberto Silva Contractor', '1023456789', 'CC', 'roberto.silva.contractor@outlook.com', '3157894561', 'Diagonal 20 # 35-12', 'Barranquilla', 'Roberto Silva', 4, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(8, 'Ferretería El Progreso', '52345678901', 'CC', 'ferreteriaprogreso@hotmail.com', '3209876543', 'Carrera 8 # 12-30', 'Manizales', 'Dora Alicia Mendoza', 3, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(9, 'Distribuidora de Tuberías del Caribe', '800678901-2', 'NIT', 'distribuidoratuberias@gmail.com', '6018901234', 'Calle 70 # 45-18', 'Cartagena', 'Ing. Patricia Herrera', 2, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(10, 'Mantenimiento Integral PYME', '900789012-3', 'NIT', 'mantenimientointegral@empresa.com', '6019012345', 'Avenida El Dorado # 68-90', 'Bogotá', 'Técnico Francisco López', 4, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `credenciales`
--

CREATE TABLE `credenciales` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `tipo` enum('empleado','cliente') NOT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `credenciales`
--

INSERT INTO `credenciales` (`id`, `usuario`, `contrasena`, `tipo`, `empleado_id`, `cliente_id`, `ultimo_acceso`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$d2yHiXFB5HsrWyA/jKrxfeTxS17q9CmqhDeRD.jKHLerUkbOHZkA.', 'empleado', 1, NULL, '2025-08-08 21:09:36', 1, '2025-08-08 01:08:52', '2025-08-08 21:09:36'),
(2, 'mrodriguez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 2, NULL, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(3, 'jgarcia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 3, NULL, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(4, 'aherrera', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 4, NULL, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(5, 'rjimenez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 5, NULL, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(6, 'fcastro', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 6, NULL, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(7, 'dmorales', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 7, NULL, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(8, 'constructora.bogota', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 1, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(9, 'hidraulicos.norte', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 2, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(10, 'garcia.hermanos', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 3, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(11, 'suministros.cali', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 4, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(12, 'plomeria.profesional', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 5, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(13, 'construcciones.valle', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 6, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(14, 'roberto.silva', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 7, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(15, 'ferreteria.progreso', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 8, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(16, 'tuberias.caribe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 9, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(17, 'mantenimiento.pyme', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 10, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `descuentos_clientes`
--

CREATE TABLE `descuentos_clientes` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `porcentaje_descuento` decimal(5,2) NOT NULL DEFAULT 0.00,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `descuentos_clientes`
--

INSERT INTO `descuentos_clientes` (`id`, `cliente_id`, `grupo_id`, `porcentaje_descuento`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 15.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(2, 1, 4, 12.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(3, 1, 13, 10.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(4, 1, 14, 8.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(5, 2, 4, 18.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(6, 2, 12, 15.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(7, 2, 13, 12.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(8, 4, 1, 20.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(9, 4, 2, 18.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(10, 4, 5, 15.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(11, 4, 6, 12.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(12, 6, 13, 14.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(13, 6, 14, 12.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(14, 6, 1, 10.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(15, 9, 15, 22.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(16, 9, 16, 20.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(17, 9, 13, 18.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(18, 9, 14, 16.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_orden_venta`
--

CREATE TABLE `detalle_orden_venta` (
  `id` int(11) NOT NULL,
  `orden_venta_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL,
  `descuento_porcentaje` decimal(5,2) DEFAULT 0.00,
  `descuento_monto` decimal(12,2) DEFAULT 0.00,
  `impuesto_porcentaje` decimal(5,2) DEFAULT 0.00,
  `impuesto_monto` decimal(12,2) DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_orden_venta`
--

INSERT INTO `detalle_orden_venta` (`id`, `orden_venta_id`, `producto_id`, `cantidad`, `precio_unitario`, `descuento_porcentaje`, `descuento_monto`, `impuesto_porcentaje`, `impuesto_monto`, `subtotal`, `created_at`) VALUES
(1, 1, 1, 15.00, 140476.00, 15.00, 316071.00, 19.00, 340183.11, 1790069.00, '2025-08-08 01:08:52'),
(2, 1, 13, 20.00, 47305.00, 15.00, 142221.00, 19.00, 152756.01, 803979.00, '2025-08-08 01:08:52'),
(3, 2, 7, 25.00, 99860.00, 18.00, 449370.00, 19.00, 388954.70, 2047130.00, '2025-08-08 01:08:52'),
(4, 2, 12, 30.00, 33103.00, 18.00, 178756.20, 19.00, 154742.42, 814433.80, '2025-08-08 01:08:52'),
(5, 3, 1, 20.00, 140476.00, 20.00, 562856.00, 19.00, 426843.36, 2246544.00, '2025-08-08 01:08:52'),
(6, 3, 2, 50.00, 47500.00, 18.00, 427500.00, 19.00, 370025.00, 1947500.00, '2025-08-08 01:08:52'),
(7, 3, 21, 8.00, 855047.00, 20.00, 1368075.20, 19.00, 1039737.15, 5472300.80, '2025-08-08 01:08:52'),
(8, 4, 22, 40.00, 3500.00, 0.00, 0.00, 19.00, 26600.00, 140000.00, '2025-08-08 01:08:52'),
(9, 4, 29, 50.00, 671.00, 0.00, 0.00, 19.00, 6374.50, 33550.00, '2025-08-08 01:08:52'),
(10, 4, 6, 25.00, 2716.00, 0.00, 0.00, 19.00, 12901.00, 67900.00, '2025-08-08 01:08:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedidos`
--

CREATE TABLE `detalle_pedidos` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL,
  `descuento_porcentaje` decimal(5,2) DEFAULT 0.00,
  `descuento_monto` decimal(12,2) DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_pedidos`
--

INSERT INTO `detalle_pedidos` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio_unitario`, `descuento_porcentaje`, `descuento_monto`, `subtotal`, `created_at`) VALUES
(1, 1, 1, 15.00, 140476.00, 15.00, 316071.00, 1790069.00, '2025-08-08 01:08:52'),
(2, 1, 13, 20.00, 47305.00, 15.00, 142221.00, 803979.00, '2025-08-08 01:08:52'),
(3, 2, 7, 25.00, 99860.00, 18.00, 449370.00, 2047130.00, '2025-08-08 01:08:52'),
(4, 2, 12, 30.00, 33103.00, 18.00, 178756.20, 814433.80, '2025-08-08 01:08:52'),
(5, 3, 1, 20.00, 140476.00, 20.00, 562856.00, 2246544.00, '2025-08-08 01:08:52'),
(6, 3, 2, 50.00, 47500.00, 18.00, 427500.00, 1947500.00, '2025-08-08 01:08:52'),
(7, 3, 21, 8.00, 855047.00, 20.00, 1368075.20, 5472300.80, '2025-08-08 01:08:52'),
(8, 4, 22, 40.00, 3500.00, 0.00, 0.00, 140000.00, '2025-08-08 01:08:52'),
(9, 4, 29, 50.00, 671.00, 0.00, 0.00, 33550.00, '2025-08-08 01:08:52'),
(10, 4, 6, 25.00, 2716.00, 0.00, 0.00, 67900.00, '2025-08-08 01:08:52'),
(11, 5, 13, 35.00, 47305.00, 14.00, 232164.50, 1423505.50, '2025-08-08 01:08:52'),
(12, 5, 17, 25.00, 108356.00, 14.00, 370167.00, 2338733.00, '2025-08-08 01:08:52'),
(13, 5, 1, 10.00, 140476.00, 10.00, 140476.00, 1264284.00, '2025-08-08 01:08:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones_clientes`
--

CREATE TABLE `direcciones_clientes` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre identificador de la dirección',
  `direccion` text NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `zona_entrega` varchar(50) DEFAULT NULL COMMENT 'Norte, Sur, Centro, etc.',
  `tiempo_entrega_estimado` int(11) DEFAULT NULL COMMENT 'Días hábiles estimados',
  `es_principal` tinyint(1) DEFAULT 0,
  `es_facturacion` tinyint(1) DEFAULT 0,
  `es_envio` tinyint(1) DEFAULT 1,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `direcciones_clientes`
--

INSERT INTO `direcciones_clientes` (`id`, `cliente_id`, `nombre`, `direccion`, `ciudad`, `departamento`, `codigo_postal`, `telefono`, `zona_entrega`, `tiempo_entrega_estimado`, `es_principal`, `es_facturacion`, `es_envio`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 'Sede Principal', 'Calle 72 # 10-15', 'Bogotá', 'Cundinamarca', '110111', '6013456789', 'Norte', 1, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(2, 1, 'Obra Zona Rosa', 'Carrera 11 # 93-25', 'Bogotá', 'Cundinamarca', '110221', '3001234567', 'Norte', 1, 0, 0, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(3, 1, 'Bodega Sur', 'Calle 13 Sur # 68-45', 'Bogotá', 'Cundinamarca', '110811', '6017654321', 'Sur', 2, 0, 0, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(4, 2, 'Oficina Principal', 'Carrera 15 # 85-30', 'Bogotá', 'Cundinamarca', '110221', '6014567890', 'Norte', 1, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(5, 2, 'Taller de Servicios', 'Calle 80 # 20-45', 'Bogotá', 'Cundinamarca', '110111', '3157891234', 'Norte', 1, 0, 0, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(6, 3, 'Taller Principal', 'Calle 45 # 20-18', 'Medellín', 'Antioquia', '050012', '3001234567', 'Centro', 3, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(7, 4, 'Sede Principal', 'Avenida 6N # 23-45', 'Cali', 'Valle del Cauca', '760001', '6015678901', 'Norte', 4, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(8, 4, 'Almacén Sur', 'Carrera 100 # 15-78', 'Cali', 'Valle del Cauca', '760035', '3209876543', 'Sur', 4, 0, 0, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(9, 5, 'Oficina Principal', 'Carrera 30 # 50-25', 'Bucaramanga', 'Santander', '680001', '6016789012', 'Centro', 5, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(10, 6, 'Sede Administrativa', 'Calle 100 # 15-40', 'Cali', 'Valle del Cauca', '760001', '6017890123', 'Norte', 4, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(11, 7, 'Dirección Personal', 'Diagonal 20 # 35-12', 'Barranquilla', 'Atlántico', '080001', '3157894561', 'Norte', 6, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(12, 8, 'Local Comercial', 'Carrera 8 # 12-30', 'Manizales', 'Caldas', '170001', '3209876543', 'Centro', 7, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(13, 9, 'Almacén Principal', 'Calle 70 # 45-18', 'Cartagena', 'Bolívar', '130001', '6018901234', 'Norte', 5, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(14, 9, 'Punto de Venta', 'Avenida Pedro de Heredia # 30-25', 'Cartagena', 'Bolívar', '130010', '3156789012', 'Centro', 5, 0, 0, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(15, 10, 'Oficina Central', 'Avenida El Dorado # 68-90', 'Bogotá', 'Cundinamarca', '110111', '6019012345', 'Occidente', 2, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `cargo` enum('admin','vendedor','repartidor') NOT NULL DEFAULT 'vendedor',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `nombre`, `email`, `telefono`, `cargo`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Carlos Alberto Mendoza', 'cmendoza@soltecnica.com', '3201234567', 'admin', 1, '2025-08-08 01:08:51', '2025-08-08 01:08:51'),
(2, 'María Elena Rodríguez', 'mrodriguez@soltecnica.com', '3157891234', 'vendedor', 1, '2025-08-08 01:08:51', '2025-08-08 01:08:51'),
(3, 'José Luis García', 'jgarcia@soltecnica.com', '3009876543', 'vendedor', 1, '2025-08-08 01:08:51', '2025-08-08 01:08:51'),
(4, 'Ana Patricia Herrera', 'aherrera@soltecnica.com', '3125678901', 'vendedor', 1, '2025-08-08 01:08:51', '2025-08-08 01:08:51'),
(5, 'Roberto Jiménez Silva', 'rjimenez@soltecnica.com', '3145567890', 'repartidor', 1, '2025-08-08 01:08:51', '2025-08-08 01:08:51'),
(6, 'Fernando Castro López', 'fcastro@soltecnica.com', '3176543210', 'repartidor', 1, '2025-08-08 01:08:51', '2025-08-08 01:08:51'),
(7, 'Diana Patricia Morales', 'dmorales@soltecnica.com', '3198765432', 'vendedor', 1, '2025-08-08 01:08:51', '2025-08-08 01:08:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envios`
--

CREATE TABLE `envios` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) DEFAULT NULL,
  `orden_venta_id` int(11) DEFAULT NULL,
  `numero_guia` varchar(100) DEFAULT NULL,
  `transportista` varchar(255) DEFAULT NULL,
  `repartidor_id` int(11) DEFAULT NULL,
  `fecha_programada` date DEFAULT curdate(),
  `fecha_salida` datetime DEFAULT NULL,
  `fecha_entrega_real` datetime DEFAULT NULL,
  `estado` enum('programado','en_preparacion','en_transito','entregado','fallido','devuelto') DEFAULT 'programado',
  `direccion_entrega_id` int(11) DEFAULT NULL,
  `receptor_nombre` varchar(255) DEFAULT NULL,
  `receptor_documento` varchar(50) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `costo_envio` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `envios`
--

INSERT INTO `envios` (`id`, `pedido_id`, `orden_venta_id`, `numero_guia`, `transportista`, `repartidor_id`, `fecha_programada`, `fecha_salida`, `fecha_entrega_real`, `estado`, `direccion_entrega_id`, `receptor_nombre`, `receptor_documento`, `observaciones`, `costo_envio`, `created_at`, `updated_at`) VALUES
(1, 4, 4, 'GU-2025-001', 'Transporte Sol Técnica', 5, '2025-08-07', '2025-08-07 08:30:00', '2025-08-07 14:15:00', 'entregado', 6, 'Miguel García', '79123456789', 'Entrega exitosa - Material recibido conforme', 45000.00, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(2, 3, 3, 'GU-2025-002', 'Transporte Sol Técnica', 6, '2025-08-08', '2025-08-08 09:00:00', NULL, 'en_transito', 7, 'Carmen Torres', '900345678-9', 'En ruta hacia Cali', 85000.00, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(3, 1, 1, 'GU-2025-003', 'Transporte Sol Técnica', 5, '2025-08-09', NULL, NULL, 'programado', 1, 'Pedro Martínez', '900123456-7', 'Envío programado para mañana', 35000.00, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(4, 2, 2, 'GU-2025-004', 'Transporte Sol Técnica', 6, '2025-08-08', '2025-08-08 07:45:00', NULL, 'en_transito', 4, 'Laura Gómez', '800234567-8', 'Material urgente en camino', 25000.00, '2025-08-08 01:08:52', '2025-08-08 01:08:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos_productos`
--

CREATE TABLE `grupos_productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `grupos_productos`
--

INSERT INTO `grupos_productos` (`id`, `nombre`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'SHC 80', 'Accesorios SCH 80 para alta presión', 1, '2025-07-27 03:05:22', '2025-08-08 19:18:20'),
(2, 'Alcant Novaf Acc', 'Accesorios para alcantarillado Novafort', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(3, 'PavComponentes', 'Componentes especiales Pavco', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(4, 'Acc Ultratemp', 'Accesorios CPVC para alta temperatura', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(5, 'Pression Conexiones', 'Conexiones para tuberías a presión', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(6, 'Sanitaria Accesorio', 'Accesorios para sistemas sanitarios', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(7, 'ACC SANITARIA 8-10PULG', 'Accesorios sanitarios de gran diámetro', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(8, 'Acc PF+UAD', 'Accesorios para sistemas de acueducto', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(9, 'GRIFERIAS', 'Grifería y accesorios de instalación', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(10, 'COFLEX', 'Productos flexibles y sifones', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(11, 'Sold.Pvc y Lubricant', 'Soldaduras y lubricantes', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(12, 'Tuberia Ultratemp', 'Tuberías CPVC', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(13, 'Presion Tuberias', 'Tuberías para sistemas a presión', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(14, 'Sanitarias Tuberias', 'Tuberías para sistemas sanitarios', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(15, 'Alcant Novafort S8Tb 1 110-500', 'Tuberías Novafort S8', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(16, 'Alcant Novafort S4 Tb', 'Tuberías Novafort S4', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(17, 'Tb Nvafort 24PULG-48PULG', 'Tuberías Novafort gran diámetro', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(18, 'Conduflex', 'Tuberías flexibles', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(19, 'Conduit Tuberias', 'Tuberías conduit', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(20, 'Drenaje', 'Sistemas de drenaje', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(21, 'C900', 'Tuberías C900 para acueducto', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(22, 'Ventilacion Tuberia', 'Tuberías para ventilación', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(23, 'Sanitaria Novatec', 'Tuberías sanitarias Novatec', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(24, 'Tuberias Acueducto', 'Tuberías para acueducto', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(25, 'Accesorios Acueducto', 'Accesorios para acueducto', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(26, 'Tanques Pavco', 'Tanques y accesorios', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(27, 'CONEXIONES HD AWWA C900', 'Conexiones alta densidad', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(28, 'Soldadura Cpvc', 'Soldaduras especiales CPVC', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(29, 'Tub Biaxial', 'Tuberías biaxiales', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(30, 'Biaxial Lisa', 'Tuberías biaxiales extremo liso', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(31, 'ACUEDUCTO RADIO COR', 'Accesorios radio corto acueducto', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(32, 'Conduit Conexiones', 'Conexiones para conduit', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(33, 'Camara MAHOLES', 'Cámaras y manholes', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22'),
(34, 'OTROS', 'Productos varios', 1, '2025-07-27 03:05:22', '2025-07-27 03:05:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_venta`
--

CREATE TABLE `orden_venta` (
  `id` int(11) NOT NULL,
  `numero_documento` varchar(50) NOT NULL COMMENT 'Mismo número que el pedido asociado',
  `pedido_id` int(11) NOT NULL COMMENT 'Pedido asociado - OBLIGATORIO',
  `cliente_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `fecha_emision` date DEFAULT curdate(),
  `fecha_vencimiento` date DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `descuento_total` decimal(12,2) DEFAULT 0.00,
  `impuestos_total` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL,
  `estado` enum('pendiente','pagada','vencida','cancelada') DEFAULT 'pendiente',
  `metodo_pago` enum('efectivo','tarjeta','transferencia','credito','cheque') DEFAULT 'efectivo',
  `direccion_facturacion_id` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `orden_venta`
--

INSERT INTO `orden_venta` (`id`, `numero_documento`, `pedido_id`, `cliente_id`, `empleado_id`, `fecha_emision`, `fecha_vencimiento`, `subtotal`, `descuento_total`, `impuestos_total`, `total`, `estado`, `metodo_pago`, `direccion_facturacion_id`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 'PED-2025-001', 1, 1, 2, '2025-08-07', '2025-08-22', 2500000.00, 375000.00, 405800.00, 2530800.00, 'pendiente', 'credito', 1, 'Facturación a 15 días', '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(2, 'PED-2025-002', 2, 2, 3, '2025-08-07', '2025-08-14', 1800000.00, 324000.00, 279840.00, 1755840.00, 'pendiente', 'transferencia', 4, 'Pago por transferencia bancaria', '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(3, 'PED-2025-003', 3, 4, 4, '2025-08-06', '2025-08-13', 3200000.00, 640000.00, 486400.00, 3046400.00, 'pendiente', 'credito', 7, 'Cliente con crédito aprobado', '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(4, 'PED-2025-004', 4, 3, 2, '2025-08-05', '2025-08-12', 850000.00, 0.00, 161500.00, 1011500.00, 'pagada', 'efectivo', 6, 'Pago en efectivo contra entrega', '2025-08-08 01:08:52', '2025-08-08 01:08:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `numero_documento` varchar(50) NOT NULL COMMENT 'Número único compartido con orden_venta',
  `cliente_id` int(11) NOT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `estado` enum('borrador','confirmado','en_preparacion','listo_envio','enviado','entregado','cancelado') DEFAULT 'borrador',
  `fecha_pedido` date DEFAULT curdate(),
  `fecha_entrega_estimada` date DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `descuento_porcentaje` decimal(5,2) NOT NULL DEFAULT 0.00,
  `descuento_total` decimal(12,2) DEFAULT 0.00,
  `impuestos_total` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `direccion_entrega_id` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `pedido_template` tinyint(1) DEFAULT 0 COMMENT 'Marcar como plantilla para re-pedidos',
  `nombre_template` varchar(100) DEFAULT NULL COMMENT 'Nombre de la plantilla',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `numero_documento`, `cliente_id`, `empleado_id`, `estado`, `fecha_pedido`, `fecha_entrega_estimada`, `subtotal`, `descuento_total`, `impuestos_total`, `total`, `direccion_entrega_id`, `observaciones`, `pedido_template`, `nombre_template`, `created_at`, `updated_at`) VALUES
(1, 'PED-2025-001', 1, 2, 'confirmado', '2025-08-07', '2025-08-09', 2500000.00, 375000.00, 405800.00, 2530800.00, 1, 'Entrega en horario de oficina - Obra en construcción', 0, NULL, '2025-08-08 01:08:52', '2025-08-08 03:36:23'),
(2, 'PED-2025-002', 2, 3, 'en_preparacion', '2025-08-07', '2025-08-08', 1800000.00, 324000.00, 279840.00, 1755840.00, 4, 'Material para reparación de tubería principal', 0, NULL, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(3, 'PED-2025-003', 4, 4, 'listo_envio', '2025-08-06', '2025-08-08', 3200000.00, 640000.00, 486400.00, 3046400.00, 7, 'Pedido urgente - Cliente VIP', 0, NULL, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(4, 'PED-2025-004', 3, 2, 'enviado', '2025-08-05', '2025-08-07', 850000.00, 0.00, 161500.00, 1011500.00, 6, 'Primera compra del cliente', 0, NULL, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(5, 'PED-2025-005', 6, 4, 'borrador', '2025-08-07', '2025-08-10', 4500000.00, 630000.00, 734100.00, 4604100.00, 10, 'Pedido para proyecto residencial', 1, 'Pedido Mensual Construcciones Valle', '2025-08-08 01:08:52', '2025-08-08 01:08:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `descripcion` text NOT NULL,
  `precio` decimal(12,2) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `codigo`, `descripcion`, `precio`, `grupo_id`, `created_at`, `updated_at`) VALUES
(1, '2903785', 'BRIDA AJUSTABLE PVC SCH 80 PSI 150 6', 140476.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(2, '2000225', 'ABRAZADERA INOX SILLA KIT 160MM', 47500.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(3, '2000226', 'ABRAZADERA INOX SILLA KIT 200MM', 52020.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(4, '2000227', 'ABRAZADERA INOX SILLA KIT 250MM', 59556.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(5, '2000228', 'ABRAZADERA INOX SILLA KIT 315MM', 67564.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(6, '2903140', 'ACOPLE FLEX 1/2 A 1/2 LAVAMANOS 40 CM (PARA PRUEBAS)', 2716.00, 3, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(7, '2907231', 'ADAPTADOR HEMBRA CPVC ROSCA METÁLICA 1PULG', 99860.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(8, '2907229', 'ADAPTADOR HEMBRA CPVC ROSCA METÁLICA  1/2PULG', 28529.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(9, '2907233', 'ADAPTADOR HEMBRA CPVC ROSCA METÁLICA 1-1/2PULG', 238643.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(10, '2907232', 'ADAPTADOR HEMBRA CPVC ROSCA METÁLICA 1-1/4PULG', 145847.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(11, '2907234', 'ADAPTADOR HEMBRA CPVC ROSCA METÁLICA 2PULG', 286843.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(12, '2907230', 'ADAPTADOR HEMBRA CPVC ROSCA METÁLICA  3/4PULG', 33103.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(13, '2907421', 'ADAPTADOR HEMBRA CPVC SCH 80 1PULG', 47305.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(14, '2907422', 'ADAPTADOR HEMBRA CPVC SCH 80 1.1/4PULG', 60429.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(15, '2907419', 'ADAPTADOR HEMBRA CPVC SCH 80  1/2PULG', 21597.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(16, '2907423', 'ADAPTADOR HEMBRA CPVC SCH 80 1-1/2PULG', 70333.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(17, '2907424', 'ADAPTADOR HEMBRA CPVC SCH 80 2PULG', 108356.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(18, '2907425', 'ADAPTADOR HEMBRA CPVC SCH 80 2.1/2PULG', 232450.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(19, '2907426', 'ADAPTADOR HEMBRA CPVC SCH 80 3PULG', 475913.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(20, '2907420', 'ADAPTADOR HEMBRA CPVC SCH 80  3/4PULG', 23864.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(21, '2907427', 'ADAPTADOR HEMBRA CPVC SCH 80 4PULG', 855047.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(22, '2900691', 'ADAPTADOR HEMBRA PF+UAD 1/2PULG', 3500.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(23, '2907509', 'ADAPTADOR HEMBRA PVC SCH 80 1.1/2PULG', 44299.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(24, '2907508', 'ADAPTADOR HEMBRA PVC SCH 80 1.1/4PULG', 36120.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(25, '2907510', 'ADAPTADOR HEMBRA PVC SCH 80 2PULG', 77232.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(26, '2907511', 'ADAPTADOR HEMBRA PVC SCH 80 2.1/2PULG', 122019.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(27, '2907512', 'ADAPTADOR HEMBRA PVC SCH 80 3PULG', 136359.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(28, '2907513', 'ADAPTADOR HEMBRA PVC SCH 80 4PULG', 236118.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(29, '2900714', 'ADAPTADOR HEMBRA PVC-P 1/2PULG', 671.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(30, '2900706', 'ADAPTADOR HEMBRA PVC-P 1-1/4PULG', 4401.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(31, '2900702', 'ADAPTADOR HEMBRA PVC-P 1-1/2PULG', 7444.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(32, '2900724', 'ADAPTADOR HEMBRA PVC-P 2PULG', 13252.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(33, '2900740', 'ADAPTADOR HEMBRA PVC-P 3/4PULG', 1208.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(34, '2900678', 'ADAPTADOR LIMPIEZA PVC-S 2PULG', 9270.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(35, '2900680', 'ADAPTADOR LIMPIEZA PVC-S 3PULG', 19936.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(36, '2900682', 'ADAPTADOR LIMPIEZA PVC-S 4PULG', 29640.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(37, '2900686', 'ADAPTADOR LIMPIEZA PVC-S 6PULG', 77155.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(38, '2910331', 'ADAPTADOR MACHO CPVC  1/2PULG', 2299.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(39, '2910333', 'ADAPTADOR MACHO CPVC 1PULG', 17058.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(40, '2910334', 'ADAPTADOR MACHO CPVC 1.1/4PULG', 28423.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(41, '2910335', 'ADAPTADOR MACHO CPVC 1-1/2PULG', 35336.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(42, '2903734', 'ADAPTADOR MACHO CPVC 2PULG', 61615.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(43, '2910332', 'ADAPTADOR MACHO CPVC  3/4PULG', 2827.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(44, '2907237', 'ADAPTADOR MACHO CPVC ROSCA METÁLICA 1PULG', 72716.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(45, '2907238', 'ADAPTADOR MACHO CPVC ROSCA METÁLICA 1.1/4PULG', 200555.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(46, '2907235', 'ADAPTADOR MACHO CPVC ROSCA METÁLICA  1/2PULG', 23408.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(47, '2907239', 'ADAPTADOR MACHO CPVC ROSCA METÁLICA 1-1/2PULG', 264822.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(48, '2907240', 'ADAPTADOR MACHO CPVC ROSCA METÁLICA 2PULG', 415655.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(49, '2907236', 'ADAPTADOR MACHO CPVC ROSCA METÁLICA  3/4PULG', 41969.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(50, '2907429', 'ADAPTADOR MACHO CPVC SCH 80 1PULG', 35469.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(51, '2907430', 'ADAPTADOR MACHO CPVC SCH 80 1.1/4PULG', 90560.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(52, '2906862', 'ADAPTADOR MACHO CPVC SCH 80  1/2PULG', 19318.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(53, '2906863', 'ADAPTADOR MACHO CPVC SCH 80 1-1/2PULG', 109817.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(54, '2906864', 'ADAPTADOR MACHO CPVC SCH 80 2PULG', 192284.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(55, '2907431', 'ADAPTADOR MACHO CPVC SCH 80 2.1/2PULG', 293464.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(56, '2907432', 'ADAPTADOR MACHO CPVC SCH 80 3PULG', 394642.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(57, '2907428', 'ADAPTADOR MACHO CPVC SCH 80  3/4PULG', 23560.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(58, '2907433', 'ADAPTADOR MACHO CPVC SCH 80 4PULG', 663278.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(59, '2900755', 'ADAPTADOR MACHO PF+UAD 1/2PULG', 3429.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(60, '2903146', 'ADAPTADOR MACHO PF+UAD 3/4', 28625.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(61, '2911122', 'ADAPTADOR MACHO PVC SCH 80 1PULG', 24579.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(62, '2907515', 'ADAPTADOR MACHO PVC SCH 80 1.1/2PULG', 38074.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(63, '2907514', 'ADAPTADOR MACHO PVC SCH 80 1.1/4PULG', 28397.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(64, '2907516', 'ADAPTADOR MACHO PVC SCH 80 2PULG', 60265.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(65, '2907517', 'ADAPTADOR MACHO PVC SCH 80 2.1/2PULG', 67032.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(66, '2907518', 'ADAPTADOR MACHO PVC SCH 80 3PULG', 74400.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(67, '2907519', 'ADAPTADOR MACHO PVC SCH 80 4PULG', 135298.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(68, '2900762', 'ADAPTADOR MACHO PVC-P 1PULG', 2248.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(69, '2900779', 'ADAPTADOR MACHO PVC-P 1/2PULG', 594.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(70, '2900767', 'ADAPTADOR MACHO PVC-P 1-1/2PULG', 5546.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(71, '2900771', 'ADAPTADOR MACHO PVC-P 1-1/4PULG', 4733.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(72, '2900784', 'ADAPTADOR MACHO PVC-P 2PULG', 7923.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(73, '2900790', 'ADAPTADOR MACHO PVC-P 2.1/2PULG', 20601.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(74, '2900794', 'ADAPTADOR MACHO PVC-P 3PULG', 31145.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(75, '2900802', 'ADAPTADOR MACHO PVC-P 3/4PULG', 1077.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(76, '2900807', 'ADAPTADOR MACHO PVC-P 4PULG', 57292.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(77, '2902965', 'ADAPTADOR NOVAFORT 110X4PULGSANIT', 18030.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(78, '2902604', 'ADAPTADOR NOVAFORT 160X6PULG SANIT', 47473.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(79, '2902605', 'ADAPTADOR NOVAFORT 200X6PULGSANIT', 67126.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(80, '2904914', 'ADAPTADOR NOVAFORT 200X8PULGSANIT', 225835.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(81, '2904915', 'ADAPTADOR NOVAFORT 250X10PULGSANIT', 519454.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(82, '2900669', 'ADAPTADOR NOVAFORT SANITARIA 4 - 110', 23462.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(83, '2900670', 'ADAPTADOR NOVAFORT SANITARIA 6 - 160', 46236.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(84, '2900671', 'ADAPTADOR NOVAFORT SANITARIA 8 - 200', 89930.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(85, '2905390', 'ADAPTADORES LIMPIEZA PVCS 10PULG', 2658520.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(86, '2905389', 'ADAPTADORES LIMPIEZA PVCS 8PULG', 979512.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(87, '2906396', 'ADHESIVO EPÓXICO NOVAFORT 2 LITROS', 280238.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(88, '2906320', 'ADHESIVO EPÓXICO NOVAFORT 1 LITRO', 144200.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(89, '2907434', 'BRIDA AJUSTABLE CPVC SCH 80  1-1/2PULG', 110567.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(90, '2907435', 'BRIDA AJUSTABLE CPVC SCH 80  2PULG', 133646.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(91, '2907436', 'BRIDA AJUSTABLE CPVC SCH 80  2-1/2PULG', 277146.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(92, '2907437', 'BRIDA AJUSTABLE CPVC SCH 80  3PULG', 315814.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(93, '2907438', 'BRIDA AJUSTABLE CPVC SCH 80  4PULG', 354483.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(94, '2907439', 'BRIDA AJUSTABLE CPVC SCH 80  6PULG', 603575.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(95, '2903787', 'BRIDA AJUSTABLE PVC SCH 80 (150 PSI) 10PULG', 594009.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(96, '2903788', 'BRIDA AJUSTABLE PVC SCH 80 (150 PSI) 12PULG', 647286.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(97, '2903783', 'BRIDA AJUSTABLE PVC SCH 80 PSI 150 3PULG', 54576.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(98, '2903784', 'BRIDA AJUSTABLE PVC SCH 80 PSI 150 4PULG', 79237.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(99, '2903786', 'BRIDA AJUSTABLE PVC SCH 80 (150 PSI) 8PULG', 238705.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(100, '2907520', 'BRIDA AJUSTABLE PVC SCH 80 1 1/2PULG', 41164.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(101, '2907521', 'BRIDA AJUSTABLE PVC SCH 80 (150 PSI) 2PULG', 56040.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(102, '2910946', 'BRIDA FLEXIBLE 3PULG', 58143.00, 10, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(103, '2910947', 'BRIDA FLEXIBLE CORTA 4PULG', 58363.00, 10, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(104, '2906865', 'BUJE CPVC SCH 80 1PULG x 1/2PULG', 26694.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(105, '2907441', 'BUJE CPVC SCH 80 1PULG x 3/4PULG', 38343.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(106, '2907447', 'BUJE CPVC SCH 80 1.1/2PULG x 1PULG', 69610.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(107, '2907448', 'BUJE CPVC SCH 80 1.1/2PULG x 1.1/4PULG', 69610.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(108, '2907445', 'BUJE CPVC SCH 80 1.1/2PULG x 1/2PULG', 80134.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(109, '2907446', 'BUJE CPVC SCH 80 1.1/2PULG x 3/4PULG', 80134.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(110, '2907444', 'BUJE CPVC SCH 80 1.1/4PULG x 1PULG', 70896.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(111, '2907442', 'BUJE CPVC SCH 80 1.1/4PULG x 1/2PULG', 70899.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(112, '2907443', 'BUJE CPVC SCH 80 1.1/4PULG x 3/4PULG', 70896.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(113, '2907451', 'BUJE CPVC SCH 80  2PULG x 1PULG', 103551.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(114, '2906866', 'BUJE CPVC SCH 80  2PULG x 1.1/2PULG', 97733.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(115, '2907452', 'BUJE CPVC SCH 80 2PULG x 1.1/4PULG', 110199.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(116, '2907449', 'BUJE CPVC SCH 80 2PULG x 1/2PULG', 103551.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(117, '2907450', 'BUJE CPVC SCH 80 2PULG x 3/4PULG', 103551.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(118, '2906868', 'BUJE CPVC SCH 80  2.1/2PULG x 1.1/2PULG', 93652.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(119, '2907453', 'BUJE CPVC SCH 80  2.1/2PULG x 2PULG', 104404.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(120, '2906869', 'BUJE CPVC SCH 80  3 x 2 1/2PULG', 205137.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(121, '2907180', 'BUJE CPVC SCH 80 3PULG x 2PULG', 159095.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(122, '2907440', 'BUJE CPVC SCH 80   3/4PULG x 1/2PULG', 16591.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(123, '2906870', 'BUJE CPVC SCH 80  4PULG x 2PULG', 212022.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(124, '2907181', 'BUJE CPVC SCH 80  4PULG x 3PULG', 175817.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(125, '2907455', 'BUJE CPVC SCH 80  6PULG x 4PULG', 338066.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(126, '2907524', 'BUJE PVC SCH 80 1.1/4 x 1', 16698.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(127, '2907522', 'BUJE PVC SCH 80 1.1/4 x 1/2', 17392.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(128, '2907523', 'BUJE PVC SCH 80 1.1/4 x 3/4', 16698.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(129, '2907527', 'BUJE PVC SCH 80 1-1/2 x 1', 21052.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(130, '2907525', 'BUJE PVC SCH 80 1-1/2 x 1/2', 21446.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(131, '2907528', 'BUJE PVC SCH 80 1-1/2 x 1-1/4', 21449.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(132, '2907526', 'BUJE PVC SCH 80 1-1/2 x 3/4', 21446.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(133, '2907531', 'BUJE PVC SCH 80 2 x 1', 31184.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(134, '2907533', 'BUJE PVC SCH 80 2 x 1.1/2', 30771.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(135, '2907532', 'BUJE PVC SCH 80 2 x 1.1/4', 29241.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(136, '2907529', 'BUJE PVC SCH 80 2 x 1/2', 31236.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(137, '2907530', 'BUJE PVC SCH 80 2 x 3/4', 30575.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(138, '2907534', 'BUJE PVC SCH 80 2.1/2 x 1', 75586.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(139, '2907536', 'BUJE PVC SCH 80 2.1/2 x 1.1/2', 54145.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(140, '2907535', 'BUJE PVC SCH 80 2.1/2 x 1.1/4', 52874.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(141, '2907537', 'BUJE PVC SCH 80 2.1/2 x 2', 50770.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(142, '2907538', 'BUJE PVC SCH 80 3 x 1', 89649.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(143, '2907540', 'BUJE PVC SCH 80 3 x 1.1/2', 84222.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(144, '2907539', 'BUJE PVC SCH 80 3 x 1.1/4', 89649.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(145, '2907541', 'BUJE PVC SCH 80 3 x 2', 80557.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(146, '2907542', 'BUJE PVC SCH 80 3 x 2.1/2', 82675.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(147, '2907543', 'BUJE PVC SCH 80 4 x 2', 118972.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(148, '2907544', 'BUJE PVC SCH 80 4 x 3', 118972.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(149, '2907545', 'BUJE PVC SCH 80 6 x 3', 161005.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(150, '2909789', 'BUJE PVC SCH 80 6 x 4', 155107.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(151, '2907546', 'BUJE PVC SCH 80 8 x 6', 377327.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(152, '2900846', 'BUJE ROSCADO PVC-P 1PULG x 1/2PULG', 3390.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(153, '2900854', 'BUJE ROSCADO PVC-P 1PULG x 3/4PULG', 3390.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(154, '2900863', 'BUJE ROSCADO PVC-P 1.1/2PULG x 1PULG', 6778.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(155, '2900871', 'BUJE ROSCADO PVC-P 1.1/2PULG x 1.1/4PULG', 6778.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(156, '2900878', 'BUJE ROSCADO PVC-P 1.1/2PULG x 1/2PULG', 6778.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(157, '2900887', 'BUJE ROSCADO PVC-P 1.1/2PULG x 3/4PULG', 6778.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(158, '2900895', 'BUJE ROSCADO PVC-P 1.1/4PULG x 1PULG', 5688.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(159, '2900903', 'BUJE ROSCADO PVC-P 1.1/4PULG x 1/2PULG', 5688.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(160, '2900910', 'BUJE ROSCADO PVC-P 1.1/4PULG x 3/4PULG', 5688.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(161, '2900921', 'BUJE ROSCADO PVC-P  1/2PULG x 3/8PULG', 1551.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(162, '2900924', 'BUJE ROSCADO PVC-P 2PULG x 1PULG', 10902.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(163, '2900933', 'BUJE ROSCADO PVC-P 2PULG x 1.1/2PULG', 10902.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(164, '2900942', 'BUJE ROSCADO PVC-P 2PULG x 1.1/4PULG', 10902.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(165, '2900950', 'BUJE ROSCADO PVC-P 2PULG x 1/2PULG', 10902.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(166, '2900956', 'BUJE ROSCADO PVC-P 2PULG x 3/4PULG', 10902.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(167, '2900976', 'BUJE ROSCADO PVC-P 3PULG x 2PULG', 47889.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(168, '2900990', 'BUJE ROSCADO PVC-P 3/4PULG x 1/2PULG', 1945.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(169, '2910350', 'BUJE SOLDADO CPVC  3/4 x 1/2PULG', 2009.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(170, '2910352', 'BUJE SOLDADO CPVC 1 x 1/2PULG', 7945.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(171, '2910354', 'BUJE SOLDADO CPVC 1 x 3/4PULG', 7805.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(172, '2910356', 'BUJE SOLDADO CPVC 1.1/2PULG x 1PULG', 14696.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(173, '2910357', 'BUJE SOLDADO CPVC 1.1/2PULG x 1.1/4PULG', 14659.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(174, '2903742', 'BUJE SOLDADO CPVC 1.1/2PULG x  1/2PULG', 13789.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(175, '2903743', 'BUJE SOLDADO CPVC 1.1/2PULG x  3/4PULG', 14719.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(176, '2903735', 'BUJE SOLDADO CPVC 1.1/4 x  1/2PULG', 11820.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(177, '2910355', 'BUJE SOLDADO CPVC 1.1/4 x 1PULG', 10023.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(178, '2903736', 'BUJE SOLDADO CPVC 1.1/4 x  3/4PULG', 11727.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(179, '2903748', 'BUJE SOLDADO CPVC 2 x 1PULG', 30319.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(180, '2903750', 'BUJE SOLDADO CPVC 2 x 1.1/2PULG', 29703.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(181, '2903749', 'BUJE SOLDADO CPVC 2 X 1.1/4PULG', 29817.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(182, '2903746', 'BUJE SOLDADO CPVC 2 X 1/2PULG', 31990.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(183, '2903747', 'BUJE SOLDADO CPVC 2 X 3/4PULG', 31989.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(184, '2900849', 'BUJE SOLDADO PVC-P 1 x 1/2PULG', 1662.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(185, '2900858', 'BUJE SOLDADO PVC-P 1 x 3/4PULG', 1662.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(186, '2900866', 'BUJE SOLDADO PVC-P 1-1/2PULG x 1PULG', 4928.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(187, '2900882', 'BUJE SOLDADO PVC-P 1-1/2PULG x 1/2PULG', 4928.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(188, '2900875', 'BUJE SOLDADO PVC-P 1-1/2PULG x 1-1/4PULG', 4928.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(189, '2900890', 'BUJE SOLDADO PVC-P 1-1/2PULG x 3/4PULG', 4928.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(190, '2900898', 'BUJE SOLDADO PVC-P 1-1/4PULG x 1PULG', 3192.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(191, '2900906', 'BUJE SOLDADO PVC-P 1-1/4PULG x 1/2PULG', 3192.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(192, '2900914', 'BUJE SOLDADO PVC-P 1-1/4PULG x 3/4PULG', 3192.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(193, '2900928', 'BUJE SOLDADO PVC-P 2PULG x 1PULG', 7534.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(194, '2900952', 'BUJE SOLDADO PVC-P 2PULG x 1/2PULG', 7534.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(195, '2900937', 'BUJE SOLDADO PVC-P 2PULG x 1-1/2PULG', 7534.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(196, '2900945', 'BUJE SOLDADO PVC-P 2PULG x 1-1/4PULG', 7534.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(197, '2900959', 'BUJE SOLDADO PVC-P 2PULG x 3/4PULG', 7534.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(198, '2900966', 'BUJE SOLDADO PVC-P 2-1/2PULG x 1-1/2PULG', 18898.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(199, '2900971', 'BUJE SOLDADO PVC-P 2-1/2PULG x 2PULG', 17748.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(200, '2900979', 'BUJE SOLDADO PVC-P 3PULG x 2PULG', 27136.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(201, '2900986', 'BUJE SOLDADO PVC-P 3PULG x 2.1/2PULG', 27136.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(202, '2900995', 'BUJE SOLDADO PVC-P 3/4PULG x 1/2PULG', 835.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(203, '2901003', 'BUJE SOLDADO PVC-P 4PULG x 2PULG', 42792.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(204, '2901009', 'BUJE SOLDADO PVC-P 4PULG x 2.1/2PULG', 42792.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(205, '2901014', 'BUJE SOLDADO PVC-P 4PULG x 3PULG', 42792.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(206, '2903813', 'BUJE SOLDADO PVC-S 10PULG x 6PULG', 587117.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(207, '2903814', 'BUJE SOLDADO PVC-S 10PULG x 8PULG', 641331.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(208, '2901021', 'BUJE SOLDADO PVC-S 2PULG x 1-1/2PULG', 3468.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(209, '2901026', 'BUJE SOLDADO PVC-S 3PULG x 1-1/2PULG', 7982.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(210, '2901028', 'BUJE SOLDADO PVC-S 3PULG x 2PULG', 7530.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(211, '2901030', 'BUJE SOLDADO PVC-S 4PULG x 2PULG', 13266.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(212, '2901033', 'BUJE SOLDADO PVC-S 4PULG x 3PULG', 13266.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(213, '2901036', 'BUJE SOLDADO PVC-S 6PULG x 4PULG', 49882.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(214, '2903811', 'BUJE SOLDADO PVC-S 8PULG x 4PULG', 188476.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(215, '2909775', 'BUJE SOLDADO PVC-S 8PULG x 6PULG', 152232.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(216, '2000245', 'CAUCHO  KIT SILLA TEE - NOVAFORT 160X110', 19446.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(217, '2000246', 'CAUCHO  KIT SILLA TEE - NOVAFORT 200 X110', 24101.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(218, '2000247', 'CAUCHO  KIT SILLA TEE - NOVAFORT 200 X160', 24101.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(219, '2000248', 'CAUCHO  KIT SILLA TEE - NOVAFORT 250X110', 26479.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(220, '2000249', 'CAUCHO  KIT SILLA TEE - NOVAFORT 250X160', 24082.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(221, '2000250', 'CAUCHO  KIT SILLA TEE - NOVAFORT 315X110', 28078.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(222, '2000251', 'CAUCHO  KIT SILLA TEE - NOVAFORT 315X160', 25699.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(223, '2000252', 'CAUCHO KIT SILLA YEE - NOVAFORT 160X110', 19446.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(224, '2000253', 'CAUCHO KIT SILLA YEE - NOVAFORT 200 X110', 24101.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(225, '2000254', 'CAUCHO KIT SILLA YEE - NOVAFORT 200 X160', 24101.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(226, '2000255', 'CAUCHO KIT SILLA YEE - NOVAFORT 250X110', 26479.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(227, '2000256', 'CAUCHO KIT SILLA YEE - NOVAFORT 250X160', 24082.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(228, '2000257', 'CAUCHO KIT SILLA YEE - NOVAFORT 315X110', 28078.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(229, '2000258', 'CAUCHO KIT SILLA YEE - NOVAFORT 315X160', 30571.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(230, '2000401', 'CAUCHO KIT SILLA YEE S4 DALIAN - NOVAFORT 200X160', 21912.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(231, '2000402', 'CAUCHO KIT SILLA YEE S4 DALIAN - NOVAFORT 250X160', 21894.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(232, '2000400', 'CAUCHO KIT SILLA YEE S4 DALIAN - NOVAFORT 315X160', 23363.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(233, '2908186', 'CINTA SELLANTE PREMIUN PARA ROSCA (TEFLON) 12MM X 12M (empaque de 12 unidades) - PAVCO', 38864.00, 9, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(234, '2908187', 'CINTA SELLANTE PREMIUN PARA ROSCA (TEFLON) 18MM X 20M (empaque de 6 unidades) - PAVCO', 40857.00, 9, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(235, '2909556', 'CINTA SELLANTE PREMIUN PARA ROSCA (TEFLON) 18MM X 50M - PAVCO', 13303.00, 9, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(236, '2909557', 'CINTA SELLANTE PREMIUN PARA ROSCA (TEFLON) 24MM X 50M - PAVCO', 17191.00, 9, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(237, '2903559', 'CLICK INSERTA 250 X 160', 279510.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(238, '2903478', 'CLICK INSERTA 315 X 160', 320826.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(239, '2903634', 'CLICK INSERTA 400 X 160', 342389.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(240, '2903635', 'CLICK INSERTA 450-500 X 160', 386733.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(241, '2901162', 'CODO 22° PVC-S 2PULG C x C ', 6174.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(242, '2901163', 'CODO 22° PVC-S 2PULG C x E ', 6174.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(243, '2901164', 'CODO 22° PVC-S 3PULG C x C ', 11741.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(244, '2901165', 'CODO 22° PVC-S 3PULG C x E ', 11741.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(245, '2901167', 'CODO 22° PVC-S 4PULG C x C ', 19491.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(246, '2901168', 'CODO 22° PVC-S 4PULG C x E ', 19491.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(247, '2903450', 'CODO 22° PVC-S 6PULG C x C ', 187324.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(248, '2901045', 'CODO 45 NOVAFORT 110MM - 4PULG', 30921.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(249, '2901046', 'CODO 45 NOVAFORT 160MM - 6PULG', 74233.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(250, '2902687', 'CODO 45 PREFABRICADO NOVAFORT 200MM', 213225.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(251, '2902688', 'CODO 45 PREFABRICADO NOVAFORT 250MM', 413068.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(252, '2903071', 'CODO 45 PREFABRICADO NOVAFORT 315MM', 634823.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(253, '2910363', 'CODO 45° CPVC 1PULG', 10047.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(254, '2910365', 'CODO 45° CPVC 1.1/2PULG', 36896.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(255, '2910364', 'CODO 45° CPVC 1.1/4PULG', 26046.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(256, '2910360', 'CODO 45° CPVC  1/2PULG', 2112.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(257, '2903753', 'CODO 45° CPVC 2PULG', 90319.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(258, '2910361', 'CODO 45° CPVC  3/4PULG', 3819.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(259, '2907458', 'CODO 45° CPVC SCH 80  1PULG', 39792.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(260, '2907459', 'CODO 45° CPVC SCH 80  1.1/4PULG', 78058.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(261, '2907456', 'CODO 45° CPVC SCH 80   1/2PULG', 17307.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(262, '2907460', 'CODO 45° CPVC SCH 80  1-1/2PULG', 80079.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(263, '2907461', 'CODO 45° CPVC SCH 80  2PULG', 89889.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(264, '2907462', 'CODO 45° CPVC SCH 80  2.1/2PULG', 183715.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(265, '2907182', 'CODO 45° CPVC SCH 80 3PULG', 189291.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(266, '2907457', 'CODO 45° CPVC SCH 80   3/4PULG', 25012.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(267, '2907183', 'CODO 45° CPVC SCH 80  4PULG', 348535.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(268, '2907463', 'CODO 45° CPVC SCH 80  6PULG', 888392.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(269, '2911385', 'CODO 45° H.D. C900 4PULG', 998520.00, 27, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(270, '2911120', 'CODO 45° PVC SCH 80 1PULG', 13070.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(271, '2907548', 'CODO 45° PVC SCH 80 1.1/2PULG', 42563.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(272, '2907547', 'CODO 45° PVC SCH 80 1.1/4PULG', 32393.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(273, '2907549', 'CODO 45° PVC SCH 80 2PULG', 50613.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(274, '2907550', 'CODO 45° PVC SCH 80 2.1/2PULG', 115851.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(275, '2907551', 'CODO 45° PVC SCH 80 3PULG', 129429.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(276, '2907552', 'CODO 45° PVC SCH 80 4PULG', 222769.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(277, '2909790', 'CODO 45° PVC SCH 80 6PULG', 295310.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(278, '2907553', 'CODO 45° PVC SCH 80 8PULG', 608245.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(279, '2901074', 'CODO 45° PVC-P  1/2PULG', 1424.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(280, '2901096', 'CODO 45° PVC-P  3/4PULG', 2276.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(281, '2901064', 'CODO 45° PVC-P 1PULG', 4334.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(282, '2901069', 'CODO 45° PVC-P 1-1/2PULG', 10509.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(283, '2901073', 'CODO 45° PVC-P 1-1/4PULG', 7838.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(284, '2901083', 'CODO 45° PVC-P 2PULG', 17378.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(285, '2901087', 'CODO 45° PVC-P 2.1/2PULG', 48988.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(286, '2901090', 'CODO 45° PVC-P 3PULG', 55950.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(287, '2901100', 'CODO 45° PVC-P 4PULG', 119036.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(288, '2901179', 'CODO 45° PVC-S  1-1/2PULG C x C ', 4375.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(289, '2901181', 'CODO 45° PVC-S  2PULG C x C ', 5307.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(290, '2901183', 'CODO 45° PVC-S  2PULG C x E ', 5307.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(291, '2901185', 'CODO 45° PVC-S  3PULG C x C ', 11407.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(292, '2901187', 'CODO 45° PVC-S  3PULG C x E ', 11407.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(293, '2901189', 'CODO 45° PVC-S  4PULG C x C ', 20091.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(294, '2901191', 'CODO 45° PVC-S  4PULG C x E ', 20091.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(295, '2901193', 'CODO 45° PVC-S  6PULG C x C ', 73064.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(296, '2901195', 'CODO 45° PVC-S  6PULG C x E ', 73064.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(297, '2909776', 'CODO 45° PVC-S  8PULG C x C ', 216538.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(298, '2903804', 'CODO 45° PVC-S 10PULG C x C ', 683792.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(299, '2903806', 'CODO 45° PVC-S 10PULG C x E ', 691624.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(300, '2901180', 'CODO 45° PVC-S  1-1/2PULG C x E ', 4375.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(301, '2903805', 'CODO 45° PVC-S  8PULG C x E ', 254014.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(302, '2902689', 'CODO 90 PREFABRICADO NOVAFORT 200MM', 216729.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(303, '2902690', 'CODO 90 PREFABRICADO NOVAFORT 250MM', 414462.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(304, '2902691', 'CODO 90 PREFABRICADO NOVAFORT 315MM', 634823.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(305, '2903061', 'CODO 90 PREFABRICADO NOVAFORT 355MM', 1553880.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(306, '2902692', 'CODO 90 PREFABRICADO NOVAFORT 400MM', 2077080.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(307, '2902693', 'CODO 90 PREFABRICADO NOVAFORT 450MM', 2651140.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(308, '2902723', 'CODO 90 PREFABRICADO NOVAFORT 500MM', 2619920.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(309, '2910372', 'CODO 90° CPVC  1/2PULG', 2112.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(310, '2910373', 'CODO 90° CPVC  3/4PULG', 3819.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(311, '2910374', 'CODO 90° CPVC 1PULG', 11215.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(312, '2910377', 'CODO 90° CPVC 1.1/2PULG', 40097.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(313, '2910375', 'CODO 90° CPVC 1.1/4PULG', 22764.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(314, '2903756', 'CODO 90° CPVC 2PULG', 78799.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(315, '2907466', 'CODO 90° CPVC SCH 80 1PULG', 28913.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(316, '2907467', 'CODO 90° CPVC SCH 80  1.1/4PULG', 55450.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(317, '2907464', 'CODO 90° CPVC SCH 80   1/2PULG', 14607.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(318, '2907468', 'CODO 90° CPVC SCH 80  1-1/2PULG', 69829.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(319, '2907469', 'CODO 90° CPVC SCH 80 2PULG', 116024.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(320, '2906871', 'CODO 90° CPVC SCH 80 2.1/2PULG', 191107.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(321, '2907465', 'CODO 90° CPVC SCH 80   3/4PULG', 16927.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(322, '2906873', 'CODO 90° CPVC SCH 80  4PULG', 352679.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(323, '2907470', 'CODO 90° CPVC SCH 80  6PULG', 671828.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(324, '2911388', 'CODO 90° H.D. C900 4PULG', 1176730.00, 27, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(325, '2901051', 'CODO 90 NOVAFORT 110MM - 4PULG', 61296.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(326, '2901052', 'CODO 90 NOVAFORT 160MM - 6PULG', 143889.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(327, '2911119', 'CODO 90° PVC SCH 80 1PULG', 12543.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(328, '2907555', 'CODO 90° PVC SCH 80 1.1/2PULG', 17413.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(329, '2907554', 'CODO 90° PVC SCH 80 1.1/4PULG', 17412.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(330, '2907556', 'CODO 90° PVC SCH 80 2PULG', 23698.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(331, '2907557', 'CODO 90° PVC SCH 80 2.1/2PULG', 49253.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(332, '2907558', 'CODO 90° PVC SCH 80 3PULG', 58285.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(333, '2907559', 'CODO 90° PVC SCH 80 4PULG', 82038.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(334, '2909791', 'CODO 90° PVC SCH 80 6PULG', 245734.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(335, '2907560', 'CODO 90° PVC SCH 80 8PULG', 643416.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(336, '2901122', 'CODO 90° PVC-P  1/2PULG', 862.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(337, '2901144', 'CODO 90° PVC-P  3/4PULG', 1380.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(338, '2901105', 'CODO 90° PVC-P 1PULG', 2699.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(339, '2901110', 'CODO 90° PVC-P 1-1/2PULG', 9678.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(340, '2901114', 'CODO 90° PVC-P 1-1/4PULG', 5183.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(341, '2901127', 'CODO 90° PVC-P 2PULG', 15862.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(342, '2901132', 'CODO 90° PVC-P 2.1/2PULG', 45680.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(343, '2901137', 'CODO 90° PVC-P 3PULG', 59115.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(344, '2901149', 'CODO 90° PVC-P 4PULG', 128247.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(345, '2903802', 'CODO 90° PVC-S 10PULG C x C ', 822302.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(346, '2901209', 'CODO 90° PVC-S 1-1/2PULG C x C ', 3783.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(347, '2901210', 'CODO 90° PVC-S 1-1/2PULG C x E ', 4622.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(348, '2901213', 'CODO 90° PVC-S 2PULG C x C ', 4433.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(349, '2901214', 'CODO 90° PVC-S 2PULG C x E ', 5467.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(350, '2901217', 'CODO 90° PVC-S 3PULG C x C ', 10270.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(351, '2901218', 'CODO 90° PVC-S 3PULG C x E ', 11889.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(352, '2901221', 'CODO 90° PVC-S 4PULG C x C ', 17858.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(353, '2901222', 'CODO 90° PVC-S 4PULG C x E ', 22017.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(354, '2901224', 'CODO 90° PVC-S 6PULG C x C ', 151167.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(355, '2901226', 'CODO 90° PVC-S 6PULG C x E ', 151167.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(356, '2909777', 'CODO 90° PVC-S 8PULG C x C', 241051.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(357, '2908617', 'CODO 90º PVC UP RADIO CORTO 2PULG', 79621.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(358, '2909095', 'CODO 90º PVC UP RADIO CORTO 3PULG', 119895.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(359, '2909096', 'CODO 90º PVC UP RADIO CORTO 4PULG', 158823.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(360, '2906872', 'CODO CPVC SCH 80 3PULG', 233738.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(361, '2901156', 'CODO REVENTILADO 3PULG x 2PULG', 31383.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(362, '2901157', 'CODO REVENTILADO 4PULG x 2PULG', 42018.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(363, '2901791', 'CODO 90° ROS - SOLD PVC-P 1/2PULG', 2141.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(364, '2904567', 'COLLAR DE DERIVACION CON INSERTO METALICO 2PULG X 1/2PULG', 20606.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(365, '2904569', 'COLLAR DE DERIVACION CON INSERTO METALICO 3PULG x 1/2PULG', 29371.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(366, '2904570', 'COLLAR DE DERIVACION CON INSERTO METALICO 4PULG X 1/2PULG', 29884.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(367, '2904571', 'COLLAR DE DERIVACION CON INSERTO METALICO 6PULG X 1/2PULG', 37988.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(368, '2901231', 'COLLAR DERIVACION UNION MECANICA SNAP 2PULG x 3/4PULG', 13438.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(369, '2901239', 'COLLAR DERIVACION UNION MECANICA SNAP 3PULG x 3/4PULG', 25847.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(370, '2901243', 'COLLAR DERIVACION UNION MECANICA SNAP 4PULG x 3/4PULG', 28413.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(371, '2901247', 'COLLAR DERIVACION UNION MECANICA SNAP 6PULG x 3/4PULG', 33324.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(372, '2900186', 'CONDUGAS BLANCO (recubrimiento TB - Rollo de 50m)', 141699.00, 18, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(373, '2903070', 'CONECTOR NOVA- FORT CONCRETO 160X6', 99379.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(374, '2902606', 'CONECTOR NOVA- FORT CONCRETO 200X8PULG', 102163.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(375, '2903479', 'COPA SIERRA CLICK 160', 2043700.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(376, '2901254', 'ENTRADA TANQUE PLÁSTICO PVC-P 1/2PULG', 8056.00, 26, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(377, '2000281', 'HIDROSELLO NOVAFORT 110MM S8', 3581.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(378, '2000282', 'HIDROSELLO NOVAFORT 160 MM S8 Y S4', 5817.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(379, '2000283', 'HIDROSELLO NOVAFORT 200 MM S8 Y S4', 10513.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(380, '2000845', 'HIDROSELLO NOVAFORT 24PULG S4', 138989.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(381, '2000390', 'HIDROSELLO NOVAFORT 250 MM S4', 19087.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(382, '2000284', 'HIDROSELLO NOVAFORT 250 MM S8', 18362.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(383, '2000358', 'HIDROSELLO NOVAFORT 27PULG S4', 208263.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(384, '2000359', 'HIDROSELLO NOVAFORT 30PULG S4', 272791.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(385, '2000285', 'HIDROSELLO NOVAFORT 315 MM S8 Y S4', 38521.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(386, '2000541', 'HIDROSELLO NOVAFORT 33', 350573.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(387, '2000386', 'HIDROSELLO NOVAFORT 355 MM S8 Y S4', 40440.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(388, '2000542', 'HIDROSELLO NOVAFORT 36', 409396.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(389, '2000734', 'HIDROSELLO NOVAFORT 39', 496001.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(390, '2000391', 'HIDROSELLO NOVAFORT 400 MM S4', 78445.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(391, '2000286', 'HIDROSELLO NOVAFORT 400 MM S8', 78445.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(392, '2000751', 'HIDROSELLO NOVAFORT 42', 558859.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(393, '2000287', 'HIDROSELLO NOVAFORT 450 MM S8', 113392.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(394, '2000288', 'HIDROSELLO NOVAFORT 500 MM S8', 127565.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(395, '2901258', 'JUNTA DE EXPANSION PVC-S 3PULG', 45664.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(396, '2901260', 'JUNTA DE EXPANSION PVC-S 4PULG', 52134.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(397, '2902730', 'JUNTA DE EXPANSION PVC-S 6PULG', 104481.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(398, '2901264', 'KIT SILLA TEE 160X110', 171378.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(399, '2901265', 'KIT SILLA TEE 200X110', 198092.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(400, '2901266', 'KIT SILLA TEE 200X160', 198092.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(401, '2901267', 'KIT SILLA TEE 250X110', 223937.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(402, '2901268', 'KIT SILLA TEE 250X160', 223937.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(403, '2902731', 'KIT SILLA TEE 315X110', 336719.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(404, '2902732', 'KIT SILLA TEE 315X160', 336719.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(405, '2901269', 'KIT SILLA YEE NOVAFORT 160X110', 171378.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(406, '2901270', 'KIT SILLA YEE NOVAFORT 200X110', 198092.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(407, '2901271', 'KIT SILLA YEE NOVAFORT 200X160', 198092.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(408, '2901272', 'KIT SILLA YEE NOVAFORT 250X110', 223937.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(409, '2901273', 'KIT SILLA YEE NOVAFORT 250X160', 223937.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(410, '2902733', 'KIT SILLA YEE NOVAFORT 315X110', 336719.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(411, '2907849', 'KIT SILLA YEE NOVAFORT 315X160', 336719.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(412, '2901794', 'KIT SILLA YEE NOVAFORT S4 PLUS 200X160', 198092.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(413, '2901785', 'KIT SILLA YEE NOVAFORT S4 PLUS 250X160', 223937.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(414, '2907850', 'KIT SILLA YEE NOVAFORT S4 PLUS 315X160', 336719.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(415, '2902736', 'LIMPIADOR CPVC 112 Grms 1/32', 11496.00, 11, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(416, '2902735', 'LIMPIADOR CPVC 28 Grms 1/128', 4352.00, 11, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(417, '2902739', 'LIMPIADOR CPVC 300 Grms 1/8', 35822.00, 11, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(418, '2902738', 'LIMPIADOR CPVC 56 Grms 1/64', 7376.00, 11, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(419, '2902737', 'LIMPIADOR PVC 760 GRMS 1/4', 65913.00, 11, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(420, '2902743', 'Lubricante 500 g', 30982.00, 25, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(421, '2901792', 'NIPLE ROSCADO PVC 1/2PULG', 989.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(422, '2903087', 'REDUCCION CONCENTRICA - NOVAFORT 160X110', 125150.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(423, '2903086', 'REDUCCION CONCENTRICA - NOVAFORT 160X4PULG', 113867.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(424, '2903092', 'REDUCCION EXCENTRICA NOVAFORT 160X110', 74462.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(425, '2902999', 'REDUCCION EXCENTRICA NOVAFORT 200 X160', 119636.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(426, '2903549', 'REDUCCION UNION MECANICA 2PULG X 1 1/2PULG', 202408.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(427, '2903547', 'REDUCCION UNION MECANICA 3PULG x 2PULG', 277371.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(428, '2908618', 'REDUCCION UNION MECANICA 4PULG x 3PULG', 102895.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(429, '2908619', 'REDUCCION UNION MECANICA 6PULG X 4PULG', 221866.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(430, '2903471', 'REDUCCION UNION MECANICA 8PULG X 6PULG', 1573370.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(431, '2903545', 'REDUCCION UNION MECANICA SNAP 4PULG x 2PULG', 321804.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(432, '2903288', 'SERRUCHO DE PUNTA (NOVAFORT)', 201068.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(433, '2901281', 'SIFON 135° PVC-S 3PULG', 14804.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(434, '2901283', 'SIFON 135° PVC-S 4PULG', 32358.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(435, '2901291', 'SIFON 180° PVC-S 2PULG CxC (Sin Codo)', 7338.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(436, '2901286', 'SIFON CON TAPÓN PVC-S 1-1/2PULG', 7768.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(437, '2901292', 'SIFON CON TAPÓN PVC-S 2PULG', 11940.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(438, '2910955', 'SIFON DESMONTABLE TIPO P 1.1/2PULG (lavamanos/lavaplatos)', 16075.00, 10, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(439, '2910495', 'SIFON FLEXIBLE PSA 1 1/4PULG', 14544.00, 3, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(440, '2901295', 'SILLA TEE NOVAFORT 160x110', 103304.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(441, '2903085', 'SILLA TEE NOVAFORT 160x160', 103304.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(442, '2901297', 'SILLA TEE NOVAFORT 200x110', 126529.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(443, '2901299', 'SILLA TEE NOVAFORT 200x160', 126529.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(444, '2902985', 'SILLA TEE NOVAFORT 24X160', 572894.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(445, '2902986', 'SILLA TEE NOVAFORT 24X200', 627280.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(446, '2901301', 'SILLA TEE NOVAFORT 250x110', 146719.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(447, '2901303', 'SILLA TEE NOVAFORT 250x160', 146719.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(448, '2903093', 'SILLA TEE NOVAFORT 250X200', 187302.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(449, '2905671', 'SILLA TEE NOVAFORT 27X315', 1000380.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(450, '2902766', 'SILLA TEE NOVAFORT 315x110', 227467.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25');
INSERT INTO `productos` (`id`, `codigo`, `descripcion`, `precio`, `grupo_id`, `created_at`, `updated_at`) VALUES
(451, '2902768', 'SILLA TEE NOVAFORT 315x160', 227467.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(452, '2903094', 'SILLA TEE NOVAFORT 315X200', 307503.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(453, '2903095', 'SILLA TEE NOVAFORT 315X250', 307503.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(454, '2902984', 'SILLA TEE NOVAFORT 355x110', 307503.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(455, '2902981', 'SILLA TEE NOVAFORT 355x160', 342457.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(456, '2903111', 'SILLA TEE NOVAFORT 355X200', 342456.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(457, '2902770', 'SILLA TEE NOVAFORT 400 X 110', 368789.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(458, '2902772', 'SILLA TEE NOVAFORT 400 X 160', 375490.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(459, '2903096', 'SILLA TEE NOVAFORT 400X200', 375494.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(460, '2903097', 'SILLA TEE NOVAFORT 400X250', 375494.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(461, '2902774', 'SILLA TEE NOVAFORT 450 X 160', 401636.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(462, '2902775', 'SILLA TEE NOVAFORT 500 X 160', 612702.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(463, '2901309', 'SILLA YEE NOVAFORT 160X110', 103304.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(464, '2901311', 'SILLA YEE NOVAFORT 200 X110', 126529.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(465, '2901313', 'SILLA YEE NOVAFORT 200 X160', 126529.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(466, '2903112', 'SILLA YEE NOVAFORT 24X160', 502899.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(467, '2902977', 'SILLA YEE NOVAFORT 24X200', 622404.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(468, '2903113', 'SILLA YEE NOVAFORT 250 x 200', 217572.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(469, '2901315', 'SILLA YEE NOVAFORT 250X110', 146719.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(470, '2901317', 'SILLA YEE NOVAFORT 250X160', 146719.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(471, '2903107', 'SILLA YEE NOVAFORT 27X 160', 624243.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(472, '2902987', 'SILLA YEE NOVAFORT 27X200', 626072.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(473, '2903059', 'SILLA YEE NOVAFORT 27X250', 1199400.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(474, '2906134', 'SILLA YEE NOVAFORT 30X160', 651794.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(475, '2906474', 'SILLA YEE NOVAFORT 30X200', 993469.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(476, '2902777', 'SILLA YEE NOVAFORT 315X110', 227467.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(477, '2907844', 'SILLA YEE NOVAFORT 315X160', 227467.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(478, '2902978', 'SILLA YEE NOVAFORT 315x200', 307503.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(479, '2906159', 'SILLA YEE NOVAFORT 33X160', 679263.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(480, '2902983', 'SILLA YEE NOVAFORT 355X110', 342457.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(481, '2902982', 'SILLA YEE NOVAFORT 355X160', 342457.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(482, '2902979', 'SILLA YEE NOVAFORT 355X200', 342456.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(483, '2906135', 'SILLA YEE NOVAFORT 36X160', 733596.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(484, '2902781', 'SILLA YEE NOVAFORT 400 X 110', 375494.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(485, '2907845', 'SILLA YEE NOVAFORT 400 X 160', 375494.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(486, '2902785', 'SILLA YEE NOVAFORT 400X200', 375494.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(487, '2902776', 'SILLA YEE NOVAFORT 400X250', 375494.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(488, '2902787', 'SILLA YEE NOVAFORT 450 X 160', 401636.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(489, '2902980', 'SILLA YEE NOVAFORT 450X200', 401632.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(490, '2902789', 'SILLA YEE NOVAFORT 500 X 160', 612702.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(491, '2902990', 'SILLA YEE NOVAFORT 500 X 200', 612707.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(492, '2909942', 'SOLDADURA LIQUIDA CPVC 1/128 Gal.', 8098.00, 11, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(493, '2909923', 'SOLDADURA LIQUIDA CPVC 1/16 Gal.', 49310.00, 11, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(494, '2909943', 'SOLDADURA LIQUIDA CPVC 1/32 Gal.', 30260.00, 11, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(495, '2909921', 'SOLDADURA LIQUIDA CPVC 1/4 Gal.', 146568.00, 28, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(496, '2909944', 'SOLDADURA LIQUIDA CPVC 1/64 Gal.', 17888.00, 11, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(497, '2909922', 'SOLDADURA LIQUIDA CPVC 1/8 Gal.', 81702.00, 11, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(498, '2909997', 'SOLDADURA LIQUIDA PVC 1/128 Gal.', 7762.00, 11, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(499, '2910002', 'SOLDADURA LIQUIDA PVC 1/16 Gal.', 43664.00, 11, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(500, '2909998', 'SOLDADURA LIQUIDA PVC 1/32 Gal.', 26032.00, 11, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(501, '2910000', 'SOLDADURA LIQUIDA PVC 1/4 Gal.', 136698.00, 11, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(502, '2909999', 'SOLDADURA LIQUIDA PVC 1/64 Gal.', 15204.00, 11, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(503, '2910001', 'SOLDADURA LIQUIDA PVC 1/8 Gal.', 70636.00, 11, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(504, '2903810', 'TAPON DE PRUEBA PVC-S 10PULG', 398226.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(505, '2901437', 'TAPON DE PRUEBA PVC-S 1-1/2PULG', 1254.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(506, '2901439', 'TAPON DE PRUEBA PVC-S 2PULG', 1641.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(507, '2901441', 'TAPON DE PRUEBA PVC-S 3PULG', 2083.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(508, '2901443', 'TAPON DE PRUEBA PVC-S 4PULG', 4136.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(509, '2905559', 'TAPON DE PRUEBA PVC-S 6PULG', 18607.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(510, '2903809', 'TAPON DE PRUEBA PVC-S 8PULG', 261480.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(511, '2901357', 'TAPON ROSCADO PVC-P 1PULG', 2806.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(512, '2901388', 'TAPON ROSCADO PVC-P 1/2PULG', 672.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(513, '2901367', 'TAPON ROSCADO PVC-P 1-1/2PULG', 6572.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(514, '2901375', 'TAPON ROSCADO PVC-P 1-1/4PULG', 4966.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(515, '2901398', 'TAPON ROSCADO PVC-P 2PULG', 10746.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(516, '2901405', 'TAPON ROSCADO PVC-P 2.1/2PULG', 24764.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(517, '2901414', 'TAPON ROSCADO PVC-P 3PULG', 39425.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(518, '2901425', 'TAPON ROSCADO PVC-P 3/4PULG', 1972.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(519, '2901434', 'TAPON ROSCADO PVC-P 4PULG', 72790.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(520, '2910385', 'TAPON SOLDADO CPVC  1/2PULG', 2009.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(521, '2910386', 'TAPON SOLDADO CPVC  3/4PULG', 2009.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(522, '2910387', 'TAPON SOLDADO CPVC 1PULG', 7266.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(523, '2910389', 'TAPON SOLDADO CPVC  1.1/2PULG', 20707.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(524, '2910388', 'TAPON SOLDADO CPVC 1.1/4PULG', 13388.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(525, '2903759', 'TAPON SOLDADO CPVC 2PULG', 50278.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(526, '2907473', 'TAPON SOLDADO CPVC SCH 80  1PULG', 62970.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(527, '2907474', 'TAPON SOLDADO CPVC SCH 80  1.1/4PULG', 68929.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(528, '2907471', 'TAPON SOLDADO CPVC SCH 80   1/2PULG', 25054.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(529, '2907475', 'TAPON SOLDADO CPVC SCH 80  1-1/2PULG', 69233.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(530, '2907476', 'TAPON SOLDADO CPVC SCH 80  2PULG', 72670.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(531, '2906874', 'TAPON SOLDADO CPVC SCH 80  2-1/2PULG', 178054.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(532, '2906875', 'TAPON SOLDADO CPVC SCH 80 3PULG', 194684.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(533, '2907472', 'TAPON SOLDADO CPVC SCH 80   3/4PULG', 35579.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(534, '2906876', 'TAPON SOLDADO CPVC SCH 80  4PULG', 203730.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(535, '2907477', 'TAPON SOLDADO CPVC SCH 80  6PULG', 672230.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(536, '2907561', 'TAPON SOLDADO PVC SCH 80  1.1/4PULG', 24043.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(537, '2907562', 'TAPON SOLDADO PVC SCH 80  1-1/2PULG', 24588.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(538, '2907563', 'TAPON SOLDADO PVC SCH 80  2PULG', 47494.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(539, '2907564', 'TAPON SOLDADO PVC SCH 80  2.1/2PULG', 96924.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(540, '2907565', 'TAPON SOLDADO PVC SCH 80  3PULG', 99590.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(541, '2907566', 'TAPON SOLDADO PVC SCH 80  4PULG', 121426.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(542, '2909792', 'TAPON SOLDADO PVC SCH 80  6PULG', 500846.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(543, '2907567', 'TAPON SOLDADO PVC SCH 80  8PULG', 584497.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(544, '2901390', 'TAPON SOLDADO PVC-P  1/2PULG', 492.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(545, '2901427', 'TAPON SOLDADO PVC-P  3/4PULG', 986.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(546, '2901359', 'TAPON SOLDADO PVC-P 1PULG', 1650.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(547, '2901369', 'TAPON SOLDADO PVC-P 1-1/2PULG', 5175.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(548, '2901377', 'TAPON SOLDADO PVC-P 1-1/4PULG', 3975.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(549, '2901400', 'TAPON SOLDADO PVC-P 2PULG', 8224.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(550, '2901406', 'TAPON SOLDADO PVC-P 2.1/2PULG ', 19360.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(551, '2901415', 'TAPON SOLDADO PVC-P 3PULG', 31467.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(552, '2901435', 'TAPON SOLDADO PVC-P 4PULG', 57173.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(553, '2910396', 'TEE CPVC  1/2PULG', 2803.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(554, '2910397', 'TEE CPVC  3/4PULG', 4440.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(555, '2910398', 'TEE CPVC 1PULG', 25475.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(556, '2910400', 'TEE CPVC 1.1/2PULG', 60143.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(557, '2910399', 'TEE CPVC 1-1/4PULG', 44787.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(558, '2903765', 'TEE CPVC 2PULG', 109423.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(559, '2907480', 'TEE CPVC SCH 80 1PULG', 41364.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(560, '2907482', 'TEE CPVC SCH 80 1.1/2PULG', 107735.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(561, '2907481', 'TEE CPVC SCH 80 1.1/4PULG', 88833.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(562, '2907478', 'TEE CPVC SCH 80  1/2PULG', 33163.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(563, '2907483', 'TEE CPVC SCH 80 2PULG', 119993.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(564, '2906877', 'TEE CPVC SCH 80 2-1/2PULG', 251434.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(565, '2906878', 'TEE CPVC SCH 80 3PULG', 327917.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(566, '2907479', 'TEE CPVC SCH 80  3/4PULG', 33773.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(567, '2906879', 'TEE CPVC SCH 80 4PULG', 404399.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(568, '2907484', 'TEE CPVC SCH 80 6PULG', 784827.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(569, '2903109', 'TEE DOBLE PREFABRICADO NOVAFORT 315 X 160', 855906.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(570, '2901469', 'TEE DOBLE PVC-S 1-1/2PULG', 14249.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(571, '2901471', 'TEE DOBLE PVC-S 2PULG', 17003.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(572, '2901473', 'TEE DOBLE PVC-S 3PULG', 38577.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(573, '2901476', 'TEE DOBLE PVC-S 4PULG', 61342.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(574, '2901460', 'TEE DOBLE REDUCIDA PVC-S 2PULG x 1-1/2PULG', 12957.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(575, '2901462', 'TEE DOBLE REDUCIDA PVC-S 3PULG x 2PULG', 26351.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(576, '2901464', 'TEE DOBLE REDUCIDA PVC-S 4PULG x 2PULG', 55029.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(577, '2901466', 'TEE DOBLE REDUCIDA PVC-S 4PULG x 3PULG', 55029.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(578, '2911400', 'TEE H.D. C900 4PULG', 1365250.00, 27, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(579, '2901445', 'TEE NOVAFORT 160X160', 86248.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(580, '2901526', 'TEE NOVAFORT 200X160', 219398.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(581, '2902826', 'TEE NOVAFORT 250X160', 254706.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(582, '2902825', 'TEE PREFABRICADO NOVAFORT 200MM', 230976.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(583, '2903108', 'TEE PREFABRICADO NOVAFORT 250 X 200', 583654.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(584, '2902820', 'TEE PREFABRICADO NOVAFORT 250MM', 719823.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(585, '2903110', 'TEE PREFABRICADO NOVAFORT 315 X 200', 963031.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(586, '2902821', 'TEE PREFABRICADO NOVAFORT 315MM', 875484.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(587, '2903062', 'TEE PREFABRICADO NOVAFORT 355MM', 1269450.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(588, '2902822', 'TEE PREFABRICADO NOVAFORT 400MM', 1814540.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(589, '2902823', 'TEE PREFABRICADO NOVAFORT 450MM', 2414600.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(590, '2906089', 'TEE PREFABRICADO NOVAFORT 500MM', 2719910.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(591, '2909793', 'TEE PVC SCH 80  6PULG', 424108.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(592, '2907568', 'TEE PVC SCH 80  1.1/4PULG', 63097.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(593, '2907569', 'TEE PVC SCH 80  1-1/2PULG', 66260.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(594, '2907570', 'TEE PVC SCH 80  2PULG', 77876.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(595, '2907571', 'TEE PVC SCH 80  2.1/2PULG', 79396.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(596, '2907572', 'TEE PVC SCH 80  3PULG', 101844.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(597, '2907573', 'TEE PVC SCH 80  4PULG', 118003.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(598, '2907574', 'TEE PVC SCH 80  8PULG', 910499.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(599, '2908623', 'TEE PVC UP RADIO CORTO 2PULG', 113570.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(600, '2903538', 'TEE PVC UP RADIO CORTO 2.1/2PULG', 359625.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(601, '2909098', 'TEE PVC UP RADIO CORTO 3PULG', 153118.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(602, '2909099', 'TEE PVC UP RADIO CORTO 4PULG', 239018.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(603, '2908624', 'TEE PVC UP RADIO CORTO 6PULG', 660784.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(604, '2901481', 'TEE PVC-P 1PULG', 3754.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(605, '2901498', 'TEE PVC-P 1/2PULG', 1136.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(606, '2901808', 'TEE PVC-P 1/2PULG - ROSCADO', 2576.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(607, '2901486', 'TEE PVC-P 1-1/2PULG', 12727.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(608, '2901490', 'TEE PVC-P 1-1/4PULG', 9695.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(609, '2901503', 'TEE PVC-P 2PULG', 20267.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(610, '2901508', 'TEE PVC-P 2.1/2PULG', 48071.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(611, '2901513', 'TEE PVC-P 3PULG', 76469.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(612, '2901519', 'TEE PVC-P 3/4PULG', 1921.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(613, '2901524', 'TEE PVC-P 4PULG', 166872.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(614, '2903800', 'TEE PVC-S 10PULG', 1024090.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(615, '2901558', 'TEE PVC-S 1-1/2PULG', 7893.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(616, '2901561', 'TEE PVC-S 2PULG', 9045.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(617, '2901563', 'TEE PVC-S 3PULG', 11350.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(618, '2901567', 'TEE PVC-S 4PULG', 23654.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(619, '2911125', 'TEE PVC-S 6PULG', 214801.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(620, '2903799', 'TEE PVC-S 8PULG', 575005.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(621, '2901530', 'TEE REDUCIDA PVC-P 1PULG x 1/2PULG', 5585.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(622, '2901532', 'TEE REDUCIDA PVC-P 1PULG x 3/4PULG', 5585.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(623, '2901538', 'TEE REDUCIDA PVC-P 3/4PULG x 1/2PULG', 2831.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(624, '2901543', 'TEE REDUCIDA PVC-S 2PULG x 1-1/2PULG', 8678.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(625, '2901545', 'TEE REDUCIDA PVC-S 3PULG x 2PULG', 21793.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(626, '2901548', 'TEE REDUCIDA PVC-S 4PULG x 2PULG', 38014.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(627, '2901550', 'TEE REDUCIDA PVC-S 4PULG x 3PULG', 38014.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(628, '2909348', 'TEE REDUCIDA PVC-S 6PULG x 4PULG', 214801.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(629, '2911382', 'TRANSICION BRIDAXCAMPANA H.D. C900 4PULG', 1007420.00, 27, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(630, '2911383', 'TRANSICION BRIDAXCAMPANA H.D. C900 6PULG', 1224910.00, 27, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(631, '2911384', 'TRANSICION BRIDAXCAMPANA H.D. C900 8PULG', 1850550.00, 27, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(632, '2900090', 'TUBERIA ALCANTARILLADO NOVAFORT 110 MM S8', 24695.00, 15, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(633, '2900100', 'TUBERIA ALCANTARILLADO NOVAFORT 160 MM S4', 46934.00, 16, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(634, '2900102', 'TUBERIA ALCANTARILLADO NOVAFORT 200 MM S4', 67093.00, 16, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(635, '2906313', 'TUBERIA ALCANTARILLADO NOVAFORT 24PULG', 607171.00, 17, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(636, '2900096', 'TUBERIA ALCANTARILLADO NOVAFORT 250 MM S4', 97992.00, 16, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(637, '2900511', 'TUBERIA ALCANTARILLADO NOVAFORT 27PULG', 714030.00, 17, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(638, '2906378', 'TUBERIA ALCANTARILLADO NOVAFORT 30PULG', 891040.00, 17, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(639, '2904921', 'TUBERIA ALCANTARILLADO NOVAFORT 315 MM S4', 141005.00, 16, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(640, '2904604', 'TUBERIA ALCANTARILLADO NOVAFORT 33PULG', 1228720.00, 17, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(641, '2902494', 'TUBERIA ALCANTARILLADO NOVAFORT 355 MM S4', 211315.00, 16, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(642, '2904605', 'TUBERIA ALCANTARILLADO NOVAFORT 36PULG', 1653880.00, 17, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(643, '2905865', 'TUBERIA ALCANTARILLADO NOVAFORT 39PULG', 2212820.00, 17, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(644, '2909762', 'TUBERIA ALCANTARILLADO NOVAFORT 400 MM S4', 258462.00, 16, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(645, '2905866', 'TUBERIA ALCANTARILLADO NOVAFORT 42PULG', 2597830.00, 17, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(646, '2910888', 'TUBERIA ALCANTARILLADO NOVAFORT 45PULG', 2802180.00, 17, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(647, '2910889', 'TUBERIA ALCANTARILLADO NOVAFORT 48PULG', 3416060.00, 17, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(648, '2900182', 'TUBERIA CONDUFLEX VERDE 1 1/4PULG', 12449.00, 18, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(649, '2900181', 'TUBERIA CONDUFLEX VERDE 1PULG', 8151.00, 18, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(650, '2900119', 'TUBERIA CONDUFLEX VERDE 1/2PULG', 2797.00, 18, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(651, '2900121', 'TUBERIA CONDUFLEX VERDE 3/4PULG', 4860.00, 18, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(652, '2900125', 'TUBERIA CONDUIT 1', 5065.00, 19, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(653, '2900133', 'TUBERIA CONDUIT 1/2PULG', 2790.00, 19, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(654, '2900128', 'TUBERIA CONDUIT 1-1/2PULG', 9976.00, 19, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(655, '2900130', 'TUBERIA CONDUIT 1-1/4PULG', 7825.00, 19, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(656, '2900135', 'TUBERIA CONDUIT 2PULG', 15344.00, 19, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(657, '2900138', 'TUBERIA CONDUIT 3/4PULG', 3654.00, 19, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(658, '2900144', 'TUBERIA CORRUGADA CON FILTRO 2 1/2PULG (ROLLO DE 150M)', 18369.00, 20, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(659, '2900147', 'TUBERIA CORRUGADA CON FILTRO 4PULG (ROLLO DE 100M)', 31935.00, 20, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(660, '2900151', 'TUBERIA CORRUGADA CON FILTRO 6PULG (ROLLO DE 50M)', 73136.00, 20, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(661, '2900153', 'TUBERIA CORRUGADA CON FILTRO 8PULG (ROLLO DE 35M)', 99316.00, 20, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(662, '2900143', 'TUBERIA CORRUGADA SIN FILTRO 65 mm (ROLLO DE 150M)', 13412.00, 20, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(663, '2900146', 'TUBERIA CORRUGADA SIN FILTRO 100 mm (Rollo de 100m)', 23694.00, 20, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(664, '2900150', 'TUBERIA CORRUGADA SIN FILTRO 160 mm (ROLLO DE 50M)', 52789.00, 20, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(665, '2900152', 'TUBERIA CORRUGADA SIN FILTRO 200 mm (ROLLO DE 35M)', 73860.00, 20, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(666, '2910217', 'TUBERIA CPVC  1/2PULG', 9457.00, 12, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(667, '2910219', 'TUBERIA CPVC  3/4PULG', 15542.00, 12, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(668, '2910221', 'TUBERIA CPVC 1PULG', 26294.00, 12, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(669, '2910223', 'TUBERIA CPVC 1.1/2PULG', 77607.00, 12, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(670, '2910222', 'TUBERIA CPVC 1.1/4PULG', 53846.00, 12, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(671, '2910224', 'TUBERIA CPVC 2PULG', 132097.00, 12, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(672, '2907415', 'TUBERIA CPVC SCH 80   1PULG', 68032.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(673, '2907416', 'TUBERIA CPVC SCH 80  1.1/4PULG', 94677.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(674, '2907413', 'TUBERIA CPVC SCH 80    1/2PULG', 50143.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(675, '2907417', 'TUBERIA CPVC SCH 80  1-1/2PULG', 106154.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(676, '2907418', 'TUBERIA CPVC SCH 80  2PULG', 162458.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(677, '2906859', 'TUBERIA CPVC SCH 80  2-1/2PULG', 209451.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(678, '2906860', 'TUBERIA CPVC SCH 80  3PULG', 264810.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(679, '2907414', 'TUBERIA CPVC SCH 80    3/4PULG', 55156.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(680, '2906861', 'TUBERIA CPVC SCH 80  4PULG', 448824.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(681, '2907499', 'TUBERIA CPVC SCH 80  6PULG', 866544.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(682, '2910537', 'TUBERIA PVC C900 DR 14 4PULG', 143116.00, 21, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(683, '2910540', 'TUBERIA PVC C900 DR 14 6PULG', 297007.00, 21, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(684, '2910536', 'TUBERIA PVC C900 DR 18 4PULG', 114191.00, 21, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(685, '2910539', 'TUBERIA PVC C900 DR 18 6PULG', 235719.00, 21, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(686, '2900338', 'TUBERIA PVC-L 1-1/2PULG (Ventilación)', 8084.00, 22, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(687, '2900341', 'TUBERIA PVC-L 2PULG (Ventilación)', 11688.00, 22, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(688, '2900344', 'TUBERIA PVC-L 3PULG (Ventilación)', 15600.00, 22, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(689, '2900347', 'TUBERIA PVC-L 4PULG (VENTILACIÓN).', 26899.00, 22, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(690, '2911118', 'TUBERIA PVC SCH 80 1PULG', 19528.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(691, '2907501', 'TUBERIA PVC SCH 80  1.1/2PULG', 31086.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(692, '2907500', 'TUBERIA PVC SCH 80  1.1/4PULG', 28528.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(693, '2907502', 'TUBERIA PVC SCH 80  2PULG', 43401.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(694, '2907503', 'TUBERIA PVC SCH 80  2.1/2PULG', 74339.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(695, '2907504', 'TUBERIA PVC SCH 80  3PULG', 92305.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(696, '2907505', 'TUBERIA PVC SCH 80  4PULG', 124997.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(697, '2907506', 'TUBERIA PVC SCH 80  6PULG', 237624.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(698, '2907507', 'TUBERIA PVC SCH 80  8PULG', 358197.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(699, '2900213', 'TUBERIA PVC-P 1PULG RDE 13.5 (315 psi) TRABAJO PESADO', 9350.00, 13, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(700, '2900220', 'TUBERIA PVC-P 1PULG RDE 21 (200 psi)', 6457.00, 13, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(701, '2902449', 'TUBERIA PVC-P 1/2PULG RDE 13.5 (315 psi)', 3713.00, 13, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(702, '2900266', 'TUBERIA PVC-P 1/2PULG RDE 9  (500 psi) TRABAJO PESADO', 5204.00, 13, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(703, '2902450', 'TUBERIA PVC-P 1-1/2PULG RDE 21 (200 psi)', 15187.00, 13, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(704, '2900225', 'TUBERIA PVC-P 1-1/4PULG RDE 21 (200 psi) ', 11631.00, 13, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(705, '2902453', 'TUBERIA PVC-P 2PULG RDE 21 (200 psi) ', 23289.00, 13, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(706, '2900246', 'TUBERIA PVC-P 2PULG RDE 26 (160 psi)', 19228.00, 13, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(707, '2900230', 'TUBERIA PVC-P 2.1/2PULG RDE 21 (200 psi)', 44607.00, 13, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(708, '2900233', 'TUBERIA PVC-P 3PULG RDE 21 (200 psi) ', 52394.00, 13, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(709, '2900251', 'TUBERIA PVC-P 3PULG RDE 26 (160 psi)', 41080.00, 13, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(710, '2900210', 'TUBERIA PVC-P 3/4PULG RDE 11 (400psi)', 6929.00, 13, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(711, '2900237', 'TUBERIA PVC-P 3/4PULG RDE 21 (200psi) ', 4601.00, 13, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(712, '2900240', 'TUBERIA PVC-P 4PULG RDE 21 (200 psi)', 89165.00, 13, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(713, '2904616', 'TUBERIA PVC-P 6PULG RDE 21 (200 psi)', 190083.00, 13, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(714, '2900421', 'TUBERIA PVC-S 10PULG', 206029.00, 14, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(715, '2900319', 'TUBERIA PVC-S 1-1/2PULG', 13416.00, 14, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(716, '2902515', 'TUBERIA PVC-S 2PULG', 16633.00, 14, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(717, '2902517', 'TUBERIA PVC-S 3PULG', 24844.00, 14, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(718, '2900331', 'TUBERIA PVC-S 4PULG', 34941.00, 14, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(719, '2900336', 'TUBERIA PVC-S 6PULG', 73321.00, 14, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(720, '2900420', 'TUBERIA PVC-S 8PULG', 131904.00, 14, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(721, '2900323', 'TUBERIA PVC-S NOVATEC 2PULG', 16633.00, 23, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(722, '2900326', 'TUBERIA PVC-S NOVATEC 3PULG', 24844.00, 23, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(723, '2900330', 'TUBERIA PVC-S NOVATEC 4PULG', 34941.00, 23, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(724, '2900335', 'TUBERIA PVC-S NOVATEC 6PULG', 73321.00, 23, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(725, '2902411', 'TUBERIA UNION MECANICA SNAP 10PULG RDE 21', 323625.00, 24, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(726, '2902421', 'TUBERIA UNION MECANICA SNAP 12PULG RDE 21', 452814.00, 24, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(727, '2902431', 'TUBERIA UNION MECANICA SNAP 14PULG RDE 21', 561544.00, 24, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(728, '2900010', 'TUBERIA UNION MECANICA SNAP 2PULG RDE 21', 17866.00, 24, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(729, '2900022', 'TUBERIA UNION MECANICA SNAP 3PULG RDE 21', 44724.00, 24, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(730, '2900033', 'TUBERIA UNION MECANICA SNAP 4PULG RDE 21', 59073.00, 24, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(731, '2900043', 'TUBERIA UNION MECANICA SNAP 6PULG RDE 21', 121366.00, 24, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(732, '2900054', 'TUBERIA UNION MECANICA SNAP 8PULG RDE 21', 205580.00, 24, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(733, '2910408', 'UNION CPVC  1/2PULG', 1535.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(734, '2910409', 'UNION CPVC  3/4PULG', 2265.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(735, '2910410', 'UNION CPVC 1PULG', 9995.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(736, '2910412', 'UNION CPVC 1-1/2PULG', 34975.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(737, '2910411', 'UNION CPVC 1-1/4PULG', 31621.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(738, '2903768', 'UNION CPVC 2PULG', 38328.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(739, '2907487', 'UNION CPVC SCH 80  1PULG', 28081.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(740, '2907488', 'UNION CPVC SCH 80  1.1/4PULG', 39484.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(741, '2907485', 'UNION CPVC SCH 80   1/2PULG', 14895.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(742, '2907489', 'UNION CPVC SCH 80  1-1/2PULG', 53047.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(743, '2906880', 'UNION CPVC SCH 80 2 1/2PULG', 128496.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(744, '2907490', 'UNION CPVC SCH 80  2PULG', 90772.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(745, '2906881', 'UNION CPVC SCH 80 3PULG', 207158.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(746, '2907486', 'UNION CPVC SCH 80   3/4PULG', 20916.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(747, '2906882', 'UNION CPVC SCH 80  4PULG', 335326.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(748, '2907491', 'UNION CPVC SCH 80  6PULG', 463495.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(749, '2902885', 'UNION MECANICA RAPIDA O PASANTE SNAP 10PULG', 681242.00, 25, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(750, '2902889', 'UNION MECANICA RAPIDA O PASANTE SNAP 12PULG', 1055780.00, 25, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(751, '2902892', 'UNION MECANICA RAPIDA O PASANTE SNAP 2PULG', 37052.00, 25, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(752, '2902898', 'UNION MECANICA RAPIDA O PASANTE SNAP 3PULG', 61002.00, 25, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(753, '2902901', 'UNION MECANICA RAPIDA O PASANTE SNAP 4PULG', 88887.00, 25, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(754, '2902905', 'UNION MECANICA RAPIDA O PASANTE SNAP 6PULG', 207538.00, 25, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(755, '2902909', 'UNION MECANICA RAPIDA O PASANTE SNAP 8PULG', 381288.00, 25, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(756, '2902941', 'UNION MECANICA REPARACION SNAP 10PULG', 763419.00, 25, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(757, '2902943', 'UNION MECANICA REPARACION SNAP 12PULG', 1397510.00, 25, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(758, '2902945', 'UNION MECANICA REPARACION SNAP 2PULG', 42895.00, 25, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(759, '2902950', 'UNION MECANICA REPARACION SNAP 3PULG', 68325.00, 25, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(760, '2902953', 'UNION MECANICA REPARACION SNAP 4PULG', 104976.00, 25, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(761, '2902956', 'UNION MECANICA REPARACION SNAP 6PULG', 243090.00, 25, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(762, '2902959', 'UNION MECANICA REPARACION SNAP 8PULG', 446968.00, 25, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(763, '2901576', 'UNION NOVAFORT 110 MM', 17247.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(764, '2901577', 'UNION NOVAFORT 160 MM', 41791.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(765, '2907997', 'UNION NOVAFORT 200 MM', 70149.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(766, '2906753', 'UNION NOVAFORT 24PULG', 719654.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(767, '2902914', 'UNION NOVAFORT 250 MM', 199253.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(768, '2900517', 'UNION NOVAFORT 27PULG', 809609.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(769, '2900518', 'UNION NOVAFORT 30PULG', 1011020.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(770, '2902917', 'UNION NOVAFORT 315 MM', 340926.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(771, '2906333', 'UNION NOVAFORT 33PULG', 3054020.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(772, '2910937', 'UNION NOVAFORT 355MM', 452544.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(773, '2907224', 'UNION NOVAFORT 36PULG', 4409580.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(774, '2907225', 'UNION NOVAFORT 39PULG', 5720890.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(775, '2902921', 'UNION NOVAFORT 400 MM', 519794.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(776, '2907299', 'UNION NOVAFORT 42PULG', 6200220.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(777, '2911972', 'UNION NOVAFORT 45PULG', 10624400.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(778, '2902924', 'UNION NOVAFORT 450 MM', 546758.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(779, '2911973', 'UNION NOVAFORT 48PULG', 12049100.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(780, '2902926', 'UNION NOVAFORT 500 MM', 614275.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(781, '2911121', 'UNION PVC SCH 80 1PULG', 15068.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(782, '2907576', 'UNION PVC SCH 80 1.1/2PULG', 25351.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(783, '2907575', 'UNION PVC SCH 80 1.1/4PULG', 23389.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(784, '2907577', 'UNION PVC SCH 80 2PULG', 29097.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(785, '2907578', 'UNION PVC SCH 80 2.1/2PULG', 62821.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(786, '2907579', 'UNION PVC SCH 80 3PULG', 72129.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(787, '2907580', 'UNION PVC SCH 80 4PULG', 92703.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(788, '2909794', 'UNION PVC SCH 80 6PULG', 194428.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(789, '2907581', 'UNION PVC SCH 80 8PULG', 276722.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(790, '2902904', 'UNION PVC UP 4PULG', 93323.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(791, '2901635', 'UNION PVC-P  1/2PULG', 551.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(792, '2901661', 'UNION PVC-P  3/4PULG', 869.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(793, '2901616', 'UNION PVC-P 1PULG', 1424.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(794, '2901621', 'UNION PVC-P 1-1/2PULG', 3561.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(795, '2901626', 'UNION PVC-P 1-1/4PULG', 2607.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(796, '2901642', 'UNION PVC-P 2PULG', 5835.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(797, '2901647', 'UNION PVC-P 2.1/2PULG', 23086.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(798, '2901654', 'UNION PVC-P 3PULG', 28599.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(799, '2901667', 'UNION PVC-P 4PULG', 62127.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(800, '2903817', 'UNION PVC-S 10PULG', 321381.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(801, '2901690', 'UNION PVC-S 1-1/2PULG', 3123.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(802, '2901693', 'UNION PVC-S 2PULG', 3576.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(803, '2901696', 'UNION PVC-S 3PULG', 5162.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(804, '2901700', 'UNION PVC-S 4PULG', 10405.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(805, '2901703', 'UNION PVC-S 6PULG', 45198.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(806, '2909778', 'UNION PVC-S 8PULG', 130084.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(807, '2903397', 'UNIÓN REPARACIÓN DESLIZANTE PVC-P 1PULG', 19069.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(808, '2903398', 'UNIÓN REPARACIÓN DESLIZANTE PVC-P 1.1/2PULG', 72288.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(809, '2903399', 'UNIÓN REPARACIÓN DESLIZANTE PVC-P 1/2PULG', 8293.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(810, '2903400', 'UNIÓN REPARACIÓN DESLIZANTE PVC-P 2PULG', 80427.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(811, '2903401', 'UNIÓN REPARACIÓN DESLIZANTE PVC-P 3/4PULG', 9149.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(812, '2910421', 'UNIVERSAL CPVC 1/2PULG', 14635.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(813, '2910422', 'UNIVERSAL CPVC 3/4PULG', 16304.00, 4, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(814, '2907494', 'UNIVERSAL CPVC SCH 80  1PULG', 82630.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(815, '2907495', 'UNIVERSAL CPVC SCH 80  1.1/4PULG', 167296.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(816, '2907492', 'UNIVERSAL CPVC SCH 80   1/2PULG', 66603.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(817, '2907496', 'UNIVERSAL CPVC SCH 80  1-1/2PULG', 170482.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(818, '2907497', 'UNIVERSAL CPVC SCH 80  2PULG', 376794.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(819, '2907493', 'UNIVERSAL CPVC SCH 80 3/4PULG', 69018.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(820, '2907583', 'UNIVERSAL PVC SCH 80 1 1/2PULG', 86997.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(821, '2907582', 'UNIVERSAL PVC SCH 80 1 1/4PULG', 76886.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(822, '2907584', 'UNIVERSAL PVC SCH 80 2PULG', 118029.00, 1, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(823, '2901672', 'UNIVERSAL PVC-P 1PULG', 12841.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(824, '2901679', 'UNIVERSAL PVC-P 1/2PULG', 4790.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(825, '2901802', 'UNIVERSAL PVC-P 1-1/2PULG', 39784.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(826, '2901801', 'UNIVERSAL PVC-P 1-1/4PULG', 23179.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(827, '2901800', 'UNIVERSAL PVC-P 2PULG', 50855.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(828, '2901685', 'UNIVERSAL PVC-P 3/4PULG', 8495.00, 5, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(829, '2901709', 'YEE NOVAFORT 160 X 160', 117820.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(830, '2907351', 'YEE NOVAFORT 200 X 160', 225409.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(831, '2902962', 'YEE NOVAFORT 250 X 160', 427634.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(832, '2902963', 'YEE NOVAFORT 315 X 160', 457305.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(833, '2902964', 'YEE NOVAFORT 400 X 160', 845240.00, 2, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(834, '2903798', 'YEE PVC-S 10PULG', 729191.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(835, '2901748', 'YEE PVC-S 2PULG', 10172.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(836, '2901751', 'YEE PVC-S 3PULG', 20894.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(837, '2901755', 'YEE PVC-S 4PULG', 36357.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(838, '2901758', 'YEE PVC-S 6PULG', 172024.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(839, '2903794', 'YEE PVC-S 8PULG', 447355.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(840, '2901729', 'YEE PVC-S DOBLE 2PULG', 16270.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(841, '2901731', 'YEE PVC-S DOBLE 3PULG', 39830.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(842, '2901734', 'YEE PVC-S DOBLE 4PULG', 64082.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(843, '2901721', 'YEE PVC-S DOBLE REDUCIDA 2 x 3 x 2PULG', 33564.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(844, '2901724', 'YEE PVC-S DOBLE REDUCIDA 2 x 4 x 2PULG', 41318.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(845, '2901726', 'YEE PVC-S DOBLE REDUCIDA 3 x 4 x 3PULG', 51789.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(846, '2903795', 'YEE REDUCIDA PVC-S 10PULG x 4PULG', 673303.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(847, '2903796', 'YEE REDUCIDA PVC-S 10PULG x 6PULG', 896515.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(848, '2903797', 'YEE REDUCIDA PVC-S 10PULG x 8PULG', 1151510.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(849, '2901738', 'YEE REDUCIDA PVC-S 3 x 2PULG', 19735.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(850, '2901741', 'YEE REDUCIDA PVC-S 4PULG x 2PULG', 31209.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(851, '2901743', 'YEE REDUCIDA PVC-S 4PULG x 3PULG', 31209.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(852, '2901716', 'YEE REDUCIDA PVC-S 6 x 4PULG', 163831.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(853, '2903792', 'YEE REDUCIDA PVC-S 8 x 4PULG', 319744.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(854, '2903793', 'YEE REDUCIDA PVC-S 8 x 6PULG', 312459.00, 7, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(855, '2911389', 'CODO 90° H.D. C900 6PULG', 1779450.00, 27, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(856, '2911390', 'CODO 90° H.D. C900 8PULG', 2882130.00, 27, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(857, '2911386', 'CODO 45° H.D. C900 6PULG', 1654270.00, 27, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(858, '2911387', 'CODO 45° H.D. C900 8PULG', 2706790.00, 27, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(859, '2911401', 'TEE H.D. C900 6PULG', 2297870.00, 27, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(860, '2911402', 'TEE H.D. C900 8PULG', 3396460.00, 27, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(861, '2900145', 'TUBERIA CORRUGADA 65MM SIN FILTRO (DRENAJE)', 14183.00, 20, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(862, '2902930', 'UNION CORRUGADA 65MM (DRENAJE)', 8096.00, 20, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(863, '2910004', 'SOLDADURA COLOR VERDE LOW VOC 1/4 GAL', 136699.00, 11, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(864, '2905080', 'TUBERIA BIAXIAL 3PULG PVC 160 PSI', 38774.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(865, '2905079', 'TUBERIA BIAXIAL 3PULG PVC 200 PSI', 46757.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(866, '2900106', 'TUBERIA BIAXIAL 10PULG PVC 160 PSI', 263220.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(867, '2900105', 'TUBERIA BIAXIAL 10PULG PVC 200 PSI', 323625.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(868, '2900108', 'TUBERIA BIAXIAL 12PULG PVC 160 PSI', 387916.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(869, '2900107', 'TUBERIA BIAXIAL 12PULG PVC 200 PSI', 452814.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(870, '2900523', 'TUBERIA BIAXIAL 14PULG PVC 160 PSI', 461680.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(871, '2900524', 'TUBERIA BIAXIAL 14PULG PVC 200 PSI', 561544.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(872, '2905387', 'TUBERIA BIAXIAL 16PULG PVC 160 PSI', 612552.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(873, '2905388', 'TUBERIA BIAXIAL 16PULG PVC 200 PSI', 737035.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(874, '2905392', 'TUBERIA BIAXIAL 18PULG PVC 160 PSI', 785165.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(875, '2905393', 'TUBERIA BIAXIAL 18PULG PVC 200 PSI', 945972.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(876, '2905394', 'TUBERIA BIAXIAL 20PULG PVC 160 PSI', 1011620.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(877, '2905395', 'TUBERIA BIAXIAL 20PULG PVC 200 PSI', 1178320.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(878, '2900110', 'TUBERIA BIAXIAL 4PULG PVC 160 PSI', 49742.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(879, '2900109', 'TUBERIA BIAXIAL 4PULG PVC 200 PSI', 59073.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(880, '2900112', 'TUBERIA BIAXIAL 6PULG PVC 160 PSI', 99404.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(881, '2900111', 'TUBERIA BIAXIAL 6PULG PVC 200 PSI', 121366.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(882, '2900114', 'TUBERIA BIAXIAL 8PULG PVC 160 PSI', 169108.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(883, '2900113', 'TUBERIA BIAXIAL 8PULG PVC 200 PSI', 205580.00, 29, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(884, '2900092', 'TUBERIA ALCANTARILLADO NOVAFORT 160 MM S8', 46934.00, 15, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(885, '2900094', 'TUBERIA ALCANTARILLADO NOVAFORT 200 MM S8', 67093.00, 15, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(886, '2900081', 'TUBERIA ALCANTARILLADO NOVAFORT 250 MM S8', 97992.00, 15, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(887, '2900083', 'TUBERIA ALCANTARILLADO NOVAFORT 315 MM S8', 141005.00, 15, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(888, '2902493', 'TUBERIA ALCANTARILLADO NOVAFORT 355 MM S8', 211315.00, 15, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(889, '2900085', 'TUBERIA ALCANTARILLADO NOVAFORT 400 MM S8', 258462.00, 15, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(890, '2900087', 'TUBERIA ALCANTARILLADO NOVAFORT 450 MM S8', 355045.00, 15, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(891, '2900089', 'TUBERIA ALCANTARILLADO NOVAFORT 500 MM S8', 435181.00, 15, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(892, '2910542', 'TUBERIA PVC C900 DR 14 8PULG', 508487.00, 21, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(893, '2910541', 'TUBERIA PVC C900 DR 18 8PULG', 403561.00, 21, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(894, '2909174', 'JUNTA DE EXPANSION DE 2 CUERPOS 3PULG', 45664.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(895, '2901688', 'JUNTA DE EXPANSION DE 2 CUERPOS 4PULG', 52346.00, 6, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(896, '2911094', 'TUBERIA BIAXIAL 4PULG EXTREMO LISO PR 200 PSI', 72567.00, 30, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(897, '2911093', 'TUBERIA BIAXIAL 3PULG EXTREMO LISO PR 200 PSI', 42641.00, 30, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(898, '2911095', 'TUBERIA BIAXIAL 6PULG EXTREMO LISO PR 200 PSI', 154700.00, 30, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(899, '2911397', 'TAPON HD AWWA C110 4PULG - PAVCO', 488003.00, 27, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(900, '2911398', 'TAPON HD AWWA C110 6PULG - PAVCO', 832665.00, 27, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(901, '2911399', 'TAPON HD AWWA C110 8PULG - PAVCO', 1398040.00, 27, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(902, '2910945', 'ADAPTADOR A PARED PARA SIFON BLANCO - PAVCO', 10454.00, 10, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(903, '2910493', 'ACOPLE 1/2PULG x 1/2PULG (LAVAMANOS ) L = 60 CM', 4072.00, 3, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(904, '2911406', 'UNION HD AWWA C110 4PULG CXC', 1061860.00, 27, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(905, '2908620', 'TAPON INYECTADO NACIONAL 3PULG', 71273.00, 31, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(906, '65519', 'HOJA DE SEGUETA', 4699.00, 34, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(907, '46495', 'ESTOPA', 2299.00, 34, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(908, '2901040', 'CAJA GALVANIZADA 2400', 4180.00, 32, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(909, '2901326', 'SUPLEMENTO CAJA GALVANIZADA 2400', 1580.00, 32, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(910, '2901044', 'CAJA GALVANIZADA 5800', 2797.00, 32, '2025-07-27 08:20:25', '2025-07-27 08:20:25');
INSERT INTO `productos` (`id`, `codigo`, `descripcion`, `precio`, `grupo_id`, `created_at`, `updated_at`) VALUES
(911, '2901042', 'CAJA OCTAGONAL GALVANIZADA', 3783.00, 32, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(912, '2908616', 'CODO 45° 2PULG INYECTADO NACIONAL - PAVCO', 76406.00, 31, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(913, '2909092', 'CODO 45° 3PULG INYECTADO NACIONAL - PAVCO', 114837.00, 31, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(914, '2909093', 'CODO 45° 4PULG INYECTADO NACIONAL - PAVCO', 150169.00, 31, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(915, '2909094', 'CODO 45° 6PULG INYECTADO NACIONAL - PAVCO', 281889.00, 31, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(916, '2903537', 'TEE PVC UP RADIO CORTO 3PULG X 2PULG', 500585.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(917, '2903535', 'TEE PVC UP RADIO CORTO 4PULG X 2PULG', 771684.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(918, '2903533', 'TEE PVC UP RADIO CORTO 4PULG X 3PULG', 771684.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(919, '2903532', 'TEE PVC UP RADIO CORTO 6PULG X 3PULG', 1728210.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(920, '2903531', 'TEE PVC UP RADIO CORTO 6PULG X 4PULG', 1728210.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(921, '2903530', 'TEE PVC UP RADIO CORTO 8PULG X 4PULG', 2916840.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(922, '2903529', 'TEE PVC UP RADIO CORTO 8PULG X 6PULG', 2985420.00, 8, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(923, '2901845', 'BASE CAJA DE INSPECCION 315 (160 X 110)', 78631.00, 33, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(924, '2902824', 'BASE CAJA DE INSPECCION 400 (200 X 160)', 260142.00, 33, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(925, '2902538', 'ELEVADOR CAJA DE INSPECCION 315 (315 X 500)', 58130.00, 33, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(926, '2902540', 'ELEVADOR CAJA DE INSPECCION 400 (400 X 500)', 91297.00, 33, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(927, '2902539', 'ELEVADOR CAJA DE INSPECCION 315 (315 X 1000)', 102944.00, 33, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(928, '2902541', 'ELEVADOR CAJA DE INSPECCION 400 (400 X 1000)', 160964.00, 33, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(929, '2903703', 'AROTAPA PP CAJA (PAVCO) 315', 293979.00, 33, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(930, '2903702', 'AROTAPA PP CAJA (PAVCO) 400', 268716.00, 33, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(931, '2903562', 'AROTAPA PP CAJA (PAVCO) 1000/600', 1148120.00, 33, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(932, '2900825', 'ADAPTADOR TERMINAL PVC 1/2PULG', 734.00, 32, '2025-07-27 08:20:25', '2025-07-27 08:20:25'),
(935, '123', 'Producto Prueba 01', 100.00, 34, '2025-08-08 03:40:22', '2025-08-08 03:40:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_favoritos`
--

CREATE TABLE `productos_favoritos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad_habitual` decimal(10,2) DEFAULT 1.00,
  `nombre_personalizado` varchar(255) DEFAULT NULL COMMENT 'Nombre que el cliente le da al producto',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos_favoritos`
--

INSERT INTO `productos_favoritos` (`id`, `cliente_id`, `producto_id`, `cantidad_habitual`, `nombre_personalizado`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 50.00, 'Brida 6\" para obras grandes', 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(2, 1, 13, 20.00, 'Adaptador CPVC 1\"', 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(3, 1, 17, 15.00, 'Adaptador CPVC 2\"', 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(4, 2, 7, 30.00, 'Adaptador CPVC 1\" alta temp', 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(5, 2, 12, 25.00, 'Adaptador CPVC 3/4\"', 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(6, 2, 43, 40.00, 'Adaptador CPVC 3/4\" especial', 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(7, 3, 22, 10.00, 'Adaptador PF+UAD 1/2\"', 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(8, 3, 29, 15.00, 'Adaptador PVC-P 1/2\"', 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(9, 5, 6, 5.00, 'Acople flexible lavamanos', 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(10, 5, 34, 8.00, 'Adaptador limpieza 2\"', 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(11, 8, 2, 12.00, 'Abrazadera silla 160mm', 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(12, 8, 3, 10.00, 'Abrazadera silla 200mm', 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(13, 8, 4, 8.00, 'Abrazadera silla 250mm', 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_documento` (`numero_documento`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_clientes_empleado` (`empleado_asignado`),
  ADD KEY `idx_clientes_activo` (`activo`),
  ADD KEY `idx_clientes_ciudad` (`ciudad`);

--
-- Indices de la tabla `credenciales`
--
ALTER TABLE `credenciales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `idx_credenciales_empleado` (`empleado_id`),
  ADD KEY `idx_credenciales_cliente` (`cliente_id`),
  ADD KEY `idx_credenciales_tipo` (`tipo`),
  ADD KEY `idx_credenciales_activo` (`activo`);

--
-- Indices de la tabla `descuentos_clientes`
--
ALTER TABLE `descuentos_clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_cliente_grupo` (`cliente_id`,`grupo_id`),
  ADD KEY `idx_descuentos_clientes_cliente` (`cliente_id`),
  ADD KEY `idx_descuentos_clientes_grupo` (`grupo_id`),
  ADD KEY `idx_descuentos_clientes_activo` (`activo`);

--
-- Indices de la tabla `detalle_orden_venta`
--
ALTER TABLE `detalle_orden_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_detalle_orden_venta_orden` (`orden_venta_id`),
  ADD KEY `idx_detalle_orden_venta_producto` (`producto_id`);

--
-- Indices de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_detalle_pedidos_pedido` (`pedido_id`),
  ADD KEY `idx_detalle_pedidos_producto` (`producto_id`);

--
-- Indices de la tabla `direcciones_clientes`
--
ALTER TABLE `direcciones_clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_direcciones_cliente` (`cliente_id`),
  ADD KEY `idx_direcciones_principal` (`es_principal`),
  ADD KEY `idx_direcciones_activo` (`activo`),
  ADD KEY `idx_direcciones_ciudad` (`ciudad`),
  ADD KEY `idx_direcciones_zona` (`zona_entrega`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_empleados_activo` (`activo`),
  ADD KEY `idx_empleados_cargo` (`cargo`);

--
-- Indices de la tabla `envios`
--
ALTER TABLE `envios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_guia` (`numero_guia`),
  ADD KEY `idx_envios_pedido` (`pedido_id`),
  ADD KEY `idx_envios_orden_venta` (`orden_venta_id`),
  ADD KEY `idx_envios_repartidor` (`repartidor_id`),
  ADD KEY `idx_envios_estado` (`estado`),
  ADD KEY `idx_envios_fecha` (`fecha_programada`),
  ADD KEY `idx_envios_direccion` (`direccion_entrega_id`);

--
-- Indices de la tabla `grupos_productos`
--
ALTER TABLE `grupos_productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `idx_grupos_activo` (`activo`);

--
-- Indices de la tabla `orden_venta`
--
ALTER TABLE `orden_venta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_documento` (`numero_documento`),
  ADD UNIQUE KEY `pedido_id` (`pedido_id`),
  ADD KEY `idx_orden_venta_cliente` (`cliente_id`),
  ADD KEY `idx_orden_venta_empleado` (`empleado_id`),
  ADD KEY `idx_orden_venta_fecha` (`fecha_emision`),
  ADD KEY `idx_orden_venta_estado` (`estado`),
  ADD KEY `idx_orden_venta_direccion` (`direccion_facturacion_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_documento` (`numero_documento`),
  ADD KEY `idx_pedidos_cliente` (`cliente_id`),
  ADD KEY `idx_pedidos_empleado` (`empleado_id`),
  ADD KEY `idx_pedidos_estado` (`estado`),
  ADD KEY `idx_pedidos_fecha` (`fecha_pedido`),
  ADD KEY `idx_pedidos_direccion` (`direccion_entrega_id`),
  ADD KEY `idx_pedidos_template` (`pedido_template`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `idx_productos_grupo` (`grupo_id`),
  ADD KEY `idx_productos_precio` (`precio`);

--
-- Indices de la tabla `productos_favoritos`
--
ALTER TABLE `productos_favoritos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_cliente_producto` (`cliente_id`,`producto_id`),
  ADD KEY `idx_productos_favoritos_cliente` (`cliente_id`),
  ADD KEY `idx_productos_favoritos_producto` (`producto_id`),
  ADD KEY `idx_productos_favoritos_activo` (`activo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `credenciales`
--
ALTER TABLE `credenciales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `descuentos_clientes`
--
ALTER TABLE `descuentos_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `detalle_orden_venta`
--
ALTER TABLE `detalle_orden_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `direcciones_clientes`
--
ALTER TABLE `direcciones_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `envios`
--
ALTER TABLE `envios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `grupos_productos`
--
ALTER TABLE `grupos_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `orden_venta`
--
ALTER TABLE `orden_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=937;

--
-- AUTO_INCREMENT de la tabla `productos_favoritos`
--
ALTER TABLE `productos_favoritos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `fk_clientes_empleado` FOREIGN KEY (`empleado_asignado`) REFERENCES `empleados` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
