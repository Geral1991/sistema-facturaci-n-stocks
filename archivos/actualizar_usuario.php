<?php
session_start();
require_once "../conexion/conexion.php";
require_once "../conexion/funciones.php";
validarSesion("../index.php");
validarCsrf();

$idusuario  = trim($_POST['idusuario'] ?? '');
$nomusuario = trim($_POST['nomusuario'] ?? '');
$nombres    = trim($_POST['nombres'] ?? '');
$apellidos  = trim($_POST['apellidos'] ?? '');
$email      = trim($_POST['email'] ?? '');
$password   = $_POST['password'] ?? '';
$estado     = trim($_POST['estado'] ?? '1');
$estado     = ($estado === '0') ? '0' : '1';

if ($idusuario === '' || $nomusuario === '' || $nombres === '' || $apellidos === '') {
    $_SESSION['mensaje'] = "Debe completar todos los campos obligatorios.";
    $_SESSION['tipoMensaje'] = "error";
    header("Location: modificar_usuario.php?id=" . urlencode($idusuario));
    exit;
}

if ($password !== '') {
    // Se solicitó cambio de contraseña: validar longitud mínima y hashear
    if (strlen($password) < 6) {
        $_SESSION['mensaje'] = "La nueva contraseña debe tener al menos 6 caracteres.";
        $_SESSION['tipoMensaje'] = "error";
        header("Location: modificar_usuario.php?id=" . urlencode($idusuario));
        exit;
    }
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "UPDATE Usuarios SET nomusuario = ?, password = ?, apellidos = ?, nombres = ?, email = ?, estado = ? WHERE idusuario = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "sssssss", $nomusuario, $passwordHash, $apellidos, $nombres, $email, $estado, $idusuario);
} else {
    // No se cambia la contraseña actual
    $sql = "UPDATE Usuarios SET nomusuario = ?, apellidos = ?, nombres = ?, email = ?, estado = ? WHERE idusuario = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ssssss", $nomusuario, $apellidos, $nombres, $email, $estado, $idusuario);
}

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['mensaje'] = "Usuario actualizado correctamente.";
    $_SESSION['tipoMensaje'] = "exito";
} else {
    if (mysqli_errno($conexion) == 1062) {
        $_SESSION['mensaje'] = "Ya existe otro usuario con ese nombre de usuario.";
    } else {
        $_SESSION['mensaje'] = "Error al actualizar el registro.";
    }
    $_SESSION['tipoMensaje'] = "error";
}
mysqli_stmt_close($stmt);
header("Location: usuarios.php");
exit;