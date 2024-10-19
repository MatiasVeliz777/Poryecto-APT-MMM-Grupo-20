<?php
session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include('conexion.php');

    // Verifica si se están recibiendo los campos correctos
    if (isset($_POST['usuario']) && isset($_POST['password'])) {
        $usuario = $_POST['usuario'];
        // Encriptamos la contraseña usando SHA2 con 256 bits
        $contrasena = hash('sha256', $_POST['password']); 

        // Consulta para verificar el usuario y la contraseña encriptada
        $sql = "SELECT * FROM usuarios WHERE nombre_usuario='$usuario' AND contraseña='$contrasena'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Login exitoso
            $_SESSION['usuario'] = $usuario;
            header('location: perfil.php');
            exit();
        } else {
            // Login fallido
            $error = "Usuario o contraseña incorrectos.";
        }
    } else {
        $error = "Los campos de usuario y contraseña son requeridos.";
    }
    
    $conn->close();
}
?>


<!DOCTYPE php>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        
    </style>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body class="body-login">
    <div class="login-container">
        <img src="Images/logo_clinica.png" alt="Clínica San Agustín">
        <form action="" method="POST">
            <input type="text" id="usuario" name="usuario" placeholder="Rut sin puntos ni guion" required>
            <div class="password-container">
                <input type="password" id="password" name="password" placeholder="Contraseña" required>
                <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
            </div>
            <input type="submit" value="Acceder">
        </form>
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
    </div>

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
</body>
</html>
