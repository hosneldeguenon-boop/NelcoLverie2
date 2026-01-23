<?php
/**
 * TEST D'ENVOI EMAIL
 * Permet de tester la configuration SMTP et d'envoyer un email de test
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test d'envoi Email</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; min-height: 100vh; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: white; padding: 30px; margin-bottom: 20px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        h1 { color: #333; margin-bottom: 10px; }
        h2 { color: #667eea; margin: 20px 0 15px; font-size: 20px; }
        .info { background: #e3f2fd; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #2196F3; }
        .success { background: #d4edda; border-color: #28a745; color: #155724; }
        .error { background: #f8d7da; border-color: #dc3545; color: #721c24; }
        .warning { background: #fff3cd; border-color: #ffc107; color: #856404; }
        .config-item { padding: 10px; background: #f8f9fa; margin: 5px 0; border-radius: 5px; }
        .config-label { font-weight: 600; color: #555; }
        .config-value { color: #667eea; font-family: monospace; }
        .form-group { margin: 20px 0; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        input, textarea { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; }
        input:focus, textarea:focus { outline: none; border-color: #667eea; }
        button { background: #667eea; color: white; padding: 15px 30px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; width: 100%; margin-top: 10px; }
        button:hover { background: #5568d3; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; color: #e83e8c; }
        .guide { background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #ffc107; }
        .guide ol { margin-left: 20px; margin-top: 10px; }
        .guide li { margin: 10px 0; line-height: 1.6; }
        .guide a { color: #667eea; font-weight: 600; }
        .back-link { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 8px; }
        .back-link:hover { background: #5a6268; }
    </style>
</head>
<body>
<div class='container'>";

// Vérifier si PHPMailer est installé
$autoloadPath = __DIR__ . '/vendor/autoload.php';
$phpmailerInstalled = file_exists($autoloadPath);

if (!$phpmailerInstalled) {
    echo "<div class='card'>
        <h1>❌ PHPMailer non installé</h1>
        <div class='error info'>
            <p><strong>PHPMailer n'est pas installé sur votre serveur.</strong></p>
            <p>Veuillez d'abord exécuter <code>install_phpmailer.php</code></p>
        </div>
        <a href='install_phpmailer.php' class='back-link'>📦 Installer PHPMailer</a>
    </div></body></html>";
    exit;
}

require_once $autoloadPath;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// TRAITEMENT DU FORMULAIRE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testEmail = filter_var($_POST['test_email'] ?? '', FILTER_VALIDATE_EMAIL);
    $testMessage = htmlspecialchars($_POST['test_message'] ?? 'Ceci est un email de test');
    
    if ($testEmail) {
        echo "<div class='card'>";
        echo "<h1>📧 Envoi d'email de test...</h1>";
        
        try {
            $mail = new PHPMailer(true);
            
            // Configuration SMTP avec logs détaillés
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->Debugoutput = function($str, $level) {
                echo "<div class='info' style='font-size: 12px; font-family: monospace;'>$str</div>";
            };
            
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            $mail->CharSet = 'UTF-8';
            
            // Options SSL
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Configuration email
            $mail->setFrom(FROM_EMAIL, FROM_NAME);
            $mail->addAddress($testEmail);
            
            $mail->isHTML(true);
            $mail->Subject = '🧪 Email de test - ' . FROM_NAME;
            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(90deg, #667eea, #764ba2); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
                    .success { background: #d4edda; padding: 15px; border-radius: 5px; color: #155724; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>✅ Test d'envoi réussi !</h1>
                    </div>
                    <div class='content'>
                        <p><strong>Félicitations !</strong></p>
                        <p>Votre configuration SMTP fonctionne correctement.</p>
                        <div class='success'>
                            <p>📧 Configuration testée avec succès</p>
                            <p>⏰ " . date('d/m/Y H:i:s') . "</p>
                        </div>
                        <p><strong>Message de test :</strong></p>
                        <p>" . nl2br($testMessage) . "</p>
                        <hr>
                        <p style='color: #666; font-size: 14px;'>Cet email a été envoyé depuis " . FROM_NAME . "</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->AltBody = "Test réussi ! Configuration SMTP fonctionnelle.\n\nMessage: $testMessage";
            
            // Envoyer
            $mail->send();
            
            echo "<div class='success info'>";
            echo "<h2>✅ EMAIL ENVOYÉ AVEC SUCCÈS !</h2>";
            echo "<p>L'email a été envoyé à : <strong>$testEmail</strong></p>";
            echo "<p>Vérifiez votre boîte de réception (et les spams).</p>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='error info'>";
            echo "<h2>❌ ERREUR D'ENVOI</h2>";
            echo "<p><strong>Message d'erreur :</strong></p>";
            echo "<pre style='background: white; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
            echo htmlspecialchars($mail->ErrorInfo);
            echo "</pre>";
            echo "<p><strong>Exception :</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
            
            echo "<div class='warning info'>";
            echo "<h3>🔧 Solutions possibles :</h3>";
            echo "<ol>";
            echo "<li>Vérifiez que le mot de passe d'application est correct</li>";
            echo "<li>Activez l'authentification à 2 facteurs sur Gmail</li>";
            echo "<li>Générez un nouveau mot de passe d'application</li>";
            echo "<li>Vérifiez que 'Accès moins sécurisé' est désactivé (utilisez les mots de passe d'application à la place)</li>";
            echo "</ol>";
            echo "</div>";
        }
        
        echo "</div>";
    }
}

// AFFICHER LA CONFIGURATION ACTUELLE
echo "<div class='card'>";
echo "<h1>⚙️ Configuration SMTP actuelle</h1>";

echo "<div class='config-item'>";
echo "<span class='config-label'>Serveur SMTP :</span> ";
echo "<span class='config-value'>" . (defined('SMTP_HOST') ? SMTP_HOST : '❌ Non défini') . "</span>";
echo "</div>";

echo "<div class='config-item'>";
echo "<span class='config-label'>Port :</span> ";
echo "<span class='config-value'>" . (defined('SMTP_PORT') ? SMTP_PORT : '❌ Non défini') . "</span>";
echo "</div>";

echo "<div class='config-item'>";
echo "<span class='config-label'>Username (Email) :</span> ";
echo "<span class='config-value'>" . (defined('SMTP_USERNAME') ? SMTP_USERNAME : '❌ Non défini') . "</span>";
echo "</div>";

echo "<div class='config-item'>";
echo "<span class='config-label'>Password :</span> ";
$hasPassword = defined('SMTP_PASSWORD') && !empty(SMTP_PASSWORD);
echo "<span class='config-value'>" . ($hasPassword ? '✅ Défini (' . strlen(SMTP_PASSWORD) . ' caractères)' : '❌ Non défini') . "</span>";
echo "</div>";

echo "<div class='config-item'>";
echo "<span class='config-label'>De (From) :</span> ";
echo "<span class='config-value'>" . (defined('FROM_EMAIL') ? FROM_EMAIL : '❌ Non défini') . "</span>";
echo "</div>";

echo "</div>";

// GUIDE DE CONFIGURATION
echo "<div class='card'>";
echo "<h1>📚 Guide : Obtenir un mot de passe d'application Gmail</h1>";

echo "<div class='guide'>";
echo "<h3>⚠️ IMPORTANT : Vous devez utiliser un MOT DE PASSE D'APPLICATION, pas votre mot de passe Gmail normal !</h3>";
echo "</div>";

echo "<h2>Étape 1 : Activer l'authentification à 2 facteurs</h2>";
echo "<div class='info'>";
echo "<ol>";
echo "<li>Allez sur <a href='https://myaccount.google.com/security' target='_blank'>https://myaccount.google.com/security</a></li>";
echo "<li>Dans la section 'Connexion à Google', cliquez sur 'Validation en deux étapes'</li>";
echo "<li>Suivez les instructions pour activer la 2FA (si ce n'est pas déjà fait)</li>";
echo "</ol>";
echo "</div>";

echo "<h2>Étape 2 : Générer un mot de passe d'application</h2>";
echo "<div class='info'>";
echo "<ol>";
echo "<li>Une fois la 2FA activée, retournez sur <a href='https://myaccount.google.com/security' target='_blank'>https://myaccount.google.com/security</a></li>";
echo "<li>Cherchez 'Mots de passe des applications' (App passwords)</li>";
echo "<li>Cliquez dessus et connectez-vous si nécessaire</li>";
echo "<li>Sélectionnez 'Autre (nom personnalisé)'</li>";
echo "<li>Tapez 'Laverie' ou 'Mon Site'</li>";
echo "<li>Cliquez sur 'Générer'</li>";
echo "<li><strong>⚠️ IMPORTANT : Copiez le mot de passe de 16 caractères généré</strong></li>";
echo "</ol>";
echo "</div>";

echo "<h2>Étape 3 : Mettre à jour config.php</h2>";
echo "<div class='info'>";
echo "<p>Ouvrez votre fichier <code>config.php</code> et remplacez cette ligne :</p>";
echo "<pre style='background: white; padding: 10px; border-radius: 5px;'>define('SMTP_PASSWORD', 'ancien_mot_de_passe');</pre>";
echo "<p>Par le nouveau mot de passe d'application (16 caractères, sans espaces) :</p>";
echo "<pre style='background: white; padding: 10px; border-radius: 5px;'>define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx'); // Remplacez par votre mot de passe</pre>";
echo "</div>";

echo "</div>";

// FORMULAIRE DE TEST
echo "<div class='card'>";
echo "<h1>🧪 Tester l'envoi d'email</h1>";
echo "<form method='POST'>";
echo "<div class='form-group'>";
echo "<label>📧 Email de destination (où recevoir le test) :</label>";
echo "<input type='email' name='test_email' required placeholder='votre.email@example.com' value='" . (SMTP_USERNAME ?? '') . "'>";
echo "</div>";

echo "<div class='form-group'>";
echo "<label>💬 Message de test (optionnel) :</label>";
echo "<textarea name='test_message' rows='3' placeholder='Entrez un message de test...'>Ceci est un test d'envoi d'email depuis mon système de laverie.</textarea>";
echo "</div>";

echo "<button type='submit'>📤 Envoyer l'email de test</button>";
echo "</form>";
echo "</div>";

echo "<div class='card' style='text-align: center;'>";
echo "<a href='diagnostic.php' class='back-link'>🔍 Retour au diagnostic</a>";
echo "<a href='forgot_password.php' class='back-link'>🔐 Tester mot de passe oublié</a>";
echo "</div>";

echo "</div></body></html>";
?>