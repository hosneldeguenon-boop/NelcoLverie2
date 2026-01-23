<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarifs - Nelco Laverie</title>
    <style>
        /* Reset & Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header - EXACTEMENT comme index.html */
        .header {
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, #4e1676 0%, #7020A0 50%, #8a2ec7 100%);
        }

        .header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 20px;
            position: relative;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-img {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo-img img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .logo-text {
            display: flex;
            flex-direction: column;
            line-height: 1;
        }

        .logo-welco {
            font-size: 1.8rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 2px;
        }

        .logo-laverie {
            font-size: 2.2rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 3px;
            margin-top: -5px;
        }

        .nav ul {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            padding: 0.5rem 0;
            border-bottom: 2px solid transparent;
        }

        .nav a:hover,
        .nav a.active {
            color: #ffd700;
            border-bottom-color: #ffd700;
        }

        .mobile-menu-toggle {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
        }

        .mobile-menu-toggle span {
            width: 30px;
            height: 3px;
            background-color: #fff;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .mobile-menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }

        .mobile-menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .mobile-menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }

        /* Section Tarifs */
        .tarifs-section {
            padding: 4rem 0;
            background-color: white;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            color: #0066cc;
            margin-bottom: 3rem;
            font-weight: 700;
        }

        .table-anim {
            max-width: 900px;
            margin: 0 auto 3rem;
            overflow: hidden;
            animation: fadeIn 0.8s ease forwards;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0, 51, 102, 0.15);
            transition: transform 0.3s ease;
        }

        table:hover {
            transform: translateY(-6px);
        }

        th {
            background: #003366;
            color: white;
            padding: 1rem;
            font-size: 1.1rem;
        }

        td {
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        tbody tr:hover {
            background: #e6f0ff;
        }

        .note-box {
            max-width: 90%;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border: 3px solid #003366;
            border-radius: 12px;
            text-align: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Buttons */
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin: 3rem 0;
            padding-bottom: 3rem;
        }

        .btn {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-primary {
            background-color: #ffd700;
            color: #0066cc;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
        }

        .btn-primary:hover {
            background-color: #ffed4e;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.6);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: flex;
            }

            .header .container {
                flex-wrap: wrap;
            }

            .logo {
                gap: 0.5rem;
            }

            .logo-img {
                width: 60px;
                height: 60px;
            }

            .nav {
                order: 3;
                width: 100%;
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease;
            }

            .nav.active {
                max-height: 500px;
            }

            .nav ul {
                flex-direction: column;
                gap: 0;
                padding: 1rem 0;
            }

            .nav li {
                width: 100%;
                text-align: center;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .nav a {
                display: block;
                padding: 1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            th, td {
                font-size: 0.9rem;
                padding: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .logo-welco {
                font-size: 1.2rem;
                letter-spacing: 1px;
            }

            .logo-laverie {
                font-size: 1.5rem;
                letter-spacing: 2px;
            }

            .logo-img {
                width: 50px;
                height: 50px;
            }

            .section-title {
                font-size: 1.7rem;
            }
        }

        @media (max-width: 360px) {
            .logo-welco {
                font-size: 1rem;
            }

            .logo-laverie {
                font-size: 1.3rem;
            }

            .logo-img {
                width: 45px;
                height: 45px;
            }
        }

        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>

    <!-- Header - EXACTEMENT comme index.html -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <div class="logo-img">
                    <img src="en_tête.png" alt="Nelco Laverie Logo">
                </div>
                <div class="logo-text">
                    <span class="logo-welco">NELCO</span>
                    <span class="logo-laverie">LAVERIE</span>
                </div>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="index.html">Accueil</a></li>
                    <li><a href="voir_tarifs.php" class="active">Voir nos tarifs</a></li>
                    <li><a href="#offres">Offres</a></li>
                    <li><a href="testht.php">Passer commande</a></li>
                    <li><a href="comments.php">Commentaires</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </nav>
            <button class="mobile-menu-toggle" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <!-- CONTENU TARIFS -->
    <section class="tarifs-section">
        <h2 class="section-title">Tarifs Lavage</h2>

        <div class="table-anim">
            <table>
                <thead>
                    <tr>
                        <th>Poids</th>
                        <th>Froid</th>
                        <th>Tiede</th>
                        <th>Chaud</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Jusqu'à 5/6kg</td>
                        <td>2500F</td>
                        <td>3000F</td>
                        <td>3500F</td>
                    </tr>
                    <tr>
                        <td>Jusqu'à 7/8kg</td>
                        <td>3000F</td>
                        <td>3500F</td>
                        <td>4000F</td>
                    </tr>
                    <tr>
                        <td>10kg</td>
                        <td>5000F</td>
                        <td>6000F</td>
                        <td>7000F</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="note-box">
            <p><strong style="color:red;">NB:</strong> Le tarif des lavages incluant des articles volumineux (couettes, draps, serviettes, etc) varie selon la capacité des machines.</p>
        </div>

        <h2 class="section-title">Tarifs Séchage</h2>

        <div class="table-anim">
            <table>
                <thead>
                    <tr>
                        <th>Poids</th>
                        <th>Prix</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Jusqu'à 2kg</td><td>1000F</td></tr>
                    <tr><td>Jusqu'à 3kg</td><td>1500F</td></tr>
                    <tr><td>Jusqu'à 4kg</td><td>2000F</td></tr>
                    <tr><td>Jusqu'à 5kg</td><td>2500F</td></tr>
                    <tr><td>Jusqu'à 8kg</td><td>3000F</td></tr>
                </tbody>
            </table>
        </div>

        <h2 class="section-title">Autres Services</h2>

        <div class="table-anim">
            <table>
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Tarif</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Pliage</td><td>500F / panier</td></tr>
                    <tr><td>Repassage</td><td>À partir de 150F</td></tr>
                    <tr><td>Collecte / Livraison</td><td>Selon distance</td></tr>
                </tbody>
            </table>
        </div>

    </section>

    <div class="hero-buttons">
        <a href="commandes.php" class="btn btn-primary">🛒 Passer commande maintenant</a>
    </div>

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