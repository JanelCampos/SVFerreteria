<?php
include "../../conexion.php";
session_start();
require_once __DIR__ . "/../includes/analytics.php";

$filters = analyticsGetDateFilters($_GET);
$reportData = analyticsGetReportDashboardData($conexionDB, $filters);
$overview = $reportData['overview'];
$yearOptions = analyticsGetYearOptions();
$topClientsRows = $reportData['top_clients'];
$topProducts = $reportData['top_products'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <?php include "../includes/scripts_2.php"; ?>
    <?php include "../includes/title.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include "../includes/header_2.php"; ?>

    <section id="container" class="analytics-page">
        <div class="analytics-hero">
            <div>
                <span class="analytics-eyebrow">Centro de reportes</span>
                <h1>Reportes y analiticas del negocio</h1>
                <p>Todos los graficos y rankings usan el mismo periodo, permiten comparar ventas, utilidad, gastos, vendedores, clientes y productos, y excluyen permanentemente al Cliente General.</p>
            </div>
            <div class="analytics-actions">
                <a href="ventas.php" class="btn btn-outline-primary">Listado de ventas</a>
                <a href="gastos.php" class="btn btn-outline-secondary">Gastos</a>
                <a href="estadisticas.php" class="btn btn-primary">Reportes de productos</a>
            </div>
        </div>

        <form method="GET" class="analytics-filter-form mb-4">
            <div class="row g-3">
                <div class="col-12 col-md-3 col-xl-2">
                    <label for="period" class="form-label">Tipo de periodo</label>
                    <select name="period" id="period" class="form-select">
                        <option value="year" <?php echo $filters['period'] === 'year' ? 'selected' : ''; ?>>Anual</option>
                        <option value="month" <?php echo $filters['period'] === 'month' ? 'selected' : ''; ?>>Mensual</option>
                        <option value="custom" <?php echo $filters['period'] === 'custom' ? 'selected' : ''; ?>>Rango personalizado</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 col-xl-2 analytics-period-field" data-period-target="year">
                    <label for="year" class="form-label">Anio</label>
                    <select name="year" id="year" class="form-select">
                        <?php foreach ($yearOptions as $yearOption) { ?>
                            <option value="<?php echo $yearOption; ?>" <?php echo (int)$filters['year'] === (int)$yearOption ? 'selected' : ''; ?>><?php echo $yearOption; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-12 col-md-3 col-xl-2 analytics-period-field" data-period-target="month">
                    <label for="month" class="form-label">Mes</label>
                    <input type="month" name="month" id="month" class="form-control" value="<?php echo htmlspecialchars($filters['month']); ?>">
                </div>
                <div class="col-12 col-md-3 col-xl-2 analytics-period-field" data-period-target="custom">
                    <label for="start_date" class="form-label">Fecha inicio</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlspecialchars($filters['start_date']); ?>">
                </div>
                <div class="col-12 col-md-3 col-xl-2 analytics-period-field" data-period-target="custom">
                    <label for="end_date" class="form-label">Fecha fin</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlspecialchars($filters['end_date']); ?>">
                </div>
                <div class="col-12 col-md-3 col-xl-2">
                    <label for="top_metric" class="form-label">Ranking clientes</label>
                    <select name="top_metric" id="top_metric" class="form-select">
                        <option value="monto" <?php echo $filters['top_metric'] === 'monto' ? 'selected' : ''; ?>>Mayor monto</option>
                        <option value="utilidad" <?php echo $filters['top_metric'] === 'utilidad' ? 'selected' : ''; ?>>Mayor utilidad</option>
                        <option value="cantidad" <?php echo $filters['top_metric'] === 'cantidad' ? 'selected' : ''; ?>>Mas compras</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 col-xl-2">
                    <label for="top_limit" class="form-label">Top clientes</label>
                    <select name="top_limit" id="top_limit" class="form-select">
                        <option value="10" <?php echo $filters['top_limit'] === 10 ? 'selected' : ''; ?>>Top 10</option>
                        <option value="20" <?php echo $filters['top_limit'] === 20 ? 'selected' : ''; ?>>Top 20</option>
                        <option value="50" <?php echo $filters['top_limit'] === 50 ? 'selected' : ''; ?>>Top 50</option>
                    </select>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">Aplicar filtros</button>
                    <a href="reportes.php" class="btn btn-outline-secondary">Restablecer</a>
                </div>
            </div>
        </form>

        <div class="analytics-kpis">
            <article class="analytics-kpi">
                <span>Ventas totales</span>
                <strong>S/. <?php echo number_format($overview['total_sales'], 2); ?></strong>
                <small><?php echo htmlspecialchars($filters['label']); ?></small>
            </article>
            <article class="analytics-kpi">
                <span>Utilidad total</span>
                <strong>S/. <?php echo number_format($overview['total_profit'], 2); ?></strong>
                <small>Periodo filtrado</small>
            </article>
            <article class="analytics-kpi">
                <span>Gastos personales</span>
                <strong>S/. <?php echo number_format($overview['personal_expenses'], 2); ?></strong>
                <small>Egresos operativos</small>
            </article>
            <article class="analytics-kpi">
                <span>Gastos de capital</span>
                <strong>S/. <?php echo number_format($overview['capital_expenses'], 2); ?></strong>
                <small>Inversion y reposicion</small>
            </article>
            <article class="analytics-kpi">
                <span>Clientes activos</span>
                <strong><?php echo (int)$overview['active_clients']; ?></strong>
                <small>Sin Cliente General</small>
            </article>
            <article class="analytics-kpi">
                <span>Top cliente</span>
                <strong><?php echo htmlspecialchars($overview['top_client_name']); ?></strong>
                <small>S/. <?php echo number_format($overview['top_client_amount'], 2); ?></small>
            </article>
        </div>

        <div class="analytics-grid">
            <article class="analytics-panel analytics-panel-lg">
                <div class="analytics-panel-head">
                    <div>
                        <h2>Ventas por mes</h2>
                        <p>Comparativo mensual del ingreso generado dentro del periodo.</p>
                    </div>
                </div>
                <div style="height: 350px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </article>
            
            <article class="analytics-panel">
                <div class="analytics-panel-head">
                    <div>
                        <h2>Productos mas vendidos</h2>
                        <p>Resumen de unidades y utilidad generada.</p>
                    </div>
                </div>
                <div class="analytics-table-wrap" style="height: 300px;">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Unidades</th>
                                <th>Utilidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($topProducts) { ?>
                                <?php foreach ($topProducts as $product) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['Nombre']); ?></td>
                                        <td><?php echo (int)$product['cantidadVendida']; ?></td>
                                        <td>S/. <?php echo number_format((float)$product['utilidadGenerada'], 2); ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr><td colspan="3">No hay productos vendidos en el periodo seleccionado.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="analytics-panel">
                <div class="analytics-panel-head">
                    <div>
                        <h2>Clientes con mayor volumen y utilidad</h2>
                        <p>Top <?php echo (int)$filters['top_limit']; ?> del periodo.</p>
                    </div>
                </div>
                <div class="analytics-table-wrap" style="height: 350px;">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Compras</th>
                                <th>Monto</th>
                                <th>Utilidad</th>
                                <th>Ultima compra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($topClientsRows) { ?>
                                <?php foreach ($topClientsRows as $client) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($client['Nombre']); ?></td>
                                        <td><?php echo (int)$client['cantidadCompras']; ?></td>
                                        <td>S/. <?php echo number_format((float)$client['montoCompras'], 2); ?></td>
                                        <td>S/. <?php echo number_format((float)$client['utilidadGenerada'], 2); ?></td>
                                        <td><?php echo htmlspecialchars((string)$client['ultimaCompra']); ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr><td colspan="5">No hay clientes con compras en el periodo seleccionado.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="analytics-panel analytics-panel-lg">
                <div class="analytics-panel-head">
                    <div>
                        <h2>Ganancia bruta por mes</h2>
                        <p>Evolucion mensual del margen bruto generado.</p>
                    </div>
                </div>
                <div style="height: 350px;">
                    <canvas id="profitChart"></canvas>
                </div>
            </article>

            <article class="analytics-panel analytics-panel-lg">
                <div class="analytics-panel-head">
                    <div>
                        <h2>Rentabilidad por mes</h2>
                        <p>Evolucion mensual de la rentabilidad generada.</p>
                    </div>
                </div>
                <div style="height: 350px;">
                    <canvas id="rentabilityChart"></canvas>
                </div>
            </article>

            <article class="analytics-panel">
                <div class="analytics-panel-head">
                    <div>
                        <h2>Desempeno por vendedor</h2>
                        <p>Ventas generadas por cada vendedor en el periodo seleccionado.</p>
                    </div>
                </div>
                <div style="height: 350px;">
                    <canvas id="sellerChart"></canvas>
                </div>
            </article>

            <article class="analytics-panel">
                <div class="analytics-panel-head">
                    <div>
                        <h2>Ventas por metodo de pago</h2>
                        <p>Distribucion del ingreso cobrado por canal.</p>
                    </div>
                </div>
                <div style="height: 350px;">
                    <canvas id="paymentChart"></canvas>
                </div>
            </article>

            <article class="analytics-panel analytics-panel-lg">
                <div class="analytics-panel-head">
                    <div>
                        <h2>Gastos personales vs capital</h2>
                        <p>Comparacion mensual de egresos por tipo.</p>
                    </div>
                </div>
                <div style="height: 350px;">
                    <canvas id="expenseChart"></canvas>
                </div>
            </article>

            <article class="analytics-panel analytics-panel-lg">
                <div class="analytics-panel-head">
                    <div>
                        <h2>Top clientes</h2>
                        <p>Ranking principal segun el criterio seleccionado.</p>
                    </div>
                </div>
                <div style="height: 350px;">
                    <canvas id="topClientsChart"></canvas>
                </div>
            </article>

            <article class="analytics-panel">
                <div class="analytics-panel-head">
                    <div>
                        <h2>Estado de ventas</h2>
                        <p>Concentracion por estado operativo.</p>
                    </div>
                </div>
                <div style="height: 350px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </article>

            <article class="analytics-panel">
                <div class="analytics-panel-head">
                    <div>
                        <h2>Capital Total Invertido</h2>
                        <p>Capital total invertido en la empresa (Suma la utilidad).</p>
                    </div>
                </div>
                <div style="height: 350px;">
                    <canvas id="capitalChart"></canvas>
                </div>
            </article>

            <article class="analytics-panel analytics-panel-lg">
                <div class="analytics-panel-head">
                    <div>
                        <h2>Capital invertido por mes</h2>
                        <p>Evolucion mensual del capital invertido (suma la utilidad mensual).</p>
                    </div>
                </div>
                <div style="height: 350px;">
                    <canvas id="capitalBreakdownChart"></canvas>
                </div>
            </article>
            
        </div>
    </section>

    <?php include "../includes/footer_2.php"; ?>

    <script>
        function createMoneyChart(elementId, type, labels, datasets, horizontal = false) {
            const element = document.getElementById(elementId);
            if (!element) {
                return;
            }

            new Chart(element, {
                type: type,
                data: { labels, datasets },
                options: {
                    indexAxis: horizontal ? 'y' : 'x',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        createMoneyChart('salesChart', 'bar',
            <?php echo json_encode($reportData['sales_series']['labels']); ?>,
            [{
                label: 'Ventas',
                data: <?php echo json_encode($reportData['sales_series']['values']); ?>,
                backgroundColor: 'rgba(15, 123, 138, 0.22)',
                borderColor: '#0f7b8a',
                borderWidth: 1
            }]
        );

        createMoneyChart('profitChart', 'bar',
            <?php echo json_encode($reportData['profit_series']['labels']); ?>,
            [{
                label: 'Utilidad',
                data: <?php echo json_encode($reportData['profit_series']['values']); ?>,
                borderColor: '#2b00ffff',
                backgroundColor: 'rgba(20, 65, 200, 0.12)',
                borderWidth: 1
            }]
        );

        createMoneyChart('rentabilityChart', 'bar',
            <?php echo json_encode($reportData['rentability_series']['labels']); ?>,
            [{
                label: 'Rentabilidad',
                data: <?php echo json_encode($reportData['rentability_series']['values']); ?>,
                borderColor: '#2b00ffff',
                backgroundColor: 'rgba(20, 65, 200, 0.12)',
                borderWidth: 1
            }]
        );

        createMoneyChart('capitalBreakdownChart', 'bar',
            <?php echo json_encode($reportData['capitalBreakdown_series']['labels']); ?>,
            [{
                label: 'Capital',
                data: <?php echo json_encode($reportData['capitalBreakdown_series']['values']); ?>,
                borderColor: '#2b00ffff',
                backgroundColor: 'rgba(20, 65, 200, 0.12)',
                borderWidth: 1
            }]
        );

        createMoneyChart('expenseChart', 'bar',
            <?php echo json_encode($reportData['expense_series']['labels']); ?>,
            [
                {
                    label: 'Personal',
                    data: <?php echo json_encode($reportData['expense_series']['personal']); ?>,
                    backgroundColor: 'rgba(220, 38, 38, 0.18)',
                    borderColor: '#dc2626',
                    borderWidth: 1
                },
                {
                    label: 'Capital',
                    data: <?php echo json_encode($reportData['expense_series']['capital']); ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.18)',
                    borderColor: '#3b82f6',
                    borderWidth: 1
                }
            ]
        );

        createMoneyChart('paymentChart', 'doughnut',
            <?php echo json_encode($reportData['payment_methods']['labels']); ?>,
            [{
                label: 'Monto',
                data: <?php echo json_encode($reportData['payment_methods']['values']); ?>,
                backgroundColor: ['#0f7b8a', '#3b82f6', '#8b5cf6', '#f59e0b']
            }]
        );

        createMoneyChart('statusChart', 'pie',
            <?php echo json_encode($reportData['status_breakdown']['labels']); ?>,
            [{
                label: 'Ventas',
                data: <?php echo json_encode($reportData['status_breakdown']['values']); ?>,
                backgroundColor: ['#16a34a', '#f59e0b', '#3b82f6', '#dc2626']
            }]
        );

        createMoneyChart('capitalChart', 'pie',
            <?php echo json_encode($reportData['capital_breakdown']['labels']); ?>,
            [{
                label: 'Capital',
                data: <?php echo json_encode($reportData['capital_breakdown']['values']); ?>,
                backgroundColor: ['#6b09d4ff', '#f59e0b', '#3b82f6', '#dc2626']
            }]
        );

        createMoneyChart('sellerChart', 'pie',
            <?php echo json_encode($reportData['seller_performance']['labels']); ?>,
            [{
                label: 'Ventas por vendedor',
                data: <?php echo json_encode($reportData['seller_performance']['values']); ?>,
                backgroundColor: ['#2318ebff', '#13f50bff', '#9f3bf6ff', '#dc2626', '#882965ff', '#cd0dd3ff', '#1d6d1bff', '#56158fff', '#8a1c1cff', '#621649ff']
            }]
        );

        createMoneyChart('topClientsChart', 'bar',
            <?php echo json_encode($reportData['top_clients_chart']['labels']); ?>,
            [{
                label: 'Ranking de clientes',
                data: <?php echo json_encode($reportData['top_clients_chart']['values']); ?>,
                backgroundColor: 'rgba(14, 165, 233, 0.22)',
                borderColor: '#0ea5e9',
                borderWidth: 1
            }],
            true
        );

        (function togglePeriodFields() {
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
