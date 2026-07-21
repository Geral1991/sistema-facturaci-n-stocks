<?php
session_start();
require_once "../conexion/conexion.php";
require_once "../conexion/funciones.php";
validarSesion("../index.php");

$mensaje = $_SESSION['mensaje'] ?? '';
$tipoMensaje = $_SESSION['tipoMensaje'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['tipoMensaje']);

$sql = "SELECT p.idproducto, p.nomproducto, p.unimed, p.stock, p.cosuni, p.preuni, p.estado,
               c.nomcategoria, pr.nomproveedor
        FROM Productos p
        INNER JOIN Categorias c   ON p.idcategoria = c.idcategoria
        INNER JOIN Proveedores pr ON p.idproveedor = pr.idproveedor
        ORDER BY p.nomproducto";
$resultado = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Productos - Sistema de Facturación</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: Arial, Helvetica, sans-serif; background-color: #eef1f5; margin: 0; padding: 30px 15px; }
    .contenedor { max-width: 1100px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h1 { color: #2c3e50; font-size: 20px; margin-top: 0; }
    a.volver { display: inline-block; margin-bottom: 15px; color: #2980b9; text-decoration: none; font-size: 13px; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 15px; }
    table th, table td { border: 1px solid #ddd; padding: 8px; font-size: 13px; text-align: left; }
    table th { background-color: #f2f2f2; }
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
    .stock-bajo { color: #e74c3c; font-weight: bold; }
</style>
</head>
<body>
<div class="contenedor">
    <a class="volver" href="../dashboard.php">&larr; Volver al dashboard</a>
    <h1>Productos</h1>

    <?php if ($mensaje !== ''): ?>
        <div class="mensaje <?php echo escapar($tipoMensaje); ?>"><?php echo escapar($mensaje); ?></div>
    <?php endif; ?>

    <a class="btn btn-verde nuevo-btn" href="nuevo_producto.php">+ Nuevo Producto</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Unidad</th>
                <th>Stock</th>
                <th>Costo Unit.</th>
                <th>Precio Unit.</th>
                <th>Categoría</th>
                <th>Proveedor</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($resultado) === 0): ?>
            <tr><td colspan="10">No hay registros para mostrar.</td></tr>
            <?php endif; ?>
            <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
            <tr>
                <td><?php echo escapar($fila['idproducto']); ?></td>
                <td><?php echo escapar($fila['nomproducto']); ?></td>
                <td><?php echo escapar($fila['unimed']); ?></td>
                <td class="<?php echo ((int)$fila['stock'] < 10) ? 'stock-bajo' : ''; ?>">
                    <?php echo escapar($fila['stock']); ?>
                </td>
                <td><?php echo number_format((float)$fila['cosuni'], 2); ?></td>
                <td><?php echo number_format((float)$fila['preuni'], 2); ?></td>
                <td><?php echo escapar($fila['nomcategoria']); ?></td>
                <td><?php echo escapar($fila['nomproveedor']); ?></td>
                <td>
                    <?php if ($fila['estado'] === '1'): ?>
                        <span class="estado-activo">Activo</span>
                    <?php else: ?>
                        <span class="estado-inactivo">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td class="acciones">
                    <a class="btn btn-amarillo" href="modificar_producto.php?id=<?php echo urlencode($fila['idproducto']); ?>">Modificar</a>
                    <a class="btn btn-rojo" href="confirmar_eliminar.php?entidad=producto&id=<?php echo urlencode($fila['idproducto']); ?>">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>