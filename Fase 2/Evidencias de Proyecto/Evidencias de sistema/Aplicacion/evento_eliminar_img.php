<?php
// Conectar a la base de datos
include('conexion.php'); // Asegúrate de tener una conexión válida

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $imagen_id = intval($_POST['imagen_id']);
    $ruta_imagen = $_POST['ruta_imagen'];
    $evento_id = intval($_POST['evento_id']); // Obtener el evento_id desde el formulario

    // Eliminar la imagen de la carpeta
    if (file_exists($ruta_imagen)) {
        unlink($ruta_imagen); // Elimina la imagen del servidor
    }

    // Eliminar la referencia de la base de datos
    $query = "DELETE FROM imagenes_eventos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $imagen_id);

    if ($stmt->execute()) {
        // Redirigir de vuelta a la página del evento
        header('Location: evento_asistencia.php?evento_id=' . $evento_id);
        exit();
    } else {
        echo "Error al eliminar la imagen.";
    }
}
?>
