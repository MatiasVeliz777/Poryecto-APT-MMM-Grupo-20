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

// Procesar adición de un evento
if (isset($_POST['agregar_evento'])) {
    $titulo = $_POST['titulo'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $ubicacion = $_POST['ubicacion'];

    $sql = "INSERT INTO eventos (titulo, fecha, hora, ubicacion) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $titulo, $fecha, $hora, $ubicacion);

    if ($stmt->execute()) {
        $mensaje = "Evento agregado exitosamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al agregar el evento: " . $conn->error;
        $tipo_mensaje = "danger";
    }
    $stmt->close();
}

// Procesar eliminación de un evento
if (isset($_GET['eliminar_evento'])) {
    $id = intval($_GET['eliminar_evento']);
    $stmt = $conn->prepare("DELETE FROM eventos WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $mensaje = "Evento eliminado exitosamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al eliminar el evento: " . $conn->error;
        $tipo_mensaje = "danger";
    }
    $stmt->close();
}

// Procesar actualización de un evento
if (isset($_POST['actualizar_evento'])) {
    $id = intval($_POST['id']);
    $titulo = $_POST['titulo'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $ubicacion = $_POST['ubicacion'];

    $stmt = $conn->prepare("UPDATE eventos SET titulo = ?, fecha = ?, hora = ?, ubicacion = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $titulo, $fecha, $hora, $ubicacion, $id);

    if ($stmt->execute()) {
        $mensaje = "Evento actualizado exitosamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al actualizar el evento: " . $conn->error;
        $tipo_mensaje = "danger";
    }
    $stmt->close();
}

if (isset($_POST['registrar_asistencia'])) {
    $evento_id = $_POST['evento_id'];
    $rut_usuario = $_SESSION['rut'];

    // Verificar si el usuario ya se registró en este evento
    $check_sql = "SELECT * FROM asistencias_eventos WHERE evento_id = ? AND rut_usuario = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("is", $evento_id, $rut_usuario);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $mensaje = "Ya estás registrado para este evento.";
        $tipo_mensaje = "warning";
    } else {
        // Registrar asistencia
        $sql = "INSERT INTO asistencias_eventos (evento_id, rut_usuario) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $evento_id, $rut_usuario);

        if ($stmt->execute()) {
            $mensaje = "Te has registrado exitosamente para el evento.";
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
<html lang="es">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Próximos Eventos</title>
    <link rel="stylesheet" href="styles/style_calendar.css">
    <!-- Bootstrap CSS (solo una referencia a la última versión) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    
    <!-- Lineicons -->
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/style_calendar.css">
    


        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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

        <!-- Aqui Empieza el calendario -->
    <div class="mensaje-popup">
        
        <div class="titulo-eventos">
        <h2>Calendario de Eventos</h2>
        </div>
    </div>
    
    <div class="container">
        <div class="calendar">
            <div class="titulo-calendar">
                <h2>Calendario de Eventos</h2>
            </div>
            <!-- Botones para cambiar de mes -->
            <div class="d-flex justify-content-between">
                <a class="btn btn-outline-secondary"  href="?mes=<?php echo ($mes == 1) ? 12 : $mes - 1; ?>&ano=<?php echo ($mes == 1) ? $ano - 1 : $ano; ?>">Anterior</a>
                <?php
                    // Obtener el nombre del mes en inglés
                    $mes_en_ingles = strftime('%B', mktime(0, 0, 0, $mes, 10));

                    // Traducir el mes al español usando la función traducir_mes
                    $mes_traducido = traducir_mes_mes($mes_en_ingles);

                    // Mostrar el mes traducido y el año
                    echo "<h3>" . $mes_traducido . " " . $ano . "</h3>";
                    ?>
                <a class="btn btn-outline-secondary" href="?mes=<?php echo ($mes == 12) ? 1 : $mes + 1; ?>&ano=<?php echo ($mes == 12) ? $ano + 1 : $ano; ?>">Siguiente</a>
            </div>

            <table class="table table-bordered">
                <div class="table-responsive">
                <thead>
                    <tr>
                        <th>Lun</th>
                        <th>Mar</th>
                        <th>Mié</th>
                        <th>Jue</th>
                        <th>Vie</th>
                        <th>Sáb</th>
                        <th>Dom</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Obtener eventos del mes actual
                    $stmt = $conn->prepare("SELECT * FROM eventos WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? ORDER BY fecha ASC");
                    $stmt->bind_param("ii", $mes, $ano);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    $eventos = [];
                    while ($row = $result->fetch_assoc()) {
                        $dia = date('j', strtotime($row['fecha']));
                        $eventos[$dia][] = $row;
                    }

                    $stmt->close();

                    // Mostrar calendario (asumimos que empieza un lunes)
                    $primerDiaDelMes = date('N', strtotime("$ano-$mes-01"));
                    $diasEnMes = date('t', strtotime("$ano-$mes-01"));

                    echo "<tr>";
                    for ($i = 1; $i < $primerDiaDelMes; $i++) {
                        echo "<td></td>"; // Días en blanco hasta el primer día del mes
                    }

                    for ($dia = 1; $dia <= $diasEnMes; $dia++) {
                        if (($dia + $primerDiaDelMes - 2) % 7 == 0 && $dia != 1) {
                            echo "</tr><tr>"; // Inicia una nueva fila cada semana
                        }
 
                        
                        $class = isset($eventos[$dia]) ? "event-day" : "";
                        echo "<td class='$class'>$dia</td>";
                    }

                    echo "</tr>";
                    ?>
                </tbody>
            </table>
            
            <!-- Botón para abrir el modal -->
            <?php if ($rol == 5) {
        echo "<button type=\"button\" class=\"btn btn-outline-primary mt-2\" data-bs-toggle=\"modal\" data-bs-target=\"#addEventModal\">
        <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"25\" height=\"25\" fill=\"currentColor\" class=\"bi bi-plus-circle mr-1\" viewBox=\"0 0 16 16\">
            <path d=\"M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16\"/>
            <path d=\"M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4\"/>
        </svg>
        Agregar Evento
      </button>";
;
}

?>
        </div>
        

        <div class="events-list">
    <h2>Próximos Eventos</h2>
    <?php
    foreach ($eventos as $dia => $eventos_dia) {
        foreach ($eventos_dia as $evento) {
            // Contenedor general del evento con clase clickeable
            echo "<div class='event-item clickeable' onclick=\"location.href='evento_asistencia.php?evento_id=" . $evento['id'] . "'\" style='cursor: pointer;'>";

            echo "<div class='event-header'>";
            echo "<div class='event-date'>" . $fecha_traducida = traducir_fecha($evento['fecha']) . "</div>";
            // Limitar el título a 40 caracteres
            $titulo = (strlen($evento['titulo']) > 30) ? substr($evento['titulo'], 0, 25) . '...' : $evento['titulo'];

            // Limitar la ubicación a 40 caracteres
            $ubicacion = (strlen($evento['ubicacion']) > 30) ? substr($evento['ubicacion'], 0, 37) . '...' : $evento['ubicacion'];

            // Mostrar el contenido con el límite aplicado
            echo "<div class='event-title'><h4>" . $titulo . "</h4></div>";
            echo "</div>"; // end event-header

            echo "<p>Hora: " . date('h:i A', strtotime($evento['hora'])) . "</p>";
            echo "<p>Ubicación: " . $ubicacion . "</p>";
            // Div para botones de acciones (modificar/eliminar) solo si tiene permisos
            echo "<div class='event-actions'>";

            if ($_SESSION['cargo_id'] == 26) {
                // Botón para eliminar el evento con confirmación y stopPropagation
                echo "<button class='btn btn-outline-danger btn-sm' onclick=\"event.stopPropagation(); confirmarEliminacion('" . $evento['id'] . "')\">
                    <svg xmlns='http://www.w3.org/2000/svg' width='17' height='20' fill='currentColor' class='bi bi-dash-circle' viewBox='0 0 16 16'>
                        <path d='M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16'/>
                        <path d='M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8'/>
                    </svg>
                    </button>";

                // Botón para modificar el evento (abrir modal) con stopPropagation()
                echo "<button class='btn btn-outline-dark btn-sm' data-bs-toggle='modal' data-bs-target='#updateEventModal" . $evento['id'] . "' onclick='event.stopPropagation();'>
                    <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-arrow-clockwise' viewBox='0 0 16 16'>
                        <path fill-rule='evenodd' d='M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z'/>
                        <path d='M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466'/>
                    </svg>
                    </button>";
            }

            // Mostrar botón para registrar asistencia
            // Obtener la fecha actual
            $fecha_actual = date("Y-m-d");

            // Consulta para verificar si el usuario ya está registrado y obtener la fecha del evento
            $evento_id = $evento['id'];
            $rut_usuario = $_SESSION['rut'];

            $check_sql = "SELECT fecha FROM eventos WHERE id = ? AND fecha >= ?";
            $stmt_check = $conn->prepare($check_sql);
            $stmt_check->bind_param("is", $evento_id, $fecha_actual);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                // Verificar si el usuario ya está registrado en este evento
                $check_asistencia_sql = "SELECT * FROM asistencias_eventos WHERE evento_id = ? AND rut_usuario = ?";
                $stmt_check_asistencia = $conn->prepare($check_asistencia_sql);
                $stmt_check_asistencia->bind_param("is", $evento_id, $rut_usuario);
                $stmt_check_asistencia->execute();
                $result_check_asistencia = $stmt_check_asistencia->get_result();

                if ($result_check_asistencia->num_rows == 0) {
                    // Si el usuario no está registrado y el evento es futuro, muestra el botón "Asistir"
                    echo "<form method='POST'>";
                    echo "<input type='hidden' name='evento_id' value='" . $evento['id'] . "'>";
                    echo "<button type='submit' name='registrar_asistencia' class='btn btn-outline-primary btn-sm' onclick='event.stopPropagation();'>Asistir</button>";
                    echo "</form>";
                }

                $stmt_check_asistencia->close();
            }

            $stmt_check->close();
            
            echo "</div>"; // end event-actions

            echo "</div>"; // end event-item
        }
    }
    ?>
</div>

<!-- Modal para agregar evento -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="titulo">Título del Evento</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" required>
                    </div>
                    <div class="form-group">
                        <label for="hora">Hora</label>
                        <input type="time" class="form-control" id="hora" name="hora" required>
                    </div>
                    <div class="form-group">
                        <label for="ubicacion">Ubicación</label>
                        <input type="text" class="form-control" id="ubicacion" name="ubicacion" required>
                    </div>
                    <button type="submit" name="agregar_evento" class="btn btn-primary">Guardar Evento</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modales fuera del contenedor de eventos -->
<?php
foreach ($eventos as $dia => $eventos_dia) {
    foreach ($eventos_dia as $evento) {
        // Modal para actualizar el evento, ahora fuera del contenedor clickeable
        echo "<div class='modal fade' id='updateEventModal" . $evento['id'] . "' tabindex='-1' aria-labelledby='updateEventModalLabel' aria-hidden='true'>
            <div class='modal-dialog'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title'>Actualizar Evento</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Cerrar'></button>
                    </div>
                    <div class='modal-body'>
                        <form method='POST'>
                            <input type='hidden' name='id' value='" . $evento['id'] . "'>
                            <div class='form-group'>
                                <label for='titulo'>Título del Evento</label>
                                <input type='text' class='form-control' id='titulo' name='titulo' value='" . $evento['titulo'] . "' required>
                            </div>
                            <div class='form-group'>
                                <label for='fecha'>Fecha</label>
                                <input type='date' class='form-control' id='fecha' name='fecha' value='" . $evento['fecha'] . "' required>
                            </div>
                            <div class='form-group'>
                                <label for='hora'>Hora</label>
                                <input type='time' class='form-control' id='hora' name='hora' value='" . $evento['hora'] . "' required>
                            </div>
                            <div class='form-group'>
                                <label for='ubicacion'>Ubicación</label>
                                <input type='text' class='form-control' id='ubicacion' name='ubicacion' value='" . $evento['ubicacion'] . "' required>
                            </div>
                            <button type='submit' name='actualizar_evento' class='btn btn-success'>Actualizar Evento</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>";
    }
}
?>

<!-- Script para SweetAlert2 -->
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



</div>


    <?php $conn->close(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="script.js"></script>
</body>
</html>
