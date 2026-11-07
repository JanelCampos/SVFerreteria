-- El indice no es UNIQUE porque existen RUC duplicados historicos que deben verificarse.
ALTER TABLE `proveedores`
  MODIFY `ruc` varchar(20) DEFAULT NULL,
  MODIFY `Telefono` varchar(20) DEFAULT NULL,
  ADD KEY `idx_proveedores_ruc` (`ruc`);
