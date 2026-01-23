<?php
/**
 * Récupérer tous les commentaires publics
 * Accessible à tous (connecté ou non)
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Access-Control-Allow-Origin: *');

error_log('=== get_comments.php appelé ===');

require_once 'config.php';

try {
    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('Erreur de connexion à la base de données');
    }

    // Récupérer tous les commentaires avec infos utilisateur
    $stmt = $conn->prepare("
        SELECT 
            c.id,
            c.user_id,
            c.rating,
            c.comment_text,
            c.created_at,
            u.firstname,
            u.lastname
        FROM comments c
        INNER JOIN users u ON c.user_id = u.id
        ORDER BY c.created_at DESC
    ");
    
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log('✅ ' . count($comments) . ' commentaires chargés');

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'comments' => $comments,
        'count' => count($comments)
    ]);

} catch (PDOException $e) {
    error_log('❌ Erreur PDO: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données',
        'comments' => []
    ]);
} catch (Exception $e) {
    error_log('❌ Exception: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'comments' => []
    ]);
}
?>