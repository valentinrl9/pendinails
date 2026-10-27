<?php

function pendinails_visita_es_bot(): bool
{
    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    if (trim($ua) === '') {
        return true;
    }

    return (bool)preg_match(
        '/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|telegrambot|curl|wget|python-requests|headless|lighthouse|pagespeed|pingdom|uptimerobot|monitoring/i',
        $ua
    );
}

function pendinails_visitas_zona(mysqli $conn): void
{
    $zona = new DateTimeZone('Europe/Madrid');
    $offset = (new DateTime('now', $zona))->format('P');
    $conn->query("SET time_zone = '" . $conn->real_escape_string($offset) . "'");
}

function pendinails_visitas_preparar(mysqli $conn): bool
{
    static $lista = false;
    if ($lista) {
        return true;
    }

    $sql = "CREATE TABLE IF NOT EXISTS visitas (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        creado_en DATETIME NOT NULL,
        dia DATE NOT NULL,
        tipo ENUM('visita','qr') NOT NULL,
        ruta VARCHAR(120) NOT NULL,
        PRIMARY KEY (id),
        KEY idx_dia_tipo (dia, tipo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if ($conn->query($sql) !== true) {
        return false;
    }

    pendinails_visitas_zona($conn);
    $lista = true;
    return true;
}

function pendinails_registrar_visita(mysqli $conn, string $tipo): void
{
    if ($tipo !== 'visita' && $tipo !== 'qr') {
        return;
    }
    if (pendinails_visita_es_bot()) {
        return;
    }
    $proposito = strtolower((string)($_SERVER['HTTP_PURPOSE'] ?? $_SERVER['HTTP_X_PURPOSE'] ?? ''));
    if ($proposito === 'prefetch' || $proposito === 'preview') {
        return;
    }
    if (!pendinails_visitas_preparar($conn)) {
        return;
    }

    $ruta = (string)($_SERVER['REQUEST_URI'] ?? '/');
    $ruta = str_replace(["\r", "\n"], '', $ruta);
    if (strlen($ruta) > 120) {
        $ruta = substr($ruta, 0, 120);
    }

    $stmt = $conn->prepare('INSERT INTO visitas (creado_en, dia, tipo, ruta) VALUES (NOW(), CURDATE(), ?, ?)');
    if (!$stmt) {
        return;
    }
    $stmt->bind_param('ss', $tipo, $ruta);
    $stmt->execute();
    $stmt->close();
}

function pendinails_resumen_visitas(mysqli $conn): array
{
    $vacio = ['visita' => 0, 'qr' => 0];
    $resumen = [
        'hoy' => $vacio,
        'semana' => $vacio,
        'mes' => $vacio,
        'dias' => [],
        'listo' => false,
        'error' => '',
    ];

    if (!pendinails_visitas_preparar($conn)) {
        $resumen['error'] = (string)$conn->error;
        return $resumen;
    }

    $rangos = [
        'hoy' => 'dia = CURDATE()',
        'semana' => 'dia >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)',
        'mes' => 'dia >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)',
    ];

    foreach ($rangos as $clave => $where) {
        $consulta = $conn->query("SELECT tipo, COUNT(*) AS n FROM visitas WHERE $where GROUP BY tipo");
        if (!$consulta) {
            return $resumen;
        }
        while ($fila = $consulta->fetch_assoc()) {
            if (isset($resumen[$clave][$fila['tipo']])) {
                $resumen[$clave][$fila['tipo']] = (int)$fila['n'];
            }
        }
    }

    $dias = $conn->query(
        "SELECT dia,
                SUM(tipo = 'visita') AS visitas,
                SUM(tipo = 'qr') AS qr
         FROM visitas
         WHERE dia >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
         GROUP BY dia
         ORDER BY dia DESC"
    );
    if (!$dias) {
        return $resumen;
    }
    while ($fila = $dias->fetch_assoc()) {
        $resumen['dias'][] = [
            'dia' => (string)$fila['dia'],
            'visitas' => (int)$fila['visitas'],
            'qr' => (int)$fila['qr'],
        ];
    }

    $resumen['listo'] = true;
    return $resumen;
}
