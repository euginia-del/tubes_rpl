-- =========================================
-- Laundry Service Database (MySQL / MariaDB)
-- =========================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_status_log;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================
-- USERS
-- =========================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer','worker','supervisor','admin') DEFAULT 'customer',
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =========================================
-- SERVICES
-- =========================================
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price_per_weight DECIMAL(10,2) NOT NULL DEFAULT 0,
    price_per_unit DECIMAL(10,2) NOT NULL DEFAULT 0,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================================
-- ORDERS
-- =========================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    category VARCHAR(100) NOT NULL,
    weight DECIMAL(10,2) NOT NULL DEFAULT 0,
    pickup_date DATE NOT NULL,
    pickup_time VARCHAR(50),
    address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status VARCHAR(50) DEFAULT 'pending',
    payment_proof VARCHAR(255),
    status VARCHAR(50) DEFAULT 'Pending Pickup',
    worker_id INT NULL,
    total DECIMAL(10,2) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (worker_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =========================================
-- PAYMENTS
-- =========================================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method VARCHAR(50) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    proof_file VARCHAR(255),
    verified_by INT NULL,
    verified_at TIMESTAMP NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =========================================
-- ORDER STATUS LOG
-- =========================================
CREATE TABLE order_status_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    changed_by INT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =========================================
-- INDEXES
-- =========================================
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_created_at ON orders(created_at);
CREATE INDEX idx_orders_payment_status ON orders(payment_status);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_services_category ON services(category);

-- =========================================
-- SEED USERS
-- =========================================
INSERT INTO users (name, email, password, role, phone, address) VALUES
('Alex', 'user@laundry.com', 'password123', 'customer', '08123456789', 'Jl. Sudirman No.1'),
('Worker', 'worker@laundry.com', 'workerpass', 'worker', '081234567810', 'Jl. Worker'),
('Supervisor', 'supervisor@laundry.com', 'supervisorpass', 'supervisor', '081234567811', 'Jl. Supervisor'),
('Admin', 'admin@laundry.com', 'adminpass', 'admin', '081234567812', 'Jl. Admin');

-- =========================================
-- SEED SERVICES
-- =========================================
INSERT INTO services (name, category, price_per_weight, price_per_unit, description) VALUES
('Cuci Kering', 'Cuci', 5000, 0, 'Cuci dan kering standar'),
('Setrika', 'Setrika', 0, 3000, 'Setrika per pcs'),
('Dry Clean', 'Dry Clean', 15000, 0, 'Cuci kering kimia'),
('Cuci Selimut', 'Cuci Besar', 25000, 0, 'Cuci selimut per kg'),
('Sepatu', 'Cuci Khusus', 0, 50000, 'Cuci sepatu per pasang');

-- =========================================
-- SEED ORDERS
-- =========================================
INSERT INTO orders (
    user_id, service_id, category, weight,
    pickup_date, pickup_time, address,
    payment_method, total, status, payment_status
) VALUES
(1, 1, 'Cuci', 2.5, '2026-04-15', '08:00-12:00', 'Jl. Sudirman No.1', 'transfer', 12500, 'In Progress', 'paid'),
(1, 2, 'Setrika', 10, '2026-04-16', '14:00-18:00', 'Jl. Sudirman No.1', 'cash', 30000, 'Pending Pickup', 'pending'),
(2, 3, 'Dry Clean', 1, '2026-04-14', '10:00-14:00', 'Jl. Worker', 'cash', 15000, 'Completed', 'verified');

-- =========================================
-- SEED PAYMENTS
-- =========================================
INSERT INTO payments (order_id, amount, method, status, proof_file) VALUES
(1, 12500, 'transfer', 'paid', 'uploads/proof1.jpg'),
(3, 15000, 'cash', 'verified', NULL);

-- =========================================
-- SEED LOGS
-- =========================================
INSERT INTO order_status_log (order_id, status, changed_by, notes) VALUES
(1, 'Pending Pickup', 1, 'Order created'),
(1, 'In Progress', 2, 'Assigned to worker'),
(3, 'Completed', 3, 'Supervisor approved');