<?php
/**
 * 🔍 DIAGNOSTIC PHPMAILER - Vérification de l'installation
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Diagnostic PHPMailer</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; min-height: 100vh; }
        .container { max-width: 900px; margin: 0 auto; }
        .card { background: white; padding: 30px; margin-bottom: 20px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        h1 { color: #333; margin-bottom: 20px; font-size: 28px; }
        h2 { color: #667eea; margin: 20px 0 15px; font-size: 20px; }
        .status { padding: 15px; margin: 10px 0; border-radius: 8px; font-weight: 600; }
        .success { background: #d4edda; border-left: 4px solid #28a745; color: #155724; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; color: #721c24; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; color: #856404; }
        .info { background: #e3f2fd; border-left: 4px solid #2196F3; color: #0d47a1; }
        .file-list { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .file-item { padding: 8px; margin: 5px 0; font-family: monospace; }
        .exists { color: #28a745; }
        .missing { color: #dc3545; }
        code { background: #f8f9fa; padding: 3px 8px; border-radius: 4px; font-family: monospace; color: #e83e8c; }
        .solution { background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #ffc107; }
        .solution ol { margin-left: 20px; margin-top: 10px; }
        .solution li { margin: 10px 0; line-height: 1.6; }
        .path { background: white; padding: 10px; border-radius: 5px; margin: 10px 0; font-family: monospace; word-break: break-all; }
    </style>
</head>
<body>
<div class='container'>";

echo "<div class='card'>";
echo "<h1>🔍 Diagnostic PHPMailer</h1>";

// Chemin actuel
$currentDir = __DIR__;
echo "<div class='info status'>";
echo "📁 <strong>Répertoire actuel :</strong><br>";
echo "<div class='path'>$currentDir</div>";
echo "</div>";

// Vérifier PHPMailer
$phpmailerPath = $currentDir . '/PHPMailer/src/';
$vendorPath = $currentDir . '/vendor/';

echo "<h2>📦 Vérification de l'installation PHPMailer</h2>";

// Cas 1 : PHPMailer manuel (GitHub)
$manualFiles = [
    'Exception.php' => $phpmailerPath . 'Exception.php',
    'PHPMailer.php' => $phpmailerPath . 'PHPMailer.php',
    'SMTP.php' => $phpmailerPath . 'SMTP.php'
];

$manualInstalled = true;
echo "<div class='file-list'>";
echo "<strong>🔹 Installation manuelle (PHPMailer/src/) :</strong><br><br>";

foreach ($manualFiles as $name => $path) {
    $exists = file_exists($path);
    $manualInstalled = $manualInstalled && $exists;
    $class = $exists ? 'exists' : 'missing';
    $icon = $exists ? '✅' : '❌';
    echo "<div class='file-item $class'>$icon $name : " . ($exists ? 'Présent' : 'Manquant') . "</div>";
    if ($exists) {
        echo "<div style='margin-left: 30px; font-size: 12px; color: #666;'>$path</div>";
    }
}
echo "</div>";

// Cas 2 : Composer/Vendor
$vendorAutoload = $vendorPath . 'autoload.php';
$composerInstalled = file_exists($vendorAutoload);

echo "<div class='file-list'>";
echo "<strong>🔹 Installation via Composer (vendor/) :</strong><br><br>";
$class = $composerInstalled ? 'exists' : 'missing';
$icon = $composerInstalled ? '✅' : '❌';
echo "<div class='file-item $class'>$icon vendor/autoload.php : " . ($composerInstalled ? 'Présent' : 'Manquant') . "</div>";
if ($composerInstalled) {
    echo "<div style='margin-left: 30px; font-size: 12px; color: #666;'>$vendorAutoload</div>";
}
echo "</div>";

// Résultat final
echo "<h2>📊 Résultat du diagnostic</h2>";

if ($manualInstalled) {
    echo "<div class='success status'>";
    echo "✅ <strong>PHPMailer est correctement installé (Installation manuelle)</strong><br>";
    echo "Les fichiers PHPMailer sont présents dans le dossier PHPMailer/src/<br>";
    echo "Votre système devrait fonctionner correctement.";
    echo "</div>";
    
    // Test de chargement
    echo "<h2>🧪 Test de chargement des classes</h2>";
    try {
        require_once $phpmailerPath . 'Exception.php';
        require_once $phpmailerPath . 'PHPMailer.php';
        require_once $phpmailerPath . 'SMTP.php';
        
        echo "<div class='success status'>";
        echo "✅ <strong>Classes PHPMailer chargées avec succès !</strong><br>";
        echo "PHPMailer\PHPMailer\PHPMailer : Disponible<br>";
        echo "PHPMailer\PHPMailer\SMTP : Disponible<br>";
        echo "PHPMailer\PHPMailer\Exception : Disponible";
        echo "</div>";
        
        // Vérifier que les classes existent
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            echo "<div class='success status'>";
            echo "✅ <strong>La classe PHPMailer est accessible</strong>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error status'>";
        echo "❌ <strong>Erreur lors du chargement :</strong><br>";
        echo htmlspecialchars($e->getMessage());
        echo "</div>";
    }
    
} elseif ($composerInstalled) {
    echo "<div class='success status'>";
    echo "✅ <strong>PHPMailer est installé via Composer</strong><br>";
    echo "Le fichier vendor/autoload.php est présent.";
    echo "</div>";
    
} else {
    echo "<div class='error status'>";
    echo "❌ <strong>PHPMailer n'est PAS installé !</strong><br>";
    echo "Aucune installation détectée (ni manuelle, ni Composer).";
    echo "</div>";
    
    echo "<div class='solution'>";
    echo "<h3>💡 Solutions :</h3>";
    echo "<ol>";
    echo "<li><strong>Télécharger PHPMailer depuis GitHub :</strong><br>";
    echo "   • Allez sur <a href='https://github.com/PHPMailer/PHPMailer' target='_blank'>https://github.com/PHPMailer/PHPMailer</a><br>";
    echo "   • Cliquez sur 'Code' → 'Download ZIP'<br>";
    echo "   • Décompressez et uploadez le dossier 'src' dans un dossier 'PHPMailer' sur votre serveur</li>";
    echo "<li><strong>Structure attendue :</strong><br>";
    echo "   <code>votre_site/PHPMailer/src/Exception.php</code><br>";
    echo "   <code>votre_site/PHPMailer/src/PHPMailer.php</code><br>";
    echo "   <code>votre_site/PHPMailer/src/SMTP.php</code></li>";
    echo "<li><strong>Ou installer via Composer (si disponible) :</strong><br>";
    echo "   <code>composer require phpmailer/phpmailer</code></li>";
    echo "</ol>";
    echo "</div>";
}

// Test de configuration
if ($manualInstalled || $composerInstalled) {
    echo "<h2>⚙️ Configuration SMTP</h2>";
    
    require_once 'config.php';
    
    echo "<div class='file-list'>";
    echo "<div class='file-item'><strong>Serveur SMTP :</strong> " . (defined('SMTP_HOST') ? SMTP_HOST : '❌ Non défini') . "</div>";
    echo "<div class='file-item'><strong>Port :</strong> " . (defined('SMTP_PORT') ? SMTP_PORT : '❌ Non défini') . "</div>";
    echo "<div class='file-item'><strong>Username :</strong> " . (defined('SMTP_USERNAME') ? SMTP_USERNAME : '❌ Non défini') . "</div>";
    echo "<div class='file-item'><strong>Password :</strong> " . (defined('SMTP_PASSWORD') && !empty(SMTP_PASSWORD) ? '✅ Défini (' . strlen(SMTP_PASSWORD) . ' caractères)' : '❌ Non défini') . "</div>";
    echo "<div class='file-item'><strong>From Email :</strong> " . (defined('FROM_EMAIL') ? FROM_EMAIL : '❌ Non défini') . "</div>";
    echo "<div class='file-item'><strong>From Name :</strong> " . (defined('FROM_NAME') ? FROM_NAME : '❌ Non défini') . "</div>";
    echo "</div>";
}

// Recommandations
echo "<h2>📝 Prochaines étapes</h2>";
echo "<div class='info status'>";

if ($manualInstalled) {
    echo "✅ <strong>Votre installation est prête !</strong><br><br>";
    echo "1. Vérifiez que le fichier <code>email_config.php</code> utilise le bon chargement<br>";
    echo "2. Testez l'envoi d'email avec <code>test_email.php</code><br>";
    echo "3. Essayez une inscription sur <code>creer_compte.php</code>";
} else {
    echo "⚠️ <strong>Actions requises :</strong><br><br>";
    echo "1. Installez PHPMailer (voir solutions ci-dessus)<br>";
    echo "2. Vérifiez la structure des dossiers<br>";
    echo "3. Relancez ce diagnostic";
}

echo "</div>";

echo "</div>"; // Fermeture card

echo "<div class='card' style='text-align: center;'>";
echo "<a href='test_email.php' style='display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin: 5px;'>🧪 Tester l'envoi d'email</a>";
echo "<a href='creer_compte.php' style='display: inline-block; padding: 12px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 8px; margin: 5px;'>📝 Page d'inscription</a>";
echo "</div>";

echo "</div></body></html>";
?>
