<?php
// Conectar a la base de datos
include('conexion.php');

// Función para enviar una notificación
function enviar_notificacion($rut_usuario, $mensaje, $conn) {
    $sql = "INSERT INTO notificaciones (rut, mensaje, fecha_creacion) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $rut_usuario, $mensaje);

    if ($stmt->execute()) {
        echo "Notificación enviada al usuario con RUT: $rut_usuario\n";
    } else {
        echo "Error al enviar la notificación: " . $conn->error . "\n";
    }

    $stmt->close();
}

// Obtener la fecha de hoy y calcular la fecha de mañana
$hoy = date('Y-m-d');
$manana = date('Y-m-d', strtotime('+1 day'));

// Consultar los eventos que se realizarán mañana
$sql_eventos = "SELECT e.id AS evento_id, e.titulo, e.fecha, a.rut_usuario 
                FROM eventos e
                JOIN asistencias_eventos a ON e.id = a.evento_id
                WHERE e.fecha = ?";
$stmt = $conn->prepare($sql_eventos);
$stmt->bind_param("s", $manana);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si hay eventos que se realizarán mañana
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $evento_titulo = $row['titulo'];
        $evento_fecha = $row['fecha'];
        $rut_usuario = $row['rut_usuario'];

        // Crear el mensaje de la notificación
        $mensaje = "El evento \"$evento_titulo\" se realizará mañana ($evento_fecha). Para más información, ingresa al portal de eventos.";

        // Enviar la notificación
        enviar_notificacion($rut_usuario, $mensaje, $conn);
    }
} else {
    echo "No hay eventos programados para mañana.\n";
}

// Cerrar la conexión
$stmt->close();


// Consultar las capacitaciones que se realizarán mañana
$sql_capacitaciones = "SELECT c.id AS capacitacion_id, c.titulo, c.fecha, ac.rut_usuario 
                       FROM capacitaciones c
                       JOIN asistencia_capacitaciones ac ON c.id = ac.capacitacion_id
                       WHERE c.fecha = ?";
$stmt_c = $conn->prepare($sql_capacitaciones);
$stmt_c->bind_param("s", $manana);
$stmt_c->execute();
$result_c = $stmt_c->get_result();

// Verificar si hay capacitaciones que se realizarán mañana
if ($result_c->num_rows > 0) {
    while ($row = $result_c->fetch_assoc()) {
        $capacitacion_titulo = $row['titulo'];
        $capacitacion_fecha = $row['fecha'];
        $rut_usuario = $row['rut_usuario'];

        // Crear el mensaje de la notificación
        $mensaje = "La capacitación \"$capacitacion_titulo\" se realizará mañana ($capacitacion_fecha). Para más información, ingresa al portal de capacitaciones.";

        // Enviar la notificación
        enviar_notificacion($rut_usuario, $mensaje, $conn);
    }
} else {
    echo "No hay capacitaciones programadas para mañana.\n";
}

// Cerrar la conexión
$stmt_c->close();


$conn->close();

?>
