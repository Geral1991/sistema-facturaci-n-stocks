<?php
session_start();
require_once "../conexion/conexion.php";
require_once "../conexion/funciones.php";
validarSesion("../index.php");
validarCsrf();

$idusuario = trim($_POST['id'] ?? '');

if ($idusuario === '') {
    header("Location: usuarios.php");
    exit;
}

// No se permite que un usuario se elimine a sí mismo mientras tiene la sesión activa
if ($idusuario === $_SESSION['idusuario']) {
    $_SESSION['mensaje'] = "No puedes eliminar el usuario con el que iniciaste sesión.";
    $_SESSION['tipoMensaje'] = "error";
    header("Location: usuarios.php");
    exit;
}

$sql = "DELETE FROM Usuarios WHERE idusuario = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $idusuario);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $_SESSION['mensaje'] = "Usuario eliminado correctamente.";
        $_SESSION['tipoMensaje'] = "exito";
    } else {
        $_SESSION['mensaje'] = "El usuario ya no existe.";
        $_SESSION['tipoMensaje'] = "error";
    }
} else {
    if (mysqli_errno($conexion) == 1451) {
        $_SESSION['mensaje'] = "No se puede eliminar: el usuario tiene ventas o compras registradas en el sistema.";
    } else {
        $_SESSION['mensaje'] = "Error al eliminar el registro.";
    }
    $_SESSION['tipoMensaje'] = "error";
}
mysqli_stmt_close($stmt);
header("Location: usuarios.php");
exit;