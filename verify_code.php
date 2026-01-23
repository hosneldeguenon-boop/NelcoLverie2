<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du code - Nelco Laverie</title>
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
        .input-box input { width: 100%; padding: 15px 50px 15px 20px; border-radius: 10px; outline: none; background: #f5f5f5; border: 2px solid #e0e0e0; color: #333; font-size: 24px; text-align: center; letter-spacing: 10px; transition: all 0.3s ease; }
        .input-box input:focus { border-color: #667eea; background: white; }
        .input-box i { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); color: #667eea; font-size: 16px; }
        .btn { width: 100%; padding: 15px; border-radius: 10px; border: 0; font-weight: 600; cursor: pointer; background: linear-gradient(90deg,#667eea,#764ba2); color: #fff; transition: 0.3s; font-size: 16px; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102,126,234,0.4); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .message { padding: 15px; border-radius: 10px; margin-bottom: 20px; display: none; font-size: 14px; }
        .error-message { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .success-message { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .back-link { text-align: center; margin-top: 18px; }
        .back-link a { color: #667eea; text-decoration: none; font-size: 14px; font-weight: 600; }
    </style>
</head>
<body>
    <section>
        <h1>🔑 Code de vérification</h1>
        <p class="subtitle">Entrez le code à 6 chiffres reçu par email</p>
        
        <div class="email-display" id="emailDisplay"></div>
        
        <div class="success-message message" id="successMessage"></div>
        <div class="error-message message" id="errorMessage"></div>
        
        <form id="verifyCodeForm">
            <div class="input-box">
                <input type="text" id="code" placeholder="000000" maxlength="6" required autofocus inputmode="numeric">
                <i class="fas fa-key"></i>
            </div>
            
            <button type="submit" class="btn" id="verifyBtn">
                Vérifier le code
            </button>
            
            <div class="back-link">
                <a href="forgot_password.php">← Renvoyer un code</a>
            </div>
        </form>
    </section>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const email = urlParams.get('email');
        
        const emailDisplay = document.getElementById('emailDisplay');
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        
        if (!email) {
            errorMessage.textContent = '❌ Email manquant. Recommencez la procédure.';
            errorMessage.style.display = 'block';
            document.getElementById('verifyCodeForm').style.display = 'none';
        } else {
            emailDisplay.textContent = `📧 Email: ${email}`;
        }

        // Auto-format du code (seulement des chiffres)
        document.getElementById('code').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });

        document.getElementById('verifyCodeForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const code = document.getElementById('code').value;
            const verifyBtn = document.getElementById('verifyBtn');
            
            successMessage.style.display = 'none';
            errorMessage.style.display = 'none';
            
            if (code.length !== 6) {
                errorMessage.textContent = '❌ Le code doit contenir 6 chiffres.';
                errorMessage.style.display = 'block';
                return;
            }
            
            verifyBtn.disabled = true;
            verifyBtn.textContent = 'Vérification...';
            
            try {
                const response = await fetch('process_verify_code.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({email: email, code: code})
                });
                
                const data = await response.json();
                
                if (data.success) {
                    successMessage.textContent = '✅ Code vérifié avec succès !';
                    successMessage.style.display = 'block';
                    
                    setTimeout(() => {
                        window.location.href = 'new_password.php?email=' + encodeURIComponent(email) + '&code=' + code;
                    }, 1500);
                } else {
                    errorMessage.textContent = '❌ ' + data.message;
                    errorMessage.style.display = 'block';
                    verifyBtn.disabled = false;
                    verifyBtn.textContent = 'Vérifier le code';
                }
            } catch (error) {
                console.error('Erreur:', error);
                errorMessage.textContent = '❌ Erreur de connexion. Réessayez.';
                errorMessage.style.display = 'block';
                verifyBtn.disabled = false;
                verifyBtn.textContent = 'Vérifier le code';
            }
        });
    </script>
</body>
</html>