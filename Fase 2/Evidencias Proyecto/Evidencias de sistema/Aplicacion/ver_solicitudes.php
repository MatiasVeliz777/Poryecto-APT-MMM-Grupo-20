<?php
session_start();
include("conexion.php");

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

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes de Soporte</title>
    <link href="style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table img {
            max-width: 80px;
            max-height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .urgencia-alta {
            color: red;
            font-weight: bold;
        }

        .urgencia-media {
            color: orange;
        }

        .urgencia-baja {
            color: green;
        }

        /* Hacer que las filas sean clicables */
        .clickable-row {
            cursor: pointer;
        }

        /* Estilos para el perfil del usuario en el modal */
        .user-profile {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
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

        .modal-section:last-child {
            border-bottom: none;
        }

        .modal-section h5 {
            font-size: 1.1em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .modal-section p {
            font-size: 0.95em;
            margin: 0;
        }

        /* Estilo para la imagen dentro del modal */
        .modal img {
            border-radius: 8px;
            object-fit: cover;
            max-width: 100%;
            margin-top: 10px;
        }

        .table th {
            background-color: #00304A;
            color: #fff;
            text-align: center;
            font-weight: 600;
            padding: 15px;
            border-top: none;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Solicitudes de Soporte</h1>
    <div class="table-responsive">
        <!-- Filtro de roles -->
    <div class="filter-container mb-4">
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
    
    <div class="table-container">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Contenido</th>
                    <th>Urgencia</th>
                    <th>Imagen del Soporte</th>
                    <th>Fecha de Creación</th>
                    <th>Estado</th>
                    <th>Rol</th>
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
                              data-titulo="' . $row['Titulo del Soporte'] . '" 
                              data-contenido="' . $row['Contenido del Soporte'] . '" 
                              data-urgencia="' . ucfirst($row['Urgencia del Soporte']) . '" 
                              data-imagen="' . (!empty($row['Imagen del Soporte']) ? $row['Imagen del Soporte'] : 'Images/noimagen.jpg') . '" 
                              data-fecha="'   . traducirMeses(date('j F Y', strtotime($row['Fecha de Creación']))) . '" 
                              data-estado="' . $row['Estado del Soporte'] . '" 
                              data-usuario="' . $row['Nombre del Usuario'] . '" 
                              data-usuario-imagen="' . (!empty($row['Imagen del Usuario']) ? $row['Imagen del Usuario'] : 'Images/profile_photo/imagen_default.jpg') . '" 
                              data-rol="' . $row['Nombre del Rol'] . '">';
                        
                        echo '<td>' . $row['ID del Soporte'] . '</td>';
                        echo '<td>' . $row['Titulo del Soporte'] . '</td>';
                        echo '<td>' . (strlen($row['Contenido del Soporte']) > 50 ? substr($row['Contenido del Soporte'], 0, 50) . '...' : $row['Contenido del Soporte']) . '</td>';
                        echo '<td class="'.$urgencia_class.'">' . ucfirst($row['Urgencia del Soporte']) . '</td>';
                        echo '<td>';
                        if (!empty($row['Imagen del Soporte'])) {
                            echo '<img src="' . $row['Imagen del Soporte'] . '" alt="Imagen del soporte">';
                        } else {
                            echo 'No se adjunto imagen';
                        }
                        echo '</td>';  
                        echo '<td>' . date('d/m/Y', strtotime($row['Fecha de Creación'])) . '</td>';
                        echo '<td>' . $row['Estado del Soporte'] . '</td>';
                        echo '<td>' . $row['Nombre del Rol'] . '</td>';
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
</div>

<!-- Modal de Bootstrap -->
<div class="modal fade" id="solicitudModal" tabindex="-1" aria-labelledby="solicitudModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="solicitudModalLabel">Detalles de la Solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Sección del perfil del usuario -->
                <div class="user-profile">
                    <img id="modal-imagen-usuario" src="" alt="Imagen del usuario" class="img-fluid">
                    <p><strong id="modal-usuario"></strong></p>
                </div>

                <!-- Sección del título -->
                <div class="modal-section">
                    <h5>Título:</h5>
                    <p id="modal-titulo"></p>
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

                <!-- Sección de estado -->
                <div class="modal-section">
                    <h5>Estado:</h5>
                    <p id="modal-estado"></p>
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
                    <img id="modal-imagen-soporte" src="" alt="Imagen del soporte" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Llenar el modal con los datos de la fila clicada
document.querySelectorAll('.clickable-row').forEach(row => {
    row.addEventListener('click', function() {
        document.getElementById('modal-titulo').textContent = this.getAttribute('data-titulo');
        document.getElementById('modal-contenido').textContent = this.getAttribute('data-contenido');
        document.getElementById('modal-urgencia').textContent = this.getAttribute('data-urgencia');
        document.getElementById('modal-estado').textContent = this.getAttribute('data-estado');
        document.getElementById('modal-fecha').textContent = this.getAttribute('data-fecha');
        document.getElementById('modal-usuario').textContent = this.getAttribute('data-usuario');
        document.getElementById('modal-rol').textContent = this.getAttribute('data-rol');
        document.getElementById('modal-imagen-soporte').src = this.getAttribute('data-imagen') || 'Images/sin_imagen.png';
        document.getElementById('modal-imagen-usuario').src = this.getAttribute('data-usuario-imagen') || 'Images/profile_photo/imagen_default.jpg';
    });
});
</script>
</body>
</html>
