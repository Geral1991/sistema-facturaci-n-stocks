<?php
session_start();
require_once "../conexion/conexion.php";
require_once "../conexion/funciones.php";
validarSesion("../index.php");

$mensaje = $_SESSION['mensaje'] ?? '';
$tipoMensaje = $_SESSION['tipoMensaje'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['tipoMensaje']);

$token = csrf();

$proveedores = mysqli_query($conexion, "SELECT idproveedor, nomproveedor FROM Proveedores ORDER BY nomproveedor");
$categorias  = mysqli_query($conexion, "SELECT idcategoria, nomcategoria FROM Categorias ORDER BY nomcategoria");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nuevo Producto - Sistema de Facturación</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: Arial, Helvetica, sans-serif; background-color: #eef1f5; margin: 0; padding: 30px 15px; }
    .contenedor { max-width: 500px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h1 { color: #2c3e50; font-size: 20px; margin-top: 0; }
    a.volver { display: inline-block; margin-bottom: 15px; color: #2980b9; text-decoration: none; font-size: 13px; }
    label { display: block; font-weight: bold; color: #34495e; font-size: 13px; margin-bottom: 4px; }
    input[type="text"], input[type="number"], select {
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
    <a class="volver" href="productos.php">&larr; Volver al listado</a>
    <h1>Nuevo Producto</h1>

    <?php if ($mensaje !== ''): ?>
        <div class="mensaje <?php echo escapar($tipoMensaje); ?>"><?php echo escapar($mensaje); ?></div>
    <?php endif; ?>

    <form action="guardar_producto.php" method="POST">
        <label for="idproducto">Código</label>
        <input type="text" id="idproducto" name="idproducto" maxlength="10" required>

        <label for="nomproducto">Nombre del producto</label>
        <input type="text" id="nomproducto" name="nomproducto" maxlength="128" required>

        <label for="unimed">Unidad de medida</label>
        <input type="text" id="unimed" name="unimed" maxlength="15" placeholder="Ej: UND, BOL, BOT, KG">

        <label for="idproveedor">Proveedor</label>
        <select id="idproveedor" name="idproveedor" required>
            <option value="">-- Seleccione --</option>
            <?php while ($p = mysqli_fetch_assoc($proveedores)): ?>
                <option value="<?php echo escapar($p['idproveedor']); ?>"><?php echo escapar($p['nomproveedor']); ?></option>
            <?php endwhile; ?>
        </select>

        <label for="idcategoria">Categoría</label>
        <select id="idcategoria" name="idcategoria" required>
            <option value="">-- Seleccione --</option>
            <?php while ($c = mysqli_fetch_assoc($categorias)): ?>
                <option value="<?php echo escapar($c['idcategoria']); ?>"><?php echo escapar($c['nomcategoria']); ?></option>
            <?php endwhile; ?>
        </select>

        <label for="stock">Stock inicial</label>
        <input type="number" id="stock" name="stock" min="0" value="0" required>

        <label for="cosuni">Costo unitario (S/)</label>
        <input type="number" id="cosuni" name="cosuni" min="0" step="0.0001" value="0.0000" required>

        <label for="preuni">Precio de venta unitario (S/)</label>
        <input type="number" id="preuni" name="preuni" min="0" step="0.0001" value="0.0000" required>

        <label for="estado">Estado</label>
        <select id="estado" name="estado" required>
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <input type="hidden" name="csrf" value="<?php echo escapar($token); ?>">

        <button type="submit" class="btn btn-verde">Guardar</button>
        <a href="productos.php"><button type="button" class="btn btn-rojo">Cancelar</button></a>
    </form>
</div>
</body>
</html>