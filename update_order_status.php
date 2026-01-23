<?php
/**
 * Script de mise à jour du statut des commandes
 * 
 * FONCTIONNALITÉ CRITIQUE:
 * Lorsqu'une commande passe au statut "terminé",
 * le nombre de lavages (points_counter) du client est mis à jour
 * en ajoutant la valeur de 'lav' stockée dans order_details.
 * 
 * UTILISATION:
 * - Appelé depuis le dashboard admin
 * - Appelé automatiquement lors de la validation d'une livraison
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once 'config.php';

try {
    // Vérifier que l'admin est connecté
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        throw new Exception('Accès non autorisé');
    }
    
    // Récupérer les données
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['orderId']) || !isset($data['newStatus'])) {
        throw new Exception('Données invalides');
    }
    
    $orderId = intval($data['orderId']);
    $newStatus = cleanInput($data['newStatus']);
    
    // Statuts valides
    $validStatuses = ['en_attente_paiement', 'paye', 'en_cours', 'termine', 'annule'];
    
    if (!in_array($newStatus, $validStatuses)) {
        throw new Exception('Statut invalide');
    }
    
    $conn = getDBConnection();
    
    // Commencer une transaction
    $conn->beginTransaction();
    
    // Récupérer les informations de la commande
    $stmt = $conn->prepare("
        SELECT 
            o.id,
            o.user_id,
            o.status as old_status,
            o.order_details,
            u.points_counter
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Commande introuvable');
    }
    
    $oldStatus = $order['old_status'];
    
    // Mettre à jour le statut de la commande
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $orderId]);
    
    // ============================================
    // LOGIQUE CRITIQUE: Mise à jour du nombre de lavages
    // ============================================
    /**
     * Si la commande passe au statut "terminé" ET qu'elle n'était pas
     * déjà terminée, on met à jour le nombre_lavage du client.
     */
    if ($newStatus === 'termine' && $oldStatus !== 'termine') {
        
        // Récupérer les détails de la commande
        $orderDetails = json_decode($order['order_details'], true);
        
        // Récupérer le compteur lav
        $lav = intval($orderDetails['lav'] ?? 0);
        
        if ($lav > 0) {
            // Mettre à jour le nombre de lavages du client
            $stmt = $conn->prepare("
                UPDATE users 
                SET points_counter = points_counter + ? 
                WHERE id = ?
            ");
            $stmt->execute([$lav, $order['user_id']]);
            
            // Log pour traçabilité
            error_log(sprintf(
                "Commande #%d terminée: +%d lavages pour user #%d (ancien: %d, nouveau: %d)",
                $orderId,
                $lav,
                $order['user_id'],
                $order['points_counter'],
                $order['points_counter'] + $lav
            ));
        }
    }
    
    // ============================================
    // LOGIQUE BONUS: Annulation d'une commande terminée
    // ============================================
    /**
     * Si une commande terminée est annulée, on retire les lavages
     * qui avaient été ajoutés au client.
     */
    if ($newStatus === 'annule' && $oldStatus === 'termine') {
        
        $orderDetails = json_decode($order['order_details'], true);
        $lav = intval($orderDetails['lav'] ?? 0);
        
        if ($lav > 0) {
            // Retirer les lavages du client
            $stmt = $conn->prepare("
                UPDATE users 
                SET points_counter = GREATEST(0, points_counter - ?) 
                WHERE id = ?
            ");
            $stmt->execute([$lav, $order['user_id']]);
            
            error_log(sprintf(
                "Commande #%d annulée après terminaison: -%d lavages pour user #%d",
                $orderId,
                $lav,
                $order['user_id']
            ));
        }
    }
    
    // Valider la transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Statut mis à jour avec succès',
        'oldStatus' => $oldStatus,
        'newStatus' => $newStatus
    ]);
    
} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>