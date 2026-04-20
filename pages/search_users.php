<?php
/**
 * =============================================================================
 * SEARCH USERS AJAX - AGRE FITNESS
 * =============================================================================
 * Endpoint pour la recherche prédictive d'utilisateurs.
 * Retourne les résultats au format JSON pour le typeahead.
 */

session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

$query = trim($_GET['q'] ?? '');
if (strlen($query) < 2) {
    echo json_encode(['success' => true, 'users' => []]);
    exit;
}

try {
    // Recherche insensible à la casse
    $searchTerm = '%' . $query . '%';
    $stmt = $pdo->prepare("
        SELECT id, username, profile_pic 
        FROM fitness_users 
        WHERE username LIKE :search 
          AND id != :current_user 
        ORDER BY username ASC 
        LIMIT 10
    ");
    $stmt->execute([
        ':search' => $searchTerm,
        ':current_user' => $_SESSION['user_id']
    ]);
    $users = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'users' => $users,
        'query' => $query
    ]);
    
} catch (PDOException $e) {
    error_log("Erreur recherche utilisateurs: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
}
