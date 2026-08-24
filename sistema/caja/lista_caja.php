<?php
    include "../../conexion.php";
    session_start();
    include "../includes/paginador.php";
    require_once __DIR__ . "/../includes/analytics.php";
    $filters = analyticsGetDateFilters($_GET);
    $yearOptions = analyticsGetYearOptions();

    $usuario = $_SESSION['idUser'];

    $query = $conexionDB->prepare("
        SELECT Actividad
        FROM caja
        GROUP BY Actividad
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
        SELECT c.IdCaja, c.FechaApertura, c.Actividad, c.Monto_inicial, c.Monto_salida, c.Total_caja, c.Cod_Empleado, e.Nombre 
        FROM caja c 
        INNER JOIN empleados e ON c.Cod_Empleado = e.IdEmpleado
        ORDER BY c.IdCaja DESC
    ";
    $where = "
        caja c 
        INNER JOIN empleados e ON c.Cod_Empleado = e.IdEmpleado
        ORDER BY c.IdCaja DESC
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
                    <h1>Historial de caja</h1>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="actividad_caja_diaria.php" class="btn_new">Actividad diaria</a>
                <a href="../index.php" class="btn_new">Atrás</a>
                </div>
            </div>
        </div>
        <div class="header_container">
            <div class="row g-3">
                <?php 
                $totalc = mysqli_query($conexionDB,"SELECT TotalEfectivo,TotalTarjeta,Total_caja,Utilidad 
                                                    FROM caja
                                                    WHERE IdCaja = (SELECT MAX(IdCaja)
                                                                    FROM caja)");
                $data = mysqli_fetch_array($totalc);
                $row = mysqli_num_rows($totalc);
                if($row > 0){
                    $TotalEfectivo = $data['TotalEfectivo'];
                    $TotalTarjeta = $data['TotalTarjeta'];
                    $totalcaja = $data['Total_caja'];
                    $Utilidad = $data['Utilidad'];
                }else{
                    $TotalEfectivo = 0;
                    $TotalTarjeta = 0;
                    $totalcaja = 0;
                    $Utilidad = 0;
                } 
                
                ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Total caja: </h5>
                        </div>
                        <div class="card-body">
                            <span>S/. <?php echo number_format($totalcaja,2); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Utilidad total: </h5>
                        </div>
                        <div class="card-body">
                            <span>S/. <?php echo number_format($Utilidad,2); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Total Efectivo: </h5>
                        </div>
                        <div class="card-body">
                            <span>S/. <?php echo number_format($TotalEfectivo,2); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Total tarjeta: </h5>
                        </div>
                        <div class="card-body">
                            <span>S/. <?php echo number_format($TotalTarjeta,2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="left_section">
                <div class="row g-3 align-items-end">   
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="busqedaActividad">Actividad</label>
                        <select class="filtrosBusqueda" name="busqedaActividad" id="busqedaActividad">
                            <option value=""><a href="lista_caja.php">Actividad</a></option>
                            <?php foreach($dataCaja as $actividad){ ?>
                                <option value="<?php echo $actividad; ?>"><?php echo $actividad; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="period">Periodo</label>
                        <select class="filtrosBusqueda" name="period" id="period">
                            <option value="year" <?php echo $filters['period'] === 'year' ? 'selected' : ''; ?>>Anual</option>
                            <option value="month" <?php echo $filters['period'] === 'month' ? 'selected' : ''; ?>>Mensual</option>
                            <option value="custom" <?php echo $filters['period'] === 'custom' ? 'selected' : ''; ?>>Rango personalizado</option>
                        </select>
                    </div>
                    <div class="analytics-period-field col-12 col-md-4 col-lg-3" data-period-target="year">
                        <label for="year">Anio</label>
                        <select class="filtrosBusqueda" name="year" id="year">
                            <?php foreach ($yearOptions as $yearOption) { ?>
                                <option value="<?php echo $yearOption; ?>" <?php echo (int)$filters['year'] === (int)$yearOption ? 'selected' : ''; ?>><?php echo $yearOption; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="analytics-period-field col-12 col-md-4 col-lg-3" data-period-target="month">
                        <label for="month">Mes</label>
                        <input class="filtrosBusqueda" type="month" name="month" id="month" value="<?php echo htmlspecialchars($filters['month']); ?>">
                    </div>
                    <div class="analytics-period-field col-12 col-md-4 col-lg-3" data-period-target="custom">
                        <label for="start_date">Fecha inicio</label>
                        <input class="filtrosBusqueda" type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($filters['start_date']); ?>">
                    </div>
                    <div class="analytics-period-field col-12 col-md-4 col-lg-3" data-period-target="custom">
                        <label for="end_date">Fecha fin</label>
                        <input class="filtrosBusqueda" type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($filters['end_date']); ?>">
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" onclick="buscarCaja()">Aplicar filtros</button>
                            <a href="lista_caja.php" class="btn btn-outline-secondary">Restablecer</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="containerTable">
            <table id="tablaCaja">
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
                                    <td>S/. <?php echo $data["Total_caja"]; ?></td>
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
    renderPaginator($total_records, $results_per_page, $current_page, 'lista_caja.php');
    ?>
    <div id="paginator"></div>
	</section>
	<?php include "../includes/footer_2.php"; ?>

    <script>
        (function toggleClientPeriodFields() {
            const periodControl = document.getElementById('period');
            const fields = document.querySelectorAll('.analytics-period-field');

            function refresh() {
                const selected = periodControl.value;
                fields.forEach((field) => {
                    const target = field.getAttribute('data-period-target');
                    const visible =
                        (selected === 'year' && target === 'year') ||
                        (selected === 'month' && target === 'month') ||
                        (selected === 'custom' && target === 'custom');

                    field.style.display = visible ? '' : 'none';
                });
            }

            refresh();
            periodControl.addEventListener('change', refresh);
        })();
    </script>
</body>
</html>