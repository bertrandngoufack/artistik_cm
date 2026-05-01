<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'Espace Parents - KISSAI SCHOOL' ?></title>
    <link rel="stylesheet" href="/assets/bulma/css/bulma.min.css">
    <link rel="stylesheet" href="<?= base_url("assets/fontawesome/css/all.min.css") ?>">
</head>
<body>
    <nav class="navbar is-primary" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a class="navbar-item" href="/">
                <strong>KISSAI SCHOOL - Espace Parents</strong>
            </a>
        </div>
        <div class="navbar-end">
            <a class="navbar-item" href="/">
                <span class="icon"><i class="fas fa-home"></i></span>
                <span>Accueil</span>
            </a>
        </div>
    </nav>

    <main class="section">
        <div class="container">
            <div class="columns is-centered">
                <div class="column is-6">
                    <div class="card">
                        <div class="card-header">
                            <p class="card-header-title">
                                <span class="icon"><i class="fas fa-users"></i></span>
                                Accès Espace Parents
                            </p>
                        </div>
                        <div class="card-content">
                            <?php if (isset($error) && $error): ?>
                                <div class="notification is-danger">
                                    <button class="delete"></button>
                                    <?= $error ?>
                                </div>
                            <?php endif; ?>

                            <form action="/auth/authenticate-parent" method="POST">
                                <div class="field">
                                    <label class="label">Matricule de l'élève</label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="text" name="matricule" placeholder="Ex: 2024-001" required>
                                        <span class="icon is-small is-left">
                                            <i class="fas fa-id-card"></i>
                                        </span>
                                    </div>
                                    <p class="help">Entrez le matricule de votre enfant</p>
                                </div>

                                <div class="field">
                                    <label class="label">Année de naissance</label>
                                    <div class="control has-icons-left">
                                        <input class="input" type="number" name="birth_year" placeholder="Ex: 2010" min="1990" max="2020" required>
                                        <span class="icon is-small is-left">
                                            <i class="fas fa-calendar"></i>
                                        </span>
                                    </div>
                                    <p class="help">Année de naissance de votre enfant</p>
                                </div>

                                <div class="field">
                                    <div class="control">
                                        <button class="button is-primary is-fullwidth" type="submit">
                                            <span class="icon"><i class="fas fa-sign-in-alt"></i></span>
                                            <span>Accéder à l'espace</span>
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="has-text-centered mt-4">
                                <p class="has-text-grey">
                                    <span class="icon"><i class="fas fa-info-circle"></i></span>
                                    Accédez aux informations de votre enfant en toute sécurité
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="notification is-info is-light mt-4">
                        <div class="content">
                            <h4 class="title is-5">
                                <span class="icon"><i class="fas fa-question-circle"></i></span>
                                Besoin d'aide ?
                            </h4>
                            <p>Contactez l'administration de l'école pour obtenir le matricule de votre enfant.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="content has-text-centered">
            <p>
                <strong>KISSAI SCHOOL</strong> - Espace Parents. 
                Système de gestion scolaire moderne et sécurisé.
            </p>
        </div>
    </footer>

    <script src="/assets/bulma/js/bulma.js"></script>
    <script>
        // Fermer les notifications
        document.addEventListener('DOMContentLoaded', () => {
            (document.querySelectorAll('.notification .delete') || []).forEach(($delete) => {
                const $notification = $delete.parentNode;
                $delete.addEventListener('click', () => {
                    $notification.parentNode.removeChild($notification);
                });
            });
        });
    </script>
</body>
</html>





