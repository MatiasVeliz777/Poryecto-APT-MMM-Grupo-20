<?php
include('conexion.php'); // Conexión a la base de datos

session_start();
$rut_usuario = $_SESSION['rut']; // RUT del usuario autenticado

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); // Redirigir al login si no ha iniciado sesión
    exit();
}

$error = "";

// Obtener el usuario que ha iniciado sesión
$usuario = $_SESSION['usuario'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pregunta = $_POST['pregunta'];

    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare("INSERT INTO preguntas_encuesta (pregunta) VALUES (?)");
    $stmt->bind_param('s', $pregunta);

    if ($stmt->execute()) {
        // Respuesta exitosa
        echo json_encode(['success' => true]);
    } else {
        // Error en la consulta
        echo json_encode(['success' => false]);
    }

    $stmt->close();
}

$conn->close();
?>