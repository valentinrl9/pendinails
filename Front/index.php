<?php
include("../config/db.php");

function normalizar_v_js($texto) {
    $texto = mb_strtolower($texto, 'UTF-8');
    $buscar = array('á', 'é', 'í', 'ó', 'ú', 'ñ', ' ');
    $reemplazar = array('a', 'e', 'i', 'o', 'u', 'n', '');
    $texto = str_replace($buscar, $reemplazar, $texto);
    return preg_replace('/[^a-z0-9]/', '', $texto);
}

function imagen_web($ruta) {
    if (!$ruta) return $ruta;
    $webp = preg_replace('/\.(png|jpe?g)$/i', '.webp', $ruta);
    if (is_file(__DIR__ . '/' . $webp)) return $webp;
    return $ruta;
}

$whatsapp = '';
$configPath = __DIR__ . '/../private/config.php';
if (is_file($configPath)) {
    require_once $configPath;
    if (defined('WHATSAPP_NUMBER')) {
        $whatsapp = preg_replace('/\D+/', '', (string) WHATSAPP_NUMBER);
        if (strlen($whatsapp) === 9) {
            $whatsapp = '34' . $whatsapp;
        }
    }
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$base = $scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$ogImage = $base . '/' . imagen_web('img/hero_pendinails.png');

$productos = [];
$categorias = [];
if (isset($conn) && $conn) {
    $catRes = $conn->query("SELECT DISTINCT c.nombre FROM categorias c INNER JOIN productos p ON c.id_categoria = p.categoria ORDER BY c.nombre ASC");
    if ($catRes) {
        while ($row = $catRes->fetch_assoc()) {
            $categorias[] = $row['nombre'];
        }
    }
    $pRes = $conn->query("SELECT p.*, c.nombre AS nombre_cat FROM productos p LEFT JOIN categorias c ON p.categoria = c.id_categoria ORDER BY p.id_producto DESC");
    if ($pRes) {
        while ($row = $pRes->fetch_assoc()) {
            $productos[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PendiNails · Joyas artesanales únicas</title>
    <meta name="description" content="Joyas únicas elaboradas a mano con uñas sintéticas. Pendientes exclusivos de PendiNails, atelier en El Ejido, Almería.">
    <link rel="canonical" href="<?= htmlspecialchars($base) ?>/index.php">
    <meta property="og:type" content="website">
    <meta property="og:title" content="PendiNails · Joyas artesanales únicas">
    <meta property="og:description" content="Piezas exclusivas hechas a mano. Encarga la tuya desde el catálogo.">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($base) ?>/index.php">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon-16.png">
    <link rel="shortcut icon" href="img/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="img/icon-180.png">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#090808">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="PendiNails">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Outfit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="stylos.css">
</head>
<body>
    <header class="nav">
        <a class="brand" href="#inicio">
            <img src="img/icon-192.png" alt="PendiNails">
            <span>PendiNails</span>
        </a>
        <button class="menu-btn" type="button" aria-label="Abrir menú">☰</button>
        <ul class="nav-links">
            <li><a href="#inicio">Inicio</a></li>
            <li><a href="#colecciones">Colecciones</a></li>
            <li><a href="#contacto">Contacto</a></li>
        </ul>
    </header>

    <section class="hero" id="inicio">
        <div class="hero-inner">
            <div class="hero-copy">
                <p class="eyebrow">Joyería artesanal</p>
                <h1>Piezas<br><em>únicas</em></h1>
                <p>Joyas elaboradas con uñas sintéticas, transformadas a mano en pendientes exclusivos.</p>
                <a class="btn-gold" href="#colecciones">Ver catálogo</a>
            </div>
            <div class="hero-photo">
                <img src="<?= htmlspecialchars(imagen_web('img/modeloPendiNails.png')) ?>" alt="Modelo luciendo un pendiente PendiNails" fetchpriority="high">
            </div>
        </div>
    </section>

    <section class="intro">
        <p class="eyebrow">La firma</p>
        <h2>Hecho a mano, para lucir</h2>
        <div class="gold-line"></div>
        <p>Cada pieza nace como una uña y se convierte en joya. Pocas unidades, acabado en oro y formas que no se repiten.</p>
    </section>

    <section class="catalog" id="colecciones">
        <div class="intro" style="padding-top: 0;">
            <h2>Nuestras colecciones</h2>
            <div class="gold-line"></div>
        </div>
        <div class="toolbar">
            <div class="filters">
                <button type="button" class="is-active" data-filter="todos">Todas</button>
                <?php foreach ($categorias as $cat): ?>
                    <button type="button" data-filter="<?= htmlspecialchars(normalizar_v_js($cat)) ?>"><?= htmlspecialchars($cat) ?></button>
                <?php endforeach; ?>
            </div>
            <input type="search" id="buscarNombre" placeholder="Buscar por nombre" autocomplete="off">
        </div>
        <div class="grid" id="catalogoGrid">
            <?php foreach ($productos as $p): ?>
                <article class="card"
                    data-categoria="<?= htmlspecialchars(normalizar_v_js($p['nombre_cat'] ?? 'sincategoria')) ?>"
                    data-nombre="<?= htmlspecialchars(mb_strtolower(trim($p['nombre']), 'UTF-8')) ?>">
                    <img src="<?= htmlspecialchars(imagen_web($p['imagen_url'])) ?>"
                        class="pendinail-img"
                        loading="lazy"
                        alt="<?= htmlspecialchars($p['nombre']) ?>">
                    <small><?= htmlspecialchars($p['nombre_cat'] ?? 'Colección') ?></small>
                    <h3><?= htmlspecialchars($p['nombre']) ?></h3>
                    <p><?= htmlspecialchars($p['descripcion']) ?></p>
                    <div class="price"><?= number_format((float)$p['precio'], 2, ',', '.') ?> €</div>
                    <button type="button" class="btn-gold btn-encargar" data-pieza="<?= htmlspecialchars($p['nombre']) ?>">Encargar esta pieza</button>
                </article>
            <?php endforeach; ?>
            <p class="no-results" id="noResults">No se encontraron piezas coincidentes.</p>
        </div>
    </section>

    <section class="contact" id="contacto">
        <div class="contact-wrap">
            <p class="eyebrow">Atelier</p>
            <h2>Contacto</h2>
            <div class="gold-line"></div>
            <p>¿Tienes alguna duda o quieres un diseño personalizado? Escríbenos a <a href="mailto:pendinails@gmail.com" style="color: var(--gold-soft);">pendinails@gmail.com</a> o usa el formulario.</p>

            <form id="formContacto" action="enviar_contacto.php" method="POST">
                <label class="hp" for="website">Web</label>
                <input class="hp" type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                <input type="text" name="nombre" id="cNombre" placeholder="Nombre completo" required>
                <input type="email" name="email" id="cEmail" placeholder="Correo electrónico" required>
                <textarea name="mensaje" id="cMensaje" placeholder="¿En qué podemos ayudarte?" required></textarea>
                <label class="wa-opt">
                    <input type="checkbox" id="enviarWhatsapp" name="enviar_whatsapp" value="1">
                    También enviar por WhatsApp (si lo tienes instalado)
                </label>
                <button type="submit" class="btn-gold" id="btnEnviar">Enviar mensaje</button>
            </form>
            <div id="mensajeExito" class="form-msg">Gracias. Hemos recibido tu mensaje y te responderemos lo antes posible.</div>
            <div id="mensajeError" class="form-msg error">No se ha podido enviar. Escríbenos a pendinails@gmail.com.</div>
        </div>
    </section>

    <footer>
        <p>© <?= date('Y') ?> PendiNails · pendinails@gmail.com · El Ejido, Almería</p>
        <div class="footer-links">
            <a href="legal/aviso-legal.php">Aviso legal</a>
            <a href="legal/privacidad.php">Privacidad</a>
            <a href="legal/cookies.php">Cookies</a>
            <a href="legal/condiciones.php">Condiciones</a>
        </div>
    </footer>

    <div class="lightbox" id="lightbox" hidden>
        <img id="lightboxImage" alt="Pieza PendiNails ampliada">
    </div>

    <div class="cookie-banner" id="cookieBanner">
        <span>Usamos cookies técnicas y, si lo aceptas, analíticas de Google para mejorar la web. <a href="legal/cookies.php">Más información</a>.</span>
        <div class="cookie-actions">
            <button type="button" class="btn-gold" id="cookieReject">Rechazar</button>
            <button type="button" class="btn-gold" id="cookieAccept">Aceptar</button>
        </div>
    </div>

    <?php if ($whatsapp): ?>
    <a class="whatsapp-fab" href="https://wa.me/<?= htmlspecialchars($whatsapp) ?>?text=<?= rawurlencode('Hola, me interesa una pieza de PendiNails.') ?>" target="_blank" rel="noopener" aria-label="Escribir por WhatsApp">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 3.5A11 11 0 0 0 2.1 17.7L1 23l5.5-1.1A11 11 0 0 0 21 12a10.9 10.9 0 0 0-.5-8.5zM12 20.2a8.2 8.2 0 0 1-4.2-1.1l-.3-.2-3.2.6.6-3.1-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.4-.7-1.6-.8s-.4-.1-.5.1-.6.8-.7.9-.3.2-.5.1a6.7 6.7 0 0 1-2-1.2 7.4 7.4 0 0 1-1.4-1.7c-.1-.2 0-.4.1-.5l.4-.4.1-.3a1.4 1.4 0 0 0 0-.5c0-.1-.5-1.3-.7-1.8s-.4-.4-.5-.4h-.4a.8.8 0 0 0-.6.3 2.5 2.5 0 0 0-.8 1.9 4.4 4.4 0 0 0 .9 2.3 10 10 0 0 0 3.8 3.4 12.7 12.7 0 0 0 1.3.5 3.1 3.1 0 0 0 1.4.1 2.4 2.4 0 0 0 1.6-1.1 2 2 0 0 0 .1-1.1c-.1-.1-.2-.1-.4-.2z"/></svg>
    </a>
    <?php endif; ?>

    <script>
        window.PENDINAILS_WA = <?= json_encode($whatsapp) ?>;
    </script>
    <script src="scriptv2.js?v=4"></script>
</body>
</html>
