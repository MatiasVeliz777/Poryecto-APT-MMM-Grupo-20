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
$sql = "SELECT rut, nombre, correo, imagen, cargo_id, rol_id, admin
        FROM personal 
        WHERE rut = (SELECT rut FROM usuarios WHERE nombre_usuario = '$usuario')";;
$result = $conn->query($sql);

// Verificar si se encontró el usuario
if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc(); // Extraer los datos del usuario
    $rol = $user_data['rol_id'];
    $rut = $user_data['rut'];
    // Guardar el rol en la sesión
    $rol = $user_data['rol_id'];
    $admin = $user_data['admin'];
    // Guardar el rol en la sesión
    $_SESSION['rol'] = $rol;
    $_SESSION['admin'] = $admin;
} else {
    $error = "No se encontraron datos para el usuario.";
}


$sql_cargo = "SELECT NOMBRE_CARGO FROM cargos WHERE id = '" . $user_data['cargo_id'] . "'";

$result_cargo = $conn->query($sql_cargo);

if ($result_cargo->num_rows > 0) {
    $cargo_data = $result_cargo->fetch_assoc(); // Extraer los datos del usuario
} else {
    $error = "No se encontraron datos para el cargo.";
}

// Procesar la solicitud cuando se envía el formulario
$solicitudEnviada = false;
$solicitud_opc = false; // Indica si la operación fue exitosa
$error_opc = false; 
$solicitudDuplicada = false;

$rutInvalido = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Procesar el formulario de creación de cargo
    // Crear un cargo desde el modal
    if (isset($_POST['action']) && $_POST['action'] === 'crear_cargo') {
        $nombre_cargo = $_POST['nombre_cargo'];
        $sql = "INSERT INTO cargos (NOMBRE_CARGO) VALUES ('$nombre_cargo')";
        if ($conn->query($sql) === TRUE) {
            $solicitud_opc = true; // Marca la operación como exitosa
        } else {
            $error_opc = true; // Marca la operación como fallida
        }
        exit;
    }

    // Crear un rol desde el modal
    if (isset($_POST['action']) && $_POST['action'] === 'crear_rol') {
        $nombre_rol = $_POST['nombre_rol'];
        $sql = "INSERT INTO roles (rol) VALUES ('$nombre_rol')";
        if ($conn->query($sql) === TRUE) {
            $solicitud_opc = true; // Marca la operación como exitosa
        } else {
            $error_opc = true; // Marca la operación como fallida
        }
        exit;
    }

    // Función para validar el formato del RUT
    function validarFormatoRUT($rut) {
        // Expresión regular para el formato xx.xxx.xxx-x
        return preg_match('/^\d{1,2}\.\d{3}\.\d{3}-[0-9kK]{1}$/', $rut);
    }
    
    $apellidos = $_POST['apellidos'];
    $nombres = $_POST['nombres'];
    $rut_personal = strtoupper($_POST['rut_personal']);
    $correo = $_POST['correo'];
    $fecha_nac = $_POST['fecha_nac'];
    $cargo_id_ag = $_POST['nom-cargo']; // Recibir el cargo seleccionado
    $rol_id_ag = $_POST['nom-rol']; 
    
    $usuario = $_SESSION['usuario'];

    // Verificar el formato del RUT
    if (!validarFormatoRUT($rut_personal)) {
        $rutInvalido = true; // Indicar que hubo un error por formato inválido
    }

    if (!$rutInvalido) {
    // Concatenar apellidos y nombres y convertir todo a mayúsculas
    $nombre_completo = strtoupper($apellidos . " " . $nombres);
    // Agregar un 0 delante del RUT
    $rut_personal = '0' . $rut_personal;

    // Verificar si se ha subido una imagen
    if (!empty($_FILES['imagen']['name'])) {
        // Obtener la extensión original del archivo
        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);

        // Crear el nombre del archivo usando el nombre completo y la extensión
        $imagen = $nombre_completo . "." . $extension;
        $imagen_tmp = $_FILES['imagen']['tmp_name'];

        // Establecer la carpeta de destino
        $imagen_folder = 'Images/fotos_personal/' . $imagen;

        // Mover la imagen a la carpeta de fotos_personal
        if (move_uploaded_file($imagen_tmp, $imagen_folder)) {
            $imagen_db = $imagen;
        } else {
            $error_opc = true;
            exit();
        }
    } else {
        $imagen_db = $nombre_completo;
    }


    // Insertar los datos del nuevo empleado en la tabla personal
    $sql = "INSERT INTO personal (rut, nombre, correo, imagen, fecha_nacimiento, cargo_id, rol_id) 
            VALUES ('$rut_personal', '$nombre_completo', '$correo', " . ($imagen_db ? "'$imagen_db'" : "NULL") . ", '$fecha_nac', $cargo_id_ag, $rol_id_ag)";

    if ($conn->query($sql) === TRUE) {

        // Función para normalizar nombres de archivos reemplazando caracteres especiales
            function normalizar_nombre($nombre) {
                $buscar = array('á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ');
                $reemplazar = array('a', 'e', 'i', 'o', 'u', 'n', 'A', 'E', 'I', 'O', 'U', 'N');
                return str_replace($buscar, $reemplazar, $nombre);
            }

            // Ruta de la carpeta donde están las imágenes
            $carpeta_fotos = 'Images/fotos_personal/';

            // Consultar todas las filas de la tabla `personal`
            $sql_new = "SELECT rut, imagen FROM personal";
            $result_new = $conn->query($sql_new);

            if ($result_new->num_rows > 0) {
                while ($row = $result_new->fetch_assoc()) {
                    $rut = $row['rut'];
                    $imagen_original = $row['imagen'];

                    // Normalizar el nombre de la imagen
                    $imagen_normalizada = normalizar_nombre($imagen_original);

                    // Verificar si el nombre original es diferente al normalizado
                    if ($imagen_original !== $imagen_normalizada) {
                        // Cambiar el nombre del archivo en la carpeta
                        $ruta_original = $carpeta_fotos . $imagen_original;
                        $ruta_normalizada = $carpeta_fotos . $imagen_normalizada;

                        if (file_exists($ruta_original)) {
                            rename($ruta_original, $ruta_normalizada);
                        }

                        // Actualizar el nombre de la imagen en la base de datos
                        $sql_update = "UPDATE personal SET imagen = '$imagen_normalizada' WHERE rut = '$rut'";
                        if ($conn->query($sql_update) === TRUE) {
                            
                        } else {
                            echo "Error al actualizar la imagen para RUT: $rut: " . $conn->error . "<br>";
                        }
                    }
                }
            } else {
                echo "No se encontraron registros en la tabla `personal`.<br>";
            }

        // Inserta la notificación en la tabla
        $mensaje = "!Un nuevo empleado se ha unido a la  de la empresa, ve a darle la bienvenida a $nombre_completo !";
        $query = "
                INSERT INTO notificaciones (rut, mensaje, fecha_creacion)
                SELECT rut, ?, NOW()
                FROM usuarios
            ";

            // Preparamos la consulta
            $stmt = $conn->prepare($query);

            // Verificamos si la preparación fue exitosa
            if ($stmt) {
                // Vinculamos el mensaje como parámetro
                $stmt->bind_param("s", $mensaje); // 's' indica que el parámetro es una cadena

                // Ejecutamos la consulta
                if ($stmt->execute()) {
                    $solicitudEnviada = true;  // Marcamos que la solicitud se ha enviado correctamente
                } else {
                    $error_opc = true; // Si hay un error en la ejecución
                }

                // Cerramos la consulta
                $stmt->close();
            } else {
                $error_opc = true; // Si no se pudo preparar la consulta
            }
    } else {
        $solicitudDuplicada = true;
    }
}
}

// Consulta para obtener todos los cargos
$sql_cargo_ag = "SELECT id, NOMBRE_CARGO FROM cargos";
$result_cargo_ag = $conn->query($sql_cargo_ag);

// Consulta para obtener todos los cargos
$sql_rol_ag = "SELECT id, rol FROM roles";
$result_rol_ag = $conn->query($sql_rol_ag);

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



$conn->close();
?>

<!DOCTYPE php>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal</title>
    <link rel="stylesheet" href="styles/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
     <!-- SweetAlert2 -->
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>    
    <!-- Lineicons -->
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> a{text-decoration: none;}</style>
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
                    <a href="home.php">Intranet</a>
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
                    <a href="home.php" class="sidebar-link">
                    <i class="lni lni-home"></i>

                           <span>Inicio</span>
                    </a>
                </li>
            <?php if ($_SESSION['admin'] == 1): ?>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#añadir" aria-expanded="false" aria-controls="añadir">
                        <i class="lni lni-circle-plus"></i>
                        <span>Añadir</span>
                    </a>
                    <ul id="añadir" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        
                    <li class="sidebar-item">
                        <a href="agregar_personal.php" class="sidebar-link">Agregar Empleado</a>
                    </li>
                        <li class="sidebar-item">
                            <a href="empleado_mes.php" class="sidebar-link">Agregar Empleado del Año</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="felicitaciones_agregar.php" class="sidebar-link">Agregar Felicitacion</a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <li class="sidebar-item">
                    <a href="perfil.php" class="sidebar-link">
                    <i class="lni lni-user"></i>
                        <span>Perfil</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#multi" aria-expanded="false" aria-controls="multi">
                        <i class="lni lni-users"></i>
                        <span>Personal</span>
                    </a>
                    <ul id="multi" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="empleados_meses.php" class="sidebar-link">Empleado del Año</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="felicitaciones.php" class="sidebar-link">Felicitaciones</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="personal_nuevo.php" class="sidebar-link">Nuevos empleados</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="cumpleanos.php" class="sidebar-link">Cumpleaños</a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-item">
                    <a href="calendario_prueba.php" class="sidebar-link">
                    <i class="lni lni-calendar"></i>
                    <span>Empresa</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="capacitaciones.php" class="sidebar-link">
                        <i class="lni lni-agenda"></i>
                        <span>Capacitaciones</span>
                    </a>
                </li>

                <?php if ($_SESSION['admin'] == 1): ?>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#encuestas" aria-expanded="false" aria-controls="encuestas">
                        <i class="lni lni-pencil"></i>
                        <span>Encuestas</span>
                    </a>
                    <ul id="encuestas" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        
                    <li class="sidebar-item">
                            <a href="crear_encuesta.php" class="sidebar-link">Crear encuesta</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="encuestas.php" class="sidebar-link">Encuestas</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="respuestas.php" class="sidebar-link">Respuestas de encuestas</a>
                        </li>
                    </ul>
                </li>
                <?php else: ?>
                    <li class="sidebar-item">
                    <a href="encuestas.php" class="sidebar-link">
                    <i class="lni lni-pencil"></i>
                    <span>Encuestas</span>
                    </a>
                </li>
                <?php endif; ?>
            
                <li class="sidebar-item">
                    <a href="documentos.php" class="sidebar-link">
                        <i class="lni lni-files"></i>
                        <span>Documentos</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="foro.php" class="sidebar-link">
                    <i class="lni lni-comments"></i>
                    <span>Foro</span>
                    </a>
                </li>

                <?php if ($_SESSION['rol'] == 4 || $_SESSION['admin'] == 1): ?>
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
                        <i class="lni lni-cog"></i>
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

            <?php if ($_SESSION['admin'] == 1): ?>
            <li class="sidebar-item">
                    <a href="estadisticas.php" class="sidebar-link">
                    <i class="lni lni-bar-chart"></i>
                    <span>Estadisticas</span>
                    </a>
            </li>
            <?php endif; ?>
            </ul>

            <div class="sidebar-footer" style="margin-bottom: 20px;">
                <a href="cerrar_sesion.php" class="sidebar-link">
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
                <div class="user-nom" style="padding: 15px;">
                <div class="notificaciones-container">
                    <span class="campanita" id="campanita">
                        🔔
                        <span class="campanita-badge" id="campanita-badge"></span>
                    </span>
                    <div class="notificaciones-desplegable" id="notificaciones">
                        <div class="notificaciones-header">
                            <h5 style="font-size: 1.4rem; margin-bottom: 3px;">📥 Notificaciones 📥</h5>
                        </div>
                        <div id="contenido-notificaciones">
                            <p style="text-align: center; color: #888;">Cargando...</p>
                        </div>
                    </div>
                </div>

                </div>
            </div>
        </div>

        <script>
document.addEventListener('DOMContentLoaded', function () {
    const campanita = document.getElementById('campanita');
    const campanitaBadge = document.getElementById('campanita-badge');
    const notificacionesDesplegable = document.getElementById('notificaciones');
    const contenidoNotificaciones = document.getElementById('contenido-notificaciones');
    let notificacionesAbiertas = false; // Bandera para rastrear si el desplegable está abierto

    // Obtener notificaciones desde el servidor
    async function obtenerNotificaciones() {
        try {
            const response = await fetch('notificaciones.php');
            const notificaciones = await response.json();

            contenidoNotificaciones.innerHTML = '';
            if (notificaciones.length > 0) {
                notificaciones.forEach(notif => {
                    const div = document.createElement('div');
                    div.classList.add('notificacion');
                    div.classList.add(notif.leida === "0" ? 'no-leida' : 'leida');
                    div.innerHTML = `
                        <p>${notif.mensaje}</p>
                        <div class="fecha-con-eliminar">
                            <span class="fecha">${new Date(notif.fecha_creacion).toLocaleString()}</span>
                            <button class="notificacion-eliminar" data-id="${notif.id}">❌</button>
                        </div>
                    `;
                    contenidoNotificaciones.appendChild(div);
                });

                // Añadir eventos para los botones de eliminar
                document.querySelectorAll('.notificacion-eliminar').forEach(btn => {
                    btn.addEventListener('click', async function() {
                        const id = this.getAttribute('data-id');
                        const notificacion = this.closest('.notificacion'); // Obtener el contenedor de la notificación
                        notificacion.classList.add('eliminando'); // Añadir clase de animación

                        // Esperar a que termine la animación antes de eliminar
                        setTimeout(async () => {
                            await eliminarNotificacion(id); // Llamada para eliminar la notificación desde el backend
                            notificacion.remove(); // Eliminar el nodo del DOM
                        }, 300); // Espera el tiempo de la transición antes de eliminar el nodo
                    });
                });
            } else {
                contenidoNotificaciones.innerHTML = '<p style="text-align: center; color: #888;">No hay notificaciones.</p>';
            }

            // Actualizar badge
            const nuevasNotificaciones = notificaciones.filter(notif => notif.leida === "0");
            if (nuevasNotificaciones.length > 0) {
                campanitaBadge.textContent = nuevasNotificaciones.length;
                campanitaBadge.style.display = 'inline-block';
            } else {
                campanitaBadge.style.display = 'none';
            }
        } catch (error) {
            console.error('Error al obtener notificaciones:', error);
        }
    }

    // Marcar notificaciones como leídas
    async function marcarNotificacionesLeidas() {
        try {
            const response = await fetch('marcar_leidas.php', { method: 'POST' });
            const resultado = await response.json();

            if (resultado.success) {
                // Cambiar las notificaciones a "leída"
                document.querySelectorAll('.notificacion.no-leida').forEach(notificacion => {
                    notificacion.classList.remove('no-leida');
                    notificacion.classList.add('leida');
                });
                // Actualizar badge
                campanitaBadge.style.display = 'none';
            }
        } catch (error) {
            console.error('Error al marcar como leídas:', error);
        }
    }

    // Eliminar notificación
    async function eliminarNotificacion(id) {
        try {
            const response = await fetch('notificacion_eliminar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            });

            const result = await response.json();
            if (result.success) {
                // Eliminar la notificación del DOM
                const notifElement = document.querySelector(`button[data-id="${id}"]`).closest('.notificacion');
                notifElement.remove();
            } else {
                console.error('Error al eliminar la notificación');
            }
        } catch (error) {
            console.error('Error al eliminar la notificación:', error);
        }
    }

    // Alternar desplegable
    campanita.addEventListener('click', () => {
        notificacionesAbiertas = !notificacionesAbiertas; // Alternar estado

        // Mostrar/ocultar desplegable
        notificacionesDesplegable.classList.toggle('active');

        if (!notificacionesAbiertas) {
            // Si se cierra el desplegable, marcar como leídas
            marcarNotificacionesLeidas();
        }
    });

    // Consultar cada 5 segundos
    setInterval(obtenerNotificaciones, 5000);

    // Cargar al inicio
    obtenerNotificaciones();
});

    </script>

        

        <div class="topnav">
        <a href="home.php" class="mr-active">Intranet</a>
        <div id="mobileLinks">
            <!-- Agregar elementos del menú existente -->
            <a href="perfil.php"><i class="lni lni-user"style="margin-right: 10px;"></i>Perfil</a>
            <?php if ($_SESSION['admin'] == 1): ?>
                <a href="agregar_personal.php"><i class="lni lni-users"style="margin-right: 10px;"></i>Agregar Personal</a>
                <a href="empleado_mes.php"><i class="lni lni-users"style="margin-right: 10px;"></i>Agregar Empleado del Mes</a>
                <a href="felicitaciones_agregar.php"><i class="lni lni-users"style="margin-right: 10px;"></i>Agregar Felicitación</a>

            <?php endif; ?>
            <a href="personal_nuevo.php"><i class="lni lni-users"style="margin-right: 10px;"></i>Personal</a>
            <a href="felicitaciones.php"><i class="lni lni-users"style="margin-right: 10px;"></i>Felicitaciones</a>
            <a href="empleados_meses.php"><i class="lni lni-users"style="margin-right: 10px;"></i>Empleado del mes</a>
            <a href="cumpleanos.php"><i class="lni lni-calendar"style="margin-right: 10px;"></i>Cumpleaños</a>
            <a href="calendario_prueba.php"><i class="lni lni-calendar"style="margin-right: 10px;"></i>Eventos</a>
            <a href="capacitaciones.php"><i class="lni lni-agenda"style="margin-right: 10px;"></i>Capacitaciones</a>
            <a href="documentos.php"><i class="lni lni-files"style="margin-right: 10px;"></i>Documentos</a>
            <a href="foro.php"><i class="lni lni-comments"style="margin-right: 10px;"></i>Foro</a>
            <a href="encuestas.php"><i class="lni lni-pencil"style="margin-right: 10px;"></i>Encuestas</a>
            <?php if ($_SESSION['admin'] == 1): ?>
                <a href="crear_encuesta.php">Crear Encuesta</a>
                <a href="respuestas.php">Respuestas de encuestas</a>
            <?php endif; ?>
            <a href="solicitudes.php"><i class="lni lni-popup" style="margin-right: 10px;"></i>Solicitudes</a>
            <?php if ($_SESSION['admin'] == 1): ?>
                <a href="solicitudes_usuarios.php">Ver solicitudes</a>
            <?php endif; ?>
            
            <a href="soporte.php"><i class="lni lni-cog"style="margin-right: 10px;"></i>Soporte Informático</a>
            <?php if ($_SESSION['rol'] == 4): ?>
                <a href="soporte_def.php">ver soportes</a>
            <?php endif; ?>

            <?php if ($_SESSION['admin'] == 1): ?>
                <a href="estadisticas.php"><i class="lni lni-bar-chart"style="margin-right: 10px;"></i>Estadisticas</a>
                <?php endif; ?>

            <a href="cerrar_sesion.php"><i class="lni lni-exit"style="margin-right: 10px;"></i>Salir</a>
        </div>
        <a href="javascript:void(0);" class="icon" onclick="toggleMobileMenu()">
            <i class="fa fa-bars"></i>
        </a>
    </div>

    <script>
    function toggleMobileMenu() {
        const mobileLinks = document.getElementById("mobileLinks");
        if (mobileLinks.classList.contains("open")) {
            mobileLinks.classList.remove("open");
        } else {
            mobileLinks.classList.add("open");
        }
    }
    </script>
    


    <div class="alertas-container">
    <span class="icono-campana" id="icono-campana">
        🔔
        <span class="badge-campana" id="badge-campana"></span>
    </span>
    <div class="alertas-desplegable" id="alertas">
        <div class="alertas-header">
            <h5 style="font-size: 1.4rem; margin-bottom: 3px;">📥 Alertas 📥</h5>
        </div>
        <div id="contenido-alertas">
            <p style="text-align: center; color: #888;">Cargando...</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const iconoCampana = document.getElementById('icono-campana');
    const badgeCampana = document.getElementById('badge-campana');
    const alertasDesplegable = document.getElementById('alertas');
    const contenidoAlertas = document.getElementById('contenido-alertas');
    let alertasAbiertas = false; // Bandera para rastrear si el desplegable está abierto

    // Obtener alertas desde el servidor
    async function obtenerAlertas() {
        try {
            const response = await fetch('notificaciones.php');
            const alertas = await response.json();

            contenidoAlertas.innerHTML = '';
            if (alertas.length > 0) {
                alertas.forEach(alerta => {
                    const div = document.createElement('div');
                    div.classList.add('alerta');
                    div.classList.add(alerta.leida === "0" ? 'no-leida' : 'leida');
                    div.innerHTML = `
                        <p>${alerta.mensaje}</p>
                        <div class="fecha-con-eliminar">
                            <span class="fecha">${new Date(alerta.fecha_creacion).toLocaleString()}</span>
                            <button class="alerta-eliminar" data-id="${alerta.id}">❌</button>
                        </div>
                    `;
                    contenidoAlertas.appendChild(div);
                });

                // Añadir eventos para los botones de eliminar
                document.querySelectorAll('.alerta-eliminar').forEach(btn => {
                    btn.addEventListener('click', async function() {
                        const id = this.getAttribute('data-id');
                        await eliminarAlerta(id);
                    });
                });
            } else {
                contenidoAlertas.innerHTML = '<p style="text-align: center; color: #888;">No hay alertas.</p>';
            }

            // Actualizar badge
            const nuevasAlertas = alertas.filter(alerta => alerta.leida === "0");
            if (nuevasAlertas.length > 0) {
                badgeCampana.textContent = nuevasAlertas.length;
                badgeCampana.style.display = 'inline-block';
            } else {
                badgeCampana.style.display = 'none';
            }
        } catch (error) {
            console.error('Error al obtener alertas:', error);
        }
    }

    // Marcar alertas como leídas
    async function marcarAlertasLeidas() {
        try {
            const response = await fetch('marcar_leidas.php', { method: 'POST' });
            const resultado = await response.json();

            if (resultado.success) {
                // Cambiar las alertas a "leída"
                document.querySelectorAll('.alerta.no-leida').forEach(alerta => {
                    alerta.classList.remove('no-leida');
                    alerta.classList.add('leida');
                });
                // Actualizar badge
                badgeCampana.style.display = 'none';
            }
        } catch (error) {
            console.error('Error al marcar como leídas:', error);
        }
    }

    // Eliminar alerta
        async function eliminarAlerta(id) {
            try {
                const response = await fetch('notificacion_eliminar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                });

                const result = await response.json();
                if (result.success) {
                    // Buscar la alerta y añadir la clase de animación
                    const alertaElement = document.querySelector(`button[data-id="${id}"]`).closest('.alerta');
                    alertaElement.classList.add('eliminando'); // Añadir clase de animación

                    // Esperar a que termine la animación antes de eliminar
                    setTimeout(() => {
                        alertaElement.remove(); // Eliminar el nodo del DOM
                    }, 300); // Esperar el tiempo de la transición antes de eliminar el nodo
                } else {
                    console.error('Error al eliminar la alerta');
                }
            } catch (error) {
                console.error('Error al eliminar la alerta:', error);
            }
        }

    // Alternar desplegable
    iconoCampana.addEventListener('click', () => {
        alertasAbiertas = !alertasAbiertas; // Alternar estado

        // Mostrar/ocultar desplegable
        alertasDesplegable.classList.toggle('active');

        if (!alertasAbiertas) {
            // Si se cierra el desplegable, marcar como leídas
            marcarAlertasLeidas();
        }
    });

    // Consultar cada 5 segundos
    setInterval(obtenerAlertas, 1000);

    // Cargar al inicio
    obtenerAlertas();
});
</script>


    <header class="solicitud-header">
    <h1>Añadir personal nuevo</h1>
</header>

<div class="solicitud-container-wrapper">
    <!-- Box para las instrucciones -->
    <div class="solicitud-instructions">
        <h3>Como añadir un nuevo usuario</h3>
        <p>1. Ingresa los apellidos y los nombres de la persona de desea agregar.</p>
        <p>2. Ingresa el rut de la persona con el foramto indicado en la celda (Si no coincide con el formato, se presentaran problemas.).</p>
        <p>4. Procurar no equivocarse en el correo ya que afectaria al usuario.</p>
        <p>5. Seleccionar la fecha de nacimiento (Tambien se puede escribir colocando el dia, mes y año).</p>
        <p>6. Selecciona uno de los cargos a los cuales corresponde el nuevo usuario, de no encontrarlo puede crear uno nuevo y asignarselo.</p>
        <p>7. Procurar que la imagen tenga un formato correcto (jpg, png, jpeg, etc).</p>

    </div>

    <!-- Formulario de soporte técnico -->
    <div class="solicitud-container">
        <h2>Añadir Personal</h2>
        <h3>Ingrese los datos</h3>
               
        
        <p>Si no encuentras un Cargo o Rol al cual quieres asignar al empleado, puedes entrar aqui y crear las opciones necesarios.</p>
        <div class="button-container" style="display: flex;justify-content: center;align-items: center;margin-top: 20px; /* Espaciado opcional superior */  margin-bottom: 20px; /* Espaciado opcional inferior */">
    
        <!-- Botón para abrir el modal -->
            <button type="button" class="solicitud-submit-btn" style="padding: 8px;font-size: 1rem;width: 40%; margin-bottom: 15px;" data-bs-toggle="modal" data-bs-target="#modalGestion">
                Crear Rol o Cargo
            </button>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modalGestion" tabindex="-1" aria-labelledby="modalGestionLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalGestionLabel">Crear Rol o Cargo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Formulario para crear cargo -->
                        <form id="formCrearCargo">
                            <div class="mb-3">
                                <label for="nombre_cargo" class="form-label">Nombre del Cargo:</label>
                                <input type="text" id="nombre_cargo" name="nombre_cargo" class="form-control">
                                <button type="button" class="btn btn-success mt-2" id="btnCrearCargo">Crear Cargo</button>
                            </div>
                        </form>

                        <hr>

                        <!-- Formulario para crear rol -->
                        <form id="formCrearRol">
                            <div class="mb-3">
                                <label for="nombre_rol" class="form-label">Nombre del Rol:</label>
                                <input type="text" id="nombre_rol" name="nombre_rol" class="form-control">
                                <button type="button" class="btn btn-primary mt-2" id="btnCrearRol">Crear Rol</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <form class="solicitud-form" method="POST" action="agregar_personal.php" enctype="multipart/form-data">
            <!-- Campo para el título -->
            <div class="input-group">
            <i class="fa-solid fa-font"></i>
                <input type="text" name="apellidos" placeholder="Apellidos Paterno y Materno" required>
            </div>

            <div class="input-group">
            <i class="fa-solid fa-font"></i>
                <input type="text" name="nombres" placeholder="Primer y segundo nombre" required>
            </div>

            <div class="input-group">
            <i class="fa-solid fa-fingerprint"></i>
                <input type="text" name="rut_personal" placeholder="Ejemplo: 12.345.678-9" required>
            </div>

            <div class="input-group">
            <i class="fa-solid fa-envelope"></i>
                <input type="email" name="correo" placeholder="Correo del usuario nuevo" required>
            </div>

            <div class="input-group">
            <i class="fa-solid fa-calendar-days"></i>
                <input type="date" name="fecha_nac" placeholder="Fecha de nacimiento" required>
            </div>

            <!-- Selección el cargo del nuevo usuario -->
            <div class="input-group">
                <i class="fa-solid fa-list"></i>
                <select name="nom-cargo" required>
                    <option value="">Seleccione el cargo</option>
                    <?php
                    // Comprobar si la consulta devolvió resultados
                    if ($result_cargo_ag->num_rows > 0) {
                        // Generar las opciones dinámicamente
                        while ($row = $result_cargo_ag->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . $row['NOMBRE_CARGO'] . "</option>";
                        }
                    } else {
                        echo "<option value=''>No hay cargos disponibles</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Selección el rol del nuevo usuario -->
            <div class="input-group">
                <i class="fa-solid fa-list"></i>
                <select name="nom-rol" required>
                    <option value="">Seleccione el Área</option>
                    <?php
                    // Comprobar si la consulta devolvió resultados
                    if ($result_rol_ag->num_rows > 0) {
                        // Generar las opciones dinámicamente
                        while ($row = $result_rol_ag->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . $row['rol'] . "</option>";
                        }
                    } else {
                        echo "<option value=''>No hay cargos disponibles</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Campo para subir imagen -->
            <div class="input-group">
                <i class="fas fa-upload"></i>
                <input type="file" name="imagen">
            </div>

            <!-- Barra de progreso -->
            <div class="progress">
                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>

            <!-- Botón de envío -->
            <button type="submit" class="solicitud-submit-btn">Enviar</button>
        </form>

        <!-- Información de contacto -->
        <div class="contact-info">
            <div>
                <h4>📞 Teléfono</h4>
                <p>+56(9)999-99-99</p>
                <p>+56(9)888-88-88</p>
            </div>
            <div>
                <h4>📧 Correos</h4>
                <p>clincia@gmail.com</p>
                <p>clincia@gmail.com</p>
            </div>
        </div>
    </div>
    
<!-- SweetAlert2 -->

<script>
    // Crear Cargo
    document.getElementById('btnCrearCargo').addEventListener('click', function () {
        const nombreCargo = document.getElementById('nombre_cargo').value;

        if (nombreCargo) {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=crear_cargo&nombre_cargo=${encodeURIComponent(nombreCargo)}`
            })
            .then(response => response.text())
            .then(data => {
                Swal.fire({
                    title: '¡Opción Agregada Exitosamente!',
                    text: 'El Cargo que acabas de crear se agregó correctamente.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload(); // Recargar opciones dinámicamente
                });
            })
            .catch(error => {
                Swal.fire({
                    title: '¡Error al Agregar!',
                    text: 'Hubo un problema al intentar agregar el Cargo. Por favor, inténtalo de nuevo.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        } else {
            Swal.fire({
                title: 'Campo Vacío',
                text: 'Por favor, ingresa un nombre para el Cargo.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
        }
    });

    // Crear Rol
    document.getElementById('btnCrearRol').addEventListener('click', function () {
        const nombreRol = document.getElementById('nombre_rol').value;

        if (nombreRol) {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=crear_rol&nombre_rol=${encodeURIComponent(nombreRol)}`
            })
            .then(response => response.text())
            .then(data => {
                Swal.fire({
                    title: '¡Opción Agregada Exitosamente!',
                    text: 'El Rol que acabas de crear se agregó correctamente.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload(); // Recargar opciones dinámicamente
                });
            })
            .catch(error => {
                Swal.fire({
                    title: '¡Error al Agregar!',
                    text: 'Hubo un problema al intentar agregar el Rol. Por favor, inténtalo de nuevo.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        } else {
            Swal.fire({
                title: 'Campo Vacío',
                text: 'Por favor, ingresa un nombre para el Rol.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
        }
    });
</script>


<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if ($solicitudEnviada) : ?>
<script>
    Swal.fire({
        title: '¡Usuario Agregado Exitosamente!',
        text: 'El usuario que acabas de crear se agrego correctamente',
        icon: 'success',
        confirmButtonText: 'OK'
    });
</script>
<?php endif; ?>

<?php if ($rutInvalido) : ?>
<script>
    Swal.fire({
        title: '¡Error! ¡Rut Invalido!',
        text: 'Revisa el formato del rut del empleado que estas queriendo ingresar, y procura que cumpla con el formato requerido!',
        icon: 'error',
        confirmButtonText: 'OK'
    });
</script>
<?php endif; ?>


<?php if ($solicitudDuplicada) : ?>
    <script>
        Swal.fire({
            title: '¡Error!',
            text: 'El empleado con este RUT ya existe en el sistema. Por favor, verifica los datos.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    </script>
<?php endif; ?>


<!-- Alertas SweetAlert -->
<?php if ($solicitud_opc) : ?>
    <script>
        Swal.fire({
            title: '¡Opción Agregada Exitosamente!',
            text: 'La opción de Cargo o Rol que acabas de crear se agregó correctamente',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    </script>
<?php endif; ?>

<?php if ($error_opc) : ?>
    <script>
        Swal.fire({
            title: '¡Error al Agregar!',
            text: 'Hubo un problema al intentar agregar la opción. Por favor, inténtalo de nuevo.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    </script>
<?php endif; ?>
    
<script>
    // Simulación de progreso en la carga de archivo (opcional)
    document.querySelector('input[type="file"]').addEventListener('change', function() {
        const progress = document.querySelector('.progress');
        const progressBar = document.querySelector('.progress-bar');
        progress.style.display = 'block';

        let width = 0;
        const interval = setInterval(function() {
            if (width >= 100) {
                clearInterval(interval);
            } else {
                width++;
                progressBar.style.width = width + '%';
                progressBar.textContent = width + '%';
            }
        }, 30);
    });
</script>



</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
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
</body>
</html>
