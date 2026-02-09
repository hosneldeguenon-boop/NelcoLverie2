<?php
/**
 * ✅ CRÉATION TABLE ANNOUNCEMENTS - SYSTÈME D'ANNONCES NELCO LAVERIE
 * Exécuter ce fichier UNE SEULE FOIS pour créer la table
 */

require_once 'config.php';

try {
    $conn = getDBConnection();
    
    // Créer la table announcements
    // Note: Cette version pointe vers la table 'admins' au lieu de 'users'
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
    
    echo "✅ Table 'announcements' créée avec succès !<br>";
    echo "Structure de la table :<br>";
    echo "- id : Identifiant unique de l'annonce<br>";
    echo "- admin_id : ID de l'administrateur qui a publié<br>";
    echo "- title : Titre de l'annonce<br>";
    echo "- content : Contenu de l'annonce<br>";
    echo "- media_type : Type de média (none, image, audio, video_link)<br>";
    echo "- media_path : Chemin du fichier ou lien YouTube/Vimeo<br>";
    echo "- created_at : Date et heure de création<br>";
    echo "- updated_at : Date et heure de dernière modification<br>";
    
} catch (PDOException $e) {
    die("❌ Erreur lors de la création de la table : " . $e->getMessage());
}
?>
