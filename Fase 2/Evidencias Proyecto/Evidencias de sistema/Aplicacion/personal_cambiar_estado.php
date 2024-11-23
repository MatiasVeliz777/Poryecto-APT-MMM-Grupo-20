<?php
include("conexion.php");
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); // Redirigir al login si no ha iniciado sesión
    exit();
}

$error = "";

// Verificar si se recibieron los datos necesarios
if (isset($_POST['rut']) && isset($_POST['accion'])) {
    $rut = $_POST['rut'];
    $accion = $_POST['accion']; // Valor esperado: 'habilitar' o 'inhabilitar'

    // Determinar el nuevo estado en función de la acción
    $nuevo_estado = ($accion === 'habilitar') ? 1 : 0;

    // Preparar la consulta de actualización
    $sql_update = "UPDATE usuarios SET activo = ? WHERE rut = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param('is', $nuevo_estado, $rut);

    // Ejecutar la consulta y verificar el resultado
    if ($stmt_update->execute()) {
        echo ($accion === 'habilitar') ? "Empleado habilitado correctamente." : "Empleado inhabilitado correctamente.";
    } else {
        echo "Error al cambiar el estado del empleado.";
    }

    $stmt_update->close();
} else {
    echo "Datos incompletos. No se pudo realizar la acción.";
}

$conn->close();
?>
