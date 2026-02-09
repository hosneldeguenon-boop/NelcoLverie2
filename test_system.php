<?php
/**
 * ✅ FICHIER DE TEST - SYSTÈME D'ANNONCES
 * Vérification rapide de l'installation
 */

require_once 'config.php';

echo "<h1>🔍 Test du système d'annonces - Nelco Laverie</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .success { color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; margin: 10px 0; }
    .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; margin: 10px 0; }
    .info { color: blue; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; margin: 10px 0; }
    h2 { color: #667eea; margin-top: 30px; }
</style>";

// Test 1 : Connexion à la base de données
echo "<h2>1️⃣ Test de connexion à la base de données</h2>";
try {
    $conn = getDBConnection();
    echo "<div class='success'>✅ Connexion réussie à la base de données</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Erreur de connexion : " . $e->getMessage() . "</div>";
    exit;
}

// Test 2 : Vérification de la table announcements
echo "<h2>2️⃣ Vérification de la table 'announcements'</h2>";
try {
    $stmt = $conn->query("SHOW TABLES LIKE 'announcements'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>✅ La table 'announcements' existe</div>";
        
        // Afficher la structure
        $columns = $conn->query("DESCRIBE announcements")->fetchAll();
        echo "<div class='info'><strong>Structure de la table :</strong><br>";
        echo "<table border='1' cellpadding='5' style='margin-top: 10px; border-collapse: collapse;'>";
        echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Clé</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "</tr>";
        }
        echo "</table></div>";
    } else {
        echo "<div class='error'>❌ La table 'announcements' n'existe pas. Exécutez create_announcements_table.php</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Erreur : " . $e->getMessage() . "</div>";
}

// Test 3 : Vérification du dossier uploads
echo "<h2>3️⃣ Vérification du dossier uploads</h2>";
$upload_dir = __DIR__ . '/uploads/announcements/';
if (file_exists($upload_dir)) {
    echo "<div class='success'>✅ Le dossier uploads/announcements/ existe</div>";
    
    if (is_writable($upload_dir)) {
        echo "<div class='success'>✅ Le dossier est accessible en écriture</div>";
    } else {
        echo "<div class='error'>⚠️ Le dossier n'est pas accessible en écriture. Permissions requises : 755</div>";
    }
} else {
    echo "<div class='error'>❌ Le dossier uploads/announcements/ n'existe pas</div>";
    echo "<div class='info'>Créez-le avec : mkdir -p uploads/announcements && chmod 755 uploads/announcements</div>";
}

// Test 4 : Compter les annonces existantes
echo "<h2>4️⃣ Statistiques des annonces</h2>";
try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM announcements");
    $result = $stmt->fetch();
    $total = $result['total'];
    
    echo "<div class='info'>📊 Nombre total d'annonces : <strong>" . $total . "</strong></div>";
    
    if ($total > 0) {
        $stmt = $conn->query("
            SELECT 
                media_type, 
                COUNT(*) as count 
            FROM announcements 
            GROUP BY media_type
        ");
        $types = $stmt->fetchAll();
        
        echo "<div class='info'><strong>Répartition par type :</strong><br>";
        foreach ($types as $type) {
            echo "- " . ucfirst($type['media_type']) . " : " . $type['count'] . "<br>";
        }
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Erreur : " . $e->getMessage() . "</div>";
}

// Test 5 : Vérification des sessions admin
echo "<h2>5️⃣ Test des sessions admin</h2>";
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    echo "<div class='success'>✅ Session admin active (ID: " . $_SESSION['admin_id'] . ")</div>";
} else {
    echo "<div class='error'>⚠️ Aucune session admin active</div>";
    echo "<div class='info'>Pour publier des annonces, l'administrateur doit être connecté avec :<br>";
    echo "<code>\$_SESSION['admin_logged_in'] = true;<br>";
    echo "\$_SESSION['admin_id'] = [ID_ADMIN];</code></div>";
}

// Test 6 : Vérification des fichiers
echo "<h2>6️⃣ Vérification des fichiers du système</h2>";
$required_files = [
    'submit_announcement.php' => 'Publication des annonces',
    'get_announcements.php' => 'Récupération des annonces',
    'delete_announcement.php' => 'Suppression des annonces',
    'publish_announcement.html' => 'Page de publication (admin)',
    'announcements.html' => 'Page d\'affichage publique'
];

$all_files_ok = true;
foreach ($required_files as $file => $description) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<div class='success'>✅ $file - $description</div>";
    } else {
        echo "<div class='error'>❌ $file manquant - $description</div>";
        $all_files_ok = false;
    }
}

// Résumé final
echo "<h2>📋 Résumé</h2>";
if ($all_files_ok && file_exists($upload_dir)) {
    echo "<div class='success'><strong>✅ Le système est prêt à être utilisé !</strong></div>";
    echo "<div class='info'>";
    echo "<strong>Prochaines étapes :</strong><br>";
    echo "1. Connectez-vous en tant qu'administrateur<br>";
    echo "2. Accédez à <a href='publish_announcement.html'>publish_announcement.html</a><br>";
    echo "3. Publiez votre première annonce<br>";
    echo "4. Consultez les annonces sur <a href='announcements.html'>announcements.html</a>";
    echo "</div>";
} else {
    echo "<div class='error'><strong>⚠️ Certains éléments nécessitent votre attention</strong></div>";
    echo "<div class='info'>Consultez les erreurs ci-dessus et corrigez-les avant d'utiliser le système.</div>";
}

echo "<hr style='margin: 30px 0;'>";
echo "<p style='color: #666;'>Test effectué le " . date('d/m/Y à H:i:s') . "</p>";
?>
