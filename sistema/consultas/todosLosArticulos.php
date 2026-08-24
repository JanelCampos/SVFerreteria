<?php 
    $sql = "SELECT *,a.Nombre as nombreProducto, p.Nombre as nombreProveedor FROM articulos a
    INNER JOIN proveedores p ON p.IdProveedor = a.Cod_Proveedor";
?>
