<?php
/**
 * ✅ TABLEAU DE BORD GÉRANT - CYCLE FIDÉLITÉ 11 LAVAGES
 * NOUVELLES FONCTIONNALITÉS:
 * - Affichage des linges avec poids et température
 * - Support des commandes à blanc
 * - Demande du nombre de lavages à la livraison des commandes à blanc
 */

require_once '../config.php';

// Traiter les actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $conn = getDBConnection();
    
    if ($_POST['action'] === 'livrer') {
        $orderId = $_POST['order_id'];
        
        try {
            $conn->beginTransaction();
            
            // Récupérer commande
            $stmt = $conn->prepare("
                SELECT user_id, nombre_lavage_apres, nombre_lavage_avant, status, service_type, order_details
                FROM orders 
                WHERE id = ?
                FOR UPDATE
            ");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            
            if (!$order) {
                throw new Exception('Commande introuvable');
            }
            
            if ($order['status'] === 'livree') {
                throw new Exception('Commande déjà livrée');
            }
            
            $userId = $order['user_id'];
            
            // ✅ NOUVEAU : Pour les commandes à blanc, utiliser le nombre de lavages saisi
            if ($order['service_type'] === 'commande_blanc' && isset($_POST['nombre_lavages_blanc'])) {
                $nombreLavagesEffectues = intval($_POST['nombre_lavages_blanc']);
                
                if ($nombreLavagesEffectues <= 0) {
                    throw new Exception('Le nombre de lavages doit être positif');
                }
                
                // Récupérer les points actuels du client
                $stmt = $conn->prepare("SELECT points_counter FROM users WHERE id = ? FOR UPDATE");
                $stmt->execute([$userId]);
                $userPoints = $stmt->fetch();
                $ancienNombreLavage = intval($userPoints['points_counter']);
                
                // Calculer le nouveau nombre de lavages
                $totalLavages = $ancienNombreLavage + $nombreLavagesEffectues;
                $nombreReductions = floor($totalLavages / LOYALTY_CYCLE);
                $nouveauNombreLavage = $totalLavages % LOYALTY_CYCLE;
                
                // Si on atteint exactement 11 ou multiple, on passe à 0
                if ($nouveauNombreLavage === 0 && $totalLavages > 0) {
                    $nouveauNombreLavage = 0;
                }
                
                // Mettre à jour les informations de la commande
                $stmt = $conn->prepare("
                    UPDATE orders 
                    SET nombre_lavage_avant = ?,
                        nombre_lavage_commande = ?,
                        nombre_lavage_apres = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $ancienNombreLavage,
                    $nombreLavagesEffectues,
                    $nouveauNombreLavage,
                    $orderId
                ]);
                
            } else {
                // Commande normale : utiliser nombre_lavage_apres existant
                $nouveauNombreLavage = intval($order['nombre_lavage_apres']);
            }
            
            // Verrouiller utilisateur
            $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? FOR UPDATE");
            $stmt->execute([$userId]);
            
            // ✅ Mettre à jour points_counter (qui représente nombre_lavage)
            $stmt = $conn->prepare("UPDATE users SET points_counter = ? WHERE id = ?");
            $stmt->execute([$nouveauNombreLavage, $userId]);
            
            // Marquer commande comme livrée
            $stmt = $conn->prepare("
                UPDATE orders 
                SET status = 'livree', 
                    delivered_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$orderId]);
            
            $conn->commit();
            
            $successMessage = "✅ Commande livrée avec succès ! Fidélité mise à jour.";
            
        } catch (Exception $e) {
            $conn->rollBack();
            $errorMessage = "❌ Erreur: " . $e->getMessage();
        }
        
    } elseif ($_POST['action'] === 'annuler') {
        $orderId = $_POST['order_id'];
        $raison = $_POST['raison'] ?? 'Non spécifié';
        
        try {
            $conn->beginTransaction();
            
            // Récupérer infos commande
            $stmt = $conn->prepare("
                SELECT user_id, status, nombre_lavage_avant, nombre_lavage_apres 
                FROM orders 
                WHERE id = ?
                FOR UPDATE
            ");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            
            if (!$order) {
                throw new Exception('Commande introuvable');
            }
            
            // ✅ Si la commande était livrée, restaurer l'ancien score
            if ($order['status'] === 'livree') {
                $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? FOR UPDATE");
                $stmt->execute([$order['user_id']]);
                
                // Restaurer points_counter à sa valeur avant la commande
                $stmt = $conn->prepare("UPDATE users SET points_counter = ? WHERE id = ?");
                $stmt->execute([$order['nombre_lavage_avant'], $order['user_id']]);
            }
            // ✅ Si pas encore livrée, pas besoin de toucher aux points (ils n'ont pas été modifiés)
            
            // Annuler la commande
            $stmt = $conn->prepare("
                UPDATE orders 
                SET status = 'annulee', 
                    cancelled_reason = ?, 
                    cancelled_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$raison, $orderId]);
            
            $conn->commit();
            
            $successMessage = "❌ Commande annulée. Points de fidélité restaurés.";
            
        } catch (Exception $e) {
            $conn->rollBack();
            $errorMessage = "❌ Erreur: " . $e->getMessage();
        }
        
    } elseif ($_POST['action'] === 'supprimer') {
        $orderId = $_POST['order_id'];
        
        $stmt = $conn->prepare("SELECT status FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        if ($order && in_array($order['status'], ['livree', 'annulee'])) {
            $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $successMessage = "🗑️ Commande supprimée.";
        } else {
            $errorMessage = "⚠️ Seules les commandes livrées ou annulées peuvent être supprimées.";
        }
    }
}

// Récupération des commandes
$conn = getDBConnection();

$statusFilter = $_GET['status'] ?? 'en_attente';
$search = $_GET['search'] ?? '';

// Statistiques
$statsQuery = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status IN ('en_attente', 'confirmee', 'en_cours', 'pending', 'en_attente_pesee') THEN 1 ELSE 0 END) as en_attente,
        SUM(CASE WHEN status = 'livree' THEN 1 ELSE 0 END) as livree,
        SUM(CASE WHEN status = 'annulee' THEN 1 ELSE 0 END) as annulee,
        SUM(CASE WHEN status = 'livree' THEN total_amount ELSE 0 END) as ca_total
    FROM orders
";
$stmt = $conn->query($statsQuery);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Requête principale
$query = "
    SELECT 
        o.*,
        u.firstname,
        u.lastname,
        u.email,
        u.phone,
        u.customer_code,
        u.points_counter as nombre_lavage_actuel
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE 1=1
";

$params = [];

if ($statusFilter === 'en_attente') {
    $query .= " AND o.status IN ('en_attente', 'confirmee', 'en_cours', 'pending', 'en_attente_pesee')";
} elseif ($statusFilter === 'livree') {
    $query .= " AND o.status = 'livree'";
} elseif ($statusFilter === 'annulee') {
    $query .= " AND o.status = 'annulee'";
}

if (!empty($search)) {
    $query .= " AND (
        o.order_number LIKE ? OR
        u.firstname LIKE ? OR
        u.lastname LIKE ? OR
        u.customer_code LIKE ? OR
        u.phone LIKE ? OR
        o.order_details LIKE ?
    )";
    $searchParam = "%$search%";
    $params = array_fill(0, 6, $searchParam);
}

if ($statusFilter === 'en_attente') {
    $query .= " ORDER BY o.pickup_date ASC, o.created_at DESC";
} else {
    $query .= " ORDER BY o.created_at DESC";
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Commandes - Nelco</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1800px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; }
        .nav-links { display: flex; gap: 15px; margin-top: 15px; }
        .nav-links a { color: white; text-decoration: none; padding: 8px 15px; background: rgba(255,255,255,0.2); border-radius: 5px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; padding: 20px 30px; background: #f8f9fa; }
        .stat-card { background: white; padding: 15px; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .stat-card .number { font-size: 28px; font-weight: bold; color: #667eea; }
        .toolbar { padding: 20px 30px; display: flex; justify-content: space-between; gap: 15px; border-bottom: 1px solid #e0e0e0; flex-wrap: wrap; }
        .filters { display: flex; gap: 10px; }
        .filter-btn { padding: 8px 16px; border: 2px solid #e0e0e0; background: white; border-radius: 5px; text-decoration: none; color: #333; }
        .filter-btn.active { background: #667eea; color: white; border-color: #667eea; }
        .search-box { flex: 1; min-width: 250px; }
        .search-box input { width: 100%; padding: 10px 15px; border: 2px solid #e0e0e0; border-radius: 5px; }
        .table-container { padding: 30px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 12px; text-align: left; font-weight: 600; background: #f8f9fa; border-bottom: 2px solid #dee2e6; font-size: 13px; }
        td { padding: 15px 12px; border-bottom: 1px solid #dee2e6; font-size: 14px; }
        tr:hover { background: #f8f9fa; }
        .badge { padding: 5px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; display: inline-block; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-delivered { background: #d4edda; color: #155724; }
        .badge-cancelled { background: #f8d7da; color: #721c24; }
        .badge-blanc { background: #e3f2fd; color: #1976d2; border: 2px solid #1976d2; }
        .action-btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; margin: 2px; font-weight: 600; white-space: nowrap; }
        .btn-deliver { background: #28a745; color: white; }
        .btn-deliver:hover { background: #218838; }
        .btn-cancel { background: #dc3545; color: white; }
        .btn-cancel:hover { background: #c82333; }
        .btn-details { background: #17a2b8; color: white; }
        .btn-details:hover { background: #138496; }
        .points-badge { background: #ffd700; color: #856404; padding: 3px 8px; border-radius: 10px; font-weight: bold; font-size: 12px; }
        .success-message { padding: 15px; margin: 20px 30px; border-radius: 5px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error-message { padding: 15px; margin: 20px 30px; border-radius: 5px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        /* Modal styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); }
        .modal.active { display: flex; align-items: center; justify-content: center; animation: fadeIn 0.3s; }
        .modal-content { background: white; padding: 30px; border-radius: 15px; max-width: 900px; width: 90%; max-height: 90vh; overflow-y: auto; animation: slideIn 0.3s; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideIn { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #667eea; }
        .modal-header h2 { color: #667eea; font-size: 24px; }
        .close-btn { background: none; border: none; font-size: 32px; cursor: pointer; color: #999; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.3s; }
        .close-btn:hover { background: #f0f0f0; color: #333; }
        .detail-section { margin: 25px 0; }
        .detail-section h3 { color: #495057; margin-bottom: 15px; font-size: 18px; display: flex; align-items: center; gap: 10px; }
        .detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; }
        .detail-item { background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea; }
        .detail-label { font-size: 12px; color: #6c757d; margin-bottom: 5px; text-transform: uppercase; font-weight: 600; }
        .detail-value { font-weight: 600; color: #333; font-size: 15px; }
        .highlight { background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 5px; display: inline-block; }
        .financial-box { background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); border: 2px solid #667eea; border-radius: 12px; padding: 20px; margin-top: 15px; }
        .financial-item { display: flex; justify-content: space-between; padding: 12px 15px; margin: 8px 0; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .financial-item.total { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: bold; font-size: 18px; margin-top: 15px; }
        .financial-item.reduction { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .financial-label { font-size: 15px; font-weight: 500; }
        .financial-value { font-size: 16px; font-weight: 600; }
        
        /* Cancel modal */
        .cancel-modal-content { max-width: 550px; }
        .form-group { margin: 20px 0; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #495057; }
        .form-group textarea, .form-group input { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-family: inherit; font-size: 14px; }
        .form-group textarea { resize: vertical; }
        .form-group textarea:focus, .form-group input:focus { border-color: #667eea; outline: none; }
        .modal-actions { display: flex; gap: 12px; margin-top: 25px; }
        .modal-actions button { flex: 1; padding: 14px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 15px; transition: all 0.3s; }
        .modal-actions button:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        
        .loyalty-highlight { background: linear-gradient(135deg, #ffd70020 0%, #ffa50020 100%); border: 2px solid #ffd700; border-radius: 10px; padding: 15px; }
        .loyalty-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; margin: 5px 0; }
        .loyalty-arrow { color: #667eea; font-size: 20px; margin: 0 10px; }
        
        /* ✅ NOUVEAU : Styles pour le modal des commandes à blanc */
        .blanc-warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 8px; margin: 15px 0; color: #856404; }
        .blanc-input-group { display: flex; align-items: center; gap: 10px; margin-top: 15px; }
        .blanc-input-group input { flex: 1; padding: 12px; border: 2px solid #ffc107; border-radius: 8px; font-size: 16px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-tachometer-alt"></i> Tableau de Bord Gérant</h1>
            <p>Nelco Laverie - Gestion des commandes</p>
            <div class="nav-links">
                <a href="view_users_v2.php"><i class="fas fa-users"></i> Utilisateurs</a>
                <a href="manage_orders.php" style="background: rgba(255,255,255,0.3);"><i class="fas fa-shopping-cart"></i> Commandes</a>
            </div>
        </div>
        
        <?php if (isset($successMessage)): ?>
        <div class="success-message"><i class="fas fa-check-circle"></i> <?= $successMessage ?></div>
        <?php endif; ?>

        <?php if (isset($errorMessage)): ?>
        <div class="error-message"><i class="fas fa-exclamation-circle"></i> <?= $errorMessage ?></div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card"><div class="number"><?= $stats['total'] ?></div><div class="label">Total</div></div>
            <div class="stat-card"><div class="number"><?= $stats['en_attente'] ?></div><div class="label">En attente</div></div>
            <div class="stat-card"><div class="number"><?= $stats['livree'] ?></div><div class="label">Livrées</div></div>
            <div class="stat-card"><div class="number"><?= $stats['annulee'] ?></div><div class="label">Annulées</div></div>
            <div class="stat-card"><div class="number"><?= number_format($stats['ca_total'], 0, ',', ' ') ?> F</div><div class="label">CA</div></div>
        </div>
        
        <div class="toolbar">
            <div class="filters">
                <a href="?status=en_attente" class="filter-btn <?= $statusFilter === 'en_attente' ? 'active' : '' ?>">
                    En attente (<?= $stats['en_attente'] ?>)
                </a>
                <a href="?status=livree" class="filter-btn <?= $statusFilter === 'livree' ? 'active' : '' ?>">
                    Livrées (<?= $stats['livree'] ?>)
                </a>
                <a href="?status=annulee" class="filter-btn <?= $statusFilter === 'annulee' ? 'active' : '' ?>">
                    Annulées (<?= $stats['annulee'] ?>)
                </a>
            </div>
            
            <div class="search-box">
                <form method="GET">
                    <?php if ($statusFilter !== 'en_attente'): ?>
                        <input type="hidden" name="status" value="<?= $statusFilter ?>">
                    <?php endif; ?>
                    <input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>">
                </form>
            </div>
        </div>
        
        <div class="table-container">
            <?php if (count($orders) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>N° Commande</th>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Collecte</th>
                            <th>Livraison</th>
                            <th>Montant</th>
                            <th>Points Actuels</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): 
                            $orderDetails = json_decode($order['order_details'], true);
                            $isCommandeBlanc = ($order['service_type'] === 'commande_blanc');
                        ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                                    <?php if ($isCommandeBlanc): ?>
                                        <br><span class="badge badge-blanc">📋 À BLANC</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($order['lastname']) ?> <?= htmlspecialchars($order['firstname']) ?></strong><br>
                                    <small><?= htmlspecialchars($order['customer_code']) ?></small>
                                </td>
                                <td><?= $isCommandeBlanc ? 'Commande à blanc' : 'Normale' ?></td>
                                <td><?= date('d/m/Y', strtotime($order['pickup_date'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($order['delivery_date'])) ?></td>
                                <td><strong><?= number_format($order['total_amount'], 0, ',', ' ') ?> F</strong></td>
                                <td>
                                    <span class="points-badge">
                                        <i class="fas fa-star"></i> <?= $order['nombre_lavage_actuel'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    if ($order['status'] === 'livree') {
                                        echo '<span class="badge badge-delivered">Livrée ✓</span>';
                                    } elseif ($order['status'] === 'annulee') {
                                        echo '<span class="badge badge-cancelled">Annulée</span>';
                                    } elseif ($order['status'] === 'en_attente_pesee') {
                                        echo '<span class="badge badge-pending">En attente pesée</span>';
                                    } else {
                                        echo '<span class="badge badge-pending">En attente</span>';
                                    }
                                    ?>
                                </td>
                                <td style="white-space: nowrap;">
                                    <button type="button" class="action-btn btn-details" onclick='showDetails(<?= json_encode($order, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                        <i class="fas fa-eye"></i> Détails
                                    </button>
                                    
                                    <?php if (!in_array($order['status'], ['livree', 'annulee'])): ?>
                                        <?php if ($isCommandeBlanc): ?>
                                            <button type="button" class="action-btn btn-deliver" onclick="showBlancLivraisonModal(<?= $order['id'] ?>)">
                                                <i class="fas fa-check"></i> Livrer
                                            </button>
                                        <?php else: ?>
                                            <form method="POST" style="display: inline-block;">
                                                <input type="hidden" name="action" value="livrer">
                                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                <button type="submit" class="action-btn btn-deliver" onclick="return confirm('✅ Confirmer la livraison ?\n\nLes points de fidélité seront mis à jour.')">
                                                    <i class="fas fa-check"></i> Livrer
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <button type="button" class="action-btn btn-cancel" onclick="showCancelModal(<?= $order['id'] ?>)">
                                            <i class="fas fa-times"></i> Annuler
                                        </button>
                                    <?php elseif ($order['status'] === 'livree'): ?>
                                        <button type="button" class="action-btn btn-cancel" onclick="showCancelModal(<?= $order['id'] ?>)" title="Annuler cette commande livrée (les points seront retirés)">
                                            <i class="fas fa-undo"></i> Annuler
                                        </button>
                                    <?php else: ?>
                                        <small style="color: #6c757d;">Terminée</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; padding: 40px; color: #6c757d;">Aucune commande trouvée</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Détails -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-file-invoice"></i> Détails de la commande</h2>
                <button class="close-btn" onclick="closeDetailsModal()">&times;</button>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>

    <!-- Modal Annulation -->
    <div id="cancelModal" class="modal">
        <div class="modal-content cancel-modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-times-circle"></i> Annuler la commande</h2>
                <button class="close-btn" onclick="closeCancelModal()">&times;</button>
            </div>
            <form method="POST" id="cancelForm">
                <input type="hidden" name="action" value="annuler">
                <input type="hidden" name="order_id" id="cancelOrderId">
                <div class="form-group">
                    <label>Raison de l'annulation *</label>
                    <textarea name="raison" rows="4" required placeholder="Expliquez pourquoi cette commande est annulée..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="closeCancelModal()" style="background: #6c757d; color: white;">Fermer</button>
                    <button type="submit" style="background: #dc3545; color: white;">Confirmer l'annulation</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ✅ NOUVEAU : Modal pour livraison commande à blanc -->
    <div id="blancLivraisonModal" class="modal">
        <div class="modal-content cancel-modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-soap"></i> Livraison - Commande à blanc</h2>
                <button class="close-btn" onclick="closeBlancLivraisonModal()">&times;</button>
            </div>
            <form method="POST" id="blancLivraisonForm">
                <input type="hidden" name="action" value="livrer">
                <input type="hidden" name="order_id" id="blancOrderId">
                
                <div class="blanc-warning">
                    <h4 style="margin-bottom: 10px;"><i class="fas fa-info-circle"></i> Information importante</h4>
                    <p>Cette commande est une <strong>commande à blanc</strong>. Vous devez saisir le nombre de lavages effectués pour cette commande.</p>
                </div>
                
                <div class="form-group">
                    <label>Nombre de lavages effectués *</label>
                    <div class="blanc-input-group">
                        <input type="number" name="nombre_lavages_blanc" id="nombreLavagesBlanc" min="1" required 
                               placeholder="Ex: 3" style="text-align: center;">
                        <span style="color: #6c757d; font-weight: 600;">lavages</span>
                    </div>
                    <small style="color: #6c757d; display: block; margin-top: 8px;">
                        <i class="fas fa-lightbulb"></i> Ce nombre sera ajouté aux points de fidélité du client
                    </small>
                </div>
                
                <div class="modal-actions">
                    <button type="button" onclick="closeBlancLivraisonModal()" style="background: #6c757d; color: white;">Annuler</button>
                    <button type="submit" style="background: #28a745; color: white;">
                        <i class="fas fa-check"></i> Confirmer la livraison
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ✅ FONCTION AFFICHAGE DÉTAILS AVEC SUPPORT LINGES ET COMMANDES À BLANC
        function showDetails(order) {
            const modal = document.getElementById('detailsModal');
            const modalBody = document.getElementById('modalBody');
            
            const details = typeof order.order_details === 'string' 
                ? JSON.parse(order.order_details || '{}') 
                : (order.order_details || {});
            
            const isCommandeBlanc = order.service_type === 'commande_blanc';
            
            // ============================================
            // ✅ GÉNÉRATION DE LA LISTE DES LINGES SÉLECTIONNÉS
            // ============================================
            let lingesHTML = '';
            const linges = details.linges || [];
            
            if (linges.length > 0 && !isCommandeBlanc) {
                const ordinaires = linges.filter(l => l.type === 'ordinaire');
                const volumineux = linges.filter(l => l.type === 'volumineux');
                
                if (ordinaires.length > 0) {
                    lingesHTML += '<h4 style="color: #667eea; margin-top: 15px; margin-bottom: 10px;">📦 Linges Ordinaires</h4>';
                    ordinaires.forEach(linge => {
                        const couleurLabel = linge.couleur === 'blanc' ? '⚪ Blanc' : '🔵 Couleur';
                        const tempLabel = linge.temperature === 'chaud' ? '🔥 Chaud' : 
                                        linge.temperature === 'tiede' ? '🌡️ Tiède' : '❄️ Froid';
                        
                        lingesHTML += `
                            <div class="detail-item">
                                <div class="detail-label">${linge.groupe} - ${couleurLabel} - ${tempLabel}</div>
                                <div class="detail-value highlight">${linge.nombre} pièce(s) × ${linge.proportionUnite} kg = ${(linge.nombre * linge.proportionUnite).toFixed(2)} kg</div>
                            </div>
                        `;
                    });
                }
                
                if (volumineux.length > 0) {
                    lingesHTML += '<h4 style="color: #667eea; margin-top: 15px; margin-bottom: 10px;">🛏️ Linges Volumineux</h4>';
                    volumineux.forEach(linge => {
                        const couleurLabel = linge.couleur === 'blanc' ? '⚪ Blanc' : '🔵 Couleur';
                        const tempLabel = linge.temperature === 'chaud' ? '🔥 Chaud' : 
                                        linge.temperature === 'tiede' ? '🌡️ Tiède' : '❄️ Froid';
                        
                        lingesHTML += `
                            <div class="detail-item">
                                <div class="detail-label">${linge.groupe} - ${couleurLabel} - ${tempLabel}</div>
                                <div class="detail-value highlight">${linge.nombre} pièce(s) × ${linge.proportionUnite} kg = ${(linge.nombre * linge.proportionUnite).toFixed(2)} kg</div>
                            </div>
                        `;
                    });
                }
            }
            
            let html = `
                ${isCommandeBlanc ? `
                    <div style="background: #e3f2fd; border-left: 4px solid #1976d2; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <h3 style="color: #1976d2; margin-bottom: 8px;"><i class="fas fa-clipboard-list"></i> Commande à blanc</h3>
                        <p style="color: #1565c0; font-size: 14px;">Cette commande a été enregistrée sans détails de poids. Les prix ont été fixés après pesée.</p>
                    </div>
                ` : ''}
                
                <div class="detail-section">
                    <h3><i class="fas fa-info-circle"></i> Informations générales</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">N° Commande</div>
                            <div class="detail-value">${order.order_number}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Date commande</div>
                            <div class="detail-value">${new Date(order.created_at).toLocaleString('fr-FR')}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Client (Titulaire compte)</div>
                            <div class="detail-value">${order.firstname} ${order.lastname}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Code client</div>
                            <div class="detail-value highlight">${order.customer_code}</div>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h3><i class="fas fa-user-tag"></i> Client de cette commande</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">Nom du client</div>
                            <div class="detail-value highlight">${details.nomClientSaisi || order.firstname + ' ' + order.lastname}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Téléphone du client</div>
                            <div class="detail-value highlight">${details.telephoneSaisi || order.phone}</div>
                        </div>
                    </div>
                </div>
            `;
            
            // ✅ AFFICHER LA SECTION LINGES SI DES LINGES ONT ÉTÉ SÉLECTIONNÉS
            if (lingesHTML) {
                html += `
                    <div class="detail-section">
                        <h3><i class="fas fa-tshirt"></i> Linges commandés</h3>
                        <div class="detail-grid">
                            ${lingesHTML}
                        </div>
                    </div>
                `;
            }
            
            html += `
                <div class="detail-section">
                    <h3><i class="fas fa-map-marker-alt"></i> Adresses & Dates</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">Collecte</div>
                            <div class="detail-value">${order.pickup_address}</div>
                            <small style="color: #6c757d;">${details.communeCollecte || ''} - ${new Date(order.pickup_date).toLocaleDateString('fr-FR')}</small>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Livraison</div>
                            <div class="detail-value">${order.delivery_address}</div>
                            <small style="color: #6c757d;">${details.communeLivraison || ''} - ${new Date(order.delivery_date).toLocaleDateString('fr-FR')}</small>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h3><i class="fas fa-star"></i> Points de Fidélité (Cycle 11 lavages)</h3>
                    <div class="loyalty-highlight">
                        <div class="loyalty-item">
                            <div>
                                <div class="detail-label">Points AVANT commande</div>
                                <div class="detail-value" style="font-size: 24px; color: #667eea;">${order.nombre_lavage_avant || 0} <small>lavages</small></div>
                            </div>
                            <div class="loyalty-arrow">+</div>
                            <div>
                                <div class="detail-label">Lavages de cette commande</div>
                                <div class="detail-value highlight" style="font-size: 24px;">${order.nombre_lavage_commande || 0} <small>lavages</small></div>
                            </div>
                            <div class="loyalty-arrow">→</div>
                            <div>
                                <div class="detail-label">Points APRÈS livraison</div>
                                <div class="detail-value" style="font-size: 24px; color: #28a745;">${order.nombre_lavage_apres || 0} <small>lavages</small></div>
                            </div>
                        </div>
                        <hr style="margin: 15px 0; border: none; border-top: 2px dashed #ffd700;">
                        <div class="loyalty-item">
                            <div style="flex: 1;">
                                <div class="detail-label">Points actuels du client</div>
                                <div class="detail-value" style="font-size: 28px; color: #667eea;">
                                    <i class="fas fa-star" style="color: #ffd700;"></i> ${order.nombre_lavage_actuel || 0} <small>lavages</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h3><i class="fas fa-dollar-sign"></i> Détails financiers</h3>
                    <div class="financial-box">
                        <div class="financial-item">
                            <span class="financial-label">Prix lavage (brut)</span>
                            <span class="financial-value">${(details.prixLavageBrut || order.washing_price || 0).toLocaleString()} FCFA</span>
                        </div>
                        ${(details.reductionFidelite > 0) ? `
                        <div class="financial-item reduction">
                            <span class="financial-label">🎁 Réduction fidélité</span>
                            <span class="financial-value">-${(details.reductionFidelite || 0).toLocaleString()} FCFA</span>
                        </div>
                        ` : ''}
                        <div class="financial-item">
                            <span class="financial-label">Prix lavage (net)</span>
                            <span class="financial-value">${(order.washing_price || 0).toLocaleString()} FCFA</span>
                        </div>
                        <div class="financial-item">
                            <span class="financial-label">Prix séchage</span>
                            <span class="financial-value">${(details.prixSechage || order.drying_price || 0).toLocaleString()} FCFA</span>
                        </div>
                        <div class="financial-item">
                            <span class="financial-label">Prix pliage</span>
                            <span class="financial-value">${(details.prixPliage || order.folding_price || 0).toLocaleString()} FCFA</span>
                        </div>
                        <div class="financial-item">
                            <span class="financial-label">Prix repassage</span>
                            <span class="financial-value">${(details.prixRepassage || order.ironing_price || 0).toLocaleString()} FCFA</span>
                        </div>
                        <div class="financial-item">
                            <span class="financial-label">Prix collecte/livraison</span>
                            <span class="financial-value">${(details.prixCollecte || order.delivery_price || 0).toLocaleString()} FCFA</span>
                        </div>
                        <div class="financial-item total">
                            <span class="financial-label">TOTAL</span>
                            <span class="financial-value">${(order.total_amount || 0).toLocaleString()} FCFA</span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h3><i class="fas fa-credit-card"></i> Paiement</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-label">Moyen de paiement</div>
                            <div class="detail-value">${details.moyenPaiement || order.payment_method || 'Non spécifié'}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">N° Transaction</div>
                            <div class="detail-value">${order.transaction_id || 'En attente'}</div>
                        </div>
                    </div>
                </div>
            `;
            
            modalBody.innerHTML = html;
            modal.classList.add('active');
        }
        
        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.remove('active');
        }

        function showCancelModal(orderId) {
            document.getElementById('cancelOrderId').value = orderId;
            document.getElementById('cancelModal').classList.add('active');
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').classList.remove('active');
            document.getElementById('cancelForm').reset();
        }

        // ✅ NOUVEAU : Fonctions pour le modal de livraison commande à blanc
        function showBlancLivraisonModal(orderId) {
            document.getElementById('blancOrderId').value = orderId;
            document.getElementById('nombreLavagesBlanc').value = '';
            document.getElementById('blancLivraisonModal').classList.add('active');
        }

        function closeBlancLivraisonModal() {
            document.getElementById('blancLivraisonModal').classList.remove('active');
            document.getElementById('blancLivraisonForm').reset();
        }

        // Fermer modals en cliquant en dehors
        window.onclick = function(event) {
            const detailsModal = document.getElementById('detailsModal');
            const cancelModal = document.getElementById('cancelModal');
            const blancModal = document.getElementById('blancLivraisonModal');
            
            if (event.target === detailsModal) {
                closeDetailsModal();
            }
            if (event.target === cancelModal) {
                closeCancelModal();
            }
            if (event.target === blancModal) {
                closeBlancLivraisonModal();
            }
        }
    </script>
</body>
</html>
