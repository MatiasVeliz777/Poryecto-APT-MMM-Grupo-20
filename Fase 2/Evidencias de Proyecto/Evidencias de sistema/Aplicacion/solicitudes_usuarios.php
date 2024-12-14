<?php
// Conectar a la base de datos
include("conexion.php");
session_start();
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); // Redirigir al login si no ha iniciado sesión
    exit();
}

$error = "";

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

// Procesar la solicitud cuando se envía el formulario
$solicitudEnviada = false;
$errorAlGuardar = false; // Variable para manejar los errores

// Función para cargar las áreas
function cargarAreas($conn) {
    $sql = "SELECT id, nombre_area FROM soli_areas";
    $result = $conn->query($sql);
    $areas = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $areas[] = $row;
        }
    }
    return $areas;
}

// Función para cargar las categorías basadas en el área seleccionada
function cargarCategorias($conn, $id_area) {
    $sql = "SELECT id, nombre_categoria FROM soli_categorias WHERE id_area = $id_area";
    $result = $conn->query($sql);
    $categorias = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }
    }
    return $categorias;
}

// Función para cargar los sub-servicios basados en la categoría seleccionada
function cargarSubServicios($conn, $id_categoria) {
    $sql = "SELECT id, nombre_sub_servicio FROM soli_servicios WHERE id_categoria = $id_categoria";
    $result = $conn->query($sql);
    $sub_servicios = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sub_servicios[] = $row;
        }
    }
    return $sub_servicios;
}

// Si se hace una solicitud AJAX para categorías o sub-servicios, procesarla aquí
if (isset($_GET['id_area'])) {
    echo json_encode(cargarCategorias($conn, $_GET['id_area']));
    exit;
}

if (isset($_GET['id_categoria'])) {
    echo json_encode(cargarSubServicios($conn, $_GET['id_categoria']));
    exit;
}

// Cargar las áreas para mostrarlas en el HTML
$areas = cargarAreas($conn);

// Mensaje de error por defecto
$error_message = "";

// Obtener el RUT del usuario autenticado desde la sesión
$rut_usuario = $_SESSION['rut']; // Asegúrate de que esta variable exista y tenga el formato correcto

// Si el formulario es enviado, procesar los datos
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir los datos del formulario
    $id_area = $_POST['area'];
    $id_categoria = $_POST['categoria'];
    $id_sub_servicio = $_POST['sub_servicio'];
    $comentarios = $_POST['comentarios'];
    $fecha_hora = date('Y-m-d H:i:s'); // Obtener la fecha y hora actual

    // Validar que todos los campos estén completos
    if (empty($id_area) || empty($id_categoria) || empty($id_sub_servicio)) {
        $error_message = "Por favor, complete todos los campos del formulario.";
    } else {
        // Inserción en la base de datos
        $sql = "INSERT INTO solicitudes (rut, id_area, id_categoria, id_sub_servicio, comentarios, id_rol, fecha_hora) 
                VALUES ('$rut_usuario', '$id_area', '$id_categoria', '$id_sub_servicio', '$comentarios', $rol, '$fecha_hora')"; // Ajusta el RUT y el id_rol según lo que necesites

        if ($conn->query($sql) === TRUE) {
            $error_message = "Solicitud registrada con éxito.";
        } else {
            $error_message = "Error al registrar la solicitud: " . $conn->error;
        }
    }
}


// Si se ha seleccionado un área para filtrar
$areaSeleccionada = isset($_GET['area']) ? $_GET['area'] : '';

$sql_soli = "SELECT 
                solicitudes.id, 
                usuarios.rut, 
                soli_areas.nombre_area, 
                soli_categorias.nombre_categoria, 
                soli_servicios.nombre_sub_servicio, 
                solicitudes.comentarios, 
                solicitudes.fecha_hora, 
                personal.nombre AS NombreUsuario, 
                personal.imagen AS ImagenUsuario
            FROM solicitudes
            INNER JOIN usuarios ON solicitudes.rut = usuarios.rut
            INNER JOIN soli_areas ON solicitudes.id_area = soli_areas.id
            INNER JOIN soli_categorias ON solicitudes.id_categoria = soli_categorias.id
            INNER JOIN soli_servicios ON solicitudes.id_sub_servicio = soli_servicios.id
            INNER JOIN personal ON usuarios.rut = personal.rut
            LEFT JOIN soli_respuestas ON solicitudes.id = soli_respuestas.solicitud_id
            WHERE soli_respuestas.solicitud_id IS NULL";

// Añadir el filtro de área si se seleccionó
if (!empty($areaSeleccionada)) {
    $sql_soli .= " AND solicitudes.id_area = '$areaSeleccionada'";
}

$sql_soli .= " ORDER BY solicitudes.fecha_hora DESC";

$result = $conn->query($sql_soli);

if ($result === false) {
    echo "Error en la consulta SQL: " . $conn->error;
}



// Consulta para obtener todas las solicitudes y verificar si están respondidas
$sql_solicitudes = "SELECT solicitudes.id, usuarios.rut, soli_areas.nombre_area, soli_categorias.nombre_categoria, soli_servicios.nombre_sub_servicio, 
                           solicitudes.comentarios, solicitudes.fecha_hora, respuestas.respuesta
                    FROM solicitudes
                    INNER JOIN usuarios ON solicitudes.rut = usuarios.rut
                    INNER JOIN soli_areas ON solicitudes.id_area = soli_areas.id
                    INNER JOIN soli_categorias ON solicitudes.id_categoria = soli_categorias.id
                    INNER JOIN soli_servicios ON solicitudes.id_sub_servicio = soli_servicios.id
                    LEFT JOIN respuestas ON solicitudes.id = respuestas.solicitud_id
                    WHERE usuarios.rut = '$rut_usuario'
                    ORDER BY solicitudes.fecha_hora DESC";

// Añadir la condición del filtro si hay un área seleccionada
if (!empty($areaSeleccionada)) {
    $sql_solicitudes .= " AND solicitudes.id_area = '$areaSeleccionada'";
}

$result_solicitudes = $conn->query($sql_solicitudes);

$conn->close();
?>

<!DOCTYPE php>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud</title>
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
<style>
    .card-body {
        flex-grow: 1;
        padding: 20px;
        max-height: 650px; /* Limitar la altura máxima del cuerpo de la tarjeta */
        overflow: auto; /* Hacer que el contenido restante sea scrolleable si excede el límite */
        scrollbar-width: none; /* Para Firefox */
    }
    .form-group{
        margin: 20px;
    }    
    .solicitud-container{
        width: 1800px;
    }
    .table {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Sombra ligera para dar un efecto flotante */
        border-radius: 8px; /* Bordes redondeados */
        overflow: hidden;
    }
    .table th, .table td {
        vertical-align: middle;
        text-align: center;
        padding:10px;
        font-size: 0.9em;
    }

    .table td {
        padding:8px;
        font-size: 1em;
    }
    .table-hover tbody tr:hover {
        background-color: #f1f1f1; /* Color más claro al pasar el ratón */
        cursor: pointer;
    }
    .thead-dark th {
        background-color: #343a40;
        color: white;
    }
    .text-center {
        text-align: center;
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
    
    
</style>


</head>
<body>
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
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

        <header class="solicitud-header">
    <h1>Solicitudes de los usuarios</h1>
</header>


<div class="solicitud-container-wrapper" style="margin-bottom: 50px; width: auto; max-width:90%;">


    <div class="solicitud-container">
    <label for="area" style="margin-left: 300px">Filtrar por Área</label>
 
    <!-- Formulario de filtro por área -->
  <form method="GET" action="">
    
    <div class="form-group" style="margin-top: 0px; display: flex; justify-content: space-evenly;">
      <select id="area" name="area" class="form-control" style="width: 40%;">
        <option value="">Todas las áreas</option>
        <?php foreach ($areas as $area): ?>
          <option value="<?php echo $area['id']; ?>" <?php if ($areaSeleccionada == $area['id']) echo 'selected'; ?>>
            <?php echo $area['nombre_area']; ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary" style="">Filtrar</button>

    </div>
    
  </form>    


 <!-- Tabla responsiva para mostrar las solicitudes -->
 <div class="table-responsive">
 <h3>Solicitudes Pendientes</h3>
    <table class="table table-striped table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Fecha y hora</th>
                <th>Área</th>
                <th>Categoría</th>
                <th>Sub-servicio</th>
                <th>Comentarios</th>
                
                <th>Responder</th> <!-- Columna para el botón de respuesta -->
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): 

                    $imagen_user_sop = $row['ImagenUsuario']; // Se asume que este campo contiene solo el nombre del archivo

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
                    ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_hora'])); ?></td>
                        <td><?php echo $row['nombre_area']; ?></td>
                        <td><?php echo $row['nombre_categoria']; ?></td>
                        <td><?php echo $row['nombre_sub_servicio']; ?></td>
                        <td>
                            <?php 
                            $comentarios = htmlspecialchars($row['comentarios']); 
                            echo strlen($comentarios) > 35 ? substr($comentarios, 0, 35) . '...' : $comentarios;
                            ?>
                        </td>
                        <td>
                            <!-- Botón para abrir el modal -->
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalResponder" 
                                    data-id="<?php echo $row['id']; ?>" 
                                    data-rut="<?php echo $row['rut']; ?>" 
                                    data-area="<?php echo $row['nombre_area']; ?>" 
                                    data-categoria="<?php echo $row['nombre_categoria']; ?>" 
                                    data-subservicio="<?php echo $row['nombre_sub_servicio']; ?>" 
                                    data-comentarios="<?php echo $comentarios; ?>" 
                                    data-nombre="<?php echo $row['NombreUsuario']; ?>"
                                    data-img="<?php echo $imagen_final_user; ?>"
                                    data-fecha="<?php echo date('d/m/Y H:i', strtotime($row['fecha_hora'])); ?>">
                                Responder
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No se encontraron solicitudes.</td>
                </tr>
            <?php 
        endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para responder a la solicitud -->
<div class="modal fade" id="modalResponder" tabindex="-1" role="dialog" aria-labelledby="modalResponderLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
      <div class="user-profile">
                    <img id="modal-imagen-usuario" src="" alt="Imagen del usuario" class="img-fluid rounded-circle me-3" style="width: 60px;">
                    <p><strong id="modal-usuario"></strong></p>
                </div>
        <button type="button"  class="btn-close" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Aquí se mostrarán los detalles de la solicitud -->
        <form action="responder_solicitud.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="solicitud_id" id="solicitud_id" />

          <div class="form-group" style="margin-top:0px;">
            <label for="area_solicitud">Área:</label>
            <input type="text" class="form-control" id="area_solicitud" readonly>
          </div>
          <div class="form-group">
            <label for="categoria_solicitud">Categoría:</label>
            <input type="text" class="form-control" id="categoria_solicitud" readonly>
          </div>
          <div class="form-group">
            <label for="subservicio_solicitud">Sub-servicio:</label>
            <input type="text" class="form-control" id="subservicio_solicitud" readonly>
          </div>
          <div class="form-group">
            <label for="comentarios_solicitud">Comentarios:</label>
            <textarea  class="form-control" id="comentarios_solicitud" readonly></textarea>
          </div>
          <div class="form-group">
            <label for="respuesta_texto">Respuesta:</label>
            <textarea name="respuesta_texto" class="form-control" placeholder="Escribe una respuesta"></textarea>
          </div>
          <div class="form-group">
            <label for="archivo_respuesta">Subir archivo (opcional):</label>
            <input type="file" name="archivo_respuesta" class="form-control" />
          </div>
          <button type="submit" class="btn btn-success">Enviar respuesta</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
    // Llenar el modal con los datos de la solicitud seleccionada
$('#modalResponder').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Botón que activó el modal
    var id = button.data('id');
    var rut = button.data('rut');
    var area = button.data('area');
    var categoria = button.data('categoria');
    var subservicio = button.data('subservicio');
    var comentarios = button.data('comentarios');
    var fecha = button.data('fecha');
    var nombre = button.data('nombre');
    var img = button.data('img');

    var modal = $(this);
    modal.find('#solicitud_id').val(id);
    modal.find('#area_solicitud').val(area);
    modal.find('#categoria_solicitud').val(categoria);
    modal.find('#subservicio_solicitud').val(subservicio);
    modal.find('#comentarios_solicitud').val(comentarios);
    document.getElementById('modal-imagen-usuario').src = img;
    document.getElementById('modal-usuario').innerText = nombre;
});

</script>


<?php
include("conexion.php");
// Obtener el RUT del usuario autenticado
$rut_usuario = $_SESSION['rut'];

// Si se ha seleccionado un área para filtrar
$areaSeleccionada = isset($_GET['area']) ? $_GET['area'] : '';

// Consulta para obtener todas las solicitudes respondidas, sin filtrar por usuario
$sql_respondidas = "SELECT solicitudes.id AS solicitud_id, personal.rut, soli_areas.nombre_area,
    soli_categorias.nombre_categoria, soli_servicios.nombre_sub_servicio,
    solicitudes.comentarios, solicitudes.fecha_hora, soli_respuestas.respuesta_texto, soli_respuestas.archivo,
    soli_respuestas.fecha_respuesta, soli_respuestas.id AS respuesta_id, personal.nombre, personal.imagen AS imgUser 
    FROM solicitudes 
    INNER JOIN personal ON solicitudes.rut = personal.rut 
    INNER JOIN soli_areas ON solicitudes.id_area = soli_areas.id 
    INNER JOIN soli_categorias ON solicitudes.id_categoria = soli_categorias.id
    INNER JOIN soli_servicios ON solicitudes.id_sub_servicio = soli_servicios.id 
    INNER JOIN soli_respuestas ON solicitudes.id = soli_respuestas.solicitud_id";
            
    

$result_respondidas = $conn->query($sql_respondidas);

// Añadir el filtro de área si se seleccionó
if (!empty($areaSeleccionada)) {
    $sql_respondidas .= " WHERE solicitudes.id_area = '$areaSeleccionada'";
}

$sql_respondidas .= " ORDER BY solicitudes.fecha_hora DESC";

$result_respondidas = $conn->query($sql_respondidas);

if ($result_respondidas === false) {
    echo "Error en la consulta SQL: " . $conn->error;
}
?>

<!-- Tabla para mostrar todas las solicitudes respondidas -->
<div class="table-responsive">
    <h3>Solicitudes Respondidas</h3>
    <table class="table table-striped table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>Fecha de Respuesta</th>
                <th>Área</th>
                <th>Categoría</th>
                <th>Sub-servicio</th>
                <th>Respuesta</th>
                
            </tr>
        </thead>
        <tbody>
            <?php if ($result_respondidas->num_rows > 0): ?>
                <?php while ($row_resp = $result_respondidas->fetch_assoc()): 
                        $imagen_user_sop = $row_resp['imgUser']; // Nombre del archivo de imagen

                        // Diagnóstico: Verificar el valor de $imagen_user_sop y $carpeta_fotos
                        
                        // Construir la ruta completa de la imagen del usuario
                        $ruta_imguser_sop1 = $carpeta_fotos . $imagen_user_sop;
                        
                        // Diagnóstico: Verificar la ruta final de la imagen
                        
                        // Verificar si la imagen del usuario existe en la carpeta
                        if (file_exists($ruta_imguser_sop1)) {
                            $imagen_final_user = $ruta_imguser_sop1;
                        } else {
                            $imagen_final_user = $imagen_default;
                        }
                        
                        // Mostrar la imagen final asignad                        
                    // Otros datos de la respuesta
                        $respuestaId = $row_resp['respuesta_id'];
                        $archivo = $row_resp['archivo']; // Suponiendo que tienes el nombre del archivo en tu tabla y query
                        ?>
                        <tr onclick="openModal(
                        '<?php echo $row_resp['rut']; ?>', 
                        '<?php echo addslashes($row_resp['nombre_area']); ?>', 
                        '<?php echo addslashes($row_resp['nombre_categoria']); ?>', 
                        '<?php echo addslashes($row_resp['nombre_sub_servicio']); ?>', 
                        '<?php echo addslashes($row_resp['comentarios']); ?>', 
                        '<?php echo addslashes($row_resp['respuesta_texto']); ?>', 
                        '<?php echo date('d/m/Y H:i', strtotime($row_resp['fecha_respuesta'])); ?>',
                        '<?php echo $row_resp['respuesta_id']; ?>',  // Asegúrate de que este campo exista en tu consulta SQL
                        '<?php echo addslashes($row_resp['archivo']); ?>', 
                        '<?php echo addslashes($row_resp['nombre']); ?>',  // Ajusta si tienes el nombre del archivo en tu tabla
                        '<?php echo $imagen_final_user; ?>'  // Ajusta si tienes el nombre del archivo en tu tabla
                    )">
                        <td><?php echo date('d/m/Y H:i', strtotime($row_resp['fecha_respuesta'])); ?></td>
                        <td><?php echo $row_resp['nombre_area']; ?></td>
                        <td><?php echo $row_resp['nombre_categoria']; ?></td>
                        <td><?php echo $row_resp['nombre_sub_servicio']; ?></td>
                        
                        <td>
                            <?php 
                            $respuesta = htmlspecialchars($row_resp['respuesta_texto']); 
                            echo strlen($respuesta) > 40 ? substr($respuesta, 0, 40) . '...' : $respuesta;
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No se encontraron solicitudes respondidas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para ver detalles de la solicitud respondida -->
<div class="modal fade" id="modalDetalles" tabindex="-1" role="dialog" aria-labelledby="modalDetallesLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <div class="user-profile">
            <img id="modalimg" src="" alt="" class="img-fluid rounded-circle me-3" style="width: 60px;">
            <p><strong></strong> <span id="modalName"></span></p>
            </div>                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Área:</strong> <span id="modalArea"></span></p>
                <p><strong>Categoría:</strong> <span id="modalCategoria"></span></p>
                <p><strong>Sub-servicio:</strong> <span id="modalSubservicio"></span></p>
                <p style="word-wrap: break-word; white-space: normal; max-height: 150px; overflow-y: auto;"><strong>Comentarios:</strong> <span id="modalComentarios"></span></p>
                <p style="word-wrap: break-word; white-space: normal; max-height: 150px; overflow-y: auto;"><strong>Respuesta:</strong> <span id="modalRespuesta"></span></p>
                <p><strong>Fecha de Respuesta:</strong> <span id="modalFechaRespuesta"></span></p>
                <p><strong>Archivo:</strong> <span id="modalArchivo"></span></p> <!-- Sección para el archivo -->
            </div>
            <div class="modal-footer">
                <form id="formEliminarRespuesta">
                    <input type="hidden" name="id" id="inputRespuestaId">
                    <input type="hidden" name="archivo" id="inputArchivoRespuesta">
                    <button type="button" class="btn btn-danger" onclick="confirmarEliminacionRespuesta()">Eliminar Respuesta</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </form>
            </div>
        </div>
    </div>
</div>


  
</div>

<script>
function openModal(rut, area, categoria, subservicio, comentarios, respuesta, fechaRespuesta, id, archivo, nombre, img) {
    // Cargar los datos en el modal
    document.getElementById('modalimg').src = img;
    document.getElementById('modalName').innerText = nombre;
    document.getElementById('modalArea').innerText = area;
    document.getElementById('modalCategoria').innerText = categoria;
    document.getElementById('modalSubservicio').innerText = subservicio;
    document.getElementById('modalComentarios').innerText = comentarios;
    document.getElementById('modalRespuesta').innerText = respuesta;
    document.getElementById('modalFechaRespuesta').innerText = fechaRespuesta;
    
    
    // Asignar el ID de la respuesta y el archivo al formulario de eliminación
    document.getElementById('inputRespuestaId').value = id;
    document.getElementById('inputArchivoRespuesta').value = archivo;
    
    // Mostrar el archivo si existe, o mostrar mensaje "No se adjuntó archivo"
const modalArchivo = document.getElementById('modalArchivo');
if (archivo) {
    // Extraer solo el nombre del archivo sin la carpeta 'uploads/'
    const nombreArchivo = archivo.split('/').pop();
    modalArchivo.innerHTML = `<a href="${archivo}" target="_blank">${nombreArchivo}</a>`;
} else {
    modalArchivo.innerText = "No se adjuntó archivo";
}
    // Abrir el modal
    $('#modalDetalles').modal('show');
}

</script>



<script>
function confirmarEliminacionRespuesta() {
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
            const respuestaId = document.getElementById('inputRespuestaId').value;
            const archivo = document.getElementById('inputArchivoRespuesta').value;

            fetch('eliminar_respuesta_s.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: respuestaId, archivo: archivo })
            })
            .then(response => response.json()) // Espera respuesta en JSON
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        '¡Eliminado!',
                        data.message,
                        'success'
                    ).then(() => {
                        location.reload(); // Recargar la página después de la eliminación
                    });
                } else {
                    Swal.fire(
                        'Error',
                        data.message,
                        'error'
                    );
                }
            })
            .catch(error => {
                Swal.fire(
                    'Error',
                    'No se pudo conectar con el servidor.',
                    'error'
                );
                console.error("Error en la conexión con el servidor:", error);
            });
        }
    });
}

</script>

  <script>
    // Cargar las categorías cuando se selecciona un área
    function cargarCategorias(id_area) {
      var categoriaSelect = document.getElementById('categoria');
      var subServicioSelect = document.getElementById('sub_servicio');

      // Limpiar categorías y sub-servicios
      categoriaSelect.innerHTML = '<option value="">Seleccione una categoría</option>';
      subServicioSelect.innerHTML = '<option value="">Seleccione un sub-servicio</option>';
      categoriaSelect.disabled = true;
      subServicioSelect.disabled = true;

      if (id_area) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '?id_area=' + id_area, true);
        xhr.onload = function() {
          if (this.status === 200) {
            var categorias = JSON.parse(this.responseText);
            categorias.forEach(function(categoria) {
              var option = document.createElement('option');
              option.value = categoria.id;
              option.textContent = categoria.nombre_categoria;
              categoriaSelect.appendChild(option);
            });
            categoriaSelect.disabled = false;
          }
        };
        xhr.send();
      }
    }

    // Cargar los sub-servicios cuando se selecciona una categoría
    function cargarSubServicios(id_categoria) {
      var subServicioSelect = document.getElementById('sub_servicio');

      // Limpiar los sub-servicios
      subServicioSelect.innerHTML = '<option value="">Seleccione un sub-servicio</option>';
      subServicioSelect.disabled = true;

      if (id_categoria) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '?id_categoria=' + id_categoria, true);
        xhr.onload = function() {
          if (this.status === 200) {
            var subServicios = JSON.parse(this.responseText);
            subServicios.forEach(function(subServicio) {
              var option = document.createElement('option');
              option.value = subServicio.id;
              option.textContent = subServicio.nombre_sub_servicio;
              subServicioSelect.appendChild(option);
            });
            subServicioSelect.disabled = false;
          }
        };
        xhr.send();
      }
    }
  </script>
<!-- Importar SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmarEliminacion(idPregunta) {
    // Mostrar modal de confirmación
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
            // Si el usuario confirma, proceder a eliminar la pregunta
            fetch('eliminar_pregunta.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_pregunta=' + encodeURIComponent(idPregunta)
            })
            .then(response => response.json()) // Asumimos que la respuesta será JSON
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        '¡Eliminado!',
                        'La pregunta ha sido eliminada.',
                        'success'
                    ).then(() => {
                        location.reload(); // Recargar la página después de la eliminación
                    });
                } else {
                    Swal.fire(
                        'Error',
                        'Ocurrió un error al eliminar la pregunta.',
                        'error'
                    );
                }
            })
            .catch(error => {
                Swal.fire(
                    'Error',
                    'No se pudo conectar con el servidor.',
                    'error'
                );
            });
        }
    });
}
</script>

</div>

    <!-- jQuery y Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Mostrar alerta de éxito si la solicitud fue enviada correctamente -->
<?php if ($solicitudEnviada) : ?>
<script>
    Swal.fire({
        title: '¡Pregunta guardada!',
        text: 'Tu pregunta ha sido guardada correctamente.',
        icon: 'success',
        confirmButtonText: 'OK'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'encuestas_prueba.php'; // Redirigir después de cerrar el modal
        }
    });
</script>
<?php endif; ?>

<!-- Mostrar alerta de error si hubo un problema al guardar -->
<?php if ($errorAlGuardar) : ?>
<script>
    Swal.fire({
        title: 'Error',
        text: 'Ocurrió un error al guardar la pregunta. Por favor, intenta nuevamente.',
        icon: 'error',
        confirmButtonText: 'OK'
    });
</script>
<?php endif; ?>

    <!-- Linking SwiperJS script -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- Linking custom script -->
<script src="scripts/script_cards.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
      crossorigin="anonymous"></script>
  <script src="scripts/script.js"></script>
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
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- Bootstrap JavaScript -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
