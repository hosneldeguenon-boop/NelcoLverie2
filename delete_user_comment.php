<?php
/**
 * Supprimer son propre commentaire
 * L'utilisateur ne peut supprimer QUE ses propres commentaires
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

error_log('=== delete_user_comment.php ===');

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

    if (!$comment_id) {
        throw new Exception('ID commentaire invalide');
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
        error_log('❌ Tentative suppression non autorisée');
        http_response_code(403);
        throw new Exception('Vous ne pouvez supprimer que vos propres commentaires');
    }

    // SUPPRESSION
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    $result = $stmt->execute([$comment_id, $user_id]);

    if (!$result) {
        throw new Exception('Erreur lors de la suppression');
    }

    error_log('✅ Commentaire #' . $comment_id . ' supprimé');

    echo json_encode([
        'success' => true,
        'message' => 'Votre avis a été supprimé avec succès'
    ]);

} catch (Exception $e) {
    error_log('❌ Exception: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>