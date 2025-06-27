-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 26-06-2025 a las 22:56:53
-- Versión del servidor: 10.11.10-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u837350477_Cuestionario`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellido1` varchar(100) NOT NULL,
  `apellido2` varchar(100) DEFAULT NULL,
  `correo` varchar(150) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `fecha_alta` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin_cond`
--

CREATE TABLE `admin_cond` (
  `id_admin` int(11) NOT NULL,
  `id_condominio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calles`
--

CREATE TABLE `calles` (
  `id_calle` int(11) NOT NULL,
  `id_condominio` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `casas`
--

CREATE TABLE `casas` (
  `id_casa` int(11) NOT NULL,
  `casa` varchar(255) NOT NULL,
  `id_condominio` int(11) NOT NULL,
  `id_calle` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `condominios`
--

CREATE TABLE `condominios` (
  `id_condominio` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `direccion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados_condominio`
--

CREATE TABLE `empleados_condominio` (
  `id_empleado` int(11) NOT NULL,
  `id_condominio` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellido1` varchar(100) NOT NULL,
  `apellido2` varchar(100) NOT NULL,
  `puesto` enum('servicio','administracion','mantenimiento') NOT NULL,
  `fecha_contrato` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `engomados`
--

CREATE TABLE `engomados` (
  `id_engomado` int(11) NOT NULL,
  `id_persona` int(11) NOT NULL,
  `id_condominio` int(11) NOT NULL,
  `id_casa` int(11) NOT NULL,
  `id_calle` int(11) NOT NULL,
  `placa` varchar(20) NOT NULL,
  `modelo` varchar(100) NOT NULL,
  `color` varchar(50) NOT NULL,
  `ano` year(4) NOT NULL,
  `foto` tinytext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entrance`
--

CREATE TABLE `entrance` (
  `id_entrance` int(11) NOT NULL,
  `id_persona` int(11) NOT NULL,
  `id_condominio` int(11) NOT NULL,
  `id_calle` int(11) NOT NULL,
  `id_casa` int(11) NOT NULL,
  `nombre_accesante` varchar(100) NOT NULL,
  `hora_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `hora_entrada` datetime DEFAULT NULL,
  `hora_salida` datetime DEFAULT NULL,
  `utilizado` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = no usado, 1 = usado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

CREATE TABLE `personas` (
  `id_persona` int(11) NOT NULL,
  `id_casa` int(11) NOT NULL,
  `id_condominio` int(11) NOT NULL,
  `id_calle` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellido1` varchar(100) NOT NULL,
  `apellido2` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `correo_electronico` varchar(150) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `jerarquia` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = administrador, 0 = residente',
  `curp` char(18) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tags`
--

CREATE TABLE `tags` (
  `id_persona` int(11) NOT NULL,
  `id_condominio` int(11) NOT NULL,
  `id_casa` int(11) NOT NULL,
  `id_calle` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id_tarea` int(11) NOT NULL,
  `id_condominio` int(11) NOT NULL,
  `id_calle` int(11) NOT NULL,
  `id_trabajador` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `imagen` tinytext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `admin_cond`
--
ALTER TABLE `admin_cond`
  ADD PRIMARY KEY (`id_admin`,`id_condominio`),
  ADD KEY `idx_ac_admin` (`id_admin`),
  ADD KEY `idx_ac_condominio` (`id_condominio`);

--
-- Indices de la tabla `calles`
--
ALTER TABLE `calles`
  ADD PRIMARY KEY (`id_calle`),
  ADD KEY `idx_calles_condominio` (`id_condominio`);

--
-- Indices de la tabla `casas`
--
ALTER TABLE `casas`
  ADD PRIMARY KEY (`id_casa`),
  ADD KEY `idx_casas_condominio` (`id_condominio`),
  ADD KEY `idx_casas_calle` (`id_calle`);

--
-- Indices de la tabla `condominios`
--
ALTER TABLE `condominios`
  ADD PRIMARY KEY (`id_condominio`);

--
-- Indices de la tabla `empleados_condominio`
--
ALTER TABLE `empleados_condominio`
  ADD PRIMARY KEY (`id_empleado`),
  ADD KEY `idx_emp_condominio` (`id_condominio`);

--
-- Indices de la tabla `engomados`
--
ALTER TABLE `engomados`
  ADD PRIMARY KEY (`id_engomado`),
  ADD KEY `idx_eng_persona` (`id_persona`),
  ADD KEY `idx_eng_condominio` (`id_condominio`),
  ADD KEY `idx_eng_casa` (`id_casa`),
  ADD KEY `idx_eng_calle` (`id_calle`);

--
-- Indices de la tabla `entrance`
--
ALTER TABLE `entrance`
  ADD PRIMARY KEY (`id_entrance`),
  ADD KEY `idx_ent_persona` (`id_persona`),
  ADD KEY `idx_ent_condominio` (`id_condominio`),
  ADD KEY `idx_ent_calle` (`id_calle`),
  ADD KEY `idx_ent_casa` (`id_casa`);

--
-- Indices de la tabla `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`id_persona`),
  ADD UNIQUE KEY `uk_curp` (`curp`),
  ADD UNIQUE KEY `uk_personas_email` (`correo_electronico`),
  ADD KEY `idx_id_casa` (`id_casa`),
  ADD KEY `idx_personas_condominio` (`id_condominio`),
  ADD KEY `idx_personas_calle` (`id_calle`),
  ADD KEY `idx_personas_email` (`correo_electronico`);

--
-- Indices de la tabla `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id_persona`,`id_casa`),
  ADD KEY `idx_tags_persona` (`id_persona`),
  ADD KEY `idx_tags_condominio` (`id_condominio`),
  ADD KEY `idx_tags_casa` (`id_casa`),
  ADD KEY `idx_tags_calle` (`id_calle`);

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id_tarea`),
  ADD KEY `idx_tareas_condominio` (`id_condominio`),
  ADD KEY `idx_tareas_calle` (`id_calle`),
  ADD KEY `idx_tareas_trabajador` (`id_trabajador`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `calles`
--
ALTER TABLE `calles`
  MODIFY `id_calle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `casas`
--
ALTER TABLE `casas`
  MODIFY `id_casa` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `condominios`
--
ALTER TABLE `condominios`
  MODIFY `id_condominio` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empleados_condominio`
--
ALTER TABLE `empleados_condominio`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `engomados`
--
ALTER TABLE `engomados`
  MODIFY `id_engomado` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `entrance`
--
ALTER TABLE `entrance`
  MODIFY `id_entrance` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personas`
--
ALTER TABLE `personas`
  MODIFY `id_persona` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id_tarea` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `admin_cond`
--
ALTER TABLE `admin_cond`
  ADD CONSTRAINT `fk_ac_admin` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ac_condominio` FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `calles`
--
ALTER TABLE `calles`
  ADD CONSTRAINT `fk_calles_condominio` FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `casas`
--
ALTER TABLE `casas`
  ADD CONSTRAINT `fk_casas_calle` FOREIGN KEY (`id_calle`) REFERENCES `calles` (`id_calle`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_casas_condominio` FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `empleados_condominio`
--
ALTER TABLE `empleados_condominio`
  ADD CONSTRAINT `fk_emp_condominio` FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `engomados`
--
ALTER TABLE `engomados`
  ADD CONSTRAINT `fk_engomados_calles` FOREIGN KEY (`id_calle`) REFERENCES `calles` (`id_calle`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_engomados_casas` FOREIGN KEY (`id_casa`) REFERENCES `casas` (`id_casa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_engomados_condominios` FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_engomados_personas` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `entrance`
--
ALTER TABLE `entrance`
  ADD CONSTRAINT `fk_ent_calle` FOREIGN KEY (`id_calle`) REFERENCES `calles` (`id_calle`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ent_casa` FOREIGN KEY (`id_casa`) REFERENCES `casas` (`id_casa`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ent_condominio` FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ent_persona` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `personas`
--
ALTER TABLE `personas`
  ADD CONSTRAINT `fk_persona_casa` FOREIGN KEY (`id_casa`) REFERENCES `casas` (`id_casa`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_personas_calle` FOREIGN KEY (`id_calle`) REFERENCES `calles` (`id_calle`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_personas_condominio` FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tags`
--
ALTER TABLE `tags`
  ADD CONSTRAINT `fk_tags_calles` FOREIGN KEY (`id_calle`) REFERENCES `calles` (`id_calle`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tags_casas` FOREIGN KEY (`id_casa`) REFERENCES `casas` (`id_casa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tags_condominios` FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tags_personas_persona` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD CONSTRAINT `fk_tareas_calle` FOREIGN KEY (`id_calle`) REFERENCES `calles` (`id_calle`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tareas_condominio` FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tareas_trabajador` FOREIGN KEY (`id_trabajador`) REFERENCES `empleados_condominio` (`id_empleado`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
