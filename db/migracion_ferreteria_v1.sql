-- ============================================================
-- Migracion Ferreteria SVPachacutec - Fase 1
-- Fecha: 2026-08-11
-- Objetivo: nueva estructura para categorias, UDM, precio minimo,
-- descuentos escalonados, auditoria login, cotizaciones, alertas,
-- y retiro del modulo de inversores.
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. NUEVAS TABLAS INDEPENDIENTES
-- ============================================================

-- Tabla de categorias de articulos (Req 2)
CREATE TABLE IF NOT EXISTS `categorias` (
  `IdCategoria` int(11) NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(80) NOT NULL,
  `Descripcion` varchar(200) DEFAULT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`IdCategoria`),
  UNIQUE KEY `uk_categoria_nombre` (`Nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de auditoria de login (Req 5)
CREATE TABLE IF NOT EXISTS `auditoria_login` (
  `IdAuditoria` bigint(20) NOT NULL AUTO_INCREMENT,
  `Cod_Empleado` int(11) DEFAULT NULL,
  `FechaHora` datetime NOT NULL DEFAULT current_timestamp(),
  `IP` varchar(45) DEFAULT NULL,
  `UserAgent` varchar(255) DEFAULT NULL,
  `Dispositivo` varchar(100) DEFAULT NULL,
  `Exito` tinyint(1) NOT NULL DEFAULT 0,
  `MotivoFallo` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`IdAuditoria`),
  KEY `idx_auditoria_empleado` (`Cod_Empleado`),
  KEY `idx_auditoria_fecha` (`FechaHora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de alertas del sistema (Req 1)
CREATE TABLE IF NOT EXISTS `alertas_sistema` (
  `IdAlerta` bigint(20) NOT NULL AUTO_INCREMENT,
  `Tipo` enum('stock_bajo','cuota_vencida','otro') NOT NULL,
  `IdReferencia` int(11) DEFAULT NULL,
  `Mensaje` text NOT NULL,
  `FechaGeneracion` datetime NOT NULL DEFAULT current_timestamp(),
  `Leida` tinyint(1) NOT NULL DEFAULT 0,
  `FechaLectura` datetime DEFAULT NULL,
  PRIMARY KEY (`IdAlerta`),
  KEY `idx_alertas_tipo` (`Tipo`),
  KEY `idx_alertas_leida` (`Leida`),
  KEY `idx_alertas_fecha` (`FechaGeneracion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de cotizaciones (Req 6)
CREATE TABLE IF NOT EXISTS `cotizaciones` (
  `IdCotizacion` int(11) NOT NULL AUTO_INCREMENT,
  `Fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `Cod_Cliente` int(11) NOT NULL,
  `Cod_Empleado` int(11) NOT NULL,
  `SubTotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Estado` enum('vigente','aprobada','anulada','vencida') NOT NULL DEFAULT 'vigente',
  `VigenciaHasta` date DEFAULT NULL,
  `Observaciones` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`IdCotizacion`),
  KEY `idx_cotizacion_cliente` (`Cod_Cliente`),
  KEY `idx_cotizacion_empleado` (`Cod_Empleado`),
  KEY `idx_cotizacion_fecha` (`Fecha`),
  KEY `idx_cotizacion_estado` (`Estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Detalle de cotizaciones (Req 6)
CREATE TABLE IF NOT EXISTS `detalle_cotizacion` (
  `IdDetalle` bigint(20) NOT NULL AUTO_INCREMENT,
  `Cod_Cotizacion` int(11) NOT NULL,
  `Cod_Articulo` int(11) NOT NULL,
  `NombreArticulo` varchar(100) NOT NULL,
  `Cantidad` decimal(10,2) NOT NULL,
  `PrecioUnitario` decimal(10,2) NOT NULL,
  `PorcentajeDescuento` decimal(5,2) NOT NULL DEFAULT 0.00,
  `PrecioConDescuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `SubTotal` decimal(10,2) NOT NULL,
  `Unidad` varchar(30) DEFAULT NULL,
  `FactorAplicado` decimal(10,4) DEFAULT 1.0000,
  PRIMARY KEY (`IdDetalle`),
  KEY `idx_detalle_cotizacion` (`Cod_Cotizacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. AMPLIACION DE LA TABLA ARTICULOS
-- ============================================================

-- Agregar columna Cod_Categoria (Req 2)
ALTER TABLE `articulos`
  ADD COLUMN `Cod_Categoria` int(11) DEFAULT NULL AFTER `Nombre`,
  ADD KEY `idx_articulo_categoria` (`Cod_Categoria`);

-- Agregar precio minimo (Req 7)
ALTER TABLE `articulos`
  ADD COLUMN `Precio_Minimo` decimal(8,2) NOT NULL DEFAULT 0.00 AFTER `Precio_Unitario`;

-- Agregar unidad base (Req 3)
ALTER TABLE `articulos`
  ADD COLUMN `Unidad_Base` varchar(30) NOT NULL DEFAULT 'unidad' AFTER `Precio_Minimo`;

-- Agregar stock de alerta (Req 1, configurable por articulo)
ALTER TABLE `articulos`
  ADD COLUMN `Stock_Alerta` int(11) NOT NULL DEFAULT 5 AFTER `Cantidad`;

-- ============================================================
-- 3. TABLAS DEPENDIENTES DE ARTICULOS (Req 3 + Req 8)
-- ============================================================

-- Unidades de medida variables por articulo (Req 3)
CREATE TABLE IF NOT EXISTS `articulo_unidades` (
  `IdUnidad` int(11) NOT NULL AUTO_INCREMENT,
  `Cod_Articulo` int(11) NOT NULL,
  `Unidad` varchar(30) NOT NULL,
  `FactorEquivalencia` decimal(10,4) NOT NULL DEFAULT 1.0000,
  `PrecioVenta` decimal(8,2) NOT NULL DEFAULT 0.00,
  `EsPredeterminada` tinyint(1) NOT NULL DEFAULT 0,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`IdUnidad`),
  KEY `idx_unidades_articulo` (`Cod_Articulo`),
  CONSTRAINT `fk_unidades_articulo` FOREIGN KEY (`Cod_Articulo`) REFERENCES `articulos` (`IdArticulo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Descuentos escalonados por cantidad (Req 8)
CREATE TABLE IF NOT EXISTS `articulo_descuentos_cantidad` (
  `IdDescuento` int(11) NOT NULL AUTO_INCREMENT,
  `Cod_Articulo` int(11) NOT NULL,
  `CantidadMinima` int(11) NOT NULL,
  `PorcentajeDescuento` decimal(5,2) NOT NULL,
  `FechaCreacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`IdDescuento`),
  KEY `idx_descuentos_articulo` (`Cod_Articulo`),
  CONSTRAINT `fk_descuentos_articulo` FOREIGN KEY (`Cod_Articulo`) REFERENCES `articulos` (`IdArticulo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. AMPLIACION DE TABLAS DE DETALLE DE VENTAS
--    (UDM, factor, descuento aplicado - Req 3 + 8)
-- ============================================================

ALTER TABLE `detalle_temp`
  ADD COLUMN `Unidad` varchar(30) DEFAULT NULL AFTER `Ganancias`,
  ADD COLUMN `FactorAplicado` decimal(10,4) DEFAULT 1.0000 AFTER `Unidad`,
  ADD COLUMN `PorcentajeDescuento` decimal(5,2) NOT NULL DEFAULT 0.00 AFTER `FactorAplicado`,
  ADD COLUMN `PrecioConDescuento` decimal(8,2) NOT NULL DEFAULT 0.00 AFTER `PorcentajeDescuento`;

ALTER TABLE `detalle_venta_articulos`
  ADD COLUMN `Unidad` varchar(30) DEFAULT NULL AFTER `Total`,
  ADD COLUMN `FactorAplicado` decimal(10,4) DEFAULT 1.0000 AFTER `Unidad`,
  ADD COLUMN `PorcentajeDescuento` decimal(5,2) NOT NULL DEFAULT 0.00 AFTER `FactorAplicado`,
  ADD COLUMN `PrecioConDescuento` decimal(8,2) NOT NULL DEFAULT 0.00 AFTER `PorcentajeDescuento`;

ALTER TABLE `detallefactura`
  ADD COLUMN `Unidad` varchar(30) DEFAULT NULL AFTER `Medio_Pago`,
  ADD COLUMN `FactorAplicado` decimal(10,4) DEFAULT 1.0000 AFTER `Unidad`,
  ADD COLUMN `PorcentajeDescuento` decimal(5,2) NOT NULL DEFAULT 0.00 AFTER `FactorAplicado`,
  ADD COLUMN `PrecioConDescuento` decimal(8,2) NOT NULL DEFAULT 0.00 AFTER `PorcentajeDescuento`;

-- ============================================================
-- 5. AMPLIACION DE VENTAS PARA REGISTRAR VENDEDOR (Req 4)
-- ============================================================

ALTER TABLE `ventas`
  ADD COLUMN `Cod_Vendedor` int(11) DEFAULT NULL AFTER `Cod_Caja`,
  ADD KEY `idx_ventas_vendedor` (`Cod_Vendedor`);

-- ============================================================
-- 6. RETIRO DEL MODULO DE INVERSORES (Req 9)
--    Orden: FKs -> columnas FK -> tablas dependientes -> tabla padre
-- ============================================================

-- 6.1 Quitar FK fk_inversor de articulos (si existe)
-- Nota: INFORMATION_SCHEMA se usa para detectar el nombre real del constraint
SET @fk_existe_fk_inversor := (
    SELECT CONSTRAINT_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'articulos'
      AND CONSTRAINT_NAME = 'fk_inversor'
    LIMIT 1
);
SET @sql_drop_fk := IF(@fk_existe_fk_inversor IS NOT NULL,
    'ALTER TABLE `articulos` DROP FOREIGN KEY `fk_inversor`',
    'SELECT 1');
PREPARE stmt FROM @sql_drop_fk; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 6.2 Quitar indice si existe
SET @idx_exists := (
    SELECT INDEX_NAME
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'articulos'
      AND INDEX_NAME = 'fk_inversor'
    LIMIT 1
);
SET @sql_drop_idx := IF(@idx_exists IS NOT NULL,
    'ALTER TABLE `articulos` DROP INDEX `fk_inversor`',
    'SELECT 1');
PREPARE stmt FROM @sql_drop_idx; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 6.3 Quitar columna Cod_Inversor de articulos
ALTER TABLE `articulos`
  DROP COLUMN `Cod_Inversor`;

-- 6.4 Eliminar tablas de inversor en orden correcto (hijos -> padre)
DROP TABLE IF EXISTS `detalle_inversion`;
DROP TABLE IF EXISTS `inversiones`;
DROP TABLE IF EXISTS `ventas_inversor`;
DROP TABLE IF EXISTS `inversores`;

-- ============================================================
-- 7. FKs de categorias despues de retirar inversor
-- ============================================================

ALTER TABLE `articulos`
  ADD CONSTRAINT `fk_articulo_categoria` FOREIGN KEY (`Cod_Categoria`) REFERENCES `categorias` (`IdCategoria`) ON DELETE SET NULL;

-- ============================================================
-- 8. DATOS INICIALES DE MIGRACION
-- ============================================================

-- Categoria por defecto
INSERT INTO `categorias` (`Nombre`, `Descripcion`, `Estado`) VALUES
('Sin categoria', 'Categoria por defecto', 1),
('Herramientas Manuales', 'Alicates, destornilladores, martillos, etc.', 1),
('Herramientas Electricas', 'Taladros, amoladoras, sierras, etc.', 1),
('Pinturas y Accesorios', 'Pinturas, brochas, rodillos', 1),
('Ferreteria General', 'Tornillos, tuercas, arandelas, etc.', 1),
('Material Electrico', 'Cables, enchufes, interruptores', 1),
('Material de Construccion', 'Cemento, arena, ladrillos, etc.', 1),
('Accesorios de Banio', 'Griferias, duchas, inodoros, etc.', 1),
('Accesorios de Cocina', 'Fregaderos, llaves, etc.', 1);

-- Asignar "Sin categoria" a articulos existentes
UPDATE `articulos` SET `Cod_Categoria` = 1 WHERE `Cod_Categoria` IS NULL;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
