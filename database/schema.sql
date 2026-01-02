-- ============================================
-- ONE CHEF PIZZA - Database Schema
-- ============================================

CREATE DATABASE IF NOT EXISTS onechef_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE onechef_db;

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(50),
    description TEXT,
    priority INT DEFAULT 100,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    category_id INT NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    old_price DECIMAL(10,2),
    photo VARCHAR(500),
    weight VARCHAR(50),
    prep_time INT DEFAULT 30 COMMENT 'Preparation time in minutes',
    is_new BOOLEAN DEFAULT FALSE,
    is_popular BOOLEAN DEFAULT FALSE,
    active BOOLEAN DEFAULT TRUE,
    pos_id VARCHAR(100),
    priority INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Topping groups
CREATE TABLE topping_groups (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('single', 'multiple', 'required') DEFAULT 'multiple',
    min_selection INT DEFAULT 0,
    max_selection INT DEFAULT 1,
    category_id INT,
    priority INT DEFAULT 100,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Toppings
CREATE TABLE toppings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    weight VARCHAR(50),
    active BOOLEAN DEFAULT TRUE,
    pos_id VARCHAR(100),
    topping_group_id INT,
    priority INT DEFAULT 100,
    FOREIGN KEY (topping_group_id) REFERENCES topping_groups(id) ON DELETE CASCADE
);

-- Product-topping group relations
CREATE TABLE product_topping_groups (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    topping_group_id INT NOT NULL,
    required BOOLEAN DEFAULT FALSE,
    UNIQUE KEY unique_product_group (product_id, topping_group_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (topping_group_id) REFERENCES topping_groups(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    type ENUM('delivery', 'pickup') NOT NULL DEFAULT 'delivery',
    address TEXT,
    total DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('new', 'confirmed', 'cooking', 'ready', 'delivering', 'completed', 'canceled') DEFAULT 'new',
    payment_method ENUM('cash', 'card', 'online') DEFAULT 'cash',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    comment TEXT,
    preparation_time DATETIME,
    source ENUM('website', 'phone', 'instagram') DEFAULT 'website',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Order items
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(200) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    toppings JSON,
    comment TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Customers
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    phone VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100),
    email VARCHAR(100),
    address TEXT,
    loyalty_points INT DEFAULT 0,
    total_orders INT DEFAULT 0,
    total_spent DECIMAL(10,2) DEFAULT 0.00,
    last_order_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample data
INSERT INTO categories (name, slug, icon, priority) VALUES
('Піца', 'pizza', '🍕', 10),
('Суші', 'sushi', '🍣', 20),
('Бургери', 'burgers', '🍔', 30),
('Салати', 'salads', '🥗', 40),
('Напої', 'drinks', '🥤', 50),
('Закуски', 'snacks', '🍟', 60);

INSERT INTO products (name, category_id, price, description, weight, prep_time) VALUES
('Маргарита', 1, 189, 'Класична піца з томатним соусом та моцарелою', '500г', 20),
('Пепероні', 1, 219, 'Піца з салямі пепероні та сиром', '550г', 20),
('Філадельфія', 2, 249, 'Рол з лососем та сиром філадельфія', '250г', 15),
('Чізбургер', 3, 129, 'Бургер з яловичиною та сиром', '350г', 10),
('Кола', 5, 45, 'Напій Coca-Cola 0.5л', '500мл', 2);

-- Indexes for performance
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_active ON products(active);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_phone ON orders(phone);
CREATE INDEX idx_orders_created ON orders(created_at);

-- Trigger for order number
DELIMITER //
CREATE TRIGGER before_order_insert
BEFORE INSERT ON orders
FOR EACH ROW
BEGIN
    DECLARE next_num INT;
    DECLARE order_prefix VARCHAR(10);
    
    SET order_prefix = DATE_FORMAT(NOW(), '%y%m%d');
    
    SELECT COALESCE(MAX(SUBSTRING(order_number, 7)), 0) + 1 INTO next_num
    FROM orders 
    WHERE order_number LIKE CONCAT(order_prefix, '%');
    
    SET NEW.order_number = CONCAT(order_prefix, LPAD(next_num, 4, '0'));
END//
DELIMITER ;