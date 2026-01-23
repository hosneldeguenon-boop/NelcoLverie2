<?php
/**
 * ✅ Script pour récupérer le nombre de lavages (points de fidélité)
 * Correction : Utilise points_counter qui représente le nombre de lavages
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

try {
    // Vérifier que l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Utilisateur non connecté',
            'nombre_lavage' => 0
        ]);
        exit;
    }

    $userId = $_SESSION['user_id'];
    
    $conn = getDBConnection();
    
    // ✅ Récupérer points_counter qui représente le nombre de lavages
    $stmt = $conn->prepare("SELECT points_counter FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('Utilisateur introuvable');
    }
    
    $nombreLavage = intval($user['points_counter']);
    
    echo json_encode([
        'success' => true,
        'nombre_lavage' => $nombreLavage
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'nombre_lavage' => 0
    ]);
}
?>