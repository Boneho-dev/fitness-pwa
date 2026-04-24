<?php
// Railway env vars (MYSQLHOST / MYSQL_HOST both supported)
// Fallback → XAMPP local (root / no password)
$host = getenv('MYSQLHOST')     ?: getenv('MYSQL_HOST')     ?: 'localhost';
$port = getenv('MYSQLPORT')     ?: getenv('MYSQL_PORT')     ?: '3306';
$db   = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'agre_fitness';
$user = getenv('MYSQLUSER')     ?: getenv('MYSQL_USER')     ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
