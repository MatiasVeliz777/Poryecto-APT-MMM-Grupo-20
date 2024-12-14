<?php
session_start();
include("conexion.php"); // Tu archivo de conexión a la base de datos

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener el ID de la notificación a eliminar
    $data = json_decode(file_get_contents("php://input"), true);
    $notif_id = $data['id'];

    // Obtener el RUT del usuario logeado
    $rut = $_SESSION['rut']; 

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // Consulta para eliminar la notificación
    $sql = "DELETE FROM notificaciones WHERE id = ? AND rut = ?";

    // Preparar y ejecutar la consulta
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("is", $notif_id, $rut); // 'i' para integer, 's' para string
        $stmt->execute();

        // Verificar si se eliminó la notificación
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró la notificación o no se pudo eliminar']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta']);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
