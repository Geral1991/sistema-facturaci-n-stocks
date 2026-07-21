<?php
session_start();
require_once "../conexion/conexion.php";
require_once "../conexion/funciones.php";
validarSesion("../index.php");

$id = trim($_GET['id'] ?? '');
if ($id === '') {
    header("Location: usuarios.php");
    exit;
}

$sql = "SELECT idusuario, nomusuario, nombres, apellidos, email, estado FROM Usuarios WHERE idusuario = ? LIMIT 1";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$registro = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

if (!$registro) {
    $_SESSION['mensaje'] = "El usuario solicitado no existe.";
    $_SESSION['tipoMensaje'] = "error";
    header("Location: usuarios.php");
    exit;
}

$token = csrf();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Modificar Usuario - Sistema de Facturación</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: Arial, Helvetica, sans-serif; background-color: #eef1f5; margin: 0; padding: 30px 15px; }
    .contenedor { max-width: 500px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h1 { color: #2c3e50; font-size: 20px; margin-top: 0; }
    a.volver { display: inline-block; margin-bottom: 15px; color: #2980b9; text-decoration: none; font-size: 13px; }
    label { display: block; font-weight: bold; color: #34495e; font-size: 13px; margin-bottom: 4px; }
    input[type="text"], input[type="email"], input[type="password"], select {
        width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;
    }
    input[readonly] { background-color: #f2f2f2; color: #555555; }
    .btn { padding: 8px 14px; border: none; border-radius: 4px; font-size: 13px; font-weight: bold; cursor: pointer; color: #ffffff; text-decoration: none; display: inline-block; }
    .btn-amarillo { background-color: #f39c12; color: #333333; }
    .btn-amarillo:hover { background-color: #d68910; }
    .btn-rojo { background-color: #e74c3c; }
    .btn-rojo:hover { background-color: #c0392b; }
    .texto-ayuda { color: #7f8c8d; font-size: 12px; margin-top: -10px; margin-bottom: 15px; }
</style>
</head>
<body>
<div class="contenedor">
    <a class="volver" href="usuarios.php">&larr; Volver al listado</a>
    <h1>Modificar Usuario</h1>

    <form action="actualizar_usuario.php" method="POST" autocomplete="off">
        <label for="idusuario">Código</label>
        <input type="text" id="idusuario" value="<?php echo escapar($registro['idusuario']); ?>" readonly>
        <input type="hidden" name="idusuario" value="<?php echo escapar($registro['idusuario']); ?>">

        <label for="nomusuario">Usuario (login)</label>
        <input type="text" id="nomusuario" name="nomusuario" maxlength="15" required value="<?php echo escapar($registro['nomusuario']); ?>">

        <label for="nombres">Nombres</label>
        <input type="text" id="nombres" name="nombres" maxlength="64" required value="<?php echo escapar($registro['nombres']); ?>">

        <label for="apellidos">Apellidos</label>
        <input type="text" id="apellidos" name="apellidos" maxlength="64" required value="<?php echo escapar($registro['apellidos']); ?>">

        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" maxlength="64" value="<?php echo escapar($registro['email']); ?>">

        <label for="password">Nueva contraseña</label>
        <input type="password" id="password" name="password" maxlength="255">
        <p class="texto-ayuda">Déjalo en blanco si no deseas cambiar la contraseña actual.</p>

        <label for="estado">Estado</label>
        <select id="estado" name="estado" required>
            <option value="1" <?php echo ($registro['estado'] === '1') ? 'selected' : ''; ?>>Activo</option>
            <option value="0" <?php echo ($registro['estado'] === '0') ? 'selected' : ''; ?>>Inactivo</option>
        </select>

        <input type="hidden" name="csrf" value="<?php echo escapar($token); ?>">

        <button type="submit" class="btn btn-amarillo">Actualizar</button>
        <a href="usuarios.php"><button type="button" class="btn btn-rojo">Cancelar</button></a>
    </form>
</div>
</body>
</html>