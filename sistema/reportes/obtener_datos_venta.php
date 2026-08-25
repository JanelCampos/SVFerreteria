<?php
function obtenerDatosVenta($idVenta) {
    // Conexión a la base de datos
    include ("../../conexion.php");

    // Consulta SQL para obtener los datos de la venta
    $sql = "SELECT 
                v.IdVenta, 
                v.Fecha, 
                c.Nombre AS cliente, 
                dva.Cod_articulo, 
                dva.nombreArticulo as Nombre, 
                dva.Cantidad, 
                dva.precio_venta, 
                v.Total, 
                e.Nombre AS vendedor, 
                v.saldo, 
                v.vuelto, 
                (v.efectivo + v.tarjeta) as importeTotal, 
                dva.Unidad as Unidad,
                dva.Total as totalVentaArticulo,
                dva.PorcentajeDescuento as porcentajeDescuento,
                dva.PrecioConDescuento as precioConDescuento
            FROM ventas v
            INNER JOIN clientes c ON v.dniCliente = c.Dni
            INNER JOIN caja ca ON ca.IdCaja = v.Cod_caja 
            INNER JOIN empleados e ON ca.Cod_Empleado = e.IdEmpleado
            INNER JOIN detalle_venta_articulos dva ON dva.Cod_Venta = v.IdVenta
            WHERE v.IdVenta = ?";

    $stmt = $conexionDB->prepare($sql);
    $stmt->bind_param("i", $idVenta);
    $stmt->execute();
    $result = $stmt->get_result();

    $datosVenta = [];
    while ($row = $result->fetch_assoc()) {
        $datosVenta[] = $row;
    }

    $stmt->close();
    $conexionDB->close();

    return $datosVenta;
}
?>
