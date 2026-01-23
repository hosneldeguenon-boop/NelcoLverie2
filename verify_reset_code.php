<?php
/**
 * ÉTAPE 2 : Vérifie le code de réinitialisation
 * Reçoit l'email ET le code à 6 chiffres
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
    error_log(">>> DÉBUT verify_reset_code.php - " . date('Y-m-d H:i:s'));
    
    $json = file_get_contents('php://input');
    error_log("JSON reçu: " . $json);
    
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Données JSON invalides');
    }
    
    // Récupérer et nettoyer les données
    $email = cleanInput($data['email'] ?? '');
    $code = cleanInput($data['code'] ?? '');
    
    error_log("Email: [$email]");
    error_log("Code: [$code]");
    
    if (empty($email) || empty($code)) {
        throw new Exception('Email et code obligatoires');
    }
    
    if (strlen($code) !== 6 || !ctype_digit($code)) {
        throw new Exception('Le code doit contenir 6 chiffres');
    }
    
    // Connexion BDD
    $conn = getDBConnection();
    error_log("Connexion BDD établie");
    
    // Vérifier le code
    $stmt = $conn->prepare("
        SELECT id, user_id, expiry, used 
        FROM password_reset_codes 
        WHERE email = ? AND code = ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$email, $code]);
    $resetCode = $stmt->fetch();
    
    if (!$resetCode) {
        error_log("❌ Code invalide ou introuvable");
        throw new Exception('Code invalide');
    }
    
    error_log("✅ Code trouvé - ID: {$resetCode['id']}");
    
    // Vérifier si le code a déjà été utilisé
    if ($resetCode['used']) {
        error_log("❌ Code déjà utilisé");
        throw new Exception('Ce code a déjà été utilisé');
    }
    
    // Vérifier l'expiration
    $now = new DateTime();
    $expiry = new DateTime($resetCode['expiry']);
    
    if ($now > $expiry) {
        error_log("❌ Code expiré - Expiration: {$resetCode['expiry']}");
        throw new Exception('Code expiré. Demandez un nouveau code.');
    }
    
    error_log("✅ Code valide et non expiré");
    error_log(">>> FIN verify_reset_code.php (SUCCÈS)");
    error_log("========================================");
    
    echo json_encode([
        'success' => true,
        'message' => 'Code vérifié avec succès'
    ]);
    
} catch (Exception $e) {
    error_log("❌ ERREUR: " . $e->getMessage());
    error_log(">>> FIN verify_reset_code.php (ÉCHEC)");
    error_log("========================================");
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>