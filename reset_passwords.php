<?php
/**
 * BACKEND ÉTAPE 3 : Change le mot de passe
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
    error_log("=== DÉBUT reset_passwords.php ===");
    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Aucune donnée reçue');
    }
    
    $email = trim($data['email'] ?? '');
    $code = trim($data['code'] ?? '');
    $newPassword = $data['password'] ?? '';
    
    error_log("Email: $email | Code: $code | Password length: " . strlen($newPassword));
    
    if (empty($email) || empty($code) || empty($newPassword)) {
        throw new Exception('Email, code et mot de passe sont obligatoires');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email invalide');
    }
    
    if (!preg_match('/^\d{6}$/', $code)) {
        throw new Exception('Code invalide');
    }
    
    if (strlen($newPassword) < 8) {
        throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
    }
    
    $conn = getDBConnection();
    
    // VÉRIFIER LE CODE
    $stmt = $conn->prepare("
        SELECT prc.id, prc.user_id, prc.expiry, prc.used, u.email, u.firstname 
        FROM password_reset_codes prc
        JOIN users u ON prc.user_id = u.id
        WHERE prc.email = ? AND prc.code = ?
    ");
    $stmt->execute([$email, $code]);
    $resetRequest = $stmt->fetch();
    
    if (!$resetRequest) {
        error_log("Code invalide");
        throw new Exception('Code de réinitialisation invalide ou expiré');
    }
    
    error_log("Code trouvé pour user_id: " . $resetRequest['user_id']);
    
    // VÉRIFIER SI DÉJÀ UTILISÉ
    if ($resetRequest['used']) {
        error_log("Code déjà utilisé");
        throw new Exception('Ce code a déjà été utilisé');
    }
    
    // VÉRIFIER L'EXPIRATION
    $now = new DateTime();
    $expiry = new DateTime($resetRequest['expiry']);
    
    if ($now > $expiry) {
        error_log("Code expiré");
        throw new Exception('Ce code a expiré. Veuillez faire une nouvelle demande.');
    }
    
    error_log("Code valide et non expiré");
    
    // HASHER LE NOUVEAU MOT DE PASSE
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    error_log("Mot de passe hashé");
    
    // METTRE À JOUR LE MOT DE PASSE
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updated = $stmt->execute([$hashedPassword, $resetRequest['user_id']]);
    
    if (!$updated) {
        throw new Exception('Erreur lors de la mise à jour du mot de passe');
    }
    
    error_log("Mot de passe mis à jour");
    
    // MARQUER LE CODE COMME UTILISÉ
    $stmt = $conn->prepare("UPDATE password_reset_codes SET used = 1 WHERE id = ?");
    $stmt->execute([$resetRequest['id']]);
    
    // SUPPRIMER LES AUTRES CODES
    $stmt = $conn->prepare("DELETE FROM password_reset_codes WHERE user_id = ? AND id != ?");
    $stmt->execute([$resetRequest['user_id'], $resetRequest['id']]);
    
    error_log("Code marqué comme utilisé et autres codes supprimés");
    error_log("=== FIN reset_passwords.php (SUCCÈS) ===");
    
    echo json_encode([
        'success' => true,
        'message' => 'Votre mot de passe a été changé avec succès ! Redirection...'
    ]);
    
} catch (Exception $e) {
    error_log("ERREUR: " . $e->getMessage());
    error_log("=== FIN reset_passwords.php (ÉCHEC) ===");
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>