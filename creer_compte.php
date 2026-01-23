<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Nelco Laverie</title>
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
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 40px 20px;
            overflow-x: hidden;
        }

        section{
            background-color: rgba(0,0,0,0.25);
            border: 2px solid rgba(255,255,255,0.15);
            backdrop-filter: blur(8px);
            padding: 30px;
            width: 100%;
            max-width: 450px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            margin-top: 170px;
        }

        section h1{
            font-size: 32px;
            text-align: center;
            color: #fff;
            margin-bottom: 25px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .input-box{
            width: 100%;
            margin-bottom: 20px;
            position: relative;
        }
        
        .input-box input,
        .input-box select{
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

        .input-box select option {
            background-color: #1a1a2e;
            color: #fff;
        }

        .input-box input:focus,
        .input-box select:focus {
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
        .input-box i.fa-user,
        .input-box i.fa-envelope,
        .input-box i.fa-phone,
        .input-box i.fa-map-marker-alt,
        .input-box i.fa-venus-mars,
        .input-box i.fa-whatsapp {
            pointer-events: none;
        }

        .input-box i:hover {
            color: #60a5fa;
        }

        .name-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .name-row .input-box {
            margin-bottom: 0;
        }

        .terms{
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            font-size: 14px;
            color:#fff;
            margin-bottom: 25px;
        }
        .terms label {
            display: flex;
            align-items: flex-start;
            cursor: pointer;
        }
        .terms label input[type="checkbox"]{
            margin-right: 8px;
            margin-top: 3px;
        }
        .terms a{
            color: #fff;
            text-decoration: none;
            transition: 0.25s;
            font-weight: 600;
            margin-left: 10px;
        }
        .terms a:hover{
            text-decoration: underline;
            opacity: 0.9;
        }

        .login-link{
            text-align: center;
            margin-top: 18px;
            color: #fff;
            font-size: 14px;
        }
        .login-link a{
            color: #fff;
            text-decoration: none;
            transition: 0.25s;
            font-weight: 600;
        }
        .login-link a:hover{
            text-decoration: underline;
            opacity: 0.9;
        }

        .signup-btn{
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
        .signup-btn:hover{
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(96,165,250,0.35);
        }

        .signup-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .password-requirements {
            color: rgba(255,255,255,0.8);
            font-size: 12px;
            margin-top: 5px;
            margin-left: 15px;
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

        .phone-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .phone-row .input-box {
            margin-bottom: 0;
        }

        .location-btn {
            position: absolute;
            right: 50px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(96,165,250,0.2);
            border: none;
            color: #fff;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .location-btn:hover {
            background: rgba(96,165,250,0.4);
        }

        .location-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .address-hint {
            color: rgba(255,255,255,0.7);
            font-size: 11px;
            margin-top: 5px;
            margin-left: 15px;
        }

        .success-message {
            color: #4ade80;
            font-size: 12px;
            margin-top: 5px;
            margin-left: 15px;
            display: none;
        }

        /* Modal pour permissions mobile */
        .permission-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .permission-modal.active {
            display: flex;
        }

        .permission-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 90%;
            width: 400px;
            text-align: center;
        }

        .permission-content h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .permission-content p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .permission-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .permission-buttons button {
            padding: 10px 25px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-allow {
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
            color: white;
        }

        .btn-cancel {
            background: #e5e7eb;
            color: #333;
        }

        .coord-display {
            color: rgba(255,255,255,0.6);
            font-size: 10px;
            margin-top: 3px;
            margin-left: 15px;
            font-family: monospace;
        }

        @media (max-width: 768px) {
            body {
                padding: 30px 15px;
                align-items: flex-start;
            }
            
            section h1 {
                font-size: 28px;
            }
            
            section {
                padding: 25px 20px;
                margin: 15px;
            }
            
            .name-row, .phone-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .name-row .input-box,
            .phone-row .input-box {
                margin-bottom: 20px;
            }
            
            .input-box input,
            .input-box select {
                font-size: 16px;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 20px 10px;
            }
            
            section h1 {
                font-size: 24px;
                letter-spacing: 1px;
            }
            
            section {
                padding: 20px 15px;
            }
            
            .input-box input,
            .input-box select {
                padding: 13px 45px 13px 18px;
                font-size: 16px;
            }
            
            .location-btn {
                right: 45px;
                font-size: 10px;
                padding: 4px 8px;
            }
        }
    </style>
</head>
<body>
    <section>
        <h1>Inscription</h1>
        <form id="signupForm">
            <div class="name-row">
                <div class="input-box">
                    <input type="text" id="lastname" placeholder="Nom" required minlength="2">
                    <i class="fas fa-user"></i>
                </div>
                
                <div class="input-box">
                    <input type="text" id="firstname" placeholder="Prénom" required minlength="2">
                    <i class="fas fa-user"></i>
                </div>
            </div>
            
            <div class="input-box">
                <input type="email" id="email" placeholder="Email" required>
                <i class="fas fa-envelope"></i>
            </div>

            <div class="phone-row">
                <div class="input-box">
                    <input type="tel" id="phone" placeholder="Téléphone" required pattern="[0-9+\s\-()]{8,}">
                    <i class="fas fa-phone"></i>
                </div>
                
                <div class="input-box">
                    <input type="tel" id="whatsapp" placeholder="WhatsApp" required pattern="[0-9+\s\-()]{8,}">
                    <i class="fab fa-whatsapp"></i>
                </div>
            </div>

            <div class="input-box" style="position: relative;">
                <input type="text" id="address" placeholder="Adresse (optionnel)">
                <button type="button" class="location-btn" id="locationBtn">
                    <i class="fas fa-location-arrow"></i> Localiser
                </button>
                <i class="fas fa-map-marker-alt"></i>
                <div class="address-hint">Cliquez sur "Localiser" pour utiliser votre position</div>
                <div class="success-message" id="locationSuccess">✓ Position récupérée</div>
                <div class="coord-display" id="coordDisplay"></div>
            </div>

            <div class="input-box">
                <select id="gender" required>
                    <option value="" disabled selected>Genre</option>
                    <option value="homme">Homme</option>
                    <option value="femme">Femme</option>
                </select>
                <i class="fas fa-venus-mars"></i>
            </div>
            
            <div class="input-box password-field">
                <input type="password" id="password" placeholder="Mot de passe" required minlength="8">
                <i class="fas fa-lock"></i>
                <i class="fas fa-eye-slash toggle-password" id="togglePassword"></i>
                <div class="password-requirements">8 caractères minimum</div>
            </div>
            
            <div class="input-box password-field">
                <input type="password" id="confirmPassword" placeholder="Confirmer le mot de passe" required minlength="8">
                <i class="fas fa-lock"></i>
                <i class="fas fa-eye-slash toggle-password" id="toggleConfirmPassword"></i>
                <div class="error-message" id="passwordError">Les mots de passe ne correspondent pas</div>
            </div>
            
            <div class="terms">
                <label>
                    <input type="checkbox" id="terms" required>
                    J'accepte les <a href="#" onclick="event.preventDefault();">conditions d'utilisation</a>
                </label>
            </div>
            
            <button type="submit" class="signup-btn">S'inscrire</button>
            
            <div class="login-link">
                Déjà un compte ? <a href="testht.php">Se connecter</a>
            </div>
        </form>
    </section>

    <!-- Modal de permission -->
    <div class="permission-modal" id="permissionModal">
        <div class="permission-content">
            <h3>📍 Autorisation de localisation</h3>
            <p>Pour remplir automatiquement votre adresse, nous avons besoin d'accéder à votre position.</p>
            <p><strong>Après avoir cliqué sur "Autoriser", acceptez la demande de votre navigateur.</strong></p>
            <div class="permission-buttons">
                <button type="button" class="btn-allow" id="btnAllow">Autoriser</button>
                <button type="button" class="btn-cancel" id="btnCancel">Annuler</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('signupForm');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const passwordError = document.getElementById('passwordError');
            const togglePassword = document.getElementById('togglePassword');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const locationBtn = document.getElementById('locationBtn');
            const addressInput = document.getElementById('address');
            const locationSuccess = document.getElementById('locationSuccess');
            const coordDisplay = document.getElementById('coordDisplay');
            const submitBtn = form.querySelector('.signup-btn');
            const permissionModal = document.getElementById('permissionModal');
            const btnAllow = document.getElementById('btnAllow');
            const btnCancel = document.getElementById('btnCancel');
            
            let locationRequestInProgress = false;
            
            // Gestion du toggle password
            function setupPasswordToggle(toggleElement, passwordField) {
                toggleElement.addEventListener('click', function(e) {
                    e.preventDefault();
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    
                    this.classList.toggle('fa-eye-slash');
                    this.classList.toggle('fa-eye');
                });
            }
            
            setupPasswordToggle(togglePassword, passwordInput);
            setupPasswordToggle(toggleConfirmPassword, confirmPasswordInput);
            
            // Validation des mots de passe
            function validatePasswords() {
                const password = passwordInput.value;
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
            passwordInput.addEventListener('input', validatePasswords);
            
            // Fonction pour récupérer l'adresse depuis les coordonnées
            async function getAddressFromCoords(lat, lon) {
                try {
                    console.log('🌍 Récupération adresse pour:', lat, lon);
                    
                    // Afficher les coordonnées
                    coordDisplay.textContent = `Lat: ${lat.toFixed(6)}, Lon: ${lon.toFixed(6)}`;
                    coordDisplay.style.display = 'block';
                    
                    // Essayer avec Nominatim (OpenStreetMap)
                    const response = await fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`,
                        {
                            headers: {
                                'User-Agent': 'NelcoLaverie/1.0',
                                'Accept-Language': 'fr'
                            }
                        }
                    );
                    
                    if (!response.ok) {
                        throw new Error('Erreur API Nominatim');
                    }
                    
                    const data = await response.json();
                    console.log('📍 Données Nominatim:', data);
                    
                    if (data && data.address) {
                        const addr = data.address;
                        let formattedAddress = '';
                        
                        // Construction de l'adresse dans l'ordre français
                        const parts = [];
                        
                        if (addr.house_number) parts.push(addr.house_number);
                        if (addr.road) parts.push(addr.road);
                        if (addr.neighbourhood) parts.push(addr.neighbourhood);
                        if (addr.suburb) parts.push(addr.suburb);
                        if (addr.city) parts.push(addr.city);
                        else if (addr.town) parts.push(addr.town);
                        else if (addr.village) parts.push(addr.village);
                        else if (addr.municipality) parts.push(addr.municipality);
                        if (addr.state) parts.push(addr.state);
                        if (addr.postcode) parts.push(addr.postcode);
                        if (addr.country) parts.push(addr.country);
                        
                        formattedAddress = parts.join(', ');
                        
                        if (!formattedAddress && data.display_name) {
                            formattedAddress = data.display_name;
                        }
                        
                        return formattedAddress || 'Adresse non disponible';
                    }
                    
                    return data.display_name || 'Adresse non disponible';
                    
                } catch (error) {
                    console.error('❌ Erreur récupération adresse:', error);
                    
                    // Fallback : afficher juste les coordonnées
                    return `Position: ${lat.toFixed(6)}, ${lon.toFixed(6)}`;
                }
            }
            
            // Fonction pour gérer la géolocalisation
            function requestGeolocation() {
                if (locationRequestInProgress) {
                    console.log('⏳ Demande déjà en cours...');
                    return;
                }
                
                locationRequestInProgress = true;
                locationBtn.disabled = true;
                locationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Localisation...';
                locationSuccess.style.display = 'none';
                coordDisplay.style.display = 'none';
                
                const options = {
                    enableHighAccuracy: true,
                    timeout: 20000,
                    maximumAge: 0
                };
                
                console.log('📍 Demande de géolocalisation...');
                
                navigator.geolocation.getCurrentPosition(
                    async function(position) {
                        console.log('✅ Position obtenue:', position);
                        
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        const accuracy = position.coords.accuracy;
                        
                        console.log(`📍 Lat: ${lat}, Lon: ${lon}, Précision: ${accuracy}m`);
                        
                        // Récupérer l'adresse
                        const address = await getAddressFromCoords(lat, lon);
                        
                        addressInput.value = address;
                        locationBtn.innerHTML = '<i class="fas fa-check"></i> Localisé';
                        locationSuccess.style.display = 'block';
                        
                        setTimeout(() => {
                            locationBtn.disabled = false;
                            locationBtn.innerHTML = '<i class="fas fa-location-arrow"></i> Localiser';
                            locationRequestInProgress = false;
                        }, 3000);
                    },
                    function(error) {
                        console.error('❌ Erreur géolocalisation:', error);
                        
                        let errorMsg = '';
                        
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMsg = '🚫 Permission refusée\n\nPour activer la localisation :\n\n' +
                                          '1. Ouvrez les paramètres de votre navigateur\n' +
                                          '2. Cherchez "Autorisations" ou "Localisation"\n' +
                                          '3. Autorisez ce site à accéder à votre position\n' +
                                          '4. Réessayez';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMsg = '📍 Position indisponible\n\n' +
                                          'Vérifiez que :\n' +
                                          '• Le GPS est activé sur votre appareil\n' +
                                          '• Vous avez une connexion Internet\n' +
                                          '• Vous êtes à l\'extérieur si possible';
                                break;
                            case error.TIMEOUT:
                                errorMsg = '⏱️ Délai dépassé\n\n' +
                                          'La localisation prend trop de temps.\n' +
                                          'Réessayez dans un endroit avec un meilleur signal.';
                                break;
                            default:
                                errorMsg = '❌ Erreur inconnue\n\nVeuillez réessayer.';
                        }
                        
                        alert(errorMsg);
                        
                        locationBtn.disabled = false;
                        locationBtn.innerHTML = '<i class="fas fa-location-arrow"></i> Localiser';
                        locationRequestInProgress = false;
                        coordDisplay.style.display = 'none';
                    },
                    options
                );
            }
            
            // Gestion du clic sur le bouton de localisation
            locationBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (!navigator.geolocation) {
                    alert('❌ La géolocalisation n\'est pas supportée par votre navigateur.\n\nVeuillez saisir votre adresse manuellement.');
                    return;
                }
                
                // Sur mobile, afficher d'abord le modal explicatif
                if (/Mobi|Android|iPhone/i.test(navigator.userAgent)) {
                    permissionModal.classList.add('active');
                } else {
                    // Sur desktop, lancer directement
                    requestGeolocation();
                }
            });
            
            // Gestion du modal
            btnAllow.addEventListener('click', function() {
                permissionModal.classList.remove('active');
                requestGeolocation();
            });
            
            btnCancel.addEventListener('click', function() {
                permissionModal.classList.remove('active');
            });
            
            // Validation email en temps réel
            const emailInput = document.getElementById('email');
            emailInput.addEventListener('blur', function() {
                const email = this.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (email && !emailRegex.test(email)) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
            });
            
            // Validation téléphone en temps réel
            function validatePhone(input) {
                input.addEventListener('blur', function() {
                    const phone = this.value.trim();
                    const phoneRegex = /^[0-9+\s\-()]{8,}$/;
                    
                    if (phone && !phoneRegex.test(phone)) {
                        this.classList.add('error');
                    } else {
                        this.classList.remove('error');
                    }
                });
            }
            
            validatePhone(document.getElementById('phone'));
            validatePhone(document.getElementById('whatsapp'));
            
            // Soumission du formulaire
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                
                if (!validatePasswords()) {
                    confirmPasswordInput.focus();
                    return false;
                }
                
                const lastname = document.getElementById('lastname').value.trim();
                const firstname = document.getElementById('firstname').value.trim();
                const email = document.getElementById('email').value.trim();
                const phone = document.getElementById('phone').value.trim();
                const whatsapp = document.getElementById('whatsapp').value.trim();
                const gender = document.getElementById('gender').value;
                const password = document.getElementById('password').value;
                const terms = document.getElementById('terms').checked;
                
                if (!lastname || !firstname || !email || !phone || !whatsapp || !gender || !password) {
                    alert('❌ Veuillez remplir tous les champs obligatoires');
                    return false;
                }
                
                if (!terms) {
                    alert('❌ Veuillez accepter les conditions d\'utilisation');
                    return false;
                }
                
                if (password.length < 8) {
                    alert('❌ Le mot de passe doit contenir au moins 8 caractères');
                    passwordInput.focus();
                    return false;
                }
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Inscription en cours...';
                
                const formData = {
                    lastname: lastname,
                    firstname: firstname,
                    email: email,
                    phone: phone,
                    whatsapp: whatsapp,
                    address: document.getElementById('address').value.trim() || null,
                    gender: gender,
                    password: password
                };
                
                fetch('register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('✅ Inscription réussie !\n\nUn email de confirmation a été envoyé à ' + formData.email + '\n\nVeuillez vérifier votre boîte de réception et cliquer sur le lien de confirmation pour activer votre compte.');
                        window.location.href = 'testht.php';
                    } else {
                        alert('❌ Erreur : ' + (data.message || 'Une erreur est survenue'));
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'S\'inscrire';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('❌ Une erreur est survenue lors de l\'inscription.\n\nVeuillez réessayer.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'S\'inscrire';
                });
            });
        });
    </script>
</body>
</html>