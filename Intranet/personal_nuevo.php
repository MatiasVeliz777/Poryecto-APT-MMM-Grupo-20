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

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="icon" href="Images/icono2.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles/style_cards.css">
    <link rel="stylesheet" href="styles/style_new_cards.css">
    <style>
         /* Estilos generales del contenedor */
         .custom-container1 {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: flex-start; /* Alineación superior */
            gap: 20px;
            max-width: 100%;
            padding: 20px;
            box-sizing: border-box;
            background-color: #f8f9fa; /* Fondo claro para mejor visibilidad */
            border-radius: 10px;
            margin: 20px 0; /* Espacio superior e inferior */
        }

        /* Tarjeta individual de cada perfil */
        

        /* Título de la sección */
        .section-title {
            width: 100%;
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .footer {
    background-color: #2c3e50;
    color: white;
    padding: 40px 0;
}

.footer-container {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
}

.footer-section {
    flex: 1;
    margin: 20px;
    text-align: center;
}

.footer-section h4 {
    font-size: 18px;
    margin-bottom: 15px;
}

.footer-section p {
    font-size: 14px;
    margin-bottom: 10px;
}

.social-icons a {
    color: white;
    font-size: 20px;
    margin: 0 10px;
    text-decoration: none;
}

.social-icons a:hover {
    color: #3498db;
}

.footer-bottom {
    text-align: center;
    padding: 10px 0;
    border-top: 1px solid #3f5367;
    font-size: 14px;
}

.main-content {
    display: block;
    width: 100%; /* Asegúrate de que ocupe todo el ancho */
}

.footer {
    width: 100%; /* Asegura que el footer ocupe todo el ancho */
    clear: both;  /* Asegura que el footer esté debajo de todo el contenido */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
.footer {
    clear: both;
    width: 100%;
    background-color: #2c3e50;
    color: white;
    padding: 40px 0;
}
.wrapper {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.main-content {
    flex-grow: 1; /* El contenido principal se expande para llenar el espacio */
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
                    <a href="#" class="sidebar-link">
                        <i class="lni lni-agenda"></i>
                        <span>Capacitaciones</span>
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
                            <a href="calendario.php" class="sidebar-link">Empresa</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">cumpleaños</a>
                        </li>
                    </ul>
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
                            <a href="#" class="sidebar-link">Nuevos empleados</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Cumpleaños</a>
                        </li>
                    </ul>
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

    
<!-- Contenedor de usuarios nuevos -->
<div class="custom-container1">
    <h2 style="width: 100%; text-align: center;">Usuarios Nuevos de este Mes</h2>
    <?php
    if ($result_nuevos->num_rows > 0) {
        while ($nuevo_user = $result_nuevos->fetch_assoc()) {
            // Ruta de la imagen del usuario nuevo
            $ruta_imagen_nuevo = $carpeta_fotos . $nuevo_user['imagen'];
            
            // Verificar si la imagen del usuario nuevo existe
            $imagen_usuario_nuevo = file_exists($ruta_imagen_nuevo) ? $ruta_imagen_nuevo : $imagen_default;
            ?>  
        <div class="cards-new-employees-card swiper-slide">
          <div class="image-content cards-new-employees-image">
            <span class="overlay cards-new-employees-overlay"></span>
            <div class="cards-new-employees-image-wrapper">
            <img src="<?php echo $imagen_usuario_nuevo; ?>" class="profile-picture-nuevo" alt="Foto de Perfil">
            </div>
          </div>
          <div class="cards-new-employees-content">
            <h2 class="cards-new-employees-name"><?php echo $nuevo_user['nombre']; ?></h2>
            <p  class="cards-new-employees-description"><strong>Fecha de Nacimiento:</strong> <?php echo traducir_fecha($nuevo_user['fecha_nacimiento']); ?></p>
            <p class="cards-new-employees-description"><strong>Cargo:</strong> <?php echo $nuevo_user['NOMBRE_CARGO']; ?></p>
            <p class="cards-new-employees-description"><strong>Fecha de Ingreso:</strong> <?php echo traducir_fecha($nuevo_user['fecha_creacion']); ?></p>

            <button class="cards-new-employees-button">Bienvenido!</button>
          </div>
        </div>

        <?php
        }
    } else {
        echo '<p>No hay usuarios nuevos este mes.</p>';
    }
    ?>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="js/script.js"></script>
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
