-- ============================================================
-- Sistema de Facturación y Control de Stocks
-- Script de creación de base de datos y datos de prueba
-- Motor: MySQL / MariaDB (phpMyAdmin)
-- ============================================================

-- Se elimina la base si ya existe, para evitar conflictos con tablas
-- creadas en intentos anteriores (ej. error #1050 "la tabla ya existe").
DROP DATABASE IF EXISTS sistema_ventas;

CREATE DATABASE sistema_ventas
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE sistema_ventas;

-- ============================================================
-- 1. TABLAS MAESTRAS (sin dependencias)
-- ============================================================

CREATE TABLE Clientes (
    idcliente    VARCHAR(10)  NOT NULL,
    nomcliente   VARCHAR(128) NOT NULL,
    ruccliente   VARCHAR(11)  NULL,
    dircliente   VARCHAR(128) NULL,
    telcliente   VARCHAR(9)   NULL,
    emailcliente VARCHAR(64)  NULL,
    PRIMARY KEY (idcliente)
) ENGINE=InnoDB;

CREATE TABLE Usuarios (
    idusuario  VARCHAR(3)   NOT NULL,
    nomusuario VARCHAR(15)  NOT NULL,
    password   VARCHAR(255) NOT NULL,
    apellidos  VARCHAR(64)  NOT NULL,
    nombres    VARCHAR(64)  NOT NULL,
    email      VARCHAR(64)  NULL,
    estado     CHAR(1)      NOT NULL DEFAULT '1',
    PRIMARY KEY (idusuario),
    UNIQUE KEY uq_nomusuario (nomusuario)
) ENGINE=InnoDB;

CREATE TABLE CondicionVenta (
    idcondicion  CHAR(2)     NOT NULL,
    nomcondicion VARCHAR(64) NOT NULL,
    PRIMARY KEY (idcondicion)
) ENGINE=InnoDB;

CREATE TABLE Categorias (
    idcategoria  CHAR(2)      NOT NULL,
    nomcategoria VARCHAR(128) NOT NULL,
    PRIMARY KEY (idcategoria)
) ENGINE=InnoDB;

CREATE TABLE Proveedores (
    idproveedor    VARCHAR(3)   NOT NULL,
    nomproveedor   VARCHAR(128) NOT NULL,
    rucproveedor   VARCHAR(11)  NULL,
    dirproveedor   VARCHAR(128) NULL,
    telproveedor   VARCHAR(9)   NULL,
    emailproveedor VARCHAR(64)  NULL,
    PRIMARY KEY (idproveedor)
) ENGINE=InnoDB;

-- ============================================================
-- 2. TABLA PRODUCTOS (depende de Proveedores y Categorias)
-- ============================================================

CREATE TABLE Productos (
    idproducto  VARCHAR(10)     NOT NULL,
    idproveedor VARCHAR(3)      NOT NULL,
    nomproducto VARCHAR(128)    NOT NULL,
    unimed      VARCHAR(15)     NULL,
    stock       INT             NOT NULL DEFAULT 0,
    cosuni      DECIMAL(10,4)   NOT NULL DEFAULT 0,
    preuni      DECIMAL(10,4)   NOT NULL DEFAULT 0,
    idcategoria CHAR(2)         NOT NULL,
    estado      CHAR(1)         NOT NULL DEFAULT '1',
    PRIMARY KEY (idproducto),
    CONSTRAINT fk_producto_proveedor FOREIGN KEY (idproveedor) REFERENCES Proveedores(idproveedor),
    CONSTRAINT fk_producto_categoria FOREIGN KEY (idcategoria) REFERENCES Categorias(idcategoria)
) ENGINE=InnoDB;

-- ============================================================
-- 3. VENTAS (Facturas y DetalleFactura)
-- ============================================================

CREATE TABLE Facturas (
    idfactura   INT             NOT NULL AUTO_INCREMENT,
    fecha       DATE            NOT NULL,
    idcliente   VARCHAR(10)     NOT NULL,
    idusuario   VARCHAR(3)      NOT NULL,
    fechareg    DATETIME        NOT NULL,
    idcondicion CHAR(2)         NOT NULL,
    valorventa  DECIMAL(10,4)   NOT NULL DEFAULT 0,
    igv         DECIMAL(10,4)   NOT NULL DEFAULT 0,
    PRIMARY KEY (idfactura),
    CONSTRAINT fk_factura_cliente   FOREIGN KEY (idcliente)   REFERENCES Clientes(idcliente),
    CONSTRAINT fk_factura_usuario   FOREIGN KEY (idusuario)   REFERENCES Usuarios(idusuario),
    CONSTRAINT fk_factura_condicion FOREIGN KEY (idcondicion) REFERENCES CondicionVenta(idcondicion)
) ENGINE=InnoDB;

CREATE TABLE DetalleFactura (
    iddetalle  INT             NOT NULL AUTO_INCREMENT,
    idfactura  INT             NOT NULL,
    idproducto VARCHAR(10)     NOT NULL,
    cant       INT             NOT NULL,
    cosuni     DECIMAL(19,4)   NOT NULL,
    preuni     DECIMAL(10,4)   NOT NULL,
    PRIMARY KEY (iddetalle),
    CONSTRAINT fk_detfact_factura  FOREIGN KEY (idfactura)  REFERENCES Facturas(idfactura),
    CONSTRAINT fk_detfact_producto FOREIGN KEY (idproducto) REFERENCES Productos(idproducto)
) ENGINE=InnoDB;

-- ============================================================
-- 4. COMPRAS (compras y detallecompras)
-- ============================================================

CREATE TABLE compras (
    idcompra    INT             NOT NULL AUTO_INCREMENT,
    fecha       DATE            NOT NULL,
    idproveedor VARCHAR(3)      NOT NULL,
    idusuario   VARCHAR(3)      NOT NULL,
    total       DECIMAL(10,4)   NOT NULL DEFAULT 0,
    fechareg    DATETIME        NOT NULL,
    PRIMARY KEY (idcompra),
    CONSTRAINT fk_compra_proveedor FOREIGN KEY (idproveedor) REFERENCES Proveedores(idproveedor),
    CONSTRAINT fk_compra_usuario   FOREIGN KEY (idusuario)   REFERENCES Usuarios(idusuario)
) ENGINE=InnoDB;

CREATE TABLE detallecompras (
    iddetallecompra INT             NOT NULL AUTO_INCREMENT,
    idcompra        INT             NOT NULL,
    idproducto      VARCHAR(10)     NOT NULL,
    cant            INT             NOT NULL,
    cosuni          DECIMAL(10,4)   NOT NULL,
    PRIMARY KEY (iddetallecompra),
    CONSTRAINT fk_detcompra_compra   FOREIGN KEY (idcompra)   REFERENCES compras(idcompra),
    CONSTRAINT fk_detcompra_producto FOREIGN KEY (idproducto) REFERENCES Productos(idproducto)
) ENGINE=InnoDB;

-- ============================================================
-- 5. DATOS DE PRUEBA
-- ============================================================

-- Usuario de acceso al sistema
-- Usuario:    admin
-- Contraseña: admin123   (ya almacenada como hash bcrypt, compatible con password_verify())
INSERT INTO Usuarios (idusuario, nomusuario, password, apellidos, nombres, email, estado) VALUES
('001', 'admin', '$2b$10$rI1X.I7egLuHRa2.NHtzMujIs3/8rKbe1mQoo9MC2UPmqUc/sFxtm', 'Pérez Quispe', 'Gerardo', 'admin@ipsdi.edu.pe', '1');

-- Condiciones de venta
INSERT INTO CondicionVenta (idcondicion, nomcondicion) VALUES
('01', 'Contado'),
('02', 'Crédito');

-- Categorías
INSERT INTO Categorias (idcategoria, nomcategoria) VALUES
('01', 'Abarrotes'),
('02', 'Bebidas'),
('03', 'Limpieza'),
('04', 'Cuidado Personal');

-- Proveedores
INSERT INTO Proveedores (idproveedor, nomproveedor, rucproveedor, dirproveedor, telproveedor, emailproveedor) VALUES
('001', 'Distribuidora Arequipa S.A.C.', '20456789123', 'Av. Ejército 456, Arequipa', '959111222', 'ventas@distarequipa.pe'),
('002', 'Comercial Sur E.I.R.L.', '20498712345', 'Calle Mercaderes 210, Arequipa', '959333444', 'contacto@comercialsur.pe');

-- Clientes
INSERT INTO Clientes (idcliente, nomcliente, ruccliente, dircliente, telcliente, emailcliente) VALUES
('0000001', 'Cliente Varios', NULL, 'Arequipa', NULL, NULL),
('0000002', 'María Fernanda Rojas', NULL, 'Cayma, Arequipa', '959555666', 'mfrojas@gmail.com');

-- Productos (con stock inicial para poder probar registrar_venta.php)
INSERT INTO Productos (idproducto, idproveedor, nomproducto, unimed, stock, cosuni, preuni, idcategoria, estado) VALUES
('P0000001', '001', 'Arroz Extra 5kg', 'BOL', 50, 12.5000, 16.9000, '01', '1'),
('P0000002', '001', 'Aceite Vegetal 1L', 'BOT', 40, 8.0000, 11.5000, '01', '1'),
('P0000003', '002', 'Gaseosa 1.5L', 'BOT', 60, 3.5000, 5.5000, '02', '1'),
('P0000004', '002', 'Detergente 1kg', 'BOL', 30, 6.0000, 9.9000, '03', '1');
