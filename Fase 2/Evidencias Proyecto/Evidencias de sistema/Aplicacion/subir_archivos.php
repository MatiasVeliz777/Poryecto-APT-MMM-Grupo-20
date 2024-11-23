<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Archivos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Portal para Subir y Descargar Archivos</h2>

        <!-- Formulario para subir archivos -->
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="archivo">Selecciona un archivo (PDF o DOC):</label>
                <input type="file" name="archivo" class="form-control" id="archivo" accept=".pdf,.doc,.docx" required>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Subir Archivo</button>
        </form>

        <hr>

        <!-- Lista de archivos para descargar -->
        <h3 class="text-center">Archivos Disponibles</h3>
        <ul class="list-group">
            <?php
                // Aquí se cargarán los archivos de la base de datos para descargarlos
                include 'listar_archivos.php';
            ?>
        </ul>
    </div>
</body>
</html>
