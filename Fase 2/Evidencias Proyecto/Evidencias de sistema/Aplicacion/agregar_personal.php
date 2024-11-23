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
$solicitud_opc = false; // Indica si la operación fue exitosa
$error_opc = false;  

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


    $apellidos = $_POST['apellidos'];
    $nombres = $_POST['nombres'];
    $rut_personal = $_POST['rut_personal'];
    $correo = $_POST['correo'];
    $fecha_nac = $_POST['fecha_nac'];
    $cargo_id_ag = $_POST['nom-cargo']; // Recibir el cargo seleccionado
    $rol_id_ag = $_POST['nom-rol']; 
    
    $usuario = $_SESSION['usuario'];

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
            echo "Error al subir la imagen.";
            exit();
        }
    } else {
        $imagen_db = $nombre_completo;
    }


    // Insertar los datos del nuevo empleado en la tabla personal
    $sql = "INSERT INTO personal (rut, nombre, correo, imagen, fecha_nacimiento, cargo_id, rol_id) 
            VALUES ('$rut_personal', '$nombre_completo', '$correo', " . ($imagen_db ? "'$imagen_db'" : "NULL") . ", '$fecha_nac', $cargo_id_ag, $rol_id_ag)";

    if ($conn->query($sql) === TRUE) {
        $solicitudEnviada = true; // Marcamos que la solicitud se ha enviado
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
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
