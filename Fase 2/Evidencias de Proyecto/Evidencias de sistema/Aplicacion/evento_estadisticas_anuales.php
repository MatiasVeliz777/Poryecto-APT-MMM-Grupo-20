<?php
include("conexion.php");
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php"); // Redirigir al login si no ha iniciado sesión
    exit();
}

$error = "";

// Obtener el total de personal
$total_personal_sql = "SELECT COUNT(*) AS total_personal FROM personal";
$result_total_personal = $conn->query($total_personal_sql);
$total_personal = $result_total_personal->fetch_assoc()['total_personal'];

// Obtener el año actual
$anio_actual = date('Y'); // Año actual

// Consulta 1: Obtener todos los eventos del año actual
$eventos_anuales_sql = "
    SELECT id AS evento_id, titulo AS evento_titulo
    FROM eventos
    WHERE YEAR(fecha) = ?
";
$stmt_eventos_anuales = $conn->prepare($eventos_anuales_sql);
$stmt_eventos_anuales->bind_param("i", $anio_actual);
$stmt_eventos_anuales->execute();
$result_eventos_anuales = $stmt_eventos_anuales->get_result();

// Crear un array para almacenar los IDs de los eventos y contar el total de eventos
$total_eventos = 0;
$eventos_ids = [];
while ($row = $result_eventos_anuales->fetch_assoc()) {
    $total_eventos++;
    $eventos_ids[] = $row['evento_id']; // Almacenar el ID para la segunda consulta
}

// Inicializar contadores para asistencias totales
$total_asistencias = 0;

// Si hay eventos en el año actual, continuar con la segunda consulta
if (!empty($eventos_ids)) {
    // Convertir los IDs a una lista separada por comas
    $ids_placeholder = implode(',', $eventos_ids);

    // Consulta 2: Contar todas las asistencias registradas en los eventos del año
    $asistencias_sql = "
        SELECT COUNT(*) AS total_asistencias
        FROM asistencias_eventos
        WHERE evento_id IN ($ids_placeholder)
    ";
    $result_asistencias = $conn->query($asistencias_sql);

    if ($row = $result_asistencias->fetch_assoc()) {
        $total_asistencias = $row['total_asistencias'];
    }
}

// Calcular el porcentaje de participación
$capacidad_total = $total_personal * $total_eventos; // Capacidad máxima total (todos los empleados en todos los eventos)
$porcentaje_participacion = $capacidad_total > 0 ? ($total_asistencias / $capacidad_total) * 100 : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas Anuales de Asistencia a Eventos</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }

        .chart-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px auto;
            max-width: 700px; /* Ajustar tamaño máximo del contenedor */
        }

        canvas {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <h2>Estadísticas Anuales de Asistencia a Eventos (<?php echo $anio_actual; ?>)</h2>
    <p><strong>Total de eventos en el año:</strong> <?php echo $total_eventos; ?></p>
    <p><strong>Total de asistencias registradas:</strong> <?php echo $total_asistencias; ?></p>
    <p><strong>Porcentaje de participación:</strong> <?php echo number_format($porcentaje_participacion, 2); ?>%</p>
    <div class="chart-container">
        <canvas id="attendanceChart"></canvas>
    </div>
    <button id="downloadPDF">Descargar PDF</button>

    <script>
    const ctx = document.getElementById('attendanceChart').getContext('2d');

    // Datos para el gráfico
    const data = {
        labels: ['Capacidad Total', 'Asistencias Registradas'],
        datasets: [{
            data: [<?php echo $capacidad_total; ?>, <?php echo $total_asistencias; ?>],
            backgroundColor: ['rgba(54, 162, 235, 0.6)', 'rgba(255, 99, 132, 0.6)'],
            borderColor: ['rgba(54, 162, 235, 1)', 'rgba(255, 99, 132, 1)'],
            borderWidth: 1
        }]
    };

    // Configuración del gráfico
    const chart = new Chart(ctx, {
        type: 'pie', // Gráfico de pastel
        data: data,
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Comparativa de Capacidad Total vs Asistencias Registradas'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            return `${label}: ${value} (${(value / <?php echo $capacidad_total; ?> * 100).toFixed(2)}%)`;
                        }
                    }
                }
            }
        }
    });

    // Función para descargar el gráfico como PDF
    document.getElementById('downloadPDF').addEventListener('click', function () {
        html2canvas(document.getElementById('attendanceChart')).then(canvas => {
            const imgData = canvas.toDataURL('image/png'); // Convertir el canvas a imagen
            const { jsPDF } = window.jspdf; // Importar jsPDF
            const pdf = new jsPDF();

            // Ajustar las dimensiones
            const imgWidth = 190; // Ancho de la imagen en el PDF
            const pageWidth = pdf.internal.pageSize.getWidth();
            const imgHeight = canvas.height * imgWidth / canvas.width; // Proporción de la imagen
            const pageHeight = pdf.internal.pageSize.getHeight();

            // Centrar la imagen en la página
            const x = (pageWidth - imgWidth) / 2;
            const y = (pageHeight - imgHeight) / 2;

            // Agregar título y la imagen al PDF
            pdf.text('Estadísticas Anuales de Asistencia a Eventos', 10, 10); // Título del PDF
            pdf.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight); // Agregar la imagen al PDF
            pdf.save('Estadisticas_Anuales.pdf'); // Descargar el PDF
        });
    });
    </script>
</body>
</html>

