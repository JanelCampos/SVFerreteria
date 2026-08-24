-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-07-2026 a las 06:11:46
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
-- Base de datos: `db_prueba`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `articulos`
--

CREATE TABLE `articulos` (
  `IdArticulo` int(11) NOT NULL,
  `codigoBarras` varchar(20) DEFAULT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `Precio_Compra` decimal(8,2) NOT NULL,
  `Precio_Unitario` decimal(8,2) NOT NULL,
  `Cod_Proveedor` int(11) NOT NULL,
  `Cod_Inversor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja`
--

CREATE TABLE `caja` (
  `IdCaja` int(11) NOT NULL,
  `FechaApertura` datetime NOT NULL DEFAULT current_timestamp(),
  `Actividad` text NOT NULL,
  `Monto_inicial` decimal(8,2) NOT NULL,
  `Monto_salida` decimal(8,2) NOT NULL,
  `totalEfectivoDia` double DEFAULT NULL,
  `totalTarjetaDia` double DEFAULT NULL,
  `totalCajaDia` double DEFAULT NULL,
  `utilidadDia` double DEFAULT NULL,
  `TotalEfectivo` float(8,2) NOT NULL,
  `TotalTarjeta` float(8,2) NOT NULL,
  `Total_caja` decimal(8,2) NOT NULL,
  `Utilidad` float(8,2) NOT NULL,
  `Cod_Empleado` int(11) NOT NULL,
  `Estado` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `Id_Cliente` int(11) NOT NULL,
  `Nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Dni` int(8) DEFAULT NULL,
  `Telefono` int(9) DEFAULT NULL,
  `direccion` varchar(50) DEFAULT NULL,
  `Fecha_Registro` date NOT NULL,
  `cantidadCompras` int(11) DEFAULT NULL,
  `montoCompras` double DEFAULT NULL,
  `gananciaGenerada` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente_temp`
--

CREATE TABLE `cliente_temp` (
  `idClienteTemp` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `dni` int(11) DEFAULT NULL,
  `telefono` int(11) NOT NULL,
  `direccion` varchar(20) NOT NULL,
  `fechaRegistro` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuotas`
--

CREATE TABLE `cuotas` (
  `idCuota` int(11) NOT NULL,
  `montoCuota` float(8,2) NOT NULL,
  `fechaCuota` date NOT NULL,
  `estado` tinyint(1) NOT NULL,
  `idPrestamo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detallefactura`
--

CREATE TABLE `detallefactura` (
  `idDetalleFactura` int(11) NOT NULL,
  `idVenta` int(11) NOT NULL,
  `codArticulo` int(11) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `precio_venta` decimal(8,2) NOT NULL,
  `Medio_Pago` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_inversion`
--

CREATE TABLE `detalle_inversion` (
  `IdDetalleInversion` int(11) NOT NULL,
  `IdInversor` int(11) NOT NULL,
  `IdInversion` int(11) NOT NULL,
  `actividad` varchar(50) DEFAULT NULL,
  `monto` double NOT NULL,
  `fecha` date NOT NULL,
  `MedioPago` varchar(20) NOT NULL,
  `Descripcion` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_salida_stock`
--

CREATE TABLE `detalle_salida_stock` (
  `idDetalleStockArticulos` int(11) NOT NULL,
  `idArticulo` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_temp`
--

CREATE TABLE `detalle_temp` (
  `correlativo` int(11) NOT NULL,
  `codArticulo` int(11) NOT NULL,
  `nombreArticulo` varchar(50) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `Precio_Compra` decimal(8,2) NOT NULL,
  `precio_venta` decimal(8,2) NOT NULL,
  `Ganancias` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta_articulos`
--

CREATE TABLE `detalle_venta_articulos` (
  `IdDetalle_Venta_Articulo` int(11) NOT NULL,
  `Cod_Venta` int(11) NOT NULL,
  `Cod_Articulo` int(11) NOT NULL,
  `nombreArticulo` varchar(50) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `Precio_Compra` decimal(8,2) NOT NULL,
  `Precio_Venta` decimal(8,2) NOT NULL,
  `Ganancias` decimal(8,2) NOT NULL,
  `Total` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `IdEmpleado` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Dni` int(11) NOT NULL,
  `Direccion` varchar(50) NOT NULL,
  `Telefono` int(11) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Usuario` varchar(20) NOT NULL,
  `Clave` varchar(300) NOT NULL,
  `Rol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`IdEmpleado`, `Nombre`, `Dni`, `Direccion`, `Telefono`, `Email`, `Usuario`, `Clave`, `Rol`) VALUES
(1, 'Roani Campos', 76805393, 'Jr. Los Geranios 345', 947629917, 'pachacutec.technology@gmail.com', 'admin', '$2y$10$ltrzWNbcJZT/dAt/4rcIHe/QK8j86mbLn2MlPnSfm80iu0hjSxUeK', 1),
(13, 'Karol Gutierrez', 75652456, 'Baños del inca', 926456459, 'gutierrezalvarezkarol@gmail.com', 'karol', '$2y$10$hO4yeGpeeZBtQFOWNsWfJey//r1bp0z1V7ajgno37J7/aIhCt6jwy', 2),
(15, 'Janel Campos', 76805393, 'Jr. Los Geranios 345', 947629917, 'jcamposg15@unc.edu.pe', 'janel', '$2y$10$Yz.cs6PMe1v6tNK1/PWusOOHtrndFWk8ttdSutPRghG/t4BWVt8s.', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `idGastos` int(11) NOT NULL,
  `montoGasto` double NOT NULL,
  `fechaGasto` date DEFAULT NULL,
  `medioPago` varchar(15) NOT NULL,
  `tipoGasto` varchar(15) NOT NULL,
  `descripcion` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inversiones`
--

CREATE TABLE `inversiones` (
  `IdInversion` int(11) NOT NULL,
  `Fecha` date NOT NULL,
  `IdEmpleado` int(11) NOT NULL,
  `IdInversor` int(11) NOT NULL,
  `Monto` float(8,2) NOT NULL,
  `montoDevuelto` float(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inversores`
--

CREATE TABLE `inversores` (
  `IdInversor` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Dni` int(11) NOT NULL,
  `Telefono` int(11) NOT NULL,
  `Direccion` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--

CREATE TABLE `prestamos` (
  `idPrestamo` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `monto` float(8,2) NOT NULL,
  `montoPagar` float(8,2) NOT NULL,
  `fechaPrestamo` date NOT NULL,
  `cuotas` int(11) NOT NULL,
  `montoCuota` float(8,2) NOT NULL,
  `fechaCuota` date NOT NULL,
  `estado` tinyint(1) NOT NULL,
  `descripcion` varchar(50) NOT NULL,
  `idEmpleado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `IdProveedor` int(11) NOT NULL,
  `ruc` int(20) DEFAULT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Direccion` varchar(50) DEFAULT NULL,
  `Telefono` int(11) NOT NULL,
  `Email` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `IdRol` int(11) NOT NULL,
  `rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`IdRol`, `rol`) VALUES
(1, 'Administrador'),
(2, 'Vendedor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventalibre`
--

CREATE TABLE `ventalibre` (
  `idVentaLibre` int(11) NOT NULL,
  `montoVentaLibre` double NOT NULL,
  `fechaVentaLibre` date NOT NULL,
  `metodoPago` varchar(12) NOT NULL,
  `tipoIngreso` varchar(15) NOT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `idEmpleado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `IdVenta` int(11) NOT NULL,
  `Fecha` datetime NOT NULL,
  `Cod_Caja` int(11) NOT NULL,
  `dniCliente` int(11) NOT NULL,
  `Medio_Pago` varchar(50) NOT NULL,
  `efectivo` float DEFAULT NULL,
  `tarjeta` float DEFAULT NULL,
  `Total` decimal(8,2) NOT NULL,
  `utilidad` decimal(8,2) NOT NULL,
  `Estado` varchar(11) NOT NULL,
  `saldo` double DEFAULT NULL,
  `vuelto` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas_inversor`
--

CREATE TABLE `ventas_inversor` (
  `idVentaInversor` int(11) NOT NULL,
  `idInversor` int(11) NOT NULL,
  `ventaTotalCapital` decimal(8,2) NOT NULL,
  `montoReinvertidoCapital` decimal(8,2) NOT NULL,
  `montoPorInvertir` decimal(8,2) NOT NULL,
  `utilidadTotal` decimal(8,2) NOT NULL,
  `P_ganancia` decimal(8,2) NOT NULL,
  `utilidadPagada` decimal(8,2) NOT NULL,
  `utilidadPorPagar` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `articulos`
--
ALTER TABLE `articulos`
  ADD PRIMARY KEY (`IdArticulo`),
  ADD KEY `Cod_Proveedor` (`Cod_Proveedor`),
  ADD KEY `fk_inversor` (`Cod_Inversor`);

--
-- Indices de la tabla `caja`
--
ALTER TABLE `caja`
  ADD PRIMARY KEY (`IdCaja`),
  ADD KEY `Cod_Empleado` (`Cod_Empleado`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`Id_Cliente`);

--
-- Indices de la tabla `cliente_temp`
--
ALTER TABLE `cliente_temp`
  ADD PRIMARY KEY (`idClienteTemp`);

--
-- Indices de la tabla `cuotas`
--
ALTER TABLE `cuotas`
  ADD PRIMARY KEY (`idCuota`),
  ADD KEY `fk_prestamo` (`idPrestamo`);

--
-- Indices de la tabla `detallefactura`
--
ALTER TABLE `detallefactura`
  ADD PRIMARY KEY (`idDetalleFactura`),
  ADD KEY `fk_venta` (`idVenta`);

--
-- Indices de la tabla `detalle_inversion`
--
ALTER TABLE `detalle_inversion`
  ADD PRIMARY KEY (`IdDetalleInversion`);

--
-- Indices de la tabla `detalle_salida_stock`
--
ALTER TABLE `detalle_salida_stock`
  ADD PRIMARY KEY (`idDetalleStockArticulos`);

--
-- Indices de la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `codArticulo` (`codArticulo`);

--
-- Indices de la tabla `detalle_venta_articulos`
--
ALTER TABLE `detalle_venta_articulos`
  ADD PRIMARY KEY (`IdDetalle_Venta_Articulo`),
  ADD KEY `fk_relacion` (`Cod_Venta`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`IdEmpleado`),
  ADD KEY `Rol` (`Rol`);

--
-- Indices de la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`idGastos`);

--
-- Indices de la tabla `inversiones`
--
ALTER TABLE `inversiones`
  ADD PRIMARY KEY (`IdInversion`),
  ADD KEY `fk_empleado1` (`IdEmpleado`);

--
-- Indices de la tabla `inversores`
--
ALTER TABLE `inversores`
  ADD PRIMARY KEY (`IdInversor`);

--
-- Indices de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`idPrestamo`),
  ADD KEY `fk_empleado_3` (`idEmpleado`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`IdProveedor`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`IdRol`);

--
-- Indices de la tabla `ventalibre`
--
ALTER TABLE `ventalibre`
  ADD PRIMARY KEY (`idVentaLibre`),
  ADD KEY `fk_empleado` (`idEmpleado`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`IdVenta`),
  ADD KEY `Cod_Caja` (`Cod_Caja`);

--
-- Indices de la tabla `ventas_inversor`
--
ALTER TABLE `ventas_inversor`
  ADD PRIMARY KEY (`idVentaInversor`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `articulos`
--
ALTER TABLE `articulos`
  MODIFY `IdArticulo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `caja`
--
ALTER TABLE `caja`
  MODIFY `IdCaja` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `Id_Cliente` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cliente_temp`
--
ALTER TABLE `cliente_temp`
  MODIFY `idClienteTemp` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cuotas`
--
ALTER TABLE `cuotas`
  MODIFY `idCuota` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detallefactura`
--
ALTER TABLE `detallefactura`
  MODIFY `idDetalleFactura` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_inversion`
--
ALTER TABLE `detalle_inversion`
  MODIFY `IdDetalleInversion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_salida_stock`
--
ALTER TABLE `detalle_salida_stock`
  MODIFY `idDetalleStockArticulos` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  MODIFY `correlativo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_venta_articulos`
--
ALTER TABLE `detalle_venta_articulos`
  MODIFY `IdDetalle_Venta_Articulo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `IdEmpleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `idGastos` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inversiones`
--
ALTER TABLE `inversiones`
  MODIFY `IdInversion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inversores`
--
ALTER TABLE `inversores`
  MODIFY `IdInversor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `idPrestamo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `IdProveedor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `IdRol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ventalibre`
--
ALTER TABLE `ventalibre`
  MODIFY `idVentaLibre` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `IdVenta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ventas_inversor`
--
ALTER TABLE `ventas_inversor`
  MODIFY `idVentaInversor` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `articulos`
--
ALTER TABLE `articulos`
  ADD CONSTRAINT `fk_inversor` FOREIGN KEY (`Cod_Inversor`) REFERENCES `inversores` (`IdInversor`);

--
-- Filtros para la tabla `cuotas`
--
ALTER TABLE `cuotas`
  ADD CONSTRAINT `fk_prestamo` FOREIGN KEY (`idPrestamo`) REFERENCES `prestamos` (`idPrestamo`);

--
-- Filtros para la tabla `detallefactura`
--
ALTER TABLE `detallefactura`
  ADD CONSTRAINT `fk_venta` FOREIGN KEY (`idVenta`) REFERENCES `ventas` (`IdVenta`);

--
-- Filtros para la tabla `detalle_venta_articulos`
--
ALTER TABLE `detalle_venta_articulos`
  ADD CONSTRAINT `fk_relacion` FOREIGN KEY (`Cod_Venta`) REFERENCES `ventas` (`IdVenta`);

--
-- Filtros para la tabla `inversiones`
--
ALTER TABLE `inversiones`
  ADD CONSTRAINT `fk_empleado1` FOREIGN KEY (`IdEmpleado`) REFERENCES `empleados` (`IdEmpleado`);

--
-- Filtros para la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `fk_empleado_3` FOREIGN KEY (`idEmpleado`) REFERENCES `empleados` (`IdEmpleado`);

--
-- Filtros para la tabla `ventalibre`
--
ALTER TABLE `ventalibre`
  ADD CONSTRAINT `fk_empleado` FOREIGN KEY (`idEmpleado`) REFERENCES `empleados` (`IdEmpleado`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
