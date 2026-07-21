<?php
/**
 * conexion.php
 * Conexión estándar a la base de datos mediante mysqli (PHP nativo).
 * Sistema de Facturación y Control de Stocks.
 */

$servidor  = "localhost";
$usuario_db = "root";
$clave_db   = "";
$basedatos  = "sistema_ventas";

$conexion = mysqli_connect($servidor, $usuario_db, $clave_db, $basedatos);

if (!$conexion) {
    die("Error de conexión a la base de datos: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8");
