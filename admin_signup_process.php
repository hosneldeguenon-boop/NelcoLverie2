<?php
/**
 * Traitement de l'inscription administrateur
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

error_log('=== admin_signup_process.php APPELÉ ===');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

try {
    // Vérifier l'autorisation d'inscription
    if (!isset($_SESSION['admin_signup_authorized']) || $_SESSION['admin_signup_authorized'] !== true) {
        error_log('Tentative d\'inscription sans autorisation');
        throw new Exception('Accès non autorisé. Code requis.');
    }
    
    // Vérifier le timeout de l'autorisation (10 minutes)
    if (isset($_SESSION['admin_signup_timestamp']) && (time() - $_SESSION['admin_signup_timestamp'] > 600)) {
        unset($_SESSION['admin_signup_authorized']);
        unset($_SESSION['admin_signup_code_id']);
        unset($_SESSION['admin_signup_timestamp']);
        throw new Exception('Session d\'inscription expirée. Veuillez recommencer.');
    }
    
    // Récupérer les données
    $json = file_get_contents('php://input');
    
    if (empty($json)) {
        throw new Exception('Aucune donnée reçue');
    }
    
    $data = json_decode($json, true);
    
    if ($data === null) {
        throw new Exception('Erreur de décodage JSON');
    }
    
    // Récupération et nettoyage des données
    $lastname = trim($data['lastname'] ?? '');
    $firstname = trim($data['firstname'] ?? '');
    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $gender = trim($data['gender'] ?? '');
    $password = $data['password'] ?? '';
    
    error_log("Tentative inscription: $username / $email");
    
    // Validations
    if (empty($lastname) || empty($firstname) || empty($username) || empty($email) || 
        empty($phone) || empty($gender) || empty($password)) {
        throw new Exception('Tous les champs sont obligatoires');
    }
    
    if (strlen($password) < 8) {
        throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Format email invalide');
    }
    
    $phone_clean = preg_replace('/[\s\-\.]/', '', $phone);
    if (!preg_match('/^[0-9]{10,}$/', $phone_clean)) {
        throw new Exception('Numéro de téléphone invalide (minimum 10 chiffres)');
    }
    
    if (!in_array($gender, ['M', 'F'])) {
        throw new Exception('Valeur de sexe invalide');
    }
    
    error_log('Validations OK');
    
    // Connexion à la base de données
    require_once 'config.php';
    $conn = getDBConnection();
    
    // Créer la table admins si elle n'existe pas
    $conn->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lastname VARCHAR(100) NOT NULL,
            firstname VARCHAR(100) NOT NULL,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(150) UNIQUE NOT NULL,
            phone VARCHAR(20) NOT NULL,
            gender ENUM('M', 'F') NOT NULL,
            password VARCHAR(255) NOT NULL,
            status ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    error_log('Table admins vérifiée/créée');
    
    // Vérifier l'unicité du username et email
    $checkStmt = $conn->prepare("
        SELECT id, username, email FROM admins 
        WHERE username = :username OR email = :email
        LIMIT 1
    ");
    
    $checkStmt->execute([
        ':username' => $username,
        ':email' => $email
    ]);
    
    if ($checkStmt->rowCount() > 0) {
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if ($existing['username'] === $username) {
            throw new Exception('Ce pseudonyme est déjà utilisé');
        }
        if ($existing['email'] === $email) {
            throw new Exception('Cet email est déjà utilisé');
        }
    }
    
    error_log('Username/Email uniques OK');
    
    // Hasher le mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    if (!$hashedPassword) {
        throw new Exception('Erreur lors du hashage du mot de passe');
    }
    
    error_log('Mot de passe hashé OK');
    
    // Insérer l'administrateur
    $insertStmt = $conn->prepare("
        INSERT INTO admins 
        (lastname, firstname, username, email, phone, gender, password, status, created_at, updated_at)
        VALUES 
        (:lastname, :firstname, :username, :email, :phone, :gender, :password, 'actif', NOW(), NOW())
    ");
    
    $result = $insertStmt->execute([
        ':lastname' => $lastname,
        ':firstname' => $firstname,
        ':username' => $username,
        ':email' => $email,
        ':phone' => $phone_clean,
        ':gender' => $gender,
        ':password' => $hashedPassword
    ]);
    
    if (!$result) {
        $errorInfo = $insertStmt->errorInfo();
        error_log('Erreur SQL: ' . $errorInfo[2]);
        throw new Exception('Erreur lors de l\'enregistrement');
    }
    
    $adminId = $conn->lastInsertId();
    
    error_log("Admin créé avec succès: ID=$adminId, Username=$username");
    
    // Nettoyer l'autorisation d'inscription
    unset($_SESSION['admin_signup_authorized']);
    unset($_SESSION['admin_signup_code_id']);
    unset($_SESSION['admin_signup_timestamp']);
    
    // Retourner le succès
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Compte administrateur créé avec succès !',
        'admin_id' => $adminId
    ]);
    
    error_log('=== Inscription admin réussie ===');

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