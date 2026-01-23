<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser le mot de passe</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        section {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        h1 {
            font-size: 28px;
            text-align: center;
            color: #333;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .email-display {
            background: #e3f2fd;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            color: #1976d2;
            font-size: 14px;
            font-weight: 500;
        }

        .input-box {
            width: 100%;
            margin-bottom: 20px;
            position: relative;
        }
        
        .input-box input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border-radius: 10px;
            outline: none;
            background: #f5f5f5;
            border: 2px solid #e0e0e0;
            color: #333;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .input-box input:focus {
            border-color: #667eea;
            background: white;
        }

        .input-box i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 16px;
        }

        .btn {
            width: 100%;
            padding: 15px;
            border-radius: 10px;
            border: 0;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: #fff;
            transition: 0.3s;
            font-size: 16px;
            margin-top: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
            font-size: 14px;
        }

        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .error-message {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .password-toggle {
            position: absolute;
            right: 45px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #667eea;
        }

        .password-req {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            margin-left: 15px;
        }
    </style>
</head>
<body>
    <section>
        <h1>🔐 Nouveau mot de passe</h1>
        <p class="subtitle">Entrez le code reçu par email et votre nouveau mot de passe</p>
        
        <div class="email-display" id="emailDisplay"></div>
        
        <div class="success-message message" id="successMessage"></div>
        <div class="error-message message" id="errorMessage"></div>
        
        <form id="resetPasswordForm">
            <div class="input-box">
                <input 
                    type="text" 
                    id="code" 
                    placeholder="Code à 6 chiffres" 
                    maxlength="6" 
                    pattern="[0-9]{6}" 
                    required
                    inputmode="numeric"
                >
                <i class="fas fa-key"></i>
            </div>

            <div class="input-box" style="position: relative;">
                <input 
                    type="password" 
                    id="password" 
                    placeholder="Nouveau mot de passe" 
                    minlength="8" 
                    required
                >
                <i class="fas fa-lock"></i>
                <i class="fas fa-eye-slash password-toggle" onclick="togglePassword('password', this)"></i>
                <div class="password-req">8 caractères minimum</div>
            </div>

            <div class="input-box" style="position: relative;">
                <input 
                    type="password" 
                    id="confirmPassword" 
                    placeholder="Confirmer le mot de passe" 
                    minlength="8" 
                    required
                >
                <i class="fas fa-lock"></i>
                <i class="fas fa-eye-slash password-toggle" onclick="togglePassword('confirmPassword', this)"></i>
            </div>
            
            <button type="submit" class="btn" id="resetBtn">
                Réinitialiser le mot de passe
            </button>
        </form>
    </section>

    <script>
      document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const code = document.getElementById('code').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const resetBtn = document.getElementById('resetBtn');
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');
    
    // Masquer les messages
    successMessage.style.display = 'none';
    errorMessage.style.display = 'none';
    
    // Validations front-end
    if (!/^\d{6}$/.test(code)) {
        errorMessage.textContent = 'Le code doit contenir exactement 6 chiffres';
        errorMessage.style.display = 'block';
        return;
    }
    
    if (password.length < 8) {
        errorMessage.textContent = 'Le mot de passe doit contenir au moins 8 caractères';
        errorMessage.style.display = 'block';
        return;
    }
    
    if (password !== confirmPassword) {
        errorMessage.textContent = 'Les mots de passe ne correspondent pas';
        errorMessage.style.display = 'block';
        return;
    }
    
    // Validation robustesse mot de passe
    if (!/[A-Z]/.test(password)) {
        errorMessage.textContent = 'Le mot de passe doit contenir au moins une majuscule';
        errorMessage.style.display = 'block';
        return;
    }
    
    if (!/[a-z]/.test(password)) {
        errorMessage.textContent = 'Le mot de passe doit contenir au moins une minuscule';
        errorMessage.style.display = 'block';
        return;
    }
    
    if (!/[0-9]/.test(password)) {
        errorMessage.textContent = 'Le mot de passe doit contenir au moins un chiffre';
        errorMessage.style.display = 'block';
        return;
    }
    
    // Désactiver le bouton
    resetBtn.disabled = true;
    resetBtn.textContent = 'Réinitialisation...';
    
    try {
        const response = await fetch('reset_passwords.php', {  // ✅ NOM CORRECT
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: email,
                code: code,
                new_password: password  // ✅ CLÉ CORRECTE
            })
        });
        
        // Vérifier le Content-Type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Réponse non-JSON:', text);
            throw new Error('Le serveur a renvoyé une réponse invalide (HTML au lieu de JSON)');
        }
        
        const data = await response.json();
        console.log('Réponse serveur:', data);
        
        if (data.success) {
            successMessage.textContent = data.message;
            successMessage.style.display = 'block';
            
            // Redirection après 2 secondes
            setTimeout(() => {
                window.location.href = 'testht.php';
            }, 2000);
        } else {
            errorMessage.textContent = data.message;
            errorMessage.style.display = 'block';
            resetBtn.disabled = false;
            resetBtn.textContent = 'Réinitialiser le mot de passe';
        }
    } catch (error) {
        console.error('Erreur complète:', error);
        errorMessage.textContent = error.message || 'Erreur de connexion au serveur';
        errorMessage.style.display = 'block';
        resetBtn.disabled = false;
        resetBtn.textContent = 'Réinitialiser le mot de passe';
    }
});
    </script>
</body>
</html>