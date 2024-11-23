<?php
include ("conexion.php"); // Asegúrate de incluir la conexión a la base de datos

// Obtener los datos enviados en JSON
$data = json_decode(file_get_contents("php://input"), true);
$respuestaId = $data['id'] ?? null;
$archivo = $data['archivo'] ?? null;

$response = ['success' => false, 'message' => ''];

if ($respuestaId && is_numeric($respuestaId)) {
    // Verificar si existe un archivo vinculado y eliminarlo
    if ($archivo) {
        $rutaArchivo = __DIR__ . '/' . $archivo;  // Define la ruta completa del archivo

        if (file_exists($rutaArchivo)) {
            if (unlink($rutaArchivo)) {
                $response['message'] .= " El archivo asociado fue eliminado correctamente.";
            } else {
                $response['message'] .= " No se pudo eliminar el archivo asociado.";
            }
        } else {
            $response['message'] .= " El archivo no existe en el directorio.";
        }
    }

    // Eliminar la respuesta de la base de datos
    $sql = "DELETE FROM soli_respuestas WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $respuestaId);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] .= " La respuesta  fue eliminada correctamente.";
        } else {
            $response['message'] .= " Error al ejecutar la eliminación en la base de datos: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $response['message'] .= " Error al preparar la consulta SQL: " . $conn->error;
    }
} else {
    $response['message'] = "ID de respuesta no válido o no recibido correctamente.";
}

// Enviar la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
