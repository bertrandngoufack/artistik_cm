<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/bulma/css/bulma.min.css') ?>">
    <link rel="stylesheet" href="http://localhost:8080/assets/fontawesome/css/all.min.css">
</head>
<body>
    <section class="section">
        <div class="container">
            <h1 class="title">À propos de KISSAI SCHOOL</h1>
            <div class="content">
                <p><?= esc($content) ?></p>
                <p>KISSAI SCHOOL est une solution complète de gestion scolaire adaptée au système éducatif camerounais.</p>
            </div>
            <a href="<?= base_url('/') ?>" class="button is-primary">Retour à l'accueil</a>
        </div>
    </section>
</body>
</html>
