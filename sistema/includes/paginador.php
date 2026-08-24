<?php
    function getPaginatedData($conexionDB, $table, $page, $results_per_page) {
        if($page == 0){
            $page = 1;
        }
        // Calcular el índice inicial
        $start_from = ($page - 1) * $results_per_page;

        // Obtener el total de registros
        $sql_total = "SELECT COUNT(*) AS total FROM $table";
        $result_total = $conexionDB->query($sql_total);
        $row_total = $result_total->fetch_assoc();
        $total_records = $row_total['total'];

        // Obtener los datos paginados
        $sql = "SELECT * FROM $table LIMIT $start_from, $results_per_page";
        $result = $conexionDB->query($sql);

        // Devolver los datos y el total de registros
        return [$result, $total_records];
    }

    function getPaginatedDataAll($conexionDB, $consulta, $where, $page, $results_per_page){
        if($page == 0){
            $page = 1;
        }
        $start_from = ($page - 1) * $results_per_page;

        // Obtener el total de registros
        $sql_total = "SELECT COUNT(*) AS total FROM $where";
        $result_total = $conexionDB->query($sql_total);
        $row_total = $result_total->fetch_assoc();
        $total_records = $row_total['total'];

        // Obtener los datos paginados
        $sql = "$consulta LIMIT $start_from, $results_per_page";
        $result = $conexionDB->query($sql);

        // Devolver los datos y el total de registros
        return [$result, $total_records];
    }

    function getPaginatedDataArticulos($conexionDB, $consulta, $where, $page, $results_per_page){
        if($page == 0){
            $page = 1;
        }
        $start_from = ($page - 1) * $results_per_page;

        // Obtener el total de registros
        $sql_total = "SELECT COUNT(*) AS total FROM $where";
        $result_total = $conexionDB->query($sql_total);
        $row_total = $result_total->fetch_assoc();
        $total_records = $row_total['total'];

        // Obtener los datos paginados
        $sql = "$consulta LIMIT $start_from, $results_per_page";
        $result = $conexionDB->query($sql);

        // Devolver los datos y el total de registros
        return [$result, $total_records];
    }

    function getPaginatedDataVentas($conexionDB, $consulta, $where, $page, $results_per_page){
        if($page == 0){
            $page = 1;
        }
        $start_from = ($page - 1) * $results_per_page;

        // Obtener el total de registros
        $sql_total = "SELECT COUNT(*) AS total FROM $where";
        $result_total = $conexionDB->query($sql_total);
        $row_total = $result_total->fetch_assoc();
        $total_records = $row_total['total'];

        // Obtener los datos paginados
        $sql = "$consulta LIMIT $start_from, $results_per_page";
        $result = $conexionDB->query($sql);

        // Devolver los datos y el total de registros
        return [$result, $total_records];
    }

    function getPaginatedDataGastos($conexionDB, $consulta, $where, $page, $results_per_page){
        if($page == 0){
            $page = 1;
        }
        $start_from = ($page - 1) * $results_per_page;

        // Obtener el total de registros
        $sql_total = "SELECT COUNT(*) AS total FROM $where";
        $result_total = $conexionDB->query($sql_total);
        $row_total = $result_total->fetch_assoc();
        $total_records = $row_total['total'];

        // Obtener los datos paginados
        $sql = "$consulta LIMIT $start_from, $results_per_page";
        $result = $conexionDB->query($sql);

        // Devolver los datos y el total de registros
        return [$result, $total_records];
    }

    function getPaginatedDataPMV($conexionDB, $consulta, $where, $page, $results_per_page){
        if($page == 0){
            $page = 1;
        }
        $start_from = ($page - 1) * $results_per_page;

        // Obtener el total de registros
        $sql_total = "SELECT COUNT(*) AS total FROM (SELECT a.IdArticulo $where) AS sub";
        $result_total = $conexionDB->query($sql_total);
        $row_total = $result_total->fetch_assoc();
        $total_records = $row_total['total'];

        // Obtener los datos paginados
        $sql = "$consulta LIMIT $start_from, $results_per_page";
        $result = $conexionDB->query($sql);

        // Devolver los datos y el total de registros
        return [$result, $total_records];
    }

    function renderPaginator($total_records, $results_per_page, $current_page, $page_url) {
        $total_pages = ceil($total_records / $results_per_page);
        $page_range = 2; // Número de páginas que se mostrarán a la izquierda y derecha de la página actual
        $baseParams = $_GET;
        unset($baseParams['page']);

        $buildUrl = function ($page) use ($page_url, $baseParams) {
            $queryParams = $baseParams;
            $queryParams['page'] = $page;
            return $page_url . '?' . http_build_query($queryParams);
        };
    
        echo '<nav aria-label="Page navigation" id="paginador" class="pagination-nav">';
        echo '<ul class="pagination flex-wrap justify-content-center">';
    
        // Enlace a la primera página
        echo '<li class="page-item"><a class="page-link" href="'.$buildUrl(1).'">Primera</a></li>';
    
        // Enlace a la página anterior
        if ($current_page > 1) {
            echo '<li class="page-item"><a class="page-link" href="'.$buildUrl($current_page - 1).'">Anterior</a></li>';
        }
    
        // Calcular el rango de páginas a mostrar
        $start_page = max(1, $current_page - $page_range);
        $end_page = min($total_pages, $current_page + $page_range);
    
        // Si el rango de páginas no comienza desde la primera página, mostrar puntos suspensivos
        if ($start_page > 1) {
            echo '<li class="page-item"><a class="page-link" href="'.$buildUrl(1).'">1</a></li>';
            if ($start_page > 2) {
                echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
            }
        }
    
        // Enlaces de números de páginas
        for ($i = $start_page; $i <= $end_page; $i++) {
            $active = $current_page == $i ? 'active' : '';
            echo '<li class="page-item '.$active.'"><a class="page-link" href="'.$buildUrl($i).'">'.$i.'</a></li>';
        }
    
        // Si el rango de páginas no termina en la última página, mostrar puntos suspensivos
        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) {
                echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
            }
            echo '<li class="page-item"><a class="page-link" href="'.$buildUrl($total_pages).'">'.$total_pages.'</a></li>';
        }
    
        // Enlace a la página siguiente
        if ($current_page < $total_pages) {
            echo '<li class="page-item"><a class="page-link" href="'.$buildUrl($current_page + 1).'">Siguiente</a></li>';
        }
    
        // Enlace a la última página
        echo '<li class="page-item"><a class="page-link" href="'.$buildUrl($total_pages).'">Última</a></li>';
    
        echo '</ul>';
        echo '</nav>';
    }
    
?>
