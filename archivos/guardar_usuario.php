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

if ($idusuario === '' || $nomusuario === '' || $nombres === '' || $apellidos === '' || $password === '') {
    $_SESSION['mensaje'] = "Debe completar todos los campos obligatorios.";
    $_SESSION['tipoMensaje'] = "error";
    header("Location: nuevo_usuario.php");
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['mensaje'] = "La contraseña debe tener al menos 6 caracteres.";
    $_SESSION['tipoMensaje'] = "error";
    header("Location: nuevo_usuario.php");
    exit;
}

// La contraseña nunca se guarda en texto plano: se hashea con password_hash()
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO Usuarios (idusuario, nomusuario, password, apellidos, nombres, email, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "sssssss", $idusuario, $nomusuario, $passwordHash, $apellidos, $nombres, $email, $estado);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['mensaje'] = "Usuario guardado correctamente.";
    $_SESSION['tipoMensaje'] = "exito";
    mysqli_stmt_close($stmt);
    header("Location: usuarios.php");
    exit;
} else {
    if (mysqli_errno($conexion) == 1062) {
        $_SESSION['mensaje'] = "Ya existe un usuario con ese código o ese nombre de usuario.";
    } else {
        $_SESSION['mensaje'] = "Error al guardar el registro.";
    }
    $_SESSION['tipoMensaje'] = "error";
    mysqli_stmt_close($stmt);
    header("Location: nuevo_usuario.php");
    exit;
}