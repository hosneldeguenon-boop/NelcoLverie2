<?php
/**
 * Supprimer un commentaire (Admin)
 * Fichier: delete_comment.php
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

error_log('delete_comment.php appelé');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    // Vérifier l'authentification admin
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        error_log('Admin non authentifié');
        http_response_code(401);
        throw new Exception('Non authentifié');
    }

    $json = file_get_contents('php://input');
    error_log('JSON reçu: ' . $json);
    
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Erreur de décodage JSON');
    }

    $comment_id = intval($data['comment_id'] ?? 0);

    if (!$comment_id) {
        throw new Exception('ID commentaire invalide');
    }

    error_log('Tentative suppression: comment_id=' . $comment_id . ', admin_id=' . $_SESSION['admin_id']);

    $conn = getDBConnection();

    if (!$conn) {
        throw new Exception('Erreur de connexion à la base de données');
    }

    // Supprimer le commentaire
    $deleteStmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    $result = $deleteStmt->execute([$comment_id]);

    if (!$result) {
        error_log('Erreur SQL: ' . implode(' ', $deleteStmt->errorInfo()));
        throw new Exception('Erreur lors de la suppression');
    }

    error_log('Commentaire supprimé: ' . $comment_id);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Commentaire supprimé avec succès'
    ]);

} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>