<?php 

    $query = mysqli_query($conexionDB,"SELECT *, a.Nombre as nombreProducto, a.Cantidad, a.Precio_Compra,
                                        p.Nombre as nombreProveedor
                                        FROM articulos a
                                        INNER JOIN proveedores p ON p.IdProveedor = a.Cod_Proveedor
                                        WHERE IdArticulo = $idArticulo");
?>
