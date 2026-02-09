<?php
/**
 * ✅ RÉCUPÉRATION DE TOUTES LES ANNONCES - NELCO LAVERIE
 * Accessible à tous (visiteurs et utilisateurs connectés)
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Access-Control-Allow-Origin: *');

error_log('=== get_announcements.php appelé ===');

require_once 'config.php';

try {
    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('Erreur de connexion à la base de données');
    }

    // ✅ Récupérer toutes les annonces avec informations admin
    $stmt = $conn->prepare("
        SELECT 
            a.id,
            a.admin_id,
            a.title,
            a.content,
            a.media_type,
            a.media_path,
            a.created_at,
            a.updated_at,
            ad.firstname as admin_firstname,
            ad.lastname as admin_lastname
        FROM announcements a
        LEFT JOIN admins ad ON a.admin_id = ad.id
        ORDER BY a.created_at DESC
    ");
    
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log('✅ ' . count($announcements) . ' annonces chargées');

    // ✅ Statistiques (optionnel)
    $stats = [
        'total' => count($announcements),
        'with_media' => 0
    ];

    foreach ($announcements as $announcement) {
        if ($announcement['media_type'] !== 'none' && !empty($announcement['media_path'])) {
            $stats['with_media']++;
        }
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'announcements' => $announcements,
        'stats' => $stats,
        'count' => count($announcements)
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log('❌ Erreur PDO: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données',
        'announcements' => []
    ]);
} catch (Exception $e) {
    error_log('❌ Exception: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'announcements' => []
    ]);
}
?>
