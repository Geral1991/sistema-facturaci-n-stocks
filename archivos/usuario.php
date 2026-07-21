<?php
session_start();
require_once "../conexion/conexion.php";
require_once "../conexion/funciones.php";
validarSesion("../index.php");

$mensaje = $_SESSION['mensaje'] ?? '';
$tipoMensaje = $_SESSION['tipoMensaje'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['tipoMensaje']);

$resultado = mysqli_query($conexion, "SELECT idusuario, nomusuario, apellidos, nombres, email, estado FROM Usuarios ORDER BY nombres");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Usuarios - Sistema de Facturación</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: Arial, Helvetica, sans-serif; background-color: #eef1f5; margin: 0; padding: 30px 15px; }
    .contenedor { max-width: 950px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .contenedor.angosto { max-width: 500px; }
    h1 { color: #2c3e50; font-size: 20px; margin-top: 0; }
    a.volver { display: inline-block; margin-bottom: 15px; color: #2980b9; text-decoration: none; font-size: 13px; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 15px; }
    table th, table td { border: 1px solid #ddd; padding: 8px; font-size: 13px; text-align: left; }
    table th { background-color: #f2f2f2; }
    label { display: block; font-weight: bold; color: #34495e; font-size: 13px; margin-bottom: 4px; }
    input[type="text"], input[type="email"], input[type="password"], select {
        width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;
    }
    input[readonly] { background-color: #f2f2f2; color: #555555; }
    .btn { padding: 8px 14px; border: none; border-radius: 4px; font-size: 13px; font-weight: bold; cursor: pointer; color: #ffffff; text-decoration: none; display: inline-block; }
    .btn-verde { background-color: #27ae60; }
    .btn-verde:hover { background-color: #219150; }
    .btn-amarillo { background-color: #f39c12; color: #333333; }
    .btn-amarillo:hover { background-color: #d68910; }
    .btn-rojo { background-color: #e74c3c; }
    .btn-rojo:hover { background-color: #c0392b; }
    .nuevo-btn { margin-bottom: 15px; }
    .acciones a { margin-right: 6px; }
    .mensaje { padding: 12px 15px; border-radius: 4px; font-size: 14px; margin-bottom: 20px; }
    .mensaje.exito { background-color: #eafaf1; color: #196f3d; border: 1px solid #27ae60; }
    .mensaje.error { background-color: #fdecea; color: #c0392b; border: 1px solid #e74c3c; }
    .estado-activo { color: #196f3d; font-weight: bold; }
    .estado-inactivo { color: #c0392b; font-weight: bold; }
    .texto-ayuda { color: #7f8c8d; font-size: 12px; margin-top: -10px; margin-bottom: 15px; }
</style>
</head>
<body>
<div class="contenedor">
    <a class="volver" href="../dashboard.php">&larr; Volver al dashboard</a>
    <h1>Usuarios</h1>

    <?php if ($mensaje !== ''): ?>
        <div class="mensaje <?php echo escapar($tipoMensaje); ?>"><?php echo escapar($mensaje); ?></div>
    <?php endif; ?>

    <a class="btn btn-verde nuevo-btn" href="nuevo_usuario.php">+ Nuevo Usuario</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Nombres</th>
                <th>Apellidos</th>
                <th>Correo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($resultado) === 0): ?>
            <tr><td colspan="7">No hay registros para mostrar.</td></tr>
            <?php endif; ?>
            <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
            <tr>
                <td><?php echo escapar($fila['idusuario']); ?></td>
                <td><?php echo escapar($fila['nomusuario']); ?></td>
                <td><?php echo escapar($fila['nombres']); ?></td>
                <td><?php echo escapar($fila['apellidos']); ?></td>
                <td><?php echo escapar($fila['email']); ?></td>
                <td>
                    <?php if ($fila['estado'] === '1'): ?>
                        <span class="estado-activo">Activo</span>
                    <?php else: ?>
                        <span class="estado-inactivo">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td class="acciones">
                    <a class="btn btn-amarillo" href="modificar_usuario.php?id=<?php echo urlencode($fila['idusuario']); ?>">Modificar</a>
                    <a class="btn btn-rojo" href="confirmar_eliminar.php?entidad=usuario&id=<?php echo urlencode($fila['idusuario']); ?>">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>