<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/bulma/css/bulma.min.css') ?>">
</head>
<body>
    <section class="section">
        <div class="container">
            <h1 class="title">Aide</h1>
            <div class="content">
                <p><?= esc($content) ?></p>
            </div>
            <a href="<?= base_url('/') ?>" class="button is-primary">Retour à l'accueil</a>
        </div>
    </section>
</body>
</html>




