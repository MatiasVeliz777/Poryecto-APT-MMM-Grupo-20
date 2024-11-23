<?php
session_start();

// Verificar si el usuario está logeado y es activo
if (!isset($_SESSION['usuario']) || !isset($_SESSION['activo']) || $_SESSION['activo'] != 1) {
    // Redirigir a la página de login con un mensaje de error si no está activo
    header("Location: login.php?error=Usuario inactivo");
    exit();
}
?>
