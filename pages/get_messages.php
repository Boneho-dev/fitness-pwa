<?php
/**
 * GET MESSAGES - V1.1 AGRE FITNESS
 * API pour récupérer les nouveaux messages via AJAX (polling)
 */

session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

$currentUserId = $_SESSION['user_id'];
$chatUserId = isset($_GET['user']) ? intval($_GET['user']) : 0;
$lastId = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

if (!$chatUserId) {
    echo json_encode(['success' => false, 'error' => 'Utilisateur invalide']);
    exit;
}

try {
    // Récupérer les messages plus récents que last_id
    $stmt = $pdo->prepare("
        SELECT m.*, u.username, u.profile_pic 
        FROM messages m
        JOIN fitness_users u ON m.expediteur_id = u.id
        WHERE m.id > ?
        AND ((m.expediteur_id = ? AND m.destinataire_id = ?) 
          OR (m.expediteur_id = ? AND m.destinataire_id = ?))
        ORDER BY m.date_envoi ASC
    ");
    $stmt->execute([$lastId, $currentUserId, $chatUserId, $chatUserId, $currentUserId]);
    $messages = $stmt->fetchAll();
    
    // Marquer comme lus les messages reçus
    $stmt = $pdo->prepare("
        UPDATE messages SET lu = 1 
        WHERE destinataire_id = ? 
        AND expediteur_id = ? 
        AND id > ?
    ");
    $stmt->execute([$currentUserId, $chatUserId, $lastId]);
    
    // Formater les messages
    $formatted = array_map(function($msg) {
        return [
            'id' => $msg['id'],
            'expediteur_id' => $msg['expediteur_id'],
            'contenu' => htmlspecialchars($msg['contenu']),
            'media_url' => $msg['media_url'],
            'time' => date('H:i', strtotime($msg['date_envoi'])),
            'profile_pic' => $msg['profile_pic'] ?? 'default_avatar.png'
        ];
    }, $messages);
    
    echo json_encode([
        'success' => true,
        'messages' => $formatted
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur base de données']);
}
?>
