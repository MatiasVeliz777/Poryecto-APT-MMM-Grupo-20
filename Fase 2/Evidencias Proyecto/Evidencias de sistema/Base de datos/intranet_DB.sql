-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-11-2024 a las 21:57:31
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
-- Base de datos: `intranet`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos`
--

CREATE TABLE `archivos` (
  `id` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `tipo_archivo` varchar(50) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `archivos`
--

INSERT INTO `archivos` (`id`, `nombre_archivo`, `tipo_archivo`, `ruta_archivo`, `fecha_subida`) VALUES
(1, '000000366578960.pdf', 'application/pdf', 'uploads/000000366578960.pdf', '2024-09-09 20:02:49'),
(2, 'eticket_general_bse003_01112024_2100_37340694_2.pdf', 'application/pdf', 'uploads/eticket_general_bse003_01112024_2100_37340694_2.pdf', '2024-09-09 20:26:35'),
(3, 'eticket_general_bse003_01112024_2100_37340694_2.pdf', 'application/pdf', 'uploads/eticket_general_bse003_01112024_2100_37340694_2.pdf', '2024-09-09 20:29:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias_eventos`
--

CREATE TABLE `asistencias_eventos` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) DEFAULT NULL,
  `rut_usuario` varchar(13) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistencias_eventos`
--

INSERT INTO `asistencias_eventos` (`id`, `evento_id`, `rut_usuario`, `fecha_registro`) VALUES
(2, 16, '013.772.680-7', '2024-10-21 14:08:41'),
(3, 16, '015.624.248-9', '2024-10-21 14:16:20'),
(4, 34, '013.612.924-4', '2024-10-21 16:38:13'),
(5, 16, '016.708.637-3', '2024-10-23 15:31:26'),
(6, 16, '016.933.555-9', '2024-10-23 15:31:42'),
(12, 34, '016.291.388-3', '2024-10-24 15:01:33'),
(14, 16, '016.291.388-3', '2024-10-28 16:06:29'),
(20, NULL, '013.612.924-4', '2024-11-12 17:02:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_capacitaciones`
--

CREATE TABLE `asistencia_capacitaciones` (
  `id` int(11) NOT NULL,
  `capacitacion_id` int(11) NOT NULL,
  `rut_usuario` varchar(13) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistencia_capacitaciones`
--

INSERT INTO `asistencia_capacitaciones` (`id`, `capacitacion_id`, `rut_usuario`, `fecha_registro`) VALUES
(11, 1, '013.612.924-4', '2024-11-14 16:02:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `capacitaciones`
--

CREATE TABLE `capacitaciones` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `ubicacion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `capacitaciones`
--

INSERT INTO `capacitaciones` (`id`, `titulo`, `fecha`, `hora`, `ubicacion`) VALUES
(1, 'TOKEN DE 473', '2024-11-20', '17:30:00', 'Sala de conferencias 2'),
(2, 'Curso de Excell', '2024-11-21', '21:00:00', 'Remoto');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargos`
--

CREATE TABLE `cargos` (
  `id` int(11) NOT NULL,
  `NOMBRE_CARGO` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cargos`
--

INSERT INTO `cargos` (`id`, `NOMBRE_CARGO`) VALUES
(1, 'Administrativo'),
(2, 'Adminsion Pabellon'),
(3, 'Analista Area Comercial'),
(4, 'Analista Contable'),
(5, 'Asistente Contable'),
(6, 'Asistente de Calidad'),
(7, 'Asistente de Imagen'),
(8, 'Asistente de Reas'),
(9, 'Asistente Ginecologia'),
(10, 'Asistente Oftlamologia'),
(11, 'Auditoria Ficha Clinica'),
(12, 'Auxiliar de Aseo'),
(13, 'Auxiliar de Pabellon'),
(14, 'Contador Interno'),
(15, 'Convenios Medicos y Seremi'),
(16, 'Coordinador Edificio 442'),
(17, 'Coordinador Edificio 473'),
(18, 'Coordinadora de Personal'),
(19, 'Digitador'),
(20, 'Director Medico'),
(21, 'Directora'),
(22, 'Ejecutiva Comercial'),
(23, 'Enc. Adquisisciones y Bodegas'),
(24, 'Encargada de Aseo'),
(25, 'Encargada de Recepciones'),
(26, 'Encargada RR.HH'),
(27, 'Encargado de Estudios y Proyec'),
(28, 'Encargado de Informática'),
(29, 'Enfermera Proc. Endocopicos'),
(30, 'Enfermera TM'),
(31, 'Enfermera Unidad de Calidad'),
(32, 'Enfermera Urgencia'),
(33, 'Enfermero Pabellon'),
(34, 'Enfermero Proced. Clínicos'),
(35, 'Enfermero Urgencias'),
(36, 'Estafeta'),
(37, 'Gerente de Adm. y RR.HH'),
(38, 'Gerente General'),
(39, 'Mantención'),
(40, 'Prevencionista de Riesgos'),
(41, 'Recepcionista'),
(42, 'Tecnico Informatica'),
(43, 'Tecnólogo Medico de Imagen'),
(44, 'Tecnólogo Medico de Laboratori'),
(45, 'Tenm TM'),
(46, 'Tens  Laboratorio'),
(47, 'Tens Dental'),
(48, 'Tens Endoscopia'),
(49, 'Tens Esterilizacion'),
(50, 'Tens Imagen'),
(51, 'Tens Pabellon'),
(52, 'Tens Procedimiento'),
(53, 'Tens TM'),
(54, 'Tens Urgencia'),
(55, 'Tesoreria');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado_mes`
--

CREATE TABLE `empleado_mes` (
  `id` int(11) NOT NULL,
  `rut` varchar(13) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `mes_year` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleado_mes`
--

INSERT INTO `empleado_mes` (`id`, `rut`, `descripcion`, `mes_year`) VALUES
(3, '015.624.248-9', 'Felicidades a Estrella Aguilar por ser seleccionado como el empleado del mes. Gracias a su dedicación, compromiso y esfuerzo constante, ha demostrado un alto nivel de profesionalismo en su rol como Enfermera. Su actitud proactiva y capacidad para colaborar con el equipo ha sido clave en el éxito de nuestros proyectos recientes. Además, su enfoque en la mejora continua y su disposición para asumir nuevos desafíos son un ejemplo para todos. ¡Gracias por tu trabajo excepcional y por inspirar a todos en la empresa!', '2024-09-29'),
(10, '019.411.173-8', 'Felicidades a Estrella Aguilar por ser seleccionado como el empleado del mes. Gracias a su dedicación, compromiso y esfuerzo constante, ha demostrado un alto nivel de profesionalismo en su rol como Enfermera. Su actitud proactiva y capacidad para colaborar con el equipo ha sido clave en el éxito de nuestros proyectos recientes. Además, su enfoque en la mejora continua y su disposición para asumir nuevos desafíos son un ejemplo para todos. ¡Gracias por tu trabajo excepcional y por inspirar a todos en la empresa!\r\ns desafíos son un ejemplo para todos. ¡Gracias por tu trabajo excepcional y por inspirar a todos en la empresa!Felicidades a Estrella Aguilar por ser seleccionado como el empleado del mes. Gracias a su dedicación, compromiso y esfuerzo constante, ha demostrado un alto nivel de profesionalismo en su rol como Enfermera. Su actitud proactiva y capacidad para colaborar con el equipo ha sido clave en el éxito de nuestros proyectos recientes. Además, su enfoque en la mejora continua y su disposición para asumir nuevos desafíos son un ejemplo para todos. ¡Gracias por tu trabajo excepcional y por inspirar a todos en la empresa!\r\ns desafíos son un ejemplo para todos. ¡Gracias por tu trabajo excepcional y por inspirar a todos en la empresa!', '2024-10-29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `ubicacion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id`, `titulo`, `fecha`, `hora`, `ubicacion`) VALUES
(16, 'hola', '2024-10-17', '16:43:00', 'Sala de conferencias 2'),
(19, '1231231', '2024-09-11', '17:16:00', 'Sala de conferencias 2'),
(20, 'holaaa', '2024-09-19', '14:37:00', 'Sala de conferencias 2'),
(34, 'pokemon', '2024-10-11', '15:59:00', 'Sala de conferencias 2'),
(38, '122222222222222222222222222222222222222', '2024-10-25', '17:13:00', 'Sala de conferencias 2'),
(40, 'Capacitacion de Excell', '2024-11-06', '15:01:00', '473, administracion'),
(75, '1231231', '2024-11-05', '18:00:00', 'HARGOLEIN'),
(76, 'TOKEN DE 473', '2024-11-19', '03:20:00', 'asdasd');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foro_preguntas`
--

CREATE TABLE `foro_preguntas` (
  `id_pregunta` int(11) NOT NULL,
  `rut_usuario` varchar(15) DEFAULT NULL,
  `pregunta` text NOT NULL,
  `foto_pregunta` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `foro_preguntas`
--

INSERT INTO `foro_preguntas` (`id_pregunta`, `rut_usuario`, `pregunta`, `foto_pregunta`, `fecha_creacion`) VALUES
(6, '016.291.388-3', 'asdadad', NULL, '2024-11-15 18:25:51'),
(12, '013.612.924-4', 'cuando pagan chiques estoy con deudas haat la yugular tu', NULL, '2024-11-19 15:31:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foro_respuestas`
--

CREATE TABLE `foro_respuestas` (
  `id_respuesta` int(11) NOT NULL,
  `id_pregunta` int(11) DEFAULT NULL,
  `rut_usuario` varchar(15) DEFAULT NULL,
  `respuesta` text NOT NULL,
  `fecha_respuesta` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `foro_respuestas`
--

INSERT INTO `foro_respuestas` (`id_respuesta`, `id_pregunta`, `rut_usuario`, `respuesta`, `fecha_respuesta`) VALUES
(1, 6, '016.291.388-3', 'emm whatasigma\r\n', '2024-11-15 18:32:20'),
(3, 6, '013.612.924-4', 'eres tonto? hola jeje', '2024-11-15 19:46:25'),
(6, 6, '013.612.924-4', 'que', '2024-11-19 21:03:44'),
(7, 12, '013.612.924-4', 'nose yo igual tengo hambre :v', '2024-11-19 21:06:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_capacitaciones`
--

CREATE TABLE `imagenes_capacitaciones` (
  `id` int(11) NOT NULL,
  `capacitacion_id` int(11) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `imagenes_capacitaciones`
--

INSERT INTO `imagenes_capacitaciones` (`id`, `capacitacion_id`, `ruta_imagen`) VALUES
(3, 1, 'Images/imagenes_eventos/img_672bc9ddc61818.47607742.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_eventos`
--

CREATE TABLE `imagenes_eventos` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `imagenes_eventos`
--

INSERT INTO `imagenes_eventos` (`id`, `evento_id`, `ruta_imagen`) VALUES
(7, 16, 'Images/imagenes_eventos/img_6718f18c124362.57685025.png'),
(8, 16, 'Images/imagenes_eventos/img_6718f18c1ac2a1.01258895.jpg'),
(13, 16, 'Images/imagenes_eventos/img_67190476410076.30538698.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagenes_respuesta`
--

CREATE TABLE `imagenes_respuesta` (
  `id_imagen` int(11) NOT NULL,
  `id_respuesta` int(11) DEFAULT NULL,
  `ruta_imagen` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `imagenes_respuesta`
--

INSERT INTO `imagenes_respuesta` (`id_imagen`, `id_respuesta`, `ruta_imagen`) VALUES
(2, 3, 'uploads/sans.jpg'),
(3, 7, 'uploads/png-transparent-emoji-sadness-emoticon-smiley-sad-emoji-crying-imoji-face-sticker-desktop-wallpaper-thumbnail.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opciones_encuesta`
--

CREATE TABLE `opciones_encuesta` (
  `id_opcion` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `opcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `opciones_encuesta`
--

INSERT INTO `opciones_encuesta` (`id_opcion`, `id_pregunta`, `opcion`) VALUES
(5, 27, '1234'),
(6, 27, '5678'),
(7, 28, 'si'),
(8, 28, 'no'),
(9, 28, 'que'),
(10, 30, 'bueno'),
(11, 30, 'malo'),
(12, 34, 'bueno'),
(13, 34, 'malo'),
(14, 36, 'bueno'),
(15, 36, 'malo'),
(16, 36, 'maomeno');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal`
--

CREATE TABLE `personal` (
  `rut` varchar(13) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `cargo_id` int(11) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`rut`, `nombre`, `correo`, `imagen`, `fecha_nacimiento`, `cargo_id`, `rol_id`, `activo`) VALUES
('004.108.784-6', 'GASTI ECCLEFIELD PATRICIA', 'patriciagasti@gmail.com', 'GASTI ECCLEFIELD PATRICIA.jpg', '1946-09-08', 21, 12, 1),
('005.217.048-6', 'DONOSO GONZALEZ JOSE MANUEL', 'jdonoso@saludsanagustin.cl', 'DONOSO GONZALEZ JOSE MANUEL.jpg', '1946-05-11', 20, 12, 1),
('006.760.123-8', 'VASQUEZ ESCARATE JORGE ENRIQUE', 'jeve1955@gmail.com', 'VASQUEZ ESCARATE JORGE ENRIQUE.jpg', '1955-12-19', 36, 9, 1),
('007.010.373-7', 'SILVA FUENTES SOLEDAD ALEJANDRA', 'silvasoledad873@gmail.com', 'SILVA FUENTES SOLEDAD ALEJANDRA.jpg', '1970-01-11', 41, 16, 1),
('008.055.787-6', 'GONZALEZ WINKLER LILY MARLEN', 'gwinkler51@gmail.com', 'GONZALEZ WINKLER LILY MARLEN.jpg', '1961-04-11', 10, 8, 1),
('008.091.193-9', 'POBLETE JEREZ CECILIA DE LAS MERCE', 'chechipoblete@gmail.com', 'POBLETE JEREZ CECILIA DE LAS MERCE.jpg', '1958-04-07', 41, 16, 1),
('008.265.448-8', 'JERIA MARAMBIO PATRICIO ANTONIO', 'antoniojeriamarambio1@gmail.com', 'JERIA MARAMBIO PATRICIO ANTONIO.jpg', '1958-07-27', 12, 9, 1),
('008.369.757-1', 'GOMEZ GINART JOSE MIGUEL', 'jgomezginart@gmail.com', 'GOMEZ GINART JOSE MIGUEL.jpg', '1972-06-05', 41, 16, 1),
('008.729.610-5', 'ROMANINI ALVAREZ ROSANA', 'rossanaromanini@gmail.com', 'ROMANINI ALVAREZ ROSANA.jpg', '1959-05-31', 41, 16, 1),
('009.080.082-5', 'HERNANDEZ JORQUERA CECILIA DEL CARMEN', 'cecihernandezjorquera@gmail.com', 'HERNANDEZ JORQUERA CECILIA DEL CARMEN.jpg', '1959-12-12', 12, 9, 1),
('009.636.190-4', 'CARVAJAL CARVAJAL AMERICA', 'merycarva59@gmail.com', 'CARVAJAL CARVAJAL AMERICA.jpg', '1959-10-09', 12, 9, 1),
('009.835.804-8', 'MUÑOZ HERNANDEZ GABRIELA PATRICIA', 'gabriela.-mh@hotmail.com', 'MUÑOZ HERNANDEZ GABRIELA PATRICIA.jpg', '1964-05-29', 41, 16, 1),
('009.966.634-K', 'GUZMAN BRITO BLANCA ROSA', 'blanca.guzmanbrito2123@gmail.com', 'GUZMAN BRITO BLANCA ROSA.jpg', '1963-10-08', 12, 9, 1),
('010.210.899-K', 'REYES GUAJARDO HAYDEE DEL CARMEN', 'hayregu@hotmail.com', 'REYES GUAJARDO HAYDEE DEL CARMEN.jpg', '1965-04-10', 52, 10, 1),
('010.519.341-6', 'CARRASCO MATURANA YOLANDA LUISA', 'yolandaluisa7@gmail.com', 'CARRASCO MATURANA YOLANDA LUISA.jpg', '1965-08-09', 12, 9, 1),
('010.689.130-3', 'DONOSO GASTI BEATRIZ', 'bdonoso@saludsanagustin.cl', 'DONOSO GASTI BEATRIZ.jpg', '1978-09-01', 23, 14, 1),
('010.816.905-2', 'VARGAS GONZALEZ MARCELA DEL ROSARIO', 'marcela9.vargas.g@gmail.com', 'VARGAS GONZALEZ MARCELA DEL ROSARIO.jpg', '1965-03-09', 12, 9, 1),
('010.835.657-K', 'DONOSO BUSTAMANTE NELSON GONZALO', 'donosonelson10@gmail.com', 'DONOSO BUSTAMANTE NELSON GONZALO.jpg', '1966-11-21', 16, 13, 1),
('010.943.542-2', 'HERNANDEZ MALDONADO PILAR DEL CARMEN', 'phernandezmaldonado1@gmail.com', 'HERNANDEZ MALDONADO PILAR DEL CARMEN.jpg', '1969-02-05', 6, 8, 1),
('010.984.579-5', 'GOMEZ FABIO MARIA LUISA', 'gomezfabiom@gmail.com', 'GOMEZ FABIO MARIA LUISA.jpg', '1968-07-21', 12, 9, 1),
('011.170.481-3', 'OLIVOS BERRIOS ANA ROSA', 'aniolivosberrios@hotmail.com', 'OLIVOS BERRIOS ANA ROSA.jpg', '1967-11-15', 47, 10, 1),
('011.230.831-8', 'ROJAS ALVAREZ CLAUDIO AMADEO', 'claudio.rojasalv@gmail.com', 'ROJAS ALVAREZ CLAUDIO AMADEO.jpg', '1968-01-13', 39, 17, 1),
('011.396.369-7', 'CERDA HERMOSILLA GEMITA RAMONA', 'gemygalilea@gmail.com', 'CERDA HERMOSILLA GEMITA RAMONA.jpg', '1969-01-23', 41, 16, 1),
('011.397.025-1', 'ROSALES ARTIGAS LILIANA ANDREA', 'rosales_artigas@hotmail.com', 'ROSALES ARTIGAS LILIANA ANDREA.jpg', '1969-09-23', 7, 8, 1),
('011.608.005-2', 'NARANJO ESCARATE ELSA DEL CARMEN', 'elsa.naranjo.1970@gmail.com', 'NARANJO ESCARATE ELSA DEL CARMEN.jpg', '1970-01-15', 12, 9, 1),
('011.608.394-9', 'VALDOVINOS MATELUNA VICTORIA CAROLINA', 'vvaldovinosmateluna@gmail.com', 'VALDOVINOS MATELUNA VICTORIA CAROLINA.jpg', '1970-07-11', 12, 9, 1),
('011.697.150-K', 'ARTIGAS CALDERON MARIA DEL CARMEN', 'mariaartigas004@gmail.com', 'ARTIGAS CALDERON MARIA DEL CARMEN.jpg', '1971-04-13', 12, 9, 1),
('011.697.156-9', 'AGUILERA RUBIO MIRNA ESTER', 'mirnaaguilera2020@yahoo.com', 'AGUILERA RUBIO MIRNA ESTER.jpg', '1971-06-08', 41, 16, 1),
('011.697.805-9', 'CABRERA BECERRA ROXANA DEL CARMEN', 'montajesmasferrer@gmail.com', 'CABRERA BECERRA ROXANA DEL CARMEN.jpg', '1971-09-07', 37, 2, 1),
('011.948.720-K', 'PARRAGUEZ CARTAGENA JESSICA DEL CARMEN', 'jessicaparraguez350@gmail.com', 'PARRAGUEZ CARTAGENA JESSICA DEL CARMEN.jpg', '1972-04-18', 12, 9, 1),
('011.980.181-8', 'UGARTE VEGA MARIBET FABIOLA', 'fabiolaugartevega@gmail.com', 'UGARTE VEGA MARIBET FABIOLA.jpg', '1972-09-19', 5, 8, 1),
('012.178.058-5', 'LEIVA ATENAS CECILIA', 'clatenas71@gmail.com', 'LEIVA ATENAS CECILIA.jpg', '1971-12-13', 41, 16, 1),
('012.178.390-8', 'PIÑA CASTRO ALEJANDRA SALOME', 'alejandrasalome.p@gmail.com', 'PIÑA CASTRO ALEJANDRA SALOME.jpg', '1972-03-24', 12, 9, 1),
('012.178.902-7', 'CALDERON POBLETE MARIA TERESA', 'mtcalder@gmail.com', 'CALDERON POBLETE MARIA TERESA.jpg', '1972-07-10', 47, 10, 1),
('012.584.671-8', 'REY ESCORZA CARLOS MANUEL', 'carlos.rey.escorza@gmail.com', 'REY ESCORZA CARLOS MANUEL.jpg', '1974-06-22', 14, 15, 1),
('012.799.368-8', 'HUERTA GOMEZ PABLO MANUEL', 'hgpablomontse@hotmail.com', 'HUERTA GOMEZ PABLO MANUEL.jpg', '1975-05-05', 50, 10, 1),
('013.057.919-1', 'TORRES SOTO CLAUDIA ROSA', 'claudia.torres136@gmail.com', 'TORRES SOTO CLAUDIA ROSA.jpg', '1974-08-25', 12, 9, 1),
('013.338.299-2', 'ESPINOZA FERRADA ISABEL PAMELA', 'anson.banqueteria.isa@mail.com', 'ESPINOZA FERRADA ISABEL PAMELA.jpg', '1978-04-17', 12, 9, 1),
('013.338.575-4', 'FUENTES FUENTES MARITZA ALEJANDRA', 'mary.ff06@gmail.com', 'FUENTES FUENTES MARITZA ALEJANDRA.jpg', '1978-07-06', 41, 16, 1),
('013.340.833-9', 'GODOY BECERRA CLAUDIA ANDREA', 'claudi.gody78@gmail.com', 'GODOY BECERRA CLAUDIA ANDREA.jpg', '1978-08-07', 2, 17, 1),
('013.340.890-8', 'GONZALEZ HERNANDEZ CLAUDIA ANDREA', 'clauhernandez2608@gmail.com', 'GONZALEZ HERNANDEZ CLAUDIA ANDREA.jpg', '1978-10-09', 9, 8, 1),
('013.405.524-3', 'EMIG KLEIN LILIAN MARCELA', 'lilianemigk@hotmail.com', 'EMIG KLEIN LILIAN MARCELA.jpg', '1978-05-02', 3, 15, 1),
('013.560.287-6', 'GODOY BECERRA ANGEL ALBERTO', 'agodoy79@yahoo.com', 'GODOY BECERRA ANGEL ALBERTO.jpg', '1979-11-07', 27, 14, 1),
('013.612.924-4', 'LOPEZ CARRASCO VERONICA ANDREA', 'verolopezcarrasco@gmail.com', 'LOPEZ CARRASCO VERONICA ANDREA.jpg', '1979-10-28', 26, 5, 1),
('013.663.177-2', 'CANALES VILLA JOHN', 'johncanalesv@gmail.com', 'CANALES VILLA JOHN.jpg', '1976-04-23', 43, 10, 1),
('013.772.318-2', 'RIVERA URETA NANCY PATRICIA', 'priveraureta840@gmail.com', 'RIVERA URETA NANCY PATRICIA.jpg', '1980-04-08', 12, 9, 1),
('013.772.680-7', 'CAÑAS SILVA MARGARITA DEL CARMEN', 'margaknhas@gmail.com', 'CAÑAS SILVA MARGARITA DEL CARMEN.jpg', '1979-12-28', 9, 8, 1),
('013.773.309-9', 'JORQUERA JORQUERA MARIA JOSE', 'mariajosej.jorquera@gmail.com', 'JORQUERA JORQUERA MARIA JOSE.jpg', '1980-12-09', 51, 10, 1),
('014.007.065-3', 'LOPEZ DEVIA VIVIANA VICTORIA', 'vivianalopeztw@gmail.com', 'LOPEZ DEVIA VIVIANA VICTORIA.jpg', '1981-02-16', 55, 15, 1),
('014.246.103-K', 'REYES CERDA CECILIA ALEJANDRA', 'cecyreyescerda@gmail.com', 'REYES CERDA CECILIA ALEJANDRA.jpg', '1974-06-06', 15, 1, 1),
('014.312.126-7', 'SILVA AREVALO ELISA ENCARNACION', 'elysilvaa1@hotmail.com', 'SILVA AREVALO ELISA ENCARNACION.jpg', '1976-04-13', 11, 15, 1),
('014.312.453-3', 'FIGUEROA ZUÑIGA PAMELA DEL PILAR', 'pameladelpilarfigueroa@gmail.com', 'FIGUEROA ZUÑIGA PAMELA DEL PILAR.jpg', '1976-05-31', 47, 10, 1),
('014.379.751-1', 'MORALES QUINTANILLA ALICIA CAROLINA', 'aliciam469@gmail.com', 'MORALES QUINTANILLA ALICIA CAROLINA.jpg', '1977-03-27', 41, 16, 1),
('014.380.201-9', 'VARGAS LOPEZ FERNANDA', 'fervargas.lopez22@gmail.com', 'VARGAS LOPEZ FERNANDA.jpg', '1977-07-12', 25, 14, 1),
('014.380.263-9', 'AGUILAR CARRASCO BARBARA ANDREA', 'barbaraaguilar1977@gmail.com', 'AGUILAR CARRASCO BARBARA ANDREA.jpg', '1977-06-11', 12, 9, 1),
('014.416.077-0', 'ORELLANA ZURITA MARIA LILIANA', 'morellana_zurita@hotmail.com', 'ORELLANA ZURITA MARIA LILIANA.jpg', '1969-08-23', 24, 14, 1),
('014.442.723-8', 'BIZAMA VILUGRON MARCELA', 'marcebivi23@gmail.com', 'BIZAMA VILUGRON MARCELA.jpg', '1973-11-23', 17, 13, 1),
('015.404.493-0', 'QUIROZ VARGAS TAMARA NATALY', 'tamaraquiroz.v@gmail.com', 'QUIROZ VARGAS TAMARA NATALY.jpg', '1985-08-27', 41, 16, 1),
('015.622.305-0', 'HENRIQUEZ PARRA MARIA JESUS', 'maruhenriquezparra@gmail.com', 'HENRIQUEZ PARRA MARIA JESUS.jpg', '1984-06-30', 4, 15, 1),
('015.622.596-7', 'VILLA BARRIA CAROLINA DEL CARMEN', 'carolinavilla682@gmail.com', 'VILLA BARRIA CAROLINA DEL CARMEN.jpg', '1981-07-14', 12, 9, 1),
('015.624.248-9', 'AGUILAR REYES ESTRELLA MARIBEL', 'estrellaaguilarreyes27@gmail.com', 'AGUILAR REYES ESTRELLA MARIBEL.jpg', '1984-03-27', 48, 10, 1),
('015.681.591-8', 'ZUÑIGA SANCHEZ CYNTHIA ALEJANDRA', 'cynthia.piscis83@hotmail.com', 'ZUÑIGA SANCHEZ CYNTHIA ALEJANDRA.jpg', '1983-02-21', 41, 16, 1),
('015.780.241-0', 'CONTRERAS FLORES VICTOR HUGO', 'vcontreras112@gmail.com', 'CONTRERAS FLORES VICTOR HUGO.jpg', '1984-01-30', 38, 2, 1),
('015.796.663-4', 'MORALES ROSALES CATALINA DE LAS MERC', 'cm.moralesr@gmail.com', 'MORALES ROSALES CATALINA DE LAS MERC.jpg', '1984-02-10', 41, 16, 1),
('016.114.663-3', 'SILVA MARIQUEO CYNTHIA NATALI', 'silvacynthia1985@gmail.com', 'SILVA MARIQUEO CYNTHIA NATALI.jpg', '1985-03-28', 12, 9, 1),
('016.290.784-0', 'VALDOVINOS MATELUNA ALEJANDRA ISABEL', 'alejandravm66@gmail.com', 'VALDOVINOS MATELUNA ALEJANDRA ISABEL.jpg', '1985-09-21', 41, 16, 1),
('016.291.388-3', 'PLAZA CRUCES RODRIGO ALEJANDRO\r\n', 'rpc842@gmail.com\r\n', 'PLAZA CRUCES RODRIGO ALEJANDRO.jpg', '1986-01-19', 28, 4, 1),
('016.341.258-6', 'ALVAREZ LEYTON ANGELINA MAGDALENA', 'angialvarezl@gmail.com', 'ALVAREZ LEYTON ANGELINA MAGDALENA.jpg', '1986-08-29', 41, 16, 1),
('016.367.501-3', 'TORREJON CABEZAS SALVADOR AQUILES', 'salvasupa@gmail.com', 'TORREJON CABEZAS SALVADOR AQUILES.jpg', '1987-05-01', 44, 10, 1),
('016.576.413-7', 'PIZARRO PIZARRO DANIELA FERNANDA', 'd.pizarro738@gmail.com', 'PIZARRO PIZARRO DANIELA FERNANDA.jpg', '1986-12-26', 53, 10, 1),
('016.576.960-0', 'LEIVA FUENTES PAMELA', 'pamelaleivafuentes198927@gmail.com', 'LEIVA FUENTES PAMELA.jpg', '1989-02-21', 53, 10, 1),
('016.577.130-3', 'BRAVO MALLEA GISELLE ARACELI', 'gisellebravomallea@gmail.com', 'BRAVO MALLEA GISELLE ARACELI.jpg', '1989-03-16', 18, 5, 1),
('016.577.383-7', 'ARRAÑO REYES DOMINIQUE ALEJANDRA', 'domi.joase.17@gmail.com', 'ARRAÑO REYES DOMINIQUE ALEJANDRA.jpg', '1989-05-23', 48, 10, 1),
('016.577.451-5', 'SANTIS CAMPAÑA ANITA MARIA', 'anysc_1277@hotmail.com', 'SANTIS CAMPAÑA ANITA MARIA.jpg', '1989-06-07', 53, 10, 1),
('016.670.205-4', 'MARTINEZ ALVAREZ MARIA FERNANDA', 'fernandamartinezalvarez27@gmail.com', 'MARTINEZ ALVAREZ MARIA FERNANDA.jpg', '1987-11-11', 54, 10, 1),
('016.708.622-5', 'SALINAS CUETO BARBARA DANIELA', 'barbarissc@gmail.com', 'SALINAS CUETO BARBARA DANIELA.jpg', '1987-05-04', 47, 10, 1),
('016.708.637-3', 'CASTRO MEZA NICOLAS ANTONIO', 'castrom.nicolas@gmail.com', 'CASTRO MEZA NICOLAS ANTONIO.jpg', '1987-05-05', 39, 17, 1),
('016.758.860-3', 'RAMIREZ CASTRO SANTIAGO', 'naxinramcas@gmail.com', 'RAMIREZ CASTRO SANTIAGO.jpg', '1988-03-04', 51, 10, 1),
('016.786.907-6', 'VALDIVIA NARANJO SANDRA VALESKA', 'sandravaleskagenoveva@gmail.com', 'VALDIVIA NARANJO SANDRA VALESKA.jpg', '1987-10-10', 41, 16, 1),
('016.856.366-3', 'URRUTIA MARTINEZ IVONNE YORDANNA', 'shimbo_17@yahoo.es', 'URRUTIA MARTINEZ IVONNE YORDANNA.jpg', '1988-07-03', 41, 16, 1),
('016.933.555-9', 'CATRILEO HUENCHUMARIAN JOCELINE MARGARITA', 'margarita.catrileo.h@gmail.com', 'CATRILEO HUENCHUMARIAN JOCELINE MARGARITA.jpg', '1986-03-22', 49, 10, 1),
('017.081.914-4', 'LAPLAGNE MORALES YEXEL VIRGINIA', 'ylaplagnem@gmail.com', 'LAPLAGNE MORALES YEXEL VIRGINIA.jpg', '1988-11-24', 52, 10, 1),
('017.082.075-4', 'VERA MORA DANIEL ANTONIO', 'daniel.vera.1988@gmail.com', 'VERA MORA DANIEL ANTONIO.jpg', '1988-11-30', 4, 15, 1),
('017.127.627-6', 'MORAGA SANCHEZ FELIPE ALBERTO', 'tm.felipemoraga@gmail.com', 'MORAGA SANCHEZ FELIPE ALBERTO.jpg', '1988-10-29', 43, 10, 1),
('017.398.118-K', 'MIRANDA ROMERO JOSELIN DEL CARMEN', 'joselinmirandar@gmail.com', 'MIRANDA ROMERO JOSELIN DEL CARMEN.jpg', '1989-12-09', 29, 11, 1),
('017.399.273-4', 'FARIAS CATALAN TANIA CAROLINA', 'tania-280@hotmail.com', 'FARIAS CATALAN TANIA CAROLINA.jpg', '1990-06-24', 51, 10, 1),
('017.966.075-K', 'BARRERA CARROZA CAMILA ANDREA', 'camybc13@gmail.com', 'BARRERA CARROZA CAMILA ANDREA.jpg', '1991-10-22', 49, 10, 1),
('017.985.938-6', 'CUETO JERIA PAMELA TAMARA', 'p.cueto.j@gmail.com', 'CUETO JERIA PAMELA TAMARA.jpg', '1991-07-31', 29, 11, 1),
('017.985.939-4', 'ARAOS MANZO ALFONSO EDUARDO', 'poncho_house@hotmail.es', 'ARAOS MANZO ALFONSO EDUARDO.jpg', '1991-08-01', 33, 11, 1),
('017.986.298-0', 'OSORIO CAUTIVO CRISTIAN ANDRES', 'cristianosoriocautivo@gmail.com', 'OSORIO CAUTIVO CRISTIAN ANDRES.jpg', '1991-07-19', 12, 9, 1),
('017.986.809-1', 'VERA GODOY MARIA DEL CARMEN', 'vera.godoy285@gmail.com', 'VERA GODOY MARIA DEL CARMEN.jpg', '1991-12-21', 53, 10, 1),
('018.011.629-K', 'OLIVARES CORTES LESTER GIOVANIE', 'lesterolivaresc@gmail.com', 'OLIVARES CORTES LESTER GIOVANIE.jpg', '1992-04-30', 43, 10, 1),
('018.081.476-0', 'GONZALEZ SILVA PATRICIA FERNANDA', 'patriciagonzalezsilva92@gmail.com', 'GONZALEZ SILVA PATRICIA FERNANDA.jpg', '1992-07-31', 48, 10, 1),
('018.212.413-3', 'ARMIJO CASTAÑEDA PATRICIA BEATRIZ', 'paty.armijo@gmail.com', 'ARMIJO CASTAÑEDA PATRICIA BEATRIZ.jpg', '1992-03-18', 46, 10, 1),
('018.212.546-6', 'CARDENAS SANDOVAL YANET CATALINA', 'yanetcardenass@gmail.com', 'CARDENAS SANDOVAL YANET CATALINA.jpg', '1992-05-03', 34, 11, 1),
('018.213.543-7', 'ALVAREZ AREVALO ANA MARIA', 'alvarezarevaloa@gmail.com', 'ALVAREZ AREVALO ANA MARIA.jpg', '1992-10-04', 51, 10, 1),
('018.324.947-9', 'NUÑEZ MADARIAGA DAYANA CECILIA', 'alonsopenailillo@gmail.com', 'NUÑEZ MADARIAGA DAYANA CECILIA.jpg', '1992-09-17', 41, 16, 1),
('018.424.836-0', 'GARCIA ROCHA DANIELA ANDREA', 'danielagarciarocha@gmail.com', 'GARCIA ROCHA DANIELA ANDREA.jpg', '1993-06-09', 51, 10, 1),
('018.457.022-K', 'MARTINEZ ENCINA PAOLA ESTEFANIA', 'pmartinezencina@gmail.com', 'MARTINEZ ENCINA PAOLA ESTEFANIA.jpg', '1993-06-24', 47, 10, 1),
('018.487.184-K', 'ARAOS HERNANDEZ NICOLAS ANDRES', 'nicoaraoshernandez@gmail.com', 'ARAOS HERNANDEZ NICOLAS ANDRES.jpg', '1993-05-07', 1, 1, 1),
('018.487.251-K', 'VENEGAS CORNEJO NATALIE DANAE', 'natalievenegasc.93@gmail.com', 'VENEGAS CORNEJO NATALIE DANAE.jpg', '1993-05-22', 53, 10, 1),
('018.487.577-2', 'LOYOLA ALVAREZ CAROLINA STEPHANIE', 'carolinaloyola3007@gmail.com', 'LOYOLA ALVAREZ CAROLINA STEPHANIE.jpg', '1993-07-30', 32, 11, 1),
('018.569.401-1', 'ALVEAR MORALES JONATHAN DANIEL', 'jona.alvear.m@gmail.com', 'ALVEAR MORALES JONATHAN DANIEL.jpg', '1993-08-10', 47, 10, 1),
('018.624.771-K', 'FUENTES MANCILLA AMANDA JAZMIN', 'amanda.fuentes.mancilla@gmail.com', 'FUENTES MANCILLA AMANDA JAZMIN.jpg', '1994-04-14', 30, 11, 1),
('018.777.145-5', 'CUEVAS FICA ALEJANDRA LILAY', 'lilay.cuevas@gmail.com', 'CUEVAS FICA ALEJANDRA LILAY.jpg', '1994-02-17', 51, 10, 1),
('019.063.023-4', 'MENA LOPEZ DIEGO ESTEBAN ALEX', 'diegomena_lopez@hotmail.com', 'MENA LOPEZ DIEGO ESTEBAN ALEX.jpg', '1995-05-19', 40, 1, 1),
('019.067.740-0', 'LOPEZ ADARO PEDRO SAMUEL', 'samuellopez2033@gmail.com', 'LOPEZ ADARO PEDRO SAMUEL.jpg', '1995-02-17', 42, 4, 1),
('019.068.395-8', 'ARMIJO CASTAÑEDA CATALINA FRANCISCA', 'cata.armijoc@gmail.com', 'ARMIJO CASTAÑEDA CATALINA FRANCISCA.jpg', '1995-07-29', 41, 16, 1),
('019.069.276-0', 'SANCHEZ GOMEZ JAVIERA ALEJANDRA', 'javieras764@gmail.com', 'SANCHEZ GOMEZ JAVIERA ALEJANDRA.jpg', '1996-01-11', 48, 10, 1),
('019.069.843-2', 'MANZO GUZMAN MARIA JOSE', 'cotemanzo6@gmail.com', 'MANZO GUZMAN MARIA JOSE.jpg', '1996-04-23', 53, 10, 1),
('019.212.116-7', 'LEON CORREA MARIA JOSE', 'coteleoncorrea@gmail.com', 'LEON CORREA MARIA JOSE.jpg', '1995-11-29', 48, 10, 1),
('019.387.737-0', 'CISTERNAS ULLOA GESEBEL ANGELICA', 'gese.cisu@gmil.com', 'CISTERNAS ULLOA GESEBEL ANGELICA.jpg', '1996-09-06', 22, 15, 1),
('019.404.231-0', 'GUAJARDO SANTIS MAGVIA AMPARITO', 'magvia1323@gmail.com', 'GUAJARDO SANTIS MAGVIA AMPARITO.jpg', '1996-09-23', 43, 10, 1),
('019.411.173-8', 'ALVAREZ BARRIA JACQUELINE ANDREA', 'jacquelinealvarez545@gmail.com', 'ALVAREZ BARRIA JACQUELINE ANDREA.jpg', '1996-05-25', 1, 1, 1),
('019.411.660-8', 'CARDOZA NUÑEZ CAMILA PAZ', 'c.cardozanunez@outlook.com', 'CARDOZA NUÑEZ CAMILA PAZ.jpg', '1996-09-07', 30, 11, 1),
('019.412.104-0', 'LIZANA ARMIJO CONSTANZA CAMILA', 'constanzalizana96@gmail.com', 'LIZANA ARMIJO CONSTANZA CAMILA.jpg', '1996-11-14', 50, 10, 1),
('019.412.326-4', 'MALDONADO VILCHES NICOLAS FERNANDO', 'nicofer1797@gmail.com', 'MALDONADO VILCHES NICOLAS FERNANDO.jpg', '1997-01-17', 42, 4, 1),
('019.412.770-7', 'AYALA ARMIJO DANITZA TAMARA', 'danitzaayala197@gmail.com', 'AYALA ARMIJO DANITZA TAMARA.jpg', '1997-04-01', 41, 16, 1),
('019.413.049-K', 'ALIAGA CARTAGENA FRANCISCA VALENTINA', 'fran081997@gmail.com', 'ALIAGA CARTAGENA FRANCISCA VALENTINA.jpg', '1997-06-08', 50, 10, 1),
('019.537.466-K', 'SANTANA HUENULEF FERNANDA CAMILA', 'fernandasantana.h40@gmail.com', 'SANTANA HUENULEF FERNANDA CAMILA.jpg', '1997-10-07', 41, 16, 1),
('019.646.692-4', 'BASULTO CORTES MARIA JOSE', 'maria.basultoc@gmail.com', 'BASULTO CORTES MARIA JOSE.jpg', '1997-04-08', 43, 10, 1),
('019.689.979-0', 'ALVAREZ PIZARRO MARIA JOSE', 'majoalvarez1211@gmail.com', 'ALVAREZ PIZARRO MARIA JOSE.jpg', '1997-11-12', 44, 10, 1),
('019.732.499-6', 'MALLEA GUERRERO JAVIERA FERNANDA', 'jmalleaguerrero@gmail.com', 'MALLEA GUERRERO JAVIERA FERNANDA.jpg', '1997-09-29', 19, 15, 1),
('019.758.443-2', 'DURAN NARANJO JAVIERA CAROLINA', 'javidurann@icloud.com', 'DURAN NARANJO JAVIERA CAROLINA.jpg', '1998-05-04', 31, 11, 1),
('019.803.992-6', 'ARAUS ARAUS ANTONIA CATALINA', 'antoniacataaraus@gmail.com', 'ARAUS ARAUS ANTONIA CATALINA.jpg', '1998-04-18', 48, 10, 1),
('019.880.714-1', 'URBINA NAHUEL MALINDY ANASTASIA', 'm.nahuel.ur@gmail.com', 'URBINA NAHUEL MALINDY ANASTASIA.jpg', '1988-05-09', 50, 10, 1),
('019.887.740-9', 'BAEZ BAEZ DANIELA ANDREA', 'baezdaniela.ab@gmail.com', 'BAEZ BAEZ DANIELA ANDREA.jpg', '1998-02-22', 49, 10, 1),
('019.922.131-0', 'ANAIS ESCOBAR SERGIO ANDRE', 'sergioanais98@gmail.com', 'ANAIS ESCOBAR SERGIO ANDRE.jpg', '1998-05-15', 8, 8, 1),
('019.924.337-3', 'BARRERA MEZA JAVIERA CECILIA', 'javierabarrerameza@gmail.com', 'BARRERA MEZA JAVIERA CECILIA.jpg', '1998-04-30', 48, 10, 1),
('019.924.363-2', 'REYES CORDOVA CATALINA PAOLA', 'catalinareyesc1998@gmail.com', 'REYES CORDOVA CATALINA PAOLA.jpg', '1998-05-06', 50, 10, 1),
('019.924.450-7', 'ROMAN DIAZ CATALINA DEL CARMEN', 'cataromand15@gmail.com', 'ROMAN DIAZ CATALINA DEL CARMEN.jpg', '1998-06-09', 1, 1, 1),
('019.985.259-0', 'VILCHEZ VILLA JUAN CRISTOBAL', 'juancri7216@gmail.com', 'VILCHEZ VILLA JUAN CRISTOBAL.jpg', '1998-09-01', 1, 1, 1),
('020.123.384-4', 'GUTIERREZ ARANEDA MARIA FRANCISCA', 'mariafranciscaaranedagutierrez@gmail.com', 'GUTIERREZ ARANEDA MARIA FRANCISCA.jpg', '1998-10-23', 53, 10, 1),
('020.123.638-K', 'HUERTA SILVA SEBASTIAN ALEXIS', 'sebastianhuerta532@gmail.com', 'HUERTA SILVA SEBASTIAN ALEXIS.jpg', '1998-12-07', 1, 1, 1),
('020.123.657-6', 'VALDENEGRO MIRANDA GENESIS CATALINA', 'genevaldenegro@icloud.com', 'VALDENEGRO MIRANDA GENESIS CATALINA.jpg', '1998-11-20', 43, 10, 1),
('020.123.864-1', 'SERRANO ALLENDES CARLOS IGNACIO', 'serranocarlos177@gmail.com', 'SERRANO ALLENDES CARLOS IGNACIO.jpg', '1999-01-10', 50, 10, 1),
('020.123.995-8', 'CASTILLO HINOJOSA JAVIERA CONSTANZA', 'javiera.c.castillohinojosa@gmail.com', 'CASTILLO HINOJOSA JAVIERA CONSTANZA.jpg', '1999-02-22', 41, 16, 1),
('020.124.394-7', 'CERON SUAREZ SOFIA BETSABE', 'sofiaceron0510@gmail.com', 'CERON SUAREZ SOFIA BETSABE.jpg', '1999-05-10', 51, 10, 1),
('020.245.968-4', 'LOPEZ AGUILAR KRISHNA MARIBEL', 'krishnalopez.1b@gmail.com', 'LOPEZ AGUILAR KRISHNA MARIBEL.jpg', '2000-10-05', 45, 10, 1),
('020.310.905-9', 'CORDOVA SILVA MELANIE ALEJANDRA', 'cordovamelanie02@gmail.com', 'CORDOVA SILVA MELANIE ALEJANDRA.jpg', '1999-10-27', 53, 10, 1),
('020.311.054-5', 'LOYOLA REYES VALENTIN ENRIQUE', 'valentinloyola@hotmail.com', 'LOYOLA REYES VALENTIN ENRIQUE.jpg', '2000-01-27', 49, 10, 1),
('020.311.934-8', 'MORALES CUEVAS VALENTINA ALEJANDRA', 'valentina.moralescuevas19@gmail.com', 'MORALES CUEVAS VALENTINA ALEJANDRA.jpg', '2000-09-06', 52, 10, 1),
('020.311.959-3', 'MORALES SANTIBAÑEZ MARIA ANTONIETA', 'moralessantibanez0@gmail.com', 'MORALES SANTIBAÑEZ MARIA ANTONIETA.jpg', '2000-09-11', 1, 1, 1),
('020.312.013-3', 'BUSTOS MALDONADO MARIA FERNANDA', 'maldonadomariafernanda878@gmail.com', 'BUSTOS MALDONADO MARIA FERNANDA.jpg', '2000-09-16', 46, 10, 1),
('020.336.237-4', 'CHANDIA PADILLA DAGOBERTO SEBASTIAN', 'dagochandia4@gmail.com', 'CHANDIA PADILLA DAGOBERTO SEBASTIAN.jpg', '2000-05-25', 35, 11, 1),
('020.379.554-8', 'GALLARDO GONZALEZ JAVIERA CAROLINA', 'javiera.gallardoo12@gmail.com', 'GALLARDO GONZALEZ JAVIERA CAROLINA.jpg', '1999-11-04', 48, 10, 1),
('020.603.421-1', 'MUÑOZ ORMEÑO NATALIA BELEN', 'nataliabelen1515@hotmail.com', 'MUÑOZ ORMEÑO NATALIA BELEN.jpg', '2000-09-15', 53, 10, 1),
('020.603.559-5', 'BUSTOS ORTEGA DIANA CAMILA', 'caamiibustos15@gmail.com', 'BUSTOS ORTEGA DIANA CAMILA.jpg', '2000-10-24', 7, 8, 1),
('020.603.617-6', 'PINTO JERIA SONIA CONSTANZA', 'soniapintoj@gmail.com', 'PINTO JERIA SONIA CONSTANZA.jpg', '2000-10-27', 53, 10, 1),
('020.603.811-K', 'MARTINEZ TOLEDO CAMILA IGNACIA', 'martinezcamila740@gmail.com', 'MARTINEZ TOLEDO CAMILA IGNACIA.jpg', '2000-12-06', 53, 10, 1),
('020.603.940-K', 'URBINA MELLADO VALERIA ALEJANDRA', 'valeriurbinamellado@gmail.com', 'URBINA MELLADO VALERIA ALEJANDRA.jpg', '2001-01-01', 46, 10, 1),
('020.604.145-5', 'BUSTAMANTE OROZCO MARIA CATALINA', 'cb59071@gmail.com', 'BUSTAMANTE OROZCO MARIA CATALINA.jpg', '2001-02-15', 41, 16, 1),
('020.604.202-8', 'CARREÑO VARGAS CARLA JAZMIN', 'carlitajazmin4@gmail.com', 'CARREÑO VARGAS CARLA JAZMIN.jpg', '2001-02-26', 53, 10, 1),
('020.604.267-2', 'CARDOZA MIRANDA JOSE IGNACIO', 'joseignaciocardoza@gmail.com', 'CARDOZA MIRANDA JOSE IGNACIO.jpg', '2001-03-20', 12, 9, 1),
('020.878.820-5', 'CONTRERAS TAPIA MILLARAY ALMENDRA', 'millacontreras416@gmail.com', 'CONTRERAS TAPIA MILLARAY ALMENDRA.jpg', '2001-10-25', 41, 16, 1),
('020.879.027-7', 'BUSTOS REYES JOSE LUIS', 'jos.bustosr@gmail.com', 'BUSTOS REYES JOSE LUIS.jpg', '2001-12-11', 13, 9, 1),
('020.879.300-4', 'BERRIOS IBARRA KARINA ALEJANDRA', 'kberriosibarra@gmail.com', 'BERRIOS IBARRA KARINA ALEJANDRA.jpg', '2002-02-12', 50, 10, 1),
('020.879.545-7', 'CORTES ADRIAZOLA GABRIELA IGNACIA', 'gabriellacortesadria@gmail.com', 'CORTES ADRIAZOLA GABRIELA IGNACIA.jpg', '2002-04-11', 46, 10, 1),
('020.879.724-7', 'VELIS SALAS FRANCISCA ISIDORA', 'franciscavelissalas@gmail.com', 'VELIS SALAS FRANCISCA ISIDORA.jpg', '2002-05-20', 1, 1, 1),
('021.495.682-9', 'CUEVAS ALIAGA ANTONIA PAZ', 'antocuevasa8@gmail.com', 'CUEVAS ALIAGA ANTONIA PAZ.jpg', '2004-01-26', 50, 10, 1),
('021.932.645-9', 'DAZA GONZALEZ SONIA VALENTINA', 'soniavalentina@yahoo.es', 'DAZA GONZALEZ SONIA VALENTINA.jpg', '1999-12-28', 10, 8, 1),
('0212325422', 'TORO YAñEZ LA CABRA', 'mati.veliz2003@gmail.com', 'TORO YAñEZ LA CABRA.png', '2003-02-06', 16, 13, 1),
('022.034.186-0', 'MILLAN PENAGOS KERIN ALEJANDRA', 'kmillanpenagos@gmail.com', 'MILLAN PENAGOS KERIN ALEJANDRA.jpg', '1978-10-08', 1, 1, 1),
('022.264.410-0', 'MILLAN PENAGOS ESMERALDA JOHANA', 'esme@live.cl', 'MILLAN PENAGOS ESMERALDA JOHANA.jpg', '1979-09-28', 31, 11, 1);

--
-- Disparadores `personal`
--
DELIMITER $$
CREATE TRIGGER `after_personal_insert` AFTER INSERT ON `personal` FOR EACH ROW BEGIN
    INSERT INTO usuarios (rut, nombre_usuario, contraseña, fecha_creacion) 
    SELECT 
        NEW.rut AS rut,
        SUBSTRING(REPLACE(REPLACE(NEW.rut, '.', ''), '-', ''), IF(LEFT(NEW.rut, 1) = '0', 2, 1)) AS nombre_usuario,
        SHA2(RIGHT(SUBSTRING(REPLACE(REPLACE(NEW.rut, '.', ''), '-', ''), IF(LEFT(NEW.rut, 1) = '0', 2, 1)), 4), 256) AS contraseña,
        CURRENT_TIMESTAMP AS fecha_creacion;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_personal_delete` BEFORE DELETE ON `personal` FOR EACH ROW BEGIN
    DELETE FROM `usuarios` WHERE `rut` = OLD.`rut`;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `delete_user_before_personal` BEFORE DELETE ON `personal` FOR EACH ROW BEGIN
    DELETE FROM usuarios WHERE rut = OLD.rut;
    -- Agrega más DELETE para otras relaciones del usuario si las tienes
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas_encuesta`
--

CREATE TABLE `preguntas_encuesta` (
  `id_pregunta` int(11) NOT NULL,
  `pregunta` text NOT NULL,
  `tipo_pregunta` varchar(50) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `preguntas_encuesta`
--

INSERT INTO `preguntas_encuesta` (`id_pregunta`, `pregunta`, `tipo_pregunta`, `fecha_creacion`) VALUES
(26, 'que te parecio el event del 18?', 'texto', '2024-10-08 20:54:53'),
(27, 'hola probando 123', 'seleccion_unica', '2024-10-08 20:55:27'),
(28, 'estas satisfecho con nuestra aplicacion?', 'seleccion_unica', '2024-10-09 15:51:22'),
(29, 'estas satisfecho con nuestra aplicacion?', 'texto', '2024-10-09 17:36:04'),
(30, 'que te parecio el event del 18?', 'seleccion_unica', '2024-10-09 17:36:20'),
(34, 'que te parecio el event del 18?', 'seleccion_unica', '2024-10-17 14:19:31'),
(35, 'que te parecio el event del año nuevo?', 'texto', '2024-10-29 19:13:01'),
(36, 'que te parecio el event del 18?', 'seleccion_unica', '2024-10-29 19:15:57');

--
-- Disparadores `preguntas_encuesta`
--
DELIMITER $$
CREATE TRIGGER `bef_delete_preg_enc` BEFORE DELETE ON `preguntas_encuesta` FOR EACH ROW BEGIN
    -- Eliminar las respuestas ligadas a la pregunta que será eliminada
    DELETE FROM respuestas_encuesta WHERE id_pregunta = OLD.id_pregunta;

    -- Eliminar las opciones ligadas a la pregunta que será eliminada
    DELETE FROM opciones_encuesta WHERE id_pregunta = OLD.id_pregunta;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_delete_pregunta` BEFORE DELETE ON `preguntas_encuesta` FOR EACH ROW BEGIN
    -- Elimina todas las respuestas asociadas a la pregunta que se va a eliminar
    DELETE FROM respuestas_encuesta WHERE id_pregunta = OLD.id_pregunta;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas_encuesta`
--

CREATE TABLE `respuestas_encuesta` (
  `id_respuesta` int(11) NOT NULL,
  `rut_usuario` varchar(13) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `calificacion` int(11) DEFAULT NULL,
  `respuesta` text DEFAULT NULL,
  `fecha_respuesta` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `respuestas_encuesta`
--

INSERT INTO `respuestas_encuesta` (`id_respuesta`, `rut_usuario`, `id_pregunta`, `calificacion`, `respuesta`, `fecha_respuesta`) VALUES
(28, '016.291.388-3', 26, NULL, 'buenisimo :3', '2024-10-09 15:48:25'),
(46, '016.291.388-3', 28, NULL, 'que', '2024-10-09 15:51:31'),
(47, '016.291.388-3', 28, 0, 'no', '2024-10-09 15:55:53'),
(48, '016.291.388-3', 27, NULL, '1234', '2024-10-09 16:08:30'),
(49, '018.213.543-7', 28, NULL, 'si', '2024-10-09 16:49:03'),
(50, '018.213.543-7', 26, 5, 'buenisimo 10/10', '2024-10-09 16:58:35'),
(51, '018.213.543-7', 27, NULL, '1234', '2024-10-09 17:08:36'),
(56, '016.291.388-3', 30, NULL, 'malo', '2024-10-10 16:38:52'),
(57, '016.291.388-3', 30, NULL, 'bueno', '2024-10-10 16:39:08'),
(60, '016.291.388-3', 29, 5, 'ahora g2\n', '2024-10-10 16:44:01'),
(61, '016.291.388-3', 30, 0, 'malo', '2024-10-10 17:20:25'),
(66, '016.291.388-3', 34, NULL, 'malo', '2024-10-17 14:20:26'),
(67, '013.612.924-4', 35, 3, 'maomoneo', '2024-10-29 19:13:44'),
(68, '015.624.248-9', 35, 1, 'malo', '2024-10-29 19:15:15'),
(69, '013.612.924-4', 36, NULL, 'maomeno', '2024-10-29 19:16:09'),
(70, '019.067.740-0', 26, 5, 'malisimo\r\n', '2024-11-08 16:40:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `rol` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `rol`) VALUES
(1, 'Administrador'),
(2, 'Gerente'),
(4, 'Soporte informatico'),
(5, 'RRHH'),
(8, 'Asistente'),
(9, 'Auxiliares'),
(10, 'Tens'),
(11, 'Enfermeria'),
(12, 'Directivos'),
(13, 'Coordinadores'),
(14, 'Encargados'),
(15, 'Finanzas'),
(16, 'Recepcionista'),
(17, 'Otros');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes`
--

CREATE TABLE `solicitudes` (
  `id` int(11) NOT NULL,
  `rut` varchar(13) NOT NULL,
  `id_area` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `id_sub_servicio` int(11) NOT NULL,
  `comentarios` text DEFAULT NULL,
  `id_rol` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitudes`
--

INSERT INTO `solicitudes` (`id`, `rut`, `id_area`, `id_categoria`, `id_sub_servicio`, `comentarios`, `id_rol`, `fecha_hora`) VALUES
(11, '016.291.388-3', 1, 1, 1, 'asdadas', 4, '2024-10-15 22:18:37'),
(24, '016.291.388-3', 2, 2, 2, '', 4, '2024-10-16 21:34:56'),
(26, '016.291.388-3', 2, 2, 2, '', 4, '2024-10-16 21:35:07'),
(29, '016.291.388-3', 1, 1, 1, '', 4, '2024-10-18 16:12:23'),
(30, '013.560.287-6', 2, 2, 2, '', 14, '2024-10-24 18:15:15'),
(31, '016.291.388-3', 1, 1, 1, 'LAJKJDLKASJDLKASJDLKAJDLKAJDLKAJDLAKJDLKSJLAKJDLKAJDLAKDJALKDJADLKASJDLKAJDLKASD', 4, '2024-10-28 11:00:55'),
(32, '013.612.924-4', 1, 1, 1, 'necesito mi documento de antiguedad', 5, '2024-10-29 11:01:16'),
(34, '013.612.924-4', 1, 1, 1, 'antiguedad', 5, '2024-10-29 16:21:02'),
(35, '013.612.924-4', 1, 1, 1, 'a', 5, '2024-11-11 11:14:55'),
(36, '013.612.924-4', 1, 1, 1, 'aaaa', 5, '2024-11-11 11:15:03'),
(37, '016.291.388-3', 1, 1, 1, 'adadaasd', 4, '2024-11-11 13:30:16'),
(39, '015.624.248-9', 2, 2, 2, 'asdsadasd', 10, '2024-11-13 16:49:52'),
(40, '015.624.248-9', 1, 1, 1, 'sadasdasdas', 10, '2024-11-13 16:50:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `soli_areas`
--

CREATE TABLE `soli_areas` (
  `id` int(11) NOT NULL,
  `nombre_area` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `soli_areas`
--

INSERT INTO `soli_areas` (`id`, `nombre_area`) VALUES
(1, 'RRHH'),
(2, 'Informatica');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `soli_categorias`
--

CREATE TABLE `soli_categorias` (
  `id` int(11) NOT NULL,
  `id_area` int(11) NOT NULL,
  `nombre_categoria` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `soli_categorias`
--

INSERT INTO `soli_categorias` (`id`, `id_area`, `nombre_categoria`) VALUES
(1, 1, 'Documentos'),
(2, 2, 'Auditorio');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `soli_respuestas`
--

CREATE TABLE `soli_respuestas` (
  `id` int(11) NOT NULL,
  `solicitud_id` int(11) NOT NULL,
  `rut_usuario` varchar(20) NOT NULL,
  `respuesta_texto` text DEFAULT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `fecha_respuesta` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `soli_respuestas`
--

INSERT INTO `soli_respuestas` (`id`, `solicitud_id`, `rut_usuario`, `respuesta_texto`, `archivo`, `fecha_respuesta`) VALUES
(1, 11, '016.291.388-3', 'holi, aqui esta el archivo q me pidio ', 'uploads/Listado Personal Correos Electronicos.xlsx', '2024-10-24 21:51:17'),
(2, 30, '013.560.287-6', 'se enviaran enseguida los computadores a auditoria', '', '2024-10-24 23:15:54'),
(3, 31, '016.291.388-3', 'gurren lagan', 'uploads/rut ligado a cargo.xlsx', '2024-10-28 18:04:23'),
(9, 32, '013.612.924-4', 'toma', 'uploads/Listado Personal Correos Electronicos (1).xlsx', '2024-10-29 16:42:11'),
(10, 34, '013.612.924-4', '', 'uploads/1.3_APT122_AutoevaluaciónFase1 (2).docx', '2024-10-29 20:25:30'),
(11, 29, '013.612.924-4', 'zxczcz', '', '2024-11-11 14:07:46'),
(12, 36, '013.612.924-4', '', 'uploads/013.612.924-4/[Modelo] Informe P. Profesional_Nombre y Apellido.docx', '2024-11-11 15:15:32'),
(13, 35, '013.612.924-4', '', 'uploads/013.612.924-4/1.3_APT122_AutoevaluaciónFase1 (2) (1).docx', '2024-11-11 15:15:59'),
(14, 26, '016.291.388-3', 'asdad', '', '2024-11-11 20:42:26'),
(15, 24, '016.291.388-3', 'aqui esta el doc', 'archivos/solicitudes/016.291.388-3/[Modelo] Informe P. Profesional_Nombre y Apellido.docx', '2024-11-11 20:44:42'),
(16, 39, '013.612.924-4', 'asda', '', '2024-11-13 20:50:13'),
(17, 40, '013.612.924-4', 'bvcSDSDSDSDSDSDSDSDSDSDSDSDSDSDSDSDSDSDSDSDSDSDSDSDSDSDSDSDSDSDSDSD', 'archivos/solicitudes/015.624.248-9/[Modelo] Informe P. Profesional_Nombre y Apellido.docx', '2024-11-13 20:50:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `soli_servicios`
--

CREATE TABLE `soli_servicios` (
  `id` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `nombre_sub_servicio` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `soli_servicios`
--

INSERT INTO `soli_servicios` (`id`, `id_categoria`, `nombre_sub_servicio`) VALUES
(1, 1, 'solicitar documentos'),
(2, 2, 'Computador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `soportes`
--

CREATE TABLE `soportes` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `contenido` text NOT NULL,
  `urgencia` enum('bajo','medio','alto') NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `rut` varchar(13) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `estado` enum('En espera','En curso','Solucionado') DEFAULT 'En espera',
  `fecha_solucionado` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `soportes`
--

INSERT INTO `soportes` (`id`, `titulo`, `contenido`, `urgencia`, `imagen`, `fecha_creacion`, `rut`, `rol_id`, `estado`, `fecha_solucionado`) VALUES
(14, 'TOKEN DE 473', 'ASDASD', 'bajo', 'Images/soporte_img/wtf.jpg', '2024-10-30 17:29:14', '016.291.388-3', 4, 'Solucionado', '2024-11-04 15:58:44'),
(16, 'Problmea con kopland ', 'ASDADASDADASDA', 'alto', 'Images/soporte_img/sans.jpg', '2024-10-30 17:30:39', '016.291.388-3', 4, 'En espera', NULL),
(19, 'PROBANDO ACTU ', 'sadasdas', 'alto', 'Images/soporte_img/image.png', '2024-11-15 14:08:55', '013.612.924-4', 5, 'En curso', NULL),
(22, '122222222222222222222222222222222222222', 'sadsadasdas', 'alto', NULL, '2024-11-15 15:23:54', '013.612.924-4', 5, 'Solucionado', '2024-11-15 15:35:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `rut` varchar(13) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`rut`, `nombre_usuario`, `contraseña`, `fecha_creacion`, `activo`) VALUES
('004.108.784-6', '041087846', 'e995babee4a69297fb22545147cbb59443f493908e1e3c495d9150cc8e9c78b3', '2024-09-23 16:42:48', 1),
('005.217.048-6', '052170486', 'e40e0225f947f1ae6cba2245dd9a1a50361512bb718c1ca1ce3f983fe3b3aa4e', '2024-09-23 16:42:48', 1),
('006.760.123-8', '067601238', '8de143c7e8ffc2a50d4910226e43210686863274cb0435990149fdecb0163dd8', '2024-09-23 16:42:48', 1),
('007.010.373-7', '070103737', 'f021014960c5f61b68f18f5ec06e3d02982b069f2230cc120b6ca3061868d6e2', '2024-09-23 16:42:48', 1),
('008.055.787-6', '080557876', '05a4cd58579909328296060a91fa22242c6449980486c196868a007bc4ccd455', '2024-09-23 16:42:48', 1),
('008.091.193-9', '080911939', '94ad0b5b0a595b7eaafe9404423d8543b4c8dcfc26ecffb44f6894e2ca46fd61', '2024-09-23 16:42:48', 1),
('008.265.448-8', '082654488', 'be9deda60cc4b7cfdc2f1e3396ebed5ad6d28937205db8ef46c6a0ed32cd5841', '2024-09-23 16:42:48', 1),
('008.369.757-1', '083697571', '7523c9c2844bf4ee5b29e6ed142171b6e56ca1fdea8e512c8cab2e931b91e925', '2024-09-23 16:42:48', 1),
('008.729.610-5', '087296105', 'bb60bd01547340647bd3b49ecb0632c504c89fadbb31b09d483092de91a44e73', '2024-09-23 16:42:48', 1),
('009.080.082-5', '090800825', '0581fd688d7aee6463c55b053661a94bdc4badef25a23514cfe2621397012f35', '2024-09-23 16:42:48', 1),
('009.636.190-4', '096361904', '90bbc9533a02213ffdf4d1482eb9b97a5feec554e33641d40f24777cbe5a8341', '2024-09-23 16:42:48', 1),
('009.835.804-8', '098358048', 'b0644ea728716c39361af0c766ccf408f962005a0c577f773fd46ff95eab30cb', '2024-09-23 16:42:48', 1),
('009.966.634-K', '09966634K', '3f4306ad1938fb8a568a6cd542797258030e13c17acff98b8c781b4699de5bd7', '2024-09-23 16:42:48', 1),
('010.210.899-K', '10210899K', 'fd085a93554ff059f8a1059d755e3e5d46589148b28577b86166bffd65b493c3', '2024-09-23 16:42:48', 1),
('010.519.341-6', '105193416', '71eebf8f5760b70c2eb19230bef73d7cc009bbf06695182f42782114b0d53ab7', '2024-09-23 16:42:48', 1),
('010.689.130-3', '106891303', '58070c528ac8e387bfc110298bf417979caaa9ec0612a18f3c6ae17bd425090d', '2024-09-23 16:42:48', 1),
('010.816.905-2', '108169052', '214538a798d46607ed8c5bb7cb54c13f9bc164789f576296189559feeef5b3ad', '2024-09-23 16:42:48', 1),
('010.835.657-K', '10835657K', '5b5041f94c6971092c6de06c824fc7d44b75103ada6e961acbf964f7300ab9f8', '2024-09-23 16:42:48', 1),
('010.943.542-2', '109435422', 'e084d7507dab6604894c203b3834cd7ce8f16385daeae56e202cd9e930f788d2', '2024-09-23 16:42:48', 1),
('010.984.579-5', '109845795', 'f69861481e073a00af7e91a27ef8c4384f3cf7f753b48d4ce654d6bd54f62b99', '2024-09-23 16:42:48', 1),
('011.170.481-3', '111704813', '9df82ff1547bdff292926e347ad933ea0af998b93568749faca6b121222a04cf', '2024-09-23 16:42:48', 1),
('011.230.831-8', '112308318', '453afb3d310def9d21d43098461dcd25b506fe4b760db482b7b524e82b14b5cc', '2024-09-23 16:42:48', 1),
('011.396.369-7', '113963697', '72abacb094771a0a64a5494730ee84e4628681cfcc6523bff0bc6ebf7c5a5a10', '2024-09-23 16:42:48', 1),
('011.397.025-1', '113970251', 'e0799b5b526a8866b02243691e20c51cf33ef66d84c7c4e3b99727aa48d21d20', '2024-09-23 16:42:48', 1),
('011.608.005-2', '116080052', 'a62aef6bb252cddd3bba35aa52968f35ba596d0e5cda16b5989b195a5c4df4a5', '2024-09-23 16:42:48', 1),
('011.608.394-9', '116083949', 'f8a789591ad8dac092a4fb7f4c75569fb7bd9dcd6ff98f4898d4f9cfb1f06fb5', '2024-09-23 16:42:48', 1),
('011.697.150-K', '11697150K', '173877b81acc6e0abc568a2d3eadf0a1ef5c90f41fdda6f6ed40cf3781e0e838', '2024-09-23 16:42:48', 1),
('011.697.156-9', '116971569', 'c98c37cbc0242b2cd603d4ad823a1c29e7314df3ad8f1810cab0fb98c84fc2c4', '2024-09-23 16:42:48', 1),
('011.697.805-9', '116978059', '384c96b545513a15112f75f254140f4c3223c21d6e7eeb4b3d1caa9eab89f218', '2024-09-09 16:58:20', 1),
('011.948.720-K', '11948720K', 'eadc095f9d7a39e83067ce6a1e8e0825675ff87698a8a5500e9db65817def55c', '2024-09-23 16:42:48', 1),
('011.980.181-8', '119801818', '8ffe8459134b46975acd31df13a50c51dbeacf1c19a764bf1602ba7c73ffc8fb', '2024-09-23 16:42:48', 1),
('012.178.058-5', '121780585', '3d1cc14a8f634815923386e03a67ddd2a86d223c553cfd35be6b950bcecb093d', '2024-09-23 16:42:48', 1),
('012.178.390-8', '121783908', '03f65a290e11d78647e557030042d74d268521768c4a1648264a1b38047a4b37', '2024-09-23 16:42:48', 1),
('012.178.902-7', '121789027', 'f15d449e81e8973348b1777a703a6f3edf2850104cfd1bae2cb50d4f88a404b8', '2024-09-23 16:42:48', 1),
('012.584.671-8', '125846718', 'bb340b0f7e66b52b7426c07934c03a99881c41e8ea05b4ceba0e3f09b24dd538', '2024-09-23 16:42:48', 1),
('012.799.368-8', '127993688', '670b08a8750893e8ba690b1b11f3138c9c6935977a68486854a0a518ce4156ce', '2024-09-23 16:42:48', 1),
('013.057.919-1', '130579191', 'd5330200931a810748a2a665d5c597676cf92ff5585d9a0287923affd27369bb', '2024-09-23 16:42:48', 1),
('013.338.299-2', '133382992', '5e387e075b1e55bb4cee37981404386671c0d18d82eaf5decd10f9f2daee8a05', '2024-09-23 16:42:48', 1),
('013.338.575-4', '133385754', '16608390f96063e7f5af2c0bddf798a0231abc3414992af155ce8f1e9ee650dd', '2024-09-23 16:42:48', 1),
('013.340.833-9', '133408339', 'd1069877e7102e0b4c7ad7dad4074e9510420a2e2a1b6996bddf0a1c228e6749', '2024-09-23 16:42:48', 1),
('013.340.890-8', '133408908', '165e75840c3993a19ef16c0235963ba9d3b278953c8114c06e4034d7d4836803', '2024-09-23 16:42:48', 1),
('013.405.524-3', '134055243', 'e206dd002a594e557800e0de38fbbaf0e371a2604b75c85fd4afb57138851e2c', '2024-09-23 16:42:48', 1),
('013.560.287-6', '135602876', 'bc805497f86694cef2e1cf6df62989cb818f69a80f4fb170e5b7cd66aef5d64b', '2024-09-23 16:42:48', 1),
('013.612.924-4', '136129244', '82f1fc68c02f9f616f47296d1d2c9242e387944e47d0647e35eb26fe0b1ee31a', '2024-09-23 16:42:48', 1),
('013.663.177-2', '136631772', '075441be7bc0cdbab6093bbaed5a25b2c06d33c6a2e74601cbea17d0885a75a5', '2024-09-23 16:42:48', 1),
('013.772.318-2', '137723182', '6749ccd704f8c8bfe84093c1ac87c1d39898cd313f30f165416cd948bfaf9c28', '2024-09-23 16:42:48', 1),
('013.772.680-7', '137726807', 'dc6c5df2f682fbfbcd174159a3c8fccaf571ed5609acf3386290b6df9994b030', '2024-09-23 16:42:48', 1),
('013.773.309-9', '137733099', '3c8b996ee07b276ec0f915f23e940adbd1fb336db41a443edb4a089a63c79352', '2024-09-23 16:42:48', 1),
('014.007.065-3', '140070653', '99f4f9d8b5b4fdfb1b6141fe6c4cfff15532006b13f4ef1bbde1c8dd7dc80a14', '2024-09-23 16:42:48', 1),
('014.246.103-K', '14246103K', 'd351aad0f05a282bd5b8e9577ded170ee9abe7695b76b64ac92e5d4594993e55', '2024-09-23 16:42:48', 1),
('014.312.126-7', '143121267', 'ec216fb204db213fecf8a4a72363a84fc97d5d1e3ec362eaf42a69ce6a025e16', '2024-09-23 16:42:48', 1),
('014.312.453-3', '143124533', '323e4847e6864b77e07ca44f314f7d3677c9314e7259a99ca74c7b2c90a2e940', '2024-09-23 16:42:48', 1),
('014.379.751-1', '143797511', '440a5f172610e2fcfc71ff38c54a6c0638613b0ed0c3ed62d149d97a00372f28', '2024-09-23 16:42:48', 1),
('014.380.201-9', '143802019', '023e33504ab909cf87a6f4e4e545090e40bdc0a2153e5b68b19f7fad2b737904', '2024-09-23 16:42:48', 1),
('014.380.263-9', '143802639', 'b0981fa41b7abd6f7823e15e12cb37995821c63e5ea9121edebbbe109ec20245', '2024-09-23 16:42:48', 1),
('014.416.077-0', '144160770', 'b475282a6c30362274c7c3e99f0f5398ad6bbcfdf9bdc05ffee0a70b1b1fc537', '2024-09-23 16:42:48', 1),
('014.442.723-8', '144427238', 'bb08deaeb0e2fce9bb14e5e9cf3275fae1d3c8b8631f4e46a2e8a842ec96ae3c', '2024-09-23 16:42:48', 1),
('015.404.493-0', '154044930', 'afb36973671a3f3a0d2b2078c1d9aac9f2d019b374de201c25942dc3a2e62d15', '2024-09-23 16:42:48', 1),
('015.622.305-0', '156223050', '923460afd903841a21363fb1ae650e8d624aaea62efe94c043dced07673a52e3', '2024-09-23 16:42:48', 1),
('015.622.596-7', '156225967', 'a87f21d5268130b76fddbadf4b50f7c6d1b7c1bc0ee223c6f283ce8b2fc71a29', '2024-09-23 16:42:48', 1),
('015.624.248-9', '156242489', '9a1cfcffdce419d6f29a75e409e7777545f08520c667e460803db230c2ab3830', '2024-09-23 16:42:48', 1),
('015.681.591-8', '156815918', '356b964e125ff2d6b8ac99d4c5471425379fd88311a8e0f1d4833a997835f2df', '2024-09-23 16:42:48', 1),
('015.780.241-0', '157802410', 'd896af65d5b6b01300e22d3778efe9dc777fcde29ff9a6f2dd04242a7b0367ac', '2024-09-23 16:42:48', 1),
('015.796.663-4', '157966634', 'b138c0b35a91131122875f960be18c9873bd9bd0034ba7847caa364b91462473', '2024-09-23 16:42:48', 1),
('016.114.663-3', '161146633', 'e485f271f7db87ad8888f40c7b00412cc0c97c9bbf35790e7ba85e08ff602860', '2024-09-23 16:42:48', 1),
('016.290.784-0', '162907840', '40b39ea6c2159767b4444e02ae797a44fd37b380cbb203c58c451dace3b6d4de', '2024-09-23 16:42:48', 1),
('016.291.388-3', '162913883', '2a783f5db09827a7268061e9301367008827e2b835a233fab7395c1221c1cc3c', '2024-09-06 15:35:08', 1),
('016.341.258-6', '163412586', 'cfb05fff77b9b26d027b12c345d05bd1d453619318c52e827458b865860f6a85', '2024-09-23 16:42:48', 1),
('016.367.501-3', '163675013', '9e3816a4198e3722c9c9f9bc0217e092a2626b995bf820682a3dd9a2288d6f6f', '2024-09-23 16:42:48', 1),
('016.576.413-7', '165764137', '8d2efba45f136abdd6d9548ca3be7df0542bb39e5c0aadba883e191d1c54712b', '2024-09-23 16:42:48', 1),
('016.576.960-0', '165769600', 'cb4802988b933e8ff97a3e858151e41252e06a64e2e944ed6968ea5ed6018aa3', '2024-09-23 16:42:48', 1),
('016.577.130-3', '165771303', '58070c528ac8e387bfc110298bf417979caaa9ec0612a18f3c6ae17bd425090d', '2024-09-23 16:42:48', 1),
('016.577.383-7', '165773837', '2e86d55251d6498c920d16ad838f97057382f5d281c0f341d31a3e228f29842a', '2024-09-23 16:42:48', 1),
('016.577.451-5', '165774515', '136040f6ccedf6eee620407451ceefe7c93172d4386a0099758eff8e7ef1bbfe', '2024-09-23 16:42:48', 1),
('016.670.205-4', '166702054', '793de180d506f6cf4f63933deae077478547c31145ade55de72f98cb6d8cc282', '2024-09-23 16:42:48', 1),
('016.708.622-5', '167086225', 'f3fc70cb226d1f9c3e1c8ca9b5aba30957e43dc6269723e827b3e8fa12d230dd', '2024-09-23 16:42:48', 1),
('016.708.637-3', '167086373', 'b45febca2754cb4d62975a87deeee47986e4debf7af4da2c03a88c6c03ca116b', '2024-09-23 16:42:48', 1),
('016.758.860-3', '167588603', '2a891a20f59a24584d21bf77ed4d5fc967fb9343c17735d78e1f8d29ce5f12ae', '2024-09-23 16:42:48', 1),
('016.786.907-6', '167869076', 'cb7f82dc3ddf71d511da904c5d4a2c311c3f1d2ea3dc0652927e4ea6d5548e56', '2024-09-23 16:42:48', 1),
('016.856.366-3', '168563663', '90b8243fdbf5e03d85ff2a105242ab4be7bf44f5d7699c2cb648068c36237d48', '2024-09-23 16:42:48', 1),
('016.933.555-9', '169335559', '30c6570a99d5554ca6975f2ca386e7d3af1b3b92dc063e0d34d90223f5d2e7dd', '2024-09-23 16:42:48', 1),
('017.081.914-4', '170819144', 'daabc5d7dd36d9ee332a9fa45f00b5af77438bef04909d36bfe05d7568e97920', '2024-09-23 16:42:48', 1),
('017.082.075-4', '170820754', '73de0c729b9f0eb102b80b2459f5f9137563714fbfb106894f3873fe3b809820', '2024-09-23 16:42:48', 1),
('017.127.627-6', '171276276', '09cb71299e9ded350b21f4c9fcd648ded44920456a5dae51e70286c52569e05c', '2024-09-23 16:42:48', 1),
('017.398.118-K', '17398118K', '333c8a8f9dd2c93016910e91c0c6b135912c532c711a911c8e62ce862df2c10d', '2024-09-23 16:42:48', 1),
('017.399.273-4', '173992734', '4bc5b5c0c74badffcc7acb902a9cd02c36153af2edb617e7b3726ee00fc5263c', '2024-09-23 16:42:48', 1),
('017.966.075-K', '17966075K', 'be821fa7d86a6f9b3d8bb98effa1a833d3d4e3a3eda611a2d4a3e60bbdf139f1', '2024-09-23 16:42:48', 1),
('017.985.938-6', '179859386', '3217efb0c7592918e22986cb85ff86d1a7bbc81b6a293403235ebb2f952f6a1c', '2024-09-23 16:42:48', 1),
('017.985.939-4', '179859394', '36f817964b1ef56082c1f92ff9bce922de0f0ba0acc973155b1a4cfc5e6fa096', '2024-09-23 16:42:48', 1),
('017.986.298-0', '179862980', 'ed3f057dba227b4464df4351a9e1446fb7fd70aff119dab800498102300f5509', '2024-09-23 16:42:48', 1),
('017.986.809-1', '179868091', '570f9eac085a0183973170690355fa9f61e2bf0010f38101fd0d54f7788e9f4d', '2024-09-23 16:42:48', 1),
('018.011.629-K', '18011629K', '184cfe986bc119c5b6ab13ae15f87d085b23faa92604a7d4a5a94aade6697621', '2024-09-23 16:42:48', 1),
('018.081.476-0', '180814760', 'ce741a4b1dccaa73955ab83ecdc59db7caaa65f955d5435ff1ab152e01e71d55', '2024-09-23 16:42:48', 1),
('018.212.413-3', '182124133', 'a07cf27590e57bf851029cada1b752019189ba52defb3e43401887fcb2489b5e', '2024-09-23 16:42:48', 1),
('018.212.546-6', '182125466', 'a3eac2342f596faa5889cc8e589a689e102243d100f56d22442d729690592f67', '2024-09-23 16:42:48', 1),
('018.213.543-7', '182135437', 'dae89e11241685c65efa5f0ae0828574ab3a20de919325fbf83f9f2ff929dded', '2024-09-23 16:42:48', 1),
('018.324.947-9', '183249479', '6d2d7450bcc1d3ddc80eba9a018f7f8ca1f844a2c0c4850e1a076423d25235d1', '2024-09-23 16:42:48', 1),
('018.424.836-0', '184248360', '8b87536f8a2b8b843e653af46bd30d2806273249f4e1037a4392192c82fa7d80', '2024-09-23 16:42:48', 1),
('018.457.022-K', '18457022K', 'f9c3e6fecccb166b00b0f90a52be0a45adb827806f07769f74abedddc712f5fa', '2024-09-23 16:42:48', 1),
('018.487.184-K', '18487184K', 'b06c5f25710819dea22294a0ad313b92056a8fb7696c7aca482caf63e6a2a93a', '2024-10-02 15:47:53', 1),
('018.487.251-K', '18487251K', 'a8f2d2beeeca6c0c6b5ab7b5bcd7de660da56310a8432e4a1677f76ab5761278', '2024-09-23 16:42:48', 1),
('018.487.577-2', '184875772', '1246892ef4ec21379a82257e2c33bf86177b864c86ef44e49e861d0169425633', '2024-09-23 16:42:48', 1),
('018.569.401-1', '185694011', '0f179fbfd346fdd67b2f7276afcef7e3b617e71e86088ae47bc5cec86d9e5594', '2024-09-23 16:42:48', 1),
('018.624.771-K', '18624771K', 'f07d5fb0959db388b4fcbe8c5ec96e3ad9e3bc04ca99108761946e07b6964326', '2024-09-23 16:42:48', 1),
('018.777.145-5', '187771455', '257ad493dc36225fcc2b8db98198dd24a3379258a2121f421c965671ca308bf8', '2024-09-23 16:42:48', 1),
('019.063.023-4', '190630234', '0db4656d6fe25eb9a3be58879e74a29ef7b3409fcb2b905f325bfe073d42480e', '2024-09-23 16:42:48', 1),
('019.067.740-0', '190677400', '7078c7f8564ee0300ee371c8511553483f3465110b7b962bd63822b541aa8892', '2024-09-23 16:42:48', 1),
('019.068.395-8', '190683958', '015287fce017a7b874a7466be0d7e0423a7068b9cf19c4d7fa299d35128c7e05', '2024-09-23 16:42:48', 1),
('019.069.276-0', '190692760', '5fff864d27239fa252f76a884f2d427362b8e758d654db16a80d4136a1dca2d2', '2024-09-23 16:42:48', 1),
('019.069.843-2', '190698432', '533f4ff07f8d5bc358bbbe3c655c0099262440baf55b1fbbecc6151a08706f1c', '2024-09-23 16:42:48', 1),
('019.212.116-7', '192121167', '972fb81a3b9e6076bb06207a2687f1f1d167ec845852aa6a33e11c1cf1f282f7', '2024-09-23 16:42:48', 1),
('019.387.737-0', '193877370', '6f80b5cfb37b77f187f0ead220693d0335dc4398b05d8cac0f9592d0b54395d0', '2024-09-23 16:42:48', 1),
('019.404.231-0', '194042310', '21945e7f31fb51b4fccc6947a26b2573b9bc4763ae10b6bd1b59afda8959aab3', '2024-09-23 16:42:48', 1),
('019.411.173-8', '194111738', '30606ac3b4fd5c618ac4c6555ce007edd7fe73d75b685320aa56be211ebc4b40', '2024-10-02 15:58:31', 1),
('019.411.660-8', '194116608', '64a7395596281735024450a49eb6bb9201ec9d4175aeeb2ad6e5d9191ae04b18', '2024-09-23 16:42:48', 1),
('019.412.104-0', '194121040', '0d21ae129a64e1d19e4a94dfca3a67c777e17374e9d4ca2f74b65647a88119ea', '2024-09-23 16:42:48', 1),
('019.412.326-4', '194123264', '587887b1f664c61a994f9e4cbf72e138f42d54d4d0e95a1722f3d5b304d46049', '2024-09-23 16:42:48', 1),
('019.412.770-7', '194127707', '0a53ec672831a9da252456439c06ff3f181181b0602b8473ee3afb3d528a31a5', '2024-09-23 16:42:48', 1),
('019.413.049-K', '19413049K', '391744a21215745061f5d9c58dafddcf970a89d4156092333e8aaab02efdaa16', '2024-09-23 16:42:48', 1),
('019.537.466-K', '19537466K', 'ac19ac233919aa675bd663916de452fef1437faeb25a8b79454b703aa1446009', '2024-09-23 16:42:48', 1),
('019.646.692-4', '196466924', 'ba740deaf5506829cfe4062ca6d7ea7da8f6ecc6bf9c1277f0aceaedf58eb8b9', '2024-09-23 16:42:48', 1),
('019.689.979-0', '196899790', '02710fc94f019128aa19eefd971e8609a9707ad478a3e57ba7a366f3b5dbba7f', '2024-09-23 16:42:48', 1),
('019.732.499-6', '197324996', '576da443f7be5075a80a943b2501b19011ffd6cbec435df69e1cbb91061723f5', '2024-09-23 16:42:48', 1),
('019.758.443-2', '197584432', 'c848d8b966371b676698600ad8f542eca1b6aca7b8f4f3aef42e5e59d4c4e880', '2024-09-23 16:42:48', 1),
('019.803.992-6', '198039926', '4d31f39d152f87ae0ffbc7455b8e8e3b5eec282a8a22aef8cea16765b0f000d4', '2024-09-23 16:42:48', 1),
('019.880.714-1', '198807141', '8a9dd8ca8317aa590c6b22348000c17f51d1638cab16a7a0eea64c3b6c280b96', '2024-09-23 16:42:48', 1),
('019.887.740-9', '198877409', '5c70e097f61398b42cc4a176160f8ca88dc6e181230591416e04fc1a5bf5a882', '2024-09-23 16:42:48', 1),
('019.922.131-0', '199221310', '28995a2c0d04b66d5c7c818536bb00cd1fc1ac422aea47627de0999782fee3f4', '2024-09-23 16:42:48', 1),
('019.924.337-3', '199243373', '5ed4cae00638d8a9eaf32ba02116287d3ad6341f051718e47d26331939f731a3', '2024-09-23 16:42:48', 1),
('019.924.363-2', '199243632', '37df889efd442031f8614eb1e10ddbd95909b115c2f6c8e750fe2a6b3c074f54', '2024-09-23 16:42:48', 1),
('019.924.450-7', '199244507', 'a27d23ba80488a930cbfbb64db5f9e3761f5d0d0fc6e92da68abc3b48c802334', '2024-09-23 16:42:48', 1),
('019.985.259-0', '199852590', '69d9200c309f5f97567dc02e0898b21d18cd294ee8bd4d597edf3fdc015c83d2', '2024-09-23 16:42:48', 1),
('020.123.384-4', '201233844', '05ee8853268c69ba66c3a1c5433545b27e1e55ec8cc6d473a2b6a170a0820063', '2024-09-23 16:42:48', 1),
('020.123.638-K', '20123638K', '4e9bf0aad4417cf78228ac226af570ae9451b0bca66387ec6c1a4df7d5a0defa', '2024-10-02 16:02:41', 1),
('020.123.657-6', '201236576', '343c8d52baa0c98d0bbf5bef33246aa618dd628f73092ff20b8856293e8cb589', '2024-09-23 16:42:48', 1),
('020.123.864-1', '201238641', 'df7fecd585f7da0161c75c49b21cfece931fcd87a59e8c45fe1550cc43d85c8d', '2024-09-23 16:42:48', 1),
('020.123.995-8', '201239958', '80dd469bc6fc1ac5c51bfca801c605d6cb296868a644718171026820131a5ba8', '2024-09-23 16:42:48', 1),
('020.124.394-7', '201243947', '0baee19b37f1990636ab5815a9ca64e65a5fd4e5eee69b244aad87cf8e316346', '2024-09-23 16:42:48', 1),
('020.245.968-4', '202459684', '62e17c87411c32084be09d9e7e872ba7a3b0ec10b4924c8bf93a846894932d7b', '2024-09-23 16:42:48', 1),
('020.310.905-9', '203109059', 'eb982d2b777ad8d039420c3a7c6c342756093856e2eaf87b901ac089ba0b5414', '2024-09-23 16:42:48', 1),
('020.311.054-5', '203110545', '1d3a37fb3887d18d1b2171b2da0799b0920cd20472ac0069307ba16abe0dbc26', '2024-09-23 16:42:48', 1),
('020.311.934-8', '203119348', '736cf841efdf1b7fd11dd42acc384c53142382a61e29a5ab65c2b17f28fa76a9', '2024-09-23 16:42:48', 1),
('020.311.959-3', '203119593', '7edf7a2aab998565bb3bf363ba0879f0fa19166d9e3f59ceb12a03319bfa6e23', '2024-09-23 16:42:48', 1),
('020.312.013-3', '203120133', '339e8e63e3b56cc6cd5149d33a74ba3684aa4410df3f9433bde48ea32f0a2ad1', '2024-09-23 16:42:48', 1),
('020.336.237-4', '203362374', '92128c7fc22f3f685e9914450a0bd855e258aafff9351663260b080a4a7c5188', '2024-09-23 16:42:48', 1),
('020.379.554-8', '203795548', '869ab3d28136025484ecb4235eed83d0eacef3129d0ac4cf8b23afdab847fd6f', '2024-09-23 16:42:48', 1),
('020.603.421-1', '206034211', 'c864021c8f2bf3ad3d713c75125a7da8d3f10712d9e633f49f681af79b41146b', '2024-09-23 16:42:48', 1),
('020.603.559-5', '206035595', '2b20a53b75b0eb73f4e9fb9ec020e740e61c4e607b4aa6c1ea3eb233a5f74a82', '2024-09-23 16:42:48', 1),
('020.603.617-6', '206036176', '9ee00ea72efc54d7273beb4b750152fd84525145232ea286bef9508f755e5d77', '2024-09-23 16:42:48', 1),
('020.603.811-K', '20603811K', 'f0cc1cd7574dcb14fef7ce2d7517f55fbf5cef7bee267503e924c0cbb7f8f29e', '2024-09-23 16:42:48', 1),
('020.603.940-K', '20603940K', '19460dfa1b636bc55bf5f3aacacf9a931f47bf0a18249c049fedbfa08047babc', '2024-09-23 16:42:48', 1),
('020.604.145-5', '206041455', '257ad493dc36225fcc2b8db98198dd24a3379258a2121f421c965671ca308bf8', '2024-09-23 16:42:48', 1),
('020.604.202-8', '206042028', '6ae9e4d22c4670b9140fc378214b3274fb3f64d16058717f974515000680b24c', '2024-09-23 16:42:48', 1),
('020.604.267-2', '206042672', 'b11b916a54e9e274f044ff0ffa37651e9f024aa71f65d4205c3d0f446984bf5f', '2024-09-23 16:42:48', 1),
('020.878.820-5', '208788205', 'b9d7ccd5bdd0a0baf344adfc5669d27b3503f59f4a1d87bdd61b94d0810c7fe0', '2024-09-23 16:42:48', 1),
('020.879.027-7', '208790277', '7ad191d07ed73b128a44274bb2c045f5395a565e3685852a77b9c54774144bdb', '2024-09-23 16:42:48', 1),
('020.879.300-4', '208793004', 'ad25fc1532c8454fdda1d5e5258dd5771e919eaf4db2ca59842043804ccb6fb5', '2024-09-23 16:42:48', 1),
('020.879.545-7', '208795457', '56a5ec9c037f29456ae0acac1307832b0f56808d6a220d978237f721109a4111', '2024-09-23 16:42:48', 1),
('020.879.724-7', '208797247', '41bdad81f82b77b86fe7b25798291f8d07f75f82e82495a38073e2345e1b277b', '2024-09-23 16:42:48', 1),
('021.495.682-9', '214956829', '7bb674da8d83afb8d1d44090960b9c1a721d086f2759ac83917f298ded66c498', '2024-09-23 16:42:48', 1),
('021.932.645-9', '219326459', 'b4659ba19064b95e052239db97885c7c20e8a6491db14deef197cd69224cbae6', '2024-09-23 16:42:48', 1),
('0212325422', '212325422', 'e084d7507dab6604894c203b3834cd7ce8f16385daeae56e202cd9e930f788d2', '2024-11-20 16:43:03', 1),
('022.034.186-0', '220341860', '5cd5e6e836cd713686bd2ccc7a5626db84a4c23f91d00d02eaed726d9f5b7220', '2024-09-23 16:42:48', 1),
('022.264.410-0', '222644100', '69898c7bb333c6ad353c482fcc8f9603ededb2760f3f055096b25fe6dff69e38', '2024-09-23 16:42:48', 1);

--
-- Disparadores `usuarios`
--
DELIMITER $$
CREATE TRIGGER `delete_assistances_before_user` BEFORE DELETE ON `usuarios` FOR EACH ROW BEGIN
    DELETE FROM asistencia_capacitaciones WHERE rut_usuario = OLD.rut;
    -- Agrega aquí más tablas relacionadas con el usuario si es necesario
END
$$
DELIMITER ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `archivos`
--
ALTER TABLE `archivos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asistencias_eventos`
--
ALTER TABLE `asistencias_eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asistencias_eventos_ibfk_1` (`evento_id`),
  ADD KEY `asistencias_eventos_ibfk_2` (`rut_usuario`);

--
-- Indices de la tabla `asistencia_capacitaciones`
--
ALTER TABLE `asistencia_capacitaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `capacitacion_id` (`capacitacion_id`),
  ADD KEY `asistencia_capacitaciones_ibfk_2` (`rut_usuario`);

--
-- Indices de la tabla `capacitaciones`
--
ALTER TABLE `capacitaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `empleado_mes`
--
ALTER TABLE `empleado_mes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empleado_mes_ibfk_1` (`rut`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `foro_preguntas`
--
ALTER TABLE `foro_preguntas`
  ADD PRIMARY KEY (`id_pregunta`),
  ADD KEY `rut_usuario` (`rut_usuario`);

--
-- Indices de la tabla `foro_respuestas`
--
ALTER TABLE `foro_respuestas`
  ADD PRIMARY KEY (`id_respuesta`),
  ADD KEY `id_pregunta` (`id_pregunta`),
  ADD KEY `rut_usuario` (`rut_usuario`);

--
-- Indices de la tabla `imagenes_capacitaciones`
--
ALTER TABLE `imagenes_capacitaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `capacitacion_id` (`capacitacion_id`);

--
-- Indices de la tabla `imagenes_eventos`
--
ALTER TABLE `imagenes_eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `imagenes_eventos_ibfk_1` (`evento_id`);

--
-- Indices de la tabla `imagenes_respuesta`
--
ALTER TABLE `imagenes_respuesta`
  ADD PRIMARY KEY (`id_imagen`),
  ADD KEY `id_respuesta` (`id_respuesta`);

--
-- Indices de la tabla `opciones_encuesta`
--
ALTER TABLE `opciones_encuesta`
  ADD PRIMARY KEY (`id_opcion`),
  ADD KEY `id_pregunta` (`id_pregunta`);

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`rut`),
  ADD KEY `cargo_id` (`cargo_id`),
  ADD KEY `rol_id` (`rol_id`);

--
-- Indices de la tabla `preguntas_encuesta`
--
ALTER TABLE `preguntas_encuesta`
  ADD PRIMARY KEY (`id_pregunta`);

--
-- Indices de la tabla `respuestas_encuesta`
--
ALTER TABLE `respuestas_encuesta`
  ADD PRIMARY KEY (`id_respuesta`),
  ADD KEY `id_pregunta` (`id_pregunta`),
  ADD KEY `respuestas_encuesta_ibfk_2` (`rut_usuario`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `id_area` (`id_area`),
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `id_sub_servicio` (`id_sub_servicio`),
  ADD KEY `solicitudes_ibfk_1` (`rut`);

--
-- Indices de la tabla `soli_areas`
--
ALTER TABLE `soli_areas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `soli_categorias`
--
ALTER TABLE `soli_categorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_area` (`id_area`);

--
-- Indices de la tabla `soli_respuestas`
--
ALTER TABLE `soli_respuestas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rut_usuario` (`rut_usuario`),
  ADD KEY `soli_respuestas_ibfk_1` (`solicitud_id`);

--
-- Indices de la tabla `soli_servicios`
--
ALTER TABLE `soli_servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `soportes`
--
ALTER TABLE `soportes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rol_id` (`rol_id`),
  ADD KEY `soportes_ibfk_1` (`rut`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`rut`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `archivos`
--
ALTER TABLE `archivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `asistencias_eventos`
--
ALTER TABLE `asistencias_eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `asistencia_capacitaciones`
--
ALTER TABLE `asistencia_capacitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `capacitaciones`
--
ALTER TABLE `capacitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT de la tabla `empleado_mes`
--
ALTER TABLE `empleado_mes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT de la tabla `foro_preguntas`
--
ALTER TABLE `foro_preguntas`
  MODIFY `id_pregunta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `foro_respuestas`
--
ALTER TABLE `foro_respuestas`
  MODIFY `id_respuesta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `imagenes_capacitaciones`
--
ALTER TABLE `imagenes_capacitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `imagenes_eventos`
--
ALTER TABLE `imagenes_eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `imagenes_respuesta`
--
ALTER TABLE `imagenes_respuesta`
  MODIFY `id_imagen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `opciones_encuesta`
--
ALTER TABLE `opciones_encuesta`
  MODIFY `id_opcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `preguntas_encuesta`
--
ALTER TABLE `preguntas_encuesta`
  MODIFY `id_pregunta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `respuestas_encuesta`
--
ALTER TABLE `respuestas_encuesta`
  MODIFY `id_respuesta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `soli_areas`
--
ALTER TABLE `soli_areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `soli_categorias`
--
ALTER TABLE `soli_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `soli_respuestas`
--
ALTER TABLE `soli_respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `soli_servicios`
--
ALTER TABLE `soli_servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `soportes`
--
ALTER TABLE `soportes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencias_eventos`
--
ALTER TABLE `asistencias_eventos`
  ADD CONSTRAINT `asistencias_eventos_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `asistencias_eventos_ibfk_2` FOREIGN KEY (`rut_usuario`) REFERENCES `usuarios` (`rut`) ON DELETE CASCADE;

--
-- Filtros para la tabla `asistencia_capacitaciones`
--
ALTER TABLE `asistencia_capacitaciones`
  ADD CONSTRAINT `asistencia_capacitaciones_ibfk_1` FOREIGN KEY (`capacitacion_id`) REFERENCES `capacitaciones` (`id`),
  ADD CONSTRAINT `asistencia_capacitaciones_ibfk_2` FOREIGN KEY (`rut_usuario`) REFERENCES `usuarios` (`rut`) ON DELETE CASCADE;

--
-- Filtros para la tabla `empleado_mes`
--
ALTER TABLE `empleado_mes`
  ADD CONSTRAINT `empleado_mes_ibfk_1` FOREIGN KEY (`rut`) REFERENCES `personal` (`rut`) ON DELETE CASCADE;

--
-- Filtros para la tabla `foro_preguntas`
--
ALTER TABLE `foro_preguntas`
  ADD CONSTRAINT `foro_preguntas_ibfk_1` FOREIGN KEY (`rut_usuario`) REFERENCES `usuarios` (`rut`) ON DELETE CASCADE;

--
-- Filtros para la tabla `foro_respuestas`
--
ALTER TABLE `foro_respuestas`
  ADD CONSTRAINT `foro_respuestas_ibfk_1` FOREIGN KEY (`id_pregunta`) REFERENCES `foro_preguntas` (`id_pregunta`) ON DELETE CASCADE,
  ADD CONSTRAINT `foro_respuestas_ibfk_2` FOREIGN KEY (`rut_usuario`) REFERENCES `usuarios` (`rut`) ON DELETE CASCADE;

--
-- Filtros para la tabla `imagenes_capacitaciones`
--
ALTER TABLE `imagenes_capacitaciones`
  ADD CONSTRAINT `imagenes_capacitaciones_ibfk_1` FOREIGN KEY (`capacitacion_id`) REFERENCES `capacitaciones` (`id`);

--
-- Filtros para la tabla `imagenes_eventos`
--
ALTER TABLE `imagenes_eventos`
  ADD CONSTRAINT `imagenes_eventos_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `imagenes_respuesta`
--
ALTER TABLE `imagenes_respuesta`
  ADD CONSTRAINT `imagenes_respuesta_ibfk_1` FOREIGN KEY (`id_respuesta`) REFERENCES `foro_respuestas` (`id_respuesta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `opciones_encuesta`
--
ALTER TABLE `opciones_encuesta`
  ADD CONSTRAINT `opciones_encuesta_ibfk_1` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas_encuesta` (`id_pregunta`);

--
-- Filtros para la tabla `personal`
--
ALTER TABLE `personal`
  ADD CONSTRAINT `personal_ibfk_1` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`),
  ADD CONSTRAINT `personal_ibfk_2` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);

--
-- Filtros para la tabla `respuestas_encuesta`
--
ALTER TABLE `respuestas_encuesta`
  ADD CONSTRAINT `respuestas_encuesta_ibfk_1` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas_encuesta` (`id_pregunta`),
  ADD CONSTRAINT `respuestas_encuesta_ibfk_2` FOREIGN KEY (`rut_usuario`) REFERENCES `usuarios` (`rut`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD CONSTRAINT `solicitudes_ibfk_1` FOREIGN KEY (`rut`) REFERENCES `usuarios` (`rut`) ON DELETE CASCADE,
  ADD CONSTRAINT `solicitudes_ibfk_2` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `solicitudes_ibfk_3` FOREIGN KEY (`id_area`) REFERENCES `soli_areas` (`id`),
  ADD CONSTRAINT `solicitudes_ibfk_4` FOREIGN KEY (`id_categoria`) REFERENCES `soli_categorias` (`id`),
  ADD CONSTRAINT `solicitudes_ibfk_5` FOREIGN KEY (`id_sub_servicio`) REFERENCES `soli_servicios` (`id`);

--
-- Filtros para la tabla `soli_categorias`
--
ALTER TABLE `soli_categorias`
  ADD CONSTRAINT `soli_categorias_ibfk_1` FOREIGN KEY (`id_area`) REFERENCES `soli_areas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `soli_respuestas`
--
ALTER TABLE `soli_respuestas`
  ADD CONSTRAINT `soli_respuestas_ibfk_1` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `soli_respuestas_ibfk_2` FOREIGN KEY (`rut_usuario`) REFERENCES `usuarios` (`rut`);

--
-- Filtros para la tabla `soli_servicios`
--
ALTER TABLE `soli_servicios`
  ADD CONSTRAINT `soli_servicios_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `soli_categorias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `soportes`
--
ALTER TABLE `soportes`
  ADD CONSTRAINT `soportes_ibfk_1` FOREIGN KEY (`rut`) REFERENCES `personal` (`rut`) ON DELETE CASCADE,
  ADD CONSTRAINT `soportes_ibfk_2` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);

DELIMITER $$
--
-- Eventos
--
CREATE DEFINER=`root`@`localhost` EVENT `eliminar_solicitudes_solucionadas` ON SCHEDULE EVERY 1 WEEK STARTS '2024-11-02 23:59:00' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM soportes
    WHERE estado = 'Solucionado'
    AND fecha_solucionado IS NOT NULL
    AND DATE(fecha_solucionado) <= CURDATE()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
