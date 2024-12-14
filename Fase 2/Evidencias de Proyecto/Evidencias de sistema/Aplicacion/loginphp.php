<?php
session_start();

// Datos de conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "intranet";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener los datos del formulario
$usuario = $_POST['usuario'];
$contraseña = md5($_POST['password']);

// Consulta para verificar el usuario
$sql = "SELECT * FROM usuarios WHERE usuario='$usuario' AND contraseña='$contraseña'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Login exitoso
    $_SESSION['usuario'] = $usuario;
    echo "Login exitoso. Bienvenido, $usuario!";
} else {
    // Login fallido
    echo "Usuario o contraseña incorrectos.";
}

$conn->close();
?>
