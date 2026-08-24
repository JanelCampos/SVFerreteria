-- ============================================================
-- Migracion Ferreteria SVPachacutec - Fase 6 (Cotizaciones)
-- Fecha: 2026-08-12
-- Objetivo: tabla temporal de detalle para cotizaciones
-- ============================================================

START TRANSACTION;
SET FOREIGN_KEY_CHECKS = 0;

-- Tabla temporal de detalle de cotizacion (equivalente a detalle_temp pero para cotizaciones)
-- El Cod_SesionCotizacion identifica el formulario en edicion (0 = nueva).
CREATE TABLE IF NOT EXISTS `detalle_cotizacion_temp` (
  `correlativo` int(11) NOT NULL AUTO_INCREMENT,
  `Cod_SesionCotizacion` int(11) NOT NULL DEFAULT 0,
  `Cod_Empleado` int(11) DEFAULT NULL,
  `codArticulo` int(11) NOT NULL,
  `nombreArticulo` varchar(100) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `Precio_Compra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_venta` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Ganancias` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Unidad` varchar(30) DEFAULT NULL,
  `FactorAplicado` decimal(10,4) DEFAULT 1.0000,
  `PorcentajeDescuento` decimal(5,2) NOT NULL DEFAULT 0.00,
  `PrecioConDescuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`correlativo`),
  KEY `idx_detalle_cot_temp_sesion` (`Cod_SesionCotizacion`),
  KEY `idx_detalle_cot_temp_empleado` (`Cod_Empleado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
