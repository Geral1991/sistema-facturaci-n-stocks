#Sistema de Facturación y Control de Stocks

Proyecto final del curso, desarrollado en PHP nativo procedimental, MySQL (vía phpMyAdmin) y CSS puro embebido, sin frameworks ni librerías externas, siguiendo los lineamientos exigidos por el docente.

Integrantes del equipo

Ver archivo integrantes.txt en la raíz del proyecto.

Descripción del proyecto

El sistema permite gestionar el inventario y la facturación de un negocio: mantenimiento de datos maestros (productos, clientes, proveedores, categorías, usuarios), registro de ventas y compras con actualización automática de stock, y consultas de reportes (stock actual y ranking de productos más vendidos).

Objetivos
Objetivo general: desarrollar un sistema web de facturación y control de stocks usando PHP nativo y MySQL, sin frameworks.
Objetivos específicos:
Gestionar productos, clientes, proveedores, categorías y usuarios (CRUD completo).
Registrar ventas y compras, actualizando automáticamente el stock de productos.
Implementar seguridad nativa contra XSS y CSRF en todos los formularios.
Generar reportes de stock y ranking de ventas.
Tecnologías utilizadas
Componente	Tecnología
Backend	PHP 8.2 nativo (sin frameworks, sin Composer)
Base de datos	MySQL / MariaDB (phpMyAdmin)
Frontend	HTML5 + CSS puro embebido (sin Bootstrap/Tailwind)
Servidor local	Apache (XAMPP)
Control de versiones	Git / GitHub
Estructura del proyecto
sistema_ventas/
│
├── integrantes.txt          <- Nombres del equipo (requerido por indicaciones)
├── README.md                <- Este archivo
│
├── sql/
│   └── sistema_ventas_db.sql   <- Script de creación de BD + datos de prueba
│
├── conexion/
│   ├── conexion.php          <- Conexión mysqli a la base de datos
│   └── funciones.php         <- Funciones de seguridad: escapar(), csrf(), validarSesion()
│
├── index.php                 <- Login
├── validar_login.php         <- Procesa el login
├── logout.php                <- Cierra sesión
├── dashboard.php              <- Panel principal con menú ARCHIVOS / PROCESOS / CONSULTAS
│
├── archivos/                 <- Mantenimiento (CRUD) de datos maestros
│   ├── productos.php | nuevo_producto.php | guardar_producto.php | modificar_producto.php | actualizar_producto.php | eliminar_producto.php
│   ├── clientes.php | nuevo_cliente.php | guardar_cliente.php | modificar_cliente.php | actualizar_cliente.php | eliminar_cliente.php
│   ├── proveedores.php | nuevo_proveedor.php | guardar_proveedor.php | modificar_proveedor.php | actualizar_proveedor.php | eliminar_proveedor.php
│   ├── categorias.php | nuevo_categoria.php | guardar_categoria.php | modificar_categoria.php | actualizar_categoria.php | eliminar_categoria.php
│   ├── usuarios.php | nuevo_usuario.php | guardar_usuario.php | modificar_usuario.php | actualizar_usuario.php | eliminar_usuario.php
│   └── confirmar_eliminar.php   <- Confirmación de borrado, genérica y reutilizable
│
├── procesos/
│   ├── registrar_venta.php    <- Resta stock automáticamente al vender
│   └── registrar_compra.php   <- Suma stock automáticamente al comprar
│
└── consultas/
    ├── stock_productos.php    <- Reporte de stock con alerta de stock bajo
    └── ranking_ventas.php     <- Top productos más vendidos (con filtro por fechas)
Proceso de desarrollo

El desarrollo se organizó en las siguientes etapas:

Diseño de la base de datos. Se definieron 10 tablas (Clientes, Usuarios, CondicionVenta, Categorias, Proveedores, Productos, Facturas, DetalleFactura, compras, detallecompras) respetando los nombres, tipos y llaves foráneas especificados por el docente. El script se encuentra en sql/sistema_ventas_db.sql e incluye datos de prueba (un usuario, proveedores, clientes, categorías y productos con stock inicial).
Núcleo de seguridad (conexion/funciones.php). Antes de construir cualquier módulo se implementaron las funciones base reutilizadas en todo el sistema:
escapar(): sanitiza toda salida hacia HTML con htmlspecialchars(), previniendo XSS.
csrf() / validarCsrf(): genera un token por sesión con random_bytes() y lo valida con hash_equals() en cada formulario POST, deteniendo la ejecución con die() si no coincide.
validarSesion(): protege cada script para que solo usuarios autenticados puedan acceder.
Módulo de acceso. index.php (login), validar_login.php (valida credenciales con consultas preparadas y password_verify()), logout.php y dashboard.php (menú de navegación).
Procesos transaccionales. registrar_venta.php y registrar_compra.php usan transacciones (mysqli_begin_transaction / commit / rollback) para garantizar que la inserción de la cabecera, el detalle y la actualización de stock ocurran de forma atómica. Se valida el stock disponible antes de confeccionar una venta.
Mantenimiento de datos maestros (archivos/). Se construyó un CRUD completo para cada entidad (listar, crear, modificar, eliminar), con una página de confirmación de eliminación genérica (confirmar_eliminar.php) que reutiliza la misma lógica para las 5 entidades. Los borrados que violan una llave foránea (por ejemplo, eliminar un proveedor con productos asociados) se capturan y se informan con un mensaje claro en vez de un error de MySQL.
Reportes (consultas/). Consultas SQL de agregación (SUM, GROUP BY, JOIN) para el stock actual y el ranking de productos más vendidos.
Pruebas manuales. Se verificó: registro de venta con stock insuficiente (debe rechazarse), actualización de stock tras venta/compra, intento de eliminar registros referenciados por llave foránea, y validación de los tokens CSRF.
Medidas de seguridad implementadas
XSS: toda variable proveniente de la base de datos o del usuario se imprime a través de escapar().
CSRF: todo formulario POST incluye un token de sesión validado con hash_equals().
Inyección SQL: todas las consultas usan sentencias preparadas (mysqli_prepare + mysqli_stmt_bind_param).
Contraseñas: se almacenan hasheadas con password_hash() (bcrypt) y se verifican con password_verify().
Sesiones: session_regenerate_id() tras un login exitoso, para prevenir fijación de sesión.
Instalación y ejecución local (XAMPP)
Copiar la carpeta sistema_ventas/ dentro de C:\xampp\htdocs\.
Importar sql/sistema_ventas_db.sql desde phpMyAdmin (crea la base de datos sistema_ventas completa, con datos de prueba).
Iniciar Apache y MySQL desde el Panel de Control de XAMPP.
Abrir http://localhost/sistema_ventas/index.php.
Iniciar sesión con el usuario de prueba:
Usuario: admin
Contraseña: admin123
Licencia

Proyecto académico desarrollado con fines educativos.
