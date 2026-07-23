-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-08-2025 a las 15:05:00
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
-- Base de datos: `cons_neiva`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacion_tramite`
--

CREATE TABLE `asignacion_tramite` (
  `asignacion_id_tramite` int(11) NOT NULL,
  `asignacion_cod_tramite` varchar(25) NOT NULL,
  `asignacion_cc_usuario` int(15) NOT NULL,
  `asignacion_nombre_usuario` varchar(40) NOT NULL,
  `asignacion_apellido_usuario` varchar(40) NOT NULL,
  `asignacion_rol_usuario` varchar(25) NOT NULL,
  `observacion_a_usuario_tramite` text NOT NULL,
  `asignacion_fecha_tramite` datetime NOT NULL,
  `creacion_tram_cc_usuario` int(15) DEFAULT NULL,
  `creacion_tram_nombre_usuario` varchar(40) DEFAULT NULL,
  `creacion_tram_apellido_usuario` varchar(40) DEFAULT NULL,
  `creacion_tram_rol_usuario` varchar(40) DEFAULT NULL,
  `fecha_limite` date DEFAULT NULL,
  `asignacion_estado_tramite` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asignacion_tramite`
--

INSERT INTO `asignacion_tramite` (`asignacion_id_tramite`, `asignacion_cod_tramite`, `asignacion_cc_usuario`, `asignacion_nombre_usuario`, `asignacion_apellido_usuario`, `asignacion_rol_usuario`, `observacion_a_usuario_tramite`, `asignacion_fecha_tramite`, `creacion_tram_cc_usuario`, `creacion_tram_nombre_usuario`, `creacion_tram_apellido_usuario`, `creacion_tram_rol_usuario`, `fecha_limite`, `asignacion_estado_tramite`) VALUES
(31, 'CAT-2025-07-00043', 694780, 'Procedencia Juridica', 'Catastro Neiva', 'procedencia_juridica', '', '2025-08-28 07:52:50', 12345890, 'Ventanilla Catastral ', 'Catastro Neiva', 'ventanilla_catastral', '2025-09-02', 'A TIEMPO'),
(32, 'CAT-2025-11-00040', 694780, 'Procedencia Juridica', 'Catastro Neiva', 'procedencia_juridica', '', '2025-08-28 07:53:14', 12345890, 'Ventanilla Catastral ', 'Catastro Neiva', 'ventanilla_catastral', '2025-09-02', 'A TIEMPO'),
(33, 'CAT-2025-07-00046', 694780, 'Procedencia Juridica', 'Catastro Neiva', 'procedencia_juridica', '', '2025-08-28 07:53:37', 12345890, 'Ventanilla Catastral ', 'Catastro Neiva', 'ventanilla_catastral', '2025-09-02', 'SUSPENSION'),
(34, 'CAT-2025-05-00042', 694780, 'Procedencia Juridica', 'Catastro Neiva', 'procedencia_juridica', '', '2025-08-28 07:59:33', 12345890, 'Ventanilla Catastral ', 'Catastro Neiva', 'ventanilla_catastral', '2025-09-02', 'A TIEMPO'),
(35, 'CAT-2025-03-00048', 694780, 'Procedencia Juridica', 'Catastro Neiva', 'procedencia_juridica', '', '2025-08-28 08:00:05', 12345890, 'Ventanilla Catastral ', 'Catastro Neiva', 'ventanilla_catastral', '2025-09-02', 'PRIORIDAD'),
(36, 'CAT-2025-03-00048', 1324560987, 'Coordinacion Tecnica', 'Catastro Neiva', 'coordinacion_tecnica', '', '2025-08-28 08:00:37', 694780, 'Procedencia Juridica', 'Catastro Neiva', 'procedencia_juridica', '2025-09-02', 'A TIEMPO'),
(37, 'CAT-2025-05-00042', 1324560987, 'Coordinacion Tecnica', 'Catastro Neiva', 'coordinacion_tecnica', '', '2025-08-28 08:01:30', 694780, 'Procedencia Juridica', 'Catastro Neiva', 'procedencia_juridica', '2025-09-02', 'SUSPENSION'),
(38, 'CAT-2025-07-00046', 1324560987, 'Coordinacion Tecnica', 'Catastro Neiva', 'coordinacion_tecnica', '', '2025-08-28 08:02:20', 694780, 'Procedencia Juridica', 'Catastro Neiva', 'procedencia_juridica', '2025-09-02', 'A TIEMPO'),
(39, 'CAT-2025-11-00040', 1324560987, 'Coordinacion Tecnica', 'Catastro Neiva', 'coordinacion_tecnica', '', '2025-08-28 08:03:32', 694780, 'Procedencia Juridica', 'Catastro Neiva', 'procedencia_juridica', '2025-09-02', 'A TIEMPO'),
(40, 'CAT-2025-11-00040', 657450010, 'Control Calidad', 'Catastro Neiva', 'control_calidad', '', '2025-08-28 08:04:44', 1324560987, 'Coordinacion Tecnica', 'Catastro Neiva', 'coordinacion_tecnica', '2025-09-02', 'A TIEMPO'),
(41, 'CAT-2025-07-00046', 657450010, 'Control Calidad', 'Catastro Neiva', 'control_calidad', '', '2025-08-28 08:05:08', 1324560987, 'Coordinacion Tecnica', 'Catastro Neiva', 'coordinacion_tecnica', '2025-09-02', 'A TIEMPO'),
(42, 'CAT-2025-07-00046', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '', '2025-08-28 08:07:15', 657450010, 'Control Calidad', 'Catastro Neiva', 'control_calidad', '2025-09-02', 'A TIEMPO'),
(43, 'CAT-2025-11-00040', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '', '2025-08-28 08:07:43', 657450010, 'Control Calidad', 'Catastro Neiva', 'control_calidad', '2025-09-02', 'A TIEMPO'),
(44, 'CAT-2025-07-00046', 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', '', '2025-08-28 08:13:16', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '2025-09-02', 'PRIORIDAD'),
(45, 'CAT-2025-11-00040', 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', '', '2025-08-28 08:13:41', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '2025-09-02', 'A TIEMPO'),
(46, 'CAT-2025-07-00043', 1324560987, 'Coordinacion Tecnica', 'Catastro Neiva', 'coordinacion_tecnica', '', '2025-08-28 10:46:41', 694780, 'Procedencia Juridica', 'Catastro Neiva', 'procedencia_juridica', '2025-09-02', 'PRIORIDAD'),
(47, 'CAT-2025-05-00042', 657450010, 'Control Calidad', 'Catastro Neiva', 'control_calidad', '', '2025-08-28 10:50:01', 1324560987, 'Coordinacion Tecnica', 'Catastro Neiva', 'coordinacion_tecnica', '2025-09-02', 'A TIEMPO'),
(48, 'CAT-2025-05-00042', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '', '2025-08-28 10:50:43', 657450010, 'Control Calidad', 'Catastro Neiva', 'control_calidad', '2025-09-02', 'PRIORIDAD'),
(49, 'CAT-2025-05-00042', 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', '', '2025-08-28 10:53:29', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '2025-09-02', 'PRIORIDAD'),
(50, 'CAT-2025-04-00047', 694780, 'Procedencia Juridica', 'Catastro Neiva', 'procedencia_juridica', '', '2025-08-28 11:49:02', 12345890, 'Ventanilla Catastral ', 'Catastro Neiva', 'ventanilla_catastral', '2025-09-02', 'A TIEMPO'),
(51, 'CAT-2025-04-00047', 1324560987, 'Coordinacion Tecnica', 'Catastro Neiva', 'coordinacion_tecnica', '', '2025-08-28 11:49:33', 694780, 'Procedencia Juridica', 'Catastro Neiva', 'procedencia_juridica', '2025-09-02', 'A TIEMPO'),
(52, 'CAT-2025-07-00043', 657450010, 'Control Calidad', 'Catastro Neiva', 'control_calidad', '', '2025-08-28 11:50:28', 1324560987, 'Coordinacion Tecnica', 'Catastro Neiva', 'coordinacion_tecnica', '2025-09-02', 'A TIEMPO'),
(53, 'CAT-2025-03-00048', 657450010, 'Control Calidad', 'Catastro Neiva', 'control_calidad', '', '2025-08-28 11:52:26', 1324560987, 'Coordinacion Tecnica', 'Catastro Neiva', 'coordinacion_tecnica', '2025-09-02', 'SUSPENSION'),
(54, 'CAT-2025-04-00047', 657450010, 'Control Calidad', 'Catastro Neiva', 'control_calidad', '', '2025-08-28 11:52:46', 1324560987, 'Coordinacion Tecnica', 'Catastro Neiva', 'coordinacion_tecnica', '2025-09-02', 'A TIEMPO'),
(55, 'CAT-2025-07-00043', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '', '2025-08-28 11:53:52', 657450010, 'Control Calidad', 'Catastro Neiva', 'control_calidad', '2025-09-02', 'PRIORIDAD'),
(56, 'CAT-2025-04-00047', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '', '2025-08-28 11:54:21', 657450010, 'Control Calidad', 'Catastro Neiva', 'control_calidad', '2025-09-02', 'A TIEMPO'),
(57, 'CAT-2025-03-00048', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '', '2025-08-28 11:54:38', 657450010, 'Control Calidad', 'Catastro Neiva', 'control_calidad', '2025-09-02', 'SUSPENSION'),
(58, 'CAT-2025-07-00043', 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', '', '2025-08-28 11:55:56', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '2025-09-02', 'A TIEMPO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `certificado_catastral`
--

CREATE TABLE `certificado_catastral` (
  `certificado_id` int(11) NOT NULL,
  `codigo_certificado` varchar(50) NOT NULL,
  `certificado_hora_creacion` datetime NOT NULL,
  `cert_tipo_documento` varchar(50) NOT NULL,
  `cert_num_cc_interesado` int(50) NOT NULL,
  `cert_primer_nombre_interesado` varchar(50) NOT NULL,
  `cert_segundo_nombre_interesado` varchar(50) NOT NULL,
  `cert_primer_apellido_interesado` varchar(50) NOT NULL,
  `cert_segundo_apellido_interesado` varchar(50) NOT NULL,
  `cert_numero_cel_interesado` varchar(50) NOT NULL,
  `cert_correo_electronico` varchar(50) NOT NULL,
  `cert_soporte_pago` longblob NOT NULL,
  `cert_medio_envio` varchar(50) NOT NULL,
  `cert_npn_predio` varchar(50) NOT NULL,
  `cert_fmi_predio` varchar(50) NOT NULL,
  `cert_anio_vigencia` text NOT NULL,
  `cert_avaluo_terreno_tramite` varchar(50) NOT NULL,
  `cert_direccion_predio` varchar(50) NOT NULL,
  `cert_dest_econ_predio` varchar(50) NOT NULL,
  `cert_area_terreno_predio` varchar(50) NOT NULL,
  `cert_area_construccion_predio` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `certificado_catastral`
--

INSERT INTO `certificado_catastral` (`certificado_id`, `codigo_certificado`, `certificado_hora_creacion`, `cert_tipo_documento`, `cert_num_cc_interesado`, `cert_primer_nombre_interesado`, `cert_segundo_nombre_interesado`, `cert_primer_apellido_interesado`, `cert_segundo_apellido_interesado`, `cert_numero_cel_interesado`, `cert_correo_electronico`, `cert_soporte_pago`, `cert_medio_envio`, `cert_npn_predio`, `cert_fmi_predio`, `cert_anio_vigencia`, `cert_avaluo_terreno_tramite`, `cert_direccion_predio`, `cert_dest_econ_predio`, `cert_area_terreno_predio`, `cert_area_construccion_predio`) VALUES
(1, 'CERT-2025-01', '2025-08-28 12:05:17', 'Cedula_Ciudadania', 1005691780, 'MIGUEL', 'ANGEL', 'CAÑON ', 'RIVERA', '3112306274', 'miguelriveraw14@gmail.com', 0x736f706f727465735f7061676f2f434552542d323032352d30312f56454e54414e494c4c41322e706466, 'Fisico,Correo', '410010001000000060003000000000', '220-414141', '2025-01-01', '54333000', 'LOTE LOS LAGOS', 'Agropecuario', '1333364', '195');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `certificado_propietarios`
--

CREATE TABLE `certificado_propietarios` (
  `id_certificado_propietario` int(11) NOT NULL,
  `prop_cod_certificado` varchar(50) NOT NULL,
  `npn_predio_certificado` varchar(50) NOT NULL,
  `nombres_propietario` varchar(70) NOT NULL,
  `tipo_doc_propietario` varchar(50) NOT NULL,
  `cc_num_propietario` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `certificado_propietarios`
--

INSERT INTO `certificado_propietarios` (`id_certificado_propietario`, `prop_cod_certificado`, `npn_predio_certificado`, `nombres_propietario`, `tipo_doc_propietario`, `cc_num_propietario`) VALUES
(1, 'CERT-2025-01', '410010001000000060003000000000', 'EDELMIRA DUSSAN PASCUAS', 'Cedula_Ciudadania', '000055177476'),
(2, 'CERT-2025-01', '410010001000000060003000000000', 'WILLIAM DUSSAN PASCUAS', 'Cedula_Ciudadania', '000012134575');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `devolucion_tramites`
--

CREATE TABLE `devolucion_tramites` (
  `id_devolucion` int(11) NOT NULL,
  `id_historial_asignacion` int(11) DEFAULT NULL,
  `id_historial_revision` int(11) DEFAULT NULL,
  `historial_cod_tramite` varchar(25) NOT NULL,
  `asignacion_cc_usuario` int(15) NOT NULL,
  `asignacion_nombre_usuario` varchar(40) NOT NULL,
  `asignacion_apellido_usuario` varchar(40) NOT NULL,
  `asignacion_rol_usuario` varchar(40) NOT NULL,
  `observacion_a_usuario_tramite` text DEFAULT NULL,
  `historial_fecha_tramite` datetime DEFAULT NULL,
  `creacion_tram_cc_usuario` int(15) NOT NULL,
  `creacion_tram_nombre_usuario` varchar(40) NOT NULL,
  `creacion_tram_apellido_usuario` varchar(40) NOT NULL,
  `creacion_tram_rol_usuario` varchar(40) NOT NULL,
  `rol_actual` varchar(100) NOT NULL,
  `cedula_sesion` int(15) NOT NULL,
  `nombre_sesion` varchar(40) NOT NULL,
  `apellido_sesion` varchar(40) NOT NULL,
  `fecha_limite` date DEFAULT NULL,
  `historial_estado_tramite` varchar(100) NOT NULL,
  `motivo_devolucion` varchar(10000) DEFAULT NULL,
  `documento_soporte` longblob DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `devolucion_tramites`
--

INSERT INTO `devolucion_tramites` (`id_devolucion`, `id_historial_asignacion`, `id_historial_revision`, `historial_cod_tramite`, `asignacion_cc_usuario`, `asignacion_nombre_usuario`, `asignacion_apellido_usuario`, `asignacion_rol_usuario`, `observacion_a_usuario_tramite`, `historial_fecha_tramite`, `creacion_tram_cc_usuario`, `creacion_tram_nombre_usuario`, `creacion_tram_apellido_usuario`, `creacion_tram_rol_usuario`, `rol_actual`, `cedula_sesion`, `nombre_sesion`, `apellido_sesion`, `fecha_limite`, `historial_estado_tramite`, `motivo_devolucion`, `documento_soporte`, `fecha_creacion`) VALUES
(13, NULL, NULL, 'CAT-2025-11-00040', 0, '', '', '', NULL, NULL, 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', 'control_calidad', 657450010, 'Control Calidad', 'Catastro Neiva', NULL, 'DEVUELTO', 'No fggdh', 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d31312d30303034302f7472616d697465735f6465766f6c7563696f6e2f636f6e736f6c69646163696f6e2f50525545424120434f4e54524f4c2043414c494441442e706466, '2025-08-28 09:58:55'),
(14, NULL, NULL, 'CAT-2025-07-00046', 0, '', '', '', NULL, NULL, 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', 'consolidacion', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', NULL, 'DEVUELTO', 'Este es ', 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034362f7472616d697465735f6465766f6c7563696f6e2f656469746f722f50525545424120434f4e534f4c49444143494f4e2e706466, '2025-08-28 10:05:13'),
(15, NULL, NULL, 'CAT-2025-07-00043', 0, '', '', '', NULL, NULL, 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', 'consolidacion', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', NULL, 'DEVUELTO', '11:59 am ', 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034332f7472616d697465735f6465766f6c7563696f6e2f656469746f722f50525545424120434f4e534f4c49444143494f4e2e706466, '2025-08-28 12:00:54'),
(16, NULL, NULL, 'CAT-2025-07-00043', 0, '', '', '', NULL, NULL, 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', 'consolidacion', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', NULL, 'DEVUELTO', 'FXHGJFKJ', 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034332f7472616d697465735f6465766f6c7563696f6e2f656469746f722f50525545424120434f4f5244494e414349c3934e205445434e4943412e706466, '2025-08-28 12:15:26'),
(17, NULL, NULL, 'CAT-2025-07-00046', 0, '', '', '', NULL, NULL, 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', 'consolidacion', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', NULL, 'DEVUELTO', 'hOLA FJSGLDHFGJHFDGBD', 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034362f7472616d697465735f6465766f6c7563696f6e2f656469746f722f50525545424120434f4f5244494e414349c3934e205445434e4943412e706466, '2025-08-28 14:31:25'),
(18, NULL, NULL, 'CAT-2025-07-00046', 0, '', '', '', NULL, NULL, 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', 'consolidacion', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', NULL, 'DEVUELTO', '', 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034362f7472616d697465735f6465766f6c7563696f6e2f656469746f722f50525545424120434f4e534f4c49444143494f4e2e706466, '2025-08-28 15:44:07'),
(19, NULL, NULL, 'CAT-2025-05-00042', 0, '', '', '', NULL, NULL, 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', 'consolidacion', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', NULL, 'DEVUELTO', 'para edicion devolucion', 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30352d30303034322f7472616d697465735f6465766f6c7563696f6e2f656469746f722f50525545424120434f4e534f4c49444143494f4e2e706466, '2025-08-28 16:05:01'),
(20, NULL, NULL, 'CAT-2025-07-00046', 0, '', '', '', NULL, NULL, 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', 'consolidacion', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', NULL, 'DEVUELTO', 'Para edicion 410', 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034362f7472616d697465735f6465766f6c7563696f6e2f656469746f722f50525545424120434f4e534f4c49444143494f4e2e706466, '2025-08-28 16:11:55'),
(21, NULL, NULL, 'CAT-2025-07-00043', 0, '', '', '', NULL, NULL, 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', 'consolidacion', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', NULL, 'DEVUELTO', 'edicion 412', 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034332f7472616d697465735f6465766f6c7563696f6e2f656469746f722f5052554542415f392e706466, '2025-08-28 16:13:10'),
(22, NULL, NULL, 'CAT-2025-05-00042', 0, '', '', '', NULL, NULL, 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', 'consolidacion', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', NULL, 'DEVUELTO', 'Edicion', 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30352d30303034322f7472616d697465735f6465766f6c7563696f6e2f656469746f722f50525545424120434f4e534f4c49444143494f4e2e706466, '2025-08-28 16:18:19'),
(23, NULL, NULL, 'CAT-2025-11-00040', 0, '', '', '', NULL, NULL, 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', 'consolidacion', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', NULL, 'DEVUELTO', 'Edicion', 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d31312d30303034302f7472616d697465735f6465766f6c7563696f6e2f656469746f722f50525545424120434f4e54524f4c2043414c494441442e706466, '2025-08-28 16:19:05'),
(24, NULL, NULL, 'CAT-2025-11-00040', 0, '', '', '', NULL, NULL, 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', 'consolidacion', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', NULL, 'DEVUELTO', 'Edicion 429', 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d31312d30303034302f7472616d697465735f6465766f6c7563696f6e2f656469746f722f50525545424120434f4e534f4c49444143494f4e2e706466, '2025-08-28 16:30:07'),
(25, NULL, NULL, 'CAT-2025-11-00040', 0, '', '', '', NULL, NULL, 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', 'consolidacion', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', NULL, 'DEVUELTO', '429', 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d31312d30303034302f7472616d697465735f6465766f6c7563696f6e2f656469746f722f50525545424120434f4e534f4c49444143494f4e2e706466, '2025-08-28 16:30:44'),
(26, NULL, NULL, 'CAT-2025-11-00040', 0, '', '', '', NULL, NULL, 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', 'consolidacion', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', NULL, 'DEVUELTO', '436', 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d31312d30303034302f7472616d697465735f6465766f6c7563696f6e2f656469746f722f5052554542415f31302e706466, '2025-08-28 16:37:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos_tram_asignacion`
--

CREATE TABLE `documentos_tram_asignacion` (
  `id_documento` int(11) NOT NULL,
  `cod_tramite` varchar(25) NOT NULL,
  `doc_cedula_usuario` int(15) NOT NULL,
  `nombre_doc1` longblob NOT NULL,
  `tipo_doc1` varchar(100) DEFAULT NULL,
  `nombre_doc2` longblob DEFAULT NULL,
  `tipo_doc2` varchar(100) DEFAULT NULL,
  `nombre_doc3` longblob DEFAULT NULL,
  `tipo_doc3` varchar(100) DEFAULT NULL,
  `nombre_doc4` longblob DEFAULT NULL,
  `tipo_doc4` varchar(100) DEFAULT NULL,
  `nombre_doc5` longblob DEFAULT NULL,
  `tipo_doc5` varchar(100) DEFAULT NULL,
  `doc_observaciones` text NOT NULL,
  `fecha_cargue_doc` datetime NOT NULL DEFAULT current_timestamp(),
  `documento_estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `documentos_tram_asignacion`
--

INSERT INTO `documentos_tram_asignacion` (`id_documento`, `cod_tramite`, `doc_cedula_usuario`, `nombre_doc1`, `tipo_doc1`, `nombre_doc2`, `tipo_doc2`, `nombre_doc3`, `tipo_doc3`, `nombre_doc4`, `tipo_doc4`, `nombre_doc5`, `tipo_doc5`, `doc_observaciones`, `fecha_cargue_doc`, `documento_estado`) VALUES
(22, 'CAT-2025-07-00043', 694780, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034332f76656e74616e696c6c615f63617461737472616c2f5052554542415f31302e706466, 'Plano_Predial', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 07:52:50', 1),
(23, 'CAT-2025-11-00040', 694780, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d31312d30303034302f76656e74616e696c6c615f63617461737472616c2f5052554542415f392e706466, 'Resolución', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 07:53:14', 1),
(24, 'CAT-2025-07-00046', 694780, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034362f76656e74616e696c6c615f63617461737472616c2f5052554542415f382e706466, 'Escritura_Publica', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 07:53:37', 1),
(25, 'CAT-2025-05-00042', 694780, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30352d30303034322f76656e74616e696c6c615f63617461737472616c2f5052554542415f362e706466, 'Informe_Edicion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 07:59:33', 1),
(26, 'CAT-2025-03-00048', 694780, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30332d30303034382f76656e74616e696c6c615f63617461737472616c2f5052554542415f342e706466, 'Plano_Predial', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 08:00:06', 1),
(27, 'CAT-2025-03-00048', 1324560987, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30332d30303034382f70726f636564656e6369615f6a757269646963612f5052554542415f332e706466, 'Plano_Predial', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 08:00:37', 1),
(28, 'CAT-2025-05-00042', 1324560987, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30352d30303034322f70726f636564656e6369615f6a757269646963612f5052554542415f322e706466, 'Resolución', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 08:01:30', 1),
(29, 'CAT-2025-07-00046', 1324560987, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034362f70726f636564656e6369615f6a757269646963612f5052554542415f322e706466, 'Manzana_Catastral', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 08:02:21', 1),
(30, 'CAT-2025-11-00040', 1324560987, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d31312d30303034302f70726f636564656e6369615f6a757269646963612f5052554542415f415349474e4143494f4e2e706466, 'Documento_Privado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 08:03:32', 1),
(31, 'CAT-2025-11-00040', 657450010, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d31312d30303034302f636f6f7264696e6163696f6e5f7465636e6963612f5052554542415f415349474e4143494f4e2e706466, 'Plano_Predial', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 08:04:44', 1),
(32, 'CAT-2025-07-00046', 657450010, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034362f636f6f7264696e6163696f6e5f7465636e6963612f5052554542415f434f4e534552564143494f4e2e706466, 'Manzana_Catastral', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 08:05:08', 1),
(33, 'CAT-2025-07-00046', 745016982, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034362f636f6e74726f6c5f63616c696461642f5052554542415f434f4e534552564143494f4e2e706466, 'Plano_Predial', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 08:07:15', 1),
(34, 'CAT-2025-11-00040', 745016982, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d31312d30303034302f636f6e74726f6c5f63616c696461642f5052554542415f434f4f5244494e4143494f4e2e706466, 'Resolución', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 08:07:43', 1),
(35, 'CAT-2025-07-00046', 104785236, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034362f636f6e736f6c69646163696f6e2f5052554542415f434f4f5244494e4143494f4e2e706466, 'Otro', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 08:13:17', 1),
(36, 'CAT-2025-11-00040', 104785236, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d31312d30303034302f636f6e736f6c69646163696f6e2f5052554542415f4a555249444943412e706466, 'Resolución', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 08:13:41', 1),
(37, 'CAT-2025-07-00043', 1324560987, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034332f70726f636564656e6369615f6a757269646963612f50525545424120434f4f5244494e414349c3934e205445434e4943412e706466, 'Informe_Edicion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 10:46:41', 1),
(38, 'CAT-2025-05-00042', 657450010, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30352d30303034322f636f6f7264696e6163696f6e5f7465636e6963612f50525545424120434f4f5244494e414349c3934e205445434e4943412e706466, 'Informe_Visita', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 10:50:02', 1),
(39, 'CAT-2025-05-00042', 745016982, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30352d30303034322f636f6e74726f6c5f63616c696461642f50525545424120434f4e534f4c49444143494f4e2e706466, 'Plano_Predial', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 10:50:43', 1),
(40, 'CAT-2025-05-00042', 104785236, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30352d30303034322f636f6e736f6c69646163696f6e2f50525545424120434f4e54524f4c2043414c494441442e706466, 'Plano_Predial', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 10:53:29', 1),
(41, 'CAT-2025-04-00047', 694780, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30342d30303034372f76656e74616e696c6c615f63617461737472616c2f50525545424120434f4e54524f4c2043414c494441442e706466, 'Manzana_Catastral', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 11:49:02', 1),
(42, 'CAT-2025-04-00047', 1324560987, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30342d30303034372f70726f636564656e6369615f6a757269646963612f5052554542415f31302e706466, 'Resolución', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 11:49:33', 1),
(43, 'CAT-2025-07-00043', 657450010, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034332f636f6f7264696e6163696f6e5f7465636e6963612f5052554542415f382e706466, 'Devolución_Tramite', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 11:50:28', 1),
(44, 'CAT-2025-03-00048', 657450010, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30332d30303034382f636f6f7264696e6163696f6e5f7465636e6963612f50525545424120434f4f5244494e414349c3934e205445434e4943412e706466, 'Impuesto_Predial', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 11:52:26', 1),
(45, 'CAT-2025-04-00047', 657450010, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30342d30303034372f636f6f7264696e6163696f6e5f7465636e6963612f5052554542415f382e706466, 'Plano_Predial', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 11:52:46', 1),
(46, 'CAT-2025-07-00043', 745016982, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034332f636f6e74726f6c5f63616c696461642f50525545424120434f4e54524f4c2043414c494441442e706466, 'Manzana_Catastral', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 11:53:52', 1),
(47, 'CAT-2025-04-00047', 745016982, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30342d30303034372f636f6e74726f6c5f63616c696461642f50525545424120434f4e534f4c49444143494f4e2e706466, 'Notificacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 11:54:21', 1),
(48, 'CAT-2025-03-00048', 745016982, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30332d30303034382f636f6e74726f6c5f63616c696461642f5052554542415f31302e706466, 'Plano_Predial', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 11:54:38', 1),
(49, 'CAT-2025-07-00043', 104785236, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034332f636f6e736f6c69646163696f6e2f5052554542415f382e706466, 'Resolución', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 11:55:56', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_entrega_asignacion`
--

CREATE TABLE `doc_entrega_asignacion` (
  `id_doc_entrega` int(11) NOT NULL,
  `cod_tramite` varchar(25) NOT NULL,
  `doc_cedula_usuario` int(15) NOT NULL,
  `asignacion_id_tramite` int(11) NOT NULL,
  `doc1` longblob DEFAULT NULL,
  `tipo_doc1` varchar(100) DEFAULT NULL,
  `doc2` longblob DEFAULT NULL,
  `tipo_doc2` varchar(100) DEFAULT NULL,
  `doc3` longblob DEFAULT NULL,
  `tipo_doc3` varchar(100) DEFAULT NULL,
  `doc4` longblob DEFAULT NULL,
  `tipo_doc4` varchar(100) DEFAULT NULL,
  `doc5` longblob DEFAULT NULL,
  `tipo_doc5` varchar(100) DEFAULT NULL,
  `doc_observaciones` text DEFAULT NULL,
  `fecha_cargue_doc` datetime NOT NULL DEFAULT current_timestamp(),
  `documento_estado` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `doc_entrega_asignacion`
--

INSERT INTO `doc_entrega_asignacion` (`id_doc_entrega`, `cod_tramite`, `doc_cedula_usuario`, `asignacion_id_tramite`, `doc1`, `tipo_doc1`, `doc2`, `tipo_doc2`, `doc3`, `tipo_doc3`, `doc4`, `tipo_doc4`, `doc5`, `tipo_doc5`, `doc_observaciones`, `fecha_cargue_doc`, `documento_estado`) VALUES
(14, 'CAT-2025-11-00040', 104785236, 45, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d31312d30303034302f7472616d697465735f7265766973696f6e2f656469746f722f5465206578706c6963616d6f7320746f646f20736f627265206c617320706c616e7461732e706466, 'Informe_Calidad', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 08:16:04', 'CARGADO'),
(15, 'CAT-2025-07-00046', 104785236, 44, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034362f7472616d697465735f7265766973696f6e2f656469746f722f5052554542415f52414449434143494f4e2e706466, 'Resolución', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ghfjgj', '2025-08-28 08:42:42', 'CARGADO'),
(16, 'CAT-2025-11-00040', 104785236, 45, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d31312d30303034302f7472616d697465735f7265766973696f6e2f656469746f722f50525545424120434f4e54524f4c2043414c494441442e706466, 'Notificacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 09:03:22', 'CARGADO'),
(17, 'CAT-2025-11-00040', 104785236, 45, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d31312d30303034302f7472616d697465735f7265766973696f6e2f636f6e736f6c69646163696f6e2f5052554542415f392e706466, 'Plano_Predial', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 09:04:53', 'CARGADO'),
(18, 'CAT-2025-07-00046', 104785236, 44, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034362f7472616d697465735f7265766973696f6e2f656469746f722f5052554542415f4156414c554f2e706466, 'Plano_Predial', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 10:03:44', 'CARGADO'),
(19, 'CAT-2025-05-00042', 104785236, 49, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30352d30303034322f7472616d697465735f7265766973696f6e2f656469746f722f50525545424120434f4f5244494e414349c3934e205445434e4943412e706466, 'Otro', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 10:54:39', 'CARGADO'),
(20, 'CAT-2025-05-00042', 104785236, 49, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30352d30303034322f7472616d697465735f7265766973696f6e2f636f6e736f6c69646163696f6e2f50525545424120434f4f5244494e414349c3934e205445434e4943412e706466, 'Informe_Visita', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 10:57:50', 'CARGADO'),
(21, 'CAT-2025-05-00042', 104785236, 49, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30352d30303034322f7472616d697465735f7265766973696f6e2f656469746f722f50525545424120434f4f5244494e414349c3934e205445434e4943412e706466, 'Informe_Visita', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 11:38:12', 'CARGADO'),
(22, 'CAT-2025-07-00043', 104785236, 58, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30372d30303034332f7472616d697465735f7265766973696f6e2f656469746f722f50525545424120434f4e534f4c49444143494f4e2e706466, 'Plano_Predial', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 11:57:50', 'CARGADO'),
(23, 'CAT-2025-05-00042', 104785236, 49, 0x2e2e2f7472616d697465735f636f6e736572766163696f6e2f323032352f4341542d323032352d30352d30303034322f7472616d697465735f7265766973696f6e2f636f6e736f6c69646163696f6e2f5052554542415f31302e706466, 'Notificacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '2025-08-28 15:54:32', 'CARGADO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entrega_asignacion`
--

CREATE TABLE `entrega_asignacion` (
  `id_entrega_asignacion` int(11) NOT NULL,
  `entrega_id_tramite` int(11) NOT NULL,
  `entrega_cod_tramite` varchar(25) NOT NULL,
  `entrega_cc_usuario` int(15) NOT NULL,
  `entrega_nombre_usuario` varchar(40) NOT NULL,
  `entrega_apellido_usuario` varchar(40) NOT NULL,
  `entrega_rol_usuario` varchar(25) NOT NULL,
  `observacion_a_usuario_tramite` text NOT NULL,
  `historial_fecha_tramite` datetime NOT NULL,
  `creacion_tram_cc_usuario` int(15) NOT NULL,
  `creacion_tram_nombre_usuario` varchar(40) NOT NULL,
  `creacion_tram_apellido_usuario` varchar(40) NOT NULL,
  `creacion_tram_rol_usuario` varchar(40) NOT NULL,
  `quien_entrego_cc` int(15) DEFAULT NULL,
  `quien_entrego_nombre` varchar(100) DEFAULT NULL,
  `quien_entrego_apellido` varchar(100) DEFAULT NULL,
  `quien_entrego_rol` varchar(100) DEFAULT NULL,
  `fecha_limite` date DEFAULT NULL,
  `historial_estado_tramite` varchar(100) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `entrega_asignacion`
--

INSERT INTO `entrega_asignacion` (`id_entrega_asignacion`, `entrega_id_tramite`, `entrega_cod_tramite`, `entrega_cc_usuario`, `entrega_nombre_usuario`, `entrega_apellido_usuario`, `entrega_rol_usuario`, `observacion_a_usuario_tramite`, `historial_fecha_tramite`, `creacion_tram_cc_usuario`, `creacion_tram_nombre_usuario`, `creacion_tram_apellido_usuario`, `creacion_tram_rol_usuario`, `quien_entrego_cc`, `quien_entrego_nombre`, `quien_entrego_apellido`, `quien_entrego_rol`, `fecha_limite`, `historial_estado_tramite`, `fecha_creacion`) VALUES
(51, 45, 'CAT-2025-11-00040', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '', '2025-08-20 15:09:03', 0, '', '', '', NULL, NULL, NULL, NULL, '2025-09-01', '', '2025-08-28 09:03:22'),
(52, 45, 'CAT-2025-11-00040', 657450010, 'Control Calidad', 'Catastro Neiva', 'control_calidad', '', '2025-08-20 15:09:03', 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', NULL, NULL, NULL, NULL, '2025-09-01', '', '2025-08-28 09:04:52'),
(53, 44, 'CAT-2025-07-00046', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '', '2025-08-22 08:03:37', 0, '', '', '', NULL, NULL, NULL, NULL, '2025-09-01', '', '2025-08-28 10:03:44'),
(54, 49, 'CAT-2025-05-00042', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '', '2025-08-21 08:21:28', 0, '', '', '', NULL, NULL, NULL, NULL, '2025-09-01', '', '2025-08-28 10:54:39'),
(55, 49, 'CAT-2025-05-00042', 657450010, 'Control Calidad', 'Catastro Neiva', 'control_calidad', '', '2025-08-21 08:21:28', 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', NULL, NULL, NULL, NULL, '2025-09-01', '', '2025-08-28 10:57:50'),
(56, 49, 'CAT-2025-05-00042', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '', '2025-08-21 08:21:28', 0, '', '', '', NULL, NULL, NULL, NULL, '2025-09-01', '', '2025-08-28 11:38:12'),
(57, 58, 'CAT-2025-07-00043', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '', '2025-08-21 08:23:53', 0, '', '', '', NULL, NULL, NULL, NULL, '2025-09-01', '', '2025-08-28 11:57:50'),
(58, 49, 'CAT-2025-05-00042', 657450010, 'Control Calidad', 'Catastro Neiva', 'control_calidad', '', '2025-08-21 08:21:28', 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', NULL, NULL, NULL, NULL, '2025-09-01', '', '2025-08-28 15:54:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_tramite`
--

CREATE TABLE `estados_tramite` (
  `id` int(11) NOT NULL,
  `es_nombre` varchar(100) NOT NULL,
  `es_tipo` varchar(50) DEFAULT NULL,
  `es_descripcion` text DEFAULT NULL,
  `es_dias_disparador` int(11) DEFAULT NULL,
  `es_disparador_evento` varchar(50) DEFAULT NULL,
  `es_rol_asociado` varchar(100) DEFAULT NULL,
  `estado` varchar(100) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `asignacion_id` int(11) NOT NULL,
  `cod_tramite` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_tramite`
--

INSERT INTO `estados_tramite` (`id`, `es_nombre`, `es_tipo`, `es_descripcion`, `es_dias_disparador`, `es_disparador_evento`, `es_rol_asociado`, `estado`, `creado_en`, `asignacion_id`, `cod_tramite`) VALUES
(39, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'procedencia_juridica', 'ACTIVO', '2025-08-28 12:52:50', 31, 'CAT-2025-07-00043'),
(40, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'procedencia_juridica', 'ACTIVO', '2025-08-28 12:53:14', 32, 'CAT-2025-11-00040'),
(41, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'procedencia_juridica', 'ACTIVO', '2025-08-28 12:53:37', 33, 'CAT-2025-07-00046'),
(42, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'procedencia_juridica', 'ACTIVO', '2025-08-28 12:59:33', 34, 'CAT-2025-05-00042'),
(43, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'procedencia_juridica', 'ACTIVO', '2025-08-28 13:00:06', 35, 'CAT-2025-03-00048'),
(44, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'coordinacion_tecnica', 'ACTIVO', '2025-08-28 13:00:37', 36, 'CAT-2025-03-00048'),
(45, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'coordinacion_tecnica', 'ACTIVO', '2025-08-28 13:01:30', 37, 'CAT-2025-05-00042'),
(46, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'coordinacion_tecnica', 'ACTIVO', '2025-08-28 13:02:20', 38, 'CAT-2025-07-00046'),
(47, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'coordinacion_tecnica', 'ACTIVO', '2025-08-28 13:03:32', 39, 'CAT-2025-11-00040'),
(48, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'control_calidad', 'ACTIVO', '2025-08-28 13:04:44', 40, 'CAT-2025-11-00040'),
(49, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'control_calidad', 'ACTIVO', '2025-08-28 13:05:08', 41, 'CAT-2025-07-00046'),
(50, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'consolidacion', 'ACTIVO', '2025-08-28 13:07:15', 42, 'CAT-2025-07-00046'),
(51, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'consolidacion', 'ACTIVO', '2025-08-28 13:07:43', 43, 'CAT-2025-11-00040'),
(52, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'editor', 'ACTIVO', '2025-08-28 13:13:17', 44, 'CAT-2025-07-00046'),
(53, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'editor', 'ACTIVO', '2025-08-28 13:13:41', 45, 'CAT-2025-11-00040'),
(54, 'REVISION', 'automatico', 'Trámite ', 5, NULL, 'procedencia_juridica', 'ACTIVO', '2025-08-28 13:16:04', 45, 'CAT-2025-11-00040'),
(55, 'REVISION', 'automatico', 'Trámite ', 5, NULL, 'procedencia_juridica', 'ACTIVO', '2025-08-28 13:42:42', 44, 'CAT-2025-07-00046'),
(56, 'REVISION', 'automatico', 'Trámite ', 5, NULL, 'procedencia_juridica', 'ACTIVO', '2025-08-28 14:03:22', 45, 'CAT-2025-11-00040'),
(57, 'REVISION', 'automatico', 'Trámite ', 5, NULL, 'procedencia_juridica', 'ACTIVO', '2025-08-28 14:04:52', 45, 'CAT-2025-11-00040'),
(58, 'DEVUELTO', 'automatico', 'Trámite devuelto por control_calidad', 5, NULL, 'consolidacion', 'ACTIVO', '2025-08-28 14:40:19', 45, 'CAT-2025-11-00040'),
(59, 'REVISION', 'automatico', 'Trámite ', 5, NULL, 'procedencia_juridica', 'ACTIVO', '2025-08-28 15:03:44', 44, 'CAT-2025-07-00046'),
(60, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'coordinacion_tecnica', 'ACTIVO', '2025-08-28 15:46:41', 46, 'CAT-2025-07-00043'),
(61, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'control_calidad', 'ACTIVO', '2025-08-28 15:50:02', 47, 'CAT-2025-05-00042'),
(62, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'consolidacion', 'ACTIVO', '2025-08-28 15:50:43', 48, 'CAT-2025-05-00042'),
(63, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'editor', 'ACTIVO', '2025-08-28 15:53:29', 49, 'CAT-2025-05-00042'),
(64, 'REVISION', 'automatico', 'Trámite ', 5, NULL, 'procedencia_juridica', 'ACTIVO', '2025-08-28 15:54:39', 49, 'CAT-2025-05-00042'),
(65, 'REVISION', 'automatico', 'Trámite ', 5, NULL, 'procedencia_juridica', 'ACTIVO', '2025-08-28 15:57:50', 49, 'CAT-2025-05-00042'),
(66, 'REVISION', 'automatico', 'Trámite ', 5, NULL, 'procedencia_juridica', 'ACTIVO', '2025-08-28 16:38:12', 49, 'CAT-2025-05-00042'),
(67, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'procedencia_juridica', 'ACTIVO', '2025-08-28 16:49:02', 50, 'CAT-2025-04-00047'),
(68, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'coordinacion_tecnica', 'ACTIVO', '2025-08-28 16:49:33', 51, 'CAT-2025-04-00047'),
(69, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'control_calidad', 'ACTIVO', '2025-08-28 16:50:28', 52, 'CAT-2025-07-00043'),
(70, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'control_calidad', 'ACTIVO', '2025-08-28 16:52:26', 53, 'CAT-2025-03-00048'),
(71, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'control_calidad', 'ACTIVO', '2025-08-28 16:52:46', 54, 'CAT-2025-04-00047'),
(72, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'consolidacion', 'ACTIVO', '2025-08-28 16:53:52', 55, 'CAT-2025-07-00043'),
(73, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'consolidacion', 'ACTIVO', '2025-08-28 16:54:21', 56, 'CAT-2025-04-00047'),
(74, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'consolidacion', 'ACTIVO', '2025-08-28 16:54:38', 57, 'CAT-2025-03-00048'),
(75, 'ASIGNADO', 'automatico', 'Trámite asignado automáticamente según rol', 5, NULL, 'editor', 'ACTIVO', '2025-08-28 16:55:56', 58, 'CAT-2025-07-00043'),
(76, 'REVISION', 'automatico', 'Trámite ', 5, NULL, 'procedencia_juridica', 'ACTIVO', '2025-08-28 16:57:50', 58, 'CAT-2025-07-00043'),
(77, 'REVISION', 'automatico', 'Trámite ', 5, NULL, 'procedencia_juridica', 'ACTIVO', '2025-08-28 20:54:32', 49, 'CAT-2025-05-00042');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_asignacion`
--

CREATE TABLE `historial_asignacion` (
  `id_historial_asignacion` int(11) NOT NULL,
  `historial_id_tramite` int(11) NOT NULL,
  `historial_cod_tramite` varchar(25) NOT NULL,
  `historial_cc_usuario` int(15) NOT NULL,
  `historial_nombre_usuario` varchar(40) NOT NULL,
  `historial_apellido_usuario` varchar(40) NOT NULL,
  `historial_rol_usuario` varchar(25) NOT NULL,
  `observacion_a_usuario_tramite` text NOT NULL,
  `historial_fecha_tramite` datetime NOT NULL,
  `creacion_tram_cc_usuario` int(15) NOT NULL,
  `creacion_tram_nombre_usuario` varchar(40) NOT NULL,
  `creacion_tram_apellido_usuario` varchar(40) NOT NULL,
  `creacion_tram_rol_usuario` varchar(40) NOT NULL,
  `fecha_limite` date DEFAULT NULL,
  `historial_estado_tramite` varchar(100) NOT NULL,
  `est_ventanilla` varchar(100) DEFAULT NULL,
  `fecha_ventanilla` date DEFAULT NULL,
  `est_procedencia` varchar(100) DEFAULT NULL,
  `fecha_procedencia` date DEFAULT NULL,
  `est_atencion_procedencia` varchar(100) DEFAULT NULL,
  `fecha_ate_procedencia` date DEFAULT NULL,
  `est_conservacion` varchar(100) DEFAULT NULL,
  `fecha_conservacion` date DEFAULT NULL,
  `est_lider_juridico` varchar(100) DEFAULT NULL,
  `fecha_lid_juridico` date DEFAULT NULL,
  `est_control_calidad` varchar(100) DEFAULT NULL,
  `fecha_cont_calidad` date DEFAULT NULL,
  `est_lider_economico` varchar(100) DEFAULT NULL,
  `fecha_lid_economico` date DEFAULT NULL,
  `est_consolidacion` varchar(100) DEFAULT NULL,
  `fecha_consolidacion` date DEFAULT NULL,
  `est_edicion` varchar(100) DEFAULT NULL,
  `fecha_edicion` date DEFAULT NULL,
  `est_avaluos` varchar(100) DEFAULT NULL,
  `fecha_avaluos` date DEFAULT NULL,
  `est_reconocimiento` varchar(100) DEFAULT NULL,
  `fecha_reconocimiento` date DEFAULT NULL,
  `est_director` varchar(100) DEFAULT NULL,
  `fecha_director` date DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_asignacion`
--

INSERT INTO `historial_asignacion` (`id_historial_asignacion`, `historial_id_tramite`, `historial_cod_tramite`, `historial_cc_usuario`, `historial_nombre_usuario`, `historial_apellido_usuario`, `historial_rol_usuario`, `observacion_a_usuario_tramite`, `historial_fecha_tramite`, `creacion_tram_cc_usuario`, `creacion_tram_nombre_usuario`, `creacion_tram_apellido_usuario`, `creacion_tram_rol_usuario`, `fecha_limite`, `historial_estado_tramite`, `est_ventanilla`, `fecha_ventanilla`, `est_procedencia`, `fecha_procedencia`, `est_atencion_procedencia`, `fecha_ate_procedencia`, `est_conservacion`, `fecha_conservacion`, `est_lider_juridico`, `fecha_lid_juridico`, `est_control_calidad`, `fecha_cont_calidad`, `est_lider_economico`, `fecha_lid_economico`, `est_consolidacion`, `fecha_consolidacion`, `est_edicion`, `fecha_edicion`, `est_avaluos`, `fecha_avaluos`, `est_reconocimiento`, `fecha_reconocimiento`, `est_director`, `fecha_director`, `fecha_creacion`) VALUES
(9, 31, 'CAT-2025-07-00043', 694780, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', '', '2025-08-28 07:52:50', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '2025-09-02', 'A TIEMPO', 'APROBADO', '2025-08-28', 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, '2025-08-28 07:52:50'),
(10, 32, 'CAT-2025-11-00040', 694780, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', '', '2025-08-28 07:53:14', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '2025-09-02', 'A TIEMPO', 'APROBADO', '2025-08-28', 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, '2025-08-28 07:53:15'),
(11, 33, 'CAT-2025-07-00046', 694780, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', '', '2025-08-28 07:53:37', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '2025-09-02', 'SUSPENSION', 'APROBADO', '2025-08-28', 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, '2025-08-28 07:53:37'),
(12, 34, 'CAT-2025-05-00042', 694780, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', '', '2025-08-28 07:59:33', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '2025-09-02', 'A TIEMPO', 'APROBADO', '2025-08-28', 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, '2025-08-28 07:59:33'),
(13, 35, 'CAT-2025-03-00048', 694780, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '', '2025-08-28 08:00:05', 657450010, 'Control Calidad', 'Catastro Neiva', 'control_calidad', '2025-09-02', 'PRIORIDAD', 'APROBADO', '2025-08-28', 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, '2025-08-28 08:00:06'),
(14, 50, 'CAT-2025-04-00047', 694780, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacion', '', '2025-08-28 11:49:02', 657450010, 'Control Calidad', 'Catastro Neiva', 'control_calidad', '2025-09-02', 'A TIEMPO', 'APROBADO', '2025-08-28', 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'APROBADO', '2025-08-28', 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, '2025-08-28 11:49:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_devolucion`
--

CREATE TABLE `historial_devolucion` (
  `id_devolucion` int(11) NOT NULL,
  `id_historial_asignacion` int(11) DEFAULT NULL,
  `entrega_id_tramite` int(11) NOT NULL,
  `historial_cod_tramite` varchar(25) NOT NULL,
  `observacion_a_usuario_tramite` text NOT NULL,
  `cedula_sesion` int(11) NOT NULL,
  `nombre_sesion` varchar(100) NOT NULL,
  `apellido_sesion` varchar(100) NOT NULL,
  `historial_fecha_tramite` datetime NOT NULL,
  `devolucion_tram_cc_usuario` int(11) NOT NULL,
  `devolucion_tram_nombre_usuario` varchar(40) NOT NULL,
  `devolucion_tram_apellido_usuario` varchar(40) NOT NULL,
  `devolucion_tram_rol_usuario` varchar(40) NOT NULL,
  `rol_actual` varchar(100) DEFAULT NULL,
  `fecha_limite` date DEFAULT NULL,
  `historial_estado_tramite` varchar(100) NOT NULL,
  `est_ventanilla` varchar(100) DEFAULT NULL,
  `fecha_ventanilla` date DEFAULT NULL,
  `est_procedencia` varchar(100) DEFAULT NULL,
  `fecha_procedencia` date DEFAULT NULL,
  `est_atencion_procedencia` varchar(100) DEFAULT NULL,
  `fecha_ate_procedencia` date DEFAULT NULL,
  `est_conservacion` varchar(100) DEFAULT NULL,
  `fecha_conservacion` date DEFAULT NULL,
  `est_lider_juridico` varchar(100) DEFAULT NULL,
  `fecha_lid_juridico` date DEFAULT NULL,
  `est_control_calidad` varchar(100) DEFAULT NULL,
  `fecha_cont_calidad` date DEFAULT NULL,
  `est_lider_economico` varchar(100) DEFAULT NULL,
  `fecha_lid_economico` date DEFAULT NULL,
  `est_consolidacion` varchar(100) DEFAULT NULL,
  `fecha_consolidacion` date DEFAULT NULL,
  `est_edicion` varchar(100) DEFAULT NULL,
  `fecha_edicion` date DEFAULT NULL,
  `est_avaluos` varchar(100) DEFAULT NULL,
  `fecha_avaluos` date DEFAULT NULL,
  `est_reconocimiento` varchar(100) DEFAULT NULL,
  `fecha_reconocimiento` date DEFAULT NULL,
  `est_director` varchar(100) DEFAULT NULL,
  `fecha_director` date DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_devolucion`
--

INSERT INTO `historial_devolucion` (`id_devolucion`, `id_historial_asignacion`, `entrega_id_tramite`, `historial_cod_tramite`, `observacion_a_usuario_tramite`, `cedula_sesion`, `nombre_sesion`, `apellido_sesion`, `historial_fecha_tramite`, `devolucion_tram_cc_usuario`, `devolucion_tram_nombre_usuario`, `devolucion_tram_apellido_usuario`, `devolucion_tram_rol_usuario`, `rol_actual`, `fecha_limite`, `historial_estado_tramite`, `est_ventanilla`, `fecha_ventanilla`, `est_procedencia`, `fecha_procedencia`, `est_atencion_procedencia`, `fecha_ate_procedencia`, `est_conservacion`, `fecha_conservacion`, `est_lider_juridico`, `fecha_lid_juridico`, `est_control_calidad`, `fecha_cont_calidad`, `est_lider_economico`, `fecha_lid_economico`, `est_consolidacion`, `fecha_consolidacion`, `est_edicion`, `fecha_edicion`, `est_avaluos`, `fecha_avaluos`, `est_reconocimiento`, `fecha_reconocimiento`, `est_director`, `fecha_director`, `fecha_creacion`) VALUES
(1, NULL, 44, 'CAT-2025-07-00046', '', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', '2025-08-22 08:03:37', 0, '', '', '', 'consolidacion', NULL, 'DEVUELTO', 'ASIGNADO', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'ENTREGADO', '2025-08-28', 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, '2025-08-28 15:44:07'),
(2, NULL, 49, 'CAT-2025-05-00042', 'para edicion devolucion', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', '2025-08-21 08:21:28', 0, '', '', '', 'consolidacion', NULL, 'DEVUELTO', 'ASIGNADO', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'ENTREGADO', '2025-08-28', 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, '2025-08-28 16:05:01'),
(3, NULL, 58, 'CAT-2025-07-00043', 'edicion 412', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', '2025-08-21 08:23:53', 0, '', '', '', 'consolidacion', NULL, 'DEVUELTO', 'ASIGNADO', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'DEVOLUCION', '2025-08-28', 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, '2025-08-28 16:13:11'),
(6, NULL, 45, 'CAT-2025-11-00040', '436', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', '2025-08-20 15:09:03', 0, '', '', '', 'consolidacion', NULL, 'DEVUELTO', 'ASIGNADO', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'DEVOLUCION', '2025-08-28', 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, '2025-08-28 16:37:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_revision`
--

CREATE TABLE `historial_revision` (
  `id_revision` int(11) NOT NULL,
  `id_historial_asignacion` int(11) DEFAULT NULL,
  `entrega_id_tramite` int(11) NOT NULL,
  `historial_cod_tramite` varchar(25) NOT NULL,
  `asignacion_cc_usuario` int(15) NOT NULL,
  `asignacion_nombre_usuario` varchar(40) NOT NULL,
  `asignacion_apellido_usuario` varchar(40) NOT NULL,
  `asignacion_rol_usuario` varchar(25) NOT NULL,
  `observacion_a_usuario_tramite` text NOT NULL,
  `historial_fecha_tramite` datetime NOT NULL,
  `creacion_tram_cc_usuario` int(15) NOT NULL,
  `creacion_tram_nombre_usuario` varchar(40) NOT NULL,
  `creacion_tram_apellido_usuario` varchar(40) NOT NULL,
  `creacion_tram_rol_usuario` varchar(40) NOT NULL,
  `rol_actual` varchar(100) DEFAULT NULL,
  `fecha_limite` date DEFAULT NULL,
  `historial_estado_tramite` varchar(100) NOT NULL,
  `est_ventanilla` varchar(100) NOT NULL,
  `fecha_ventanilla` date DEFAULT NULL,
  `est_procedencia` varchar(100) NOT NULL,
  `fecha_procedencia` date DEFAULT NULL,
  `est_atencion_procedencia` varchar(100) NOT NULL,
  `fecha_ate_procedencia` date DEFAULT NULL,
  `est_conservacion` varchar(100) NOT NULL,
  `fecha_conservacion` date DEFAULT NULL,
  `est_lider_juridico` varchar(100) NOT NULL,
  `fecha_lid_juridico` date DEFAULT NULL,
  `est_control_calidad` varchar(100) NOT NULL,
  `fecha_cont_calidad` date DEFAULT NULL,
  `est_lider_economico` varchar(100) NOT NULL,
  `fecha_lid_economico` date DEFAULT NULL,
  `est_consolidacion` varchar(100) NOT NULL,
  `fecha_consolidacion` date DEFAULT NULL,
  `est_edicion` varchar(100) NOT NULL,
  `fecha_edicion` date DEFAULT NULL,
  `est_avaluos` varchar(100) NOT NULL,
  `fecha_avaluos` date DEFAULT NULL,
  `est_reconocimiento` varchar(100) NOT NULL,
  `fecha_reconocimiento` date DEFAULT NULL,
  `est_director` varchar(100) NOT NULL,
  `fecha_director` date DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_revision`
--

INSERT INTO `historial_revision` (`id_revision`, `id_historial_asignacion`, `entrega_id_tramite`, `historial_cod_tramite`, `asignacion_cc_usuario`, `asignacion_nombre_usuario`, `asignacion_apellido_usuario`, `asignacion_rol_usuario`, `observacion_a_usuario_tramite`, `historial_fecha_tramite`, `creacion_tram_cc_usuario`, `creacion_tram_nombre_usuario`, `creacion_tram_apellido_usuario`, `creacion_tram_rol_usuario`, `rol_actual`, `fecha_limite`, `historial_estado_tramite`, `est_ventanilla`, `fecha_ventanilla`, `est_procedencia`, `fecha_procedencia`, `est_atencion_procedencia`, `fecha_ate_procedencia`, `est_conservacion`, `fecha_conservacion`, `est_lider_juridico`, `fecha_lid_juridico`, `est_control_calidad`, `fecha_cont_calidad`, `est_lider_economico`, `fecha_lid_economico`, `est_consolidacion`, `fecha_consolidacion`, `est_edicion`, `fecha_edicion`, `est_avaluos`, `fecha_avaluos`, `est_reconocimiento`, `fecha_reconocimiento`, `est_director`, `fecha_director`, `fecha_creacion`) VALUES
(6, 10, 51, 'CAT-2025-11-00040', 0, '', '', '', '', '2025-08-20 15:09:03', 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', 'consolidacion', NULL, 'REVISION', 'ASIGNADO', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'DEVUELTO', '2025-08-28', 'PENDIENTE', NULL, 'DEVUELTO', '2025-08-28', 'ENTREGADO', '2025-08-28', 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, '2025-08-28 09:03:22'),
(7, 11, 53, 'CAT-2025-07-00046', 0, '', '', '', '', '2025-08-22 08:03:37', 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', 'consolidacion', NULL, 'REVISION', 'ASIGNADO', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'DEVUELTO', '2025-08-28', 'ENTREGADO', '2025-08-28', 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, '2025-08-28 10:03:44'),
(8, 12, 54, 'CAT-2025-05-00042', 0, '', '', '', '', '2025-08-21 08:21:28', 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', 'consolidacion', NULL, 'REVISION', 'ASIGNADO', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'DEVUELTO', '2025-08-28', 'ENTREGADO', '2025-08-28', 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, '2025-08-28 10:54:40'),
(9, 9, 57, 'CAT-2025-07-00043', 0, '', '', '', '', '2025-08-21 08:23:53', 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'editor', 'consolidacion', NULL, 'REVISION', 'ASIGNADO', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'DEVUELTO', '2025-08-28', 'ENTREGADO', '2025-08-28', 'PENDIENTE', NULL, 'PENDIENTE', NULL, 'PENDIENTE', NULL, '2025-08-28 11:57:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tramite_info_predio`
--

CREATE TABLE `tramite_info_predio` (
  `id_info_predio` int(10) NOT NULL,
  `info_cod_tramite` varchar(25) NOT NULL,
  `id_tramite_rad` int(10) NOT NULL,
  `fmi_predio_tram` varchar(20) NOT NULL,
  `npn_predio_tram` varchar(40) NOT NULL,
  `nombre_propietario_tram` varchar(40) NOT NULL,
  `tipo_doc_propietario_tram` varchar(40) NOT NULL,
  `cedula_propietario_tram` varchar(40) NOT NULL,
  `valor_avaluo_terreno_tram` int(25) NOT NULL,
  `direccion_predio_terreno_tram` varchar(50) NOT NULL,
  `destino_econ_predio_tram` varchar(50) NOT NULL,
  `area_terr_predio_tram` int(15) NOT NULL,
  `area_cons_predio_tram` int(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tramite_info_predio`
--

INSERT INTO `tramite_info_predio` (`id_info_predio`, `info_cod_tramite`, `id_tramite_rad`, `fmi_predio_tram`, `npn_predio_tram`, `nombre_propietario_tram`, `tipo_doc_propietario_tram`, `cedula_propietario_tram`, `valor_avaluo_terreno_tram`, `direccion_predio_terreno_tram`, `destino_econ_predio_tram`, `area_terr_predio_tram`, `area_cons_predio_tram`) VALUES
(4, 'CAT-2025-01-00039', 39, '54112', '410010001000000010011000000000', 'CARLOS CABRERA VILLAMIL', 'Cedula_Ciudadania', '12100915', 4254000, 'POTRERO GUACIRCO LA COCHERA', 'Agropecuario', 4802, 0),
(5, 'CAT-2025-11-00040', 40, '5445', '410010001000000010018000000000', 'FANNY MORENO MEDINA', 'Cedula_Ciudadania', '55157835', 1308000, 'LA CANADA', 'Agropecuario', 78464, 0),
(6, 'CAT-2025-04-00041', 41, '86556', '410010001000000010009000000000', 'CARLOS CABRERA VILLAMIL', 'Cedula_Ciudadania', '12100915', 5955000, 'MANGON GUACIRCO', 'Agropecuario', 6722, 0),
(7, 'CAT-2025-05-00042', 42, '654874', '410010001000000010006000000000', 'LUIS AMADOR MORENO RAMOS', 'Sin_Informacion', '0', 12764000, 'FRAILE', 'Agropecuario', 14406, 0),
(8, 'CAT-2025-07-00043', 43, '77898', '410010001000000010004500000001', 'ALEIDY MENESES RAMIREZ', 'Cedula_Ciudadania', '36309926', 248000, 'Cs MEJORA', 'Habitacional', 0, 40),
(9, 'CAT-2025-02-00044', 44, '55639', '410010001000000010008000000000', 'CARLOS CABRERA VILLAMIL', 'Cedula_Ciudadania', '12100915', 16351000, 'LA BOMBA', 'Agropecuario', 18454, 0),
(10, 'CAT-2025-02-00045', 45, '04579', '410010001000000010010000000000', 'ALFREDO MOSQUERA GARZON', 'Cedula_Ciudadania', '1605985', 77519000, 'MANGA EL MOLINO', 'Agropecuario', 87500, 0),
(11, 'CAT-2025-07-00046', 46, '6852', '410010001000000010022000000000', 'CONSUELO FALLA SALAS', 'Cedula_Ciudadania', '36147571', 1752000, 'Cs LOTE', 'Agropecuario', 1978, 0),
(12, 'CAT-2025-04-00047', 47, '220-441074', '410010001000000140007000000000', 'OVIDIO ARIAS ROJAS', 'Cedula_Ciudadania', '4884756', 20957000, 'VENTILADOR', 'Agropecuario', 437500, 90),
(13, 'CAT-2025-03-00048', 48, '220-474111', '410010001000000150002000000000', 'SANTOS GUTIERREZ CALDERON', 'Cedula_Ciudadania', '4885337', 33802000, 'EL DESQUITE', 'Agropecuario', 794514, 104),
(14, 'CAT-2025-12-00049', 49, '1414141', '410010105000002460028000000000', 'JOSE FERNANDO RIVERA VELANDIA', 'Cedula_Ciudadania', '7716969', 52366000, 'C 1H 18 30 37', 'Habitacional', 165, 153),
(15, 'CAT-2025-04-00050', 50, '', '410010105000002460029000000000', 'MYRIAM SALAZAR VELANDIA', 'Cedula_Ciudadania', '36167361', 9488000, 'C 1H 18 27', 'Habitacional', 68, 30);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tramite_radicacion`
--

CREATE TABLE `tramite_radicacion` (
  `id_tramite` int(10) NOT NULL,
  `cod_tramite` varchar(25) NOT NULL,
  `fecha_rad` datetime NOT NULL,
  `documento_interesado` varchar(30) NOT NULL,
  `num_doc_interesado` int(30) NOT NULL,
  `primer_nombre_interesado` varchar(30) NOT NULL,
  `segundo_nombre_interesado` varchar(30) NOT NULL,
  `primer_apellido_interesado` varchar(30) NOT NULL,
  `segundo_apellido_interesado` varchar(30) NOT NULL,
  `telefono_interesado` varchar(25) NOT NULL,
  `correo_interesado` varchar(50) NOT NULL,
  `mutacion_tramite` varchar(35) NOT NULL,
  `tsolicitante_tramite` varchar(40) NOT NULL,
  `fmi_predio` varchar(30) NOT NULL,
  `npn_predio` varchar(40) NOT NULL,
  `sol_escrita_tramite` longblob NOT NULL,
  `cop_escritura_tramite` longblob NOT NULL,
  `ctl_tramite` longblob NOT NULL,
  `doc_identidad_tramite` longblob NOT NULL,
  `carta_autorizacion_tramite` longblob NOT NULL,
  `otros_doc_tramite` longblob NOT NULL,
  `observacion_tramite` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tramite_radicacion`
--

INSERT INTO `tramite_radicacion` (`id_tramite`, `cod_tramite`, `fecha_rad`, `documento_interesado`, `num_doc_interesado`, `primer_nombre_interesado`, `segundo_nombre_interesado`, `primer_apellido_interesado`, `segundo_apellido_interesado`, `telefono_interesado`, `correo_interesado`, `mutacion_tramite`, `tsolicitante_tramite`, `fmi_predio`, `npn_predio`, `sol_escrita_tramite`, `cop_escritura_tramite`, `ctl_tramite`, `doc_identidad_tramite`, `carta_autorizacion_tramite`, `otros_doc_tramite`, `observacion_tramite`) VALUES
(39, 'CAT-2025-01-00039', '2025-08-20 15:07:10', 'Cedula_Ciudadania', 1080290320, 'sofia', 'ashley', 'cerquera', 'ladino', '3214508730', 'sofiacerquera527@gmail.com', 'Mutacion_1', 'Propietario', '54112', '410010001000000010011000000000', 0x5052554542415f4156414c554f2e706466, 0x5052554542415f4a555249444943412e706466, 0x5052554542415f434f4e534552564143494f4e2e706466, 0x5052554542415f4a555249444943412e706466, 0x5465206578706c6963616d6f7320746f646f20736f627265206c617320706c616e7461732e706466, 0x5465206578706c6963616d6f7320746f646f20736f627265206c617320706c616e7461732e706466, ''),
(40, 'CAT-2025-11-00040', '2025-08-20 15:09:03', 'Cedula_Ciudadania', 55190519, 'MARCELA', 'LADINO', 'RAMIREZ', 'RAMIREZ', '3214508730', 'sofiacerquera527@gmail.com', 'Solicitud', 'Entidad_Publica', '5445', '410010001000000010018000000000', 0x5052554542415f415349474e4143494f4e2e706466, 0x4355454e544120444520434f42524f204345534152205641524741532e706466, 0x4355454e544120444520434f42524f2044454c203120616c203330206465206a756c696f20323032352e706466, 0x414454482d464f2d30383620494e464f524d45205041524349414c204f50532d444e505f4c415552412053414e444f56414c2e706466, 0x4375656e746120646520436f62726f204a756c696f202d20416c656a616e64726f2e706466, 0x52555420416c656a616e64726f2e706466, ''),
(41, 'CAT-2025-04-00041', '2025-08-21 08:06:36', 'Cedula_Ciudadania', 7786456, 'MARCELA', 'ashley', 'RAMIREZ', 'ladino', '3214508730', 'sofiacerquera527@gmail.com', 'Mutacion_4', 'Autorizado-Tercero', '86556', '410010001000000010009000000000', 0x5052554542415f362e706466, 0x5052554542415f372e706466, 0x5052554542415f382e706466, 0x5052554542415f392e706466, 0x5052554542415f31302e706466, 0x5052554542415f322e706466, ''),
(42, 'CAT-2025-05-00042', '2025-08-21 08:21:28', 'Cedula_Ciudadania', 83233648, 'Diogenes', 'Cerquera', 'Zuleta', 'Zuleta', '3214508730', 'sofiacerquera527@gmail.com', 'Mutacion_5', 'Poseedor', '654874', '410010001000000010006000000000', 0x5052554542415f312e706466, 0x5052554542415f322e706466, 0x5052554542415f332e706466, 0x5052554542415f342e706466, 0x5052554542415f352e706466, 0x5052554542415f362e706466, ''),
(43, 'CAT-2025-07-00043', '2025-08-21 08:23:53', 'Cedula_Ciudadania', 72356, 'MARCELA', 'LADINO', 'RAMIREZ', 'RAMIREZ', '3214508730', 'sofiacerquera527@gmail.com', 'Complementacion', 'Autorizado-Tercero', '77898', '410010001000000010004500000001', 0x5052554542415f332e706466, 0x5052554542415f322e706466, 0x5052554542415f312e706466, 0x5052554542415f342e706466, 0x5052554542415f352e706466, 0x5052554542415f362e706466, ''),
(44, 'CAT-2025-02-00044', '2025-08-21 08:26:48', 'Cedula_Ciudadania', 87413, 'laura', 'sofia', 'rojas', 'gomez', '3214508730', 'laura@gmail.com', 'Mutacion_2', 'Entidad_Publica', '55639', '410010001000000010008000000000', 0x5052554542415f415349474e4143494f4e2e706466, 0x5052554542415f434f4e534552564143494f4e2e706466, 0x5052554542415f434f4f5244494e4143494f4e2e706466, 0x5052554542415f4a555249444943412e706466, 0x5052554542415f312e706466, 0x5052554542415f322e706466, ''),
(45, 'CAT-2025-02-00045', '2025-08-21 08:38:30', 'Cedula_Ciudadania', 714243, 'laura', 'Cerquera', 'RAMIREZ', 'RAMIREZ', '3214508730', 'laura@gmail.com', 'Mutacion_2', 'Propietario', '04579', '410010001000000010010000000000', 0x5052554542415f31302e706466, 0x5052554542415f392e706466, 0x5052554542415f382e706466, 0x5052554542415f372e706466, 0x5052554542415f362e706466, 0x5052554542415f352e706466, ''),
(46, 'CAT-2025-07-00046', '2025-08-22 08:03:37', 'Cedula_Ciudadania', 55190519, 'Miguel', 'Angel', 'Cañin', 'Rivera', '3214508730', 'miguel@gmail.com', 'Complementacion', 'Propietario', '6852', '410010001000000010022000000000', 0x5465206578706c6963616d6f7320746f646f20736f627265206c617320706c616e7461732e706466, 0x5052554542415f4156414c554f2e706466, 0x5052554542415f52414449434143494f4e2e706466, 0x5052554542415f4a555249444943412e706466, 0x5052554542415f434f4f5244494e4143494f4e2e706466, 0x5052554542415f434f4e534552564143494f4e2e706466, ''),
(47, 'CAT-2025-04-00047', '2025-08-27 07:53:00', 'Cedula_Ciudadania', 1005691780, 'MIGUEL ', 'ANGEL', 'CAÑON', 'RIVERA', '3112306274', 'miguelriveraw14@gmail.com', 'Mutacion_4', 'Propietario', '220-441074', '410010001000000140007000000000', 0x56454e54414e494c4c41322e706466, 0x56454e54414e494c4c41322e706466, 0x504f4c5f54524154414d49454e544f5f504552534f4e2e706466, 0x5343414e313137372e706466, 0x5343414e313137372e706466, 0x56454e54414e494c4c41322e706466, 'SOLICITUD DE TRAMITE DE DESENGLOBE'),
(48, 'CAT-2025-03-00048', '2025-08-27 08:04:19', 'Cedula_Ciudadania', 1005691780, 'MIGUEL', 'ANGEL', 'CAÑON', 'RIVERA', '3112306274', 'MIGUELRIVERAW14@GMAIL.COM', 'Mutacion_3', 'Propietario', '220-474111', '410010001000000150002000000000', 0x56454e54414e494c4c41322e706466, 0x5343414e313137372e706466, 0x312d36373030373836363530383034395f333631323235303030303939363631302e706466, 0x434f4e5354414e4349415f52414449434143494f4e2e706466, 0x434f4e5354414e4349415f52414449434143494f4e2e706466, 0x5343414e313137372e706466, ''),
(49, 'CAT-2025-12-00049', '2025-08-28 16:08:10', 'Cedula_Ciudadania', 1005691780, 'miguel', 'prueba', 'mapa', 'vistor', '3112306274', 'miguelriveraw14@gmail.com', 'Otro', 'Propietario', '1414141', '410010105000002460028000000000', 0x504f4c5f54524154414d49454e544f5f504552534f4e2e706466, 0x504f4c5f54524154414d49454e544f5f504552534f4e2e706466, 0x504f4c5f54524154414d49454e544f5f504552534f4e2e706466, 0x504f4c5f54524154414d49454e544f5f504552534f4e2e706466, 0x504f4c5f54524154414d49454e544f5f504552534f4e2e706466, 0x504f4c5f54524154414d49454e544f5f504552534f4e2e706466, 'prueba visor'),
(50, 'CAT-2025-04-00050', '2025-08-28 16:27:50', 'Cedula_Ciudadania', 11, 'juan ', 'gabriel', 'garcia', 'garcia', '3100000', 'juan@gmail.com', 'Mutacion_4', 'Autorizado-Tercero', '', '410010105000002460029000000000', 0x5343414e313137372e706466, 0x5343414e313137372e706466, 0x56454e54414e494c4c41322e706466, 0x504f4c5f54524154414d49454e544f5f504552534f4e2e706466, 0x5343414e313137372e706466, 0x56454e54414e494c4c41322e706466, 'pruebvas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_cons`
--

CREATE TABLE `usuarios_cons` (
  `id_usuario` int(11) NOT NULL,
  `usuario_cons` varchar(40) NOT NULL,
  `password_cons` varchar(30) NOT NULL,
  `cedula_usuario` int(15) NOT NULL,
  `nombre_usuario` varchar(40) NOT NULL,
  `apellido_usuario` varchar(40) NOT NULL,
  `correo_usuario` varchar(40) NOT NULL,
  `celular_usuario` varchar(40) NOT NULL,
  `rol_usuario` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios_cons`
--

INSERT INTO `usuarios_cons` (`id_usuario`, `usuario_cons`, `password_cons`, `cedula_usuario`, `nombre_usuario`, `apellido_usuario`, `correo_usuario`, `celular_usuario`, `rol_usuario`) VALUES
(1, 'Miguel_admin', '2025Neiva', 1005691780, 'Miguel Angel', 'Rivera', 'auxtecnico.arbitrium@gmail.com', '3112306274', 'administrador'),
(2, 'Director_Catastro', 'catastroDIREC25**', 740852036, 'Director Catastro', 'Catastro Neiva', 'directorcatastroneiva@gmail.com', '3106737702', 'director_catastro'),
(3, 'Ventanilla_Catastral', 'VenCatastro2025', 12345890, 'Ventanilla Catastral ', 'Catastro Neiva', 'ventanillacatastro.arbitrium@gmail.com', '3106737702', 'ventanilla_catastral'),
(4, 'Procedencia_Juridica', 'ProcedenciaJuridicaARB', 694780, 'Procedencia Juridica', 'Catastro Neiva', 'procedenciacatastro.arbitrium@gmail.com', '3106737702', 'procedencia_juridica'),
(5, 'Juridico_Catastro', 'JuridicoCatastroARB', 654321789, 'Juridico Catastro', 'Catastro Neiva', 'juridicocatastro.arbitrium@gmail.com', '3106737702', 'revision_juridica'),
(6, 'Coordinacion_Tecnica', 'CoorTecnicaARB', 1324560987, 'Coordinacion Tecnica', 'Catastro Neiva', 'coordinaciontecnica.arbitrium@gmail.com', '3106737702', 'coordinacion_tecnica'),
(7, 'Control_Calidad', 'ControlCALD25', 657450010, 'Control Calidad', 'Catastro Neiva', 'controlcalidad.arbitrium@gmail.com', '3106737702', 'control_calidad'),
(8, 'Consolidacion_Catastro', 'ConSOLCAT25*', 745016982, 'Consolidacion Catastro', 'Neiva Arbitrium', 'consolidacioncatastro.arbitrium@gmail.co', '3106737702', 'consolidacion'),
(11, 'Edicion_Catastro', 'EDICIONARB2555', 104785236, 'Edicion Catastro', 'Arbitrium Neiva', 'edicionneiva.arbitrium@gmail.com', '3106737702', 'editor'),
(12, 'Reconocedor_Predial', 'reconCATASTRO25', 715141001, 'Reconocedor Predial', 'Catastro Neiva', 'reconpredial.arbitrium@gmail.com', '3106737702', 'reconocedor'),
(13, 'Componente_Economico', 'COMPEcon2025', 1309241123, 'Componente Economico', ' Arbitrium Neiva', 'compeconomico.arbitrium@gmail.com', '3106737702', 'componente_economico'),
(14, 'Avaluos_Catastro', 'AVALCatas25**', 74512404, 'Avaluo Catastro', 'Neiva Arbitrium', 'avaluoscatastroneiva@gmail.com', '3106737702', 'avaluos'),
(15, 'Atemcion_Procedencia', 'ProcedenciaarbITRIUM', 951753846, 'Atencion Procedencia', 'Neiva Arbitrium', 'atencionprocedencia.arbitrium@gmail.com', '3106737702', 'atencion_procedencia');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignacion_tramite`
--
ALTER TABLE `asignacion_tramite`
  ADD PRIMARY KEY (`asignacion_id_tramite`),
  ADD KEY `asignacion_cod_tramite` (`asignacion_cod_tramite`),
  ADD KEY `asignacion_cc_usuario` (`asignacion_cc_usuario`);

--
-- Indices de la tabla `certificado_catastral`
--
ALTER TABLE `certificado_catastral`
  ADD PRIMARY KEY (`certificado_id`),
  ADD KEY `codigo_certificado` (`codigo_certificado`,`cert_npn_predio`);

--
-- Indices de la tabla `certificado_propietarios`
--
ALTER TABLE `certificado_propietarios`
  ADD PRIMARY KEY (`id_certificado_propietario`),
  ADD KEY `prop_cod_certificado` (`prop_cod_certificado`,`npn_predio_certificado`);

--
-- Indices de la tabla `devolucion_tramites`
--
ALTER TABLE `devolucion_tramites`
  ADD PRIMARY KEY (`id_devolucion`);

--
-- Indices de la tabla `documentos_tram_asignacion`
--
ALTER TABLE `documentos_tram_asignacion`
  ADD PRIMARY KEY (`id_documento`),
  ADD KEY `doc_cedula_usuario` (`doc_cedula_usuario`),
  ADD KEY `cod_tramite` (`cod_tramite`);

--
-- Indices de la tabla `doc_entrega_asignacion`
--
ALTER TABLE `doc_entrega_asignacion`
  ADD PRIMARY KEY (`id_doc_entrega`),
  ADD KEY `fk_doc_asignacion` (`cod_tramite`),
  ADD KEY `fk_doc_usuario` (`doc_cedula_usuario`);

--
-- Indices de la tabla `entrega_asignacion`
--
ALTER TABLE `entrega_asignacion`
  ADD PRIMARY KEY (`id_entrega_asignacion`),
  ADD KEY `fk_entrega_tramite` (`entrega_id_tramite`);

--
-- Indices de la tabla `estados_tramite`
--
ALTER TABLE `estados_tramite`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_asignacion_tramite` (`asignacion_id`);

--
-- Indices de la tabla `historial_asignacion`
--
ALTER TABLE `historial_asignacion`
  ADD PRIMARY KEY (`id_historial_asignacion`),
  ADD KEY `fk_historial_asignacion_tramite` (`historial_id_tramite`);

--
-- Indices de la tabla `historial_devolucion`
--
ALTER TABLE `historial_devolucion`
  ADD PRIMARY KEY (`id_devolucion`),
  ADD KEY `idx_entrega_id_tramite` (`entrega_id_tramite`),
  ADD KEY `idx_historial_cod_tramite` (`historial_cod_tramite`);

--
-- Indices de la tabla `historial_revision`
--
ALTER TABLE `historial_revision`
  ADD PRIMARY KEY (`id_revision`),
  ADD KEY `fk_entrega_asignacion` (`entrega_id_tramite`);

--
-- Indices de la tabla `tramite_info_predio`
--
ALTER TABLE `tramite_info_predio`
  ADD PRIMARY KEY (`id_info_predio`),
  ADD KEY `id_tramite_rad` (`id_tramite_rad`),
  ADD KEY `npn_predio_tram` (`npn_predio_tram`),
  ADD KEY `info_cod_tramite` (`info_cod_tramite`);

--
-- Indices de la tabla `tramite_radicacion`
--
ALTER TABLE `tramite_radicacion`
  ADD PRIMARY KEY (`id_tramite`),
  ADD KEY `npn_predio` (`npn_predio`),
  ADD KEY `cod_tramite` (`cod_tramite`);

--
-- Indices de la tabla `usuarios_cons`
--
ALTER TABLE `usuarios_cons`
  ADD PRIMARY KEY (`id_usuario`),
  ADD KEY `cedula_usuario` (`cedula_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignacion_tramite`
--
ALTER TABLE `asignacion_tramite`
  MODIFY `asignacion_id_tramite` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de la tabla `certificado_catastral`
--
ALTER TABLE `certificado_catastral`
  MODIFY `certificado_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `certificado_propietarios`
--
ALTER TABLE `certificado_propietarios`
  MODIFY `id_certificado_propietario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `devolucion_tramites`
--
ALTER TABLE `devolucion_tramites`
  MODIFY `id_devolucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `documentos_tram_asignacion`
--
ALTER TABLE `documentos_tram_asignacion`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `doc_entrega_asignacion`
--
ALTER TABLE `doc_entrega_asignacion`
  MODIFY `id_doc_entrega` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `entrega_asignacion`
--
ALTER TABLE `entrega_asignacion`
  MODIFY `id_entrega_asignacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de la tabla `estados_tramite`
--
ALTER TABLE `estados_tramite`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT de la tabla `historial_asignacion`
--
ALTER TABLE `historial_asignacion`
  MODIFY `id_historial_asignacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `historial_devolucion`
--
ALTER TABLE `historial_devolucion`
  MODIFY `id_devolucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `historial_revision`
--
ALTER TABLE `historial_revision`
  MODIFY `id_revision` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `tramite_info_predio`
--
ALTER TABLE `tramite_info_predio`
  MODIFY `id_info_predio` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `tramite_radicacion`
--
ALTER TABLE `tramite_radicacion`
  MODIFY `id_tramite` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `usuarios_cons`
--
ALTER TABLE `usuarios_cons`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignacion_tramite`
--
ALTER TABLE `asignacion_tramite`
  ADD CONSTRAINT `asignacion_tramite_ibfk_1` FOREIGN KEY (`asignacion_cod_tramite`) REFERENCES `tramite_radicacion` (`cod_tramite`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asignacion_tramite_ibfk_2` FOREIGN KEY (`asignacion_cc_usuario`) REFERENCES `usuarios_cons` (`cedula_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tramite_asignado` FOREIGN KEY (`asignacion_cod_tramite`) REFERENCES `tramite_radicacion` (`cod_tramite`);

--
-- Filtros para la tabla `certificado_propietarios`
--
ALTER TABLE `certificado_propietarios`
  ADD CONSTRAINT `certificado_propietarios_ibfk_1` FOREIGN KEY (`prop_cod_certificado`) REFERENCES `certificado_catastral` (`codigo_certificado`);

--
-- Filtros para la tabla `devolucion_tramites`
--
ALTER TABLE `devolucion_tramites`
  ADD CONSTRAINT `fk_devolucivon_tramite` FOREIGN KEY (`id_historial_asignacion`) REFERENCES `historial_asignacion` (`id_historial_asignacion`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `documentos_tram_asignacion`
--
ALTER TABLE `documentos_tram_asignacion`
  ADD CONSTRAINT `documentos_tram_asignacion_ibfk_1` FOREIGN KEY (`doc_cedula_usuario`) REFERENCES `usuarios_cons` (`cedula_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `documentos_tram_asignacion_ibfk_2` FOREIGN KEY (`cod_tramite`) REFERENCES `tramite_radicacion` (`cod_tramite`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `doc_entrega_asignacion`
--
ALTER TABLE `doc_entrega_asignacion`
  ADD CONSTRAINT `fk_doc_asignacion` FOREIGN KEY (`cod_tramite`) REFERENCES `asignacion_tramite` (`asignacion_cod_tramite`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_doc_usuario` FOREIGN KEY (`doc_cedula_usuario`) REFERENCES `usuarios_cons` (`cedula_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `entrega_asignacion`
--
ALTER TABLE `entrega_asignacion`
  ADD CONSTRAINT `fk_entrega_tramite` FOREIGN KEY (`entrega_id_tramite`) REFERENCES `asignacion_tramite` (`asignacion_id_tramite`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `estados_tramite`
--
ALTER TABLE `estados_tramite`
  ADD CONSTRAINT `fk_asignacion_tramite` FOREIGN KEY (`asignacion_id`) REFERENCES `asignacion_tramite` (`asignacion_id_tramite`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `historial_asignacion`
--
ALTER TABLE `historial_asignacion`
  ADD CONSTRAINT `fk_historial_asignacion_tramite` FOREIGN KEY (`historial_id_tramite`) REFERENCES `asignacion_tramite` (`asignacion_id_tramite`);

--
-- Filtros para la tabla `historial_revision`
--
ALTER TABLE `historial_revision`
  ADD CONSTRAINT `fk_entrega_asignacion` FOREIGN KEY (`entrega_id_tramite`) REFERENCES `entrega_asignacion` (`id_entrega_asignacion`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tramite_info_predio`
--
ALTER TABLE `tramite_info_predio`
  ADD CONSTRAINT `fk_tramite` FOREIGN KEY (`id_tramite_rad`) REFERENCES `tramite_radicacion` (`id_tramite`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tramite_info_predio_ibfk_1` FOREIGN KEY (`id_tramite_rad`) REFERENCES `tramite_radicacion` (`id_tramite`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tramite_info_predio_ibfk_2` FOREIGN KEY (`npn_predio_tram`) REFERENCES `tramite_radicacion` (`npn_predio`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
