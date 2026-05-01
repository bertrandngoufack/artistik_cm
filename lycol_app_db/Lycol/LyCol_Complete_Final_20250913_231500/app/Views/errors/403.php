<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - KISSAI SCHOOL</title>
    <link rel="stylesheet" href="<?= base_url('assets/bulma/css/bulma.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url("assets/fontawesome/css/all.min.css") ?>">
</head>
<body>
    <section class="hero is-fullheight">
        <div class="hero-body">
            <div class="container">
                <div class="columns is-centered">
                    <div class="column is-6">
                        <div class="box has-text-centered">
                            <h1 class="title is-1 has-text-warning">
                                <i class="fas fa-ban"></i> 403
                            </h1>
                            <h2 class="subtitle is-4">Accès interdit</h2>
                            <p class="content"><?= esc($message) ?></p>
                            <div class="buttons is-centered">
                                <a href="<?= base_url('/') ?>" class="button is-primary">
                                    <span class="icon">
                                        <i class="fas fa-home"></i>
                                    </span>
                                    <span>Retour à l'accueil</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
