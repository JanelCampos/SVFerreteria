<?php
include "conexion.php";
include "sistema/includes/zona_horaria.php";

function backupDatabase($host, $user, $pass, $dbname, $backupFile) {
    $mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump'; // Ajusta esta ruta según tu instalación
    $command = "$mysqldumpPath --host=$host --user=$user --password=$pass $dbname > $backupFile 2> error_log.txt";
    exec($command, $output, $return_var);
    return $return_var === 0;
}

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'db_pachacutec';
$backupDir = 'backups/';
$backupFile = $backupDir . 'backup_' . date('d-m-Y_H-i-s') . '.sql';

// Crear el directorio de backups si no existe
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

if (backupDatabase($host, $user, $pass, $dbname, $backupFile) && filesize($backupFile) > 0) {
    // Mantener solo los 5 backups más recientes
    $backups = glob($backupDir . '*.sql');
    if (count($backups) > 5) {
        // Ordenar los archivos por fecha de modificación, del más antiguo al más reciente
        usort($backups, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        // Eliminar los backups más antiguos, dejando solo los 5 más recientes
        while (count($backups) > 5) {
            $oldestBackup = array_shift($backups);
            unlink($oldestBackup);
        }
    }

    echo json_encode(['resultado' => true, 'mensaje' => 'Backup realizado exitosamente.', 'archivo' => $backupFile]);
} else {
    $error_log = file_get_contents('error_log.txt');
    echo json_encode(['resultado' => false, 'mensaje' => 'Error al realizar el backup.', 'error' => $error_log]);
}
?>
