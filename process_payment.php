<?php
/**
 * Script de traitement du paiement
 * Valide le paiement et met à jour le statut de la commande
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Session expirée'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $orderId = $data['orderId'] ?? 0;
    $method = $data['method'] ?? '';
    $amount = $data['amount'] ?? 0;
    
    if (!$orderId || !$method) {
        throw new Exception('Données manquantes');
    }
    
    $conn = getDBConnection();
    
    // Vérifier que la commande existe et appartient à l'utilisateur
    $stmt = $conn->prepare("
        SELECT id, user_id, status 
        FROM orders 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Commande non trouvée');
    }
    
    // Générer un numéro de transaction
    $transactionId = 'TRX-' . date('YmdHis') . '-' . rand(1000, 9999);
    
    // Déterminer le statut selon le moyen de paiement
    if ($method === 'livraison') {
        $newStatus = 'en_attente';
        $paymentStatus = 'pending';
    } else {
        $newStatus = 'en_attente';
        $paymentStatus = 'success';
    }
    
    // Mettre à jour la commande
    $stmt = $conn->prepare("
        UPDATE orders 
        SET status = ?,
            payment_status = ?,
            transaction_id = ?,
            payment_date = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$newStatus, $paymentStatus, $transactionId, $orderId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Paiement effectué avec succès',
        'transactionId' => $transactionId
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>