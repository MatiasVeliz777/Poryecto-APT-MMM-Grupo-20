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
$sql = "SELECT rut, nombre, correo, imagen, fecha_nacimiento, cargo_id, rol_id, admin
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

if (isset($_GET['rut'])) {

// RUT del usuario logueado
$usuario_rut = $_GET['rut'];

// Consulta SQL con parámetros
$sql_perfiles = "SELECT p.rut, p.nombre, p.correo, p.imagen, p.fecha_nacimiento, p.cargo_id, p.rol_id, c.NOMBRE_CARGO
                FROM personal p 
                INNER JOIN cargos c ON p.cargo_id = c.id
                WHERE p.rut = ?";

$stmt_perfiles = $conn->prepare($sql_perfiles);
$stmt_perfiles->bind_param("s", $usuario_rut);

if ($stmt_perfiles->execute()) {
    $result_perfiles = $stmt_perfiles->get_result();

    // Verificar si hay resultados
    if ($result_perfiles->num_rows > 0) {
        $fila = $result_perfiles->fetch_assoc(); // Obtener la primera fila

        // Verificar si el campo 'imagen' tiene un valor
        $nombre_imagen_det = isset($fila['imagen']) ? $fila['imagen'] : null;

        // Construir la ruta completa de la imagen
        $ruta_imagen_det = $carpeta_fotos . $nombre_imagen_det;

        // Verificar si la imagen existe en el servidor
        if ($nombre_imagen_det && file_exists($ruta_imagen_det)) {
            // Si existe, usar esta imagen
            $imagen_final_det = $ruta_imagen_det;
        } else {
            // Si no existe, usar imagen predeterminada
            $imagen_final_det = $imagen_default;
        }

        // Opcional: Puedes usar otras columnas del resultado aquí
        $nombre_usuario = $fila['nombre'];
        $correo_usuario = $fila['correo'];
        $cargo_usuario = $fila['NOMBRE_CARGO'];
    } else {
        echo "No se encontraron resultados para el RUT proporcionado.";
        $imagen_final_det = $imagen_default; // Usar imagen predeterminada
    }
} else {
    echo "Error al ejecutar la consulta: " . $stmt_perfiles->error;
}




// Consulta para obtener los últimos eventos a los que ha asistido el usuario
$sql_eventos = "SELECT e.id, e.titulo, e.fecha
                FROM asistencias_eventos a
                JOIN eventos e ON a.evento_id = e.id
                WHERE a.rut_usuario = ?
                ORDER BY e.fecha DESC
                LIMIT 3";

$stmt_eventos = $conn->prepare($sql_eventos);
$stmt_eventos->bind_param("s", $usuario_rut);
$stmt_eventos->execute();
$result_eventos = $stmt_eventos->get_result();

// Consulta para obtener los últimos eventos a los que ha asistido el usuario
$sql_capas = "SELECT c.id, c.titulo, c.fecha
                FROM asistencia_capacitaciones a
                JOIN capacitaciones c ON a.capacitacion_id = c.id
                WHERE a.rut_usuario = ?
                ORDER BY c.fecha DESC
                LIMIT 3";

$stmt_capas = $conn->prepare($sql_capas);
$stmt_capas->bind_param("s", $usuario_rut);
$stmt_capas->execute();
$result_capas = $stmt_capas->get_result();

}
$conn->close();
 
?>


<!DOCTYPE php>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfiles</title>
    <link rel="icon" href="Images/icono2.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/style_encuestas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .list-group-item {
            padding: 15px;
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        .list-group-item:hover {
            background-color: #A6D9F1;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .acciones-user h5 {
            font-weight: bold;
            color: #333;
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




<!-- Contenedor personalizado para el perfil -->
<div class="custom-container">
    <!-- Imagen del perfil -->
    <img id="fotoPerfil" src="<?php echo file_exists($imagen_final_det) ? $imagen_final_det : $imagen_default; ?>" 
    class="profile-picture" alt="Foto de Perfil">

    <!-- Información del perfil -->
    <div class="profile-info">
        <h3><?php echo $fila['nombre']; ?></h3>
        <p><strong>RUT :</strong> <?php echo $fila['rut']; ?></p>
        <p><strong>Fecha de Nacimiento:</strong> 
            <?php 
            // Usar la función para formatear la fecha
            echo traducir_fecha($fila['fecha_nacimiento']);
            ?>
        </p>
        <p><strong>Cargo:</strong> <?php echo $fila['NOMBRE_CARGO']; ?></p>

        <?php if ($_SESSION['admin'] == 1): ?>
        <!-- Enlace para abrir el modal -->
        <p>
            <a href="#" data-bs-toggle="modal" data-bs-target="#cambiarFotoModal" class="change-photo-link">Cambiar foto de perfil</a>
        </p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Bootstrap para cambiar la foto -->
<div class="modal fade" id="cambiarFotoModal" tabindex="-1" aria-labelledby="cambiarFotoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cambiarFotoModalLabel">Cambiar Foto de Perfil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Formulario para subir nueva foto -->
                <form id="formCambiarFoto" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="rut" value="<?php echo $fila['rut']; ?>">
                    <div class="mb-3">
                        <label for="nueva_foto" class="form-label">Selecciona una nueva foto:</label>
                        <input type="file" name="nueva_foto" id="nueva_foto" accept="image/*" class="form-control" required>
                    </div>
                    <!-- Contenedor para vista previa de la imagen -->
                    <div class="text-center mt-3">
                        <img id="previewImagen" src="<?php echo file_exists($imagen_final_det) ? $imagen_final_det : $imagen_default; ?>" 
                        alt="Vista previa" class="img-thumbnail" style="max-width: 200px;">
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Guardar cambios</button>
                </form>
                <div id="resultadoCambioFoto" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar vista previa de la imagen seleccionada
document.getElementById("nueva_foto").addEventListener("change", function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById("previewImagen").src = event.target.result; // Cambiar la imagen de vista previa
        };
        reader.readAsDataURL(file); // Leer la imagen seleccionada
    }
});

// Manejar el formulario para cambiar la foto
document.getElementById("formCambiarFoto").addEventListener("submit", function(e) {
    e.preventDefault(); // Evita el envío estándar del formulario

    const formData = new FormData(this); // Captura los datos del formulario

    fetch("cambiar_foto.php", {
        method: "POST",
        body: formData,
    })
    .then(response => response.text()) // Parsear la respuesta del servidor
    .then(data => {
        document.getElementById("resultadoCambioFoto").innerHTML = data; // Mostrar resultado en el modal

        // Actualizar la foto de perfil en la página si se subió correctamente
        if (data.includes("Foto actualizada correctamente")) {
            const nuevaFotoUrl = formData.get("nueva_foto").name;
            document.getElementById("fotoPerfil").src = "Images/fotos_personal/" + nuevaFotoUrl;

            // Cerrar el modal automáticamente tras unos segundos
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById("cambiarFotoModal"));
                modal.hide();

                // Recargar la página después de cerrar el modal
                location.reload();
            }, 2000);
        }

    })
    .catch(error => console.error("Error al actualizar la foto:", error));
});
</script>




<div class="container-blanco" style="background-color: white; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 8px; padding: 0px; width: 70%; margin: 20px auto;">

<div class="acciones" style="padding: 30px; width: 100%; align-items: center; display:flex; flex-direction: column; justify-content: center;">
<div class="acciones-user" style="margin-bottom: 40px; width: 90%;">
    <!-- Últimos eventos asistidos -->
    <h5 class="mb-3" STYLE="TEXT-ALIGN: CENTER; font-size: 1.6rem;">Últimos Eventos Asistidos</h5>
    <p STYLE="TEXT-ALIGN: CENTER;">Se muestran los últimos 3 eventos a los que has asistido. Para ver el historial  </p>
    <P STYLE="TEXT-ALIGN: CENTER;">completo de tus asistencias, dirígete a la <a href="calendario_prueba.php">página de eventos</a></P>
    <?php if ($result_eventos->num_rows > 0): ?>
        <div class="list-group">
            <?php while ($evento = $result_eventos->fetch_assoc()): ?>
                <a href="evento_asistencia.php?evento_id=<?php echo $evento['id']; ?>" class="list-group-item list-group-item-action" style="border: 1px solid #ddd; border-radius: 5px; margin-bottom: 10px; text-decoration: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Evento:</strong> <?php echo htmlspecialchars($evento['titulo']); ?>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <strong>Fecha:</strong> <?php echo traducir_fecha($evento['fecha']); ?>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No hay eventos recientes.</p>
    <?php endif; ?>
</div>

<div class="acciones-user" style="margin-bottom: 40px; width: 90%;">
    <!-- Últimos eventos asistidos -->
    <h5 class="mb-3" STYLE="TEXT-ALIGN: CENTER; font-size: 1.6rem;">Últimas Capacitaciones Asistidas</h5>
    <p STYLE="TEXT-ALIGN: CENTER;">Se muestran los últimos 3 eventos a los que has asistido. Para ver el historial  </p>
    <P STYLE="TEXT-ALIGN: CENTER;">completo de tus asistencias, dirígete a la <a href="calendario.php">página de eventos</a></P>
    <?php if ($result_capas->num_rows > 0): ?>
        <div class="list-group">
            <?php while ($capas = $result_capas->fetch_assoc()): ?>
                <a href="evento_asistencia.php?evento_id=<?php echo $capas['id']; ?>" class="list-group-item list-group-item-action" style="border: 1px solid #ddd; border-radius: 5px; margin-bottom: 10px; text-decoration: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Evento:</strong> <?php echo htmlspecialchars($capas['titulo']); ?>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <strong>Fecha:</strong> <?php echo traducir_fecha($capas['fecha']); ?>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No hay eventos recientes.</p>
    <?php endif; ?>
</div>

<?php if ($_SESSION['admin'] == 1): ?>
    <div class="acciones-user" style="margin-bottom: 20px; width: 90%;">
        <!-- Últimos eventos asistidos -->
        <h5 STYLE="TEXT-ALIGN: CENTER;font-size: 1.6rem;">Respuestas encuestas</h5>
        <p STYLE="TEXT-ALIGN: CENTER;">Se muestran las últimos 3 Respuestas que usted ha respondido. Para ver el historial  </p>
        <P STYLE="TEXT-ALIGN: CENTER; margin-bottom: 40px;">completo de tus Respuestas, dirígete a la <a href="ver_enc_prueba.php">página de Encuestas</a></P>
    <?php 
        include("conexion.php");
        // Mostrar las preguntas ya respondidas
        $query_respondidas = "
        SELECT p.*, r.id_respuesta, r.calificacion, r.respuesta, r.fecha_respuesta
        FROM preguntas_encuesta p
        JOIN respuestas_encuesta r
        ON p.id_pregunta = r.id_pregunta
        WHERE r.rut_usuario = ? 
        ORDER BY r.fecha_respuesta DESC
        LIMIT 3
        ";

        $stmt_respondidas = $conn->prepare($query_respondidas);
        $stmt_respondidas->bind_param("s", $usuario_rut);
        $stmt_respondidas->execute();
        $result_respondidas = $stmt_respondidas->get_result();

        if ($result_respondidas->num_rows > 0) {
            while ($row = $result_respondidas->fetch_assoc()) {
                
                echo "<div class='input-group1' style='margin: 0px; padding:20px;'>";
                echo "<div class='pregunta-contenedor'>";
                
                echo "<div class='pregunta-calificacion' style='margin-bottom: 0px;'>";
                echo "<label class='form-label pregunta-label'>{$row['pregunta']}</label>";
            
                echo "<div class='calificacion-estrellas'>";
                
                echo "</div>";
                
                echo "</div>";
                
                
                echo "<div class='pregunta-calificacion'>";
                // Mostrar estrellas en función de la calificación si la pregunta no es de selección única
                if ($row['tipo_pregunta'] !== 'seleccion_unica') {
                    echo "<div class='calificacion-estrellas'>";
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $row['calificacion']) {
                            // Estrella llena (amarilla)
                            echo "<span class='estrella llena'>★</span>";
                        } else {
                            // Estrella vacía (gris)
                            echo "<span class='estrella vacia'>★</span>";
                        }
                    }

                    echo "</div>";
                }

                // Mostrar la fecha y hora junto a la calificación
                
                echo "</div>";

                // Mostrar respuesta si existe
                if (empty($row['respuesta'])) {
                    echo "<div class='respuesta-contenedor'>";
                    echo "<p class='respuesta-texto'>Sin respuesta comentada.</p>";
                    echo "</div>";
                } else {
                    echo "<div class='respuesta-contenedor'>";
                    echo "<p class='respuesta-texto'><strong>Respuesta:</strong> {$row['respuesta']}</p>";
                    echo "</div>";
                    echo "<p class='fecha-respuesta' style='display: inline-block; margin:0px;  text-align: center;'><strong></strong> " . date('d-m-Y', strtotime($row['fecha_respuesta'])) . "</p>";

                }

                echo "</div>";
                echo "<hr>";
                echo "</div>";
            }
        } else {
            echo "<p>No has respondido ninguna encuesta aún.</p>";
        }

        // Cerrar los statements y la conexión
        $stmt_respondidas->close();
        $conn->close();

        ?>
    </div>
    <?php endif; ?>
  
</div>
</div>
        
</div>
</div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="scripts/script.js"></script>
</body>

</html>