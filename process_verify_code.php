<?php
/**
 * BACKEND ÉTAPE 2 : Vérifie le code de réinitialisation
 * Compatible InfiniteFree
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    error_log("=== DÉBUT process_verify_code.php ===");
    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Aucune donnée reçue');
    }
    
    $email = trim($data['email'] ?? '');
    $code = trim($data['code'] ?? '');
    
    if (empty($email) || empty($code)) {
        throw new Exception('Email et code sont obligatoires');
    }
    
    if (!preg_match('/^\d{6}$/', $code)) {
        throw new Exception('Code invalide (6 chiffres requis)');
    }
    
    $conn = getDBConnection();
    
    // Vérifier le code
    $stmt = $conn->prepare("
        SELECT id, user_id, expiry, used
        FROM password_reset_codes
        WHERE email = ? AND code = ? AND used = 0
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$email, $code]);
    $resetCode = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resetCode) {
        error_log("Code invalide ou déjà utilisé");
        throw new Exception('Code invalide ou déjà utilisé');
    }
    
    // Vérifier l'expiration
    $now = new DateTime();
    $expiry = new DateTime($resetCode['expiry']);
    
    if ($now > $expiry) {
        error_log("Code expiré");
        throw new Exception('Code expiré. Demandez un nouveau code.');
    }
    
    error_log("Code vérifié avec succès");
    error_log("=== FIN process_verify_code.php (SUCCÈS) ===");
    
    echo json_encode([
        'success' => true,
        'message' => 'Code vérifié avec succès'
    ]);
    
} catch (Exception $e) {
    error_log("ERREUR: " . $e->getMessage());
    error_log("=== FIN process_verify_code.php (ÉCHEC) ===");
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>