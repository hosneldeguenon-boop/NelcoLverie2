<?php
/**
 * ✅ SUPPRESSION D'UNE ANNONCE - NELCO LAVERIE
 * Réservé aux administrateurs uniquement
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

error_log('=== delete_announcement.php appelé ===');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    // ✅ VÉRIFICATION AUTHENTIFICATION ADMIN
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        error_log('❌ Admin non authentifié');
        http_response_code(401);
        throw new Exception('Accès réservé aux administrateurs');
    }

    $admin_id = (int)$_SESSION['admin_id'];

    // Récupérer les données
    $json = file_get_contents('php://input');
    error_log('JSON reçu: ' . $json);
    
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Erreur de décodage JSON');
    }

    $announcement_id = intval($data['announcement_id'] ?? 0);

    if (!$announcement_id) {
        throw new Exception('ID annonce invalide');
    }

    error_log('Tentative suppression: announcement_id=' . $announcement_id . ', admin_id=' . $admin_id);

    $conn = getDBConnection();

    if (!$conn) {
        throw new Exception('Erreur de connexion à la base de données');
    }

    // ✅ Récupérer l'annonce pour supprimer le fichier média si nécessaire
    $stmt = $conn->prepare("SELECT media_type, media_path FROM announcements WHERE id = ?");
    $stmt->execute([$announcement_id]);
    $announcement = $stmt->fetch();

    if (!$announcement) {
        throw new Exception('Annonce introuvable');
    }

    // ✅ Supprimer le fichier média si c'est une image ou un audio
    if (in_array($announcement['media_type'], ['image', 'audio']) && !empty($announcement['media_path'])) {
        $file_path = __DIR__ . '/' . $announcement['media_path'];
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                error_log('✅ Fichier média supprimé: ' . $file_path);
            } else {
                error_log('⚠️ Impossible de supprimer le fichier: ' . $file_path);
            }
        }
    }

    // ✅ Supprimer l'annonce de la base de données
    $deleteStmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $result = $deleteStmt->execute([$announcement_id]);

    if (!$result) {
        error_log('❌ Erreur SQL: ' . implode(' ', $deleteStmt->errorInfo()));
        throw new Exception('Erreur lors de la suppression');
    }

    error_log('✅ Annonce supprimée: ID=' . $announcement_id);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Annonce supprimée avec succès'
    ]);

} catch (Exception $e) {
    error_log('❌ Exception: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
