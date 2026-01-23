<?php
/**
 * Mettre à jour le statut d'un commentaire (approuver/rejeter)
 * Réservé aux administrateurs
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

// TODO: Vérifier que l'utilisateur est admin
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
//     echo json_encode(['success' => false, 'message' => 'Accès refusé']);
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Aucune donnée reçue');
    }
    
    $commentId = (int)($data['comment_id'] ?? 0);
    $status = $data['status'] ?? '';
    
    if ($commentId <= 0) {
        throw new Exception('ID de commentaire invalide');
    }
    
    if (!in_array($status, ['approved', 'rejected', 'pending'])) {
        throw new Exception('Statut invalide');
    }
    
    $conn = getDBConnection();
    
    // Vérifier que le commentaire existe
    $stmt = $conn->prepare("SELECT id FROM comments WHERE id = ?");
    $stmt->execute([$commentId]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Commentaire introuvable');
    }
    
    // Mettre à jour le statut
    $stmt = $conn->prepare("UPDATE comments SET status = ? WHERE id = ?");
    $success = $stmt->execute([$status, $commentId]);
    
    if (!$success) {
        throw new Exception('Erreur lors de la mise à jour');
    }
    
    error_log("Commentaire $commentId mis à jour: $status");
    
    echo json_encode([
        'success' => true,
        'message' => 'Statut mis à jour avec succès'
    ]);
    
} catch (Exception $e) {
    error_log("Erreur update_comment_status: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>