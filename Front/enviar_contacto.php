<?php
/**
 * PendiNails - Envío de formulario usando la API de Brevo (compatible con InfinityFree)
 */

// Cargar API key desde /private/config.php
require __DIR__ . "/../private/config.php";
// Si enviar_contacto.php está en la raíz de htdocs, usa:
// require __DIR__ . "/private/config.php";

error_reporting(0);

// Solo aceptar POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "acceso_denegado";
    exit;
}

// Recogida y limpieza de datos
$nombre  = strip_tags(trim($_POST['nombre']));
$email   = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$mensaje = strip_tags(trim($_POST['mensaje']));

if (empty($nombre) || empty($email) || empty($mensaje)) {
    echo "error_campos_vacios";
    exit;
}

// Construcción del mensaje
$asunto = "Nuevo contacto desde PendiNails: " . $nombre;
$fecha  = date('d/m/Y H:i:s');

$cuerpo  = "Detalles del mensaje recibido el $fecha:\n";
$cuerpo .= "------------------------------------------\n";
$cuerpo .= "Nombre: $nombre\n";
$cuerpo .= "Email: $email\n";
$cuerpo .= "Mensaje:\n$mensaje\n";
$cuerpo .= "------------------------------------------\n";

// API key real desde config.php
$apiKey = "";
//$apiKey = SENDINBLUE_API_KEY;

// Datos para Brevo
$data = [
    "sender" => [
        "email" => "pendinails@gmail.com",
        "name"  => "PendiNails Web"
    ],
    "to" => [
        ["email" => "pendinails@gmail.com"]
    ],
    "subject" => $asunto,
    "textContent" => $cuerpo
];

// Envío con cURL
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

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Respuesta final para JS
if ($httpCode === 201) {
    echo "OK";
} else {
    echo $response;
}
?>
