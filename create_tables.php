<?php
/**
 * Script de création de toutes les tables
 * À exécuter UNE FOIS pour initialiser la base de données
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Création des Tables</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: sans-serif; }
        body { background: #f5f7fa; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #17a2b8; }
        .btn { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin-top: 20px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 13px; }
    </style>
</head>
<body>
<div class='container'>
<h1>🔧 Création des Tables de la Base de Données</h1>";

try {
    $conn = getDBConnection();
    
    // TABLE 1 : users
    echo "<h2>1. Table 'users'</h2>";
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lastname VARCHAR(100) NOT NULL,
        firstname VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL UNIQUE,
        whatsapp VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        gender ENUM('homme', 'femme') NOT NULL,
        password VARCHAR(255) NOT NULL,
        customer_code VARCHAR(20) NOT NULL UNIQUE,
        points_counter INT DEFAULT 0,
        status ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
        last_login DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_customer_code (customer_code),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "<div class='success'>✅ Table 'users' créée/vérifiée avec succès</div>";
    
    // TABLE 2 : password_reset_codes
    echo "<h2>2. Table 'password_reset_codes'</h2>";
    $sql = "CREATE TABLE IF NOT EXISTS password_reset_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        email VARCHAR(255) NOT NULL,
        code VARCHAR(6) NOT NULL,
        expiry DATETIME NOT NULL,
        used BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_email_code (email, code),
        INDEX idx_expiry (expiry),
        INDEX idx_used (used)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "<div class='success'>✅ Table 'password_reset_codes' créée/vérifiée avec succès</div>";
    
    // TABLE 3 : orders
    echo "<h2>3. Table 'orders'</h2>";
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        order_number VARCHAR(50) NOT NULL UNIQUE,
        customer_code VARCHAR(20) NOT NULL,
        service_type VARCHAR(50) NOT NULL,
        pickup_address TEXT NOT NULL,
        pickup_date DATE NOT NULL,
        delivery_address TEXT NOT NULL,
        delivery_date DATE NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        washing_price DECIMAL(10,2) DEFAULT 0,
        drying_price DECIMAL(10,2) DEFAULT 0,
        folding_price DECIMAL(10,2) DEFAULT 0,
        ironing_price DECIMAL(10,2) DEFAULT 0,
        delivery_price DECIMAL(10,2) DEFAULT 0,
        loyalty_discount DECIMAL(10,2) DEFAULT 0,
        total_weight DECIMAL(10,2) DEFAULT 0,
        order_details TEXT,
        payment_method VARCHAR(50),
        transaction_id VARCHAR(100),
        status ENUM('en_attente_paiement', 'paye', 'en_cours', 'termine', 'annule') DEFAULT 'en_attente_paiement',
        points_at_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_order_number (order_number),
        INDEX idx_user_id (user_id),
        INDEX idx_status (status),
        INDEX idx_pickup_date (pickup_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "<div class='success'>✅ Table 'orders' créée/vérifiée avec succès</div>";
    
    // Vérification finale
    echo "<h2>📊 Vérification Finale</h2>";
    
    $tables = ['users', 'password_reset_codes', 'orders'];
    foreach ($tables as $table) {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch()['count'];
        echo "<div class='info'>ℹ️ Table '$table' : $count enregistrement(s)</div>";
    }
    
    echo "<div class='success'>";
    echo "<h3>✅ Toutes les tables ont été créées avec succès !</h3>";
    echo "<p>Votre base de données est maintenant prête à être utilisée.</p>";
    echo "</div>";
    
    echo "<a href='diagnostic.php' class='btn'>🔍 Lancer le Diagnostic</a>";
    echo "<a href='testht.php' class='btn'>🔐 Page de Connexion</a>";
    echo "<a href='creer_compte.php' class='btn'>📝 Créer un Compte</a>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Erreur lors de la création des tables</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "</div></body></html>";
?>