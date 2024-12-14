<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); // Redirigir al login si no ha iniciado sesión
    exit();
}

// Detectar el código de error desde las cabeceras HTTP
$error_code = http_response_code();

if ($error_code == 403) {
    include '403.php'; // Mostrar página personalizada para error 403
    exit;
} elseif ($error_code == 404) {
    include '404.php'; // Mostrar página personalizada para error 404
    exit;
}


$error = "";
include("conexion.php");

// Obtener el usuario que ha iniciado sesión
$usuario = $_SESSION['usuario'];

// Consultar los datos del empleado en la tabla 'personal'
$sql = "SELECT rut, nombre, correo, imagen, fecha_nacimiento, cargo_id, rol_id, admin
        FROM personal 
        WHERE rut = (SELECT rut FROM usuarios WHERE nombre_usuario = '$usuario')";
$result = $conn->query($sql);

// Verificar si se encontró el usuario
if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc(); // Extraer los datos del usuario
    // Guardar todos los datos del usuario en la sesión
    $_SESSION['rut'] = $user_data['rut'];
    $_SESSION['nombre'] = $user_data['nombre'];
    $_SESSION['correo'] = $user_data['correo'];
    $_SESSION['imagen'] = $user_data['imagen']; // Asegúrate de guardar la imagen aquí
    $_SESSION['cargo_id'] = $user_data['cargo_id'];
    $rol = $user_data['rol_id'];
    $admin = $user_data['admin'];
    // Guardar el rol en la sesión
    $_SESSION['rol'] = $rol;
    $_SESSION['admin'] = $admin;
} else {
    $error = "No se encontraron datos para el usuario.";
}

// Consultar el cargo del usuario
$sql_cargo = "SELECT NOMBRE_CARGO FROM cargos WHERE id = '" . $user_data['cargo_id'] . "'";
$result_cargo = $conn->query($sql_cargo);

if ($result_cargo->num_rows > 0) {
    $cargo_data = $result_cargo->fetch_assoc(); // Extraer los datos del usuario
} else {
    $error = "No se encontraron datos para el cargo.";
}

// Función para traducir los nombres de los días y meses al español
function traducir_fecha($fecha){
    $dias = array("Sunday" => "Domingo", "Monday" => "Lunes", "Tuesday" => "Martes", 
                  "Wednesday" => "Miércoles", "Thursday" => "Jueves", 
                  "Friday" => "Viernes", "Saturday" => "Sábado");
    
    $meses = array("January" => "Enero", "February" => "Febrero", "March" => "Marzo", 
                   "April" => "Abril", "May" => "Mayo", "June" => "Junio", 
                   "July" => "Julio", "August" => "Agosto", "September" => "Septiembre", 
                   "October" => "Octubre", "November" => "Noviembre", "December" => "Diciembre");
    
    $dia_nombre = $dias[date('l', strtotime($fecha))];
    $dia_numero = date('d', strtotime($fecha));
    $mes_nombre = $meses[date('F', strtotime($fecha))];
    $anio = date('Y', strtotime($fecha));
    
    return "$dia_nombre, $dia_numero de $mes_nombre de $anio";
}

function traducir_mes($fecha){
    $meses = array("January" => "Enero", "February" => "Febrero", "March" => "Marzo", 
                   "April" => "Abril", "May" => "Mayo", "June" => "Junio", 
                   "July" => "Julio", "August" => "Agosto", "September" => "Septiembre", 
                   "October" => "Octubre", "November" => "Noviembre", "December" => "Diciembre");
    
    $mes_nombre = $meses[date('F', strtotime($fecha))];
    $anio = date('Y', strtotime($fecha));
    
    return "$mes_nombre de $anio";
}

// Ruta de la carpeta donde están las imágenes de perfil
$carpeta_fotos = 'Images/fotos_personal/'; // Cambia esta ruta a la carpeta donde están tus fotos
$imagen_default = 'Images/profile_photo/imagen_default.jpg'; // Ruta de la imagen predeterminada

// Obtener el nombre del archivo de imagen desde la base de datos
$nombre_imagen = $user_data['imagen']; // Se asume que este campo contiene solo el nombre del archivo

// Construir la ruta completa de la imagen del usuario
$ruta_imagen_usuario = $carpeta_fotos . $nombre_imagen;

// Verificar si la imagen del usuario existe en la carpeta
if (file_exists($ruta_imagen_usuario)) {
    // Si la imagen existe, se usa esa ruta
    $imagen_final = $ruta_imagen_usuario;
} else {
    // Si no existe, se usa la imagen predeterminada
    $imagen_final = $imagen_default;
}

// Mostrar usuarios nuevos del mes actual
$mes_actual = date('m');
$año_actual = date('Y');

// Consulta para obtener usuarios nuevos del mes actual
$sql_nuevos = "SELECT u.rut, u.nombre_usuario, u.fecha_creacion, p.nombre, p.fecha_nacimiento, c.NOMBRE_CARGO, p.imagen 
               FROM usuarios u
               INNER JOIN personal p ON u.rut = p.rut
               INNER JOIN cargos c ON p.cargo_id = c.id
               WHERE MONTH(u.fecha_creacion) = ? AND YEAR(u.fecha_creacion) = ?";

$stmt_nuevos = $conn->prepare($sql_nuevos);
$stmt_nuevos->bind_param('ii', $mes_actual, $año_actual);
$stmt_nuevos->execute();
$result_nuevos = $stmt_nuevos->get_result();



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
    $sql_tarjetas_cumple = "SELECT nombre, fecha_nacimiento, imagen, rut
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

$cards_html_cum = '';
$dia_actual = date('d');  // Día actual con dos dígitos
$mes_actual = date('m');  // Mes actual con dos dígitos

while ($row_cum = $result_tarjetas_cumple->fetch_assoc()) {
    // Definir el día y mes del cumpleaños con ceros a la izquierda
    $dia_cum = str_pad(date('d', strtotime($row_cum['fecha_nacimiento'])), 2, "0", STR_PAD_LEFT);
    $mes_cum = str_pad(date('m', strtotime($row_cum['fecha_nacimiento'])), 2, "0", STR_PAD_LEFT);

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

   
    $nombre_usuario_Session = $_SESSION['nombre'];  // Nombre completo de la persona logeada (quien va a dar la bienvenida)

    // Asegurarnos de que $nuevo_user contiene los datos del nuevo usuario
    $rut_usuario_cum = $row_cum['rut'];

    // Verificamos si ya existe una notificación de bienvenida para este usuario
    $query_check = "SELECT * FROM notificaciones WHERE rut = ? AND mensaje LIKE ?";
    $stmt_check = $conn->prepare($query_check);
    $mensaje_bienvenida = "🎂El usuario $nombre_usuario_Session te ha deseado un feliz cumpleaños.🎂"; // El mensaje debe ser exactamente como se almacenará

    // Buscamos en la base de datos si ya existe este mensaje para el nuevo usuario
    $stmt_check->bind_param("ss", $rut_usuario_cum, $mensaje_bienvenida);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    $mostrar_boton = ($result_check->num_rows == 0);  // Si no hay notificación, mostramos el botón

    $stmt_check->close();

    // Generar el HTML de la tarjeta de cumpleaños
    $cards_html_cum .= "
    <div id='birthday-$dia_cum' class='birthday-card'>
        <img src='$imagen_cum' alt='Foto de $nombre_cum'>
        <div class='birthday-info'>
            <h5>🎂 $nombre_cum</h5>
            <p>$fecha_cum</p>";

    // Mostrar el botón solo si el empleado está cumpliendo años hoy
    if ($dia_cum == $dia_actual && $mes_cum == $mes_actual) {

        if ($mostrar_boton){
            $cards_html_cum .= "
            <button class='greet-btn' onclick=\"saludarCumple('$rut_usuario_cum', '$nombre_cum')\">Saludarlo en su día</button>";
        }
    } 

    $cards_html_cum .= "
        </div>
    </div>";
}

$stmt_tarjetas_cumple->close();


// Obtener el mes y el año actual o los seleccionados
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Obtener la fecha actual en formato 'Y-m-d'
$fecha_actual = date("Y-m-d");

// Consulta para obtener solo los eventos del mes actual a partir de la fecha actual
$query = $conn->prepare("SELECT id, DAY(fecha) AS day, fecha, titulo, hora, ubicacion 
                         FROM eventos 
                         WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? AND fecha >= ? 
                         ORDER BY fecha ASC");
$query->bind_param("iis", $month, $year, $fecha_actual);
$query->execute();
$result = $query->get_result();

// Inicializar tarjetas de eventos como una cadena vacía
$eventCards = '';
// Verificar si hay resultados en la consulta
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $eventCards .= "
        <div id='event-{$row['id']}' class='event-card clickeable' onclick=\"location.href='evento_asistencia.php?evento_id={$row['id']}'\" style='cursor: pointer; position: relative;'>
            <div class='event-header'>
                <p class='event-date'>" . traducir_fecha($row['fecha']) . "</p>
                <h5 class='event-title'>{$row['titulo']}</h5>
            </div>
            <p class='event-time' style='margin-top: 0px;'>Hora: " . date('H:i', strtotime($row['hora'])) . "</p>
            <p class='event-location'>Ubicación: {$row['ubicacion']}</p>";

        // Botón "Asistir" si el usuario no está registrado y el evento es futuro
        $evento_id = $row['id'];
        $rut_usuario = $_SESSION['rut'];

        // Comprobar si el usuario ya está registrado para el evento
        $check_asistencia_sql = "SELECT * FROM asistencias_eventos WHERE evento_id = ? AND rut_usuario = ?";
        $stmt_check_asistencia = $conn->prepare($check_asistencia_sql);
        $stmt_check_asistencia->bind_param("is", $evento_id, $rut_usuario);
        $stmt_check_asistencia->execute();
        $result_check_asistencia = $stmt_check_asistencia->get_result();

        if ($result_check_asistencia->num_rows == 0) {
            $eventCards .= "<form method='POST' style='display: inline;' onclick='event.stopPropagation();'>
                                <input type='hidden' name='evento_id' value='{$row['id']}'>
                                <button type='submit' style='width: 80%; padding: 5px; margin-top: 5px; text-align: center;' name='registrar_asistencia' class='btn btn-outline-primary btn-sm'>Asistir al Evento</button>
                            </form>";
        } else {
            $eventCards .= "<p style='color: #23be69; font-weight: none; margin-top: 5px;'>Ya estás registrado en este evento</p>";
        }

        $stmt_check_asistencia->close();

        $eventCards .= "</div><hr>";
    }
} else {
    // Mostrar mensaje si no hay eventos
    $eventCards = "
    <div class='card'>
        <div class='card-body'>
            <h3 class='text-center'>No hay eventos disponibles</h3>
            <p class='text-center'>No se han programado eventos para este momento.</p>
        </div>
    </div>";
}

if (isset($_POST['registrar_asistencia'])) {
    $evento_id = $_POST['evento_id'];
    $rut_usuario = $_SESSION['rut'];

    // Verificar si el usuario ya se registró en este evento
    $check_sql = "SELECT * FROM asistencias_eventos WHERE evento_id = ? AND rut_usuario = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("is", $evento_id, $rut_usuario);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $mensaje = "Ya estás registrado para este evento.";
        $tipo_mensaje = "warning";
    } else {
        // Registrar asistencia
        $sql = "INSERT INTO asistencias_eventos (evento_id, rut_usuario) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $evento_id, $rut_usuario);

        if ($stmt->execute()) {
            $mensaje = "Te has registrado exitosamente para el evento.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al registrar la asistencia: " . $conn->error;
            $tipo_mensaje = "danger";
        }
        $stmt->close();
    }
    $stmt_check->close();
}

// Obtener el mes y el año actual o los seleccionados
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Obtener la fecha actual en formato 'Y-m-d'
$fecha_actual = date("Y-m-d");

// Consulta para obtener solo las capacitaciones del mes actual a partir de la fecha actual
$query = $conn->prepare("SELECT id, DAY(fecha) AS day, fecha, titulo, hora, ubicacion 
                         FROM capacitaciones 
                         WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? AND fecha >= ? 
                         ORDER BY fecha ASC");
$query->bind_param("iis", $month, $year, $fecha_actual);
$query->execute();
$result = $query->get_result();

// Generar tarjetas de capacitaciones
$trainingCards = '';
// Verificar si hay resultados en la consulta
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $trainingCards .= "
        <div id='training-{$row['id']}' class='event-card clickeable' onclick=\"location.href='capacitacion_asistencia.php?capacitacion_id={$row['id']}'\" style='cursor: pointer; position: relative;'>
            <div class='event-header'>
                <p class='event-date'>" . traducir_fecha($row['fecha']) . "</p>
                <h5 class='event-title'>{$row['titulo']}</h5>
            </div>
            <p class='event-time' style='margin-top: 0px;'>Hora: " . date('H:i', strtotime($row['hora'])) . "</p>
            <p class='event-location'>Ubicación: {$row['ubicacion']}</p>";

        // Botón "Asistir" si el usuario no está registrado y la capacitación es futura
        $capacitacion_id = $row['id'];
        $rut_usuario = $_SESSION['rut'];

        // Comprobar si el usuario ya está registrado para la capacitación
        $check_asistencia_sql = "SELECT * FROM asistencia_capacitaciones WHERE capacitacion_id = ? AND rut_usuario = ?";
        $stmt_check_asistencia = $conn->prepare($check_asistencia_sql);
        $stmt_check_asistencia->bind_param("is", $capacitacion_id, $rut_usuario);
        $stmt_check_asistencia->execute();
        $result_check_asistencia = $stmt_check_asistencia->get_result();

        if ($result_check_asistencia->num_rows == 0) {
            $trainingCards .= "<form method='POST' style='display: inline;' onclick='event.stopPropagation();'>
                                   <input type='hidden' name='capacitacion_id' value='{$row['id']}'>
                                   <button type='submit' style='width: 80%; padding: 5px; margin-top: 5px; text-align: center;' name='registrar_asistencia_c' class='btn btn-outline-primary btn-sm'>Asistir a la Capacitación</button>
                               </form>";
        } else {
            $trainingCards .= "<p style='color: #23be69; font-weight: none; margin-top: 5px;'>Ya estás registrado en esta capacitación</p>";
        }

        $stmt_check_asistencia->close();

        $trainingCards .= "</div><hr>";
    }
} else {
    // Mostrar mensaje si no hay capacitaciones para este mes
    $trainingCards = "
    <div class='card'>
        <div class='card-body'>
            <h3 class='text-center'>No hay capacitaciones disponibles</h3>
            <p class='text-center'>No se han programado capacitaciones para este mes.</p>
        </div>
    </div>";
}
    

if (isset($_POST['registrar_asistencia_c'])) {
    $capacitacion_id = $_POST['capacitacion_id'];
    $rut_usuario = $_SESSION['rut'];

    // Verificar si el usuario ya se registró en esta capacitación
    $check_sql = "SELECT * FROM asistencia_capacitaciones WHERE capacitacion_id = ? AND rut_usuario = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("is", $capacitacion_id, $rut_usuario);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $mensaje = "Ya estás registrado para esta capacitación.";
        $tipo_mensaje = "warning";
    } else {
        // Registrar asistencia
        $sql = "INSERT INTO asistencia_capacitaciones (capacitacion_id, rut_usuario) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $capacitacion_id, $rut_usuario);

        if ($stmt->execute()) {
            $mensaje = "Te has registrado exitosamente para la capacitación.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al registrar la asistencia: " . $conn->error;
            $tipo_mensaje = "danger";
        }
        $stmt->close();
    }
    $stmt_check->close();
}


$conn->close();
?>


<!DOCTYPE php>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="icon" href="Images/icono2.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="styles/style_cards.css">
    <link rel="stylesheet" href="styles/style_new_cards.css">
    <link rel="stylesheet" href="styles/style_cums.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        body{
            font-family: "Montserrat", sans-serif;
        }
        /* Agregar los estilos CSS modificados aquí */
        .custom-container {
            display: flex; /* Añadido */
            flex-wrap: wrap; /* Permitir que los elementos se ajusten en la siguiente fila si no hay espacio */
            justify-content: center; /* Centrar los elementos en el contenedor */
            margin: 20px; /* Separación entre contenedores */
        }

        .profile-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin: 10px; /* Separación entre cada tarjeta de perfil */
            width: 250px; /* Ancho fijo para las tarjetas */
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); /* Sombra para el contenedor */
            text-align: center;
        }

        .profile-picture {
            border-radius: 50%; /* Imagen redonda */
            width: 100px; /* Tamaño fijo de la imagen */
            height: 100px; /* Tamaño fijo de la imagen */
            margin-bottom: 15px; /* Separación con el contenido */
        }

        .empleado-mes-card{
            max-width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;

        }

        .cards-new-employees p{
            text-align: center;
            align-items: center;
            justify-content: center;
            display:block;
        }
        .event-card {
            margin-bottom: 15px;
            padding: 10px;
            border-left: 4px solid #00304A; /* Color de borde izquierdo */
            background-color: #c7eafa3a; /* Fondo más claro */
            border-radius: 4px;
            position: relative;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            transition: 0.1s;
        }

        .event-card:hover {
            background-color: #CCE4F7; /* Fondo en hover */
            transform: scale(1.02); /* Escala en hover */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Sombra en hover */
            cursor: pointer;
        }

        .event-header {
            display: flex;
            flex-direction: column; /* Alinea fecha y título en columna */
            margin-bottom: 10px;
        }

        .event-date {
            font-size: 1.25rem; /* Tamaño de fuente para la fecha */
            font-weight: bold;
            color: #00304A;
        }

        .event-title {
            font-size: 1rem; /* Tamaño de fuente para el título */
            color: #00304A;
            margin-top:10px;
            margin-bottom: 0px;
        }

        .event-card p {
            margin: 0px 0 0;
            color: #333; /* Color de texto en detalles */
        }


        .events-list {
            max-height: 500px; /* Altura máxima de la lista de eventos */
            overflow-y: auto; /* Scroll vertical */
            padding-right: 10px;
            background-color: #fff;
        }

        .event-link {
            text-decoration: none;
            color: inherit;
        }

        .event-link:hover {
            background-color: #f8f9fa; /* Fondo en hover */
        }

        .alert {
            max-width: 400px;
            margin: 20px;
            text-align: center;
        }

        .mensaje-popup {
            display: flex;
            justify-content: center;
        }

        .btn-outline-danger, .btn-outline-dark {
            margin-left: 5px;
        }

        .button-container {
            display: flex;
            justify-content: center;
        }
        /* Responsivo para pantallas pequeñas (máximo 768px) */
@media (max-width: 768px) {
    /* Contenedor principal */
    .custom-container {
        flex-direction: column;
        align-items: center;
    }

    /* Tarjeta de perfil */
    .profile-card {
        width: 100%;
        padding: 15px;
    }

    /* Imagen de perfil */
    .profile-picture {
        width: 80px;
        height: 80px;
    }

    /* Empleado del mes */
    .empleado-mes-card {
        flex-direction: column;
        align-items: center;
        margin: 0px;
    }

    /* Lista de eventos */
    .events-list {
        max-height: 300px;
    }

    /* Alerta */
    .alert {
        width: 90%;
    }

    /* Fechas y títulos de eventos */
    .event-date {
        font-size: 1rem;
    }

    .event-title {
        font-size: 0.9rem;
    }
}
/* Responsive para pantallas pequeñas (hasta 768px) */
@media (max-width: 720px) {
    

    /* Ajuste del botón toggle en el sidebar */
    .toggle-btn {
        display: block;
        font-size: 1.5em;
    }

    .sidebar-logo {
        text-align: center;
    }

    /* Tarjetas de perfil y empleado del mes */
    .profile-container, .empleado-mes-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 120%;
    }

 


    /* Ajuste del carrusel */
    .carousel-caption h1 {
        font-size: 1.2rem;
    }

    .carousel-caption p {
        font-size: 0.9rem;
    }

    /* Títulos y secciones */
    .titulo-home h2 {
        font-size: 1.5rem;
        
    }

    .titulo-home {
        margin-top: 10px !important; 
    }

    .titulo-home p {
        font-size: 0.9rem;
    }

    /* Footer responsive */
    .footer-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .footer-section {
        margin: 10px 0;
    }

    .social-icons a {
        font-size: 1.5em;
        margin: 0 5px;
    }

    /* Botones de navegación en swiper */
    .swiper-button-next, .swiper-button-prev {
        display: none;
    }

    /* Ajuste de elementos individuales */
    .sidebar-footer, .user-info, .header-home {
        flex-direction: column;
        align-items: center;
    }

    /* Lista de eventos y capacitaciones */
    .birthday-list-box {
        width: 100% !important;
    }
    
    #events-list, #event-list {
        flex-direction: column;
        align-items: center;
    }

    /* Ajustes de la barra de navegación y el nombre de usuario */
    .navbar, .user-nom {
        font-size: 1.2rem;
        text-align: center;
        padding: 10px 0;
    }
    .empleado-mes-card .card-emp-mes{
        width: 100% !important;
        height: 520px !important;
    }
}
    .card-body{
        max-height: 230px; /* Limitar la altura máxima del cuerpo de la tarjeta */
        overflow: auto; /* Hacer que el contenido restante sea scrolleable si excede el límite */
        scrollbar-width: none; /* Para Firefox */
    }

    .cards-new-employees-wrapper.center {
        justify-content: center;
        display: flex;
    }

    </style>
</head>

<body>
    
    
<div class="main-content">
<div class="wrapper">
        <aside id="sidebar">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                    <i class="lni lni-menu"></i>
                </button>
                <div class="sidebar-logo">
                    <a href="home.php">Intranet</a>
                </div>
            </div>
             <!-- Contenedor de la imagen de perfil -->
        <div class="profile-container text-center my-2">
        <img src="<?php 
    // Verificar si la imagen del usuario existe en la carpeta
    if (file_exists($ruta_imagen_usuario)) {
        // Si la imagen existe, se usa esa ruta
        echo $ruta_imagen_usuario;
    } else {
        // Si no existe, se usa la imagen predeterminada
        echo $imagen_default;
    }
?>" class="profile-picture" alt="Foto de Perfil">

        </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="home.php" class="sidebar-link">
                    <i class="lni lni-home"></i>

                           <span>Inicio</span>
                    </a>
                </li>
            <?php if ($_SESSION['admin'] == 1): ?>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#añadir" aria-expanded="false" aria-controls="añadir">
                        <i class="lni lni-circle-plus"></i>
                        <span>Añadir</span>
                    </a>
                    <ul id="añadir" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        
                    <li class="sidebar-item">
                        <a href="agregar_personal.php" class="sidebar-link">Agregar Empleado</a>
                    </li>
                        <li class="sidebar-item">
                            <a href="empleado_mes.php" class="sidebar-link">Agregar Empleado del Año</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="felicitaciones_agregar.php" class="sidebar-link">Agregar Felicitacion</a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <li class="sidebar-item">
                    <a href="perfil.php" class="sidebar-link">
                    <i class="lni lni-user"></i>
                        <span>Perfil</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#multi" aria-expanded="false" aria-controls="multi">
                        <i class="lni lni-users"></i>
                        <span>Personal</span>
                    </a>
                    <ul id="multi" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="empleados_meses.php" class="sidebar-link">Empleado del Año</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="felicitaciones.php" class="sidebar-link">Felicitaciones</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="personal_nuevo.php" class="sidebar-link">Nuevos empleados</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="cumpleanos.php" class="sidebar-link">Cumpleaños</a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-item">
                    <a href="calendario_prueba.php" class="sidebar-link">
                    <i class="lni lni-calendar"></i>
                    <span>Empresa</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="capacitaciones.php" class="sidebar-link">
                        <i class="lni lni-agenda"></i>
                        <span>Capacitaciones</span>
                    </a>
                </li>

                <?php if ($_SESSION['admin'] == 1): ?>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#encuestas" aria-expanded="false" aria-controls="encuestas">
                        <i class="lni lni-pencil"></i>
                        <span>Encuestas</span>
                    </a>
                    <ul id="encuestas" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        
                    <li class="sidebar-item">
                            <a href="crear_encuesta.php" class="sidebar-link">Crear encuesta</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="encuestas.php" class="sidebar-link">Encuestas</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="respuestas.php" class="sidebar-link">Respuestas de encuestas</a>
                        </li>
                    </ul>
                </li>
                <?php else: ?>
                    <li class="sidebar-item">
                    <a href="encuestas.php" class="sidebar-link">
                    <i class="lni lni-pencil"></i>
                    <span>Encuestas</span>
                    </a>
                </li>
                <?php endif; ?>
            
                <li class="sidebar-item">
                    <a href="documentos.php" class="sidebar-link">
                        <i class="lni lni-files"></i>
                        <span>Documentos</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="foro.php" class="sidebar-link">
                    <i class="lni lni-comments"></i>
                    <span>Foro</span>
                    </a>
                </li>

                <?php if ($_SESSION['rol'] == 4 || $_SESSION['admin'] == 1): ?>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#solicitudes" aria-expanded="false" aria-controls="solicitudes">
                        <i class="lni lni-popup"></i>
                        <span>Solicitudes</span>
                    </a>
                    <ul id="solicitudes" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="solicitudes.php" class="sidebar-link">Solicitudes</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="solicitudes_usuarios.php" class="sidebar-link">Solicitudes de usuarios</a>
                        </li>
                    </ul>
                </li>
                <?php else: ?>
                    <li class="sidebar-item">
                    <a href="solicitudes.php" class="sidebar-link">
                        <i class="lni lni-popup"></i>
                        <span>Solicitudes</span>
                    </a>
                </li>
                <?php endif; ?>
    


                <?php if ($_SESSION['rol'] == 4): ?>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#soporte" aria-expanded="false" aria-controls="soporte">
                        <i class="lni lni-cog"></i>
                        <span>Soporte Técnico</span>
                    </a>
                    <ul id="soporte" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="soporte.php" class="sidebar-link">Soporte Técnico</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="soporte_def.php" class="sidebar-link">Ver Solicitudes</a>
                        </li>
                    </ul>
                </li>
            <?php else: ?>
                <li class="sidebar-item">
                    <a href="soporte.php" class="sidebar-link">
                        <i class="lni lni-cog"></i>
                        <span>Soporte Informático</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($_SESSION['admin'] == 1): ?>
            <li class="sidebar-item">
                    <a href="estadisticas.php" class="sidebar-link">
                    <i class="lni lni-bar-chart"></i>
                    <span>Estadisticas</span>
                    </a>
            </li>
            <?php endif; ?>
            </ul>

            <div class="sidebar-footer" style="margin-bottom: 20px;">
                <a href="cerrar_sesion.php" class="sidebar-link">
                    <i class="lni lni-exit"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <div class="main" style="padding-top: 15px;">
        <div class="header-home">
            <div class="header">
                <div class="ficha">Ficha:‎ ‎ ‎ <?php echo $usuario; ?></div>
                <div class="user-nom">
                    <i class="fas fa-user"></i> <span><?php echo $user_data['nombre']; ?></span>
                </div>
                <div class="user-nom" style="padding: 15px;">
                <div class="notificaciones-container">
                    <span class="campanita" id="campanita">
                        🔔
                        <span class="campanita-badge" id="campanita-badge"></span>
                    </span>
                    <div class="notificaciones-desplegable" id="notificaciones">
                        <div class="notificaciones-header">
                            <h5 style="font-size: 1.4rem; margin-bottom: 3px;">📥 Notificaciones 📥</h5>
                        </div>
                        <div id="contenido-notificaciones">
                            <p style="text-align: center; color: #888;">Cargando...</p>
                        </div>
                    </div>
                </div>

                </div>
            </div>
        </div>

        <script>
document.addEventListener('DOMContentLoaded', function () {
    const campanita = document.getElementById('campanita');
    const campanitaBadge = document.getElementById('campanita-badge');
    const notificacionesDesplegable = document.getElementById('notificaciones');
    const contenidoNotificaciones = document.getElementById('contenido-notificaciones');
    let notificacionesAbiertas = false; // Bandera para rastrear si el desplegable está abierto

    // Obtener notificaciones desde el servidor
    async function obtenerNotificaciones() {
        try {
            const response = await fetch('notificaciones.php');
            const notificaciones = await response.json();

            contenidoNotificaciones.innerHTML = '';
            if (notificaciones.length > 0) {
                notificaciones.forEach(notif => {
                    const div = document.createElement('div');
                    div.classList.add('notificacion');
                    div.classList.add(notif.leida === "0" ? 'no-leida' : 'leida');
                    div.innerHTML = `
                        <p>${notif.mensaje}</p>
                        <div class="fecha-con-eliminar">
                            <span class="fecha">${new Date(notif.fecha_creacion).toLocaleString()}</span>
                            <button class="notificacion-eliminar" data-id="${notif.id}">❌</button>
                        </div>
                    `;
                    contenidoNotificaciones.appendChild(div);
                });

                // Añadir eventos para los botones de eliminar
                document.querySelectorAll('.notificacion-eliminar').forEach(btn => {
                    btn.addEventListener('click', async function() {
                        const id = this.getAttribute('data-id');
                        const notificacion = this.closest('.notificacion'); // Obtener el contenedor de la notificación
                        notificacion.classList.add('eliminando'); // Añadir clase de animación

                        // Esperar a que termine la animación antes de eliminar
                        setTimeout(async () => {
                            await eliminarNotificacion(id); // Llamada para eliminar la notificación desde el backend
                            notificacion.remove(); // Eliminar el nodo del DOM
                        }, 300); // Espera el tiempo de la transición antes de eliminar el nodo
                    });
                });
            } else {
                contenidoNotificaciones.innerHTML = '<p style="text-align: center; color: #888;">No hay notificaciones.</p>';
            }

            // Actualizar badge
            const nuevasNotificaciones = notificaciones.filter(notif => notif.leida === "0");
            if (nuevasNotificaciones.length > 0) {
                campanitaBadge.textContent = nuevasNotificaciones.length;
                campanitaBadge.style.display = 'inline-block';
            } else {
                campanitaBadge.style.display = 'none';
            }
        } catch (error) {
            console.error('Error al obtener notificaciones:', error);
        }
    }

    // Marcar notificaciones como leídas
    async function marcarNotificacionesLeidas() {
        try {
            const response = await fetch('marcar_leidas.php', { method: 'POST' });
            const resultado = await response.json();

            if (resultado.success) {
                // Cambiar las notificaciones a "leída"
                document.querySelectorAll('.notificacion.no-leida').forEach(notificacion => {
                    notificacion.classList.remove('no-leida');
                    notificacion.classList.add('leida');
                });
                // Actualizar badge
                campanitaBadge.style.display = 'none';
            }
        } catch (error) {
            console.error('Error al marcar como leídas:', error);
        }
    }

    // Eliminar notificación
    async function eliminarNotificacion(id) {
        try {
            const response = await fetch('notificacion_eliminar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            });

            const result = await response.json();
            if (result.success) {
                // Eliminar la notificación del DOM
                const notifElement = document.querySelector(`button[data-id="${id}"]`).closest('.notificacion');
                notifElement.remove();
            } else {
                console.error('Error al eliminar la notificación');
            }
        } catch (error) {
            console.error('Error al eliminar la notificación:', error);
        }
    }

    // Alternar desplegable
    campanita.addEventListener('click', () => {
        notificacionesAbiertas = !notificacionesAbiertas; // Alternar estado

        // Mostrar/ocultar desplegable
        notificacionesDesplegable.classList.toggle('active');

        if (!notificacionesAbiertas) {
            // Si se cierra el desplegable, marcar como leídas
            marcarNotificacionesLeidas();
        }
    });

    // Consultar cada 5 segundos
    setInterval(obtenerNotificaciones, 5000);

    // Cargar al inicio
    obtenerNotificaciones();
});

    </script>

        

        <div class="topnav">
        <a href="home.php" class="mr-active">Intranet</a>
        <div id="mobileLinks">
            <!-- Agregar elementos del menú existente -->
            <a href="perfil.php"><i class="lni lni-user"style="margin-right: 10px;"></i>Perfil</a>
            <?php if ($_SESSION['admin'] == 1): ?>
                <a href="agregar_personal.php"><i class="lni lni-users"style="margin-right: 10px;"></i>Agregar Personal</a>
                <a href="empleado_mes.php"><i class="lni lni-users"style="margin-right: 10px;"></i>Agregar Empleado del Mes</a>
                <a href="felicitaciones_agregar.php"><i class="lni lni-users"style="margin-right: 10px;"></i>Agregar Felicitación</a>

            <?php endif; ?>
            <a href="personal_nuevo.php"><i class="lni lni-users"style="margin-right: 10px;"></i>Personal</a>
            <a href="felicitaciones.php"><i class="lni lni-users"style="margin-right: 10px;"></i>Felicitaciones</a>
            <a href="empleados_meses.php"><i class="lni lni-users"style="margin-right: 10px;"></i>Empleado del mes</a>
            <a href="cumpleanos.php"><i class="lni lni-calendar"style="margin-right: 10px;"></i>Cumpleaños</a>
            <a href="calendario_prueba.php"><i class="lni lni-calendar"style="margin-right: 10px;"></i>Eventos</a>
            <a href="capacitaciones.php"><i class="lni lni-agenda"style="margin-right: 10px;"></i>Capacitaciones</a>
            <a href="documentos.php"><i class="lni lni-files"style="margin-right: 10px;"></i>Documentos</a>
            <a href="foro.php"><i class="lni lni-comments"style="margin-right: 10px;"></i>Foro</a>
            <a href="encuestas.php"><i class="lni lni-pencil"style="margin-right: 10px;"></i>Encuestas</a>
            <?php if ($_SESSION['admin'] == 1): ?>
                <a href="crear_encuesta.php">Crear Encuesta</a>
                <a href="respuestas.php">Respuestas de encuestas</a>
            <?php endif; ?>
            <a href="solicitudes.php"><i class="lni lni-popup" style="margin-right: 10px;"></i>Solicitudes</a>
            <?php if ($_SESSION['admin'] == 1): ?>
                <a href="solicitudes_usuarios.php">Ver solicitudes</a>
            <?php endif; ?>
            
            <a href="soporte.php"><i class="lni lni-cog"style="margin-right: 10px;"></i>Soporte Informático</a>
            <?php if ($_SESSION['rol'] == 4): ?>
                <a href="soporte_def.php">ver soportes</a>
            <?php endif; ?>

            <?php if ($_SESSION['admin'] == 1): ?>
                <a href="estadisticas.php"><i class="lni lni-bar-chart"style="margin-right: 10px;"></i>Estadisticas</a>
                <?php endif; ?>

            <a href="cerrar_sesion.php"><i class="lni lni-exit"style="margin-right: 10px;"></i>Salir</a>
        </div>
        <a href="javascript:void(0);" class="icon" onclick="toggleMobileMenu()">
            <i class="fa fa-bars"></i>
        </a>
    </div>

    <script>
    function toggleMobileMenu() {
        const mobileLinks = document.getElementById("mobileLinks");
        if (mobileLinks.classList.contains("open")) {
            mobileLinks.classList.remove("open");
        } else {
            mobileLinks.classList.add("open");
        }
    }
    </script>
    


    <div class="alertas-container">
    <span class="icono-campana" id="icono-campana">
        🔔
        <span class="badge-campana" id="badge-campana"></span>
    </span>
    <div class="alertas-desplegable" id="alertas">
        <div class="alertas-header">
            <h5 style="font-size: 1.4rem; margin-bottom: 3px;">📥 Alertas 📥</h5>
        </div>
        <div id="contenido-alertas">
            <p style="text-align: center; color: #888;">Cargando...</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const iconoCampana = document.getElementById('icono-campana');
    const badgeCampana = document.getElementById('badge-campana');
    const alertasDesplegable = document.getElementById('alertas');
    const contenidoAlertas = document.getElementById('contenido-alertas');
    let alertasAbiertas = false; // Bandera para rastrear si el desplegable está abierto

    // Obtener alertas desde el servidor
    async function obtenerAlertas() {
        try {
            const response = await fetch('notificaciones.php');
            const alertas = await response.json();

            contenidoAlertas.innerHTML = '';
            if (alertas.length > 0) {
                alertas.forEach(alerta => {
                    const div = document.createElement('div');
                    div.classList.add('alerta');
                    div.classList.add(alerta.leida === "0" ? 'no-leida' : 'leida');
                    div.innerHTML = `
                        <p>${alerta.mensaje}</p>
                        <div class="fecha-con-eliminar">
                            <span class="fecha">${new Date(alerta.fecha_creacion).toLocaleString()}</span>
                            <button class="alerta-eliminar" data-id="${alerta.id}">❌</button>
                        </div>
                    `;
                    contenidoAlertas.appendChild(div);
                });

                // Añadir eventos para los botones de eliminar
                document.querySelectorAll('.alerta-eliminar').forEach(btn => {
                    btn.addEventListener('click', async function() {
                        const id = this.getAttribute('data-id');
                        await eliminarAlerta(id);
                    });
                });
            } else {
                contenidoAlertas.innerHTML = '<p style="text-align: center; color: #888;">No hay alertas.</p>';
            }

            // Actualizar badge
            const nuevasAlertas = alertas.filter(alerta => alerta.leida === "0");
            if (nuevasAlertas.length > 0) {
                badgeCampana.textContent = nuevasAlertas.length;
                badgeCampana.style.display = 'inline-block';
            } else {
                badgeCampana.style.display = 'none';
            }
        } catch (error) {
            console.error('Error al obtener alertas:', error);
        }
    }

    // Marcar alertas como leídas
    async function marcarAlertasLeidas() {
        try {
            const response = await fetch('marcar_leidas.php', { method: 'POST' });
            const resultado = await response.json();

            if (resultado.success) {
                // Cambiar las alertas a "leída"
                document.querySelectorAll('.alerta.no-leida').forEach(alerta => {
                    alerta.classList.remove('no-leida');
                    alerta.classList.add('leida');
                });
                // Actualizar badge
                badgeCampana.style.display = 'none';
            }
        } catch (error) {
            console.error('Error al marcar como leídas:', error);
        }
    }

    // Eliminar alerta
        async function eliminarAlerta(id) {
            try {
                const response = await fetch('notificacion_eliminar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                });

                const result = await response.json();
                if (result.success) {
                    // Buscar la alerta y añadir la clase de animación
                    const alertaElement = document.querySelector(`button[data-id="${id}"]`).closest('.alerta');
                    alertaElement.classList.add('eliminando'); // Añadir clase de animación

                    // Esperar a que termine la animación antes de eliminar
                    setTimeout(() => {
                        alertaElement.remove(); // Eliminar el nodo del DOM
                    }, 300); // Esperar el tiempo de la transición antes de eliminar el nodo
                } else {
                    console.error('Error al eliminar la alerta');
                }
            } catch (error) {
                console.error('Error al eliminar la alerta:', error);
            }
        }

    // Alternar desplegable
    iconoCampana.addEventListener('click', () => {
        alertasAbiertas = !alertasAbiertas; // Alternar estado

        // Mostrar/ocultar desplegable
        alertasDesplegable.classList.toggle('active');

        if (!alertasAbiertas) {
            // Si se cierra el desplegable, marcar como leídas
            marcarAlertasLeidas();
        }
    });

    // Consultar cada 5 segundos
    setInterval(obtenerAlertas, 1000);

    // Cargar al inicio
    obtenerAlertas();
});
</script>





        <div class="sliderimages">
        <div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
            <div class="carousel-caption d-none d-md-block">
                <h1>Bienvenido a la Intranet Clinica San Agustin!</h1>
                <p>Este nuevo sistema ayudara a todos el personal de nuestra clinica, desde poder 
                    visualizar informacion importante como eventos empresariales, capacitaciones, 
                    nuevos empleados, soporte y muchas mas funciones que hacen de esta un sistema agradable</p>
            </div>
            <img src="Images/carousel/img6.png" class="d-block w-100" alt="...">
            
            </div>
            <div class="carousel-item">
            <img src="Images/carousel/img2.jpg" class="d-block w-100" alt="...">
            <div class="carousel-caption d-none d-md-block">
                <h1>Bienvenido a la Intranet Clinica San Agustin!</h1>
                <p>Este nuevo sistema ayudara a todos el personal de nuestra clinica, desde poder 
                    visualizar informacion importante como eventos empresariales, capacitaciones, 
                    nuevos empleados, soporte y muchas mas funciones que hacen de esta un sistema agradable</p>
            </div>
            </div>
            <div class="carousel-item">
            <img src="Images/carousel/img5.jpg" class="d-block w-100" alt="...">
            <div class="carousel-caption d-none d-md-block">
                <h1>Bienvenido a la Intranet Clinica San Agustin!</h1>
                <p>Este nuevo sistema ayudara a todos el personal de nuestra clinica, desde poder 
                    visualizar informacion importante como eventos empresariales, capacitaciones, 
                    nuevos empleados, soporte y muchas mas funciones que hacen de esta un sistema agradable</p>
            </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
        </div>


        </div>

        <div class="titulo-home">
            <h2 style="width: 100%; text-align: center; margin-top: 20px;">Nuestro Empleado del Año</h2>
            <p>Queremos honorar a nuestro empleado mas reconocido en el año, destacando y colabrondo mucho </p>
            <p>para clínica como con sus compañeros!</p>
        </div>

        <div class="titulo-home">
        <h2 style="width: 100%; text-align: center; margin-top: 20px;">Empleado del Año - <?php echo traducir_mes(date('F Y')); ?></h2>
        
        </div>
    <div class="empleado-mes-card" style="margin-bottom: 0px;">

    <div class="card-emp-mes" style="height: 200px;">
    <?php
// Incluir la conexión a la base de datos
include("conexion.php");

// Ruta de la carpeta de fotos
$carpeta_fotos = 'Images/fotos_personal/';
$imagen_default = 'Images/profile_photo/imagen_default.jpg';

// Obtener el mes y año actuales
$mes_actual = date('m'); // Mes actual
$año_actual = date('Y');  // Año actual

// Consulta para obtener el empleado del mes actual
$query_emp_mes = "SELECT p.nombre, p.imagen, em.descripcion, c.NOMBRE_CARGO
          FROM empleado_mes em 
          JOIN personal p ON em.rut = p.rut 
          JOIN cargos c ON p.cargo_id = c.id
          WHERE MONTH(em.mes_year) = '$mes_actual' AND YEAR(em.mes_year) = '$año_actual'
          LIMIT 1";

// Ejecutar la consulta
$result_emp_mes = $conn->query($query_emp_mes);

// Si no hay resultados, buscar el empleado del mes anterior
if ($result_emp_mes->num_rows == 0) {
    $mes_anterior = date('m', strtotime('first day of -1 month'));
    $año_anterior = date('Y', strtotime('first day of -1 month'));

    // Consulta para obtener el empleado del mes anterior
    $query_emp_mes_anterior = "SELECT p.nombre, p.imagen, em.descripcion, c.NOMBRE_CARGO
              FROM empleado_mes em 
              JOIN personal p ON em.rut = p.rut 
              JOIN cargos c ON p.cargo_id = c.id
              WHERE MONTH(em.mes_year) = '$mes_anterior' AND YEAR(em.mes_year) = '$año_anterior'
              LIMIT 1";

    // Ejecutar la consulta para el mes anterior
    $result_emp_mes = $conn->query($query_emp_mes_anterior);

    // Si tampoco hay empleado del mes anterior
    if ($result_emp_mes->num_rows == 0) {
        // Mostrar una tarjeta con el mensaje de que no hay empleados del mes
        echo "
        <div class='card' style='height: 505px;'>
            <div class='card-body'>
                <h3 class='text-center'>No hay empleado del año registrado</h3>
                <p class='text-center'>No se encontró empleado del año para este mes ni para el mes anterior.</p>
            </div>
        </div>";
    } else {
        // Mostrar el empleado del mes anterior
        $empleado_mes = $result_emp_mes->fetch_assoc();
        $img_emp_mes = $carpeta_fotos . $empleado_mes['imagen'];
        $img_emp_mes = file_exists($img_emp_mes) ? $img_emp_mes : $imagen_default;

        echo "
        <div class='card' style='height: 505px;'>
            <h3>Empleado del Año Anterior</h3>
            <img src='$img_emp_mes' alt='" . $empleado_mes['nombre'] . "' class='empleado-mes-imagen'>
            <h3 class='empleado-mes-nombre'>" . $empleado_mes['nombre'] . "</h3>
            <div class='card-body'>
                <p class='empleado-mes-cargo'>" . $empleado_mes['NOMBRE_CARGO'] . "</p>
                <p class='empleado-mes-descripcion'>" . $empleado_mes['descripcion'] . "</p>
            </div>
        </div>";
    }
} else {
    // Si hay Empleado del Año actual, mostrarlo
    $empleado_mes = $result_emp_mes->fetch_assoc();
    $img_emp_mes = $carpeta_fotos . $empleado_mes['imagen'];
    $img_emp_mes = file_exists($img_emp_mes) ? $img_emp_mes : $imagen_default;

    echo "
    <div class='card' style='height: 505px;'>
        <img src='$img_emp_mes' alt='" . $empleado_mes['nombre'] . "' class='empleado-mes-imagen'>
        <h3 class='empleado-mes-nombre'>" . $empleado_mes['nombre'] . "</h3>
        <div class='card-body'>
            <p class='empleado-mes-cargo'>" . $empleado_mes['NOMBRE_CARGO'] . "</p>
            <p class='empleado-mes-descripcion'>" . $empleado_mes['descripcion'] . "</p>
        </div>
    </div>";
}


// Cerrar la conexión a la base de datos

?>
</div>


<!-- Contenedor de las tarjetas de cumpleaños -->
<div class="birthday-list-box" id="#cumpleaños" style="justify-content: center;
        align-items: center ;">
        <h4 >Cumpleaños de este mes</h4>
        <div id="birthday-list"><?php echo $cards_html_cum; ?></div> <!-- Aquí se cargarán las tarjetas dinámicamente -->
        </div>
</div>
    
<div class="titulo-home" style="margin-top: 50px;">
            <h2 style="width: 100%; text-align: center; margin-top: 20px;">Eventos y Capacitaciones</h2>
            <p>Estos son los eventos y las capacitaciones del mes pendientes, si quieres ver eventos pasados, puedes acceder al portal respectivo!</p>
            
        </div>

<div class="empleado-mes-card">
    <div  class="birthday-list-box" style="justify-content: center; align-items: center; width: 50%;">
        <h4>Capacitaciones de este mes</h4>
        <div id="events-list">
            <?php echo $trainingCards; ?>
        </div>
    </div>

    <!-- Contenedor de las tarjetas de eventos -->
    <div  class="birthday-list-box" style="justify-content: center; align-items: center;width: 50%;">
        <h4>Eventos de este mes</h4>
        <div id="event-list">
            <?php echo $eventCards; ?>
        </div> 
        <!-- Aquí se cargarán las tarjetas de eventos -->
    </div>
    
</div>

<div class="empleado-mes-card" id="empleados-nuevos" style="display: block; width:100%; margin-bottom: 0px !important; padding-bottom: 0px !important;">

<?php
// Verificar si hay resultados y mostrar el contenido solo si existen usuarios nuevos

// Consulta para obtener felicitaciones del mes actual
$sql_felicitaciones = "SELECT p.nombre, p.imagen, em.descripcion, c.NOMBRE_CARGO
          FROM felicitaciones em 
          JOIN personal p ON em.rut = p.rut 
          JOIN cargos c ON p.cargo_id = c.id
          WHERE MONTH(em.mes_year) = ? AND YEAR(em.mes_year) = ?";

$stmt_felicitacion = $conn->prepare($sql_felicitaciones);
$stmt_felicitacion->bind_param('ii', $mes_actual, $año_actual);
$stmt_felicitacion->execute();
$result_felicitacion = $stmt_felicitacion->get_result();

if ($result_felicitacion->num_rows > 0) {
    ?>
    <div class="titulo-home">
        <h4 style="font-size: 2rem;">Felicitaciones de Personal</h4>
        <p style="margin-bottom: 0px;">Le queremos dar las felicitaciones a estos colaboradores de la clínica los cuales se merecen el reconocimiento por su trabajo!</p>
    </div>
    <!-- Contenedor principal del perfil -->
    <div class="body-cards">
        <div class="slide-container swiper" style="padding-top: 0px;">
            <div class="slide-content cards-new-employees">
                <!-- Ajustar el estilo dinámicamente según el número de registros -->
                <div class="cards-new-employees-wrapper swiper-wrapper <?php echo ($result_felicitacion->num_rows < 3) ? 'center' : ''; ?>">
                    <?php
                    while ($nuevo_user = $result_felicitacion->fetch_assoc()) {
                        // Ruta de la imagen del usuario nuevo
                        $ruta_imagen_nuevo = $carpeta_fotos . $nuevo_user['imagen'];
                        
                        // Verificar si la imagen del usuario nuevo existe
                        $imagen_usuario_nuevo = file_exists($ruta_imagen_nuevo) ? $ruta_imagen_nuevo : $imagen_default;
                        // Obtener el nombre completo del usuario logeado
                        $nombre_usuario = $_SESSION['nombre'];  // Nombre completo de la persona logeada (quien va a dar la bienvenida)

                        // Asegurarnos de que $nuevo_user contiene los datos del nuevo usuario
                        $nombre_nuevo_usuario = $nuevo_user['nombre'];  // Nombre del nuevo usuario     
                        ?>
                        <div class="cards-new-employees-card swiper-slide">
                            <div class="image-content cards-new-employees-image">
                                <span class="overlay cards-new-employees-overlay"></span>
                                <div class="cards-new-employees-image-wrapper">
                                    <img src="<?php echo $imagen_usuario_nuevo; ?>" class="profile-picture-nuevo" alt="Foto de Perfil">
                                </div>
                            </div>
                            <div class="cards-new-employees-content" style="overflow: auto; scrollbar-width: none; ">
                                <h2 class="cards-new-employees-name" style="font-size: 18px; margin-bottom: 10px; ">¡Muchas Gracias!</h2>
                                <h2 class="cards-new-employees-name" style="font-size: 20px;  margin-bottom: 10px; color: #0056b3;"><?php echo $nuevo_user['nombre']; ?></h2>
                            
                                <p class="cards-new-employees-description"><strong>Cargo:</strong> <?php echo $nuevo_user['NOMBRE_CARGO']; ?></p>
                                <p class="cards-new-employees-description"><strong>Descripcion:</strong> <?php echo $nuevo_user['descripcion']; ?></p>
                               
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <div class="swiper-button-next swiper-navBtn"></div>
                <div class="swiper-button-prev swiper-navBtn"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>

    <?php
}
?>

</div>


<div class="empleado-mes-card" id="empleados-nuevos" style="display: block; width:100%; margin-top: 0px !important;">
    
<?php

// Verificar si hay resultados y mostrar el contenido solo si existen usuarios nuevos
if ($result_nuevos->num_rows > 0) {
    ?>
    <div class="titulo-home">
        <h4 style="font-size: 2rem;">Usuarios Nuevos de este Mes</h4>
        <p style="margin-bottom: 0px;">Le damos la bienvenida a nuestro nuevo personal que se ha sumado a la clínica!</p>
    </div>
    <!-- Contenedor principal del perfil -->
    <div class="body-cards">
        <div class="slide-container swiper" style="padding-top: 0px;">
            <div class="slide-content cards-new-employees">
            <div class="cards-new-employees-wrapper swiper-wrapper <?php echo ($result_nuevos->num_rows < 3) ? 'center' : ''; ?>">
            <?php
                    while ($nuevo_user = $result_nuevos->fetch_assoc()) {
                        // Ruta de la imagen del usuario nuevo
                        $ruta_imagen_nuevo = $carpeta_fotos . $nuevo_user['imagen'];
                        
                        // Verificar si la imagen del usuario nuevo existe
                        $imagen_usuario_nuevo = file_exists($ruta_imagen_nuevo) ? $ruta_imagen_nuevo : $imagen_default;
                       // Obtener el nombre completo del usuario logeado
                        $nombre_usuario = $_SESSION['nombre'];  // Nombre completo de la persona logeada (quien va a dar la bienvenida)

                        // Asegurarnos de que $nuevo_user contiene los datos del nuevo usuario
                        $rut_usuario = $nuevo_user['rut'];
                        $nombre_nuevo_usuario = $nuevo_user['nombre'];  // Nombre del nuevo usuario

                        // Verificamos si ya existe una notificación de bienvenida para este usuario
                        $query_check = "SELECT * FROM notificaciones WHERE rut = ? AND mensaje LIKE ?";
                        $stmt_check = $conn->prepare($query_check);
                        $mensaje_bienvenida = "El usuario " . $nombre_usuario . " le dio la bienvenida."; // El mensaje debe ser exactamente como se almacenará

                        // Buscamos en la base de datos si ya existe este mensaje para el nuevo usuario
                        $stmt_check->bind_param("ss", $rut_usuario, $mensaje_bienvenida);
                        $stmt_check->execute();
                        $result_check = $stmt_check->get_result();

                        $mostrar_boton = ($result_check->num_rows == 0);  // Si no hay notificación, mostramos el botón

                        $stmt_check->close();
                        
                        ?>
                        <div class="cards-new-employees-card swiper-slide">
                            <div class="image-content cards-new-employees-image">
                                <span class="overlay cards-new-employees-overlay"></span>
                                <div class="cards-new-employees-image-wrapper">
                                    <img src="<?php echo $imagen_usuario_nuevo; ?>" class="profile-picture-nuevo" alt="Foto de Perfil">
                                </div>
                            </div>
                            <div class="cards-new-employees-content">
                                <h2 class="cards-new-employees-name" style="font-size: 20px;"><?php echo $nuevo_user['nombre']; ?></h2>
                                <p class="cards-new-employees-description"><strong>Fecha de Nacimiento:</strong> <?php echo traducir_fecha($nuevo_user['fecha_nacimiento']); ?></p>
                                <p class="cards-new-employees-description"><strong>Cargo:</strong> <?php echo $nuevo_user['NOMBRE_CARGO']; ?></p>
                                <p class="cards-new-employees-description"><strong>Fecha de Ingreso:</strong> <?php echo traducir_fecha($nuevo_user['fecha_creacion']); ?></p>
                                
                                <!-- Botón para dar la bienvenida -->
                                <?php if ($mostrar_boton): ?>
                                    <button type="button" class="cards-new-employees-button btn-bienvenida" 
                                        onclick="darBienvenida('<?php echo $nuevo_user['rut']; ?>', '<?php echo $_SESSION['nombre']; ?>', 'home.php')">
                                        ¡Bienvenido!
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <div class="swiper-button-next swiper-navBtn"></div>
                <div class="swiper-button-prev swiper-navBtn"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>

    <?php
}
?>
    
</div>

<!-- Incluir SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Función para enviar la notificación de cumpleaños
function saludarCumple(rutCumpleanero, nombreCumpleanero) {
    // Mensaje personalizado
    var nombreUsuario = "<?php echo $_SESSION['nombre']; ?>"; // Usuario logeado
    var mensaje = "🎂El usuario " + nombreUsuario + " te ha deseado un feliz cumpleaños.🎂";

    // Mostrar una alerta de confirmación antes de enviar la notificación
    Swal.fire({
        icon: 'info',
        title: '¿Deseas enviarle un saludo?',
        text: '¡Esta acción notificará a ' + nombreCumpleanero + '!',
        showCancelButton: true,
        confirmButtonText: 'Sí, saludar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Enviar la notificación vía AJAX
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "insertar_notificacion.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Saludo enviado!',
                        text: 'Tu saludo ha sido enviado a ' + nombreCumpleanero + '.',
                        timer: 1500
                    });
                    // Desaparecer el botón después de dar la bienvenida
                    var boton = document.querySelector('.greet-btn');
                    boton.style.display = 'none'; // Oculta el botón
                }
            };
            xhr.send("rut_usuario=" + rutCumpleanero + "&mensaje=" + encodeURIComponent(mensaje));
        }
    });
}
</script>

<script>
// Función para dar la bienvenida y enviar la notificación
function darBienvenida(rutUsuario, nombreUsuario) {
    // Mostrar SweetAlert con la notificación
    Swal.fire({
        icon: 'success',
        title: '¡Bienvenido!',
        text: 'Le has dado la bienvenida al nuevo usuario.',
        showConfirmButton: false,
        timer: 1500
    }).then(() => {
        // Realizar la petición para insertar la notificación
        var mensaje = "El usuario " + nombreUsuario + " le dio la bienvenida.";
        
        // Hacer la solicitud AJAX para insertar la notificación
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "insertar_notificacion.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // Redirigir a la sección de empleados nuevos después de insertar la notificación
                window.location.href = "home.php#empleados-nuevos";
                
                // Desaparecer el botón después de dar la bienvenida
                var boton = document.querySelector('.btn-bienvenida');
                boton.style.display = 'none'; // Oculta el botón
            }
        };
        xhr.send("rut_usuario=" + rutUsuario + "&mensaje=" + encodeURIComponent(mensaje));
    });
}
</script>



<!-- Agrega este script en tu HTML, preferentemente al final del cuerpo (body) -->
<footer class="footer">
    <div class="footer-container">
        <div class="footer-section">
            <h4>Contáctanos</h4>
            <p>Teléfono: +56 9 1234 5678</p>
            <p>Email: contacto@clinicadesalud.cl</p>
        </div>
        <div class="footer-section">
            <h4>Síguenos en Redes Sociales</h4>
            <div class="social-icons">
                <a href="https://www.facebook.com" target="_blank"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.twitter.com" target="_blank"><i class="fab fa-twitter"></i></a>
                <a href="https://www.instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="https://www.linkedin.com" target="_blank"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
        <div class="footer-section">
            <h4>Dirección</h4>
            <p>Avenida Siempre Viva 742</p>
            <p>Santiago, Chile</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2024 Clínica de Salud. Todos los derechos reservados.</p>
    </div>
</footer>

</div>
  <!-- Linking SwiperJS script -->
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

  <!-- Linking custom script -->
  <script src="scripts/script_cards.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="scripts/script_nav_home.js"></script>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- JavaScript -->
<script src="scripts/script_new_cards.js"></script>
</body>



</html>