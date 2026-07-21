<?php
/**
 * funciones.php
 * Núcleo de funciones de seguridad nativa (OWASP Top 10 - PHP nativo).
 * Centraliza la protección contra XSS y CSRF de todo el sistema.
 */

/**
 * escapar()
 * Sanitiza cualquier dato que se imprima en el HTML proveniente de la
 * base de datos o de entradas del usuario, previniendo ataques XSS.
 *
 * @param string $html
 * @return string
 */
function escapar($html) {
    return htmlspecialchars((string)$html, ENT_QUOTES, 'UTF-8');
}

/**
 * csrf()
 * Genera (si no existe) y retorna un token seguro de sesión para
 * proteger los formularios contra ataques CSRF.
 *
 * @return string
 */
function csrf() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/**
 * validarCsrf()
 * Valida el token enviado por POST contra el token almacenado en la
 * sesión utilizando hash_equals() (comparación segura contra timing
 * attacks). Si no coincide, detiene la ejecución inmediatamente.
 *
 * @return void
 */
function validarCsrf() {
    if (
        !isset($_POST['csrf']) ||
        !isset($_SESSION['csrf']) ||
        !hash_equals($_SESSION['csrf'], $_POST['csrf'])
    ) {
        die("Token de seguridad inválido. Operación cancelada por motivos de seguridad.");
    }
}

/**
 * validarSesion()
 * Verifica que exista una sesión de usuario activa. Si no existe,
 * redirige al login. Debe llamarse al inicio de todo script protegido.
 *
 * @param string $rutaIndex Ruta relativa hacia index.php desde el
 *                          archivo que invoca la función (según su
 *                          profundidad de carpeta).
 * @return void
 */
function validarSesion($rutaIndex = "index.php") {
    if (empty($_SESSION['idusuario'])) {
        header("Location: " . $rutaIndex);
        exit;
    }
}
