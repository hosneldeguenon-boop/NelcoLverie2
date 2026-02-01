<?php
/**
 * Tableau de bord administrateur
 * 
 * MODIFICATION: Changement de terminologie
 * - "Points de fidélité" → "Nombre de lavages"
 */

session_start();

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Timeout de session (30 minutes d'inactivité)
$timeout_duration = 1800;

if (isset($_SESSION['admin_login_time'])) {
    $elapsed_time = time() - $_SESSION['admin_login_time'];
    
    if ($elapsed_time > $timeout_duration) {
        session_unset();
        session_destroy();
        header("Location: admin_login.php?timeout=1");
        exit();
    }
}

// Mettre à jour le timestamp
$_SESSION['admin_login_time'] = time();

// Récupérer les infos de l'admin
$admin_firstname = $_SESSION['admin_firstname'] ?? 'Admin';
$admin_lastname = $_SESSION['admin_lastname'] ?? '';
$admin_username = $_SESSION['admin_username'] ?? '';
$admin_email = $_SESSION['admin_email'] ?? '';

// Empêcher le cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Connexion à la base pour récupérer les stats
require_once 'config.php';
$conn = getDBConnection();

// Récupérer les statistiques
try {
    // Total utilisateurs
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total admins
    $stmt = $conn->query("SELECT COUNT(*) as total FROM admins WHERE status = 'actif'");
    $totalAdmins = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total commandes
    $stmt = $conn->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // NOUVEAU: Total des lavages effectués (somme de tous les points_counter)
    $stmt = $conn->query("SELECT SUM(points_counter) as total_lavages FROM users");
    $totalLavages = $stmt->fetch(PDO::FETCH_ASSOC)['total_lavages'] ?? 0;
    
} catch (Exception $e) {
    error_log('Erreur récupération stats: ' . $e->getMessage());
    $totalUsers = 0;
    $totalAdmins = 0;
    $totalOrders = 0;
    $totalLavages = 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Nelco Laverie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f5f7fa;
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            color: white;
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            color: white;
            text-align: right;
        }

        .user-info .name {
            font-weight: 600;
            font-size: 14px;
        }

        .user-info .role {
            font-size: 12px;
            opacity: 0.9;
        }

        .btn-logout {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .welcome-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .welcome-card h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .welcome-card p {
            color: #666;
            font-size: 14px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .stat-icon.blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .stat-icon.orange {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-icon.purple {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-content h3 {
            font-size: 28px;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-content p {
            color: #999;
            font-size: 14px;
        }

        .quick-actions {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .quick-actions h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 20px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            text-decoration: none;
            justify-content: center;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin-responsive.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <i class="fas fa-tachometer-alt"></i>
            Nelco Laverie - Admin
        </div>
        <div class="navbar-user">
            <div class="user-info">
                <div class="name"><?php echo htmlspecialchars($admin_firstname . ' ' . $admin_lastname); ?></div>
                <div class="role">@<?php echo htmlspecialchars($admin_username); ?></div>
            </div>
            <button class="btn-logout" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </button>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-card">
            <h1>👋 Bienvenue, <?php echo htmlspecialchars($admin_firstname); ?> !</h1>
            <p>Vous êtes connecté avec succès à votre espace d'administration</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $totalUsers; ?></h3>
                    <p>Utilisateurs inscrits</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $totalAdmins; ?></h3>
                    <p>Administrateurs actifs</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $totalOrders; ?></h3>
                    <p>Commandes totales</p>
                </div>
            </div>

            <!-- MODIFICATION: Nouvelle carte pour les lavages -->
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-soap"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $totalLavages; ?></h3>
                    <p>Lavages effectués</p>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <h2>🚀 Actions rapides</h2>
            <div class="actions-grid">
                <a href="admin/manage_orders.php" class="action-btn">
                    <i class="fas fa-list"></i>
                    Gérer les commandes
                </a>
                <a href="admin/view_users_v2.php" class="action-btn">
                    <i class="fas fa-users-cog"></i>
                    Voir les utilisateurs
                </a>
                <a href="view_admins.php" class="action-btn">
                    <i class="fas fa-user-shield"></i>
                    Liste des admins
                </a>
                <a href="index.html" class="action-btn">
                    <i class="fas fa-home"></i>
                    Retour au site
                </a>
            </div>
        </div>
    </div>

    <script>
        // Fonction de déconnexion
        function logout() {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                window.location.href = 'admin_logout.php';
            }
        }

        // Rafraîchir les stats toutes les 30 secondes
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>