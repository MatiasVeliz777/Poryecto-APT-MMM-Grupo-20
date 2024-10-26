<?php
session_start();
include("conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener el ID del soporte y el nuevo estado desde el formulario
    $soporte_id = $_POST['soporte_id'];
    $nuevo_estado = $_POST['estado'];

    // Depuración: Verificar los datos recibidos
    if (empty($soporte_id) || empty($nuevo_estado)) {
        die("Error: Datos incompletos. Soporte ID: $soporte_id, Estado: $nuevo_estado");
    }

    // Actualizar el estado de la solicitud en la base de datos
    $sql = "UPDATE soportes SET estado = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Error en la preparación de la consulta: ' . $conn->error);
    }

    $stmt->bind_param('si', $nuevo_estado, $soporte_id);

    if ($stmt->execute()) {
        // Redireccionar o mostrar un mensaje de éxito
        header('Location: soporte_def.php?mensaje=Estado actualizado');
        exit();
    } else {
        // Manejar el error
        echo "Error al actualizar el estado: " . $stmt->error;
    }

    // Cerrar la conexión
    $stmt->close();
    $conn->close();
}

?>
