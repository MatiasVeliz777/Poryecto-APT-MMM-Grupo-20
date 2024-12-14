<?php
include("conexion.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $solicitud_id = (int) $_POST['solicitud_id'];

    // Consulta para eliminar la solicitud
    $sql = "DELETE FROM solicitudes WHERE id = $solicitud_id";

    if ($conn->query($sql) === TRUE) {
        header("Location: solicitudes.php?msg=Solicitud eliminada con éxito");
    } else {
        echo "Error al eliminar la solicitud: " . $conn->error;
    }
}
?>