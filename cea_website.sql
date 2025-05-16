-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-05-2025 a las 14:02:10
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
-- Base de datos: `cea_website`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `clave`, `valor`, `created_at`, `updated_at`) VALUES
(1, 'sitio_nombre', 'Centro de Estudios Avanzadoasdadasdadss', '2025-05-11 09:38:03', '2025-05-11 09:39:08'),
(2, 'sitio_descripcion', 'Centro de investigación y formación de alto nivels', '2025-05-11 09:38:03', '2025-05-11 09:39:13'),
(3, 'sitio_email', 'contacto@cea.edu.mxs', '2025-05-11 09:38:03', '2025-05-11 09:39:13'),
(4, 'sitio_telefono', '+52 (55) 1234-5678s', '2025-05-11 09:38:03', '2025-05-11 09:39:13'),
(5, 'sitio_direccion', 'Av. Universidad 3000, Ciudad Universitaria, Coyoacán, 04510 Ciudad de México, CDMXs', '2025-05-11 09:38:03', '2025-05-11 09:39:13'),
(6, 'tema_color_primario', '#4299e1', '2025-05-11 09:38:03', '2025-05-11 09:38:03'),
(7, 'tema_color_secundario', '#48bb78', '2025-05-11 09:38:03', '2025-05-11 09:38:03'),
(8, 'tema_fuente_principal', 'Arial, sans-serif', '2025-05-11 09:38:03', '2025-05-11 09:38:03'),
(9, 'tema_mostrar_slider', '1', '2025-05-11 09:38:03', '2025-05-11 09:38:03'),
(10, 'tema_items_por_pagina', '10', '2025-05-11 09:38:03', '2025-05-11 09:38:03'),
(11, 'sistema_modo_mantenimiento', '1', '2025-05-11 09:38:03', '2025-05-11 10:36:52'),
(12, 'sistema_mensaje_mantenimiento', 'Estamos realizando tareas de mantenimiento. Por favor, vuelva más tarde.', '2025-05-11 09:38:03', '2025-05-11 09:38:03'),
(13, 'sistema_cache_habilitado', '0', '2025-05-11 09:38:03', '2025-05-11 09:38:03'),
(14, 'sistema_tiempo_cache', '60', '2025-05-11 09:38:03', '2025-05-11 09:38:03'),
(15, 'sistema_registros_por_pagina', '20', '2025-05-11 09:38:03', '2025-05-11 09:38:03'),
(61, 'tema_logo', 'logo.jpeg', '2025-05-11 09:39:45', '2025-05-11 11:50:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactos`
--

CREATE TABLE `contactos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `asunto` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `respuesta` text DEFAULT NULL,
  `fecha_respuesta` datetime DEFAULT NULL,
  `respondido_por` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `fecha_envio` datetime DEFAULT current_timestamp(),
  `ip` varchar(45) DEFAULT NULL,
  `estado` enum('nuevo','leido','respondido','archivado') NOT NULL DEFAULT 'nuevo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `contactos`
--

INSERT INTO `contactos` (`id`, `nombre`, `email`, `asunto`, `mensaje`, `respuesta`, `fecha_respuesta`, `respondido_por`, `telefono`, `fecha_envio`, `ip`, `estado`) VALUES
(1, 'Leonel', 'leonelalmanza21@gmail.com', 'Prueba', 'asd', NULL, NULL, NULL, '', '2025-05-10 06:09:19', '::1', ''),
(2, 'asd', 'leonelalmanza21@gmail.com', 'asd', 'asd', 'Holamiamor', '2025-05-10 21:48:18', NULL, '', '2025-05-10 06:09:33', '::1', 'archivado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `convocatorias`
--

CREATE TABLE `convocatorias` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('activa','cerrada') NOT NULL DEFAULT 'activa',
  `imagen` varchar(255) DEFAULT NULL,
  `documento` varchar(255) DEFAULT NULL,
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `convocatorias`
--

INSERT INTO `convocatorias` (`id`, `titulo`, `descripcion`, `fecha_inicio`, `fecha_fin`, `estado`, `imagen`, `documento`, `fecha_publicacion`) VALUES
(4, 'ghjhjg', 'hgjh', '2025-05-11', '2025-05-11', 'activa', '68207e0635014_475439481_1294312945068948_7324853632957114351_n.jpg', '68207e06353d7_HorariosInformatica2025-2.pdf', '2025-05-11 10:37:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `instructor` varchar(100) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `horario` varchar(100) NOT NULL,
  `lugar` varchar(100) NOT NULL,
  `cupo` int(11) NOT NULL DEFAULT 20,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('abierto','cerrado','completo') NOT NULL DEFAULT 'abierto',
  `imagen` varchar(255) DEFAULT NULL,
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id`, `titulo`, `descripcion`, `instructor`, `fecha_inicio`, `fecha_fin`, `horario`, `lugar`, `cupo`, `precio`, `estado`, `imagen`, `fecha_publicacion`) VALUES
(3, 'Area de embutidosas', 'dasdsadasd', 'asdad', '2025-05-10', '2025-05-10', 'sdadad', 'dasda', 20, 1.00, 'abierto', '682039017ec16_Elaboración de paté.jpg', '2025-05-11 05:43:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('academico','cultural','administrativo','otro') NOT NULL DEFAULT 'otro',
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `cupo_maximo` int(11) DEFAULT NULL,
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `usuario_id` int(11) DEFAULT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  `enlace_registro` varchar(255) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `galeria`
--

CREATE TABLE `galeria` (
  `id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp(),
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `galeria`
--

INSERT INTO `galeria` (`id`, `categoria_id`, `usuario_id`, `titulo`, `descripcion`, `archivo`, `fecha_subida`, `fecha_creacion`) VALUES
(3, 1, 999, 'Area de embutidos', 'Procesado de la carne de cerdo y otros derivados en productos tratados con tecnología industrial.', '68206f58e51e9_Área de embutidos.png', '2025-05-10 23:05:30', '2025-05-10 23:05:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `galeria_categorias`
--

CREATE TABLE `galeria_categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `galeria_categorias`
--

INSERT INTO `galeria_categorias` (`id`, `nombre`, `descripcion`, `fecha_creacion`) VALUES
(1, 'Productos Carnicos', 'Si', '2025-05-10 22:35:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paginas`
--

CREATE TABLE `paginas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `contenido` text NOT NULL,
  `meta_descripcion` varchar(255) DEFAULT NULL,
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `paginas`
--

INSERT INTO `paginas` (`id`, `titulo`, `slug`, `contenido`, `meta_descripcion`, `ultima_actualizacion`) VALUES
(1, 'Inicio', 'home', '<h1>Bienvenidos al Centro de Estudios Avanzados</h1><p>El Centro de Estudios Avanzados (CEA) es una institución dedicada a la investigación y formación de alto nivel en diversas áreas del conocimiento.</p>', 'Centro de Estudios Avanzados - Página principal', '2025-05-10 00:30:12'),
(2, 'Misión y Visión', 'mision-vision', '<h1>Misión y Visión</h1><p>Nuestra misión es fomentar la investigación de calidad y la formación de recursos humanos altamente capacitados.</p>', 'Misión y Visión del Centro de Estudios Avanzados', '2025-05-10 00:30:12'),
(3, 'Historia', 'historia', '<h1>Historia</h1><p>El Centro de Estudios Avanzados fue fundado en 2010 con el objetivo de promover la investigación.</p>', 'Historia del Centro de Estudios Avanzados', '2025-05-11 03:28:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `rol` enum('admin','editor') NOT NULL DEFAULT 'editor',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `nombre`, `email`, `rol`, `fecha_creacion`, `reset_token`, `reset_expiry`) VALUES
(1, 'admin', '$2y$10$XjcGDK4RAEeJkQMqbFBVtOAqw6oI4LrBYTvtYSEA6jsqSPxzriMiq', 'Administrador', 'admin@cea.edu.mx', 'admin', '2025-05-10 00:30:12', NULL, NULL),
(2, 'Leonel', '$2y$10$t84IGhlwLQBPfiYi/WKit.M7M07UjsXt7Yj3EnSkKwZ/uapM1Nsy.', 'Leonel Almanza Medina', 'leonelalmanza21@gmail.com', 'editor', '2025-05-10 00:30:12', '0dbbde3757b572a64dc7af79cafd208b44d219c3cc40124fee98b9a6917e231e', '2025-05-11 11:53:37'),
(3, '422033662', '$2y$10$dkF.vHT5yh7UdQX2cVcB3eA./ACIhQkg60eTMLK/1DK5nJSTRAAWq', 'Leonel Almanza Medina', 'leonelalmanza21@gmail.com', 'admin', '2025-07-13 04:18:37', NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indices de la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `convocatorias`
--
ALTER TABLE `convocatorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `galeria`
--
ALTER TABLE `galeria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `galeria_categorias`
--
ALTER TABLE `galeria_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `paginas`
--
ALTER TABLE `paginas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT de la tabla `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `convocatorias`
--
ALTER TABLE `convocatorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `galeria`
--
ALTER TABLE `galeria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `galeria_categorias`
--
ALTER TABLE `galeria_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `paginas`
--
ALTER TABLE `paginas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `galeria`
--
ALTER TABLE `galeria`
  ADD CONSTRAINT `galeria_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `galeria_categorias` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
