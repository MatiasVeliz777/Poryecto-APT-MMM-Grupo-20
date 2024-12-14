<?php
session_start();
include("conexion.php"); // Conexión a la base de datos

// Verifica si el usuario está logeado
if (!isset($_SESSION['rut'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

$rut = $_SESSION['rut']; // RUT del usuario logeado

// Actualiza las notificaciones a "leída"
$query = "UPDATE notificaciones SET leida = 1 WHERE rut = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $rut);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
