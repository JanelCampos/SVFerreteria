<?php
require('conexion.php');
session_start();

header('Content-Type: application/json');

function detectarDispositivo($userAgent) {
    $userAgent = strtolower($userAgent);
    if (strpos($userAgent, 'android') !== false || strpos($userAgent, 'iphone') !== false || strpos($userAgent, 'ipod') !== false) {
        return 'Móvil';
    } elseif (strpos($userAgent, 'ipad') !== false || strpos($userAgent, 'tablet') !== false) {
        return 'Tablet';
    } else {
        return 'Escritorio';
    }
}

function getIPCliente() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($arr[0]);
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function registrarAuditoriaLogin($conexionDB, $codEmpleado, $exito, $motivoFallo = null) {
    $ip = getIPCliente();
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 250) : '';
    $dispositivo = detectarDispositivo($userAgent);
    $query = $conexionDB->prepare("
        INSERT INTO auditoria_login (Cod_Empleado, IP, UserAgent, Dispositivo, Exito, MotivoFallo)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    if ($query) {
        $exitoInt = $exito ? 1 : 0;
        $query->bind_param("isssis", $codEmpleado, $ip, $userAgent, $dispositivo, $exitoInt, $motivoFallo);
        $query->execute();
        $query->close();
    }
}

$data = json_decode(file_get_contents('php://input'), true);
$alert = '';

if (!empty($_SESSION['active'])) {
    echo json_encode(['resultado' => true]);
    exit;
} else {
    $Usuario = $conexionDB->escape_string($data['usuario']);
    $Clave = $conexionDB->escape_string($data['clave']);

    $query = mysqli_query($conexionDB, "SELECT e.IdEmpleado, e.Nombre, e.Dni, e.Email, e.Usuario, e.Telefono,e.Clave, r.IdRol, r.rol
                                        FROM empleados e
                                        INNER JOIN rol r ON r.IdRol = e.Rol 
                                        WHERE e.Usuario = '$Usuario'");
    $result = mysqli_fetch_array($query);

    if ($result) {
        $idEmpleado = intval($result['IdEmpleado']);
        if (password_verify($Clave, $result['Clave'])) {
            $_SESSION['active'] = true;
            $_SESSION['idUser'] = $result['IdEmpleado'];
            $_SESSION['nombre'] = $result['Nombre'];
            $_SESSION['dni'] = $result['Dni'];
            $_SESSION['email'] = $result['Email'];
            $_SESSION['user'] = $result['Usuario'];
            $_SESSION['rol'] = $result['IdRol'];
            $_SESSION['rol_name'] = $result['rol'];
            $_SESSION['telefono'] = $result['Telefono'];

            registrarAuditoriaLogin($conexionDB, $idEmpleado, true, null);

            echo json_encode(['resultado' => true]);
            exit;
        } else {
            $alert = 'El usuario o la clave son incorrectos!!';
            registrarAuditoriaLogin($conexionDB, $idEmpleado, false, 'Clave incorrecta');
            session_destroy();
            echo json_encode(['resultado' => false, 'mensaje' => $alert]);
            exit;
        }
    } else {
        $alert = 'El usuario o la clave son incorrectos!!';
        registrarAuditoriaLogin($conexionDB, null, false, 'Usuario no encontrado: ' . $Usuario);
        session_destroy();
        echo json_encode(['resultado' => false, 'mensaje' => $alert]);
        exit;
    }
    $conexionDB->close();
}
?>
