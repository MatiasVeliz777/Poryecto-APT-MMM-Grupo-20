<?php
include('conexion.php');

if (isset($_GET['id_respuesta'])) {
    $id_respuesta = $_GET['id_respuesta'];

    // Obtener el id_pregunta asociado a la respuesta
    $query = "SELECT id_pregunta FROM respuestas_encuesta WHERE id_respuesta = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error en la consulta SQL para id_pregunta']);
        exit;
    }

    $stmt->bind_param('i', $id_respuesta);
    $stmt->execute();
    $stmt->bind_result($id_pregunta);
    $stmt->fetch();
    $stmt->close();

    if (!$id_pregunta) {
        echo json_encode(['success' => false, 'message' => 'No se encontró id_pregunta para la respuesta proporcionada']);
        exit;
    }

    // Obtener las opciones de la tabla opciones_encuesta basadas en el id_pregunta
    $query_opciones = "SELECT opcion FROM opciones_encuesta WHERE id_pregunta = ?";
    $stmt_opciones = $conn->prepare($query_opciones);
    if (!$stmt_opciones) {
        echo json_encode(['success' => false, 'message' => 'Error en la consulta SQL para obtener opciones']);
        exit;
    }

    $stmt_opciones->bind_param('i', $id_pregunta);
    $stmt_opciones->execute();
    $result_opciones = $stmt_opciones->get_result();

    if ($result_opciones->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No se encontraron opciones para esta pregunta']);
        exit;
    }

    $opciones = [];
    while ($row = $result_opciones->fetch_assoc()) {
        $opciones[] = $row['opcion'];
    }
    $stmt_opciones->close();

    // Devolver las opciones en formato JSON
    echo json_encode(['success' => true, 'opciones' => $opciones]);
} else {
    echo json_encode(['success' => false, 'message' => 'ID de respuesta no proporcionado']);
}

$conn->close();
?>
