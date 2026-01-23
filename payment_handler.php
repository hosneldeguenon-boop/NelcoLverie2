<?php
/**
 * ✅ GESTIONNAIRE DE PAIEMENT - CYCLE 11 LAVAGES
 * Correction du bug de redirection paiement à la livraison
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

// Vérification session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session expirée']);
    exit;
}

// Vérification méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    // Récupération des données
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    error_log("=== PAYMENT HANDLER ===");
    error_log("Data reçue: " . print_r($data, true));
    
    $orderId = $data['orderId'] ?? 0;
    $method = $data['method'] ?? '';
    $amount = $data['amount'] ?? 0;
    $phoneNumber = $data['phoneNumber'] ?? '';
    
    // Validation
    if (!$orderId || !$method || !$amount) {
        throw new Exception('Données manquantes');
    }
    
    $conn = getDBConnection();
    
    // Vérifier que la commande existe et appartient à l'utilisateur
    $stmt = $conn->prepare("
        SELECT id, user_id, total_amount, status 
        FROM orders 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Commande non trouvée');
    }
    
    // Vérifier le montant (avec tolérance pour arrondis)
    $diff = abs($order['total_amount'] - $amount);
    if ($diff > 1) {
        error_log("Différence montant: Attendu={$order['total_amount']}, Reçu=$amount, Diff=$diff");
        throw new Exception("Montant incorrect");
    }
    
    // ============================================
    // ✅ PAIEMENT À LA LIVRAISON
    // ============================================
    if ($method === 'livraison') {
        error_log(">>> PAIEMENT À LA LIVRAISON");
        
        // Générer un numéro de transaction
        $transactionId = 'COD-' . date('YmdHis') . '-' . rand(1000, 9999);
        
        // Mise à jour de la commande
        $stmt = $conn->prepare("
            UPDATE orders 
            SET status = 'en_attente',
                payment_status = 'pending',
                payment_method = 'cash_on_delivery',
                transaction_id = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $result = $stmt->execute([$transactionId, $orderId]);
        
        error_log("Update result: " . ($result ? 'SUCCESS' : 'FAILED'));
        error_log("Rows affected: " . $stmt->rowCount());
        
        if (!$result) {
            throw new Exception("Échec mise à jour commande");
        }
        
        // Réponse réussie
        $response = [
            'success' => true,
            'message' => 'Commande enregistrée. Paiement à la livraison.',
            'payment_method' => 'livraison',
            'transaction_id' => $transactionId,
            'redirect' => 'order_summary.php?orderId=' . $orderId
        ];
        
        error_log("Response: " . json_encode($response));
        
        echo json_encode($response);
        exit;
    }
    
    // ============================================
    // PAIEMENTS MOBILE MONEY
    // ============================================
    
    if (!$phoneNumber) {
        throw new Exception('Numéro de téléphone requis pour ce mode de paiement');
    }
    
    $apiUrl = '';
    $providerName = '';
    
    switch ($method) {
        case 'mtn':
            $apiUrl = PAYMENT_API_MTN ?? '';
            $providerName = 'MTN Mobile Money';
            break;
        case 'moov':
            $apiUrl = PAYMENT_API_MOOV ?? '';
            $providerName = 'Moov Money';
            break;
        case 'celtiis':
            $apiUrl = PAYMENT_API_CELTIIS ?? '';
            $providerName = 'Celtiis Money';
            break;
        default:
            throw new Exception('Méthode de paiement invalide');
    }
    
    // Vérifier si l'API est configurée
    if (empty($apiUrl)) {
        echo json_encode([
            'success' => false,
            'message' => 'Le service ' . $providerName . ' n\'est pas encore configuré. Veuillez choisir "Paiement à la livraison".',
            'error_code' => 'API_NOT_CONFIGURED'
        ]);
        exit;
    }
    
    // Simuler un paiement réussi (car les APIs ne sont pas encore configurées)
    // Dans un environnement de production, il faudrait vraiment appeler l'API
    
    $transactionId = strtoupper($method) . '-' . date('YmdHis') . '-' . rand(1000, 9999);
    
    // Mise à jour de la commande
    $stmt = $conn->prepare("
        UPDATE orders 
        SET status = 'en_attente',
            payment_status = 'success',
            payment_method = ?,
            transaction_id = ?,
            payment_date = NOW(),
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$method, $transactionId, $orderId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Paiement effectué avec succès !',
        'transaction_id' => $transactionId,
        'payment_method' => $providerName,
        'redirect' => 'order_summary.php?orderId=' . $orderId
    ]);
    
} catch (Exception $e) {
    error_log("Exception payment_handler: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>