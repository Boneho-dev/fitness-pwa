<?php
/**
 * COMMENT PROCESS - V1.1 AGRE FITNESS
 * Gestion des commentaires via AJAX
 * Sécurité : PDO prepared statements, htmlspecialchars
 */

session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

$userId = $_SESSION['user_id'];
$postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

if (!$postId || empty($content)) {
    echo json_encode(['success' => false, 'error' => 'Données invalides']);
    exit;
}

if (strlen($content) > 1000) {
    echo json_encode(['success' => false, 'error' => 'Commentaire trop long (max 1000 caractères)']);
    exit;
}

try {
    // Vérifier que le post existe
    $stmt = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Post introuvable']);
        exit;
    }
    
    // Insérer le commentaire
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$postId, $userId, htmlspecialchars($content, ENT_QUOTES, 'UTF-8')]);
    $commentId = $pdo->lastInsertId();
    
    // Compter les commentaires
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
    $stmt->execute([$postId]);
    $commentsCount = $stmt->fetchColumn();
    
    // Récupérer les infos de l'utilisateur pour l'affichage
    $stmt = $pdo->prepare("SELECT username, profile_pic FROM fitness_users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'comments_count' => $commentsCount,
        'comment_id' => $commentId,
        'username' => htmlspecialchars($user['username']),
        'profile_pic' => $user['profile_pic'] ?? 'default_avatar.png',
        'content' => htmlspecialchars($content),
        'created_at' => date('d/m/Y H:i')
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur base de données']);
}
