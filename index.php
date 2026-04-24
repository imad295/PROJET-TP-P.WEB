<?php
require_once 'includes/config.php';

// Vérifier s'il y a des administrateurs
$stmt = $pdo->query("SELECT COUNT(*) FROM administrateur");
$admin_exists = $stmt->fetchColumn() > 0;

// Rediriger si déjà connecté
if(isLoggedIn()) {
    switch($_SESSION['role']) {
        case 'admin': header('Location: pages/dashboard_admin.php'); exit();
        case 'enseignant': header('Location: pages/dashboard_enseignant.php'); exit();
        case 'etudiant': header('Location: pages/dashboard_etudiant.php'); exit();
    }
}

// Récupérer les statistiques
$stmt = $pdo->query("SELECT COUNT(*) FROM etudiant");
$total_etudiants = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM enseignant");
$total_enseignants = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM module");
$total_modules = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USTHB - Gestion Scolarité</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <style>
        /* ========== STYLES NAVBAR ANIMÉE ========== */
        
        /* Navbar de base */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(10px);
            padding: 18px 50px;
            z-index: 1000;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-bottom: 1px solid rgba(99, 102, 241, 0.2);
        }

        /* Navbar au scroll */
        .navbar.scrolled {
            padding: 10px 50px;
            background: rgba(15, 23, 42, 0.98);
            backdrop-filter: blur(15px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border-bottom-color: rgba(99, 102, 241, 0.5);
        }

        /* Animation du logo */
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: white;
            transition: all 0.3s ease;
            overflow: hidden;
            animation: pulseLogo 2s ease-in-out infinite;
        }

        @keyframes pulseLogo {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4);
            }
            50% {
                box-shadow: 0 0 0 8px rgba(59, 130, 246, 0);
            }
        }

        .logo:hover .logo-icon {
            transform: rotate(5deg) scale(1.05);
        }

        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 14px;
        }

        .logo-text h1 {
            font-size: 22px;
            color: #ffffff;
            margin: 0;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #ffffff, #a5b4fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo:hover .logo-text h1 {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-text p {
            font-size: 10px;
            color: #94a3b8;
        }

        /* Menu bouton mobile */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: #ffffff;
            font-size: 24px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .mobile-menu-btn:hover {
            transform: scale(1.1);
            color: #3b82f6;
        }

        /* Liens de navigation */
        .nav-links {
            display: flex;
            gap: 8px;
        }

        .nav-links a {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            padding: 8px 18px;
            border-radius: 40px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        /* Animation hover des liens */
        .nav-links a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: -1;
            border-radius: 40px;
        }

        .nav-links a:hover::before {
            left: 0;
        }

        .nav-links a:hover {
            color: white;
            transform: translateY(-2px);
        }

        /* Lien Contact spécial */
        .nav-links a[href="#contact"] {
            color: #f59e0b;
        }

        .nav-links a[href="#contact"]::before {
            background: linear-gradient(135deg, #f59e0b, #f97316);
        }

        .nav-links a[href="#contact"]:hover {
            color: white;
        }

        /* Animation apparition des liens */
        .nav-links a {
            animation: slideDown 0.5s ease-out forwards;
            opacity: 0;
        }

        .nav-links a:nth-child(1) { animation-delay: 0.1s; }
        .nav-links a:nth-child(2) { animation-delay: 0.2s; }
        .nav-links a:nth-child(3) { animation-delay: 0.3s; }
        .nav-links a:nth-child(4) { animation-delay: 0.4s; }
        .nav-links a:nth-child(5) { animation-delay: 0.5s; }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Boutons de navigation */
        .nav-buttons {
            display: flex;
            gap: 12px;
            animation: fadeInRight 0.8s ease-out;
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .btn-login-nav {
            padding: 8px 24px;
            background: transparent;
            border: 1.5px solid #3b82f6;
            color: #3b82f6;
            border-radius: 40px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-login-nav::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: -1;
            border-radius: 40px;
        }

        .btn-login-nav:hover::before {
            left: 0;
        }

        .btn-login-nav:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
            border-color: transparent;
        }

        .btn-login-nav:active {
            transform: translateY(0);
        }

        /* Animation du conteneur */
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* Styles pour la date et l'heure */
        .datetime-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 30px 0 20px;
            flex-wrap: wrap;
        }

        .date-box, .time-box {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(139, 92, 246, 0.2));
            backdrop-filter: blur(10px);
            padding: 12px 28px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
            font-weight: 500;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .date-box:hover, .time-box:hover {
            transform: translateY(-3px);
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.3), rgba(139, 92, 246, 0.3));
            border-color: rgba(99, 102, 241, 0.5);
        }

        .date-box i, .time-box i {
            color: #3b82f6;
            font-size: 18px;
        }

        .date-box span, .time-box span {
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* Footer styles */
        .footer-links ul li {
            margin-bottom: 12px;
        }

        .footer-links ul li a {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s;
        }

        .footer-links ul li a i {
            font-size: 10px;
        }

        .footer-links ul li a:hover {
            color: #3b82f6;
            transform: translateX(5px);
        }

        .footer-links ul li i {
            width: 25px;
            color: #3b82f6;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            margin-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-bottom a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 12px;
            transition: color 0.3s;
        }

        .footer-bottom a:hover {
            color: #3b82f6;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            justify-content: center;
        }

        .social-links a {
            width: 35px;
            height: 35px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background: #3b82f6;
            color: white;
            transform: translateY(-3px);
        }

        .footer-links ul li .fa-calendar-day,
        .footer-links ul li .fa-calendar-times {
            color: #f59e0b;
        }

        .footer-links ul li .fa-clock {
            color: #10b981;
        }

        .footer-links ul li strong {
            color: #ffffff;
            font-size: 13px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }
            .navbar.scrolled {
                padding: 10px 20px;
            }
            .mobile-menu-btn {
                display: block;
            }
            .nav-links {
                display: none;
                width: 100%;
                flex-direction: column;
                align-items: center;
                gap: 15px;
                padding: 20px 0;
                animation: slideDown 0.3s ease-out;
            }
            .nav-links.show {
                display: flex;
            }
            .nav-buttons {
                display: none;
            }
            .datetime-container {
                gap: 15px;
                margin: 20px 0 15px;
            }
            .date-box, .time-box {
                padding: 8px 18px;
                font-size: 12px;
            }
            .footer-container {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .footer-links ul li a {
                justify-content: center;
            }
            .footer-links ul li i {
                width: auto;
            }
            .footer-bottom div {
                flex-direction: column;
                text-align: center;
            }
            .footer-bottom a {
                margin: 10px 0 !important;
            }
            .social-links {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Background Effects -->
    <div class="bg-gradient"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <!-- Navigation Animée -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <div class="logo">
                <div class="logo-icon">
                    <img src="img/usthb.png" alt="USTHB Logo">
                </div>
                <div class="logo-text">
                    <h1>USTHB</h1>
                    <p>Gestion Scolarité</p>
                </div>
            </div>
            
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="nav-links" id="navLinks">
                <a href="#home"><i class="fas fa-home"></i> Accueil</a>
                <a href="#features"><i class="fas fa-star"></i> Fonctionnalités</a>
                <a href="#roles"><i class="fas fa-users"></i> Rôles</a>
                <a href="public_etudiants.php"><i class="fas fa-address-book"></i> Annuaire</a>
                <a href="#contact"><i class="fas fa-envelope"></i> Contact</a>
            </div>
            
            <div class="nav-buttons">
                <a href="pages/login.php" class="btn-login-nav">
                    <i class="fas fa-sign-in-alt"></i> Connexion
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-star"></i>
                <span>Plateforme officielle USTHB</span>
            </div>
            <h1>Gestion de <span>Scolarité</span><br></h1>
            <p class="hero-description">
                Une plateforme complète pour gérer les étudiants, les notes et les relevés académiques.
                Simplifiez la gestion de votre établissement avec notre solution intuitive.
            </p>
            <div class="hero-buttons">
                <a href="pages/login.php" class="btn-hero-primary">
                    Connexion <i class="fas fa-sign-in-alt"></i>
                </a>
                <a href="public_etudiants.php" class="btn-hero-secondary">
                    Annuaire <i class="fas fa-users"></i>
                </a>
                <a href="#features" class="btn-hero-secondary">
                    En savoir plus <i class="fas fa-play"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Stats Section avec Date et Heure -->
    <section class="stats-section">
        <div class="stats-container">
            <div class="stat-item">
                <div class="stat-number" id="statEtudiants">0</div>
                <div class="stat-label">Étudiants inscrits</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="statEnseignants">0</div>
                <div class="stat-label">Enseignants</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="statModules">0</div>
                <div class="stat-label">Modules</div>
            </div>
        </div>
        
        <div class="datetime-container">
            <div class="date-box">
                <i class="fas fa-calendar-alt"></i>
                <span id="currentDate">Chargement...</span>
            </div>
            <div class="time-box">
                <i class="fas fa-clock"></i>
                <span id="currentTime">--:--:--</span>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="section-header">
            <div class="section-tag">Fonctionnalités</div>
            <h2>Ce que notre <span>plateforme</span> vous offre</h2>
            <p>Des outils puissants pour une gestion académique optimale</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-users"></i></div>
                <h3>Gestion des étudiants</h3>
                <p>Ajoutez, modifiez et suivez tous vos étudiants en un seul endroit.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chalkboard-user"></i></div>
                <h3>Gestion des enseignants</h3>
                <p>Administrez les comptes enseignants et leurs modules assignés.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-book"></i></div>
                <h3>Gestion des modules</h3>
                <p>Organisez les matières et définissez leurs coefficients.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-pen-fancy"></i></div>
                <h3>Saisie des notes</h3>
                <p>Enregistrez les notes et calculez automatiquement les moyennes.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                <h3>Statistiques</h3>
                <p>Analysez les performances avec des rapports détaillés.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-file-alt"></i></div>
                <h3>Relevés de notes</h3>
                <p>Générez des relevés officiels pour chaque étudiant.</p>
            </div>
        </div>
    </section>

    <div style="height: 80px;"></div>

    <!-- Roles Section -->
    <section id="roles" class="roles-section">
        <div class="section-header">
            <div class="section-tag">Accès par rôle</div>
            <h2>Une interface <span>adaptée</span> à chaque utilisateur</h2>
            <p>Des fonctionnalités personnalisées selon votre rôle</p>
        </div>
        
        <div class="roles-grid">
            <div class="role-card">
                <div class="role-icon"><i class="fas fa-crown"></i></div>
                <h3>Administrateur</h3>
                <p>Contrôle total sur la plateforme</p>
                <ul class="role-features">
                    <li><i class="fas fa-check-circle"></i> Gestion complète des utilisateurs</li>
                    <li><i class="fas fa-check-circle"></i> Configuration des modules</li>
                    <li><i class="fas fa-check-circle"></i> Statistiques globales</li>
                    <li><i class="fas fa-check-circle"></i> Gestion des notes</li>
                </ul>
            </div>
            <div class="role-card">
                <div class="role-icon"><i class="fas fa-person-chalkboard"></i></div>
                <h3>Enseignant</h3>
                <p>Gestion pédagogique</p>
                <ul class="role-features">
                    <li><i class="fas fa-check-circle"></i> Saisie des notes</li>
                    <li><i class="fas fa-check-circle"></i> Suivi des étudiants</li>
                    <li><i class="fas fa-check-circle"></i> Relevés de classes</li>
                </ul>
            </div>
            <div class="role-card">
                <div class="role-icon"><i class="fas fa-user-graduate"></i></div>
                <h3>Étudiant</h3>
                <p>Suivi académique personnel</p>
                <ul class="role-features">
                    <li><i class="fas fa-check-circle"></i> Consultation des notes</li>
                    <li><i class="fas fa-check-circle"></i> Relevé personnel</li>
                    <li><i class="fas fa-check-circle"></i> Moyenne générale</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-card">
            <h2>Prêt à rejoindre la plateforme ?</h2>
            <p>Connectez-vous dès maintenant et profitez de tous nos services</p>
            <div class="cta-buttons">
                <a href="pages/login.php" class="btn-hero-primary">Se connecter</a>
                <a href="public_etudiants.php" class="btn-hero-secondary">Voir l'annuaire</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="footer">
        <div class="footer-container">
            <div class="footer-brand">
                <div class="logo-icon">
                    <img src="img/usthb.png" alt="USTHB Logo">
                </div>
                <h3>USTHB</h3>
                <p>Faculté d'Informatique<br>Université des Sciences et de la Technologie Houari Boumediene</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <div class="footer-links">
                <h4>Navigation</h4>
                <ul>
                    <li><a href="#home"><i class="fas fa-chevron-right"></i> Accueil</a></li>
                    <li><a href="#features"><i class="fas fa-chevron-right"></i> Fonctionnalités</a></li>
                    <li><a href="#roles"><i class="fas fa-chevron-right"></i> Rôles</a></li>
                    <li><a href="public_etudiants.php"><i class="fas fa-chevron-right"></i> Annuaire</a></li>
                    <li><a href="pages/login.php"><i class="fas fa-chevron-right"></i> Connexion</a></li>
                </ul>
            </div>
            
            <div class="footer-links">
                <h4>Coordonnées</h4>
                <ul>
                    <li><i class="fas fa-map-marker-alt"></i> BP 32, El Alia, Bab Ezzouar, 16111 Alger, Algérie</li>
                    <li><i class="fas fa-envelope"></i> <a href="mailto:faculte.info@usthb.dz">faculte.info@usthb.dz</a></li>
                    <li><i class="fas fa-phone"></i> +213 (0) 23 15 10 00</li>
                    <li><i class="fas fa-fax"></i> +213 (0) 23 15 10 01</li>
                    <li><i class="fas fa-globe"></i> <a href="http://www.usthb.dz" target="_blank">www.usthb.dz</a></li>
                </ul>
            </div>
            
            <div class="footer-links">
                <h4>Horaires d'ouverture</h4>
                <ul>
                    <li><i class="fas fa-calendar-day"></i> <strong>Dimanche - Jeudi</strong></li>
                    <li><i class="fas fa-clock"></i> 08h00 - 16h00</li>
                    <li style="margin-top: 10px;"><i class="fas fa-calendar-times"></i> <strong>Vendredi & Samedi</strong></li>
                    <li><i class="fas fa-clock"></i> <span style="color: #ef4444;">Fermé</span></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; max-width: 1200px; margin: 0 auto;">
                <p>&copy; <?php echo date('Y'); ?> USTHB - Faculté d'Informatique. Tous droits réservés.</p>
                <p>
                    <a href="#">Mentions légales</a>
                    <a href="#" style="margin-left: 20px;">Politique de confidentialité</a>
                    <a href="#" style="margin-left: 20px;">CGU</a>
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if(window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navLinks = document.getElementById('navLinks');

        if(mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                navLinks.classList.toggle('show');
            });
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                    if(navLinks.classList.contains('show')) {
                        navLinks.classList.remove('show');
                    }
                }
            });
        });

        // Animation des statistiques
        function animateNumber(element, target) {
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if(current >= target) {
                    element.textContent = target;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current);
                }
            }, 40);
        }

        animateNumber(document.getElementById('statEtudiants'), <?php echo $total_etudiants; ?>);
        animateNumber(document.getElementById('statEnseignants'), <?php echo $total_enseignants; ?>);
        animateNumber(document.getElementById('statModules'), <?php echo $total_modules; ?>);

        // Fonction date et heure
        function updateDateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateString = now.toLocaleDateString('fr-FR', options);
            const timeString = now.toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            
            const dateElement = document.getElementById('currentDate');
            const timeElement = document.getElementById('currentTime');
            
            if (dateElement) dateElement.textContent = dateString;
            if (timeElement) timeElement.textContent = timeString;
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);
    </script>
</body>
</html>