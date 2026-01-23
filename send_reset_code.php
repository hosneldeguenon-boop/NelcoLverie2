<?php
/**
 * VERSION 2 - ENVOI CODE RESET
 * Plus robuste avec meilleure gestion d'erreurs
 */

// Configuration erreurs
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/errors.log');

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Fonction de log
function logMessage($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message);
}

// Fonction de réponse JSON
function jsonResponse($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if (!$success) {
        http_response_code(400);
    }
    
    echo json_encode($response);
    exit;
}

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logMessage("❌ Méthode non autorisée: {$_SERVER['REQUEST_METHOD']}");
    jsonResponse(false, 'Méthode non autorisée');
}

logMessage("========================================");
logMessage(">>> DÉBUT send_reset_code_v2.php");

try {
    // 1. Charger les configs
    logMessage("📂 Chargement config.php");
    if (!file_exists(__DIR__ . '/config.php')) {
        throw new Exception('config.php introuvable');
    }
    require_once __DIR__ . '/config.php';
    
    logMessage("📂 Chargement email_config.php");
    if (!file_exists(__DIR__ . '/email_config.php')) {
        throw new Exception('email_config.php introuvable');
    }
    require_once __DIR__ . '/email_config.php';
    
    // 2. Lire les données
    logMessage("📥 Lecture des données POST");
    $json = file_get_contents('php://input');
    
    if (empty($json)) {
        throw new Exception('Aucune donnée reçue');
    }
    
    logMessage("JSON brut: " . substr($json, 0, 200));
    
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON invalide: ' . json_last_error_msg());
    }
    
    // 3. Valider l'email
    $email = isset($data['email']) ? trim($data['email']) : '';
    logMessage("Email reçu: [$email]");
    
    if (empty($email)) {
        throw new Exception('Email obligatoire');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email invalide');
    }
    
    // 4. Connexion BDD
    logMessage("🔌 Connexion BDD");
    $conn = getDBConnection();
    
    // 5. Chercher l'utilisateur
    logMessage("🔍 Recherche utilisateur");
    $stmt = $conn->prepare("SELECT id, firstname, lastname, email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        logMessage("❌ Utilisateur introuvable: $email");
        // Message générique pour la sécurité
        jsonResponse(true, 'Si cet email existe, un code a été envoyé');
    }
    
    logMessage("✅ Utilisateur trouvé: ID={$user['id']}, Nom={$user['firstname']} {$user['lastname']}");
    
    // 6. Créer/vérifier la table
    logMessage("📋 Vérification table password_reset_codes");
    $conn->exec("
        CREATE TABLE IF NOT EXISTS password_reset_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            email VARCHAR(255) NOT NULL,
            code VARCHAR(6) NOT NULL,
            expiry DATETIME NOT NULL,
            used BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_email_code (email, code),
            INDEX idx_expiry (expiry)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // 7. Supprimer anciens codes
    logMessage("🗑️ Suppression anciens codes");
    $stmt = $conn->prepare("DELETE FROM password_reset_codes WHERE user_id = ? AND used = FALSE");
    $stmt->execute([$user['id']]);
    
    // 8. Générer le code
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
    
    logMessage("🔢 Code généré: $code (expire: $expiry)");
    
    // 9. Enregistrer en BDD
    logMessage("💾 Enregistrement code");
    $stmt = $conn->prepare("
        INSERT INTO password_reset_codes (user_id, email, code, expiry) 
        VALUES (?, ?, ?, ?)
    ");
    
    if (!$stmt->execute([$user['id'], $email, $code, $expiry])) {
        throw new Exception('Erreur enregistrement code');
    }
    
    logMessage("✅ Code enregistré (ID: {$conn->lastInsertId()})");
    
    // 10. Envoyer l'email
    logMessage("📧 Envoi email à {$user['email']}");
    
    if (!function_exists('sendResetCode')) {
        throw new Exception('Fonction sendResetCode() introuvable');
    }
    
    $emailSent = sendResetCode($user['email'], $user['firstname'], $code);
    
    if (!$emailSent) {
        logMessage("❌ Échec envoi email");
        throw new Exception('Erreur lors de l\'envoi de l\'email');
    }
    
    logMessage("✅ Email envoyé avec succès");
    logMessage(">>> FIN send_reset_code_v2.php (SUCCÈS)");
    logMessage("========================================");
    
    jsonResponse(true, 'Un code de vérification a été envoyé à votre adresse email');
    
} catch (Exception $e) {
    logMessage("❌ ERREUR: {$e->getMessage()}");
    logMessage("   Fichier: {$e->getFile()}");
    logMessage("   Ligne: {$e->getLine()}");
    logMessage(">>> FIN send_reset_code_v2.php (ÉCHEC)");
    logMessage("========================================");
    
    jsonResponse(false, $e->getMessage());
}
?>