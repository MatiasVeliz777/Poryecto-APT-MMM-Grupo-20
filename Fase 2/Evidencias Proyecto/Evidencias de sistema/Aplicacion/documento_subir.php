<?php
include("conexion.php");

// Verifica la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verifica si se ha enviado un archivo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo'])) {
    $nombre_documento = $_POST['nombre_documento']; // Nombre personalizado del archivo
    $nombre_archivo = $_FILES['archivo']['name'];
    $descripcion = $_POST['descripcion']; // Descripción del archivo
    $tipo_archivo = $_FILES['archivo']['type'];
    $ruta_temporal = $_FILES['archivo']['tmp_name'];

    // Solo permitimos archivos PDF y DOC/DOCX
    $extensiones_permitidas = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (in_array($tipo_archivo, $extensiones_permitidas)) {
        // Establecemos la carpeta de destino
        $carpeta_destino = 'uploads/';
        if (!is_dir($carpeta_destino)) {
            mkdir($carpeta_destino, 0777, true); // Crea la carpeta si no existe
        }

        $ruta_destino = $carpeta_destino . basename($nombre_archivo);

        // Movemos el archivo a la carpeta de destino
        if (move_uploaded_file($ruta_temporal, $ruta_destino)) {
            // Insertamos los detalles del archivo en la base de datos
            $sql = "INSERT INTO archivos (nombre_archivo, tipo_archivo, ruta_archivo, descripcion) 
                    VALUES ('$nombre_documento', '$tipo_archivo', '$ruta_destino', '$descripcion')";

            if ($conn->query($sql) === TRUE) {
                header('Location: documentos.php?status=success&message=Archivo subido correctamente');
                exit();
            } else {
                header('Location: documentos.php?status=error&message=Error al guardar el archivo en la base de datos');
                exit();
            }
        } else {
            header('Location: documentos.php?status=error&message=Error al mover el archivo');
            exit();
        }
    } else {
        header('Location: documentos.php?status=error&message=Formato de archivo no permitido');
        exit();
    }
}

$conn->close();
?>