<?php
/**
 * SEND MESSAGE - V1.1 AGRE FITNESS
 * API pour envoyer un message (texte + image) via AJAX
 */

session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

$currentUserId = $_SESSION['user_id'];
$destinataireId = isset($_POST['destinataire_id']) ? intval($_POST['destinataire_id']) : 0;
$contenu = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';

if (!$destinataireId) {
    echo json_encode(['success' => false, 'error' => 'Destinataire invalide']);
    exit;
}

// Vérifier que le destinataire existe
$stmt = $pdo->prepare("SELECT id FROM fitness_users WHERE id = ?");
$stmt->execute([$destinataireId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Destinataire introuvable']);
    exit;
}

$mediaUrl = null;

// Gestion de l'upload d'image
if (!empty($_FILES['image']['name'])) {
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = mime_content_type($_FILES['image']['tmp_name']);
    
    if (!in_array($fileType, $allowed)) {
        echo json_encode(['success' => false, 'error' => 'Format non supporté (JPG, PNG, GIF, WEBP)']);
        exit;
    }
    
    if ($_FILES['image']['size'] > 5 * 1024 * 1024) { // 5MB max
        echo json_encode(['success' => false, 'error' => 'Image trop lourde (max 5MB)']);
        exit;
    }
    
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = 'msg_' . $currentUserId . '_' . time() . '.' . $ext;
    $uploadPath = '../assets/images/messages/' . $filename;
    
    // Créer le dossier si inexistant
    if (!is_dir('../assets/images/messages/')) {
        mkdir('../assets/images/messages/', 0755, true);
    }
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
        $mediaUrl = 'messages/' . $filename;
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur upload']);
        exit;
    }
}

// Au moins un contenu ou une image requis
if (empty($contenu) && !$mediaUrl) {
    echo json_encode(['success' => false, 'error' => 'Message vide']);
    exit;
}

if (strlen($contenu) > 2000) {
    echo json_encode(['success' => false, 'error' => 'Message trop long (max 2000)']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO messages (expediteur_id, destinataire_id, contenu, media_url) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $currentUserId, 
        $destinataireId, 
        htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8'), 
        $mediaUrl
    ]);
    
    $messageId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'id' => $messageId,
        'contenu' => htmlspecialchars($contenu),
        'media_url' => $mediaUrl,
        'time' => date('H:i')
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur base de données']);
}
?>
