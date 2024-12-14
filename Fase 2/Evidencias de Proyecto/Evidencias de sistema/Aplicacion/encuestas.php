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

// Procesar las respuestas solo si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'calificacion') === 0 && !empty($value)) {
            $id_pregunta = str_replace('calificacion', '', $key);
            $calificacion = intval($value);  // Convertir el valor a entero
            $respuesta = $_POST['respuesta' . $id_pregunta] ?? '';
            $rut_usuario = $_SESSION['rut'];  // Se obtiene el rut del usuario autenticado desde la sesión
    
            // Validar que la calificación esté entre 1 y 5 antes de la inserción
            if ($calificacion < 1 || $calificacion > 5) {
                echo "Error: la calificación debe estar entre 1 y 5.";
                exit();
            }
    
            // Usar consulta preparada para evitar inyección SQL
            $stmt = $conn->prepare("INSERT INTO respuestas_encuesta (rut_usuario, id_pregunta, calificacion, respuesta) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siis", $rut_usuario, $id_pregunta, $calificacion, $respuesta);
    
            if (!$stmt->execute()) {
                echo "Error: " . $stmt->error;
            }
    
            $stmt->close();
        }
    }
}

// Consulta para obtener preguntas pendientes (no respondidas)
$query_pendientes = "
    SELECT p.*
    FROM preguntas_encuesta p
    LEFT JOIN respuestas_encuesta r
    ON p.id_pregunta = r.id_pregunta AND r.rut_usuario = ?
    WHERE r.id_pregunta IS NULL
";

$stmt_pendientes = $conn->prepare($query_pendientes);
$stmt_pendientes->bind_param("s", $rut_usuario);
$stmt_pendientes->execute();
$result_pendientes = $stmt_pendientes->get_result();

// Consulta para obtener preguntas respondidas
$query_respondidas = "
    SELECT p.*, r.calificacion, r.respuesta
    FROM preguntas_encuesta p
    JOIN respuestas_encuesta r
    ON p.id_pregunta = r.id_pregunta
    WHERE r.rut_usuario = ?
";

$stmt_respondidas = $conn->prepare($query_respondidas);
$stmt_respondidas->bind_param("s", $rut_usuario);
$stmt_respondidas->execute();
$result_respondidas = $stmt_respondidas->get_result();
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
}
.solicitud-container {
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 800px;
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

<div class="solicitud-container-wrapper">

        
<div class="solicitud-container">
    <h1>Encuestas Pendientes</h1>
    <form  id="form-encuesta" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" class="solicitud-form">
        <?php
        // Mostrar las preguntas pendientes
        if ($result_pendientes->num_rows > 0) {
            while ($row = $result_pendientes->fetch_assoc()) {
                echo "<div class='input-group' style='margin-bottom: 20px;'>";
                echo "<label for='pregunta{$row['id_pregunta']}' class='form-label pregunta-label'>{$row['pregunta']}</label>";
                
                echo "<div class='rating'>
                    <input type='radio' id='star5_{$row['id_pregunta']}' name='calificacion{$row['id_pregunta']}' value='5' >
                    <label for='star5_{$row['id_pregunta']}'>★</label>

                    <input type='radio' id='star4_{$row['id_pregunta']}' name='calificacion{$row['id_pregunta']}' value='4'>
                    <label for='star4_{$row['id_pregunta']}'>★</label>

                    <input type='radio' id='star3_{$row['id_pregunta']}' name='calificacion{$row['id_pregunta']}' value='3'>
                    <label for='star3_{$row['id_pregunta']}'>★</label>

                    <input type='radio' id='star2_{$row['id_pregunta']}' name='calificacion{$row['id_pregunta']}' value='2'>
                    <label for='star2_{$row['id_pregunta']}'>★</label>

                    <input type='radio' id='star1_{$row['id_pregunta']}' name='calificacion{$row['id_pregunta']}' value='1'>
                    <label for='star1_{$row['id_pregunta']}'>★</label>
                </div>";

                echo "<div class='input-group'>
                        <textarea class='form-control' name='respuesta{$row['id_pregunta']}' placeholder='Comentario opcional'></textarea>
                      </div>";
                echo "</div>";
            }
        } else {
            echo "<p>No tienes encuestas pendientes por responder.</p>";
        }
        ?>
        <button type="submit" class="solicitud-submit-btn" style="margin-bottom: 30px; width: 100%; background-color: #002855; color: white;">Enviar Respuesta</button>
    </form>

    <h1>Encuestas Respondidas</h1>
    <?php
    // Mostrar las preguntas ya respondidas
    if ($result_respondidas->num_rows > 0) {
        while ($row = $result_respondidas->fetch_assoc()) {
            echo "<div class='input-group1' style='margin-bottom: 20px;'>";
            echo "<div class='pregunta-contenedor'>";
                echo "<div class='pregunta-calificacion'>";
                    echo "<label class='form-label pregunta-label'>Pregunta: {$row['pregunta']}</label>";
                    echo "<span class='calificacion-label'><span class='badge bg-warning text-dark'>{$row['calificacion']} estrellas</span></span>";
                echo "</div>";
                if(empty($row['respuesta'])){
                    echo "<div class='respuesta-contenedor'>";
                    echo "<p class='respuesta-texto'>Sin respuesta comentada.</p>";
                echo "</div>";
                }else{
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
    $stmt_pendientes->close();
    $stmt_respondidas->close();
    $conn->close();
    ?>
</div>


    
    </div>
</div>
 <!-- jQuery y Bootstrap JS -->
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Capturar el envío del formulario
document.getElementById('form-encuesta').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevenir el envío por defecto del formulario

    let formValid = true;  // Bandera para verificar si el formulario es válido

    // Recorremos cada pregunta para verificar si al menos una calificación está marcada
    document.querySelectorAll('.rating').forEach(function(ratingGroup) {
        const radioButtons = ratingGroup.querySelectorAll('input[type="radio"]');
        let oneChecked = false;

        radioButtons.forEach(function(radioButton) {
            if (radioButton.checked) {
                oneChecked = true;
            }
        });

        if (!oneChecked) {
            formValid = false;  // Si ninguna calificación está seleccionada, el formulario no es válido
        }
    });

    // Si el formulario no es válido, mostramos una alerta de error y no enviamos el formulario
    if (!formValid) {
        Swal.fire({
            title: 'Error',
            text: 'Debes seleccionar una calificación para todas las preguntas.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;  // Detenemos la ejecución si no es válido
    }

    // Si el formulario es válido, continuar con el envío
    const formData = new FormData(this);

    fetch('guardar_respuesta.php', {
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
                    window.location.href = 'encuestas.php'; // Redirigir después de cerrar el modal
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
</body>
</html>
