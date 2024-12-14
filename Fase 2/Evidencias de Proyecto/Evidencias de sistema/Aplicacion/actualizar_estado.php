<?php
session_start();
include("conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $soporte_id = $_POST['soporte_id'];
    $nuevo_estado = $_POST['estado'];
    
    if (empty($soporte_id) || empty($nuevo_estado)) {
        die("Error: Datos incompletos. Soporte ID: $soporte_id, Estado: $nuevo_estado");
    }

    // Actualizar el estado y, si es "Solucionado", actualizar fecha_solucionado
    if ($nuevo_estado == 'Solucionado') {
        $sql = "UPDATE soportes SET estado = ?, fecha_solucionado = NOW() WHERE id = ?";
    } else {
        $sql = "UPDATE soportes SET estado = ?, fecha_solucionado = NULL WHERE id = ?";
        
    }

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Error en la preparación de la consulta: ' . $conn->error);
    }

    $stmt->bind_param('si', $nuevo_estado, $soporte_id);

    if ($stmt->execute()) {
        header('Location: soporte_def.php?mensaje=Estado actualizado');
        exit();
    } else {
        echo "Error al actualizar el estado: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

?>
