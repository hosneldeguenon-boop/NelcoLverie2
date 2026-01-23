<?php
/**
 * Page d'administration pour modérer les commentaires
 * IMPORTANT: Ce code DOIT être au TOUT DÉBUT du fichier
 */

// Démarrer la session
session_start();

// Empêcher la mise en cache de la page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Vérifier si l'utilisateur est connecté en tant qu'admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Vérifier le timeout de session (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php?timeout=1");
    exit();
}

// Mettre à jour le temps de dernière activité
$_SESSION['last_activity'] = time();

// Connexion à la base de données
require_once 'config.php';
$conn = getDBConnection();

// Récupérer tous les commentaires
$stmt = $conn->prepare("
    SELECT c.id, c.user_id, c.rating, c.comment_text, c.created_at, c.updated_at,
           u.firstname, u.lastname, u.email
    FROM comments c
    JOIN users u ON c.user_id = u.id
    ORDER BY c.created_at DESC
");
$stmt->execute();
$comments = $stmt->fetchAll();

// Statistiques
$stmt = $conn->query("
    SELECT 
        COUNT(*) as total,
        AVG(rating) as avg_rating
    FROM comments
");
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modération des commentaires</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: #f0f2f5;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            margin-bottom: 20px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 700;
        }

        .stat-card .label {
            font-size: 14px;
            opacity: 0.9;
        }

        .comments-grid {
            display: grid;
            gap: 15px;
        }

        .comment-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 5px solid #667eea;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 20px;
        }

        .user-details h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 3px;
        }

        .user-details .email {
            color: #666;
            font-size: 12px;
        }

        .rating {
            display: flex;
            gap: 3px;
        }

        .star {
            color: #ffd700;
            font-size: 18px;
        }

        .star.empty {
            color: #e5e7eb;
        }

        .comment-text {
            color: #333;
            line-height: 1.6;
            margin: 15px 0;
        }

        .comment-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }

        .comment-date {
            color: #666;
            font-size: 12px;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Retour au Dashboard
        </a>

        <div class="header">
            <h1><i class="fas fa-shield-alt"></i> Modération des commentaires</h1>
            <div class="stats">
                <div class="stat-card">
                    <div class="number"><?= $stats['total'] ?></div>
                    <div class="label">Total Commentaires</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?= $stats['avg_rating'] ? round($stats['avg_rating'], 1) : 0 ?></div>
                    <div class="label">Note Moyenne</div>
                </div>
            </div>
        </div>

        <div class="comments-grid">
            <?php if (empty($comments)): ?>
                <div class="comment-card">
                    <div class="empty-state">
                        <i class="fas fa-comment-slash"></i>
                        <p>Aucun commentaire pour le moment.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-card">
                        <div class="comment-header">
                            <div class="user-info">
                                <div class="avatar">
                                    <?= strtoupper(substr($comment['firstname'], 0, 1)) ?>
                                </div>
                                <div class="user-details">
                                    <h3><?= htmlspecialchars($comment['firstname'] . ' ' . $comment['lastname']) ?></h3>
                                    <span class="email"><?= htmlspecialchars($comment['email']) ?></span>
                                </div>
                            </div>
                            <div>
                                <?php if ($comment['rating']): ?>
                                    <div class="rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star star <?= $i <= $comment['rating'] ? '' : 'empty' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="comment-text">
                            <?= nl2br(htmlspecialchars($comment['comment_text'])) ?>
                        </div>
                        
                        <div class="comment-meta">
                            <span class="comment-date">
                                <i class="far fa-clock"></i>
                                <?= date('d/m/Y à H:i', strtotime($comment['created_at'])) ?>
                            </span>
                            
                            <div class="actions">
                                <button class="btn btn-delete" onclick="deleteComment(<?= $comment['id'] ?>)">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Supprimer un commentaire
        async function deleteComment(commentId) {
            if (!confirm('Voulez-vous vraiment SUPPRIMER définitivement ce commentaire ?')) {
                return;
            }

            try {
                const response = await fetch('delete_comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        comment_id: commentId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert('Commentaire supprimé avec succès');
                    location.reload();
                } else {
                    alert('Erreur: ' + data.message);
                }
            } catch (error) {
                alert('Erreur de connexion');
            }
        }
    </script>
</body>
</html>