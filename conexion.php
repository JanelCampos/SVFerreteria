<?php

    require_once __DIR__."/sistema/includes/zona_horaria.php";

    $HOST = "localhost";
    $DB_USER = "root";
    $DB_PASSWORD = "";
    $DB_NAME = "db_ferreteria";

    try {
        $conexionDB = new mysqli("$HOST", "$DB_USER", "$DB_PASSWORD", "$DB_NAME");
        if ($conexionDB->connect_error){
            die("Ocurrió un error al conectar la base de datos!");
        }

        // Zona horaria de Perú para esta conexión MySQL/MariaDB
        $conexionDB->query("SET time_zone = '-05:00'");
    }
    catch (Exception $ex){
        echo "Ocurrió un error al conectarse a la base de datos!".$ex->getMessage();
    }

?>