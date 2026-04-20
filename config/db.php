<?php
// Configuration pour InfinityFree
$host = 'sql100.infinityfree.com';
$db   = 'if0_41629481_agre';
$user = 'if0_41629481';
$pass = 'qvEyuhz2x5JkoU';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
  // On active les erreurs pour débugger plus facilement
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  // Si la connexion échoue, on affiche l'erreur
  die("Erreur de connexion à la base de données : " . $e->getMessage());
}
