<?php
/**
 * Page de vérification d'email
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/errors.log');

require_once 'config.php';

$message = '';
$messageType = '';
$redirectToLogin = false;

if (isset($_GET['code'])) {
    $verificationCode = cleanInput($_GET['code']);
    
    try {
        error_log("========================================");
        error_log(">>> DÉBUT verify_email.php");
        error_log("Code reçu: " . $verificationCode);
        
        $conn = getDBConnection();
        
        // Récupérer la demande de vérification
        $stmt = $conn->prepare("
            SELECT id, email, user_data, expiry, verified 
            FROM email_verifications 
            WHERE verification_code = ? 
            LIMIT 1
        ");
        $stmt->execute([$verificationCode]);
        $verification = $stmt->fetch();
        
        if (!$verification) {
            error_log("❌ Code de vérification invalide");
            throw new Exception('Code de vérification invalide ou expiré.');
        }
        
        error_log("✅ Code trouvé pour: " . $verification['email']);
        
        // Vérifier si déjà vérifié
        if ($verification['verified']) {
            error_log("ℹ️ Email déjà vérifié");
            $message = '✅ Votre email a déjà été vérifié. Vous pouvez vous connecter.';
            $messageType = 'success';
            $redirectToLogin = true;
        } else {
            // Vérifier l'expiration
            $now = new DateTime();
            $expiry = new DateTime($verification['expiry']);
            
            if ($now > $expiry) {
                error_log("❌ Code expiré");
                throw new Exception('Ce lien de vérification a expiré. Veuillez vous réinscrire.');
            }
            
            // Décoder les données utilisateur
            $userData = json_decode($verification['user_data'], true);
            
            if (!$userData) {
                throw new Exception('Données utilisateur invalides.');
            }
            
            // Insérer l'utilisateur dans la base de données
            $stmt = $conn->prepare("
                INSERT INTO users (
                    lastname, firstname, email, phone, whatsapp, 
                    address, gender, password, customer_code, 
                    points_counter, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'actif', NOW())
            ");
            
            $stmt->execute([
                $userData['lastname'],
                $userData['firstname'],
                $userData['email'],
                $userData['phone'],
                $userData['whatsapp'],
                $userData['address'],
                $userData['gender'],
                $userData['password'],
                $userData['customer_code']
            ]);
            
            $userId = $conn->lastInsertId();
            
            error_log("✅ Utilisateur créé - ID: $userId");
            
            // Marquer la vérification comme effectuée
            $stmt = $conn->prepare("UPDATE email_verifications SET verified = 1 WHERE id = ?");
            $stmt->execute([$verification['id']]);
            
            error_log("✅ Vérification marquée comme complétée");
            
            $message = '🎉 Félicitations ! Votre compte a été créé avec succès. Vous allez être redirigé vers la page de connexion...';
            $messageType = 'success';
            $redirectToLogin = true;
            
            error_log(">>> FIN verify_email.php (SUCCÈS)");
        }
        
        error_log("========================================");
        
    } catch (Exception $e) {
        error_log("❌ ERREUR: " . $e->getMessage());
        error_log(">>> FIN verify_email.php (ÉCHEC)");
        error_log("========================================");
        
        $message = '❌ ' . $e->getMessage();
        $messageType = 'error';
    }
} else {
    $message = '❌ Aucun code de vérification fourni.';
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification d'email - Nelco Laverie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 50px;
            width: 100%;
            max-width: 500px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .success .icon {
            color: #10b981;
        }
        
        .error .icon {
            color: #ef4444;
        }
        
        h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 15px;
        }
        
        .message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }
        
        .loader {
            display: none;
            margin: 20px auto;
        }
        
        .loader.active {
            display: block;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container <?php echo $messageType; ?>">
        <?php if ($messageType === 'success'): ?>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Vérification réussie !</h1>
        <?php else: ?>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <h1>Erreur de vérification</h1>
        <?php endif; ?>
        
        <p class="message"><?php echo $message; ?></p>
        
        <?php if ($redirectToLogin): ?>
            <div class="loader active">
                <div class="spinner"></div>
                <p style="margin-top: 15px; color: #666;">Redirection en cours...</p>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = 'testht.php';
                }, 3000);
            </script>
        <?php else: ?>
            <a href="testht.php" class="btn">Retour à la connexion</a>
        <?php endif; ?>
    </div>
</body>
</html>