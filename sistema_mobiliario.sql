-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-11-2025 a las 22:26:59
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
-- Base de datos: `sistema_mobiliario`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
--

CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `responsable_id` int(11) DEFAULT NULL,
  `activo` tinyint(4) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `departamentos`
--

INSERT INTO `departamentos` (`id`, `nombre`, `descripcion`, `responsable_id`, `activo`, `fecha_creacion`) VALUES
(1, 'Dirección General', 'Dirección general de la empresa', NULL, 1, '2025-11-05 19:18:29'),
(2, 'Recursos Humanos', 'Gestión del capital humano', NULL, 1, '2025-11-05 19:18:29'),
(3, 'Producción Madera', 'Área de fabricación en madera', NULL, 1, '2025-11-05 19:18:29'),
(4, 'Producción Metal', 'Área de fabricación en metal', NULL, 1, '2025-11-05 19:18:29'),
(5, 'Ventas y Marketing', 'Área comercial y atención a clientes', NULL, 1, '2025-11-05 19:18:29'),
(6, 'Contabilidad y Finanzas', 'Gestión financiera y contable', NULL, 1, '2025-11-05 19:18:29'),
(7, 'Almacén e Inventario', 'Control de inventario y materiales', NULL, 1, '2025-11-05 19:18:29'),
(8, 'Sistemas y TI', 'Tecnologías de la información', NULL, 1, '2025-11-05 19:18:29'),
(9, 'Calidad', 'Control de calidad y procesos', NULL, 1, '2025-11-05 19:18:29'),
(10, 'Diseño', 'Diseño de productos y planos', NULL, 1, '2025-11-05 19:18:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
  `codigo_empleado` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` enum('M','F','Otro') DEFAULT NULL,
  `departamento_id` int(11) DEFAULT NULL,
  `puesto_id` int(11) DEFAULT NULL,
  `fecha_contratacion` date DEFAULT NULL,
  `salario_base` decimal(10,2) DEFAULT NULL,
  `tipo_contrato` enum('Indeterminado','Temporal','Practicas','Formacion') DEFAULT NULL,
  `activo` tinyint(4) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `codigo_empleado`, `nombre`, `apellidos`, `email`, `telefono`, `direccion`, `fecha_nacimiento`, `genero`, `departamento_id`, `puesto_id`, `fecha_contratacion`, `salario_base`, `tipo_contrato`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'EMP-001', 'Carlos', 'Martínez López', 'carlos.martinez@empresa.com', '555-0101', NULL, NULL, NULL, 1, 1, '2020-01-15', 50000.00, 'Indeterminado', 1, '2025-11-05 19:18:29', '2025-11-05 19:18:29'),
(2, 'EMP-002', 'Ana', 'García Rodríguez', 'ana.garcia@empresa.com', '555-0102', NULL, NULL, NULL, 2, 2, '2021-03-10', 35000.00, 'Indeterminado', 1, '2025-11-05 19:18:29', '2025-11-05 19:18:29'),
(3, 'EMP-003', 'Luis', 'Hernández Pérez', 'luis.hernandez@empresa.com', '555-0103', NULL, NULL, NULL, 3, 4, '2022-06-20', 22000.00, 'Indeterminado', 1, '2025-11-05 19:18:29', '2025-11-05 19:18:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nominas`
--

CREATE TABLE `nominas` (
  `id` int(11) NOT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `periodo` date NOT NULL,
  `salario_bruto` decimal(10,2) DEFAULT NULL,
  `deducciones` decimal(10,2) DEFAULT NULL,
  `salario_neto` decimal(10,2) DEFAULT NULL,
  `horas_trabajadas` int(11) DEFAULT NULL,
  `horas_extra` int(11) DEFAULT NULL,
  `bono_productividad` decimal(10,2) DEFAULT NULL,
  `otros_conceptos` decimal(10,2) DEFAULT NULL,
  `estado` enum('Pendiente','Calculada','Pagada','Cancelada') DEFAULT 'Pendiente',
  `fecha_calculada` timestamp NULL DEFAULT NULL,
  `fecha_pagada` timestamp NULL DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puestos`
--

CREATE TABLE `puestos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `departamento_id` int(11) DEFAULT NULL,
  `salario_base` decimal(10,2) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(4) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `puestos`
--

INSERT INTO `puestos` (`id`, `nombre`, `departamento_id`, `salario_base`, `descripcion`, `activo`, `fecha_creacion`) VALUES
(1, 'Director General', 1, 50000.00, NULL, 1, '2025-11-05 19:18:29'),
(2, 'Gerente de RH', 2, 35000.00, NULL, 1, '2025-11-05 19:18:29'),
(3, 'Asistente de RH', 2, 18000.00, NULL, 1, '2025-11-05 19:18:29'),
(4, 'Operador CNC Madera', 3, 22000.00, NULL, 1, '2025-11-05 19:18:29'),
(5, 'Carpintero', 3, 20000.00, NULL, 1, '2025-11-05 19:18:29'),
(6, 'Ensamblador', 3, 18000.00, NULL, 1, '2025-11-05 19:18:29'),
(7, 'Soldador', 4, 23000.00, NULL, 1, '2025-11-05 19:18:29'),
(8, 'Operador CNC Metal', 4, 24000.00, NULL, 1, '2025-11-05 19:18:29'),
(9, 'Vendedor', 5, 15000.00, NULL, 1, '2025-11-05 19:18:29'),
(10, 'Contador', 6, 28000.00, NULL, 1, '2025-11-05 19:18:29'),
(11, 'Almacenista', 7, 16000.00, NULL, 1, '2025-11-05 19:18:29'),
(12, 'Soporte TI', 8, 20000.00, NULL, 1, '2025-11-05 19:18:29'),
(13, 'Inspector de Calidad', 9, 21000.00, NULL, 1, '2025-11-05 19:18:29'),
(14, 'Diseñador Industrial', 10, 25000.00, NULL, 1, '2025-11-05 19:18:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `rol` enum('super_admin','director','gerente_rh','empleado') DEFAULT 'empleado',
  `empleado_id` int(11) DEFAULT NULL,
  `activo` tinyint(4) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `email`, `password`, `nombre`, `rol`, `empleado_id`, `activo`, `fecha_creacion`, `ultimo_login`) VALUES
(1, 'admin', 'admin@sistemamobiliario.com', '$2y$10$r3B7oJkq9zQL8QLxLc1L.uLb6s5q5w5e5Y5Y5Y5Y5Y5Y5Y5Y5Y', 'Administrador del Sistema', 'super_admin', NULL, 1, '2025-11-05 19:18:29', NULL),
(2, 'carlos.martinez', 'carlos.martinez@empresa.com', '$2y$10$r3B7oJkq9zQL8QLxLc1L.uLb6s5q5w5e5Y5Y5Y5Y5Y5Y5Y5Y5Y', 'Carlos Martínez López', 'director', 1, 1, '2025-11-05 19:18:29', NULL),
(3, 'ana.garcia', 'ana.garcia@empresa.com', '$2y$10$r3B7oJkq9zQL8QLxLc1L.uLb6s5q5w5e5Y5Y5Y5Y5Y5Y5Y5Y5Y', 'Ana García Rodríguez', 'gerente_rh', 2, 1, '2025-11-05 19:18:29', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_empleado` (`codigo_empleado`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `departamento_id` (`departamento_id`),
  ADD KEY `puesto_id` (`puesto_id`);

--
-- Indices de la tabla `nominas`
--
ALTER TABLE `nominas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empleado_id` (`empleado_id`);

--
-- Indices de la tabla `puestos`
--
ALTER TABLE `puestos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `departamento_id` (`departamento_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `empleado_id` (`empleado_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `nominas`
--
ALTER TABLE `nominas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `puestos`
--
ALTER TABLE `puestos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD CONSTRAINT `empleados_ibfk_1` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `empleados_ibfk_2` FOREIGN KEY (`puesto_id`) REFERENCES `puestos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `nominas`
--
ALTER TABLE `nominas`
  ADD CONSTRAINT `nominas_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `puestos`
--
ALTER TABLE `puestos`
  ADD CONSTRAINT `puestos_ibfk_1` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
