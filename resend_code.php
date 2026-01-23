<?php
/**
 * Script de renvoi du code de vérification
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';
require_once 'email_config.php';

function generateSecureCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Aucune donnée reçue');
    }
    
    $email = trim($data['email'] ?? '');
    
    if (empty($email)) {
        throw new Exception('Email obligatoire');
    }
    
    $conn = getDBConnection();
    
    // Vérifier que l'utilisateur existe
    $stmt = $conn->prepare("SELECT id, firstname FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('Aucun compte associé à cet email');
    }
    
    // Vérifier qu'on ne renvoie pas trop souvent (limite : 1 fois par minute)
    $stmt = $conn->prepare("
        SELECT created_at 
        FROM password_reset_codes 
        WHERE email = ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $lastCode = $stmt->fetch();
    
    if ($lastCode) {
        $lastTime = new DateTime($lastCode['created_at']);
        $now = new DateTime();
        $diff = $now->getTimestamp() - $lastTime->getTimestamp();
        
        if ($diff < 60) {
            throw new Exception('Veuillez attendre avant de demander un nouveau code');
        }
    }
    
    // Générer un nouveau code
    $code = generateSecureCode();
    $expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
    
    // Supprimer les anciens codes non utilisés
    $stmt = $conn->prepare("DELETE FROM password_reset_codes WHERE email = ? AND used = FALSE");
    $stmt->execute([$email]);
    
    // Enregistrer le nouveau code
    $stmt = $conn->prepare("
        INSERT INTO password_reset_codes (user_id, email, code, expiry) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$user['id'], $email, $code, $expiry]);
    
    // Envoyer l'email
    $emailSent = sendResetCode($email, $user['firstname'], $code);
    
    if (!$emailSent) {
        throw new Exception('Erreur lors de l\'envoi de l\'email');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Un nouveau code a été envoyé'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>