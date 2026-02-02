<?php
/**
 * ✅ TRAITEMENT COMMANDE À BLANC - CORRECTION DU BUG STATUS
 * Enregistre uniquement les données collectées, sans poids ni température
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

try {
    // Vérification session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Utilisateur non connecté');
    }

    $userId = $_SESSION['user_id'];
    
    // Récupération données
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Données invalides');
    }
    
    // Validation des champs requis pour commande à blanc
    $requiredFields = [
        'typeCommande', 'nomClient', 'telephone', 
        'adresseCollecte', 'adresseLivraison',
        'dateCollecte', 'heureCollecte', 
        'dateLivraison', 'heureLivraison'
    ];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Le champ $field est requis");
        }
    }
    
    // Vérifier que c'est bien une commande à blanc
    if ($data['typeCommande'] !== 'commande_blanc') {
        throw new Exception('Type de commande invalide');
    }
    
    // Connexion BDD
    $conn = getDBConnection();
    $conn->beginTransaction();
    
    // Récupérer customer_code
    $stmt = $conn->prepare("
        SELECT customer_code 
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('Utilisateur introuvable');
    }
    
    // Générer numéro de commande
    $orderNumber = 'CMD-BLANC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Préparer les détails de commande (sans poids ni température)
    $orderDetails = [
        'typeCommande' => 'commande_blanc',
        'nomClientSaisi' => cleanInput($data['nomClient']),
        'telephoneSaisi' => cleanInput($data['telephone']),
        'adresseCollecte' => cleanInput($data['adresseCollecte']),
        'descriptionCollecte' => cleanInput($data['descriptionCollecte'] ?? ''),
        'adresseLivraison' => cleanInput($data['adresseLivraison']),
        'descriptionLivraison' => cleanInput($data['descriptionLivraison'] ?? ''),
        'dateCollecte' => $data['dateCollecte'],
        'heureCollecte' => $data['heureCollecte'],
        'dateLivraison' => $data['dateLivraison'],
        'heureLivraison' => $data['heureLivraison']
    ];
    
    // ✅ CORRECTION : Insertion dans la base avec le bon ordre des valeurs
    $stmt = $conn->prepare("
        INSERT INTO orders (
            user_id,
            order_number,
            customer_code,
            service_type,
            pickup_address,
            pickup_date,
            delivery_address,
            delivery_date,
            total_amount,
            washing_price,
            drying_price,
            folding_price,
            ironing_price,
            delivery_price,
            loyalty_discount,
            total_weight,
            order_details,
            payment_method,
            status
        ) VALUES (
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?
        )
    ");
    
    // Combiner date et heure pour pickup_date et delivery_date
    $pickupDateTime = $data['dateCollecte'] . ' ' . $data['heureCollecte'] . ':00';
    $deliveryDateTime = $data['dateLivraison'] . ' ' . $data['heureLivraison'] . ':00';
    
    $stmt->execute([
        $userId,
        $orderNumber,
        $user['customer_code'],
        'commande_blanc',       // service_type
        cleanInput($data['adresseCollecte']) . (isset($data['descriptionCollecte']) && $data['descriptionCollecte'] ? ' - ' . cleanInput($data['descriptionCollecte']) : ''),
        $pickupDateTime,
        cleanInput($data['adresseLivraison']) . (isset($data['descriptionLivraison']) && $data['descriptionLivraison'] ? ' - ' . cleanInput($data['descriptionLivraison']) : ''),
        $deliveryDateTime,
        0,                      // total_amount
        0,                      // washing_price
        0,                      // drying_price
        0,                      // folding_price
        0,                      // ironing_price
        0,                      // delivery_price
        0,                      // loyalty_discount
        0,                      // total_weight
        json_encode($orderDetails, JSON_UNESCAPED_UNICODE),
        'pending',              // payment_method
        'en_attente'            // ✅ status CORRIGÉ : 'en_attente' au lieu de 'en_attente_pesee'
    ]);
    
    $orderId = $conn->lastInsertId();
    
    // Valider transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Commande à blanc enregistrée avec succès',
        'orderId' => $orderId,
        'orderNumber' => $orderNumber
    ]);
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Erreur process_commande_blanc: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
