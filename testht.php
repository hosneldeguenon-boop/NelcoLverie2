<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        html, body {
            height: 100%;
        }

        body{
            background-image: url('img1.png');
            background-color: #dceffb;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        section{
            background-color: rgba(0,0,0,0.25);
            border: 2px solid rgba(255,255,255,0.15);
            backdrop-filter: blur(8px);
            padding: 30px;
            width: 450px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        section h1{
            font-size: 30px;
            text-align: center;
            color: #fff;
            margin-bottom: 20px;
        }

        .input-box{
            width: 100%;
            margin-bottom: 20px;
            position: relative;
        }
        
        .input-box input{
            width: 100%;
            padding: 15px 50px 15px 20px;
            border-radius: 25px;
            outline: none;
            background-color: rgba(255,255,255,0.04);
            border: 2px solid rgba(255,255,255,0.15);
            color: #fff;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .input-box input:focus {
            border-color: rgba(96,165,250,0.8);
            box-shadow: 0 0 10px rgba(96,165,250,0.2);
        }

        .input-box input.error {
            border-color: #ff6b6b;
            box-shadow: 0 0 10px rgba(255,107,107,0.2);
        }

        .input-box input::placeholder{
            color: rgba(255,255,255,0.85);
        }

        .input-box i{
            position: absolute;
            transform: translateY(-50%);
            right: 20px;
            top: 50%;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .input-box i.fa-eye-slash,
        .input-box i.fa-eye {
            right: 45px;
        }

        .input-box i.fa-lock,
        .input-box i.fa-envelope {
            pointer-events: none;
        }

        .input-box i:hover {
            color: #60a5fa;
        }

        .remember-forgot{
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 14px;
            color:#fff;
            margin-bottom: 25px;
        }
        
        .remember-forgot label {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .remember-forgot label input[type="checkbox"]{
            margin-right: 8px;
        }
        
        .remember-forgot a{
            color: #fff;
            text-decoration: none;
            transition: 0.25s;
            font-weight: 600;
        }
        
        .remember-forgot a:hover{
            text-decoration: underline;
            opacity: 0.9;
        }

        .signup-link{
            text-align: center;
            margin-top: 18px;
            color: #fff;
            font-size: 14px;
        }
        
        .signup-link a{
            color: #fff;
            text-decoration: none;
            transition: 0.25s;
            font-weight: 600;
        }
        
        .signup-link a:hover{
            text-decoration: underline;
            opacity: 0.9;
        }

        .login-btn{
            width: 100%;
            padding: 12px;
            border-radius: 25px;
            outline: none;
            border: 0;
            font-weight: 700;
            cursor: pointer;
            background: linear-gradient(90deg,#3b82f6,#60a5fa);
            color: #fff;
            transition: 0.25s;
            box-shadow: 0 6px 18px rgba(96,165,250,0.25);
        }
        
        .login-btn:hover{
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(96,165,250,0.35);
        }

        .error-message {
            color: #ff6b6b;
            font-size: 12px;
            margin-top: 5px;
            margin-left: 15px;
            display: none;
        }

        .password-field {
            position: relative;
        }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin-responsive.css">
</head>
<body>
    <section>
        <h1>Connexion</h1>
        <form id="loginForm">
            <div class="input-box">
                <input type="email" id="email" placeholder="Email" required>
                <i class="fas fa-envelope"></i>
            </div>
            
            <div class="input-box password-field">
                <input type="password" id="password" placeholder="Mot de passe" required>
                <i class="fas fa-lock"></i>
                <i class="fas fa-eye-slash toggle-password" id="togglePassword"></i>
            </div>
            
            <div class="remember-forgot">
                <label>
                    <input type="checkbox" id="remember">
                    Se souvenir de moi
                </label>
                <a href="forgot_password.php">Mot de passe oublié ?</a>
            </div>
            
            <button type="submit" class="login-btn">Se connecter</button>
            
            <div class="signup-link">
                Pas encore de compte ? <a href="creer_compte.php">S'inscrire</a>
            </div>
        </form>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                this.classList.toggle('fa-eye-slash');
                this.classList.toggle('fa-eye');
            });
            
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                
                const formData = {
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value,
                    remember: document.getElementById('remember').checked
                };
                
                fetch('login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Connexion réussie ! Bienvenue ' + data.user.firstname + ' !');
                        window.location.href = 'tarifs.php';
                    } else {
                        alert('Erreur : ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de la connexion.');
                });
            });
        });
    </script>
</body>
</html>