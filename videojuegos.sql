-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS videojuegos_db;
USE videojuegos_db;

DROP TABLE IF EXISTS videojuegos;

-- Crear la tabla de videojuegos
CREATE TABLE videojuegos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compania VARCHAR(100) NOT NULL,
    consola VARCHAR(100) NOT NULL,
    videojuego VARCHAR(150) NOT NULL,
    puntuacion INT DEFAULT 0,
    precio DECIMAL(10, 2) DEFAULT 0.00
);

-- Insertar los datos iniciales
INSERT INTO videojuegos (compania, consola, videojuego, puntuacion, precio) VALUES
('Nintendo', 'Switch', 'The Legend of Zelda', 97, 59.99),
('Nintendo', 'Switch', 'Super Mario', 92, 49.99),
('Nintendo', 'wii', 'Mario Party', 75, 29.99),
('Nintendo', 'wii', 'The Legend of Zelda 2', 88, 19.99),
('Sony', 'PlayStation 4', 'God of War', 94, 19.99),
('Sony', 'PlayStation 4', 'Spider-Man', 90, 29.99),
('Sony', 'PlayStation 5', 'Horizon Forbiden', 89, 79.99),
('Sony', 'PlayStation 5', 'Fortnite', 85, 0.00),
('Microsoft', 'Xbox 360', 'Halo Infinite', 87, 14.99),
('Microsoft', 'Xbox Series X', 'Forza Horizon', 92, 69.99),
('Microsoft', 'Xbox Series S', 'Fortnite', 85, 0.00),
('PC', 'Wii emulator', 'Mario Galaxy', 96, 0.00),
('PC', 'Steam', 'Elden Ring', 96, 59.99),
('PC', 'Steam', 'Fortnite', 85, 0.00),
('PC', 'Epic Games', 'Cyberpunk', 86, 59.99),
('PC', 'Epic Games', 'Vendetta', 70, 19.99);