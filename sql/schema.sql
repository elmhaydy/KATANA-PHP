-- ===========================
-- Mini e-Commerce (Projet 7)
-- schema.sql (MySQL)
-- ===========================

DROP DATABASE IF EXISTS mini_ecommerce;
CREATE DATABASE mini_ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mini_ecommerce;

-- ---------------------------
-- USERS
-- ---------------------------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('ROLE_USER','ROLE_ADMIN') NOT NULL DEFAULT 'ROLE_USER',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------
-- PRODUCTS
-- ---------------------------
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  description TEXT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  stock INT NOT NULL DEFAULT 0,
  image VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CHECK (stock >= 0),
  CHECK (price >= 0)
) ENGINE=InnoDB;

-- ---------------------------
-- ORDERS
-- ---------------------------
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status ENUM('PENDING','CONFIRMED','SHIPPED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CHECK (total >= 0)
) ENGINE=InnoDB;

-- ---------------------------
-- ORDER ITEMS
-- ---------------------------
CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  line_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  CONSTRAINT fk_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_items_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CHECK (quantity > 0),
  CHECK (unit_price >= 0),
  CHECK (line_total >= 0)
) ENGINE=InnoDB;

-- ===========================
-- DONNÉES DE DÉMO
-- ===========================

-- ⚠️ Mot de passe démo : "test1234"
-- Pour aller vite, on met un hash compatible password_hash() (bcrypt).
-- Si tu préfères, je peux te donner un petit script PHP pour générer ton hash.

INSERT INTO users (email, password_hash, role) VALUES
('admin@katana.test', '$2y$10$wH9mBqI2fV3yJx3gXQ3eOeQm8H5YBv1k9qj9b4P3o0j0mJm2pQW9S', 'ROLE_ADMIN'),
('user1@katana.test', '$2y$10$wH9mBqI2fV3yJx3gXQ3eOeQm8H5YBv1k9qj9b4P3o0j0mJm2pQW9S', 'ROLE_USER'),
('user2@katana.test', '$2y$10$wH9mBqI2fV3yJx3gXQ3eOeQm8H5YBv1k9qj9b4P3o0j0mJm2pQW9S', 'ROLE_USER');

-- Produits démo (simple)
INSERT INTO products (name, description, price, stock, image) VALUES
('Katana Akai', 'Katana rouge, tranchant premium.', 1299.00, 5, NULL),
('Katana Kuro', 'Katana noir, style sobre et moderne.', 999.00, 3, NULL),
('Katana Shiro', 'Katana blanc, édition limitée.', 1099.00, 0, NULL),
('Wakizashi Argent', 'Lame courte, finition argentée.', 499.00, 8, NULL),
('Support Katana', 'Support bois laqué.', 79.90, 20, NULL),
('Kit entretien', 'Huile + chiffon + étui.', 39.90, 15, NULL);

-- (Optionnel) 1 commande démo pour montrer le back-office vite
INSERT INTO orders (user_id, total, status) VALUES
(2, 1078.90, 'CONFIRMED');

INSERT INTO order_items (order_id, product_id, quantity, unit_price, line_total) VALUES
(1, 2, 1, 999.00, 999.00),
(1, 5, 1, 79.90, 79.90);
