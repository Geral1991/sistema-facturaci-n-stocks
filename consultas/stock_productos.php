<?php
session_start();

require_once "../conexion/conexion.php";
require_once "../conexion/funciones.php";

validarSesion("../index.php");

/*=========================================
CONSULTA DE STOCK DE PRODUCTOS
==========================================*/

$sql = "SELECT
            p.idproducto,
            p.nomproducto,
            c.nomcategoria,
            pr.nomproveedor,
            p.unimed,
            p.stock,
            p.cosuni,
            p.preuni,
            p.estado
        FROM Productos p
        INNER JOIN Categorias c
            ON p.idcategoria = c.idcategoria
        INNER JOIN Proveedores pr
            ON p.idproveedor = pr.idproveedor
        ORDER BY p.nomproducto";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Consulta de Stock de Productos</title>

<style>

*{
    box-sizing:border-box;
}

body{

    margin:0;
    padding:30px;
    background:#eef1f5;
    font-family:Arial, Helvetica, sans-serif;

}

.contenedor{

    max-width:1200px;
    margin:auto;
    background:white;
    padding:25px;
    border-radius:8px;
    box-shadow:0 2px 8px rgba(0,0,0,.15);

}

h1{

    margin-top:0;
    color:#2c3e50;
    text-align:center;

}

table{

    width:100%;
    border-collapse:collapse;
    margin-top:20px;

}

table th,
table td{

    border:1px solid #ddd;
    padding:10px;
    text-align:left;

}

table th{

    background:#f2f2f2;

}

.btn{

    display:inline-block;
    padding:10px 18px;
    text-decoration:none;
    color:white;
    border-radius:4px;
    font-weight:bold;

}

.azul{

    background:#3498db;

}

.azul:hover{

    background:#2e86c1;

}

.stock-alto{

    color:#27ae60;
    font-weight:bold;

}

.stock-bajo{

    color:#e67e22;
    font-weight:bold;

}

.stock-agotado{

    color:#e74c3c;
    font-weight:bold;

}

</style>

</head>

<body>

<div class="contenedor">

<h1>Consulta de Stock de Productos</h1>

<a href="../dashboard.php" class="btn azul">
Volver al Dashboard
</a>

<table>

<thead>

<tr>

<th>Código</th>
<th>Producto</th>
<th>Categoría</th>
<th>Proveedor</th>
<th>Unidad</th>
<th>Stock</th>
<th>Costo</th>
<th>Precio</th>
<th>Estado</th>

</tr>

</thead>

<tbody>

<?php

while($fila=mysqli_fetch_assoc($resultado))
{

?>

<tr>

<td>

<?php echo escapar($fila["idproducto"]); ?>

</td>

<td>

<?php echo escapar($fila["nomproducto"]); ?>

</td>

<td>

<?php echo escapar($fila["nomcategoria"]); ?>

</td>

<td>

<?php echo escapar($fila["nomproveedor"]); ?>

</td>

<td>

<?php echo escapar($fila["unimed"]); ?>

</td>

<td>

<?php

if($fila["stock"]==0)
{

    echo "<span class='stock-agotado'>".$fila["stock"]."</span>";

}
elseif($fila["stock"]<=10)
{

    echo "<span class='stock-bajo'>".$fila["stock"]."</span>";

}
else
{

    echo "<span class='stock-alto'>".$fila["stock"]."</span>";

}

?>

</td>

<td>

S/
<?php echo number_format($fila["cosuni"],4); ?>

</td>

<td>

S/
<?php echo number_format($fila["preuni"],4); ?>

</td>

<td>
    
<?php

if($fila["estado"]=="1")
{
    echo "Activo";
}
else
{
    echo "Inactivo";
}

?>

</td>

</tr>

<?php

}

mysqli_stmt_close($stmt);

?>

</tbody>

</table>

</div>

</body>

</html>