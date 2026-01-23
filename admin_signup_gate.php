<?php
/**
 * Page de vérification du code d'accès pour l'inscription admin
 * Un code secret doit être entré avant d'accéder au formulaire d'inscription
 */

session_start();

// Si déjà un code valide en session, rediriger vers l'inscription
if (isset($_SESSION['admin_signup_authorized']) && $_SESSION['admin_signup_authorized'] === true) {
    header("Location: admin_signup.php");
    exit();
}

// Si déjà connecté comme admin, rediriger vers le dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Inscription Admin - Nelco Laverie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .gate-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .gate-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .gate-header .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .gate-header .logo i {
            font-size: 40px;
            color: white;
        }

        .gate-header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .gate-header p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert.show {
            display: block;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 18px;
            letter-spacing: 3px;
            text-align: center;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }

        .form-group input:disabled {
            background: #f5f5f5;
            cursor: not-allowed;
        }

        .btn-verify {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-verify:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102,126,234,0.4);
        }

        .btn-verify:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .back-link a:hover {
            gap: 8px;
        }

        .info-box {
            background: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #1e40af;
            line-height: 1.6;
        }

        .info-box i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="gate-container">
        <div class="gate-header">
            <div class="logo">
                <i class="fas fa-key"></i>
            </div>
            <h1>Code d'Accès Requis</h1>
            <p>Entrez le code d'autorisation pour créer un compte administrateur</p>
        </div>

        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            Ce code est délivré uniquement aux personnes autorisées à gérer le système.
        </div>

        <div id="alertMessage" class="alert"></div>

        <form id="gateForm">
            <div class="form-group">
                <label for="accessCode">
                    <i class="fas fa-shield-alt"></i> Code d'Autorisation (10 chiffres)
                </label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input 
                        type="text" 
                        id="accessCode" 
                        name="accessCode" 
                        required 
                        autofocus
                        maxlength="10"
                        pattern="[0-9]{10}"
                        placeholder="0000000000"
                        inputmode="numeric"
                    >
                </div>
            </div>

            <button type="submit" class="btn-verify" id="verifyBtn">
                <span id="btnText">Vérifier le Code</span>
                <i class="fas fa-arrow-right" id="btnIcon"></i>
            </button>
        </form>

        <div class="back-link">
            <a href="admin_login.php">
                <i class="fas fa-arrow-left"></i>
                Retour à la connexion
            </a>
        </div>
    </div>

    <script>
        // Auto-format : accepter seulement les chiffres
        document.getElementById('accessCode').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });

        // Gestion du formulaire
        document.getElementById('gateForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const accessCode = document.getElementById('accessCode').value.trim();
            const verifyBtn = document.getElementById('verifyBtn');
            const btnText = document.getElementById('btnText');
            const btnIcon = document.getElementById('btnIcon');
            
            // Validation
            if (accessCode.length !== 10) {
                showAlert('Le code doit contenir exactement 10 chiffres', 'error');
                return;
            }
            
            // Désactiver le bouton et afficher le spinner
            verifyBtn.disabled = true;
            btnText.textContent = 'Vérification...';
            btnIcon.className = 'spinner';
            
            try {
                const response = await fetch('verify_admin_code.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        access_code: accessCode
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('Code validé ! Redirection...', 'success');
                    setTimeout(() => {
                        window.location.href = 'admin_signup.php';
                    }, 1000);
                } else {
                    showAlert(data.message || 'Code incorrect', 'error');
                    // Réactiver le bouton
                    verifyBtn.disabled = false;
                    btnText.textContent = 'Vérifier le Code';
                    btnIcon.className = 'fas fa-arrow-right';
                    // Réinitialiser le champ
                    document.getElementById('accessCode').value = '';
                    document.getElementById('accessCode').focus();
                }
                
            } catch (error) {
                console.error('Erreur:', error);
                showAlert('Erreur de connexion au serveur', 'error');
                // Réactiver le bouton
                verifyBtn.disabled = false;
                btnText.textContent = 'Vérifier le Code';
                btnIcon.className = 'fas fa-arrow-right';
            }
        });
        
        // Fonction pour afficher les alertes
        function showAlert(message, type) {
            const alertDiv = document.getElementById('alertMessage');
            alertDiv.textContent = message;
            alertDiv.className = `alert alert-${type} show`;
            
            // Masquer après 5 secondes (sauf succès)
            if (type !== 'success') {
                setTimeout(() => {
                    alertDiv.classList.remove('show');
                }, 5000);
            }
        }
    </script>
</body>
</html>