<?php
    include "../../conexion.php";
    session_start();
    include "../includes/paginador.php";

    if ($_SESSION['rol'] != 1) {
        header("Location: ../index.php");
        exit;
    }

    $results_per_page = 10;
    $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;

    $consulta = "
        SELECT IdCategoria, Nombre, Descripcion, Estado, FechaCreacion
        FROM categorias
        WHERE Estado = 1
        ORDER BY IdCategoria DESC
    ";
    $where = "categorias WHERE Estado = 1 ORDER BY IdCategoria DESC";

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
        <div class="title_container">
            <h1>Listado de categorías</h1>
            <a href="registro_categoria.php" class="btn_new"><i class="fas fa-plus"></i> Crear categoría</a>
        </div>
        <div class="header_container">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="busquedaCategoria">Nombre</label>
                    <input class="filtrosBusqueda" type="text" name="busquedaCategoria" id="busquedaCategoria" placeholder="Nombre de categoría">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" onclick="buscarCategoria()">Aplicar filtros</button>
                        <a href="lista_categorias.php" class="btn btn-outline-secondary">Restablecer</a>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-4 ms-lg-auto">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <button type="button" class="btn btn-danger" onclick="exportarCategoriasPDF()">Exportar PDF</button>
                        <button type="button" class="btn btn-success" onclick="exportarCategoriasEXCEL()">Exportar Excel</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="containerTable">
            <table id="tablaCategorias">
                <thead>
                    <tr>
                        <th>Nro.</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Fecha de creación</th>
                        <th>Cant. artículos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($row > 0) {
                        while ($data = $result->fetch_assoc()) {
                            $idCat = $data['IdCategoria'];
                            $query_count = $conexionDB->prepare("SELECT COUNT(*) as cant FROM articulos WHERE Cod_Categoria = ?");
                            $query_count->bind_param("i", $idCat);
                            $query_count->execute();
                            $count = $query_count->get_result()->fetch_assoc()['cant'];
                            $query_count->close();
                    ?>
                            <tr>
                                <td><?php echo $data["IdCategoria"]; ?></td>
                                <td><?php echo $data["Nombre"]; ?></td>
                                <td><?php echo empty($data["Descripcion"]) ? '-' : $data["Descripcion"]; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($data["FechaCreacion"])); ?></td>
                                <td><span class="badge bg-primary"><?php echo $count; ?></span></td>
                                <td>
                                    <div class="btn-group dropend">
                                        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Acciones
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('editarCategoria', <?php echo $data['IdCategoria']; ?>)">Editar</button></li>
                                            <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('eliminarCategoria', <?php echo $data['IdCategoria']; ?>)">Eliminar</button></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                    <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div id="eliminarCategoria" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Eliminar categoría</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idCategoriaEliminar" name="idCategoriaEliminar">
                        <p class="text-danger"><strong>Importante:</strong> Si la categoría tiene artículos asignados, no se podrá eliminar. Primero reasigne los artículos a otra categoría.</p>
                        <div class="mb-3">
                            <p>¿Está seguro de eliminar la categoría?</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="eliminarCategoria()">Confirmar</button>
                        <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('eliminarCategoria')">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="editarCategoria" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar categoría</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idCategoriaeditar" name="idCategoriaeditar">
                        <div class="mb-3">
                            <label for="nombreCategoriaEditar">Nombre</label>
                            <input type="text" name="nombreCategoriaEditar" id="nombreCategoriaEditar" placeholder="Nombre de categoría" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcionCategoriaEditar">Descripción</label>
                            <input type="text" name="descripcionCategoriaEditar" id="descripcionCategoriaEditar" placeholder="Descripción (opcional)">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="editarCategoria()">Confirmar</button>
                        <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('editarCategoria')">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <?php
        renderPaginator($total_records, $results_per_page, $current_page, 'lista_categorias.php');
        ?>
        <div id="paginator"></div>
    </section>
    <?php include "../includes/footer_2.php"; ?>
</body>
</html>
