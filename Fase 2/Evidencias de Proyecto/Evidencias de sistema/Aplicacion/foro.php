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
// Mostrar errores en el navegador
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Procesar la creación de una nueva pregunta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_pregunta'])) {
    $rut_usuario = $_SESSION['rut']; // Reemplaza con el RUT del usuario autenticado
    $pregunta = $_POST['pregunta'];
    $foto_pregunta = null;

    // Verificar si se subió una imagen para la pregunta
    if (isset($_FILES['foto_pregunta']) && $_FILES['foto_pregunta']['error'] == 0) {
        $foto_pregunta = 'uploads/' . basename($_FILES['foto_pregunta']['name']);
        if (!move_uploaded_file($_FILES['foto_pregunta']['tmp_name'], $foto_pregunta)) {
            die("Error al mover la imagen subida");
        }
    }

    // Insertar la pregunta en la base de datos
    $sql = "INSERT INTO foro_preguntas (rut_usuario, pregunta, foto_pregunta) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error al preparar la consulta: " . $conn->error);
    }

    $stmt->bind_param("sss", $rut_usuario, $pregunta, $foto_pregunta);
    if (!$stmt->execute()) {
        die("Error al ejecutar la consulta: " . $stmt->error);
    }
    $stmt->close();
    // Redirigir después de eliminar
    header("Location: foro.php");
    exit();
}

// Procesar la respuesta a una pregunta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['responder_pregunta'])) {
    $id_pregunta = $_POST['id_pregunta'];
    $rut_usuario = $_SESSION['rut']; // Reemplaza con el RUT del usuario autenticado
    $respuesta = $_POST['respuesta'];

    // Insertar la respuesta en la base de datos
    $sql = "INSERT INTO foro_respuestas (id_pregunta, rut_usuario, respuesta) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $id_pregunta, $rut_usuario, $respuesta);
    $stmt->execute();
    $id_respuesta = $stmt->insert_id; // Obtener el ID de la respuesta insertada

    // Verificar si se subió una imagen para la respuesta
    if (isset($_FILES['foto_respuesta']) && $_FILES['foto_respuesta']['error'] == 0) {
        $ruta_imagen = 'uploads/' . basename($_FILES['foto_respuesta']['name']);
        move_uploaded_file($_FILES['foto_respuesta']['tmp_name'], $ruta_imagen);

        // Guardar la imagen en la tabla `imagenes_respuesta`
        $sql_imagen = "INSERT INTO imagenes_respuesta (id_respuesta, ruta_imagen) VALUES (?, ?)";
        $stmt_imagen = $conn->prepare($sql_imagen);
        $stmt_imagen->bind_param("is", $id_respuesta, $ruta_imagen);
        $stmt_imagen->execute();
        $stmt_imagen->close();
        
    }
    $stmt->close();
    // Redirigir después de eliminar
    header("Location: foro.php");
    exit();
}

// Obtener todas las preguntas
$sql_preguntas = "SELECT fp.*, p.nombre, p.imagen 
                    FROM foro_preguntas fp 
                    INNER JOIN personal p ON fp.rut_usuario = p.rut 
                    ORDER BY fecha_creacion DESC";
$result_preguntas = $conn->query($sql_preguntas);

// Procesar la eliminación de una pregunta
if (isset($_GET['eliminar_pregunta'])) {
    $id_pregunta = $_GET['eliminar_pregunta'];
    $rut_usuario = $_SESSION['rut']; // Usuario autenticado

    // Verificar si el usuario es el propietario de la pregunta
    $sql = "DELETE FROM foro_preguntas WHERE id_pregunta = ? AND rut_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id_pregunta, $rut_usuario);
    $stmt->execute();
    $stmt->close();
    // Redirigir después de eliminar
    header("Location: foro.php");
    exit();
}

// Procesar la eliminación de una respuesta
if (isset($_GET['eliminar_respuesta'])) {
    $id_respuesta = $_GET['eliminar_respuesta'];
    $rut_usuario = $_SESSION['rut']; // Usuario autenticado

    // Verificar si el usuario es el propietario de la respuesta
    $sql = "DELETE FROM foro_respuestas WHERE id_respuesta = ? AND rut_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id_respuesta, $rut_usuario);
    $stmt->execute();
    $stmt->close();

    // Redirigir después de eliminar
    header("Location: foro.php");
    exit();
}

// Procesar la actualización de una pregunta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_pregunta'])) {
    $id_pregunta = $_POST['id_pregunta'];
    $pregunta = $_POST['pregunta'];
    $rut_usuario = $_SESSION['rut'];

    // Actualizar la pregunta si el usuario es el propietario
    $sql = "UPDATE foro_preguntas SET pregunta = ? WHERE id_pregunta = ? AND rut_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sis", $pregunta, $id_pregunta, $rut_usuario);
    $stmt->execute();
    $stmt->close();
    // Redirigir después de eliminar
    header("Location: foro.php");
    exit();
}


?>

<!DOCTYPE php>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foro</title>
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
        .container { max-width: 900px; margin: auto; padding: 20px; font-family: Arial, sans-serif; }
        .question { max-width: 900px; }
        .question-form, .question { border: 1px solid #ddd; padding: 15px; padding-top: 3px; border-radius: 5px; margin-bottom: 20px; }
        .question-form textarea, .response-form textarea { width: 100%; }
        .question img, .response img { max-width: 100%; margin-top: 10px; }
        .cont_img {
            max-width: 100%;
            margin-top: 10px;
            display: flex; /* Cambiamos el display a flex */
            justify-content: center; /* Centra horizontalmente */
            align-items: center; /* Centra verticalmente si fuera necesario */
        }
        .response { margin-left: 50px; max-width: 600px; border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px; }
        .response-form { margin-left: 40px; max-width: 600px; padding: 10px; border-top: 1px solid #ddd; }
        .user-profile { display: flex; align-items: center; }
        .user-profile img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-right: 15px; }
        .user-profile p { margin: 0; font-weight: none; font-size: 0.9rem; }
        .dropbtn {
            background: #4a90e2;
            border: none;
            font-size: 0; /* Esconde cualquier espacio extra del botón */
            cursor: pointer;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center; /* Centra verticalmente */
            justify-content: center; /* Centra horizontalmente */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .dropbtn:hover {
            background-color: #357abd;
            transform: scale(1.1);
        }

        .dropbtn p {
            font-size: 1.9rem; /* Tamaño de los tres puntos */
            color: white; /* Asegura que los puntos sean visibles */
            font-family: Arial, sans-serif; /* Uniformidad */
            text-align: center; /* Por si acaso */
        }
        .pregunta-cont {
            position: relative;
        }

        .response {
            position: relative;
        }

        .dropdown {
            position: absolute;
            top: 20px;
            right: 10px;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #fff;
            border-radius: 8px;
            min-width: 180px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            overflow: hidden;
        }

        .dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            font-size: 14px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
            color: #000;
        }

        .dropdown-content a.text-danger:hover {
            background-color: #ffe6e6;
            color: #d9534f;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown:hover .dropbtn {
            background-color: #357abd;
        }

        .solicitud-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 1000px;
        }
        
        /* Media queries para pantallas pequeñas */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .question-form, 
            .question,
            .response-form {
                margin-left: 0;
                max-width: 100%;
            }

            .response {
                margin-left: 0;
                max-width: 100%;
            }

            .user-profile img {
                width: 50px;
                height: 50px;
                margin-right: 10px;
            }

            .dropbtn {
                width: 35px;
                height: 35px;
            }

            .solicitud-container {
                padding: 20px;
                width: 100%;
            }
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
        <h1>Portal de Foro</h1>
    </header>


<button id="scrollToTop" class="scroll-to-top1">↑ </button>

<div class="solicitud-container-wrapper">

<div class="solicitud-container">

<div class="container" style="padding: 0px;">
    <!-- Formulario para crear una nueva pregunta -->
    <h2>Foro de Preguntas y Respuestas</h2>
    <h6 style="text-align: center;">
        ¡Bienvenido al Foro de Preguntas y Respuestas! Aquí puedes participar compartiendo tus inquietudes, resolviendo dudas y ayudando a otros con tus conocimientos. 
        Todas las preguntas y respuestas están diseñadas para fomentar un ambiente de colaboración y aprendizaje. 
        Recuerda que tus aportes son valiosos, así que no tengas miedo de participar. ¡Estamos aquí para apoyarnos mutuamente!
    </h6>

    <div class="question-form" style="margin-top:30px;">
        <form action="foro.php" method="POST" enctype="multipart/form-data">
        <div class="form-floating" style="margin-bottom: 20px;">
            <textarea class="form-control" placeholder="Escribe tu pregunta aqui..." style=" height: 150px;margin-top:20px;"name="pregunta" id="floatingTextarea"></textarea>
            <label for="floatingTextarea">¿Que te estas Preguntando?... </label>
            </div>            
        <input type="file"  class="form-control" id="inputGroupFile01" name="foto_pregunta" accept="image/*"><br><br>
        <input type="hidden" name="crear_pregunta" value="1">
        <button type="submit" class="btn btn-primary">Publicar Pregunta</button>
        </form>
    </div>

    <h3>Preguntas hechas en el foro</h3>
    <h6 style="text-align: center; margin-bottom: 20px;">Estas son las preguntas que se han hecho en el foro, para responder y ver las respuesats del foro haciendo click en cualquier pregunta. ¡Recuerda responder con Respeto!</h6>
    <!-- Mostrar todas las preguntas con respuestas -->
        <?php while ($pregunta = $result_preguntas->fetch_assoc()): 
            
            $carpeta_fotos = 'Images/fotos_personal/'; // Cambia esta ruta a la carpeta donde están tus fotos
            $imagen_default = 'Images/profile_photo/imagen_default.jpg'; // Ruta de la imagen predeterminada

            $imagen_user_sop = $pregunta['imagen']; // Se asume que este campo contiene solo el nombre del archivo

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

            // Consulta para obtener el total de respuestas de cada pregunta
            $stmt = $conn->prepare("SELECT COUNT(*) as total_respuestas FROM foro_respuestas WHERE id_pregunta = ?");
            $stmt->bind_param("i", $pregunta['id_pregunta']);
            $stmt->execute();
            $result = $stmt->get_result();
            $total_respuestas = $result->fetch_assoc()['total_respuestas'];
            $stmt->close();
        ?>
               

        <div class="pregunta-cont">
            <div class="question">
                <div class="user-profile">
                    <img id="modal-imagen-usuario" src="<?php echo $imagen_final_user; ?>" alt="Imagen del usuario" class="img-fluid rounded-circle me-3">
                    <p><strong id="modal-usuario"><?php echo htmlspecialchars($pregunta['nombre']); ?></strong></p>
                </div>
                <button class="btn btn-link w-100 text-start text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $pregunta['id_pregunta']; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $pregunta['id_pregunta']; ?>">
                    <p style="font-size: 1.3rem; margin-top: 10px; text-decoration: none; color: #008AC9;">
                        <strong><?php echo htmlspecialchars($pregunta['pregunta']); ?></strong>
                    </p>
                </button>

                <div class="cont_img">
                <?php if ($pregunta['foto_pregunta']): ?>
                    <img src="<?php echo $pregunta['foto_pregunta']; ?>" alt="Imagen de la pregunta">
                <?php endif; ?>
                </div>
                <!-- Botones de acciones -->
                <!-- Dropdown Menu -->
        <?php if ($pregunta['rut_usuario'] === $_SESSION['rut']): ?>

            <div class="dropdown" style="float:right;">
            <button class="dropbtn"><p>...</p></button>
            <div class="dropdown-content">
                <a href="#" data-bs-toggle="modal" data-bs-target="#modalActualizarPregunta-<?php echo $pregunta['id_pregunta']; ?>">Actualizar</a>
                <a href="foro.php?eliminar_pregunta=<?php echo $pregunta['id_pregunta']; ?>" class="text-danger">Eliminar</a>      
            </div>
            </div>

        <!-- Modal para actualizar la pregunta -->
        <div class="modal fade" id="modalActualizarPregunta-<?php echo $pregunta['id_pregunta']; ?>" tabindex="-1" aria-labelledby="modalActualizarPreguntaLabel-<?php echo $pregunta['id_pregunta']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalActualizarPreguntaLabel-<?php echo $pregunta['id_pregunta']; ?>">Actualizar Pregunta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <form action="foro.php" method="POST">
                            <input type="hidden" name="id_pregunta" value="<?php echo $pregunta['id_pregunta']; ?>">
                            <textarea name="pregunta" class="form-control" rows="3" required><?php echo htmlspecialchars($pregunta['pregunta']); ?></textarea>
                            <button type="submit" name="actualizar_pregunta" class="btn btn-primary mt-3">Guardar cambios</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
        <div class="d-flex justify-content-between align-items-center mt-2" style="background-color: white;">
        <p class="text-muted mb-0 date-time">
                Fecha: <span><?php echo date("d-m-Y", strtotime($pregunta['fecha_creacion'])); ?></span>
                <span style="margin-left: 5px;"><?php echo date("H:i:s", strtotime($pregunta['fecha_creacion'])); ?></span>
            </p>
            <p class="text-muted mb-0">Respuestas: <?php echo $total_respuestas; ?></p>
        </div>
            </div>
            
            <!-- Respuestas colapsables -->
            <div id="collapse-<?php echo $pregunta['id_pregunta']; ?>" class="collapse">
                <div class="response-form">
                    <form action="foro.php" method="POST" enctype="multipart/form-data">
                    <div class="form-floating" style="margin-bottom: 20px;">
                        <textarea class="form-control" placeholder="Escribe tu Respuesta aqui..." style=" height: 150px;margin-top:20px;"name="respuesta" id="floatingTextarea"></textarea>
                        <label for="floatingTextarea">Escribe tu Respuesta</label>
                    </div>                          
                    <input type="file"  class="form-control" id="inputGroupFile01" name="foto_respuesta" accept="image/*"><br><br>
                        <input type="hidden" name="id_pregunta" value="<?php echo $pregunta['id_pregunta']; ?>">
                        <input type="hidden" name="responder_pregunta" value="1">
                        <button type="submit" class="btn btn-secondary">Responder</button>
                    </form>
                </div>

                 <!-- Mostrar las respuestas de esta pregunta -->
            <?php
            // Consulta para obtener las respuestas con un parámetro para id_pregunta
                $sql_respuestas = "SELECT r.*, i.ruta_imagen, p.nombre, p.imagen 
                FROM foro_respuestas r
                LEFT JOIN imagenes_respuesta i ON r.id_respuesta = i.id_respuesta
                INNER JOIN personal p ON r.rut_usuario = p.rut
                WHERE r.id_pregunta = ? 
                ORDER BY r.fecha_respuesta DESC";

                // Preparar la consulta
                $stmt = $conn->prepare($sql_respuestas);
                if ($stmt === false) {
                die("Error al preparar la consulta: " . $conn->error);
                }

                // Pasar el id_pregunta como parámetro
                $stmt->bind_param("i", $pregunta['id_pregunta']);

                // Ejecutar la consulta
                $stmt->execute();

                // Obtener los resultados
                $result_respuestas = $stmt->get_result();
            
                while ($respuesta = $result_respuestas->fetch_assoc()):
                    
                    $carpeta_fotos = 'Images/fotos_personal/'; // Cambia esta ruta a la carpeta donde están tus fotos
                    $imagen_default = 'Images/profile_photo/imagen_default.jpg'; // Ruta de la imagen predeterminada

                    $imagen_user_sop = $respuesta['imagen']; // Se asume que este campo contiene solo el nombre del archivo

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
                    <div class="pregunta-cont">
                    <div class="response">
                        <div class="user-profile">
                            <img id="modal-imagen-usuario" src="<?php echo $imagen_final_user; ?>" alt="Imagen del usuario" class="img-fluid rounded-circle me-3">
                            <p><strong id="modal-usuario"><?php echo htmlspecialchars($respuesta['nombre']); ?></strong></p>
                        </div>
                        <p style="margin-top: 10px;"><?php echo htmlspecialchars($respuesta['respuesta']); ?></p>
                        <div class="cont_img">
                            <?php if ($respuesta['ruta_imagen']): ?>
                                <img src="<?php echo $respuesta['ruta_imagen']; ?>" alt="Imagen de la respuesta">
                            <?php endif; ?>
                        </div>
                        
                        <!-- Dropdown de acciones -->
                        <?php if ($respuesta['rut_usuario'] === $_SESSION['rut']): ?>
                            <div class="dropdown mt-3  justify-content-end">
                                <button class="dropbtn btn btn-outline-primary btn-sm rounded-circle">
                                    <p>...</p>
                                </button>
                                <div class="dropdown-content shadow">
                                    <!-- Opción para Actualizar -->
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#modalActualizarRespuesta-<?php echo $respuesta['id_respuesta']; ?>">Actualizar</a>
                                    <a href="foro.php?eliminar_respuesta=<?php echo $respuesta['id_respuesta']; ?>" class="text-danger">Eliminar</a>
                                    </div>
                            </div>

                            <!-- Modal para actualizar la respuesta -->
                            <div class="modal fade" id="modalActualizarRespuesta-<?php echo $respuesta['id_respuesta']; ?>" tabindex="-1" aria-labelledby="modalActualizarRespuestaLabel-<?php echo $respuesta['id_respuesta']; ?>" aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalActualizarRespuestaLabel-<?php echo $respuesta['id_respuesta']; ?>">Actualizar Respuesta</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="foro.php" method="POST">
                                                <input type="hidden" name="id_respuesta" value="<?php echo $respuesta['id_respuesta']; ?>">
                                                <textarea name="respuesta" class="form-control" rows="3" required><?php echo htmlspecialchars($respuesta['respuesta']); ?></textarea>
                                                <button type="submit" name="actualizar_respuesta" class="btn btn-primary mt-3">Guardar cambios</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p style="margin-top:20px;"><small>Fecha: <?php echo date("d-m-Y, H:i", strtotime($respuesta['fecha_respuesta'])); ?>
                            </small></p>

                        </div>
                        <?php endif; ?>

                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

</div>
</div>

</div>
</div>
</div>

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

<script>
    document.getElementById('scrollToTop').addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
</script>
<!-- Scripts de Bootstrap -->
<script src="scripts/script.js"></script>
<script src="scripts/script_cards.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
