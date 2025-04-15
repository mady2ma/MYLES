<?php
require_once 'config.php';

function initializeDatabase() {
    $conn = getDBConnection();
    
    $conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $conn->select_db(DB_NAME);
    
    $conn->query("
        CREATE TABLE IF NOT EXISTS collections (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) UNIQUE NOT NULL,
            created DATE NOT NULL,
            last_updated DATE NOT NULL,
            notes TEXT,
            S INT DEFAULT 0,
            M INT DEFAULT 0,
            L INT DEFAULT 0,
            XL INT DEFAULT 0,
            XXL INT DEFAULT 0,
            XXXL INT DEFAULT 0,
            Mix INT DEFAULT 0
        )
    ");
    
    $conn->query("
        CREATE TABLE IF NOT EXISTS transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(50) NOT NULL,
            collection_name VARCHAR(255),
            date DATE NOT NULL,
            issued_to VARCHAR(255),
            notes TEXT,
            S INT DEFAULT 0,
            M INT DEFAULT 0,
            L INT DEFAULT 0,
            XL INT DEFAULT 0,
            XXL INT DEFAULT 0,
            XXXL INT DEFAULT 0,
            Mix INT DEFAULT 0,
            timestamp DATETIME NOT NULL,
            FOREIGN KEY (collection_name) REFERENCES collections(name) ON DELETE SET NULL
        )
    ");
    
    $conn->close();
}

initializeDatabase();
?>