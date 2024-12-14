<?php
session_start();
include("conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['rut_usuario'], $_POST['mensaje']) || empty($_POST['rut_usuario']) || empty($_POST['mensaje'])) {
        echo "<script>alert('Error: Datos insuficientes.');</script>";
        echo "<script>window.location.href = 'home.php';</script>"; // Redirigir a home.php
        exit;
    }

    $rut_usuario = $_POST['rut_usuario'];
    $mensaje = $_POST['mensaje'];
    $redirigir = $_POST['redirigir'];

    // Inserta la notificación en la tabla
    $query = "INSERT INTO notificaciones (rut, mensaje, fecha_creacion) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("ss", $rut_usuario, $mensaje);
        if ($stmt->execute()) {
            echo "<script>window.location.href = 'home.php';</script>"; // Redirigir a home.php
        } else {
            echo "<script>alert('Error al insertar notificación: " . $stmt->error . "');</script>";
            echo "<script>window.location.href = 'home.php';</script>"; // Redirigir a home.php
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error al preparar la consulta: " . $conn->error . "');</script>";
        echo "<script>window.location.href = 'home.php';</script>"; // Redirigir a home.php
    }

    $conn->close();
} else {
    http_response_code(405); // Método no permitido
    echo "<script>alert('Método no permitido.');</script>";
    echo "<script>window.location.href = 'home.php';</script>"; // Redirigir a home.php
}
?>
