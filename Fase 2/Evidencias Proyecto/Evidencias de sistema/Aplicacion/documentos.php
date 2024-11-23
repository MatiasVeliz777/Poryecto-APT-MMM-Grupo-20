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

// Obtener los archivos disponibles
$sql_archivos = "SELECT id, nombre_archivo, tipo_archivo, ruta_archivo, descripcion FROM archivos";
$result_archivos = $conn->query($sql_archivos);

// Verificar si hay resultados
$archivos = [];
if ($result_archivos->num_rows > 0) {
    while ($row = $result_archivos->fetch_assoc()) {
        $archivos[] = $row;
    }
}

$conn->close();
 
?>


<!DOCTYPE php>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Archivos</title>
    <link rel="icon" href="Images/icono2.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="styles/style.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
    <h1>Soporte Técnico</h1>
</header>


<div class="solicitud-container-wrapper">
    <!-- Box para las instrucciones -->
    <div class="solicitud-instructions">
        <h3>Instrucciones</h3>
        <p>1. Ingresa el título del problema que estás experimentando.</p>
        <p>2. Describe el problema con la mayor cantidad de detalles posible. <strong> Procura aclarar el Edificio y Piso en el cual se necesita el soporte.</strong></p>
        <p>4. Indica el nivel de urgencia.</p>
        <p>5. Puedes adjuntar una imagen o archivo relacionado con el problema si es necesario.</p>
    </div>

        <div class="documento-container"> 
    <!-- Botón para abrir el modal -->
    <?php if ($_SESSION['rol'] == 5): ?>
    <div class="text-center mt-4">
            <button class="btn btn-primary" data-toggle="modal" style="margin-bottom: 20px;" data-target="#subirArchivoModal">
                Subir Nuevo Archivo
            </button>
        </div>
        <?php endif; ?>

    <h2 class="documento-h2">Descargar Documento</h2>
    <form action="#" method="POST">
        <div class="documento-form-group">
            <label for="documento">Hola <?php echo $user_data['nombre']; ?>, por favor</label>
            <label for="documento">Seleccione el documento que desea descargar:</label>
            <select id="documento" name="documento" class="documento-form-select" onchange="mostrarDescripcion()">
                <option value="" disabled selected>Elija un documento</option>
                <?php foreach ($archivos as $archivo): ?>
                    <option value="<?php echo $archivo['id']; ?>" 
                            data-descripcion="<?php echo htmlspecialchars($archivo['descripcion']); ?>" 
                            data-ruta="<?php echo $archivo['ruta_archivo']; ?>">
                        <?php echo $archivo['nombre_archivo']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="descripcion-contenedor" class="mt-3"></div>
        <div id="acciones-contenedor" class="mt-3 d-flex gap-2" style="background-color: white; width:100%;">
            <!-- Botones dinámicos aparecerán aquí -->
        </div>
    </form>
</div>
</div>
</div>
</div>
</div>
</div>


<!-- Modal para subir archivo -->
<div class="modal fade" id="subirArchivoModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Subir Nuevo Archivo</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Formulario para subir archivos -->
                <form action="documento_subir.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group" style="margin-top:10px;">
                        <label for="nombre_archivo">Nombre del Documento:</label>
                        <input type="text" name="nombre_documento" class="form-control" id="nombre_archivo" placeholder="Dale un nombre al documento"required>
                    </div>
                    <div class="form-group"style="margin-top:10px;">
                        <label for="descripcion_archivo">Descripción del Documento:</label>
                        <textarea name="descripcion" class="form-control" id="descripcion_archivo" rows="3" placeholder="Escribe una breve descipciond el docuemnto (Opcional)" required></textarea>
                    </div>
                    <div class="form-group"style="margin-top:20px;">
                        <label for="archivo">Selecciona un archivo (PDF o DOC):</label>
                        <input type="file" name="archivo" class="form-control" id="archivo" accept=".pdf,.doc,.docx" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px; width:100%;">Subir Archivo</button>
                </form>
            </div>
        </div>
    </div>
</div>


</div>
<script>
function mostrarDescripcion() {
    const documentoSelect = document.getElementById("documento");
    const descripcionContenedor = document.getElementById("descripcion-contenedor");
    const accionesContenedor = document.getElementById("acciones-contenedor");

    // Obtener el documento seleccionado
    const selectedOption = documentoSelect.options[documentoSelect.selectedIndex];
    const descripcion = selectedOption.getAttribute("data-descripcion");
    const ruta = selectedOption.getAttribute("data-ruta");
    const idArchivo = selectedOption.value; // ID del archivo para eliminar

    if (descripcion && ruta) {
        descripcionContenedor.innerHTML = `<p>${descripcion}</p>`;

        // Generar botones dinámicamente
        let botonesHTML = `
            <a href="${ruta}" class="btn btn-success"  style="width: 100%;" download>Descargar</a>
        `;

        // Mostrar el botón "Eliminar" solo si el rol del usuario es 5
        <?php if ($_SESSION['rol'] == 5): ?>
            botonesHTML += `
                <button type="button" class="btn btn-danger" onclick="eliminarArchivo(${idArchivo})">Eliminar</button>
            `;
        <?php endif; ?>

        accionesContenedor.innerHTML = botonesHTML;
    } else {
        descripcionContenedor.innerHTML = "<p class='text-danger'>Seleccione un documento válido.</p>";
        accionesContenedor.innerHTML = ""; // Limpia los botones si no hay selección válida
    }
}

function eliminarArchivo(idArchivo) {
    // Mostrar alerta de confirmación con SweetAlert2
    Swal.fire({
        title: "¿Estás seguro?",
        text: "No podrás recuperar este archivo después de eliminarlo.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            // Realizar solicitud AJAX para eliminar el archivo
            fetch('documento_eliminar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `archivo_id=${idArchivo}`
            })
            .then(response => response.json()) // Procesar respuesta como JSON
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: "success",
                        title: "¡Eliminado!",
                        text: data.message,
                        confirmButtonText: "Aceptar"
                    }).then(() => {
                        location.reload(); // Recarga la página para actualizar la lista
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: data.message,
                        confirmButtonText: "Aceptar"
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Ocurrió un error al realizar la solicitud.",
                    confirmButtonText: "Aceptar"
                });
                console.error("Error al realizar la solicitud:", error);
            });
        }
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const params = new URLSearchParams(window.location.search);
    const status = params.get("status");
    const message = params.get("message");

    if (status === "success") {
        Swal.fire({
            icon: "success",
            title: "¡Éxito!",
            text: message,
            confirmButtonText: "Aceptar"
        });
    } else if (status === "error") {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: message,
            confirmButtonText: "Aceptar"
        });
    }
});
</script>
 
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="scripts/script.js"></script>

    <footer class="footer">
    <div class="footer-container">
        <div class="footer-section">
            <h4>Contáctanos</h4>
            <p>Teléfono: 56 22 928 1600</p>
            <p>www.saludsanagustin.cl/csa/</p>
        </div>
        <div class="footer-section">
            <h4>Horarios de atención</h4>
            <p>De Lunes a Sábado:</p>
            <p>Desde 07:00 hrs.</p>
            <p>Domingo: Desde las 08:00</p>
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
            <p>San Agustín 473 – 442</p>
            <p>Melipilla, Chile</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2024 Clínica de Salud. Todos los derechos reservados.</p>
    </div>
</footer> 
</body>
</html>
