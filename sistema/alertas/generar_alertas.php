<?php
session_start();
include "../../conexion.php";

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['active']) || $_SESSION['rol'] != 1) {
    echo json_encode(['resultado' => false, 'mensaje' => 'Acceso denegado']);
    exit;
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

if ($accion === 'run') {
    $alertasStock = 0;
    $alertasCuotas = 0;
    $nuevas = 0;

    // a) Stock bajo
    $sqlStock = "SELECT IdArticulo, Nombre, Cantidad, Stock_Alerta, Unidad_Presentacion
                 FROM articulos
                 WHERE Cantidad <= Stock_Alerta AND Stock_Alerta > 0";
    $resStock = mysqli_query($conexionDB, $sqlStock);

    if ($resStock) {
        while ($row = mysqli_fetch_assoc($resStock)) {
            $alertasStock++;
            $idArt = (int)$row['IdArticulo'];
            $nombre = mysqli_real_escape_string($conexionDB, $row['Nombre']);
            $cant = (int)$row['Cantidad'];
            $stockA = (int)$row['Stock_Alerta'];
            $udm = mysqli_real_escape_string($conexionDB, $row['Unidad_Presentacion']);

            $chk = mysqli_query($conexionDB, "SELECT IdAlerta FROM alertas_sistema
                                              WHERE Tipo='stock_bajo'
                                                AND IdReferencia=$idArt
                                                AND Leida=0
                                              LIMIT 1");
            if (!$chk || mysqli_num_rows($chk) == 0) {
                $msg = "Stock bajo: Artículo $nombre quedan $cant $udm (alerta ≤$stockA)";
                $ins = mysqli_query($conexionDB, "INSERT INTO alertas_sistema
                    (Tipo, IdReferencia, Mensaje, FechaGeneracion)
                    VALUES ('stock_bajo', $idArt, '$msg', NOW())");
                if ($ins) $nuevas++;
            }
        }
    }

    // b) Cuotas vencidas - fallback dinámico por INFORMATION_SCHEMA
    $cuotasEncontradas = false;
    $tablaCuotas = '';
    $tablaPrestamos = '';
    $colIdCuota = '';
    $colIdPrestamo = '';
    $colIdPrestamoRef = '';
    $colNumCuota = '';
    $colMonto = '';
    $colFechaVenc = '';
    $colEstado = '';
    $colEstadoValorPendiente = '';
    $colNombreCliente = '';
    $colDniCliente = '';

    $detectTablas = mysqli_query($conexionDB, "SELECT TABLE_NAME, COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND (TABLE_NAME IN ('prestamos_cuotas','cuotas','cuotas_prestamo','pagos_prestamo','lista_cuotas')
               OR TABLE_NAME IN ('prestamos','prestamo'))
        ORDER BY TABLE_NAME");
    $mapCols = [];
    if ($detectTablas) {
        while ($r = mysqli_fetch_assoc($detectTablas)) {
            $t = $r['TABLE_NAME'];
            if (!isset($mapCols[$t])) $mapCols[$t] = [];
            $mapCols[$t][] = strtolower($r['COLUMN_NAME']);
        }
    }

    // Fallback: nombres de tablas según exista
    // Opción A: prestamos_cuotas + prestamos
    if (isset($mapCols['prestamos_cuotas']) && isset($mapCols['prestamos'])) {
        $tablaCuotas = 'prestamos_cuotas';
        $tablaPrestamos = 'prestamos';
        $colsC = $mapCols['prestamos_cuotas'];
        $colsP = $mapCols['prestamos'];
        foreach ($colsC as $c) {
            if ($c === 'idcuota') $colIdCuota = 'IdCuota';
            elseif ($c === 'cod_prestamo' || $c === 'idprestamo') $colIdPrestamoRef = ($c==='cod_prestamo')?'Cod_Prestamo':'IdPrestamo';
            elseif ($c === 'numerocuota') $colNumCuota = 'NumeroCuota';
            elseif ($c === 'monto') $colMonto = 'Monto';
            elseif ($c === 'fechavencimiento' || $c === 'fechacuota') $colFechaVenc = ($c==='fechavencimiento')?'FechaVencimiento':'fechaCuota';
            elseif ($c === 'estado') $colEstado = 'Estado';
        }
        if (!$colIdCuota) $colIdCuota = 'IdCuota';
        if (!$colIdPrestamoRef) $colIdPrestamoRef = 'Cod_Prestamo';
        if (!$colNumCuota) $colNumCuota = 'NumeroCuota';
        if (!$colMonto) $colMonto = 'Monto';
        if (!$colFechaVenc) $colFechaVenc = 'FechaVencimiento';
        if (!$colEstado) $colEstado = 'Estado';
        $colEstadoValorPendiente = "'pendiente'";
        foreach ($colsP as $c) {
            if ($c === 'idprestamo') $colIdPrestamo = 'IdPrestamo';
            elseif ($c === 'nombrecliente' || $c === 'nombre') $colNombreCliente = ($c==='nombrecliente')?'NombreCliente':'nombre';
            elseif ($c === 'dnicliente' || $c === 'dni') $colDniCliente = ($c==='dnicliente')?'DniCliente':'dni';
        }
        if (!$colIdPrestamo) $colIdPrestamo = 'IdPrestamo';
        if (!$colNombreCliente) $colNombreCliente = 'NombreCliente';
        if (!$colDniCliente) $colDniCliente = 'DniCliente';
        $cuotasEncontradas = true;
    }
    // Opción B: cuotas + prestamos (estructura que existe actualmente en el sistema)
    elseif (isset($mapCols['cuotas']) && isset($mapCols['prestamos'])) {
        $tablaCuotas = 'cuotas';
        $tablaPrestamos = 'prestamos';
        $colIdCuota = 'idCuota';
        $colIdPrestamoRef = 'idPrestamo';
        $colNumCuota = 'idCuota';
        $colMonto = 'montoCuota';
        $colFechaVenc = 'fechaCuota';
        $colEstado = 'estado';
        $colEstadoValorPendiente = "0";
        $colIdPrestamo = 'idPrestamo';
        $colsP = $mapCols['prestamos'];
        $colNombreCliente = 'nombre';
        $colDniCliente = 'dni';
        foreach ($colsP as $c) {
            if ($c === 'nombrecliente') $colNombreCliente = 'NombreCliente';
            elseif ($c === 'dnicliente') $colDniCliente = 'DniCliente';
            elseif ($c === 'nombre') $colNombreCliente = 'nombre';
            elseif ($c === 'dni') $colDniCliente = 'dni';
        }
        $cuotasEncontradas = true;
    }
    // Opción C: cuotas_prestamo / pagos_prestamo / lista_cuotas
    elseif (isset($mapCols['cuotas_prestamo'])) {
        $tablaCuotas = 'cuotas_prestamo';
        $tablaPrestamos = isset($mapCols['prestamos']) ? 'prestamos' : (isset($mapCols['prestamo']) ? 'prestamo' : '');
        $cuotasEncontradas = $tablaPrestamos !== '';
        if ($cuotasEncontradas) {
            $colsC = $mapCols['cuotas_prestamo'];
            $colsP = $mapCols[$tablaPrestamos];
            $colIdCuota = in_array('idcuota', $colsC) ? 'IdCuota' : (in_array('id', $colsC) ? 'id' : 'IdCuota');
            $colIdPrestamoRef = in_array('cod_prestamo', $colsC) ? 'Cod_Prestamo' : (in_array('idprestamo', $colsC) ? 'idPrestamo' : 'IdPrestamo');
            $colNumCuota = in_array('numerocuota', $colsC) ? 'NumeroCuota' : $colIdCuota;
            $colMonto = in_array('monto', $colsC) ? 'Monto' : (in_array('montocuota', $colsC) ? 'montoCuota' : 'Monto');
            $colFechaVenc = in_array('fechavencimiento', $colsC) ? 'FechaVencimiento' : (in_array('fechacuota', $colsC) ? 'fechaCuota' : 'FechaVencimiento');
            $colEstado = in_array('estado', $colsC) ? 'Estado' : 'estado';
            $colEstadoValorPendiente = in_array('estado', $colsC) ? "'pendiente'" : "0";
            $colIdPrestamo = in_array('idprestamo', $colsP) ? 'IdPrestamo' : 'idPrestamo';
            $colNombreCliente = in_array('nombrecliente', $colsP) ? 'NombreCliente' : (in_array('nombre', $colsP) ? 'nombre' : 'NombreCliente');
            $colDniCliente = in_array('dnicliente', $colsP) ? 'DniCliente' : (in_array('dni', $colsP) ? 'dni' : 'DniCliente');
        }
    } elseif (isset($mapCols['pagos_prestamo'])) {
        $tablaCuotas = 'pagos_prestamo';
        $tablaPrestamos = isset($mapCols['prestamos']) ? 'prestamos' : (isset($mapCols['prestamo']) ? 'prestamo' : '');
        $cuotasEncontradas = $tablaPrestamos !== '';
        if ($cuotasEncontradas) {
            $colsC = $mapCols['pagos_prestamo'];
            $colsP = $mapCols[$tablaPrestamos];
            $colIdCuota = in_array('idcuota', $colsC) ? 'IdCuota' : (in_array('idpagoprestamo', $colsC) ? 'IdPagoPrestamo' : 'id');
            $colIdPrestamoRef = in_array('cod_prestamo', $colsC) ? 'Cod_Prestamo' : (in_array('idprestamo', $colsC) ? 'idPrestamo' : 'IdPrestamo');
            $colNumCuota = in_array('numerocuota', $colsC) ? 'NumeroCuota' : $colIdCuota;
            $colMonto = in_array('monto', $colsC) ? 'Monto' : (in_array('montocuota', $colsC) ? 'montoCuota' : 'Monto');
            $colFechaVenc = in_array('fechavencimiento', $colsC) ? 'FechaVencimiento' : (in_array('fechacuota', $colsC) ? 'fechaCuota' : 'FechaVencimiento');
            $colEstado = in_array('estado', $colsC) ? 'Estado' : 'estado';
            $colEstadoValorPendiente = in_array('estado', $colsC) ? "'pendiente'" : "0";
            $colIdPrestamo = in_array('idprestamo', $colsP) ? 'IdPrestamo' : 'idPrestamo';
            $colNombreCliente = in_array('nombrecliente', $colsP) ? 'NombreCliente' : (in_array('nombre', $colsP) ? 'nombre' : 'NombreCliente');
            $colDniCliente = in_array('dnicliente', $colsP) ? 'DniCliente' : (in_array('dni', $colsP) ? 'dni' : 'DniCliente');
        }
    } elseif (isset($mapCols['lista_cuotas'])) {
        $tablaCuotas = 'lista_cuotas';
        $tablaPrestamos = isset($mapCols['prestamos']) ? 'prestamos' : (isset($mapCols['prestamo']) ? 'prestamo' : '');
        $cuotasEncontradas = $tablaPrestamos !== '';
        if ($cuotasEncontradas) {
            $colsC = $mapCols['lista_cuotas'];
            $colsP = $mapCols[$tablaPrestamos];
            $colIdCuota = in_array('idcuota', $colsC) ? 'IdCuota' : 'id';
            $colIdPrestamoRef = in_array('cod_prestamo', $colsC) ? 'Cod_Prestamo' : (in_array('idprestamo', $colsC) ? 'idPrestamo' : 'IdPrestamo');
            $colNumCuota = in_array('numerocuota', $colsC) ? 'NumeroCuota' : $colIdCuota;
            $colMonto = in_array('monto', $colsC) ? 'Monto' : (in_array('montocuota', $colsC) ? 'montoCuota' : 'Monto');
            $colFechaVenc = in_array('fechavencimiento', $colsC) ? 'FechaVencimiento' : (in_array('fechacuota', $colsC) ? 'fechaCuota' : 'FechaVencimiento');
            $colEstado = in_array('estado', $colsC) ? 'Estado' : 'estado';
            $colEstadoValorPendiente = in_array('estado', $colsC) ? "'pendiente'" : "0";
            $colIdPrestamo = in_array('idprestamo', $colsP) ? 'IdPrestamo' : 'idPrestamo';
            $colNombreCliente = in_array('nombrecliente', $colsP) ? 'NombreCliente' : (in_array('nombre', $colsP) ? 'nombre' : 'NombreCliente');
        }
    }

    $warnCuotas = '';
    if (!$cuotasEncontradas) {
        $warnCuotas = 'No se detectaron tablas de cuotas/prestamos. Se omite módulo cuotas vencidas.';
    } else {
        // Construir query dinámico
        $sqlCuotas = "SELECT
                        c.{$colIdCuota} AS IdCuota,
                        p.{$colIdPrestamo} AS IdPrestamo,
                        p.{$colNombreCliente} AS NombreCliente,
                        c.{$colNumCuota} AS NumeroCuota,
                        c.{$colMonto} AS Monto,
                        c.{$colFechaVenc} AS FechaVencimiento
                      FROM {$tablaCuotas} c
                      INNER JOIN {$tablaPrestamos} p ON p.{$colIdPrestamo} = c.{$colIdPrestamoRef}
                      WHERE c.{$colEstado} = {$colEstadoValorPendiente}
                        AND DATE(c.{$colFechaVenc}) < CURDATE()";
        $resCuotas = mysqli_query($conexionDB, $sqlCuotas);
        if ($resCuotas) {
            while ($row = mysqli_fetch_assoc($resCuotas)) {
                $alertasCuotas++;
                $idCuota = (int)$row['IdCuota'];
                $nomC = mysqli_real_escape_string($conexionDB, $row['NombreCliente']);
                $dniC = mysqli_real_escape_string($conexionDB, $row['DniCliente']);
                $numC = (int)$row['NumeroCuota'];
                $montoC = (float)$row['Monto'];
                $fecV = $row['FechaVencimiento'];

                $chk2 = mysqli_query($conexionDB, "SELECT IdAlerta FROM alertas_sistema
                                                   WHERE Tipo='cuota_vencida'
                                                     AND IdReferencia=$idCuota
                                                     AND Leida=0
                                                   LIMIT 1");
                if (!$chk2 || mysqli_num_rows($chk2) == 0) {
                    $msg2 = "Cuota vencida: Cliente $nomC (DNI $dniC) - Cuota N°$numC - Monto S/. " . number_format($montoC, 2) . " - Venc. $fecV";
                    $ins2 = mysqli_query($conexionDB, "INSERT INTO alertas_sistema
                        (Tipo, IdReferencia, Mensaje, FechaGeneracion)
                        VALUES ('cuota_vencida', $idCuota, '$msg2', NOW())");
                    if ($ins2) $nuevas++;
                }
            }
        }
    }

    $out = [
        'resultado' => true,
        'alertasStock' => $alertasStock,
        'alertasCuotas' => $alertasCuotas,
        'nuevas' => $nuevas
    ];
    if ($warnCuotas) $out['advertencia'] = $warnCuotas;
    echo json_encode($out);
    exit;
}

if ($accion === 'count') {
    $res = mysqli_query($conexionDB, "SELECT COUNT(*) AS c FROM alertas_sistema WHERE Leida=0");
    $count = 0;
    if ($res) {
        $r = mysqli_fetch_assoc($res);
        $count = (int)$r['c'];
    }
    $ultimas = [];
    $resU = mysqli_query($conexionDB, "SELECT IdAlerta, Tipo, Mensaje, FechaGeneracion, Leida
                                       FROM alertas_sistema
                                       WHERE Leida=0
                                       ORDER BY FechaGeneracion DESC
                                       LIMIT 5");
    if ($resU) {
        while ($r = mysqli_fetch_assoc($resU)) $ultimas[] = $r;
    }
    echo json_encode(['resultado' => true, 'count' => $count, 'ultimas' => $ultimas]);
    exit;
}

if ($accion === 'list') {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = 20;
    $offset = ($page - 1) * $perPage;
    $filtroTipo = isset($_GET['tipo']) && in_array($_GET['tipo'], ['stock_bajo','cuota_vencida','otro'])
        ? " AND Tipo='" . $_GET['tipo'] . "'" : '';
    $soloSinLeer = isset($_GET['solo_sin_leer']) && $_GET['solo_sin_leer'] == '1' ? " AND Leida=0" : '';
    $limitOverride = isset($_GET['limit']) ? ' LIMIT ' . (int)$_GET['limit'] : " LIMIT $perPage OFFSET $offset";

    $sqlTotal = "SELECT COUNT(*) AS c FROM alertas_sistema WHERE 1=1 $filtroTipo $soloSinLeer";
    $resTotal = mysqli_query($conexionDB, $sqlTotal);
    $total = 0;
    if ($resTotal) {
        $r = mysqli_fetch_assoc($resTotal);
        $total = (int)$r['c'];
    }

    $sql = "SELECT IdAlerta, Tipo, IdReferencia, Mensaje, FechaGeneracion, Leida, FechaLectura
            FROM alertas_sistema
            WHERE 1=1 $filtroTipo $soloSinLeer
            ORDER BY FechaGeneracion DESC
            $limitOverride";
    $res = mysqli_query($conexionDB, $sql);
    $datos = [];
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) $datos[] = $r;
    }
    echo json_encode(['resultado' => true, 'datos' => $datos, 'total' => $total]);
    exit;
}

if ($accion === 'markread') {
    $input = json_decode(file_get_contents('php://input'), true);
    $idAlerta = isset($input['IdAlerta']) ? (int)$input['IdAlerta'] : 0;
    if ($idAlerta <= 0) {
        echo json_encode(['resultado' => false, 'mensaje' => 'IdAlerta inválido']);
        exit;
    }
    $upd = mysqli_query($conexionDB, "UPDATE alertas_sistema
                                      SET Leida=1, FechaLectura=NOW()
                                      WHERE IdAlerta=$idAlerta");
    echo json_encode(['resultado' => (bool)$upd]);
    exit;
}

echo json_encode(['resultado' => false, 'mensaje' => 'Acción no válida']);
