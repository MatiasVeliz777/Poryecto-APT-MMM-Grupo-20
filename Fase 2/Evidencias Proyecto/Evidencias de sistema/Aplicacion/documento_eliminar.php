<?php
include("conexion.php");

// Verifica la conexión
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos.']));
}

// Verificar si se ha recibido el ID del archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archivo_id'])) {
    $archivo_id = $_POST['archivo_id'];

    // Obtener la ruta del archivo para eliminarlo del servidor
    $sql = "SELECT ruta_archivo FROM archivos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $archivo_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $ruta_archivo = $row['ruta_archivo'];

        // Eliminar el archivo físico del servidor
        if (file_exists($ruta_archivo)) {
            unlink($ruta_archivo); // Elimina el archivo físico
        }

        // Eliminar el registro de la base de datos
        $sql = "DELETE FROM archivos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $archivo_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Archivo eliminado correctamente.']);
            exit();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el archivo de la base de datos.']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Archivo no encontrado.']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Solicitud no válida.']);
    exit();
}

$conn->close();
?>
