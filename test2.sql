-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-07-2025 a las 04:28:58
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
-- Base de datos: `test2`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `action`
--

CREATE TABLE `action` (
  `action_id` int(10) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `action`
--

INSERT INTO `action` (`action_id`, `description`) VALUES
(1, 'Se creo un usuario'),
(2, 'Se elimino un usuario'),
(3, 'Se editaron los datos a un usuario'),
(4, 'Se activo un usuario'),
(5, 'Se desactivo un usuario'),
(6, 'Se activo un material\r\n'),
(7, 'Se desactivo un material\r\n'),
(8, 'Se actualizo un material'),
(9, 'Entro un nuevo material'),
(10, 'Salio un material'),
(11, 'Se elimino un movimiento'),
(12, 'Se edito un movimiento '),
(13, 'Se agrego un material'),
(14, 'Se elimino un material');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `binnacle`
--

CREATE TABLE `binnacle` (
  `binnacle_id` int(20) NOT NULL,
  `time` datetime NOT NULL DEFAULT current_timestamp(),
  `action` int(10) NOT NULL,
  `user` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `binnacle`
--

INSERT INTO `binnacle` (`binnacle_id`, `time`, `action`, `user`) VALUES
(2, '2025-04-19 07:22:19', 1, 2),
(6, '2025-04-27 03:27:05', 3, 3),
(7, '2025-04-27 03:30:12', 2, 3),
(8, '2025-04-27 03:32:22', 2, 3),
(9, '2025-04-27 03:32:32', 2, 3),
(10, '2025-04-27 03:39:40', 3, 3),
(14, '2025-04-27 03:44:43', 5, 3),
(15, '2025-04-27 03:44:47', 4, 3),
(16, '2025-04-27 03:45:24', 5, 3),
(17, '2025-04-27 03:45:28', 5, 3),
(18, '2025-04-27 03:45:30', 4, 3),
(19, '2025-04-27 03:45:31', 5, 3),
(20, '2025-04-27 03:45:32', 4, 3),
(21, '2025-04-27 03:45:33', 4, 3),
(22, '2025-04-27 03:45:34', 4, 3),
(23, '2025-04-27 03:45:35', 4, 3),
(24, '2025-04-27 03:45:36', 4, 3),
(26, '2025-04-27 03:54:12', 1, 3),
(27, '2025-04-27 03:54:21', 2, 3),
(29, '2025-04-27 04:34:12', 4, 3),
(30, '2025-04-27 04:34:18', 5, 3),
(31, '2025-04-27 04:34:37', 5, 3),
(32, '2025-04-27 04:34:42', 4, 3),
(33, '2025-04-27 04:37:18', 4, 3),
(34, '2025-04-27 04:37:42', 5, 3),
(35, '2025-04-27 04:47:38', 1, 3),
(36, '2025-04-27 04:48:47', 3, 3),
(37, '2025-04-27 05:07:58', 2, 3),
(38, '2025-05-01 06:18:16', 4, 3),
(39, '2025-05-01 06:18:18', 5, 3),
(41, '2025-05-01 07:14:48', 4, 3),
(42, '2025-05-01 07:14:52', 5, 3),
(43, '2025-05-01 07:14:54', 4, 3),
(44, '2025-05-01 17:10:26', 7, 3),
(45, '2025-05-01 17:11:40', 6, 3),
(46, '2025-05-01 17:11:59', 6, 3),
(47, '2025-05-01 17:12:10', 7, 3),
(48, '2025-05-01 19:19:42', 7, 3),
(49, '2025-05-01 19:19:49', 6, 3),
(50, '2025-05-01 20:46:38', 3, 3),
(51, '2025-05-01 20:59:28', 7, 3),
(52, '2025-05-01 20:59:34', 7, 3),
(53, '2025-05-01 20:59:37', 6, 3),
(54, '2025-05-01 21:00:39', 7, 3),
(55, '2025-05-01 21:01:46', 7, 3),
(56, '2025-05-01 21:01:49', 6, 3),
(57, '2025-05-01 21:02:18', 6, 3),
(58, '2025-05-01 21:02:21', 7, 3),
(59, '2025-05-01 21:02:28', 7, 3),
(60, '2025-05-01 21:02:34', 6, 3),
(61, '2025-05-01 21:04:19', 7, 3),
(62, '2025-05-01 21:04:22', 6, 3),
(63, '2025-05-01 21:04:26', 6, 3),
(66, '2025-05-01 21:35:42', 2, 3),
(67, '2025-05-01 21:36:14', 2, 3),
(68, '2025-05-02 01:19:38', 9, 3),
(69, '2025-05-02 01:19:51', 2, 3),
(70, '2025-05-02 01:22:39', 9, 3),
(71, '2025-05-02 01:25:16', 10, 3),
(72, '2025-05-02 01:29:33', 1, 3),
(73, '2025-05-08 10:43:47', 4, 3),
(74, '2025-05-08 10:43:49', 5, 3),
(75, '2025-05-08 10:44:14', 4, 3),
(76, '2025-05-08 10:44:26', 5, 3),
(77, '2025-05-08 10:44:39', 4, 3),
(78, '2025-05-08 10:44:51', 5, 3),
(79, '2025-05-08 10:44:52', 4, 3),
(80, '2025-05-08 10:44:53', 5, 3),
(81, '2025-05-08 10:44:53', 4, 3),
(82, '2025-05-08 10:44:53', 5, 3),
(83, '2025-05-08 10:44:53', 4, 3),
(84, '2025-05-08 10:44:54', 5, 3),
(85, '2025-05-08 10:44:54', 4, 3),
(86, '2025-05-08 10:44:55', 5, 3),
(87, '2025-05-08 10:45:04', 4, 3),
(88, '2025-05-08 10:45:06', 5, 3),
(89, '2025-05-08 10:45:11', 4, 3),
(90, '2025-05-08 10:45:11', 5, 3),
(91, '2025-05-08 10:45:12', 4, 3),
(92, '2025-05-08 10:45:13', 5, 3),
(93, '2025-05-08 10:45:13', 4, 3),
(94, '2025-05-08 10:45:17', 5, 3),
(95, '2025-05-08 10:45:25', 5, 3),
(96, '2025-05-08 10:45:31', 4, 3),
(97, '2025-05-08 10:45:33', 5, 3),
(98, '2025-05-08 10:45:34', 4, 3),
(99, '2025-05-08 10:45:39', 5, 3),
(100, '2025-05-08 10:45:39', 4, 3),
(101, '2025-05-08 10:45:39', 5, 3),
(102, '2025-05-08 10:45:40', 4, 3),
(103, '2025-05-08 10:45:40', 5, 3),
(104, '2025-05-08 10:45:40', 4, 3),
(105, '2025-05-08 10:45:41', 5, 3),
(106, '2025-05-08 10:45:41', 4, 3),
(107, '2025-05-08 10:45:42', 5, 3),
(108, '2025-05-08 10:45:42', 4, 3),
(109, '2025-05-08 10:45:43', 5, 3),
(110, '2025-05-08 10:45:44', 4, 3),
(111, '2025-05-08 10:45:57', 5, 3),
(112, '2025-05-08 10:46:01', 4, 3),
(113, '2025-05-08 10:46:02', 5, 3),
(114, '2025-05-08 10:46:02', 4, 3),
(115, '2025-05-08 10:46:02', 5, 3),
(116, '2025-05-08 10:46:03', 4, 3),
(117, '2025-05-08 10:46:03', 5, 3),
(118, '2025-05-08 10:46:03', 4, 3),
(119, '2025-05-08 10:46:04', 5, 3),
(120, '2025-05-08 10:46:04', 4, 3),
(121, '2025-05-08 10:46:04', 5, 3),
(122, '2025-05-08 10:46:05', 4, 3),
(123, '2025-05-08 10:46:06', 5, 3),
(124, '2025-05-08 10:46:10', 4, 3),
(125, '2025-05-08 10:46:11', 5, 3),
(126, '2025-05-08 10:46:12', 4, 3),
(127, '2025-05-10 07:50:42', 4, 3),
(128, '2025-05-10 07:50:48', 5, 3),
(129, '2025-05-10 07:50:50', 4, 3),
(130, '2025-05-10 07:50:51', 5, 3),
(131, '2025-05-11 05:29:24', 4, 3),
(132, '2025-05-11 05:29:36', 5, 3),
(133, '2025-05-11 05:29:37', 4, 3),
(134, '2025-05-11 05:29:38', 5, 3),
(135, '2025-05-11 05:29:59', 4, 3),
(136, '2025-05-11 05:30:19', 5, 3),
(137, '2025-05-11 06:28:43', 5, 3),
(138, '2025-05-11 06:29:09', 4, 3),
(139, '2025-05-11 06:29:24', 5, 3),
(140, '2025-05-11 06:29:35', 4, 3),
(141, '2025-05-11 06:29:55', 5, 3),
(142, '2025-05-11 06:30:20', 4, 3),
(143, '2025-05-11 06:30:23', 5, 3),
(144, '2025-05-11 06:30:30', 4, 3),
(145, '2025-05-11 07:34:51', 1, 3),
(146, '2025-05-11 07:42:05', 5, 3),
(147, '2025-05-11 08:32:01', 4, 3),
(148, '2025-05-11 08:39:49', 9, 3),
(149, '2025-05-11 08:40:42', 4, 3),
(150, '2025-05-11 08:41:05', 5, 3),
(151, '2025-05-11 08:41:09', 4, 3),
(152, '2025-05-11 08:41:10', 5, 3),
(153, '2025-05-11 08:41:12', 4, 3),
(154, '2025-05-11 08:41:13', 5, 3),
(155, '2025-05-11 09:13:47', 3, 3),
(156, '2025-05-11 09:21:59', 9, 3),
(157, '2025-05-11 09:24:57', 10, 3),
(158, '2025-05-11 09:27:09', 3, 3),
(159, '2025-05-11 09:33:35', 3, 3),
(160, '2025-05-11 09:48:14', 12, 3),
(161, '2025-05-11 10:40:17', 1, 3),
(162, '2025-05-11 10:40:17', 13, 3),
(163, '2025-05-11 10:41:20', 2, 3),
(164, '2025-05-11 10:42:02', 1, 3),
(165, '2025-05-11 10:42:02', 13, 3),
(166, '2025-05-11 10:42:36', 14, 3),
(167, '2025-05-12 03:43:57', 1, 3),
(168, '2025-05-12 03:55:57', 1, 3),
(170, '2025-05-12 04:23:28', 1, 3),
(171, '2025-05-12 04:25:02', 5, 14),
(172, '2025-05-12 04:25:03', 4, 14),
(173, '2025-05-12 05:25:57', 1, 14),
(174, '2025-05-12 05:38:00', 3, 14),
(175, '2025-05-12 05:38:59', 3, 14),
(176, '2025-05-12 05:39:40', 3, 14),
(177, '2025-05-12 05:39:59', 3, 14),
(178, '2025-05-12 05:40:29', 3, 14),
(179, '2025-05-12 05:42:02', 12, 14),
(180, '2025-05-12 05:42:08', 3, 14),
(181, '2025-05-12 05:48:26', 3, 14),
(182, '2025-05-12 05:48:36', 3, 14),
(183, '2025-05-12 05:49:27', 3, 14),
(184, '2025-05-12 05:50:05', 3, 14),
(185, '2025-05-12 05:50:58', 3, 14),
(186, '2025-05-12 05:51:55', 3, 14),
(187, '2025-05-12 05:52:57', 3, 14),
(188, '2025-05-12 05:59:45', 3, 14),
(189, '2025-05-12 06:01:32', 3, 14),
(190, '2025-05-12 06:01:46', 3, 14),
(191, '2025-05-12 06:01:54', 3, 14),
(192, '2025-05-12 06:02:00', 3, 14),
(193, '2025-05-12 06:02:13', 3, 14),
(194, '2025-05-12 06:02:57', 3, 14),
(195, '2025-05-12 06:03:25', 3, 14),
(196, '2025-05-12 06:04:35', 3, 14),
(197, '2025-05-12 06:07:05', 3, 14),
(198, '2025-05-12 06:07:08', 3, 14),
(199, '2025-05-12 06:07:16', 3, 14),
(200, '2025-05-12 06:13:45', 5, 3),
(201, '2025-05-12 06:13:48', 4, 3),
(202, '2025-05-12 06:13:50', 5, 3),
(203, '2025-05-12 06:13:56', 4, 3),
(204, '2025-05-12 06:29:16', 1, 3),
(205, '2025-05-15 23:12:02', 9, 3),
(206, '2025-05-15 23:14:47', 10, 3),
(207, '2025-05-15 23:14:57', 9, 3),
(208, '2025-05-15 23:15:12', 10, 3),
(209, '2025-05-15 23:17:35', 12, 3),
(210, '2025-05-15 23:17:47', 12, 3),
(211, '2025-05-15 23:21:25', 12, 3),
(212, '2025-05-15 23:21:35', 12, 3),
(213, '2025-05-15 23:25:32', 12, 3),
(214, '2025-05-15 23:25:52', 12, 3),
(215, '2025-05-15 23:28:05', 12, 3),
(216, '2025-05-15 23:28:14', 12, 3),
(217, '2025-05-15 23:28:22', 12, 3),
(218, '2025-05-15 23:28:26', 12, 3),
(219, '2025-05-15 23:28:55', 12, 3),
(220, '2025-05-15 23:33:01', 12, 3),
(221, '2025-05-15 23:33:10', 12, 3),
(222, '2025-05-15 23:33:21', 12, 3),
(223, '2025-05-15 23:34:20', 12, 3),
(224, '2025-05-15 23:34:47', 12, 3),
(225, '2025-05-15 23:47:31', 12, 3),
(226, '2025-05-15 23:47:45', 12, 3),
(227, '2025-05-15 23:48:07', 12, 3),
(228, '2025-05-15 23:52:20', 12, 3),
(229, '2025-05-15 23:52:45', 12, 3),
(230, '2025-05-15 23:52:55', 12, 3),
(231, '2025-05-15 23:53:01', 12, 3),
(232, '2025-05-15 23:53:07', 12, 3),
(233, '2025-05-15 23:58:04', 12, 3),
(234, '2025-05-16 00:01:20', 12, 3),
(235, '2025-06-28 18:20:48', 5, 14),
(236, '2025-06-28 18:20:50', 4, 14),
(237, '2025-06-28 18:22:12', 12, 14),
(238, '2025-06-28 18:22:20', 12, 14),
(239, '2025-06-28 18:22:32', 12, 14),
(240, '2025-06-28 18:31:02', 12, 14),
(241, '2025-06-28 18:31:59', 12, 14),
(242, '2025-06-28 18:32:06', 12, 14),
(243, '2025-06-28 18:32:18', 12, 14),
(244, '2025-06-28 18:32:23', 12, 14),
(245, '2025-06-28 18:33:01', 12, 14),
(246, '2025-06-28 18:33:13', 12, 14),
(247, '2025-06-28 18:34:26', 12, 14),
(248, '2025-06-28 18:35:19', 12, 14),
(249, '2025-06-28 18:43:09', 12, 14),
(250, '2025-06-28 19:11:58', 5, 14),
(251, '2025-06-28 19:12:00', 4, 14),
(252, '2025-06-28 19:15:25', 5, 14),
(253, '2025-06-28 19:15:32', 4, 14),
(254, '2025-06-29 01:19:26', 5, 14),
(255, '2025-06-29 01:19:29', 4, 14),
(256, '2025-06-29 01:24:19', 3, 14),
(257, '2025-06-29 01:24:36', 12, 14),
(258, '2025-06-29 01:26:15', 12, 14),
(259, '2025-06-29 01:27:43', 12, 14),
(260, '2025-06-29 01:27:58', 12, 14),
(261, '2025-06-29 01:28:06', 3, 14),
(262, '2025-06-29 01:28:14', 12, 14),
(263, '2025-06-29 01:29:19', 3, 14),
(264, '2025-06-29 01:29:23', 3, 14),
(265, '2025-06-29 01:29:37', 3, 14),
(266, '2025-06-29 01:31:00', 12, 14),
(267, '2025-06-29 01:32:32', 12, 14),
(268, '2025-06-29 01:33:08', 12, 14),
(269, '2025-06-29 01:36:29', 12, 14),
(270, '2025-06-29 01:36:33', 12, 14),
(271, '2025-06-29 01:36:43', 12, 14),
(272, '2025-06-29 01:36:59', 3, 14),
(273, '2025-06-29 01:37:17', 3, 14),
(274, '2025-06-29 01:37:21', 3, 14),
(275, '2025-06-29 01:38:29', 3, 14),
(276, '2025-06-29 01:38:41', 3, 14),
(277, '2025-06-29 01:40:01', 3, 14),
(278, '2025-06-29 01:41:41', 3, 14),
(279, '2025-06-29 03:57:31', 1, 14),
(280, '2025-06-29 04:00:57', 1, 14),
(281, '2025-06-29 04:01:32', 9, 14),
(282, '2025-06-29 04:01:42', 10, 14),
(283, '2025-06-29 04:04:46', 13, 14),
(284, '2025-06-29 04:06:31', 13, 14),
(285, '2025-06-29 04:12:01', 14, 14),
(286, '2025-07-01 03:35:04', 12, 14),
(287, '2025-07-01 03:35:25', 12, 14),
(288, '2025-07-01 03:44:54', 12, 14),
(289, '2025-07-01 03:45:19', 12, 14),
(290, '2025-07-01 03:45:33', 12, 14),
(291, '2025-07-01 03:47:15', 12, 14),
(292, '2025-07-01 03:47:57', 12, 14),
(293, '2025-07-01 03:48:16', 12, 14),
(294, '2025-07-01 03:54:12', 12, 14),
(295, '2025-07-01 03:56:49', 12, 14),
(296, '2025-07-01 03:57:10', 12, 14),
(297, '2025-07-01 03:57:41', 12, 14),
(298, '2025-07-01 04:01:27', 12, 14),
(299, '2025-07-01 04:01:42', 12, 14),
(300, '2025-07-01 04:01:59', 12, 14),
(301, '2025-07-01 04:05:29', 12, 14),
(302, '2025-07-01 04:05:39', 12, 14),
(303, '2025-07-01 04:05:51', 12, 14),
(304, '2025-07-01 04:28:13', 12, 14),
(305, '2025-07-01 04:28:21', 12, 14),
(306, '2025-07-01 04:28:40', 12, 14),
(307, '2025-07-01 04:28:53', 12, 14),
(308, '2025-07-01 04:32:29', 5, 14),
(309, '2025-07-01 04:32:47', 4, 14),
(310, '2025-07-01 04:33:03', 12, 14),
(311, '2025-07-01 04:33:14', 12, 14),
(312, '2025-07-01 04:33:22', 12, 14),
(313, '2025-07-22 03:58:49', 5, 16),
(314, '2025-07-22 03:58:53', 4, 16),
(315, '2025-07-22 03:59:20', 5, 16),
(316, '2025-07-22 03:59:30', 4, 16),
(317, '2025-07-22 03:59:50', 5, 16),
(318, '2025-07-22 04:00:04', 4, 16),
(319, '2025-07-22 04:00:17', 5, 16),
(320, '2025-07-22 04:04:26', 4, 16),
(321, '2025-07-22 04:05:02', 5, 16),
(322, '2025-07-22 04:05:12', 4, 16),
(323, '2025-07-22 04:06:24', 5, 16),
(324, '2025-07-22 04:06:28', 4, 16),
(325, '2025-07-22 04:07:15', 5, 16),
(326, '2025-07-22 04:07:19', 4, 16),
(327, '2025-07-22 04:12:51', 5, 16),
(328, '2025-07-22 04:12:55', 4, 16),
(329, '2025-07-22 04:13:15', 5, 16),
(330, '2025-07-22 04:13:19', 4, 16),
(331, '2025-07-22 04:23:43', 5, 16),
(332, '2025-07-22 04:24:54', 4, 16),
(333, '2025-07-22 04:25:07', 5, 16),
(334, '2025-07-22 04:25:17', 4, 16),
(335, '2025-07-22 04:25:19', 5, 16),
(336, '2025-07-22 04:25:54', 4, 16),
(337, '2025-07-22 04:25:56', 5, 16),
(338, '2025-07-22 04:30:10', 4, 16),
(339, '2025-07-22 04:30:14', 5, 16),
(340, '2025-07-22 04:30:34', 4, 16),
(341, '2025-07-22 04:30:36', 5, 16),
(342, '2025-07-22 04:32:11', 4, 16),
(343, '2025-07-22 04:35:32', 5, 16),
(344, '2025-07-22 05:13:30', 3, 16),
(345, '2025-07-22 05:17:38', 3, 16),
(346, '2025-07-22 05:17:47', 3, 16),
(347, '2025-07-22 05:17:54', 3, 16),
(348, '2025-07-22 05:54:14', 4, 16),
(349, '2025-07-23 00:29:27', 5, 16),
(350, '2025-07-23 00:29:31', 4, 16),
(351, '2025-07-27 00:14:45', 9, 16),
(352, '2025-07-27 00:15:12', 10, 16),
(353, '2025-07-27 00:16:11', 12, 16),
(354, '2025-07-27 00:16:17', 12, 16),
(355, '2025-07-27 04:24:32', 1, 16),
(356, '2025-07-27 04:25:13', 1, 16),
(357, '2025-07-27 04:27:10', 1, 16);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `categoria_id` int(10) NOT NULL,
  `details` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`categoria_id`, `details`) VALUES
(1, 'Tuberias y fibras plasticas'),
(2, 'Acero'),
(3, 'Estructura'),
(4, 'Cerramiento y bloques'),
(5, 'Cables y accesorios electricos'),
(6, 'Cemento y derivados'),
(7, 'Piezas sanitarias, griferias y ceramica'),
(8, 'Pintura e impermeabilizantes'),
(9, 'Batea y fregadero'),
(10, 'Puertas, ventanas, y productos de madera'),
(11, 'Batea y fregadero acero'),
(12, 'Techos'),
(13, 'Especial'),
(14, 'Electrico');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `material`
--

CREATE TABLE `material` (
  `material_id` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `tonelada` varchar(255) NOT NULL,
  `amount_total` float NOT NULL DEFAULT 0,
  `unidad` int(10) NOT NULL,
  `categoria` int(10) NOT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `material`
--

INSERT INTO `material` (`material_id`, `name`, `tonelada`, `amount_total`, `unidad`, `categoria`, `estatus`) VALUES
(1, 'Tubos tuberias plasticos', '0.00001', 22, 1, 1, 1),
(2, 'Fibras', '0.01', 73, 1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movement`
--

CREATE TABLE `movement` (
  `materials_id` int(10) NOT NULL,
  `code` varchar(255) NOT NULL,
  `amount` float NOT NULL,
  `date_income` date DEFAULT NULL,
  `date_expiration` date DEFAULT NULL,
  `estatus` tinyint(1) NOT NULL DEFAULT 1,
  `type` varchar(10) NOT NULL,
  `material` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movement`
--

INSERT INTO `movement` (`materials_id`, `code`, `amount`, `date_income`, `date_expiration`, `estatus`, `type`, `material`) VALUES
(1, 'asdASD', 3, '2025-05-10', NULL, 1, 'entrada', 1),
(5, 'DSFi', 16, '2025-05-23', NULL, 1, 'entrada', 1),
(7, 'nfaso', 18, '2025-05-01', NULL, 1, 'salida', 1),
(11, 'nfasodsd', 2, '2025-05-11', NULL, 1, 'salida', 2),
(12, 'BVS', 12, '2025-05-11', NULL, 1, 'entrada', 2),
(13, 'nfasoss', 10, '2025-05-11', NULL, 1, 'salida', 2),
(17, 'asdASD', 13, '2025-07-27', NULL, 1, 'entrada', 2),
(18, 'DSF', 12, '2025-07-27', NULL, 1, 'salida', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `rol_id` int(10) NOT NULL,
  `type` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`rol_id`, `type`) VALUES
(1, 'admin'),
(2, 'user');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unidad`
--

CREATE TABLE `unidad` (
  `unidad_id` int(10) NOT NULL,
  `siglas` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `unidad`
--

INSERT INTO `unidad` (`unidad_id`, `siglas`, `description`) VALUES
(1, 'UND', ''),
(2, 'ROLLO', ''),
(3, 'M3', ''),
(4, 'GLN', ''),
(5, 'KG', ''),
(6, 'ML', ''),
(7, 'SCO', ''),
(8, 'm2', ''),
(9, 'TON', ''),
(10, 'Kit', ''),
(11, 'GAL', ''),
(12, 'JGO', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE `user` (
  `user_id` int(10) NOT NULL,
  `name` varchar(20) NOT NULL,
  `gmail` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` int(10) NOT NULL,
  `attempts` int(3) NOT NULL DEFAULT 0,
  `security_question` varchar(50) NOT NULL,
  `security_response` varchar(50) NOT NULL,
  `token_recuperacion` varchar(64) DEFAULT NULL,
  `token_expiracion` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user`
--

INSERT INTO `user` (`user_id`, `name`, `gmail`, `password`, `rol`, `attempts`, `security_question`, `security_response`, `token_recuperacion`, `token_expiracion`, `active`) VALUES
(2, 'Jesuss', 'xacake5941@bauscn.com', '7496160oao$', 1, 0, 'Mi primera mascota', 'Coco1111', NULL, NULL, 0),
(3, 'Guate', 'uzcategui94533@gmail.com', '$2y$10$WjHcKALkBAK9qHlvnSJr8eY4bf.Rl18vUjof3otcNPcnsfMQDjhiu', 1, 0, 'color favorito', 'azul', NULL, NULL, 1),
(9, 'dsi', 'ds@gmail.com', '123456', 2, 0, 'color favorito', 'azul', NULL, NULL, 1),
(14, 'ds', 'kahoxo3830@ofacer.com', '$2y$10$JDJYWWN6Sy42xBdmmjw1duPuIohm3BYeTc1BC.NKo8C8YRZxELUb2', 1, 0, 'color favorito', 'azul', 'e0021f31300b83322fa994d611ff5b4a7e9934d6626059e856df84b5208e9b6d', '2025-06-28 20:51:37', 1),
(16, 'dssd', 'meteli3242@evusd.com', '$2y$10$9ufxcBkc92dNFjBeW/PgaeEmec6D7EGJDwygkVCXQWRLIeNVeIvi.', 1, 0, 'color favorito', 'azul', NULL, NULL, 1),
(19, 'admin', 'uptag1@gmail.com', '$2y$10$yy5jgdd6rkrtGKMcJGJrJ.Y0xVlp1Uj2LvCnaE94p6JAmUYVyjD8G', 1, 0, 'color favorito', 'azul', NULL, NULL, 1),
(20, 'usuario', 'uptag2@gmail.com', '$2y$10$6K35DX3GOajhshJWBbwLFOLfIFPqiyNfkUhJMr2P41qcX0G0hEh7u', 2, 0, 'color favorito', 'azul', NULL, NULL, 1),
(21, 'DSi2', 'jejefa5953@inkight.com', '$2y$10$G/I4.FVEn27bkre7l6ZbP.DaLgSmHRwdN66eK8MCE3B8wGE.TI6xi', 1, 0, 'color favorito', 'azul', NULL, NULL, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `action`
--
ALTER TABLE `action`
  ADD PRIMARY KEY (`action_id`);

--
-- Indices de la tabla `binnacle`
--
ALTER TABLE `binnacle`
  ADD PRIMARY KEY (`binnacle_id`),
  ADD KEY `user` (`user`),
  ADD KEY `action` (`action`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`categoria_id`);

--
-- Indices de la tabla `material`
--
ALTER TABLE `material`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `categoria` (`categoria`),
  ADD KEY `unidad` (`unidad`);

--
-- Indices de la tabla `movement`
--
ALTER TABLE `movement`
  ADD PRIMARY KEY (`materials_id`),
  ADD KEY `material` (`material`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`rol_id`);

--
-- Indices de la tabla `unidad`
--
ALTER TABLE `unidad`
  ADD PRIMARY KEY (`unidad_id`);

--
-- Indices de la tabla `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `rol` (`rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `action`
--
ALTER TABLE `action`
  MODIFY `action_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `binnacle`
--
ALTER TABLE `binnacle`
  MODIFY `binnacle_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=358;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `categoria_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `material`
--
ALTER TABLE `material`
  MODIFY `material_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `movement`
--
ALTER TABLE `movement`
  MODIFY `materials_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `rol_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `unidad`
--
ALTER TABLE `unidad`
  MODIFY `unidad_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `binnacle`
--
ALTER TABLE `binnacle`
  ADD CONSTRAINT `binnacle_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `binnacle_ibfk_2` FOREIGN KEY (`action`) REFERENCES `action` (`action_id`);

--
-- Filtros para la tabla `material`
--
ALTER TABLE `material`
  ADD CONSTRAINT `material_ibfk_1` FOREIGN KEY (`categoria`) REFERENCES `categoria` (`categoria_id`),
  ADD CONSTRAINT `material_ibfk_2` FOREIGN KEY (`unidad`) REFERENCES `unidad` (`unidad_id`);

--
-- Filtros para la tabla `movement`
--
ALTER TABLE `movement`
  ADD CONSTRAINT `movement_ibfk_1` FOREIGN KEY (`material`) REFERENCES `material` (`material_id`);

--
-- Filtros para la tabla `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`rol`) REFERENCES `rol` (`rol_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
