<?php
$host = 'localhost';
$db_name = 'np03cs4s250095';
$username = 'np03cs4s250095';
$password = 'i22u2h72YY';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error for admin/developer
    error_log("Database Connection Error: " . $e->getMessage());
}
?>