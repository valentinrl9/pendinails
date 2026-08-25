<?php
/**
 * PendiNails - Envío de formulario de contacto
 * Email (Brevo o PHPMailer) es el canal principal. WhatsApp es opcional en el navegador.
 */

require __DIR__ . "/../private/config.php";
require __DIR__ . "/phpmailer/src/Exception.php";
require __DIR__ . "/phpmailer/src/PHPMailer.php";
require __DIR__ . "/phpmailer/src/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: text/plain; charset=UTF-8');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "acceso_denegado";
    exit;
}

if (!empty($_POST['website'])) {
    echo "OK";
    exit;
}

$nombre  = trim(strip_tags((string)($_POST['nombre'] ?? '')));
$email   = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_SANITIZE_EMAIL);
$mensaje = trim(strip_tags((string)($_POST['mensaje'] ?? '')));

if ($nombre === '' || $mensaje === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "error_campos_vacios";
    exit;
}

$fecha  = date('d/m/Y H:i:s');
$asunto = "Nuevo contacto desde PendiNails: " . $nombre;
$cuerpo  = "Detalles del mensaje recibido el $fecha:\n";
$cuerpo .= "------------------------------------------\n";
$cuerpo .= "Nombre: $nombre\n";
$cuerpo .= "Email: $email\n";
$cuerpo .= "Mensaje:\n$mensaje\n";
$cuerpo .= "------------------------------------------\n";

$logDir = __DIR__ . "/mensajes";
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logOk = (bool) file_put_contents(
    $logDir . "/mensajes_recibidos.txt",
    "==========================================\nFECHA: " . date('d-m-Y H:i:s') . "\nNUEVO MENSAJE RECIBIDO:\n" . $cuerpo . "\n",
    FILE_APPEND | LOCK_EX
);

$mailOk = false;
$apiKey = defined('SENDINBLUE_API_KEY') ? trim((string) SENDINBLUE_API_KEY) : '';

if ($apiKey !== '') {
    $data = [
        "sender" => [
            "email" => "pendinails@gmail.com",
            "name"  => "PendiNails Web"
        ],
        "to" => [
            ["email" => "pendinails@gmail.com"]
        ],
        "replyTo" => [
            "email" => $email,
            "name"  => $nombre
        ],
        "subject" => $asunto,
        "textContent" => $cuerpo
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.brevo.com/v3/smtp/email");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: application/json",
        "api-key: $apiKey",
        "content-type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $mailOk = ($httpCode === 201);
}

if (!$mailOk) {
    try {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $smtpPass = defined('SMTP_PASSWORD') ? trim((string) SMTP_PASSWORD) : '';

        if ($smtpPass !== '') {
            $mail->isSMTP();
            $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'smtp-relay.brevo.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = defined('SMTP_USER') ? SMTP_USER : 'pendinails@gmail.com';
            $mail->Password   = $smtpPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = defined('SMTP_PORT') ? (int) SMTP_PORT : 587;
        } else {
            $mail->isMail();
        }

        $mail->setFrom('pendinails@gmail.com', 'PendiNails Web');
        $mail->addAddress('pendinails@gmail.com');
        $mail->addReplyTo($email, $nombre);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo;
        $mailOk = $mail->send();
    } catch (Exception $e) {
        $mailOk = false;
    }
}

if ($logOk || $mailOk) {
    echo "OK";
} else {
    echo "error_envio";
}
