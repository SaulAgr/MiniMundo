-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-06-2025 a las 23:47:28
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
-- Base de datos: `sistema_educativo`
--
CREATE DATABASE IF NOT EXISTS `sistema_educativo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sistema_educativo`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `avisos`
--

DROP TABLE IF EXISTS `avisos`;
CREATE TABLE `avisos` (
  `id` int(11) NOT NULL,
  `clase_id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `mensaje` text NOT NULL,
  `urgente` tinyint(1) DEFAULT 0,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Avisos de profesores a padres';

--
-- Volcado de datos para la tabla `avisos`
--

INSERT INTO `avisos` (`id`, `clase_id`, `profesor_id`, `titulo`, `mensaje`, `urgente`, `fecha`) VALUES
(1, 13, 36, 'Recordatorio', 'No olvidar traer el libro de inglés', 0, '2025-06-09 23:43:14'),
(2, 14, 37, 'Actividad', 'Llevar materiales de listening', 1, '2025-06-09 23:43:14'),
(3, 13, 36, 'asda', 'asdasd', 0, '2025-06-10 08:18:01'),
(12, 15, 36, 'asda', 'asda', 0, '2025-06-10 08:43:10'),
(13, 13, 36, 'lkasndl', 'kjsnddkja', 0, '2025-06-10 08:49:03'),
(14, 13, 36, 'sdfs', 'a', 0, '2025-06-10 08:59:32'),
(15, 13, 36, 'prueba', '1', 0, '2025-06-13 03:40:18'),
(16, 15, 36, 'ejercicio 2', '1', 0, '2025-06-13 03:40:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `avisos_comentarios`
--

DROP TABLE IF EXISTS `avisos_comentarios`;
CREATE TABLE `avisos_comentarios` (
  `id` int(11) NOT NULL,
  `aviso_id` int(11) NOT NULL,
  `padre_id` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Comentarios de padres en avisos';

--
-- Volcado de datos para la tabla `avisos_comentarios`
--

INSERT INTO `avisos_comentarios` (`id`, `aviso_id`, `padre_id`, `mensaje`, `fecha`) VALUES
(1, 1, 41, 'Gracias por el aviso', '2025-06-09 23:43:14'),
(2, 2, 42, 'Entendido, gracias', '2025-06-09 23:43:14'),
(3, 16, 41, 'no c', '2025-06-13 03:57:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificaciones`
--

DROP TABLE IF EXISTS `calificaciones`;
CREATE TABLE `calificaciones` (
  `alumno_id` int(11) NOT NULL,
  `clase_id` int(11) NOT NULL,
  `calificacion` decimal(5,2) DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  `periodo_evaluacion` varchar(50) DEFAULT NULL,
  `fecha_calificacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Calificaciones de los alumnos';

--
-- Volcado de datos para la tabla `calificaciones`
--

INSERT INTO `calificaciones` (`alumno_id`, `clase_id`, `calificacion`, `observacion`, `periodo_evaluacion`, `fecha_calificacion`) VALUES
(34, 13, 9.50, 'Excelente avance', 'Parcial 1', '2025-06-09 23:43:14'),
(34, 14, 8.70, 'Buen trabajo', 'Parcial 1', '2025-06-09 23:43:14'),
(34, 15, 9.00, 'beri gud', NULL, '2025-06-12 20:07:55'),
(35, 14, 7.80, 'Debe practicar más listening', 'Parcial 1', '2025-06-09 23:43:14'),
(116, 15, 6.00, 'nais', NULL, '2025-06-12 20:50:22'),
(133, 13, 8.00, 'nais', NULL, '2025-06-12 20:04:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clases`
--

DROP TABLE IF EXISTS `clases`;
CREATE TABLE `clases` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `profesor_id` int(11) DEFAULT NULL,
  `periodo` varchar(20) NOT NULL,
  `coordinador_id` int(11) DEFAULT NULL,
  `cerrada` tinyint(1) DEFAULT 0,
  `cupo_maximo` int(11) DEFAULT 30,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Clases y materias del sistema';

--
-- Volcado de datos para la tabla `clases`
--

INSERT INTO `clases` (`id`, `nombre`, `descripcion`, `profesor_id`, `periodo`, `coordinador_id`, `cerrada`, `cupo_maximo`, `created_at`) VALUES
(13, 'Inglés Básico', 'Curso introductorio de inglés para principiantes', 36, '2025-1', 38, 0, 30, '2025-06-09 23:43:14'),
(14, 'Inglés Intermedio', 'Curso de inglés nivel intermedio', 37, '2025-1', 38, 0, 30, '2025-06-09 23:43:14'),
(15, 'Inglés Avanzado', 'Curso avanzado de inglés conversacional', 36, '2025-2', 38, 0, 30, '2025-06-09 23:43:14'),
(17, 'prueba', '', 44, '2025-4', 38, 0, 30, '2025-06-12 03:26:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hijos`
--

DROP TABLE IF EXISTS `hijos`;
CREATE TABLE `hijos` (
  `id` int(11) NOT NULL,
  `padre_id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `parentesco` enum('padre','madre','tutor','abuelo','otro') DEFAULT 'padre',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relación padres-hijos';

--
-- Volcado de datos para la tabla `hijos`
--

INSERT INTO `hijos` (`id`, `padre_id`, `alumno_id`, `parentesco`, `fecha_registro`) VALUES
(1, 41, 34, 'padre', '2025-06-09 23:43:14'),
(2, 42, 35, 'madre', '2025-06-09 23:43:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones`
--

DROP TABLE IF EXISTS `inscripciones`;
CREATE TABLE `inscripciones` (
  `id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `clase_id` int(11) NOT NULL,
  `aprobada` tinyint(1) DEFAULT 0,
  `fecha_inscripcion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_aprobacion` timestamp NULL DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Inscripciones de alumnos en clases';

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`id`, `alumno_id`, `clase_id`, `aprobada`, `fecha_inscripcion`, `fecha_aprobacion`, `observaciones`) VALUES
(6, 35, 14, 1, '2025-06-09 23:43:14', NULL, NULL),
(7, 35, 15, 0, '2025-06-09 23:43:14', NULL, NULL),
(8, 34, 15, 1, '2025-06-10 00:07:37', '2025-06-10 01:25:44', NULL),
(9, 34, 13, 1, '2025-06-10 00:47:49', NULL, NULL),
(10, 35, 13, 1, '2025-06-10 00:47:49', NULL, NULL),
(11, 34, 14, 1, '2025-06-10 01:01:27', '2025-06-12 17:08:49', NULL),
(12, 45, 14, 1, '2025-06-12 02:58:13', NULL, NULL),
(13, 116, 15, 1, '2025-06-12 02:58:19', NULL, NULL),
(14, 133, 13, 1, '2025-06-12 15:51:51', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes_padre_profesor`
--

DROP TABLE IF EXISTS `mensajes_padre_profesor`;
CREATE TABLE `mensajes_padre_profesor` (
  `id` int(11) NOT NULL,
  `padre_id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `de_quien` enum('padre','profesor') NOT NULL,
  `leido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mensajes entre padres y profesores';

--
-- Volcado de datos para la tabla `mensajes_padre_profesor`
--

INSERT INTO `mensajes_padre_profesor` (`id`, `padre_id`, `profesor_id`, `mensaje`, `fecha`, `de_quien`, `leido`) VALUES
(1, 41, 36, '¿Cómo va mi hijo?', '2025-06-09 23:43:14', 'padre', 0),
(2, 36, 41, 'Va muy bien, gracias por preguntar.', '2025-06-09 23:43:14', 'profesor', 0),
(3, 41, 36, 'as', '2025-06-10 08:56:40', 'profesor', 0),
(4, 42, 36, 'Hola', '2025-06-10 08:59:46', 'profesor', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

DROP TABLE IF EXISTS `reportes`;
CREATE TABLE `reportes` (
  `id` int(11) NOT NULL,
  `clase_id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `profesor_id` int(11) NOT NULL,
  `titulo` varchar(200) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `tipo_reporte` enum('disciplinario','academico','conductual') DEFAULT 'academico',
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Reportes sobre alumnos';

--
-- Volcado de datos para la tabla `reportes`
--

INSERT INTO `reportes` (`id`, `clase_id`, `alumno_id`, `profesor_id`, `titulo`, `descripcion`, `comentario`, `tipo_reporte`, `fecha`) VALUES
(1, 13, 34, 36, 'Falta a clase', 'Faltó el lunes', 'Avisar a padres', 'disciplinario', '2025-06-09 23:43:14'),
(2, 14, 35, 37, 'Participación', 'Participó mucho en clase', '', 'academico', '2025-06-09 23:43:14'),
(3, 13, 35, 36, 'lkjdfls', 'lslif', NULL, 'academico', '2025-06-10 08:52:43'),
(4, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:15'),
(5, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:16'),
(6, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:16'),
(7, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:17'),
(8, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:17'),
(9, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:17'),
(10, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:17'),
(11, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:17'),
(12, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:24'),
(13, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:27'),
(14, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:27'),
(15, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:27'),
(16, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:28'),
(17, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:29'),
(18, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:29'),
(19, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:30'),
(20, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:30'),
(21, 15, 35, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:52:30'),
(22, 13, 133, 36, 'prueba', 'prueba de reporte', NULL, 'academico', '2025-06-13 03:56:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Roles del sistema educativo';

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `created_at`) VALUES
(1, 'Alumno', 'Estudiante del sistema educativo', '2025-06-09 23:43:14'),
(2, 'Profesor', 'Docente que imparte clases', '2025-06-09 23:43:14'),
(3, 'Coordinador', 'Coordinador académico', '2025-06-09 23:43:14'),
(4, 'Director', 'Director de la institución', '2025-06-09 23:43:14'),
(5, 'Admin', 'Administrador del sistema', '2025-06-09 23:43:14'),
(6, 'Padre', 'Padre de familia o tutor', '2025-06-09 23:43:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_accion`
--

DROP TABLE IF EXISTS `solicitudes_accion`;
CREATE TABLE `solicitudes_accion` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `solicitante_id` int(11) NOT NULL,
  `estado` enum('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
  `motivo` text DEFAULT NULL,
  `respuesta` text DEFAULT NULL,
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_respuesta` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Solicitudes de acciones administrativas';

--
-- Volcado de datos para la tabla `solicitudes_accion`
--

INSERT INTO `solicitudes_accion` (`id`, `tipo`, `usuario_id`, `solicitante_id`, `estado`, `motivo`, `respuesta`, `fecha_solicitud`, `fecha_respuesta`) VALUES
(1, 'Cambio de grupo', 34, 41, 'pendiente', 'Solicito cambio por horario', NULL, '2025-06-09 23:43:14', NULL),
(2, 'Baja temporal', 35, 42, 'pendiente', 'Vacaciones familiares', NULL, '2025-06-09 23:43:14', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

DROP TABLE IF EXISTS `tareas`;
CREATE TABLE `tareas` (
  `id` int(11) NOT NULL,
  `clase_id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_entrega` date NOT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tareas y asignaciones';

--
-- Volcado de datos para la tabla `tareas`
--

INSERT INTO `tareas` (`id`, `clase_id`, `titulo`, `descripcion`, `fecha_entrega`, `archivo`, `activa`, `fecha_creacion`) VALUES
(1, 13, 'Tarea 1', 'Completar ejercicios del libro, página 10', '2025-06-20', NULL, 1, '2025-06-09 23:43:14'),
(2, 14, 'Essay', 'Redactar un ensayo sobre tus vacaciones', '2025-06-22', NULL, 1, '2025-06-09 23:43:14'),
(3, 15, 'Listening Practice', 'Escuchar el audio y responder preguntas', '2025-07-01', NULL, 1, '2025-06-09 23:43:14'),
(4, 15, 'aslkda', 'amnsdjkas', '2025-06-25', '', 1, '2025-06-10 00:17:49'),
(5, 13, 'prueba', 'prueba de tarea', '2025-07-04', '', 1, '2025-06-12 03:52:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Usuarios del sistema educativo';

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol_id`, `activo`, `fecha_registro`, `ultimo_acceso`) VALUES
(34, 'Ana Alumno', 'ana.alumno@sistema.edu', '1234', 1, 1, '2025-06-09 23:43:14', NULL),
(35, 'Pedro Alumno', 'pedro.alumno@sistema.edu', '1234', 1, 0, '2025-06-09 23:43:14', NULL),
(36, 'Luis Prof', 'luis.profesor@sistema.edu', '1234', 2, 1, '2025-06-09 23:43:14', NULL),
(37, 'Laura Profesora', 'laura.profesor@sistema.edu', '1234', 2, 1, '2025-06-09 23:43:14', NULL),
(38, 'Marta Coordinadora', 'marta.coordinador@sistema.edu', '1234', 3, 1, '2025-06-09 23:43:14', NULL),
(39, 'Carlos Director', 'carlos.director@sistema.edu', '1234', 4, 1, '2025-06-09 23:43:14', NULL),
(40, 'Sofia Admin', 'sofia.admin@sistema.edu', '1234', 5, 1, '2025-06-09 23:43:14', NULL),
(41, 'Jose Padre', 'jose.padre@sistema.edu', '1234', 6, 1, '2025-06-09 23:43:14', NULL),
(42, 'Maria Madre', 'maria.madre@sistema.edu', '1234', 6, 1, '2025-06-09 23:43:14', NULL),
(43, 'juan', 'juan@gmail.com', '123', 3, 1, '2025-06-10 01:27:01', NULL),
(44, 'asd', 'juanprofe@sistema.edu', '1234', 2, 1, '2025-06-10 02:19:09', NULL),
(45, 'asdml', 'asdr@sistema.edu', '1234', 1, 1, '2025-06-10 02:19:24', NULL),
(116, 'prueba alumno', 'prueba.alumno@sistema.edu', '1234', 1, 1, '2025-06-12 02:55:21', NULL),
(133, 'aadw', 'adasd@sistema.edu', '1234', 1, 1, '2025-06-12 02:59:19', NULL),
(134, 'profp', 'profp.profesor@sistema.edu', '1234', 2, 1, '2025-06-12 16:02:50', NULL);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_clases_completo`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `v_clases_completo`;
CREATE TABLE `v_clases_completo` (
`id` int(11)
,`nombre` varchar(100)
,`descripcion` text
,`profesor` varchar(100)
,`coordinador` varchar(100)
,`periodo` varchar(20)
,`cerrada` tinyint(1)
,`cupo_maximo` int(11)
,`inscritos` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_familia`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `v_familia`;
CREATE TABLE `v_familia` (
`id` int(11)
,`padre` varchar(100)
,`hijo` varchar(100)
,`parentesco` enum('padre','madre','tutor','abuelo','otro')
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_inscripciones_completo`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `v_inscripciones_completo`;
CREATE TABLE `v_inscripciones_completo` (
`id` int(11)
,`alumno` varchar(100)
,`clase` varchar(100)
,`profesor` varchar(100)
,`aprobada` tinyint(1)
,`fecha_inscripcion` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_usuarios_completo`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `v_usuarios_completo`;
CREATE TABLE `v_usuarios_completo` (
`id` int(11)
,`nombre` varchar(100)
,`email` varchar(100)
,`rol` varchar(50)
,`activo` tinyint(1)
,`fecha_registro` timestamp
,`ultimo_acceso` timestamp
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_clases_completo`
--
DROP TABLE IF EXISTS `v_clases_completo`;

DROP VIEW IF EXISTS `v_clases_completo`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_clases_completo`  AS SELECT `c`.`id` AS `id`, `c`.`nombre` AS `nombre`, `c`.`descripcion` AS `descripcion`, `p`.`nombre` AS `profesor`, `coord`.`nombre` AS `coordinador`, `c`.`periodo` AS `periodo`, `c`.`cerrada` AS `cerrada`, `c`.`cupo_maximo` AS `cupo_maximo`, count(`i`.`id`) AS `inscritos` FROM (((`clases` `c` left join `usuarios` `p` on(`c`.`profesor_id` = `p`.`id`)) left join `usuarios` `coord` on(`c`.`coordinador_id` = `coord`.`id`)) left join `inscripciones` `i` on(`c`.`id` = `i`.`clase_id` and `i`.`aprobada` = 1)) GROUP BY `c`.`id` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_familia`
--
DROP TABLE IF EXISTS `v_familia`;

DROP VIEW IF EXISTS `v_familia`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_familia`  AS SELECT `h`.`id` AS `id`, `p`.`nombre` AS `padre`, `a`.`nombre` AS `hijo`, `h`.`parentesco` AS `parentesco` FROM ((`hijos` `h` join `usuarios` `p` on(`h`.`padre_id` = `p`.`id`)) join `usuarios` `a` on(`h`.`alumno_id` = `a`.`id`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_inscripciones_completo`
--
DROP TABLE IF EXISTS `v_inscripciones_completo`;

DROP VIEW IF EXISTS `v_inscripciones_completo`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_inscripciones_completo`  AS SELECT `i`.`id` AS `id`, `a`.`nombre` AS `alumno`, `c`.`nombre` AS `clase`, `p`.`nombre` AS `profesor`, `i`.`aprobada` AS `aprobada`, `i`.`fecha_inscripcion` AS `fecha_inscripcion` FROM (((`inscripciones` `i` join `usuarios` `a` on(`i`.`alumno_id` = `a`.`id`)) join `clases` `c` on(`i`.`clase_id` = `c`.`id`)) left join `usuarios` `p` on(`c`.`profesor_id` = `p`.`id`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_usuarios_completo`
--
DROP TABLE IF EXISTS `v_usuarios_completo`;

DROP VIEW IF EXISTS `v_usuarios_completo`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_usuarios_completo`  AS SELECT `u`.`id` AS `id`, `u`.`nombre` AS `nombre`, `u`.`email` AS `email`, `r`.`nombre` AS `rol`, `u`.`activo` AS `activo`, `u`.`fecha_registro` AS `fecha_registro`, `u`.`ultimo_acceso` AS `ultimo_acceso` FROM (`usuarios` `u` join `roles` `r` on(`u`.`rol_id` = `r`.`id`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `avisos`
--
ALTER TABLE `avisos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_avisos_fecha` (`fecha`),
  ADD KEY `idx_avisos_urgente` (`urgente`),
  ADD KEY `idx_avisos_clase_fecha` (`clase_id`,`fecha`),
  ADD KEY `idx_avisos_profesor_urgente` (`profesor_id`,`urgente`);

--
-- Indices de la tabla `avisos_comentarios`
--
ALTER TABLE `avisos_comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_avisos_comentarios_padre` (`padre_id`),
  ADD KEY `idx_avisos_comentarios_fecha` (`fecha`),
  ADD KEY `idx_avisos_comentarios_aviso` (`aviso_id`);

--
-- Indices de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD PRIMARY KEY (`alumno_id`,`clase_id`),
  ADD KEY `fk_calificaciones_clase` (`clase_id`),
  ADD KEY `idx_calificaciones_fecha` (`fecha_calificacion`);

--
-- Indices de la tabla `clases`
--
ALTER TABLE `clases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_clases_profesor` (`profesor_id`),
  ADD KEY `idx_clases_coordinador` (`coordinador_id`),
  ADD KEY `idx_clases_periodo` (`periodo`),
  ADD KEY `idx_clases_cerrada` (`cerrada`),
  ADD KEY `idx_clases_profesor_periodo` (`profesor_id`,`periodo`);

--
-- Indices de la tabla `hijos`
--
ALTER TABLE `hijos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hijos_padre_alumno` (`padre_id`,`alumno_id`),
  ADD KEY `fk_hijos_alumno` (`alumno_id`),
  ADD KEY `idx_hijos_parentesco` (`parentesco`);

--
-- Indices de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_inscripciones_alumno_clase` (`alumno_id`,`clase_id`),
  ADD KEY `idx_inscripciones_aprobada` (`aprobada`),
  ADD KEY `idx_inscripciones_fecha` (`fecha_inscripcion`),
  ADD KEY `idx_inscripciones_clase_aprobada` (`clase_id`,`aprobada`);

--
-- Indices de la tabla `mensajes_padre_profesor`
--
ALTER TABLE `mensajes_padre_profesor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_mensajes_profesor` (`profesor_id`),
  ADD KEY `idx_mensajes_fecha` (`fecha`),
  ADD KEY `idx_mensajes_leido` (`leido`),
  ADD KEY `idx_mensajes_conversacion` (`padre_id`,`profesor_id`,`fecha`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reportes_clase` (`clase_id`),
  ADD KEY `idx_reportes_fecha` (`fecha`),
  ADD KEY `idx_reportes_tipo` (`tipo_reporte`),
  ADD KEY `idx_reportes_alumno_fecha` (`alumno_id`,`fecha`),
  ADD KEY `idx_reportes_profesor_fecha` (`profesor_id`,`fecha`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `solicitudes_accion`
--
ALTER TABLE `solicitudes_accion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_solicitudes_usuario` (`usuario_id`),
  ADD KEY `idx_solicitudes_estado` (`estado`),
  ADD KEY `idx_solicitudes_tipo` (`tipo`),
  ADD KEY `idx_solicitudes_fecha` (`fecha_solicitud`),
  ADD KEY `idx_solicitudes_solicitante_estado` (`solicitante_id`,`estado`);

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tareas_fecha_entrega` (`fecha_entrega`),
  ADD KEY `idx_tareas_clase_activa` (`clase_id`,`activa`),
  ADD KEY `idx_tareas_clase_fecha` (`clase_id`,`fecha_entrega`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_usuarios_email` (`email`),
  ADD KEY `idx_usuarios_rol` (`rol_id`),
  ADD KEY `idx_usuarios_activo` (`activo`),
  ADD KEY `idx_usuarios_rol_activo` (`rol_id`,`activo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `avisos`
--
ALTER TABLE `avisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `avisos_comentarios`
--
ALTER TABLE `avisos_comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `clases`
--
ALTER TABLE `clases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `hijos`
--
ALTER TABLE `hijos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `mensajes_padre_profesor`
--
ALTER TABLE `mensajes_padre_profesor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `solicitudes_accion`
--
ALTER TABLE `solicitudes_accion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `avisos`
--
ALTER TABLE `avisos`
  ADD CONSTRAINT `fk_avisos_clase` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_avisos_profesor` FOREIGN KEY (`profesor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `avisos_comentarios`
--
ALTER TABLE `avisos_comentarios`
  ADD CONSTRAINT `fk_avisos_comentarios_aviso` FOREIGN KEY (`aviso_id`) REFERENCES `avisos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_avisos_comentarios_padre` FOREIGN KEY (`padre_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD CONSTRAINT `fk_calificaciones_alumno` FOREIGN KEY (`alumno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_calificaciones_clase` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `clases`
--
ALTER TABLE `clases`
  ADD CONSTRAINT `fk_clases_coordinador` FOREIGN KEY (`coordinador_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_clases_profesor` FOREIGN KEY (`profesor_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `hijos`
--
ALTER TABLE `hijos`
  ADD CONSTRAINT `fk_hijos_alumno` FOREIGN KEY (`alumno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hijos_padre` FOREIGN KEY (`padre_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD CONSTRAINT `fk_inscripciones_alumno` FOREIGN KEY (`alumno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inscripciones_clase` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `mensajes_padre_profesor`
--
ALTER TABLE `mensajes_padre_profesor`
  ADD CONSTRAINT `fk_mensajes_padre` FOREIGN KEY (`padre_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mensajes_profesor` FOREIGN KEY (`profesor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `fk_reportes_alumno` FOREIGN KEY (`alumno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reportes_clase` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reportes_profesor` FOREIGN KEY (`profesor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `solicitudes_accion`
--
ALTER TABLE `solicitudes_accion`
  ADD CONSTRAINT `fk_solicitudes_solicitante` FOREIGN KEY (`solicitante_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_solicitudes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD CONSTRAINT `fk_tareas_clase` FOREIGN KEY (`clase_id`) REFERENCES `clases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
