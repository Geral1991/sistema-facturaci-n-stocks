<?php
session_start();
require_once "../conexion/conexion.php";
require_once "../conexion/funciones.php";
validarSesion("../index.php");
validarCsrf();

$idproducto  = trim($_POST['idproducto'] ?? '');
$nomproducto = trim($_POST['nomproducto'] ?? '');
$unimed      = trim($_POST['unimed'] ?? '');
$idproveedor = trim($_POST['idproveedor'] ?? '');
$idcategoria = trim($_POST['idcategoria'] ?? '');
$stock       = (int)($_POST['stock'] ?? 0);
$cosuni      = (float)($_POST['cosuni'] ?? 0);
$preuni      = (float)($_POST['preuni'] ?? 0);
$estado      = trim($_POST['estado'] ?? '1');
$estado      = ($estado === '0') ? '0' : '1';

if ($idproducto === '' || $nomproducto === '' || $idproveedor === '' || $idcategoria === '' || $stock < 0 || $cosuni < 0 || $preuni < 0) {
    $_SESSION['mensaje'] = "Debe completar todos los campos obligatorios con valores válidos.";
    $_SESSION['tipoMensaje'] = "error";
    header("Location: nuevo_producto.php");
    exit;
}

$sql = "INSERT INTO Productos (idproducto, idproveedor, nomproducto, unimed, stock, cosuni, preuni, idcategoria, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param(
    $stmt,
    "ssssiddss",
    $idproducto, $idproveedor, $nomproducto, $unimed, $stock, $cosuni, $preuni, $idcategoria, $estado
);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['mensaje'] = "Producto guardado correctamente.";
    $_SESSION['tipoMensaje'] = "exito";
    mysqli_stmt_close($stmt);
    header("Location: productos.php");
    exit;
} else {
    if (mysqli_errno($conexion) == 1062) {
        $_SESSION['mensaje'] = "Ya existe un producto con ese código.";
    } elseif (mysqli_errno($conexion) == 1452) {
        $_SESSION['mensaje'] = "El proveedor o categoría seleccionados no son válidos.";
    } else {
        $_SESSION['mensaje'] = "Error al guardar el registro.";
    }
    $_SESSION['tipoMensaje'] = "error";
    mysqli_stmt_close($stmt);
    header("Location: nuevo_producto.php");
    exit;
}