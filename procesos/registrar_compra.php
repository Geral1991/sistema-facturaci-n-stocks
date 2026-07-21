<?php
/**
 * registrar_compra.php
 * Módulo transaccional de registro de compras (abastecimiento).
 * Inserta en compras y detallecompras, y SUMA automáticamente
 * el stock comprado en la tabla Productos.
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

    $fecha       = trim($_POST['fecha'] ?? '');
    $idproveedor = trim($_POST['idproveedor'] ?? '');
    $idusuario   = $_SESSION['idusuario'];

    $listaProductos = $_POST['idproducto'] ?? [];
    $listaCantidad  = $_POST['cant'] ?? [];
    $listaCosto     = $_POST['cosuni'] ?? [];

    if ($fecha === '' || $idproveedor === '' || empty($listaProductos)) {
        $mensaje = "Debe completar todos los campos y agregar al menos un producto.";
        $tipoMensaje = "error";
    } else {

        mysqli_begin_transaction($conexion);

        try {
            $total = 0.0;
            $detalles = []; // idproducto, cant, cosuni

            foreach ($listaProductos as $indice => $idproducto) {
                $idproducto = trim($idproducto);
                $cant   = (int)($listaCantidad[$indice] ?? 0);
                $cosuni = (float)($listaCosto[$indice] ?? 0);

                if ($idproducto === '' || $cant <= 0 || $cosuni <= 0) {
                    continue;
                }

                // Verificar que el producto exista y bloquear la fila (FOR UPDATE)
                $sqlProd = "SELECT idproducto, nomproducto FROM Productos WHERE idproducto = ? FOR UPDATE";
                $stmtProd = mysqli_prepare($conexion, $sqlProd);
                mysqli_stmt_bind_param($stmtProd, "s", $idproducto);
                mysqli_stmt_execute($stmtProd);
                $resProd = mysqli_stmt_get_result($stmtProd);
                $producto = mysqli_fetch_assoc($resProd);
                mysqli_stmt_close($stmtProd);

                if (!$producto) {
                    throw new Exception("El producto seleccionado no existe.");
                }

                $subtotal = $cant * $cosuni;
                $total += $subtotal;

                $detalles[] = [
                    'idproducto' => $idproducto,
                    'cant'       => $cant,
                    'cosuni'     => $cosuni,
                ];
            }

            if (empty($detalles)) {
                throw new Exception("No se registró ningún producto válido en la compra.");
            }

            // 2. Insertar cabecera de la compra
            $sqlCompra = "INSERT INTO compras (fecha, idproveedor, idusuario, total, fechareg)
                          VALUES (?, ?, ?, ?, NOW())";
            $stmtCompra = mysqli_prepare($conexion, $sqlCompra);
            mysqli_stmt_bind_param($stmtCompra, "sssd", $fecha, $idproveedor, $idusuario, $total);
            mysqli_stmt_execute($stmtCompra);
            $idcompra = mysqli_insert_id($conexion);
            mysqli_stmt_close($stmtCompra);

            // 3. Insertar detalle y SUMAR stock por cada producto comprado
            foreach ($detalles as $det) {
                $sqlDetalle = "INSERT INTO detallecompras (idcompra, idproducto, cant, cosuni)
                               VALUES (?, ?, ?, ?)";
                $stmtDetalle = mysqli_prepare($conexion, $sqlDetalle);
                mysqli_stmt_bind_param(
                    $stmtDetalle,
                    "isid",
                    $idcompra, $det['idproducto'], $det['cant'], $det['cosuni']
                );
                mysqli_stmt_execute($stmtDetalle);
                mysqli_stmt_close($stmtDetalle);

                // SUMA automática del stock comprado
                $sqlStock = "UPDATE Productos SET stock = stock + ?, cosuni = ? WHERE idproducto = ?";
                $stmtStock = mysqli_prepare($conexion, $sqlStock);
                mysqli_stmt_bind_param($stmtStock, "ids", $det['cant'], $det['cosuni'], $det['idproducto']);
                mysqli_stmt_execute($stmtStock);
                mysqli_stmt_close($stmtStock);
            }

            mysqli_commit($conexion);
            $mensaje = "Compra registrada correctamente con el N° " . $idcompra . ". El stock fue actualizado.";
            $tipoMensaje = "exito";

        } catch (Exception $e) {
            mysqli_rollback($conexion);
            $mensaje = "Error al registrar la compra: " . $e->getMessage();
            $tipoMensaje = "error";
        }
    }
}

/* ==========================================================
   DATOS PARA EL FORMULARIO
   ========================================================== */
$token = csrf();

$proveedores = mysqli_query($conexion, "SELECT idproveedor, nomproveedor FROM Proveedores ORDER BY nomproveedor");
$productos = mysqli_query($conexion, "SELECT idproducto, nomproducto, cosuni FROM Productos WHERE estado = '1' ORDER BY nomproducto");

$productosJs = [];
while ($p = mysqli_fetch_assoc($productos)) {
    $productosJs[] = [
        'id'     => $p['idproducto'],
        'nombre' => $p['nomproducto'],
        'cosuni' => (float)$p['cosuni'],
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registrar Compra - Sistema de Facturación</title>
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
    <h1>Registrar Compra (Abastecimiento)</h1>

    <?php if ($mensaje !== ''): ?>
        <div class="mensaje <?php echo escapar($tipoMensaje); ?>"><?php echo escapar($mensaje); ?></div>
    <?php endif; ?>

    <form action="registrar_compra.php" method="POST" id="formCompra">

        <div class="fila-cabecera">
            <div>
                <label for="fecha">Fecha</label>
                <input type="date" id="fecha" name="fecha" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div>
                <label for="idproveedor">Proveedor</label>
                <select id="idproveedor" name="idproveedor" required>
                    <option value="">-- Seleccione --</option>
                    <?php while ($p = mysqli_fetch_assoc($proveedores)): ?>
                        <option value="<?php echo escapar($p['idproveedor']); ?>">
                            <?php echo escapar($p['nomproveedor']); ?>
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
                    <th style="width:120px;">Costo Unit.</th>
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
            <strong>Total de la compra: S/ <span id="lblTotal">0.00</span></strong>
        </div>

        <input type="hidden" name="csrf" value="<?php echo escapar($token); ?>">

        <button type="submit" class="btn btn-verde">Guardar Compra</button>
        <a href="../dashboard.php"><button type="button" class="btn btn-rojo">Cancelar</button></a>
    </form>
</div>

<script>
// Catálogo de productos disponible (generado desde PHP, sin frameworks)
const productos = <?php echo json_encode($productosJs, JSON_UNESCAPED_UNICODE); ?>;

function crearSelectProductos() {
    let opciones = '<option value="">-- Seleccione --</option>';
    productos.forEach(function (p) {
        opciones += '<option value="' + p.id + '" data-cosuni="' + p.cosuni + '">' + p.nombre + '</option>';
    });
    return opciones;
}

function agregarFila() {
    const tbody = document.querySelector('#tablaProductos tbody');
    const fila = document.createElement('tr');

    fila.innerHTML =
        '<td><select name="idproducto[]" onchange="actualizarFila(this)" required>' + crearSelectProductos() + '</select></td>' +
        '<td><input type="number" name="cant[]" min="1" value="1" onchange="actualizarFila(this)" required></td>' +
        '<td><input type="number" step="0.0001" min="0.0001" name="cosuni[]" value="0.00" onchange="actualizarFila(this)" required></td>' +
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
    const cosuniInput = fila.querySelector('input[name="cosuni[]"]');
    const subtotalInput = fila.querySelector('.subtotal');

    // Si el producto acaba de seleccionarse y el costo aún no fue editado, se sugiere su último costo
    const opcionSeleccionada = select.options[select.selectedIndex];
    if (elemento === select) {
        const costoSugerido = parseFloat(opcionSeleccionada.getAttribute('data-cosuni')) || 0;
        cosuniInput.value = costoSugerido.toFixed(4);
    }

    const cantidad = parseInt(cantInput.value) || 0;
    const cosuni = parseFloat(cosuniInput.value) || 0;

    subtotalInput.value = (cantidad * cosuni).toFixed(2);

    calcularTotales();
}

function calcularTotales() {
    let total = 0;
    document.querySelectorAll('#tablaProductos tbody .subtotal').forEach(function (input) {
        total += parseFloat(input.value) || 0;
    });
    document.getElementById('lblTotal').textContent = total.toFixed(2);
}

// Al cargar la página se agrega automáticamente la primera fila
document.addEventListener('DOMContentLoaded', function () {
    agregarFila();
});
</script>

</body>
</html>
