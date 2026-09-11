-- CLEA System Database Schema
-- Database for machinery maintenance management

CREATE DATABASE IF NOT EXISTS clea_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clea_system;

-- Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    azienda VARCHAR(255),
    ruolo ENUM('tecnico', 'admin') DEFAULT 'tecnico',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_azienda (azienda)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Machines Table
CREATE TABLE machines (
    id INT PRIMARY KEY AUTO_INCREMENT,
    serial_number VARCHAR(255) NOT NULL UNIQUE,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    refrigerant_quantity DECIMAL(10, 2),
    installation_description TEXT,
    installation_date DATE NOT NULL,
    technician_id INT NOT NULL,
    installation_company VARCHAR(255),
    general_description TEXT,
    public_token VARCHAR(64) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_serial_number (serial_number),
    INDEX idx_public_token (public_token),
    INDEX idx_technician_id (technician_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Maintenance Table
CREATE TABLE maintenance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    machine_id INT NOT NULL,
    technician_id INT NOT NULL,
    maintenance_date DATE NOT NULL,
    technician_name VARCHAR(255) NOT NULL,
    maintenance_company VARCHAR(255),
    maintenance_type ENUM('ordinaria', 'straordinaria') NOT NULL,
    problem_description TEXT,
    intervention_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE CASCADE,
    FOREIGN KEY (technician_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_machine_id (machine_id),
    INDEX idx_maintenance_date (maintenance_date),
    INDEX idx_technician_id (technician_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Machine Photos Table
CREATE TABLE machine_photos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    machine_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE CASCADE,
    INDEX idx_machine_id (machine_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample technician for testing
INSERT INTO users (email, password_hash, nome, cognome, azienda, ruolo)
VALUES ('tecnico@clea.it', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36gZvWQi', 'Mario', 'Rossi', 'TechSys', 'tecnico');
-- Password: password123 (hashed with password_hash())

-- Sample machine for testing
INSERT INTO machines (serial_number, brand, model, refrigerant_quantity, installation_date, technician_id, installation_company, public_token)
VALUES ('SN-2026-001', 'Daikin', 'FTXB25C', 2.5, '2026-01-15', 1, 'TechSys', 'abc123xyz789def456ghi789');

-- Sample maintenance record
INSERT INTO maintenance (machine_id, technician_id, maintenance_date, technician_name, maintenance_company, maintenance_type, problem_description, intervention_description)
VALUES (1, 1, '2026-02-10', 'Mario Rossi', 'TechSys', 'ordinaria', 'Controllo periodico', 'Pulizia filtri e controllo pressione refrigerante');
