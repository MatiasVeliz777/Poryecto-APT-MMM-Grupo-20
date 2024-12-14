<?php
// Incluye la conexión a la base de datos
include('conexion.php');

// Inicia la sesión si no ha sido iniciada
session_start();

// Verifica si el usuario está logueado
if (!isset($_SESSION['rut'])) {
    // Si el usuario no está logueado, redirige a la página de inicio de sesión o una página de error
    header('Location: login.php');
    exit;
}

// Verifica si se han enviado los datos del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $evento_id = intval($_POST['evento_id']);
    $usuario_rut = $_POST['usuario_rut']; // El RUT es enviado en el formulario como usuario_id

    // Elimina el registro de asistencia del usuario en la capacitación
    $query = "DELETE FROM asistencia_capacitaciones WHERE capacitacion_id = ? AND rut_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $evento_id, $usuario_rut);

    if ($stmt->execute()) {
        // Si la eliminación fue exitosa, redirige de nuevo a la página del evento con un mensaje de éxito
        header("Location: capacitacion_asistencia.php?capacitacion_id=$evento_id");
        exit;
    } else {
        // Si hubo un error en la eliminación, muestra un mensaje de error
        echo "Error al intentar desasistir de la capacitación.";
    }

    $stmt->close();
} else {
    // Si no se accedió a esta página mediante un formulario POST, redirige al usuario
    header('Location: capacitaciones.php');
    exit;
}

// Cierra la conexión
$conn->close();
?>
