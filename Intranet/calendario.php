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

    <!-- Bootstrap JS and dependencies (solo una referencia a la última versión) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+8I5fs5q5nWtbj+7G8DA6/DlM9xkh"
        crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        
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
            <img src="<?php echo !empty($user_data['imagen']) ? $user_data['imagen'] : 'Images/profile_photo/imagen_default.jpg'; ?>" class="img-fluid rounded-circle" alt="Foto de Perfil">

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
                    <a href="#" class="sidebar-link">
                        <i class="lni lni-agenda"></i>
                        <span>Capacitaciones</span>
                    </a>
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
                            <a href="#" class="sidebar-link">cumpleaños</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#multi" aria-expanded="false" aria-controls="multi">
                        <i class="lni lni-layout"></i>
                        <span>Documentos</span>
                    </a>
                    <ul id="multi" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Reglamento interno</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Contratos</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Protocolos</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Manuales</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="lni lni-popup"></i>
                        <span>Foro</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#multi" aria-expanded="false" aria-controls="multi">
                        <i class="lni lni-layout"></i>
                        <span>Personal</span>
                    </a>
                    <ul id="multi" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Empleado del mes</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Nuevos empleados</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Cumpleaños</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="solicitud.php" class="sidebar-link">
                        <i class="lni lni-popup"></i>
                        <span>Solicitudes</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="soporte.php" class="sidebar-link">
                        <i class="lni lni-cog"></i>
                        <span>Soporte tecnico</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="#" class="sidebar-link">
                    <i class="lni lni-exit"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>
        <div class="main p-3">
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
        <!-- Aqui termina el menu -->

        <!-- Aqui Empieza el calendario -->
    <div class="mensaje-popup">
        
        <!-- Mostrar alerta si hay un mensaje -->
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensaje; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
                
            </div>
        <?php endif; ?>
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
                <h3><?php echo strftime('%B', mktime(0, 0, 0, $mes, 10)) . " " . $ano; ?></h3>
                <a class="btn btn-outline-secondary" href="?mes=<?php echo ($mes == 12) ? 1 : $mes + 1; ?>&ano=<?php echo ($mes == 12) ? $ano + 1 : $ano; ?>">Siguiente</a>
            </div>

            <table class="table table-bordered">
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
                    $stmt = $conn->prepare("SELECT * FROM eventos WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?");
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
            echo "<div class='event-item'>";
            echo "<div class='event-header'>";
            echo "<div class='event-date'>" . date('j \d\e F', strtotime($evento['fecha'])) . "</div>";
            echo "<div class='event-title'><h4>"  . $evento['titulo'] . "</h4></div>";
            echo "<div class='event-actions'>";
            if ($_SESSION['cargo_id'] == 26 ) {
            echo "<a class='btn btn-outline-danger btn-sm' href='?eliminar_evento=" . $evento['id'] . "&mes=$mes&ano=$ano'>
                <svg xmlns='http://www.w3.org/2000/svg' width='17' height='20' fill='currentColor' class='bi bi-dash-circle' viewBox='0 0 16 16'>
                    <path d='M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16'/>
                    <path d='M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8'/>
                </svg> 
                </a>";
            echo "<button class='btn btn-outline-dark btn-sm' data-bs-toggle='modal' data-bs-target='#updateEventModal" . $evento['id'] . "'>
                <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-arrow-clockwise' viewBox='0 0 16 16'>
                    <path fill-rule='evenodd' d='M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z'/>
                    <path d='M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466'/>
                </svg>
                </button>";
                ;
            }
            echo "</div>"; // end event-actions
            echo "</div>"; // end event-header
            echo "<p>Hora: " . date('h:i A', strtotime($evento['hora'])) . "</p>";
            echo "<p>Ubicación: " . $evento['ubicacion'] . "</p>";
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
                                <div class='form-group' style='margin-bottom: 10px;'>
                                    <label for='titulo'>Título del Evento</label>
                                    <input type='text' class='form-control' id='titulo' name='titulo' value='" . $evento['titulo'] . "' required>
                                </div>
                                <div class='form-group'style='margin-bottom: 10px;'>
                                    <label for='fecha'>Fecha</label>
                                    <input type='date' class='form-control' id='fecha' name='fecha' value='" . $evento['fecha'] . "' required>
                                </div>
                                <div class='form-group'style='margin-bottom: 10px;'>
                                    <label for='hora'>Hora</label>
                                    <input type='time' class='form-control' id='hora' name='hora' value='" . $evento['hora'] . "' required>
                                </div>
                                <div class='form-group'style='margin-bottom: 10px;'>
                                    <label for='ubicacion'>Ubicación</label>
                                    <input type='text' class='form-control' id='ubicacion' name='ubicacion' value='" . $evento['ubicacion'] . "' required>
                                </div>
                                <button type='submit' name='actualizar_evento' class='btn btn-success'>Actualizar Evento</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>";
            echo "</div>"; // end event-item
        }
    }
    ?>
</div>

        <!-- Modal para agregar eventos -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEventModalLabel">Agregar Evento</h5>
                <!-- Cambié el botón de cierre para Bootstrap 5 -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- Imagen del logo de la clínica -->
                <div class="text-center mb-4">
                    <img src="Images/logo_clinica.png" alt="Logo de la Clínica" style="max-width: 150px;">
                </div>

                <!-- Formulario para agregar eventos -->
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
                    <div class="button-container mt-4">
                        <button type="submit" name="agregar_evento" class="btn btn-outline-primary">Guardar Evento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</div>


    <?php $conn->close(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="js/script.js"></script>
</body>
</html>
