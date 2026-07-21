<?php
session_start();
require_once "../conexion/conexion.php";
require_once "../conexion/funciones.php";
validarSesion("../index.php");

$id = trim($_GET['id'] ?? '');
if ($id === '') {
    header("Location: productos.php");
    exit;
}

$sql = "SELECT idproducto, idproveedor, nomproducto, unimed, stock, cosuni, preuni, idcategoria, estado
        FROM Productos WHERE idproducto = ? LIMIT 1";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$registro = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

if (!$registro) {
    $_SESSION['mensaje'] = "El producto solicitado no existe.";
    $_SESSION['tipoMensaje'] = "error";
    header("Location: productos.php");
    exit;
}

$token = csrf();

$proveedores = mysqli_query($conexion, "SELECT idproveedor, nomproveedor FROM Proveedores ORDER BY nomproveedor");
$categorias  = mysqli_query($conexion, "SELECT idcategoria, nomcategoria FROM Categorias ORDER BY nomcategoria");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Modificar Producto - Sistema de Facturación</title>
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
    input[readonly] { background-color: #f2f2f2; color: #555555; }
    .btn { padding: 8px 14px; border: none; border-radius: 4px; font-size: 13px; font-weight: bold; cursor: pointer; color: #ffffff; text-decoration: none; display: inline-block; }
    .btn-amarillo { background-color: #f39c12; color: #333333; }
    .btn-amarillo:hover { background-color: #d68910; }
    .btn-rojo { background-color: #e74c3c; }
    .btn-rojo:hover { background-color: #c0392b; }
</style>
</head>
<body>
<div class="contenedor">
    <a class="volver" href="productos.php">&larr; Volver al listado</a>
    <h1>Modificar Producto</h1>

    <form action="actualizar_producto.php" method="POST">
        <label for="idproducto">Código</label>
        <input type="text" id="idproducto" value="<?php echo escapar($registro['idproducto']); ?>" readonly>
        <input type="hidden" name="idproducto" value="<?php echo escapar($registro['idproducto']); ?>">

        <label for="nomproducto">Nombre del producto</label>
        <input type="text" id="nomproducto" name="nomproducto" maxlength="128" required value="<?php echo escapar($registro['nomproducto']); ?>">

        <label for="unimed">Unidad de medida</label>
        <input type="text" id="unimed" name="unimed" maxlength="15" value="<?php echo escapar($registro['unimed']); ?>">

        <label for="idproveedor">Proveedor</label>
        <select id="idproveedor" name="idproveedor" required>
            <?php while ($p = mysqli_fetch_assoc($proveedores)): ?>
                <option value="<?php echo escapar($p['idproveedor']); ?>" <?php echo ($p['idproveedor'] === $registro['idproveedor']) ? 'selected' : ''; ?>>
                    <?php echo escapar($p['nomproveedor']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="idcategoria">Categoría</label>
        <select id="idcategoria" name="idcategoria" required>
            <?php while ($c = mysqli_fetch_assoc($categorias)): ?>
                <option value="<?php echo escapar($c['idcategoria']); ?>" <?php echo ($c['idcategoria'] === $registro['idcategoria']) ? 'selected' : ''; ?>>
                    <?php echo escapar($c['nomcategoria']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="stock">Stock</label>
        <input type="number" id="stock" name="stock" min="0" required value="<?php echo escapar($registro['stock']); ?>">

        <label for="cosuni">Costo unitario (S/)</label>
        <input type="number" id="cosuni" name="cosuni" min="0" step="0.0001" required value="<?php echo escapar($registro['cosuni']); ?>">

        <label for="preuni">Precio de venta unitario (S/)</label>
        <input type="number" id="preuni" name="preuni" min="0" step="0.0001" required value="<?php echo escapar($registro['preuni']); ?>">

        <label for="estado">Estado</label>
        <select id="estado" name="estado" required>
            <option value="1" <?php echo ($registro['estado'] === '1') ? 'selected' : ''; ?>>Activo</option>
            <option value="0" <?php echo ($registro['estado'] === '0') ? 'selected' : ''; ?>>Inactivo</option>
        </select>

        <input type="hidden" name="csrf" value="<?php echo escapar($token); ?>">

        <button type="submit" class="btn btn-amarillo">Actualizar</button>
        <a href="productos.php"><button type="button" class="btn btn-rojo">Cancelar</button></a>
    </form>
</div>
</body>
</html>