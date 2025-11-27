-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-11-2025 a las 06:27:32
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
(1, 'Constructora Bogotá S.A.S.', '12345689', 'NIT', 'compras@constructorabogota.com', '6013456789', 'Calle 72 # 10-15', 'Bogotá', 'Oscar Londoño', 2, 1, '2025-08-08 01:08:52', '2025-10-15 20:54:32'),
(2, 'Hidráulicos del Norte Ltda.', '800234567-8', 'NIT', 'gerencia@hidraulicosdelnorte.com', '6014567890', 'Carrera 15 # 85-30', 'Bogotá', 'Arq. Laura Gómez', 3, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(3, 'Instalaciones García Hermanos', '79123456789', 'CC', 'instalacionesgarcia@gmail.com', '3001234567', 'Calle 45 # 20-18', 'Medellín', 'Miguel García', 2, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(4, 'Suministros Industriales Cali S.A.S.', '900345678-9', 'NIT', 'ventas@suministrosindustrialescali.com', '6015678901', 'Avenida 6N # 23-45', 'Cali', 'Ing. Carmen Torres', 4, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(5, 'Plomería Profesional Ltda.', '800456789-0', 'NIT', 'contacto@plomeriaprofesional.com', '6016789012', 'Carrera 30 # 50-25', 'Bucaramanga', 'Técnico Juan Pérez', 3, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(6, 'Construcciones Valle del Cauca', '900567890-1', 'NIT', 'proyectos@construccionesvalle.com', '6017890123', 'Calle 100 # 15-40', 'Cali', 'Arq. Sebastián Ruiz', 2, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(7, 'Roberto Silva Contractor', '1023456789', 'CC', 'roberto.silva.contractor@outlook.com', '3157894561', 'Diagonal 20 # 35-12', 'Barranquilla', 'Roberto Silva', 4, 1, '2025-08-08 01:08:52', '2025-10-14 21:19:15'),
(8, 'Ferretería El Progreso', '52345678901', 'CC', 'ferreteriaprogreso@hotmail.com', '3209876543', 'Carrera 8 # 12-30', 'Manizales', 'Dora Alicia Mendoza', 3, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(9, 'Distribuidora de Tuberías del Caribe', '800678901-2', 'NIT', 'distribuidoratuberias@gmail.com', '6018901234', 'Calle 70 # 45-18', 'Cartagena', 'Ing. Patricia Herrera', 2, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(10, 'Mantenimiento Integral PYME', '900789012-3', 'NIT', 'mantenimientointegral@empresa.com', '6019012345', 'Avenida El Dorado # 68-90', 'Bogotá', 'Técnico Francisco López', 4, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(13, 'Cliente de Pruebaasdasdasdasd', '123456', 'NIT', 'cliente@prueba.com', '3001234567', 'Calle 72 # 10-15', 'Cali', 'Ing. Pedro Martínez', NULL, 1, '2025-08-24 03:45:54', '2025-11-21 20:11:37'),
(14, 'Tomas Londoño', '1092852724', 'CC', 'tomaslonfake@gmail.com', '3008918889', 'Terra Quimbaya Etapa 2', 'Calarca', 'Tomas Londoño', NULL, 1, '2025-11-19 18:10:07', '2025-11-19 18:10:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobantes_entrega`
--

CREATE TABLE `comprobantes_entrega` (
  `id` int(11) NOT NULL,
  `entrega_id` int(11) NOT NULL,
  `receptor_nombre` varchar(255) NOT NULL,
  `receptor_documento` varchar(50) DEFAULT NULL,
  `receptor_email` varchar(255) DEFAULT NULL,
  `foto_cliente` longblob DEFAULT NULL,
  `firma_cliente` longblob DEFAULT NULL,
  `firma_repartidor` longblob DEFAULT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `codigo_qr` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `fecha_generacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `enviado_por_email` tinyint(1) DEFAULT 0,
  `fecha_envio_email` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `comprobantes_entrega`
--

INSERT INTO `comprobantes_entrega` (`id`, `entrega_id`, `receptor_nombre`, `receptor_documento`, `receptor_email`, `foto_cliente`, `firma_cliente`, `firma_repartidor`, `latitud`, `longitud`, `codigo_qr`, `observaciones`, `pdf_path`, `fecha_generacion`, `enviado_por_email`, `fecha_envio_email`, `created_at`) VALUES
(1, 1, 'Miguel Garc??a', '79123456', 'instalacionesgarcia@gmail.com', NULL, NULL, NULL, 6.24420300, -75.58121500, 'ENT-000001-A7B9C2', 'Cliente recibi?? conforme, firma y foto capturadas', '/uploads/comprobantes/comprobante_ENT-000001-A7B9C2.pdf', '2025-11-19 18:56:07', 1, '2025-08-07 14:30:00', '2025-08-07 19:25:00'),
(3, 3, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', 0x2f70726f6a6563745469656e64615669727475616c2f75706c6f6164732f656e7472656761732f666f746f732f666f746f5f454e542d3030303031332d4138313444442e6a7067, 0x2f70726f6a6563745469656e64615669727475616c2f75706c6f6164732f656e7472656761732f6669726d61732f6669726d615f454e542d3030303031332d4138313444442e706e67, NULL, 4.51261010, -75.65436060, 'ENT-000013-A814DD', '', '/projectTiendaVirtual/uploads/comprobantes/comprobante_ENT-000013-A814DD.pdf', '2025-11-20 21:00:41', 1, '2025-11-20 16:00:43', '2025-11-20 21:00:41'),
(4, 4, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', 0x2f70726f6a6563745469656e64615669727475616c2f75706c6f6164732f656e7472656761732f666f746f732f666f746f5f454e542d3030303031332d3444463044442e6a7067, 0x2f70726f6a6563745469656e64615669727475616c2f75706c6f6164732f656e7472656761732f6669726d61732f6669726d615f454e542d3030303031332d3444463044442e706e67, NULL, 4.51261010, -75.65436060, 'ENT-000013-4DF0DD', '', '/projectTiendaVirtual/uploads/comprobantes/comprobante_ENT-000013-4DF0DD.pdf', '2025-11-20 21:00:49', 1, '2025-11-20 16:00:51', '2025-11-20 21:00:49'),
(5, 5, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', 0x2f70726f6a6563745469656e64615669727475616c2f75706c6f6164732f656e7472656761732f666f746f732f666f746f5f454e542d3030303032362d3741413541322e6a7067, 0x2f70726f6a6563745469656e64615669727475616c2f75706c6f6164732f656e7472656761732f6669726d61732f6669726d615f454e542d3030303032362d3741413541322e706e67, NULL, 4.51289747, -75.65376940, 'ENT-000026-7AA5A2', 'Hoals', '/projectTiendaVirtual/uploads/comprobantes/comprobante_ENT-000026-7AA5A2.pdf', '2025-11-20 21:49:10', 1, '2025-11-20 16:49:12', '2025-11-20 21:49:10'),
(6, 6, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', 0x2f70726f6a6563745469656e64615669727475616c2f75706c6f6164732f656e7472656761732f666f746f732f666f746f5f454e542d3030303032372d4633363932392e6a7067, 0x2f70726f6a6563745469656e64615669727475616c2f75706c6f6164732f656e7472656761732f6669726d61732f6669726d615f454e542d3030303032372d4633363932392e706e67, NULL, 4.51288300, -75.65307843, 'ENT-000027-F36929', 'Prueba 02', '/projectTiendaVirtual/uploads/comprobantes/comprobante_ENT-000027-F36929.pdf', '2025-11-20 21:54:27', 1, '2025-11-20 16:54:29', '2025-11-20 21:54:27'),
(7, 7, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', 0x2f70726f6a6563745469656e64615669727475616c2f75706c6f6164732f656e7472656761732f666f746f732f666f746f5f454e542d3030303032382d3437364630302e6a7067, 0x2f70726f6a6563745469656e64615669727475616c2f75706c6f6164732f656e7472656761732f6669726d61732f6669726d615f454e542d3030303032382d3437364630302e706e67, NULL, 4.51282312, -75.65310508, 'ENT-000028-476F00', 'Prueba 3', '/projectTiendaVirtual/uploads/comprobantes/comprobante_ENT-000028-476F00.pdf', '2025-11-20 21:56:21', 1, '2025-11-20 16:56:23', '2025-11-20 21:56:21'),
(8, 8, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', 0x2f70726f6a6563745469656e64615669727475616c2f75706c6f6164732f656e7472656761732f666f746f732f666f746f5f454e542d3030303032392d4546443442432e6a7067, 0x2f70726f6a6563745469656e64615669727475616c2f75706c6f6164732f656e7472656761732f6669726d61732f6669726d615f454e542d3030303032392d4546443442432e706e67, NULL, 4.51277325, -75.65375583, 'ENT-000029-EFD4BC', 'Prueba 04', '/projectTiendaVirtual/uploads/comprobantes/comprobante_ENT-000029-EFD4BC.pdf', '2025-11-20 22:01:40', 1, '2025-11-20 17:01:42', '2025-11-20 22:01:40'),
(9, 9, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', NULL, NULL, NULL, 4.51282178, -75.65343730, 'ENT-000030-139FC0', 'Pruea 05', '/projectTiendaVirtual/uploads/comprobantes/comprobante_ENT-000030-139FC0.pdf', '2025-11-20 22:21:36', 1, '2025-11-20 17:21:38', '2025-11-20 22:21:36'),
(10, 10, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', NULL, NULL, NULL, 4.51283596, -75.65314151, 'ENT-000031-3A428F', 'ninguna', '/projectTiendaVirtual/uploads/comprobantes/comprobante_ENT-000031-3A428F.pdf', '2025-11-24 23:34:04', 1, '2025-11-24 18:34:06', '2025-11-24 23:34:04'),
(11, 11, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', NULL, NULL, NULL, 4.51261210, -75.65435790, 'ENT-000032-847AB8', '', '/projectTiendaVirtual/uploads/comprobantes/comprobante_ENT-000032-847AB8.pdf', '2025-11-25 00:12:53', 1, '2025-11-24 19:12:55', '2025-11-25 00:12:53');

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
(1, 'admin', '$2y$10$BpkDiIPc1/1Cn4VF1JQHlOVOqv99zTr/qWlFtSCnNMuTfca8YY2cy', 'empleado', 1, NULL, '2025-11-27 04:35:21', 1, '2025-08-08 01:08:52', '2025-11-27 04:35:21'),
(2, 'mrodriguez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 2, NULL, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(3, 'jgarcia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 3, NULL, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(4, 'aherrera', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 4, NULL, NULL, 0, '2025-08-08 01:08:52', '2025-08-09 03:11:59'),
(5, 'rjimenez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 5, NULL, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(6, 'fcastro', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 6, NULL, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(7, 'dmorales', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 7, NULL, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(8, 'cliente2', '$2y$10$K4dmTtJfGc.2PvyVosfK8Op6/olfvuGb9nt1Nn4uacWH0f2GPf0D2', 'cliente', 1, 1, '2025-11-24 23:25:02', 1, '2025-08-08 01:08:52', '2025-11-24 23:25:02'),
(9, 'hidraulicos.norte', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 2, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(10, 'garcia.hermanos', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 3, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(11, 'suministros.cali', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 4, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(12, 'plomeria.profesional', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 5, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(13, 'construcciones.valle', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 6, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(14, 'roberto.silva', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 7, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(15, 'ferreteria.progreso', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 8, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(16, 'tuberias.caribe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', NULL, 9, NULL, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(17, 'mantenimiento.pyme', '123', 'cliente', NULL, 10, NULL, 1, '2025-08-08 01:08:52', '2025-10-16 16:58:46'),
(18, 'cliente', '$2y$10$Gv2skFGtan3Yb9ZPmNbLa.kLQyNXHFhZjDL3uB2qEoRHi3CS8gVSC', 'cliente', NULL, 13, '2025-11-27 04:48:54', 1, '2025-10-16 17:00:59', '2025-11-27 04:48:54'),
(20, 'repartidor', '$2y$10$3FmJWleDcQGkReD0vLY7CuvdxrLaaUAhoALVigIrNmsYj/Clxg9wC', 'empleado', 11, NULL, '2025-11-27 04:39:53', 1, '2025-11-19 16:38:42', '2025-11-27 04:39:53'),
(21, 'tomas.londoño', '$2y$10$n0XaM/ii4ZPwYglmnjAAj.7PlJoIPO6aAxdNlJN.qWzHtrXp0wxDS', 'cliente', NULL, 14, NULL, 1, '2025-11-19 18:10:07', '2025-11-19 18:10:07'),
(23, 'jolmedo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 10, NULL, NULL, 1, '2025-11-19 18:56:07', '2025-11-19 18:56:07');

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
(12, 6, 13, 14.00, 0, '2025-08-08 01:08:52', '2025-10-15 20:54:23'),
(13, 6, 14, 12.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(14, 6, 1, 10.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(15, 9, 15, 22.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(16, 9, 16, 20.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(17, 9, 13, 18.21, 1, '2025-08-08 01:08:52', '2025-11-21 20:16:33'),
(18, 9, 14, 16.00, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(19, 13, 21, 20.00, 1, '2025-10-27 15:00:37', '2025-10-27 15:00:37'),
(20, 13, 2, 20.00, 1, '2025-11-11 22:08:28', '2025-11-11 22:08:28');

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
(10, 4, 6, 25.00, 2716.00, 0.00, 0.00, 19.00, 12901.00, 67900.00, '2025-08-08 01:08:52'),
(11, 5, 2, 5.00, 47500.00, 7.00, 16625.00, 16.00, 35340.00, 220875.00, '2025-10-16 15:33:52'),
(12, 6, 2, 1.00, 47500.00, 55.00, 26125.00, 16.00, 3420.00, 21375.00, '2025-10-16 16:39:28'),
(13, 7, 2, 5.00, 47500.00, 15.00, 35625.00, 16.00, 32300.00, 201875.00, '2025-10-20 15:02:00'),
(14, 8, 2, 20.00, 47500.00, 15.00, 142500.00, 16.00, 129200.00, 807500.00, '2025-10-20 15:13:00'),
(15, 9, 2, 100.00, 47500.00, 20.00, 950000.00, 16.00, 608000.00, 3800000.00, '2025-10-20 16:01:51'),
(16, 10, 4, 10.00, 59556.00, 20.00, 119112.00, 16.00, 76231.68, 476448.00, '2025-10-21 00:06:25'),
(17, 11, 17, 100.00, 108356.00, 15.00, 1625340.00, 16.00, 1473641.60, 10835600.00, '2025-10-21 00:14:49');

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
(13, 5, 1, 10.00, 140476.00, 10.00, 140476.00, 1264284.00, '2025-08-08 01:08:52'),
(14, 73, 2, 5.00, 47500.00, 0.00, 0.00, 237500.00, '2025-10-16 15:30:15'),
(15, 74, 2, 5.00, 47500.00, 0.00, 0.00, 237500.00, '2025-10-16 15:33:52'),
(16, 75, 2, 1.00, 47500.00, 0.00, 0.00, 47500.00, '2025-10-16 16:39:28'),
(17, 76, 2, 5.00, 47500.00, 0.00, 0.00, 237500.00, '2025-10-20 15:02:00'),
(18, 77, 2, 20.00, 47500.00, 0.00, 0.00, 950000.00, '2025-10-20 15:13:00'),
(19, 78, 2, 1.00, 47500.00, 0.00, 0.00, 47500.00, '2025-10-20 15:48:07'),
(20, 79, 2, 1.00, 47500.00, 0.00, 0.00, 47500.00, '2025-10-20 15:48:14'),
(21, 80, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:00:24'),
(22, 81, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:00:26'),
(23, 82, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:00:28'),
(24, 83, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:00:28'),
(25, 84, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:00:28'),
(26, 85, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:00:28'),
(27, 86, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:00:28'),
(28, 87, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:00:29'),
(29, 88, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:00:29'),
(30, 89, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:00:29'),
(31, 90, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:00:30'),
(32, 91, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:00:30'),
(33, 92, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:00:30'),
(34, 93, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:01:36'),
(35, 94, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:01:37'),
(36, 95, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:01:37'),
(37, 96, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:01:37'),
(38, 97, 2, 100.00, 47500.00, 0.00, 0.00, 4750000.00, '2025-10-20 16:01:51'),
(39, 98, 4, 10.00, 59556.00, 0.00, 0.00, 595560.00, '2025-10-21 00:06:25'),
(40, 99, 17, 100.00, 108356.00, 0.00, 0.00, 10835600.00, '2025-10-21 00:14:49'),
(41, 100, 682, 10.00, 143116.00, 20.00, 286232.00, 1431160.00, '2025-10-27 15:05:47'),
(42, 101, 2, 3.00, 47500.00, 0.00, 0.00, 142500.00, '2025-11-11 22:07:23'),
(43, 101, 219, 5.00, 26479.00, 0.00, 0.00, 132395.00, '2025-11-11 22:07:23'),
(44, 101, 907, 2.00, 2299.00, 0.00, 0.00, 4598.00, '2025-11-11 22:07:23'),
(45, 102, 2, 3.00, 47500.00, 20.00, 28500.00, 142500.00, '2025-11-11 22:08:54'),
(46, 102, 3, 7.00, 52020.00, 20.00, 72828.00, 364140.00, '2025-11-11 22:08:54'),
(47, 102, 219, 5.00, 26479.00, 20.00, 26479.00, 132395.00, '2025-11-11 22:08:54'),
(48, 102, 907, 2.00, 2299.00, 0.00, 0.00, 4598.00, '2025-11-11 22:08:54'),
(49, 103, 2, 3.00, 47500.00, 20.00, 28500.00, 142500.00, '2025-11-11 22:09:35'),
(50, 103, 3, 7.00, 52020.00, 20.00, 72828.00, 364140.00, '2025-11-11 22:09:35'),
(51, 103, 8, 10.00, 28529.00, 0.00, 0.00, 285290.00, '2025-11-11 22:09:35'),
(52, 103, 219, 5.00, 26479.00, 20.00, 26479.00, 132395.00, '2025-11-11 22:09:35'),
(53, 103, 907, 2.00, 2299.00, 0.00, 0.00, 4598.00, '2025-11-11 22:09:35'),
(54, 104, 2, 3.00, 47500.00, 20.00, 28500.00, 142500.00, '2025-11-15 18:02:36'),
(55, 104, 219, 5.00, 26479.00, 20.00, 26479.00, 132395.00, '2025-11-15 18:02:36'),
(56, 104, 907, 2.00, 2299.00, 0.00, 0.00, 4598.00, '2025-11-15 18:02:36'),
(57, 105, 2, 3.00, 47500.00, 20.00, 28500.00, 142500.00, '2025-11-15 18:11:05'),
(58, 105, 7, 1.00, 99860.00, 0.00, 0.00, 99860.00, '2025-11-15 18:11:05'),
(59, 105, 219, 5.00, 26479.00, 20.00, 26479.00, 132395.00, '2025-11-15 18:11:05'),
(60, 105, 907, 2.00, 2299.00, 0.00, 0.00, 4598.00, '2025-11-15 18:11:05'),
(61, 106, 2, 3.00, 47500.00, 20.00, 28500.00, 142500.00, '2025-11-21 20:10:22'),
(62, 106, 219, 5.00, 26479.00, 20.00, 26479.00, 132395.00, '2025-11-21 20:10:22'),
(63, 106, 907, 2.00, 2299.00, 0.00, 0.00, 4598.00, '2025-11-21 20:10:22'),
(64, 107, 72, 24.00, 7923.00, 0.00, 0.00, 190152.00, '2025-11-24 23:19:06'),
(65, 107, 189, 1.00, 4928.00, 0.00, 0.00, 4928.00, '2025-11-24 23:19:06'),
(66, 107, 203, 6.00, 42792.00, 0.00, 0.00, 256752.00, '2025-11-24 23:19:06'),
(67, 107, 324, 4.00, 1176730.00, 0.00, 0.00, 4706920.00, '2025-11-24 23:19:06'),
(68, 107, 341, 60.00, 15862.00, 0.00, 0.00, 951720.00, '2025-11-24 23:19:06'),
(69, 107, 419, 3.00, 65913.00, 0.00, 0.00, 197739.00, '2025-11-24 23:19:06'),
(70, 107, 501, 6.00, 136698.00, 0.00, 0.00, 820188.00, '2025-11-24 23:19:06'),
(71, 107, 552, 1.00, 57173.00, 0.00, 0.00, 57173.00, '2025-11-24 23:19:06'),
(72, 107, 613, 5.00, 166872.00, 0.00, 0.00, 834360.00, '2025-11-24 23:19:06'),
(73, 107, 684, 19.00, 114191.00, 20.00, 433925.80, 2169629.00, '2025-11-24 23:19:06'),
(74, 107, 799, 5.00, 62127.00, 0.00, 0.00, 310635.00, '2025-11-24 23:19:06'),
(75, 107, 879, 16.00, 59073.00, 0.00, 0.00, 945168.00, '2025-11-24 23:19:06'),
(76, 108, 72, 24.00, 7923.00, 0.00, 0.00, 190152.00, '2025-11-24 23:19:10'),
(77, 108, 189, 1.00, 4928.00, 0.00, 0.00, 4928.00, '2025-11-24 23:19:10'),
(78, 108, 203, 6.00, 42792.00, 0.00, 0.00, 256752.00, '2025-11-24 23:19:10'),
(79, 108, 324, 4.00, 1176730.00, 0.00, 0.00, 4706920.00, '2025-11-24 23:19:10'),
(80, 108, 341, 60.00, 15862.00, 0.00, 0.00, 951720.00, '2025-11-24 23:19:10'),
(81, 108, 419, 3.00, 65913.00, 0.00, 0.00, 197739.00, '2025-11-24 23:19:10'),
(82, 108, 501, 6.00, 136698.00, 0.00, 0.00, 820188.00, '2025-11-24 23:19:10'),
(83, 108, 552, 1.00, 57173.00, 0.00, 0.00, 57173.00, '2025-11-24 23:19:10'),
(84, 108, 613, 5.00, 166872.00, 0.00, 0.00, 834360.00, '2025-11-24 23:19:10'),
(85, 108, 684, 19.00, 114191.00, 20.00, 433925.80, 2169629.00, '2025-11-24 23:19:10'),
(86, 108, 799, 5.00, 62127.00, 0.00, 0.00, 310635.00, '2025-11-24 23:19:10'),
(87, 108, 879, 16.00, 59073.00, 0.00, 0.00, 945168.00, '2025-11-24 23:19:10');

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
  `contacto_receptor` varchar(255) DEFAULT NULL COMMENT 'Nombre de la persona que recibe en esta direcci??n',
  `documento_receptor` varchar(50) DEFAULT NULL COMMENT 'Documento de identidad del receptor',
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

INSERT INTO `direcciones_clientes` (`id`, `cliente_id`, `nombre`, `direccion`, `ciudad`, `departamento`, `codigo_postal`, `telefono`, `contacto_receptor`, `documento_receptor`, `zona_entrega`, `tiempo_entrega_estimado`, `es_principal`, `es_facturacion`, `es_envio`, `activo`, `created_at`, `updated_at`) VALUES
(1, 1, 'Sede Principal', 'Calle 72 # 10-15', 'Bogotá', 'Cundinamarca', '110111', '6013456789', 'Oscar Londo??o', '1012345678', 'Norte', 1, 0, 1, 1, 1, '2025-08-08 01:08:52', '2025-11-19 18:56:07'),
(2, 1, 'Obra Zona Rosa', 'Carrera 11 # 93-25', 'Bogotá', 'Cundinamarca', '110221', '3001234567', NULL, NULL, 'Norte', 1, 1, 0, 1, 1, '2025-08-08 01:08:52', '2025-08-23 22:14:19'),
(3, 1, 'Bodega Sur', 'Calle 13 Sur # 68-45', 'Bogotá', 'Cundinamarca', '110811', '6017654321', NULL, NULL, 'Sur', 2, 0, 0, 1, 1, '2025-08-08 01:08:52', '2025-10-16 14:22:14'),
(4, 2, 'Oficina Principal', 'Carrera 15 # 85-30', 'Bogotá', 'Cundinamarca', '110221', '6014567890', 'Laura G??mez', '52987654', 'Norte', 1, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-11-19 18:56:07'),
(5, 2, 'Taller de Servicios', 'Calle 80 # 20-45', 'Bogotá', 'Cundinamarca', '110111', '3157891234', NULL, NULL, 'Norte', 1, 0, 0, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(6, 3, 'Taller Principal', 'Calle 45 # 20-18', 'Medellín', 'Antioquia', '050012', '3001234567', 'Miguel Garc??a', '79123456', 'Centro', 3, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-11-19 18:56:07'),
(7, 4, 'Sede Principal', 'Avenida 6N # 23-45', 'Cali', 'Valle del Cauca', '760001', '6015678901', 'Carmen Torres', '31456789', 'Norte', 4, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-11-19 18:56:07'),
(8, 4, 'Almacén Sur', 'Carrera 100 # 15-78', 'Cali', 'Valle del Cauca', '760035', '3209876543', NULL, NULL, 'Sur', 4, 0, 0, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(9, 5, 'Oficina Principal', 'Carrera 30 # 50-25', 'Bucaramanga', 'Santander', '680001', '6016789012', NULL, NULL, 'Centro', 5, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(10, 6, 'Sede Administrativa', 'Calle 100 # 15-40', 'Cali', 'Valle del Cauca', '760001', '6017890123', NULL, NULL, 'Norte', 4, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-10-16 14:26:18'),
(11, 7, 'Dirección Personal', 'Diagonal 20 # 35-12', 'Barranquilla', 'Atlántico', '080001', '3157894561', NULL, NULL, 'Norte', 6, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(12, 8, 'Local Comercial', 'Carrera 8 # 12-30', 'Manizales', 'Caldas', '170001', '3209876543', NULL, NULL, 'Centro', 7, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(13, 9, 'Almacén Principal', 'Calle 70 # 45-18', 'Cartagena', 'Bolívar', '130001', '6018901234', NULL, NULL, 'Norte', 5, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(14, 9, 'Punto de Venta', 'Avenida Pedro de Heredia # 30-25', 'Cartagena', 'Bolívar', '130010', '3156789012', NULL, NULL, 'Centro', 5, 0, 0, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(15, 10, 'Oficina Central', 'Avenida El Dorado # 68-90', 'Bogotá', 'Cundinamarca', '110111', '6019012345', NULL, NULL, 'Occidente', 2, 1, 1, 1, 1, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(17, 5, 'Bodega Principal Bucaramanga', 'Carrera 30 # 50-25', 'Bucaramanga', 'Santander', '680001', NULL, 'Juan P??rez', '13456789', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:56:07', '2025-11-19 18:56:07'),
(18, 5, 'Obra Ca??averal', 'Calle 45 # 30-18', 'Floridablanca', 'Santander', '681001', NULL, 'Supervisor Carlos Ruiz', '91234567', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:56:07', '2025-11-19 18:56:07'),
(19, 6, 'Sede Administrativa', 'Calle 100 # 15-40', 'Cali', 'Valle del Cauca', '760001', NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 1, '2025-11-19 18:56:07', '2025-11-19 18:56:07'),
(20, 6, 'Proyecto Residencial Sur', 'Carrera 48 # 5-67', 'Cali', 'Valle del Cauca', '760045', NULL, 'Sebasti??n Ruiz', '1094567890', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:56:07', '2025-11-19 18:56:07'),
(21, 7, 'Oficina Barranquilla', 'Diagonal 20 # 35-12', 'Barranquilla', 'Atl??ntico', '080001', NULL, 'Roberto Silva', '1023456789', NULL, NULL, 0, 1, 1, 1, '2025-11-19 18:56:07', '2025-11-19 18:56:07'),
(22, 8, 'Local Comercial', 'Carrera 8 # 12-30', 'Manizales', 'Caldas', '170001', NULL, 'Dora Alicia Mendoza', '52345678901', NULL, NULL, 0, 1, 1, 1, '2025-11-19 18:56:07', '2025-11-19 18:56:07'),
(23, 9, 'Bodega Cartagena', 'Calle 70 # 45-18', 'Cartagena', 'Bol??var', '130001', NULL, 'Patricia Herrera', '55123456', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:56:07', '2025-11-19 18:56:07'),
(24, 10, 'Sede Bogot??', 'Avenida El Dorado # 68-90', 'Bogot??', 'Cundinamarca', '110111', NULL, 'Francisco L??pez', '80234567', NULL, NULL, 0, 1, 1, 1, '2025-11-19 18:56:07', '2025-11-19 18:56:07'),
(25, 5, 'Bodega Principal Bucaramanga', 'Carrera 30 # 50-25', 'Bucaramanga', 'Santander', '680001', NULL, 'Juan P??rez', '13456789', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:56:46', '2025-11-19 18:56:46'),
(26, 5, 'Obra Ca??averal', 'Calle 45 # 30-18', 'Floridablanca', 'Santander', '681001', NULL, 'Supervisor Carlos Ruiz', '91234567', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:56:46', '2025-11-19 18:56:46'),
(27, 6, 'Sede Administrativa', 'Calle 100 # 15-40', 'Cali', 'Valle del Cauca', '760001', NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 1, '2025-11-19 18:56:46', '2025-11-19 18:56:46'),
(28, 6, 'Proyecto Residencial Sur', 'Carrera 48 # 5-67', 'Cali', 'Valle del Cauca', '760045', NULL, 'Sebasti??n Ruiz', '1094567890', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:56:46', '2025-11-19 18:56:46'),
(29, 7, 'Oficina Barranquilla', 'Diagonal 20 # 35-12', 'Barranquilla', 'Atl??ntico', '080001', NULL, 'Roberto Silva', '1023456789', NULL, NULL, 0, 1, 1, 1, '2025-11-19 18:56:46', '2025-11-19 18:56:46'),
(30, 8, 'Local Comercial', 'Carrera 8 # 12-30', 'Manizales', 'Caldas', '170001', NULL, 'Dora Alicia Mendoza', '52345678901', NULL, NULL, 0, 1, 1, 1, '2025-11-19 18:56:46', '2025-11-19 18:56:46'),
(31, 9, 'Bodega Cartagena', 'Calle 70 # 45-18', 'Cartagena', 'Bol??var', '130001', NULL, 'Patricia Herrera', '55123456', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:56:46', '2025-11-19 18:56:46'),
(32, 10, 'Sede Bogot??', 'Avenida El Dorado # 68-90', 'Bogot??', 'Cundinamarca', '110111', NULL, 'Francisco L??pez', '80234567', NULL, NULL, 0, 1, 1, 1, '2025-11-19 18:56:46', '2025-11-19 18:56:46'),
(33, 5, 'Bodega Principal Bucaramanga', 'Carrera 30 # 50-25', 'Bucaramanga', 'Santander', '680001', NULL, 'Juan P??rez', '13456789', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:57:25', '2025-11-19 18:57:25'),
(34, 5, 'Obra Ca??averal', 'Calle 45 # 30-18', 'Floridablanca', 'Santander', '681001', NULL, 'Supervisor Carlos Ruiz', '91234567', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:57:25', '2025-11-19 18:57:25'),
(35, 6, 'Sede Administrativa', 'Calle 100 # 15-40', 'Cali', 'Valle del Cauca', '760001', NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 1, '2025-11-19 18:57:25', '2025-11-19 18:57:25'),
(36, 6, 'Proyecto Residencial Sur', 'Carrera 48 # 5-67', 'Cali', 'Valle del Cauca', '760045', NULL, 'Sebasti??n Ruiz', '1094567890', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:57:25', '2025-11-19 18:57:25'),
(37, 7, 'Oficina Barranquilla', 'Diagonal 20 # 35-12', 'Barranquilla', 'Atl??ntico', '080001', NULL, 'Roberto Silva', '1023456789', NULL, NULL, 0, 1, 1, 1, '2025-11-19 18:57:25', '2025-11-19 18:57:25'),
(38, 8, 'Local Comercial', 'Carrera 8 # 12-30', 'Manizales', 'Caldas', '170001', NULL, 'Dora Alicia Mendoza', '52345678901', NULL, NULL, 0, 1, 1, 1, '2025-11-19 18:57:25', '2025-11-19 18:57:25'),
(39, 9, 'Bodega Cartagena', 'Calle 70 # 45-18', 'Cartagena', 'Bol??var', '130001', NULL, 'Patricia Herrera', '55123456', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:57:25', '2025-11-19 18:57:25'),
(40, 10, 'Sede Bogot??', 'Avenida El Dorado # 68-90', 'Bogot??', 'Cundinamarca', '110111', NULL, 'Francisco L??pez', '80234567', NULL, NULL, 0, 1, 1, 1, '2025-11-19 18:57:25', '2025-11-19 18:57:25'),
(41, 5, 'Bodega Principal Bucaramanga', 'Carrera 30 # 50-25', 'Bucaramanga', 'Santander', '680001', NULL, 'Juan P??rez', '13456789', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:58:05', '2025-11-19 18:58:05'),
(42, 5, 'Obra Ca??averal', 'Calle 45 # 30-18', 'Floridablanca', 'Santander', '681001', NULL, 'Supervisor Carlos Ruiz', '91234567', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:58:05', '2025-11-19 18:58:05'),
(43, 6, 'Sede Administrativa', 'Calle 100 # 15-40', 'Cali', 'Valle del Cauca', '760001', NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 1, '2025-11-19 18:58:05', '2025-11-19 18:58:05'),
(44, 6, 'Proyecto Residencial Sur', 'Carrera 48 # 5-67', 'Cali', 'Valle del Cauca', '760045', NULL, 'Sebasti??n Ruiz', '1094567890', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:58:05', '2025-11-19 18:58:05'),
(45, 7, 'Oficina Barranquilla', 'Diagonal 20 # 35-12', 'Barranquilla', 'Atl??ntico', '080001', NULL, 'Roberto Silva', '1023456789', NULL, NULL, 0, 1, 1, 1, '2025-11-19 18:58:05', '2025-11-19 18:58:05'),
(46, 8, 'Local Comercial', 'Carrera 8 # 12-30', 'Manizales', 'Caldas', '170001', NULL, 'Dora Alicia Mendoza', '52345678901', NULL, NULL, 0, 1, 1, 1, '2025-11-19 18:58:05', '2025-11-19 18:58:05'),
(47, 9, 'Bodega Cartagena', 'Calle 70 # 45-18', 'Cartagena', 'Bol??var', '130001', NULL, 'Patricia Herrera', '55123456', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:58:05', '2025-11-19 18:58:05'),
(48, 10, 'Sede Bogot??', 'Avenida El Dorado # 68-90', 'Bogot??', 'Cundinamarca', '110111', NULL, 'Francisco L??pez', '80234567', NULL, NULL, 0, 1, 1, 1, '2025-11-19 18:58:05', '2025-11-19 18:58:05'),
(49, 5, 'Bodega Principal Bucaramanga', 'Carrera 30 # 50-25', 'Bucaramanga', 'Santander', '680001', NULL, 'Juan P??rez', '13456789', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:58:31', '2025-11-19 18:58:31'),
(50, 5, 'Obra Ca??averal', 'Calle 45 # 30-18', 'Floridablanca', 'Santander', '681001', NULL, 'Supervisor Carlos Ruiz', '91234567', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:58:31', '2025-11-19 18:58:31'),
(51, 6, 'Sede Administrativa', 'Calle 100 # 15-40', 'Cali', 'Valle del Cauca', '760001', NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 1, '2025-11-19 18:58:31', '2025-11-19 18:58:31'),
(52, 6, 'Proyecto Residencial Sur', 'Carrera 48 # 5-67', 'Cali', 'Valle del Cauca', '760045', NULL, 'Sebasti??n Ruiz', '1094567890', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:58:31', '2025-11-19 18:58:31'),
(53, 7, 'Oficina Barranquilla', 'Diagonal 20 # 35-12', 'Barranquilla', 'Atl??ntico', '080001', NULL, 'Roberto Silva', '1023456789', NULL, NULL, 0, 1, 1, 1, '2025-11-19 18:58:31', '2025-11-19 18:58:31'),
(54, 8, 'Local Comercial', 'Carrera 8 # 12-30', 'Manizales', 'Caldas', '170001', NULL, 'Dora Alicia Mendoza', '52345678901', NULL, NULL, 0, 1, 1, 1, '2025-11-19 18:58:31', '2025-11-19 18:58:31'),
(55, 9, 'Bodega Cartagena', 'Calle 70 # 45-18', 'Cartagena', 'Bol??var', '130001', NULL, 'Patricia Herrera', '55123456', NULL, NULL, 0, 0, 1, 1, '2025-11-19 18:58:31', '2025-11-19 18:58:31'),
(56, 10, 'Sede Bogot??', 'Avenida El Dorado # 68-90', 'Bogot??', 'Cundinamarca', '110111', NULL, 'Francisco L??pez', '80234567', NULL, NULL, 0, 1, 1, 1, '2025-11-19 18:58:31', '2025-11-19 18:58:31'),
(57, 13, 'Generica', 'Cra 23e #10-24', 'Armenia', 'Quindio', '760001', '3001234567', NULL, NULL, NULL, NULL, 1, 0, 1, 1, '2025-11-24 23:32:21', '2025-11-24 23:32:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `cargo` enum('admin','repartidor') NOT NULL DEFAULT 'admin',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `nombre`, `email`, `telefono`, `cargo`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Carlos Alberto Mendoza', 'cmendoza@soltecnica.com', '3201234567', 'admin', 1, '2025-08-08 06:08:51', '2025-08-08 06:08:51'),
(2, 'María Elena Rodríguez', 'mrodriguez@soltecnica.com', '3157891234', 'admin', 1, '2025-08-08 01:08:51', '2025-11-20 22:26:36'),
(3, 'José Luis García', 'jgarcia@soltecnica.com', '3009876543', 'admin', 1, '2025-08-08 01:08:51', '2025-11-20 22:26:36'),
(4, 'Ana Patricia Herrera', 'aherrera@soltecnica.com', '3125678901', 'admin', 1, '2025-08-08 01:08:51', '2025-11-20 22:26:36'),
(5, 'Roberto Jiménez Silva', 'rjimenez@soltecnica.com', '3145567890', 'repartidor', 1, '2025-08-08 01:08:51', '2025-08-08 01:08:51'),
(6, 'Fernando Castro López', 'fcastro@soltecnica.com', '3176543210', 'repartidor', 1, '2025-08-08 01:08:51', '2025-08-08 01:08:51'),
(7, 'Diana Patricia Morales', 'dmorales@soltecnica.com', '3198765432', 'admin', 1, '2025-08-08 01:08:51', '2025-11-20 22:26:36'),
(10, 'Jairo Patricio Olmedo', 'holamundo@prueba.com', '12333333', 'repartidor', 1, '2025-11-15 18:15:18', '2025-11-15 18:15:18'),
(11, 'Repartidor de Prueba', 'repartidor.hola@repartidor.com', '3001231212', 'repartidor', 1, '2025-11-19 16:38:42', '2025-11-19 16:38:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entregas`
--

CREATE TABLE `entregas` (
  `id` int(11) NOT NULL,
  `envio_id` int(11) DEFAULT NULL,
  `pedido_id` int(11) DEFAULT NULL,
  `factura_id` int(11) DEFAULT NULL,
  `repartidor_id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `direccion_entrega` text NOT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `telefono_cliente` varchar(20) DEFAULT NULL,
  `receptor_nombre` varchar(255) DEFAULT NULL,
  `receptor_documento` varchar(50) DEFAULT NULL,
  `receptor_email` varchar(255) DEFAULT NULL,
  `estado` enum('programado','en_preparacion','en_transito','entregado','fallido','devuelto','cancelado') DEFAULT 'programado',
  `fecha_creacion` datetime NOT NULL,
  `fecha_entrega_real` datetime DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `entregas`
--

INSERT INTO `entregas` (`id`, `envio_id`, `pedido_id`, `factura_id`, `repartidor_id`, `cliente_id`, `direccion_entrega`, `ciudad`, `telefono_cliente`, `receptor_nombre`, `receptor_documento`, `receptor_email`, `estado`, `fecha_creacion`, `fecha_entrega_real`, `observaciones`, `created_at`, `updated_at`) VALUES
(1, 1, 4, NULL, 5, NULL, 'Calle 45 # 20-18', 'Medell??n', '3001234567', 'Miguel Garc??a', '79123456', 'instalacionesgarcia@gmail.com', 'entregado', '2025-08-07 08:30:00', '2025-08-07 14:25:00', 'Entrega sin novedades, cliente satisfecho', '2025-08-07 19:25:00', '2025-11-19 18:56:07'),
(2, 1, 4, NULL, 5, NULL, 'Calle 45 # 20-18', 'Medell??n', '3001234567', 'Miguel Garc??a', '79123456', 'instalacionesgarcia@gmail.com', 'entregado', '2025-08-07 08:30:00', '2025-08-07 14:25:00', 'Entrega sin novedades, cliente satisfecho', '2025-08-07 19:25:00', '2025-11-19 18:56:46'),
(3, 13, 104, NULL, 11, NULL, '', NULL, NULL, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', 'entregado', '2025-11-20 16:00:41', '2025-11-20 16:00:41', '', '2025-11-20 21:00:41', '2025-11-20 21:00:41'),
(4, 13, 104, NULL, 11, NULL, '', NULL, NULL, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', 'entregado', '2025-11-20 16:00:49', '2025-11-20 16:00:49', '', '2025-11-20 21:00:49', '2025-11-20 21:00:49'),
(5, 26, 103, NULL, 11, NULL, '', NULL, NULL, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', 'entregado', '2025-11-20 16:49:10', '2025-11-20 16:49:10', 'Hoals', '2025-11-20 21:49:10', '2025-11-20 21:49:10'),
(6, 27, 102, NULL, 11, NULL, '', NULL, NULL, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', 'entregado', '2025-11-20 16:54:27', '2025-11-20 16:54:27', 'Prueba 02', '2025-11-20 21:54:27', '2025-11-20 21:54:27'),
(7, 28, 101, NULL, 11, NULL, '', NULL, NULL, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', 'entregado', '2025-11-20 16:56:21', '2025-11-20 16:56:21', 'Prueba 3', '2025-11-20 21:56:21', '2025-11-20 21:56:21'),
(8, 29, 100, NULL, 11, NULL, '', NULL, NULL, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', 'entregado', '2025-11-20 17:01:40', '2025-11-20 17:01:40', 'Prueba 04', '2025-11-20 22:01:40', '2025-11-20 22:01:40'),
(9, 30, 99, NULL, 11, NULL, '', NULL, NULL, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', 'entregado', '2025-11-20 17:21:36', '2025-11-20 17:21:36', 'Pruea 05', '2025-11-20 22:21:36', '2025-11-20 22:21:36'),
(10, 31, 108, NULL, 11, NULL, '', NULL, NULL, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', 'entregado', '2025-11-24 18:34:04', '2025-11-24 18:34:04', 'ninguna', '2025-11-24 23:34:04', '2025-11-24 23:34:04'),
(11, 32, 98, NULL, 11, NULL, '', NULL, NULL, 'Tomas', '1092852724', 'tomaslondonomuriel123@gmail.com', 'entregado', '2025-11-24 19:12:53', '2025-11-24 19:12:53', '', '2025-11-25 00:12:53', '2025-11-25 00:12:53');

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
(3, 1, 1, 'GU-2025-00', 'Transporte Sol Técnica', 10, '2025-11-20', NULL, '2025-11-21 21:19:42', 'entregado', 1, 'Oscar Londo??o', '1012345678', 'Envío programado para mañana', 35000.00, '2025-08-08 01:08:52', '2025-11-21 20:19:42'),
(4, 2, 2, 'GU-2025-004', 'Transporte Sol Técnica', 6, '2025-08-08', '2025-08-08 07:45:00', NULL, 'en_transito', 4, 'Laura Gómez', '800234567-8', 'Material urgente en camino', 25000.00, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(10, 5, NULL, 'GU-2025-01', 'Transporte Sol Técnica', 5, '2025-11-22', NULL, NULL, 'programado', 17, 'Juan P??rez', '13456789', 'Envio de Prueba', 0.00, '2025-10-16 16:32:54', '2025-11-19 19:01:07'),
(11, 93, NULL, 'PED-20251016-3087', 'Transporte Sol Técnica', 6, '2025-11-21', NULL, NULL, 'en_transito', 19, 'Sebasti??n Ruiz', '1094567890', 'Envio de Prueba #2', 0.00, '2025-10-16 16:55:26', '2025-11-24 23:29:23'),
(12, 105, NULL, 'PED-20251115-8589', 'Transporte Sol Técnica', 10, '2025-11-20', NULL, NULL, 'programado', 20, 'Roberto Silva', '1023456789', NULL, 0.00, '2025-11-19 18:24:24', '2025-11-19 18:56:07'),
(13, 104, NULL, 'PED-20251115-8879', 'Transporte Sol Técnica', 11, '2025-11-19', NULL, '2025-11-20 16:00:49', 'entregado', 16, 'Tomas', '1092852724', NULL, 0.00, '2025-11-19 18:39:10', '2025-11-20 21:00:49'),
(14, 1, NULL, NULL, 'Transporte Sol T??cnica', 5, '2025-11-19', NULL, NULL, 'programado', 1, 'Oscar Londo??o', '1012345678', NULL, 0.00, '2025-11-19 18:56:07', '2025-11-19 18:56:07'),
(15, 2, NULL, NULL, 'Transporte Sol T??cnica', 5, '2025-11-20', NULL, NULL, 'programado', 4, 'Laura G??mez', '52987654', NULL, 0.00, '2025-11-19 18:56:07', '2025-11-19 18:56:07'),
(16, 3, NULL, NULL, 'Transporte Sol T??cnica', 6, '2025-11-19', NULL, NULL, 'en_preparacion', 7, 'Carmen Torres', '31456789', NULL, 0.00, '2025-11-19 18:56:07', '2025-11-19 18:56:07'),
(17, 4, NULL, NULL, 'Transporte Sol T??cnica', 6, '2025-11-18', NULL, NULL, 'fallido', 17, 'Juan P??rez', '13456789', NULL, 0.00, '2025-11-19 18:56:07', '2025-11-19 18:56:07'),
(18, NULL, NULL, NULL, 'Transporte Sol T??cnica', 10, '2025-11-21', NULL, NULL, 'programado', 21, 'Dora Alicia Mendoza', '52345678901', NULL, 0.00, '2025-11-19 18:56:07', '2025-11-19 18:56:07'),
(19, NULL, NULL, NULL, 'Transporte Sol T??cnica', 10, '2025-11-19', NULL, NULL, 'en_transito', 22, 'Patricia Herrera', '55123456', NULL, 0.00, '2025-11-19 18:56:07', '2025-11-19 18:56:07'),
(20, 1, NULL, NULL, 'Transporte Sol T??cnica', 5, '2025-11-19', NULL, NULL, 'programado', 1, 'Oscar Londo??o', '1012345678', NULL, 0.00, '2025-11-19 18:57:25', '2025-11-19 18:57:25'),
(22, 3, NULL, NULL, 'Transporte Sol T??cnica', 6, '2025-11-19', NULL, NULL, 'en_preparacion', 7, 'Carmen Torres', '31456789', NULL, 0.00, '2025-11-19 18:57:25', '2025-11-19 18:57:25'),
(23, 4, NULL, NULL, 'Transporte Sol T??cnica', 6, '2025-11-18', NULL, NULL, 'fallido', 17, 'Juan P??rez', '13456789', NULL, 0.00, '2025-11-19 18:57:25', '2025-11-19 18:57:25'),
(24, NULL, NULL, NULL, 'Transporte Sol T??cnica', 10, '2025-11-21', NULL, NULL, 'programado', 21, 'Dora Alicia Mendoza', '52345678901', NULL, 0.00, '2025-11-19 18:57:25', '2025-11-19 18:57:25'),
(25, NULL, NULL, NULL, 'Transporte Sol T??cnica', 10, '2025-11-19', NULL, NULL, 'en_transito', 22, 'Patricia Herrera', '55123456', NULL, 0.00, '2025-11-19 18:57:25', '2025-11-19 18:57:25'),
(26, 103, NULL, 'PED-20251111-5767', 'Transporte Sol Técnica', 11, '2025-11-20', NULL, '2025-11-20 16:49:10', 'entregado', 16, 'Tomas', '1092852724', 'Hola', 0.00, '2025-11-20 21:47:29', '2025-11-20 21:49:10'),
(27, 102, NULL, 'PED-20251111-2332', 'Transporte Sol Técnica', 11, '2025-11-20', NULL, '2025-11-20 16:54:27', 'entregado', 16, 'Tomas', '1092852724', 'Prueba 2', 0.00, '2025-11-20 21:53:25', '2025-11-20 21:54:27'),
(28, 101, NULL, 'PED-20251111-7717', 'Transporte Sol Técnica', 11, '2025-11-20', NULL, '2025-11-20 16:56:21', 'entregado', 16, 'Tomas', '1092852724', NULL, 0.00, '2025-11-20 21:55:25', '2025-11-20 21:56:21'),
(29, 100, NULL, 'PED-20251027-4658', 'Transporte Sol Técnica', 11, '2025-11-20', NULL, '2025-11-20 17:01:40', 'entregado', 16, 'Tomas', '1092852724', 'Prueba 04', 0.00, '2025-11-20 22:00:33', '2025-11-20 22:01:40'),
(30, 99, NULL, 'PED-20251021-7194', 'Transporte Sol Técnica', 11, '2025-11-20', NULL, '2025-11-20 17:21:36', 'entregado', 16, 'Tomas', '1092852724', 'Prueba 5', 0.00, '2025-11-20 22:18:45', '2025-11-20 22:21:36'),
(31, 108, NULL, 'PED-20251125-3764', 'Transporte Sol Técnica', 11, '2025-11-24', NULL, '2025-11-24 18:34:04', 'entregado', 57, 'Tomas', '1092852724', 'Hola', 0.00, '2025-11-24 23:32:48', '2025-11-24 23:34:04'),
(32, 98, NULL, 'PED-20251021-5774', 'Transporte Sol Técnica', 11, '2025-11-25', NULL, '2025-11-24 19:12:53', 'entregado', 57, 'Tomas', '1092852724', NULL, 0.00, '2025-11-25 00:11:55', '2025-11-25 00:12:53');

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
(8, 'Acc PF+UAD', 'Accesorios para sistemas de acueducto', 1, '2025-07-27 03:05:22', '2025-10-16 15:59:41'),
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
-- Estructura de tabla para la tabla `historial_entregas`
--

CREATE TABLE `historial_entregas` (
  `id` int(11) NOT NULL,
  `entrega_id` int(11) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `estado_anterior` varchar(50) DEFAULT NULL,
  `estado_nuevo` varchar(50) DEFAULT NULL,
  `repartidor_id` int(11) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_accion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `historial_entregas`
--

INSERT INTO `historial_entregas` (`id`, `entrega_id`, `accion`, `estado_anterior`, `estado_nuevo`, `repartidor_id`, `observaciones`, `fecha_accion`) VALUES
(1, 1, 'ENTREGA_COMPLETADA', 'en_transito', 'entregado', 5, 'Entrega realizada exitosamente', '2025-11-19 18:58:31'),
(2, 3, 'ENTREGA_COMPLETADA', 'en_transito', 'entregado', 11, '', '2025-11-20 21:00:41'),
(3, 4, 'ENTREGA_COMPLETADA', 'entregado', 'entregado', 11, '', '2025-11-20 21:00:49'),
(4, 5, 'ENTREGA_COMPLETADA', 'programado', 'entregado', 11, 'Hoals', '2025-11-20 21:49:10'),
(5, 6, 'ENTREGA_COMPLETADA', 'programado', 'entregado', 11, 'Prueba 02', '2025-11-20 21:54:27'),
(6, 7, 'ENTREGA_COMPLETADA', 'programado', 'entregado', 11, 'Prueba 3', '2025-11-20 21:56:21'),
(7, 8, 'ENTREGA_COMPLETADA', 'programado', 'entregado', 11, 'Prueba 04', '2025-11-20 22:01:40'),
(8, 9, 'ENTREGA_COMPLETADA', 'programado', 'entregado', 11, 'Pruea 05', '2025-11-20 22:21:36'),
(9, 10, 'ENTREGA_COMPLETADA', 'programado', 'entregado', 11, 'ninguna', '2025-11-24 23:34:04'),
(10, 11, 'ENTREGA_COMPLETADA', 'programado', 'entregado', 11, '', '2025-11-25 00:12:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(4, 'PED-2025-004', 4, 3, 2, '2025-08-05', '2025-08-12', 850000.00, 0.00, 161500.00, 1011500.00, 'pagada', 'efectivo', 6, 'Pago en efectivo contra entrega', '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(5, 'PED-20251016-8272', 74, 13, 1, '2025-10-16', '2025-10-23', 220875.00, 16625.00, 35340.00, 256215.00, 'pendiente', 'efectivo', NULL, 'Pedido de compra', '2025-10-16 15:33:52', '2025-10-16 15:33:52'),
(6, 'PED-20251016-3087', 75, 13, 1, '2025-10-16', '2025-10-23', 21375.00, 26125.00, 3420.00, 24795.00, 'pendiente', 'efectivo', NULL, 'Pedido de Prueba 2', '2025-10-16 16:39:28', '2025-10-16 16:39:28'),
(7, 'PED-20251020-8432', 76, 13, 1, '2025-10-20', '2025-10-27', 201875.00, 35625.00, 32300.00, 234175.00, 'pendiente', 'efectivo', NULL, '', '2025-10-20 15:02:00', '2025-10-20 15:02:00'),
(8, 'PED-20251020-4984', 77, 13, 1, '2025-10-20', '2025-10-27', 807500.00, 142500.00, 129200.00, 936700.00, 'pendiente', 'transferencia', NULL, '', '2025-10-20 15:13:00', '2025-10-20 15:13:00'),
(9, 'PED-20251020-0734', 97, 13, 1, '2025-10-20', '2025-12-19', 3800000.00, 950000.00, 608000.00, 4408000.00, 'pendiente', 'credito', NULL, 'cotizacion de prueba', '2025-10-20 16:01:51', '2025-10-20 16:01:51'),
(10, 'PED-20251021-5774', 98, 13, 1, '2025-10-20', '2025-11-20', 476448.00, 119112.00, 76231.68, 552679.68, 'pendiente', 'credito', NULL, 'Pedido de prueba 1', '2025-10-21 00:06:25', '2025-10-21 00:06:25'),
(11, 'PED-20251021-7194', 99, 13, 1, '2025-10-20', '2025-10-28', 10835600.00, 1625340.00, 1473641.60, 10683901.60, 'pendiente', 'efectivo', NULL, 'Pedido de Prueba 20/10/25', '2025-10-21 00:14:49', '2025-10-21 00:14:49');

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
  `fecha_pedido` datetime DEFAULT current_timestamp(),
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

INSERT INTO `pedidos` (`id`, `numero_documento`, `cliente_id`, `empleado_id`, `estado`, `fecha_pedido`, `fecha_entrega_estimada`, `subtotal`, `descuento_porcentaje`, `descuento_total`, `impuestos_total`, `total`, `direccion_entrega_id`, `observaciones`, `pedido_template`, `nombre_template`, `created_at`, `updated_at`) VALUES
(1, 'PED-2025-001', 1, 2, 'en_preparacion', '2025-08-07 00:00:00', '2025-08-09', 2500000.00, 0.00, 375000.00, 405800.00, 2530800.00, 1, 'Entrega en horario de oficina - Obra en construcción', 0, NULL, '2025-08-08 01:08:52', '2025-08-09 02:49:11'),
(2, 'PED-2025-002', 2, 3, 'en_preparacion', '2025-08-07 00:00:00', '2025-08-08', 1800000.00, 0.00, 324000.00, 279840.00, 1755840.00, 4, 'Material para reparación de tubería principal', 0, NULL, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(3, 'PED-2025-003', 4, 4, 'listo_envio', '2025-08-06 00:00:00', '2025-08-08', 3200000.00, 0.00, 640000.00, 486400.00, 3046400.00, 7, 'Pedido urgente - Cliente VIP', 0, NULL, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(4, 'PED-2025-004', 3, 2, 'enviado', '2025-08-05 00:00:00', '2025-08-07', 850000.00, 0.00, 0.00, 161500.00, 1011500.00, 6, 'Primera compra del cliente', 0, NULL, '2025-08-08 01:08:52', '2025-08-08 01:08:52'),
(5, 'PED-2025-005', 6, 4, 'enviado', '2025-08-07 00:00:00', '2025-08-10', 4500000.00, 0.00, 630000.00, 734100.00, 4604100.00, 10, 'Pedido para proyecto residencial', 1, 'Pedido Mensual Construcciones Valle', '2025-08-08 01:08:52', '2025-10-16 16:38:11'),
(93, 'COT-20251020-1880', 13, 1, 'borrador', '2025-10-20 00:00:00', NULL, 4750000.00, 0.00, 950000.00, 608000.00, 4408000.00, NULL, 'cotizacion de prueba', 0, NULL, '2025-10-20 16:01:36', '2025-10-20 16:01:36'),
(94, 'COT-20251020-9309', 13, 1, 'borrador', '2025-10-20 00:00:00', NULL, 4750000.00, 0.00, 950000.00, 608000.00, 4408000.00, NULL, 'cotizacion de prueba', 0, NULL, '2025-10-20 16:01:37', '2025-10-20 16:01:37'),
(95, 'COT-20251020-9141', 13, 1, 'borrador', '2025-10-20 00:00:00', NULL, 4750000.00, 0.00, 950000.00, 608000.00, 4408000.00, NULL, 'cotizacion de prueba', 0, NULL, '2025-10-20 16:01:37', '2025-10-20 16:01:37'),
(96, 'COT-20251020-6322', 13, 1, 'borrador', '2025-10-20 00:00:00', NULL, 4750000.00, 0.00, 950000.00, 608000.00, 4408000.00, NULL, 'cotizacion de prueba', 0, NULL, '2025-10-20 16:01:37', '2025-10-20 16:01:37'),
(97, 'PED-20251020-0734', 13, 1, 'confirmado', '2025-10-20 00:00:00', '2025-10-23', 4750000.00, 0.00, 950000.00, 608000.00, 4408000.00, NULL, 'cotizacion de prueba', 0, NULL, '2025-10-20 16:01:51', '2025-10-20 16:01:51'),
(98, 'PED-20251021-5774', 13, 1, 'confirmado', '2025-10-20 00:00:00', '2025-11-09', 595560.00, 20.00, 119112.00, 76231.68, 552679.68, NULL, 'Pedido de prueba 1', 0, NULL, '2025-10-21 00:06:25', '2025-10-21 00:06:25'),
(99, 'PED-20251021-7194', 13, 1, 'confirmado', '2025-10-20 00:00:00', '2025-10-30', 10835600.00, 15.00, 1625340.00, 1473641.60, 10683901.60, NULL, 'Pedido de Prueba 20/10/25', 0, NULL, '2025-10-21 00:14:49', '2025-10-21 00:14:49'),
(100, 'PED-20251027-4658', 13, NULL, 'confirmado', '2025-10-27 00:00:00', NULL, 1431160.00, 0.00, 286232.00, 183188.48, 1328116.48, NULL, '', 0, NULL, '2025-10-27 15:05:47', '2025-10-27 15:05:47'),
(101, 'PED-20251111-7717', 13, NULL, 'confirmado', '2025-11-11 00:00:00', '2025-11-18', 279493.00, 0.00, 0.00, 44718.88, 324211.88, NULL, '', 0, NULL, '2025-11-11 22:07:23', '2025-11-11 22:07:23'),
(102, 'PED-20251111-2332', 13, NULL, 'confirmado', '2025-11-11 00:00:00', '2025-11-18', 643633.00, 0.00, 127807.00, 82532.16, 598358.16, NULL, '', 0, NULL, '2025-11-11 22:08:54', '2025-11-11 22:08:54'),
(103, 'PED-20251111-5767', 13, NULL, 'confirmado', '2025-11-11 00:00:00', '2025-11-18', 928923.00, 0.00, 127807.00, 128178.56, 929294.56, NULL, '', 0, NULL, '2025-11-11 22:09:35', '2025-11-11 22:09:35'),
(104, 'PED-20251115-8879', 13, NULL, 'confirmado', '2025-11-15 00:00:00', '2025-11-21', 279493.00, 0.00, 54979.00, 35922.24, 260436.24, NULL, '', 0, NULL, '2025-11-15 18:02:36', '2025-11-15 18:02:36'),
(105, 'PED-20251115-8589', 13, NULL, 'confirmado', '2025-11-15 13:11:05', '2025-11-21', 379353.00, 0.00, 54979.00, 51899.84, 376273.84, NULL, '', 0, NULL, '2025-11-15 18:11:05', '2025-11-15 18:11:05'),
(106, 'PED-20251121-3397', 13, NULL, 'enviado', '2025-11-21 15:10:22', '2025-11-28', 279493.00, 0.00, 54979.00, 35922.24, 260436.24, NULL, '', 0, NULL, '2025-11-21 20:10:22', '2025-11-21 20:13:26'),
(107, 'COT-20251125-5485', 13, NULL, 'borrador', '2025-11-24 18:19:06', '2025-12-04', 11445364.00, 0.00, 433925.80, 1761830.11, 12773268.31, NULL, 'Ninguna', 0, NULL, '2025-11-24 23:19:06', '2025-11-27 04:48:26'),
(108, 'PED-20251125-3764', 13, NULL, 'entregado', '2025-11-24 18:19:10', '2025-12-04', 11445364.00, 0.00, 433925.80, 1761830.11, 12773268.31, NULL, 'Ninguna', 0, NULL, '2025-11-24 23:19:10', '2025-11-27 04:48:08');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `unidad_medida` varchar(20) NOT NULL DEFAULT 'und' COMMENT 'Unidad de medida del producto (und, mt, kg, etc.)',
  `unidad_empaque` int(11) NOT NULL DEFAULT 1 COMMENT 'Cantidad de unidades por empaque'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `codigo`, `descripcion`, `precio`, `grupo_id`, `created_at`, `updated_at`, `unidad_medida`, `unidad_empaque`) VALUES
(1, '2903785', 'BRIDA AJUSTABLE PVC SCH 80 PSI 150 6', 140476.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(2, '2000225', 'ABRAZADERA INOX SILLA KIT 160MM', 47500.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(3, '2000226', 'ABRAZADERA INOX SILLA KIT 200MM', 52020.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(4, '2000227', 'ABRAZADERA INOX SILLA KIT 250MM', 59556.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(5, '2000228', 'ABRAZADERA INOX SILLA KIT 315MM', 67564.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(6, '2903140', 'ACOPLE FLEX 1/2 A 1/2 LAVAMANOS 40 CM (PARA PRUEBAS)', 2716.00, 3, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(7, '2907231', 'ADAPTADOR HEMBRA CPVC ROSCA METÁLICA 1PULG', 99860.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(8, '2907229', 'ADAPTADOR HEMBRA CPVC ROSCA METÁLICA  1/2PULG', 28529.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(9, '2907233', 'ADAPTADOR HEMBRA CPVC ROSCA METÁLICA 1-1/2PULG', 238643.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(10, '2907232', 'ADAPTADOR HEMBRA CPVC ROSCA METÁLICA 1-1/4PULG', 145847.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(11, '2907234', 'ADAPTADOR HEMBRA CPVC ROSCA METÁLICA 2PULG', 286843.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(12, '2907230', 'ADAPTADOR HEMBRA CPVC ROSCA METÁLICA  3/4PULG', 33103.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(13, '2907421', 'ADAPTADOR HEMBRA CPVC SCH 80 1PULG', 47305.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(14, '2907422', 'ADAPTADOR HEMBRA CPVC SCH 80 1.1/4PULG', 60429.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(15, '2907419', 'ADAPTADOR HEMBRA CPVC SCH 80  1/2PULG', 21597.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(16, '2907423', 'ADAPTADOR HEMBRA CPVC SCH 80 1-1/2PULG', 70333.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(17, '2907424', 'ADAPTADOR HEMBRA CPVC SCH 80 2PULG', 108356.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(18, '2907425', 'ADAPTADOR HEMBRA CPVC SCH 80 2.1/2PULG', 232450.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(19, '2907426', 'ADAPTADOR HEMBRA CPVC SCH 80 3PULG', 475913.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(20, '2907420', 'ADAPTADOR HEMBRA CPVC SCH 80  3/4PULG', 23864.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(21, '2907427', 'ADAPTADOR HEMBRA CPVC SCH 80 4PULG', 855047.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(22, '2900691', 'ADAPTADOR HEMBRA PF+UAD 1/2PULG', 3500.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(23, '2907509', 'ADAPTADOR HEMBRA PVC SCH 80 1.1/2PULG', 44299.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(24, '2907508', 'ADAPTADOR HEMBRA PVC SCH 80 1.1/4PULG', 36120.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(25, '2907510', 'ADAPTADOR HEMBRA PVC SCH 80 2PULG', 77232.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(26, '2907511', 'ADAPTADOR HEMBRA PVC SCH 80 2.1/2PULG', 122019.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(27, '2907512', 'ADAPTADOR HEMBRA PVC SCH 80 3PULG', 136359.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(28, '2907513', 'ADAPTADOR HEMBRA PVC SCH 80 4PULG', 236118.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(29, '2900714', 'ADAPTADOR HEMBRA PVC-P 1/2PULG', 671.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(30, '2900706', 'ADAPTADOR HEMBRA PVC-P 1-1/4PULG', 4401.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(31, '2900702', 'ADAPTADOR HEMBRA PVC-P 1-1/2PULG', 7444.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(32, '2900724', 'ADAPTADOR HEMBRA PVC-P 2PULG', 13252.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(33, '2900740', 'ADAPTADOR HEMBRA PVC-P 3/4PULG', 1208.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(34, '2900678', 'ADAPTADOR LIMPIEZA PVC-S 2PULG', 9270.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(35, '2900680', 'ADAPTADOR LIMPIEZA PVC-S 3PULG', 19936.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(36, '2900682', 'ADAPTADOR LIMPIEZA PVC-S 4PULG', 29640.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(37, '2900686', 'ADAPTADOR LIMPIEZA PVC-S 6PULG', 77155.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(38, '2910331', 'ADAPTADOR MACHO CPVC  1/2PULG', 2299.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(39, '2910333', 'ADAPTADOR MACHO CPVC 1PULG', 17058.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(40, '2910334', 'ADAPTADOR MACHO CPVC 1.1/4PULG', 28423.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(41, '2910335', 'ADAPTADOR MACHO CPVC 1-1/2PULG', 35336.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(42, '2903734', 'ADAPTADOR MACHO CPVC 2PULG', 61615.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(43, '2910332', 'ADAPTADOR MACHO CPVC  3/4PULG', 2827.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(44, '2907237', 'ADAPTADOR MACHO CPVC ROSCA METÁLICA 1PULG', 72716.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(45, '2907238', 'ADAPTADOR MACHO CPVC ROSCA METÁLICA 1.1/4PULG', 200555.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(46, '2907235', 'ADAPTADOR MACHO CPVC ROSCA METÁLICA  1/2PULG', 23408.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(47, '2907239', 'ADAPTADOR MACHO CPVC ROSCA METÁLICA 1-1/2PULG', 264822.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(48, '2907240', 'ADAPTADOR MACHO CPVC ROSCA METÁLICA 2PULG', 415655.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(49, '2907236', 'ADAPTADOR MACHO CPVC ROSCA METÁLICA  3/4PULG', 41969.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(50, '2907429', 'ADAPTADOR MACHO CPVC SCH 80 1PULG', 35469.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(51, '2907430', 'ADAPTADOR MACHO CPVC SCH 80 1.1/4PULG', 90560.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(52, '2906862', 'ADAPTADOR MACHO CPVC SCH 80  1/2PULG', 19318.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(53, '2906863', 'ADAPTADOR MACHO CPVC SCH 80 1-1/2PULG', 109817.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(54, '2906864', 'ADAPTADOR MACHO CPVC SCH 80 2PULG', 192284.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(55, '2907431', 'ADAPTADOR MACHO CPVC SCH 80 2.1/2PULG', 293464.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(56, '2907432', 'ADAPTADOR MACHO CPVC SCH 80 3PULG', 394642.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(57, '2907428', 'ADAPTADOR MACHO CPVC SCH 80  3/4PULG', 23560.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(58, '2907433', 'ADAPTADOR MACHO CPVC SCH 80 4PULG', 663278.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(59, '2900755', 'ADAPTADOR MACHO PF+UAD 1/2PULG', 3429.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(60, '2903146', 'ADAPTADOR MACHO PF+UAD 3/4', 28625.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(61, '2911122', 'ADAPTADOR MACHO PVC SCH 80 1PULG', 24579.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(62, '2907515', 'ADAPTADOR MACHO PVC SCH 80 1.1/2PULG', 38074.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(63, '2907514', 'ADAPTADOR MACHO PVC SCH 80 1.1/4PULG', 28397.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(64, '2907516', 'ADAPTADOR MACHO PVC SCH 80 2PULG', 60265.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(65, '2907517', 'ADAPTADOR MACHO PVC SCH 80 2.1/2PULG', 67032.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(66, '2907518', 'ADAPTADOR MACHO PVC SCH 80 3PULG', 74400.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(67, '2907519', 'ADAPTADOR MACHO PVC SCH 80 4PULG', 135298.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(68, '2900762', 'ADAPTADOR MACHO PVC-P 1PULG', 2248.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(69, '2900779', 'ADAPTADOR MACHO PVC-P 1/2PULG', 594.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(70, '2900767', 'ADAPTADOR MACHO PVC-P 1-1/2PULG', 5546.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(71, '2900771', 'ADAPTADOR MACHO PVC-P 1-1/4PULG', 4733.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(72, '2900784', 'ADAPTADOR MACHO PVC-P 2PULG', 7923.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(73, '2900790', 'ADAPTADOR MACHO PVC-P 2.1/2PULG', 20601.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(74, '2900794', 'ADAPTADOR MACHO PVC-P 3PULG', 31145.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(75, '2900802', 'ADAPTADOR MACHO PVC-P 3/4PULG', 1077.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(76, '2900807', 'ADAPTADOR MACHO PVC-P 4PULG', 57292.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(77, '2902965', 'ADAPTADOR NOVAFORT 110X4PULGSANIT', 18030.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(78, '2902604', 'ADAPTADOR NOVAFORT 160X6PULG SANIT', 47473.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(79, '2902605', 'ADAPTADOR NOVAFORT 200X6PULGSANIT', 67126.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(80, '2904914', 'ADAPTADOR NOVAFORT 200X8PULGSANIT', 225835.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(81, '2904915', 'ADAPTADOR NOVAFORT 250X10PULGSANIT', 519454.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(82, '2900669', 'ADAPTADOR NOVAFORT SANITARIA 4 - 110', 23462.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(83, '2900670', 'ADAPTADOR NOVAFORT SANITARIA 6 - 160', 46236.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(84, '2900671', 'ADAPTADOR NOVAFORT SANITARIA 8 - 200', 89930.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(85, '2905390', 'ADAPTADORES LIMPIEZA PVCS 10PULG', 2658520.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(86, '2905389', 'ADAPTADORES LIMPIEZA PVCS 8PULG', 979512.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(87, '2906396', 'ADHESIVO EPÓXICO NOVAFORT 2 LITROS', 280238.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(88, '2906320', 'ADHESIVO EPÓXICO NOVAFORT 1 LITRO', 144200.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(89, '2907434', 'BRIDA AJUSTABLE CPVC SCH 80  1-1/2PULG', 110567.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(90, '2907435', 'BRIDA AJUSTABLE CPVC SCH 80  2PULG', 133646.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(91, '2907436', 'BRIDA AJUSTABLE CPVC SCH 80  2-1/2PULG', 277146.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(92, '2907437', 'BRIDA AJUSTABLE CPVC SCH 80  3PULG', 315814.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(93, '2907438', 'BRIDA AJUSTABLE CPVC SCH 80  4PULG', 354483.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(94, '2907439', 'BRIDA AJUSTABLE CPVC SCH 80  6PULG', 603575.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(95, '2903787', 'BRIDA AJUSTABLE PVC SCH 80 (150 PSI) 10PULG', 594009.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(96, '2903788', 'BRIDA AJUSTABLE PVC SCH 80 (150 PSI) 12PULG', 647286.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(97, '2903783', 'BRIDA AJUSTABLE PVC SCH 80 PSI 150 3PULG', 54576.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(98, '2903784', 'BRIDA AJUSTABLE PVC SCH 80 PSI 150 4PULG', 79237.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(99, '2903786', 'BRIDA AJUSTABLE PVC SCH 80 (150 PSI) 8PULG', 238705.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(100, '2907520', 'BRIDA AJUSTABLE PVC SCH 80 1 1/2PULG', 41164.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(101, '2907521', 'BRIDA AJUSTABLE PVC SCH 80 (150 PSI) 2PULG', 56040.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(102, '2910946', 'BRIDA FLEXIBLE 3PULG', 58143.00, 10, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(103, '2910947', 'BRIDA FLEXIBLE CORTA 4PULG', 58363.00, 10, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(104, '2906865', 'BUJE CPVC SCH 80 1PULG x 1/2PULG', 26694.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(105, '2907441', 'BUJE CPVC SCH 80 1PULG x 3/4PULG', 38343.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(106, '2907447', 'BUJE CPVC SCH 80 1.1/2PULG x 1PULG', 69610.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(107, '2907448', 'BUJE CPVC SCH 80 1.1/2PULG x 1.1/4PULG', 69610.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(108, '2907445', 'BUJE CPVC SCH 80 1.1/2PULG x 1/2PULG', 80134.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(109, '2907446', 'BUJE CPVC SCH 80 1.1/2PULG x 3/4PULG', 80134.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(110, '2907444', 'BUJE CPVC SCH 80 1.1/4PULG x 1PULG', 70896.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(111, '2907442', 'BUJE CPVC SCH 80 1.1/4PULG x 1/2PULG', 70899.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(112, '2907443', 'BUJE CPVC SCH 80 1.1/4PULG x 3/4PULG', 70896.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(113, '2907451', 'BUJE CPVC SCH 80  2PULG x 1PULG', 103551.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(114, '2906866', 'BUJE CPVC SCH 80  2PULG x 1.1/2PULG', 97733.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(115, '2907452', 'BUJE CPVC SCH 80 2PULG x 1.1/4PULG', 110199.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(116, '2907449', 'BUJE CPVC SCH 80 2PULG x 1/2PULG', 103551.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(117, '2907450', 'BUJE CPVC SCH 80 2PULG x 3/4PULG', 103551.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(118, '2906868', 'BUJE CPVC SCH 80  2.1/2PULG x 1.1/2PULG', 93652.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(119, '2907453', 'BUJE CPVC SCH 80  2.1/2PULG x 2PULG', 104404.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(120, '2906869', 'BUJE CPVC SCH 80  3 x 2 1/2PULG', 205137.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(121, '2907180', 'BUJE CPVC SCH 80 3PULG x 2PULG', 159095.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(122, '2907440', 'BUJE CPVC SCH 80   3/4PULG x 1/2PULG', 16591.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(123, '2906870', 'BUJE CPVC SCH 80  4PULG x 2PULG', 212022.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(124, '2907181', 'BUJE CPVC SCH 80  4PULG x 3PULG', 175817.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(125, '2907455', 'BUJE CPVC SCH 80  6PULG x 4PULG', 338066.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(126, '2907524', 'BUJE PVC SCH 80 1.1/4 x 1', 16698.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(127, '2907522', 'BUJE PVC SCH 80 1.1/4 x 1/2', 17392.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(128, '2907523', 'BUJE PVC SCH 80 1.1/4 x 3/4', 16698.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(129, '2907527', 'BUJE PVC SCH 80 1-1/2 x 1', 21052.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(130, '2907525', 'BUJE PVC SCH 80 1-1/2 x 1/2', 21446.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(131, '2907528', 'BUJE PVC SCH 80 1-1/2 x 1-1/4', 21449.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(132, '2907526', 'BUJE PVC SCH 80 1-1/2 x 3/4', 21446.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(133, '2907531', 'BUJE PVC SCH 80 2 x 1', 31184.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(134, '2907533', 'BUJE PVC SCH 80 2 x 1.1/2', 30771.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(135, '2907532', 'BUJE PVC SCH 80 2 x 1.1/4', 29241.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(136, '2907529', 'BUJE PVC SCH 80 2 x 1/2', 31236.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(137, '2907530', 'BUJE PVC SCH 80 2 x 3/4', 30575.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(138, '2907534', 'BUJE PVC SCH 80 2.1/2 x 1', 75586.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(139, '2907536', 'BUJE PVC SCH 80 2.1/2 x 1.1/2', 54145.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(140, '2907535', 'BUJE PVC SCH 80 2.1/2 x 1.1/4', 52874.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(141, '2907537', 'BUJE PVC SCH 80 2.1/2 x 2', 50770.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(142, '2907538', 'BUJE PVC SCH 80 3 x 1', 89649.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(143, '2907540', 'BUJE PVC SCH 80 3 x 1.1/2', 84222.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(144, '2907539', 'BUJE PVC SCH 80 3 x 1.1/4', 89649.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(145, '2907541', 'BUJE PVC SCH 80 3 x 2', 80557.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(146, '2907542', 'BUJE PVC SCH 80 3 x 2.1/2', 82675.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(147, '2907543', 'BUJE PVC SCH 80 4 x 2', 118972.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(148, '2907544', 'BUJE PVC SCH 80 4 x 3', 118972.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(149, '2907545', 'BUJE PVC SCH 80 6 x 3', 161005.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(150, '2909789', 'BUJE PVC SCH 80 6 x 4', 155107.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(151, '2907546', 'BUJE PVC SCH 80 8 x 6', 377327.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(152, '2900846', 'BUJE ROSCADO PVC-P 1PULG x 1/2PULG', 3390.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(153, '2900854', 'BUJE ROSCADO PVC-P 1PULG x 3/4PULG', 3390.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(154, '2900863', 'BUJE ROSCADO PVC-P 1.1/2PULG x 1PULG', 6778.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(155, '2900871', 'BUJE ROSCADO PVC-P 1.1/2PULG x 1.1/4PULG', 6778.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(156, '2900878', 'BUJE ROSCADO PVC-P 1.1/2PULG x 1/2PULG', 6778.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(157, '2900887', 'BUJE ROSCADO PVC-P 1.1/2PULG x 3/4PULG', 6778.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(158, '2900895', 'BUJE ROSCADO PVC-P 1.1/4PULG x 1PULG', 5688.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(159, '2900903', 'BUJE ROSCADO PVC-P 1.1/4PULG x 1/2PULG', 5688.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(160, '2900910', 'BUJE ROSCADO PVC-P 1.1/4PULG x 3/4PULG', 5688.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(161, '2900921', 'BUJE ROSCADO PVC-P  1/2PULG x 3/8PULG', 1551.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(162, '2900924', 'BUJE ROSCADO PVC-P 2PULG x 1PULG', 10902.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(163, '2900933', 'BUJE ROSCADO PVC-P 2PULG x 1.1/2PULG', 10902.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(164, '2900942', 'BUJE ROSCADO PVC-P 2PULG x 1.1/4PULG', 10902.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(165, '2900950', 'BUJE ROSCADO PVC-P 2PULG x 1/2PULG', 10902.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(166, '2900956', 'BUJE ROSCADO PVC-P 2PULG x 3/4PULG', 10902.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(167, '2900976', 'BUJE ROSCADO PVC-P 3PULG x 2PULG', 47889.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(168, '2900990', 'BUJE ROSCADO PVC-P 3/4PULG x 1/2PULG', 1945.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(169, '2910350', 'BUJE SOLDADO CPVC  3/4 x 1/2PULG', 2009.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(170, '2910352', 'BUJE SOLDADO CPVC 1 x 1/2PULG', 7945.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(171, '2910354', 'BUJE SOLDADO CPVC 1 x 3/4PULG', 7805.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(172, '2910356', 'BUJE SOLDADO CPVC 1.1/2PULG x 1PULG', 14696.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(173, '2910357', 'BUJE SOLDADO CPVC 1.1/2PULG x 1.1/4PULG', 14659.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(174, '2903742', 'BUJE SOLDADO CPVC 1.1/2PULG x  1/2PULG', 13789.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(175, '2903743', 'BUJE SOLDADO CPVC 1.1/2PULG x  3/4PULG', 14719.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(176, '2903735', 'BUJE SOLDADO CPVC 1.1/4 x  1/2PULG', 11820.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(177, '2910355', 'BUJE SOLDADO CPVC 1.1/4 x 1PULG', 10023.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(178, '2903736', 'BUJE SOLDADO CPVC 1.1/4 x  3/4PULG', 11727.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(179, '2903748', 'BUJE SOLDADO CPVC 2 x 1PULG', 30319.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(180, '2903750', 'BUJE SOLDADO CPVC 2 x 1.1/2PULG', 29703.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(181, '2903749', 'BUJE SOLDADO CPVC 2 X 1.1/4PULG', 29817.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(182, '2903746', 'BUJE SOLDADO CPVC 2 X 1/2PULG', 31990.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(183, '2903747', 'BUJE SOLDADO CPVC 2 X 3/4PULG', 31989.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(184, '2900849', 'BUJE SOLDADO PVC-P 1 x 1/2PULG', 1662.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(185, '2900858', 'BUJE SOLDADO PVC-P 1 x 3/4PULG', 1662.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(186, '2900866', 'BUJE SOLDADO PVC-P 1-1/2PULG x 1PULG', 4928.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(187, '2900882', 'BUJE SOLDADO PVC-P 1-1/2PULG x 1/2PULG', 4928.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(188, '2900875', 'BUJE SOLDADO PVC-P 1-1/2PULG x 1-1/4PULG', 4928.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(189, '2900890', 'BUJE SOLDADO PVC-P 1-1/2PULG x 3/4PULG', 4928.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(190, '2900898', 'BUJE SOLDADO PVC-P 1-1/4PULG x 1PULG', 3192.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(191, '2900906', 'BUJE SOLDADO PVC-P 1-1/4PULG x 1/2PULG', 3192.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(192, '2900914', 'BUJE SOLDADO PVC-P 1-1/4PULG x 3/4PULG', 3192.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(193, '2900928', 'BUJE SOLDADO PVC-P 2PULG x 1PULG', 7534.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(194, '2900952', 'BUJE SOLDADO PVC-P 2PULG x 1/2PULG', 7534.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(195, '2900937', 'BUJE SOLDADO PVC-P 2PULG x 1-1/2PULG', 7534.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(196, '2900945', 'BUJE SOLDADO PVC-P 2PULG x 1-1/4PULG', 7534.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(197, '2900959', 'BUJE SOLDADO PVC-P 2PULG x 3/4PULG', 7534.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(198, '2900966', 'BUJE SOLDADO PVC-P 2-1/2PULG x 1-1/2PULG', 18898.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(199, '2900971', 'BUJE SOLDADO PVC-P 2-1/2PULG x 2PULG', 17748.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(200, '2900979', 'BUJE SOLDADO PVC-P 3PULG x 2PULG', 27136.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(201, '2900986', 'BUJE SOLDADO PVC-P 3PULG x 2.1/2PULG', 27136.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(202, '2900995', 'BUJE SOLDADO PVC-P 3/4PULG x 1/2PULG', 835.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(203, '2901003', 'BUJE SOLDADO PVC-P 4PULG x 2PULG', 42792.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(204, '2901009', 'BUJE SOLDADO PVC-P 4PULG x 2.1/2PULG', 42792.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(205, '2901014', 'BUJE SOLDADO PVC-P 4PULG x 3PULG', 42792.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(206, '2903813', 'BUJE SOLDADO PVC-S 10PULG x 6PULG', 587117.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(207, '2903814', 'BUJE SOLDADO PVC-S 10PULG x 8PULG', 641331.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(208, '2901021', 'BUJE SOLDADO PVC-S 2PULG x 1-1/2PULG', 3468.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(209, '2901026', 'BUJE SOLDADO PVC-S 3PULG x 1-1/2PULG', 7982.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(210, '2901028', 'BUJE SOLDADO PVC-S 3PULG x 2PULG', 7530.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(211, '2901030', 'BUJE SOLDADO PVC-S 4PULG x 2PULG', 13266.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(212, '2901033', 'BUJE SOLDADO PVC-S 4PULG x 3PULG', 13266.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(213, '2901036', 'BUJE SOLDADO PVC-S 6PULG x 4PULG', 49882.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(214, '2903811', 'BUJE SOLDADO PVC-S 8PULG x 4PULG', 188476.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(215, '2909775', 'BUJE SOLDADO PVC-S 8PULG x 6PULG', 152232.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(216, '2000245', 'CAUCHO  KIT SILLA TEE - NOVAFORT 160X110', 19446.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(217, '2000246', 'CAUCHO  KIT SILLA TEE - NOVAFORT 200 X110', 24101.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(218, '2000247', 'CAUCHO  KIT SILLA TEE - NOVAFORT 200 X160', 24101.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(219, '2000248', 'CAUCHO  KIT SILLA TEE - NOVAFORT 250X110', 26479.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(220, '2000249', 'CAUCHO  KIT SILLA TEE - NOVAFORT 250X160', 24082.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(221, '2000250', 'CAUCHO  KIT SILLA TEE - NOVAFORT 315X110', 28078.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(222, '2000251', 'CAUCHO  KIT SILLA TEE - NOVAFORT 315X160', 25699.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(223, '2000252', 'CAUCHO KIT SILLA YEE - NOVAFORT 160X110', 19446.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(224, '2000253', 'CAUCHO KIT SILLA YEE - NOVAFORT 200 X110', 24101.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(225, '2000254', 'CAUCHO KIT SILLA YEE - NOVAFORT 200 X160', 24101.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(226, '2000255', 'CAUCHO KIT SILLA YEE - NOVAFORT 250X110', 26479.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(227, '2000256', 'CAUCHO KIT SILLA YEE - NOVAFORT 250X160', 24082.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(228, '2000257', 'CAUCHO KIT SILLA YEE - NOVAFORT 315X110', 28078.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(229, '2000258', 'CAUCHO KIT SILLA YEE - NOVAFORT 315X160', 30571.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(230, '2000401', 'CAUCHO KIT SILLA YEE S4 DALIAN - NOVAFORT 200X160', 21912.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(231, '2000402', 'CAUCHO KIT SILLA YEE S4 DALIAN - NOVAFORT 250X160', 21894.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(232, '2000400', 'CAUCHO KIT SILLA YEE S4 DALIAN - NOVAFORT 315X160', 23363.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(233, '2908186', 'CINTA SELLANTE PREMIUN PARA ROSCA (TEFLON) 12MM X 12M (empaque de 12 unidades) - PAVCO', 38864.00, 9, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(234, '2908187', 'CINTA SELLANTE PREMIUN PARA ROSCA (TEFLON) 18MM X 20M (empaque de 6 unidades) - PAVCO', 40857.00, 9, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(235, '2909556', 'CINTA SELLANTE PREMIUN PARA ROSCA (TEFLON) 18MM X 50M - PAVCO', 13303.00, 9, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(236, '2909557', 'CINTA SELLANTE PREMIUN PARA ROSCA (TEFLON) 24MM X 50M - PAVCO', 17191.00, 9, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(237, '2903559', 'CLICK INSERTA 250 X 160', 279510.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(238, '2903478', 'CLICK INSERTA 315 X 160', 320826.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(239, '2903634', 'CLICK INSERTA 400 X 160', 342389.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(240, '2903635', 'CLICK INSERTA 450-500 X 160', 386733.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(241, '2901162', 'CODO 22° PVC-S 2PULG C x C ', 6174.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(242, '2901163', 'CODO 22° PVC-S 2PULG C x E ', 6174.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(243, '2901164', 'CODO 22° PVC-S 3PULG C x C ', 11741.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(244, '2901165', 'CODO 22° PVC-S 3PULG C x E ', 11741.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(245, '2901167', 'CODO 22° PVC-S 4PULG C x C ', 19491.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(246, '2901168', 'CODO 22° PVC-S 4PULG C x E ', 19491.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(247, '2903450', 'CODO 22° PVC-S 6PULG C x C ', 187324.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(248, '2901045', 'CODO 45 NOVAFORT 110MM - 4PULG', 30921.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(249, '2901046', 'CODO 45 NOVAFORT 160MM - 6PULG', 74233.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(250, '2902687', 'CODO 45 PREFABRICADO NOVAFORT 200MM', 213225.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(251, '2902688', 'CODO 45 PREFABRICADO NOVAFORT 250MM', 413068.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(252, '2903071', 'CODO 45 PREFABRICADO NOVAFORT 315MM', 634823.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(253, '2910363', 'CODO 45° CPVC 1PULG', 10047.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(254, '2910365', 'CODO 45° CPVC 1.1/2PULG', 36896.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(255, '2910364', 'CODO 45° CPVC 1.1/4PULG', 26046.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(256, '2910360', 'CODO 45° CPVC  1/2PULG', 2112.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(257, '2903753', 'CODO 45° CPVC 2PULG', 90319.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(258, '2910361', 'CODO 45° CPVC  3/4PULG', 3819.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(259, '2907458', 'CODO 45° CPVC SCH 80  1PULG', 39792.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(260, '2907459', 'CODO 45° CPVC SCH 80  1.1/4PULG', 78058.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(261, '2907456', 'CODO 45° CPVC SCH 80   1/2PULG', 17307.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(262, '2907460', 'CODO 45° CPVC SCH 80  1-1/2PULG', 80079.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(263, '2907461', 'CODO 45° CPVC SCH 80  2PULG', 89889.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(264, '2907462', 'CODO 45° CPVC SCH 80  2.1/2PULG', 183715.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(265, '2907182', 'CODO 45° CPVC SCH 80 3PULG', 189291.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(266, '2907457', 'CODO 45° CPVC SCH 80   3/4PULG', 25012.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(267, '2907183', 'CODO 45° CPVC SCH 80  4PULG', 348535.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(268, '2907463', 'CODO 45° CPVC SCH 80  6PULG', 888392.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(269, '2911385', 'CODO 45° H.D. C900 4PULG', 998520.00, 27, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(270, '2911120', 'CODO 45° PVC SCH 80 1PULG', 13070.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(271, '2907548', 'CODO 45° PVC SCH 80 1.1/2PULG', 42563.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(272, '2907547', 'CODO 45° PVC SCH 80 1.1/4PULG', 32393.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(273, '2907549', 'CODO 45° PVC SCH 80 2PULG', 50613.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(274, '2907550', 'CODO 45° PVC SCH 80 2.1/2PULG', 115851.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(275, '2907551', 'CODO 45° PVC SCH 80 3PULG', 129429.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(276, '2907552', 'CODO 45° PVC SCH 80 4PULG', 222769.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(277, '2909790', 'CODO 45° PVC SCH 80 6PULG', 295310.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(278, '2907553', 'CODO 45° PVC SCH 80 8PULG', 608245.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(279, '2901074', 'CODO 45° PVC-P  1/2PULG', 1424.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(280, '2901096', 'CODO 45° PVC-P  3/4PULG', 2276.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(281, '2901064', 'CODO 45° PVC-P 1PULG', 4334.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(282, '2901069', 'CODO 45° PVC-P 1-1/2PULG', 10509.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(283, '2901073', 'CODO 45° PVC-P 1-1/4PULG', 7838.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(284, '2901083', 'CODO 45° PVC-P 2PULG', 17378.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(285, '2901087', 'CODO 45° PVC-P 2.1/2PULG', 48988.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(286, '2901090', 'CODO 45° PVC-P 3PULG', 55950.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(287, '2901100', 'CODO 45° PVC-P 4PULG', 119036.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(288, '2901179', 'CODO 45° PVC-S  1-1/2PULG C x C ', 4375.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(289, '2901181', 'CODO 45° PVC-S  2PULG C x C ', 5307.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(290, '2901183', 'CODO 45° PVC-S  2PULG C x E ', 5307.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(291, '2901185', 'CODO 45° PVC-S  3PULG C x C ', 11407.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(292, '2901187', 'CODO 45° PVC-S  3PULG C x E ', 11407.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(293, '2901189', 'CODO 45° PVC-S  4PULG C x C ', 20091.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(294, '2901191', 'CODO 45° PVC-S  4PULG C x E ', 20091.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(295, '2901193', 'CODO 45° PVC-S  6PULG C x C ', 73064.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(296, '2901195', 'CODO 45° PVC-S  6PULG C x E ', 73064.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(297, '2909776', 'CODO 45° PVC-S  8PULG C x C ', 216538.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(298, '2903804', 'CODO 45° PVC-S 10PULG C x C ', 683792.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(299, '2903806', 'CODO 45° PVC-S 10PULG C x E ', 691624.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(300, '2901180', 'CODO 45° PVC-S  1-1/2PULG C x E ', 4375.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(301, '2903805', 'CODO 45° PVC-S  8PULG C x E ', 254014.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(302, '2902689', 'CODO 90 PREFABRICADO NOVAFORT 200MM', 216729.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(303, '2902690', 'CODO 90 PREFABRICADO NOVAFORT 250MM', 414462.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(304, '2902691', 'CODO 90 PREFABRICADO NOVAFORT 315MM', 634823.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(305, '2903061', 'CODO 90 PREFABRICADO NOVAFORT 355MM', 1553880.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(306, '2902692', 'CODO 90 PREFABRICADO NOVAFORT 400MM', 2077080.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(307, '2902693', 'CODO 90 PREFABRICADO NOVAFORT 450MM', 2651140.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(308, '2902723', 'CODO 90 PREFABRICADO NOVAFORT 500MM', 2619920.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(309, '2910372', 'CODO 90° CPVC  1/2PULG', 2112.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(310, '2910373', 'CODO 90° CPVC  3/4PULG', 3819.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(311, '2910374', 'CODO 90° CPVC 1PULG', 11215.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(312, '2910377', 'CODO 90° CPVC 1.1/2PULG', 40097.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(313, '2910375', 'CODO 90° CPVC 1.1/4PULG', 22764.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(314, '2903756', 'CODO 90° CPVC 2PULG', 78799.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(315, '2907466', 'CODO 90° CPVC SCH 80 1PULG', 28913.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(316, '2907467', 'CODO 90° CPVC SCH 80  1.1/4PULG', 55450.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(317, '2907464', 'CODO 90° CPVC SCH 80   1/2PULG', 14607.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(318, '2907468', 'CODO 90° CPVC SCH 80  1-1/2PULG', 69829.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(319, '2907469', 'CODO 90° CPVC SCH 80 2PULG', 116024.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(320, '2906871', 'CODO 90° CPVC SCH 80 2.1/2PULG', 191107.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(321, '2907465', 'CODO 90° CPVC SCH 80   3/4PULG', 16927.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(322, '2906873', 'CODO 90° CPVC SCH 80  4PULG', 352679.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(323, '2907470', 'CODO 90° CPVC SCH 80  6PULG', 671828.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(324, '2911388', 'CODO 90° H.D. C900 4PULG', 1176730.00, 27, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(325, '2901051', 'CODO 90 NOVAFORT 110MM - 4PULG', 61296.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(326, '2901052', 'CODO 90 NOVAFORT 160MM - 6PULG', 143889.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(327, '2911119', 'CODO 90° PVC SCH 80 1PULG', 12543.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(328, '2907555', 'CODO 90° PVC SCH 80 1.1/2PULG', 17413.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(329, '2907554', 'CODO 90° PVC SCH 80 1.1/4PULG', 17412.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(330, '2907556', 'CODO 90° PVC SCH 80 2PULG', 23698.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(331, '2907557', 'CODO 90° PVC SCH 80 2.1/2PULG', 49253.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(332, '2907558', 'CODO 90° PVC SCH 80 3PULG', 58285.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(333, '2907559', 'CODO 90° PVC SCH 80 4PULG', 82038.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(334, '2909791', 'CODO 90° PVC SCH 80 6PULG', 245734.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(335, '2907560', 'CODO 90° PVC SCH 80 8PULG', 643416.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(336, '2901122', 'CODO 90° PVC-P  1/2PULG', 862.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(337, '2901144', 'CODO 90° PVC-P  3/4PULG', 1380.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(338, '2901105', 'CODO 90° PVC-P 1PULG', 2699.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(339, '2901110', 'CODO 90° PVC-P 1-1/2PULG', 9678.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(340, '2901114', 'CODO 90° PVC-P 1-1/4PULG', 5183.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(341, '2901127', 'CODO 90° PVC-P 2PULG', 15862.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(342, '2901132', 'CODO 90° PVC-P 2.1/2PULG', 45680.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(343, '2901137', 'CODO 90° PVC-P 3PULG', 59115.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(344, '2901149', 'CODO 90° PVC-P 4PULG', 128247.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(345, '2903802', 'CODO 90° PVC-S 10PULG C x C ', 822302.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(346, '2901209', 'CODO 90° PVC-S 1-1/2PULG C x C ', 3783.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(347, '2901210', 'CODO 90° PVC-S 1-1/2PULG C x E ', 4622.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(348, '2901213', 'CODO 90° PVC-S 2PULG C x C ', 4433.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(349, '2901214', 'CODO 90° PVC-S 2PULG C x E ', 5467.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(350, '2901217', 'CODO 90° PVC-S 3PULG C x C ', 10270.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(351, '2901218', 'CODO 90° PVC-S 3PULG C x E ', 11889.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(352, '2901221', 'CODO 90° PVC-S 4PULG C x C ', 17858.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(353, '2901222', 'CODO 90° PVC-S 4PULG C x E ', 22017.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(354, '2901224', 'CODO 90° PVC-S 6PULG C x C ', 151167.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(355, '2901226', 'CODO 90° PVC-S 6PULG C x E ', 151167.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(356, '2909777', 'CODO 90° PVC-S 8PULG C x C', 241051.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(357, '2908617', 'CODO 90º PVC UP RADIO CORTO 2PULG', 79621.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(358, '2909095', 'CODO 90º PVC UP RADIO CORTO 3PULG', 119895.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(359, '2909096', 'CODO 90º PVC UP RADIO CORTO 4PULG', 158823.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(360, '2906872', 'CODO CPVC SCH 80 3PULG', 233738.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(361, '2901156', 'CODO REVENTILADO 3PULG x 2PULG', 31383.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(362, '2901157', 'CODO REVENTILADO 4PULG x 2PULG', 42018.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(363, '2901791', 'CODO 90° ROS - SOLD PVC-P 1/2PULG', 2141.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(364, '2904567', 'COLLAR DE DERIVACION CON INSERTO METALICO 2PULG X 1/2PULG', 20606.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(365, '2904569', 'COLLAR DE DERIVACION CON INSERTO METALICO 3PULG x 1/2PULG', 29371.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(366, '2904570', 'COLLAR DE DERIVACION CON INSERTO METALICO 4PULG X 1/2PULG', 29884.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(367, '2904571', 'COLLAR DE DERIVACION CON INSERTO METALICO 6PULG X 1/2PULG', 37988.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(368, '2901231', 'COLLAR DERIVACION UNION MECANICA SNAP 2PULG x 3/4PULG', 13438.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(369, '2901239', 'COLLAR DERIVACION UNION MECANICA SNAP 3PULG x 3/4PULG', 25847.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(370, '2901243', 'COLLAR DERIVACION UNION MECANICA SNAP 4PULG x 3/4PULG', 28413.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(371, '2901247', 'COLLAR DERIVACION UNION MECANICA SNAP 6PULG x 3/4PULG', 33324.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(372, '2900186', 'CONDUGAS BLANCO (recubrimiento TB - Rollo de 50m)', 141699.00, 18, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(373, '2903070', 'CONECTOR NOVA- FORT CONCRETO 160X6', 99379.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(374, '2902606', 'CONECTOR NOVA- FORT CONCRETO 200X8PULG', 102163.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(375, '2903479', 'COPA SIERRA CLICK 160', 2043700.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(376, '2901254', 'ENTRADA TANQUE PLÁSTICO PVC-P 1/2PULG', 8056.00, 26, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(377, '2000281', 'HIDROSELLO NOVAFORT 110MM S8', 3581.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(378, '2000282', 'HIDROSELLO NOVAFORT 160 MM S8 Y S4', 5817.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(379, '2000283', 'HIDROSELLO NOVAFORT 200 MM S8 Y S4', 10513.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(380, '2000845', 'HIDROSELLO NOVAFORT 24PULG S4', 138989.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(381, '2000390', 'HIDROSELLO NOVAFORT 250 MM S4', 19087.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(382, '2000284', 'HIDROSELLO NOVAFORT 250 MM S8', 18362.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(383, '2000358', 'HIDROSELLO NOVAFORT 27PULG S4', 208263.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(384, '2000359', 'HIDROSELLO NOVAFORT 30PULG S4', 272791.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(385, '2000285', 'HIDROSELLO NOVAFORT 315 MM S8 Y S4', 38521.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(386, '2000541', 'HIDROSELLO NOVAFORT 33', 350573.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(387, '2000386', 'HIDROSELLO NOVAFORT 355 MM S8 Y S4', 40440.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(388, '2000542', 'HIDROSELLO NOVAFORT 36', 409396.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(389, '2000734', 'HIDROSELLO NOVAFORT 39', 496001.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(390, '2000391', 'HIDROSELLO NOVAFORT 400 MM S4', 78445.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(391, '2000286', 'HIDROSELLO NOVAFORT 400 MM S8', 78445.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(392, '2000751', 'HIDROSELLO NOVAFORT 42', 558859.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(393, '2000287', 'HIDROSELLO NOVAFORT 450 MM S8', 113392.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(394, '2000288', 'HIDROSELLO NOVAFORT 500 MM S8', 127565.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(395, '2901258', 'JUNTA DE EXPANSION PVC-S 3PULG', 45664.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(396, '2901260', 'JUNTA DE EXPANSION PVC-S 4PULG', 52134.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(397, '2902730', 'JUNTA DE EXPANSION PVC-S 6PULG', 104481.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(398, '2901264', 'KIT SILLA TEE 160X110', 171378.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(399, '2901265', 'KIT SILLA TEE 200X110', 198092.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(400, '2901266', 'KIT SILLA TEE 200X160', 198092.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(401, '2901267', 'KIT SILLA TEE 250X110', 223937.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(402, '2901268', 'KIT SILLA TEE 250X160', 223937.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(403, '2902731', 'KIT SILLA TEE 315X110', 336719.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(404, '2902732', 'KIT SILLA TEE 315X160', 336719.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(405, '2901269', 'KIT SILLA YEE NOVAFORT 160X110', 171378.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(406, '2901270', 'KIT SILLA YEE NOVAFORT 200X110', 198092.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(407, '2901271', 'KIT SILLA YEE NOVAFORT 200X160', 198092.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(408, '2901272', 'KIT SILLA YEE NOVAFORT 250X110', 223937.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(409, '2901273', 'KIT SILLA YEE NOVAFORT 250X160', 223937.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(410, '2902733', 'KIT SILLA YEE NOVAFORT 315X110', 336719.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(411, '2907849', 'KIT SILLA YEE NOVAFORT 315X160', 336719.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(412, '2901794', 'KIT SILLA YEE NOVAFORT S4 PLUS 200X160', 198092.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1);
INSERT INTO `productos` (`id`, `codigo`, `descripcion`, `precio`, `grupo_id`, `created_at`, `updated_at`, `unidad_medida`, `unidad_empaque`) VALUES
(413, '2901785', 'KIT SILLA YEE NOVAFORT S4 PLUS 250X160', 223937.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(414, '2907850', 'KIT SILLA YEE NOVAFORT S4 PLUS 315X160', 336719.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(415, '2902736', 'LIMPIADOR CPVC 112 Grms 1/32', 11496.00, 11, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(416, '2902735', 'LIMPIADOR CPVC 28 Grms 1/128', 4352.00, 11, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(417, '2902739', 'LIMPIADOR CPVC 300 Grms 1/8', 35822.00, 11, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(418, '2902738', 'LIMPIADOR CPVC 56 Grms 1/64', 7376.00, 11, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(419, '2902737', 'LIMPIADOR PVC 760 GRMS 1/4', 65913.00, 11, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(420, '2902743', 'Lubricante 500 g', 30982.00, 25, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(421, '2901792', 'NIPLE ROSCADO PVC 1/2PULG', 989.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(422, '2903087', 'REDUCCION CONCENTRICA - NOVAFORT 160X110', 125150.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(423, '2903086', 'REDUCCION CONCENTRICA - NOVAFORT 160X4PULG', 113867.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(424, '2903092', 'REDUCCION EXCENTRICA NOVAFORT 160X110', 74462.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(425, '2902999', 'REDUCCION EXCENTRICA NOVAFORT 200 X160', 119636.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(426, '2903549', 'REDUCCION UNION MECANICA 2PULG X 1 1/2PULG', 202408.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(427, '2903547', 'REDUCCION UNION MECANICA 3PULG x 2PULG', 277371.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(428, '2908618', 'REDUCCION UNION MECANICA 4PULG x 3PULG', 102895.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(429, '2908619', 'REDUCCION UNION MECANICA 6PULG X 4PULG', 221866.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(430, '2903471', 'REDUCCION UNION MECANICA 8PULG X 6PULG', 1573370.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(431, '2903545', 'REDUCCION UNION MECANICA SNAP 4PULG x 2PULG', 321804.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(432, '2903288', 'SERRUCHO DE PUNTA (NOVAFORT)', 201068.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(433, '2901281', 'SIFON 135° PVC-S 3PULG', 14804.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(434, '2901283', 'SIFON 135° PVC-S 4PULG', 32358.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(435, '2901291', 'SIFON 180° PVC-S 2PULG CxC (Sin Codo)', 7338.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(436, '2901286', 'SIFON CON TAPÓN PVC-S 1-1/2PULG', 7768.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(437, '2901292', 'SIFON CON TAPÓN PVC-S 2PULG', 11940.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(438, '2910955', 'SIFON DESMONTABLE TIPO P 1.1/2PULG (lavamanos/lavaplatos)', 16075.00, 10, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(439, '2910495', 'SIFON FLEXIBLE PSA 1 1/4PULG', 14544.00, 3, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(440, '2901295', 'SILLA TEE NOVAFORT 160x110', 103304.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(441, '2903085', 'SILLA TEE NOVAFORT 160x160', 103304.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(442, '2901297', 'SILLA TEE NOVAFORT 200x110', 126529.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(443, '2901299', 'SILLA TEE NOVAFORT 200x160', 126529.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(444, '2902985', 'SILLA TEE NOVAFORT 24X160', 572894.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(445, '2902986', 'SILLA TEE NOVAFORT 24X200', 627280.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(446, '2901301', 'SILLA TEE NOVAFORT 250x110', 146719.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(447, '2901303', 'SILLA TEE NOVAFORT 250x160', 146719.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(448, '2903093', 'SILLA TEE NOVAFORT 250X200', 187302.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(449, '2905671', 'SILLA TEE NOVAFORT 27X315', 1000380.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(450, '2902766', 'SILLA TEE NOVAFORT 315x110', 227467.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(451, '2902768', 'SILLA TEE NOVAFORT 315x160', 227467.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(452, '2903094', 'SILLA TEE NOVAFORT 315X200', 307503.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(453, '2903095', 'SILLA TEE NOVAFORT 315X250', 307503.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(454, '2902984', 'SILLA TEE NOVAFORT 355x110', 307503.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(455, '2902981', 'SILLA TEE NOVAFORT 355x160', 342457.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(456, '2903111', 'SILLA TEE NOVAFORT 355X200', 342456.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(457, '2902770', 'SILLA TEE NOVAFORT 400 X 110', 368789.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(458, '2902772', 'SILLA TEE NOVAFORT 400 X 160', 375490.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(459, '2903096', 'SILLA TEE NOVAFORT 400X200', 375494.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(460, '2903097', 'SILLA TEE NOVAFORT 400X250', 375494.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(461, '2902774', 'SILLA TEE NOVAFORT 450 X 160', 401636.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(462, '2902775', 'SILLA TEE NOVAFORT 500 X 160', 612702.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(463, '2901309', 'SILLA YEE NOVAFORT 160X110', 103304.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(464, '2901311', 'SILLA YEE NOVAFORT 200 X110', 126529.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(465, '2901313', 'SILLA YEE NOVAFORT 200 X160', 126529.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(466, '2903112', 'SILLA YEE NOVAFORT 24X160', 502899.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(467, '2902977', 'SILLA YEE NOVAFORT 24X200', 622404.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(468, '2903113', 'SILLA YEE NOVAFORT 250 x 200', 217572.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(469, '2901315', 'SILLA YEE NOVAFORT 250X110', 146719.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(470, '2901317', 'SILLA YEE NOVAFORT 250X160', 146719.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(471, '2903107', 'SILLA YEE NOVAFORT 27X 160', 624243.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(472, '2902987', 'SILLA YEE NOVAFORT 27X200', 626072.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(473, '2903059', 'SILLA YEE NOVAFORT 27X250', 1199400.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(474, '2906134', 'SILLA YEE NOVAFORT 30X160', 651794.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(475, '2906474', 'SILLA YEE NOVAFORT 30X200', 993469.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(476, '2902777', 'SILLA YEE NOVAFORT 315X110', 227467.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(477, '2907844', 'SILLA YEE NOVAFORT 315X160', 227467.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(478, '2902978', 'SILLA YEE NOVAFORT 315x200', 307503.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(479, '2906159', 'SILLA YEE NOVAFORT 33X160', 679263.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(480, '2902983', 'SILLA YEE NOVAFORT 355X110', 342457.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(481, '2902982', 'SILLA YEE NOVAFORT 355X160', 342457.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(482, '2902979', 'SILLA YEE NOVAFORT 355X200', 342456.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(483, '2906135', 'SILLA YEE NOVAFORT 36X160', 733596.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(484, '2902781', 'SILLA YEE NOVAFORT 400 X 110', 375494.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(485, '2907845', 'SILLA YEE NOVAFORT 400 X 160', 375494.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(486, '2902785', 'SILLA YEE NOVAFORT 400X200', 375494.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(487, '2902776', 'SILLA YEE NOVAFORT 400X250', 375494.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(488, '2902787', 'SILLA YEE NOVAFORT 450 X 160', 401636.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(489, '2902980', 'SILLA YEE NOVAFORT 450X200', 401632.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(490, '2902789', 'SILLA YEE NOVAFORT 500 X 160', 612702.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(491, '2902990', 'SILLA YEE NOVAFORT 500 X 200', 612707.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(492, '2909942', 'SOLDADURA LIQUIDA CPVC 1/128 Gal.', 8098.00, 11, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(493, '2909923', 'SOLDADURA LIQUIDA CPVC 1/16 Gal.', 49310.00, 11, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(494, '2909943', 'SOLDADURA LIQUIDA CPVC 1/32 Gal.', 30260.00, 11, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(495, '2909921', 'SOLDADURA LIQUIDA CPVC 1/4 Gal.', 146568.00, 28, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(496, '2909944', 'SOLDADURA LIQUIDA CPVC 1/64 Gal.', 17888.00, 11, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(497, '2909922', 'SOLDADURA LIQUIDA CPVC 1/8 Gal.', 81702.00, 11, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(498, '2909997', 'SOLDADURA LIQUIDA PVC 1/128 Gal.', 7762.00, 11, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(499, '2910002', 'SOLDADURA LIQUIDA PVC 1/16 Gal.', 43664.00, 11, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(500, '2909998', 'SOLDADURA LIQUIDA PVC 1/32 Gal.', 26032.00, 11, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(501, '2910000', 'SOLDADURA LIQUIDA PVC 1/4 Gal.', 136698.00, 11, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(502, '2909999', 'SOLDADURA LIQUIDA PVC 1/64 Gal.', 15204.00, 11, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(503, '2910001', 'SOLDADURA LIQUIDA PVC 1/8 Gal.', 70636.00, 11, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(504, '2903810', 'TAPON DE PRUEBA PVC-S 10PULG', 398226.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(505, '2901437', 'TAPON DE PRUEBA PVC-S 1-1/2PULG', 1254.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(506, '2901439', 'TAPON DE PRUEBA PVC-S 2PULG', 1641.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(507, '2901441', 'TAPON DE PRUEBA PVC-S 3PULG', 2083.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(508, '2901443', 'TAPON DE PRUEBA PVC-S 4PULG', 4136.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(509, '2905559', 'TAPON DE PRUEBA PVC-S 6PULG', 18607.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(510, '2903809', 'TAPON DE PRUEBA PVC-S 8PULG', 261480.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(511, '2901357', 'TAPON ROSCADO PVC-P 1PULG', 2806.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(512, '2901388', 'TAPON ROSCADO PVC-P 1/2PULG', 672.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(513, '2901367', 'TAPON ROSCADO PVC-P 1-1/2PULG', 6572.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(514, '2901375', 'TAPON ROSCADO PVC-P 1-1/4PULG', 4966.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(515, '2901398', 'TAPON ROSCADO PVC-P 2PULG', 10746.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(516, '2901405', 'TAPON ROSCADO PVC-P 2.1/2PULG', 24764.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(517, '2901414', 'TAPON ROSCADO PVC-P 3PULG', 39425.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(518, '2901425', 'TAPON ROSCADO PVC-P 3/4PULG', 1972.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(519, '2901434', 'TAPON ROSCADO PVC-P 4PULG', 72790.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(520, '2910385', 'TAPON SOLDADO CPVC  1/2PULG', 2009.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(521, '2910386', 'TAPON SOLDADO CPVC  3/4PULG', 2009.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(522, '2910387', 'TAPON SOLDADO CPVC 1PULG', 7266.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(523, '2910389', 'TAPON SOLDADO CPVC  1.1/2PULG', 20707.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(524, '2910388', 'TAPON SOLDADO CPVC 1.1/4PULG', 13388.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(525, '2903759', 'TAPON SOLDADO CPVC 2PULG', 50278.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(526, '2907473', 'TAPON SOLDADO CPVC SCH 80  1PULG', 62970.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(527, '2907474', 'TAPON SOLDADO CPVC SCH 80  1.1/4PULG', 68929.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(528, '2907471', 'TAPON SOLDADO CPVC SCH 80   1/2PULG', 25054.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(529, '2907475', 'TAPON SOLDADO CPVC SCH 80  1-1/2PULG', 69233.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(530, '2907476', 'TAPON SOLDADO CPVC SCH 80  2PULG', 72670.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(531, '2906874', 'TAPON SOLDADO CPVC SCH 80  2-1/2PULG', 178054.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(532, '2906875', 'TAPON SOLDADO CPVC SCH 80 3PULG', 194684.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(533, '2907472', 'TAPON SOLDADO CPVC SCH 80   3/4PULG', 35579.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(534, '2906876', 'TAPON SOLDADO CPVC SCH 80  4PULG', 203730.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(535, '2907477', 'TAPON SOLDADO CPVC SCH 80  6PULG', 672230.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(536, '2907561', 'TAPON SOLDADO PVC SCH 80  1.1/4PULG', 24043.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(537, '2907562', 'TAPON SOLDADO PVC SCH 80  1-1/2PULG', 24588.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(538, '2907563', 'TAPON SOLDADO PVC SCH 80  2PULG', 47494.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(539, '2907564', 'TAPON SOLDADO PVC SCH 80  2.1/2PULG', 96924.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(540, '2907565', 'TAPON SOLDADO PVC SCH 80  3PULG', 99590.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(541, '2907566', 'TAPON SOLDADO PVC SCH 80  4PULG', 121426.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(542, '2909792', 'TAPON SOLDADO PVC SCH 80  6PULG', 500846.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(543, '2907567', 'TAPON SOLDADO PVC SCH 80  8PULG', 584497.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(544, '2901390', 'TAPON SOLDADO PVC-P  1/2PULG', 492.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(545, '2901427', 'TAPON SOLDADO PVC-P  3/4PULG', 986.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(546, '2901359', 'TAPON SOLDADO PVC-P 1PULG', 1650.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(547, '2901369', 'TAPON SOLDADO PVC-P 1-1/2PULG', 5175.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(548, '2901377', 'TAPON SOLDADO PVC-P 1-1/4PULG', 3975.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(549, '2901400', 'TAPON SOLDADO PVC-P 2PULG', 8224.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(550, '2901406', 'TAPON SOLDADO PVC-P 2.1/2PULG ', 19360.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(551, '2901415', 'TAPON SOLDADO PVC-P 3PULG', 31467.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(552, '2901435', 'TAPON SOLDADO PVC-P 4PULG', 57173.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(553, '2910396', 'TEE CPVC  1/2PULG', 2803.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(554, '2910397', 'TEE CPVC  3/4PULG', 4440.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(555, '2910398', 'TEE CPVC 1PULG', 25475.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(556, '2910400', 'TEE CPVC 1.1/2PULG', 60143.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(557, '2910399', 'TEE CPVC 1-1/4PULG', 44787.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(558, '2903765', 'TEE CPVC 2PULG', 109423.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(559, '2907480', 'TEE CPVC SCH 80 1PULG', 41364.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(560, '2907482', 'TEE CPVC SCH 80 1.1/2PULG', 107735.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(561, '2907481', 'TEE CPVC SCH 80 1.1/4PULG', 88833.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(562, '2907478', 'TEE CPVC SCH 80  1/2PULG', 33163.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(563, '2907483', 'TEE CPVC SCH 80 2PULG', 119993.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(564, '2906877', 'TEE CPVC SCH 80 2-1/2PULG', 251434.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(565, '2906878', 'TEE CPVC SCH 80 3PULG', 327917.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(566, '2907479', 'TEE CPVC SCH 80  3/4PULG', 33773.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(567, '2906879', 'TEE CPVC SCH 80 4PULG', 404399.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(568, '2907484', 'TEE CPVC SCH 80 6PULG', 784827.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(569, '2903109', 'TEE DOBLE PREFABRICADO NOVAFORT 315 X 160', 855906.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(570, '2901469', 'TEE DOBLE PVC-S 1-1/2PULG', 14249.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(571, '2901471', 'TEE DOBLE PVC-S 2PULG', 17003.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(572, '2901473', 'TEE DOBLE PVC-S 3PULG', 38577.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(573, '2901476', 'TEE DOBLE PVC-S 4PULG', 61342.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(574, '2901460', 'TEE DOBLE REDUCIDA PVC-S 2PULG x 1-1/2PULG', 12957.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(575, '2901462', 'TEE DOBLE REDUCIDA PVC-S 3PULG x 2PULG', 26351.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(576, '2901464', 'TEE DOBLE REDUCIDA PVC-S 4PULG x 2PULG', 55029.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(577, '2901466', 'TEE DOBLE REDUCIDA PVC-S 4PULG x 3PULG', 55029.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(578, '2911400', 'TEE H.D. C900 4PULG', 1365250.00, 27, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(579, '2901445', 'TEE NOVAFORT 160X160', 86248.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(580, '2901526', 'TEE NOVAFORT 200X160', 219398.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(581, '2902826', 'TEE NOVAFORT 250X160', 254706.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(582, '2902825', 'TEE PREFABRICADO NOVAFORT 200MM', 230976.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(583, '2903108', 'TEE PREFABRICADO NOVAFORT 250 X 200', 583654.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(584, '2902820', 'TEE PREFABRICADO NOVAFORT 250MM', 719823.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(585, '2903110', 'TEE PREFABRICADO NOVAFORT 315 X 200', 963031.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(586, '2902821', 'TEE PREFABRICADO NOVAFORT 315MM', 875484.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(587, '2903062', 'TEE PREFABRICADO NOVAFORT 355MM', 1269450.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(588, '2902822', 'TEE PREFABRICADO NOVAFORT 400MM', 1814540.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(589, '2902823', 'TEE PREFABRICADO NOVAFORT 450MM', 2414600.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(590, '2906089', 'TEE PREFABRICADO NOVAFORT 500MM', 2719910.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(591, '2909793', 'TEE PVC SCH 80  6PULG', 424108.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(592, '2907568', 'TEE PVC SCH 80  1.1/4PULG', 63097.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(593, '2907569', 'TEE PVC SCH 80  1-1/2PULG', 66260.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(594, '2907570', 'TEE PVC SCH 80  2PULG', 77876.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(595, '2907571', 'TEE PVC SCH 80  2.1/2PULG', 79396.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(596, '2907572', 'TEE PVC SCH 80  3PULG', 101844.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(597, '2907573', 'TEE PVC SCH 80  4PULG', 118003.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(598, '2907574', 'TEE PVC SCH 80  8PULG', 910499.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(599, '2908623', 'TEE PVC UP RADIO CORTO 2PULG', 113570.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(600, '2903538', 'TEE PVC UP RADIO CORTO 2.1/2PULG', 359625.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(601, '2909098', 'TEE PVC UP RADIO CORTO 3PULG', 153118.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(602, '2909099', 'TEE PVC UP RADIO CORTO 4PULG', 239018.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(603, '2908624', 'TEE PVC UP RADIO CORTO 6PULG', 660784.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(604, '2901481', 'TEE PVC-P 1PULG', 3754.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(605, '2901498', 'TEE PVC-P 1/2PULG', 1136.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(606, '2901808', 'TEE PVC-P 1/2PULG - ROSCADO', 2576.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(607, '2901486', 'TEE PVC-P 1-1/2PULG', 12727.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(608, '2901490', 'TEE PVC-P 1-1/4PULG', 9695.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(609, '2901503', 'TEE PVC-P 2PULG', 20267.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(610, '2901508', 'TEE PVC-P 2.1/2PULG', 48071.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(611, '2901513', 'TEE PVC-P 3PULG', 76469.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(612, '2901519', 'TEE PVC-P 3/4PULG', 1921.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(613, '2901524', 'TEE PVC-P 4PULG', 166872.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(614, '2903800', 'TEE PVC-S 10PULG', 1024090.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(615, '2901558', 'TEE PVC-S 1-1/2PULG', 7893.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(616, '2901561', 'TEE PVC-S 2PULG', 9045.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(617, '2901563', 'TEE PVC-S 3PULG', 11350.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(618, '2901567', 'TEE PVC-S 4PULG', 23654.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(619, '2911125', 'TEE PVC-S 6PULG', 214801.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(620, '2903799', 'TEE PVC-S 8PULG', 575005.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(621, '2901530', 'TEE REDUCIDA PVC-P 1PULG x 1/2PULG', 5585.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(622, '2901532', 'TEE REDUCIDA PVC-P 1PULG x 3/4PULG', 5585.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(623, '2901538', 'TEE REDUCIDA PVC-P 3/4PULG x 1/2PULG', 2831.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(624, '2901543', 'TEE REDUCIDA PVC-S 2PULG x 1-1/2PULG', 8678.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(625, '2901545', 'TEE REDUCIDA PVC-S 3PULG x 2PULG', 21793.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(626, '2901548', 'TEE REDUCIDA PVC-S 4PULG x 2PULG', 38014.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(627, '2901550', 'TEE REDUCIDA PVC-S 4PULG x 3PULG', 38014.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(628, '2909348', 'TEE REDUCIDA PVC-S 6PULG x 4PULG', 214801.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(629, '2911382', 'TRANSICION BRIDAXCAMPANA H.D. C900 4PULG', 1007420.00, 27, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(630, '2911383', 'TRANSICION BRIDAXCAMPANA H.D. C900 6PULG', 1224910.00, 27, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(631, '2911384', 'TRANSICION BRIDAXCAMPANA H.D. C900 8PULG', 1850550.00, 27, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(632, '2900090', 'TUBERIA ALCANTARILLADO NOVAFORT 110 MM S8', 24695.00, 15, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(633, '2900100', 'TUBERIA ALCANTARILLADO NOVAFORT 160 MM S4', 46934.00, 16, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(634, '2900102', 'TUBERIA ALCANTARILLADO NOVAFORT 200 MM S4', 67093.00, 16, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(635, '2906313', 'TUBERIA ALCANTARILLADO NOVAFORT 24PULG', 607171.00, 17, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(636, '2900096', 'TUBERIA ALCANTARILLADO NOVAFORT 250 MM S4', 97992.00, 16, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(637, '2900511', 'TUBERIA ALCANTARILLADO NOVAFORT 27PULG', 714030.00, 17, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(638, '2906378', 'TUBERIA ALCANTARILLADO NOVAFORT 30PULG', 891040.00, 17, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(639, '2904921', 'TUBERIA ALCANTARILLADO NOVAFORT 315 MM S4', 141005.00, 16, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(640, '2904604', 'TUBERIA ALCANTARILLADO NOVAFORT 33PULG', 1228720.00, 17, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(641, '2902494', 'TUBERIA ALCANTARILLADO NOVAFORT 355 MM S4', 211315.00, 16, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(642, '2904605', 'TUBERIA ALCANTARILLADO NOVAFORT 36PULG', 1653880.00, 17, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(643, '2905865', 'TUBERIA ALCANTARILLADO NOVAFORT 39PULG', 2212820.00, 17, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(644, '2909762', 'TUBERIA ALCANTARILLADO NOVAFORT 400 MM S4', 258462.00, 16, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(645, '2905866', 'TUBERIA ALCANTARILLADO NOVAFORT 42PULG', 2597830.00, 17, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(646, '2910888', 'TUBERIA ALCANTARILLADO NOVAFORT 45PULG', 2802180.00, 17, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(647, '2910889', 'TUBERIA ALCANTARILLADO NOVAFORT 48PULG', 3416060.00, 17, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(648, '2900182', 'TUBERIA CONDUFLEX VERDE 1 1/4PULG', 12449.00, 18, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(649, '2900181', 'TUBERIA CONDUFLEX VERDE 1PULG', 8151.00, 18, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(650, '2900119', 'TUBERIA CONDUFLEX VERDE 1/2PULG', 2797.00, 18, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(651, '2900121', 'TUBERIA CONDUFLEX VERDE 3/4PULG', 4860.00, 18, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(652, '2900125', 'TUBERIA CONDUIT 1', 5065.00, 19, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(653, '2900133', 'TUBERIA CONDUIT 1/2PULG', 2790.00, 19, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(654, '2900128', 'TUBERIA CONDUIT 1-1/2PULG', 9976.00, 19, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(655, '2900130', 'TUBERIA CONDUIT 1-1/4PULG', 7825.00, 19, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(656, '2900135', 'TUBERIA CONDUIT 2PULG', 15344.00, 19, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(657, '2900138', 'TUBERIA CONDUIT 3/4PULG', 3654.00, 19, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(658, '2900144', 'TUBERIA CORRUGADA CON FILTRO 2 1/2PULG (ROLLO DE 150M)', 18369.00, 20, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(659, '2900147', 'TUBERIA CORRUGADA CON FILTRO 4PULG (ROLLO DE 100M)', 31935.00, 20, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(660, '2900151', 'TUBERIA CORRUGADA CON FILTRO 6PULG (ROLLO DE 50M)', 73136.00, 20, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(661, '2900153', 'TUBERIA CORRUGADA CON FILTRO 8PULG (ROLLO DE 35M)', 99316.00, 20, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(662, '2900143', 'TUBERIA CORRUGADA SIN FILTRO 65 mm (ROLLO DE 150M)', 13412.00, 20, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(663, '2900146', 'TUBERIA CORRUGADA SIN FILTRO 100 mm (Rollo de 100m)', 23694.00, 20, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(664, '2900150', 'TUBERIA CORRUGADA SIN FILTRO 160 mm (ROLLO DE 50M)', 52789.00, 20, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(665, '2900152', 'TUBERIA CORRUGADA SIN FILTRO 200 mm (ROLLO DE 35M)', 73860.00, 20, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(666, '2910217', 'TUBERIA CPVC  1/2PULG', 9457.00, 12, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(667, '2910219', 'TUBERIA CPVC  3/4PULG', 15542.00, 12, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(668, '2910221', 'TUBERIA CPVC 1PULG', 26294.00, 12, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(669, '2910223', 'TUBERIA CPVC 1.1/2PULG', 77607.00, 12, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(670, '2910222', 'TUBERIA CPVC 1.1/4PULG', 53846.00, 12, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(671, '2910224', 'TUBERIA CPVC 2PULG', 132097.00, 12, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(672, '2907415', 'TUBERIA CPVC SCH 80   1PULG', 68032.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(673, '2907416', 'TUBERIA CPVC SCH 80  1.1/4PULG', 94677.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(674, '2907413', 'TUBERIA CPVC SCH 80    1/2PULG', 50143.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(675, '2907417', 'TUBERIA CPVC SCH 80  1-1/2PULG', 106154.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(676, '2907418', 'TUBERIA CPVC SCH 80  2PULG', 162458.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(677, '2906859', 'TUBERIA CPVC SCH 80  2-1/2PULG', 209451.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(678, '2906860', 'TUBERIA CPVC SCH 80  3PULG', 264810.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(679, '2907414', 'TUBERIA CPVC SCH 80    3/4PULG', 55156.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(680, '2906861', 'TUBERIA CPVC SCH 80  4PULG', 448824.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(681, '2907499', 'TUBERIA CPVC SCH 80  6PULG', 866544.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(682, '2910537', 'TUBERIA PVC C900 DR 14 4PULG', 143116.00, 21, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(683, '2910540', 'TUBERIA PVC C900 DR 14 6PULG', 297007.00, 21, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(684, '2910536', 'TUBERIA PVC C900 DR 18 4PULG', 114191.00, 21, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(685, '2910539', 'TUBERIA PVC C900 DR 18 6PULG', 235719.00, 21, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(686, '2900338', 'TUBERIA PVC-L 1-1/2PULG (Ventilación)', 8084.00, 22, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(687, '2900341', 'TUBERIA PVC-L 2PULG (Ventilación)', 11688.00, 22, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(688, '2900344', 'TUBERIA PVC-L 3PULG (Ventilación)', 15600.00, 22, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(689, '2900347', 'TUBERIA PVC-L 4PULG (VENTILACIÓN).', 26899.00, 22, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(690, '2911118', 'TUBERIA PVC SCH 80 1PULG', 19528.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(691, '2907501', 'TUBERIA PVC SCH 80  1.1/2PULG', 31086.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(692, '2907500', 'TUBERIA PVC SCH 80  1.1/4PULG', 28528.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(693, '2907502', 'TUBERIA PVC SCH 80  2PULG', 43401.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(694, '2907503', 'TUBERIA PVC SCH 80  2.1/2PULG', 74339.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(695, '2907504', 'TUBERIA PVC SCH 80  3PULG', 92305.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(696, '2907505', 'TUBERIA PVC SCH 80  4PULG', 124997.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(697, '2907506', 'TUBERIA PVC SCH 80  6PULG', 237624.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(698, '2907507', 'TUBERIA PVC SCH 80  8PULG', 358197.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(699, '2900213', 'TUBERIA PVC-P 1PULG RDE 13.5 (315 psi) TRABAJO PESADO', 9350.00, 13, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(700, '2900220', 'TUBERIA PVC-P 1PULG RDE 21 (200 psi)', 6457.00, 13, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(701, '2902449', 'TUBERIA PVC-P 1/2PULG RDE 13.5 (315 psi)', 3713.00, 13, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(702, '2900266', 'TUBERIA PVC-P 1/2PULG RDE 9  (500 psi) TRABAJO PESADO', 5204.00, 13, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(703, '2902450', 'TUBERIA PVC-P 1-1/2PULG RDE 21 (200 psi)', 15187.00, 13, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(704, '2900225', 'TUBERIA PVC-P 1-1/4PULG RDE 21 (200 psi) ', 11631.00, 13, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(705, '2902453', 'TUBERIA PVC-P 2PULG RDE 21 (200 psi) ', 23289.00, 13, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(706, '2900246', 'TUBERIA PVC-P 2PULG RDE 26 (160 psi)', 19228.00, 13, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(707, '2900230', 'TUBERIA PVC-P 2.1/2PULG RDE 21 (200 psi)', 44607.00, 13, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(708, '2900233', 'TUBERIA PVC-P 3PULG RDE 21 (200 psi) ', 52394.00, 13, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(709, '2900251', 'TUBERIA PVC-P 3PULG RDE 26 (160 psi)', 41080.00, 13, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(710, '2900210', 'TUBERIA PVC-P 3/4PULG RDE 11 (400psi)', 6929.00, 13, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(711, '2900237', 'TUBERIA PVC-P 3/4PULG RDE 21 (200psi) ', 4601.00, 13, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(712, '2900240', 'TUBERIA PVC-P 4PULG RDE 21 (200 psi)', 89165.00, 13, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(713, '2904616', 'TUBERIA PVC-P 6PULG RDE 21 (200 psi)', 190083.00, 13, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(714, '2900421', 'TUBERIA PVC-S 10PULG', 206029.00, 14, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(715, '2900319', 'TUBERIA PVC-S 1-1/2PULG', 13416.00, 14, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(716, '2902515', 'TUBERIA PVC-S 2PULG', 16633.00, 14, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(717, '2902517', 'TUBERIA PVC-S 3PULG', 24844.00, 14, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(718, '2900331', 'TUBERIA PVC-S 4PULG', 34941.00, 14, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(719, '2900336', 'TUBERIA PVC-S 6PULG', 73321.00, 14, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(720, '2900420', 'TUBERIA PVC-S 8PULG', 131904.00, 14, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(721, '2900323', 'TUBERIA PVC-S NOVATEC 2PULG', 16633.00, 23, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(722, '2900326', 'TUBERIA PVC-S NOVATEC 3PULG', 24844.00, 23, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(723, '2900330', 'TUBERIA PVC-S NOVATEC 4PULG', 34941.00, 23, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(724, '2900335', 'TUBERIA PVC-S NOVATEC 6PULG', 73321.00, 23, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(725, '2902411', 'TUBERIA UNION MECANICA SNAP 10PULG RDE 21', 323625.00, 24, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(726, '2902421', 'TUBERIA UNION MECANICA SNAP 12PULG RDE 21', 452814.00, 24, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(727, '2902431', 'TUBERIA UNION MECANICA SNAP 14PULG RDE 21', 561544.00, 24, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(728, '2900010', 'TUBERIA UNION MECANICA SNAP 2PULG RDE 21', 17866.00, 24, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(729, '2900022', 'TUBERIA UNION MECANICA SNAP 3PULG RDE 21', 44724.00, 24, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(730, '2900033', 'TUBERIA UNION MECANICA SNAP 4PULG RDE 21', 59073.00, 24, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(731, '2900043', 'TUBERIA UNION MECANICA SNAP 6PULG RDE 21', 121366.00, 24, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(732, '2900054', 'TUBERIA UNION MECANICA SNAP 8PULG RDE 21', 205580.00, 24, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'ml', 6),
(733, '2910408', 'UNION CPVC  1/2PULG', 1535.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(734, '2910409', 'UNION CPVC  3/4PULG', 2265.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(735, '2910410', 'UNION CPVC 1PULG', 9995.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(736, '2910412', 'UNION CPVC 1-1/2PULG', 34975.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(737, '2910411', 'UNION CPVC 1-1/4PULG', 31621.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(738, '2903768', 'UNION CPVC 2PULG', 38328.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(739, '2907487', 'UNION CPVC SCH 80  1PULG', 28081.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(740, '2907488', 'UNION CPVC SCH 80  1.1/4PULG', 39484.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(741, '2907485', 'UNION CPVC SCH 80   1/2PULG', 14895.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(742, '2907489', 'UNION CPVC SCH 80  1-1/2PULG', 53047.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(743, '2906880', 'UNION CPVC SCH 80 2 1/2PULG', 128496.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(744, '2907490', 'UNION CPVC SCH 80  2PULG', 90772.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(745, '2906881', 'UNION CPVC SCH 80 3PULG', 207158.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(746, '2907486', 'UNION CPVC SCH 80   3/4PULG', 20916.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(747, '2906882', 'UNION CPVC SCH 80  4PULG', 335326.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(748, '2907491', 'UNION CPVC SCH 80  6PULG', 463495.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(749, '2902885', 'UNION MECANICA RAPIDA O PASANTE SNAP 10PULG', 681242.00, 25, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(750, '2902889', 'UNION MECANICA RAPIDA O PASANTE SNAP 12PULG', 1055780.00, 25, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(751, '2902892', 'UNION MECANICA RAPIDA O PASANTE SNAP 2PULG', 37052.00, 25, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(752, '2902898', 'UNION MECANICA RAPIDA O PASANTE SNAP 3PULG', 61002.00, 25, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(753, '2902901', 'UNION MECANICA RAPIDA O PASANTE SNAP 4PULG', 88887.00, 25, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(754, '2902905', 'UNION MECANICA RAPIDA O PASANTE SNAP 6PULG', 207538.00, 25, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(755, '2902909', 'UNION MECANICA RAPIDA O PASANTE SNAP 8PULG', 381288.00, 25, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(756, '2902941', 'UNION MECANICA REPARACION SNAP 10PULG', 763419.00, 25, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(757, '2902943', 'UNION MECANICA REPARACION SNAP 12PULG', 1397510.00, 25, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(758, '2902945', 'UNION MECANICA REPARACION SNAP 2PULG', 42895.00, 25, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(759, '2902950', 'UNION MECANICA REPARACION SNAP 3PULG', 68325.00, 25, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(760, '2902953', 'UNION MECANICA REPARACION SNAP 4PULG', 104976.00, 25, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(761, '2902956', 'UNION MECANICA REPARACION SNAP 6PULG', 243090.00, 25, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(762, '2902959', 'UNION MECANICA REPARACION SNAP 8PULG', 446968.00, 25, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(763, '2901576', 'UNION NOVAFORT 110 MM', 17247.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(764, '2901577', 'UNION NOVAFORT 160 MM', 41791.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(765, '2907997', 'UNION NOVAFORT 200 MM', 70149.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(766, '2906753', 'UNION NOVAFORT 24PULG', 719654.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(767, '2902914', 'UNION NOVAFORT 250 MM', 199253.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(768, '2900517', 'UNION NOVAFORT 27PULG', 809609.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(769, '2900518', 'UNION NOVAFORT 30PULG', 1011020.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(770, '2902917', 'UNION NOVAFORT 315 MM', 340926.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(771, '2906333', 'UNION NOVAFORT 33PULG', 3054020.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(772, '2910937', 'UNION NOVAFORT 355MM', 452544.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(773, '2907224', 'UNION NOVAFORT 36PULG', 4409580.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(774, '2907225', 'UNION NOVAFORT 39PULG', 5720890.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(775, '2902921', 'UNION NOVAFORT 400 MM', 519794.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(776, '2907299', 'UNION NOVAFORT 42PULG', 6200220.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(777, '2911972', 'UNION NOVAFORT 45PULG', 10624400.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(778, '2902924', 'UNION NOVAFORT 450 MM', 546758.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(779, '2911973', 'UNION NOVAFORT 48PULG', 12049100.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(780, '2902926', 'UNION NOVAFORT 500 MM', 614275.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(781, '2911121', 'UNION PVC SCH 80 1PULG', 15068.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(782, '2907576', 'UNION PVC SCH 80 1.1/2PULG', 25351.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(783, '2907575', 'UNION PVC SCH 80 1.1/4PULG', 23389.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(784, '2907577', 'UNION PVC SCH 80 2PULG', 29097.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(785, '2907578', 'UNION PVC SCH 80 2.1/2PULG', 62821.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(786, '2907579', 'UNION PVC SCH 80 3PULG', 72129.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(787, '2907580', 'UNION PVC SCH 80 4PULG', 92703.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(788, '2909794', 'UNION PVC SCH 80 6PULG', 194428.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(789, '2907581', 'UNION PVC SCH 80 8PULG', 276722.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(790, '2902904', 'UNION PVC UP 4PULG', 93323.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(791, '2901635', 'UNION PVC-P  1/2PULG', 551.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(792, '2901661', 'UNION PVC-P  3/4PULG', 869.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(793, '2901616', 'UNION PVC-P 1PULG', 1424.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(794, '2901621', 'UNION PVC-P 1-1/2PULG', 3561.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(795, '2901626', 'UNION PVC-P 1-1/4PULG', 2607.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(796, '2901642', 'UNION PVC-P 2PULG', 5835.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(797, '2901647', 'UNION PVC-P 2.1/2PULG', 23086.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(798, '2901654', 'UNION PVC-P 3PULG', 28599.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(799, '2901667', 'UNION PVC-P 4PULG', 62127.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(800, '2903817', 'UNION PVC-S 10PULG', 321381.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(801, '2901690', 'UNION PVC-S 1-1/2PULG', 3123.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(802, '2901693', 'UNION PVC-S 2PULG', 3576.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(803, '2901696', 'UNION PVC-S 3PULG', 5162.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(804, '2901700', 'UNION PVC-S 4PULG', 10405.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(805, '2901703', 'UNION PVC-S 6PULG', 45198.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(806, '2909778', 'UNION PVC-S 8PULG', 130084.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(807, '2903397', 'UNIÓN REPARACIÓN DESLIZANTE PVC-P 1PULG', 19069.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(808, '2903398', 'UNIÓN REPARACIÓN DESLIZANTE PVC-P 1.1/2PULG', 72288.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(809, '2903399', 'UNIÓN REPARACIÓN DESLIZANTE PVC-P 1/2PULG', 8293.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(810, '2903400', 'UNIÓN REPARACIÓN DESLIZANTE PVC-P 2PULG', 80427.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(811, '2903401', 'UNIÓN REPARACIÓN DESLIZANTE PVC-P 3/4PULG', 9149.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(812, '2910421', 'UNIVERSAL CPVC 1/2PULG', 14635.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(813, '2910422', 'UNIVERSAL CPVC 3/4PULG', 16304.00, 4, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(814, '2907494', 'UNIVERSAL CPVC SCH 80  1PULG', 82630.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(815, '2907495', 'UNIVERSAL CPVC SCH 80  1.1/4PULG', 167296.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(816, '2907492', 'UNIVERSAL CPVC SCH 80   1/2PULG', 66603.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(817, '2907496', 'UNIVERSAL CPVC SCH 80  1-1/2PULG', 170482.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(818, '2907497', 'UNIVERSAL CPVC SCH 80  2PULG', 376794.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(819, '2907493', 'UNIVERSAL CPVC SCH 80 3/4PULG', 69018.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(820, '2907583', 'UNIVERSAL PVC SCH 80 1 1/2PULG', 86997.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(821, '2907582', 'UNIVERSAL PVC SCH 80 1 1/4PULG', 76886.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(822, '2907584', 'UNIVERSAL PVC SCH 80 2PULG', 118029.00, 1, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(823, '2901672', 'UNIVERSAL PVC-P 1PULG', 12841.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(824, '2901679', 'UNIVERSAL PVC-P 1/2PULG', 4790.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(825, '2901802', 'UNIVERSAL PVC-P 1-1/2PULG', 39784.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(826, '2901801', 'UNIVERSAL PVC-P 1-1/4PULG', 23179.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(827, '2901800', 'UNIVERSAL PVC-P 2PULG', 50855.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(828, '2901685', 'UNIVERSAL PVC-P 3/4PULG', 8495.00, 5, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(829, '2901709', 'YEE NOVAFORT 160 X 160', 117820.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(830, '2907351', 'YEE NOVAFORT 200 X 160', 225409.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:48', 'und', 1),
(831, '2902962', 'YEE NOVAFORT 250 X 160', 427634.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(832, '2902963', 'YEE NOVAFORT 315 X 160', 457305.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(833, '2902964', 'YEE NOVAFORT 400 X 160', 845240.00, 2, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(834, '2903798', 'YEE PVC-S 10PULG', 729191.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(835, '2901748', 'YEE PVC-S 2PULG', 10172.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1);
INSERT INTO `productos` (`id`, `codigo`, `descripcion`, `precio`, `grupo_id`, `created_at`, `updated_at`, `unidad_medida`, `unidad_empaque`) VALUES
(836, '2901751', 'YEE PVC-S 3PULG', 20894.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(837, '2901755', 'YEE PVC-S 4PULG', 36357.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(838, '2901758', 'YEE PVC-S 6PULG', 172024.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(839, '2903794', 'YEE PVC-S 8PULG', 447355.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(840, '2901729', 'YEE PVC-S DOBLE 2PULG', 16270.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(841, '2901731', 'YEE PVC-S DOBLE 3PULG', 39830.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(842, '2901734', 'YEE PVC-S DOBLE 4PULG', 64082.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(843, '2901721', 'YEE PVC-S DOBLE REDUCIDA 2 x 3 x 2PULG', 33564.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(844, '2901724', 'YEE PVC-S DOBLE REDUCIDA 2 x 4 x 2PULG', 41318.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(845, '2901726', 'YEE PVC-S DOBLE REDUCIDA 3 x 4 x 3PULG', 51789.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(846, '2903795', 'YEE REDUCIDA PVC-S 10PULG x 4PULG', 673303.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(847, '2903796', 'YEE REDUCIDA PVC-S 10PULG x 6PULG', 896515.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(848, '2903797', 'YEE REDUCIDA PVC-S 10PULG x 8PULG', 1151510.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(849, '2901738', 'YEE REDUCIDA PVC-S 3 x 2PULG', 19735.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(850, '2901741', 'YEE REDUCIDA PVC-S 4PULG x 2PULG', 31209.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(851, '2901743', 'YEE REDUCIDA PVC-S 4PULG x 3PULG', 31209.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(852, '2901716', 'YEE REDUCIDA PVC-S 6 x 4PULG', 163831.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(853, '2903792', 'YEE REDUCIDA PVC-S 8 x 4PULG', 319744.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(854, '2903793', 'YEE REDUCIDA PVC-S 8 x 6PULG', 312459.00, 7, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(855, '2911389', 'CODO 90° H.D. C900 6PULG', 1779450.00, 27, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(856, '2911390', 'CODO 90° H.D. C900 8PULG', 2882130.00, 27, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(857, '2911386', 'CODO 45° H.D. C900 6PULG', 1654270.00, 27, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(858, '2911387', 'CODO 45° H.D. C900 8PULG', 2706790.00, 27, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(859, '2911401', 'TEE H.D. C900 6PULG', 2297870.00, 27, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(860, '2911402', 'TEE H.D. C900 8PULG', 3396460.00, 27, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(861, '2900145', 'TUBERIA CORRUGADA 65MM SIN FILTRO (DRENAJE)', 14183.00, 20, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(862, '2902930', 'UNION CORRUGADA 65MM (DRENAJE)', 8096.00, 20, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(863, '2910004', 'SOLDADURA COLOR VERDE LOW VOC 1/4 GAL', 136699.00, 11, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(864, '2905080', 'TUBERIA BIAXIAL 3PULG PVC 160 PSI', 38774.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(865, '2905079', 'TUBERIA BIAXIAL 3PULG PVC 200 PSI', 46757.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(866, '2900106', 'TUBERIA BIAXIAL 10PULG PVC 160 PSI', 263220.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(867, '2900105', 'TUBERIA BIAXIAL 10PULG PVC 200 PSI', 323625.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(868, '2900108', 'TUBERIA BIAXIAL 12PULG PVC 160 PSI', 387916.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(869, '2900107', 'TUBERIA BIAXIAL 12PULG PVC 200 PSI', 452814.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(870, '2900523', 'TUBERIA BIAXIAL 14PULG PVC 160 PSI', 461680.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(871, '2900524', 'TUBERIA BIAXIAL 14PULG PVC 200 PSI', 561544.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(872, '2905387', 'TUBERIA BIAXIAL 16PULG PVC 160 PSI', 612552.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(873, '2905388', 'TUBERIA BIAXIAL 16PULG PVC 200 PSI', 737035.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(874, '2905392', 'TUBERIA BIAXIAL 18PULG PVC 160 PSI', 785165.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(875, '2905393', 'TUBERIA BIAXIAL 18PULG PVC 200 PSI', 945972.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(876, '2905394', 'TUBERIA BIAXIAL 20PULG PVC 160 PSI', 1011620.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(877, '2905395', 'TUBERIA BIAXIAL 20PULG PVC 200 PSI', 1178320.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(878, '2900110', 'TUBERIA BIAXIAL 4PULG PVC 160 PSI', 49742.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(879, '2900109', 'TUBERIA BIAXIAL 4PULG PVC 200 PSI', 59073.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(880, '2900112', 'TUBERIA BIAXIAL 6PULG PVC 160 PSI', 99404.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(881, '2900111', 'TUBERIA BIAXIAL 6PULG PVC 200 PSI', 121366.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(882, '2900114', 'TUBERIA BIAXIAL 8PULG PVC 160 PSI', 169108.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(883, '2900113', 'TUBERIA BIAXIAL 8PULG PVC 200 PSI', 205580.00, 29, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(884, '2900092', 'TUBERIA ALCANTARILLADO NOVAFORT 160 MM S8', 46934.00, 15, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(885, '2900094', 'TUBERIA ALCANTARILLADO NOVAFORT 200 MM S8', 67093.00, 15, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(886, '2900081', 'TUBERIA ALCANTARILLADO NOVAFORT 250 MM S8', 97992.00, 15, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(887, '2900083', 'TUBERIA ALCANTARILLADO NOVAFORT 315 MM S8', 141005.00, 15, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(888, '2902493', 'TUBERIA ALCANTARILLADO NOVAFORT 355 MM S8', 211315.00, 15, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(889, '2900085', 'TUBERIA ALCANTARILLADO NOVAFORT 400 MM S8', 258462.00, 15, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(890, '2900087', 'TUBERIA ALCANTARILLADO NOVAFORT 450 MM S8', 355045.00, 15, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(891, '2900089', 'TUBERIA ALCANTARILLADO NOVAFORT 500 MM S8', 435181.00, 15, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(892, '2910542', 'TUBERIA PVC C900 DR 14 8PULG', 508487.00, 21, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(893, '2910541', 'TUBERIA PVC C900 DR 18 8PULG', 403561.00, 21, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(894, '2909174', 'JUNTA DE EXPANSION DE 2 CUERPOS 3PULG', 45664.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(895, '2901688', 'JUNTA DE EXPANSION DE 2 CUERPOS 4PULG', 52346.00, 6, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(896, '2911094', 'TUBERIA BIAXIAL 4PULG EXTREMO LISO PR 200 PSI', 72567.00, 30, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(897, '2911093', 'TUBERIA BIAXIAL 3PULG EXTREMO LISO PR 200 PSI', 42641.00, 30, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(898, '2911095', 'TUBERIA BIAXIAL 6PULG EXTREMO LISO PR 200 PSI', 154700.00, 30, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'ml', 6),
(899, '2911397', 'TAPON HD AWWA C110 4PULG - PAVCO', 488003.00, 27, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(900, '2911398', 'TAPON HD AWWA C110 6PULG - PAVCO', 832665.00, 27, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(901, '2911399', 'TAPON HD AWWA C110 8PULG - PAVCO', 1398040.00, 27, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(902, '2910945', 'ADAPTADOR A PARED PARA SIFON BLANCO - PAVCO', 10454.00, 10, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(903, '2910493', 'ACOPLE 1/2PULG x 1/2PULG (LAVAMANOS ) L = 60 CM', 4072.00, 3, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(904, '2911406', 'UNION HD AWWA C110 4PULG CXC', 1061860.00, 27, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(905, '2908620', 'TAPON INYECTADO NACIONAL 3PULG', 71273.00, 31, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(906, '65519', 'HOJA DE SEGUETA', 4699.00, 34, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(907, '46495', 'ESTOPA', 2299.00, 34, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(908, '2901040', 'CAJA GALVANIZADA 2400', 4180.00, 32, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(909, '2901326', 'SUPLEMENTO CAJA GALVANIZADA 2400', 1580.00, 32, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(910, '2901044', 'CAJA GALVANIZADA 5800', 2797.00, 32, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(911, '2901042', 'CAJA OCTAGONAL GALVANIZADA', 3783.00, 32, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(912, '2908616', 'CODO 45° 2PULG INYECTADO NACIONAL - PAVCO', 76406.00, 31, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(913, '2909092', 'CODO 45° 3PULG INYECTADO NACIONAL - PAVCO', 114837.00, 31, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(914, '2909093', 'CODO 45° 4PULG INYECTADO NACIONAL - PAVCO', 150169.00, 31, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(915, '2909094', 'CODO 45° 6PULG INYECTADO NACIONAL - PAVCO', 281889.00, 31, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(916, '2903537', 'TEE PVC UP RADIO CORTO 3PULG X 2PULG', 500585.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(917, '2903535', 'TEE PVC UP RADIO CORTO 4PULG X 2PULG', 771684.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(918, '2903533', 'TEE PVC UP RADIO CORTO 4PULG X 3PULG', 771684.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(919, '2903532', 'TEE PVC UP RADIO CORTO 6PULG X 3PULG', 1728210.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(920, '2903531', 'TEE PVC UP RADIO CORTO 6PULG X 4PULG', 1728210.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(921, '2903530', 'TEE PVC UP RADIO CORTO 8PULG X 4PULG', 2916840.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(922, '2903529', 'TEE PVC UP RADIO CORTO 8PULG X 6PULG', 2985420.00, 8, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(923, '2901845', 'BASE CAJA DE INSPECCION 315 (160 X 110)', 78631.00, 33, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(924, '2902824', 'BASE CAJA DE INSPECCION 400 (200 X 160)', 260142.00, 33, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(925, '2902538', 'ELEVADOR CAJA DE INSPECCION 315 (315 X 500)', 58130.00, 33, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(926, '2902540', 'ELEVADOR CAJA DE INSPECCION 400 (400 X 500)', 91297.00, 33, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(927, '2902539', 'ELEVADOR CAJA DE INSPECCION 315 (315 X 1000)', 102944.00, 33, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(928, '2902541', 'ELEVADOR CAJA DE INSPECCION 400 (400 X 1000)', 160964.00, 33, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(929, '2903703', 'AROTAPA PP CAJA (PAVCO) 315', 293979.00, 33, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(930, '2903702', 'AROTAPA PP CAJA (PAVCO) 400', 268716.00, 33, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(931, '2903562', 'AROTAPA PP CAJA (PAVCO) 1000/600', 1148120.00, 33, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(932, '2900825', 'ADAPTADOR TERMINAL PVC 1/2PULG', 734.00, 32, '2025-07-27 08:20:25', '2025-11-27 05:25:49', 'und', 1),
(935, '123', 'Producto Prueba 01', 100.00, 9, '2025-08-08 03:40:22', '2025-10-16 14:21:47', 'und', 1);

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_activas`
--

CREATE TABLE `sesiones_activas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(64) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `user_ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sesiones_activas`
--

INSERT INTO `sesiones_activas` (`id`, `user_id`, `session_token`, `created_at`, `expires_at`, `user_ip`) VALUES
(3, 1, 'c5173fce2d7e0d395ad93d00c42654451725802181d439d0d105c77055160e28', '2025-08-26 04:46:49', '2025-08-26 12:46:49', '::1'),
(4, 1, '38a6843c1bcd70ce5ad78e3b7d89f93d53da017aa855f7cea743af643d6627de', '2025-08-26 04:46:52', '2025-08-26 12:46:52', '::1'),
(7, 1, '52d1fe164bbd4df328b5073e4350d6b544cb8088321f847bc721beb46c4d2520', '2025-08-26 00:45:41', '2025-08-26 08:45:41', '::1'),
(8, 1, '29dcd1dee0a134838971e8a0991cc0dc25d484c06ec30cf943479ce1db7c3f5e', '2025-08-26 00:45:50', '2025-08-26 08:45:50', '::1'),
(9, 1, '87bae0fd002cda06543cd542760a0b9d5a482f06bc27911f46fa160aee19b4e4', '2025-08-26 00:48:07', '2025-08-26 08:48:07', '::1');

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
-- Indices de la tabla `comprobantes_entrega`
--
ALTER TABLE `comprobantes_entrega`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_qr_unique` (`codigo_qr`),
  ADD KEY `entrega_id` (`entrega_id`);

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
-- Indices de la tabla `entregas`
--
ALTER TABLE `entregas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `repartidor_id` (`repartidor_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `estado` (`estado`),
  ADD KEY `fecha_entrega` (`fecha_creacion`),
  ADD KEY `idx_envio_id` (`envio_id`);

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
-- Indices de la tabla `historial_entregas`
--
ALTER TABLE `historial_entregas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `entrega_id` (`entrega_id`),
  ADD KEY `fecha_accion` (`fecha_accion`),
  ADD KEY `fk_historial_repartidor` (`repartidor_id`);

--
-- Indices de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_username_timestamp` (`username`,`timestamp`);

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
  ADD KEY `idx_productos_precio` (`precio`),
  ADD KEY `idx_productos_unidad_medida` (`unidad_medida`);

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
-- Indices de la tabla `sesiones_activas`
--
ALTER TABLE `sesiones_activas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_token` (`session_token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `comprobantes_entrega`
--
ALTER TABLE `comprobantes_entrega`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `credenciales`
--
ALTER TABLE `credenciales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `descuentos_clientes`
--
ALTER TABLE `descuentos_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `detalle_orden_venta`
--
ALTER TABLE `detalle_orden_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `detalle_pedidos`
--
ALTER TABLE `detalle_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT de la tabla `direcciones_clientes`
--
ALTER TABLE `direcciones_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `entregas`
--
ALTER TABLE `entregas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `envios`
--
ALTER TABLE `envios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `grupos_productos`
--
ALTER TABLE `grupos_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `historial_entregas`
--
ALTER TABLE `historial_entregas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `orden_venta`
--
ALTER TABLE `orden_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=938;

--
-- AUTO_INCREMENT de la tabla `productos_favoritos`
--
ALTER TABLE `productos_favoritos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `sesiones_activas`
--
ALTER TABLE `sesiones_activas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `fk_clientes_empleado` FOREIGN KEY (`empleado_asignado`) REFERENCES `empleados` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `comprobantes_entrega`
--
ALTER TABLE `comprobantes_entrega`
  ADD CONSTRAINT `fk_comprobantes_entrega` FOREIGN KEY (`entrega_id`) REFERENCES `entregas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `credenciales`
--
ALTER TABLE `credenciales`
  ADD CONSTRAINT `fk_credenciales_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_credenciales_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `entregas`
--
ALTER TABLE `entregas`
  ADD CONSTRAINT `fk_entregas_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_entregas_repartidor` FOREIGN KEY (`repartidor_id`) REFERENCES `empleados` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `historial_entregas`
--
ALTER TABLE `historial_entregas`
  ADD CONSTRAINT `fk_historial_entregas` FOREIGN KEY (`entrega_id`) REFERENCES `entregas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_historial_repartidor` FOREIGN KEY (`repartidor_id`) REFERENCES `empleados` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `sesiones_activas`
--
ALTER TABLE `sesiones_activas`
  ADD CONSTRAINT `sesiones_activas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `credenciales` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
