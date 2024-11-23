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


// Procesar la solicitud cuando se envía el formulario
$solicitudEnviada = false;
$errorAlGuardar = false; // Variable para manejar los errores
$camposIncompletos = false; // Variable de control para el modal de advertencia de campos vacíos (inicializada)


// Verificar si el formulario es enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar que existan las claves en $_POST antes de acceder
    if (isset($_POST['area']) && isset($_POST['categoria']) && isset($_POST['sub_servicio'])) {
        // Recibir los datos del formulario
        $id_area = (int) $_POST['area'];  // Convertir a int
        $id_categoria = (int) $_POST['categoria']; // Convertir a int
        $id_sub_servicio = (int) $_POST['sub_servicio']; // Convertir a int
        $comentarios = $_POST['comentarios'];
        date_default_timezone_set('America/Santiago'); // Establecer zona horaria de Chile
        $fecha_hora = date('Y-m-d H:i:s'); // Obtener la fecha y hora actual
        $rut_usuario = $_SESSION['rut']; // Obtener el RUT desde la sesión
        $rol = $_SESSION['rol']; // Obtener el rol desde la sesión

        // Validación básica
        if (!empty($id_area) && !empty($id_categoria) && !empty($id_sub_servicio)) {
            // Inserción en la base de datos
            $sql = "INSERT INTO solicitudes (rut, id_area, id_categoria, id_sub_servicio, comentarios, id_rol, fecha_hora) 
                    VALUES ('$rut_usuario', $id_area, $id_categoria, $id_sub_servicio, '$comentarios', $rol, '$fecha_hora')"; 

            if ($conn->query($sql) === TRUE) {
                $solicitudEnviada = true; // La solicitud fue enviada correctamente
            } else {
                $errorAlGuardar = true; // Hubo un error al guardar la solicitud
            }
        } else {
            $camposIncompletos = true; // Faltan campos por completar
        }
    } else {
        $camposIncompletos = true; // Si no existen las claves, también faltan campos
    }
}


// Obtener el RUT del usuario autenticado desde la sesión
$rut_usuario = $_SESSION['rut']; // Asegúrate de que esta variable exista y tenga el formato correcto


// Si se ha seleccionado un área para filtrar
$areaSeleccionada = isset($_GET['area']) ? $_GET['area'] : '';

// Consulta para obtener las solicitudes, filtrando si se ha seleccionado un área
$sql_soli = "SELECT solicitudes.id, usuarios.rut, soli_areas.nombre_area, soli_categorias.nombre_categoria, soli_servicios.nombre_sub_servicio, solicitudes.comentarios 
        FROM solicitudes 
        INNER JOIN usuarios ON solicitudes.rut = usuarios.rut
        INNER JOIN soli_areas ON solicitudes.id_area = soli_areas.id
        INNER JOIN soli_categorias ON solicitudes.id_categoria = soli_categorias.id
        INNER JOIN soli_servicios ON solicitudes.id_sub_servicio = soli_servicios.id";

// Si se selecciona un área, agregarla a la consulta
if (!empty($areaSeleccionada)) {
    $sql_soli .= " WHERE solicitudes.id_area = '$areaSeleccionada'";
}

$result = $conn->query($sql_soli);

$conn->close();
?>

<!DOCTYPE php>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud</title>
    <!-- Bootstrap CSS -->
    <link rel="icon" href="Images/icono2.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="styles/style_cards.css">
    <link rel="stylesheet" href="styles/style_new_cards.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUa3YufH2ZZcGx3G6tzhhx8Y3v2aD8YCGbglbvvKvzLn5JpkOJf3X1L5jQ5r" crossorigin="anonymous">

    <style>
        .card-body {
            flex-grow: 1;
    padding: 20px;
    max-height: 300px; /* Limitar la altura máxima del cuerpo de la tarjeta */
    overflow: auto; /* Hacer que el contenido restante sea scrolleable si excede el límite */
    scrollbar-width: none; /* Para Firefox */
    background-color: #E3F6FF;
    border-radius: 5px;
    max-width: 240px;
}
    .form-group{
        margin: 20px;
    }    

    .solicitud-container .boton-soli{
        display:flex;
        align-items:center;
        justify-content: center;
    }
    .card {
        cursor: pointer;
        transition: all 0.3s ease;
        
    }

    .card:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }
    /* Limitar el ancho del modal */
    .modal-dialog {
        max-width: 600px;
    }

    /* Evitar que el texto se desborde */
    .modal-body p {
        word-wrap: break-word;  /* Permite que las palabras largas se dividan */
        white-space: normal;    /* Asegura que el texto se ajuste dentro del modal */
    }

    /* Si los comentarios son muy largos, limitar su altura */
    #modal-comentarios span {
        display: block;
        max-height: 150px; /* Limitar la altura máxima del texto */
        overflow-y: auto;  /* Agregar scroll si excede la altura */
        word-wrap: break-word; /* Dividir palabras largas */
    }
    /* Style tab links */
.tablink {
  background-color: #00304A;
  color: white;
  float: left;
  border: none; 
  outline: none;
  cursor: pointer;
  padding: 14px 16px;
  font-size: 17px;
  width: 25%;
  transition: 0.5s;
  margin-right: 10px; border-radius: 5px;
}

.tablink:hover {
  background-color: #25abf3;
  color: white;
}
/* Style the buttons inside the tab */
.tablink button {
  background-color: inherit;
  float: left;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 14px 16px;
  transition: 0.3s;
  font-size: 17px;
  
}

.wrapper a{
    text-decoration: none;
}

body a:hover{
    color: #FFFF;
    text-decoration: none;
}
#modalArchivo {
    color: #007bff;
}

#modalArchivo:hover {
    color: #0056b3;
    background-color: transparent;
    text-decoration: underline;
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

        <header class="solicitud-header">
    <h1>Solicitudes</h1>
</header>


<div class="solicitud-container-wrapper" style="margin-bottom: 50px">

<div class="solicitud-instructions">
        <h3>Instrucciones para enviar una solicitud</h3>
        <p>1. Seleccione el tipo de Área a la cual desea enviarle la solicitud que quiere generar, cada opcion elegida mostrara distintas categorias y servicios, por lo que procura seleccionar bien el Área.</p>
        <p>2. por ultimo, puedes generar un comentario adicional opcional sobre el servicio que deseas(ej: cantidad de computadores para auditorio, o un documento en especifico que no  se encunetra en las opciones).</p>
    </div>
    <div class="solicitud-container">
        <h2>Envia una Soliciutd</h2>
        <h3>Ingrese los campos</h3>
        
         <!-- Mostrar el mensaje de error si existe -->
  <?php if (!empty($error_message)): ?>
    <div style="color: red;">
      <?php echo $error_message; ?>
    </div>
  <?php endif; ?>

  <form id="solicitud-form" method="POST">
    <!-- Selector de Área -->
    <div class="form-group">
      <label for="area"><i class="fas fa-map-marked-alt"></i> Área</label>
      <select id="area" name="area" class="form-control" onchange="cargarCategorias(this.value)">
        <option value="">Seleccione un área</option>
        <?php foreach ($areas as $area): ?>
          <option value="<?php echo $area['id']; ?>"><?php echo $area['nombre_area']; ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Selector de Categoría -->
    <div class="form-group">
      <label for="categoria"><i class="fas fa-list"></i> Categoría</label>
      <select id="categoria" name="categoria" class="form-control" onchange="cargarSubServicios(this.value)" disabled>
        <option value="">Seleccione una categoría</option>
      </select>
    </div>

    <!-- Selector de Sub-Servicio -->
    <div class="form-group">
      <label for="sub_servicio"><i class="fas fa-tasks"></i> Sub-Servicio</label>
      <select id="sub_servicio" name="sub_servicio" class="form-control" disabled>
        <option value="">Seleccione un sub-servicio</option>
      </select>
    </div>

    <!-- Comentarios -->
    <div class="form-group">
      <label for="comentarios"><i class="fas fa-comment-dots"></i> Comentarios</label>
      <textarea id="comentarios" name="comentarios" class="form-control" placeholder="Escriba sus comentarios" maxlength="1000"></textarea>
    </div>
    
    <div class="boton-soli">
        <button type="submit" class="solicitud-submit-btn">Enviar Solicitud</button>
    </div>
</form>

  
    </div>
</div>


<div class="solicitud-container-wrapper" style="width: 980px; margin-bottom: 30px;">
 <!-- Tab links -->
    <div class="solicitud-container" style="width: 930px; justify-content: center; background-color: #3f8bb100; box-shadow: 0.
    ">
        <h2>Elige la opcion que deseas ver</h2>
        <h6 style="text-align: center; margin-bottom: 30px;">Aquí puedes tanto las solicitudes como las respuestas a tus solicitudes enviadas. Solo escoge el que quieras ver y listo!</h6>
        <div style="display: flex; justify-content: center;">
            <button class="tablink" onclick="openPage('Solicitudes')" id="defaultOpen">Solicitudes</button>
            <button class="tablink" onclick="openPage('Respuestas')">Respuestas</button>
            <!-- Tab content -->
        </div>
    </div>
</div>

 <div id="Solicitudes" class="tabcontent">
    <div class="solicitud-container-wrapper">
      <div class="solicitud-container" style="width: 930px;">
        <h2>Tus solicitudes</h2>
        <h6 style="text-align: center; margin-bottom: 30px;">
          Estas son tus solicitudes que has enviado, aquí puedes abrirlas y ver su contenido extenso, además de poder eliminarla si gustas.
        </h6>

        <?php
        include("conexion.php");
        $rut_usuario = $_SESSION['rut'];

        $sql_solis_ver = "
            SELECT s.id, s.rut, a.nombre_area, c.nombre_categoria, ss.nombre_sub_servicio, s.comentarios, s.fecha_hora 
            FROM solicitudes s
            INNER JOIN soli_areas a ON s.id_area = a.id
            INNER JOIN soli_categorias c ON s.id_categoria = c.id
            INNER JOIN soli_servicios ss ON s.id_sub_servicio = ss.id
            LEFT JOIN soli_respuestas r ON s.id = r.solicitud_id
            WHERE s.rut = '$rut_usuario' AND r.id IS NULL";

        $result_solis = $conn->query($sql_solis_ver);

        if ($result_solis === false || $result_solis->num_rows == 0) {
            echo "<p class='text-center'>No has enviado ninguna solicitud sin respuesta.</p>";
        } else {
        ?>
            <div class="row">
                <?php while ($row = $result_solis->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm" data-id="<?php echo $row['id']; ?>" data-toggle="modal" data-target="#solicitudModal" onclick="loadSolicitudModal(<?php echo $row['id']; ?>)">
                            <div class="card-body">
                                <h5 class="card-title">Solicitud</h5>
                                <p><strong>Área:</strong> <?php echo $row['nombre_area']; ?></p>
                                <p><strong>Categoría:</strong> <?php echo $row['nombre_categoria']; ?></p>
                                <p><strong>Sub-servicio:</strong> <?php echo $row['nombre_sub_servicio']; ?></p>
                                <p><strong>Comentarios:</strong> <?php echo htmlspecialchars($row['comentarios']); ?></p>
                                <p><strong>Fecha:</strong> <?php echo date('d-m-Y H:i', strtotime($row['fecha_hora'])); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php } ?>
      </div>
    </div>
  </div>

  <!-- Respuestas tab content -->
  <div id="Respuestas" class="tabcontent">
    <div class="solicitud-container-wrapper">
      <div class="solicitud-container" style="width: 930px;">
        <h2>Respuestas a tus solicitudes</h2>
        <h6 style="text-align: center; margin-bottom: 30px;">Aquí puedes ver las respuestas a tus solicitudes enviadas.</h6>

        <?php
        $sql_solis_respuestas = "
            SELECT s.id, s.rut, a.nombre_area, c.nombre_categoria, ss.nombre_sub_servicio, s.comentarios, s.fecha_hora, r.respuesta_texto, r.archivo, r.fecha_respuesta
            FROM solicitudes s
            INNER JOIN soli_areas a ON s.id_area = a.id
            INNER JOIN soli_categorias c ON s.id_categoria = c.id
            INNER JOIN soli_servicios ss ON s.id_sub_servicio = ss.id
            INNER JOIN soli_respuestas r ON s.id = r.solicitud_id
            WHERE s.rut = '$rut_usuario'";

        $result_respuestas = $conn->query($sql_solis_respuestas);

        if ($result_respuestas === false || $result_respuestas->num_rows == 0) {
            echo "<p class='text-center'>No tienes respuestas a tus solicitudes.</p>";
        } else {
        ?>
            <div class="row">
                <?php while ($row = $result_respuestas->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm" onclick="openModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                            <div class="card-body">
                                <h5 class="card-title">Solicitud Respondida</h5>
                                <p><strong>Área:</strong> <?php echo $row['nombre_area']; ?></p>
                                <p><strong>Categoría:</strong> <?php echo $row['nombre_categoria']; ?></p>
                                <p><strong>Sub-servicio:</strong> <?php echo $row['nombre_sub_servicio']; ?></p>
                                <p><strong>Fecha Solicitud:</strong> <?php echo date('d-m-Y H:i', strtotime($row['fecha_hora'])); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php } ?>
      </div>
    </div>
  </div>

  <!-- Bootstrap Modal -->
  <div class="modal fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="responseModalLabel">Detalles de la Respuesta</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p><strong>Área:</strong> <span id="modalArea"></span></p>
          <p><strong>Categoría:</strong> <span id="modalCategoria"></span></p>
          <p><strong>Sub-servicio:</strong> <span id="modalSubServicio"></span></p>
          <p><strong>Comentarios:</strong> <span id="modalComentarios"></span></p>
          <p><strong>Fecha Solicitud:</strong> <span id="modalFechaSolicitud"></span></p>
          <hr style="border-top: 3px solid #333;">
          <h6>Respuesta:</h6>
          <p><strong>Texto:</strong> <span id="modalRespuestaTexto"></span></p>
          <p><strong>Fecha Respuesta:</strong> <span id="modalFechaRespuesta"></span></p>
          <p><strong>Archivo:</strong> <a id="modalArchivo" href="" target="_blank"></a></p>
          </div>
      </div>
    </div>
  </div>

  <script src="script_tabs_soli.js"></script>

<!-- Modal para mostrar los detalles de la solicitud -->
<div class="modal fade" id="solicitudModal" tabindex="-1" role="dialog" aria-labelledby="solicitudModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="solicitudModalLabel">Detalles de la Solicitud</h5>
                <!-- Botón de cerrar en la esquina superior derecha -->
                <button type="button" class="close" onclick="forzarCierreModalSolicitud()" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="modal-area"><strong>Área:</strong> <span></span></p>
                <p id="modal-categoria"><strong>Categoría:</strong> <span></span></p>
                <p id="modal-subservicio"><strong>Sub-servicio:</strong> <span></span></p>
                <p id="modal-comentarios"><strong>Comentarios:</strong> <span></span></p>
                <p id="modal-fecha"><strong>Fecha:</strong> <span></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="forzarCierreModalSolicitud()">Cerrar</button>
                <button type="button" class="btn btn-danger" id="eliminar-btn">Eliminar Solicitud</button>
                
                <form method="POST" action="eliminar_solicitud.php" id="form-eliminar">
                    <input type="hidden" id="delete-solicitud-id" name="solicitud_id">
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function abrirModalSolicitud(area, categoria, subservicio, comentarios, fecha) {
        document.getElementById('modal-area').querySelector('span').innerText = area;
        document.getElementById('modal-categoria').querySelector('span').innerText = categoria;
        document.getElementById('modal-subservicio').querySelector('span').innerText = subservicio;
        document.getElementById('modal-comentarios').querySelector('span').innerText = comentarios;
        document.getElementById('modal-fecha').querySelector('span').innerText = fecha;

        // Mostrar el modal
        $('#solicitudModal').modal('show');
    }

    function forzarCierreModalSolicitud() {
        $('#solicitudModal').modal('hide');

        // Limpiar fondo oscuro y clases adicionales si quedan bloqueadas
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    }

    // Asegurarse de que el modal se limpie correctamente al cerrar
    $('#solicitudModal').on('hidden.bs.modal', function () {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();

        // Limpia el contenido del modal para liberar memoria
        document.getElementById('modal-area').querySelector('span').innerText = '';
        document.getElementById('modal-categoria').querySelector('span').innerText = '';
        document.getElementById('modal-subservicio').querySelector('span').innerText = '';
        document.getElementById('modal-comentarios').querySelector('span').innerText = '';
        document.getElementById('modal-fecha').querySelector('span').innerText = '';
    });
</script>



<script>
    // Function to open specific page tab
    function openPage(pageName, elmnt, color) {
      var i, tabcontent, tablinks;
      tabcontent = document.getElementsByClassName("tabcontent");
      for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
      }

      tablinks = document.getElementsByClassName("tablink");
      for (i = 0; i < tablinks.length; i++) {
        tablinks[i].style.backgroundColor = "";
      }

      document.getElementById(pageName).style.display = "block";
      elmnt.style.backgroundColor = color;
    }

    // Set default tab to open
    document.getElementById("defaultOpen").click();

    function openModal(data) {
    // Otros datos del modal
    document.getElementById('modalArea').innerText = data.nombre_area;
    document.getElementById('modalCategoria').innerText = data.nombre_categoria;
    document.getElementById('modalSubServicio').innerText = data.nombre_sub_servicio;
    document.getElementById('modalComentarios').innerText = data.comentarios;
    document.getElementById('modalFechaSolicitud').innerText = data.fecha_hora;
    document.getElementById('modalRespuestaTexto').innerText = data.respuesta_texto;
    document.getElementById('modalFechaRespuesta').innerText = data.fecha_respuesta;

    // Actualizar el enlace del archivo
    const modalArchivo = document.getElementById('modalArchivo');
    if (data.archivo) {
        // Establecer el href con la ruta completa
        modalArchivo.href = data.archivo;
        
        // Mostrar solo el nombre del archivo, sin el prefijo 'uploads/'
        const nombreArchivo = data.archivo.split('/').pop(); // Esto toma solo el nombre del archivo
        modalArchivo.innerText = nombreArchivo;
    } else {
        modalArchivo.removeAttribute('href');  // Elimina el enlace para que no sea clickeable
        modalArchivo.style.color = 'black';  // Cambia el estilo para que no luzca como un enlace
        modalArchivo.innerText = 'No hay archivo adjunto';
    }

    // Mostrar el modal (si estás usando Bootstrap 4)
    $('#responseModal').modal('show');
}
  </script>


<!-- Bootstrap CSS (si no está ya incluido) -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>


<script>
    function loadSolicitudModal(solicitudId) {
        var card = $('[data-id="' + solicitudId + '"]');
        var area = card.find('p:contains("Área")').text().replace('Área:', '').trim();
        var categoria = card.find('p:contains("Categoría")').text().replace('Categoría:', '').trim();
        var subservicio = card.find('p:contains("Sub-servicio")').text().replace('Sub-servicio:', '').trim();
        var comentarios = card.find('p:contains("Comentarios")').text().replace('Comentarios:', '').trim();
        var fecha = card.find('p:contains("Fecha")').text().replace('Fecha:', '').trim();

        // Colocar los datos en el modal
        $('#modal-area span').text(area);
        $('#modal-categoria span').text(categoria);
        $('#modal-subservicio span').text(subservicio);
        $('#modal-comentarios span').text(comentarios);
        $('#modal-fecha span').text(fecha);

        // Asegurarse de que el evento de clic para eliminar esté correctamente vinculado
        $('#eliminar-btn').off().on('click', function() {
            confirmarEliminacion(solicitudId);
        });

        // Mostrar el modal
        $('#solicitudModal').modal('show');
    }

    // Función para confirmar eliminación
    function confirmarEliminacion(idSolicitud) {
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
                // Si el usuario confirma, envía el formulario para eliminar la solicitud
                document.getElementById('delete-solicitud-id').value = idSolicitud;
                document.getElementById('form-eliminar').submit();
            }
        });
    }

    // Asegurarse de que el modal cierra correctamente y elimina la clase 'modal-backdrop'
    $('#solicitudModal').on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove(); // Elimina el fondo gris cuando se cierra el modal
    });

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

<!-- Importar SweetAlert y Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Mostrar el modal de éxito si la solicitud fue enviada -->
<?php if ($solicitudEnviada) : ?>
<script>
    Swal.fire({
        title: '¡Solicitud enviada!',
        text: 'Tu solicitud ha sido registrada con éxito.',
        icon: 'success',
        confirmButtonText: 'OK'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'solicitudes.php'; // Redirigir a la página que prefieras
        }
    });
</script>
<?php endif; ?>

<!-- Mostrar el modal de error si ocurrió un problema al guardar -->
<?php if ($errorAlGuardar) : ?>
<script>
    Swal.fire({
        title: 'Error',
        text: 'Ocurrió un error al registrar la solicitud. Por favor, intenta nuevamente.',
        icon: 'error',
        confirmButtonText: 'OK'
    });
</script>
<?php endif; ?>

<!-- Mostrar el modal de advertencia si faltan campos por completar -->
<?php if ($camposIncompletos) : ?>
<script>
    Swal.fire({
        title: 'Campos incompletos',
        text: 'Por favor, complete todos los campos antes de enviar la solicitud.',
        icon: 'warning',
        confirmButtonText: 'OK'
    });
</script>
<?php endif; ?>


    <!-- Linking SwiperJS script -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- Linking custom script -->
<script src="scripts/script_cards.js"></script>
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

</body>
</html>
