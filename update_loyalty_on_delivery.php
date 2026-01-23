<?php
/**
 * ✅ MISE À JOUR DU COMPTEUR FIDÉLITÉ LORS DE LA LIVRAISON
 * À appeler uniquement quand l'admin marque une commande comme "livrée"
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

try {
    // ============================================
    // 1. VÉRIFICATION ADMIN
    // ============================================
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        throw new Exception('Accès non autorisé');
    }
    
    // ============================================
    // 2. RÉCUPÉRATION DONNÉES
    // ============================================
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $orderId = intval($data['orderId'] ?? 0);
    
    if (!$orderId) {
        throw new Exception('ID commande manquant');
    }
    
    $conn = getDBConnection();
    
    // ============================================
    // 3. DÉMARRER TRANSACTION
    // ============================================
    $conn->beginTransaction();
    
    // ============================================
    // 4. RÉCUPÉRER COMMANDE
    // ============================================
    $stmt = $conn->prepare("
        SELECT 
            id, 
            user_id, 
            status, 
            nombre_lavage_apres, 
            payment_status,
            loyalty_applied
        FROM orders 
        WHERE id = ?
        FOR UPDATE
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Commande introuvable');
    }
    
    // ============================================
    // 5. VÉRIFICATIONS
    // ============================================
    if ($order['status'] === 'livree') {
        throw new Exception('Commande déjà livrée');
    }
    
    if ($order['payment_status'] !== 'success' && $order['payment_status'] !== 'pending') {
        throw new Exception('Paiement non validé');
    }
    
    if ($order['loyalty_applied'] == 1) {
        throw new Exception('Fidélité déjà appliquée');
    }
    
    // ============================================
    // 6. VERROUILLER UTILISATEUR
    // ============================================
    $stmt = $conn->prepare("
        SELECT id, nombre_lavage 
        FROM users 
        WHERE id = ?
        FOR UPDATE
    ");
    $stmt->execute([$order['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('Utilisateur introuvable');
    }
    
    $nouveauNombreLavage = intval($order['nombre_lavage_apres']);
    
    // ============================================
    // 7. METTRE À JOUR UTILISATEUR
    // ============================================
    $stmt = $conn->prepare("
        UPDATE users 
        SET nombre_lavage = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$nouveauNombreLavage, $order['user_id']]);
    
    // ============================================
    // 8. METTRE À JOUR COMMANDE
    // ============================================
    $stmt = $conn->prepare("
        UPDATE orders 
        SET status = 'livree',
            loyalty_applied = 1,
            delivered_at = NOW(),
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$orderId]);
    
    // ============================================
    // 9. LOGGER L'OPÉRATION
    // ============================================
    $stmt = $conn->prepare("
        INSERT INTO loyalty_log (
            user_id,
            order_id,
            ancien_nombre_lavage,
            nouveau_nombre_lavage,
            created_by
        ) VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $order['user_id'],
        $orderId,
        $user['nombre_lavage'],
        $nouveauNombreLavage,
        $_SESSION['user_id']
    ]);
    
    // ============================================
    // 10. VALIDER TRANSACTION
    // ============================================
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Commande livrée et fidélité mise à jour',
        'ancien_nombre_lavage' => $user['nombre_lavage'],
        'nouveau_nombre_lavage' => $nouveauNombreLavage
    ]);
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Erreur update_loyalty: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>