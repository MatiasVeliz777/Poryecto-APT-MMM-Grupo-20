<?php
include('conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_respuesta = $_POST['id_respuesta'];
    $nueva_respuesta = $_POST['nueva_respuesta'];
    $nueva_calificacion = $_POST['nueva_calificacion'];

    $sql = "UPDATE respuestas_encuesta SET respuesta = ?, calificacion = ? WHERE id_respuesta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sii', $nueva_respuesta, $nueva_calificacion, $id_respuesta);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }

}



$conn->close();
?>
