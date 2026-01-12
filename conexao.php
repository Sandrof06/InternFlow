<?php


// Sending to Main
// Database configuration (Hostinger)
$host = 'localhost';
$dbname = 'u506280443_sanjoaDB';
$username = 'u506280443_sanjoadbUser'; 
$password = 'kTcP:b;0M'; 

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("DB connection failed: " . $conn->connect_error);
    die("Database connection error.");
}

// Optional but recommended
$conn->set_charset("utf8mb4");
?>
