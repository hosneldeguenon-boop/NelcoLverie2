<?php
/**
 * Script de connexion - VERSION CORRIGÉE
 */

session_start();

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
    error_log(">>> DÉBUT login.php");
    
    $json = file_get_contents('php://input');
    error_log("JSON reçu: " . $json);
    
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Données JSON invalides');
    }
    
    $email = cleanInput($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $remember = isset($data['remember']) && $data['remember'] === true;
    
    error_log("Email: $email");
    error_log("Remember: " . ($remember ? 'Oui' : 'Non'));
    
    if (empty($email) || empty($password)) {
        throw new Exception('Email et mot de passe sont obligatoires');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format d\'email invalide');
    }
    
    $conn = getDBConnection();
    error_log("Connexion BDD OK");
    
    // Récupérer l'utilisateur
    $stmt = $conn->prepare("
        SELECT id, lastname, firstname, email, password, customer_code, points_counter, status 
        FROM users 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        error_log("❌ Utilisateur non trouvé: $email");
        throw new Exception('Email ou mot de passe incorrect');
    }
    
    error_log("✅ Utilisateur trouvé - ID: {$user['id']}");
    
    // Vérifier le statut
    if ($user['status'] === 'suspendu') {
        throw new Exception('Votre compte a été suspendu. Contactez l\'administrateur.');
    }
    
    if ($user['status'] === 'inactif') {
        throw new Exception('Votre compte est inactif. Contactez l\'administrateur.');
    }
    
    // Vérifier le mot de passe
    if (!password_verify($password, $user['password'])) {
        error_log("❌ Mot de passe incorrect pour: $email");
        throw new Exception('Email ou mot de passe incorrect');
    }
    
    error_log("✅ Mot de passe correct");
    
    // Mettre à jour last_login
    $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $updateStmt->execute([$user['id']]);
    
    // Créer la session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_firstname'] = $user['firstname'];
    $_SESSION['user_lastname'] = $user['lastname'];
    $_SESSION['customer_code'] = $user['customer_code'];
    $_SESSION['points_counter'] = $user['points_counter'];
    $_SESSION['logged_in'] = true;
    
    error_log("✅ Session créée");
    
    // Cookie remember me
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true);
        error_log("Cookie remember_token créé");
    }
    
    error_log(">>> FIN login.php (SUCCÈS)");
    error_log("========================================");
    
    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie',
        'user' => [
            'id' => $user['id'],
            'firstname' => $user['firstname'],
            'lastname' => $user['lastname'],
            'email' => $user['email'],
            'customer_code' => $user['customer_code'],
            'points' => $user['points_counter']
        ]
    ]);
    
} catch (Exception $e) {
    error_log("❌ ERREUR: " . $e->getMessage());
    error_log(">>> FIN login.php (ÉCHEC)");
    error_log("========================================");
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>