<?php
/**
 * Récupérer tous les commentaires pour l'administration
 * Ce fichier était manquant - c'est pourquoi rien ne s'affichait !
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

error_log('get_all_comments.php appelé');

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Non autorisé'
    ]);
    exit();
}

require_once 'config.php';

try {
    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('Erreur de connexion à la base de données');
    }

    // Récupérer tous les commentaires avec les informations utilisateur
    $stmt = $conn->prepare("
        SELECT 
            c.id,
            c.user_id,
            c.rating,
            c.comment_text,
            c.created_at,
            c.updated_at,
            u.firstname,
            u.lastname,
            u.email
        FROM comments c
        INNER JOIN users u ON c.user_id = u.id
        ORDER BY c.created_at DESC
    ");
    
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Statistiques
    $statsStmt = $conn->query("
        SELECT 
            COUNT(*) as total,
            AVG(rating) as average_rating
        FROM comments
    ");
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    error_log('Commentaires admin chargés: ' . count($comments));
    error_log('Stats: total=' . $stats['total'] . ', avg=' . $stats['average_rating']);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'comments' => $comments,
        'stats' => [
            'total' => (int)$stats['total'],
            'average_rating' => $stats['average_rating'] ? round($stats['average_rating'], 1) : 0
        ]
    ]);

} catch (PDOException $e) {
    error_log('Erreur PDO dans get_all_comments.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données: ' . $e->getMessage()
    ]);
    
} catch (Exception $e) {
    error_log('Exception dans get_all_comments.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
?>