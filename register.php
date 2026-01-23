<?php
/**
 * Script d'inscription avec vérification d'email
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/errors.log');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';
require_once 'email_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

try {
    error_log("========================================");
    error_log(">>> DÉBUT register.php");
    
    $json = file_get_contents('php://input');
    error_log("JSON reçu: " . $json);
    
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Données JSON invalides');
    }
    
    // Récupérer et valider les données
    $lastname = cleanInput($data['lastname'] ?? '');
    $firstname = cleanInput($data['firstname'] ?? '');
    $email = cleanInput($data['email'] ?? '');
    $phone = cleanInput($data['phone'] ?? '');
    $whatsapp = cleanInput($data['whatsapp'] ?? '');
    $address = cleanInput($data['address'] ?? ''); // Optionnel maintenant
    $gender = cleanInput($data['gender'] ?? '');
    $password = $data['password'] ?? '';
    
    // Validations
    if (empty($lastname) || empty($firstname) || empty($email) || empty($phone) || 
        empty($whatsapp) || empty($gender) || empty($password)) {
        throw new Exception('Tous les champs obligatoires doivent être remplis');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format d\'email invalide');
    }
    
    if (strlen($password) < 8) {
        throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
    }
    
    $conn = getDBConnection();
    
    // Vérifier si l'email existe déjà
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('Cet email est déjà utilisé');
    }
    
    // Générer un code de vérification
    $verificationCode = generateSecureToken(32);
    
    // Hasher le mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Générer un code client unique
    $customerCode = 'C' . strtoupper(substr(uniqid(), -8));
    
    // Créer la table de vérification email si elle n'existe pas
    $conn->exec("
        CREATE TABLE IF NOT EXISTS email_verifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            verification_code VARCHAR(64) NOT NULL,
            user_data TEXT NOT NULL,
            expiry DATETIME NOT NULL,
            verified TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_code (verification_code),
            INDEX idx_expiry (expiry)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Supprimer les anciennes demandes non vérifiées pour cet email
    $stmt = $conn->prepare("DELETE FROM email_verifications WHERE email = ? AND verified = 0");
    $stmt->execute([$email]);
    
    // Stocker temporairement les données utilisateur
    $userData = json_encode([
        'lastname' => $lastname,
        'firstname' => $firstname,
        'email' => $email,
        'phone' => $phone,
        'whatsapp' => $whatsapp,
        'address' => $address,
        'gender' => $gender,
        'password' => $hashedPassword,
        'customer_code' => $customerCode
    ]);
    
    $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    $stmt = $conn->prepare("
        INSERT INTO email_verifications (email, verification_code, user_data, expiry) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$email, $verificationCode, $userData, $expiry]);
    
    error_log("Code de vérification enregistré pour: $email");
    
    // Envoyer l'email de vérification
    $verificationLink = SITE_URL . "/verify_email.php?code=" . $verificationCode;
    
    $emailSent = sendVerificationEmail($email, $firstname, $verificationLink);
    
    if (!$emailSent) {
        error_log("Échec envoi email de vérification");
        throw new Exception('Erreur lors de l\'envoi de l\'email de vérification. Veuillez réessayer.');
    }
    
    error_log("Email de vérification envoyé avec succès");
    error_log(">>> FIN register.php (SUCCÈS)");
    error_log("========================================");
    
    echo json_encode([
        'success' => true,
        'message' => 'Un email de confirmation a été envoyé à ' . $email
    ]);
    
} catch (Exception $e) {
    error_log("❌ ERREUR: " . $e->getMessage());
    error_log(">>> FIN register.php (ÉCHEC)");
    error_log("========================================");
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>