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
        WHERE rut = (SELECT rut FROM usuarios WHERE nombre_usuario = '$usuario')";;
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
// Si rol_id es 0, mostramos todas las solicitudes
// Consultar las solicitudes junto con el nombre del personal que las envió
if ($rol_id_seleccionado == 0) {
    $sql_solicitudes = "SELECT soportes.*, roles.rol, personal.nombre
                        FROM soportes 
                        JOIN roles ON soportes.rol_id = roles.id
                        JOIN personal ON soportes.rut = personal.rut"; // Relacionar rut de 'soportes' con rut de 'personal'
} else {
    $sql_solicitudes = "SELECT soportes.*, roles.rol, personal.nombre 
                        FROM soportes 
                        JOIN roles ON soportes.rol_id = roles.id
                        JOIN personal ON soportes.rut = personal.rut
                        WHERE soportes.rol_id = '$rol_id_seleccionado'";
}

// Ejecutar la consulta
$result_solicitudes = $conn->query($sql_solicitudes);

$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Solicitudes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style_calendar.css">
    <!-- Bootstrap CSS (solo una referencia a la última versión) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    
    <!-- Lineicons -->
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style_calendar.css">

    <!-- Bootstrap JS and dependencies (solo una referencia a la última versión) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+8I5fs5q5nWtbj+7G8DA6/DlM9xkh"
        crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">


    <style>
/* Estilo general */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

h1 {
    text-align: center;
    color: #008AC9;
    margin-bottom: 20px;
    margin-top: 20px;
}

h6 {
    text-align: center;
    color: #000;
    margin-bottom: 20px;
    margin-top: 20px;
}

/* Layout de contenedores */
.soporte-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    gap: 20px;
    justify-content: space-between;
    word-wrap: break-word;
}

/* Lista de solicitudes de soporte */
.soporte-list {
    width: 35%;
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    overflow-y: auto;
    max-height: 600px;
}

/* Caja de solicitud de soporte */
.soporte-box {
    background-color: #fff;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.soporte-box:hover {
    background-color: #f0f0f0;
    transform: translateY(-5px); /* Elevar un poco al hacer hover */
}

.soporte-box.active {
    background-color: #d9ecf2;
    border-left: 5px solid #008AC9; /* Resaltar solicitud activa */
}

.soporte-title {
    font-size: 1.3rem;
    font-weight: bold;
    color: #333;
}

.soporte-content {
    margin-top: 5px;
    color: #777;
}

/* Detalles de la solicitud con imagen de fondo */
.soporte-detail {
    width: 60%;
    background-color: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    display: none;
    flex-direction: column;
    position: absolute;
    right: -100%;
    top: 0;
    transition: right 0.5s ease, opacity 0.5s ease;
    opacity: 0;
    margin-top: 132px;
}

.soporte-detail.active {
    right: 0;
    opacity: 1;
}

.soporte-detail .soporte-title {
    font-size: 1.7rem;
    font-weight: bold;
    margin-bottom: 15px;
    position: relative;
}

/* Línea divisoria debajo del título */
.soporte-detail .soporte-title::after {
    content: "";
    display: block;
    width: 100%;
    height: 2px;
    background-color: #ddd;
    margin-top: 10px;
}

/* Separación para el contenido */
.soporte-detail .soporte-content {
    margin-top: 20px;
    font-size: 1rem;
    color: #555;
    position: relative;
}

/* Línea divisoria debajo del contenido */
.soporte-detail .soporte-content::after {
    content: "";
    display: block;
    width: 100%;
    height: 2px;
    background-color: #ddd;
    margin-top: 10px;
}

.soporte-image {
    margin-top: 25px;
    max-width: 100%;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Espacio y estilos para la imagen permanente */
.soporte-permanent-image-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 150px;
    margin-top: 20px;
    border-top: 2px solid #ddd; /* Línea divisoria */
    padding-top: 20px;
}

.soporte-permanent-image {
    max-width: 60%;
    max-height: 100%;
    object-fit: contain;
}

/* Botones de acción para editar/eliminar */
.soporte-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}

.soporte-actions button {
    background-color: #008AC9;
    border: none;
    color: white;
    padding: 10px 15px;
    border-radius: 8px;
    cursor: pointer;
    margin-left: 10px;
    transition: background-color 0.3s ease;
}

.soporte-actions button:hover {
    background-color: #005f8c;
}

.soporte-actions button.delete {
    background-color: #e74c3c;
}

.soporte-actions button.delete:hover {
    background-color: #c0392b;
}

/* Subtítulos para cada sección */
.soporte-detail .section-title {
    font-size: 0.9rem;
    font-weight: bold;
    color: #888;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Aumentar el espaciado entre secciones */
.soporte-detail .soporte-content, 
.soporte-detail .soporte-title, 
.soporte-detail .soporte-image, 
.soporte-detail .soporte-permanent-image-container {
    margin-top: 15px;
}

/* Título del panel completo */
.panel-heading {
    font-size: 1.1rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 20px;
    border-bottom: 2px solid #ddd;
    padding-bottom: 10px;
}
/* Responsive */
@media (max-width: 768px) {
    .soporte-container {
        flex-direction: column;
    }

    .soporte-list, .soporte-detail {
        width: 100%;
        position: relative;
    }

    .soporte-box {
        padding: 15px; /* Ajusta el padding para pantallas pequeñas */
    }

    .soporte-title {
        font-size: 1.1rem; /* Reducir ligeramente el tamaño del título */
    }
}
    </style>
    
</head>
<body>

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
            <img src="<?php echo !empty($user_data['imagen']) ? $user_data['imagen'] : 'Images/profile_photo/imagen_default.jpg'; ?>" class="img-fluid rounded-circle" alt="Foto de Perfil">

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
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#multi" aria-expanded="false" aria-controls="multi">
                        <i class="lni lni-layout"></i>
                        <span>Documentos</span>
                    </a>
                    <ul id="multi" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                    <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Reglamento interno</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Contratos</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Protocolos</a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">Manuales</a>
                        </li>
                    </ul>
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

    <!-- Navbar or Header -->

    <h1>Ver Solicitudes de Soporte</h1>
    <h6>Ver Solicitudes de Soporte</h6>

    <div class="soporte-container">
    <!-- Left side: List of support requests -->
    <div class="soporte-list">
        <!-- Mostrar solicitudes de soporte en cajas dinámicamente -->
        <?php if ($result_solicitudes->num_rows > 0) {
            while($soporte = $result_solicitudes->fetch_assoc()) { ?>
            <div class="soporte-box" onclick="toggleDetail('<?php echo $soporte['titulo']; ?>', '<?php echo $soporte['contenido']; ?>', '<?php echo !empty($soporte['imagen']) ? $soporte['imagen'] : 'Images/noimagen.jpg'; ?>')">
                <div class="soporte-title"><?php echo $soporte['titulo']; ?></div>
                <div class="soporte-user"><b>Enviado por:</b>  <?php echo $soporte['nombre']; ?></div> <!-- Mostrar el nombre del personal -->
                <div class="soporte-content"><?php echo substr($soporte['contenido'], 0, 50) . '...'; ?></div>
            </div> <!-- Ensure each box has a separate closing div -->
        <?php } } else { ?>
            <p>No se encontraron solicitudes de soporte para este rol.</p>
        <?php } ?>
    </div>

    <!-- Right side: Detail of the selected request -->
     
    <div class="soporte-detail" id="soporteDetail">
    <!-- Título general del panel -->
    <h2 class="panel-heading">Detalles de la Solicitud</h2>

    <!-- Subtítulo para el título de la solicitud -->
    <div class="section-title">Título de la solicitud:</div>
    <h2 class="soporte-title" id="detailTitle">Título</h2>

    <!-- Subtítulo para el contenido de la solicitud -->
    <div class="section-title">Contenido de la solicitud:</div>
    <p class="soporte-content" id="detailContent">Contenido de la solicitud seleccionada.</p>

    <!-- Subtítulo para la imagen de soporte -->
    <div class="section-title">Imagen adjunta:</div>
    <img src="Images/noimagen.jpg" alt="Imagen de Soporte" class="soporte-image" id="detailImage">

    <!-- Imagen permanente debajo con subtítulo -->
    <div class="soporte-permanent-image-container">
        <img src="Images/logo_clinica.png" alt="Imagen Permanente" class="soporte-permanent-image">
    </div>

    <!-- Botones de acción para editar o eliminar -->
    <div class="soporte-actions">
        <button class="edit">Editar</button>
        <button class="delete">Eliminar</button>
    </div>
</div>


<script>
    let isDetailOpen = false;

    function toggleDetail(title, content, image) {
        const detail = document.getElementById('soporteDetail');
        const imageElement = document.getElementById('detailImage');

        if (isDetailOpen) {
            // Close the detail view (slide it out)
            detail.style.right = '-100%'; // Slide out of view
            detail.style.opacity = '0';   // Fade out
            isDetailOpen = false;
        } else {
            // Open the detail view (slide it in)
            detail.style.display = 'flex';
            detail.style.right = '0'; // Slide into view
            detail.style.opacity = '1'; // Fade in

            // Update the content
            document.getElementById('detailTitle').innerText = title;
            document.getElementById('detailContent').innerText = content;

            // Update the image dynamically
            if (image) {
                imageElement.src = image; // Use the passed image URL
                imageElement.style.display = 'block'; // Show the image
            } else {
                imageElement.src = 'Images/noimagen.jpg'; // Use placeholder if no image
            }

            isDetailOpen = true;
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="scripts/script.js"></script>
</body>
</html>
