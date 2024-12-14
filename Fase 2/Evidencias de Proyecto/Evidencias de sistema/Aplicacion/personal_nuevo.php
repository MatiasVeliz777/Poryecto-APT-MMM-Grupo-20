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

$rolSESION = $_SESSION['rol'];

// Verificar si el rol es diferente de 5 y se intenta filtrar por inactivos
if ($rolSESION != 5 && $estadoSeleccionado === 0) {
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

<!DOCTYPE html>
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

        .main-content {
            display: block;
            width: 100%; /* Asegúrate de que ocupe todo el ancho */
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
        .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-content {
            flex-grow: 1; /* El contenido principal se expande para llenar el espacio */
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
    

        <!-- Contenedor de usuarios nuevos -->


        
        <?php
// Verificar si hay usuarios nuevos este mes
if ($result_nuevos->num_rows > 0) {
?>

<div class="custom-container1">
    <h2 style="width: 100%; text-align: center;">Usuarios Nuevos de este Mes</h2>
    <?php
    while ($nuevo_user = $result_nuevos->fetch_assoc()) {
        // Ruta de la imagen del usuario nuevo
        $ruta_imagen_nuevo = $carpeta_fotos . $nuevo_user['imagen'];
        
        // Verificar si la imagen del usuario nuevo existe
        $imagen_usuario_nuevo = file_exists($ruta_imagen_nuevo) ? $ruta_imagen_nuevo : $imagen_default;
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
            <button class="cards-new-employees-button">¡Bienvenido!</button>
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

    <div class="container my-4" style="display: flex; flex-direction: row; justify-content: space-EVENLY; ">
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
     $rolSESION = $_SESSION['rol'];
    if ($rolSESION == 5): ?>        
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
                <?php if ($rolSESION == 5): ?>
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
