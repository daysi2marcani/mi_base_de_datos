 -- FinPlan AI - Base de datos para XAMPP
-- Ejecutar en phpMyAdmin o MySQL CLI

CREATE DATABASE IF NOT EXISTS finplan_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE finplan_ai;

-- Usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categorías de transacciones
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    tipo ENUM('ingreso', 'gasto') NOT NULL,
    icono VARCHAR(30) DEFAULT 'fa-tag',
    color VARCHAR(7) DEFAULT '#6366f1'
);

-- Transacciones (ingresos y gastos)
CREATE TABLE IF NOT EXISTS transacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    categoria_id INT NOT NULL,
    monto DECIMAL(12,2) NOT NULL,
    descripcion VARCHAR(255),
    fecha DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- Presupuestos sugeridos / asignados
CREATE TABLE IF NOT EXISTS presupuestos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    categoria_id INT NOT NULL,
    monto_limite DECIMAL(12,2) NOT NULL,
    mes INT NOT NULL,
    anio INT NOT NULL,
    sugerido_ml TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    UNIQUE KEY unique_presupuesto (usuario_id, categoria_id, mes, anio)
);

-- Metas de ahorro
CREATE TABLE IF NOT EXISTS metas_ahorro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    monto_objetivo DECIMAL(12,2) NOT NULL,
    monto_actual DECIMAL(12,2) DEFAULT 0,
    fecha_limite DATE,
    prioridad ENUM('baja', 'media', 'alta') DEFAULT 'media',
    sugerido_ml TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Historial de análisis ML
CREATE TABLE IF NOT EXISTS analisis_ml (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_analisis VARCHAR(50) NOT NULL,
    resultado JSON,
    confianza DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Datos de demostración
INSERT INTO categorias (nombre, tipo, icono, color) VALUES
('Salario', 'ingreso', 'fa-briefcase', '#10b981'),
('Freelance', 'ingreso', 'fa-laptop', '#06b6d4'),
('Inversiones', 'ingreso', 'fa-chart-line', '#8b5cf6'),
('Alimentación', 'gasto', 'fa-utensils', '#f59e0b'),
('Transporte', 'gasto', 'fa-car', '#3b82f6'),
('Vivienda', 'gasto', 'fa-home', '#ef4444'),
('Entretenimiento', 'gasto', 'fa-gamepad', '#ec4899'),
('Salud', 'gasto', 'fa-heart-pulse', '#14b8a6'),
('Educación', 'gasto', 'fa-graduation-cap', '#a855f7'),
('Servicios', 'gasto', 'fa-bolt', '#64748b'),
('Ropa', 'gasto', 'fa-shirt', '#f97316'),
('Otros', 'gasto', 'fa-ellipsis', '#94a3b8');

-- Usuario demo (password se configura en install.php: demo123)
INSERT INTO usuarios (nombre, email, password) VALUES
('Usuario Demo', 'demo@finplan.com', '$2y$10$placeholder');

-- Transacciones demo para el usuario 1 (últimos 6 meses)
INSERT INTO transacciones (usuario_id, categoria_id, monto, descripcion, fecha) VALUES
-- Ingresos
(1, 1, 8500.00, 'Salario mensual', '2025-01-15'),
(1, 1, 8500.00, 'Salario mensual', '2025-02-15'),
(1, 1, 8500.00, 'Salario mensual', '2025-03-15'),
(1, 1, 8700.00, 'Salario mensual', '2025-04-15'),
(1, 1, 8700.00, 'Salario mensual', '2025-05-15'),
(1, 1, 8700.00, 'Salario mensual', '2025-06-01'),
(1, 2, 1200.00, 'Proyecto web', '2025-03-20'),
(1, 2, 800.00, 'Consultoría', '2025-05-10'),
-- Gastos
(1, 4, 1200.00, 'Supermercado', '2025-01-05'),
(1, 4, 1350.00, 'Supermercado', '2025-02-05'),
(1, 4, 1280.00, 'Supermercado', '2025-03-05'),
(1, 4, 1400.00, 'Supermercado', '2025-04-05'),
(1, 4, 1320.00, 'Supermercado', '2025-05-05'),
(1, 4, 1450.00, 'Supermercado', '2025-06-05'),
(1, 5, 350.00, 'Gasolina y transporte', '2025-01-10'),
(1, 5, 380.00, 'Gasolina y transporte', '2025-02-10'),
(1, 5, 360.00, 'Gasolina y transporte', '2025-03-10'),
(1, 5, 400.00, 'Gasolina y transporte', '2025-04-10'),
(1, 5, 370.00, 'Gasolina y transporte', '2025-05-10'),
(1, 5, 390.00, 'Gasolina y transporte', '2025-06-10'),
(1, 6, 2500.00, 'Alquiler', '2025-01-01'),
(1, 6, 2500.00, 'Alquiler', '2025-02-01'),
(1, 6, 2500.00, 'Alquiler', '2025-03-01'),
(1, 6, 2500.00, 'Alquiler', '2025-04-01'),
(1, 6, 2500.00, 'Alquiler', '2025-05-01'),
(1, 6, 2500.00, 'Alquiler', '2025-06-01'),
(1, 7, 200.00, 'Streaming y ocio', '2025-01-20'),
(1, 7, 250.00, 'Cine y salidas', '2025-02-20'),
(1, 7, 180.00, 'Streaming', '2025-03-20'),
(1, 7, 300.00, 'Salidas', '2025-04-20'),
(1, 7, 220.00, 'Ocio', '2025-05-20'),
(1, 8, 150.00, 'Farmacia', '2025-02-15'),
(1, 8, 200.00, 'Consulta médica', '2025-04-15'),
(1, 9, 500.00, 'Curso online', '2025-03-01'),
(1, 10, 180.00, 'Luz y agua', '2025-01-25'),
(1, 10, 195.00, 'Luz y agua', '2025-02-25'),
(1, 10, 210.00, 'Luz y agua', '2025-03-25'),
(1, 10, 185.00, 'Luz y agua', '2025-04-25'),
(1, 10, 200.00, 'Luz y agua', '2025-05-25'),
(1, 11, 400.00, 'Ropa', '2025-02-28'),
(1, 11, 350.00, 'Calzado', '2025-05-15');

-- Meta de ahorro demo
INSERT INTO metas_ahorro (usuario_id, nombre, monto_objetivo, monto_actual, fecha_limite, prioridad) VALUES
(1, 'Fondo de emergencia', 15000.00, 5200.00, '2025-12-31', 'alta'),
(1, 'Vacaciones', 5000.00, 1800.00, '2025-08-15', 'media');
