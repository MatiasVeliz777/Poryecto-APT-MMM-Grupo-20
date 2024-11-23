<?php
// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'intranet');

// Verifica la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta para obtener los archivos
$sql = "SELECT id, nombre_archivo, ruta_archivo FROM archivos ORDER BY fecha_subida DESC";
$resultado = $conexion->query($sql);

// Mostramos los archivos en una lista
if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        echo '<li class="list-group-item">';
        echo '<a href="' . $fila['ruta_archivo'] . '" download>' . $fila['nombre_archivo'] . '</a>';
        echo '</li>';
    }
} else {
    echo '<li class="list-group-item">No hay archivos disponibles para descargar.</li>';
}

$conexion->close();
?>
