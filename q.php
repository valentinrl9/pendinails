<?php
require_once __DIR__ . '/config/security.php';
pendinails_bootstrap();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/visitas.php';

if (isset($conn) && $conn instanceof mysqli) {
    pendinails_registrar_visita($conn, 'qr');
}

header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');
header('Location: /Front/index.php', true, 302);
exit;
