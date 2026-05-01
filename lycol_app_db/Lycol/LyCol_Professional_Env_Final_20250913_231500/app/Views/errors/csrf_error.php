<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Erreur de sécurité') ?></title>
    
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
        .error-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 90%;
        }
        .error-icon {
            font-size: 4rem;
            color: #f14668;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="card">
            <div class="card-content has-text-centered">
                <div class="error-icon mb-4">
                    <i class="fas fa-shield-alt"></i>
                </div>
                
                <h1 class="title is-3 has-text-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Erreur de Sécurité
                </h1>
                
                <div class="content">
                    <p class="has-text-grey-dark">
                        <?= esc($message ?? 'Une erreur de sécurité a été détectée.') ?>
                    </p>
                    
                    <div class="notification is-warning is-light mt-4">
                        <p class="has-text-weight-semibold">
                            <i class="fas fa-info-circle"></i>
                            Que s'est-il passé ?
                        </p>
                        <p class="is-size-7">
                            Cette erreur se produit généralement lorsque :
                        </p>
                        <ul class="is-size-7">
                            <li>Le formulaire a expiré</li>
                            <li>Vous avez navigué en arrière dans votre navigateur</li>
                            <li>Une tentative d'attaque CSRF a été détectée</li>
                        </ul>
                    </div>
                    
                    <div class="buttons is-centered mt-5">
                        <a href="<?= base_url('admin/dashboard') ?>" class="button is-primary">
                            <span class="icon">
                                <i class="fas fa-home"></i>
                            </span>
                            <span>Retour au Dashboard</span>
                        </a>
                        
                        <button onclick="history.back()" class="button is-light">
                            <span class="icon">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span>Retour en arrière</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="has-text-centered mt-4">
            <p class="has-text-white is-size-7">
                <i class="fas fa-clock"></i>
                <?= date('d/m/Y H:i:s') ?>
            </p>
        </div>
    </div>

    <script>
        // Log de sécurité côté client
        console.warn('CSRF Error detected:', {
            timestamp: new Date().toISOString(),
            url: window.location.href,
            userAgent: navigator.userAgent
        });
        
        // Redirection automatique après 30 secondes
        setTimeout(function() {
            window.location.href = '<?= base_url('admin/dashboard') ?>';
        }, 30000);
    </script>
</body>
</html>





