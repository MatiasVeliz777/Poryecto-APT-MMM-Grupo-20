<?php
session_start();
include("conexion.php");

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
// Obtener el usuario que ha iniciado sesión
$usuario = $_SESSION['usuario'];

// Consultar los datos del empleado en la tabla 'personal'
$sql = "SELECT rut, nombre, correo, imagen, cargo_id, rol_id
        FROM personal 
        WHERE rut = (SELECT rut FROM usuarios WHERE nombre_usuario = '$usuario')";
$result = $conn->query($sql);

// Verificar si se encontró el usuario
if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc(); // Extraer los datos del usuario
    $rol = $user_data['rol_id'];
    // Guardar el rol en la sesión
    $_SESSION['rol'] = $rol;
} else {
    $error = "No se encontraron datos para el usuario.";
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
                            roles ON soportes.rol_id = roles.id";
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
                        WHERE soportes.rol_id = '$rol_id_seleccionado'";
}

$result_solicitudes = $conn->query($sql_solicitudes);

$sql_count = "SELECT 
                 SUM(CASE WHEN estado = 'En espera' THEN 1 ELSE 0 END) AS en_espera,
                 SUM(CASE WHEN estado = 'En curso' THEN 1 ELSE 0 END) AS en_curso,
                 SUM(CASE WHEN estado = 'Solucionado' THEN 1 ELSE 0 END) AS solucionado
              FROM soportes";
              
$result_count = $conn->query($sql_count);

// Verificar si hay resultados
if ($result_count->num_rows > 0) {
    $row_count = $result_count->fetch_assoc();
    $en_espera = $row_count['en_espera'];
    $en_curso = $row_count['en_curso'];
    $solucionado = $row_count['solucionado'];
} else {
    $en_espera = $en_curso = $solucionado = 0;
}


$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Soporte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="style.css" rel="stylesheet">
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

<div class="container-sop">
    <h1 class="text-center my-4">Ver Solicitudes de Soporte</h1>
    <div class="table-responsive">
    <!-- Filtro de roles -->
    <div class="filter-container">
        <form method="GET" action="">
            <label for="rol_id">Filtrar por Rol:</label>
            <select name="rol_id" id="rol_id" class="form-select" onchange="this.form.submit()">
                <option value="0">Todos los roles</option>
                <?php while ($rol = $result_roles->fetch_assoc()) { ?>
                    <option value="<?php echo $rol['id']; ?>" <?php echo $rol_id_seleccionado == $rol['id'] ? 'selected' : ''; ?>>
                        <?php echo $rol['rol']; ?>
                    </option>
                <?php } ?>
            </select>
        </form>
    </div>

    <!-- Sección para el gráfico circular -->
    <div class="row my-4">
        <div class="col-md-6 offset-md-3">
            <canvas id="estadoSolicitudesChart"></canvas>
        </div>
    </div>


    <div class="row my-4">
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Solicitudes en espera</h5>
                <p class="card-text"><?php echo $en_espera; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Solicitudes en curso</h5>
                <p class="card-text"><?php echo $en_curso; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Solicitudes solucionadas</h5>
                <p class="card-text"><?php echo $solucionado; ?></p>
            </div>
        </div>
    </div>
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
                    <th scope="col">Rol</th>
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
                            roles ON soportes.rol_id = roles.id";
                
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
                              data-usuario-imagen="' . (!empty($row['Imagen del Usuario']) ? $row['Imagen del Usuario'] : 'Images/profile_photo/imagen_default.jpg') . '" 
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
                    <img src="Images/logo_clinica.png" alt="Imagen Permanente" class="soporte-permanent-image">
                </div>
                </div>
            </div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
