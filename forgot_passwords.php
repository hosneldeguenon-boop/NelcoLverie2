<?php
/**
 * BACKEND ÉTAPE 1 : Génère et envoie le code de réinitialisation
 * Compatible InfiniteFree + PHPMailer
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';
require_once 'email_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    error_log("=== DÉBUT forgot_passwords.php ===");
    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Aucune donnée reçue');
    }
    
    $email = cleanInput($data['email'] ?? '');
    
    if (empty($email)) {
        throw new Exception('Email obligatoire');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email invalide');
    }
    
    $conn = getDBConnection();
    
    // Vérifier que l'utilisateur existe
    $stmt = $conn->prepare("SELECT id, firstname, lastname, email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        error_log("Utilisateur non trouvé: $email");
        throw new Exception('Aucun compte associé à cet email');
    }
    
    error_log("Utilisateur trouvé - ID: " . $user['id']);
    
    // Créer la table si nécessaire
    $conn->exec("
        CREATE TABLE IF NOT EXISTS password_reset_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            email VARCHAR(255) NOT NULL,
            code VARCHAR(6) NOT NULL,
            expiry DATETIME NOT NULL,
            used TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_email_code (email, code),
            INDEX idx_expiry (expiry)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Supprimer les anciens codes non utilisés
    $stmt = $conn->prepare("DELETE FROM password_reset_codes WHERE user_id = ? AND used = 0");
    $stmt->execute([$user['id']]);
    
    // Générer un code à 6 chiffres
    $code = generateSecureCode();
    $expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
    
    error_log("Code généré: $code - Expiration: $expiry");
    
    // Enregistrer le code
    $stmt = $conn->prepare("
        INSERT INTO password_reset_codes (user_id, email, code, expiry) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$user['id'], $email, $code, $expiry]);
    
    error_log("Code enregistré en base");
    
    // Envoyer l'email
    $emailSent = sendResetCode($user['email'], $user['firstname'], $code);
    
    if (!$emailSent) {
        error_log("Échec envoi email");
        throw new Exception('Erreur lors de l\'envoi de l\'email. Vérifiez votre configuration SMTP.');
    }
    
    error_log("Email envoyé avec succès");
    error_log("=== FIN forgot_passwords.php (SUCCÈS) ===");
    
    echo json_encode([
        'success' => true,
        'message' => 'Un code de vérification a été envoyé à votre adresse email.'
    ]);
    
} catch (Exception $e) {
    error_log("ERREUR: " . $e->getMessage());
    error_log("=== FIN forgot_passwords.php (ÉCHEC) ===");
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>