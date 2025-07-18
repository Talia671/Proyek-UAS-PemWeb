-- ========================================
-- Shoe Store Database - Complete Schema
-- ========================================
-- This script creates the complete database schema for the shoe store e-commerce application
-- including all tables, indexes, foreign keys, and initial data

-- Drop and create database
DROP DATABASE IF EXISTS shoe_store;
CREATE DATABASE shoe_store;
USE shoe_store;

-- ========================================
-- CORE TABLES
-- ========================================

-- Roles table
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT DEFAULT 2,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    category_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Product Images table
CREATE TABLE product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Carts table
CREATE TABLE carts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- ========================================
-- ORDER MANAGEMENT TABLES
-- ========================================

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    zip VARCHAR(20) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    courier_note TEXT,
    payment_method VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order Items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ========================================
-- SYSTEM TABLES
-- ========================================

-- Activity Logs table
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    activity TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Settings table
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    value TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- INDEXES FOR PERFORMANCE
-- ========================================

-- Users indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role_id ON users(role_id);

-- Products indexes
CREATE INDEX idx_products_category_id ON products(category_id);
CREATE INDEX idx_products_name ON products(name);
CREATE INDEX idx_products_price ON products(price);

-- Product Images indexes
CREATE INDEX idx_product_images_product_id ON product_images(product_id);
CREATE INDEX idx_product_images_is_primary ON product_images(is_primary);

-- Carts indexes
CREATE INDEX idx_carts_user_id ON carts(user_id);
CREATE INDEX idx_carts_product_id ON carts(product_id);

-- Orders indexes
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_order_number ON orders(order_number);
CREATE INDEX idx_orders_created_at ON orders(created_at);

-- Order Items indexes
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_order_items_product_id ON order_items(product_id);

-- Activity Logs indexes
CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at);

-- ========================================
-- INITIAL DATA
-- ========================================

-- Insert roles
INSERT INTO roles (name) VALUES 
('Admin'), 
('Customer');

-- Insert categories
INSERT INTO categories (name, description) VALUES 
('Sepatu Sneakers', 'Koleksi sepatu sneakers branded untuk gaya kasual dan olahraga'),
('Sepatu Formal', 'Sepatu formal untuk acara resmi dan pekerjaan'),
('Sandal Casual', 'Sandal santai untuk penggunaan sehari-hari'),
('Sandal Sport', 'Sandal olahraga untuk aktivitas outdoor'),
('Sepatu Olahraga', 'Sepatu khusus untuk berbagai jenis olahraga');

-- Insert system settings
INSERT INTO settings (name, value) VALUES 
('store_name', 'ShoeBrand Store'),
('store_logo', 'assets/img/logo.png'),
('store_contact', '+62 812-3456-7890'),
('store_email', 'info@shoebrand.com'),
('store_address', 'Jl. Fashion Street No. 123, Jakarta'),
('currency', 'IDR'),
('tax_rate', '11'),
('shipping_fee', '25000');

-- Insert admin user (password: admin123)
INSERT INTO users (name, email, password, role_id) VALUES 
('Administrator', 'admin@shoebrand.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insert sample customer user (password: customer123)
INSERT INTO users (name, email, password, role_id) VALUES 
('John Doe', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2);

-- Insert sample products
INSERT INTO products (name, description, price, stock, category_id) VALUES 
('Nike Air Max 270', 'Sepatu sneakers premium dengan teknologi Air Max terbaru untuk kenyamanan maksimal sepanjang hari', 1299000, 50, 1),
('Adidas Ultraboost 22', 'Sepatu lari dengan teknologi Boost untuk kenyamanan maksimal dan performa terbaik', 2199000, 30, 5),
('Converse Chuck Taylor', 'Sepatu klasik yang timeless dan stylish, cocok untuk berbagai kesempatan', 899000, 75, 1),
('Clarks Desert Boot', 'Sepatu formal casual yang elegan dengan bahan kulit premium', 1599000, 25, 2),
('Birkenstock Arizona', 'Sandal premium dengan footbed anatomis untuk kenyamanan kaki sepanjang hari', 1099000, 40, 3),
('New Balance 990v5', 'Sepatu sneakers dengan teknologi ENCAP untuk stabilitas dan kenyamanan', 1799000, 35, 1),
('Vans Old Skool', 'Sepatu skate klasik dengan desain ikonik dan daya tahan tinggi', 749000, 60, 1),
('Dr. Martens 1460', 'Sepatu boots formal dengan sol yang tahan lama dan desain klasik', 2299000, 20, 2),
('Havaianas Slim', 'Sandal Brazil yang ringan dan nyaman untuk pantai dan santai', 299000, 100, 3),
('Teva Hurricane XLT2', 'Sandal sport dengan tali yang dapat disesuaikan untuk aktivitas outdoor', 899000, 45, 4);

-- Insert sample product images
INSERT INTO product_images (product_id, image_url, is_primary) VALUES 
(1, 'assets/img/products/nike-air-max-1.jpg', TRUE),
(1, 'assets/img/products/nike-air-max-2.jpg', FALSE),
(1, 'assets/img/products/nike-air-max-3.jpg', FALSE),
(2, 'assets/img/products/adidas-ultraboost-1.jpg', TRUE),
(2, 'assets/img/products/adidas-ultraboost-2.jpg', FALSE),
(3, 'assets/img/products/converse-chuck-1.jpg', TRUE),
(3, 'assets/img/products/converse-chuck-2.jpg', FALSE),
(4, 'assets/img/products/clarks-desert-1.jpg', TRUE),
(4, 'assets/img/products/clarks-desert-2.jpg', FALSE),
(5, 'assets/img/products/birkenstock-1.jpg', TRUE),
(5, 'assets/img/products/birkenstock-2.jpg', FALSE),
(6, 'assets/img/products/newbalance-990-1.jpg', TRUE),
(7, 'assets/img/products/vans-oldskool-1.jpg', TRUE),
(8, 'assets/img/products/drmartens-1460-1.jpg', TRUE),
(9, 'assets/img/products/havaianas-slim-1.jpg', TRUE),
(10, 'assets/img/products/teva-hurricane-1.jpg', TRUE);

-- Insert sample activity logs
INSERT INTO activity_logs (user_id, activity) VALUES 
(1, 'Administrator logged in'),
(1, 'Created new product: Nike Air Max 270'),
(1, 'Updated product inventory'),
(2, 'Customer registered'),
(2, 'Customer logged in');

-- ========================================
-- COMPLETION MESSAGE
-- ========================================

SELECT 'Database shoe_store created successfully with all tables, indexes, and initial data!' as message;
