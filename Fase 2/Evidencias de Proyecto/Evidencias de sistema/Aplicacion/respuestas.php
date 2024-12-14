<?php
include('conexion.php'); // Conexión a la base de datos
session_start();

$rut_usuario = $_SESSION['rut']; // RUT del usuario autenticado

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); // Redirigir al login si no ha iniciado sesión
    exit();
}

$error = "";

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


// Consulta para obtener preguntas pendientes (no respondidas)
$query_pendientes = "
    SELECT p.*
    FROM preguntas_encuesta p
    LEFT JOIN respuestas_encuesta r
    ON p.id_pregunta = r.id_pregunta AND r.rut_usuario = ?
    WHERE r.id_pregunta IS NULL
";

$stmt_pendientes1 = $conn->prepare($query_pendientes);
$stmt_pendientes1->bind_param("s", $rut_usuario);
$stmt_pendientes1->execute();
$result_pendientes = $stmt_pendientes1->get_result();

// Consulta para obtener preguntas respondidas
$query_respondidas = "
    SELECT p.*, r.id_respuesta, r.calificacion, r.respuesta
    FROM preguntas_encuesta p
    JOIN respuestas_encuesta r
    ON p.id_pregunta = r.id_pregunta
    WHERE r.rut_usuario = ?
";

$stmt_respondidas = $conn->prepare($query_respondidas);
$stmt_respondidas->bind_param("s", $rut_usuario);
$stmt_respondidas->execute();
$result_respondidas = $stmt_respondidas->get_result();


// Obtener el usuario autenticado
$rut_usuario = $_SESSION['rut'];


?>

<!DOCTYPE php>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuestas</title>
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <style>
           .wrapper a{
            text-decoration: none;
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




        
        <header class="solicitud-header">
        <h1>Respuestas de las encuestas</h1>
    </header>

<button id="scrollToTop" class="scroll-to-top1">↑ </button>

<div class="solicitud-container-wrapper">

<div class="solicitud-container">
    <h2>Respuestas de las ecuestas</h2>  
    <h6 style="text-align: center;">Este es el apartado de Respuestas de las Encuestas, aqui podras ver todas las respuestas de las encuestas que se han realizado, puedes filtrar por fecha o por cantidad de estrellas! </h6>
    <?php
 
    echo "<p class='respuesta-texto' style='display:block; text-align:center; font-size: 13px'><strong>Si deseas ver las Respuestas filtradas,seleciona el filtro que desees </strong></p>";
    echo "<p class='respuesta-texto' style='display:block;text-align:center; font-size: 13px'><strong>en las opciones de abajo:</strong></p>";
    echo "<hr>";
     // cosulta par ver todas las respuestas
    ?>
<div class="filtros-resp" style="margin-bottom: 20px;">
    <form method="GET" action="" id="filtroForm" class="row g-3">
        <!-- Filtro de Tipo de Pregunta -->
        <div class="col-md-4">
            <label for="tipo_pregunta" class="form-label">Tipo de pregunta:</label>
            <select name="tipo_pregunta" id="tipo_pregunta" class="form-select" onchange="this.form.submit()">
                <option value="" <?php echo empty($_GET['tipo_pregunta']) ? 'selected' : ''; ?>>Todos</option>
                <option value="texto" <?php echo (isset($_GET['tipo_pregunta']) && $_GET['tipo_pregunta'] == 'texto') ? 'selected' : ''; ?>>Texto</option>
                <option value="seleccion_unica" <?php echo (isset($_GET['tipo_pregunta']) && $_GET['tipo_pregunta'] == 'seleccion_unica') ? 'selected' : ''; ?>>Selección única</option>
            </select>
        </div>

        <!-- Filtro por Mes -->
        <div class="col-md-4">
            <label for="mes" class="form-label">Mes:</label>
            <select name="mes" id="mes" class="form-select" onchange="this.form.submit()">
                <option value="" <?php echo empty($_GET['mes']) ? 'selected' : ''; ?>>Todos los meses</option>
                <option value="01" <?php echo (isset($_GET['mes']) && $_GET['mes'] == '01') ? 'selected' : ''; ?>>Enero</option>
                <option value="02" <?php echo (isset($_GET['mes']) && $_GET['mes'] == '02') ? 'selected' : ''; ?>>Febrero</option>
                <option value="03" <?php echo (isset($_GET['mes']) && $_GET['mes'] == '03') ? 'selected' : ''; ?>>Marzo</option>
                <option value="04" <?php echo (isset($_GET['mes']) && $_GET['mes'] == '04') ? 'selected' : ''; ?>>Abril</option>
                <option value="05" <?php echo (isset($_GET['mes']) && $_GET['mes'] == '05') ? 'selected' : ''; ?>>Mayo</option>
                <option value="06" <?php echo (isset($_GET['mes']) && $_GET['mes'] == '06') ? 'selected' : ''; ?>>Junio</option>
                <option value="07" <?php echo (isset($_GET['mes']) && $_GET['mes'] == '07') ? 'selected' : ''; ?>>Julio</option>
                <option value="08" <?php echo (isset($_GET['mes']) && $_GET['mes'] == '08') ? 'selected' : ''; ?>>Agosto</option>
                <option value="09" <?php echo (isset($_GET['mes']) && $_GET['mes'] == '09') ? 'selected' : ''; ?>>Septiembre</option>
                <option value="10" <?php echo (isset($_GET['mes']) && $_GET['mes'] == '10') ? 'selected' : ''; ?>>Octubre</option>
                <option value="11" <?php echo (isset($_GET['mes']) && $_GET['mes'] == '11') ? 'selected' : ''; ?>>Noviembre</option>
                <option value="12" <?php echo (isset($_GET['mes']) && $_GET['mes'] == '12') ? 'selected' : ''; ?>>Diciembre</option>
            </select>

        </div>

        <!-- Filtro por Calificación -->
        <div class="col-md-4">
            <label for="calificacion" class="form-label">Calificación:</label>
            <select name="calificacion" id="calificacion" class="form-select" onchange="this.form.submit()">
                <option value="" <?php echo empty($_GET['calificacion']) ? 'selected' : ''; ?>>Todas</option>
                <option value="0" <?php echo (isset($_GET['calificacion']) && $_GET['calificacion'] == '0') ? 'selected' : ''; ?>>0 estrellas</option>
                <option value="1" <?php echo (isset($_GET['calificacion']) && $_GET['calificacion'] == '1') ? 'selected' : ''; ?>>1 estrella</option>
                <option value="2" <?php echo (isset($_GET['calificacion']) && $_GET['calificacion'] == '2') ? 'selected' : ''; ?>>2 estrellas</option>
                <option value="3" <?php echo (isset($_GET['calificacion']) && $_GET['calificacion'] == '3') ? 'selected' : ''; ?>>3 estrellas</option>
                <option value="4" <?php echo (isset($_GET['calificacion']) && $_GET['calificacion'] == '4') ? 'selected' : ''; ?>>4 estrellas</option>
                <option value="5" <?php echo (isset($_GET['calificacion']) && $_GET['calificacion'] == '5') ? 'selected' : ''; ?>>5 estrellas</option>
            </select>
        </div>
    </form>
</div>



<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<?php
// Recoger los valores seleccionados en el formulario
$tipo_pregunta = isset($_GET['tipo_pregunta']) ? $_GET['tipo_pregunta'] : '';
$mes = isset($_GET['mes']) ? $_GET['mes'] : '';
$calificacion = isset($_GET['calificacion']) ? $_GET['calificacion'] : '';

// Crear consulta con condiciones según los filtros
$sql_preg_enc = "SELECT DISTINCT p.id_pregunta, p.pregunta, p.tipo_pregunta, p.fecha_creacion 
                FROM preguntas_encuesta p 
                JOIN respuestas_encuesta r ON p.id_pregunta = r.id_pregunta 
                WHERE 1=1";


// Añadir filtro por tipo de pregunta si está seleccionado
if (!empty($tipo_pregunta)) {
    $sql_preg_enc .= " AND p.tipo_pregunta = ?";
}

// Añadir filtro por mes si está seleccionado
if (!empty($mes)) {
    $sql_preg_enc .= " AND MONTH(p.fecha_creacion) = ?";
}

// Añadir filtro por calificación si está seleccionado, incluyendo 0 estrellas
if (isset($_GET['calificacion']) && $_GET['calificacion'] !== '') {
    $sql_preg_enc .= " AND r.calificacion = ?";
}

$stmt_pendientes1 = $conn->prepare($sql_preg_enc);

// Vincular parámetros a la consulta dependiendo de los filtros seleccionados
$bind_types = '';
$bind_params = [];

if (!empty($tipo_pregunta)) {
    $bind_types .= 's';
    $bind_params[] = $tipo_pregunta;
}
if (!empty($mes)) {
    $bind_types .= 's';
    $bind_params[] = $mes;
}
if (isset($_GET['calificacion']) && $_GET['calificacion'] !== '') {
    $bind_types .= 'i';
    $bind_params[] = (int)$_GET['calificacion'];  // Convertir calificación a entero
}

// Ejecutar el bind_param dinámico
if (!empty($bind_types)) {
    $stmt_pendientes1->bind_param($bind_types, ...$bind_params);
}

$stmt_pendientes1->execute();
$result_pendientes = $stmt_pendientes1->get_result();

if ($result_pendientes->num_rows > 0) {
    echo "<div class='accordion' id='accordionPreguntas'>"; // Iniciar el contenedor de Bootstrap Accordion

    // Recorrer todas las preguntas y mostrar en collapse de Bootstrap
    while ($row = $result_pendientes->fetch_assoc()) {
        $pregunta_id = $row['id_pregunta'];
        $pregunta = $row['pregunta'];
        $tipo_pregunta = $row['tipo_pregunta'];
        $fecha_creacion = date('d-m-Y', strtotime($row['fecha_creacion'])); // Formatear fecha

        echo "<div class='accordion-item' style='margin: 0px;'>";
        echo "<h2 class='accordion-header' id='heading{$pregunta_id}'>";
        echo "<button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapse{$pregunta_id}' aria-expanded='false' aria-controls='collapse{$pregunta_id}' style='display: flex; flex-direction: column; text-align: left; width: 100%;'>";
        // Mostrar la fecha de creación
        echo "<p style='font-size: 12px; color: gray; margin-top: 0px; margin-bottom:5px;'>Fecha de creación: " . $fecha_creacion . "</p>";

        // Mostrar tipo de pregunta
        echo "<p style='font-size: 12px; color: gray; margin-bottom: 5px;'><em>Tipo de pregunta: ";
        if ($tipo_pregunta === 'seleccion_unica') {
            echo "Selección única";
        } else {
            echo ucfirst($tipo_pregunta);
        }
        echo "</em></p>";
        // Mostrar la pregunta
        echo "<p style='margin: 0px;'><strong style='font-size: 1.2em;'>" . $pregunta . "</strong></p>";  
        echo "</button>";
        echo "</h2>";
        echo "<div id='collapse{$pregunta_id}' class='accordion-collapse collapse' aria-labelledby='heading{$pregunta_id}' data-bs-parent='#accordionPreguntas'>";
        echo "<div class='accordion-body'>";
        
        // Aquí mostramos las respuestas
        $query_respuestas = "
            SELECT p.pregunta, r.id_respuesta, r.calificacion, r.respuesta, r.fecha_respuesta, 
                p.tipo_pregunta, pe.nombre, pe.imagen
            FROM preguntas_encuesta p
            JOIN respuestas_encuesta r ON p.id_pregunta = r.id_pregunta
            JOIN personal pe ON r.rut_usuario = pe.rut
            JOIN usuarios u ON r.rut_usuario = u.rut
            WHERE p.id_pregunta = ? AND u.activo = 1
        ";

        if (isset($_GET['calificacion']) && $_GET['calificacion'] !== '') {
            $query_respuestas .= " AND r.calificacion = ?";
        }

        $stmt_respuestas = $conn->prepare($query_respuestas);

        if ($stmt_respuestas === false) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }

        if (isset($_GET['calificacion']) && $_GET['calificacion'] !== '') {
            $stmt_respuestas->bind_param("ii", $pregunta_id, $_GET['calificacion']);
        } else {
            $stmt_respuestas->bind_param("i", $pregunta_id);
        }

        $stmt_respuestas->execute();
        $result_respuestas = $stmt_respuestas->get_result();

        // Verificamos si hay respuestas
        if ($result_respuestas->num_rows > 0) {
                // Mostrar estadísticas solo para selección única
                if ($tipo_pregunta === 'seleccion_unica') {
                    echo "<h5>Estadísticas de respuestas:</h5>";

                    // Obtener el total de respuestas para la pregunta
                    $query_total_respuestas = "SELECT COUNT(*) AS total_respuestas FROM respuestas_encuesta WHERE id_pregunta = ?";
                    $stmt_total_respuestas = $conn->prepare($query_total_respuestas);
                    $stmt_total_respuestas->bind_param("i", $pregunta_id);
                    $stmt_total_respuestas->execute();
                    $result_total_respuestas = $stmt_total_respuestas->get_result();
                    $total_respuestas_row = $result_total_respuestas->fetch_assoc();
                    $total_respuestas = $total_respuestas_row['total_respuestas'];

                    // Obtener el número de respuestas por cada opción de selección única
                    $query_opciones = "
                        SELECT respuesta, COUNT(*) AS num_respuestas 
                        FROM respuestas_encuesta 
                        WHERE id_pregunta = ? 
                        GROUP BY respuesta";
                    
                    $stmt_opciones = $conn->prepare($query_opciones);
                    $stmt_opciones->bind_param("i", $pregunta_id);
                    $stmt_opciones->execute();
                    $result_opciones = $stmt_opciones->get_result();

                    // Estructura HTML para las barras
                    echo "<div class='opciones-estadisticas'>";

                    if ($total_respuestas > 0) {
                        while ($opcion_row = $result_opciones->fetch_assoc()) {
                            $respuesta = $opcion_row['respuesta'];
                            $num_respuestas = $opcion_row['num_respuestas'];
                            $porcentaje = ($num_respuestas / $total_respuestas) * 100;

                            // Mostrar barra de progreso con el porcentaje
                            echo "
                            <div class='opcion-row'>
                                <span class='opcion-label'><strong>$respuesta</strong></span>
                                <div class='opcion-bar'>
                                    <div class='filled-bar-su' style='width: {$porcentaje}%;'></div>
                                </div>
                                <span class='opcion-porcentaje'>" . round($porcentaje, 2) . "%</span>
                            </div>
                            ";
                        }
                    } else {
                        echo "<p>No hay respuestas disponibles para esta pregunta.</p>";
                    }

                    echo "</div>"; // Cerrar el contenedor de estadísticas
                }
                
                // Mostrar estadísticas de calificaciones solo para preguntas de tipo texto
                if ($tipo_pregunta === 'texto') {
                    echo "<h5>Estadísticas de calificaciones:</h5>";
                
                    // Obtener el total de respuestas para la pregunta
                    $query_total_respuestas_texto = "SELECT COUNT(*) AS total_respuestas FROM respuestas_encuesta WHERE id_pregunta = ?";
                    $stmt_total_respuestas_texto = $conn->prepare($query_total_respuestas_texto);
                    $stmt_total_respuestas_texto->bind_param("i", $pregunta_id);
                    $stmt_total_respuestas_texto->execute();
                    $result_total_respuestas_texto = $stmt_total_respuestas_texto->get_result();
                    $total_respuestas_texto_row = $result_total_respuestas_texto->fetch_assoc();
                    $total_respuestas_texto = $total_respuestas_texto_row['total_respuestas'];
                
                    // Obtener el número de respuestas por cada calificación (de 1 a 5 estrellas)
                    $query_calificaciones = "
                        SELECT calificacion, COUNT(*) AS num_respuestas 
                        FROM respuestas_encuesta 
                        WHERE id_pregunta = ? 
                        GROUP BY calificacion";
                    
                    $stmt_calificaciones = $conn->prepare($query_calificaciones);
                    $stmt_calificaciones->bind_param("i", $pregunta_id);
                    $stmt_calificaciones->execute();
                    $result_calificaciones = $stmt_calificaciones->get_result();
                
                    // Estructura HTML para las barras
                    echo "<div class='rating-stats'>";
                
                    // Recorrer cada calificación (1 a 5 estrellas)
                    if ($total_respuestas_texto > 0) {
                        while ($calificacion_row = $result_calificaciones->fetch_assoc()) {
                            $calificacion = isset($calificacion_row['calificacion']) ? $calificacion_row['calificacion'] : 0;
                            $num_respuestas = $calificacion_row['num_respuestas'];
                            $porcentaje = ($num_respuestas / $total_respuestas_texto) * 100;
                
                            // Mostrar barra de progreso y calificación
                            echo "
                            <div class='rating-row'>
                                <span class='rating-label'>{$calificacion}</span>
                                <div class='rating-bar'>
                                    <div class='filled-bar' style='width: {$porcentaje}%;'></div>
                                </div>
                                <span class='rating-percentage'>" . round($porcentaje, 2) . "%</span>
                            </div>
                            ";
                        }
                    } else {
                        echo "<p>No hay respuestas disponibles para esta pregunta.</p>";
                    }
                
                    echo "</div>"; // Cerrar el contenedor de estadísticas
                
                    $stmt_calificaciones->close();
                    $stmt_total_respuestas_texto->close();
                }
                

            while ($respuesta_row = $result_respuestas->fetch_assoc()) {
                // Ruta de la carpeta donde están las imágenes de perfil
                $carpeta_fotos = 'Images/fotos_personal/'; // Cambia esta ruta a la carpeta donde están tus fotos
                $imagen_default = 'Images/profile_photo/imagen_default.jpg'; // Ruta de la imagen predeterminada

                // Obtener el nombre del archivo de imagen desde la base de datos
                $nombre_imagen = $respuesta_row['imagen']; // Se asume que este campo contiene solo el nombre del archivo

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

                echo "<div class='input-group1' style='margin-bottom: 20px;'>";
                
                // Mostrar imagen de la persona
                echo "<div class='pregunta-contenedor'>";
                
                echo "<div class='pregunta-calificacion' style='margin-bottom: 0px;'>";
                echo "<img src='{$imagen_final}' alt='Foto de {$respuesta_row['nombre']}' style='width: 50px; height: 50px; border-radius: 50%; margin-right: 10px;'>";

                echo "<label class='form-label pregunta-label' style='margin-bottom: 0px;'>{$respuesta_row['nombre']}</label>";
            
                echo "<div class='calificacion-estrellas'>";
                echo "</div>";
                echo "</div>";
                
                echo "<div class='pregunta-calificacion'>";
                // Mostrar estrellas en función de la calificación si la pregunta no es de selección única
                if ($respuesta_row['tipo_pregunta'] !== 'seleccion_unica') {
                    echo "<div class='calificacion-estrellas'>";
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $respuesta_row['calificacion']) {
                            // Estrella llena (amarilla)
                            echo "<span class='estrella llena'>★</span>";
                        } else {
                            // Estrella vacía (gris)
                            echo "<span class='estrella vacia'>★</span>";
                        }
                    }
                    echo "<p class='fecha-respuesta' style='display: inline-block; margin:0px; margin-left:15px; text-align: center;'><strong></strong> " . date('d-m-Y', strtotime($respuesta_row['fecha_respuesta'])) . "</p>";

                    echo "</div>";
                }
                else{
                    echo "<p class='fecha-respuesta' style='display: inline-block; margin:0px; margin-top:15px; text-align: center;'><strong>Fecha de respuesta:</strong> " . date('d-m-Y', strtotime($respuesta_row['fecha_respuesta'])) . "</p>";
                }
                // Mostrar la fecha y hora junto a la calificación
                echo "</div>";
                
                
                // Mostrar respuesta si existe
                if (empty($respuesta_row['respuesta'])) {
                    echo "<div class='respuesta-contenedor'>";
                    echo "<p class='respuesta-texto'>Sin respuesta comentada.</p>";
                    echo "</div>";
                } else {
                    echo "<div class='respuesta-contenedor'>";
                    echo "<p class='respuesta-texto'><strong>Respuesta:</strong> {$respuesta_row['respuesta']}</p>";
                    
                    echo "</div>";
                }

                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>No hay respuestas para esta pregunta.</p>";
        }

        echo "</div>"; // Cerrar el body del collapse
        echo "</div>"; // Cerrar el collapse
        echo "</div>"; // Cerrar el accordion-item

        $stmt_respuestas->close();
    }

    echo "</div>"; // Cerrar el contenedor accordion
} else {
    echo "<p>No hay preguntas disponibles para mostrar.</p>";
}

$stmt_pendientes1->close();
$conn->close();
?>




        </div>
    </div>
</div>

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
