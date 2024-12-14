<?php
// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'intranet');

// Verifica la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Verifica si se ha enviado un archivo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo'])) {
    $nombre_archivo = $_FILES['archivo']['name'];
    $tipo_archivo = $_FILES['archivo']['type'];
    $ruta_temporal = $_FILES['archivo']['tmp_name'];

    // Solo permitimos archivos PDF y DOC/DOCX
    $extensiones_permitidas = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (in_array($tipo_archivo, $extensiones_permitidas)) {
        // Establecemos la carpeta de destino
        $carpeta_destino = 'archivos/documentos/';
        if (!is_dir($carpeta_destino)) {
            mkdir($carpeta_destino, 0777, true); // Crea la carpeta si no existe
        }

        $ruta_destino = $carpeta_destino . basename($nombre_archivo);

        // Movemos el archivo a la carpeta de destino
        if (move_uploaded_file($ruta_temporal, $ruta_destino)) {
            // Insertamos los detalles del archivo en la base de datos
            $sql = "INSERT INTO archivos (nombre_archivo, tipo_archivo, ruta_archivo) VALUES ('$nombre_archivo', '$tipo_archivo', '$ruta_destino')";

            if ($conexion->query($sql) === TRUE) {
                echo "Archivo subido y guardado correctamente.";
                header('location: subir_archivos.php');
            } else {
                echo "Error al guardar el archivo en la base de datos: " . $conexion->error;
            }
        } else {
            echo "Error al mover el archivo.";
        }
    } else {
        echo "Formato de archivo no permitido. Solo se aceptan archivos PDF o DOC.";
    }
}

$conexion->close();
?>
