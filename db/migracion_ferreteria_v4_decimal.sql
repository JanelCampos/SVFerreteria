-- Migracion v4 fix: solo MODIFY + columnas que NO existen (evita duplicar FactorAplicado)
ALTER TABLE detalle_temp
  MODIFY COLUMN cantidad DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  ADD COLUMN CantidadOriginal DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER cantidad,
  ADD COLUMN UnidadOriginal VARCHAR(30) NULL AFTER CantidadOriginal,
  ADD COLUMN UnidadBase VARCHAR(30) NULL AFTER FactorAplicado;

ALTER TABLE detalle_venta_articulos
  MODIFY COLUMN Cantidad DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  ADD COLUMN CantidadOriginal DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER Cantidad,
  ADD COLUMN UnidadOriginal VARCHAR(30) NULL AFTER CantidadOriginal,
  ADD COLUMN UnidadBase VARCHAR(30) NULL AFTER FactorAplicado;

ALTER TABLE detallefactura
  MODIFY COLUMN Cantidad DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  ADD COLUMN CantidadOriginal DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER Cantidad,
  ADD COLUMN UnidadOriginal VARCHAR(30) NULL AFTER CantidadOriginal,
  ADD COLUMN UnidadBase VARCHAR(30) NULL AFTER FactorAplicado;

UPDATE detalle_temp SET CantidadOriginal = cantidad WHERE CantidadOriginal = 0 AND cantidad > 0;
UPDATE detalle_venta_articulos SET CantidadOriginal = Cantidad WHERE CantidadOriginal = 0 AND Cantidad > 0;
UPDATE detallefactura SET CantidadOriginal = Cantidad WHERE CantidadOriginal = 0 AND Cantidad > 0;

ALTER TABLE detalle_cotizacion_temp
  MODIFY COLUMN cantidad DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  ADD COLUMN CantidadOriginal DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER cantidad,
  ADD COLUMN UnidadOriginal VARCHAR(30) NULL AFTER CantidadOriginal,
  ADD COLUMN UnidadBase VARCHAR(30) NULL AFTER FactorAplicado;

UPDATE detalle_cotizacion_temp SET CantidadOriginal = cantidad WHERE CantidadOriginal = 0 AND cantidad > 0;
