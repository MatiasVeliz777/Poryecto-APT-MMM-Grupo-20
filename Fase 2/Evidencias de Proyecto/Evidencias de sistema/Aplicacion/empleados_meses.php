<?php
include('conexion.php');

// Consulta para obtener todos los empleados del mes
$sql_empleados_mes = "SELECT p.nombre, p.imagen, em.descripcion, c.NOMBRE_CARGO, em.mes_year, em.id
                      FROM empleado_mes em 
                      JOIN personal p ON em.rut = p.rut 
                      JOIN cargos c ON p.cargo_id = c.id
                      ORDER BY em.mes_year DESC";

$result_empleados_mes = $conn->query($sql_empleados_mes);

include('conexion.php'); // Conexión a la base de datos
session_start();

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
    
    return "$dia_nombre, $dia_numero de $mes_nombre de $anio";
}

function traducir_mes($fecha){
    $meses = array("January" => "Enero", "February" => "Febrero", "March" => "Marzo", 
                   "April" => "Abril", "May" => "Mayo", "June" => "Junio", 
                   "July" => "Julio", "August" => "Agosto", "September" => "Septiembre", 
                   "October" => "Octubre", "November" => "Noviembre", "December" => "Diciembre");
    
    $mes_nombre = $meses[date('F', strtotime($fecha))];
    $anio = date('Y', strtotime($fecha));
    
    return "$mes_nombre de $anio";
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

// Mostrar usuarios nuevos del mes actual
$mes_actual = date('m');
$año_actual = date('Y');

// Consulta para obtener usuarios nuevos del mes actual
$sql_nuevos = "SELECT u.rut, u.nombre_usuario, u.fecha_creacion, p.nombre, p.fecha_nacimiento, c.NOMBRE_CARGO, p.imagen 
               FROM usuarios u
               INNER JOIN personal p ON u.rut = p.rut
               INNER JOIN cargos c ON p.cargo_id = c.id
               WHERE MONTH(u.fecha_creacion) = ? AND YEAR(u.fecha_creacion) = ?";

$stmt_nuevos = $conn->prepare($sql_nuevos);
$stmt_nuevos->bind_param('ii', $mes_actual, $año_actual);
$stmt_nuevos->execute();
$result_nuevos = $stmt_nuevos->get_result();

// Obtener el mes y año actuales
$mes_actual = date('m-Y');

// Procesar la solicitud cuando se envía el formulario
$emp_mes_enviada = false;


// Consulta para obtener todos los cargos
$sql_rol_ag = "SELECT id, rol FROM roles";
$result_rol_ag = $conn->query($sql_rol_ag);

$tipo_mensaje = ''; // Variable para el tipo de mensaje
$mensaje = ''; // Variable para el contenido del mensaje

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['actualizar_empleado'])) {
        // Consulta de actualización
        $id = $_POST['id'];
        $descripcion = $_POST['descripcion'];
        
        $sql_update = "UPDATE empleado_mes SET descripcion = '$descripcion' WHERE id = '$id'";
        
        if ($conn->query($sql_update) === TRUE) {
            $tipo_mensaje = 'success';
            $mensaje = 'El empleado del mes ha sido actualizado correctamente.';
        } else {
            $tipo_mensaje = 'error';
            $mensaje = 'Hubo un error al actualizar el empleado del mes.';
        }
    }

    if (isset($_POST['confirmar_eliminar_empleado'])) {
        // Consulta de eliminación
        $id = $_POST['id'];
        
        $sql_delete = "DELETE FROM empleado_mes WHERE id = '$id'";
        
        if ($conn->query($sql_delete) === TRUE) {
            $tipo_mensaje = 'success';
            $mensaje = 'El empleado del mes ha sido eliminado correctamente.';
        } else {
            $tipo_mensaje = 'error';
            $mensaje = 'Hubo un error al eliminar el empleado del mes.';
        }
    }
}


$conn->close();
?>

<!DOCTYPE php>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empleado del Mes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="Images/icono2.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="styles/style_cards.css">
    <link rel="stylesheet" href="styles/style_new_cards.css">
    <style>
        .card-body {
            flex-grow: 1;
    padding: 20px;
    max-height: 250px; /* Limitar la altura máxima del cuerpo de la tarjeta */
    overflow: auto; /* Hacer que el contenido restante sea scrolleable si excede el límite */
    scrollbar-width: none; /* Para Firefox */
}
.card:hover {
    transform: scale(1.05);
    transition: 0.2s ease;
    cursor: pointer;
}
.card{
    transition: 0.2s ease;
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
    <h1>Historial Empleados Del Mes</h1>
</header>

<div class="solicitud-container-wrapper" style="margin-top: -80px;">
    <div class="container">
    <h2 style="width: 100%; text-align: center;">Empleado del mes</h2>
    <p style="text-align: center; margin: 0 50px 0 50px">Este es el apartado de Empleado del mes, aquí podrás ver todos los empleados de los meses pasados de la empresa, 
                                                        buscar por quien gustes y ver sus razones por cumplir con su grata funcion laboral!</p>

        <div class="row" style="margin-top: 20px;">
            <?php if ($result_empleados_mes->num_rows > 0): ?>
                <?php while ($row = $result_empleados_mes->fetch_assoc()): 

                    $carpeta_fotos = 'Images/fotos_personal/'; // Ruta de la carpeta de fotos
                    $imagen_default = 'Images/profile_photo/imagen_default.jpg'; // Ruta de la imagen predeterminada
                    
                    // Obtener el nombre del archivo de imagen desde la base de datos
                    $nombre_imagen = $row['imagen'];
                    
                    // Construir la ruta completa de la imagen del usuario
                    $ruta_imagen_usuario = $carpeta_fotos . $nombre_imagen;
                    
                    // Verificar si la imagen del usuario existe en la carpeta
                    if (file_exists($ruta_imagen_usuario)) {
                        $imagen_final = $ruta_imagen_usuario;
                    } else {
                        $imagen_final = $imagen_default;
                    }

                    // Traducir el mes y año
                    $mes_y_anio = traducir_mes($row['mes_year']);
                    // Separar el mes y el año
                    $fecha_parts = explode(' de ', $mes_y_anio);
                    $mes = $fecha_parts[0];
                    $anio = $fecha_parts[1];
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow position-relative">
                            <!-- Encabezado del mes con botón alineado a la derecha -->
                            <div class="d-flex justify-content-center align-items-center position-relative" style="background-color: white;">
                                <h3 class="text-center" style="color: #008AC9; width:280px; margin: 0; flex-grow: 1;"><?php echo $mes; ?></h3>
                                <!-- Botón alineado al final -->
                                <?php if ($rol == 5): ?>
                                    <button type="button" class="btn btn-primary position-absolute end-0" style="margin-right: 0px;" data-bs-toggle="modal" data-bs-target="#empleadoModal<?php echo $row['id']; ?>">
                                        <i class="lni lni-cog"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <h5 class="text-center" style="color: #008AC9; margin-top: 0px;"><?php echo $anio; ?></h5>
                            
                            <img src="<?php echo $imagen_final; ?>" class="empleado-mes-imagen" alt="Foto de <?php echo $row['nombre']; ?>" style="max-height: 250px; object-fit: cover;">
                            <h5 class="cards-new-employees-name text-center"><?php echo $row['nombre']; ?></h5>

                            <div class="card-body">
                                <p class="empleado-mes-cargo"><strong>Cargo:</strong> <?php echo $row['NOMBRE_CARGO']; ?></p>
                                <p class="empleado-mes-descripcion"><strong>Descripción:</strong> <?php echo $row['descripcion']; ?></p>
                            </div>
                        </div>
                    </div>


                    <!-- Modal -->
                    <div class="modal fade" id="empleadoModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="empleadoModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="empleadoModalLabel<?php echo $row['id']; ?>">Actualizar datos de <?php echo $row['nombre']; ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="" method="post">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <div class="mb-3">
                                            <label for="nombre<?php echo $row['id']; ?>" class="form-label">Nombre</label>
                                            <input type="text" class="form-control" id="nombre<?php echo $row['id']; ?>" value="<?php echo $row['nombre']; ?>" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label for="cargo<?php echo $row['id']; ?>" class="form-label">Cargo</label>
                                            <input type="text" class="form-control" id="cargo<?php echo $row['id']; ?>" value="<?php echo $row['NOMBRE_CARGO']; ?>" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label for="descripcion<?php echo $row['id']; ?>" class="form-label">Descripción</label>
                                            <textarea class="form-control" style="height: 100px;" id="descripcion<?php echo $row['id']; ?>" name="descripcion"><?php echo $row['descripcion']; ?></textarea>
                                        </div>
                                        <button type="submit" name="actualizar_empleado" class="btn btn-success">Guardar cambios</button>
                                        <button type="button" class="btn btn-danger" onclick="confirmarEliminacion(<?php echo $row['id']; ?>)">Eliminar</button>
                                       </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>

                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                <p class="text-center">No se han registrado empleados del mes aún.</p>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmarEliminacion(id) {
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
            // Crear un formulario y enviarlo para eliminar
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = ''; // Tu misma página o la URL deseada

            const inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'id';
            inputId.value = id;

            const inputConfirm = document.createElement('input');
            inputConfirm.type = 'hidden';
            inputConfirm.name = 'confirmar_eliminar_empleado';
            inputConfirm.value = true;

            form.appendChild(inputId);
            form.appendChild(inputConfirm);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($tipo_mensaje)) : ?>
        Swal.fire({
            title: '<?php echo $tipo_mensaje == 'success' ? '¡Éxito!' : 'Error'; ?>',
            text: '<?php echo $mensaje; ?>',
            icon: '<?php echo $tipo_mensaje; ?>',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'empleados_meses.php';
            }
        });
    <?php endif; ?>
});
</script>


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
<!-- Enlaces de JavaScript antes del cierre de </body> -->
<!-- JavaScript de Bootstrap 4 -->
 
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

</body>
</html>
