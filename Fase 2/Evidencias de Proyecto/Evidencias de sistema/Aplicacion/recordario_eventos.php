<?php

require 'phpmailer/PHPMailer.php';
require 'phpmailer/Exception.php';
require 'phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Conexión a la base de datos
include("conexion.php");

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}


// Consultar eventos que se realizarán mañana
$sql = "SELECT e.id, e.titulo, e.fecha, e.hora, e.ubicacion, u.correo
        FROM eventos e
        JOIN asistencias_eventos a ON e.id = a.evento_id
        JOIN personal u ON a.rut_usuario = u.rut
        WHERE DATEDIFF(e.fecha, CURDATE()) = 1"; // Filtrar por correo específico
$result = $conn->query($sql);

// Configuración del remitente y destinatario
$correo_remitente = 'Notificaciones@saludsanagustin.cl';
$nombre_remitente = 'Clínica Salud San Agustín';

// Crear una instancia de PHPMailer
$mail = new PHPMailer(true);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Datos del evento
        $evento_id = $row['id'];
        $titulo = $row['titulo'];
        $fecha = $row['fecha'];
        $hora = $row['hora'];
        $ubicacion = $row['ubicacion'];
        $correo_usuario = $row['correo'];

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'mail.saludsanagustin.cl';
            $mail->SMTPAuth = true;
            $mail->Username = 'Notificaciones@saludsanagustin.cl';
            $mail->Password = 'Cmsa.666526%';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Cambiar a SMTPS para SSL
            $mail->Port = 465; // Puerto para SSL

            // Configuración del conjunto de caracteres y codificación
            $mail->CharSet = 'UTF-8'; // Configurar UTF-8 para caracteres especiales
            $mail->Encoding = 'base64'; // Codificación de caracteres

            // Remitente
            $mail->setFrom($correo_remitente, $nombre_remitente);
            // Destinatario
            $mail->addAddress($correo_usuario);

            // Asunto del correo
            $mail->Subject = '📢 Recordatorio: Evento Importante en Clínica Salud San Agustín';

            // Cuerpo en HTML
            $mail->Body = '
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Recordatorio de Evento</title>
            </head>
            <body style="font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px; color: #333;">
                <div style="max-width: 600px; margin: auto; background-color: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h1 style="color: #0056b3; text-align: center;">📅 Recordatorio de Evento</h1>
                    <p style="font-size: 18px;">Estimado/a,</p>
                    <p style="line-height: 1.6;">Te recordamos que próximamente se realizará un evento importante en nuestra clínica. Aquí tienes los detalles:</p>
                    <ul style="font-size: 16px; line-height: 1.6;">
                        <li><strong>Título:</strong> ' . htmlspecialchars($titulo) . '</li>
                        <li><strong>Fecha:</strong> ' . htmlspecialchars($fecha) . '</li>
                        <li><strong>Hora:</strong> ' . htmlspecialchars($hora) . '</li>
                        <li><strong>Lugar:</strong> ' . htmlspecialchars($ubicacion) . '</li>
                    </ul>
                    <p style="line-height: 1.6;">Por favor, confirma tu asistencia respondiendo a este correo o comunicándote con el área de recepción.</p>
                    <p style="text-align: center; margin-top: 20px;">
                        <a href="https://saludsanagustin.cl/csa/Intranet/calendario_prueba.php" style="display: inline-block; background-color: #0056b3; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-size: 16px;">Confirmar Asistencia</a>
                    </p>
                    <p style="font-size: 14px; text-align: center; color: #555; margin-top: 30px;">
                        Gracias por ser parte de nuestra comunidad.
                    </p>
                </div>
            </body>
            </html>
            ';
            

            // Cuerpo alternativo en texto plano
            $mail->AltBody = 'Recordatorio de Evento: Fecha: 15 de diciembre de 2024. Hora: 10:00 AM. Lugar: Salón principal, Clínica Salud San Agustín.';

            // Enviar el correo
            $mail->send();
            echo "Correo enviado a $correo_usuario\n";
} catch (Exception $e) {
    echo "Error al enviar el correo: {$mail->ErrorInfo}\n";
}
}
} 

?>
