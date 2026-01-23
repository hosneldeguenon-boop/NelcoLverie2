<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe - Nelco Laverie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Poppins", sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        section { background: white; padding: 40px; width: 100%; max-width: 450px; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        h1 { font-size: 28px; text-align: center; color: #333; margin-bottom: 10px; }
        .subtitle { text-align: center; color: #666; font-size: 14px; margin-bottom: 20px; }
        .email-display { background: #e3f2fd; padding: 12px; border-radius: 8px; text-align: center; margin-bottom: 20px; color: #1976d2; font-size: 14px; font-weight: 500; }
        .input-box { width: 100%; margin-bottom: 20px; position: relative; }
        .input-box input { width: 100%; padding: 15px 80px 15px 20px; border-radius: 10px; outline: none; background: #f5f5f5; border: 2px solid #e0e0e0; color: #333; font-size: 14px; transition: all 0.3s ease; }
        .input-box input:focus { border-color: #667eea; background: white; }
        .input-box input.error { border-color: #ff6b6b; }
        .input-box i { position: absolute; top: 50%; transform: translateY(-50%); color: #667eea; font-size: 16px; }
        .input-box i.fa-lock { right: 50px; pointer-events: none; }
        .input-box i.fa-eye-slash, .input-box i.fa-eye { right: 20px; cursor: pointer; }
        .password-req { font-size: 12px; color: #666; margin-top: 5px; margin-left: 15px; }
        .error-inline { color: #ff6b6b; font-size: 12px; margin-top: 5px; margin-left: 15px; display: none; }
        .btn { width: 100%; padding: 15px; border-radius: 10px; border: 0; font-weight: 600; cursor: pointer; background: linear-gradient(90deg, #667eea, #764ba2); color: #fff; transition: 0.3s; font-size: 16px; margin-top: 10px; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102,126,234,0.4); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .message { padding: 15px; border-radius: 10px; margin-bottom: 20px; display: none; font-size: 14px; text-align: center; }
        .success-message { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error-message { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    </style>
</head>
<body>
    <section>
        <h1>🔐 Nouveau mot de passe</h1>
        <p class="subtitle">Choisissez un nouveau mot de passe sécurisé</p>
        
        <div class="email-display" id="emailDisplay"></div>
        
        <div class="success-message message" id="successMessage"></div>
        <div class="error-message message" id="errorMessage"></div>
        
        <form id="resetPasswordForm">
            <div class="input-box">
                <input type="password" id="newPassword" placeholder="Nouveau mot de passe" minlength="8" required>
                <i class="fas fa-lock"></i>
                <i class="fas fa-eye-slash" id="togglePassword" onclick="togglePasswordVisibility('newPassword', 'togglePassword')"></i>
                <div class="password-req">8 caractères minimum</div>
            </div>

            <div class="input-box">
                <input type="password" id="confirmPassword" placeholder="Confirmer le mot de passe" minlength="8" required>
                <i class="fas fa-lock"></i>
                <i class="fas fa-eye-slash" id="toggleConfirmPassword" onclick="togglePasswordVisibility('confirmPassword', 'toggleConfirmPassword')"></i>
                <div class="error-inline" id="passwordError">Les mots de passe ne correspondent pas</div>
            </div>
            
            <button type="submit" class="btn" id="resetBtn">
                Changer le mot de passe
            </button>
        </form>
    </section>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const email = urlParams.get('email');
        const code = urlParams.get('code');
        
        const emailDisplay = document.getElementById('emailDisplay');
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        const passwordError = document.getElementById('passwordError');
        const newPasswordInput = document.getElementById('newPassword');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        
        if (!email || !code) {
            errorMessage.textContent = '❌ Lien invalide. Recommencez la procédure.';
            errorMessage.style.display = 'block';
            document.getElementById('resetPasswordForm').style.display = 'none';
        } else {
            emailDisplay.textContent = `📧 Email: ${email}`;
        }

        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }

        function validatePasswords() {
            const password = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword && password !== confirmPassword) {
                confirmPasswordInput.classList.add('error');
                passwordError.style.display = 'block';
                return false;
            } else {
                confirmPasswordInput.classList.remove('error');
                passwordError.style.display = 'none';
                return true;
            }
        }

        confirmPasswordInput.addEventListener('input', validatePasswords);
        newPasswordInput.addEventListener('input', validatePasswords);

        document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const password = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const resetBtn = document.getElementById('resetBtn');
            
            successMessage.style.display = 'none';
            errorMessage.style.display = 'none';
            
            if (password.length < 8) {
                errorMessage.textContent = '❌ Le mot de passe doit contenir au moins 8 caractères.';
                errorMessage.style.display = 'block';
                return;
            }
            
            if (password !== confirmPassword) {
                errorMessage.textContent = '❌ Les mots de passe ne correspondent pas.';
                errorMessage.style.display = 'block';
                confirmPasswordInput.focus();
                return;
            }
            
            resetBtn.disabled = true;
            resetBtn.textContent = 'Changement en cours...';
            
            try {
                const response = await fetch('reset_passwords.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        email: email,
                        code: code,
                        password: password
                    })
                });

                const data = await response.json();

                if (data.success) {
                    successMessage.textContent = '✅ ' + data.message;
                    successMessage.style.display = 'block';
                    document.getElementById('resetPasswordForm').style.display = 'none';
                    
                    setTimeout(() => {
                        window.location.href = 'testht.php';
                    }, 3000);
                } else {
                    errorMessage.textContent = '❌ ' + data.message;
                    errorMessage.style.display = 'block';
                    resetBtn.disabled = false;
                    resetBtn.textContent = 'Changer le mot de passe';
                }
            } catch (error) {
                console.error('Erreur:', error);
                errorMessage.textContent = '❌ Erreur de connexion au serveur.';
                errorMessage.style.display = 'block';
                resetBtn.disabled = false;
                resetBtn.textContent = 'Changer le mot de passe';
            }
        });
    </script>
</body>
</html>