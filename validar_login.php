<?php
/**
 * validar_login.php
 * Procesa el formulario de login: valida CSRF, verifica credenciales
 * contra la tabla Usuarios y abre la sesión del sistema.
 */

session_start();
require_once "conexion/conexion.php";
require_once "conexion/funciones.php";

// Solo se acepta esta petición vía POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// 1. Validación obligatoria del token CSRF
validarCsrf();

// 2. Recolección y saneo básico de entrada
$nomusuario = trim($_POST['nomusuario'] ?? '');
$password   = $_POST['password'] ?? '';

if ($nomusuario === '' || $password === '') {
    $_SESSION['login_error'] = "Debe ingresar usuario y contraseña.";
    header("Location: index.php");
    exit;
}

// 3. Consulta preparada (previene inyección SQL) del usuario
$sql = "SELECT idusuario, nomusuario, password, apellidos, nombres, email, estado
        FROM Usuarios
        WHERE nomusuario = ?
        LIMIT 1";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $nomusuario);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

// 4. Verificación de existencia, estado activo y contraseña
if (!$usuario) {
    $_SESSION['login_error'] = "Usuario o contraseña incorrectos.";
    header("Location: index.php");
    exit;
}

if ($usuario['estado'] !== '1' && strtoupper($usuario['estado']) !== 'A') {
    $_SESSION['login_error'] = "El usuario se encuentra inactivo. Contacte al administrador.";
    header("Location: index.php");
    exit;
}

// La contraseña se almacena con password_hash() al momento de crear el usuario
if (!password_verify($password, $usuario['password'])) {
    $_SESSION['login_error'] = "Usuario o contraseña incorrectos.";
    header("Location: index.php");
    exit;
}

// 5. Regenerar el ID de sesión para prevenir fijación de sesión (session fixation)
session_regenerate_id(true);

// 6. Registrar datos de sesión
$_SESSION['idusuario']  = $usuario['idusuario'];
$_SESSION['nomusuario'] = $usuario['nomusuario'];
$_SESSION['nombres']    = $usuario['nombres'];
$_SESSION['apellidos']  = $usuario['apellidos'];

header("Location: dashboard.php");
exit;
