<?php
include('conexion.php');

if (isset($_GET['rut'])) {
    $rut = $_GET['rut'];

    // Obtener la ruta de la imagen asociada al usuario
    $sql_obtener_imagen = "SELECT imagen FROM personal WHERE rut = '$rut'";
    $resultado = $conn->query($sql_obtener_imagen);

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $nombre_imagen = $fila['imagen']; // Nombre de la imagen almacenado en la base de datos
        $ruta_imagen = "Images/fotos_personal/" . $nombre_imagen; // Ruta completa de la imagen

        // Verificar si la imagen existe en el servidor y eliminarla
        if (!empty($nombre_imagen) && file_exists($ruta_imagen)) {
            unlink($ruta_imagen); // Eliminar la imagen del servidor
        }
    }

    // Consultas para eliminar el empleado de las tablas usuarios y personal
    $sql_eliminar_usuario = "DELETE FROM usuarios WHERE rut = '$rut'";
    $sql_eliminar_personal = "DELETE FROM personal WHERE rut = '$rut'";

    if ($conn->query($sql_eliminar_usuario) === TRUE && $conn->query($sql_eliminar_personal) === TRUE) {
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
