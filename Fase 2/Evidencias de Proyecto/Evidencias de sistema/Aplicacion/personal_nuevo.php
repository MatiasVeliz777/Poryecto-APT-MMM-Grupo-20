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
$sql_nuevos = "SELECT u.rut, u.nombre_usuario, u.fecha_creacion, p.nombre, p.fecha_nacimiento, c.NOMBRE_CARGO, p.imagen, u.activo
               FROM usuarios u
               INNER JOIN personal p ON u.rut = p.rut
               INNER JOIN cargos c ON p.cargo_id = c.id
               WHERE MONTH(u.fecha_creacion) = ? AND YEAR(u.fecha_creacion) = ? AND u.activo = 1 AND p.activo = 1";

$stmt_nuevos = $conn->prepare($sql_nuevos);
$stmt_nuevos->bind_param('ii', $mes_actual, $año_actual);
$stmt_nuevos->execute();
$result_nuevos = $stmt_nuevos->get_result();

// Consulta para obtener usuarios DE LA EMRESA
$sql_todos = "SELECT u.rut, u.nombre_usuario, u.fecha_creacion, p.nombre, p.fecha_nacimiento, c.NOMBRE_CARGO, p.imagen, p.rol_id
              FROM usuarios u
              INNER JOIN personal p ON u.rut = p.rut
              INNER JOIN cargos c ON p.cargo_id = c.id
              INNER JOIN roles r ON p.rol_id = r.id
              WHERE u.activo = 1 AND p.activo = 1
                ORDER BY p.nombre ASC";


$stmt_todos = $conn->prepare($sql_todos);
$stmt_todos->execute();
$result_todos = $stmt_todos->get_result();

// BUSCAR:

// Consultar los roles para mostrar las opciones en el filtro
$sql_roles = "SELECT id, rol FROM roles";
$result_roles = $conn->query($sql_roles);

// Capturar el valor del rol seleccionado y de la búsqueda

$rol_id_seleccionado = isset($_GET['rol_id']) ? (int)$_GET['rol_id'] : 0;
$estadoSeleccionado = isset($_GET['habilitado']) ? (int)$_GET['habilitado'] : 1; // 1 por defecto para mostrar activos
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';

$rolSESION = $_SESSION['admin'];

// Verificar si el rol es diferente de 5 y se intenta filtrar por inactivos
if ($rolSESION != 1 && $estadoSeleccionado === 0) {
    // Forzar la redirección a activos si no tiene permiso
    $estadoSeleccionado = 1; // Cambiar a activos
}

// Consulta base para obtener usuarios de la empresa
$sql_todos = "SELECT u.rut, u.nombre_usuario, u.fecha_creacion, p.nombre, p.fecha_nacimiento, c.NOMBRE_CARGO, p.imagen, p.rol_id, u.activo
              FROM usuarios u
              INNER JOIN personal p ON u.rut = p.rut
              INNER JOIN cargos c ON p.cargo_id = c.id
              INNER JOIN roles r ON p.rol_id = r.id";

// Array para las condiciones de la consulta
$condiciones = ["u.activo = ?"]; // Mostrar activos por defecto
$params = [$estadoSeleccionado];
$param_types = 'i';

// Agregar condición de búsqueda si se ha ingresado una
if (!empty($busqueda)) {
    $palabras = explode(' ', $busqueda);
    foreach ($palabras as $palabra) {
        $condiciones[] = "p.nombre LIKE ?";
        $params[] = "%" . $palabra . "%";
        $param_types .= 's';
    }
}

// Agregar condición de filtro por rol si se ha seleccionado uno
if ($rol_id_seleccionado > 0) {
    $condiciones[] = "p.rol_id = ?";
    $params[] = $rol_id_seleccionado;
    $param_types .= 'i';
}

// Añadir las condiciones al SQL
$sql_todos .= " WHERE " . implode(" AND ", $condiciones);
$sql_todos .= " ORDER BY p.nombre ASC";

// Preparar la consulta
$stmt_todos = $conn->prepare($sql_todos);

// Asignar parámetros si existen
if (!empty($params)) {
    $stmt_todos->bind_param($param_types, ...$params);
}

// Ejecutar y obtener resultados
$stmt_todos->execute();
$result_todos_b = $stmt_todos->get_result();


$conn->close();
?>

<!DOCTYPE php>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal</title>
    <link rel="icon" href="Images/icono2.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles/style_cards.css">
    <link rel="stylesheet" href="styles/style_new_cards.css">
    <style>
        
         /* Estilos generales del contenedor */
         .custom-container1 {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start; /* Alineación superior */
            gap: 20px;
            max-width: 100%;
            padding: 20px;
            box-sizing: border-box;
            background-color: #f8f9fa; /* Fondo claro para mejor visibilidad */
            border-radius: 10px;
            margin: 20px 0; /* Espacio superior e inferior */
        }

        /* Tarjeta individual de cada perfil */
        

        /* Título de la sección */
        .section-title {
            width: 100%;
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .footer {
            background-color: #2c3e50;
            color: white;
            padding: 40px 0;
        }

        .footer-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }

        .footer-section {
            flex: 1;
            margin: 20px;
            text-align: center;
        }

        .footer-section h4 {
            font-size: 18px;
            margin-bottom: 15px;
        }

        .footer-section p {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .social-icons a {
            color: white;
            font-size: 20px;
            margin: 0 10px;
            text-decoration: none;
        }

        .social-icons a:hover {
            color: #3498db;
        }

        .footer-bottom {
            text-align: center;
            padding: 10px 0;
            border-top: 1px solid #3f5367;
            font-size: 14px;
        }

        .footer {
            width: 100%; /* Asegura que el footer ocupe todo el ancho */
            clear: both;  /* Asegura que el footer esté debajo de todo el contenido */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .footer {
            clear: both;
            width: 100%;
            background-color: #2c3e50;
            color: white;
            padding: 40px 0;
        }
    

        .filter-item{
            margin-right: 10px;
        }
        /* Media Queries para pantallas pequeñas */
        @media (max-width: 768px) {
            .cont-filtros {
                display: flex !important;
                flex-direction: column !important; /* Cambia a disposición en columna */
                justify-content: center !important;
                align-items: stretch !important;
                gap: 15px !important; /* Reduce el espacio entre elementos */
                width: 300px;
            }

            .filter-item {
                width: 100% !important; /* Los filtros ocupan todo el ancho */
                margin-left: 0px !important; 
            }

            form.d-flex {
                flex-direction: column !important; /* Botón y barra de búsqueda en columna */
                gap: 10px !important; /* Espacio entre los elementos */
            }

            form.d-flex input {
                width: 100% !important; /* Barra de búsqueda ocupa todo el ancho */
            }

            form.d-flex button {
                width: 100% !important; /* Botón ocupa todo el ancho */
            }
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




<!-- Contenedor de usuarios nuevos -->      
        <?php
    // Verificar si hay usuarios nuevos este mes
    if ($result_nuevos->num_rows > 0) {
    ?>
    <div class="custom-container1">
        <h2 style="width: 100%; text-align: center;">Usuarios Nuevos de este Mes</h2>
        <?php
        while ($nuevo_user = $result_nuevos->fetch_assoc()) {
            include("conexion.php");
            // Ruta de la imagen del usuario nuevo
            $ruta_imagen_nuevo = $carpeta_fotos . $nuevo_user['imagen'];
            
            // Verificar si la imagen del usuario nuevo existe
            $imagen_usuario_nuevo = file_exists($ruta_imagen_nuevo) ? $ruta_imagen_nuevo : $imagen_default;
            
            // Obtener el nombre completo del usuario logeado
            $nombre_usuario = $_SESSION['nombre'];  // Nombre completo de la persona logeada (quien va a dar la bienvenida)

            // Asegurarnos de que $nuevo_user contiene los datos del nuevo usuario
            $rut_usuario = $nuevo_user['rut'];

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
            <div class="cards-new-employees-card swiper-slide"
            onclick="abrirModal('<?php echo htmlspecialchars(trim($nuevo_user['nombre']), ENT_QUOTES); ?>', 
                        '<?php echo htmlspecialchars(trim(traducir_fecha($nuevo_user['fecha_nacimiento'])), ENT_QUOTES); ?>', 
                        '<?php echo htmlspecialchars(trim($nuevo_user['NOMBRE_CARGO']), ENT_QUOTES); ?>', 
                        '<?php echo htmlspecialchars(trim(traducir_fecha($nuevo_user['fecha_creacion'])), ENT_QUOTES); ?>', 
                        '<?php echo htmlspecialchars(trim($imagen_usuario_nuevo), ENT_QUOTES); ?>', 
                        '<?php echo htmlspecialchars(trim($nuevo_user['rut']), ENT_QUOTES); ?>', 
                        '<?php echo htmlspecialchars(trim($nuevo_user['activo']), ENT_QUOTES); ?>')">
                
            <div class="image-content cards-new-employees-image">
                <span class="overlay cards-new-employees-overlay"></span>
                <div class="cards-new-employees-image-wrapper">
                <img src="<?php echo $imagen_usuario_nuevo; ?>" class="profile-picture-nuevo" alt="Foto de Perfil">
                </div>
            </div>
            <div class="cards-new-employees-content">
                <h2 class="cards-new-employees-name"><?php echo $nuevo_user['nombre']; ?></h2>
                <p class="cards-new-employees-description"><strong>Fecha de Nacimiento:</strong> <?php echo traducir_fecha($nuevo_user['fecha_nacimiento']); ?></p>
                <p class="cards-new-employees-description"><strong>Cargo:</strong> <?php echo $nuevo_user['NOMBRE_CARGO']; ?></p>
                <p class="cards-new-employees-description"><strong>Fecha de Ingreso:</strong> <?php echo traducir_fecha($nuevo_user['fecha_creacion']); ?></p>
                <!-- Botón para dar la bienvenida -->
                <?php if ($mostrar_boton): ?>
                    <button type="button" class="cards-new-employees-button btn-bienvenida" onclick="darBienvenida('<?php echo $nuevo_user['rut']; ?>', '<?php echo $_SESSION['nombre']; ?>')">¡Bienvenido!</button>
                <?php endif; ?>
            </div>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
    } // Fin del if
    ?>
        

    <!-- Contenedor de usuarios de la empresa -->

        <div class="custom-container1" >
            <h2 style="width: 100%; text-align: center;">Personal actual de la empresa</h2>
            <p style="text-align: center; margin: 0 50px 0 50px">Este es el apartado de Personal, aquí podrás ver todos los empleados actuales de la empresa, buscar por quien gustes y ver sus datos de interés!</p>

            <div class="">
                <div class="cont-filtros" style="display: flex; flex-direction: row; justify-content: space-EVENLY; ">
                <div class="filter-item" style="width: 230px; margin-left: 40px; justify-content:end;">
                
                <select name="rol_id" id="rol_id" class="form-select" onchange="filtrarPersonal()">
                <label for="rol_id">Filtrar por Área:</label>
                    <option value="0">Todas las Áreas</option>
                    <?php while ($rol = $result_roles->fetch_assoc()) { ?>
                        <option value="<?php echo $rol['id']; ?>" <?php echo $rol_id_seleccionado == $rol['id'] ? 'selected' : ''; ?>>
                            <?php echo $rol['rol']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>     
                <?php
                $rolSESION = $_SESSION['admin'];
                if ($rolSESION == 1): ?>        
                    <div class="filter-item" style="width: 230px;  justify-content:end;">
                        <select name="habilitado" id="habilitado" class="form-select" onchange="filtrarHabilitado()">
                        <label for="habilitado">Filtrar por Estado:</label>
                            <option value="1" <?php echo ($estadoSeleccionado === 1) ? 'selected' : ''; ?>>Activos</option>
                            <option value="0" <?php echo ($estadoSeleccionado === 0) ? 'selected' : ''; ?>>Inactivos</option>
                        </select>
                    </div>  
                    <?php else: ?>
                <input type="hidden" id="habilitado" value="1">
            <?php endif; ?>

                <form method="GET" action="" style="background-color: #339bf000; justify-content: center;" class="d-flex">   
                        <input class="form-control" style="width: 400px; margin-right: 20px;" type="search" placeholder="Buscar empleado por nombre" aria-label="Buscar" name="buscar" value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                        <button class="btn btn-primary" type="submit">Buscar</button>
                    </form>
                </div>
        </div>

        <div class="cont-cards" style=" display: flex;
                                        flex-wrap: wrap;
                                        justify-content: center;
                                        align-items: flex-start;
                                        gap: 20px;
                                        max-width: 85%;
                                        padding: 20px;
                                        box-sizing: border-box;
                                        background-color: #f8f9fa;
                                        border-radius: 10px;
                                        margin: 20px 0;">
        <?php
        if ($result_todos_b->num_rows > 0) {
            while ($nuevo_user = $result_todos_b->fetch_assoc()) {
                $empleadoRut = $nuevo_user['rut'];
                $ruta_imagen_nuevo = $carpeta_fotos . $nuevo_user['imagen'];
                $imagen_usuario_nuevo = file_exists($ruta_imagen_nuevo) ? $ruta_imagen_nuevo : $imagen_default;
                ?>
                <div class="cards-new-employees-card swiper-slide" style="height: auto;" data-toggle="modal" data-target="#empleadoModal" 
                onclick="abrirModal('<?php echo htmlspecialchars(trim($nuevo_user['nombre']), ENT_QUOTES); ?>', 
                        '<?php echo htmlspecialchars(trim(traducir_fecha($nuevo_user['fecha_nacimiento'])), ENT_QUOTES); ?>', 
                        '<?php echo htmlspecialchars(trim($nuevo_user['NOMBRE_CARGO']), ENT_QUOTES); ?>', 
                        '<?php echo htmlspecialchars(trim(traducir_fecha($nuevo_user['fecha_creacion'])), ENT_QUOTES); ?>', 
                        '<?php echo htmlspecialchars(trim($imagen_usuario_nuevo), ENT_QUOTES); ?>', 
                        '<?php echo htmlspecialchars(trim($nuevo_user['rut']), ENT_QUOTES); ?>', 
                        '<?php echo htmlspecialchars(trim($nuevo_user['activo']), ENT_QUOTES); ?>')"> 
                        
                        <div class="image-content cards-new-employees-image">
                        <span class="overlay cards-new-employees-overlay"></span>
                        <div class="cards-new-employees-image-wrapper">
                            <img src="<?php echo $imagen_usuario_nuevo; ?>" class="profile-picture-nuevo" alt="Foto de Perfil">
                        </div>
                    </div>
                    <div class="cards-new-employees-content">
                        <h2 class="cards-new-employees-name"><?php echo $nuevo_user['nombre']; ?></h2>
                        <p class="cards-new-employees-description"><strong>Fecha de Nacimiento:</strong> <?php echo traducir_fecha($nuevo_user['fecha_nacimiento']); ?></p>
                        <p class="cards-new-employees-description"><strong>Cargo:</strong> <?php echo $nuevo_user['NOMBRE_CARGO']; ?></p>
                        <p class="cards-new-employees-description"><strong>Fecha de Ingreso:</strong> <?php echo traducir_fecha($nuevo_user['fecha_creacion']); ?></p>
                    </div>
                </div>
            <?php
            }
        } else {
            echo '<p>No se encontraron empleados con ese nombre.</p>';
        }
        ?>
        </div>
    </div>

<!-- Modal -->
<div class="modal fade" id="empleadoModal" tabindex="-1" aria-labelledby="empleadoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="empleadoModalLabel">Detalles del Empleado</h5>
                <button type="button" class="btn-close" onclick="forzarCierreModal()" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center">
                <!-- Imagen centrada -->
                <div class="text-center">
                    <img id="modalImagen" src="" alt="Foto de Perfil" style="width: 250PX; height: 250PX; object-fit: cover; object-position: 0 10%; border-radius: 50%; border: 4px solid #0ba5ec; margin: 20px auto;">
                </div>
                <h2 id="modalNombre" class="text-center"></h2>
                
                <p><strong> <span id="modalCargo"></span></strong></p>
                <p><strong></strong> <span id="modalFechaNacimiento"></span></p>
            </div>
            <div class="modal-footer">
                <?php if ($rolSESION == 1): ?>
                    <button type="button" class="btn btn-danger" id="btnEliminar">Eliminar</button>
                    <button type="button" class="btn btn-warning" id="btnInhabilitar" onclick="confirmarInhabilitacion(this.getAttribute('data-rut'))">Inhabilitar empleado</button>
                <?php endif; ?>
                <button type="button" class="btn btn-info" id="btnVerPerfil">Ver Perfil</button>
            </div>
        </div>
    </div>
</div>


</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmarEliminacion(rut) {
    // Cierra el modal de Bootstrap
    $('#empleadoModal').modal('hide');

    // Calcula el ancho del scrollbar y ajusta el padding-right
    const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
    document.body.style.paddingRight = `${scrollbarWidth}px`;
    document.body.style.overflow = 'hidden'; // Esto previene el cambio de tamaño al quitar el scroll

    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer. Para confirmar, escribe 'Eliminar'.",
        icon: 'warning',
        input: 'text',
        inputPlaceholder: 'Escribe "Eliminar"',
        inputAttributes: {
            'aria-label': 'Escribe Eliminar'
        },
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        didClose: () => {
            // Restaura el padding-right y el overflow del body cuando el alert se cierra
            document.body.style.paddingRight = '';
            document.body.style.overflow = '';
        },
        preConfirm: (inputValue) => {
            if (inputValue !== 'Eliminar') {
                Swal.showValidationMessage(
                    `<p style="color: red;">Palabra no coincide.</p>`
                );
                return false;
            }
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Empleado eliminado correctamente',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = "personal_eliminar.php?rut=" + rut;
            });
        }
    });
}

</script>



<script>
    function filtrarPersonal() {
        var rolId = document.getElementById('rol_id').value;
        var estado = document.getElementById('habilitado').value;
        window.location.href = "?rol_id=" + rolId + "&habilitado=" + estado;
    }
    function filtrarHabilitado() {
        var rolId = document.getElementById('rol_id').value;
        var estado = document.getElementById('habilitado').value;
        window.location.href = "?rol_id=" + rolId + "&habilitado=" + estado;
}
</script>

<script>

function confirmarAccion(rut, accion) {
    Swal.fire({
        title: `¿Estás seguro de que quieres ${accion === 'habilitar' ? 'habilitar' : 'inhabilitar'} al empleado?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: accion === 'habilitar' ? '#28a745' : '#d33', // Verde para habilitar, rojo para inhabilitar
        cancelButtonColor: '#3085d6',
        confirmButtonText: `Sí, ${accion === 'habilitar' ? 'habilitar' : 'inhabilitar'}`,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('personal_cambiar_estado.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `rut=${encodeURIComponent(rut)}&accion=${encodeURIComponent(accion)}`,
            })
            .then(response => response.text())
            .then(data => {
                Swal.fire({
                    title: `Empleado ${accion === 'habilitar' ? 'habilitado' : 'inhabilitado'}`,
                    text: data,
                    icon: 'success',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    location.reload(); // Recargar la página para ver los cambios
                });
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al intentar cambiar el estado del empleado.',
                    icon: 'error',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Aceptar'
                });
                console.error('Error:', error);
            });
        }
    });
}

function confirmarInhabilitacion(rut) {
    Swal.fire({
        title: '¿Estás seguro de que quieres inhabilitar al empleado?',
        text: "El empleado será inhabilitado y no aparecerá en las consultas.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, inhabilitar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Realizar la petición de inhabilitación
            fetch('personal_cambiar_estado.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'rut=' + encodeURIComponent(rut) + '&accion=inhabilitar',
            })
            .then(response => response.text())
            .then(data => {
                Swal.fire({
                    title: 'Inhabilitado',
                    text: data,
                    icon: 'success',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    // Opcional: recargar la página o actualizar la vista
                    location.reload();
                });
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al intentar inhabilitar al empleado.',
                    icon: 'error',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Aceptar'
                });
                console.error('Error:', error);
            });
        }
    });
}
function confirmarHabilitacion(rut) {
    Swal.fire({
        title: '¿Estás seguro de que quieres habilitar al empleado?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745', // Verde para habilitar
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, habilitar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('personal_cambiar_estado.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `rut=${encodeURIComponent(rut)}&accion=habilitar`,
            })
            .then(response => response.text())
            .then(data => {
                Swal.fire({
                    title: 'Empleado habilitado',
                    text: data,
                    icon: 'success',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    location.reload(); // Recargar la página
                });
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al intentar habilitar al empleado.',
                    icon: 'error',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Aceptar'
                });
                console.error('Error:', error);
            });
        }
    });
}

</script>

<script>
function abrirModal(nombre, fechaNacimiento, cargo, fechaIngreso, imagen, rut, estado) {
    document.getElementById('modalNombre').innerText = nombre;
    document.getElementById('modalFechaNacimiento').innerText = fechaNacimiento;
    document.getElementById('modalCargo').innerText = cargo;
    document.getElementById('modalImagen').src = imagen;

    // Configura el botón de redirección a perfiles.php
    var btnVerPerfil = document.getElementById('btnVerPerfil');
    if (btnVerPerfil) {
        // Establece la URL con el RUT como parámetro
        btnVerPerfil.setAttribute('onclick', `window.location.href='personal_perfiles.php?rut=${rut}'`);
    }

    // Configura el botón de habilitar/inhabilitar
    var btnInhabilitar = document.getElementById('btnInhabilitar');
    if (btnInhabilitar) {
        btnInhabilitar.setAttribute('data-rut', rut);

        if (estado == 0) { // Usuario inactivo
            btnInhabilitar.textContent = 'Habilitar empleado';
            btnInhabilitar.classList.remove('btn-warning');
            btnInhabilitar.classList.add('btn-success');
            btnInhabilitar.setAttribute('onclick', 'confirmarHabilitacion(this.getAttribute("data-rut"))');
        } else { // Usuario activo
            btnInhabilitar.textContent = 'Inhabilitar empleado';
            btnInhabilitar.classList.remove('btn-success');
            btnInhabilitar.classList.add('btn-warning');
            btnInhabilitar.setAttribute('onclick', 'confirmarInhabilitacion(this.getAttribute("data-rut"))');
        }
    }

    // Configura el botón de eliminación
    var btnEliminar = document.getElementById('btnEliminar');
    if (btnEliminar) {
        btnEliminar.setAttribute('data-rut', rut);
        btnEliminar.setAttribute('onclick', 'confirmarEliminacion(this.getAttribute("data-rut"))');
    }

    // Muestra el modal
    $('#empleadoModal').modal('show');
}

    function forzarCierreModal() {
        $('#empleadoModal').modal('hide');
    }

    // Listener para limpiar elementos adicionales al cerrar el modal
    $('#empleadoModal').on('hidden.bs.modal', function () {
    // Limpia el contenido del modal para liberar memoria
    document.getElementById('modalNombre').innerText = '';
    document.getElementById('modalFechaNacimiento').innerText = '';
    document.getElementById('modalCargo').innerText = '';
    document.getElementById('modalImagen').src = '';
    
    var btnInhabilitar = document.getElementById('btnInhabilitar');
    if (btnInhabilitar) {
        btnInhabilitar.textContent = 'Inhabilitar empleado'; // Reinicia el texto
        btnInhabilitar.classList.remove('btn-success');
        btnInhabilitar.classList.add('btn-warning');
        btnInhabilitar.setAttribute('onclick', 'confirmarInhabilitacion(this.getAttribute("data-rut"))');
    }
});

    // Eliminar manualmente los fondos adicionales si el modal se cierra repetidamente
    $(document).on('click', '.modal-backdrop', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });
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
                window.location.href = "#empleados-nuevos";
                
                // Desaparecer el botón después de dar la bienvenida
                var boton = document.querySelector('.btn-bienvenida');
                boton.style.display = 'none'; // Oculta el botón
            }
        };
        xhr.send("rut_usuario=" + rutUsuario + "&mensaje=" + encodeURIComponent(mensaje));
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</body>
</html>
