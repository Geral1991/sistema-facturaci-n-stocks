<?php
/**
 * logout.php
 * Cierra la sesión activa del sistema de forma segura.
 */

session_start();

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $parametros = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parametros["path"],
        $parametros["domain"],
        $parametros["secure"],
        $parametros["httponly"]
    );
}

session_destroy();

header("Location: index.php");
exit;
