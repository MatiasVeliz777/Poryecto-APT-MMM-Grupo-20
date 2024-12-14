<?php
include("conexion.php");

header('Content-Type: application/json');

// Última fecha recibida desde el frontend
$last_timestamp = isset($_GET['last_timestamp']) ? $_GET['last_timestamp'] : 0;

// Convertir a formato de fecha si es necesario
$last_timestamp_date = date('Y-m-d H:i:s', $last_timestamp);

// Consultar si hay nuevos registros desde la última fecha
$sql_check = "SELECT 
                soportes.id AS 'ID del Soporte',
                soportes.titulo AS 'Titulo del Soporte',
                soportes.contenido AS 'Contenido del Soporte',
                soportes.urgencia AS 'Urgencia del Soporte',
                soportes.imagen AS 'Imagen del Soporte',
                soportes.fecha_creacion AS 'Fecha de Creación',
                soportes.estado AS 'Estado del Soporte',
                personal.nombre AS 'Nombre del Usuario',
                roles.rol AS 'Nombre del Rol'
              FROM soportes
              JOIN personal ON soportes.rut = personal.rut
              JOIN roles ON soportes.rol_id = roles.id
              WHERE soportes.fecha_creacion > '$last_timestamp_date'
              ORDER BY soportes.fecha_creacion DESC";

$result = $conn->query($sql_check);

$data = [];
$new_timestamp = time();

// Verificar si hay nuevos datos
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Responder con datos y el nuevo timestamp
echo json_encode([
    'new_data' => $data,
    'new_timestamp' => $new_timestamp
]);

$conn->close();
?>
