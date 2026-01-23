<?php
/**
 * ✅ CONFIGURATION NELCO LAVERIE - CYCLE FIDÉLITÉ 11 LAVAGES
 */

// Détection environnement
define('DEVELOPMENT_MODE', $_SERVER['SERVER_NAME'] === 'localhost' || strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false);

// Configuration base de données
if (DEVELOPMENT_MODE) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'nelco_laverie');
} else {
    define('DB_HOST', 'sql111.infinityfree.com');
    define('DB_USER', 'if0_40818441');
    define('DB_PASS', 'Lenhros23112006');
    define('DB_NAME', 'if0_40818441_laverie');
}
define('DB_CHARSET', 'utf8mb4');

// Configuration site
if (DEVELOPMENT_MODE) {
    define('SITE_URL', 'http://localhost/nelco');
} else {
    define('SITE_URL', 'https://nelcolaverie.infinityfreeapp.com');
}
define('SITE_NAME', 'Nelco Laverie');

// Configuration email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'hosneldeguenon@gmail.com');
define('SMTP_PASSWORD', 'ggkk nwrt hdjn jxzp');
define('FROM_EMAIL', 'hosneldeguenon@gmail.com');
define('FROM_NAME', 'Nelco Laverie');

// Configuration paiement
define('PAYMENT_API_MTN', 'https://api-mtn.example.com/payment');
define('PAYMENT_API_MOOV', 'https://api-moov.example.com/payment');
define('PAYMENT_API_CELTIIS', 'https://api-celtiis.example.com/payment');
define('PAYMENT_TIMEOUT', 10);

// ============================================
// ✅ CONFIGURATION FIDÉLITÉ - CYCLE DE 11 LAVAGES
// ============================================
define('LOYALTY_CYCLE', 11);
define('LOYALTY_REWARD', 2500);

// Constantes statuts
define('ORDER_STATUS_PENDING', 'pending');
define('ORDER_STATUS_EN_ATTENTE', 'en_attente');
define('ORDER_STATUS_EN_COURS', 'en_cours');
define('ORDER_STATUS_PRETE', 'prete');
define('ORDER_STATUS_LIVREE', 'livree');
define('ORDER_STATUS_ANNULEE', 'annulee');

define('PAYMENT_STATUS_PENDING', 'pending');
define('PAYMENT_STATUS_SUCCESS', 'success');
define('PAYMENT_STATUS_FAILED', 'failed');
define('PAYMENT_STATUS_REFUNDED', 'refunded');

// Sécurité & timezone
date_default_timezone_set('Africa/Porto-Novo');

if (DEVELOPMENT_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/logs/php_errors.log');
}

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_secure', !DEVELOPMENT_MODE ? 1 : 0);

// Initialisation session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_httponly' => true,
        'cookie_secure' => !DEVELOPMENT_MODE,
        'use_strict_mode' => true
    ]);
}

// Connexion base de données
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            return $conn;
            
        } catch (PDOException $e) {
            error_log('Erreur connexion BDD: ' . $e->getMessage());
            if (DEVELOPMENT_MODE) {
                die('Erreur de connexion à la base de données: ' . $e->getMessage());
            } else {
                die('Erreur de connexion à la base de données.');
            }
        }
    }
    
    return $conn;
}

// Fonctions nettoyage
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (strlen($phone) == 8) {
        return preg_match('/^[0-9]{8}$/', $phone);
    }
    
    if (strlen($phone) == 11 && substr($phone, 0, 3) == '229') {
        return true;
    }
    
    return false;
}

// Fonctions utilitaires
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
           && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function generateSecureCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function generateOrderNumber() {
    return 'CMD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function generateCustomerCode($userId) {
    return 'CLT-' . str_pad($userId, 6, '0', STR_PAD_LEFT);
}

// ============================================
// ✅ FONCTION CALCUL FIDÉLITÉ - CYCLE 11 LAVAGES
// ============================================
function calculateLoyalty($ancienNombreLavage, $lavCommande) {
    $totalLavages = $ancienNombreLavage + $lavCommande;
    $nombreReductions = floor($totalLavages / LOYALTY_CYCLE);
    $nouveauNombreLavage = $totalLavages % LOYALTY_CYCLE;
    $montantReduction = $nombreReductions * LOYALTY_REWARD;
    
    return [
        'totalLavages' => $totalLavages,
        'nombreReductions' => $nombreReductions,
        'nouveauNombreLavage' => $nouveauNombreLavage,
        'montantReduction' => $montantReduction
    ];
}

// Fonctions sécurité
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Non authentifié']);
        exit;
    }
}

function requireAdmin() {
    requireAuth();
    
    if ($_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès interdit']);
        exit;
    }
}

function logSecurity($message, $level = 'INFO') {
    $logFile = __DIR__ . '/logs/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $userId = $_SESSION['user_id'] ?? 'guest';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $logMessage = "[$timestamp] [$level] [User:$userId] [IP:$ip] $message" . PHP_EOL;
    
    @file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Fonction envoi email
function sendEmail($to, $subject, $body) {
    $headers = "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $body, $headers);
}

// Fonction envoi SMS
function sendSMS($phone, $message) {
    error_log("SMS vers $phone: $message");
    return true;
}
?>