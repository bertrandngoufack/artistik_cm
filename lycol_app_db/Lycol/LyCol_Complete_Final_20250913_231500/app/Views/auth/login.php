<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title><?= esc($title ?? 'Connexion - KISSAI SCHOOL') ?></title>
    
    <!-- Bulma CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/bulma/css/bulma.min.css') ?>">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url("assets/fontawesome/css/all.min.css") ?>">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-body {
            padding: 2rem;
        }
        .field:not(:last-child) {
            margin-bottom: 1.5rem;
        }
        .button.is-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            font-weight: bold;
            padding: 0.75rem 2rem;
        }
        .button.is-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .input, .button {
            border-radius: 8px;
        }
        .input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.125em rgba(102, 126, 234, 0.25);
        }
        .notification {
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .login-links {
            text-align: center;
            margin-top: 1.5rem;
        }
        .login-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 0.5rem;
        }
        .login-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <!-- Header -->
        <div class="login-header">
            <div class="has-text-centered">
                <span class="icon is-large">
                    <i class="fas fa-graduation-cap fa-3x"></i>
                </span>
                <h1 class="title is-3 has-text-white mt-3">KISSAI SCHOOL</h1>
                <p class="subtitle is-6 has-text-white">Solution de Gestion Scolaire</p>
            </div>
        </div>

        <!-- Body -->
        <div class="login-body">
            <!-- Flash Messages -->
            <?php if (isset($error) && $error): ?>
                <div class="notification is-danger is-light">
                    <button class="delete"></button>
                    <span class="icon"><i class="fas fa-exclamation-circle"></i></span>
                    <?= esc($error) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success) && $success): ?>
                <div class="notification is-success is-light">
                    <button class="delete"></button>
                    <span class="icon"><i class="fas fa-check-circle"></i></span>
                    <?= esc($success) ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="<?= base_url('auth/authenticate') ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="field">
                    <label class="label">
                        <span class="icon"><i class="fas fa-user"></i></span>
                        Nom d'utilisateur
                    </label>
                    <div class="control has-icons-left">
                        <input class="input" type="text" name="username" placeholder="Entrez votre nom d'utilisateur" 
                               value="<?= old('username') ?>" required>
                        <span class="icon is-small is-left">
                            <i class="fas fa-user"></i>
                        </span>
                    </div>
                </div>

                <div class="field">
                    <label class="label">
                        <span class="icon"><i class="fas fa-lock"></i></span>
                        Mot de passe
                    </label>
                    <div class="control has-icons-left">
                        <input class="input" type="password" name="password" placeholder="Entrez votre mot de passe" required>
                        <span class="icon is-small is-left">
                            <i class="fas fa-lock"></i>
                        </span>
                    </div>
                </div>

                <div class="field">
                    <div class="control">
                        <button type="submit" class="button is-primary is-fullwidth">
                            <span class="icon"><i class="fas fa-sign-in-alt"></i></span>
                            <span>Se connecter</span>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Links -->
            <div class="login-links">
                <a href="<?= base_url('auth/parents') ?>">
                    <span class="icon"><i class="fas fa-users"></i></span>
                    Espace Parents
                </a>
                <span class="has-text-grey">|</span>
                <a href="<?= base_url('auth/mobile') ?>">
                    <span class="icon"><i class="fas fa-mobile-alt"></i></span>
                    Interface Mobile
                </a>
            </div>

            <!-- Demo Accounts -->
            <div class="mt-4">
                <details class="has-text-centered">
                    <summary class="has-text-grey is-size-7">Comptes de démonstration</summary>
                    <div class="mt-2">
                        <div class="notification is-info is-light is-small">
                            <p class="is-size-7"><strong>Administrateur:</strong> admin / admin123</p>
                            <p class="is-size-7"><strong>Directeur:</strong> directeur / directeur123</p>
                            <p class="is-size-7"><strong>Secrétaire:</strong> secretaire / secretaire123</p>
                            <p class="is-size-7"><strong>Enseignant:</strong> enseignant / enseignant123</p>
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </div>

    <!-- Bulma JS -->
    <script src="<?= base_url('assets/bulma/js/bulma.js') ?>"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
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

            // Form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', (e) => {
                const username = form.querySelector('input[name="username"]').value.trim();
                const password = form.querySelector('input[name="password"]').value.trim();

                if (!username || !password) {
                    e.preventDefault();
                    alert('Veuillez remplir tous les champs');
                    return false;
                }
            });
        });
    </script>
</body>
</html>
