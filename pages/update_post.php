<?php
/**
 * UPDATE POST - V1.1 AGRE FITNESS
 * Modification sécurisée des posts
 * Vérification : uniquement l'auteur peut modifier
 */

session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

$currentUserId = $_SESSION['user_id'];
$postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
$caption = isset($_POST['caption']) ? trim($_POST['caption']) : '';

if (!$postId) {
    echo json_encode(['success' => false, 'error' => 'ID post invalide']);
    exit;
}

if (strlen($caption) > 1000) {
    echo json_encode(['success' => false, 'error' => 'Légende trop longue']);
    exit;
}

try {
    // Vérifier propriétaire
    $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    
    if (!$post) {
        echo json_encode(['success' => false, 'error' => 'Post introuvable']);
        exit;
    }
    
    if ($post['user_id'] != $currentUserId) {
        echo json_encode(['success' => false, 'error' => 'Action non autorisée']);
        exit;
    }
    
    // Mise à jour
    $stmt = $pdo->prepare("UPDATE posts SET caption = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([htmlspecialchars($caption, ENT_QUOTES, 'UTF-8'), $postId, $currentUserId]);
    
    if ($stmt->rowCount() >= 0) {
        echo json_encode([
            'success' => true,
            'caption' => htmlspecialchars($caption),
            'message' => 'Post mis à jour'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Échec mise à jour']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur base de données']);
}