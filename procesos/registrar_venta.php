<?php
/**
 * registrar_venta.php
 * Módulo transaccional de registro de ventas.
 * Inserta en Facturas y DetalleFactura, y RESTA automáticamente
 * el stock vendido de la tabla Productos.
 */

session_start();
require_once "../conexion/conexion.php";
require_once "../conexion/funciones.php";

validarSesion("../index.php");

$mensaje = "";
$tipoMensaje = ""; // "exito" | "error"

/* ==========================================================
   PROCESAMIENTO DEL FORMULARIO (POST)
   ========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Validación obligatoria del token CSRF
    validarCsrf();

    $fecha      = trim($_POST['fecha'] ?? '');
    $idcliente  = trim($_POST['idcliente'] ?? '');
    $idcondicion = trim($_POST['idcondicion'] ?? '');
    $idusuario  = $_SESSION['idusuario'];

    $listaProductos = $_POST['idproducto'] ?? [];
    $listaCantidad  = $_POST['cant'] ?? [];

    if ($fecha === '' || $idcliente === '' || $idcondicion === '' || empty($listaProductos)) {
        $mensaje = "Debe completar todos los campos y agregar al menos un producto.";
        $tipoMensaje = "error";
    } else {

        mysqli_begin_transaction($conexion);
        $errorTransaccion = "";

        try {
            $valorventa = 0.0;
            $detalles = []; // idproducto, cant, cosuni, preuni

            // Validar cada línea de producto contra la base de datos real
            foreach ($listaProductos as $indice => $idproducto) {
                $idproducto = trim($idproducto);
                $cant = (int)($listaCantidad[$indice] ?? 0);

                if ($idproducto === '' || $cant <= 0) {
                    continue;
                }

                $sqlProd = "SELECT idproducto, nomproducto, stock, cosuni, preuni
                            FROM Productos WHERE idproducto = ? AND estado = '1'
                            FOR UPDATE";
                $stmtProd = mysqli_prepare($conexion, $sqlProd);
                mysqli_stmt_bind_param($stmtProd, "s", $idproducto);
                mysqli_stmt_execute($stmtProd);
                $resProd = mysqli_stmt_get_result($stmtProd);
                $producto = mysqli_fetch_assoc($resProd);
                mysqli_stmt_close($stmtProd);

                if (!$producto) {
                    throw new Exception("El producto seleccionado no existe o está inactivo.");
                }

                if ($producto['stock'] < $cant) {
                    throw new Exception("Stock insuficiente para el producto \"" . $producto['nomproducto'] . "\". Stock disponible: " . $producto['stock']);
                }

                $subtotal = $cant * (float)$producto['preuni'];
                $valorventa += $subtotal;

                $detalles[] = [
                    'idproducto' => $idproducto,
                    'cant'       => $cant,
                    'cosuni'     => $producto['cosuni'],
                    'preuni'     => $producto['preuni'],
                ];
            }

            if (empty($detalles)) {
                throw new Exception("No se registró ningún producto válido en la venta.");
            }

            $igv = $valorventa * 0.18;

            // 2. Insertar cabecera de la venta (Facturas)
            $sqlFactura = "INSERT INTO Facturas (fecha, idcliente, idusuario, fechareg, idcondicion, valorventa, igv)
                           VALUES (?, ?, ?, NOW(), ?, ?, ?)";
            $stmtFactura = mysqli_prepare($conexion, $sqlFactura);
            mysqli_stmt_bind_param(
                $stmtFactura,
                "sssidd",
                $fecha, $idcliente, $idusuario, $idcondicion, $valorventa, $igv
            );
            mysqli_stmt_execute($stmtFactura);
            $idfactura = mysqli_insert_id($conexion);
            mysqli_stmt_close($stmtFactura);

            // 3. Insertar detalle y descontar stock por cada producto
            foreach ($detalles as $det) {
                $sqlDetalle = "INSERT INTO DetalleFactura (idfactura, idproducto, cant, cosuni, preuni)
                               VALUES (?, ?, ?, ?, ?)";
                $stmtDetalle = mysqli_prepare($conexion, $sqlDetalle);
                mysqli_stmt_bind_param(
                    $stmtDetalle,
                    "isidd",
                    $idfactura, $det['idproducto'], $det['cant'], $det['cosuni'], $det['preuni']
                );
                mysqli_stmt_execute($stmtDetalle);
                mysqli_stmt_close($stmtDetalle);

                // RESTA automática del stock vendido
                $sqlStock = "UPDATE Productos SET stock = stock - ? WHERE idproducto = ?";
                $stmtStock = mysqli_prepare($conexion, $sqlStock);
                mysqli_stmt_bind_param($stmtStock, "is", $det['cant'], $det['idproducto']);
                mysqli_stmt_execute($stmtStock);
                mysqli_stmt_close($stmtStock);
            }

            mysqli_commit($conexion);
            $mensaje = "Venta registrada correctamente con el N° de factura " . $idfactura . ".";
            $tipoMensaje = "exito";

        } catch (Exception $e) {
            mysqli_rollback($conexion);
            $mensaje = "Error al registrar la venta: " . $e->getMessage();
            $tipoMensaje = "error";
        }
    }
}

/* ==========================================================
   DATOS PARA EL FORMULARIO
   ========================================================== */
$token = csrf();

$clientes = mysqli_query($conexion, "SELECT idcliente, nomcliente FROM Clientes ORDER BY nomcliente");
$condiciones = mysqli_query($conexion, "SELECT idcondicion, nomcondicion FROM CondicionVenta ORDER BY nomcondicion");
$productos = mysqli_query($conexion, "SELECT idproducto, nomproducto, preuni, stock FROM Productos WHERE estado = '1' AND stock > 0 ORDER BY nomproducto");

// Se arma un arreglo en JSON para que JavaScript (nativo, sin frameworks)
// pueda autocompletar el precio unitario y validar el stock en pantalla.
$productosJs = [];
mysqli_data_seek($productos, 0);
while ($p = mysqli_fetch_assoc($productos)) {
    $productosJs[] = [
        'id'     => $p['idproducto'],
        'nombre' => $p['nomproducto'],
        'preuni' => (float)$p['preuni'],
        'stock'  => (int)$p['stock'],
    ];
}
mysqli_data_seek($productos, 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registrar Venta - Sistema de Facturación</title>
<style>
    * { box-sizing: border-box; }
    body {
        font-family: Arial, Helvetica, sans-serif;
        background-color: #eef1f5;
        margin: 0;
        padding: 30px 15px;
    }
    .contenedor {
        max-width: 750px;
        margin: 0 auto;
        background-color: #ffffff;
        padding: 30px;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    h1 {
        color: #2c3e50;
        font-size: 20px;
        margin-top: 0;
    }
    a.volver {
        display: inline-block;
        margin-bottom: 15px;
        color: #2980b9;
        text-decoration: none;
        font-size: 13px;
    }
    label {
        display: block;
        font-weight: bold;
        color: #34495e;
        font-size: 13px;
        margin-bottom: 4px;
    }
    input[type="date"], input[type="text"], input[type="number"], select {
        width: 100%;
        padding: 8px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
    }
    .fila-cabecera {
        display: flex;
        gap: 20px;
    }
    .fila-cabecera > div {
        flex: 1;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 15px;
    }
    table th, table td {
        border: 1px solid #ddd;
        padding: 8px;
        font-size: 13px;
        text-align: left;
    }
    table th {
        background-color: #f2f2f2;
    }
    table td select, table td input {
        margin-bottom: 0;
    }
    .btn {
        padding: 9px 18px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: bold;
        cursor: pointer;
        color: #ffffff;
    }
    .btn-verde  { background-color: #27ae60; }
    .btn-verde:hover { background-color: #219150; }
    .btn-rojo   { background-color: #e74c3c; }
    .btn-rojo:hover { background-color: #c0392b; }
    .btn-agregar {
        background-color: #2980b9;
        margin-bottom: 15px;
    }
    .btn-agregar:hover { background-color: #21618c; }
    .totales {
        text-align: right;
        font-size: 14px;
        margin-bottom: 15px;
        color: #2c3e50;
    }
    .mensaje {
        padding: 12px 15px;
        border-radius: 4px;
        font-size: 14px;
        margin-bottom: 20px;
    }
    .mensaje.exito {
        background-color: #eafaf1;
        color: #196f3d;
        border: 1px solid #27ae60;
    }
    .mensaje.error {
        background-color: #fdecea;
        color: #c0392b;
        border: 1px solid #e74c3c;
    }
    .btn-quitar {
        background-color: #e74c3c;
        color: #ffffff;
        border: none;
        border-radius: 4px;
        padding: 6px 10px;
        cursor: pointer;
        font-size: 12px;
    }
    .btn-quitar:hover { background-color: #c0392b; }
</style>
</head>
<body>

<div class="contenedor">
    <a class="volver" href="../dashboard.php">&larr; Volver al dashboard</a>
    <h1>Registrar Venta</h1>

    <?php if ($mensaje !== ''): ?>
        <div class="mensaje <?php echo escapar($tipoMensaje); ?>"><?php echo escapar($mensaje); ?></div>
    <?php endif; ?>

    <form action="registrar_venta.php" method="POST" id="formVenta">

        <div class="fila-cabecera">
            <div>
                <label for="fecha">Fecha</label>
                <input type="date" id="fecha" name="fecha" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div>
                <label for="idcliente">Cliente</label>
                <select id="idcliente" name="idcliente" required>
                    <option value="">-- Seleccione --</option>
                    <?php while ($c = mysqli_fetch_assoc($clientes)): ?>
                        <option value="<?php echo escapar($c['idcliente']); ?>">
                            <?php echo escapar($c['nomcliente']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label for="idcondicion">Condición de venta</label>
                <select id="idcondicion" name="idcondicion" required>
                    <option value="">-- Seleccione --</option>
                    <?php while ($cv = mysqli_fetch_assoc($condiciones)): ?>
                        <option value="<?php echo escapar($cv['idcondicion']); ?>">
                            <?php echo escapar($cv['nomcondicion']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <table id="tablaProductos">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th style="width:90px;">Cantidad</th>
                    <th style="width:110px;">Precio Unit.</th>
                    <th style="width:110px;">Subtotal</th>
                    <th style="width:60px;"></th>
                </tr>
            </thead>
            <tbody>
                <!-- Las filas se generan dinámicamente con JavaScript nativo -->
            </tbody>
        </table>

        <button type="button" class="btn btn-agregar" onclick="agregarFila()">+ Agregar producto</button>

        <div class="totales">
            Valor de venta: S/ <span id="lblValorVenta">0.00</span><br>
            IGV (18%): S/ <span id="lblIgv">0.00</span><br>
            <strong>Total: S/ <span id="lblTotal">0.00</span></strong>
        </div>

        <input type="hidden" name="csrf" value="<?php echo escapar($token); ?>">

        <button type="submit" class="btn btn-verde">Guardar Venta</button>
        <a href="../dashboard.php"><button type="button" class="btn btn-rojo">Cancelar</button></a>
    </form>
</div>

<script>
// Catálogo de productos disponible (generado desde PHP, sin frameworks)
const productos = <?php echo json_encode($productosJs, JSON_UNESCAPED_UNICODE); ?>;

function crearSelectProductos() {
    let opciones = '<option value="">-- Seleccione --</option>';
    productos.forEach(function (p) {
        opciones += '<option value="' + p.id + '" data-preuni="' + p.preuni + '" data-stock="' + p.stock + '">' +
            p.nombre + ' (Stock: ' + p.stock + ')</option>';
    });
    return opciones;
}

function agregarFila() {
    const tbody = document.querySelector('#tablaProductos tbody');
    const fila = document.createElement('tr');

    fila.innerHTML =
        '<td><select name="idproducto[]" onchange="actualizarFila(this)" required>' + crearSelectProductos() + '</select></td>' +
        '<td><input type="number" name="cant[]" min="1" value="1" onchange="actualizarFila(this)" required></td>' +
        '<td><input type="text" class="preuni" value="0.00" readonly></td>' +
        '<td><input type="text" class="subtotal" value="0.00" readonly></td>' +
        '<td><button type="button" class="btn-quitar" onclick="quitarFila(this)">Quitar</button></td>';

    tbody.appendChild(fila);
}

function quitarFila(boton) {
    const fila = boton.closest('tr');
    fila.remove();
    calcularTotales();
}

function actualizarFila(elemento) {
    const fila = elemento.closest('tr');
    const select = fila.querySelector('select[name="idproducto[]"]');
    const cantInput = fila.querySelector('input[name="cant[]"]');
    const preuniInput = fila.querySelector('.preuni');
    const subtotalInput = fila.querySelector('.subtotal');

    const opcionSeleccionada = select.options[select.selectedIndex];
    const preuni = parseFloat(opcionSeleccionada.getAttribute('data-preuni')) || 0;
    const stockDisponible = parseInt(opcionSeleccionada.getAttribute('data-stock')) || 0;
    let cantidad = parseInt(cantInput.value) || 0;

    if (cantidad > stockDisponible) {
        alert('La cantidad supera el stock disponible (' + stockDisponible + ').');
        cantidad = stockDisponible;
        cantInput.value = stockDisponible;
    }

    preuniInput.value = preuni.toFixed(2);
    subtotalInput.value = (preuni * cantidad).toFixed(2);

    calcularTotales();
}

function calcularTotales() {
    let valorventa = 0;
    document.querySelectorAll('#tablaProductos tbody .subtotal').forEach(function (input) {
        valorventa += parseFloat(input.value) || 0;
    });
    const igv = valorventa * 0.18;
    const total = valorventa + igv;

    document.getElementById('lblValorVenta').textContent = valorventa.toFixed(2);
    document.getElementById('lblIgv').textContent = igv.toFixed(2);
    document.getElementById('lblTotal').textContent = total.toFixed(2);
}

// Al cargar la página se agrega automáticamente la primera fila
document.addEventListener('DOMContentLoaded', function () {
    agregarFila();
});
</script>

</body>
</html>
