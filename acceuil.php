<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta charset="utf-8">
    <title>Nelco Laverie - Lavage, Séchage, Repassage</title>
    <link rel="stylesheet" href="acceuil.css">
</head>
<body>
    <!-- Header -->
   <header class="header">
    <div class="container">
        <div class="logo">
            <img src="en_tête.png" alt="Nelco Laverie Logo" class="logo-img">
            <div class="logo-text">
                <span class="logo-welco">NELCO</span>
                <span class="logo-laverie">LAVERIE</span>
            </div>
        </div>
        <nav class="nav">
            <ul>
                <li><a href="#accueil" class="active">Accueil</a></li>
                <li><a href="voir_tarifs.php">Voir nos tarifs</a></li>
                <li><a href="#offres">Offres</a></li>
                <li><a href="testht.php">Passer commande</a></li>
                <li><a href="comments.php">Commentaires</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="https://wa.me/2290194194395?text=Bonjour%20je%20veux%20plus%20d'informations">Contact</a></li>
            </ul>
        </nav>
        <button class="mobile-menu-toggle" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>

    <!-- Hero Section -->
    <section class="hero" id="accueil">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Bienvenue chez NELCO LAVERIE</h1>
            <p class="hero-subtitle">Des services flexibles, rapides et soignés pensés pour s'adapter à vos besoins et à votre budget</p>
            <div class="hero-buttons">
                <a href="voir_tarifs.php" class="btn btn-primary">Consulter nos tarifs</a>
                <a href="testht.php" class="btn btn-secondary">Passer commande</a>
            </div>
        </div>
    </section>

    <!-- Horaires & Localisation -->
    <section class="info-section">
        <div class="container">
            <div class="info-cards">
                <div class="info-card">
                    <div class="info-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <h3>Horaires d'ouverture</h3>
                    <div class="info-details">
                        <p><strong>Tous les jours</strong><br>9h00 - 21h00</p>
                        <p class="quick-service">⚡ Votre linge prêt en 24h max</p>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <h3>Notre localisation</h3>
                    <div class="info-details">
                        <p><strong>Godomey PK14</strong><br>Von Station Ewell<br>Cotonou, Bénin</p>
                        <p class="phone">📱 +229 01 94 19 43 95</p>
                        <p class="social">📘 NELCOLAVERIE</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tarifs Section 
    <section class="tarifs-section" id="tarifs">
        <div class="container">
            <h2 class="section-title">Nos Tarifs</h2>
            
            <div class="tarifs-grid">
                <!-- Capacités machines 
                <div class="tarif-category">
                    <h3>Nos Machines</h3>
                    <p class="category-subtitle">3 capacités disponibles pour optimiser le traitement de votre linge</p>
                    <div class="capacity-list">
                        <div class="capacity-item">
                            <span class="capacity-size">5/6 kg</span>
                        </div>
                        <div class="capacity-item">
                            <span class="capacity-size">7/8 kg</span>
                        </div>
                        <div class="capacity-item highlight">
                            <span class="capacity-size">10 kg</span>
                            <span class="capacity-note">Idéale pour les articles très volumineux comme les couettes</span>
                        </div>
                    </div>
                </div>

                <!-- Tarifs Lavage 
                <div class="tarif-category">
                    <h3>Tarifs Lavage</h3>
                    <p class="category-subtitle">Choisissez la température qui convient le mieux à votre linge</p>
                    <div class="tarif-table">
                        <div class="tarif-header">
                            <span>Capacité</span>
                            <span>❄️ Froid</span>
                            <span>🌡️ Tiède</span>
                            <span>🔥 Chaud</span>
                        </div>
                        <div class="tarif-row">
                            <span>Jusqu'à 5/6 kg</span>
                            <span>2500F</span>
                            <span>3000F</span>
                            <span>3500F</span>
                        </div>
                        <div class="tarif-row">
                            <span>Jusqu'à 7/8 kg</span>
                            <span>3000F</span>
                            <span>3500F</span>
                            <span>4000F</span>
                        </div>
                        <div class="tarif-row">
                            <span>10 kg</span>
                            <span>5000F</span>
                            <span>6000F</span>
                            <span>7000F</span>
                        </div>
                        <p class="tarif-note">Articles très volumineux</p>
                    </div>
                </div>

                <!-- Tarifs Séchage 
                <div class="tarif-category">
                    <h3>Tarifs Séchage</h3>
                    <div class="tarif-list">
                        <div class="tarif-item">
                            <span>Jusqu'à 2 kg</span>
                            <span class="price">1000F</span>
                        </div>
                        <div class="tarif-item">
                            <span>Jusqu'à 3 kg</span>
                            <span class="price">1500F</span>
                        </div>
                        <div class="tarif-item">
                            <span>Jusqu'à 4 kg</span>
                            <span class="price">2000F</span>
                        </div>
                        <div class="tarif-item">
                            <span>Jusqu'à 6 kg</span>
                            <span class="price">2500F</span>
                        </div>
                        <div class="tarif-item">
                            <span>Jusqu'à 8 kg</span>
                            <span class="price">3000F</span>
                        </div>
                    </div>
                </div>

                <!-- Autres services 
                <div class="tarif-category">
                    <h3>Autres Services</h3>
                    <div class="tarif-list">
                        <div class="tarif-item">
                            <span>👔 Pliage</span>
                            <span class="price">500F <small>par panier</small></span>
                        </div>
                        <div class="tarif-item">
                            <span>👕 Repassage</span>
                            <span class="price">à partir de 150F</span>
                        </div>
                        <div class="tarif-item">
                            <span>🚚 Collecte/Livraison</span>
                            <span class="price">Sur demande</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tarif-note-box">
                <p><strong>Note :</strong> Le tarif des lavages incluant des articles volumineux (couettes, draps, serviettes, etc.) varie selon la capacité des machines nécessaires pour les laver.</p>
            </div>
        </div>
    </section>

    <!-- Services -->
    <section class="services-section" id="services">
        <div class="container">
            <h2 class="section-title">Nos Services</h2>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <circle cx="12" cy="12" r="6"></circle>
                            <circle cx="12" cy="12" r="2"></circle>
                        </svg>
                    </div>
                    <h3>Lavage & Séchage</h3>
                    <p>Machines performantes pour un linge propre et sec en un temps record</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3l18 18M3 3v18l6-6M21 21V3l-6 6"></path>
                        </svg>
                    </div>
                    <h3>Repassage</h3>
                    <p>Service de repassage professionnel pour un rendu impeccable</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                    </div>
                    <h3>Pressing</h3>
                    <p>Service pressing complet pour vos vêtements délicats</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                            <rect x="2" y="9" width="4" height="12"></rect>
                            <circle cx="4" cy="4" r="2"></circle>
                        </svg>
                    </div>
                    <h3>Collecte & Livraison</h3>
                    <p>Service de collecte et livraison à domicile pour votre confort</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pourquoi nous choisir -->
    <section class="why-us-section">
        <div class="container">
            <h2 class="section-title">Pourquoi nous choisir ?</h2>
            <div class="why-us-grid">
                <div class="why-card">
                    <div class="why-icon">⚡</div>
                    <h3>Rapidité</h3>
                    <p>Votre linge prêt en 24h maximum</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">✨</div>
                    <h3>Qualité</h3>
                    <p>Équipements modernes et service professionnel</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">💰</div>
                    <h3>Prix compétitifs</h3>
                    <p>Tarifs économiques et transparents</p>
                </div>
                <div class="why-card">
                    <div class="why-icon">🎯</div>
                    <h3>Flexibilité</h3>
                    <p>Services adaptés à vos besoins et votre budget</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactions -->
    <section class="interactions-section" id="offres">
        <div class="container">
            <div class="interactions-buttons">
                <a href="comments.php" class="interaction-btn">
                    <span class="btn-icon">💬</span>
                    <span class="btn-text" href="comments.php">Commenter et voir les commentaires</span>
                </a>
                <a href="#services" class="interaction-btn">
                    <span class="btn-icon">🔧</span>
                    <span class="btn-text">Découvrir nos autres services</span>
                </a>
                <a href="https://wa.me/2290194194395?text=Bonjour%20je%20veux%20plus%20d'informations" target="_blank" class="interaction-btn">
                    <span class="btn-icon">✉️</span>
                    <span class="btn-text">Nous contacter</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Nelco Laverie</h3>
                    <p>Votre partenaire de confiance pour l'entretien de votre linge</p>
                    <p class="footer-slogan">Des services flexibles, rapides et soignés</p>
                </div>
                <div class="footer-section">
                    <h4>Adresse</h4>
                    <p>Godomey PK14<br>Von Station Ewell<br>Cotonou, Bénin</p>
                </div>
                <div class="footer-section">
                    <h4>Horaires</h4>
                    <p>Tous les jours: 9h00 - 21h00</p>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p>📱 +229 01 94 19 43 95<br>📘 NELCOLAVERIE<br>✉️ contact@nelcolaverie.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Nelco Laverie — Tous droits réservés</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        const nav = document.querySelector('.nav');
        
        mobileMenuToggle.addEventListener('click', () => {
            nav.classList.toggle('active');
            mobileMenuToggle.classList.toggle('active');
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    nav.classList.remove('active');
                    mobileMenuToggle.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
