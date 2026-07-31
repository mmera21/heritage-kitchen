<?php

$host = 'localhost';
$database = 'heritage_kitchen';
$username = 'root';
$password = '';

$dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $db = new PDO($dsn, $username, $password, $options);
} catch (PDOException $error) {
    exit('Database connection failed.');
}
