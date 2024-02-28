-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-02-2024 a las 21:45:51
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
-- Base de datos: `lokeleague`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ligas`
--

CREATE TABLE `ligas` (
  `ID` int(11) NOT NULL,
  `NombreLiga` varchar(255) DEFAULT NULL,
  `NombreJuego` varchar(255) DEFAULT NULL,
  `CreatorID` int(11) DEFAULT NULL,
  `FechaCreacion` datetime DEFAULT NULL,
  `FechaFinalizacion` datetime DEFAULT NULL,
  `RandomLocke` tinyint(1) DEFAULT NULL,
  `NumeroParticipantes` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ligas`
--

INSERT INTO `ligas` (`ID`, `NombreLiga`, `NombreJuego`, `CreatorID`, `FechaCreacion`, `FechaFinalizacion`, `RandomLocke`, `NumeroParticipantes`) VALUES
(64, '1', 'Rojo', 8, '2024-02-11 17:13:00', '2024-02-23 17:14:00', 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `liga_usuarios`
--

CREATE TABLE `liga_usuarios` (
  `ID` int(11) NOT NULL,
  `LigaID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `liga_usuarios`
--

INSERT INTO `liga_usuarios` (`ID`, `LigaID`, `UserID`) VALUES
(83, 64, 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `Username` varchar(255) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `IsAuthenticated` tinyint(1) DEFAULT NULL,
  `ValidationToken` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`ID`, `Username`, `Password`, `Email`, `IsAuthenticated`, `ValidationToken`) VALUES
(8, 'aleix', '6bfcc4026b5f162799a6dc8305c09db9c1674ac616bd5c7422a45fbb6d0816ac163047c47a1f426f4f4c6b5b5042c671eabc4fdc7310fd5b183eef59dc274604', 'anaviogarcia.cf@iesesteveterradas.cat', 1, 'v0IYqtxJIQDcazIxASbSSFR4DSeS4wwUb1UJeiVY'),
(9, 'litsu', '6bfcc4026b5f162799a6dc8305c09db9c1674ac616bd5c7422a45fbb6d0816ac163047c47a1f426f4f4c6b5b5042c671eabc4fdc7310fd5b183eef59dc274604', 'navioaleix@gmail.com', NULL, 's9N4qHckG1oWGcKpSSJoZTe2ecPt6PRcv7ple2KR'),
(10, 'wqer', '6bfcc4026b5f162799a6dc8305c09db9c1674ac616bd5c7422a45fbb6d0816ac163047c47a1f426f4f4c6b5b5042c671eabc4fdc7310fd5b183eef59dc274604', 'sdafsdf@asdf.es', NULL, 'oKUDJWjAZkE3ViYzU0FhKlZdd2xdkD1RQhN8baTk'),
(11, 'litsu', '5156c200459a1b8e779547cb4d1e8d18379351358b202355457d577c9f27b1727641bfa1de3941f1bf53be540dc0c6050983f8f6162843ffddec94c97889110c', 'alnavigar98@gmail.com', 1, 'iZfq12eTfl6ZOlUjvu4CrTRPATYYrIsk1hEgix9T'),
(14, '123123', '6bfcc4026b5f162799a6dc8305c09db9c1674ac616bd5c7422a45fbb6d0816ac163047c47a1f426f4f4c6b5b5042c671eabc4fdc7310fd5b183eef59dc274604', 'littsu98@gmail.com', 1, 'QnZmGxH3WKQbYPkV7Y1bYclKBuKe5PI3GsRaTfJD');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ligas`
--
ALTER TABLE `ligas`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `FK_CreatorID` (`CreatorID`);

--
-- Indices de la tabla `liga_usuarios`
--
ALTER TABLE `liga_usuarios`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `FK_LigaID` (`LigaID`),
  ADD KEY `FK_UserID` (`UserID`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ligas`
--
ALTER TABLE `ligas`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de la tabla `liga_usuarios`
--
ALTER TABLE `liga_usuarios`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `ligas`
--
ALTER TABLE `ligas`
  ADD CONSTRAINT `FK_CreatorID` FOREIGN KEY (`CreatorID`) REFERENCES `users` (`ID`);

--
-- Filtros para la tabla `liga_usuarios`
--
ALTER TABLE `liga_usuarios`
  ADD CONSTRAINT `FK_LigaID` FOREIGN KEY (`LigaID`) REFERENCES `ligas` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_UserID` FOREIGN KEY (`UserID`) REFERENCES `users` (`ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
