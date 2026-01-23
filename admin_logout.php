<?php
/**
 * Script de déconnexion administrateur
 */

session_start();

// Logger la déconnexion
if (isset($_SESSION['admin_username'])) {
    error_log('Déconnexion admin: ' . $_SESSION['admin_username']);
}

// Détruire toutes les variables de session
$_SESSION = array();

// Détruire le cookie de session
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
header("Location: admin_login.php?logout=success");
exit();
?>