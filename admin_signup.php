<?php
/**
 * Formulaire d'inscription administrateur
 * Accessible uniquement avec un code valide
 */

session_start();

// Vérifier l'autorisation d'accès
if (!isset($_SESSION['admin_signup_authorized']) || $_SESSION['admin_signup_authorized'] !== true) {
    header("Location: admin_signup_gate.php");
    exit();
}

// Vérifier le timeout de l'autorisation (10 minutes)
if (isset($_SESSION['admin_signup_timestamp']) && (time() - $_SESSION['admin_signup_timestamp'] > 600)) {
    unset($_SESSION['admin_signup_authorized']);
    unset($_SESSION['admin_signup_code_id']);
    unset($_SESSION['admin_signup_timestamp']);
    header("Location: admin_signup_gate.php?timeout=1");
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
    <title>Inscription Admin - Nelco Laverie</title>
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

        .signup-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 600px;
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

        .signup-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .signup-header .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .signup-header .logo i {
            font-size: 40px;
            color: white;
        }

        .signup-header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .signup-header p {
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
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

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group select {
            cursor: pointer;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
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

        .btn-signup {
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
            margin-top: 20px;
        }

        .btn-signup:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102,126,234,0.4);
        }

        .btn-signup:disabled {
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

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin-responsive.css">
</head>
<body>
    <div class="signup-container">
        <div class="signup-header">
            <div class="logo">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1>Créer un Compte Admin</h1>
            <p>Remplissez tous les champs pour créer votre compte</p>
        </div>

        <div id="alertMessage" class="alert"></div>

        <form id="signupForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="lastname">Nom *</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="lastname" required placeholder="Votre nom">
                    </div>
                </div>

                <div class="form-group">
                    <label for="firstname">Prénom *</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="firstname" required placeholder="Votre prénom">
                    </div>
                </div>
            </div>

            <div class="form-group full-width">
                <label for="username">Pseudonyme *</label>
                <div class="input-wrapper">
                    <i class="fas fa-at"></i>
                    <input type="text" id="username" required placeholder="Pseudonyme unique">
                </div>
            </div>

            <div class="form-group full-width">
                <label for="email">Email *</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" required placeholder="votre@email.com">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Téléphone *</label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="phone" required placeholder="0123456789">
                    </div>
                </div>

                <div class="form-group">
                    <label for="gender">Sexe *</label>
                    <div class="input-wrapper">
                        <i class="fas fa-venus-mars"></i>
                        <select id="gender" required>
                            <option value="">Sélectionner</option>
                            <option value="M">Masculin</option>
                            <option value="F">Féminin</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group full-width">
                <label for="password">Mot de passe * (8 caractères minimum)</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" required minlength="8" placeholder="Mot de passe sécurisé">
                    <i class="fas fa-eye-slash password-toggle" id="togglePassword"></i>
                </div>
            </div>

            <div class="form-group full-width">
                <label for="confirmPassword">Confirmer le mot de passe *</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="confirmPassword" required minlength="8" placeholder="Confirmer le mot de passe">
                    <i class="fas fa-eye-slash password-toggle" id="toggleConfirmPassword"></i>
                </div>
            </div>

            <button type="submit" class="btn-signup" id="signupBtn">
                <span id="btnText">Créer le compte</span>
                <i class="fas fa-arrow-right" id="btnIcon"></i>
            </button>
        </form>

        <div class="login-link">
            Vous avez déjà un compte ? <a href="admin_login.php">Se connecter</a>
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

        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('confirmPassword');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
            this.classList.toggle('fa-eye');
        });

        // Auto-format phone number
        document.getElementById('phone').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });

        // Form submission
        document.getElementById('signupForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const lastname = document.getElementById('lastname').value.trim();
            const firstname = document.getElementById('firstname').value.trim();
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const gender = document.getElementById('gender').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            const signupBtn = document.getElementById('signupBtn');
            const btnText = document.getElementById('btnText');
            const btnIcon = document.getElementById('btnIcon');
            
            // Validations
            if (!lastname || !firstname || !username || !email || !phone || !gender || !password || !confirmPassword) {
                showAlert('Tous les champs sont obligatoires', 'error');
                return;
            }
            
            if (password.length < 8) {
                showAlert('Le mot de passe doit contenir au moins 8 caractères', 'error');
                return;
            }
            
            if (password !== confirmPassword) {
                showAlert('Les mots de passe ne correspondent pas', 'error');
                return;
            }
            
            if (phone.length < 10) {
                showAlert('Numéro de téléphone invalide (minimum 10 chiffres)', 'error');
                return;
            }
            
            // Désactiver le bouton
            signupBtn.disabled = true;
            btnText.textContent = 'Création en cours...';
            btnIcon.className = 'spinner';
            
            try {
                const response = await fetch('admin_signup_process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        lastname: lastname,
                        firstname: firstname,
                        username: username,
                        email: email,
                        phone: phone,
                        gender: gender,
                        password: password
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('Compte créé avec succès ! Redirection...', 'success');
                    setTimeout(() => {
                        window.location.href = 'admin_login.php?registered=1';
                    }, 1500);
                } else {
                    showAlert(data.message || 'Erreur lors de la création du compte', 'error');
                    signupBtn.disabled = false;
                    btnText.textContent = 'Créer le compte';
                    btnIcon.className = 'fas fa-arrow-right';
                }
                
            } catch (error) {
                console.error('Erreur:', error);
                showAlert('Erreur de connexion au serveur', 'error');
                signupBtn.disabled = false;
                btnText.textContent = 'Créer le compte';
                btnIcon.className = 'fas fa-arrow-right';
            }
        });
        
        function showAlert(message, type) {
            const alertDiv = document.getElementById('alertMessage');
            alertDiv.textContent = message;
            alertDiv.className = `alert alert-${type} show`;
            
            if (type !== 'success') {
                setTimeout(() => {
                    alertDiv.classList.remove('show');
                }, 5000);
            }
        }
    </script>
</body>
</html>