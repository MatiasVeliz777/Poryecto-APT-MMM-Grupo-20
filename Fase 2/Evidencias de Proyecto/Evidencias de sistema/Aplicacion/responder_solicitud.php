<?php
include("conexion.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $solicitud_id = $_POST['solicitud_id'];  // ID de la solicitud que se está respondiendo
    $respuesta_texto = isset($_POST['respuesta_texto']) ? $_POST['respuesta_texto'] : '';
    $archivo_subido = '';

    // Obtener el RUT del usuario que envió la solicitud
    $query_rut_solicitante = "SELECT rut FROM solicitudes WHERE id = ?";
    $stmt_rut = $conn->prepare($query_rut_solicitante);
    $stmt_rut->bind_param('i', $solicitud_id);
    $stmt_rut->execute();
    $stmt_rut->bind_result($rut_solicitante);
    $stmt_rut->fetch();
    $stmt_rut->close();

    // Verificar si se obtuvo el RUT del solicitante
    if (!$rut_solicitante) {
        echo "Error: No se pudo encontrar el RUT del solicitante.";
        exit();
    }

    // Crear el directorio basado en el RUT del solicitante
    $directorio_usuario = 'archivos/solicitudes/' . $rut_solicitante . '/';

    // Verificar si el directorio del usuario existe, si no, crearlo
    if (!is_dir($directorio_usuario)) {
        mkdir($directorio_usuario, 0777, true); // Crear el directorio con permisos de lectura y escritura
    }

    // Verificar si hay un archivo subido
    if (isset($_FILES['archivo_respuesta']) && $_FILES['archivo_respuesta']['error'] == 0) {
        $archivo = $_FILES['archivo_respuesta'];
        $archivo_subido = $directorio_usuario . basename($archivo['name']);

        // Mover el archivo al directorio del usuario
        if (move_uploaded_file($archivo['tmp_name'], $archivo_subido)) {
            echo "El archivo ha sido subido correctamente.";
        } else {
            echo "Hubo un error al subir el archivo.";
        }
    }

    // Guardar la respuesta en la base de datos
    $rut_usuario = $_SESSION['rut']; // El RUT del usuario que responde la solicitud
    $fecha_respuesta = date('Y-m-d H:i:s'); // Fecha y hora actual

    // Insertar la respuesta en la tabla de respuestas, vinculando el RUT del usuario y la solicitud
    $sql = "INSERT INTO soli_respuestas (solicitud_id, rut_usuario, respuesta_texto, archivo, fecha_respuesta) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('issss', $solicitud_id, $rut_usuario, $respuesta_texto, $archivo_subido, $fecha_respuesta);

    if ($stmt->execute()) {
        echo "Respuesta guardada con éxito.";
    } else {
        echo "Error al guardar la respuesta: " . $stmt->error;
    }

    // Redirigir a la página de solicitudes
    header("Location: solicitudes_usuarios.php");
    exit();
}

$conn->close();
?>
