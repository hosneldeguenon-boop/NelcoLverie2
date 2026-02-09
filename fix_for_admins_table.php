<?php
/**
 * 🔧 CORRECTION FINALE - Table announcements avec référence à la table ADMINS
 * Adapté à votre structure existante
 */

require_once 'config.php';

echo "<h1>🔧 Correction pour table ADMINS</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .success { color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; margin: 10px 0; }
    .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; margin: 10px 0; }
    .warning { color: orange; padding: 10px; background: #fff3cd; border: 1px solid #ffc107; margin: 10px 0; }
    .info { color: blue; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; margin: 10px 0; }
    h2 { color: #667eea; margin-top: 30px; }
    table { border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #667eea; color: white; }
</style>";

try {
    $conn = getDBConnection();
    
    echo "<h2>🔍 Diagnostic de votre structure</h2>";
    
    // Vérifier la table admins
    $stmt = $conn->query("SHOW TABLES LIKE 'admins'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>✅ Table 'admins' détectée</div>";
        
        // Afficher les admins
        $stmt = $conn->query("SELECT * FROM admins LIMIT 5");
        $admins = $stmt->fetchAll();
        
        if (count($admins) > 0) {
            echo "<div class='info'><strong>Admins trouvés (" . count($admins) . ") :</strong></div>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Prénom</th><th>Nom</th><th>Email</th></tr>";
            foreach ($admins as $admin) {
                echo "<tr>";
                echo "<td>" . ($admin['id'] ?? '-') . "</td>";
                echo "<td>" . ($admin['firstname'] ?? $admin['prenom'] ?? '-') . "</td>";
                echo "<td>" . ($admin['lastname'] ?? $admin['nom'] ?? '-') . "</td>";
                echo "<td>" . ($admin['email'] ?? '-') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<div class='error'>❌ Table 'admins' non trouvée</div>";
        exit;
    }
    
    echo "<h2>Étape 1 : Sauvegarde des annonces existantes</h2>";
    
    $backup_data = [];
    $stmt = $conn->query("SHOW TABLES LIKE 'announcements'");
    
    if ($stmt->rowCount() > 0) {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM announcements");
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            echo "<div class='warning'>⚠️ Sauvegarde de $count annonce(s)...</div>";
            $stmt = $conn->query("SELECT * FROM announcements");
            $backup_data = $stmt->fetchAll();
            echo "<div class='success'>✅ Sauvegarde réussie</div>";
        } else {
            echo "<div class='info'>Aucune annonce à sauvegarder</div>";
        }
    }
    
    echo "<h2>Étape 2 : Suppression de l'ancienne table</h2>";
    
    $conn->exec("DROP TABLE IF EXISTS announcements");
    echo "<div class='success'>✅ Ancienne table supprimée</div>";
    
    echo "<h2>Étape 3 : Création de la nouvelle table (avec référence à ADMINS)</h2>";
    
    // Table avec référence à la table admins
    $sql = "CREATE TABLE IF NOT EXISTS announcements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        media_type ENUM('none', 'image', 'audio', 'video_link') DEFAULT 'none',
        media_path VARCHAR(500) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_created_at (created_at DESC),
        INDEX idx_admin_id (admin_id),
        FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    
    echo "<div class='success'>✅ Nouvelle table créée avec référence à la table ADMINS</div>";
    echo "<div class='info'>La contrainte FOREIGN KEY pointe maintenant vers admins.id</div>";
    
    echo "<h2>Étape 4 : Restauration des données</h2>";
    
    if (count($backup_data) > 0) {
        $stmt = $conn->prepare("
            INSERT INTO announcements (id, admin_id, title, content, media_type, media_path, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $restored = 0;
        foreach ($backup_data as $announcement) {
            try {
                $stmt->execute([
                    $announcement['id'],
                    $announcement['admin_id'],
                    $announcement['title'],
                    $announcement['content'],
                    $announcement['media_type'],
                    $announcement['media_path'],
                    $announcement['created_at'],
                    $announcement['updated_at']
                ]);
                $restored++;
            } catch (PDOException $e) {
                echo "<div class='warning'>⚠️ Impossible de restaurer l'annonce #" . $announcement['id'] . " (admin_id invalide)</div>";
            }
        }
        
        echo "<div class='success'>✅ $restored annonce(s) restaurée(s)</div>";
    } else {
        echo "<div class='info'>Aucune donnée à restaurer</div>";
    }
    
    echo "<h2>✅ Correction terminée avec succès !</h2>";
    
    echo "<div class='success'><strong>Votre système d'annonces est maintenant configuré pour la table ADMINS.</strong></div>";
    
    echo "<div class='info'>";
    echo "<strong>Prochaines étapes :</strong><br>";
    echo "1. ✅ Assurez-vous d'être connecté en tant qu'admin<br>";
    echo "2. ✅ Vérifiez que \$_SESSION['admin_id'] correspond à un ID dans la table admins<br>";
    echo "3. ✅ Testez la publication d'une annonce<br>";
    echo "4. ⚠️ <strong>SUPPRIMEZ ce fichier après utilisation</strong>";
    echo "</div>";
    
    // Test de connexion actuelle
    session_start();
    echo "<h2>🔍 Vérification de votre session actuelle</h2>";
    
    if (isset($_SESSION['admin_id'])) {
        $admin_id = $_SESSION['admin_id'];
        echo "<div class='info'>Admin ID dans la session : <strong>$admin_id</strong></div>";
        
        // Vérifier si cet admin existe
        $stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo "<div class='success'>✅ Cet admin existe dans la table admins</div>";
            echo "<div class='info'><strong>Vous êtes prêt à publier !</strong></div>";
        } else {
            echo "<div class='error'>❌ L'admin_id $admin_id n'existe pas dans la table admins</div>";
            echo "<div class='warning'>Reconnectez-vous avec un compte admin valide</div>";
        }
    } else {
        echo "<div class='warning'>⚠️ Aucun admin_id dans la session</div>";
        echo "<div class='info'>Connectez-vous en tant qu'administrateur</div>";
    }
    
    // Structure finale
    echo "<h2>📋 Structure finale de la table</h2>";
    $stmt = $conn->query("DESCRIBE announcements");
    $columns = $stmt->fetchAll();
    
    echo "<table>";
    echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Clé</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Erreur PDO : " . $e->getMessage() . "</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Erreur : " . $e->getMessage() . "</div>";
}

echo "<hr style='margin: 30px 0;'>";
echo "<p style='color: #666;'>Correction effectuée le " . date('d/m/Y à H:i:s') . "</p>";
?>
