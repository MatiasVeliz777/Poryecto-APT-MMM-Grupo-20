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
$sql = "SELECT rut, nombre, correo, imagen, fecha_nacimiento, cargo_id, rol_id
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

if (isset($_GET['rut'])) {

// RUT del usuario logueado
$usuario_rut = $_GET['rut'];

// Consulta SQL con parámetros
$sql_perfiles = "SELECT p.rut, p.nombre, p.correo, p.imagen, p.fecha_nacimiento, p.cargo_id, p.rol_id, c.NOMBRE_CARGO
                FROM personal p 
                INNER JOIN cargos c ON p.cargo_id = c.id
                WHERE p.rut = ?";

$stmt_perfiles = $conn->prepare($sql_perfiles);
$stmt_perfiles->bind_param("s", $usuario_rut);

if ($stmt_perfiles->execute()) {
    $result_perfiles = $stmt_perfiles->get_result();

    // Verificar si hay resultados
    if ($result_perfiles->num_rows > 0) {
        $fila = $result_perfiles->fetch_assoc(); // Obtener la primera fila

        // Verificar si el campo 'imagen' tiene un valor
        $nombre_imagen_det = isset($fila['imagen']) ? $fila['imagen'] : null;

        // Construir la ruta completa de la imagen
        $ruta_imagen_det = $carpeta_fotos . $nombre_imagen_det;

        // Verificar si la imagen existe en el servidor
        if ($nombre_imagen_det && file_exists($ruta_imagen_det)) {
            // Si existe, usar esta imagen
            $imagen_final_det = $ruta_imagen_det;
        } else {
            // Si no existe, usar imagen predeterminada
            $imagen_final_det = $imagen_default;
        }

        // Opcional: Puedes usar otras columnas del resultado aquí
        $nombre_usuario = $fila['nombre'];
        $correo_usuario = $fila['correo'];
        $cargo_usuario = $fila['NOMBRE_CARGO'];
    } else {
        echo "No se encontraron resultados para el RUT proporcionado.";
        $imagen_final_det = $imagen_default; // Usar imagen predeterminada
    }
} else {
    echo "Error al ejecutar la consulta: " . $stmt_perfiles->error;
}




// Consulta para obtener los últimos eventos a los que ha asistido el usuario
$sql_eventos = "SELECT e.id, e.titulo, e.fecha
                FROM asistencias_eventos a
                JOIN eventos e ON a.evento_id = e.id
                WHERE a.rut_usuario = ?
                ORDER BY e.fecha DESC";

$stmt_eventos = $conn->prepare($sql_eventos);
$stmt_eventos->bind_param("s", $usuario_rut);
$stmt_eventos->execute();
$result_eventos = $stmt_eventos->get_result();

// Consulta para obtener los últimos eventos a los que ha asistido el usuario
$sql_capas = "SELECT c.id, c.titulo, c.fecha
                FROM asistencia_capacitaciones a
                JOIN capacitaciones c ON a.capacitacion_id = c.id
                WHERE a.rut_usuario = ?
                ORDER BY c.fecha DESC";

$stmt_capas = $conn->prepare($sql_capas);
$stmt_capas->bind_param("s", $usuario_rut);
$stmt_capas->execute();
$result_capas = $stmt_capas->get_result();

}
$conn->close();
 
?>


<!DOCTYPE php>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfiles</title>
    <link rel="icon" href="Images/icono2.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/style_encuestas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .list-group-item {
            padding: 15px;
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        .list-group-item:hover {
            background-color: #A6D9F1;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .acciones-user h5 {
            font-weight: bold;
            color: #333;
        }
        .d-none {
            display: none;
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
                            <a href="calendario_prueba.php" class="sidebar-link">Empresa</a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="capacitaciones.php" class="sidebar-link">
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
    
<!-- Contenedor personalizado para el perfil -->
<div class="custom-container">
    <!-- Imagen del perfil -->
    <!-- Código HTML para mostrar la imagen con la lógica de verificación directamente en el src -->
    <img src="<?php echo file_exists($imagen_final_det) ? $imagen_final_det : $imagen_default; ?>" 
    class="profile-picture" alt="Foto de Perfil">

    <!-- Información del perfil -->
    <div class="profile-info">
        <h3><?php echo $fila['nombre']; ?></h3>
        <p><strong>RUT :</strong> <?php echo $fila['rut']; ?></p>
        <p><strong>Fecha de Nacimiento:</strong> 
            <?php 
            // Usar la función para formatear la fecha
            echo traducir_fecha($fila['fecha_nacimiento']);
            ?>
        </p>
        <p><strong>Cargo:</strong> <?php echo $fila['NOMBRE_CARGO']; ?></p>
        
    </div>
</div>

<div class="container-blanco" style="background-color: white; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 8px; padding: 0px; width: 70%; margin: 20px auto;">

<div class="acciones" style="padding: 30px; width: 100%; align-items: center; display:flex; flex-direction: column; justify-content: center;">
<div class="acciones-user" style="margin-bottom: 40px; width: 90%;">
    <h5 class="mb-3" style="text-align: center; font-size: 1.6rem;">Últimos Eventos Asistidos</h5>
    <p style="text-align: center;">Se muestran los últimos 3 eventos a los que has asistido. Para ver el historial completo de tus asistencias, dirígete a la <a href="calendario.php">página de eventos</a></p>
    
    <?php if ($result_eventos->num_rows > 0): ?>
        <div class="list-group" id="lista-eventos">
            <?php $contador = 0; ?>
            <?php while ($evento = $result_eventos->fetch_assoc()): ?>
                <a href="evento_asistencia.php?evento_id=<?php echo $evento['id']; ?>" 
                   class="list-group-item list-group-item-action evento-item <?php echo $contador >= 3 ? 'd-none' : ''; ?>" 
                   style="border: 1px solid #ddd; border-radius: 5px; margin-bottom: 10px; text-decoration: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Evento:</strong> <?php echo htmlspecialchars($evento['titulo']); ?>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <strong>Fecha:</strong> <?php echo traducir_fecha($evento['fecha']); ?>
                        </div>
                    </div>
                </a>
                <?php $contador++; ?>
            <?php endwhile; ?>
        </div>
        <div style="text-align: center; margin-top: 15px;">
            <button id="ver-mas-eventos" class="btn btn-primary">Ver más</button>
        </div>
    <?php else: ?>
        <p style="text-align: center;">No hay eventos recientes.</p>
    <?php endif; ?>
</div>

<div class="acciones-user" style="margin-bottom: 40px; width: 90%;">
    <h5 class="mb-3" style="text-align: center; font-size: 1.6rem;">Últimas Capacitaciones Asistidas</h5>
    <p style="text-align: center;">Se muestran los últimos 3 eventos a los que has asistido. Para ver el historial completo de tus asistencias, dirígete a la <a href="calendario.php">página de capacitaciones</a></p>
    
    <?php if ($result_capas->num_rows > 0): ?>
        <div class="list-group" id="lista-capacitaciones">
            <?php $contador = 0; ?>
            <?php while ($capas = $result_capas->fetch_assoc()): ?>
                <a href="evento_asistencia.php?evento_id=<?php echo $capas['id']; ?>" 
                   class="list-group-item list-group-item-action capacitacion-item <?php echo $contador >= 3 ? 'd-none' : ''; ?>" 
                   style="border: 1px solid #ddd; border-radius: 5px; margin-bottom: 10px; text-decoration: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Evento:</strong> <?php echo htmlspecialchars($capas['titulo']); ?>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <strong>Fecha:</strong> <?php echo traducir_fecha($capas['fecha']); ?>
                        </div>
                    </div>
                </a>
                <?php $contador++; ?>
            <?php endwhile; ?>
        </div>
        <div style="text-align: center; margin-top: 15px;">
            <button id="ver-mas-capacitaciones" class="btn btn-primary">Ver más</button>
        </div>
    <?php else: ?>
        <p style="text-align: center;">No hay capacitaciones recientes.</p>
    <?php endif; ?>
</div>


<?php if ($_SESSION['rol'] == 5): ?>
    <div class="acciones-user" style="margin-bottom: 20px; width: 90%;">
        <!-- Últimos eventos asistidos -->
        <h5 STYLE="TEXT-ALIGN: CENTER;font-size: 1.6rem;">Respuestas encuestas</h5>
        <p STYLE="TEXT-ALIGN: CENTER;">Se muestran las últimos 3 Respuestas que usted ha respondido. Para ver el historial  </p>
        <P STYLE="TEXT-ALIGN: CENTER; margin-bottom: 40px;">completo de tus Respuestas, dirígete a la <a href="ver_enc_prueba.php">página de Encuestas</a></P>
    <?php 
        include("conexion.php");
        // Mostrar las preguntas ya respondidas
        $query_respondidas = "
        SELECT p.*, r.id_respuesta, r.calificacion, r.respuesta, r.fecha_respuesta
        FROM preguntas_encuesta p
        JOIN respuestas_encuesta r
        ON p.id_pregunta = r.id_pregunta
        WHERE r.rut_usuario = ? 
        ORDER BY r.fecha_respuesta DESC";

        $stmt_respondidas = $conn->prepare($query_respondidas);
        $stmt_respondidas->bind_param("s", $usuario_rut);
        $stmt_respondidas->execute();
        $result_respondidas = $stmt_respondidas->get_result();
        
        ?>
        <?php if ($result_respondidas->num_rows > 0): ?>
        <div id="respuestas-contenedor">
            <?php $contador = 0; ?>
            <?php while ($row = $result_respondidas->fetch_assoc()): ?>
                <div class="respuesta-item <?php echo $contador >= 3 ? 'd-none' : ''; ?>" style="margin: 0px; padding: 20px;">
                    <div class="pregunta-contenedor">
                        <div class="pregunta-calificacion" style="margin-bottom: 0px;">
                            <label class="form-label pregunta-label"><?php echo htmlspecialchars($row['pregunta']); ?></label>
                        </div>

                        <div class="pregunta-calificacion">
                            <?php if ($row['tipo_pregunta'] !== 'seleccion_unica'): ?>
                                <div class="calificacion-estrellas">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $row['calificacion']): ?>
                                            <span class="estrella llena">★</span>
                                        <?php else: ?>
                                            <span class="estrella vacia">★</span>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($row['respuesta'])): ?>
                            <div class="respuesta-contenedor">
                                <p class="respuesta-texto">Sin respuesta comentada.</p>
                            </div>
                        <?php else: ?>
                            <div class="respuesta-contenedor">
                                <p class="respuesta-texto"><strong>Respuesta:</strong> <?php echo htmlspecialchars($row['respuesta']); ?></p>
                            </div>
                            <p class="fecha-respuesta" style="display: inline-block; margin: 0px; text-align: center;">
                                <strong></strong> <?php echo date('d-m-Y', strtotime($row['fecha_respuesta'])); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <hr>
                </div>
                <?php $contador++; ?>
            <?php endwhile; ?>
        </div>
        <div style="text-align: center; margin-top: 15px;">
            <button id="ver-mas-respuestas" class="btn btn-primary">Ver más</button>
        </div>
    <?php else: ?>
        <p style="text-align: center;">No has respondido ninguna encuesta aún.</p>
    <?php endif; ?>
    </div>
    <?php endif; ?>
  
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Mostrar más eventos
    const btnVerMasEventos = document.getElementById('ver-mas-eventos');
    const eventosItems = document.querySelectorAll('.evento-item');
    

    btnVerMasEventos.addEventListener('click', () => {
        eventosItems.forEach(item => item.classList.remove('d-none')); // Mostrar todos los eventos
        btnVerMasEventos.style.display = 'none'; // Ocultar el botón de "Ver más"
    });

    // Mostrar más capacitaciones
    const btnVerMasCapacitaciones = document.getElementById('ver-mas-capacitaciones');
    const capacitacionItems = document.querySelectorAll('.capacitacion-item');

    btnVerMasCapacitaciones.addEventListener('click', () => {
        capacitacionItems.forEach(item => item.classList.remove('d-none')); // Mostrar todas las capacitaciones
        btnVerMasCapacitaciones.style.display = 'none'; // Ocultar el botón de "Ver más"
    });

    const btnVerMasRespuestas = document.getElementById('ver-mas-respuestas');
    const respuestasItems = document.querySelectorAll('.respuesta-item');

    btnVerMasRespuestas.addEventListener('click', () => {
        respuestasItems.forEach(item => item.classList.remove('d-none')); // Mostrar todas las respuestas
        btnVerMasRespuestas.style.display = 'none'; // Ocultar el botón de "Ver más"
    });
});
</script>

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="scripts/script.js"></script>
</body>

</html>