<?php
include('conexion.php'); // Conexión a la base de datos

// Procesar las respuestas de las preguntas

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pregunta = $_POST['pregunta'];
    $tipo = $_POST['tipo'];
    $opciones = isset($_POST['opciones']) ? $_POST['opciones'] : [];

    // Verificar que la pregunta y el tipo sean válidos
    if (!empty($pregunta) && !empty($tipo)) {
        // Comprobar si existe el tipo en la base de datos
        $stmt_tipo = $conn->prepare("SELECT id_tipo FROM encuesta_tipos_preg WHERE nombre_tipo = ?");
        $stmt_tipo->bind_param("s", $tipo);
        $stmt_tipo->execute();
        $stmt_tipo->bind_result($id_tipo);
        $stmt_tipo->fetch();
        $stmt_tipo->close();

        if ($id_tipo) {
            // Insertar la pregunta con el tipo válido
            $stmt = $conn->prepare("INSERT INTO encuesta_preg (pregunta, id_tipo) VALUES (?, ?)");
            $stmt->bind_param("si", $pregunta, $id_tipo);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $id_pregunta = $stmt->insert_id;  // Obtener el ID de la nueva pregunta insertada
                $stmt->close();

                // Insertar opciones, si existen
                if (!empty($opciones)) {
                    $stmt_opcion = $conn->prepare("INSERT INTO encuesta_opcion_preg (id_pregunta, opcion) VALUES (?, ?)");

                    foreach ($opciones as $opcion) {
                        if (!empty($opcion)) {
                            $stmt_opcion->bind_param("is", $id_pregunta, $opcion);
                            $stmt_opcion->execute();
                        }
                    }
                    $stmt_opcion->close();
                }

                echo "<p>Pregunta y opciones guardadas correctamente.</p>";
            } else {
                echo "<p>Error al insertar la pregunta.</p>";
            }
        } else {
            echo "<p>Error: Tipo de pregunta no válido.</p>";
        }
    } else {
        echo "<p>Por favor, rellena todos los campos.</p>";
    }
}

?>
