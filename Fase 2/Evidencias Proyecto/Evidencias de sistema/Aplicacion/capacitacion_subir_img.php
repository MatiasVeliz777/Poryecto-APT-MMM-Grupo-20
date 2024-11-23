<?php
// Conectar a la base de datos
include('conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar si se pasó el evento_id
    if (isset($_POST['evento_id'])) {
        $evento_id = intval($_POST['evento_id']);
    } else {
        die('ID del evento no especificado.');
    }

    // Verificar si se subieron archivos
    if (isset($_FILES['imagenes_evento']) && count($_FILES['imagenes_evento']['name']) > 0) {
        $imagenes = $_FILES['imagenes_evento'];

        // Directorio donde se guardarán las imágenes
        $directorio_imagenes = 'Images/imagenes_eventos/';

        // Crear el directorio si no existe
        if (!is_dir($directorio_imagenes)) {
            mkdir($directorio_imagenes, 0777, true);
        }

        // Procesar cada imagen
        for ($i = 0; $i < count($imagenes['name']); $i++) {
            $nombre_imagen = $imagenes['name'][$i];
            $tmp_imagen = $imagenes['tmp_name'][$i];
            $error_imagen = $imagenes['error'][$i];

            if ($error_imagen === UPLOAD_ERR_OK) {
                // Generar un nombre único para evitar colisiones
                $nombre_unico = uniqid('img_', true) . '.' . pathinfo($nombre_imagen, PATHINFO_EXTENSION);

                // Mover la imagen al directorio
                $ruta_imagen = $directorio_imagenes . $nombre_unico;
                if (move_uploaded_file($tmp_imagen, $ruta_imagen)) {
                    // Guardar la ruta de la imagen en la base de datos
                    $stmt = $conn->prepare("INSERT INTO imagenes_capacitaciones (capacitacion_id, ruta_imagen) VALUES (?, ?)");
                    $stmt->bind_param("is", $evento_id, $ruta_imagen);
                    if (!$stmt->execute()) {
                        echo "Error al insertar en la base de datos: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    echo "Error al mover el archivo $nombre_imagen.";
                }
            } else {
                echo "Error en el archivo $nombre_imagen: código de error $error_imagen.";
            }
        }

        // Redirigir de nuevo al evento
        header("Location: capacitacion_asistencia.php?capacitacion_id=$evento_id");
        exit();

    } else {
        echo "No se seleccionaron imágenes.";
    }
}
?>
