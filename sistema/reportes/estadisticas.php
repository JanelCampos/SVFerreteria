<?php
    include "../../conexion.php";
    session_start();
    include "../includes/paginador.php";
    require_once __DIR__ . "/../includes/analytics.php";
    $yearOptions = analyticsGetYearOptions();
    $filters = analyticsGetDateFilters($_GET);

    //traer datos de la tabla proveedores
    $query_proveedor = mysqli_query($conexionDB,"SELECT * FROM proveedores ORDER BY Nombre ASC");
    $result_proveedor = mysqli_num_rows($query_proveedor);

    $results_per_page = 10;
    $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $consulta = "
        SELECT 
            a.IdArticulo AS Cod_Articulo,
            a.Nombre,
            a.Cantidad,
            a.Precio_Compra,
            a.Precio_Unitario,
            p.Nombre AS nombreProv,
            IFNULL(SUM(dva.Cantidad), 0) AS cantidadVendida,
            IFNULL(SUM(dva.Ganancias), 0) AS gananciaGenerada
        FROM articulos a
        LEFT JOIN detalle_venta_articulos dva ON a.IdArticulo = dva.Cod_Articulo
        INNER JOIN proveedores p ON a.Cod_Proveedor = p.IdProveedor
        GROUP BY a.IdArticulo
        ORDER BY cantidadVendida DESC
    ";
    $where = "
        FROM articulos a
        LEFT JOIN detalle_venta_articulos dva ON a.IdArticulo = dva.Cod_Articulo
        INNER JOIN proveedores p ON a.Cod_Proveedor = p.IdProveedor
        GROUP BY a.IdArticulo
    ";
    list($result, $total_records) = getPaginatedDataPMV($conexionDB, $consulta, $where, $current_page, $results_per_page);

    $row = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<?php include "../includes/scripts_2.php"; ?>
    <?php include "../includes/title.php"; ?>
    <style>
        #tarjeta_container{
            display: none;
        }

        #vuelto_pendiente_container{
            display: none;
        }

        #saldo_container{
            display: none;
        }
    </style>
</head>
<body>
    
    <?php include "../includes/header_2.php"; ?>
    <div id="rol" data-rol="<?= $_SESSION['rol']; ?>"></div>
	<section id="container">
        <div class="title_container">
            <h1>Estadisticas de los productos</h1>
            <a href="../reportes/reportes.php" class="btn_new">Atrás</a>
        </div>
        <div class="header_container">
            <div class="row g-3 align-items-end">   
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="nombreArticulo">Nombre de producto</label>
                        <input class="filtrosBusqueda" type="text" name="nombre" id="nombreArticulo" placeholder="Ingrese nombre de producto">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="nombreProveedor">Proveedor</label>
                        <select class="filtrosBusqueda" id="nombreProveedor">
                            <option value="" class="option1" selected>Proveedor</option>
                            <?php
                                if($result_proveedor > 0){
                                    while ($proveedor = mysqli_fetch_array($query_proveedor)){
                            ?>
                                <option value="<?php echo $proveedor['IdProveedor']; ?>"><?php echo $proveedor['Nombre']; ?></option>
                            <?php
                                    }
                                }
                            ?>
                        </select>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="estadistica">Estadística</label>
                        <select class="filtrosBusqueda" id="estadistica">
                            <option value="">Seleccionar</option>
                            <option value="PMV">P. más vendido</option>
                            <option value="PCMG">P. con más ganancia</option>
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
                        <button type="button" class="btn btn-primary" onclick="buscarPMV()">Aplicar filtros</button>
                        <a href="estadisticas.php" class="btn btn-outline-secondary">Restablecer</a>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-4 ms-lg-auto">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <button class="btn btn-danger" type="button" onclick="exportarEstadisticas_PDF()">Exportar a PDF</button>
                        <button class="btn btn-success" type="button" onclick="exportarEstadisticas_EXCEL()">Exportar a Excel</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="containerTable">
            <table id="tablaPMV">
                <thead>
                    <tr>
                        <th>Cod.</th>
                        <th>Nombre</th>
                        <th>Stock actual</th>
                        <th>P. compra</th>
                        <th>P. venta</th>
                        <th>Proveedor</th>
                        <th>Cantidad Vendida</th>
                        <th>Ganancia generada</th>           
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if($row > 0){
                            while ($data = $result->fetch_assoc()){
                            ?>
                                <tr id="row_<?php echo $data["Cod_Articulo"]; ?>">
                                    <td><?php echo $data['Cod_Articulo']; ?></td>
                                    <td><?php echo $data["Nombre"]; ?></td>
                                    <td><?php echo $data["Cantidad"]; ?></td>
                                    <td><?php echo $data["Precio_Compra"]; ?></td>
                                    <td><?php echo $data["Precio_Unitario"]; ?></td>
                                    <td><?php echo $data["nombreProv"]; ?></td>
                                    <td><?php echo $data["cantidadVendida"]; ?></td>
                                    <td><?php echo $data["gananciaGenerada"]; ?></td>
                                </tr>
                    <?php
                            }
                        }
                    ?>
                </tbody>
            </table>
        </div>
        
        <?php 
            renderPaginator($total_records, $results_per_page, $current_page, 'estadisticas.php');
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