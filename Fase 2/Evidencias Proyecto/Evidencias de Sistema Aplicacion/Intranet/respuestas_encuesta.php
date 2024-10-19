<?php
include('conexion.php');
session_start();

// Obtener el usuario autenticado
$rut_usuario = $_SESSION['rut'];  // Asegúrate de tener este valor en la sesión

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificar si se han enviado respuestas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['respuesta'])) {
    $respuestas = $_POST['respuesta'];
    $calificaciones = $_POST['calificacion'] ?? [];

    $insertadas = false; // Bandera para verificar si se insertaron respuestas

    foreach ($respuestas as $id_pregunta => $respuesta) {
        $tipo_pregunta = "";

        // Consulta para obtener el tipo de pregunta
        $stmt = $conn->prepare("SELECT tipo_pregunta FROM preguntas_encuesta WHERE id_pregunta = ?");
        $stmt->bind_param("i", $id_pregunta);
        $stmt->execute();
        $stmt->bind_result($tipo_pregunta);
        $stmt->fetch();
        $stmt->close();

        // Verificar que la respuesta no esté vacía
        if (!empty($respuesta)) {

            // Procesar según el tipo de pregunta
            if ($tipo_pregunta === 'texto') {
                $respuesta_texto = $respuesta;  // Solo se espera un valor en el caso de texto
                $calificacion = isset($calificaciones[$id_pregunta]) ? $calificaciones[$id_pregunta] : 1;

                // Solo insertar si la respuesta no está vacía
                if (!empty($respuesta_texto)) {
                    // Insertar la respuesta y la calificación (si existe)
                    $stmt = $conn->prepare("INSERT INTO respuestas_encuesta (rut_usuario, id_pregunta, calificacion, respuesta) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param('siis', $rut_usuario, $id_pregunta, $calificacion, $respuesta_texto);
                    if ($stmt->execute()) {
                        $insertadas = true; // Marcar que se insertó una respuesta
                    }
                    $stmt->close();
                }

            } elseif ($tipo_pregunta === 'seleccion_unica') {
                // Guardar la respuesta de selección única solo si hay respuesta
                if (is_array($respuesta)) {
                    foreach ($respuesta as $opcion) {
                        if (!empty($opcion)) {
                            // Insertar la respuesta de opción seleccionada
                            $stmt = $conn->prepare("INSERT INTO respuestas_encuesta (rut_usuario, id_pregunta, respuesta) VALUES (?, ?, ?)");
                            $stmt->bind_param('sis', $rut_usuario, $id_pregunta, $opcion);
                            if ($stmt->execute()) {
                                $insertadas = true; // Marcar que se insertó una respuesta
                            }
                            $stmt->close();
                        }
                    }
                }
            }
        }
    }

    // Verificar si se insertaron respuestas
    if ($insertadas) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se insertaron respuestas.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No se recibieron respuestas.']);
}

$conn->close();
?>
