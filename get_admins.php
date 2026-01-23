<?php
/**
 * API pour récupérer la liste des administrateurs
 * Accessible uniquement aux administrateurs connectés
 */

session_start();

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header('Content-Type: application/json; charset=utf-8');

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Non autorisé. Connexion requise.'
    ]);
    exit();
}

try {
    require_once 'config.php';
    $conn = getDBConnection();
    
    // Récupérer tous les administrateurs
    $stmt = $conn->prepare("
        SELECT 
            id,
            lastname,
            firstname,
            username,
            email,
            phone,
            gender,
            status,
            created_at,
            updated_at
        FROM admins
        ORDER BY created_at DESC
    ");
    
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formater les données
    $formattedAdmins = array_map(function($admin) {
        return [
            'id' => (int)$admin['id'],
            'lastname' => $admin['lastname'],
            'firstname' => $admin['firstname'],
            'username' => $admin['username'],
            'email' => $admin['email'],
            'phone' => $admin['phone'],
            'gender' => $admin['gender'],
            'status' => $admin['status'],
            'created_at' => $admin['created_at'],
            'updated_at' => $admin['updated_at']
        ];
    }, $admins);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'admins' => $formattedAdmins,
        'total' => count($formattedAdmins)
    ]);
    
} catch (PDOException $e) {
    error_log('Erreur PDO get_admins: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de base de données'
    ]);
    
} catch (Exception $e) {
    error_log('Erreur get_admins: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur'
    ]);
}
?>