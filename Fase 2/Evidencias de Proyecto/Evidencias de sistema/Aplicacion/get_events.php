<?php
include('conexion.php');
session_start();

$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Función para traducir los nombres de los días y meses al español
function traducir_fecha($fecha){
    $dias = array("Sunday" => "Domingo", "Monday" => "Lunes", "Tuesday" => "Martes", 
                  "Wednesday" => "Miércoles", "Thursday" => "Jueves", 
                  "Friday" => "Viernes", "Saturday" => "Sábado");
    
    $meses = array("January" => "Enero", "February" => "Febrero", "March" => "Marzo", 
                   "April" => "Abril", "May" => "Mayo", "June" => "Junio", 
                   "July" => "Julio", "August" => "Agosto", "September" => "Septiembre", 
                   "October" => "Octubre", "November" => "Noviembre", "December" => "Diciembre");
    
    $dia_nombre = $dias[date('l', strtotime($fecha))];
    $dia_numero = date('d', strtotime($fecha));
    $mes_nombre = $meses[date('F', strtotime($fecha))];
    $anio = date('Y', strtotime($fecha));
    
    return " $dia_numero de $mes_nombre de $anio";
}

$query = $conn->prepare("SELECT id, DAY(fecha) AS day, fecha, titulo, hora, ubicacion FROM eventos WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? ORDER BY fecha ASC");
$query->bind_param("ii", $month, $year);
$query->execute();
$result = $query->get_result();

$events = [];
$eventCards = '';

// Generar tarjetas de eventos
while ($row = $result->fetch_assoc()) {
    $day = $row['day'];
    $events[$day][] = [
        'id' => $row['id'],
        'titulo' => $row['titulo'],
        'fecha' => $row['fecha'],
        'hora' => $row['hora'],
        'ubicacion' => $row['ubicacion']
    ];
}

// Crear el HTML de cada tarjeta de evento con estilos y botones
foreach ($events as $day => $eventList) {
    foreach ($eventList as $event) {
        $eventCards .= "<div id='event-{$event['id']}' class='event-card clickeable' onclick=\"location.href='evento_asistencia.php?evento_id={$event['id']}'\" style='cursor: pointer; position: relative;'>
                            <p class='event-date'> " . traducir_fecha($event['fecha']) . "</p>
                            <h5 class='event-title'>{$event['titulo']}</h5>
                            <p class='event-time'>Hora: " . date('H:i', strtotime($event['hora'])) . "</p>
                            <p class='event-location'>Ubicación: {$event['ubicacion']}</p>";

        // Contenedor para los botones de acción
        $eventCards .= "<div class='event-actions' style='position: absolute; top: 10px; right: 10px; display: flex; gap: 5px;'>";

        // Si el usuario tiene permisos para modificar o eliminar eventos
        if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
            // Botón para eliminar el evento
            $eventCards .= "<button class='btn btn-outline-danger btn-sm' onclick=\"event.stopPropagation(); confirmarEliminacion('{$event['id']}')\">
                                <svg xmlns='http://www.w3.org/2000/svg' width='17' height='20' fill='currentColor' class='bi bi-dash-circle' viewBox='0 0 16 16'>
                                    <path d='M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16'/>
                                    <path d='M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8'/>
                                </svg>
                             </button>";

            // Botón para modificar el evento
            $eventCards .= "<button class='btn btn-outline-dark btn-sm' data-bs-toggle='modal' data-bs-target='#updateEventModal{$event['id']}' onclick='event.stopPropagation();'>
                                <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-arrow-clockwise' viewBox='0 0 16 16'>
                                    <path fill-rule='evenodd' d='M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z'/>
                                    <path d='M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466'/>
                                </svg>
                             </button>";
        }

        // Botón "Asistir" si el usuario no está registrado y el evento es futuro
        $fecha_actual = date("Y-m-d");
        $evento_id = $event['id'];
        $rut_usuario = $_SESSION['rut'];

        $check_sql = "SELECT fecha FROM eventos WHERE id = ? AND fecha >= ?";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bind_param("is", $evento_id, $fecha_actual);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $check_asistencia_sql = "SELECT * FROM asistencias_eventos WHERE evento_id = ? AND rut_usuario = ?";
            $stmt_check_asistencia = $conn->prepare($check_asistencia_sql);
            $stmt_check_asistencia->bind_param("is", $evento_id, $rut_usuario);
            $stmt_check_asistencia->execute();
            $result_check_asistencia = $stmt_check_asistencia->get_result();

            if ($result_check_asistencia->num_rows == 0) {
                // Mostrar el botón "Asistir" si el usuario no está registrado y el evento es futuro
                $eventCards .= "<form method='POST' style='display: inline;' onclick='event.stopPropagation();'>
                                    <input type='hidden' name='evento_id' value='{$event['id']}'>
                                    <button type='submit' name='registrar_asistencia' class='btn btn-outline-primary btn-sm'>Asistir</button>
                                </form>";
            }

            $stmt_check_asistencia->close();
        }

        $stmt_check->close();

        $eventCards .= "</div>"; // Cerrar el div de los botones de acción
        $eventCards .= "</div><hr>"; // Cerrar el div de la tarjeta de evento

        // Modal de actualización de evento
        $eventCards .= "<div class='modal fade' id='updateEventModal{$event['id']}' tabindex='-1' aria-labelledby='updateEventModalLabel' aria-hidden='true'>
            <div class='modal-dialog'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title'>Actualizar Evento</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Cerrar'></button>
                    </div>
                    <div class='modal-body'>
                        <form method='POST'>
                            <input type='hidden' name='id' value='{$event['id']}'>
                            <div class='form-group mb-3'>
                                <label for='titulo-{$event['id']}'>Título del Evento</label>
                                <input type='text' class='form-control' id='titulo-{$event['id']}' name='titulo' value='{$event['titulo']}' required>
                            </div>
                            <div class='form-group mb-3'>
                                <label for='fecha-{$event['id']}'>Fecha</label>
                                <input type='date' class='form-control' id='fecha-{$event['id']}' name='fecha' value='{$event['fecha']}' required>
                            </div>
                            <div class='form-group mb-3'>
                                <label for='hora-{$event['id']}'>Hora</label>
                                <input type='time' class='form-control' id='hora-{$event['id']}' name='hora' value='{$event['hora']}' required>
                            </div>
                            <div class='form-group mb-3'>
                                <label for='ubicacion-{$event['id']}'>Ubicación</label>
                                <input type='text' class='form-control' id='ubicacion-{$event['id']}' name='ubicacion' value='{$event['ubicacion']}' required>
                            </div>
                            <button type='submit' name='actualizar_evento' class='btn btn-success'>Actualizar Evento</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>";
    }
}

echo json_encode(['events' => array_keys($events), 'cards' => $eventCards]);

$query->close();
?>
