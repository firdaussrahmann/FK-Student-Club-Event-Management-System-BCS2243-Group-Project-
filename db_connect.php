<?php
// db_connect.php
$host = 'localhost';
$dbname = 'fkstudentclub&eventmanagementsystem'; // Change this to your exact database name
$username = 'root';         // Default XAMPP username
$password = '';             // Default XAMPP password is empty

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Set PDO error mode to exception for easier debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Fetch data as associative arrays by default
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If connection fails, stop execution and show an error
    die("Database connection failed: " . $e->getMessage());
}
?>