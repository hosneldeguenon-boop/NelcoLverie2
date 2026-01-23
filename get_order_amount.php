<?php
/**
 * API pour récupérer le montant d'une commande
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

try {
    // Vérification session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Session expirée');
    }
    
    // Récupération de l'ID de commande
    $orderId = $_GET['orderId'] ?? 0;
    
    if (!$orderId) {
        throw new Exception('ID commande manquant');
    }
    
    // Connexion base de données
    $conn = getDBConnection();
    
    // Récupérer le montant de la commande
    $stmt = $conn->prepare("
        SELECT total_amount, order_number 
        FROM orders 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Commande non trouvée');
    }
    
    // Retourner le montant
    echo json_encode([
        'success' => true,
        'amount' => $order['total_amount'],
        'orderNumber' => $order['order_number']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>