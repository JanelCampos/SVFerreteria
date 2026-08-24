<?php
    include "../../conexion.php";
    session_start();
    include "../includes/paginador.php";

    $results_per_page = 10;
    $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $consulta = "
        SELECT u.IdEmpleado, u.Nombre, u.Dni, u.Direccion, u.Telefono, u.Email, u.Usuario, u.Rol ,r.rol 
        FROM empleados u 
        INNER JOIN rol r ON u.Rol = r.IdRol 
        ORDER BY u.IdEmpleado DESC
    ";
    $where = "
        empleados u 
        INNER JOIN rol r ON u.rol = r.IdRol 
        ORDER BY u.IdEmpleado DESC
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
    <style>
        #nuevaClave_container {
            display: none;
        }

        #cambiarClave_container {
            display: none;  
        }
    </style>
</head>
<body>
    
    <?php include "../includes/header_2.php"; ?>
	<section id="container">
        <div class="title_container">
            <h1>Listado de usuarios</h1>
            <a href="registro_usuario.php" class="btn_new"><i class="fas fa-user-plus"></i> Crear Usuario</a>
        </div>
        <div class="header_container">
            <div class="row g-3 align-items-end">   
                <div class="col-12 col-md-4 col-lg-3">
                    <label for="busqueda" class="form-label"> Nombre / DNI </label>
                    <input class="form-control filtrosBusqueda" type="text" name="busqueda" id="busqueda" placeholder="Nombre / DNI">
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary flex-grow-1" onclick="buscarUsuario()">Aplicar filtros</button>
                        <a href="lista_usuarios.php" class="btn btn-outline-secondary flex-grow-1">Restablecer</a>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-4 ms-lg-auto">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <button type="button" class="btn btn-danger" onclick="exportarUsuariosPDF()">Exportar PDF</button>
                        <button type="button" class="btn btn-success" onclick="exportarUsuariosEXCEL()">Exportar Excel</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="containerTable">
            <table id="tablaUsuarios">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Dni</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <body>
                    <?php
                        if($row > 0){
                            while ($data = $result->fetch_assoc()){

                            ?>
                                <tr>
                                    <td><?php echo $data["IdEmpleado"]; ?></td>
                                    <td><?php echo $data["Nombre"]; ?></td>
                                    <td><?php echo $data["Dni"]; ?></td>
                                    <td><?php echo $data["Direccion"]; ?></td>
                                    <td><?php echo $data["Telefono"]; ?></td>
                                    <td><?php echo $data["Email"]; ?></td>
                                    <td><?php echo $data["Usuario"]; ?></td>
                                    <td><?php echo $data["rol"]; ?></td>
                                    <td>
                                        <div class="btn-group dropend">
                                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Acciones
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('editarUsuario', <?php echo $data['IdEmpleado']; ?>)">Editar</button></li>
                                                <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('cambiarClave', <?php echo $data['IdEmpleado']; ?>)">Cambiar clave</button></li>
                                                <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('cambiarRol', <?php echo $data['IdEmpleado']; ?>)">Cambiar rol</button></li>
                                                <?php if($data['Rol'] != 1 && $data['IdEmpleado'] != 1) { ?>
                                                    <li><button class="dropdown-item" type="button" onclick="mostrarFormulario('eliminarUsuario', <?php echo $data['IdEmpleado']; ?>)">Eliminar</button></li>
                                                <?php
                                                    }
                                                ?>
                                            </ul>
                                        </div>
                                    </td> 
                                </tr>
                    <?php
                            }
                        }
                    ?>
                </body>
            </table>
        </div>

        <div class="modal fade" id="editarUsuario" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idUsuario" name="idUsuario">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombreUsuario">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">DNI</label>
                            <input type="text" class="form-control" id="dniUsuarioEditar">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccionUsuario">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefonoUsuario">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo</label>
                            <input type="email" class="form-control" id="correoUsuario">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" class="form-control" id="usuario">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" onclick="editarUsuario()">
                            Confirmar
                        </button>
                        <button class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="eliminarUsuario" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Eliminar usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idUsuarioEliminar" name="idUsuarioEliminar">
                        <div class="mb-3">
                            <p class="bg-danger text-white p-2">¿Esta seguro de eliminar el usuario? esta acción no se puede revertir.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="eliminarUsuario()">Confirmar</button>
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="cambiarRol" class="modal fade">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cambiar rol</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idUsuarioRol" name="idUsuarioRol">
                        <div class="mb-3">
                            <label for="rolActual">Rol actual</label>
                            <input type="text" id="rolActual" name="rolActual" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="nuevoRol">Nuevo rol</label>
                            <select name="nuevoRol" id="nuevoRol">
                                <option value="" selected>-Seleccionar-</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="cambiarRol()">Confirmar</button>
                        <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('cambiarRol')">Cancelar</button>
                    </div>
                </div>
            </div>  
        </div>

        <div id="cambiarClave" class="modal fade">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cambiar clave</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="idUsuarioClave" name="idUsuarioClave">
                        <div class="mb-3" id="claveActual_container">
                            <label for="claveActual">Ingrese su clave actual</label>
                            <input type="password" id="claveActual" name="claveActual">
                        </div>
                        <div class="mb-3" id="nuevaClave_container">
                            <label for="nuevaClave">Ingrese la clave nueva</label>
                            <input type="password" id="nuevaClave" name="nuevaClave">
                            <label for="nuevaClaveRepetida">Repita nuevamente la clave</label>
                            <input type="password" id="nuevaClaveRepetida" name="nuevaClaveRepetida">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div id="verificarClave_container">
                            <button class="btn btn-primary" type="button" onclick="verificarClave()">Siguiente</button>
                            <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('cambiarClave')">Cancelar</button>
                        </div>
                        <div id="cambiarClave_container">
                            <button class="btn btn-primary" type="button" onclick="cambiarClave()">Confirmar</button>
                            <button class="btn btn-secondary" type="button" onclick="ocultarFormulario('cambiarClave')">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>    
        </div>

        <?php
        // Renderizar el paginador
            renderPaginator($total_records, $results_per_page, $current_page, 'lista_usuarios.php');
        ?>
        <div id="paginator"></div>
	</section>
	<?php include "../includes/footer_2.php"; ?>
</body>
</html>