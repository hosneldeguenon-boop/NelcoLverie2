<?php
/**
 * API pour récupérer les statistiques du dashboard admin
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

// Vérifier l'authentification
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Non authentifié'
    ]);
    exit();
}

try {
    require_once 'config.php';
    
    $conn = getDBConnection();
    
    // Statistiques à récupérer
    $stats = [];
    
    // 1. Nombre total d'utilisateurs
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE status = 'actif'");
    $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 2. Nombre total de commandes (si la table existe)
    try {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM orders");
        $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (Exception $e) {
        $stats['total_orders'] = 0;
    }
    
    // 3. Commandes en attente (si la table existe)
    try {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'en_attente'");
        $stats['pending_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (Exception $e) {
        $stats['pending_orders'] = 0;
    }
    
    // 4. Chiffre d'affaires total (si la table existe)
    try {
        $stmt = $conn->query("SELECT SUM(total_amount) as revenue FROM orders WHERE status = 'completé'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_revenue'] = $result['revenue'] ?? 0;
    } catch (Exception $e) {
        $stats['total_revenue'] = 0;
    }
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log('Erreur get_admin_stats: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors du chargement des statistiques'
    ]);
}
?>