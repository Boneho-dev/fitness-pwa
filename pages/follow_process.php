<?php
/**
 * =============================================================================
 * FOLLOW PROCESS - AGRE FITNESS
 * =============================================================================
 * Gère les actions de suivi/désabonnement entre utilisateurs via AJAX.
 * Retourne une réponse JSON pour mise à jour dynamique de l'interface.
 */

session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

$followerId = (int)$_SESSION['user_id'];
$followingId = isset($_POST['following_id']) ? (int)$_POST['following_id'] : 0;
$action = $_POST['action'] ?? '';

// Validation des données
if ($followingId <= 0 || $followingId === $followerId) {
    echo json_encode(['success' => false, 'error' => 'ID utilisateur invalide']);
    exit;
}

if (!in_array($action, ['follow', 'unfollow'])) {
    echo json_encode(['success' => false, 'error' => 'Action invalide']);
    exit;
}

try {
    if ($action === 'follow') {
        // Vérifier si déjà abonné
        $check = $pdo->prepare("SELECT 1 FROM followers WHERE follower_id = ? AND following_id = ?");
        $check->execute([$followerId, $followingId]);
        
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Déjà abonné']);
            exit;
        }
        
        // Ajouter l'abonnement
        $stmt = $pdo->prepare("INSERT INTO followers (follower_id, following_id) VALUES (?, ?)");
        $stmt->execute([$followerId, $followingId]);
        
        // Mettre à jour last_active de l'utilisateur suivi (pour notif)
        $stmt = $pdo->prepare("UPDATE fitness_users SET last_active = NOW() WHERE id = ?");
        $stmt->execute([$followerId]);
        
        echo json_encode([
            'success' => true, 
            'action' => 'followed',
            'message' => 'Abonnement réussi'
        ]);
        
    } else {
        // Supprimer l'abonnement
        $stmt = $pdo->prepare("DELETE FROM followers WHERE follower_id = ? AND following_id = ?");
        $stmt->execute([$followerId, $followingId]);
        
        echo json_encode([
            'success' => true, 
            'action' => 'unfollowed',
            'message' => 'Désabonnement réussi'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Erreur follow_process: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
}
