<?php
/**
 * DELETE POST - V1.2 FITNESS TRACKER
 * Suppression sécurisée des posts (auteur uniquement).
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
    $stmt = $pdo->prepare("SELECT user_id, image_url FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();

    if (!$post) {
        echo json_encode(['success' => false, 'error' => 'Post introuvable']);
        exit;
    }

    if ((int)$post['user_id'] !== (int)$currentUserId) {
        echo json_encode(['success' => false, 'error' => 'Action non autorisée']);
        exit;
    }

    // Supprimer le fichier image physique si présent
    if (!empty($post['image_url'])) {
        $imagePath = '../assets/images/' . $post['image_url'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Supprimer les likes et commentaires associés
    $pdo->prepare("DELETE FROM likes WHERE post_id = ?")->execute([$postId]);
    $pdo->prepare("DELETE FROM comments WHERE post_id = ?")->execute([$postId]);

    // Supprimer le post (double vérification user_id en SQL)
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
