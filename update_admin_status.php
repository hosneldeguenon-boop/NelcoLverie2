<?php
/**
 * API pour changer le statut d'un administrateur
 * Accessible uniquement aux administrateurs connectés
 */

session_start();

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header('Content-Type: application/json; charset=utf-8');

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Non autorisé. Connexion requise.'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit();
}

try {
    // Récupérer les données
    $json = file_get_contents('php://input');
    
    if (empty($json)) {
        throw new Exception('Aucune donnée reçue');
    }
    
    $data = json_decode($json, true);
    
    if ($data === null) {
        throw new Exception('Erreur de décodage JSON');
    }
    
    $adminId = (int)($data['admin_id'] ?? 0);
    $newStatus = trim($data['status'] ?? '');
    
    // Validations
    if ($adminId <= 0) {
        throw new Exception('ID administrateur invalide');
    }
    
    if (!in_array($newStatus, ['actif', 'inactif', 'suspendu'])) {
        throw new Exception('Statut invalide');
    }
    
    // Empêcher un admin de se suspendre lui-même
    if ($adminId === (int)$_SESSION['admin_id'] && $newStatus === 'suspendu') {
        throw new Exception('Vous ne pouvez pas vous suspendre vous-même');
    }
    
    require_once 'config.php';
    $conn = getDBConnection();
    
    // Vérifier que l'admin existe
    $checkStmt = $conn->prepare("SELECT id, username FROM admins WHERE id = :id LIMIT 1");
    $checkStmt->execute([':id' => $adminId]);
    
    if ($checkStmt->rowCount() === 0) {
        throw new Exception('Administrateur non trouvé');
    }
    
    $admin = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    // Mettre à jour le statut
    $updateStmt = $conn->prepare("
        UPDATE admins 
        SET status = :status, updated_at = NOW() 
        WHERE id = :id
    ");
    
    $result = $updateStmt->execute([
        ':status' => $newStatus,
        ':id' => $adminId
    ]);
    
    if (!$result) {
        throw new Exception('Erreur lors de la mise à jour');
    }
    
    error_log("Admin {$admin['username']} (ID: $adminId) - Statut changé vers: $newStatus par admin ID: {$_SESSION['admin_id']}");
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Statut mis à jour avec succès',
        'admin_id' => $adminId,
        'new_status' => $newStatus
    ]);
    
} catch (Exception $e) {
    error_log('Erreur update_admin_status: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
} catch (PDOException $e) {
    error_log('Erreur PDO update_admin_status: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données'
    ]);
}
?>