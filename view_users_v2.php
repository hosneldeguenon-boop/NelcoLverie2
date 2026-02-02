<?php
/**
 * ✅ GESTION UTILISATEURS - SYSTÈME AVEC MODAL
 * Modification : Affichage lecture seule + modal pour ajouter des points
 */

session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../admin_login.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: ../admin_login.php?timeout=1");
    exit();
}

$_SESSION['last_activity'] = time();

require_once '../config.php';

// ============================================
// GESTION AJOUT DE POINTS VIA AJAX
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_points') {
    header('Content-Type: application/json');
    
    $conn = getDBConnection();
    $userId = intval($_POST['user_id']);
    $pointsToAdd = intval($_POST['points_to_add']);
    
    if ($pointsToAdd <= 0) {
        echo json_encode(['success' => false, 'message' => 'Le nombre de points doit être positif']);
        exit;
    }
    
    try {
        $conn->beginTransaction();
        
        // Récupérer les points actuels
        $stmt = $conn->prepare("SELECT points_counter, firstname, lastname FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception('Utilisateur introuvable');
        }
        
        $ancienPoints = intval($user['points_counter']);
        $totalPoints = $ancienPoints + $pointsToAdd;
        
        // Calculer le nouveau nombre de points (avec cycle de 11)
        $nombreReductions = floor($totalPoints / LOYALTY_CYCLE);
        $nouveauPoints = $totalPoints % LOYALTY_CYCLE;
        
        // Si on atteint exactement 11 ou multiple, on passe à 0
        if ($nouveauPoints === 0 && $totalPoints > 0) {
            $nouveauPoints = 0;
        }
        
        // Mettre à jour les points
        $stmt = $conn->prepare("UPDATE users SET points_counter = ? WHERE id = ?");
        $stmt->execute([$nouveauPoints, $userId]);
        
        $conn->commit();
        
        $message = "✅ Points ajoutés avec succès !<br>";
        $message .= "Ancien: {$ancienPoints} | Ajout: {$pointsToAdd} | Total: {$totalPoints}<br>";
        $message .= "Cycles complétés: {$nombreReductions} | Nouveau solde: {$nouveauPoints}/11";
        
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'nouveauPoints' => $nouveauPoints,
            'cyclesCompletes' => $nombreReductions,
            'userName' => $user['firstname'] . ' ' . $user['lastname']
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
    
    exit;
}

// Export CSV (inchangé)
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=utilisateurs_' . date('Y-m-d') . '.csv');
    
    $conn = getDBConnection();
    $stmt = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Code', 'Nom', 'Prénom', 'Email', 'Téléphone', 'WhatsApp', 'Genre', 'Lavages', 'Inscription', 'Statut']);
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['customer_code'],
            $row['lastname'],
            $row['firstname'],
            $row['email'],
            $row['phone'],
            $row['whatsapp'],
            $row['gender'],
            $row['points_counter'] . '/11',
            $row['created_at'],
            $row['status']
        ]);
    }
    
    fclose($output);
    exit;
}

try {
    $conn = getDBConnection();
    
    $stats = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'actif' THEN 1 ELSE 0 END) as actifs,
            SUM(CASE WHEN gender = 'homme' THEN 1 ELSE 0 END) as hommes,
            SUM(CASE WHEN gender = 'femme' THEN 1 ELSE 0 END) as femmes,
            SUM(points_counter) as total_points,
            COUNT(CASE WHEN points_counter >= 10 THEN 1 END) as proche_gratuite
        FROM users
    ")->fetch();
    
    $search = $_GET['search'] ?? '';
    
    if ($search) {
        $stmt = $conn->prepare("
            SELECT * FROM users 
            WHERE lastname LIKE ? OR firstname LIKE ? OR email LIKE ? OR phone LIKE ? OR customer_code LIKE ?
            ORDER BY created_at DESC
        ");
        $searchTerm = "%$search%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    } else {
        $stmt = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
    }
    
    $users = $stmt->fetchAll();
    
} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs - Nelco</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1800px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; }
        .header h1 { margin-bottom: 10px; }
        .nav-links { display: flex; gap: 15px; margin-top: 15px; }
        .nav-links a { color: white; text-decoration: none; padding: 8px 15px; background: rgba(255,255,255,0.2); border-radius: 5px; transition: 0.3s; }
        .nav-links a:hover { background: rgba(255,255,255,0.3); }
        .logout-btn { background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4); padding: 8px 15px; border-radius: 15px; cursor: pointer; float: right; margin-top: -40px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; padding: 20px 30px; background: #f8f9fa; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); text-align: center; }
        .stat-card .number { font-size: 32px; font-weight: bold; color: #667eea; }
        .stat-card .label { color: #6c757d; margin-top: 5px; font-size: 14px; }
        .toolbar { padding: 20px 30px; display: flex; justify-content: space-between; gap: 15px; flex-wrap: wrap; }
        .search-box { flex: 1; min-width: 250px; }
        .search-box input { width: 100%; padding: 10px 15px; border: 2px solid #e0e0e0; border-radius: 5px; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s; }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5568d3; transform: translateY(-2px); }
        .table-container { overflow-x: auto; padding: 0 30px 30px; }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 15px; text-align: left; font-weight: 600; color: #495057; background: #f8f9fa; border-bottom: 2px solid #dee2e6; }
        td { padding: 15px; border-bottom: 1px solid #dee2e6; }
        tr:hover { background: #f8f9fa; cursor: pointer; }
        .customer-code { background: #667eea; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .badge { padding: 5px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .points-badge { display: inline-block; padding: 8px 15px; border-radius: 20px; background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); border: 2px solid #667eea; color: #667eea; font-weight: 600; }
        .points-badge.proche-gratuite { background: #ff6b6b; color: white; border-color: #ff6b6b; animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
        .gender-icon { display: inline-flex; align-items: center; gap: 5px; }
        .no-results { text-align: center; padding: 40px; color: #6c757d; }

        /* NOUVEAU : Styles pour le modal */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
        .modal.active { display: flex; align-items: center; justify-content: center; }
        .modal-content { background: white; border-radius: 15px; width: 90%; max-width: 600px; box-shadow: 0 5px 30px rgba(0,0,0,0.3); animation: slideDown 0.3s ease-out; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-50px); } to { opacity: 1; transform: translateY(0); } }
        .modal-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px 30px; border-radius: 15px 15px 0 0; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h2 { font-size: 22px; }
        .close-btn { background: rgba(255,255,255,0.2); border: none; color: white; font-size: 24px; cursor: pointer; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: 0.3s; }
        .close-btn:hover { background: rgba(255,255,255,0.3); }
        .modal-body { padding: 30px; }
        .info-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .info-item { }
        .info-label { font-size: 13px; color: #6c757d; margin-bottom: 5px; font-weight: 500; }
        .info-value { font-size: 16px; color: #333; font-weight: 600; }
        .points-highlight { background: linear-gradient(135deg, #667eea10 0%, #764ba210 100%); border-left: 4px solid #667eea; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .points-current { text-align: center; font-size: 48px; font-weight: bold; color: #667eea; margin: 15px 0; }
        .add-points-section { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-top: 20px; }
        .add-points-section h3 { color: #333; margin-bottom: 15px; font-size: 18px; }
        .input-group { display: flex; gap: 10px; align-items: center; }
        .input-group input { flex: 1; padding: 12px 15px; border: 2px solid #dee2e6; border-radius: 8px; font-size: 16px; }
        .input-group button { padding: 12px 25px; background: #28a745; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.3s; }
        .input-group button:hover { background: #218838; transform: translateY(-2px); }
        .success-alert { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: none; border: 1px solid #c3e6cb; }
        .success-alert.show { display: block; animation: fadeIn 0.3s; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .error-alert { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: none; border: 1px solid #f5c6cb; }
        .error-alert.show { display: block; animation: fadeIn 0.3s; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <button class="logout-btn" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Déconnexion</button>
            <h1><i class="fas fa-users"></i> Gestion Utilisateurs</h1>
            <p>Système de fidélité - Cycle de 11 lavages</p>
            <div class="nav-links">
                <a href="view_users.php"><i class="fas fa-users"></i> Utilisateurs</a>
                <a href="manage_orders.php"><i class="fas fa-shopping-cart"></i> Commandes</a>
            </div>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="number"><?= $stats['total'] ?></div>
                <div class="label">Total utilisateurs</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['actifs'] ?></div>
                <div class="label">Comptes actifs</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['hommes'] ?></div>
                <div class="label">Hommes</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['femmes'] ?></div>
                <div class="label">Femmes</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['total_points'] ?></div>
                <div class="label">Lavages cumulés</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= $stats['proche_gratuite'] ?></div>
                <div class="label">Proche lavage gratuit</div>
            </div>
        </div>
        
        <div class="toolbar">
            <div class="search-box">
                <form method="GET">
                    <input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>">
                </form>
            </div>
            <a href="?export=csv" class="btn btn-primary">
                <i class="fas fa-download"></i> Exporter CSV
            </a>
        </div>
        
        <div class="table-container">
            <?php if (count($users) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code Client</th>
                            <th>Nom Prénom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Genre</th>
                            <th>Fidélité (Cycle 11)</th>
                            <th>Inscription</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): 
                            $pointsCounter = intval($user['points_counter']);
                            $procheGratuite = $pointsCounter >= 10;
                        ?>
                            <tr onclick="openUserModal(<?= htmlspecialchars(json_encode($user)) ?>)">
                                <td><?= $user['id'] ?></td>
                                <td><span class="customer-code"><?= htmlspecialchars($user['customer_code']) ?></span></td>
                                <td>
                                    <strong><?= htmlspecialchars($user['lastname']) ?></strong> 
                                    <?= htmlspecialchars($user['firstname']) ?>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <i class="fas fa-phone"></i> <?= htmlspecialchars($user['phone']) ?><br>
                                    <small><i class="fab fa-whatsapp" style="color: #25D366;"></i> <?= htmlspecialchars($user['whatsapp']) ?></small>
                                </td>
                                <td>
                                    <span class="gender-icon">
                                        <?php if ($user['gender'] == 'homme'): ?>
                                            <i class="fas fa-mars" style="color: #007bff;"></i> Homme
                                        <?php elseif ($user['gender'] == 'femme'): ?>
                                            <i class="fas fa-venus" style="color: #e83e8c;"></i> Femme
                                        <?php else: ?>
                                            <i class="fas fa-genderless"></i> Autre
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="points-badge <?= $procheGratuite ? 'proche-gratuite' : '' ?>">
                                        <i class="fas fa-star"></i> 
                                        <?= $pointsCounter ?>/11 lavages
                                        <?php if ($procheGratuite): ?>
                                            <i class="fas fa-fire"></i>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                <td><span class="badge badge-success">Actif</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search" style="font-size: 48px; color: #dee2e6; margin-bottom: 15px;"></i>
                    <p>Aucun utilisateur trouvé</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL UTILISATEUR -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-circle"></i> Informations Client</h2>
                <button class="close-btn" onclick="closeUserModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="successAlert" class="success-alert"></div>
                <div id="errorAlert" class="error-alert"></div>
                
                <div class="info-row">
                    <div class="info-item">
                        <div class="info-label">Code Client</div>
                        <div class="info-value" id="modalCustomerCode">-</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nom complet</div>
                        <div class="info-value" id="modalFullName">-</div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value" id="modalEmail">-</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Téléphone</div>
                        <div class="info-value" id="modalPhone">-</div>
                    </div>
                </div>

                <div class="points-highlight">
                    <div class="info-label" style="text-align: center; font-size: 14px;">Points de fidélité actuels</div>
                    <div class="points-current">
                        <i class="fas fa-star" style="color: #ffd700;"></i>
                        <span id="modalPoints">0</span>
                        <small style="font-size: 24px; color: #6c757d;">/11</small>
                    </div>
                    <div style="text-align: center; color: #6c757d; font-size: 14px;">
                        Cycle de 11 lavages - Prochain lavage gratuit à 11
                    </div>
                </div>

                <div class="add-points-section">
                    <h3><i class="fas fa-plus-circle"></i> Ajouter des lavages</h3>
                    <div class="input-group">
                        <input type="number" id="pointsToAdd" min="1" placeholder="Nombre de lavages à ajouter" />
                        <button onclick="addPoints()">
                            <i class="fas fa-check"></i> Confirmer
                        </button>
                    </div>
                    <small style="color: #6c757d; display: block; margin-top: 10px;">
                        <i class="fas fa-info-circle"></i> Exemple : Ajouter 7 lavages à un client qui a 5/11 → Total 12 → 1 cycle complété → Nouveau solde : 1/11
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentUserId = null;

        function openUserModal(user) {
            currentUserId = user.id;
            
            document.getElementById('modalCustomerCode').textContent = user.customer_code;
            document.getElementById('modalFullName').textContent = user.lastname + ' ' + user.firstname;
            document.getElementById('modalEmail').textContent = user.email;
            document.getElementById('modalPhone').textContent = user.phone;
            document.getElementById('modalPoints').textContent = user.points_counter;
            
            // Réinitialiser les alertes et le champ
            document.getElementById('successAlert').classList.remove('show');
            document.getElementById('errorAlert').classList.remove('show');
            document.getElementById('pointsToAdd').value = '';
            
            document.getElementById('userModal').classList.add('active');
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.remove('active');
            currentUserId = null;
        }

        function addPoints() {
            const pointsToAdd = parseInt(document.getElementById('pointsToAdd').value);
            
            if (!pointsToAdd || pointsToAdd <= 0) {
                showError('Veuillez entrer un nombre de lavages valide (minimum 1)');
                return;
            }

            // Désactiver le bouton pendant le traitement
            const btn = event.target.closest('button');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add_points&user_id=${currentUserId}&points_to_add=${pointsToAdd}`
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Confirmer';
                
                if (data.success) {
                    showSuccess(data.message);
                    document.getElementById('modalPoints').textContent = data.nouveauPoints;
                    document.getElementById('pointsToAdd').value = '';
                    
                    // Recharger la page après 2 secondes pour mettre à jour le tableau
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Confirmer';
                showError('Erreur de connexion au serveur');
                console.error('Error:', error);
            });
        }

        function showSuccess(message) {
            const alert = document.getElementById('successAlert');
            alert.innerHTML = '<i class="fas fa-check-circle"></i> ' + message;
            alert.classList.add('show');
            
            document.getElementById('errorAlert').classList.remove('show');
        }

        function showError(message) {
            const alert = document.getElementById('errorAlert');
            alert.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + message;
            alert.classList.add('show');
            
            document.getElementById('successAlert').classList.remove('show');
        }

        function logout() {
            if (confirm('Voulez-vous vraiment vous déconnecter ?')) {
                fetch('../admin_logout.php', { method: 'POST' })
                    .then(() => window.location.href = '../admin_login.php');
            }
        }

        // Fermer le modal en cliquant en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('userModal');
            if (event.target === modal) {
                closeUserModal();
            }
        }
    </script>
</body>
</html>
