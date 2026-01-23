<?php
/**
 * Vérifier l'authentification utilisateur
 * Optimisé pour InfinityFree
 */

ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);
session_set_cookie_params(3600);

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Access-Control-Allow-Origin: *');

error_log('=== check_user_auth.php ===');
error_log('Session ID: ' . session_id());
error_log('User ID: ' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'non défini'));

$response = [
    'authenticated' => false,
    'user_id' => null
];

if (isset($_SESSION['user_id']) && 
    isset($_SESSION['logged_in']) && 
    $_SESSION['logged_in'] === true) {
    
    $response = [
        'authenticated' => true,
        'user_id' => (int)$_SESSION['user_id'],
        'firstname' => $_SESSION['user_firstname'] ?? '',
        'lastname' => $_SESSION['user_lastname'] ?? '',
        'email' => $_SESSION['user_email'] ?? ''
    ];
    
    error_log('✅ Authentifié - ID: ' . $_SESSION['user_id']);
} else {
    error_log('❌ Non authentifié');
}

echo json_encode($response);
?>