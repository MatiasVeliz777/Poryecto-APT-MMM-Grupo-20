<?php
session_start();
include("conexion.php"); // Tu archivo de conexión a la base de datos

// Obtén el RUT del usuario logeado
$rut = $_SESSION['rut']; // Asegúrate de que esta variable existe después de iniciar sesión

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener las notificaciones
$sql = "SELECT id, mensaje, fecha_creacion, leida FROM notificaciones WHERE rut = '$rut'  ORDER BY fecha_creacion DESC";


$result = $conn->query($sql);

$notificaciones = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notificaciones[] = $row;
    }
}

// Retornar las notificaciones en formato JSON
header('Content-Type: application/json');
echo json_encode($notificaciones);

$conn->close();
?>