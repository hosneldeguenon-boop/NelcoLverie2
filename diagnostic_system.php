<?php
/**
 * FICHIER DE DIAGNOSTIC COMPLET DU SYSTÈME
 * À exécuter directement : http://votre-site.com/diagnostic_system.php
 */

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🔍 Diagnostic Système</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            line-height: 1.6;
        }
        .section {
            background: #2d2d2d;
            border-left: 4px solid #007acc;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #ce9178; }
        .info { color: #9cdcfe; }
        h2 { color: #4ec9b0; border-bottom: 2px solid #007acc; padding-bottom: 10px; }
        h3 { color: #dcdcaa; margin-top: 20px; }
        pre {
            background: #1e1e1e;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            margin-right: 10px;
        }
        .badge-ok { background: #0e639c; color: white; }
        .badge-error { background: #f48771; color: white; }
        .badge-warning { background: #ce9178; color: white; }
    </style>
</head>
<body>

<h1>🔍 DIAGNOSTIC SYSTÈME - RÉINITIALISATION MOT DE PASSE</h1>
<p><strong>Date :</strong> <?php echo date('Y-m-d H:i:s'); ?></p>

<?php

// ========================================
// 1. ENVIRONNEMENT SERVEUR
// ========================================
echo '<div class="section">';
echo '<h2>1️⃣ ENVIRONNEMENT SERVEUR</h2>';

echo '<h3>PHP</h3>';
echo '<span class="badge badge-ok">VERSION</span> ' . PHP_VERSION . '<br>';
echo '<span class="badge badge-ok">SAPI</span> ' . php_sapi_name() . '<br>';

echo '<h3>Extensions PHP critiques</h3>';
$extensions = ['pdo', 'pdo_mysql', 'json', 'mysqli', 'openssl', 'mbstring'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo '<span class="success">✅ ' . $ext . '</span><br>';
    } else {
        echo '<span class="error">❌ ' . $ext . ' (MANQUANT)</span><br>';
    }
}

echo '<h3>Configuration PHP</h3>';
$phpConfig = [
    'display_errors' => ini_get('display_errors'),
    'error_reporting' => error_reporting(),
    'log_errors' => ini_get('log_errors'),
    'error_log' => ini_get('error_log'),
    'post_max_size' => ini_get('post_max_size'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit')
];

foreach ($phpConfig as $key => $value) {
    echo "<span class='info'>$key:</span> <code>$value</code><br>";
}

echo '</div>';

// ========================================
// 2. FICHIERS SYSTÈME
// ========================================
echo '<div class="section">';
echo '<h2>2️⃣ FICHIERS SYSTÈME</h2>';

$files = [
    'config.php',
    'reset_password.php',
    'reset_passwords.php',
    'forgot_password.php',
    'forgot_passwords.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $size = filesize($path);
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $modified = date('Y-m-d H:i:s', filemtime($path));
        echo "<span class='success'>✅ $file</span><br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;Taille: <code>{$size} octets</code> | Permissions: <code>{$perms}</code> | Modifié: <code>{$modified}</code><br>";
    } else {
        echo "<span class='error'>❌ $file (INTROUVABLE)</span><br>";
    }
}

echo '</div>';

// ========================================
// 3. CONNEXION BASE DE DONNÉES
// ========================================
echo '<div class="section">';
echo '<h2>3️⃣ CONNEXION BASE DE DONNÉES</h2>';

if (file_exists(__DIR__ . '/config.php')) {
    echo '<span class="success">✅ config.php trouvé</span><br><br>';
    
    try {
        require_once 'config.php';
        
        if (function_exists('getDBConnection')) {
            echo '<span class="success">✅ Fonction getDBConnection() existe</span><br><br>';
            
            try {
                $conn = getDBConnection();
                echo '<span class="success">✅ CONNEXION BDD RÉUSSIE</span><br><br>';
                
                // Tester la connexion
                $stmt = $conn->query("SELECT VERSION() as version");
                $version = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<span class='info'>Version MySQL:</span> <code>{$version['version']}</code><br>";
                
                // Vérifier les tables
                echo '<h3>Tables existantes</h3>';
                $tables = ['users', 'password_reset_codes'];
                foreach ($tables as $table) {
                    try {
                        $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo "<span class='success'>✅ Table '$table' : {$result['count']} enregistrements</span><br>";
                        
                        // Structure de la table
                        $stmt = $conn->query("DESCRIBE $table");
                        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        echo "<pre style='margin-left: 20px;'>";
                        foreach ($columns as $col) {
                            echo "{$col['Field']} ({$col['Type']}) " . ($col['Null'] == 'NO' ? 'NOT NULL' : 'NULL') . "\n";
                        }
                        echo "</pre>";
                        
                    } catch (Exception $e) {
                        echo "<span class='error'>❌ Table '$table' : " . $e->getMessage() . "</span><br>";
                    }
                }
                
            } catch (Exception $e) {
                echo '<span class="error">❌ ÉCHEC CONNEXION BDD</span><br>';
                echo "<pre>{$e->getMessage()}</pre>";
            }
            
        } else {
            echo '<span class="error">❌ Fonction getDBConnection() NON DÉFINIE</span><br>';
        }
        
    } catch (Exception $e) {
        echo '<span class="error">❌ Erreur lors du chargement de config.php</span><br>';
        echo "<pre>{$e->getMessage()}</pre>";
    }
    
} else {
    echo '<span class="error">❌ config.php INTROUVABLE</span><br>';
}

echo '</div>';

// ========================================
// 4. TEST API ENDPOINT
// ========================================
echo '<div class="section">';
echo '<h2>4️⃣ TEST ENDPOINT reset_passwords.php</h2>';

if (file_exists(__DIR__ . '/reset_passwords.php')) {
    echo '<span class="success">✅ Fichier reset_passwords.php trouvé</span><br><br>';
    
    echo '<h3>Test d\'appel API (simulation)</h3>';
    echo '<p class="warning">⚠️ Ouvrez la console de votre navigateur et exécutez :</p>';
    echo '<pre>';
    echo "fetch('reset_passwords.php', {\n";
    echo "    method: 'POST',\n";
    echo "    headers: {'Content-Type': 'application/json'},\n";
    echo "    body: JSON.stringify({\n";
    echo "        email: 'test@example.com',\n";
    echo "        code: '123456',\n";
    echo "        new_password: 'Test1234'\n";
    echo "    })\n";
    echo "})\n";
    echo ".then(r => r.text())\n";
    echo ".then(t => console.log('Réponse brute:', t))\n";
    echo ".catch(e => console.error('Erreur:', e));\n";
    echo '</pre>';
    
} else {
    echo '<span class="error">❌ reset_passwords.php INTROUVABLE</span><br>';
}

echo '</div>';

// ========================================
// 5. LOGS D'ERREURS
// ========================================
echo '<div class="section">';
echo '<h2>5️⃣ LOGS D\'ERREURS (100 dernières lignes)</h2>';

$logFile = __DIR__ . '/errors.log';

if (file_exists($logFile)) {
    echo '<span class="success">✅ Fichier errors.log trouvé</span><br><br>';
    
    $logContent = file($logFile);
    $lastLines = array_slice($logContent, -100);
    
    if (count($lastLines) > 0) {
        echo '<pre style="max-height: 400px; overflow-y: scroll;">';
        foreach ($lastLines as $line) {
            if (strpos($line, '❌') !== false || strpos($line, 'ERREUR') !== false) {
                echo '<span class="error">' . htmlspecialchars($line) . '</span>';
            } elseif (strpos($line, '✅') !== false) {
                echo '<span class="success">' . htmlspecialchars($line) . '</span>';
            } else {
                echo htmlspecialchars($line);
            }
        }
        echo '</pre>';
    } else {
        echo '<p class="info">📄 Fichier vide</p>';
    }
} else {
    echo '<span class="warning">⚠️ Aucun fichier errors.log trouvé</span><br>';
    echo '<p>Le fichier sera créé automatiquement lors de la première erreur.</p>';
}

echo '</div>';

// ========================================
// 6. TESTS UNITAIRES
// ========================================
echo '<div class="section">';
echo '<h2>6️⃣ TESTS UNITAIRES</h2>';

echo '<h3>Test password_hash()</h3>';
$testPassword = 'Test1234';
$hash = password_hash($testPassword, PASSWORD_DEFAULT);
$verify = password_verify($testPassword, $hash);
if ($verify) {
    echo '<span class="success">✅ password_hash() et password_verify() fonctionnent</span><br>';
} else {
    echo '<span class="error">❌ Problème avec password_hash()</span><br>';
}

echo '<h3>Test JSON encode/decode</h3>';
$testData = ['success' => true, 'message' => 'Test français éàü'];
$json = json_encode($testData, JSON_UNESCAPED_UNICODE);
$decoded = json_decode($json, true);
if ($decoded['message'] === $testData['message']) {
    echo '<span class="success">✅ JSON encode/decode avec UTF-8 fonctionne</span><br>';
} else {
    echo '<span class="error">❌ Problème avec JSON UTF-8</span><br>';
}

echo '<h3>Test DateTime</h3>';
try {
    $now = new DateTime();
    $future = new DateTime('+15 minutes');
    if ($future > $now) {
        echo '<span class="success">✅ DateTime fonctionne correctement</span><br>';
    }
} catch (Exception $e) {
    echo '<span class="error">❌ Erreur DateTime: ' . $e->getMessage() . '</span><br>';
}

echo '</div>';

// ========================================
// 7. RECOMMANDATIONS
// ========================================
echo '<div class="section">';
echo '<h2>7️⃣ RECOMMANDATIONS</h2>';

echo '<ul>';
echo '<li class="info">✓ Vérifiez que tous les fichiers ci-dessus sont présents</li>';
echo '<li class="info">✓ Vérifiez que la base de données est accessible</li>';
echo '<li class="info">✓ Vérifiez les permissions des fichiers (644 pour PHP)</li>';
echo '<li class="info">✓ Consultez errors.log après chaque tentative</li>';
echo '<li class="warning">⚠️ Supprimez ce fichier de diagnostic après utilisation (sécurité)</li>';
echo '</ul>';

echo '</div>';

?>

</body>
</html>