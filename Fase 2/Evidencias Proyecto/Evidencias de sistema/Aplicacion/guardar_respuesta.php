<?php
include('conexion.php'); // Conexión a la base de datos

session_start();

header('Content-Type: application/json'); // Asegúrate de que la respuesta sea JSON

if (!isset($_SESSION['rut'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Conexión a la base de datos fallida']);
    exit();
}

$usuario = $_SESSION['usuario'];
$rut_usuario = $_SESSION['rut'];
$success = true;

foreach ($_POST as $key => $value) {
    if (strpos($key, 'calificacion') === 0 && !empty($value)) {
        $id_pregunta = str_replace('calificacion', '', $key);
        $calificacion = intval($value);
        $respuesta = $_POST['respuesta' . $id_pregunta] ?? '';

        if ($calificacion < 1 || $calificacion > 5) {
            echo json_encode(['success' => false, 'message' => 'Calificación inválida']);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO respuestas_encuesta (rut_usuario, id_pregunta, calificacion, respuesta) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siis", $rut_usuario, $id_pregunta, $calificacion, $respuesta);

        if (!$stmt->execute()) {
            $success = false;
            break;
        }

        $stmt->close();
    }
}

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar las respuestas']);
}

$conn->close();


?>