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
    $_SESSION['imagen'] = $user_data['imagen'];
    $_SESSION['cargo_id'] = $user_data['cargo_id'];
    $rol = $user_data['rol_id'];
    $admin = $user_data['admin'];
    // Guardar el rol en la sesión
    $_SESSION['rol'] = $rol;
    $_SESSION['admin'] = $admin;
} else {
    $error = "No se encontraron datos para el usuario.";
}

$sql_cargo = "SELECT NOMBRE_CARGO FROM cargos WHERE id = '" . $user_data['cargo_id'] . "'";
$result_cargo = $conn->query($sql_cargo);

if ($result_cargo->num_rows > 0) {
    $cargo_data = $result_cargo->fetch_assoc();
} else {
    $error = "No se encontraron datos para el cargo.";
}

// Verificar si se ha proporcionado un capacitacion_id
if (isset($_GET['capacitacion_id'])) {
    $capacitacion_id = intval($_GET['capacitacion_id']);
    
    // Obtener los detalles de la capacitación
    $capacitacion_sql = "SELECT titulo, fecha, hora, ubicacion FROM capacitaciones WHERE id = ?";
    $stmt_capacitacion = $conn->prepare($capacitacion_sql);
    $stmt_capacitacion->bind_param("i", $capacitacion_id);
    $stmt_capacitacion->execute();
    $result_capacitacion = $stmt_capacitacion->get_result();

    if ($result_capacitacion->num_rows > 0) {
        $evento = $result_capacitacion->fetch_assoc();
    } else {
        echo "Capacitación no encontrada.";
        exit();
    }

    // Obtener los asistentes a la capacitación
    $asistentes_sql = "SELECT p.nombre, p.rut, p.imagen, a.rut_usuario FROM asistencia_capacitaciones a
                       JOIN personal p ON a.rut_usuario = p.rut
                       WHERE a.capacitacion_id = ?";
    $stmt_asistentes = $conn->prepare($asistentes_sql);
    $stmt_asistentes->bind_param("i", $capacitacion_id);
    $stmt_asistentes->execute();
    $result_asistentes = $stmt_asistentes->get_result();
} else {
    echo "No se ha especificado una capacitación.";
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
$carpeta_fotos = 'Images/fotos_personal/';
$imagen_default = 'Images/profile_photo/imagen_default.jpg';

$nombre_imagen = $user_data['imagen'];
$ruta_imagen_usuario = $carpeta_fotos . $nombre_imagen;

if (file_exists($ruta_imagen_usuario)) {
    $imagen_final = $ruta_imagen_usuario;
} else {
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

// Supongamos que tienes el ID de la capacitación y el RUT del usuario logueado
$capacitacion_id = intval($_GET['capacitacion_id']);
$usuario_rut = $_SESSION['rut'];

// Verificar si el usuario está registrado en la capacitación
$fecha_actual = date("Y-m-d");

$query_asistencia = "SELECT asistencia_capacitaciones.*, capacitaciones.fecha 
              FROM asistencia_capacitaciones 
              INNER JOIN capacitaciones ON asistencia_capacitaciones.capacitacion_id = capacitaciones.id 
              WHERE asistencia_capacitaciones.capacitacion_id = ? 
              AND asistencia_capacitaciones.rut_usuario = ? 
              AND capacitaciones.fecha >= ?";

$stmt_asistencia = $conn->prepare($query_asistencia);
$stmt_asistencia->bind_param("iss", $capacitacion_id, $usuario_rut, $fecha_actual);
$stmt_asistencia->execute();
$result_asistencia = $stmt_asistencia->get_result();

$esta_asistiendo = ($result_asistencia->num_rows > 0);
?>



<!DOCTYPE php>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capacitaciones</title>
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


        

        <div class="topnav">
        <a href="home.php" class="mr-active">Intranet</a>
        <div id="mobileLinks">
            <!-- Agregar elementos del menú existente -->
            <a href="perfil.php"><i class="lni lni-user"style="margin-right: 10px;"></i>Perfil</a>
            <?php if ($_SESSION['admin'] == 1): ?>
                <a href="agregar_personal.php"><i class="lni lni-users"style="margin-right: 10px;"></i>Agregar Personal</a>
                <a href="empleado_mes.php"><i class="lni lni-users"style="margin-right: 10px;"></i>Agregar Empleado del Mes</a>
            <?php endif; ?>
            <a href="personal_nuevo.php"><i class="lni lni-users"style="margin-right: 10px;"></i>Personal</a>
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



        <header class="solicitud-header">
        <h1>Portal de Capacitaciones</h1>
    </header>

<button id="scrollToTop" class="scroll-to-top1">↑ </button>

<div class="solicitud-container-wrapper">

<div class="solicitud-container">
    <h2>Asistencias de Capacitaciones</h2>  
    <h6 style="text-align: center;">Este es el apartado de Capacitaciones, aqui puedes ver todo lo que tenga que ver con la Capacitaciones, desde su nombre, fecha, hora, ubicacion y y imagenes del evento! </h6>
        

   
        <div class="nom-evento" style="margin-top: 30px;">
            <h5 style="text-align: center;">Nombre de la Capacitaciones:</h5>        
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
                <form action="capacitacion_desasistir.php" method="POST">
                    <input type="hidden" name="evento_id" value="<?php echo $capacitacion_id; ?>">
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
    
<?php if ($_SESSION['admin'] == 1) {?>
    <div class="container-subir-img" style="margin-top: 60px;">
        <?php 
    // Verificar si se pasó el evento_id en la URL
    if (isset($_GET['capacitacion_id'])) {
        $evento_id = intval($_GET['capacitacion_id']);
    } else {
        die('ID del evento no especificado.');
    }
    ?>

    <h4>Agregar Imágenes del Evento</h4>
    <form id="subirImagenesForm" method="POST" action="capacitacion_subir_img.php" enctype="multipart/form-data">
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
    $evento_id = intval($_GET['capacitacion_id']);
    $query = "SELECT id, ruta_imagen FROM imagenes_capacitaciones WHERE capacitacion_id = ?";
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
    $evento_id = intval($_GET['capacitacion_id']);
    $query = "SELECT id, ruta_imagen FROM imagenes_capacitaciones WHERE capacitacion_id = ?";
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
            if ($_SESSION['admin'] == 1) {
                echo "
                    <form id='formEliminarImagen{$imagen_id}' action='capacitacion_elim_img.php' method='POST' style='position: absolute; top: 5px; right: 5px;'>
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
            <a href="capacitaciones.php" class="solicitud-submit-btn" style="border-radius: 10px; padding: 10px;">Volver</a>
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
$conn->close();
?>
