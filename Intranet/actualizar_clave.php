<?php
session_start();
include('conexion.php'); // Incluye la conexión a la base de datos

// Obtener el ID del usuario logueado (esto puede depender de tu sistema de login)
$user_id = $_SESSION['usuario'];


// Variable para verificar si la contraseña ha sido actualizada
$passwordUpdated = false;

// Verificar si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los datos del formulario
    $current_password = $_POST['password'];
    $confirm_current_password = $_POST['confirm_current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // Verificar que la contraseña actual se haya introducido dos veces correctamente
    if ($current_password !== $confirm_current_password) {
        echo "Las contraseñas actuales no coinciden.";
        exit;
    }

    // Verificar que las nuevas contraseñas coincidan
    if ($new_password !== $confirm_new_password) {
        echo "Las nuevas contraseñas no coinciden.";
        exit;
    }

    // Obtener la contraseña actual del usuario en la base de datos
    $sql = "SELECT contraseña FROM usuarios WHERE nombre_usuario = '$user_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['contraseña'];
        
        // Verificar que la contraseña actual proporcionada coincida con la almacenada en la base de datos
        // Si usas sha256, compara directamente las versiones hasheadas
        if (hash('sha256', $current_password) !== $hashed_password) {
            echo "La contraseña actual no es correcta.";
            exit;
        }
        
        // Si todo está correcto, actualizar la contraseña en la base de datos
        $new_hashed_password = hash('sha256', $new_password);
        $update_query = "UPDATE usuarios SET contraseña = '$new_hashed_password' WHERE nombre_usuario = '$user_id'";

        if ($conn->query($update_query) === TRUE) {
            // Indicar que la contraseña fue actualizada exitosamente
            $passwordUpdated = true;
            // Cerrar la sesión aquí para que se redirija al login después del popup
            session_destroy();
        } else {
            echo "Hubo un error al actualizar la contraseña. Por favor, inténtalo de nuevo.";
        }
    } else {
        echo "Usuario no encontrado.";
    }

    $conn->close();
}
?>


<!DOCTYPE php>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Contraseña</title>
    <link rel="stylesheet" href="styles/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
</head>
<body class="body-login">
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <div class="login-container">
        <img src="Images/logo_clinica.png" alt="Clínica San Agustín">
        <form action="" method="POST">
            <label for="password">Contraseña actual:</label><br>
            <input type="password" id="password" name="password" required><br><br>

            <label for="confirm_current_password">Confirma tu contraseña actual:</label><br>
            <input type="password" id="confirm_current_password" name="confirm_current_password" required><br><br>
            
            <label for="new_password">Nueva contraseña:</label><br>
            <input type="password" id="new_password" name="new_password" required><br><br>
            
            <label for="confirm_new_password">Confirmar nueva contraseña:</label><br>
            <input type="password" id="confirm_new_password" name="confirm_new_password" required><br><br>
            <div class="password-container">
            
                <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
            </div>
            <input type="submit" value="Acceder">
        </form>
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function togglePassword() {
            var passwordInput = document.getElementById('password');
            var toggleIcon = document.querySelector('.toggle-password');
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = "password";
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>

    <!-- Mostrar SweetAlert2 si la contraseña se actualizó correctamente -->
<?php if ($passwordUpdated): ?>
        <script>
             // Mostrar el popup de éxito
             Swal.fire({
                title: '¡Contraseña actualizada!',
                text: 'Tu contraseña ha sido actualizada correctamente.',
                icon: 'success',
                timer: 3000, // Tiempo de 3 segundos antes de cerrarse automáticamente
                showConfirmButton: true // O puedes poner showConfirmButton: false si no quieres mostrar el botón
            }).then((result) => {
                // Redirigir al login después de cerrar el popup
                // Redirigir al login después de cerrar el popup
                window.location.href = 'login.php';
            });
        </script>
    <?php endif; ?>
</body>
</html>
