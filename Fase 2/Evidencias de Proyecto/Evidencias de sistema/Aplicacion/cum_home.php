<?php
include('conexion.php');

// Obtener el día, mes y año seleccionados desde los parámetros GET o tomar el actual por defecto
$dia = isset($_GET['day']) ? $_GET['day'] : date('d');
$mes = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');


// Obtener el día, mes y año actuales
$dia_actual_cum = date('d');
$mes_actual_cum = date('m');
$year_actual_cum = date('Y');

// Verificar si estamos en el mes y año actual, para filtrar los días ya pasados
if ($mes == $mes_actual_cum && $year == $year_actual_cum) {
    // Mostrar solo los cumpleaños que aún no han pasado
    $sql_tarjetas_cumple = "SELECT nombre, fecha_nacimiento, imagen 
                            FROM personal 
                            WHERE MONTH(fecha_nacimiento) = ? 
                            AND DAY(fecha_nacimiento) >= ? 
                            ORDER BY DAY(fecha_nacimiento) ASC";
    
    $stmt_tarjetas_cumple = $conn->prepare($sql_tarjetas_cumple);
    $stmt_tarjetas_cumple->bind_param("ii", $mes, $dia_actual_cum); // Filtra los cumpleaños desde el día actual en adelante
} else {
    // Si es otro mes o año, mostrar todos los cumpleaños del mes seleccionado
    $sql_tarjetas_cumple = "SELECT nombre, fecha_nacimiento, imagen 
                            FROM personal 
                            WHERE MONTH(fecha_nacimiento) = ? 
                            ORDER BY DAY(fecha_nacimiento) ASC";
    
    $stmt_tarjetas_cumple = $conn->prepare($sql_tarjetas_cumple);
    $stmt_tarjetas_cumple->bind_param("i", $mes);
}

$stmt_tarjetas_cumple->execute();
$result_tarjetas_cumple = $stmt_tarjetas_cumple->get_result();

// Función para traducir el mes al español
function traducir_mes_cum($fecha_cum){
    $meses_cum = array(
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

    $dia_cum = date('d', strtotime($fecha_cum));  // Extraer el día
    $mes_nombre_cum = $meses_cum[date('F', strtotime($fecha_cum))];  // Traducir el mes
    
    return "$dia_cum de $mes_nombre_cum";  // Devolver en el formato "Día de Mes"
}

// Generar las tarjetas de cumpleaños para mostrarlas luego en el HTML
$cards_html_cum = '';
while ($row_cum = $result_tarjetas_cumple->fetch_assoc()) {
    // Definir el día del cumpleaños con ceros a la izquierda
    $dia_cum = str_pad(date('d', strtotime($row_cum['fecha_nacimiento'])), 2, "0", STR_PAD_LEFT);

    // Ruta de la carpeta donde están las imágenes de perfil
    $carpeta_fotos_cum = 'Images/fotos_personal/';
    $imagen_default_cum = 'Images/profile_photo/imagen_default.jpg';

    // Obtener el nombre del archivo de imagen desde la base de datos
    $nombre_imagen_cum = $row_cum['imagen'];

    // Construir la ruta completa de la imagen del usuario
    $ruta_imagen_usuario_cum = $carpeta_fotos_cum . $nombre_imagen_cum;
    if (file_exists($ruta_imagen_usuario_cum)) {
        $imagen_final_cum = $ruta_imagen_usuario_cum;
    } else {
        $imagen_final_cum = $imagen_default_cum;
    }

    $imagen_cum = $imagen_final_cum;
    $nombre_cum = htmlspecialchars($row_cum['nombre']);
    $fecha_cum = traducir_mes_cum($row_cum['fecha_nacimiento']);  // Traducir la fecha

    // Generar el HTML de la tarjeta de cumpleaños
    $cards_html_cum .= "
    <div id='birthday-$dia_cum' class='birthday-card'>
        <img src='$imagen_cum' alt='Foto de $nombre_cum'>
        <div class='birthday-info'>
            <h5>🎂 $nombre_cum</h5>
            <p>$fecha_cum</p>
        </div>
    </div>";
}

$stmt_tarjetas_cumple->close();
$conn->close();
?>
