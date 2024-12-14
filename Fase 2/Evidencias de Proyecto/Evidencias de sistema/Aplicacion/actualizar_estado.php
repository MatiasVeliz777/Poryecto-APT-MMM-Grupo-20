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
        $query = "SELECT rut FROM soportes WHERE id = ?";
        $stmt = $conn->prepare($query);
    
        $stmt->bind_param("i", $soporte_id);
        $stmt->execute();
    
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    
        $rut_preg = $row['rut'];
    
    
        $mensaje = "💻¡Soporte Ejecutado!💻 tu solciitud de soporte fue solucionada correctamente!";
        $query = "
            INSERT INTO notificaciones (rut, mensaje, fecha_creacion)
            VALUES (?, ?, NOW())
        ";
    
        // Preparamos la consulta
        $stmt = $conn->prepare($query);
    
        // Verificamos si la preparación fue exitosa
        if ($stmt) {
            // Vinculamos los parámetros
            $stmt->bind_param("ss", $rut_preg, $mensaje); // 'ss' indica que ambos son cadenas
    
            // Ejecutamos la consulta
            if ($stmt->execute()) {
            } 
        } else {
        }
        header('Location: soporte_def.php?mensaje=Estado actualizado');
        exit();
    } else {
        echo "Error al actualizar el estado: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

?>
