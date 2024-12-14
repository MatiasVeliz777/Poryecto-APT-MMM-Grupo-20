<?php
// Conectar a la base de datos
include('conexion.php');

// Inicializamos variables para mensajes
$mensaje = '';
$tipo_mensaje = '';

// Procesar adición de un evento
if (isset($_POST['agregar_evento'])) {
    $titulo = $_POST['titulo'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $ubicacion = $_POST['ubicacion'];

    $sql = "INSERT INTO eventos (titulo, fecha, hora, ubicacion) VALUES ('$titulo', '$fecha', '$hora', '$ubicacion')";

    if ($conn->query($sql) === TRUE) {
        $mensaje = "Evento agregado exitosamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al agregar el evento: " . $conn->error;
        $tipo_mensaje = "danger";
    }
}

// Procesar eliminación de un evento
if (isset($_GET['eliminar_evento'])) {
    $id = $_GET['eliminar_evento'];
    $sql = "DELETE FROM eventos WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        $mensaje = "Evento eliminado exitosamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al eliminar el evento: " . $conn->error;
        $tipo_mensaje = "danger";
    }
}

// Procesar actualización de un evento
if (isset($_POST['actualizar_evento'])) {
    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $ubicacion = $_POST['ubicacion'];

    $sql = "UPDATE eventos SET titulo='$titulo', fecha='$fecha', hora='$hora', ubicacion='$ubicacion' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        $mensaje = "Evento actualizado exitosamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Error al actualizar el evento: " . $conn->error;
        $tipo_mensaje = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Próximos Eventos</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            margin: 20px;
        }

        .calendar, .events-list {
            width: 45%;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .calendar table {
            width: 100%;
            border-collapse: collapse;
        }

        .calendar th, .calendar td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .calendar th {
            background-color: #4CAF50;
            color: white;
        }

        .calendar td.event-day {
            background-color: #eafaf1;
            cursor: pointer;
        }

        .event-item {
            margin-bottom: 15px;
            padding: 10px;
            border-left: 4px solid #4CAF50;
            background-color: #f9f9f9;
            border-radius: 4px;
        }

        .event-item h4 {
            margin: 0;
        }

        .event-item p {
            margin: 5px 0 0;
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

        /* Modificación para que la lista de eventos sea scrollable */
        .events-list {
            max-height: 500px; /* Altura máxima de la lista de eventos */
            overflow-y: auto;  /* Scroll vertical si excede la altura */
        }
        
    </style>
</head>
<body>

<div class="wrapper">
        <aside id="sidebar">
            <div class="d-flex">
                <button class="toggle-btn" type="button">
                    <i class="lni lni-menu"></i>
                </button>
                <div class="sidebar-logo">
                    <a href="#">Portal RHH</a>
                </div>
            </div>
             <!-- Contenedor de la imagen de perfil -->
        <div class="profile-container text-center my-2">
            <img src="Images/user1_showcase.a8046c2a.png" class="img-fluid rounded-circle" alt="Foto de Perfil">

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
                            <a href="home.html" class="sidebar-link">Mis Datos</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Register</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="lni lni-agenda"></i>
                        <span>Mi Empresa</span>
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
                            <a href="#" class="sidebar-link">Empresa</a>
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
                            <a href="#" class="sidebar-link collapsed" data-bs-toggle="collapse"
                                data-bs-target="#multi-two" aria-expanded="false" aria-controls="multi-two">
                                Two Links
                            </a>
                            <ul id="multi-two" class="sidebar-dropdown list-unstyled collapse">
                                <li class="sidebar-item">
                                    <a href="#" class="sidebar-link">Link 1</a>
                                </li>
                                <li class="sidebar-item">
                                    <a href="#" class="sidebar-link">Link 2</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
                        <i class="lni lni-popup"></i>
                        <span>Capacitaciones</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link">
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
                <div class="ficha">Ficha‎ ‎ ‎ ‎16123132</div>
                
                <div class="user-nom">
                    <i class="fas fa-user"></i> <span>RODRIGO ALEJANDRO PLAZA CRUCES</span>
                </div>
                <div class="navbar"><a href="#"><i class="fa-solid fa-magnifying-glass"></i></a></div>
                <div class="user-info">
                    <img src="Images/user1_showcase.a8046c2a.png" alt="Avatar">
                    <span>16.291.388-3</span>
                    <div class="Salir"><a href="cerrar_sesion.php"><i class="fas fa-sign-out-alt"></i> Salir </a></div>
                </div>
                
        </div>
        <!-- Ruta de Navegación -->
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
    </div>
    <div class="container">
        
        <div class="calendar">
            <h2>Calendario de Eventos</h2>
            <table>
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
                    $result = $conn->query("SELECT * FROM eventos WHERE MONTH(fecha) = MONTH(CURRENT_DATE())");
                    $eventos = [];
                    while ($row = $result->fetch_assoc()) {
                        $dia = date('j', strtotime($row['fecha']));
                        $eventos[$dia][] = $row;
                    }

                    // Mostrar calendario (asumimos que empieza un lunes)
                    $primerDiaDelMes = date('N', strtotime(date('Y-m-01')));
                    $diasEnMes = date('t');

                    echo "<tr>";
                    for ($i = 1; $i < $primerDiaDelMes; $i++) {
                        echo "<td></td>";
                    }

                    for ($dia = 1; $dia <= $diasEnMes; $dia++) {
                        if (($dia + $primerDiaDelMes - 2) % 7 == 0 && $dia != 1) {
                            echo "</tr><tr>";
                        }
                        $class = isset($eventos[$dia]) ? "event-day" : "";
                        echo "<td class='$class'>$dia</td>";
                    }

                    echo "</tr>";
                    ?>
                </tbody>
            </table>
        </div>

        <div class="events-list">
            <h2>Próximos Eventos</h2>
            <?php
            foreach ($eventos as $dia => $eventos_dia) {
                foreach ($eventos_dia as $evento) {
                    echo "<div class='event-item'>";
                    echo "<h4>" . date('j \d\e F', strtotime($evento['fecha'])) . " - " . $evento['titulo'] . "</h4>";
                    echo "<p>Hora: " . date('h:i A', strtotime($evento['hora'])) . "</p>";
                    echo "<p>Ubicación: " . $evento['ubicacion'] . "</p>";
                    echo "<a class='btn btn-danger btn-sm' href='?eliminar_evento=" . $evento['id'] . "'>Eliminar</a>";
                    echo " <button class='btn btn-warning btn-sm' data-toggle='modal' data-target='#updateEventModal" . $evento['id'] . "'>Actualizar</button>";
                    echo "</div>";
                    // Modal para actualizar evento
            echo "
            <div class='modal fade' id='updateEventModal" . $evento['id'] . "' tabindex='-1' aria-labelledby='updateEventModalLabel' aria-hidden='true'>
                <div class='modal-dialog'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <h5 class='modal-title'>Actualizar Evento</h5>
                            <button type='button' class='close' data-dismiss='modal' aria-label='Cerrar'>
                                <span aria-hidden='true'>&times;</span>
                            </button>
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

            <!-- Botón para abrir el modal -->
            <button type="button" class="btn btn-primary mt-3" data-toggle="modal" data-target="#addEventModal">
                Agregar Evento
            </button>

            <!-- Modal para agregar eventos -->
            <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEventModalLabel">Agregar Evento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
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
                    <button type="submit" name="agregar_evento" class="btn btn-success">Guardar Evento</button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
</div>

    <?php $conn->close(); ?>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="script.js"></script>
    
</body>
</html>
