<?php
session_start();
require_once "../conexion/conexion.php";
require_once "../conexion/funciones.php";
validarSesion("../index.php");
validarCsrf();

$idproveedor = trim($_POST['idproveedor'] ?? '');
$nomproveedor = trim($_POST['nomproveedor'] ?? '');
$rucproveedor = trim($_POST['rucproveedor'] ?? '');
$dirproveedor = trim($_POST['dirproveedor'] ?? '');
$telproveedor = trim($_POST['telproveedor'] ?? '');
$emailproveedor = trim($_POST['emailproveedor'] ?? '');

if ($idproveedor === '' || $nomproveedor === '') {
    $_SESSION['mensaje'] = "Debe completar todos los campos obligatorios.";
    $_SESSION['tipoMensaje'] = "error";
    header("Location: nuevo_proveedor.php");
    exit;
}

$sql = "INSERT INTO Proveedores (idproveedor, nomproveedor, rucproveedor, dirproveedor, telproveedor, emailproveedor) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "ssssss", $idproveedor, $nomproveedor, $rucproveedor, $dirproveedor, $telproveedor, $emailproveedor);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['mensaje'] = "Proveedor guardado correctamente.";
    $_SESSION['tipoMensaje'] = "exito";
    mysqli_stmt_close($stmt);
    header("Location: proveedores.php");
    exit;
} else {
    if (mysqli_errno($conexion) == 1062) {
        $_SESSION['mensaje'] = "Ya existe un registro con ese código (ID duplicado).";
    } else {
        $_SESSION['mensaje'] = "Error al guardar el registro.";
    }
    $_SESSION['tipoMensaje'] = "error";
    mysqli_stmt_close($stmt);
    header("Location: nuevo_proveedor.php");
    exit;
}