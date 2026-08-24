-- ============================================================
-- Migracion Ferreteria SVPachacutec - Fase Extra UDM
-- Objetivo: columnas historicas (CantidadOriginal / UnidadOriginal / FactorAplicado)
--           + cambiar INT a DECIMAL(10,2) para stock, salidas, entradas y detalles
-- Fecha: 2026-08-14
-- ============================================================

START TRANSACTION;
SET FOREIGN_KEY_CHECKS = 0;

-- --------- TABLA articulos ---------
ALTER TABLE `articulos`
  MODIFY COLUMN `Cantidad` decimal(10,2) NOT NULL DEFAULT 0.00,
  MODIFY COLUMN `Stock_Alerta` decimal(10,2) NOT NULL DEFAULT 0.00;

-- --------- TABLA detalle_salida_stock (historial salidas manuales) ---------
ALTER TABLE `detalle_salida_stock`
  MODIFY COLUMN `cantidad` decimal(10,2) NOT NULL DEFAULT 0.00,
  ADD COLUMN `CantidadOriginal` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `cantidad`,
  ADD COLUMN `UnidadOriginal` varchar(30) DEFAULT NULL AFTER `CantidadOriginal`,
  ADD COLUMN `FactorAplicado` decimal(10,4) NOT NULL DEFAULT 1.0000 AFTER `UnidadOriginal`,
  ADD COLUMN `UnidadBase` varchar(30) DEFAULT NULL AFTER `FactorAplicado`;

-- --------- TABLA entrada_stock / si existe historico de compras ---------
-- Si no se tiene una tabla especifica, la informacion se obtiene desde articulos
-- y los logs. Las columnas en detalle_temp / detalle_venta_articulos / detallefactura
-- ya fueron creadas en migracion V1 (Unidad / FactorAplicado).

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
