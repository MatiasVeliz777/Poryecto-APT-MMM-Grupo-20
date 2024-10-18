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
$sql = "SELECT rut, nombre, correo, imagen, cargo_id, rol_id
        FROM personal 
        WHERE rut = (SELECT rut FROM usuarios WHERE nombre_usuario = '$usuario')";;
$result = $conn->query($sql);

// Verificar si se encontró el usuario
if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc(); // Extraer los datos del usuario
    $rol = $user_data['rol_id'];
    $rut = $user_data['rut'];
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

// Procesar la solicitud cuando se envía el formulario
$solicitudEnviada = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $contenido = $_POST['contenido'];
    $urgencia = $_POST['urgencia'];
    $rol = $_SESSION['rol'];
    $usuario = $_SESSION['usuario'];

    // Verificar si se ha subido una imagen
    if (!empty($_FILES['imagen']['name'])) {
        $imagen = $_FILES['imagen']['name'];
        $imagen_tmp = $_FILES['imagen']['tmp_name'];
        $imagen_folder = 'Images/soporte_img/' . basename($imagen);

        // Mover la imagen a la carpeta de uploads
        if (move_uploaded_file($imagen_tmp, $imagen_folder)) {
            $imagen_db = $imagen_folder;
        } else {
            echo "Error al subir la imagen.";
            exit();
        }
    } else {
        $imagen_db = NULL;
    }

    // Guardar la solicitud en la base de datos
    $sql = "INSERT INTO soportes (titulo, contenido, urgencia, imagen, rut, rol_id) 
            VALUES ('$titulo', '$contenido', '$urgencia', " . ($imagen_db ? "'$imagen_db'" : "NULL") . ", '$rut','$rol')";

    if ($conn->query($sql) === TRUE) {
        $solicitudEnviada = true; // Marcamos que la solicitud se ha enviado
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE php>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soporte</title>
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


    <header class="solicitud-header">
    <h1>Soporte Técnico</h1>
</header>

<div class="solicitud-container-wrapper">
    <!-- Box para las instrucciones -->
    <div class="solicitud-instructions">
        <h3>Instrucciones</h3>
        <p>1. Ingresa el título del problema que estás experimentando.</p>
        <p>2. Describe el problema con la mayor cantidad de detalles posible.</p>
        <p>4. Indica el nivel de urgencia.</p>
        <p>5. Puedes adjuntar una imagen o archivo relacionado con el problema si es necesario.</p>
    </div>

    <!-- Formulario de soporte técnico -->
    <div class="solicitud-container">
        <h2>Soporte Técnico</h2>
        <h3>Ingrese los datos</h3>
        <form class="solicitud-form" method="POST" action="soporte.php" enctype="multipart/form-data">
        <h5>Area: <?php echo $cargo_data['NOMBRE_CARGO'];?></h5>
            <!-- Campo para el título -->
            <div class="input-group">
                <i class="fas fa-heading"></i>
                <input type="text" name="titulo" placeholder="Título del problema" required>
            </div>

            <!-- Campo para la descripción -->
            <div class="input-group">
                <i class="fas fa-pencil-alt"></i>
                <textarea name="contenido" placeholder="Describe tu problema" required></textarea>
            </div>

            <!-- Selección del nivel de urgencia -->
            <div class="input-group">
                <i class="fas fa-exclamation-triangle"></i>
                <select name="urgencia" >
                    <option value="">Nivel de urgencia</option>
                    <option value="bajo">Bajo</option>
                    <option value="medio">Medio</option>
                    <option value="alto">Alto</option>
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
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if ($solicitudEnviada) : ?>
<script>
    Swal.fire({
        title: '¡Solicitud enviada!',
        text: 'Tu solicitud ha sido enviada correctamente',
        icon: 'success',
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

</div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
<script src="js/script.js"></script>
</body>
</html>
