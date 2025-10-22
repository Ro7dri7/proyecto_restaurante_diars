CREATE DATABASE restaurante_ventas_db;
USE restaurante_ventas_db;

-- -----------------------------------------------------
-- Tabla: cliente
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS cliente (
  idCliente INT NOT NULL AUTO_INCREMENT,
  nombreCliente VARCHAR(100) NOT NULL,
  telefonoCliente VARCHAR(20) NULL,
  emailCliente VARCHAR(100) NULL UNIQUE,
  PRIMARY KEY (idCliente)
);

-- -----------------------------------------------------
-- Tabla: producto
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS producto (
  idProducto INT NOT NULL AUTO_INCREMENT,
  nombreProducto VARCHAR(100) NOT NULL,
  descripcionProducto TEXT NULL,
  precioProducto DECIMAL(10,2) NOT NULL,
  estadoProducto VARCHAR(20) NOT NULL DEFAULT 'Activo',
  PRIMARY KEY (idProducto)
);

-- -----------------------------------------------------
-- Tabla: empleado
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS empleado (
  idEmpleado INT NOT NULL AUTO_INCREMENT,
  nombreEmpleado VARCHAR(100) NOT NULL,
  cargoEmpleado VARCHAR(50) NOT NULL,
  PRIMARY KEY (idEmpleado)
);

-- -----------------------------------------------------
-- Tabla: pedido
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS pedido (
  idPedido INT NOT NULL AUTO_INCREMENT,
  idCliente INT NOT NULL,
  idEmpleado INT NOT NULL,
  fechaHoraToma DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fechaHoraEntrega DATETIME NULL,
  estadoPedido VARCHAR(50) NOT NULL DEFAULT 'Registrado',
  subtotalGeneral DECIMAL(10,2) NOT NULL,
  igv DECIMAL(10,2) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (idPedido),
  FOREIGN KEY (idCliente) REFERENCES cliente (idCliente),
  FOREIGN KEY (idEmpleado) REFERENCES empleado (idEmpleado)
);

-- -----------------------------------------------------
-- Tabla: detalle_pedido
-- (Uso "detalle_pedido" como nombre de tabla,
-- pero las columnas son camelCase como pediste)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS detalle_pedido (
  idDetallePedido INT NOT NULL AUTO_INCREMENT,
  idPedido INT NOT NULL,
  idProducto INT NOT NULL,
  cantidad INT NOT NULL,
  precioUnitario DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (idDetallePedido),
  FOREIGN KEY (idPedido) REFERENCES pedido (idPedido),
  FOREIGN KEY (idProducto) REFERENCES producto (idProducto)
);

-- -----------------------------------------------------
-- Tabla: pago
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS pago (
  idPago INT NOT NULL AUTO_INCREMENT,
  idPedido INT NOT NULL,
  idCliente INT NOT NULL,
  fechaHora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  monto DECIMAL(10,2) NOT NULL,
  metodoPago VARCHAR(50) NOT NULL,
  estado VARCHAR(20) NOT NULL DEFAULT 'Pendiente',
  notificadoPago BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (idPago),
  FOREIGN KEY (idPedido) REFERENCES pedido (idPedido),
  FOREIGN KEY (idCliente) REFERENCES cliente (idCliente)
);

-- -----------------------------------------------------
-- Tabla: reclamo
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS reclamo (
  idReclamo INT NOT NULL AUTO_INCREMENT,
  idPedido INT NOT NULL,
  idCliente INT NOT NULL,
  idEmpleado INT NULL,
  fechaSolicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fechaResolucion DATETIME NULL,
  motivo TEXT NOT NULL,
  montoSeleccionado DECIMAL(10,2) NULL,
  productoAfectado VARCHAR(100) NULL,
  estadoReclamo VARCHAR(20) NOT NULL DEFAULT 'Solicitado',
  comentarioResolucion TEXT NULL,
  metodoDevolucion VARCHAR(50) NULL,
  notificadoReclamo BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (idReclamo),
  FOREIGN KEY (idPedido) REFERENCES pedido (idPedido),
  FOREIGN KEY (idCliente) REFERENCES cliente (idCliente),
  FOREIGN KEY (idEmpleado) REFERENCES empleado (idEmpleado)
);