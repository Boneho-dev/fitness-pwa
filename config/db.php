<?php
date_default_timezone_set('Europe/Paris');

// InfinityFree production
$host = 'sql208.infinityfree.com';
$port = '3306';
$db   = 'if0_41744337_fitness';
$user = 'if0_41744337';
$pass = 'AUZwr91ThQ';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    die("Erreur de connexion à la base de données.");
}
