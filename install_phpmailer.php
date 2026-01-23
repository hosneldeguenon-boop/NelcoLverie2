<?php
/**
 * Script d'installation automatique de PHPMailer
 * À exécuter UNE FOIS depuis le navigateur
 */

set_time_limit(300); // 5 minutes max
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Installation PHPMailer</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: sans-serif; }
        body { background: #f5f7fa; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        .step { background: #e3f2fd; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #2196F3; }
        .success { background: #d4edda; border-color: #28a745; color: #155724; }
        .error { background: #f8d7da; border-color: #dc3545; color: #721c24; }
        .warning { background: #fff3cd; border-color: #ffc107; color: #856404; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; font-size: 12px; margin: 10px 0; }
        .btn { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin: 10px 5px; }
        .progress { width: 100%; height: 30px; background: #e0e0e0; border-radius: 15px; overflow: hidden; margin: 20px 0; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #667eea, #764ba2); transition: width 0.3s; text-align: center; line-height: 30px; color: white; font-weight: bold; }
    </style>
</head>
<body>
<div class='container'>
<h1>📦 Installation de PHPMailer</h1>";

$baseDir = __DIR__;
$vendorDir = $baseDir . '/vendor';
$phpmailerDir = $vendorDir . '/phpmailer/phpmailer';
$srcDir = $phpmailerDir . '/src';

$steps = [
    'download' => false,
    'extract' => false,
    'structure' => false,
    'autoload' => false,
    'verify' => false
];

try {
    // ÉTAPE 1 : Créer la structure de dossiers
    echo "<div class='step'>📁 Étape 1 : Création de la structure...</div>";
    
    if (!is_dir($vendorDir)) {
        mkdir($vendorDir, 0755, true);
    }
    if (!is_dir($phpmailerDir)) {
        mkdir($phpmailerDir, 0755, true);
    }
    if (!is_dir($srcDir)) {
        mkdir($srcDir, 0755, true);
    }
    
    echo "<div class='step success'>✅ Structure créée : vendor/phpmailer/phpmailer/src/</div>";
    $steps['structure'] = true;
    
    // ÉTAPE 2 : Télécharger PHPMailer
    echo "<div class='step'>⬇️ Étape 2 : Téléchargement de PHPMailer...</div>";
    
    $zipUrl = 'https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v6.9.1.zip';
    $zipFile = $baseDir . '/phpmailer.zip';
    
    // Vérifier si allow_url_fopen est activé
    if (!ini_get('allow_url_fopen')) {
        throw new Exception('allow_url_fopen est désactivé. Installation manuelle requise.');
    }
    
    $zipContent = @file_get_contents($zipUrl);
    
    if ($zipContent === false) {
        throw new Exception('Impossible de télécharger PHPMailer depuis GitHub');
    }
    
    file_put_contents($zipFile, $zipContent);
    echo "<div class='step success'>✅ PHPMailer téléchargé (" . round(filesize($zipFile) / 1024, 2) . " KB)</div>";
    $steps['download'] = true;
    
    // ÉTAPE 3 : Extraire le ZIP
    echo "<div class='step'>📦 Étape 3 : Extraction du fichier ZIP...</div>";
    
    if (!class_exists('ZipArchive')) {
        throw new Exception('Extension ZIP non disponible. Installation manuelle requise.');
    }
    
    $zip = new ZipArchive();
    if ($zip->open($zipFile) !== true) {
        throw new Exception('Impossible d\'ouvrir le fichier ZIP');
    }
    
    $extractDir = $baseDir . '/phpmailer-temp';
    $zip->extractTo($extractDir);
    $zip->close();
    
    echo "<div class='step success'>✅ Fichiers extraits</div>";
    $steps['extract'] = true;
    
    // ÉTAPE 4 : Copier les fichiers nécessaires
    echo "<div class='step'>📋 Étape 4 : Copie des fichiers PHP...</div>";
    
    $sourceDir = $extractDir . '/PHPMailer-6.9.1/src';
    
    if (!is_dir($sourceDir)) {
        throw new Exception('Répertoire source PHPMailer introuvable');
    }
    
    $phpFiles = [
        'Exception.php',
        'PHPMailer.php',
        'SMTP.php',
        'POP3.php',
        'OAuth.php'
    ];
    
    $copiedFiles = 0;
    foreach ($phpFiles as $file) {
        $source = $sourceDir . '/' . $file;
        $dest = $srcDir . '/' . $file;
        
        if (file_exists($source)) {
            copy($source, $dest);
            $copiedFiles++;
            echo "<div class='step'>✓ $file copié</div>";
        }
    }
    
    echo "<div class='step success'>✅ $copiedFiles fichiers copiés</div>";
    
    // ÉTAPE 5 : Créer l'autoload
    echo "<div class='step'>🔧 Étape 5 : Création de l'autoload...</div>";
    
    $autoloadContent = "<?php
/**
 * Autoload PHPMailer - Généré automatiquement
 */

\$phpmailerPath = __DIR__ . '/phpmailer/phpmailer/src/';

require_once \$phpmailerPath . 'Exception.php';
require_once \$phpmailerPath . 'PHPMailer.php';
require_once \$phpmailerPath . 'SMTP.php';

return true;
";
    
    file_put_contents($vendorDir . '/autoload.php', $autoloadContent);
    echo "<div class='step success'>✅ Autoload créé</div>";
    $steps['autoload'] = true;
    
    // ÉTAPE 6 : Nettoyer
    echo "<div class='step'>🧹 Étape 6 : Nettoyage...</div>";
    
    unlink($zipFile);
    
    // Supprimer le répertoire temporaire
    function deleteDirectory($dir) {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
    
    deleteDirectory($extractDir);
    echo "<div class='step success'>✅ Fichiers temporaires supprimés</div>";
    
    // ÉTAPE 7 : Vérification finale
    echo "<div class='step'>✔️ Étape 7 : Vérification...</div>";
    
    require_once $vendorDir . '/autoload.php';
    
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "<div class='step success'>";
        echo "<h2>✅ Installation réussie !</h2>";
        echo "<p>PHPMailer est maintenant installé et fonctionnel.</p>";
        echo "</div>";
        $steps['verify'] = true;
    } else {
        throw new Exception('Vérification échouée : classe PHPMailer introuvable');
    }
    
    // Résumé
    echo "<div class='step success'>";
    echo "<h3>📊 Résumé de l'installation</h3>";
    echo "<ul style='margin-left: 20px; margin-top: 10px;'>";
    echo "<li>✅ Structure de dossiers créée</li>";
    echo "<li>✅ PHPMailer téléchargé et extrait</li>";
    echo "<li>✅ Fichiers PHP copiés ($copiedFiles fichiers)</li>";
    echo "<li>✅ Autoload configuré</li>";
    echo "<li>✅ Installation vérifiée</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='text-align: center; margin-top: 30px;'>";
    echo "<a href='diagnostic.php' class='btn'>🔍 Lancer le Diagnostic</a>";
    echo "<a href='forgot_password.php' class='btn'>🔒 Tester Reset MDP</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='step error'>";
    echo "<h3>❌ Erreur lors de l'installation</h3>";
    echo "<p><strong>Message :</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    
    echo "<div class='step warning'>";
    echo "<h3>📝 Installation manuelle requise</h3>";
    echo "<p>Suivez ces étapes :</p>";
    echo "<ol style='margin-left: 20px; margin-top: 10px;'>";
    echo "<li>Téléchargez PHPMailer : <a href='https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v6.9.1.zip' target='_blank'>Télécharger</a></li>";
    echo "<li>Décompressez le fichier sur votre ordinateur</li>";
    echo "<li>Créez le dossier : <code>vendor/phpmailer/phpmailer/src/</code></li>";
    echo "<li>Copiez tous les fichiers .php du dossier <code>src/</code> vers <code>vendor/phpmailer/phpmailer/src/</code></li>";
    echo "<li>Créez le fichier <code>vendor/autoload.php</code> avec le contenu fourni dans le guide</li>";
    echo "<li>Uploadez le tout via FileZilla dans <code>/htdocs/vendor/</code></li>";
    echo "</ol>";
    echo "</div>";
}

echo "</div></body></html>";
?>