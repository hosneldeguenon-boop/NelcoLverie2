<?php
/**
 * Ajouter un nouveau commentaire
 * REQUIS: Utilisateur connecté
 * Filtrage des mots vulgaires
 */

ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600);
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

error_log('=== add_comment.php ===');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    // VÉRIFICATION AUTHENTIFICATION OBLIGATOIRE
    if (!isset($_SESSION['user_id']) || 
        !isset($_SESSION['logged_in']) || 
        $_SESSION['logged_in'] !== true) {
        error_log('❌ Tentative non autorisée');
        http_response_code(401);
        throw new Exception('Vous devez être connecté pour publier un avis');
    }

    $user_id = (int)$_SESSION['user_id'];
    error_log('✅ User authentifié: ' . $user_id);

    // Récupérer les données
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Aucune donnée reçue');
    }

    $comment_text = trim($data['comment_text'] ?? '');
    $rating = isset($data['rating']) && $data['rating'] ? (int)$data['rating'] : null;

    // VALIDATIONS
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
            error_log('⚠️ Mot interdit détecté: ' . $mot);
            throw new Exception('Votre commentaire contient du langage inapproprié. Veuillez modifier votre texte.');
        }
    }

    // ANTI-SPAM: Limiter à 3 commentaires par heure
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM comments 
        WHERE user_id = ? 
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $stmt->execute([$user_id]);
    $recent = $stmt->fetch();
    
    if ($recent['count'] >= 3) {
        throw new Exception('Vous avez publié trop de commentaires récemment. Veuillez patienter.');
    }

    // INSERTION DU COMMENTAIRE
    $stmt = $conn->prepare("
        INSERT INTO comments (user_id, comment_text, rating, created_at, updated_at)
        VALUES (?, ?, ?, NOW(), NOW())
    ");

    $result = $stmt->execute([$user_id, $comment_text, $rating]);

    if (!$result) {
        error_log('❌ Erreur SQL: ' . implode(' ', $stmt->errorInfo()));
        throw new Exception('Erreur lors de l\'enregistrement');
    }

    $comment_id = $conn->lastInsertId();
    error_log('✅ Commentaire #' . $comment_id . ' publié par user #' . $user_id);

    echo json_encode([
        'success' => true,
        'message' => 'Merci pour votre avis ! Il a été publié avec succès.',
        'comment_id' => $comment_id
    ]);

} catch (Exception $e) {
    error_log('❌ Exception: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>