<?php
    include "../../conexion.php";
    session_start();
    include "../includes/paginador.php";

    $usuario = $_SESSION['idUser'];

    $query = $conexionDB->prepare("
        SELECT IdCaja, Actividad, Estado
        FROM caja
        WHERE IdCaja > (
            SELECT MAX(c1.IdCaja)
            FROM caja c1
            WHERE c1.Estado = 'Cerrado'
        )
        AND Estado = 'Abierto'
        GROUP BY Actividad DESC
    ");
    $query->execute();
    $result = $query->get_result();

    $dataCaja = [];
    while ($row = $result->fetch_assoc()) {
        $dataCaja[] = $row['Actividad'];
    }

    $results_per_page = 10;
    $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $consulta = "
        SELECT 
            c.IdCaja, 
            c.FechaApertura, 
            c.Actividad, 
            c.Monto_inicial, 
            c.Monto_salida, 
            c.totalCajaDia, 
            c.Cod_Empleado, 
            e.Nombre
        FROM caja c
        INNER JOIN empleados e ON c.Cod_Empleado = e.IdEmpleado
        WHERE c.IdCaja > (
            SELECT MAX(c1.IdCaja)
            FROM caja c1
            WHERE c1.Estado = 'Cerrado'
        )
        AND c.Estado = 'Abierto'
        ORDER BY c.FechaApertura DESC
    ";
    $where = "
        caja c
        INNER JOIN empleados e ON c.Cod_Empleado = e.IdEmpleado
        WHERE c.IdCaja > (
            SELECT MAX(c1.IdCaja)
            FROM caja c1
            WHERE c1.Estado = 'Cerrado'
        )
        AND c.Estado = 'Abierto'
        ORDER BY c.FechaApertura DESC
    ";
    list($result, $total_records) = getPaginatedDataArticulos($conexionDB, $consulta, $where, $current_page, $results_per_page);

    $row = $result->num_rows;

?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<?php include "../includes/scripts_2.php"; ?>
    <?php include "../includes/title.php"; ?>
</head>
<body>
    
    <?php include "../includes/header_2.php"; ?>
	<section id="container">
        <div class="bg-white p-3 rounded shadow mb-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="mb-0">
                    <h1>Actividad diaria</h1>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if($_SESSION['rol'] == 1) { ?>
                        <a href="lista_caja.php" class="btn_new">Historial de caja</a>
                    <?php } ?>
                    <a href="../index.php" class="btn_new">Atrás</a>
                </div>
            </div>
        </div>
        <div class="header_container">
            <div class="row g-3">
                <?php 
                $totalc = mysqli_query($conexionDB,"
                    SELECT totalEfectivoDia,totalTarjetaDia,totalCajaDia,utilidadDia 
                    FROM caja 
                    WHERE IdCaja = (SELECT MAX(IdCaja) 
                    FROM caja 
                    WHERE Cod_Empleado = '$usuario')");
                $data = mysqli_fetch_array($totalc);
                $row = mysqli_num_rows($totalc);
                if($row > 0){
                    $totalEfectivoDia = $data['totalEfectivoDia'];
                    $totalTarjetaDia = $data['totalTarjetaDia'];
                    $totalCajaDia = $data['totalCajaDia'];
                    $utilidadDia = $data['utilidadDia'];
                }else{
                    $totalEfectivoDia = 0;
                    $totalTarjetaDia = 0;
                    $totalCajaDia = 0;
                    $utilidadDia = 0;
                }
                
                ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Total caja: </h5>
                        </div>
                        <div class="card-body">
                            <span>S/. <?php echo number_format($totalCajaDia,2); ?></span>
                        </div>
                    </div>
                </div>
                <?php if($_SESSION['rol'] == 1) { ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Utildiad total: </h5>
                            </div>
                            <div class="card-body">
                                <span>S/. <?php echo number_format($utilidadDia, 2); ?></span>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Total Efectivo: </h5>
                        </div>
                        <div class="card-body">
                            <span>S/. <?php echo number_format($totalEfectivoDia,2); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Total tarjeta: </h5>
                        </div>
                        <div class="card-body">
                            <span>S/. <?php echo number_format($totalTarjetaDia,2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3 align-items-end">   
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="busqedaActividadDia">Actividad</label>
                        <select class="filtrosBusqueda" name="busqedaActividadDia" id="busqedaActividadDia">
                            <option value=""><a href="actividad_caja_diaria.php">Actividad</a></option>
                            <?php foreach($dataCaja as $actividad){ ?>
                                <option value="<?php echo $actividad; ?>"><?php echo $actividad; ?></option>
                            <?php } ?>
                        </select>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" onclick="buscarCajaDia()">Aplicar filtros</button>
                        <a href="actividad_caja_diaria.php" class="btn btn-outline-secondary">Restablecer</a>
                    </div>
                </div>
            </div>
        </div>

    <div class="containerTable">
        <table id="tablaCajaDia">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Fecha / Hora: Movimientos</th>
                    <th>Actividad</th>
                    <th>Entrada (S/.)</th>
                    <th>Salida (S/.)</th>
                    <th>Dinero Total Act.(S/.)</th>
                    <th>ID-Usuario</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if($row > 0){
                        while ($data = $result->fetch_assoc()){
                        ?>
                            <tr>
                                <td><?php echo $data["IdCaja"]; ?></td>
                                <td><?php   $fechac = $data["FechaApertura"]; 
                                            $nfecha =  date("Y-m-d H:i:s", strtotime($fechac));
                                            echo $nfecha; ?></td>
                                <td><?php echo $data["Actividad"]; ?></td>
                                <td>S/. <?php echo $data["Monto_inicial"]; ?></td>
                                <td>S/. <?php echo $data["Monto_salida"]; ?></td>
                                <td>S/. <?php echo number_format($data["totalCajaDia"],2); ?></td>
                                <td><?php echo $data["Cod_Empleado"]; ?>-<?php echo $data["Nombre"]; ?></td>
                            </tr>
                <?php
                        }
                    } 
                ?>
            </tbody>
        </table>
    </div>
    <?php
        // Renderizar el paginador
        renderPaginator($total_records, $results_per_page, $current_page, 'actividad_caja_diaria.php');
        ?>
        <div id="paginator"></div>
	<?php include "../includes/footer_2.php"; ?>
</body>
</html>