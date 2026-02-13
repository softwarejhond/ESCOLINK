-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-02-2026 a las 22:38:42
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
-- Base de datos: `dash_privado`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `grade_level` varchar(255) NOT NULL,
  `course_type` varchar(255) NOT NULL,
  `modality` varchar(50) NOT NULL,
  `class_date` date NOT NULL,
  `attendance_status` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `class_observations`
--

CREATE TABLE `class_observations` (
  `id` int(11) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `grade_level` varchar(50) NOT NULL,
  `course_type` varchar(50) NOT NULL,
  `class_date` date NOT NULL,
  `observation_type` varchar(50) NOT NULL,
  `observation_text` text DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `company`
--

CREATE TABLE `company` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `nit` varchar(15) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `email` varchar(266) NOT NULL,
  `ciudad` varchar(255) NOT NULL,
  `web` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `company`
--

INSERT INTO `company` (`id`, `nombre`, `nit`, `direccion`, `telefono`, `logo`, `email`, `ciudad`, `web`) VALUES
(1, 'Poliandino', '8909852335', 'Carrera 48 # 52 Sur 81', '(604) 540 49 90', 'logo_1771009569.png', 'beneficios@amigotex.com', 'SABANETA', 'https://amigotex.com/');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `el_students`
--

CREATE TABLE `el_students` (
  `id` int(11) NOT NULL,
  `student_code` varchar(50) DEFAULT NULL COMMENT 'Código de matricula o carnet',
  `document_type` enum('TI','CC','CE','RC','PAS') NOT NULL DEFAULT 'TI',
  `document_number` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Nombre completo del estudiante',
  `grade_level` varchar(20) NOT NULL COMMENT 'Ej: 10°, Semestre 1, etc.',
  `gender` enum('M','F','OTRO') NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `cell_phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `registration_date` date DEFAULT NULL,
  `sede` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(255) DEFAULT NULL,
  `simat` varchar(255) DEFAULT NULL,
  `cell_phone2` varchar(20) DEFAULT NULL,
  `barrio` varchar(255) DEFAULT NULL,
  `comuna` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `email_history`
--

CREATE TABLE `email_history` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `recipients_count` int(11) NOT NULL,
  `successful_count` int(11) NOT NULL,
  `failed_count` int(11) NOT NULL,
  `sent_by` varchar(100) NOT NULL,
  `sent_from` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `email_recipients`
--

CREATE TABLE `email_recipients` (
  `id` int(11) NOT NULL,
  `email_id` int(11) NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `status` enum('success','failed') NOT NULL,
  `error_message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_correos`
--

CREATE TABLE `historial_correos` (
  `id` int(11) NOT NULL,
  `destinatario` varchar(255) NOT NULL,
  `cc` varchar(255) DEFAULT NULL,
  `asunto` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `estado` varchar(50) NOT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `municipios`
--

CREATE TABLE `municipios` (
  `id_municipio` int(10) UNSIGNED NOT NULL,
  `municipio` varchar(255) NOT NULL DEFAULT '',
  `estado` int(10) UNSIGNED NOT NULL,
  `departamento_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sedes`
--

CREATE TABLE `sedes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `creado_por` varchar(255) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sedes`
--

INSERT INTO `sedes` (`id`, `nombre`, `creado_por`, `fecha_creacion`) VALUES
(1, 'SEDE UNO', '22233344', '2025-10-22 00:18:56'),
(2, 'EMPRESA DOS', '22233344', '2025-10-22 00:19:05'),
(3, 'ALMACEN TRES', '22233344', '2025-10-22 00:19:24'),
(5, 'MEDELLÍN', '22233344', '2026-01-13 16:12:45'),
(6, 'SEDE PRUEBA 2', '22233344', '2026-01-13 16:18:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sms_credentials`
--

CREATE TABLE `sms_credentials` (
  `id` int(11) NOT NULL,
  `apiKey` varchar(255) NOT NULL,
  `apiSecret` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sms_credentials`
--

INSERT INTO `sms_credentials` (`id`, `apiKey`, `apiSecret`) VALUES
(1, 'mz7Y5j47vK', 'a7fcgcxbme');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sms_logs`
--

CREATE TABLE `sms_logs` (
  `id` int(11) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `message` varchar(160) NOT NULL,
  `sender` varchar(100) NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sms_logs`
--

INSERT INTO `sms_logs` (`id`, `phone`, `message`, `sender`, `sent_at`) VALUES
(5, '3508284544', 'AMIGOTEX: JUAN, tu ahorro navideño fue de $ 3,865,335. Ingresa al link para indicarnos que quieres hacer con tu dinero https://acortar.link/9E4Sy5', '1001142067', '2025-11-07 16:29:26'),
(6, '3015606006', 'AMIGOTEX: JHON, tu ahorro navideño fue de $ 1,234,417. Ingresa al link para indicarnos que quieres hacer con tu dinero https://acortar.link/9E4Sy5', '1001142067', '2025-11-07 16:29:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `smtpconfig`
--

CREATE TABLE `smtpconfig` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `port` int(11) NOT NULL,
  `dependence` text NOT NULL,
  `Subject` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `smtpconfig`
--

INSERT INTO `smtpconfig` (`id`, `username`, `host`, `email`, `password`, `port`, `dependence`, `Subject`) VALUES
(1, 'noreply@agenciaeaglesoftware.com', 'mail.agenciaeaglesoftware.com', 'noreply@agenciaeaglesoftware.com', 'sPcp]2tI29DWXjBM', 465, 'Plataforma Beneficios METROFEM', 'Confirmación de entrega - Beneficios');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `student_attendance_management`
--

CREATE TABLE `student_attendance_management` (
  `id` int(11) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `grade_level` varchar(50) NOT NULL,
  `course_type` varchar(50) NOT NULL,
  `requires_intervention` varchar(2) DEFAULT NULL,
  `responsible_username` varchar(100) DEFAULT NULL,
  `intervention_observation` text DEFAULT NULL,
  `is_resolved` varchar(2) DEFAULT NULL,
  `requires_additional_strategy` varchar(2) DEFAULT NULL,
  `strategy_observation` text DEFAULT NULL,
  `strategy_fulfilled` varchar(2) DEFAULT NULL,
  `withdrawal_reason` varchar(255) DEFAULT NULL,
  `withdrawal_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `student_attendance_management`
--

INSERT INTO `student_attendance_management` (`id`, `student_id`, `grade_level`, `course_type`, `requires_intervention`, `responsible_username`, `intervention_observation`, `is_resolved`, `requires_additional_strategy`, `strategy_observation`, `strategy_fulfilled`, `withdrawal_reason`, `withdrawal_date`, `created_at`, `updated_at`) VALUES
(1, '53602789', '7-B', 'matematicas', 'Si', '22233344', 'prueba de guardado', 'Si', '', '', '', '', NULL, '2026-02-13 21:35:23', '2026-02-13 21:35:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tutoriales`
--

CREATE TABLE `tutoriales` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `modulo` varchar(255) NOT NULL,
  `link` text NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` text NOT NULL,
  `rol` int(2) NOT NULL,
  `rol_informativo` int(11) NOT NULL,
  `extra_rol` int(2) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `orden` int(11) NOT NULL,
  `fechaCreacionUser` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `genero` text NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `edad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nombre`, `rol`, `rol_informativo`, `extra_rol`, `foto`, `orden`, `fechaCreacionUser`, `email`, `genero`, `telefono`, `direccion`, `edad`) VALUES
(17, 22233344, '$2y$10$oHlCEOyRw3OJekppM4GHNuRJQWbzFckUaYZxH9izo/XSna/zqaoTq', 'Noble Six', 1, 0, 0, '22233344_24102025.png', 1, '24102025200135', 'juandidoc11@gmail.com', 'Hombre', '3508284544', 'Diagonal 57 # 47 a 28', 33);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_register`
--

CREATE TABLE `user_register` (
  `id` int(11) NOT NULL,
  `typeID` varchar(30) NOT NULL,
  `number_id` bigint(50) NOT NULL,
  `number_id_very` varchar(15) NOT NULL,
  `first_name` varchar(25) NOT NULL,
  `second_name` varchar(25) NOT NULL,
  `first_last` varchar(25) NOT NULL,
  `second_last` varchar(25) NOT NULL,
  `birthdate` date NOT NULL,
  `expedition_date` date NOT NULL,
  `gender` mediumtext NOT NULL,
  `marital_status` mediumtext NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_very` varchar(255) NOT NULL,
  `first_phone` varchar(15) NOT NULL,
  `second_phone` varchar(15) NOT NULL,
  `password` varchar(25) NOT NULL,
  `emergency_contact_name` varchar(150) NOT NULL,
  `emergency_contact_number` varchar(15) NOT NULL,
  `nationality` mediumtext NOT NULL,
  `department` varchar(50) NOT NULL,
  `municipality` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `latitud` varchar(50) NOT NULL,
  `longitud` varchar(50) NOT NULL,
  `people_charge` int(2) NOT NULL,
  `vulnerable_population` varchar(2) NOT NULL,
  `vulnerable_type` varchar(50) NOT NULL,
  `ethnic_group` varchar(255) NOT NULL,
  `stratum` int(1) NOT NULL,
  `residence_area` mediumtext NOT NULL,
  `training_level` varchar(50) NOT NULL,
  `occupation` mediumtext NOT NULL,
  `time_obligations` varchar(50) NOT NULL,
  `motivations_belong_program` varchar(255) NOT NULL,
  `current_situation` varchar(255) NOT NULL,
  `impediment_complete_course` varchar(70) NOT NULL,
  `availability` mediumtext NOT NULL,
  `mode` varchar(50) NOT NULL,
  `headquarters` varchar(255) NOT NULL,
  `program` varchar(50) NOT NULL,
  `schedules` varchar(255) NOT NULL,
  `schedules_alternative` varchar(255) NOT NULL,
  `prior_knowledge` varchar(2) NOT NULL,
  `level` varchar(50) NOT NULL,
  `languages` varchar(25) NOT NULL,
  `languages_level` varchar(25) NOT NULL,
  `medical_condition` varchar(2) NOT NULL,
  `disability` varchar(2) NOT NULL,
  `type_disability` varchar(120) NOT NULL,
  `pregnancy` varchar(2) NOT NULL,
  `country_person` varchar(2) NOT NULL,
  `technologies` varchar(25) NOT NULL,
  `internet` varchar(2) NOT NULL,
  `knowledge_program` varchar(255) NOT NULL,
  `accept_requirements` varchar(2) NOT NULL,
  `accepts_tech_talent` varchar(2) NOT NULL,
  `accept_data_policies` varchar(2) NOT NULL,
  `file_front_id` varchar(255) NOT NULL,
  `file_back_id` varchar(255) NOT NULL,
  `status` int(1) NOT NULL,
  `statusAdmin` int(1) NOT NULL,
  `lote` int(1) NOT NULL,
  `directed_base` int(1) NOT NULL,
  `idCourse` int(5) NOT NULL,
  `contactMedium` mediumtext NOT NULL,
  `institution` varchar(255) NOT NULL,
  `creationDate` datetime NOT NULL,
  `dayUpdate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`student_id`,`grade_level`,`modality`,`class_date`),
  ADD KEY `idx_attendance_student_course_date_status` (`student_id`,`grade_level`,`class_date`,`attendance_status`);

--
-- Indices de la tabla `class_observations`
--
ALTER TABLE `class_observations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_observation` (`student_id`,`grade_level`,`course_type`,`class_date`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_grade_course_date` (`grade_level`,`course_type`,`class_date`);

--
-- Indices de la tabla `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `el_students`
--
ALTER TABLE `el_students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_number` (`document_number`),
  ADD UNIQUE KEY `student_code` (`student_code`);

--
-- Indices de la tabla `email_history`
--
ALTER TABLE `email_history`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `email_recipients`
--
ALTER TABLE `email_recipients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_id` (`email_id`);

--
-- Indices de la tabla `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `historial_correos`
--
ALTER TABLE `historial_correos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `municipios`
--
ALTER TABLE `municipios`
  ADD PRIMARY KEY (`id_municipio`),
  ADD KEY `departamento_id` (`departamento_id`);

--
-- Indices de la tabla `sedes`
--
ALTER TABLE `sedes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `sms_credentials`
--
ALTER TABLE `sms_credentials`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `smtpconfig`
--
ALTER TABLE `smtpconfig`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `student_attendance_management`
--
ALTER TABLE `student_attendance_management`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_grade_course` (`student_id`,`grade_level`,`course_type`);

--
-- Indices de la tabla `tutoriales`
--
ALTER TABLE `tutoriales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`),
  ADD KEY `id` (`id`);

--
-- Indices de la tabla `user_register`
--
ALTER TABLE `user_register`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_register_lote_number_headquarters` (`lote`,`number_id`,`headquarters`),
  ADD KEY `idx_user_register_institution` (`institution`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `class_observations`
--
ALTER TABLE `class_observations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `company`
--
ALTER TABLE `company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `el_students`
--
ALTER TABLE `el_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `email_history`
--
ALTER TABLE `email_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `email_recipients`
--
ALTER TABLE `email_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_correos`
--
ALTER TABLE `historial_correos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `municipios`
--
ALTER TABLE `municipios`
  MODIFY `id_municipio` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sedes`
--
ALTER TABLE `sedes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `sms_credentials`
--
ALTER TABLE `sms_credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `sms_logs`
--
ALTER TABLE `sms_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `smtpconfig`
--
ALTER TABLE `smtpconfig`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `student_attendance_management`
--
ALTER TABLE `student_attendance_management`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tutoriales`
--
ALTER TABLE `tutoriales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `user_register`
--
ALTER TABLE `user_register`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `email_recipients`
--
ALTER TABLE `email_recipients`
  ADD CONSTRAINT `email_recipients_ibfk_1` FOREIGN KEY (`email_id`) REFERENCES `email_history` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
