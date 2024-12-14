<?php
session_start();
include("conexion.php"); // Tu archivo de conexión a la base de datos

try {
    // SQL para eliminar las solicitudes de soporte
    $sql = "DELETE FROM soportes
            WHERE estado = 'Solucionado'
            AND fecha_solucionado IS NOT NULL
            AND DATE(fecha_solucionado) <= CURDATE()";

    // Ejecutar la consulta
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Mostrar un mensaje si se eliminan registros
    if ($stmt->rowCount() > 0) {
        echo "Se han eliminado " . $stmt->rowCount() . " solicitudes de soporte.";
    } else {
        echo "No hay solicitudes de soporte para eliminar.";
    }

} catch (PDOException $e) {
    // Capturar y mostrar errores de la base de datos
    echo "Error de conexión o ejecución: " . $e->getMessage();
} finally {
    // Cerrar la conexión
    $conn = null;
}
?>
