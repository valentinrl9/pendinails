<?php 
// Conexión a la base de datos
include("../config/db.php"); 

// Función para normalizar textos (elimina espacios, tildes y caracteres especiales)
// Esto garantiza que "Colección Rosas" se convierta en "coleccionrosas" para el ID de búsqueda.
function normalizar_v_js($texto) {
    $texto = mb_strtolower($texto, 'UTF-8');
    $buscar = array('á', 'é', 'í', 'ó', 'ú', 'ñ', ' ');
    $reemplazar = array('a', 'e', 'i', 'o', 'u', 'n', '');
    $texto = str_replace($buscar, $reemplazar, $texto);
    return preg_replace('/[^a-z0-9]/', '', $texto);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PendiNails - Joyas únicas</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="stylos.css">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background: rgba(0,0,0,0.85); backdrop-filter: blur(5px);">
    <div class="container">
        <a class="navbar-brand" href="#">PendiNails</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="#inicio">Inicio</a>
                <a class="nav-link" href="#destacados">Productos</a>
                <a class="nav-link" href="#contacto">Contacto</a>
            </div>
        </div>
    </div>
</nav>

<a href="#inicio" class="scroll-top" id="scrollTopBtn" style="display:none; position:fixed; bottom:20px; right:20px; z-index:999;">
    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ffb6c1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="18 15 12 9 6 15" />
    </svg>
</a>



<section id="inicio" class="hero mt-5 pt-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12 col-md-5 text-center">
                <div class="hero-content">
                    <h1>PendiNails</h1>
                    <p>Transformamos lo cotidiano en arte artesanal único.</p>
                    <a href="#destacados" class="btn-elegante">Ver catálogo</a>
                </div>
            </div>
            <div class="col-12 col-md-7 text-center">
                <img src="img/modeloPendiNails.png" alt="Pendientes" class="hero-img img-fluid">
            </div>
        </div>
    </div>
</section>



<section id="destacados" class="container my-5">
    <h2 class="text-center mb-5">Nuestras Colecciones</h2>

    <div class="card shadow-sm border-0 mb-5 bg-white p-4">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-md-2">
                <button id="btnTodos" class="btn btn-dark w-100 h-100 py-3">Ver Todos</button>
            </div>
            <div class="col-12 col-md-5">
                <div class="form-floating">
                    <input type="text" class="form-control" id="buscarNombre" placeholder="Buscar...">
                    <label>Buscar por nombre</label>
                </div>
            </div>
            <div class="col-12 col-md-5">
                <div class="form-floating">
                    <select class="form-select" id="filtroCategoria">
                        <option value="todos" selected>Todas las categorías</option>
                        <?php
                        // Obtenemos los nombres de las categorías reales vinculadas a productos
                        $sqlCat = "SELECT DISTINCT c.nombre 
                                   FROM categorias c 
                                   INNER JOIN productos p ON c.id_categoria = p.categoria 
                                   ORDER BY c.nombre ASC";
                        $catRes = $conn->query($sqlCat);
                        while($cRow = $catRes->fetch_assoc()) {
                            $nombreReal = $cRow['nombre'];
                            $valJS = normalizar_v_js($nombreReal); // ej: "coleccionrosas"
                            echo '<option value="'.$valJS.'">'.ucfirst($nombreReal).'</option>';
                        }
                        ?>
                    </select>
                    <label>Filtrar por colección</label>
                </div>
            </div>
        </div>
    </div>

    <div class="swiper" id="pendinailsSwiper" style="padding-bottom: 50px; transition: opacity 0.3s ease;">
        <div class="swiper-wrapper" id="swiperWrapper">
            <?php
            // Traemos productos con el nombre de su categoría
            $sqlProd = "SELECT p.*, c.nombre AS nombre_cat 
                        FROM productos p 
                        LEFT JOIN categorias c ON p.categoria = c.id_categoria";
            $pRes = $conn->query($sqlProd);
            while($row = $pRes->fetch_assoc()) {
                $catData = normalizar_v_js($row['nombre_cat'] ?? 'sin-categoria');
                $nomData = mb_strtolower(trim($row['nombre']), 'UTF-8');
                ?>
                <div class="swiper-slide" data-categoria="<?= $catData ?>" data-nombre="<?= $nomData ?>">
                    <div class="producto-slide-content p-3 border rounded shadow-sm bg-white text-center h-100">
                        <img src="<?= $row['imagen_url'] ?>" 
                            class="img-fluid mb-3 rounded pendinail-img"
                            style="height:220px; object-fit:cover;" 
                            alt="<?= $row['nombre'] ?>">
                        <h5 class="fw-bold"><?= $row['nombre'] ?></h5>
                        <p class="text-muted small"><?= $row['descripcion'] ?></p>
                        <p class="text-dark fw-bold"><?= number_format($row['precio'], 2) ?> €</p>
                        <!-- Boton de compra comentado hasta que se haga la funcionalidad online -->
                        <!-- <a href="#comprar-<?= $row['id_producto'] ?>" class="btn btn-dark btn-sm px-4">Comprar</a> -->
                    </div>
                </div>
            <?php } ?>
        </div>      
        <div class="swiper-button-prev text-dark"></div>
        <div class="swiper-button-next text-dark"></div>
        <div class="swiper-pagination"></div>
    </div>

    <div id="noResults" class="text-center d-none my-5 p-5 border rounded bg-light">
        <h4 class="text-muted">No se encontraron productos coincidentes</h4>
        <p>Prueba con otros filtros o pulsa "Ver Todos".</p>
    </div>
</section>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="searchToast" class="toast align-items-center text-white bg-dark border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">Sin resultados para esta búsqueda.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<section id="contacto" class="contacto-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="contacto-wrapper text-center">
                    <h2 class="display-4 mb-3">Contacto</h2>
                    <p class="mb-5">¿Tienes alguna duda o quieres un diseño personalizado? Escríbenos.</p>
                    
                    <form id="formContacto" action="enviar_contacto.php" method="POST" class="text-start">
                        <div class="form-floating mb-3">
                            <input type="text" name="nombre" class="form-control" id="cNombre" placeholder="Tu nombre" required>
                            <label for="cNombre">Nombre completo</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control" id="cEmail" placeholder="nombre@ejemplo.com" required>
                            <label for="cEmail">Correo electrónico</label>
                        </div>
                        <div class="form-floating mb-4">
                            <textarea name="mensaje" class="form-control" id="cMensaje" placeholder="Tu mensaje" style="height: 150px" required></textarea>
                            <label for="cMensaje">¿En qué podemos ayudarte?</label>
                        </div>
                        <button type="submit" class="btn-elegante w-100 py-3">Enviar Mensaje</button>
                    </form>

                    <div id="mensajeExito" class="mt-4 p-3 border rounded d-none" style="background: rgba(255,255,255,0.9); border-color: #c2185b !important;">
                        <h5 class="text-dark mb-0">¡Gracias! Hemos recibido tu mensaje.</h5>
                        <small class="text-muted">Te responderemos lo antes posible.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
        <p class="mb-1">© 2025 PendiNails · pendinails@gmail.com</p>

        <div class="d-flex justify-content-center gap-3 small mt-2">
            <a href="legal/aviso-legal.php" target="_blank" class="text-white-50 text-decoration-none">Aviso Legal</a>
            <span class="text-white-50">|</span>
            <a href="legal/privacidad.php" target="_blank" class="text-white-50 text-decoration-none">Política de Privacidad</a>
            <span class="text-white-50">|</span>
            <a href="legal/cookies.php" target="_blank" class="text-white-50 text-decoration-none">Política de Cookies</a>
            <span class="text-white-50">|</span>
            <a href="legal/condiciones.php" target="_blank" class="text-white-50 text-decoration-none">Condiciones de Venta</a>
        </div>
    </div>
</footer>

<!-- Modal para ver imagen ampliada -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content bg-dark">
      <div class="modal-body p-0">
        <img id="modalImage"
            src=""
            class="img-fluid"
            style=" object-fit: contain; border: 4px solid #d4af37; border-radius: 10px;">
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="scriptv2.js?v=1"></script>


</body>
</html>