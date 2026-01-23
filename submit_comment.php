<?php
/**
 * Enregistrement d'un nouveau commentaire
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté pour publier un avis.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Aucune donnée reçue');
    }
    
    $userId = $_SESSION['user_id'];
    $rating = isset($data['rating']) ? (int)$data['rating'] : null;
    $comment = trim($data['comment'] ?? '');
    
    // Validations
    if (empty($comment)) {
        throw new Exception('Le commentaire est obligatoire');
    }
    
    if (strlen($comment) > 500) {
        throw new Exception('Le commentaire ne peut pas dépasser 500 caractères');
    }
    
    if (strlen($comment) < 10) {
        throw new Exception('Le commentaire doit contenir au moins 10 caractères');
    }
    
    // Si une note est fournie, la valider
    if ($rating !== null && ($rating < 1 || $rating > 5)) {
        throw new Exception('La note doit être entre 1 et 5 étoiles');
    }
    
    // Si pas de note, mettre 5 par défaut
    if ($rating === null) {
        $rating = 5;
    }
    
    $conn = getDBConnection();
    
    // Vérifier si l'utilisateur a déjà commenté récemment (anti-spam)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM comments 
        WHERE user_id = ? 
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $stmt->execute([$userId]);
    $recentComments = $stmt->fetch();
    
    if ($recentComments['count'] >= 3) {
        throw new Exception('Vous avez publié trop de commentaires récemment. Veuillez patienter.');
    }
    
    // Filtrage de contenu inapproprié (basique)
    $bannedWords = ['merde', 'connard', 'putain', 'con']; // Ajoutez d'autres mots si nécessaire
    $commentLower = strtolower($comment);
    
    foreach ($bannedWords as $word) {
        if (strpos($commentLower, $word) !== false) {
            throw new Exception('Votre commentaire contient du langage inapproprié. Veuillez le modifier.');
        }
    }
    
    // Insérer le commentaire
    $stmt = $conn->prepare("
        INSERT INTO comments (user_id, rating, comment, status, created_at) 
        VALUES (?, ?, ?, 'approved', NOW())
    ");
    
    $success = $stmt->execute([$userId, $rating, $comment]);
    
    if (!$success) {
        throw new Exception('Erreur lors de l\'enregistrement du commentaire');
    }
    
    // Log de l'action
    error_log("Nouveau commentaire publié par user_id: $userId, note: $rating/5");
    
    echo json_encode([
        'success' => true,
        'message' => 'Merci pour votre avis ! Il a été publié avec succès.'
    ]);
    
} catch (Exception $e) {
    error_log("Erreur submit_comment: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>