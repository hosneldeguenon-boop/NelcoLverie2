<?php
/**
 * TEST ULTRA BASIQUE
 * Pour identifier où le problème se situe
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test Basique</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 10px 0; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        h1 { color: #333; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🧪 Test Basique PHP</h1>
    
    <div class="box success">
        <h2>✅ PHP fonctionne !</h2>
        <p>Version PHP : <strong><?php echo PHP_VERSION; ?></strong></p>
        <p>Heure serveur : <strong><?php echo date('Y-m-d H:i:s'); ?></strong></p>
    </div>

    <?php
    // TEST 1 : Fichiers présents
    echo "<div class='box info'>";
    echo "<h2>1️⃣ Vérification des fichiers</h2>";
    
    $files = [
        'config.php',
        'email_config.php',
        'send_reset_code.php',
        'verify_reset_code.php',
        'reset_password.php',
        'forgot_password.php'
    ];
    
    foreach ($files as $file) {
        $exists = file_exists($file);
        $icon = $exists ? '✅' : '❌';
        echo "<p>$icon <strong>$file</strong> : " . ($exists ? 'Présent' : 'ABSENT') . "</p>";
    }
    echo "</div>";
    
    // TEST 2 : Charger config.php
    echo "<div class='box info'>";
    echo "<h2>2️⃣ Test de config.php</h2>";
    
    try {
        require_once 'config.php';
        echo "<p>✅ config.php chargé avec succès</p>";
        
        // Vérifier les constantes
        $constants = ['DB_HOST', 'DB_NAME', 'DB_USER', 'SMTP_HOST', 'SMTP_USERNAME'];
        foreach ($constants as $const) {
            $defined = defined($const);
            $icon = $defined ? '✅' : '❌';
            echo "<p>$icon <strong>$const</strong> : " . ($defined ? 'Défini' : 'NON défini') . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ ERREUR : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    echo "</div>";
    
    // TEST 3 : Connexion BDD
    echo "<div class='box info'>";
    echo "<h2>3️⃣ Test connexion BDD</h2>";
    
    try {
        $conn = getDBConnection();
        echo "<p>✅ Connexion BDD réussie</p>";
        
        // Tester une requête simple
        $stmt = $conn->query("SELECT COUNT(*) as total FROM users");
        $result = $stmt->fetch();
        echo "<p>✅ Requête OK : {$result['total']} utilisateur(s)</p>";
        
    } catch (Exception $e) {
        echo "<p>❌ ERREUR BDD : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    echo "</div>";
    
    // TEST 4 : PHPMailer
    echo "<div class='box info'>";
    echo "<h2>4️⃣ Test PHPMailer</h2>";
    
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
        
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            echo "<p>✅ PHPMailer trouvé</p>";
        } else {
            echo "<p>❌ PHPMailer non chargé</p>";
        }
    } else {
        echo "<p>❌ vendor/autoload.php introuvable</p>";
    }
    echo "</div>";
    
    // TEST 5 : Test AJAX simple
    echo "<div class='box info'>";
    echo "<h2>5️⃣ Test AJAX</h2>";
    echo "<button onclick='testAjax()' style='padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>
        🧪 Tester AJAX
    </button>";
    echo "<div id='ajax-result' style='margin-top: 10px;'></div>";
    echo "</div>";
    ?>
    
    <script>
    async function testAjax() {
        const resultDiv = document.getElementById('ajax-result');
        resultDiv.innerHTML = '<p style="color: blue;">🔄 Test en cours...</p>';
        
        try {
            console.log('🧪 Test AJAX vers test_ajax.php');
            
            const response = await fetch('test_ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ test: 'hello' })
            });
            
            console.log('📡 Statut:', response.status);
            
            const text = await response.text();
            console.log('📦 Réponse:', text);
            
            let data;
            try {
                data = JSON.parse(text);
                
                if (data.success) {
                    resultDiv.innerHTML = '<p style="color: green;">✅ AJAX fonctionne ! Message: ' + data.message + '</p>';
                } else {
                    resultDiv.innerHTML = '<p style="color: red;">❌ Erreur: ' + data.message + '</p>';
                }
            } catch (e) {
                resultDiv.innerHTML = '<p style="color: red;">❌ Réponse non-JSON:<br><pre>' + text + '</pre></p>';
            }
            
        } catch (error) {
            console.error('❌ Erreur:', error);
            resultDiv.innerHTML = '<p style="color: red;">❌ Erreur: ' + error.message + '</p>';
        }
    }
    </script>
    
    <div class="box" style="text-align: center; margin-top: 30px;">
        <a href="forgot_password.php" style="display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin: 5px;">
            🔐 Aller à Reset Password
        </a>
        <a href="diagnostic.php" style="display: inline-block; padding: 12px 24px; background: #17a2b8; color: white; text-decoration: none; border-radius: 8px; margin: 5px;">
            🔍 Diagnostic complet
        </a>
    </div>
</body>
</html>