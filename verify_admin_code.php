<?php
/**
 * Vérification du code d'accès pour l'inscription admin
 */

session_start();

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_log('=== verify_admin_code.php APPELÉ ===');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

try {
    // Récupérer les données
    $json = file_get_contents('php://input');
    
    if (empty($json)) {
        throw new Exception('Aucune donnée reçue');
    }
    
    $data = json_decode($json, true);
    
    if ($data === null) {
        throw new Exception('Erreur de décodage JSON');
    }
    
    $accessCode = trim($data['access_code'] ?? '');
    
    error_log('Code reçu: ' . $accessCode);
    
    // Validation
    if (empty($accessCode)) {
        throw new Exception('Code d\'accès obligatoire');
    }
    
    if (!preg_match('/^\d{10}$/', $accessCode)) {
        throw new Exception('Le code doit contenir exactement 10 chiffres');
    }
    
    // Connexion à la base de données
    require_once 'config.php';
    $conn = getDBConnection();
    
    // Créer la table si elle n'existe pas
    $conn->exec("
        CREATE TABLE IF NOT EXISTS admin_access_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(10) UNIQUE NOT NULL,
            description VARCHAR(255),
            is_active TINYINT(1) DEFAULT 1,
            uses_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_code_active (code, is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Insérer le code par défaut s'il n'existe pas
    $stmt = $conn->prepare("
        INSERT IGNORE INTO admin_access_codes (code, description, is_active) 
        VALUES ('2311200626', 'Code d\'accès principal administrateur', 1)
    ");
    $stmt->execute();
    
    error_log('Table admin_access_codes vérifiée/créée');
    
    // Vérifier le code dans la base de données
    $stmt = $conn->prepare("
        SELECT id, code, is_active, uses_count 
        FROM admin_access_codes 
        WHERE code = :code AND is_active = 1
        LIMIT 1
    ");
    
    $stmt->execute([':code' => $accessCode]);
    
    if ($stmt->rowCount() === 0) {
        error_log('Code invalide ou inactif: ' . $accessCode);
        
        // Log de tentative échouée (optionnel)
        $logStmt = $conn->prepare("
            INSERT INTO admin_access_attempts (code_attempted, ip_address, user_agent, success)
            VALUES (:code, :ip, :ua, 0)
        ");
        
        try {
            // Créer la table des tentatives si elle n'existe pas
            $conn->exec("
                CREATE TABLE IF NOT EXISTS admin_access_attempts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    code_attempted VARCHAR(10),
                    ip_address VARCHAR(45),
                    user_agent VARCHAR(255),
                    success TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_ip_date (ip_address, created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            $logStmt->execute([
                ':code' => $accessCode,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ':ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 255)
            ]);
        } catch (Exception $e) {
            error_log('Erreur log tentative: ' . $e->getMessage());
        }
        
        throw new Exception('Code d\'accès incorrect');
    }
    
    $codeData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log('Code valide trouvé: ' . $codeData['code']);
    
    // Incrémenter le compteur d'utilisations
    $updateStmt = $conn->prepare("
        UPDATE admin_access_codes 
        SET uses_count = uses_count + 1, updated_at = NOW() 
        WHERE id = :id
    ");
    $updateStmt->execute([':id' => $codeData['id']]);
    
    // Log de tentative réussie
    try {
        $logStmt = $conn->prepare("
            INSERT INTO admin_access_attempts (code_attempted, ip_address, user_agent, success)
            VALUES (:code, :ip, :ua, 1)
        ");
        
        $logStmt->execute([
            ':code' => $accessCode,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ':ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 255)
        ]);
    } catch (Exception $e) {
        error_log('Erreur log tentative: ' . $e->getMessage());
    }
    
    // Autoriser l'accès à l'inscription
    $_SESSION['admin_signup_authorized'] = true;
    $_SESSION['admin_signup_code_id'] = $codeData['id'];
    $_SESSION['admin_signup_timestamp'] = time();
    
    error_log('Accès autorisé pour l\'inscription admin');
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Code validé avec succès'
    ]);
    
    error_log('=== Code validé avec succès ===');

} catch (Exception $e) {
    error_log('EXCEPTION: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
} catch (PDOException $e) {
    error_log('PDO EXCEPTION: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données'
    ]);
    
} catch (Throwable $e) {
    error_log('FATAL ERROR: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur'
    ]);
}
?>