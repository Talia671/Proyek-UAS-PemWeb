-- Database untuk E-commerce Sepatu & Sandal Branded
-- Sesuai dengan ERD yang diberikan

CREATE DATABASE IF NOT EXISTS shoe_store;
USE shoe_store;

-- Tabel Users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT DEFAULT 2,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Roles
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL
);

-- Tabel Categories
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Products
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    category_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Tabel Product Images
CREATE TABLE product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    image_url VARCHAR(500),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Tabel Carts
CREATE TABLE carts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Tabel Transactions
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total_amount DECIMAL(10,2),
    shipping_address TEXT,
    status VARCHAR(50) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabel Transaction Items
CREATE TABLE transaction_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id INT,
    product_id INT,
    quantity INT,
    price DECIMAL(10,2),
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Tabel Activity Logs
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    activity TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabel Settings
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    value TEXT
);

-- Insert data awal
INSERT INTO roles (name) VALUES ('Admin'), ('Customer');

INSERT INTO categories (name) VALUES 
('Sepatu Sneakers'), 
('Sepatu Formal'), 
('Sandal Casual'), 
('Sandal Sport'),
('Sepatu Olahraga');

INSERT INTO settings (name, value) VALUES 
('store_name', 'ShoeBrand Store'),
('store_logo', 'assets/img/logo.png'),
('store_contact', '+62 812-3456-7890'),
('store_email', 'info@shoebrand.com'),
('store_address', 'Jl. Fashion Street No. 123, Jakarta');

-- Insert admin user (password: admin123)
INSERT INTO users (name, email, password, role_id) VALUES 
('Administrator', 'admin@shoebrand.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Insert sample products
INSERT INTO products (name, description, price, stock, category_id) VALUES 
('Nike Air Max 270', 'Sepatu sneakers premium dengan teknologi Air Max terbaru', 1299000, 50, 1),
('Adidas Ultraboost 22', 'Sepatu lari dengan teknologi Boost untuk kenyamanan maksimal', 2199000, 30, 5),
('Converse Chuck Taylor', 'Sepatu klasik yang timeless dan stylish', 899000, 75, 1),
('Clarks Desert Boot', 'Sepatu formal casual yang elegan', 1599000, 25, 2),
('Birkenstock Arizona', 'Sandal premium dengan footbed anatomis', 1099000, 40, 3);

-- Insert sample product images
INSERT INTO product_images (product_id, image_url) VALUES 
(1, 'assets/img/products/nike-air-max-1.jpg'),
(1, 'assets/img/products/nike-air-max-2.jpg'),
(2, 'assets/img/products/adidas-ultraboost-1.jpg'),
(2, 'assets/img/products/adidas-ultraboost-2.jpg'),
(3, 'assets/img/products/converse-chuck-1.jpg'),
(4, 'assets/img/products/clarks-desert-1.jpg'),
(5, 'assets/img/products/birkenstock-1.jpg');