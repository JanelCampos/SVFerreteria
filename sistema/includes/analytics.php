<?php

function analyticsGetDateFilters($source = null)
{
    $source = is_array($source) ? $source : $_GET;
    $currentYear = (int)date('Y');
    $currentMonth = date('Y-m');

    $period = isset($source['period']) ? strtolower(trim((string)$source['period'])) : 'year';
    if (!in_array($period, ['year', 'month', 'custom'], true)) {
        $period = 'year';
    }

    $selectedYear = isset($source['year']) ? (int)$source['year'] : $currentYear;
    if ($selectedYear < 2020 || $selectedYear > ($currentYear + 1)) {
        $selectedYear = $currentYear;
    }

    $selectedMonth = isset($source['month']) ? trim((string)$source['month']) : $currentMonth;
    if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
        $selectedMonth = $currentMonth;
    }

    $inputStart = isset($source['start_date']) ? trim((string)$source['start_date']) : '';
    $inputEnd = isset($source['end_date']) ? trim((string)$source['end_date']) : '';

    if ($period === 'month') {
        $monthDate = DateTime::createFromFormat('Y-m', $selectedMonth);
        if (!$monthDate) {
            $monthDate = DateTime::createFromFormat('Y-m', $currentMonth);
        }

        $startDate = $monthDate->format('Y-m-01');
        $endDate = $monthDate->format('Y-m-t');
        $label = strftime('%B %Y', strtotime($startDate));
    } elseif ($period === 'custom' && $inputStart !== '' && $inputEnd !== '') {
        $startDate = $inputStart;
        $endDate = $inputEnd;
        if ($startDate > $endDate) {
            $swap = $startDate;
            $startDate = $endDate;
            $endDate = $swap;
        }
        $label = $startDate . ' al ' . $endDate;
    } else {
        $period = 'year';
        $startDate = sprintf('%04d-01-01', $selectedYear);
        $endDate = sprintf('%04d-12-31', $selectedYear);
        $label = 'Anio ' . $selectedYear;
    }

    $topLimit = isset($source['top_limit']) ? (int)$source['top_limit'] : 10;
    if (!in_array($topLimit, [10, 20, 50], true)) {
        $topLimit = 10;
    }

    $topMetric = isset($source['top_metric']) ? trim((string)$source['top_metric']) : 'monto';
    if (!in_array($topMetric, ['monto', 'utilidad', 'cantidad'], true)) {
        $topMetric = 'monto';
    }

    return [
        'period' => $period,
        'year' => $selectedYear,
        'month' => $selectedMonth,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'label' => ucfirst($label),
        'top_limit' => $topLimit,
        'top_metric' => $topMetric,
    ];
}

function analyticsGetYearOptions($pastYears = 5)
{
    $currentYear = (int)date('Y');
    $years = [];
    for ($year = $currentYear; $year >= ($currentYear - $pastYears); $year--) {
        $years[] = $year;
    }

    return $years;
}

function analyticsGetClientExclusionClause($alias = 'c')
{
    return "COALESCE({$alias}.Dni, 0) <> 11111111 AND TRIM(LOWER(COALESCE({$alias}.Nombre, ''))) <> 'Cliente General'";
}

function analyticsGetNonCanceledSalesClause($alias = 'v')
{
    return "LOWER(COALESCE({$alias}.Estado, '')) <> 'anulado'";
}

function analyticsBindParams($stmt, $types, array &$params)
{
    if ($types === '') {
        return;
    }

    $references = [];
    $references[] = &$types;
    foreach ($params as $index => $value) {
        $references[] = &$params[$index];
    }

    call_user_func_array([$stmt, 'bind_param'], $references);
}

function analyticsFetchAll($conexionDB, $sql, $types = '', $params = [])
{
    $stmt = $conexionDB->prepare($sql);
    if (!$stmt) {
        return [];
    }

    analyticsBindParams($stmt, $types, $params);
    if (!$stmt->execute()) {
        return [];
    }

    $result = $stmt->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function analyticsFetchOne($conexionDB, $sql, $types = '', $params = [])
{
    $rows = analyticsFetchAll($conexionDB, $sql, $types, $params);
    return $rows ? $rows[0] : null;
}

function analyticsFetchScalar($conexionDB, $sql, $types = '', $params = [], $key = 'valor')
{
    $row = analyticsFetchOne($conexionDB, $sql, $types, $params);
    if (!$row || !array_key_exists($key, $row)) {
        return 0;
    }

    return $row[$key] ?? 0;
}

function analyticsGetMonthlyBuckets($filters)
{
    $start = new DateTime($filters['start_date']);
    $end = new DateTime($filters['end_date']);
    $end->modify('first day of next month');

    $period = new DatePeriod($start->modify('first day of this month'), new DateInterval('P1M'), $end);
    $labels = [];
    $keys = [];

    foreach ($period as $date) {
        $keys[] = $date->format('Y-m');
        $labels[] = $date->format('M Y');
    }

    return ['keys' => $keys, 'labels' => $labels];
}

function analyticsBuildMonthlySeries($rows, $filters, $valueKey)
{
    $buckets = analyticsGetMonthlyBuckets($filters);
    $values = array_fill(0, count($buckets['keys']), 0);
    $indexMap = array_flip($buckets['keys']);

    foreach ($rows as $row) {
        $key = $row['periodo'];
        if (isset($indexMap[$key])) {
            $values[$indexMap[$key]] = round((float)$row[$valueKey], 2);
        }
    }

    return ['labels' => $buckets['labels'], 'values' => $values];
}

function analyticsGetOverviewMetrics($conexionDB, $filters)
{
    $salesParams = [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59'];
    $salesParamsVentas = [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59', $filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59'];
    $expenseParams = [$filters['start_date'], $filters['end_date']];

    $salesWhere = analyticsGetNonCanceledSalesClause('v') . " AND " . analyticsGetClientExclusionClause('c') . " AND v.Fecha BETWEEN ? AND ?";
    $whereClietGeneral = analyticsGetNonCanceledSalesClause('v') . " AND v.Fecha BETWEEN ? AND ?";
    $totalSales = (float)analyticsFetchScalar(
        $conexionDB,
        "SELECT 
            COALESCE(SUM(v.Total), 0) + (
                SELECT COALESCE(SUM(vl.montoVentaLibre), 0)
                FROM ventalibre vl
                WHERE vl.fechaVentaLibre BETWEEN ? AND ?
            ) AS valor
        FROM ventas v
        INNER JOIN clientes c ON c.Dni = v.dniCliente
        WHERE {$whereClietGeneral}",
        'ssss',
        $salesParamsVentas
    );

    $totalProfit = (float)analyticsFetchScalar(
    $conexionDB,
    "SELECT 
        COALESCE(SUM(v.utilidad), 0) + (
            SELECT COALESCE(SUM(vl.montoVentaLibre), 0)
            FROM ventalibre vl
            WHERE vl.fechaVentaLibre BETWEEN ? AND ?
              AND vl.tipoIngreso = 'personal'
        ) AS valor
    FROM ventas v
    INNER JOIN clientes c ON c.Dni = v.dniCliente
    WHERE {$whereClietGeneral}",
    'ssss',
    $salesParamsVentas
);

    $salesCount = (int)analyticsFetchScalar(
        $conexionDB,
        "SELECT COUNT(*) AS valor
         FROM ventas v
         INNER JOIN clientes c ON c.Dni = v.dniCliente
         WHERE {$whereClietGeneral}",
        'ss',
        $salesParams
    );

    $activeClients = (int)analyticsFetchScalar(
        $conexionDB,
        "SELECT COUNT(DISTINCT v.dniCliente) AS valor
         FROM ventas v
         INNER JOIN clientes c ON c.Dni = v.dniCliente
         WHERE {$salesWhere}",
        'ss',
        $salesParams
    );

    $pendingSales = (int)analyticsFetchScalar(
        $conexionDB,
        "SELECT COUNT(*) AS valor
         FROM ventas v
         INNER JOIN clientes c ON c.Dni = v.dniCliente
         WHERE LOWER(COALESCE(v.Estado, '')) IN ('pendiente', 'saldo') AND v.Fecha BETWEEN ? AND ? ",
        'ss',
        $salesParams
    );

    $pendingBalance = (float)analyticsFetchScalar(
        $conexionDB,
        "SELECT COALESCE(SUM(v.saldo), 0) AS valor
         FROM ventas v
         INNER JOIN clientes c ON c.Dni = v.dniCliente
         WHERE LOWER(COALESCE(v.Estado, '')) IN ('pendiente', 'saldo') AND v.Fecha BETWEEN ? AND ?",
        'ss',
        $salesParams
    );

    $personalExpenses = (float)analyticsFetchScalar(
        $conexionDB,
        "SELECT COALESCE(SUM(montoGasto), 0) AS valor
         FROM gastos
         WHERE tipoGasto = 'personal' AND fechaGasto BETWEEN ? AND ?",
        'ss',
        $expenseParams
    );

    $capitalExpenses = (float)analyticsFetchScalar(
        $conexionDB,
        "SELECT COALESCE(SUM(montoGasto), 0) AS valor
         FROM gastos
         WHERE tipoGasto = 'capital' AND fechaGasto BETWEEN ? AND ?",
        'ss',
        $expenseParams
    );

    $soldOutProducts = (int)analyticsFetchScalar(
        $conexionDB,
        "SELECT COUNT(*) AS valor FROM articulos WHERE Cantidad <= 0"
    );

    $topClient = analyticsFetchOne(
        $conexionDB,
        "SELECT c.Nombre, COUNT(v.IdVenta) AS compras, COALESCE(SUM(v.Total), 0) AS monto
         FROM ventas v
         INNER JOIN clientes c ON c.Dni = v.dniCliente
         WHERE {$salesWhere}
         GROUP BY c.Dni, c.Nombre
         ORDER BY monto DESC, compras DESC, c.Nombre ASC
         LIMIT 1",
        'ss',
        $salesParams
    );

    return [
        'total_sales' => $totalSales,
        'total_profit' => $totalProfit,
        'sales_count' => $salesCount,
        'active_clients' => $activeClients,
        'personal_expenses' => $personalExpenses,
        'capital_expenses' => $capitalExpenses,
        'average_ticket' => $salesCount > 0 ? round($totalSales / $salesCount, 2) : 0,
        'sold_out_products' => $soldOutProducts,
        'pending_sales' => $pendingSales,
        'pending_balance' => $pendingBalance,
        'top_client_name' => $topClient['Nombre'] ?? 'Sin datos',
        'top_client_amount' => isset($topClient['monto']) ? (float)$topClient['monto'] : 0,
    ];
}

function analyticsGetSalesSeries($conexionDB, $filters)
{
    $rows = analyticsFetchAll(
        $conexionDB,
        "SELECT DATE_FORMAT(v.Fecha, '%Y-%m') AS periodo, COALESCE(SUM(v.Total), 0) + COALESCE(MAX(vl.total_libre), 0) AS total
         FROM ventas v
         INNER JOIN clientes c ON c.Dni = v.dniCliente
         LEFT JOIN (
            SELECT DATE_FORMAT(fechaVentaLibre, '%Y-%m') AS periodo, SUM(montoVentaLibre) AS total_libre
            FROM ventalibre
            WHERE fechaVentaLibre BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(fechaVentaLibre, '%Y-%m')
         ) vl ON vl.periodo = DATE_FORMAT(v.Fecha, '%Y-%m')
         WHERE " . analyticsGetNonCanceledSalesClause('v') . " AND v.Fecha BETWEEN ? AND ?
         GROUP BY DATE_FORMAT(v.Fecha, '%Y-%m')
         ORDER BY periodo ASC",
        'ssss',
        [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59',$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59']
    );

    return analyticsBuildMonthlySeries($rows, $filters, 'total');
}

function analyticsGetProfitSeries($conexionDB, $filters)
{
    $rows = analyticsFetchAll(
        $conexionDB,
        "SELECT DATE_FORMAT(v.Fecha, '%Y-%m') AS periodo, COALESCE(SUM(v.utilidad), 0) + COALESCE(MAX(vl.total_libre), 0) AS total
         FROM ventas v
         INNER JOIN clientes c ON c.Dni = v.dniCliente
         LEFT JOIN (
            SELECT DATE_FORMAT(fechaVentaLibre, '%Y-%m') AS periodo, SUM(montoVentaLibre) AS total_libre
            FROM ventalibre
            WHERE fechaVentaLibre BETWEEN ? AND ? AND tipoIngreso = ?
            GROUP BY DATE_FORMAT(fechaVentaLibre, '%Y-%m')
         ) vl ON vl.periodo = DATE_FORMAT(v.Fecha, '%Y-%m')
         WHERE " . analyticsGetNonCanceledSalesClause('v') . " AND v.Fecha BETWEEN ? AND ?
         GROUP BY DATE_FORMAT(v.Fecha, '%Y-%m')
         ORDER BY periodo ASC",
        'sssss',
        [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59', 'personal', $filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59']
    );

    return analyticsBuildMonthlySeries($rows, $filters, 'total');
}

function analyticsGetRentabilitySeries($conexionDB, $filters)
{
    $rows = analyticsFetchAll(
        $conexionDB,
        "SELECT DATE_FORMAT(v.Fecha, '%Y-%m') AS periodo, (COALESCE(SUM(v.utilidad), 0) + COALESCE(MAX(vl.total_libre), 0)) - COALESCE(MAX(g.total_gasto), 0) AS total
         FROM ventas v
         INNER JOIN clientes c ON c.Dni = v.dniCliente
         LEFT JOIN (
            SELECT DATE_FORMAT(fechaVentaLibre, '%Y-%m') AS periodo, SUM(montoVentaLibre) AS total_libre
            FROM ventalibre
            WHERE fechaVentaLibre BETWEEN ? AND ? AND tipoIngreso = ?
            GROUP BY DATE_FORMAT(fechaVentaLibre, '%Y-%m')
         ) vl ON vl.periodo = DATE_FORMAT(v.Fecha, '%Y-%m')
         LEFT JOIN (
            SELECT DATE_FORMAT(fechaGasto, '%Y-%m') AS periodo, SUM(montoGasto) AS total_gasto
            FROM gastos
            WHERE fechaGasto BETWEEN ? AND ? AND tipoGasto = ?
            GROUP BY DATE_FORMAT(fechaGasto, '%Y-%m')
         ) g ON g.periodo = DATE_FORMAT(v.Fecha, '%Y-%m')
         WHERE " . analyticsGetNonCanceledSalesClause('v') . " AND v.Fecha BETWEEN ? AND ?
         GROUP BY DATE_FORMAT(v.Fecha, '%Y-%m')
         ORDER BY periodo ASC",
        'ssssssss',
        [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59', 'personal', $filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59', 'personal', $filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59']
    );

    return analyticsBuildMonthlySeries($rows, $filters, 'total');
}

function analyticsGetCapitalBreakdownSeries($conexionDB, $filters)
{
    $rows = analyticsFetchAll(
    $conexionDB,
    "SELECT 
        DATE_FORMAT(a.fechaCreacion, '%Y-%m') AS periodo,
            SUM(a.cantidad * a.precio_compra) + 
            COALESCE(
                (
                    SELECT c.Utilidad
                    FROM caja c
                    WHERE DATE_FORMAT(c.FechaApertura, '%Y-%m') = DATE_FORMAT(a.fechaCreacion, '%Y-%m')
                    ORDER BY c.IdCaja DESC
                    LIMIT 1
                ),
                0
            ) AS capitalTotal
        FROM articulos a
        WHERE a.fechaCreacion BETWEEN ? AND ?
        GROUP BY DATE_FORMAT(a.fechaCreacion, '%Y-%m')
        ORDER BY periodo",
        'ss',
        [
            $filters['start_date'] . ' 00:00:00',
            $filters['end_date'] . ' 23:59:59'
        ]
    );

    return analyticsBuildMonthlySeries($rows, $filters, 'capitalTotal');
}

function analyticsGetExpenseSeries($conexionDB, $filters)
{
    $rows = analyticsFetchAll(
        $conexionDB,
        "SELECT DATE_FORMAT(fechaGasto, '%Y-%m') AS periodo,
                COALESCE(SUM(CASE WHEN tipoGasto = 'personal' THEN montoGasto ELSE 0 END), 0) AS personal,
                COALESCE(SUM(CASE WHEN tipoGasto = 'capital' THEN montoGasto ELSE 0 END), 0) AS capital
         FROM gastos
         WHERE fechaGasto BETWEEN ? AND ?
         GROUP BY DATE_FORMAT(fechaGasto, '%Y-%m')
         ORDER BY periodo ASC",
        'ss',
        [$filters['start_date'], $filters['end_date']]
    );

    $buckets = analyticsGetMonthlyBuckets($filters);
    $indexMap = array_flip($buckets['keys']);
    $personalValues = array_fill(0, count($buckets['keys']), 0);
    $capitalValues = array_fill(0, count($buckets['keys']), 0);

    foreach ($rows as $row) {
        if (isset($indexMap[$row['periodo']])) {
            $position = $indexMap[$row['periodo']];
            $personalValues[$position] = round((float)$row['personal'], 2);
            $capitalValues[$position] = round((float)$row['capital'], 2);
        }
    }

    return [
        'labels' => $buckets['labels'],
        'personal' => $personalValues,
        'capital' => $capitalValues,
    ];
}

function analyticsGetPaymentMethodBreakdown($conexionDB, $filters)
{
    $rows = analyticsFetchAll(
        $conexionDB,
        "SELECT v.Medio_Pago AS etiqueta, COALESCE(SUM(v.Total), 0) AS total
         FROM ventas v
         INNER JOIN clientes c ON c.Dni = v.dniCliente
         WHERE " . analyticsGetNonCanceledSalesClause('v') . " AND v.Fecha BETWEEN ? AND ?
         GROUP BY v.Medio_Pago
         ORDER BY total DESC",
        'ss',
        [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59']
    );

    if (!$rows) {
        return ['labels' => ['Sin datos'], 'values' => [0]];
    }

    return [
        'labels' => array_map(static function ($row) {
            return ucfirst($row['etiqueta']);
        }, $rows),
        'values' => array_map(static function ($row) {
            return round((float)$row['total'], 2);
        }, $rows),
    ];
}

function analyticsGetStatusBreakdown($conexionDB, $filters)
{
    $rows = analyticsFetchAll(
        $conexionDB,
        "SELECT v.Estado AS etiqueta, COUNT(*) AS total
         FROM ventas v
         INNER JOIN clientes c ON c.Dni = v.dniCliente
         WHERE v.Fecha BETWEEN ? AND ?
         GROUP BY v.Estado
         ORDER BY total DESC",
        'ss',
        [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59']
    );

    if (!$rows) {
        return ['labels' => ['Sin datos'], 'values' => [0]];
    }

    return [
        'labels' => array_map(static function ($row) {
            return ucfirst($row['etiqueta']);
        }, $rows),
        'values' => array_map(static function ($row) {
            return (int)$row['total'];
        }, $rows),
    ];
}

function analyticsGetCapitalBreakdown($conexionDB, $filters)
{
    $rows = analyticsFetchAll(
    $conexionDB,
        "SELECT 
            SUM(cantidad * Precio_Compra) + 
            COALESCE(
                (SELECT Utilidad 
                FROM caja 
                ORDER BY IdCaja DESC 
                LIMIT 1), 
                0
            ) AS capitalTotal
        FROM articulos"
    );

    if (!$rows) {
        return ['labels' => ['Sin datos'], 'values' => [0]];
    }

    return [
        'labels' => array_map(static function ($row) {
            return 'Capital';
        }, $rows),
        'values' => array_map(static function ($row) {
            return round((float)$row['capitalTotal'], 2);
        }, $rows),
    ];
}

function analyticsGetSellerPerformance($conexionDB, $filters)
{
    $rows = analyticsFetchAll(
        $conexionDB,
        "SELECT e.Nombre AS etiqueta, COALESCE(SUM(v.Total), 0) AS total
         FROM ventas v
         INNER JOIN caja ca ON ca.IdCaja = v.Cod_Caja
         INNER JOIN empleados e ON e.IdEmpleado = ca.Cod_Empleado
         INNER JOIN clientes c ON c.Dni = v.dniCliente
         WHERE " . analyticsGetNonCanceledSalesClause('v') . " AND v.Fecha BETWEEN ? AND ?
         GROUP BY e.IdEmpleado, e.Nombre
         ORDER BY total DESC
         LIMIT 8",
        'ss',
        [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59']
    );

    if (!$rows) {
        return ['labels' => ['Sin datos'], 'values' => [0]];
    }

    return [
        'labels' => array_column($rows, 'etiqueta'),
        'values' => array_map(static function ($row) {
            return round((float)$row['total'], 2);
        }, $rows),
    ];
}

function analyticsGetTopProducts($conexionDB, $filters, $limit = 8)
{
    $limit = (int)$limit;
    $limit = $limit > 0 ? $limit : 8;

    return analyticsFetchAll(
        $conexionDB,
        "SELECT a.IdArticulo, a.Nombre,
                COALESCE(SUM(dva.Cantidad), 0) AS cantidadVendida,
                COALESCE(SUM(dva.Ganancias), 0) AS utilidadGenerada
         FROM detalle_venta_articulos dva
         INNER JOIN ventas v ON v.IdVenta = dva.Cod_Venta
         INNER JOIN articulos a ON a.IdArticulo = dva.Cod_Articulo
         LEFT JOIN clientes c ON c.Dni = v.dniCliente
         WHERE " . analyticsGetNonCanceledSalesClause('v') . " AND v.Fecha BETWEEN ? AND ?
         GROUP BY a.IdArticulo, a.Nombre
         ORDER BY cantidadVendida DESC, utilidadGenerada DESC, a.Nombre ASC
         LIMIT {$limit}",
        'ss',
        [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59']
    );
}

function analyticsGetTopClients($conexionDB, $filters, $limit = 10, $sort = 'monto')
{
    $limit = (int)$limit;
    $limit = $limit > 0 ? $limit : 10;
    $sortMap = [
        'monto' => 'montoCompras DESC, utilidadGenerada DESC, cantidadCompras DESC, c.Nombre ASC',
        'utilidad' => 'utilidadGenerada DESC, montoCompras DESC, cantidadCompras DESC, c.Nombre ASC',
        'cantidad' => 'cantidadCompras DESC, montoCompras DESC, utilidadGenerada DESC, c.Nombre ASC',
    ];
    $orderBy = $sortMap[$sort] ?? $sortMap['monto'];

    return analyticsFetchAll(
        $conexionDB,
        "SELECT c.Id_Cliente, c.Dni, c.Nombre, c.Telefono,
                COUNT(v.IdVenta) AS cantidadCompras,
                COALESCE(SUM(v.Total), 0) AS montoCompras,
                COALESCE(SUM(v.utilidad), 0) AS utilidadGenerada,
                MAX(v.Fecha) AS ultimaCompra
         FROM ventas v
         INNER JOIN clientes c ON c.Dni = v.dniCliente
         WHERE " . analyticsGetNonCanceledSalesClause('v') . " AND " . analyticsGetClientExclusionClause('c') . " AND v.Fecha BETWEEN ? AND ?
         GROUP BY c.Id_Cliente, c.Dni, c.Nombre, c.Telefono
         ORDER BY {$orderBy}
         LIMIT {$limit}",
        'ss',
        [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59']
    );
}

function analyticsGetClientRankingChart($conexionDB, $filters, $limit = 10, $sort = 'monto')
{
    $rows = analyticsGetTopClients($conexionDB, $filters, $limit, $sort);
    $valueKey = $sort === 'utilidad' ? 'utilidadGenerada' : ($sort === 'cantidad' ? 'cantidadCompras' : 'montoCompras');

    return [
        'labels' => array_map(static function ($row) {
            return $row['Nombre'];
        }, $rows),
        'values' => array_map(static function ($row) use ($valueKey) {
            return round((float)$row[$valueKey], 2);
        }, $rows),
        'rows' => $rows,
    ];
}

function analyticsGetReportDashboardData($conexionDB, $filters)
{
    return [
        'filters' => $filters,
        'overview' => analyticsGetOverviewMetrics($conexionDB, $filters),
        'sales_series' => analyticsGetSalesSeries($conexionDB, $filters),
        'profit_series' => analyticsGetProfitSeries($conexionDB, $filters),
        'rentability_series' => analyticsGetRentabilitySeries($conexionDB, $filters),
        'expense_series' => analyticsGetExpenseSeries($conexionDB, $filters),
        'payment_methods' => analyticsGetPaymentMethodBreakdown($conexionDB, $filters),
        'status_breakdown' => analyticsGetStatusBreakdown($conexionDB, $filters),
        'capital_breakdown' => analyticsGetCapitalBreakdown($conexionDB, $filters),
        'capitalBreakdown_series' => analyticsGetCapitalBreakdownSeries($conexionDB, $filters),
        'seller_performance' => analyticsGetSellerPerformance($conexionDB, $filters),
        'top_products' => analyticsGetTopProducts($conexionDB, $filters, 8),
        'top_clients' => analyticsGetTopClients($conexionDB, $filters, $filters['top_limit'], $filters['top_metric']),
        'top_clients_chart' => analyticsGetClientRankingChart($conexionDB, $filters, min($filters['top_limit'], 10), $filters['top_metric']),
    ];
}

function analyticsGetClientsListData($conexionDB, $filters, $search = '', $sort = '', $page = 1, $resultsPerPage = 10)
{
    $page = max(1, (int)$page);
    $resultsPerPage = max(1, (int)$resultsPerPage);
    $startFrom = ($page - 1) * $resultsPerPage;

    $types = 'ss';
    $params = [
        $filters['start_date'] . ' 00:00:00',
        $filters['end_date'] . ' 23:59:59',
    ];

    $searchSql = '';
    if ($search !== '') {
        $searchSql = " AND (c.Nombre LIKE ? OR CAST(c.Dni AS CHAR) LIKE ?)";
        $types .= 'ss';
        $searchLike = '%' . $search . '%';
        $params[] = $searchLike;
        $params[] = $searchLike;
    }

    $sortMap = [
        'cantidadCompra' => 'cantidadCompras DESC, montoCompras DESC, gananciaGenerada DESC, c.Nombre ASC',
        'mayorCompra' => 'montoCompras DESC, cantidadCompras DESC, gananciaGenerada DESC, c.Nombre ASC',
        'mayorUtilidad' => 'gananciaGenerada DESC, montoCompras DESC, cantidadCompras DESC, c.Nombre ASC',
    ];
    $orderBy = $sortMap[$sort] ?? 'c.Id_Cliente DESC';

    $activitySql = isset($sortMap[$sort]) ? ' AND (COALESCE(cv.cantidadCompras, 0) > 0 OR COALESCE(cv.montoCompras, 0) > 0 OR COALESCE(cv.gananciaGenerada, 0) > 0)' : '';

    $baseQuery = "
        FROM clientes c
        LEFT JOIN (
            SELECT v.dniCliente,
                   COUNT(v.IdVenta) AS cantidadCompras,
                   COALESCE(SUM(v.Total), 0) AS montoCompras,
                   COALESCE(SUM(v.utilidad), 0) AS gananciaGenerada
            FROM ventas v
            INNER JOIN clientes c2 ON c2.Dni = v.dniCliente
            WHERE " . analyticsGetNonCanceledSalesClause('v') . " AND " . analyticsGetClientExclusionClause('c2') . " AND v.Fecha BETWEEN ? AND ?
            GROUP BY v.dniCliente
        ) cv ON cv.dniCliente = c.Dni
        WHERE " . analyticsGetClientExclusionClause('c') . $searchSql . $activitySql;

    $countRow = analyticsFetchOne(
        $conexionDB,
        "SELECT COUNT(*) AS total {$baseQuery}",
        $types,
        $params
    );

    $rows = analyticsFetchAll(
        $conexionDB,
        "SELECT c.Id_Cliente, c.Dni, c.Nombre, c.direccion, c.Telefono, c.Fecha_Registro,
                COALESCE(cv.cantidadCompras, 0) AS cantidadCompras,
                COALESCE(cv.montoCompras, 0) AS montoCompras,
                COALESCE(cv.gananciaGenerada, 0) AS gananciaGenerada
         {$baseQuery}
         ORDER BY {$orderBy}
         LIMIT {$startFrom}, {$resultsPerPage}",
        $types,
        $params
    );

    return [
        'rows' => $rows,
        'total_records' => isset($countRow['total']) ? (int)$countRow['total'] : 0,
    ];
}

