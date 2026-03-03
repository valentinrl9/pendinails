<?php
session_start();
include("../config/db.php"); // conexión a la base de datos

// ---------- CRUD CATEGORÍAS ----------
if (isset($_POST['accion']) && $_POST['accion'] === 'crear_categoria') {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $descripcion = $conn->real_escape_string($_POST['descripcion']);

    $sql = "INSERT INTO categorias (nombre, descripcion, fecha_creacion, fecha_actualizacion)
            VALUES ('$nombre', '$descripcion', NOW(), NOW())";

    if ($conn->query($sql) === TRUE) {
        header("Location: admin.php");
        exit;
    } else {
        die("Error al insertar categoría: " . $conn->error);
    }
}

if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar_categoria') {
    $id = (int)$_POST['id_categoria'];
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $descripcion = $conn->real_escape_string($_POST['descripcion']);

    $sql = "UPDATE categorias 
            SET nombre='$nombre', descripcion='$descripcion', fecha_actualizacion=NOW()
            WHERE id_categoria=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: admin.php");
        exit;
    } else {
        die("Error al actualizar categoría: " . $conn->error);
    }
}

if (isset($_GET['eliminar_categoria'])) {
    $id = (int)$_GET['eliminar_categoria'];
    $sql = "DELETE FROM categorias WHERE id_categoria=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: admin.php");
        exit;
    } else {
        die("Error al eliminar categoría: " . $conn->error);
    }
}

// ---------- CRUD PRODUCTOS ----------
if (isset($_POST['accion']) && $_POST['accion'] === 'crear_producto') {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $descripcion = $conn->real_escape_string($_POST['descripcion']);
    $precio = (float)$_POST['precio'];
    $stock = (int)$_POST['stock'];
    $categoria = (int)$_POST['categoria'];
    $destacado = isset($_POST['destacado']) ? 1 : 0;

    // Procesar imagen subida con validación y renombrado
    $imagen_url = "";
    if (isset($_FILES['imagen_url']) && $_FILES['imagen_url']['error'] === UPLOAD_ERR_OK) {
        $permitidos = ['image/jpeg', 'image/png'];
        if (!in_array($_FILES['imagen_url']['type'], $permitidos)) {
            die("Error: solo se permiten imágenes JPG o PNG");
        }

        $extension = pathinfo($_FILES['imagen_url']['name'], PATHINFO_EXTENSION);
        $nombreUnico = uniqid("prod_", true) . "." . $extension;
        $rutaDestino = "../Front/img/" . $nombreUnico;

        if (move_uploaded_file($_FILES['imagen_url']['tmp_name'], $rutaDestino)) {
            $imagen_url = "img/" . $nombreUnico; // guardamos con prefijo img/
        }
    }

    $sql = "INSERT INTO productos (nombre, descripcion, precio, stock, categoria, imagen_url, destacado, fecha_creacion, fecha_actualizacion)
            VALUES ('$nombre','$descripcion',$precio,$stock,$categoria,'$imagen_url',$destacado,NOW(),NOW())";

    if ($conn->query($sql) === TRUE) {
        header("Location: admin.php");
        exit;
    } else {
        die("Error al insertar producto: " . $conn->error);
    }
}

if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar_producto') {
    $id = (int)$_POST['id_producto'];
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $descripcion = $conn->real_escape_string($_POST['descripcion']);
    $precio = (float)$_POST['precio'];
    $stock = (int)$_POST['stock'];
    $categoria = (int)$_POST['categoria'];
    $destacado = isset($_POST['destacado']) ? 1 : 0;

    // Procesar imagen subida con validación y renombrado
    $imagen_url = $_POST['imagen_actual']; // ruta actual (ej: img/archivo.png)
    if (isset($_FILES['imagen_url']) && $_FILES['imagen_url']['error'] === UPLOAD_ERR_OK) {
        $permitidos = ['image/jpeg', 'image/png'];
        if (!in_array($_FILES['imagen_url']['type'], $permitidos)) {
            die("Error: solo se permiten imágenes JPG o PNG");
        }

        $extension = pathinfo($_FILES['imagen_url']['name'], PATHINFO_EXTENSION);
        $nombreUnico = uniqid("prod_", true) . "." . $extension;
        $rutaDestino = "../Front/img/" . $nombreUnico;

        if (move_uploaded_file($_FILES['imagen_url']['tmp_name'], $rutaDestino)) {
            $imagen_url = "img/" . $nombreUnico; // guardamos con prefijo img/
        }
    }

    $sql = "UPDATE productos 
            SET nombre='$nombre', descripcion='$descripcion', precio=$precio, stock=$stock,
                categoria=$categoria, imagen_url='$imagen_url', destacado=$destacado, fecha_actualizacion=NOW()
            WHERE id_producto=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: admin.php");
        exit;
    } else {
        die("Error al actualizar producto: " . $conn->error);
    }
}

if (isset($_GET['eliminar_producto'])) {
    $id = (int)$_GET['eliminar_producto'];
    $sql = "DELETE FROM productos WHERE id_producto=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: admin.php");
        exit;
    } else {
        die("Error al eliminar producto: " . $conn->error);
    }
}

// ---------- LECTURA ----------
// Categorías
$categorias = $conn->query("SELECT * FROM categorias ORDER BY id_categoria ASC");

// Orden dinámico productos
$orden = "p.id_producto";
$dir   = "DESC";

if (isset($_GET['orden'])) {
    $ordenPermitido = ["p.id_producto","p.nombre","p.descripcion","p.precio","p.stock","c.nombre"];
    if (in_array($_GET['orden'], $ordenPermitido)) {
        $orden = $_GET['orden'];
    }
}
if (isset($_GET['dir']) && in_array(strtoupper($_GET['dir']), ["ASC","DESC"])) {
    $dir = strtoupper($_GET['dir']);
}

$ordenActual = $orden;
$dirActual   = $dir;

function linkOrden($columna, $texto, $ordenActual, $dirActual) {
    $nextDir = "ASC";
    if ($ordenActual === $columna) {
        $nextDir = ($dirActual === "ASC") ? "DESC" : "ASC";
    }
    return "<a href=\"?orden=$columna&dir=$nextDir\">$texto</a>";
}

$productos = $conn->query("SELECT p.*, c.nombre AS categoria_nombre
                           FROM productos p
                           LEFT JOIN categorias c ON p.categoria = c.id_categoria
                           ORDER BY $orden $dir");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Administración | Pendinails</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Enlace al CSS personalizado -->
  <link rel="stylesheet" href="estiloAdmin.css">
</head>
<body>

  <!-- Gestión de Categorías -->
  <div class="card m-3">
    <div class="card-header">Gestión de Categorías</div>
    <div class="card-body">
      <!-- Formulario para crear nueva categoría -->
      <form method="POST">
        <input type="hidden" name="accion" value="crear_categoria">
        <table class="table categorias">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Descripción</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Nuevo</td>
              <td><input type="text" name="nombre" required></td>
              <td><input type="text" name="descripcion"></td>
              <td><button type="submit" class="btn btn-pink">➕ Crear</button></td>
            </tr>
          </tbody>
        </table>
      </form>

      <!-- Tabla de categorías existentes -->
      <table class="table categorias">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($c = $categorias->fetch_assoc()): ?>
          <tr>
            <form method="POST">
              <input type="hidden" name="accion" value="actualizar_categoria">
              <input type="hidden" name="id_categoria" value="<?= $c['id_categoria'] ?>">
              <td><?= $c['id_categoria'] ?></td>
              <td><input type="text" name="nombre" value="<?= htmlspecialchars($c['nombre']) ?>"></td>
              <td><input type="text" name="descripcion" value="<?= htmlspecialchars($c['descripcion']) ?>"></td>
              <td>
                <button type="submit" class="btn btn-pink">💾</button>
                <a href="?eliminar_producto=<?= $p['id_producto'] ?>" class="btn btn-pink" onclick="return confirm('¿Eliminar producto?')">🗑️</a>
              </td>
            </form>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Gestión de Productos -->
  <div class="card m-3">
    <div class="card-header">Gestión de Productos</div>
    <div class="card-body">
      <!-- Formulario para crear nuevo producto -->
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="crear_producto">
        <table class="table productos">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Descripción</th>
              <th>Precio</th>
              <th>Stock</th>
              <th>Categoría</th>
              <th>Imagen</th>
              <th>Destacado</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Nuevo</td>
              <td><input type="text" name="nombre" required></td>
              <td><input type="text" name="descripcion"></td>
              <td><input type="number" step="0.01" name="precio" required></td>
              <td><input type="number" name="stock" required></td>
              <td>
                <select name="categoria">
                  <?php
                  $cats = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC");
                  while ($cat = $cats->fetch_assoc()):
                  ?>
                    <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                  <?php endwhile; ?>
                </select>
              </td>
              <td><input type="file" name="imagen_url" id="imagen_nueva" accept="image/png,image/jpeg"></td>
              <td><input type="checkbox" name="destacado"></td>
              <td><button type="submit" class="btn btn-pink">➕ Crear</button></td>
            </tr>
          </tbody>
        </table>
      </form>

      <!-- Tabla de productos existentes -->
      <table class="table productos">
        <thead>
          <tr>
            <th><?= linkOrden("p.id_producto","ID",$ordenActual,$dirActual) ?></th>
            <th><?= linkOrden("p.nombre","Nombre",$ordenActual,$dirActual) ?></th>
            <th><?= linkOrden("p.descripcion","Descripción",$ordenActual,$dirActual) ?></th>
            <th><?= linkOrden("p.precio","Precio",$ordenActual,$dirActual) ?></th>
            <th><?= linkOrden("p.stock","Stock",$ordenActual,$dirActual) ?></th>
            <th><?= linkOrden("c.nombre","Categoría",$ordenActual,$dirActual) ?></th>
            <th>Imagen</th>
            <th>Destacado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($p = $productos->fetch_assoc()): ?>
          <tr>
            <form method="POST" enctype="multipart/form-data">
              <input type="hidden" name="accion" value="actualizar_producto">
              <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
              <td><?= $p['id_producto'] ?></td>
              <td><input type="text" name="nombre" value="<?= htmlspecialchars($p['nombre']) ?>"></td>
              <td><input type="text" name="descripcion" value="<?= htmlspecialchars($p['descripcion']) ?>"></td>
              <td><input type="number" step="0.01" name="precio" value="<?= $p['precio'] ?>"></td>
              <td><input type="number" name="stock" value="<?= $p['stock'] ?>"></td>
              <td>
                <select name="categoria">
                  <?php
                  $cats = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC");
                  while ($cat = $cats->fetch_assoc()):
                    $selected = ($cat['id_categoria'] == $p['categoria']) ? "selected" : "";
                  ?>
                    <option value="<?= $cat['id_categoria'] ?>" <?= $selected ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                  <?php endwhile; ?>
                </select>
              </td>
              <td>
                <label style="cursor:pointer;">
                  <?php if ($p['imagen_url']): ?>
                    <img src="../Front/<?= $p['imagen_url'] ?>" 
                        alt="Imagen producto" 
                        style="max-width:60px; border:1px solid #c2185b; border-radius:4px;">
                  <?php else: ?>
                    <span style="color:#c2185b;">📷 Subir imagen</span>
                  <?php endif; ?>
                  <input type="file" name="imagen_url" class="imagen_existente" style="display:none;" accept="image/png,image/jpeg">
                </label>
                <input type="hidden" name="imagen_actual" value="<?= $p['imagen_url'] ?>">
              </td>
              <td><input type="checkbox" name="destacado" <?= $p['destacado'] ? "checked" : "" ?>></td>
              <td>
                <button type="submit" class="btn btn-pink">💾</button>
                <a href="?eliminar_producto=<?= $p['id_producto'] ?>" class="btn btn-pink" onclick="return confirm('¿Eliminar producto?')">🗑️</a>
              </td>
            </form>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Toast de error -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toastError" class="toast align-items-center text-bg-danger border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body">
          Solo se permiten imágenes JPG o PNG
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS (necesario para toasts) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap