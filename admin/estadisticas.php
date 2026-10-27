<?php
require_once __DIR__ . '/../config/security.php';
pendinails_bootstrap();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/visitas.php';

$resumen = pendinails_resumen_visitas($conn);
$dias = $resumen['dias'];
$maximo = 1;
foreach ($dias as $dia) {
    $maximo = max($maximo, $dia['visitas'], $dia['qr']);
}

function pendinails_numero(int $valor): string
{
    return number_format($valor, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Visitas y QR | PendiNails</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <link rel="stylesheet" href="estiloAdmin.css">
  <style>
    .cifras { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }
    .cifra { background: #fff; border-radius: 8px; padding: 14px; }
    .cifra strong { display: block; font-size: 2rem; line-height: 1.1; }
    .cifra span { color: #555; }
    .barra { height: 8px; background: #f8d7da; border-radius: 99px; overflow: hidden; margin-top: 6px; }
    .barra i { display: block; height: 100%; background: #c2185b; }
    .aviso { background: #fff; border-radius: 8px; padding: 14px; }
    code { font-size: 1rem; }
  </style>
</head>
<body>
  <p><a href="admin.php">← Volver al panel</a></p>
  <div class="card">
    <div class="card-header">Visitas de la web y escaneos del cartel</div>
    <div class="card-body">
      <p class="aviso">
        El QR del cartel tiene que apuntar a <code>https://pendinails.com/q</code>.
        Cada vez que alguien lo abre, suma un escaneo y después entra en la web.
        El contador no guarda nombres ni direcciones IP, y una misma persona puede sumar más de una vez si vuelve a escanear.
      </p>

      <?php if (!$resumen['listo']): ?>
        <p>No se ha podido leer el contador. Revisa la conexión con la base de datos.</p>
        <?php if ($resumen['error'] !== ''): ?>
          <p><?= htmlspecialchars($resumen['error']) ?></p>
        <?php endif; ?>
      <?php else: ?>
        <div class="cifras">
          <div class="cifra"><span>Escaneos hoy</span><strong><?= pendinails_numero($resumen['hoy']['qr']) ?></strong></div>
          <div class="cifra"><span>Escaneos, 7 días</span><strong><?= pendinails_numero($resumen['semana']['qr']) ?></strong></div>
          <div class="cifra"><span>Escaneos, 30 días</span><strong><?= pendinails_numero($resumen['mes']['qr']) ?></strong></div>
          <div class="cifra"><span>Visitas hoy</span><strong><?= pendinails_numero($resumen['hoy']['visita']) ?></strong></div>
          <div class="cifra"><span>Visitas, 7 días</span><strong><?= pendinails_numero($resumen['semana']['visita']) ?></strong></div>
          <div class="cifra"><span>Visitas, 30 días</span><strong><?= pendinails_numero($resumen['mes']['visita']) ?></strong></div>
        </div>

        <h3>Día a día</h3>
        <?php if (!$dias): ?>
          <p>Todavía no hay datos. El recuento empieza cuando alguien entra en la web ya actualizada.</p>
        <?php else: ?>
          <table class="table">
            <thead>
              <tr><th>Día</th><th>Escaneos del cartel</th><th>Visitas</th></tr>
            </thead>
            <tbody>
              <?php foreach ($dias as $dia): ?>
                <?php
                  $fecha = DateTime::createFromFormat('Y-m-d', $dia['dia']);
                  $textoFecha = $fecha ? $fecha->format('d/m/Y') : $dia['dia'];
                  $anchoQr = (int)round(($dia['qr'] / $maximo) * 100);
                  $anchoVisitas = (int)round(($dia['visitas'] / $maximo) * 100);
                ?>
                <tr>
                  <td><?= htmlspecialchars($textoFecha) ?></td>
                  <td>
                    <?= pendinails_numero($dia['qr']) ?>
                    <div class="barra"><i style="width: <?= $anchoQr ?>%"></i></div>
                  </td>
                  <td>
                    <?= pendinails_numero($dia['visitas']) ?>
                    <div class="barra"><i style="width: <?= $anchoVisitas ?>%"></i></div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
