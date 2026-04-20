<?php
/**
 * LIKE PROCESS - V1.1 AGRE FITNESS
 * Gestion des likes via AJAX
 * Sécurité : PDO prepared statements, vérification session
 */

session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

// Vérification authentification
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

$userId = $_SESSION['user_id'];
$postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
$action = isset($_POST['like_action']) ? $_POST['like_action'] : 'toggle';

if (!$postId) {
    echo json_encode(['success' => false, 'error' => 'ID post invalide']);
    exit;
}

try {
    // Vérifier si le post existe
    $stmt = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Post introuvable']);
        exit;
    }
    
    // Vérifier si déjà liké
    $stmt = $pdo->prepare("SELECT 1 FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$postId, $userId]);
    $alreadyLiked = $stmt->fetch();
    
    if ($alreadyLiked) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $userId]);
        $liked = false;
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $stmt->execute([$postId, $userId]);
        $liked = true;
    }
    
    // Compter les likes totaux
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $stmt->execute([$postId]);
    $likesCount = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'likes_count' => $likesCount
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur base de données']);
}
