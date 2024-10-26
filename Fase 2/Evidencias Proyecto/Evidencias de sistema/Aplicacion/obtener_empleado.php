<?php
include('conexion.php');

if (isset($_GET['rut'])) {
    $rut = $conn->real_escape_string($_GET['rut']); // Escapa el valor del RUT

    // Definir la carpeta donde se encuentran las imágenes de los empleados
    $carpeta_fotos = 'Images/fotos_personal/';
    $imagen_default = 'Images/profile_photo/imagen_default.jpg'; // Imagen por defecto si no existe la imagen del empleado

    // Consulta con JOIN para obtener el nombre del cargo desde la tabla de cargos
    $sql = "SELECT personal.nombre, cargos.NOMBRE_CARGO, personal.imagen 
            FROM personal 
            INNER JOIN cargos ON personal.cargo_id = cargos.id 
            WHERE personal.rut = '$rut'";
    
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Ruta completa de la imagen del empleado
        $img_emp_mes = $carpeta_fotos . $row['imagen'];

        // Verificar si la imagen del empleado existe
        $img_emp_mes = file_exists($img_emp_mes) ? $img_emp_mes : $imagen_default;

        // Devolver los datos del empleado y el cargo como JSON
        echo json_encode([
            'nombre' => $row['nombre'],
            'NOMBRE_CARGO' => $row['NOMBRE_CARGO'],
            'imagen' => $img_emp_mes // Devolver la ruta de la imagen correcta (empleado o predeterminada)
        ]);
    } else {
        echo json_encode(['error' => 'Empleado no encontrado']);
    }
} else {
    echo json_encode(['error' => 'Rut no proporcionado']);
}
?>
