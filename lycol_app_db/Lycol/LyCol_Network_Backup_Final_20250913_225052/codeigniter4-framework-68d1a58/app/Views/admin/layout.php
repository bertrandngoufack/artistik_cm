<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title><?= esc($title ?? 'KISSAI SCHOOL - Administration') ?></title>
    
    <!-- Bulma CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/bulma/css/bulma.min.css') ?>">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="http://localhost:8080/assets/fontawesome/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <!-- Custom CSS -->
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .menu-label {
            color: #fff;
            font-weight: bold;
        }
        .sidebar .menu-list a {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 5px;
            margin: 2px 0;
        }
        .sidebar .menu-list a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .sidebar .menu-list a.is-active {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        .main-content {
            background-color: #f5f5f5;
            min-height: 100vh;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .notification {
            border-radius: 8px;
        }
        .table {
            border-radius: 8px;
            overflow: hidden;
        }
        .button {
            border-radius: 6px;
        }
        .input, .select, .textarea {
            border-radius: 6px;
        }
        /* Sécurité - Masquer les tokens CSRF */
        .csrf-token {
            position: absolute;
            left: -9999px;
            opacity: 0;
        }
    </style>
    
    <!-- Token CSRF global -->
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
</head>
<body>
    <div class="columns is-gapless">
        <!-- Sidebar -->
        <div class="column is-2">
            <aside class="sidebar">
                <div class="p-4">
                    <div class="has-text-centered mb-5">
                        <h1 class="title is-4 has-text-white">
                            <i class="fas fa-graduation-cap"></i>
                            LyCol
                        </h1>
                        <p class="has-text-white is-size-7">Gestion Scolaire</p>
                    </div>

                    <aside class="menu">
                        <p class="menu-label">
                            Tableau de bord
                        </p>
                        <ul class="menu-list">
                            <li><a href="<?= base_url('admin/dashboard') ?>" class="<?= current_url() == base_url('admin/dashboard') ? 'is-active' : '' ?>">
                                <span class="icon"><i class="fas fa-tachometer-alt"></i></span>
                                <span>Dashboard</span>
                            </a></li>
                        </ul>

                        <p class="menu-label">
                            Modules principaux
                        </p>
                        <ul class="menu-list">
                            <li><a href="<?= base_url('admin/economat') ?>" class="<?= strpos(current_url(), 'economat') !== false ? 'is-active' : '' ?>">
                                <span class="icon"><i class="fas fa-money-bill-wave"></i></span>
                                <span>Économat</span>
                            </a></li>
                            <li><a href="<?= base_url('admin/scolarite') ?>" class="<?= strpos(current_url(), 'scolarite') !== false ? 'is-active' : '' ?>">
                                <span class="icon"><i class="fas fa-users"></i></span>
                                <span>Scolarité</span>
                            </a></li>
                            <li><a href="<?= base_url('admin/etudes') ?>" class="<?= strpos(current_url(), 'etudes') !== false ? 'is-active' : '' ?>">
                                <span class="icon"><i class="fas fa-book"></i></span>
                                <span>Études</span>
                            </a></li>
                            <li><a href="<?= base_url('admin/examens') ?>" class="<?= strpos(current_url(), 'examens') !== false ? 'is-active' : '' ?>">
                                <span class="icon"><i class="fas fa-file-alt"></i></span>
                                <span>Examens</span>
                            </a></li>
                            <li><a href="<?= base_url('admin/enseignants') ?>" class="<?= strpos(current_url(), 'enseignants') !== false ? 'is-active' : '' ?>">
                                <span class="icon"><i class="fas fa-chalkboard-teacher"></i></span>
                                <span>Enseignants</span>
                            </a></li>
                        </ul>

                        <p class="menu-label">
                            Modules avancés
                        </p>
                        <ul class="menu-list">
                            <li><a href="<?= base_url('admin/statistiques') ?>" class="<?= strpos(current_url(), 'statistiques') !== false ? 'is-active' : '' ?>">
                                <span class="icon"><i class="fas fa-chart-bar"></i></span>
                                <span>Statistiques</span>
                            </a></li>
                            <li><a href="<?= base_url('admin/bibliotheque') ?>" class="<?= strpos(current_url(), 'bibliotheque') !== false ? 'is-active' : '' ?>">
                                <span class="icon"><i class="fas fa-book-open"></i></span>
                                <span>Bibliothèque</span>
                            </a></li>
                            <li><a href="<?= base_url('admin/messagerie') ?>" class="<?= strpos(current_url(), 'messagerie') !== false ? 'is-active' : '' ?>">
                                <span class="icon"><i class="fas fa-envelope"></i></span>
                                <span>Messagerie</span>
                            </a></li>
                        </ul>

                        <p class="menu-label">
                            Administration
                        </p>
                        <ul class="menu-list">
                            <li><a href="<?= base_url('admin/securite') ?>" class="<?= strpos(current_url(), 'securite') !== false ? 'is-active' : '' ?>">
                                <span class="icon"><i class="fas fa-shield-alt"></i></span>
                                <span>Sécurité</span>
                            </a></li>
                            <li><a href="<?= base_url('admin/configuration') ?>" class="<?= strpos(current_url(), 'configuration') !== false ? 'is-active' : '' ?>">
                                <span class="icon"><i class="fas fa-cog"></i></span>
                                <span>Configuration</span>
                            </a></li>
                        </ul>
                    </aside>
                </div>
            </aside>
        </div>

        <!-- Main Content -->
        <div class="column">
            <div class="main-content">
                <!-- Top Navigation -->
                <nav class="navbar is-white" role="navigation" aria-label="main navigation">
                    <div class="navbar-brand">
                        <div class="navbar-item">
                            <h2 class="title is-4"><?= esc($title ?? 'LyCol') ?></h2>
                        </div>
                    </div>

                    <div class="navbar-end">
                        <div class="navbar-item has-dropdown is-hoverable">
                            <a class="navbar-link">
                                <span class="icon"><i class="fas fa-user"></i></span>
                                <span><?= esc(session()->get('first_name') . ' ' . session()->get('last_name')) ?></span>
                            </a>

                            <div class="navbar-dropdown is-right">
                                <a class="navbar-item" href="<?= base_url('admin/profile') ?>">
                                    <span class="icon"><i class="fas fa-user-circle"></i></span>
                                    <span>Profil</span>
                                </a>
                                <a class="navbar-item" href="<?= base_url('auth/changePassword') ?>">
                                    <span class="icon"><i class="fas fa-key"></i></span>
                                    <span>Changer mot de passe</span>
                                </a>
                                <hr class="navbar-divider">
                                <a class="navbar-item" href="<?= base_url('auth/logout') ?>">
                                    <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                                    <span>Déconnexion</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Page Content -->
                <div class="p-4">
                    <!-- Flash Messages -->
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="notification is-success is-light">
                            <button class="delete"></button>
                            <span class="icon"><i class="fas fa-check-circle"></i></span>
                            <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="notification is-danger is-light">
                            <button class="delete"></button>
                            <span class="icon"><i class="fas fa-exclamation-circle"></i></span>
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('warning')): ?>
                        <div class="notification is-warning is-light">
                            <button class="delete"></button>
                            <span class="icon"><i class="fas fa-exclamation-triangle"></i></span>
                            <?= session()->getFlashdata('warning') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('info')): ?>
                        <div class="notification is-info is-light">
                            <button class="delete"></button>
                            <span class="icon"><i class="fas fa-info-circle"></i></span>
                            <?= session()->getFlashdata('info') ?>
                        </div>
                    <?php endif; ?>

                    <!-- Vérification de licence persistante -->
                    <?php 
                    $licenseWarning = session()->get('license_warning');
                    if ($licenseWarning): ?>
                        <div class="notification is-warning is-light" id="license-warning">
                            <button class="delete" onclick="dismissLicenseWarning()"></button>
                            <span class="icon"><i class="fas fa-exclamation-triangle"></i></span>
                            <strong>Avertissement de Licence :</strong> <?= esc($licenseWarning) ?>
                            <br><small>L'application fonctionne en mode avertissement. Veuillez contacter l'administrateur pour résoudre ce problème.</small>
                        </div>
                    <?php endif; ?>

                    <!-- Main Content Area -->
                    <?= $this->renderSection('content') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulma JS -->
    <script src="<?= base_url('assets/bulma/js/bulma.js') ?>"></script>
    
    <!-- Custom JS -->
    <script>
        // Configuration CSRF globale
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Fonction pour ajouter automatiquement le token CSRF aux formulaires
        function addCSRFTokenToForms() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                // Vérifier si le token CSRF n'existe pas déjà (CodeIgniter utilise csrf_test_name)
                if (!form.querySelector('input[name="csrf_test_name"]')) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = 'csrf_test_name';
                    csrfInput.value = CSRF_TOKEN;
                    csrfInput.className = 'csrf-token';
                    form.appendChild(csrfInput);
                }
            });
        }
        
        // Fonction pour ajouter le token CSRF aux requêtes AJAX
        function addCSRFTokenToAjax(xhr) {
            xhr.setRequestHeader('X-CSRF-TOKEN', CSRF_TOKEN);
        }
        
        // Auto-hide notifications
        document.addEventListener('DOMContentLoaded', () => {
            // Ajouter les tokens CSRF aux formulaires
            addCSRFTokenToForms();
            
            // Auto-hide notifications after 5 seconds
            setTimeout(() => {
                const notifications = document.querySelectorAll('.notification');
                notifications.forEach(notification => {
                    notification.style.display = 'none';
                });
            }, 5000);

            // Close notification on click
            document.querySelectorAll('.notification .delete').forEach(button => {
                button.addEventListener('click', () => {
                    button.parentNode.style.display = 'none';
                });
            });

            // Mobile menu toggle
            const burger = document.querySelector('.navbar-burger');
            const menu = document.querySelector('.navbar-menu');
            
            if (burger && menu) {
                burger.addEventListener('click', () => {
                    burger.classList.toggle('is-active');
                    menu.classList.toggle('is-active');
                });
            }
            
            // Intercepter les soumissions de formulaires pour validation CSRF
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (form.tagName === 'FORM') {
                    // Vérifier si le token CSRF est présent (CodeIgniter utilise csrf_test_name)
                    const csrfInput = form.querySelector('input[name="csrf_test_name"]');
                    if (!csrfInput || !csrfInput.value) {
                        e.preventDefault();
                        alert('Erreur de sécurité: Token CSRF manquant. Veuillez recharger la page.');
                        return false;
                    }
                }
            });
        });

        // Confirm delete actions
        function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
            return confirm(message);
        }

        // Show loading spinner
        function showLoading() {
            const loading = document.createElement('div');
            loading.className = 'loading-overlay';
            loading.innerHTML = `
                <div class="has-text-centered">
                    <span class="icon is-large">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                    </span>
                    <p class="mt-2">Chargement...</p>
                </div>
            `;
            document.body.appendChild(loading);
        }

        // Hide loading spinner
        function hideLoading() {
            const loading = document.querySelector('.loading-overlay');
            if (loading) {
                loading.remove();
            }
        }
        
        // Fonction sécurisée pour les requêtes AJAX
        function secureAjaxRequest(url, options = {}) {
            const defaultOptions = {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Content-Type': 'application/json'
                }
            };
            
            const finalOptions = { ...defaultOptions, ...options };
            
            return fetch(url, finalOptions)
                .then(response => {
                    if (response.status === 403) {
                        // Erreur CSRF
                        alert('Erreur de sécurité. Veuillez recharger la page.');
                        window.location.reload();
                        throw new Error('CSRF Error');
                    }
                    return response;
                });
        }

        // Fonction pour masquer l'avertissement de licence
        function dismissLicenseWarning() {
            const warning = document.getElementById('license-warning');
            if (warning) {
                warning.style.display = 'none';
                // Optionnel : Envoyer une requête AJAX pour marquer l'avertissement comme vu
                secureAjaxRequest('<?= base_url('admin/dismiss-license-warning') ?>', {
                    method: 'POST'
                }).catch(error => {
                    // Ignorer les erreurs pour cette fonctionnalité optionnelle
                });
            }
        }
    </script>

    <!-- Additional CSS for loading overlay -->
    <style>
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
    </style>

    <!-- Additional Scripts -->
    <?= $this->renderSection('scripts') ?>
</body>
</html>
