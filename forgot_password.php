<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Nelco Laverie</title>
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
        
        section {
            background: white;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
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
        
        .reset-btn {
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
        }
        
        .reset-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }
        
        .reset-btn:disabled {
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
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <section>
        <h1>🔒 Mot de passe oublié</h1>
        <p class="subtitle">Entrez votre email pour recevoir un code de réinitialisation</p>
        
        <div class="success-message message" id="successMessage"></div>
        <div class="error-message message" id="errorMessage"></div>
        
        <form id="forgotPasswordForm">
            <div class="input-box">
                <input type="email" id="email" placeholder="Votre adresse email" required autocomplete="email">
                <i class="fas fa-envelope"></i>
            </div>
            
            <button type="submit" class="reset-btn" id="resetBtn">
                Envoyer le code
            </button>
            
            <div class="back-link">
                <a href="testht.php"><i class="fas fa-arrow-left"></i> Retour à la connexion</a>
            </div>
        </form>
    </section>

    <script>
        document.getElementById('forgotPasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value.trim();
            const resetBtn = document.getElementById('resetBtn');
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');
            
            successMessage.style.display = 'none';
            errorMessage.style.display = 'none';
            
            if (!email) {
                errorMessage.textContent = 'Veuillez entrer votre email';
                errorMessage.style.display = 'block';
                return;
            }
            
            resetBtn.disabled = true;
            resetBtn.textContent = 'Envoi en cours...';
            
            try {
                const response = await fetch('forgot_passwords.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: email })
                });

                const data = await response.json();

                if (data.success) {
                    successMessage.textContent = '✅ ' + data.message;
                    successMessage.style.display = 'block';
                    
                    setTimeout(() => {
                        window.location.href = 'verify_code.php?email=' + encodeURIComponent(email);
                    }, 2000);
                } else {
                    errorMessage.textContent = '❌ ' + data.message;
                    errorMessage.style.display = 'block';
                    resetBtn.disabled = false;
                    resetBtn.textContent = 'Envoyer le code';
                }
            } catch (error) {
                console.error('Erreur:', error);
                errorMessage.textContent = '❌ Erreur de connexion au serveur';
                errorMessage.style.display = 'block';
                resetBtn.disabled = false;
                resetBtn.textContent = 'Envoyer le code';
            }
        });
    </script>
</body>
</html>