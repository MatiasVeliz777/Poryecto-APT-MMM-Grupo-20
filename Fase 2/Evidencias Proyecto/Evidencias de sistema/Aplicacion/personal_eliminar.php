<?php
include('conexion.php');

if (isset($_GET['rut'])) {
    $rut = $_GET['rut'];

    // Consulta para eliminar el empleado de la tabla usuarios y personal
    $sql_eliminar_usuario = "DELETE FROM usuarios WHERE rut = '$rut'";
    $sql_eliminar_personal = "DELETE FROM personal WHERE rut = '$rut'";

    if ($conn->query($sql_eliminar_usuario) === TRUE && $conn->query($sql_eliminar_personal) === TRUE) {
        echo "Empleado eliminado con éxito.";
        header("Location: personal_nuevo.php"); // Redirige a la lista de empleados
        exit();
    } else {
        echo "Error al eliminar el empleado: " . $conn->error;
    }

    $conn->close();
} else {
    echo "No se proporcionó un RUT válido para eliminar.";
}
?>
