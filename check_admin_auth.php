<?php
/**
 * Vérifier l'authentification de l'administrateur via AJAX
 */

session_start();

header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    
    // Vérifier le timeout de session
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        // Session expirée
        session_unset();
        session_destroy();
        
        echo json_encode([
            'authenticated' => false,
            'message' => 'Session expirée'
        ]);
        exit();
    }
    
    // Mettre à jour le temps d'activité
    $_SESSION['last_activity'] = time();
    
    // Retourner les informations de l'admin
    echo json_encode([
        'authenticated' => true,
        'id' => $_SESSION['admin_id'] ?? null,
        'username' => $_SESSION['admin_username'] ?? '',
        'firstname' => $_SESSION['admin_firstname'] ?? '',
        'lastname' => $_SESSION['admin_lastname'] ?? '',
        'email' => $_SESSION['admin_email'] ?? ''
    ]);
    
} else {
    // Non authentifié
    echo json_encode([
        'authenticated' => false,
        'message' => 'Non authentifié'
    ]);
}
?>