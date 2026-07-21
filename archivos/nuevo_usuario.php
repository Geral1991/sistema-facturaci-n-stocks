<?php
session_start();
require_once "../conexion/funciones.php";
validarSesion("../index.php");

$mensaje = $_SESSION['mensaje'] ?? '';
$tipoMensaje = $_SESSION['tipoMensaje'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['tipoMensaje']);

$token = csrf();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nuevo Usuario - Sistema de Facturación</title>
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
    .btn { padding: 8px 14px; border: none; border-radius: 4px; font-size: 13px; font-weight: bold; cursor: pointer; color: #ffffff; text-decoration: none; display: inline-block; }
    .btn-verde { background-color: #27ae60; }
    .btn-verde:hover { background-color: #219150; }
    .btn-rojo { background-color: #e74c3c; }
    .btn-rojo:hover { background-color: #c0392b; }
    .mensaje { padding: 12px 15px; border-radius: 4px; font-size: 14px; margin-bottom: 20px; }
    .mensaje.exito { background-color: #eafaf1; color: #196f3d; border: 1px solid #27ae60; }
    .mensaje.error { background-color: #fdecea; color: #c0392b; border: 1px solid #e74c3c; }
</style>
</head>
<body>
<div class="contenedor">
    <a class="volver" href="usuarios.php">&larr; Volver al listado</a>
    <h1>Nuevo Usuario</h1>

    <?php if ($mensaje !== ''): ?>
        <div class="mensaje <?php echo escapar($tipoMensaje); ?>"><?php echo escapar($mensaje); ?></div>
    <?php endif; ?>

    <form action="guardar_usuario.php" method="POST" autocomplete="off">
        <label for="idusuario">Código (3 caracteres)</label>
        <input type="text" id="idusuario" name="idusuario" maxlength="3" required>

        <label for="nomusuario">Usuario (login)</label>
        <input type="text" id="nomusuario" name="nomusuario" maxlength="15" required>

        <label for="nombres">Nombres</label>
        <input type="text" id="nombres" name="nombres" maxlength="64" required>

        <label for="apellidos">Apellidos</label>
        <input type="text" id="apellidos" name="apellidos" maxlength="64" required>

        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" maxlength="64">

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" maxlength="255" required>

        <label for="estado">Estado</label>
        <select id="estado" name="estado" required>
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <input type="hidden" name="csrf" value="<?php echo escapar($token); ?>">

        <button type="submit" class="btn btn-verde">Guardar</button>
        <a href="usuarios.php"><button type="button" class="btn btn-rojo">Cancelar</button></a>
    </form>
</div>
</body>
</html>