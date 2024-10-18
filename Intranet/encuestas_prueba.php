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

// Procesar la solicitud cuando se envía el formulario
$solicitudEnviada = false;
$errorAlGuardar = false; // Variable para manejar los errores

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pregunta = $_POST['pregunta'] ?? '';
    $tipo_pregunta = $_POST['tipo_pregunta'] ?? '';

    if (!empty($pregunta) && !empty($tipo_pregunta)) {
        // Insertar la pregunta
        $stmt = $conn->prepare("INSERT INTO preguntas_encuesta (pregunta, tipo_pregunta) VALUES (?, ?)");
        $stmt->bind_param('ss', $pregunta, $tipo_pregunta);
        if ($stmt->execute()) {
            $id_pregunta = $stmt->insert_id;

            // Si la pregunta no es de respuesta abierta, agregar opciones
            if ($tipo_pregunta != 'texto' && isset($_POST['opcion'])) {
                foreach ($_POST['opcion'] as $opcion) {
                    if (!empty($opcion)) {
                        $stmt_opcion = $conn->prepare("INSERT INTO opciones_encuesta (id_pregunta, opcion) VALUES (?, ?)");
                        $stmt_opcion->bind_param('is', $id_pregunta, $opcion);
                        $stmt_opcion->execute();
                        $stmt_opcion->close();
                    }
                }
            }
            
            $solicitudEnviada = TRUE;
        } else {
            $errorAlGuardar = false;
        }

        $stmt->close();
    } else {
        $errorAlGuardar = false;
    }
}





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
        .card-body {
            flex-grow: 1;
    padding: 20px;
    max-height: 650px; /* Limitar la altura máxima del cuerpo de la tarjeta */
    overflow: auto; /* Hacer que el contenido restante sea scrolleable si excede el límite */
    scrollbar-width: none; /* Para Firefox */
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
    <h1>Historial Empleados Del Mes</h1>
</header>

<div class="solicitud-container-wrapper" style="margin-bottom: 50px">

<div class="solicitud-instructions">
        <h3>Instrucciones para crear una encuesta</h3>
        <p>1. Seleccione el tipo de pregunta que quiere generar, puede elegir entre "Texto" y "Seleccion unica". Escriba una pregunta clara y concisa para la encuesta.</p>
        <p>2. La pregunta debe ser relevante para el contexto y comprensible para los usuarios.</p>
        <p>3. Asegúrese de que la pregunta no contenga faltas ortográficas ni gramaticales.</p>
    </div>
    <div class="solicitud-container">
        <h2>Crear Pregunta de Encuesta</h2>
        <h3>Ingrese la pregunta</h3>
        
        <form id="form-encuesta" class="solicitud-form" method="POST" action="">

            <!-- Selector de tipo de pregunta -->
            <div class="input-group">
                <label for="tipo_pregunta">Tipo de Pregunta:</label>
                <select name="tipo_pregunta" id="tipo_pregunta" required>
                    <option value="" disabled selected>Seleccione el tipo de pregunta que desea realizar</option>
                    <option value="texto">Respuesta abierta</option>
                    <option value="seleccion_unica">Selección única</option>
                </select>
            </div>

            <!-- Campo de pregunta (oculto por defecto) -->
            <div class="input-group" id="pregunta-group" style="display:none;">
                <label for="pregunta">Ingrese la pregunta</label>
                <i class="fa-solid fa-question-circle"></i>
                <input name="pregunta" id="pregunta" rows="4" cols="50" placeholder="Escriba la pregunta de la encuesta aquí..." required></textarea>
            </div>

            <!-- Área para las opciones de respuesta (oculta por defecto) -->
            <div class="input-group" id="opciones-respuesta" style="display:none;">
                <h3>Ingrese las opciones de respuesta</h3>
                <i class="fa-solid fa-question-circle"></i>
                <input type="text" name="opcion[]" placeholder="Opción 1">
                <input type="text" name="opcion[]" placeholder="Opción 2">
                <div class="button-opcion" style="display: flex; justify-content: center; align-items: center; margin-bottom: 20px;">
                    <button type="button" id="agregar-opcion" class="solicitud-submit-btn" style="margin-top:10px;">Agregar otra opción</button>
                </div>
            </div>

            <button type="submit" class="solicitud-submit-btn">Guardar Pregunta</button>
        </form>
    </div>
    
</div>
<div class="solicitud-container-wrapper">

<?php
// Consulta para obtener todas las preguntas
$query_preguntas = "SELECT id_pregunta, pregunta FROM preguntas_encuesta";
$result_preguntas = $conn->query($query_preguntas);

// Verificar si hay preguntas en la base de datos
if ($result_preguntas->num_rows > 0) {
    echo "<div class='container mt-4'>";
    echo "<h2 class='text-center mb-4'>Listado de Preguntas</h2>";
    
    echo "<table class='table table-bordered table-striped'>";
    echo "<thead class='table-dark'><tr><th>ID Pregunta</th><th>Pregunta</th><th>Acción</th></tr></thead>";
    echo "<tbody>";

    // Recorrer cada fila de resultados y hacer la fila clickeable
    while ($row = $result_preguntas->fetch_assoc()) {
        echo "<tr style='cursor:pointer;' onclick='window.location.href=\"respuestas.php?id_pregunta=" . $row['id_pregunta'] . "\"'>";
        echo "<td>" . $row['id_pregunta'] . "</td>";
        echo "<td>" . $row['pregunta'] . "</td>";
        echo "<td>";
        
        // Botón para eliminar la pregunta, sin interferir con el onclick de la fila
        echo "<button class='btn btn-danger' style='cursor: default;' onclick='event.stopPropagation(); confirmarEliminacion(" . $row['id_pregunta'] . ")'>Eliminar</button>";
        
        echo "</td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";
} else {
    echo "<div class='container mt-4'>";
    echo "<div class='alert alert-info' role='alert'>No hay preguntas disponibles.</div>";
    echo "</div>";
}

$conn->close();
?>

    
</div>


<script>
    // Escuchar cambios en el select para tipo de pregunta
    document.getElementById('tipo_pregunta').addEventListener('change', function() {
        const opcionesDiv = document.getElementById('opciones-respuesta');
        const preguntaGroup = document.getElementById('pregunta-group');

        // Mostrar el campo de la pregunta
        preguntaGroup.style.display = 'block';

        // Mostrar/ocultar las opciones de respuesta basado en el tipo de pregunta seleccionado
        if (this.value === 'texto') {
            opcionesDiv.style.display = 'none'; // Oculta opciones si es respuesta abierta
        } else {
            opcionesDiv.style.display = 'block'; // Muestra opciones para selección única/múltiple
        }
    });

    // Agregar más opciones dinámicamente
    document.getElementById('agregar-opcion').addEventListener('click', function() {
        const opcionesDiv = document.getElementById('opciones-respuesta');
        const nuevaOpcion = document.createElement('input');
        nuevaOpcion.setAttribute('type', 'text');
        nuevaOpcion.setAttribute('name', 'opcion[]');
        nuevaOpcion.setAttribute('placeholder', 'Nueva opción');
        opcionesDiv.appendChild(nuevaOpcion);
    });
</script>
<!-- Importar SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmarEliminacion(idPregunta) {
    // Mostrar modal de confirmación
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
            // Si el usuario confirma, proceder a eliminar la pregunta
            fetch('eliminar_pregunta.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_pregunta=' + encodeURIComponent(idPregunta)
            })
            .then(response => response.json()) // Asumimos que la respuesta será JSON
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        '¡Eliminado!',
                        'La pregunta ha sido eliminada.',
                        'success'
                    ).then(() => {
                        location.reload(); // Recargar la página después de la eliminación
                    });
                } else {
                    Swal.fire(
                        'Error',
                        'Ocurrió un error al eliminar la pregunta.',
                        'error'
                    );
                }
            })
            .catch(error => {
                Swal.fire(
                    'Error',
                    'No se pudo conectar con el servidor.',
                    'error'
                );
            });
        }
    });
}
</script>







</div>

    <!-- jQuery y Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Mostrar alerta de éxito si la solicitud fue enviada correctamente -->
<?php if ($solicitudEnviada) : ?>
<script>
    Swal.fire({
        title: '¡Pregunta guardada!',
        text: 'Tu pregunta ha sido guardada correctamente.',
        icon: 'success',
        confirmButtonText: 'OK'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'encuestas_prueba.php'; // Redirigir después de cerrar el modal
        }
    });
</script>
<?php endif; ?>

<!-- Mostrar alerta de error si hubo un problema al guardar -->
<?php if ($errorAlGuardar) : ?>
<script>
    Swal.fire({
        title: 'Error',
        text: 'Ocurrió un error al guardar la pregunta. Por favor, intenta nuevamente.',
        icon: 'error',
        confirmButtonText: 'OK'
    });
</script>
<?php endif; ?>



<script>
$(document).ready(function () {
    $('#tipo-pregunta').change(function () {
        var tipoSeleccionado = $(this).val();
        var contenedor = $('#pregunta-dinamica');
        contenedor.empty();  // Limpia el contenedor

        if (tipoSeleccionado === 'Parrafo') {
            contenedor.append(`
                <div class="input-group">
                    <label for="pregunta">Escribe tu pregunta en formato de párrafo:</label>
                    <input type="text" name="pregunta" id="pregunta" placeholder="Escribe tu pregunta aquí..." required>
                </div>
            `);
        } else if (tipoSeleccionado === 'Selección única' || tipoSeleccionado === 'Selección múltiple') {
            var tipoOpcion = tipoSeleccionado === 'Selección única' ? 'radio' : 'checkbox';
            contenedor.append(`
                <div class="input-group" style="margin-bottom: 20px;">
                    <label for="pregunta" style="display: block; font-weight: bold; margin-bottom: 8px;">Escribe tu pregunta para selección:</label>
                    <input type="text" name="pregunta" id="pregunta" placeholder="Escribe tu pregunta aquí..." required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; box-sizing: border-box;">
                </div>

                <div class="input-group" id="opciones" style="margin-bottom: 20px;display: block;<">
                    <label style="display: block; font-weight: bold; margin-bottom: 8px;">Opciones:</label>
                    <div style="margin-bottom: 10px;">
                        <input type="radio" disabled style="margin-right: 10px;">
                        <input type="text" name="opciones[]" placeholder="Opción 1" style="width: calc(100% - 40px); padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <input type="radio" disabled style="margin-right: 10px;">
                        <input type="text" name="opciones[]" placeholder="Opción 2" style="width: calc(100% - 40px); padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>
                    <button type="button" class="solicitud-submit-btn" id="agregar-opcion" style="background-color: #003366; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">Agregar Opción</button>
                </div>

            `);

            // Botón para agregar más opciones
            $('#agregar-opcion').click(function () {
                var numOpciones = $('#opciones div').length;
                $('#opciones').append(`
                    <div>
                        <input type="${tipoOpcion}" disabled>
                        <input type="text" name="opciones[]" placeholder="Opción ${numOpciones + 1}">
                    </div>
                `);
            });
        } else if (tipoSeleccionado === 'Caja de selección') {
            contenedor.append(`
                <div class="input-group">
                    <label for="pregunta">Escribe tu pregunta para el select box:</label>
                    <input type="text" name="pregunta" id="pregunta" placeholder="Escribe tu pregunta aquí..." required>
                </div>
                <div class="input-group" id="opciones">
                    <label>Opciones:</label>
                    <select>
                        <option disabled selected>Seleccione una opción</option>
                        <option>Opción 1</option>
                        <option>Opción 2</option>
                    </select>
                    <button type="button" id="agregar-opcion">Agregar Opción</button>
                </div>
            `);

            // Botón para agregar más opciones
            $('#agregar-opcion').click(function () {
                var numOpciones = $('#opciones select option').length;
                $('#opciones select').append(`<option>Opción ${numOpciones}</option>`);
            });
        }
    });
});

</script>

    <!-- Linking SwiperJS script -->
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- Linking custom script -->
<script src="js/script_cards.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
      crossorigin="anonymous"></script>
  <script src="js/script.js"></script>
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
