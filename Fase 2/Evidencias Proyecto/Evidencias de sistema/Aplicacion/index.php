<?php
// Detectar el código de error desde las cabeceras HTTP
$error_code = http_response_code();

if ($error_code == 403) {
    include '403.php'; // Mostrar página personalizada para error 403
    exit;
} elseif ($error_code == 404) {
    include '404.php'; // Mostrar página personalizada para error 404
    exit;
}

// Si no es un error, continuar con la lógica normal de la aplicación
// Aquí puedes cargar tus rutas y controladores
header("Location: home.php");

?>