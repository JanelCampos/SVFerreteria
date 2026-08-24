<?php
    include "../../conexion.php";
    session_start();
    include "../includes/paginador.php";

    $results_per_page = 10;
    $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    
    $consulta = "
        SELECT *
        FROM proveedores
        ORDER BY IdProveedor DESC
    ";
    $where = "
        proveedores
        ORDER BY IdProveedor DESC
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
        <div class="title_container">
            <h1>Listado de proveedores</h1>
            <a href="registro_proveedor.php" class="btn_new"><i class="fas fa-plus"></i> Crear proveedor</a>
        </div>
        <div class="header_container">
            <div class="row g-3 align-items-end">   
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="busquedaProveedor">Nombre</label>
                    <input class="filtrosBusqueda" type="text" name="busquedaProveedor" id="busquedaProveedor" placeholder="Nombre">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" onclick="buscarProveedor()">Aplicar filtros</button>
                        <a href="lista_proveedores.php" class="btn btn-outline-secondary">Restablecer</a>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-4 ms-lg-auto">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <button type="button" class="btn btn-danger" onclick="exportarProveedoresPDF()">Exportar PDF</button>
                        <button type="button" class="btn btn-success" onclick="exportarProveedoresEXCEL()">Exportar Excel</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="containerTable">
            <table id="tablaProveedores">
                <thead>
                    <tr>
                        <th>Nro.</th>
                        <th>Ruc</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if($row > 0){
                            while ($data = $result->fetch_assoc()){
                            ?>
                                <tr>
                                    <td><?php echo $data["IdProveedor"]; ?></td>
                                    <td><?php echo $data['ruc']; ?></td>
                                    <td><?php echo $data["Nombre"]; ?></td>
                                    <td><?php echo $data["Direccion"]; ?></td>
                                    <td><?php echo $data["Telefono"]; ?></td>
                                    <td><?php echo $data["Email"]; ?></td>
                                    <td>
                                        <div class="btn-group dropend">
                                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Acciones
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('editarProveedor', <?php echo $data['IdProveedor']; ?>)">Editar</button></li>
                                                <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('eliminarProveedor', <?php echo $data['IdProveedor']; ?>)">Eliminar</button></li>
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
        

        <div id="eliminarProveedor" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Eliminar proveedor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idProveedorEliminar" name="idProveedorEliminar">
                        <div class="mb-3">
                            <p>¿Esta seguro de eliminar el usuario?</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="eliminarProveedor()">Confirmar</button>
                        <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('eliminarProveedor')">Cancelar</button>
                    </div>
                </div>
            </div> 
        </div>

        <div id="editarProveedor" class="modal fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Editar proveedor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idProveedoreditar" name="idProveedoreditar">
                        <div class="mb-3">
                            <label for="ruc">Ruc</label>
                            <input type="number" name="ruc" id="ruc" placeholder="Ingrese el ruc">
                        </div>
                        <div class="mb-3">
                            <label for="nombreProveedorEditar">Nombre</label>
                            <input type="text" name="nombreProveedorEditar" id="nombreProveedorEditar" placeholder="Ingrese el Nombre">
                        </div>
                        <div class="mb-3">
                            <label for="direccionProveedorEditar">Dirección</label>
                            <input type="text" name="direccionProveedorEditar" id="direccionProveedorEditar" placeholder="Ingrese una Dirección">
                        </div>
                        <div class="mb-3">
                            <label for="telefonoProveedorEditar">Teléfono</label>
                            <input type="number" name="telefonoProveedorEditar" id="telefonoProveedorEditar" placeholder="Ingrese un Teléfono" oninput="limitarDigitos(this,9)">
                        </div>
                        <div class="mb-3">
                            <label for="correoProveedorEditar">Email</label>
                            <input type="email" name="correoProveedorEditar" id="correoProveedorEditar" placeholder="Ingrese un Correo electrónico">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="editarProveedor()">Confirmar</button>
                        <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('editarProveedor')">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Renderizar el paginador
            renderPaginator($total_records, $results_per_page, $current_page, 'lista_proveedores.php');
        ?>
        <div id="paginator"></div>
	</section>
	<?php include "../includes/footer_2.php"; ?>
</body>
</html>