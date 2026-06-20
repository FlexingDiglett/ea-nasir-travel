CREATE DATABASE IF NOT EXISTS travel_app;
USE travel_app;

DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS airports;

-- Table Structures

CREATE TABLE airports (
    iata_code CHAR(3) PRIMARY KEY,
    city_name VARCHAR(100) NOT NULL,
    airport_name VARCHAR(255) NOT NULL,
    country_code CHAR(2)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('personal', 'agency') DEFAULT 'personal',
    contact_name VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initial Seed Data

INSERT IGNORE INTO airports (iata_code, city_name, airport_name, country_code) VALUES
('BCN', 'Barcelona', 'Barcelona–El Prat Josep Tarradellas Airport', 'ES'),
('CDG', 'Paris', 'Paris Charles de Gaulle Airport', 'FR'),
('HND', 'Tokyo', 'Tokyo Haneda Airport', 'JP'),
('IST', 'Istanbul', 'Istanbul Airport', 'TR'),
('JFK', 'New York', 'John F. Kennedy International Airport', 'US'),
('RAK', 'Marrakech', 'Marrakesh Menara Airport', 'MA');