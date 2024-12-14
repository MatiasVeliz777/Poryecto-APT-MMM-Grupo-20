<?php

include("conexion.php");

// Función para normalizar nombres de archivos reemplazando caracteres especiales
function normalizar_nombre($nombre) {
    $buscar = array('á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ');
    $reemplazar = array('a', 'e', 'i', 'o', 'u', 'n', 'A', 'E', 'I', 'O', 'U', 'N');
    return str_replace($buscar, $reemplazar, $nombre);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut = $_POST['rut'];
    $carpeta_fotos = "Images/fotos_personal/";
    $archivo = $_FILES['nueva_foto'];

    // Verificar si se subió un archivo
    if ($archivo['error'] === UPLOAD_ERR_OK) {
        $nombre_tmp = $archivo['tmp_name'];

        // Obtener el nombre del usuario asociado al RUT desde la base de datos
        $sql_nombre = "SELECT nombre FROM personal WHERE rut = ?";
        $stmt_nombre = $conn->prepare($sql_nombre);
        $stmt_nombre->bind_param("s", $rut);
        $stmt_nombre->execute();
        $result_nombre = $stmt_nombre->get_result();

        if ($result_nombre->num_rows > 0) {
            $fila = $result_nombre->fetch_assoc();
            $nombre_usuario = $fila['nombre'];

            // Normalizar el nombre del archivo para reemplazar caracteres especiales
            $nombre_usuario_normalizado = normalizar_nombre($nombre_usuario);

            // Crear un nombre de archivo único con el nombre del usuario y el RUT
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION); // Obtener la extensión del archivo
            $nombre_archivo = $nombre_usuario_normalizado . "." . $extension; // Solo el nombre del archivo
            $ruta_archivo = $carpeta_fotos . $nombre_archivo; // Ruta completa para guardar físicamente

            // Verificar si ya existe una imagen con el mismo nombre
            if (file_exists($ruta_archivo)) {
                // Eliminar la imagen existente
                if (!unlink($ruta_archivo)) {
                    echo "Error al eliminar la imagen existente.";
                    exit();
                }
            }

            // Mover el archivo a la carpeta de destino
            if (move_uploaded_file($nombre_tmp, $ruta_archivo)) {
                // Actualizar la base de datos con el nombre del archivo (sin la ruta)
                $sql_update = "UPDATE personal SET imagen = ? WHERE rut = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ss", $nombre_archivo, $rut);

                if ($stmt_update->execute()) {
                    // Redirigir a la página actual para verificar los cambios
                    echo "Foto actualizada correctamente.";
                    exit(); // Finalizar el script para evitar ejecutar más código
                } else {
                    echo "Error al actualizar la base de datos.";
                }
            } else {
                echo "Error al mover el archivo.";
            }
        } else {
            echo "No se encontró el usuario con el RUT proporcionado.";
        }
    } else {
        echo "Error al subir el archivo.";
    }
}
?>
