<?php
/**
 * dashboard.php
 * Panel principal del sistema. Protegido por sesión.
 * Contiene el menú de navegación oficial: ARCHIVOS, PROCESOS, CONSULTAS.
 */

session_start();
require_once "conexion/funciones.php";

validarSesion("index.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard - Sistema de Facturación y Control de Stocks</title>
<style>
    * { box-sizing: border-box; }
    body {
        font-family: Arial, Helvetica, sans-serif;
        background-color: #eef1f5;
        margin: 0;
        padding: 0;
    }
    header.top {
        background-color: #2c3e50;
        color: #ffffff;
        padding: 15px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    header.top h1 {
        font-size: 18px;
        margin: 0;
    }
    header.top .usuario-info {
        font-size: 13px;
    }
    nav.menu {
        background-color: #34495e;
        display: flex;
        flex-wrap: wrap;
        padding: 0 15px;
    }
    nav.menu .grupo {
        position: relative;
    }
    nav.menu .grupo > span {
        display: block;
        color: #ecf0f1;
        padding: 12px 18px;
        font-weight: bold;
        font-size: 13px;
        cursor: default;
        border-right: 1px solid #455a70;
    }
    nav.menu .submenu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background-color: #ffffff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        min-width: 190px;
        z-index: 10;
    }
    nav.menu .grupo:hover .submenu {
        display: block;
    }
    nav.menu .submenu a {
        display: block;
        padding: 10px 15px;
        color: #2c3e50;
        text-decoration: none;
        font-size: 13px;
        border-bottom: 1px solid #eee;
    }
    nav.menu .submenu a:hover {
        background-color: #f2f2f2;
    }
    nav.menu .submenu a.salir {
        color: #c0392b;
        font-weight: bold;
    }
    main.contenido {
        max-width: 900px;
        margin: 40px auto;
        background-color: #ffffff;
        padding: 30px;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    main.contenido h2 {
        color: #2c3e50;
        margin-top: 0;
    }
    main.contenido p {
        color: #555555;
        font-size: 14px;
        line-height: 1.6;
    }
</style>
</head>
<body>

<header class="top">
    <h1>Sistema de Facturación y Control de Stocks</h1>
    <div class="usuario-info">
        Conectado como: <strong><?php echo escapar($_SESSION['nombres'] . " " . $_SESSION['apellidos']); ?></strong>
        (<?php echo escapar($_SESSION['nomusuario']); ?>)
    </div>
</header>

<nav class="menu">
    <div class="grupo">
        <span>ARCHIVOS</span>
        <div class="submenu">
            <a href="archivos/productos.php">Productos</a>
            <a href="archivos/clientes.php">Clientes</a>
            <a href="archivos/proveedores.php">Proveedores</a>
            <a href="archivos/categorias.php">Categorías</a>
            <a href="archivos/usuarios.php">Usuarios</a>
            <a href="logout.php" class="salir">Terminar</a>
        </div>
    </div>
    <div class="grupo">
        <span>PROCESOS</span>
        <div class="submenu">
            <a href="procesos/registrar_venta.php">Registrar Ventas</a>
            <a href="procesos/registrar_compra.php">Registrar Compras</a>
        </div>
    </div>
    <div class="grupo">
        <span>CONSULTAS</span>
        <div class="submenu">
            <a href="consultas/stock_productos.php">Stock de productos</a>
            <a href="consultas/ranking_ventas.php">Ranking de ventas</a>
        </div>
    </div>
</nav>

<main class="contenido">
    <h2>Bienvenido, <?php echo escapar($_SESSION['nombres']); ?></h2>
    <p>
        Utilice el menú superior para acceder a los módulos de <strong>ARCHIVOS</strong>
        (mantenimiento de datos maestros), <strong>PROCESOS</strong> (registro de ventas
        y compras) y <strong>CONSULTAS</strong> (reportes de stock y ranking de ventas).
    </p>
</main>

</body>
</html>
