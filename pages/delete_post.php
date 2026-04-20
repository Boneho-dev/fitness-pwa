<?php
/**
 * DELETE POST - V1.1 AGRE FITNESS
 * Suppression sécurisée des posts
 * Vérification : uniquement l'auteur peut supprimer
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

if (!$postId) {
    echo json_encode(['success' => false, 'error' => 'ID post invalide']);
    exit;
}

try {
    // Récupérer le post avec vérification propriétaire
    $stmt = $pdo->prepare("SELECT user_id, image_url FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    
    if (!$post) {
        echo json_encode(['success' => false, 'error' => 'Post introuvable']);
        exit;
    }
    
    // VÉRIFICATION SÉCURITÉ : seul l'auteur peut supprimer
    if ($post['user_id'] != $currentUserId) {
        echo json_encode(['success' => false, 'error' => 'Action non autorisée']);
        exit;
    }
    
    // Supprimer l'image physique si elle existe
    if (!empty($post['image_url'])) {
        $imagePath = '../assets/images/' . $post['image_url'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    // Supprimer les likes associés
    $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = ?");
    $stmt->execute([$postId]);
    
    // Supprimer les commentaires associés
    $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
    $stmt->execute([$postId]);
    
    // Supprimer le post
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$postId, $currentUserId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Post supprimé']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Échec suppression']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur base de données']);
}
?>​
