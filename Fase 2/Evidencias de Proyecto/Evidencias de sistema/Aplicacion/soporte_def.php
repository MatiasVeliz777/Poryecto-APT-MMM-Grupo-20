<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); // Redirigir al login si no ha iniciado sesión
    exit();
}


// Verificar si el rol no es igual a 4
if ($_SESSION['rol'] != 4) {
    header("Location: home.php"); // Redirigir al home si no tiene el rol adecuado
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

// Obtener el rol seleccionado de la URL o por defecto
$rol_id_seleccionado = isset($_GET['rol_id']) ? $_GET['rol_id'] : 0;

// Consultar los roles para mostrar las opciones en el filtro
$sql_roles = "SELECT id, rol FROM roles";
$result_roles = $conn->query($sql_roles);

// Consultar las solicitudes filtradas por el rol seleccionado
if ($rol_id_seleccionado == 0) {
    // Si no se selecciona ningún rol específico, mostrar todas las solicitudes
    $sql_solicitudes = "SELECT 
                            soportes.id AS 'ID del Soporte',
                            soportes.titulo AS 'Titulo del Soporte',
                            soportes.contenido AS 'Contenido del Soporte',
                            soportes.urgencia AS 'Urgencia del Soporte',
                            soportes.imagen AS 'Imagen del Soporte',
                            soportes.fecha_creacion AS 'Fecha de Creación',
                            soportes.estado AS 'Estado del Soporte',
                            personal.nombre AS 'Nombre del Usuario',
                            personal.imagen AS 'Imagen del Usuario',
                            roles.rol AS 'Nombre del Rol'
                        FROM 
                            soportes
                        JOIN 
                            personal ON soportes.rut = personal.rut
                        JOIN 
                            roles ON soportes.rol_id = roles.id
                        ORDER BY soportes.fecha_creacion DESC"
                            ;
} else {
    // Si se selecciona un rol, filtrar las solicitudes por el rol seleccionado
    $sql_solicitudes = "SELECT 
                            soportes.id AS 'ID del Soporte',
                            soportes.titulo AS 'Titulo del Soporte',
                            soportes.contenido AS 'Contenido del Soporte',
                            soportes.urgencia AS 'Urgencia del Soporte',
                            soportes.imagen AS 'Imagen del Soporte',
                            soportes.fecha_creacion AS 'Fecha de Creación',
                            soportes.estado AS 'Estado del Soporte',
                            personal.nombre AS 'Nombre del Usuario',
                            personal.imagen AS 'Imagen del Usuario',
                            roles.rol AS 'Nombre del Rol'
                        FROM 
                            soportes
                        JOIN 
                            personal ON soportes.rut = personal.rut
                        JOIN 
                            roles ON soportes.rol_id = roles.id
                        WHERE soportes.rol_id = '$rol_id_seleccionado'
                        ORDER BY soportes.fecha_creacion DESC";
}

$result_solicitudes = $conn->query($sql_solicitudes);


// Contar las solicitudes por estado
$sql_estados_count = "SELECT estado, COUNT(*) AS total FROM soportes GROUP BY estado";
$result_estados_count = $conn->query($sql_estados_count);

$en_espera = 0;
$en_curso = 0;
$solucionado = 0;

// Asignar los valores de los conteos a las variables correspondientes
while ($row = $result_estados_count->fetch_assoc()) {
    if ($row['estado'] == 'En espera') {
        $en_espera = $row['total'];
    } elseif ($row['estado'] == 'En curso') {
        $en_curso = $row['total'];
    } elseif ($row['estado'] == 'Solucionado') {
        $solucionado = $row['total'];
    }
}

// Obtener el estado seleccionado de la URL o por defecto
$estado_seleccionado = isset($_GET['estado']) ? $_GET['estado'] : '0';

// Consultar las solicitudes filtradas por el rol y estado seleccionado
$sql_solicitudes = "SELECT 
                        soportes.id AS 'ID del Soporte',
                        soportes.titulo AS 'Titulo del Soporte',
                        soportes.contenido AS 'Contenido del Soporte',
                        soportes.urgencia AS 'Urgencia del Soporte',
                        soportes.imagen AS 'Imagen del Soporte',
                        soportes.fecha_creacion AS 'Fecha de Creación',
                        soportes.estado AS 'Estado del Soporte',
                        personal.nombre AS 'Nombre del Usuario',
                        personal.imagen AS 'Imagen del Usuario',
                        roles.rol AS 'Nombre del Rol'
                    FROM 
                        soportes
                    JOIN 
                        personal ON soportes.rut = personal.rut
                    JOIN 
                        roles ON soportes.rol_id = roles.id
                    WHERE 
                        1=1 
                        ";

$sql_soluc = "SELECT 
                    soportes.id AS 'ID del Soporte',
                    soportes.titulo AS 'Titulo del Soporte',
                    soportes.contenido AS 'Contenido del Soporte',
                    soportes.urgencia AS 'Urgencia del Soporte',
                    soportes.imagen AS 'Imagen del Soporte',
                    soportes.fecha_creacion AS 'Fecha de Creación',
                    soportes.estado AS 'Estado del Soporte',
                    personal.nombre AS 'Nombre del Usuario',
                    personal.imagen AS 'Imagen del Usuario',
                    roles.rol AS 'Nombre del Rol'
                    FROM 
                    soportes
                    JOIN 
                    personal ON soportes.rut = personal.rut
                    JOIN 
                    roles ON soportes.rol_id = roles.id

                    WHERE 1=1 AND soportes.estado = 'Solucionado'

                    ORDER BY soportes.fecha_creacion DESC";     

                    

// Filtrar por rol si está seleccionado
if ($rol_id_seleccionado != 0) {
    $sql_solicitudes .= " AND soportes.rol_id = '$rol_id_seleccionado'";
    $sql_soluc .= " AND soportes.rol_id = '$rol_id_seleccionado'";
}

// Filtrar por estado si está seleccionado
if ($estado_seleccionado != '0') {
    $sql_solicitudes .= " AND soportes.estado = '$estado_seleccionado'";
    $sql_soluc .= " AND soportes.rol_id = '$estado_seleccionado'";
}


// Ordenar los resultados
$sql_solicitudes .= " ORDER BY soportes.fecha_creacion DESC";

$result_soluc = $conn->query($sql_soluc);

$result_solicitudes = $conn->query($sql_solicitudes);


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

// Total de solicitudes
$total_solicitudes = $en_espera + $en_curso + $solucionado;

// Consultar la cantidad de solicitudes por nivel de urgencia
$sql_urgencia_count = "SELECT urgencia, COUNT(*) AS total FROM soportes GROUP BY urgencia";
$result_urgencia_count = $conn->query($sql_urgencia_count);

$urgencia_alta = 0;
$urgencia_media = 0;
$urgencia_baja = 0;

while ($row = $result_urgencia_count->fetch_assoc()) {
    if ($row['urgencia'] == '442') {
        $urgencia_alta = $row['total'];
    } elseif ($row['urgencia'] == '473') {
        $urgencia_baja = $row['total'];
    }
}


$conn->close();
?>


<!DOCTYPE php>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soporte</title>
    <link rel="icon" href="Images/icono2.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="styles/style.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">


    <style>
        .table img {
            max-width: 80px;
            max-height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        
        .urgencia-alta {
            color: #1dd2ff;
            font-weight: bold;
        }

        .urgencia-media {
            color: #FFD767;
        }

        .urgencia-baja {
            color: #0067dd;
        }

        /* Hacer que las filas sean clicables */
        .clickable-row {
            cursor: pointer;
        }

        /* Estilos para el perfil del usuario en el modal */
        .user-profile {
            display: flex;
            align-items: center;

        }

        .user-profile img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }

        .user-profile p {
            margin: 0;
            font-weight: bold;
            font-size: 1.2em;
        }

        /* Estilo para las secciones del modal */
        .modal-section {
            padding: 10px 0;
            border-bottom: 1px solid #eaeaea;
        }

        .modal-section .titulo {
            padding: 10px 0;
            border-bottom: 1px solid #eaeaea;
            display: flex;
            justify-content: space-between;
            overflow-wrap: break-word; 
            
        }
        .modal-section .titulo h5 {
            font-size: 1.1em;
            font-weight: bold;
            margin-bottom: 10px;
            opacity: 0.6;                           
            
        }
            #modal-titulo {
        word-wrap: break-word;         /* Permite dividir palabras largas si es necesario */
        white-space: normal;           /* Permite el salto de línea */
        max-width: 100%;               /* Asegura que el título no se desborde del modal */
        overflow-wrap: break-word;     /* Maneja palabras largas que no tienen espacios */
        word-break: break-word;        /* Asegura que las palabras largas se rompan adecuadamente */
            }

            .modal-section-te {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;       /* Alinea el título y el estado al principio verticalmente */
                flex-wrap: wrap;               /* Permite que los elementos se ajusten en pantallas pequeñas */
            }

            @media (max-width: 768px) {
                .modal-section-te {
                    flex-direction: column;    /* En pantallas pequeñas, muestra el título y el estado en columnas */
                }
                .estado-container {
                    margin-top: 10px;          /* Añade espacio entre el título y el estado en pantallas pequeñas */
                    width: 100%;               /* Asegura que el contenedor del estado ocupe todo el ancho */
                }
            }


                    .modal-section:last-child {
                        border-bottom: none;
                    }

                    .modal-section h5,.modal-section-titulo h5 {
                        font-size: 1.1em;
                        font-weight: bold;
                        margin-bottom: 10px;
                        opacity: 0.6;
                    }

                    .modal-section p {
                        font-size: 0.95em;
                        margin: 0;
                        font-weight: bold;
                        
                        
                    }

                    /* Estilo para la imagen dentro del modal */
                    .modal img {
                        border-radius: 8px;
                        object-fit: cover;
                        max-width: 100%;
                        
                    }
                    .modal-section .imgss {
                        display: flex;
                justify-content: center;
                align-items: center;
                border-radius: 10px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                    }
                    .table th {
                        background-color: #00304A;
                        color: #fff;
                        text-align: center;
                        font-weight: 600;
                        padding: 15px;
                        border-top: none;
                    }

                    /* Colores para los diferentes estados */
            .estado-solucionado {
                color: #207044;
                font-weight: bold;
            }

            .estado-curso {
                color: #FFD767;
                font-weight: bold;
            }

            .estado-espera {
                color: #E53E30;
                font-weight: bold;
            }
            .estado-container {
                display: flex;
                align-items: center;
                justify-content: flex-end;
            }

            .estado-container select {
                min-width: 150px;
            }

            .filter-container {
            display: flex;
            justify-content: flex-start;
            gap: 20px; /* Espacio entre los filtros */
            margin-bottom: 20px; /* Espacio debajo del formulario */
            justify-content: center;    
        }

        .filter-form {
            display: flex;
            gap: 20px; /* Espacio entre los filtros */
            flex-wrap: wrap; /* Permite que los elementos se muevan a otra fila si el espacio es pequeño */
            
            margin: 0px;
        }

        .filter-item {
            display: flex;
            flex-direction: column; /* Coloca el label encima del select */
            
        }

        .filter-item label {
            margin-bottom: 5px; /* Espacio entre el label y el select */
            font-weight: bold;
        }

        .form-select {
            padding: 10px; /* Espacio interno más amplio */
            font-size: 16px; /* Tamaño de fuente más grande */
            width: 200px; /* Ancho del select */
            border-radius: 5px; /* Bordes redondeados */
        }
        .statistics-container {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .statistics-container h4, .statistics-container h5 {
            margin-top: 0;
        }

        .statistics-container ul {
            list-style-type: none;
            padding: 0;
        }

        .statistics-container li {
            font-size: 14px;
            margin-bottom: 5px;
        }

    </style>
</head>

<body>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
    <h1> Solicitudes Soporte Técnico</h1>
</header>


        <div class="container-sop">
    <h1 class="text-center my-4">Ver Solicitudes de Soporte Informatico</h1>
    <div class="table-responsive">

   <!-- Filtros combinados en un solo formulario -->
   <div class="filter-container">
    <form method="GET" action="" class="filter-form">
        <div class="filter-item">
            <label for="rol_id">Filtrar por Área:</label>
            <select name="rol_id" id="rol_id" class="form-select" onchange="this.form.submit()">
                <option value="0">Todos las Área</option>
                <?php while ($rol = $result_roles->fetch_assoc()) { ?>
                    <option value="<?php echo $rol['id']; ?>" <?php echo $rol_id_seleccionado == $rol['id'] ? 'selected' : ''; ?>>
                        <?php echo $rol['rol']; ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="filter-item">
            <label for="estado">Filtrar por Estado:</label>
            <select name="estado" id="estado" class="form-select" onchange="this.form.submit()">
                <option value="0">Todos los Estados</option>
                <option value="En espera" <?php echo $estado_seleccionado == 'En espera' ? 'selected' : ''; ?>>En espera</option>
                <option value="En curso" <?php echo $estado_seleccionado == 'En curso' ? 'selected' : ''; ?>>En curso</option>
                <option value="Solucionado" <?php echo $estado_seleccionado == 'Solucionado' ? 'selected' : ''; ?>>Solucionado</option>
            </select>
        </div>
    </form>
</div>


    
    <!-- Tabla de solicitudes -->
    <div class="table-container">
        <table id="tabla-soportes" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th scope="col">Fecha</th>
                    <th scope="col">Titulo</th>
                    <th scope="col">Descripcion</th>
                    <th scope="col">Edificio</th>
                    <th scope="col">Área</th>
                    <th scope="col">Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php
                include("conexion.php");
     
                if ($result_solicitudes->num_rows > 0) {
                    while($row = $result_solicitudes->fetch_assoc()) {
                        // Asignamos clases de color a la urgencia
                        $urgencia_class = '';
                        if ($row['Urgencia del Soporte'] === '442') {
                            $urgencia_class = 'urgencia-alta';
                        } elseif ($row['Urgencia del Soporte'] === '') {
                            $urgencia_class = 'urgencia-media';
                        } elseif ($row['Urgencia del Soporte'] === '473') {
                            $urgencia_class = 'urgencia-baja';
                        }
                        // Verificar si la función ya existe antes de declararla
                        if (!function_exists('traducirMeses')) {
                            function traducirMeses($fecha) {
                                $mesesIngles = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
                                $mesesEspanol = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');

                            return str_replace($mesesIngles, $mesesEspanol, $fecha);
                        }
                        }

                        $imagen_user_sop = $row['Imagen del Usuario']; // Se asume que este campo contiene solo el nombre del archivo

                        // Construir la ruta completa de la imagen del usuario
                        $ruta_imguser_sop = $carpeta_fotos . $imagen_user_sop;

                        // Verificar si la imagen del usuario existe en la carpeta
                        if (file_exists($ruta_imguser_sop)) {
                            // Si la imagen existe, se usa esa ruta
                            $imagen_final_user = $ruta_imguser_sop;
                        } else {
                            // Si no existe, se usa la imagen predeterminada
                            $imagen_final_user = $imagen_default;
                            
                        }

                        // Crear una fila clicable con atributos de datos para el modal
                        echo '<tr class="clickable-row" data-bs-toggle="modal" data-bs-target="#solicitudModal" 
                                    data-soporte-id="' . $row['ID del Soporte'] . '"
                              data-titulo="' . $row['Titulo del Soporte'] . '" 
                              data-contenido="' . $row['Contenido del Soporte'] . '" 
                              data-urgencia="' . ucfirst($row['Urgencia del Soporte']) . '" 
                              data-imagen="' . (!empty($row['Imagen del Soporte']) ? $row['Imagen del Soporte'] : 'Images/noimagen.jpg') . '" 
                              data-fecha="'   . traducirMeses(date('j F Y', strtotime($row['Fecha de Creación']))) . '" 
                              data-estado="' . $row['Estado del Soporte'] . '" 
                              data-usuario="' . $row['Nombre del Usuario'] . '" 
                              data-usuario-imagen="' . $imagen_final_user . '" 
                              data-rol="' . $row['Nombre del Rol'] . '">';

                            echo '<td>' . date('d/m/Y H:i', strtotime($row['Fecha de Creación'])) . '</td>';
                            echo '<td>';
                            $titulo = $row['Titulo del Soporte'];
                            if (strlen($titulo) > 25) {
                                echo substr($titulo, 0, 25) . '...';
                            } else {
                                echo $titulo;
                            }
                            echo '</td>';
                        echo '<td>' . (strlen($row['Contenido del Soporte']) > 30 ? substr($row['Contenido del Soporte'], 0, 30) . '...' : $row['Contenido del Soporte']) . '</td>';
                        echo '<td class="'.$urgencia_class.'">' . ucfirst($row['Urgencia del Soporte']) . '</td>';
                        echo '<td>' . $row['Nombre del Rol'] . '</td>';
                        
                        // Definir clases de color para el estado
                        $estado_class = '';
                        if ($row['Estado del Soporte'] == 'Solucionado') {
                            $estado_class = 'estado-solucionado'; // Verde
                        } elseif ($row['Estado del Soporte'] == 'En curso') {
                            $estado_class = 'estado-curso'; // Amarillo
                        } elseif ($row['Estado del Soporte'] == 'En espera') {
                            $estado_class = 'estado-espera'; // Rojo
                        }
                        
                        // Aplicar la clase al estado
                        echo '<td class="' . $estado_class . '">' . $row['Estado del Soporte'] . '</td>';                          
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="10" class="text-center">No se encontraron solicitudes de soporte.</td></tr>';
                }

                
                ?>
                <tbody>   
            </tbody>
        </table>
    </div>

<script>
    // Recargar la página cada 10 segundos (10000 milisegundos)
    setInterval(function() {
        location.reload();
    }, 60000); // Cambia 10000 a otro valor en milisegundos si deseas un intervalo diferente
</script>


<!-- Modal de Bootstrap -->
<div class="modal fade" id="solicitudModal" tabindex="-1" aria-labelledby="solicitudModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
            <div class="user-profile">
                    <img id="modal-imagen-usuario" src="" alt="Imagen del usuario" class="img-fluid rounded-circle me-3" style="width: 60px;">
                    <p><strong id="modal-usuario"></strong></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Sección del perfil del usuario -->
            
                <!-- Sección del título -->
                <div class="modal-section-te">
    <div class="modal-section-titulo">
        <h5>Título:</h5>
        <h4 id="modal-titulo"></h4>
    </div> 
    <div class="estado-container ms-auto text-end">
        <label for="modal-estado-select" class="me-2">Estado:</label>
        <form method="POST" action="actualizar_estado.php" class="d-inline">
            <input type="hidden" name="soporte_id" id="modal-soporte-id" value="">
            <select name="estado" class="form-select d-inline w-auto" id="modal-estado-select">
                <option value="En espera">En espera</option>
                <option value="En curso">En curso</option>
                <option value="Solucionado">Solucionado</option>
            </select>
        </form>
    </div>
</div>

                <!-- Sección del contenido -->
                <div class="modal-section">
                    <h5>Contenido:</h5>
                    <p id="modal-contenido"></p>
                </div>

                <!-- Sección de urgencia -->
                <div class="modal-section">
                    <h5>Edificio:</h5>
                    <p id="modal-urgencia"></p>
                </div>

                <!-- Sección de la fecha -->
                <div class="modal-section">
                    <h5>Fecha de Creación:</h5>
                    <p id="modal-fecha"></p>
                </div>

                <!-- Sección del rol -->
                <div class="modal-section">
                    <h5>Rol:</h5>
                    <p id="modal-rol"></p>
                </div>

                <!-- Imagen del soporte -->
                <div class="modal-section">
                    <h5>Imagen del Soporte:</h5>
                    <div class="imgss">
                        <img id="modal-imagen-soporte" src="" alt="Imagen del soporte" class="img-fluid">
                    </div>
                </div>
                <div class="modal-section">
                <!-- Imagen permanente debajo con subtítulo -->
                <div class="soporte-permanent-image-container">
                    <img src="Images/logo_clinica.png" alt="Imagen Permanente" class="soporte-permanent-image" style="opacity: 0.5;">
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div class="titulo-home" style="margin-top: 20px;">
                <h4 style="font-size: 2rem; text-align: center;">Estadisticas Generales</h4>
                <p style=" font-size: 1rem; margin-bottom: 0px;">Aqui pueden observar un poco las estadisticas generales de las solicitudes de soporte!</p>
            </div>

    <div class="estadisticas" style="display: flex; justify-content: center; gap: 20px; margin: 30px; max-width: 800px; margin-left: auto; margin-right: auto;">
    <div class="statistics-container" style="flex: 1;">
        <h4>Estadísticas Rápidas de Solicitudes de soporte</h4>
        <ul>
            <li>Total de solicitudes: ‎ ‎ ‎ <b><?php echo $total_solicitudes; ?></b></li>
            <li>En espera: ‎ ‎ ‎ <b><?php echo $en_espera; ?></b></li>
            <li>En curso: ‎ ‎ ‎ <b><?php echo $en_curso; ?></b></li>
            <li>Solucionado:‎ ‎ ‎  <b><?php echo $solucionado; ?></b></li>
        </ul>
        <h5>Solicitudes de soporte por Edificio</h5>
        <ul>
            <li>Edificio 442:‎ ‎ ‎  <b><?php echo $urgencia_alta; ?></b></li>
            <li>Edificio 473: ‎ ‎ ‎ <b><?php echo $urgencia_baja; ?></b></li>
        </ul>
    </div>

    <!-- Sección para el gráfico circular -->
    <div id="chartContainer" class="my-4" style="flex: 1;">
        <canvas id="estadoSolicitudesChart"></canvas>
    </div>
</div>





<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Incluir SweetAlert desde CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const solicitudModal = document.getElementById('solicitudModal');

    solicitudModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const soporteId = button.getAttribute('data-soporte-id');
        const titulo = button.getAttribute('data-titulo');
        const contenido = button.getAttribute('data-contenido');
        const urgencia = button.getAttribute('data-urgencia');
        const estado = button.getAttribute('data-estado');
        const fecha = button.getAttribute('data-fecha');
        const rol = button.getAttribute('data-rol');
        const imagenUsuario = button.getAttribute('data-usuario-imagen');
        const nombreUsuario = button.getAttribute('data-usuario');
        const imagenSoporte = button.getAttribute('data-imagen');

        // Actualizar el contenido del modal
        document.getElementById('modal-soporte-id').value = soporteId;
        document.getElementById('modal-titulo').innerText = titulo;
        document.getElementById('modal-contenido').innerText = contenido;
        document.getElementById('modal-urgencia').innerText = urgencia;
        document.getElementById('modal-fecha').innerText = fecha;
        document.getElementById('modal-rol').innerText = rol;
        document.getElementById('modal-imagen-usuario').src = imagenUsuario;
        document.getElementById('modal-imagen-soporte').src = imagenSoporte;
        document.getElementById('modal-usuario').innerText = nombreUsuario;

        // Seleccionar el estado actual y guardar el valor inicial
        const estadoSelect = document.getElementById('modal-estado-select');
        estadoSelect.value = estado;
        estadoSelect.dataset.previous = estado; // Guardar el estado inicial

        // Remover cualquier evento de cambio previo
        estadoSelect.removeEventListener('change', handleEstadoChange);

        // Añadir evento de cambio al select para manejar la confirmación y envío
        estadoSelect.addEventListener('change', handleEstadoChange);
    });

    function handleEstadoChange(event) {
        const selectElement = event.target;
        const previousValue = selectElement.dataset.previous || 'En espera';

        // Detener el cambio automático de selección
        event.preventDefault();

        // Mostrar SweetAlert de confirmación
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Cambiar el estado de esta solicitud aplicará el cambio de manera permanente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Guardar el valor actual como el nuevo valor previo
                selectElement.dataset.previous = selectElement.value;

                // Enviar el formulario manualmente si el usuario confirma
                selectElement.closest('form').submit();
            } else {
                // Si el usuario cancela, restablecer el valor anterior
                selectElement.value = previousValue;
            }
        });
    }
});
</script>



            
<!-- Script para el gráfico circular de Chart.js -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Obtener el contexto del canvas
        var ctx = document.getElementById('estadoSolicitudesChart').getContext('2d');
        
        // Crear el gráfico circular
        var estadoSolicitudesChart = new Chart(ctx, {
            type: 'pie', // Tipo de gráfico
            data: {
                labels: ['En espera', 'En curso', 'Solucionado'], // Etiquetas para cada parte del gráfico
                datasets: [{
                    label: 'Solicitudes de Soporte',
                    data: [<?php echo $en_espera; ?>, <?php echo $en_curso; ?>, <?php echo $solucionado; ?>], // Datos dinámicos de PHP
                    backgroundColor: ['#E53E30', '#FFD767', '#207044'], // Colores para cada estado
                    borderColor: ['#ffffff'], // Color del borde
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom', // Colocar la leyenda en la parte inferior
                    }
                }
            }
        });
    });
</script>

</div>
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

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>

</body>
<script src="scripts/script.js"></script>

</html>