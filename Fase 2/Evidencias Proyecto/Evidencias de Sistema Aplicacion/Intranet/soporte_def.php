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
$sql = "SELECT rut, nombre, correo, imagen, cargo_id, rol_id
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
                    WHERE 1=1"; // Condición inicial que siempre es verdadera

// Filtrar por rol si está seleccionado
if ($rol_id_seleccionado != 0) {
    $sql_solicitudes .= " AND soportes.rol_id = '$rol_id_seleccionado'";
}

// Filtrar por estado si está seleccionado
if ($estado_seleccionado != '0') {
    $sql_solicitudes .= " AND soportes.estado = '$estado_seleccionado'";
}

// Ordenar los resultados
$sql_solicitudes .= " ORDER BY soportes.fecha_creacion DESC";

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
    <style>
        .table img {
            max-width: 80px;
            max-height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .urgencia-alta {
            color: #E53E30;
            font-weight: bold;
        }

        .urgencia-media {
            color: #FFD767;
        }

        .urgencia-baja {
            color: #207044;
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
                    <a href="home.php">Portal RHH</a>
                </div>
            </div>
             <!-- Contenedor de la imagen de perfil -->
        <div class="profile-container text-center my-2">
        <img src="<?php 
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
                        <i class="lni lni-layout"></i>
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
                            <a href="#" class="sidebar-link">Cumpleaños</a>
                        </li>
                    </ul>
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
                            <a href="cumpleaños.php" class="sidebar-link">cumpleaños</a>
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
                    <a href="solicitudes.php" class="sidebar-link">
                        <i class="lni lni-popup"></i>
                        <span>Solicitudes</span>
                    </a>
                </li>
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

        <div class="container-sop">
    <h1 class="text-center my-4">Ver Solicitudes de Soporte Informatico</h1>
    <div class="table-responsive">
    <!-- Filtro de roles -->
    <div class="filter-container">
        <form method="GET" action="">
            <label for="rol_id">Filtrar por Área:</label>
            <select name="rol_id" id="rol_id" class="form-select" onchange="this.form.submit()">
                <option value="0">Todos las Área</option>
                <?php while ($rol = $result_roles->fetch_assoc()) { ?>
                    <option value="<?php echo $rol['id']; ?>" <?php echo $rol_id_seleccionado == $rol['id'] ? 'selected' : ''; ?>>
                        <?php echo $rol['rol']; ?>
                    </option>
                <?php } ?>
            </select>
        </form>
        <!-- Filtro de estados -->
        <form method="GET" action="">
            <label for="estado">Filtrar por Estado:</label>
            <select name="estado" id="estado" class="form-select" onchange="this.form.submit()">
                <option value="0">Todos los Estados</option>
                <option value="En espera" <?php echo $estado_seleccionado == 'En espera' ? 'selected' : ''; ?>>En espera</option>
                <option value="En curso" <?php echo $estado_seleccionado == 'En curso' ? 'selected' : ''; ?>>En curso</option>
                <option value="Solucionado" <?php echo $estado_seleccionado == 'Solucionado' ? 'selected' : ''; ?>>Solucionado</option>
            </select>
        </form>
    </div>


    
    <!-- Tabla de solicitudes -->
    <div class="table-container">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th scope="col">Fecha</th>
                    <th scope="col">Titulo</th>
                    <th scope="col">Descripcion</th>
                    <th scope="col">Urgencia</th>
                    <th scope="col">Área</th>
                    <th scope="col">Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php
                include("conexion.php");

                $sql = "SELECT 
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
                        ORDER BY soportes.fecha_creacion DESC";     
                
                $result = $conn->query($sql);

                
                if ($result_solicitudes->num_rows > 0) {
                    while($row = $result_solicitudes->fetch_assoc()) {
                        // Asignamos clases de color a la urgencia
                        $urgencia_class = '';
                        if ($row['Urgencia del Soporte'] === 'alto') {
                            $urgencia_class = 'urgencia-alta';
                        } elseif ($row['Urgencia del Soporte'] === 'medio') {
                            $urgencia_class = 'urgencia-media';
                        } elseif ($row['Urgencia del Soporte'] === 'bajo') {
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

                        echo '<td>' . date('d/m/Y', strtotime($row['Fecha de Creación'])) . '</td>';
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

                $conn->close();
                ?>
</tbody>
        </table>
    </div>
    </div>
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
            <select name="estado" class="form-select d-inline w-auto" id="modal-estado-select" onchange="this.form.submit()">
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
                    <h5>Urgencia:</h5>
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

<div class="estadisticas">
<!-- Sección para el gráfico circular -->
<div id="chartContainer" class="my-4">
        <canvas id="estadoSolicitudesChart"></canvas>
    </div>
</div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

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
        const nombreUsuario = button.getAttribute('data-usuario');  // Agregamos la variable de nombre de usuario
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

        // Actualizar el nombre del usuario
        document.getElementById('modal-usuario').innerText = nombreUsuario;  // Aquí se actualiza el nombre del usuario

        // Seleccionar el estado actual
        const estadoSelect = document.getElementById('modal-estado-select');
        estadoSelect.value = estado;
    });
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

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="js/script.js"></script>
</body>

</html>