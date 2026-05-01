<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Accueil' ?> - LyCol</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero.is-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card {
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar is-primary" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="/">
                <strong>LyCol</strong>
            </a>
            <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarMenu">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>
        <div id="navbarMenu" class="navbar-menu">
            <div class="navbar-start">
                <a class="navbar-item" href="/">Accueil</a>
                <a class="navbar-item" href="/about">À propos</a>
                <a class="navbar-item" href="/contact">Contact</a>
            </div>
            <div class="navbar-end">
                <div class="navbar-item">
                    <div class="buttons">
                        <a class="button is-light" href="/auth/login">
                            <span class="icon">
                                <i class="fas fa-sign-in-alt"></i>
                            </span>
                            <span>Connexion</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero is-primary is-medium">
        <div class="hero-body">
            <div class="container">
                <div class="columns is-vcentered">
                    <div class="column is-6">
                        <h1 class="title is-1 has-text-white">
                            LyCol
                        </h1>
                        <h2 class="subtitle is-3 has-text-white">
                            Système de Gestion Scolaire Intégré
                        </h2>
                        <p class="has-text-white is-size-5">
                            Une solution complète pour la gestion administrative et pédagogique de votre établissement scolaire.
                        </p>
                        <div class="buttons is-centered">
                            <a class="button is-white is-large" href="/auth/login">
                                <span class="icon">
                                    <i class="fas fa-sign-in-alt"></i>
                                </span>
                                <span>Accéder au système</span>
                            </a>
                        </div>
                    </div>
                    <div class="column is-6">
                        <div class="has-text-centered">
                            <i class="fas fa-graduation-cap feature-icon has-text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section">
        <div class="container">
            <div class="has-text-centered mb-6">
                <h2 class="title is-2">Modules Principaux</h2>
                <p class="subtitle is-4">Découvrez les fonctionnalités de notre système</p>
            </div>
            
            <div class="columns is-multiline">
                <div class="column is-4">
                    <div class="card">
                        <div class="card-content has-text-centered">
                            <i class="fas fa-users feature-icon has-text-primary"></i>
                            <h3 class="title is-4">Scolarité</h3>
                            <p>Gestion complète des élèves, absences et discipline</p>
                        </div>
                    </div>
                </div>
                
                <div class="column is-4">
                    <div class="card">
                        <div class="card-content has-text-centered">
                            <i class="fas fa-book feature-icon has-text-primary"></i>
                            <h3 class="title is-4">Études</h3>
                            <p>Gestion des classes, matières et emplois du temps</p>
                        </div>
                    </div>
                </div>
                
                <div class="column is-4">
                    <div class="card">
                        <div class="card-content has-text-centered">
                            <i class="fas fa-chart-line feature-icon has-text-primary"></i>
                            <h3 class="title is-4">Examens</h3>
                            <p>Gestion des examens, notes et bulletins</p>
                        </div>
                    </div>
                </div>
                
                <div class="column is-4">
                    <div class="card">
                        <div class="card-content has-text-centered">
                            <i class="fas fa-money-bill-wave feature-icon has-text-primary"></i>
                            <h3 class="title is-4">Économat</h3>
                            <p>Gestion des paiements et frais scolaires</p>
                        </div>
                    </div>
                </div>
                
                <div class="column is-4">
                    <div class="card">
                        <div class="card-content has-text-centered">
                            <i class="fas fa-chart-bar feature-icon has-text-primary"></i>
                            <h3 class="title is-4">Statistiques</h3>
                            <p>Rapports et analyses détaillées</p>
                        </div>
                    </div>
                </div>
                
                <div class="column is-4">
                    <div class="card">
                        <div class="card-content has-text-centered">
                            <i class="fas fa-cog feature-icon has-text-primary"></i>
                            <h3 class="title is-4">Configuration</h3>
                            <p>Paramétrage et administration du système</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="content has-text-centered">
            <p>
                <strong>LyCol</strong> - Système de Gestion Scolaire Intégré
            </p>
            <p>
                <a href="/privacy">Confidentialité</a> | 
                <a href="/terms">Conditions d'utilisation</a> | 
                <a href="/help">Aide</a>
            </p>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', () => {
            const burger = document.querySelector('.navbar-burger');
            const menu = document.querySelector('.navbar-menu');
            
            burger.addEventListener('click', () => {
                burger.classList.toggle('is-active');
                menu.classList.toggle('is-active');
            });
        });
    </script>
</body>
</html>