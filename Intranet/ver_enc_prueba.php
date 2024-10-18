<?php
include('conexion.php'); // Conexión a la base de datos
session_start();

$rut_usuario = $_SESSION['rut']; // RUT del usuario autenticado

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

// Consultar el cargo del usuario
$sql_cargo = "SELECT NOMBRE_CARGO FROM cargos WHERE id = '" . $user_data['cargo_id'] . "'";
$result_cargo = $conn->query($sql_cargo);

if ($result_cargo->num_rows > 0) {
    $cargo_data = $result_cargo->fetch_assoc(); // Extraer los datos del usuario
} else {
    $error = "No se encontraron datos para el cargo.";
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


// Consulta para obtener preguntas pendientes (no respondidas)
$query_pendientes = "
    SELECT p.*
    FROM preguntas_encuesta p
    LEFT JOIN respuestas_encuesta r
    ON p.id_pregunta = r.id_pregunta AND r.rut_usuario = ?
    WHERE r.id_pregunta IS NULL
";

$stmt_pendientes1 = $conn->prepare($query_pendientes);
$stmt_pendientes1->bind_param("s", $rut_usuario);
$stmt_pendientes1->execute();
$result_pendientes = $stmt_pendientes1->get_result();

// Consulta para obtener preguntas respondidas
$query_respondidas = "
    SELECT p.*, r.id_respuesta, r.calificacion, r.respuesta
    FROM preguntas_encuesta p
    JOIN respuestas_encuesta r
    ON p.id_pregunta = r.id_pregunta
    WHERE r.rut_usuario = ?
";

$stmt_respondidas = $conn->prepare($query_respondidas);
$stmt_respondidas->bind_param("s", $rut_usuario);
$stmt_respondidas->execute();
$result_respondidas = $stmt_respondidas->get_result();


// Obtener el usuario autenticado
$rut_usuario = $_SESSION['rut'];



?>

<!DOCTYPE php>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuestas</title>
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
        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
        }
        .rating input {
            display: none;
        }
        .rating label {
            font-size: 2rem;
            color: gray;
            cursor: pointer;
        }
        .rating input:checked ~ label, .rating label:hover, .rating label:hover ~ label {
            color: gold;
        }

                /* Estilos para la disposición flexbox */
        .pregunta-calificacion {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .pregunta-label {
            font-weight: bold;
            flex: 1;
        }

        .pregunta button {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .calificacion-label {
            margin-left: 10px;
        }

        /* Para que la respuesta quede debajo */
        .respuesta-texto {
            margin-top: 10px;
        }

        /* Asegurar espacio adecuado en las encuestas pendientes */
        .input-group1 {
            margin-bottom: 20px;
        }

        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            margin-bottom: 10px;
        }

        .rating input {
            display: none;
        }

        .rating label {
            font-size: 2.5rem;
            color: gray;
            cursor: pointer;
        }

        .rating input:checked ~ label,
        .rating label:hover,
        .rating label:hover ~ label {
            color: gold;
        }

        .input-group1 {
            margin-bottom: 30px;
            padding: 15px;
            border-radius: 10px;
            background-color: #f9f9f9;
        }

        .pregunta-contenedor {
            margin-bottom: 15px;
        }

        .pregunta-calificacion {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .pregunta-label {
            font-size: 1.1em;
            font-weight: bold;
            color: #333;
            word-wrap: break-word;  /* Divide las palabras largas para que no se desborden */
            white-space: normal;    /* Asegura que el texto pueda ocupar varias líneas */
            max-width: 100%;        /* Asegura que el ancho máximo no exceda el contenedor */
            margin-bottom: 10px;    /* Añade algo de espacio debajo del párrafo */
        }

        .calificacion-label .badge {
            font-size: 0.9em;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .respuesta-contenedor {
            margin-top: 10px;
        }

        .respuesta-texto {
            font-size: 1em;
            color: #555;
            word-wrap: break-word;  /* Divide las palabras largas para que no se desborden */
            white-space: normal;    /* Asegura que el texto pueda ocupar varias líneas */
            max-width: 100%;        /* Asegura que el ancho máximo no exceda el contenedor */
            margin-bottom: 10px;    /* Añade algo de espacio debajo del párrafo */
        }
        .solicitud-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 800px;
        }
        .form-check-input {
        width: 1.2em;
        height: 1.2em;
        margin-top: 1em;
        text-align: center;
        justify-content: center;
        
        }
        
        .form-check-label {
            font-size: 16px;
            margin-left: 10px;
            text-align: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        .boxbox{
        width: 0.5em;
        height: 1.2em;
        margin-top: 0.1em;
        }

        .boton_pregs{
            display: flex;
            text-align: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        #scrollToSolicitudes{
            background-color: #008AC9;
    color: white;
    padding: 15px;
    border: none;
    border-radius: 5px;
    font-size: 1.2rem;
    cursor: pointer;
    transition: 0.3s;
    display:block;
        }

            #scrollToSolicitudes:hover {
        background-color: #00304A;
        
    }
    .calificacion-estrellas {
        display: flex;
        align-items: center;
    }

    .estrella {
        font-size: 24px;
        color: #ccc; /* Color por defecto para las estrellas vacías */
    }

    .estrella.llena {
        color: #FFD700; /* Color amarillo para las estrellas llenas */
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
                        <i class="lni lni-layout"></i>
                        <span>Personal</span>
                    </a>
                    <ul id="multi" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Empleado del mes</a>
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
                            <a href="#" class="sidebar-link">cumpleaños</a>
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
                    <a href="solicitud.php" class="sidebar-link">
                        <i class="lni lni-popup"></i>
                        <span>Solicitudes</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="soporte.php" class="sidebar-link">
                        <i class="lni lni-cog"></i>
                        <span>Soporte Informatico</span>
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
        <div class="main" style="padding-top: 15px;">
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
        <header class="solicitud-header">
        <h1>Portal de encuestas</h1>
    </header>


<button id="scrollToTop" class="scroll-to-top1">↑ </button>

<div class="solicitud-container-wrapper">

<div class="solicitud-container">
    <h2>Encuestas</h2>  
    <h6 style="text-align: center;">Este es el apartado de Encuestas, aqui deberas responder las encuesta que aparezcan con con sus respectivas instrucciones. Las preguntas que respondas, SOLO LAS PODRAS VER TU, asi que, no tengas miedo de responder! </h6>
    <?php
 
    echo "<p class='respuesta-texto' style='display:block; text-align:center; font-size: 13px'><strong>Si deseas ver las Respuestas, haz click </strong></p>";
    echo "<p class='respuesta-texto' style='display:block;text-align:center; font-size: 13px'><strong>en este boton para desplazarte rapidamente:</strong></p>";
    echo "<div class='boton_pregs'  >";
    echo "<button id='scrollToSolicitudes' style='width: 20%;height: 40px; font-size: 0.8em; text-align: center; padding: 0px;'  class='solicitud-submit-btn'>Mis respuestas</button>";
    echo "</div>";
    
    echo "<hr>";
    ?>
    <form id="form-encuesta" action="respuestas_encuesta.php" method="POST" class="solicitud-form" style="gap: 0px;">
    <?php

$respondidas = [];
$query_respondidas = "SELECT id_pregunta FROM respuestas_encuesta WHERE rut_usuario = ?";
$stmt_respondidas = $conn->prepare($query_respondidas);
$stmt_respondidas->bind_param('s', $rut_usuario);
$stmt_respondidas->execute();
$result_respondidas = $stmt_respondidas->get_result();

while ($row = $result_respondidas->fetch_assoc()) {
    $respondidas[] = $row['id_pregunta'];
}

$stmt_respondidas->close();

// Si no hay preguntas respondidas, selecciona todas las preguntas
if (empty($respondidas)) {
    $sql_preg_enc = "SELECT id_pregunta, pregunta, tipo_pregunta FROM preguntas_encuesta 
                    WHERE tipo_pregunta = 'texto' OR tipo_pregunta = 'seleccion_unica'";
} else {
    // Excluir las preguntas respondidas
    $placeholders = implode(',', array_fill(0, count($respondidas), '?'));
    $sql_preg_enc = "SELECT id_pregunta, pregunta, tipo_pregunta FROM preguntas_encuesta 
                    WHERE (tipo_pregunta = 'texto' OR tipo_pregunta = 'seleccion_unica')
                    AND id_pregunta NOT IN ($placeholders)";
}

$stmt_pendientes1 = $conn->prepare($sql_preg_enc);

if (!empty($respondidas)) {
    $stmt_pendientes1->bind_param(str_repeat('i', count($respondidas)), ...$respondidas);
}

$stmt_pendientes1->execute();
$result_pendientes = $stmt_pendientes1->get_result();

if ($result_pendientes->num_rows > 0) {
    while ($row = $result_pendientes->fetch_assoc()) {
        echo "<div class='pregunta'>";
        echo "<p class='respuesta-texto' style='display:block; font-size: 13px'><strong>Pregunta Encuesta:</strong></p>";

        echo "<h5><strong>" . $row['pregunta'] . "</strong></h5>";

        // Diferentes tipos de preguntas
        if ($row['tipo_pregunta'] === 'texto') {
            // Sistema de calificación con estrellas
            echo "<p class='respuesta-texto' style='display:block;text-align:center; font-size: 13px; margin-top:10px'><strong>Selecciona una puntacion</strong></p>";

            echo "<div class='rating'>
                <input type='radio' id='star5_{$row['id_pregunta']}' name='calificacion[" . $row['id_pregunta'] . "]' value='5'>
                <label for='star5_{$row['id_pregunta']}'>★</label>

                <input type='radio' id='star4_{$row['id_pregunta']}' name='calificacion[" . $row['id_pregunta'] . "]' value='4'>
                <label for='star4_{$row['id_pregunta']}'>★</label>

                <input type='radio' id='star3_{$row['id_pregunta']}' name='calificacion[" . $row['id_pregunta'] . "]' value='3'>
                <label for='star3_{$row['id_pregunta']}'>★</label>

                <input type='radio' id='star2_{$row['id_pregunta']}' name='calificacion[" . $row['id_pregunta'] . "]' value='2'>
                <label for='star2_{$row['id_pregunta']}'>★</label>

                <input type='radio' id='star1_{$row['id_pregunta']}' name='calificacion[" . $row['id_pregunta'] . "]' value='1'>
                <label for='star1_{$row['id_pregunta']}'>★</label>
            </div>";
            // Campo de respuesta tipo párrafo
            echo "<textarea style='margin-bottom: 30px;'name='respuesta[" . $row['id_pregunta'] . "]' rows='4' cols='50' placeholder='Escribe tu respuesta aquí... (opcional)'></textarea>";
        } elseif ($row['tipo_pregunta'] === 'seleccion_unica') {
            $opciones = $conn->query("SELECT opcion FROM opciones_encuesta WHERE id_pregunta = " . $row['id_pregunta']);

            echo "<div class='opciones-seleccion-multiple' style='display: row;  justify-content: center; align-items: center; margin-bottom: 20px'>";
            while ($opcion = $opciones->fetch_assoc()) {
                echo "<div class='form-check'>
                        <div class='boxbox'>
                        <input class='form-check-input' type='radio' name='respuesta[" . $row['id_pregunta'] . "][]' id='opcion_{$row['id_pregunta']}_{$opcion['opcion']}' value='" . $opcion['opcion'] . "'>
                        </div>
                        <label class='form-check-label' for='opcion_{$row['id_pregunta']}_{$opcion['opcion']}'>
                            " . $opcion['opcion'] . "
                        </label>
                    </div>";
            }
            echo "</div>";
        }

        // Campo oculto para el id_pregunta
        echo "<input type='hidden' name='id_pregunta[]' value='" . $row['id_pregunta'] . "'>";
        echo "<div class='boton_pregs'>";
        echo "<button type='submit' name='responder' class='solicitud-submit-btn'>Enviar Respuesta</button>";
        echo "</div>";
        echo "</div><hr>";
    }
} else {
    echo "<p>No hay preguntas disponibles para responder.</p>";
}
?>
        <button type='submit' name='responder' class='solicitud-submit-btn'>Enviar Respuesta</button>

    </form>


        <div class="enc-resp" style="MARGIN-TOP:20PX;">
            <h1 style="text-align: center; MARGIN-TOP:20PX;text-align: center;color: #008AC9;margin-bottom: 10px;text-transform: uppercase;font-weight: bold;">Encuestas Respondidas</h1> 
            <h6 style="text-align: center; MARGIN-BOTTOM: 20PX;">Estas son todas las respuestas de encuestas que has realizado! </h6>

        </div>
<?php 
// Mostrar las preguntas ya respondidas
$query_respondidas = "
SELECT p.*, r.id_respuesta, r.calificacion, r.respuesta, r.fecha_respuesta
FROM preguntas_encuesta p
JOIN respuestas_encuesta r
ON p.id_pregunta = r.id_pregunta
WHERE r.rut_usuario = ?
";

$stmt_respondidas = $conn->prepare($query_respondidas);
$stmt_respondidas->bind_param("s", $rut_usuario);
$stmt_respondidas->execute();
$result_respondidas = $stmt_respondidas->get_result();

if ($result_respondidas->num_rows > 0) {
    while ($row = $result_respondidas->fetch_assoc()) {
        echo "<div class='input-group1' style='margin-bottom: 20px;'>";
        echo "<div class='pregunta-contenedor'>";
        
        echo "<div class='pregunta-calificacion' style='margin-bottom: 0px;'>";
        echo "<label class='form-label pregunta-label'>{$row['pregunta']}</label>";
       
        echo "<div class='calificacion-estrellas'>";
        echo "<button type='button' class='btn btn-outline-warning' data-bs-toggle='modal' data-bs-target='#editModal' data-id='{$row['id_respuesta']}' data-pregunta='{$row['pregunta']}' data-respuesta='{$row['respuesta']}' data-tipo='{$row['tipo_pregunta']}' data-calificacion='{$row['calificacion']}'>Actualizar</button>";
        echo "</div>";
        echo "</div>";
        
        echo "<div class='pregunta-calificacion'>";
        // Mostrar estrellas en función de la calificación si la pregunta no es de selección única
        if ($row['tipo_pregunta'] !== 'seleccion_unica') {
            echo "<div class='calificacion-estrellas'>";
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $row['calificacion']) {
                    // Estrella llena (amarilla)
                    echo "<span class='estrella llena'>★</span>";
                } else {
                    // Estrella vacía (gris)
                    echo "<span class='estrella vacia'>★</span>";
                }
            }
            echo "<p class='fecha-respuesta' style='display: inline-block; margin:0px; margin-left:15px; text-align: center;'><strong></strong> " . date('d-m-Y', strtotime($row['fecha_respuesta'])) . "</p>";

            echo "</div>";
        }

        // Mostrar la fecha y hora junto a la calificación
        
        echo "</div>";

        // Mostrar respuesta si existe
        if (empty($row['respuesta'])) {
            echo "<div class='respuesta-contenedor'>";
            echo "<p class='respuesta-texto'>Sin respuesta comentada.</p>";
            echo "</div>";
        } else {
            echo "<div class='respuesta-contenedor'>";
            echo "<p class='respuesta-texto'><strong>Respuesta:</strong> {$row['respuesta']}</p>";
            echo "</div>";
        }

        echo "</div>";
        echo "</div>";
    }
} else {
    echo "<p>No has respondido ninguna encuesta aún.</p>";
}

// Cerrar los statements y la conexión
$stmt_respondidas->close();
$conn->close();

?>


            </div>
        </div>
    </div>


<!-- Modal de Bootstrap -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="updateForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Actualizar Respuesta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editPregunta" class="form-label">Pregunta</label>
                        <input type="text" class="form-control" id="editPregunta" readonly>
                    </div>

                    <!-- Área para preguntas tipo texto -->
                    <div class="mb-3" id="textRespuesta">
                        <label for="editRespuesta" class="form-label">Respuesta</label>
                        <textarea class="form-control" id="editRespuesta" rows="3"></textarea>
                    </div>

                    <!-- Área para opciones de selección única (oculta por defecto) -->
                    <div class="mb-3" id="opcionesSeleccionUnica" style="display:none;">
                        <label class="form-label">Seleccione una opción</label>
                        <div id="opcionesContainer"></div>
                    </div>

                    <!-- Calificación con estrellas (solo si es pregunta de tipo texto) -->
                    <div class="mb-3" id="calificacionEstrellas">
                        <label id="editCalificacion" class="form-label">Calificación</label>
                        <div class="rating">
                            <input type="radio" id="star5" name="calificacion" value="5">
                            <label for="star5">★</label>

                            <input type="radio" id="star4" name="calificacion" value="4">
                            <label for="star4">★</label>

                            <input type="radio" id="star3" name="calificacion" value="3">
                            <label for="star3">★</label>

                            <input type="radio" id="star2" name="calificacion" value="2">
                            <label for="star2">★</label>

                            <input type="radio" id="star1" name="calificacion" value="1">
                            <label for="star1">★</label>
                        </div>
                    </div>
                    
                    <input type="hidden" id="editIdRespuesta">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var editModal = document.getElementById('editModal');

    // Cargar los datos al abrir el modal
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Botón que activó el modal
        var id_respuesta = button.getAttribute('data-id');
        var pregunta = button.getAttribute('data-pregunta');
        var respuesta = button.getAttribute('data-respuesta');
        var tipo_pregunta = button.getAttribute('data-tipo'); // Obtener el tipo de pregunta
        var calificacion = button.getAttribute('data-calificacion'); // Obtener la calificación actual

        // Cargar los datos en el modal
        document.getElementById('editPregunta').value = pregunta;
        document.getElementById('editIdRespuesta').value = id_respuesta;

        // Mostrar el área de respuesta o selección única según el tipo de pregunta
        if (tipo_pregunta === 'seleccion_unica') {
            document.getElementById('textRespuesta').style.display = 'none';
            document.getElementById('opcionesSeleccionUnica').style.display = 'block';
            document.getElementById('calificacionEstrellas').style.display = 'none'; // Ocultar las estrellas
            cargarOpcionesSeleccionUnica(id_respuesta, respuesta);
        } else {
            document.getElementById('textRespuesta').style.display = 'block';
            document.getElementById('editRespuesta').value = respuesta;
            document.getElementById('opcionesSeleccionUnica').style.display = 'none';
            document.getElementById('calificacionEstrellas').style.display = 'block'; // Mostrar las estrellas

            // Cargar la calificación actual en las estrellas
            var stars = document.querySelectorAll('input[name="calificacion"]');
            stars.forEach(function(star) {
                if (star.value == calificacion) {
                    star.checked = true; // Marcar la estrella que coincide con la calificación
                }
            });
        }
    });
    
// Función para cargar las opciones de "seleccion_unica"
function cargarOpcionesSeleccionUnica(id_respuesta, respuestaSeleccionada) {
    console.log('Cargando opciones para id_respuesta:', id_respuesta); // Depuración

    fetch('obtener_opc_act_enc.php?id_respuesta=' + encodeURIComponent(id_respuesta))
        .then(response => response.json())
        .then(data => {
            console.log('Respuesta recibida:', data); // Depuración

            if (data.success) {
                let opcionesHTML = '';
                data.opciones.forEach(opcion => {
                    const isChecked = opcion === respuestaSeleccionada ? 'checked' : '';
                    opcionesHTML += `
                        <div class="form-check" style="display: flex; align-items:center; justify-content: start;  text-align:center;">
                            <input class="form-check-input1" type="radio" style=" zoom: 1.5;"name="opcion_unica" value="${opcion}" ${isChecked}>
                            <label class="form-check-label" style="margin:0; margin-left: 10px;">${opcion}</label>
                        </div>
                    `;
                });
                document.getElementById('opcionesContainer').innerHTML = opcionesHTML;
            } else {
                console.error('Error en la respuesta:', data.message); // Depuración en caso de error
            }
        })
        .catch(error => console.error('Error al cargar las opciones:', error));
}


    // Manejar el envío del formulario de actualización
    document.getElementById('updateForm').addEventListener('submit', function (e) {
        e.preventDefault(); // Prevenir el envío por defecto

        var id_respuesta = document.getElementById('editIdRespuesta').value;
        var tipo_pregunta = document.querySelector('[name="opcion_unica"]') ? 'seleccion_unica' : 'texto';
        var nueva_respuesta = tipo_pregunta === 'seleccion_unica'
            ? document.querySelector('input[name="opcion_unica"]:checked').value
            : document.getElementById('editRespuesta').value;

        var nueva_calificacion = tipo_pregunta === 'texto'
            ? document.querySelector('input[name="calificacion"]:checked').value
            : '';

        // Enviar los datos de actualización mediante fetch
        fetch('actualizar_respuesta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_respuesta=' + encodeURIComponent(id_respuesta) +
                  '&nueva_respuesta=' + encodeURIComponent(nueva_respuesta) +
                  '&nueva_calificacion=' + encodeURIComponent(nueva_calificacion) +
                  '&tipo_pregunta=' + encodeURIComponent(tipo_pregunta)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: '¡Respuestas guardadas!',
                    text: 'Tus respuestas han sido guardadas correctamente.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'ver_enc_prueba.php'; // Redirigir después de cerrar el modal
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al guardar las respuestas.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => console.error('Error:', error));
    });
});
</script>


<script>
    document.getElementById('scrollToTop').addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
</script>
<script>
    document.getElementById('scrollToSolicitudes').addEventListener('click', function() {
    document.querySelector('.enc-resp').scrollIntoView({ 
        behavior: 'smooth' 
    });
});
</script>

 <!-- jQuery y Bootstrap JS -->
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
// Capturar el envío del formulario
document.getElementById('form-encuesta').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevenir el envío por defecto del formulario

    // Si el formulario es válido, continuar con el envío
    const formData = new FormData(this);

    fetch('respuestas_encuesta.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) // Asumimos que la respuesta será JSON
    .then(data => {
        if (data.success) {
            // Mostrar modal de éxito con SweetAlert
            Swal.fire({
                title: '¡Respuestas guardadas!',
                text: 'Tus respuestas han sido guardadas correctamente.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'ver_enc_prueba.php'; // Redirigir después de cerrar el modal
                }
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: 'Ocurrió un error al guardar las respuestas.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: 'No se pudo conectar con el servidor.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
});
</script>




    <!-- Linking SwiperJS script -->
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- Linking custom script -->
<script src="js/script.js"></script>
<script src="js/script_cards.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
      crossorigin="anonymous"></script>

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
