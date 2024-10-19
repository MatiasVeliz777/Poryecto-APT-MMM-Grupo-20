<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); // Redirigir al login si no ha iniciado sesión
    exit();
}

$error = "";
include("conexion.php");

// Obtener el usuario que ha iniciado sesión
$usuario = $_SESSION['usuario'];

// Consultar los datos del empleado en la tabla 'personal'
$sql = "SELECT rut, nombre, correo, imagen, fecha_nacimiento, cargo_id, rol_id
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
    // Guardar el rol en la sesión
    $_SESSION['rol'] = $rol;
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

    // Generar el HTML de la tarjeta de cumpleaños
    $cards_html_cum .= "
    <div id='birthday-$dia_cum' class='birthday-card'>
        <img src='$imagen_cum' alt='Foto de $nombre_cum'>
        <div class='birthday-info'>
            <h5>🎂 $nombre_cum</h5>
            <p>$fecha_cum</p>";

    // Mostrar el botón solo si el empleado está cumpliendo años hoy
    if ($dia_cum == $dia_actual && $mes_cum == $mes_actual) {
        $cards_html_cum .= "<button class='greet-btn'>Saludarlo en su día</button>";
    }

    $cards_html_cum .= "
        </div>
    </div>";
}

$stmt_tarjetas_cumple->close();
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
                    <a href="home.php">Portal RHH</a>
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
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#profile" aria-expanded="false" aria-controls="profile">
                        <i class="lni lni-user"></i>
                        <span>Perfil</span>
                    </a>
                    <ul id="profile" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="perfil.php" class="sidebar-link">Perfil</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Mis Datos</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="lni lni-agenda"></i>
                        <span>Capacitaciones</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#auth" aria-expanded="false" aria-controls="auth">
                        <i class="lni lni-protection"></i>
                        <span>Eventos</span>
                    </a>
                    <ul id="auth" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="calendario.php" class="sidebar-link">Empresa</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">cumpleaños</a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                    <i class="lni lni-layout"></i>
                        <span>Documentacion</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="lni lni-popup"></i>
                        <span>Foro</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#multi" aria-expanded="false" aria-controls="multi">
                        <i class="lni lni-layout"></i>
                        <span>Personal</span>
                    </a>
                    <ul id="multi" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Empleado del mes</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Nuevos empleados</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Cumpleaños</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="solicitud.php" class="sidebar-link">
                        <i class="lni lni-popup"></i>
                        <span>Solicitudes</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="soporte.php" class="sidebar-link">
                        <i class="lni lni-cog"></i>
                        <span>Soporte tecnico</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="#" class="sidebar-link">
                    <i class="lni lni-exit"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        
        <div class="main" style="padding-top: 14px;">
        <div class="header-home">
            <div class="header">
                <div class="ficha">Ficha:‎ ‎ ‎ <?php echo $usuario; ?></div>
                <div class="user-nom">
                    <i class="fas fa-user"></i> <span><?php echo $user_data['nombre']; ?></span>
                </div>
                <div class="navbar"><a href="#"><i class="fa-solid fa-magnifying-glass"></i></a></div>
                <div class="user-info">
                    <span><?php echo $usuario; ?></span>
                    <div class="Salir"><a href="cerrar_sesion.php"><i class="fas fa-sign-out-alt"></i> Salir </a></div>
            </div>
            </div>
                
        </div>
        
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
            <h2 style="width: 100%; text-align: center; margin-top: 20px;">Nuestro Empleado del Mes</h2>
            <p>Queremos honorar a nuestro empleado mas activo en el mes, destacando y colabrondo mucho </p>
            <p>para empresa como con sus compañeros!</p>
        </div>

        <div class="titulo-home">
        <h2 style="width: 100%; text-align: center; margin-top: 20px;">Empleado del Mes - <?php echo traducir_mes(date('F Y')); ?></h2>
        </div>
    <div class="empleado-mes-card">

    <div class="card-emp-mes">
    <?php
// Incluir la conexión a la base de datos
include("conexion.php");

// Ruta de la carpeta de fotos
$carpeta_fotos = 'Images/fotos_personal/';
$imagen_default = 'Images/profile_photo/imagen_default.jpg';

/// Obtener el mes y año actuales
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
    // Obtener el mes anterior en formato 'Y-m-01' (primer día del mes anterior)
    // Obtener el mes anterior y año
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
        echo "<p>No hay empleado del mes registrado para este mes ni para el mes anterior.</p>";
    } else {
        // Mostrar el empleado del mes anterior
        $empleado_mes = $result_emp_mes->fetch_assoc();
        $img_emp_mes = $carpeta_fotos . $empleado_mes['imagen'];
        $img_emp_mes = file_exists($img_emp_mes) ? $img_emp_mes : $imagen_default;

        // Mostrar la información del empleado del mes anterior
        echo "
        <div class='card'>
            <h3>Empleado del Mes Anterior</h3>
            <img src='$img_emp_mes' alt='" . $empleado_mes['nombre'] . "' class='empleado-mes-imagen'>
            <h3 class='empleado-mes-nombre'>" . $empleado_mes['nombre'] . "</h3>
            <p class='empleado-mes-cargo'>" . $empleado_mes['NOMBRE_CARGO'] . "</p>
            <p class='empleado-mes-descripcion'>" . $empleado_mes['descripcion'] . "</p>
        </div>";
    }
} else {
    // Si hay empleado del mes actual, mostrarlo
    $empleado_mes = $result_emp_mes->fetch_assoc();
    $img_emp_mes = $carpeta_fotos . $empleado_mes['imagen'];
    $img_emp_mes = file_exists($img_emp_mes) ? $img_emp_mes : $imagen_default;

    // Mostrar la información del empleado del mes actual
    echo "
    <div class='card'>
        <img src='$img_emp_mes' alt='" . $empleado_mes['nombre'] . "' class='empleado-mes-imagen'>
        <h3 class='empleado-mes-nombre'>" . $empleado_mes['nombre'] . "</h3>
        <p class='empleado-mes-cargo'>" . $empleado_mes['NOMBRE_CARGO'] . "</p>
        <p class='empleado-mes-descripcion'>" . $empleado_mes['descripcion'] . "</p>
    </div>";
}

// Cerrar la conexión a la base de datos
$conn->close();
?>


    </div>


    <!-- Contenedor de las tarjetas de cumpleaños -->
    <div class="birthday-list-box" style="justify-content: center;
            align-items: center ;">
            <h4 >Cumpleaños de este mes</h4>
            <div id="birthday-list"><?php echo $cards_html_cum; ?></div> <!-- Aquí se cargarán las tarjetas dinámicamente -->
        </div>
</div>



        <div class="titulo-home">
            <h2 style="width: 100%; text-align: center; margin-top: 20px;">Usuarios Nuevos de este Mes</h2>
            <p>le damos la bienvenida a nuestros nuevo personal que se ha sumado a la clinica!</p>
        </div>
        <!-- Contenedor principal del perfil -->


<div class="body-cards">
<div class="slide-container swiper">
    <div class="slide-content cards-new-employees">
    <div class="cards-new-employees-wrapper swiper-wrapper" style="height: none">
    <?php
    if ($result_nuevos->num_rows > 0) {
        while ($nuevo_user = $result_nuevos->fetch_assoc()) {
            // Ruta de la imagen del usuario nuevo
            $ruta_imagen_nuevo = $carpeta_fotos . $nuevo_user['imagen'];
            
            // Verificar si la imagen del usuario nuevo existe
            $imagen_usuario_nuevo = file_exists($ruta_imagen_nuevo) ? $ruta_imagen_nuevo : $imagen_default;
            ?>  
        <div class="cards-new-employees-card swiper-slide">
          <div class="image-content cards-new-employees-image">
            <span class="overlay cards-new-employees-overlay"></span>
            <div class="cards-new-employees-image-wrapper">
            <img src="<?php echo $imagen_usuario_nuevo; ?>" class="profile-picture-nuevo" alt="Foto de Perfil">
            </div>
          </div>
          <div class="cards-new-employees-content">
            <h2 class="cards-new-employees-name"><?php echo $nuevo_user['nombre']; ?></h2>
            <p  class="cards-new-employees-description"><strong>Fecha de Nacimiento:</strong> <?php echo traducir_fecha($nuevo_user['fecha_nacimiento']); ?></p>
            <p class="cards-new-employees-description"><strong>Cargo:</strong> <?php echo $nuevo_user['NOMBRE_CARGO']; ?></p>
            <p class="cards-new-employees-description"><strong>Fecha de Ingreso:</strong> <?php echo traducir_fecha($nuevo_user['fecha_creacion']); ?></p>

            <button class="cards-new-employees-button">Bienvenido!</button>
          </div>
        </div>

        <?php
        }
    } else {
        echo '<p>No hay usuarios nuevos este mes.</p>';
    }
    ?>
        
      </div>
    </div>

    <div class="swiper-button-next swiper-navBtn"></div>
    <div class="swiper-button-prev swiper-navBtn"></div>
    <div class="swiper-pagination"></div>
  </div>



</div>
  <!-- Linking SwiperJS script -->
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

  <!-- Linking custom script -->
  <script src="js/script_cards.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="js/script_nav_home.js"></script>

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
</body>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- JavaScript -->
<script src="script_new_cards.js"></script>

</html>