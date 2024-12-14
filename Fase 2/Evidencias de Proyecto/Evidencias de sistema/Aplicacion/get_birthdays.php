<?php
include('conexion.php');


// Obtener el día, mes y año actuales
$dia_actual = date('d');
$mes_actual = date('m');
$year_actual = date('Y');

// Obtener el día, mes y año seleccionados desde los parámetros GET o tomar el actual por defecto
$dia = isset($_GET['day']) ? $_GET['day'] : date('d');
$mes = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Consulta para obtener los días con cumpleaños
$query = "SELECT DAY(fecha_nacimiento) as day FROM personal WHERE MONTH(fecha_nacimiento) = ? AND YEAR(fecha_nacimiento) <= ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $mes, $year);
$stmt->execute();
$result = $stmt->get_result();

$birthdays = [];

while ($row = $result->fetch_assoc()) {
    $birthdays[] = $row['day']; // Guardar los días con cumpleaños
}
$stmt->close();

// Consulta para obtener las tarjetas de empleados con cumpleaños del mes seleccionado
$sql_tarjetas_cumple = "SELECT nombre, fecha_nacimiento, imagen, rut FROM personal WHERE MONTH(fecha_nacimiento) = ? ORDER BY DAY(fecha_nacimiento) ASC";
$stmt_tarjetas_cumple = $conn->prepare($sql_tarjetas_cumple);
$stmt_tarjetas_cumple->bind_param("i", $mes);
$stmt_tarjetas_cumple->execute();
$result_tarjetas_cumple = $stmt_tarjetas_cumple->get_result();

// Función para traducir el mes al español
function traducir_mes($fecha){
    $meses = array(
        "January" => "Enero", 
        "February" => "Febrero", 
        "March" => "Marzo", 
        "April" => "Abril", 
        "May" => "Mayo", 
        "June" => "Junio", 
        "July" => "Julio", 
        "August" => "Agosto", 
        "September" => "Septiembre", 
        "October" => "Octubre", 
        "November" => "Noviembre", 
        "December" => "Diciembre"
    );

    $dia = date('d', strtotime($fecha));  // Extraer el día
    $mes_nombre = $meses[date('F', strtotime($fecha))];  // Traducir el mes
    
    return "$dia de $mes_nombre";  // Devolver en el formato "Día de Mes"
}

// Generar el HTML de las tarjetas de cumpleaños
$cards_html = '';
while ($row = $result_tarjetas_cumple->fetch_assoc()) {
    $dia_nacimiento = str_pad(date('d', strtotime($row['fecha_nacimiento'])), 2, "0", STR_PAD_LEFT);
    $mes_nacimiento = str_pad(date('m', strtotime($row['fecha_nacimiento'])), 2, "0", STR_PAD_LEFT);

    $carpeta_fotos = 'Images/fotos_personal/';
    $imagen_default = 'Images/profile_photo/imagen_default.jpg';

    $nombre_imagen = $row['imagen'];
    $ruta_imagen_usuario = $carpeta_fotos . $nombre_imagen;
    $imagen_final = file_exists($ruta_imagen_usuario) ? $ruta_imagen_usuario : $imagen_default;

    $imagen = $imagen_final;
    $nombre = htmlspecialchars($row['nombre']);
    $fecha = traducir_mes($row['fecha_nacimiento']);

    // Datos del usuario
    $rut_usuario = htmlspecialchars($row['rut']);
    $nombre = htmlspecialchars($row['nombre']);

    $cards_html .= "
    <div id='birthday-$dia_nacimiento' class='birthday-card'>
        <img src='$imagen' alt='Foto de $nombre'>
        <div class='birthday-info'>
            <h5>🎂$nombre</h5>
            <p>$fecha</p>";

    // Mostrar el botón solo si es su cumpleaños hoy
    if ($dia_nacimiento == $dia_actual && $mes_nacimiento == $mes_actual) {
        $cards_html .= "
            <button class='greet-btn' onclick=\"saludarCumple('$rut_usuario', '$nombre')\">Saludarlo en su día</button>";
    }

    $cards_html .= "</div></div>";
}

$stmt_tarjetas_cumple->close();
$conn->close();

// Devolver el array de cumpleaños y las tarjetas en formato JSON
echo json_encode([
    'birthdays' => $birthdays,
    'cards' => $cards_html
]);


?>