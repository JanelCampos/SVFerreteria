<?php
include "../../conexion.php";
session_start();
if (empty($_SESSION['active']) || $_SESSION['rol'] != 1) {
    header('location: ../');
}
$fechaPrimerDiaMes = date('Y-m-01');
$fechaHoy = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php include "../includes/scripts_2.php"; ?>
    <title>Capital y rentabilidad</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include "../includes/header_2.php"; ?>
    <section id="container" class="container-fluid pt-110">
        <div class="mb-4">
            <h1 class="h3 mb-2">Capital y rentabilidad</h1>
            <p class="text-muted">Indicadores financieros: ingresos, gastos, utilidad neta y rentabilidad</p>
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
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" onclick="buscarCapitalRentabilidad()">
                                <i class="fas fa-search me-1"></i> Buscar
                            </button>
                            <a href="capital_rentabilidad.php" class="btn btn-outline-secondary">
                                <i class="fas fa-eraser me-1"></i> Limpiar
                            </a>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="d-flex gap-2 justify-content-md-end">
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
            <div class="col-md-3">
                <div class="card h-100 shadow-sm border-0 bg-success bg-opacity-10">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="card-subtitle text-success mb-2">
                                    <i class="fas fa-arrow-up me-1"></i> INGRESOS
                                </h6>
                                <h3 class="card-title mb-0 text-success" id="cardIngresos">S/. 0.00</h3>
                                <small class="text-muted">Ventas del periodo</small>
                            </div>
                            <i class="fas fa-dollar-sign fa-2x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm border-0 bg-danger bg-opacity-10">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="card-subtitle text-danger mb-2">
                                    <i class="fas fa-arrow-down me-1"></i> GASTOS
                                </h6>
                                <h3 class="card-title mb-0 text-danger" id="cardGastos">S/. 0.00</h3>
                                <small class="text-muted">Egresos del periodo</small>
                            </div>
                            <i class="fas fa-money-bill-wave fa-2x text-danger opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm border-0 bg-primary bg-opacity-10">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="card-subtitle text-primary mb-2">
                                    <i class="fas fa-chart-line me-1"></i> UTILIDAD NETA
                                </h6>
                                <h3 class="card-title mb-0 text-primary" id="cardUtilidad">S/. 0.00</h3>
                                <small class="text-muted">Ingresos - Gastos</small>
                            </div>
                            <i class="fas fa-coins fa-2x text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100 shadow-sm border-0 bg-warning bg-opacity-10">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="card-subtitle text-warning mb-2">
                                    <i class="fas fa-percentage me-1"></i> % RENTABILIDAD
                                </h6>
                                <h3 class="card-title mb-0 text-warning" id="cardRentabilidad">0.00%</h3>
                                <small class="text-muted">(Utilidad / Ingresos) * 100</small>
                            </div>
                            <i class="fas fa-chart-pie fa-2x text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2 text-info"></i>Ingresos vs Gastos</h5>
                    </div>
                    <div class="card-body">
                        <div style="height:340px;">
                            <canvas id="chartDonut"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2 text-success"></i>Utilidad diaria (últimos 30 días)</h5>
                    </div>
                    <div class="card-body">
                        <div style="height:340px;">
                            <canvas id="chartLinea"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-table me-2 text-secondary"></i>Historial últimos 15 días</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Día</th>
                                <th class="text-end">Ingresos S/.</th>
                                <th class="text-end">Gastos S/.</th>
                                <th class="text-end">Utilidad S/.</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyTablaDiaria">
                            <tr><td colspan="4" class="text-center text-muted py-3">Cargando datos...</td></tr>
                        </tbody>
                        <tfoot id="tfootTablaDiaria" class="table-dark" style="display:none;">
                            <tr>
                                <th>TOTALES</th>
                                <th class="text-end" id="tfIngresos">0.00</th>
                                <th class="text-end" id="tfGastos">0.00</th>
                                <th class="text-end" id="tfUtilidad">0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <?php include "../includes/footer_2.php"; ?>

    <script>
        let chartDonut = null;
        let chartLinea = null;

        function obtenerFiltros() {
            return {
                fechaDesde: document.getElementById('fechaDesde').value,
                fechaHasta: document.getElementById('fechaHasta').value
            };
        }

        function buscarCapitalRentabilidad() {
            const filtros = obtenerFiltros();
            document.getElementById('tbodyTablaDiaria').innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Cargando...</td></tr>';

            fetch('buscar_capital_rentabilidad.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(filtros)
            })
            .then(res => res.json())
            .then(data => {
                if (data.resultado) {
                    renderizarTarjetas(data);
                    renderizarDonut(data);
                    renderizarLinea(data);
                    renderizarTabla(data);
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('tbodyTablaDiaria').innerHTML = '<tr><td colspan="4" class="text-center text-danger py-3">Error al cargar datos</td></tr>';
            });
        }

        function renderizarTarjetas(data) {
            document.getElementById('cardIngresos').textContent = 'S/. ' + Number(data.ingresos).toFixed(2);
            document.getElementById('cardGastos').textContent = 'S/. ' + Number(data.gastos).toFixed(2);
            document.getElementById('cardUtilidad').textContent = 'S/. ' + Number(data.utilidad).toFixed(2);
            document.getElementById('cardRentabilidad').textContent = Number(data.rentabilidadPct).toFixed(2) + '%';
        }

        function renderizarDonut(data) {
            const ctx = document.getElementById('chartDonut');
            if (chartDonut) chartDonut.destroy();
            chartDonut = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Ingresos', 'Gastos'],
                    datasets: [{
                        data: [Number(data.ingresos), Number(data.gastos)],
                        backgroundColor: ['rgba(25, 135, 84, 0.75)', 'rgba(220, 53, 69, 0.75)'],
                        borderColor: ['rgba(25, 135, 84, 1)', 'rgba(220, 53, 69, 1)'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        function renderizarLinea(data) {
            const ctx = document.getElementById('chartLinea');
            if (chartLinea) chartLinea.destroy();
            const labels = (data.serieIngresos || []).map(x => x.fecha);
            const ingresos = (data.serieIngresos || []).map(x => Number(x.monto));
            const gastos = (data.serieGastos || []).map(x => Number(x.monto));
            const utilidad = (data.serieUtilidad || []).map(x => Number(x.monto));
            chartLinea = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Utilidad',
                            data: utilidad,
                            borderColor: 'rgba(13, 110, 253, 1)',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Ingresos',
                            data: ingresos,
                            borderColor: 'rgba(25, 135, 84, 1)',
                            backgroundColor: 'rgba(25, 135, 84, 0.05)',
                            borderWidth: 2,
                            tension: 0.3
                        },
                        {
                            label: 'Gastos',
                            data: gastos,
                            borderColor: 'rgba(220, 53, 69, 1)',
                            backgroundColor: 'rgba(220, 53, 69, 0.05)',
                            borderWidth: 2,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        function renderizarTabla(data) {
            const tbody = document.getElementById('tbodyTablaDiaria');
            const tfoot = document.getElementById('tfootTablaDiaria');
            const tabla = data.tablaDiaria || [];
            if (tabla.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No hay datos para el periodo seleccionado</td></tr>';
                tfoot.style.display = 'none';
                return;
            }
            let html = '';
            let totI = 0, totG = 0, totU = 0;
            tabla.forEach(row => {
                const u = Number(row.ingresos) - Number(row.gastos);
                totI += Number(row.ingresos);
                totG += Number(row.gastos);
                totU += u;
                const cls = u < 0 ? 'text-danger' : (u > 0 ? 'text-success' : '');
                html += `<tr>
                    <td>${row.fecha}</td>
                    <td class="text-end text-success">S/. ${Number(row.ingresos).toFixed(2)}</td>
                    <td class="text-end text-danger">S/. ${Number(row.gastos).toFixed(2)}</td>
                    <td class="text-end ${cls}">S/. ${u.toFixed(2)}</td>
                </tr>`;
            });
            tbody.innerHTML = html;
            document.getElementById('tfIngresos').textContent = 'S/. ' + totI.toFixed(2);
            document.getElementById('tfGastos').textContent = 'S/. ' + totG.toFixed(2);
            const cls = totU < 0 ? 'text-danger' : (totU > 0 ? 'text-success' : '');
            const tfU = document.getElementById('tfUtilidad');
            tfU.textContent = 'S/. ' + totU.toFixed(2);
            tfU.className = 'text-end ' + cls;
            tfoot.style.display = '';
        }

        function exportarPDF() {
            const f = obtenerFiltros();
            window.open(`imprimir_capital_rentabilidad.php?fechaDesde=${f.fechaDesde}&fechaHasta=${f.fechaHasta}&nPdf=1`, '_blank');
        }

        function exportarExcel() {
            const f = obtenerFiltros();
            window.open(`imprimir_capital_rentabilidad.php?fechaDesde=${f.fechaDesde}&fechaHasta=${f.fechaHasta}&nExcel=1`, '_blank');
        }

        document.addEventListener('DOMContentLoaded', () => {
            buscarCapitalRentabilidad();
        });
    </script>
</body>
</html>
