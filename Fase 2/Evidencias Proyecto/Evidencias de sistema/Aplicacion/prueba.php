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
        WHERE rut = (SELECT rut FROM usuarios WHERE nombre_usuario = '$usuario')";
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
if ($rol_id_seleccionado == 0) {
    $sql_solicitudes = "SELECT soportes.*, roles.rol, personal.nombre, personal.imagen as profile_photo, soportes.estado
                    FROM soportes 
                    JOIN roles ON soportes.rol_id = roles.id
                    JOIN personal ON soportes.rut = personal.rut";
} else {
    $sql_solicitudes = "SELECT soportes.*, roles.rol, personal.nombre, personal.imagen as profile_photo, soportes.estado 
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
    <title>Soporte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="style.css" rel="stylesheet">

</head>
<body>
<div class="container">
    <h1 class="text-center my-4">Ver Solicitudes de Soporte</h1>

    <!-- Filtro de roles -->
    <div class="filter-container">
        <form method="GET" action="">
            <label for="rol_id">Filtrar por Rol:</label>
            <select name="rol_id" id="rol_id" class="form-select" onchange="this.form.submit()">
                <option value="0">Todos los roles</option>
                <?php while ($rol = $result_roles->fetch_assoc()) { ?>
                    <option value="<?php echo $rol['id']; ?>" <?php echo $rol_id_seleccionado == $rol['id'] ? 'selected' : ''; ?>>
                        <?php echo $rol['rol']; ?>
                    </option>
                <?php } ?>
            </select>
        </form>
    </div>

    <!-- Tabla de solicitudes -->
    <div class="table-container">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Título</th>
                    <th scope="col">Enviado por</th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Área</th>
                    <th scope="col">Estado</th>
                </tr>
            </thead>
            <tbody>
    <?php if ($result_solicitudes->num_rows > 0) {
        $index = 1;
        while($soporte = $result_solicitudes->fetch_assoc()) { ?>
        <tr onclick="toggleDetail('<?php echo $soporte['titulo']; ?>', '<?php echo $soporte['contenido']; ?>', '<?php echo $soporte['estado']; ?>', '<?php echo !empty($soporte['imagen']) ? $soporte['imagen'] : 'Images/noimagen.jpg'; ?>', '<?php echo $soporte['id']; ?>','')">
            <th scope="row"><?php echo $index++; ?></th>
            <td><?php echo $soporte['titulo']; ?></td>
            <td><?php echo $soporte['nombre']; ?></td>
            <td><?php echo date('d/m/Y', strtotime($soporte['fecha_creacion'])); ?></td>
            <td><?php echo $soporte['rol']; ?></td>
            <td><?php echo $soporte['estado']; ?></td>
        </tr>
    <?php } } else { ?>
        <tr>
            <td colspan="7" class="text-center">No se encontraron solicitudes de soporte.</td>
        </tr>
    <?php } ?>
</tbody>
        </table>
    </div>

    <!-- Detalles de la solicitud con selector de estado -->
    <div class="soporte-detail" id="soporteDetail">
        <!-- Foto de perfil y nombre -->
        <div class="d-flex align-items-center mb-3" style="background-color: none;">
            <img src="<?php echo $soporte['profile_photo']; ?>" alt="Foto de Perfil" id="detailProfileImage" class="rounded-circle me-3" style="width: 60px; height: 60px;">
            <h4 id="detailNombre">Nombre del remitente</h4>
        </div>

        <h2 class="section-title">Título de la solicitud:</h2>
        <h2 class="soporte-title" id="detailTitle">Título</h2>

        <h2 class="section-title">Contenido de la solicitud:</h2>
        <p class="soporte-content" id="detailContent">Contenido de la solicitud seleccionada.</p>

        <h2 class="section-title">Estado de la solicitud:</h2>
        <!-- Selección de estado editable en el panel -->
        <form method="POST" action="actualizar_estado.php">
            <input type="hidden" name="soporte_id" id="soporteId" value="">
            <select name="estado" class="form-select" id="detailEstado" onchange="this.form.submit()">
                <option value="En espera">En espera</option>
                <option value="En curso">En curso</option>
                <option value="Solucionado">Solucionado</option>
            </select>
        </form>

        <h2 class="section-title">Imagen adjunta:</h2>
        <img src="Images/noimagen.jpg" alt="Imagen de Soporte" class="soporte-image" id="detailImage">
    </div>
</div>

<script>
    let isDetailOpen = false;

    function toggleDetail(title, content, estado, image, soporteId, nombre_user, profileImage,) {
        const detail = document.getElementById('soporteDetail');
        const imageElement = document.getElementById('detailImage');
        const estadoElement = document.getElementById('detailEstado');
        const soporteIdElement = document.getElementById('soporteId');
        const profileImageElement = document.getElementById('detailProfileImage');
        const nombreElement = document.getElementById('detailNombre');

        // Alternar el panel de detalles
        if (isDetailOpen) {
            detail.style.right = '-20%';
            detail.style.opacity = '0';
            isDetailOpen = false;
        } else {
            detail.style.display = 'block';
            detail.style.right = '0';
            detail.style.opacity = '1';

            // Actualizar contenido
            document.getElementById('detailTitle').innerText = title;
            document.getElementById('detailContent').innerText = content;
            imageElement.src = image || 'Images/noimagen.jpg';
            soporteIdElement.value = soporteId;
            // Actualizar la imagen de perfil y el nombre del remitente
            profileImageElement.src = profileImage || 'Images/profile_photo/imagen_default.jpg';
            nombreElement.innerText = nombre_user;

            // Seleccionar el estado actual
            estadoElement.value = estado;

            isDetailOpen = true;
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
