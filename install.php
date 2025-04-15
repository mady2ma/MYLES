<?php
require_once 'config.php';

header('Content-Type: text/plain');
$conn = getDBConnection();
if (!$conn) {
    echo "Failed to connect to database.\n";
    exit;
}

echo "Installing Inventory Management System...\n";

// Drop existing tables
$conn->query("DROP TABLE IF EXISTS transactions");
$conn->query("DROP TABLE IF EXISTS collections");
$conn->query("DROP TABLE IF EXISTS users");

// Create users table
$conn->query("
    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");
if ($conn->error) {
    echo "Error creating users table: " . $conn->error . "\n";
    exit;
} else {
    echo "Users table created.\n";
}

// Create collections table
$conn->query("
    CREATE TABLE collections (
        name VARCHAR(255) PRIMARY KEY,
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
if ($conn->error) {
    echo "Error creating collections table: " . $conn->error . "\n";
    exit;
} else {
    echo "Collections table created.\n";
}

// Create transactions table
$conn->query("
    CREATE TABLE transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type ENUM('entry', 'issue') NOT NULL,
        collection_name VARCHAR(255) NOT NULL,
        date DATE NOT NULL,
        issued_to VARCHAR(255) DEFAULT NULL,
        notes TEXT,
        S INT DEFAULT 0,
        M INT DEFAULT 0,
        L INT DEFAULT 0,
        XL INT DEFAULT 0,
        XXL INT DEFAULT 0,
        XXXL INT DEFAULT 0,
        Mix INT DEFAULT 0,
        timestamp DATETIME NOT NULL,
        user_id INT,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )
");
if ($conn->error) {
    echo "Error creating transactions table: " . $conn->error . "\n";
    exit;
} else {
    echo "Transactions table created.\n";
}

// Insert default admin user
$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT); // Default password: admin123
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
echo $conn->error ? "Error inserting user: " . $conn->error . "\n" : "Inserted default admin user (username: admin, password: admin123).\n";
$stmt->close();

// Insert test data
$today = date('Y-m-d');
$now = date('Y-m-d H:i:s');
$user_id = 1; // Default admin user ID

$stmt = $conn->prepare("INSERT INTO collections (name, created, last_updated, notes, S) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssssi", $name, $created, $last_updated, $notes, $s);
$name = "Jeans"; $created = $today; $last_updated = $today; $notes = "Test collection"; $s = 10;
$stmt->execute();
echo $conn->error ? "Error inserting collection: " . $conn->error . "\n" : "Inserted test collection 'Jeans' (S=10).\n";
$stmt->close();

$stmt = $conn->prepare("INSERT INTO transactions (type, collection_name, date, notes, S, timestamp, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssisi", $type, $collection_name, $date, $notes, $s, $timestamp, $user_id);
$type = "entry"; $collection_name = "Jeans"; $date = $today; $notes = "Initial stock"; $s = 10; $timestamp = $now;
$stmt->execute();
$entry_id = $conn->insert_id;
echo $conn->error ? "Error inserting entry: " . $conn->error . "\n" : "Inserted entry transaction (id=$entry_id).\n";
$stmt->close();

$stmt = $conn->prepare("INSERT INTO transactions (type, collection_name, date, issued_to, notes, S, timestamp, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssisi", $type, $collection_name, $date, $issued_to, $notes, $s, $timestamp, $user_id);
$type = "issue"; $collection_name = "Jeans"; $date = $today; $issued_to = "Store A"; $notes = "Issued to store"; $s = 5; $timestamp = $now;
$stmt->execute();
$issue_id = $conn->insert_id;
echo $conn->error ? "Error inserting issue: " . $conn->error . "\n" : "Inserted issue transaction (id=$issue_id).\n";
$stmt->close();

$conn->close();
echo "Installation complete. Access login.php to start.\n";
?>