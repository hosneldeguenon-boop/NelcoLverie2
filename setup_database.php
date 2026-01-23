<?php
/**
 * Script d'initialisation de la base de données
 * À exécuter UNE SEULE FOIS pour créer la base de données et les tables
 */

// Configuration
$host = 'localhost';
$user = 'root';  // Remplace par ton nom d'utilisateur MySQL
$pass = '';      // Remplace par ton mot de passe MySQL
$dbname = 'laverie_db';

try {
    // Connexion sans sélectionner de base de données
    $conn = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Créer la base de données si elle n'existe pas
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $conn->exec($sql);
    echo "✓ Base de données '$dbname' créée avec succès<br>";
    
    // Sélectionner la base de données
    $conn->exec("USE $dbname");
    
    // Créer la table users avec compteur personnel sécurisé
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lastname VARCHAR(50) NOT NULL,
        firstname VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        whatsapp VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        gender ENUM('homme', 'femme', 'autre') NOT NULL,
        password VARCHAR(255) NOT NULL,
        points_counter INT DEFAULT 0 COMMENT 'Compteur de points (1 point par commande)',
        customer_code VARCHAR(20) NOT NULL UNIQUE COMMENT 'Code client unique sécurisé',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        status ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
        INDEX idx_email (email),
        INDEX idx_phone (phone),
        INDEX idx_customer_code (customer_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "✓ Table 'users' créée avec succès (avec compteur de points et code client)<br>";
    
    // Créer la table pour les commandes
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        order_number VARCHAR(20) NOT NULL UNIQUE,
        service_type VARCHAR(50) NOT NULL,
        pickup_address TEXT NOT NULL,
        pickup_date DATE NOT NULL,
        delivery_address TEXT NOT NULL,
        delivery_date DATE NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        order_details JSON COMMENT 'Détails de la commande (poids, températures, etc.)',
        payment_method VARCHAR(50) COMMENT 'Moyen de paiement',
        payment_status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
        transaction_id VARCHAR(100) COMMENT 'ID de transaction paiement',
        payment_date DATETIME COMMENT 'Date du paiement',
        points_at_order INT DEFAULT 0 COMMENT 'Points du client au moment de la commande',
        status ENUM('en_attente_paiement', 'en_attente', 'confirmee', 'en_cours', 'livree', 'annulee') DEFAULT 'en_attente',
        cancelled_reason TEXT COMMENT 'Raison de l\'annulation',
        cancelled_at DATETIME COMMENT 'Date d\'annulation',
        delivered_at DATETIME COMMENT 'Date de livraison effective',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_id (user_id),
        INDEX idx_order_number (order_number),
        INDEX idx_status (status),
        INDEX idx_pickup_date (pickup_date),
        INDEX idx_delivery_date (delivery_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "✓ Table 'orders' créée avec succès<br>";
    
    // Créer la table pour les tokens de réinitialisation de mot de passe
    $conn->exec("
        CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(64) NOT NULL UNIQUE,
            expiry DATETIME NOT NULL,
            used BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_token (token),
            INDEX idx_expiry (expiry)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Table 'password_reset_tokens' créée avec succès<br>";
    
    // Créer un trigger pour incrémenter automatiquement le compteur de points à chaque commande livrée
    $sql = "CREATE TRIGGER IF NOT EXISTS increment_points_after_delivery
            AFTER UPDATE ON orders
            FOR EACH ROW
            BEGIN
                IF NEW.status = 'livree' AND OLD.status != 'livree' THEN
                    UPDATE users 
                    SET points_counter = points_counter + 1 
                    WHERE id = NEW.user_id;
                END IF;
            END";
    
    try {
        $conn->exec("DROP TRIGGER IF EXISTS increment_points_after_delivery");
        $conn->exec($sql);
        echo "✓ Trigger 'increment_points_after_delivery' créé avec succès<br>";
    } catch(PDOException $e) {
        echo "⚠ Trigger: " . $e->getMessage() . "<br>";
    }
    
    echo "<br><strong>Installation terminée avec succès !</strong><br>";
    echo "<br><strong>Fonctionnalités créées :</strong><br>";
    echo "- Système d'authentification complet<br>";
    echo "- Gestion des utilisateurs avec code client unique<br>";
    echo "- Système de commandes avec détails JSON<br>";
    echo "- Gestion des paiements<br>";
    echo "- Compteur de points automatique (+1 par livraison)<br>";
    echo "- Système de réinitialisation de mot de passe sécurisé<br>";
    echo "<br><strong style='color: red;'>IMPORTANT : Supprimez ce fichier (setup_database.php) maintenant pour des raisons de sécurité.</strong>";
    
} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>