<?php 

// Detectar si estamos en local o en InfinityFree 

$esLocal = ($_SERVER['SERVER_NAME'] === 'localhost'); 



// Configuración según entorno 

if ($esLocal) { 

    // MODO LOCAL (XAMPP)

    $host = "localhost"; 

    $user = "root"; 

    $pass = ""; 

    $db   = "pendinails"; // Crea esta BD en phpMyAdmin

} else {

    // MODO INFINITYFREE (PRODUCCIÓN)

    $host = "sql302.infinityfree.com";   // Host MySQL de InfinityFree

    $user = "if0_41266123";              // Usuario MySQL

    $pass = "NOTNot51S7uEYp7";           // Contraseña MySQL

    $db   = "if0_41266123_pendinails";   // Nombre de la base de datos

}



// Conexión

$conn = new mysqli($host, $user, $pass, $db);



// Comprobar errores

if ($conn->connect_error) {

    die("Error de conexión: " . $conn->connect_error);

}

$conn->set_charset("utf8mb4");

?>







