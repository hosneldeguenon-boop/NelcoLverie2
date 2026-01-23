<?php
/**
 * Page de connexion administrateur
 */

session_start();

// Si déjà connecté, rediriger vers le dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit();
}

// Empêcher le cache de cette page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administrateur - Nelco Laverie</title>
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

        .login-container {
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

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .login-header .logo i {
            font-size: 40px;
            color: white;
        }

        .login-header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
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

        .alert-info {
            background: #fef7e0;
            color: #856404;
            border: 1px solid #ffeaa7;
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
            font-size: 15px;
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

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            cursor: pointer;
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        .btn-login {
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

        .btn-login:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102,126,234,0.4);
        }

        .btn-login:disabled {
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

        .links {
            text-align: center;
            margin-top: 20px;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
            margin: 0 10px;
        }

        .links a:hover {
            gap: 8px;
        }

        .divider {
            margin: 15px 0;
            text-align: center;
            color: #999;
            font-size: 14px;
        }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin-responsive.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1>Espace Administrateur</h1>
            <p>Nelco Laverie</p>
        </div>

        <div id="alertMessage" class="alert"></div>

        <form id="loginForm">
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i> Nom d'utilisateur
                </label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        autofocus
                        autocomplete="username"
                        placeholder="Entrez votre pseudonyme"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Mot de passe
                </label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        autocomplete="current-password"
                        placeholder="Entrez votre mot de passe"
                    >
                    <i class="fas fa-eye-slash password-toggle" id="togglePassword"></i>
                </div>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <span id="btnText">Se connecter</span>
                <i class="fas fa-arrow-right" id="btnIcon"></i>
            </button>
        </form>

        <div class="divider">ou</div>

        <div class="links">
            <a href="admin_signup_gate.php">
                <i class="fas fa-user-plus"></i>
                Créer un compte
            </a>
            <a href="index.html">
                <i class="fas fa-arrow-left"></i>
                Retour à l'acceuil
            </a>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
            this.classList.toggle('fa-eye');
        });

        // Vérifier les paramètres URL au chargement
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.get('logout') === 'success') {
                showAlert('Vous avez été déconnecté avec succès', 'success');
            }
            
            if (urlParams.get('timeout') === '1') {
                showAlert('Votre session a expiré. Veuillez vous reconnecter.', 'info');
            }

            if (urlParams.get('registered') === '1') {
                showAlert('Compte créé avec succès ! Vous pouvez maintenant vous connecter.', 'success');
            }
        });

        // Gestion du formulaire de connexion
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');
            const btnText = document.getElementById('btnText');
            const btnIcon = document.getElementById('btnIcon');
            
            // Validation
            if (!username || !password) {
                showAlert('Veuillez remplir tous les champs', 'error');
                return;
            }
            
            // Désactiver le bouton et afficher le spinner
            loginBtn.disabled = true;
            btnText.textContent = 'Connexion...';
            btnIcon.className = 'spinner';
            
            try {
                const response = await fetch('admin_login_process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        username: username,
                        password: password
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('Connexion réussie ! Redirection...', 'success');
                    setTimeout(() => {
                        window.location.href = 'admin_dashboard.php';
                    }, 1000);
                } else {
                    showAlert(data.message || 'Erreur de connexion', 'error');
                    // Réactiver le bouton
                    loginBtn.disabled = false;
                    btnText.textContent = 'Se connecter';
                    btnIcon.className = 'fas fa-arrow-right';
                }
                
            } catch (error) {
                console.error('Erreur:', error);
                showAlert('Erreur de connexion au serveur', 'error');
                // Réactiver le bouton
                loginBtn.disabled = false;
                btnText.textContent = 'Se connecter';
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