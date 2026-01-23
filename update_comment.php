<?php
/**
 * Modifier son propre commentaire
 * L'utilisateur ne peut modifier QUE ses propres commentaires
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

error_log('=== update_comment.php ===');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    // VÉRIFICATION AUTHENTIFICATION
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
        http_response_code(401);
        throw new Exception('Vous devez être connecté');
    }

    $user_id = (int)$_SESSION['user_id'];

    // Récupérer les données
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Aucune donnée reçue');
    }

    $comment_id = (int)($data['comment_id'] ?? 0);
    $comment_text = trim($data['comment_text'] ?? '');
    $rating = isset($data['rating']) && $data['rating'] ? (int)$data['rating'] : null;

    // VALIDATIONS
    if (!$comment_id) {
        throw new Exception('ID commentaire invalide');
    }

    if (empty($comment_text)) {
        throw new Exception('Le commentaire ne peut pas être vide');
    }

    if (strlen($comment_text) < 10) {
        throw new Exception('Le commentaire doit contenir au moins 10 caractères');
    }

    if (strlen($comment_text) > 500) {
        throw new Exception('Le commentaire ne peut pas dépasser 500 caractères');
    }

    if ($rating !== null && ($rating < 1 || $rating > 5)) {
        throw new Exception('La note doit être entre 1 et 5');
    }

    // FILTRAGE DES MOTS VULGAIRES
    $mots_interdits = [
        'pute', 'putain', 'salope', 'connard', 'connasse', 
        'merde', 'con', 'conne', 'enculé', 'enculée',
        'bâtard', 'fils de pute', 'fdp', 'pd', 'tapette',
        'nique', 'ntm', 'batard', 'encule'
    ];
    
    $comment_lower = mb_strtolower($comment_text, 'UTF-8');
    
    foreach ($mots_interdits as $mot) {
        if (strpos($comment_lower, $mot) !== false) {
            throw new Exception('Votre commentaire contient du langage inapproprié. Veuillez modifier votre texte.');
        }
    }

    $conn = getDBConnection();

    // VÉRIFIER QUE LE COMMENTAIRE APPARTIENT À L'UTILISATEUR
    $stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch();

    if (!$comment) {
        throw new Exception('Commentaire non trouvé');
    }

    if ((int)$comment['user_id'] !== $user_id) {
        error_log('❌ Tentative modification non autorisée');
        http_response_code(403);
        throw new Exception('Vous ne pouvez modifier que vos propres commentaires');
    }

    // MISE À JOUR
    $stmt = $conn->prepare("
        UPDATE comments 
        SET comment_text = ?, rating = ?, updated_at = NOW()
        WHERE id = ? AND user_id = ?
    ");

    $result = $stmt->execute([$comment_text, $rating, $comment_id, $user_id]);

    if (!$result) {
        throw new Exception('Erreur lors de la mise à jour');
    }

    error_log('✅ Commentaire #' . $comment_id . ' modifié');

    echo json_encode([
        'success' => true,
        'message' => 'Votre avis a été modifié avec succès'
    ]);

} catch (Exception $e) {
    error_log('❌ Exception: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>