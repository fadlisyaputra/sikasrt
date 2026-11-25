<?php

$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '';
$db   = 'kas_rt_db';

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Database '$db' verified / created successfully!\n";
} catch (PDOException $e) {
    echo "MySQL Connection Error: " . $e->getMessage() . "\n";
    exit(1);
}
