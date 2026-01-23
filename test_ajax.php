<?php
/**
 * TEST AJAX ULTRA SIMPLE
 * Répond juste avec du JSON
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    echo json_encode([
        'success' => true,
        'message' => 'AJAX fonctionne parfaitement !',
        'received' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}
?>