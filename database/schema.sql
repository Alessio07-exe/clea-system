-- CLEA System Database Schema
-- MySQL 5.7+

-- Create database
CREATE DATABASE IF NOT EXISTS clea_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clea_system;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    azienda VARCHAR(200),
    ruolo ENUM('tecnico', 'manager', 'admin') DEFAULT 'tecnico',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Machines table
CREATE TABLE machines (
    id INT PRIMARY KEY AUTO_INCREMENT,
    serial_number VARCHAR(255) NOT NULL UNIQUE,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    refrigerant_quantity DECIMAL(5,2),
    installation_date DATE NOT NULL,
    installation_description LONGTEXT,
    technician_id INT NOT NULL,
    installation_company VARCHAR(200),
    general_description LONGTEXT,
    public_token VARCHAR(255) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_serial (serial_number),
    INDEX idx_technician (technician_id),
    INDEX idx_token (public_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Machine photos table
CREATE TABLE machine_photos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    machine_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_filename VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE CASCADE,
    INDEX idx_machine (machine_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Maintenance table
CREATE TABLE maintenance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    machine_id INT NOT NULL,
    technician_id INT NOT NULL,
    maintenance_date DATE NOT NULL,
    technician_name VARCHAR(200) NOT NULL,
    maintenance_company VARCHAR(200),
    maintenance_type ENUM('ordinaria', 'straordinaria') NOT NULL,
    problem_description LONGTEXT,
    intervention_description LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE CASCADE,
    FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_machine (machine_id),
    INDEX idx_technician (technician_id),
    INDEX idx_date (maintenance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions table (for session management)
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create default admin user
INSERT INTO users (email, password_hash, nome, cognome, azienda, ruolo) VALUES
('admin@clea.it', '$2y$10$abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJ', 'Admin', 'CLEA', 'CLEA System', 'admin'),
('tecnico@clea.it', '$2y$10$abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJ', 'Tecnico', 'Demo', 'Demo Azienda', 'tecnico');

-- Note: Default passwords need to be set via the application login/registration
-- Email: admin@clea.it / Password: admin123
-- Email: tecnico@clea.it / Password: password123
