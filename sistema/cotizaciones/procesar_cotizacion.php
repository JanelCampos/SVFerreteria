<?php
    session_start();
    include "../../conexion.php";
    header('Content-Type: application/json');

    if (empty($_SESSION['active']) || ($_SESSION['rol'] != 1 && $_SESSION['rol'] != 2)) {
        echo json_encode(['resultado' => false, 'mensaje' => 'Acceso no autorizado']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $codEmpleado = $_SESSION['idUser'];

    $dniCliente = isset($input['dniCliente']) ? trim($input['dniCliente']) : '';
    $nombreCliente = isset($input['nombreCliente']) ? trim($input['nombreCliente']) : '';
    $direccionCliente = isset($input['direccionCliente']) ? trim($input['direccionCliente']) : '';
    $telefonoCliente = isset($input['telefonoCliente']) ? trim($input['telefonoCliente']) : '';
    $fechaVigencia = isset($input['fechaVigencia']) ? trim($input['fechaVigencia']) : date('Y-m-d', strtotime('+7 days'));
    $observaciones = isset($input['observaciones']) ? trim($input['observaciones']) : '';

    if (empty($dniCliente) || empty($nombreCliente)) {
        echo json_encode(['resultado' => false, 'mensaje' => 'DNI y nombre del cliente son obligatorios']);
        exit;
    }

    $idCliente = null;

    $queryBuscarCliente = null;
    $tablaClientes = 'clientes';
    $colDni = 'Dni';
    $colNombre = 'Nombre';
    $colDireccion = 'direccion';
    $colTelefono = 'Telefono';
    $colId = 'Id_Cliente';
    $colFecha = 'Fecha_Registro';

    $queryCheck = $conexionDB->prepare("SELECT 1 FROM clientes LIMIT 1");
    if (!$queryCheck) {
        $tablaClientes = 'cliente';
        $colDni = 'dniCliente';
        $colNombre = 'nombreCliente';
        $colDireccion = 'direccionCliente';
        $colTelefono = 'telefonoCliente';
        $colId = 'IdCliente';
        $colFecha = 'fechaRegistroCliente';
    } else {
        $queryCheck->close();
    }

    $sqlBuscar = "SELECT $colId FROM $tablaClientes WHERE $colDni = ? LIMIT 1";
    $queryBuscarCliente = $conexionDB->prepare($sqlBuscar);
    if ($queryBuscarCliente) {
        $queryBuscarCliente->bind_param("s", $dniCliente);
        if ($queryBuscarCliente->execute()) {
            $res = $queryBuscarCliente->get_result();
            if ($res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $idCliente = intval($row[$colId]);
            }
        }
        $queryBuscarCliente->close();
    }

    if ($idCliente === null) {
        $fechaReg = date('Y-m-d');
        $sqlInsert = "INSERT INTO $tablaClientes ($colDni, $colNombre, $colDireccion, $colTelefono, $colFecha) VALUES (?, ?, ?, ?, ?)";
        $queryInsertCliente = $conexionDB->prepare($sqlInsert);
        if ($queryInsertCliente) {
            $queryInsertCliente->bind_param("sssss", $dniCliente, $nombreCliente, $direccionCliente, $telefonoCliente, $fechaReg);
            if ($queryInsertCliente->execute()) {
                $idCliente = $queryInsertCliente->insert_id;
            }
            $queryInsertCliente->close();
        }

        if ($idCliente === null) {
            $sqlInsertFallback = "INSERT INTO $tablaClientes ($colDni, $colNombre, $colDireccion, $colTelefono) VALUES (?, ?, ?, ?)";
            $queryInsertFb = $conexionDB->prepare($sqlInsertFallback);
            if ($queryInsertFb) {
                $queryInsertFb->bind_param("ssss", $dniCliente, $nombreCliente, $direccionCliente, $telefonoCliente);
                if ($queryInsertFb->execute()) {
                    $idCliente = $queryInsertFb->insert_id;
                }
                $queryInsertFb->close();
            }
        }
    }

    if ($idCliente === null) {
        echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo registrar o encontrar el cliente']);
        exit;
    }

    $detalle = [];
    $tablaTempExiste = false;
    $sqlCheckTemp = "SELECT 1 FROM detalle_cotizacion_temp LIMIT 1";
    $queryCheckTemp = $conexionDB->prepare($sqlCheckTemp);
    if ($queryCheckTemp) {
        if ($queryCheckTemp->execute()) {
            $queryCheckTemp->store_result();
            if ($queryCheckTemp->num_rows !== null) {
                $tablaTempExiste = true;
            }
        }
        $queryCheckTemp->close();
    }

    if ($tablaTempExiste) {
        $sqlTemp = "SELECT * FROM detalle_cotizacion_temp WHERE Cod_Empleado = ?";
        $queryTemp = $conexionDB->prepare($sqlTemp);
        if ($queryTemp) {
            $queryTemp->bind_param("i", $codEmpleado);
            if ($queryTemp->execute()) {
                $resTemp = $queryTemp->get_result();
                if ($resTemp->num_rows > 0) {
                    while ($row = $resTemp->fetch_assoc()) {
                        $detalle[] = $row;
                    }
                }
            }
            $queryTemp->close();
        }
    }

    if (count($detalle) === 0 && isset($input['detalle']) && is_array($input['detalle'])) {
        $detalle = $input['detalle'];
    }

    if (count($detalle) === 0) {
        echo json_encode(['resultado' => false, 'mensaje' => 'Sin artículos']);
        exit;
    }

    $subTotal = 0;
    foreach ($detalle as $item) {
        $precioConDto = floatval(isset($item['PrecioConDescuento']) && $item['PrecioConDescuento'] > 0 ? $item['PrecioConDescuento'] : (isset($item['precio_venta']) ? $item['precio_venta'] : 0));
        $cantidad = floatval(isset($item['cantidad']) ? $item['cantidad'] : (isset($item['Cantidad']) ? $item['Cantidad'] : 0));
        $subTotal += $precioConDto * $cantidad;
    }
    $total = $subTotal;

    $idCotizacion = null;
    $estado = 'vigente';
    $sqlInsertCot = "INSERT INTO cotizaciones (Fecha, Cod_Cliente, Cod_Empleado, SubTotal, Total, Estado, VigenciaHasta, Observaciones) VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?)";
    $queryInsertCot = $conexionDB->prepare($sqlInsertCot);
    if ($queryInsertCot) {
        $queryInsertCot->bind_param("iidddss", $idCliente, $codEmpleado, $subTotal, $total, $estado, $fechaVigencia, $observaciones);
        if ($queryInsertCot->execute()) {
            $idCotizacion = $queryInsertCot->insert_id;
        }
        $queryInsertCot->close();
    }

    if ($idCotizacion === null) {
        echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo registrar la cotización']);
        exit;
    }

    $sqlInsertDet = "INSERT INTO detalle_cotizacion (Cod_Cotizacion, Cod_Articulo, NombreArticulo, Cantidad, PrecioUnitario, PorcentajeDescuento, PrecioConDescuento, SubTotal, Unidad, FactorAplicado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $queryInsertDet = $conexionDB->prepare($sqlInsertDet);
    if ($queryInsertDet) {
        foreach ($detalle as $item) {
            $codArticulo = intval(isset($item['codArticulo']) ? $item['codArticulo'] : (isset($item['Cod_Articulo']) ? $item['Cod_Articulo'] : (isset($item['IdArticulo']) ? $item['IdArticulo'] : 0)));
            $nombreArt = isset($item['nombreArticulo']) ? $item['nombreArticulo'] : (isset($item['NombreArticulo']) ? $item['NombreArticulo'] : '');
            $cantidad = floatval(isset($item['cantidad']) ? $item['cantidad'] : (isset($item['Cantidad']) ? $item['Cantidad'] : 0));
            $precioUnitario = floatval(isset($item['precio_venta']) ? $item['precio_venta'] : (isset($item['PrecioUnitario']) ? $item['PrecioUnitario'] : 0));
            $porcentajeDto = floatval(isset($item['PorcentajeDescuento']) ? $item['PorcentajeDescuento'] : 0);
            $precioConDto = floatval(isset($item['PrecioConDescuento']) && $item['PrecioConDescuento'] > 0 ? $item['PrecioConDescuento'] : $precioUnitario);
            $subTotalDet = $precioConDto * $cantidad;
            $unidad = isset($item['Unidad']) ? $item['Unidad'] : (isset($item['unidad']) ? $item['unidad'] : '-');
            $factorAplicado = floatval(isset($item['FactorAplicado']) ? $item['FactorAplicado'] : 1);

            $queryInsertDet->bind_param("iisdddddsd", $idCotizacion, $codArticulo, $nombreArt, $cantidad, $precioUnitario, $porcentajeDto, $precioConDto, $subTotalDet, $unidad, $factorAplicado);
            $queryInsertDet->execute();
        }
        $queryInsertDet->close();
    }

    if ($tablaTempExiste) {
        $sqlLimpiarTemp = "DELETE FROM detalle_cotizacion_temp WHERE Cod_Empleado = ?";
        $queryLimpiarTemp = $conexionDB->prepare($sqlLimpiarTemp);
        if ($queryLimpiarTemp) {
            $queryLimpiarTemp->bind_param("i", $codEmpleado);
            $queryLimpiarTemp->execute();
            $queryLimpiarTemp->close();
        }
    }

    echo json_encode([
        'resultado' => true,
        'idCotizacion' => $idCotizacion,
        'mensaje' => 'Cotización guardada'
    ]);
?>
