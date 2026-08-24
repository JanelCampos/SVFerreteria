<?php
    include "../../conexion.php";
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents('php://input'), true);

    $idProveedor = isset($input['idProveedor']) ? $input['idProveedor'] : null;

    if($idProveedor === '' && $idProveedor === null){
        echo json_encode(['resultado' => false, 'mensaje' => 'El id no puede estar vacio']);
    }else{
        $query = $conexionDB->prepare("
            SELECT * 
            FROM articulos
            WHERE Cod_Proveedor = ?
        ");
        $query->bind_param("i",$idProveedor);
        $query->execute();
        $result = $query->get_result();
        $row = $result->num_rows;
        if($row <= 0){
            $query_delete = $conexionDB->prepare("
                DELETE 
                FROM proveedores
                WHERE IdProveedor = ?
            ");
            $query_delete->bind_param("i", $idProveedor);
            if($query_delete->execute()){
                echo json_encode(['resultado' => true, 'mensaje' => 'El registro ha sido eliminado correctamente']);
            }else{
                echo json_encode(['resultado' => false, 'mensaje' => 'No se pudo eliminar el registro']);
            }
        }else{
            echo json_encode(['resultado' => false, 'mensaje' => 'No se puede eliminar el proveedor, ya que hay articulos asociados']);
        }
    }
?>