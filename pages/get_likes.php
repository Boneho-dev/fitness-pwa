<?php
session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

$postId = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

if (!$postId) {
    echo json_encode(['success' => false, 'error' => 'ID post invalide']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.profile_pic
        FROM likes l
        JOIN fitness_users u ON l.user_id = u.id
        WHERE l.post_id = ?
        ORDER BY l.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$postId]);
    $likers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'likers' => $likers,
        'count' => count($likers)
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur base de données']);
}