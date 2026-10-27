<?php
require_once __DIR__ . '/config/security.php';
pendinails_bootstrap();
$consulta = str_replace(["\r", "\n"], '', (string)($_SERVER['QUERY_STRING'] ?? ''));
$destino = 'Front/index.php' . ($consulta !== '' ? '?' . $consulta : '');
header('Location: ' . $destino);
exit;
