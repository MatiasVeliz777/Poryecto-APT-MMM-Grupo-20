<?php
include('conexion.php');
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

// Verificar si se ha enviado el ID de la pregunta a eliminar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_pregunta'])) {
    $id_pregunta = $_POST['id_pregunta'];

    // Consulta para eliminar la pregunta (el trigger o ON DELETE CASCADE eliminará también las respuestas)
    $query_eliminar = "DELETE FROM preguntas_encuesta WHERE id_pregunta = ?";
    $stmt = $conn->prepare($query_eliminar);
    $stmt->bind_param('i', $id_pregunta);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Pregunta eliminada']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la pregunta']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error: no se recibió ningún ID de pregunta']);
}
?>
