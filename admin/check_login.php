<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login Admin</title>
</head>
<body>
  <h2>Acceso administrador</h2>
  <form action="check_login.php" method="post">
    <label for="usuario">Usuario:</label>
    <input type="text" name="usuario" required>
    <br><br>
    <label for="password">Contraseña:</label>
    <input type="password" name="password" required>
    <br><br>
    <button type="submit">Entrar</button>
  </form>
</body>
</html>
