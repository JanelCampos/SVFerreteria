<?php
include "../../conexion.php";
session_start();
if (empty($_SESSION['active']) || $_SESSION['rol'] != 1) {
    header('location: ../');
}
$fechaPrimerDiaMes = date('Y-m-01');
$fechaHoy = date('Y-m-d');
$queryEmpleados = mysqli_query($conexionDB, "SELECT IdEmpleado, Nombre FROM empleados ORDER BY Nombre ASC");
$resultEmpleados = mysqli_num_rows($queryEmpleados);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php include "../includes/scripts_2.php"; ?>
    <title>Reporte de ventas por vendedor</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include "../includes/header_2.php"; ?>
    <section id="container" class="container-fluid pt-110">
        <div class="mb-4">
            <h1 class="h3 mb-2">Reporte de ventas por vendedor</h1>
            <p class="text-muted">Análisis de desempeño por vendedor y productos más vendidos</p>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label for="fechaDesde" class="form-label">Fecha desde</label>
                        <input type="date" id="fechaDesde" class="form-control" value="<?php echo $fechaPrimerDiaMes; ?>">
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="fechaHasta" class="form-label">Fecha hasta</label>
                        <input type="date" id="fechaHasta" class="form-control" value="<?php echo $fechaHoy; ?>">
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="IdVendedor" class="form-label">Vendedor</label>
                        <select id="IdVendedor" class="form-select">
                            <option value="">Todos</option>
                            <?php
                            if ($resultEmpleados > 0) {
                                while ($empleado = mysqli_fetch_assoc($queryEmpleados)) {
                                    echo '<option value="' . $empleado['IdEmpleado'] . '">' . htmlspecialchars($empleado['Nombre']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" onclick="buscarVentasVendedor()">
                                <i class="fas fa-search me-1"></i> Aplicar filtros
                            </button>
                            <a href="ventas_por_vendedor.php" class="btn btn-outline-secondary">
                                <i class="fas fa-eraser me-1"></i> Restablecer
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row g-2 mt-3">
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <span class="badge text-bg-danger p-2" style="cursor:pointer;" onclick="exportarPDF()">
                                <i class="fas fa-file-pdf me-1"></i> Exportar PDF
                            </span>
                            <span class="badge text-bg-success p-2" style="cursor:pointer;" onclick="exportarExcel()">
                                <i class="fas fa-file-excel me-1"></i> Exportar Excel
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="fas fa-users me-2 text-primary"></i>Resumen por vendedor</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Vendedor</th>
                                        <th class="text-center">N° Ventas</th>
                                        <th class="text-end">Total S/.</th>
                                        <th class="text-end">Utilidad S/.</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyResumenVendedor">
                                    <tr><td colspan="4" class="text-center text-muted py-3">Cargando datos...</td></tr>
                                </tbody>
                                <tfoot id="tfootResumenVendedor" class="table-dark" style="display:none;">
                                    <tr>
                                        <th>TOTALES</th>
                                        <th class="text-center" id="totNVentas">0</th>
                                        <th class="text-end" id="totTotalVenta">0.00</th>
                                        <th class="text-end" id="totUtilidad">0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="fas fa-trophy me-2 text-warning"></i>Top productos vendidos</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">Pos</th>
                                        <th>Producto</th>
                                        <th>Categ.</th>
                                        <th class="text-center">Cant.</th>
                                        <th class="text-end">Importe S/.</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyTopProductos">
                                    <tr><td colspan="5" class="text-center text-muted py-3">Cargando datos...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2 text-info"></i>Gráfico de ventas por vendedor</h5>
            </div>
            <div class="card-body">
                <div id="chartVentasVendedor" style="height:340px;">
                    <canvas id="canvasChartVentas"></canvas>
                </div>
            </div>
        </div>
    </section>

    <?php include "../includes/footer_2.php"; ?>

    <script>
        let chartVentas = null;

        function obtenerFiltros() {
            return {
                fechaDesde: document.getElementById('fechaDesde').value,
                fechaHasta: document.getElementById('fechaHasta').value,
                IdVendedor: document.getElementById('IdVendedor').value
            };
        }

        function buscarVentasVendedor() {
            const filtros = obtenerFiltros();
            document.getElementById('tbodyResumenVendedor').innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Cargando...</td></tr>';
            document.getElementById('tbodyTopProductos').innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Cargando...</td></tr>';

            fetch('buscar_ventas_vendedor.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(filtros)
            })
            .then(res => res.json())
            .then(data => {
                renderizarResumen(data);
                renderizarTopProductos(data);
                renderizarGrafico(data);
            })
            .catch(err => {
                document.getElementById('tbodyResumenVendedor').innerHTML = '<tr><td colspan="4" class="text-center text-danger py-3">Error al cargar datos</td></tr>';
                document.getElementById('tbodyTopProductos').innerHTML = '<tr><td colspan="5" class="text-center text-danger py-3">Error al cargar datos</td></tr>';
                console.error(err);
            });
        }

        function renderizarResumen(data) {
            const tbody = document.getElementById('tbodyResumenVendedor');
            const tfoot = document.getElementById('tfootResumenVendedor');
            if (!data.resultado || !data.resumenPorVendedor || data.resumenPorVendedor.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No hay datos para el periodo seleccionado</td></tr>';
                tfoot.style.display = 'none';
                return;
            }
            let html = '';
            data.resumenPorVendedor.forEach(item => {
                html += `<tr>
                    <td>${escapeHtml(item.NombreEmpleado)}</td>
                    <td class="text-center">${item.NVentas}</td>
                    <td class="text-end">S/. ${Number(item.TotalVenta).toFixed(2)}</td>
                    <td class="text-end">S/. ${Number(item.Utilidad).toFixed(2)}</td>
                </tr>`;
            });
            tbody.innerHTML = html;
            document.getElementById('totNVentas').textContent = data.resumenPorVendedor.reduce((a, b) => a + Number(b.NVentas), 0);
            document.getElementById('totTotalVenta').textContent = 'S/. ' + Number(data.totalGeneral || 0).toFixed(2);
            document.getElementById('totUtilidad').textContent = 'S/. ' + Number(data.resumenPorVendedor.reduce((a, b) => a + Number(b.Utilidad), 0)).toFixed(2);
            tfoot.style.display = '';
        }

        function renderizarTopProductos(data) {
            const tbody = document.getElementById('tbodyTopProductos');
            if (!data.resultado || !data.topProductos || data.topProductos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No hay productos vendidos en el periodo</td></tr>';
                return;
            }
            let html = '';
            data.topProductos.forEach((item, idx) => {
                html += `<tr>
                    <td class="text-center"><strong>${idx + 1}</strong></td>
                    <td>${escapeHtml(item.Nombre)}</td>
                    <td>${escapeHtml(item.NombreCategoria || '-')}</td>
                    <td class="text-center">${item.Cantidad}</td>
                    <td class="text-end">S/. ${Number(item.Importe).toFixed(2)}</td>
                </tr>`;
            });
            tbody.innerHTML = html;
        }

        function renderizarGrafico(data) {
            const ctx = document.getElementById('canvasChartVentas');
            if (chartVentas) {
                chartVentas.destroy();
            }
            chartVentas = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.chartLabels || [],
                    datasets: [{
                        label: 'Importe total S/.',
                        data: data.chartData || [],
                        backgroundColor: 'rgba(13, 110, 253, 0.6)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }

        function exportarPDF() {
            const f = obtenerFiltros();
            window.open(`imprimir_ventas_vendedor.php?fechaDesde=${f.fechaDesde}&fechaHasta=${f.fechaHasta}&IdVendedor=${f.IdVendedor}&nPdf=1`, '_blank');
        }

        function exportarExcel() {
            const f = obtenerFiltros();
            window.open(`imprimir_ventas_vendedor.php?fechaDesde=${f.fechaDesde}&fechaHasta=${f.fechaHasta}&IdVendedor=${f.IdVendedor}&nExcel=1`, '_blank');
        }

        document.addEventListener('DOMContentLoaded', () => {
            buscarVentasVendedor();
        });
    </script>
</body>
</html>
