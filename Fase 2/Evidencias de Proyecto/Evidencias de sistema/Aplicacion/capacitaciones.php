<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); // Redirigir al login si no ha iniciado sesión
    exit();
}

$error = "";

// Conectar a la base de datos
include('conexion.php');

// Inicializamos variables para mensajes
$mensaje = '';
$tipo_mensaje = '';

// Obtener el usuario que ha iniciado sesión
$usuario = $_SESSION['usuario'];


// Consultar los datos del empleado en la tabla 'personal'
$sql = "SELECT rut, nombre, correo, imagen, cargo_id, fecha_nacimiento, rol_id
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


// Obtener el mes y el año desde el GET, o establecer el mes actual si no se proporciona
$mes = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$ano = isset($_GET['ano']) ? $_GET['ano'] : date('Y');

// Procesar adición de una capacitación
if (isset($_POST['agregar_capacitacion'])) {
    $titulo = $_POST['titulo'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $ubicacion = $_POST['ubicacion'];

    $sql = "INSERT INTO capacitaciones (titulo, fecha, hora, ubicacion) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("ssss", $titulo, $fecha, $hora, $ubicacion);

    if ($stmt->execute()) {
        $mensaje = "Capacitación agregada exitosamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al agregar la capacitación: " . $conn->error;
        $tipo_mensaje = "danger";
        echo $mensaje; // Mostrar el error en la salida
    }

    $stmt->close();
}

// Procesar eliminación de una capacitación
if (isset($_GET['eliminar_capacitacion'])) {
    $id = intval($_GET['eliminar_capacitacion']);
    $stmt = $conn->prepare("DELETE FROM capacitaciones WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $mensaje = "Capacitación eliminada exitosamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al eliminar la capacitación: " . $conn->error;
        $tipo_mensaje = "danger";
    }
    $stmt->close();
}

// Procesar actualización de una capacitación
if (isset($_POST['actualizar_capacitacion'])) {
    $id = intval($_POST['id']);
    $titulo = $_POST['titulo'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $ubicacion = $_POST['ubicacion'];

    $stmt = $conn->prepare("UPDATE capacitaciones SET titulo = ?, fecha = ?, hora = ?, ubicacion = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $titulo, $fecha, $hora, $ubicacion, $id);

    if ($stmt->execute()) {
        $mensaje = "Capacitación actualizada exitosamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al actualizar la capacitación: " . $conn->error;
        $tipo_mensaje = "danger";
    }
    $stmt->close();
}

// Procesar registro de asistencia a una capacitación
if (isset($_POST['registrar_asistencia'])) {
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
    
    return " $dia_numero de $mes_nombre de $anio";
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

function traducir_mes_mes($mes) {
    $meses = array(
        "January" => "Enero", "February" => "Febrero", "March" => "Marzo", 
        "April" => "Abril", "May" => "Mayo", "June" => "Junio", 
        "July" => "Julio", "August" => "Agosto", "September" => "Septiembre", 
        "October" => "Octubre", "November" => "Noviembre", "December" => "Diciembre"
    );
    return isset($meses[$mes]) ? $meses[$mes] : $mes;
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="styles/style_cums.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/style_calendar.css">   
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <!-- Bootstrap CSS (solo una referencia a la última versión) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    
    <!-- Lineicons -->
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    <style>
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
            margin-top: 5px; /* Espacio entre la fecha y el título */
            color: #00304A;
        }

        .event-card p {
            margin: 5px 0 0;
            color: #333; /* Color de texto en detalles */
        }

        .event-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 10px;
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

        .event-list-box{
        justify-content: center; align-items: center; width:450px;
        }
        @media (max-width: 768px) {
        .event-list-box{
            width: 200px;
        }}

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
        <!-- Aqui termina el menu -->

<div class="titulo-home">
    <h2 style="width: 100%; text-align: center; margin-top: 20px;">Calendario de Capacitaciones</h2>
    <p>Consulta todos los eventos programados en la clínica, busca por mes y navega por los días para ver los eventos programados.</p>
</div>

<div class="main-container-cump">
    <div class="wrapper-cum">
        <!-- Contenedor del calendario -->
        <div class="calendar-box">
            <header>
                <p class="current-date"></p>
                <div class="icons">
                    <span id="prev" class="material-symbols-rounded"><</span>
                    <span id="next" class="material-symbols-rounded">></span>
                </div>
            </header>
            <div class="calendar-cum">
                <ul class="weeks">
                    <li>Dom</li>
                    <li>Lun</li>
                    <li>Mar</li>
                    <li>Mié</li>
                    <li>Jue</li>
                    <li>Vie</li>
                    <li>Sáb</li>
                </ul>
                <ul class="days"></ul> <!-- Aquí se rellenarán los días del mes -->
            </div>
            <?php if ($rol == 5): ?>
                <!-- Botón para abrir el modal de agregar evento -->
                <button type="button" class="btn btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#addEventModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-plus-circle mr-1" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                    </svg>
                    Agregar Capacitacion
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Contenedor de los eventos del día seleccionado -->
    <div class="event-list-box" >
        <h4>Capacitaciones del Mes</h4>
        <div id="event-list"></div> <!-- Aquí se cargarán los eventos dinámicamente -->
        
    </div>
    
</div>



<!-- Modal para agregar evento -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered"> <!-- modal-md para tamaño mediano -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEventModalLabel">Agregar Capacitacion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <!-- Título del Evento -->
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Título del Evento" required>
                        <label for="titulo">Título del Evento</label>
                    </div>

                    <!-- Fecha del Evento usando Flatpickr -->
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control flatpickr-date" id="fecha" name="fecha" placeholder="Fecha" required>
                        <label for="fecha">Fecha</label>
                    </div>

                    <!-- Hora del Evento usando Flatpickr -->
                    <div class="form-floating mb-3">
                        <input type="time" class="form-control" id="hora" name="hora" placeholder="Hora" required>
                        <label for="hora">Hora</label>
                    </div>

                    <!-- Ubicación del Evento -->
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="ubicacion" name="ubicacion" placeholder="Ubicación" required>
                        <label for="ubicacion">Ubicación</label>
                    </div>

                    <!-- Botón Guardar Evento -->
                    <div class="d-grid">
                        <button type="submit" name="agregar_capacitacion" class="btn btn-primary">Guardar Evento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Incluir Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inicializar Flatpickr para el campo de fecha
        flatpickr("#fecha", {
            dateFormat: "Y-m-d", // Formato compatible con MySQL
            minDate: "today", // No permitir fechas pasadas
            locale: "es" // Cambiar al español (asegúrate de incluir la configuración de idioma si es necesario)
        });

        // Inicializar Flatpickr para el campo de hora
        flatpickr("#hora", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i", // Formato compatible con MySQL para la hora
            time_24hr: true // Formato de 24 horas
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Mostrar alertas de acuerdo a la acción realizada -->
<script>
<?php if (isset($tipo_mensaje)) : ?>
    <?php if ($tipo_mensaje == 'success') : ?>
        Swal.fire({
            title: '¡Éxito!',
            text: '<?php echo $mensaje; ?>',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    <?php elseif ($tipo_mensaje == 'danger') : ?>
        Swal.fire({
            title: 'Error',
            text: '<?php echo $mensaje; ?>',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    <?php elseif ($tipo_mensaje == 'warning') : ?>
        Swal.fire({
            title: 'Advertencia',
            text: '<?php echo $mensaje; ?>',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
    <?php endif; ?>
<?php endif; ?>
</script>

<!-- Función para confirmar la eliminación del evento -->
<script>
function confirmarEliminacion(idEvento) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡Esta acción no se puede deshacer!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirigir a la página para eliminar el evento
            window.location.href = '?eliminar_evento=' + idEvento;
        }
    });
}
</script>

<script src="scripts/script.js"></script>
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



<!-- Bootstrap Bundle with Popper (necesario para los modales de Bootstrap) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
        <!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- Flatpickr JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script src="scripts/script_capacitaciones.js"></script>

</body>
</html>