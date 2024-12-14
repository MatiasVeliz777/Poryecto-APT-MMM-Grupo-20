<?php
// Conectar a la base de datos
include('conexion.php');

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
        WHERE rut = (SELECT rut FROM usuarios WHERE nombre_usuario = '$usuario')";;
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



$sql_cargo = "SELECT NOMBRE_CARGO FROM cargos WHERE id = '" . $user_data['cargo_id'] . "'";

$result_cargo = $conn->query($sql_cargo);

if ($result_cargo->num_rows > 0) {
    $cargo_data = $result_cargo->fetch_assoc(); // Extraer los datos del usuario
} else {
    $error = "No se encontraron datos para el cargo.";
}

// Verificar si se ha proporcionado un evento_id
if (isset($_GET['evento_id'])) {
    $evento_id = intval($_GET['evento_id']);
    
    // Obtener los detalles del evento
    $evento_sql = "SELECT titulo, fecha, hora, ubicacion FROM eventos WHERE id = ?";
    $stmt_evento = $conn->prepare($evento_sql);
    $stmt_evento->bind_param("i", $evento_id);
    $stmt_evento->execute();
    $result_evento = $stmt_evento->get_result();

    if ($result_evento->num_rows > 0) {
        $evento = $result_evento->fetch_assoc();
    } else {
        echo "Evento no encontrado.";
        exit();
    }

    // Obtener los asistentes al evento
    $asistentes_sql = "SELECT p.nombre, p.rut, p.imagen, a.rut_usuario FROM asistencias_eventos a
                       JOIN personal p ON a.rut_usuario = p.rut
                       WHERE a.evento_id = ?";
    $stmt_asistentes = $conn->prepare($asistentes_sql);
    $stmt_asistentes->bind_param("i", $evento_id);
    $stmt_asistentes->execute();
    $result_asistentes = $stmt_asistentes->get_result();
} else {
    echo "No se ha especificado un evento.";
    exit();
}

function traducir_mes($fecha) {
    $meses = array(
        "January" => "Enero", "February" => "Febrero", "March" => "Marzo", 
        "April" => "Abril", "May" => "Mayo", "June" => "Junio", 
        "July" => "Julio", "August" => "Agosto", "September" => "Septiembre", 
        "October" => "Octubre", "November" => "Noviembre", "December" => "Diciembre"
    );
    
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
    
    return " $dia_numero de $mes_nombre de $anio";
}


// Supongamos que tienes el ID del evento y el RUT del usuario logueado
$evento_id = intval($_GET['evento_id']);
$usuario_rut = $_SESSION['rut']; // RUT del usuario logueado

// Verificar si el usuario está registrado en el evento
// Obtener la fecha actual
$fecha_actual = date("Y-m-d");

$query_asistencia = "SELECT asistencias_eventos.*, eventos.fecha 
              FROM asistencias_eventos 
              INNER JOIN eventos ON asistencias_eventos.evento_id = eventos.id 
              WHERE asistencias_eventos.evento_id = ? 
              AND asistencias_eventos.rut_usuario = ? 
              AND eventos.fecha >= ?";

$stmt_asistencia = $conn->prepare($query_asistencia);
$stmt_asistencia->bind_param("iss", $evento_id, $usuario_rut, $fecha_actual );
$stmt_asistencia->execute();
$result_asistencia = $stmt_asistencia->get_result();

$esta_asistiendo = ($result_asistencia->num_rows > 0);
?>
 

<!DOCTYPE php>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="Images/icono2.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="styles/style_cards.css">
    <link rel="stylesheet" href="styles/style_new_cards.css">
    <link rel="stylesheet" href="styles/style_encuestas.css">
    <link rel="stylesheet" href="styles/style_evento_imgs.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <style>
          
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
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#multi" aria-expanded="false" aria-controls="multi">
                        <i class="lni lni-users"></i>
                        <span>Personal</span>
                    </a>
                    <ul id="multi" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <?php if ($_SESSION['rol'] == 5): ?>
                    <li class="sidebar-item">
                            <a href="agregar_personal.php" class="sidebar-link">Agregar Empleado</a>
                        </li>
                    <li class="sidebar-item">
                            <a href="empleado_mes.php" class="sidebar-link">Agregar Empleado del Mes</a>
                        </li>
                        <?php endif; ?>
                    <li class="sidebar-item">
                            <a href="empleados_meses.php" class="sidebar-link">Empleado del mes</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="personal_nuevo.php" class="sidebar-link">Nuevos empleados</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="cumpleaños.php" class="sidebar-link">Cumpleaños</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#auth" aria-expanded="false" aria-controls="auth">
                        <i class="lni lni-calendar"></i>
                        <span>Eventos</span>
                    </a>
                    <ul id="auth" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="calendario.php" class="sidebar-link">Empresa</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="lni lni-agenda"></i>
                        <span>Capacitaciones</span>
                    </a>
                </li>

                <?php if ($_SESSION['rol'] == 5): ?>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#encuestas" aria-expanded="false" aria-controls="encuestas">
                        <i class="lni lni-pencil"></i>
                        <span>Encuestas</span>
                    </a>
                    <ul id="encuestas" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        
                    <li class="sidebar-item">
                            <a href="encuestas_prueba.php" class="sidebar-link">Crear encuesta</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="ver_enc_prueba.php" class="sidebar-link">Encuestas</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="respuestas.php" class="sidebar-link">Respuestas de encuestas</a>
                        </li>
                    </ul>
                </li>
                <?php else: ?>
                    <li class="sidebar-item">
                    <a href="ver_enc_prueba.php" class="sidebar-link">
                    <i class="lni lni-pencil"></i>
                    <span>Encuestas</span>
                    </a>
                </li>
                <?php endif; ?>
            
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="lni lni-files"></i>
                        <span>Documentos</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                    <i class="lni lni-comments"></i>
                    <span>Foro</span>
                    </a>
                </li>

                <?php if ($_SESSION['rol'] == 4 || $_SESSION['rol'] == 5): ?>
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
                        <i class="lni lni-protection"></i>
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

            </ul>
            <div class="sidebar-footer">
                <a href="#" class="sidebar-link">
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
                <div class="navbar"><a href="#"><i class="fa-solid fa-magnifying-glass"></i></a></div>
                <div class="user-info">
                    <span><?php echo $usuario; ?></span>
                    <div class="Salir"><a href="cerrar_sesion.php"><i class="fas fa-sign-out-alt"></i> Salir </a></div>
                </div>
                </div>
        </div>


        <header class="solicitud-header">
        <h1>Portal de eventos</h1>
    </header>

<button id="scrollToTop" class="scroll-to-top1">↑ </button>

<div class="solicitud-container-wrapper">

<div class="solicitud-container">
    <h2>Asistencias de Eventos</h2>  
    <h6 style="text-align: center;">Este es el apartado de Eventos, aqui puedes ver todo lo que tenga que ver con el evento, dede su nombre, fecha, hora, ubicacion y y imagenes del evento! </h6>
        

   
        <div class="nom-evento">
            <h5 style="text-align: center;">Nombre del Evento:</h5>        
            <h2 style="margin-bottom: 20px;"> <?php echo $evento['titulo']; ?></h2>
        </div>

        <div class="evento-container" style="display: flex; justify-content: space-between;">
    <!-- Columna de los datos del evento -->
    <div class="datos-evento" style="flex: 1; padding-right: 20px;">
        <p style="margin-bottom: 0px; margin-top: 20px;">Fecha:</p>
        <h4><strong></strong> <?php echo traducir_fecha($evento['fecha']); ?></h4>
        <p style="margin-bottom: 0px;">Hora:</p>
        <h4><strong></strong> <?php echo date('h:i A', strtotime($evento['hora'])); ?></h4>
        <p style="margin-bottom: 0px;">Ubicación:</p>
        <h4><strong></strong> <?php echo $evento['ubicacion']; ?></h4>
         
    </div>
    

    <!-- Columna de la lista de asistentes -->
<div class="lista-asistentes" style="flex: 1;">
    <div class="titulo-asist" style="margin-bottom: 20px;display: flex; justify-content: space-evenly; text-align: center; align-items:center; justify-content; center;">
    <h3 style="text-align: center; align-items: center; display: flex; justify-content: center; margin-bottom: 0px;">Lista de Asistentes</h3>
    <!-- Mostrar botón 'Desasistir' si el usuario está registrado en el evento -->
    <?php if ($esta_asistiendo): ?>
                <form action="evento_desasistir.php" method="POST">
                    <input type="hidden" name="evento_id" value="<?php echo $evento_id; ?>">
                    <input type="hidden" name="usuario_rut" value="<?php echo $usuario_rut; ?>">
                    <button type="submit" class="btn btn-outline-danger" style=" padding: 10px 10px;  cursor: pointer;">
                        Desasistir
                    </button>
                </form>
            
        <?php endif; ?>
        </div>
        
    <?php if ($result_asistentes->num_rows > 0): ?>

        <div class="listar-asist" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 15px;">
    <?php 
    $max_mostrar = 4; // Número máximo de asistentes a mostrar inicialmente
    $indice = 0;
    $rut_usuario_logueado = $_SESSION['rut']; // Obtener el RUT del usuario logueado
    
    // Arrays para separar al usuario logueado y los demás asistentes
    $usuario_logueado_asistente = null;
    $otros_asistentes = [];

    // Recorrer a los asistentes para separarlos
    while ($asistente = $result_asistentes->fetch_assoc()) {
        if ($asistente['rut_usuario'] === $rut_usuario_logueado) {
            // Si el asistente es el usuario logueado, lo almacenamos
            $usuario_logueado_asistente = $asistente;
        } else {
            // Los demás asistentes
            $otros_asistentes[] = $asistente;
        }
    }

    // Mostrar primero al usuario logueado si está asistiendo
    if ($usuario_logueado_asistente) {
        $carpeta_fotos = 'Images/fotos_personal/';
        $imagen_default = 'Images/profile_photo/imagen_default.jpg';
        $nombre_imagen = $usuario_logueado_asistente['imagen']; 
        $ruta_imagen_usuario = file_exists($carpeta_fotos . $nombre_imagen) ? $carpeta_fotos . $nombre_imagen : $imagen_default;

        // Mostrar al usuario logueado
        echo "<div class='asistente-item' style='text-align: center; width:100px; height:100px; background-color: #A6D9F1; height: 140px; padding-top: 5px; border-radius: 10px;'>";
        echo "<div class='img-asist' style='display: inline-block; margin: 0 10px;'>";
        echo "<img src='{$ruta_imagen_usuario}' alt='Foto de {$usuario_logueado_asistente['nombre']}' style='width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #1CA5EA;'>";
        echo "</div>";
        echo "<div class='nombre-asist' style='margin-top: 5px; font-size: 16px;'>";

        $nombre_completo = $usuario_logueado_asistente['nombre'];
        $partes = explode(' ', $nombre_completo);
        if (count($partes) >= 2) {
            $apellido_paterno = $partes[0];
            $excepciones = ['de', 'la', 'del', 'las', 'los']; 
            $primer_nombre = '';
            for ($i = 2; $i < count($partes); $i++) {
                if (!in_array(strtolower($partes[$i]), $excepciones)) {
                    $primer_nombre = $partes[$i];
                    break;
                }
            }
            echo $primer_nombre . ' ' . $apellido_paterno;
        } else {
            echo $nombre_completo;
        }

        echo "</div></div>";
        $indice++;
    }

    // Mostrar a los demás asistentes
    foreach ($otros_asistentes as $asistente) {
        $carpeta_fotos = 'Images/fotos_personal/';
        $imagen_default = 'Images/profile_photo/imagen_default.jpg';
        $nombre_imagen = $asistente['imagen']; 
        $ruta_imagen_usuario = file_exists($carpeta_fotos . $nombre_imagen) ? $carpeta_fotos . $nombre_imagen : $imagen_default;

        // Mostrar solo los primeros $max_mostrar asistentes
        $display_style = ($indice < $max_mostrar) ? "block" : "none";

        echo "<div class='asistente-item' id='asistente-{$indice}' style='text-align: center; width:100px; height:100px; display: {$display_style};'>";
        echo "<div class='img-asist' style='display: inline-block; margin: 0 10px;'>";
        echo "<img src='{$ruta_imagen_usuario}' alt='Foto de {$asistente['nombre']}' style='width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #1CA5EA;'>";
        echo "</div>";
        echo "<div class='nombre-asist' style='margin-top: 5px; font-size: 16px;'>";

        $nombre_completo = $asistente['nombre'];
        $partes = explode(' ', $nombre_completo);
        if (count($partes) >= 2) {
            $apellido_paterno = $partes[0];
            $excepciones = ['de', 'la', 'del', 'las', 'los']; 
            $primer_nombre = '';
            for ($i = 2; $i < count($partes); $i++) {
                if (!in_array(strtolower($partes[$i]), $excepciones)) {
                    $primer_nombre = $partes[$i];
                    break;
                }
            }
            echo $primer_nombre . ' ' . $apellido_paterno;
        } else {
            echo $nombre_completo;
        }

        echo "</div></div>";
        $indice++;
    }
    ?>
</div>




        <?php if ($result_asistentes->num_rows > $max_mostrar): ?>
        <!-- Botón de ver más y ocultar -->
        <div id="botones-control-asistentes" style="text-align: right; margin-top: 10px;">
            <span id="ver-mas-btn-asistentes" onclick="mostrarMasAsistentes()" style="cursor: pointer; color: #0044cc; text-decoration: underline;">Ver más...</span>
            <span id="ocultar-btn-asistentes" onclick="ocultarAsistentes()" style="display: none; cursor: pointer; color: #0044cc; text-decoration: underline;">Ocultar</span>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <p style="text-align: center;">No hay asistentes registrados aún para este evento.</p>
    <?php endif; ?>
</div>
</div>
    
<?php if ($rol == 5) {?>
    <div class="container-subir-img" style="margin-top: 60px;">
        <?php 
    // Verificar si se pasó el evento_id en la URL
    if (isset($_GET['evento_id'])) {
        $evento_id = intval($_GET['evento_id']);
    } else {
        die('ID del evento no especificado.');
    }
    ?>

    <h4>Agregar Imágenes del Evento</h4>
    <form id="subirImagenesForm" method="POST" action="evento_subir_img.php" enctype="multipart/form-data">
        <div class="form-group">
            <input type="file" name="imagenes_evento[]" class="form-control" multiple required id="imagenesInput">
        </div>
        <input type="hidden" name="evento_id" value="<?php echo $evento_id; ?>">
        <button type="button" onclick="mostrarAlertaExito()" class="btn btn-primary mt-2">Subir Imágenes</button>
    </form>

    <!-- Contenedor para la previsualización de imágenes -->
    <div id="preview" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px;"></div>

    <?php }?>

    <!-- Contenedor para las imágenes en miniatura -->
<!-- Contenedor para las imágenes en miniatura -->
<h2 style=" margin-top: 40px;">Galeria de eventos</h2>  
<h6 style="text-align: center;">Haz click en las imagenes para entrar en el la galeria de eventos, donde prodras ver todas las fotos que se han hecho en este evento pasado! </h6>

<!-- Slideshow container -->
<div class="evento-slider-container" style="margin-top: 30px;">

  <?php
    // Obtener las imágenes del evento desde la base de datos
    $evento_id = intval($_GET['evento_id']);
    $query = "SELECT id, ruta_imagen FROM imagenes_eventos WHERE evento_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $evento_id);
    $stmt->execute();
    $result = $stmt->get_result();
    // Resetear el contador y mostrar las imágenes en el modal
    $imgCounter = 1;
    $stmt->execute(); // Reejecutar la consulta para obtener las imágenes de nuevo
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ruta_imagen = $row['ruta_imagen'];
            echo '
            <div class="evento-slide evento-fade-effect">
              <div class="evento-slide-number">' . $imgCounter . ' / ' . $result->num_rows . '</div>
              <img src="' . $ruta_imagen . '" style="width:100%; display: flex; justify-content: center;">
            </div>';
            $imgCounter++;
        }
    }
  ?>

  <!-- Next and previous buttons -->
  
</div>
<br>

<!-- The dots/circles -->
<div style="text-align:center">
  <?php
  // Crear los puntos para cada imagen
  for ($i = 1; $i <= $result->num_rows; $i++) {
      echo '<span class="evento-dot-control" onclick="goToSlide(' . $i . ')"></span>';
  }
  ?>
</div>

<div class="event-row" style="margin-top: 20px;">
    <?php
    // Obtener las imágenes del evento desde la base de datos
    $evento_id = intval($_GET['evento_id']);
    $query = "SELECT id, ruta_imagen FROM imagenes_eventos WHERE evento_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $evento_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $counter = 1; // Contador para cada imagen
    $total_imagenes = $result->num_rows; // Número total de imágenes
    $max_mostrar = 4; // Número máximo de imágenes a mostrar inicialmente

    if ($total_imagenes > 0) {
        echo "<div id='imagenes-contenedor'>";
        $indice = 0;
        while ($row = $result->fetch_assoc()) {
            $ruta_imagen = $row['ruta_imagen'];
            $imagen_id = $row['id']; // Asegúrate de que tienes el ID de la imagen para borrarla
            
            // Mostrar solo las primeras $max_mostrar imágenes inicialmente
            $display_style = ($indice < $max_mostrar) ? "block" : "none";
            
            echo "
                <div class='event-column' style='position: relative; display: {$display_style};' id='imagen-{$indice}'>
                    <img src='{$ruta_imagen}' onclick='openEventoModal();currentEventoSlide({$counter})' class='evento-hover-shadow' style='width:100%'>
                    
                    <!-- Botón de eliminar -->";
            
            // Solo mostrar el botón si el rol es 5
            if ($rol == 5) {
                echo "
                    <form id='formEliminarImagen{$imagen_id}' action='evento_eliminar_img.php' method='POST' style='position: absolute; top: 5px; right: 5px;'>
                        <input type='hidden' name='imagen_id' value='{$imagen_id}'>
                        <input type='hidden' name='ruta_imagen' value='{$ruta_imagen}'>
                        <input type='hidden' name='evento_id' value='{$evento_id}'>
                        <button type='button' onclick='confirmarEliminacionImagen(\"{$imagen_id}\")' style='font-size: 24px; background-color: rgba(255, 255, 255, 0); color: red; border: none; width: 30px; height: 30px; cursor: pointer;'>&times;</button>
                    </form>";
            }
            
            echo "
                </div>"; // Cerrar la div de cada imagen
            
            $indice++;
            $counter++;
        }
        echo "</div>"; // Cerrar el contenedor de imágenes
    
    
        
        // Mostrar los botones 'Ver más' y 'Ocultar'
        if ($total_imagenes > $max_mostrar) {
            echo "
                <div id='botones-control' style='text-align: right; margin-top: 10px; display:flex; justify-content: end;'>
                    <span id='ver-mas-btn' onclick='mostrarMasImagenes()' style='cursor: pointer; color: #0044cc; text-decoration: underline;'>Ver más...</span>
                    <span id='ocultar-btn' onclick='ocultarImagenes()' style='display: none; cursor: pointer; color: #0044cc; text-decoration: underline;'>Ocultar</span>
                </div>";
        }
    } else {
        echo '<p>No hay imágenes para este evento.</p>';
    }
    ?>
</div>


<!-- Modal para mostrar las imágenes -->
<div id="eventoModal" class="evento-modal">
  <span class="evento-close cursor" onclick="closeEventoModal()">&times;</span>
  <div class="evento-modal-content">

    <?php
    // Resetear el contador y mostrar las imágenes en el modal
    $counter = 1;
    $stmt->execute(); // Reejecutar la consulta para obtener las imágenes de nuevo
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ruta_imagen = $row['ruta_imagen'];
            echo '
            <div class="evento-mySlides">
              <div class="evento-numbertext">' . $counter . ' / ' . $result->num_rows . '</div>
              <img src="' . $ruta_imagen . '" style="width:100%; display: flex; justify-content: center;">
            </div>';
            $counter++;
        }
    }
    ?>

    <!-- Botones para siguiente/anterior -->
    <a class="evento-prev" onclick="plusEventoSlides(-1)">&#10094;</a>
    <a class="evento-next" onclick="plusEventoSlides(1)">&#10095;</a>

    <!-- Texto del caption -->
    <div class="evento-caption-container">
      <p id="eventoCaption"></p>
    </div>

    <!-- Miniaturas debajo del modal -->
    <div class="event-row" style="margin-top:0px;">
        <?php
        // Mostrar miniaturas dentro del modal
        $counter = 1;
        $stmt->execute(); // Reejecutar la consulta
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $ruta_imagen = $row['ruta_imagen'];
                echo '
                <div class="event-column">
                    <img class="evento-demo" src="' . $ruta_imagen . '" onclick="currentEventoSlide(' . $counter . ')" alt="Imagen ' . $counter . '" style="width:100%">
                </div>';
                $counter++;
            }
        }
        ?>
    </div>
  </div>
</div>

        <div class="boton-soli" style="margin-top: 60px; width:auto;">
            <a href="calendario.php" class="solicitud-submit-btn" style="border-radius: 10px; padding: 10px;">Volver</a>
        </div>
        
    </div>
    </div>
    </div>
</div>

<script>
function mostrarMasAsistentes() {
    // Mostrar todos los asistentes
    var totalAsistentes = <?php echo $result_asistentes->num_rows; ?>;
    for (var i = 0; i < totalAsistentes; i++) {
        var asistente = document.getElementById('asistente-' + i);
        if (asistente) {
            asistente.style.display = 'block';
        }
    }
    // Ocultar el texto 'Ver más' y mostrar el texto 'Ocultar'
    document.getElementById('ver-mas-btn-asistentes').style.display = 'none';
    document.getElementById('ocultar-btn-asistentes').style.display = 'inline';
}

function ocultarAsistentes() {
    // Ocultar los asistentes adicionales
    var maxMostrar = <?php echo $max_mostrar; ?>;
    var totalAsistentes = <?php echo $result_asistentes->num_rows; ?>;
    for (var i = maxMostrar; i < totalAsistentes; i++) {
        var asistente = document.getElementById('asistente-' + i);
        if (asistente) {
            asistente.style.display = 'none';
        }
    }
    // Mostrar el texto 'Ver más' y ocultar el texto 'Ocultar'
    document.getElementById('ver-mas-btn-asistentes').style.display = 'inline';
    document.getElementById('ocultar-btn-asistentes').style.display = 'none';
}
</script>

<script>
function mostrarMasImagenes() {
    // Mostrar todas las imágenes restantes
    var totalImagenes = <?php echo $total_imagenes; ?>;
    for (var i = 0; i < totalImagenes; i++) {
        var imagen = document.getElementById('imagen-' + i);
        if (imagen) {
            imagen.style.display = 'block';
        }
    }
    // Ocultar el botón 'Ver más' y mostrar el botón 'Ocultar'
    document.getElementById('ver-mas-btn').style.display = 'none';
    document.getElementById('ocultar-btn').style.display = 'inline-block';
}

function ocultarImagenes() {
    // Ocultar las imágenes adicionales
    var maxMostrar = <?php echo $max_mostrar; ?>;
    var totalImagenes = <?php echo $total_imagenes; ?>;
    for (var i = maxMostrar; i < totalImagenes; i++) {
        var imagen = document.getElementById('imagen-' + i);
        if (imagen) {
            imagen.style.display = 'none';
        }
    }
    // Mostrar el botón 'Ver más' y ocultar el botón 'Ocultar'
    document.getElementById('ver-mas-btn').style.display = 'inline-block';
    document.getElementById('ocultar-btn').style.display = 'none';
}
</script>

<script>
    let slideIndex = 0;
    let autoScroll;  // Variable para almacenar el timeout del autoscroll

    // Mostrar las diapositivas automáticamente y permitir control manual
    function autoShowSlides() {
        let i;
        let slides = document.getElementsByClassName("evento-slide");
        let dots = document.getElementsByClassName("evento-dot-control");

        // Ocultar todas las diapositivas
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }

        // Incrementar el índice del slide
        slideIndex++;
        if (slideIndex > slides.length) {
            slideIndex = 1; // Si sobrepasa el número de diapositivas, vuelve al primero
        }

        // Mostrar la diapositiva actual
        slides[slideIndex - 1].style.display = "block";

        // Eliminar la clase 'active' de todos los puntos
        for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active-dot", "");
        }

        // Añadir la clase 'active' al punto correspondiente
        dots[slideIndex - 1].className += " active-dot";

        // Cambiar la diapositiva cada 4 segundos
        autoScroll = setTimeout(autoShowSlides, 4000); // Cambia la imagen cada 4 segundos
    }

    // Controles para siguiente/anterior
    function plusSlides(n) {
        clearTimeout(autoScroll);  // Detener el autoscroll temporalmente
        showSlides(slideIndex += n);
    }

    // Controles para ir a un slide específico
    function currentSlide(n) {
        clearTimeout(autoScroll);  // Detener el autoscroll temporalmente
        showSlides(slideIndex = n);
    }

    // Mostrar diapositivas manualmente
    function showSlides(n) {
        let i;
        let slides = document.getElementsByClassName("evento-slide");
        let dots = document.getElementsByClassName("evento-dot-control");

        // Reiniciar el índice si es necesario
        if (n > slides.length) {slideIndex = 1}
        if (n < 1) {slideIndex = slides.length}

        // Ocultar todas las diapositivas
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }

        // Eliminar la clase 'active' de todos los puntos
        for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active-dot", "");
        }

        // Mostrar la diapositiva actual y activar el punto correspondiente
        slides[slideIndex - 1].style.display = "block";
        dots[slideIndex - 1].className += " active-dot";

        // Reiniciar el autoscroll después de la acción manual
        autoScroll = setTimeout(autoShowSlides, 4000);  // Reiniciar el autoscroll
    }

    // Iniciar el autoscroll cuando la página carga
    window.onload = function() {
        autoShowSlides();  // Iniciar el slideshow automáticamente
    };
</script>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function mostrarAlertaExito() {
    var eventoId = document.querySelector('input[name="evento_id"]').value;

    Swal.fire({
        title: '¡Imágenes subidas!',
        text: 'Las imágenes han sido subidas con éxito.',
        icon: 'success',
        confirmButtonText: 'OK'
    }).then((result) => {
        if (result.isConfirmed) {
            // Enviar el formulario
            document.getElementById('subirImagenesForm').submit();
        }
    });
}
</script>



<script>
function confirmarEliminacionImagen(imagenId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta imagen se eliminará permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Enviar el formulario correspondiente para eliminar la imagen
            document.getElementById('formEliminarImagen' + imagenId).submit();
        }
    });
}
</script>


<script>
    document.getElementById('imagenesInput').addEventListener('change', function(event) {
        var preview = document.getElementById('preview');
        preview.innerHTML = ''; // Limpiar cualquier vista previa anterior

        var files = event.target.files; // Obtener los archivos seleccionados

        // Iterar sobre los archivos seleccionados
        for (var i = 0; i < files.length; i++) {
            var file = files[i];

            // Verificar si el archivo es una imagen
            if (file.type.startsWith('image/')) {
                var reader = new FileReader();

                // Crear un div para cada imagen y añadirla al contenedor de previsualización
                reader.onload = function(e) {
                    var imageContainer = document.createElement('div');
                    imageContainer.style.position = 'relative';
                    imageContainer.style.display = 'inline-block';

                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100px';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    img.style.border = '2px solid #1CA5EA';
                    img.style.borderRadius = '10px';
                    img.style.margin = '5px';

                    // Añadir la imagen al contenedor
                    imageContainer.appendChild(img);
                    preview.appendChild(imageContainer);
                };

                // Leer el archivo de imagen
                reader.readAsDataURL(file);
            } else {
                // Mostrar mensaje si no es una imagen
                alert('Solo se pueden previsualizar archivos de imagen.');
            }
        }
    });
</script>

<!-- Script para manejar el modal y las imágenes -->
<script>
// Abrir el modal
function openEventoModal() {
  document.getElementById("eventoModal").style.display = "block";
}

// Cerrar el modal
function closeEventoModal() {
  document.getElementById("eventoModal").style.display = "none";
}

var eventoSlideIndex = 1;
showEventoSlides(eventoSlideIndex);

// Controles siguiente/anterior
function plusEventoSlides(n) {
  showEventoSlides(eventoSlideIndex += n);
}

// Control de miniaturas
function currentEventoSlide(n) {
  showEventoSlides(eventoSlideIndex = n);
}

function showEventoSlides(n) {
  var i;
  var slides = document.getElementsByClassName("evento-mySlides");
  var dots = document.getElementsByClassName("evento-demo");
  var captionText = document.getElementById("eventoCaption");
  if (n > slides.length) {eventoSlideIndex = 1}
  if (n < 1) {eventoSlideIndex = slides.length}
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[eventoSlideIndex-1].style.display = "block";
  dots[eventoSlideIndex-1].className += " active";
  captionText.innerHTML = dots[eventoSlideIndex-1].alt;
}
</script>

    <script src="scripts/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.getElementById('scrollToTop').addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
</script>

 <!-- jQuery y Bootstrap JS -->
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Linking SwiperJS script -->
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- Linking custom script -->
<script src="scripts/script_cards.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
      crossorigin="anonymous"></script>
      
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
</html>


<?php
// Cerrar las conexiones
$stmt_evento->close();
$stmt_asistentes->close();
$conn->close();
?>
