<?php

include('conexion.php');
session_start();

// Consulta SQL revisada
$query = "
    SELECT 
        DATE_FORMAT(r.fecha_respuesta, '%Y-%m') AS mes, 
        COUNT(*) AS total_respuestas
    FROM 
        respuestas_encuesta r
    JOIN 
        usuarios u ON r.rut_usuario = u.rut
    WHERE 
        u.activo = 1 
        AND MONTH(r.fecha_respuesta) = MONTH(CURRENT_DATE()) 
        AND YEAR(r.fecha_respuesta) = YEAR(CURRENT_DATE())
    GROUP BY 
        mes
";

// Ejecutar la consulta
$result = $conn->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Convertir los datos a JSON
header('Content-Type: application/json');
echo json_encode($data);
?>