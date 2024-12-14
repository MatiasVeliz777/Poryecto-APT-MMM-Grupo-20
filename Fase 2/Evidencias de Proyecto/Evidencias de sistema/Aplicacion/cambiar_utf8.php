<?php
include("conexion.php");

// Función para normalizar nombres de archivos reemplazando caracteres especiales
function normalizar_nombre($nombre) {
    $buscar = array('á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ');
    $reemplazar = array('a', 'e', 'i', 'o', 'u', 'n', 'A', 'E', 'I', 'O', 'U', 'N');
    return str_replace($buscar, $reemplazar, $nombre);
}

// Ruta de la carpeta donde están las imágenes
$carpeta_fotos = 'Images/fotos_personal/';

// Consultar todas las filas de la tabla `personal`
$sql = "SELECT rut, imagen FROM personal";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rut = $row['rut'];
        $imagen_original = $row['imagen'];

        // Normalizar el nombre de la imagen
        $imagen_normalizada = normalizar_nombre($imagen_original);

        // Verificar si el nombre original es diferente al normalizado
        if ($imagen_original !== $imagen_normalizada) {
            // Cambiar el nombre del archivo en la carpeta
            $ruta_original = $carpeta_fotos . $imagen_original;
            $ruta_normalizada = $carpeta_fotos . $imagen_normalizada;

            if (file_exists($ruta_original)) {
                rename($ruta_original, $ruta_normalizada);
            }

            // Actualizar el nombre de la imagen en la base de datos
            $sql_update = "UPDATE personal SET imagen = '$imagen_normalizada' WHERE rut = '$rut'";
            if ($conn->query($sql_update) === TRUE) {
                echo "Imagen actualizada para RUT: $rut<br>";
            } else {
                echo "Error al actualizar la imagen para RUT: $rut: " . $conn->error . "<br>";
            }
        }
    }
} else {
    echo "No se encontraron registros en la tabla `personal`.<br>";
}

$conn->close();
?>
