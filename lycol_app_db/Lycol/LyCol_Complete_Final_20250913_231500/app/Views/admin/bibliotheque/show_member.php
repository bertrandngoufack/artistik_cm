<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Administration</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <section class="hero is-primary is-small">
            <div class="hero-body">
                <div class="container">
                    <h1 class="title">
                        <i class="fas fa-user"></i> <?= $title ?>
                    </h1>
                    <h2 class="subtitle">
                        Détails complets du membre
                    </h2>
                </div>
            </div>
        </section>

        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                <li><a href="<?= base_url('admin/bibliotheque') ?>">Bibliothèque</a></li>
                <li><a href="<?= base_url('admin/bibliotheque/members') ?>">Membres</a></li>
                <li class="is-active"><a href="#" aria-current="page">Détails</a></li>
            </ul>
        </nav>

        <!-- Content -->
        <div class="columns">
            <div class="column is-8">
                <!-- Member Information Card -->
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            <i class="fas fa-info-circle"></i> Informations du Membre
                        </p>
                    </header>
                    <div class="card-content">
                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Nom complet</label>
                                    <div class="control">
                                        <p class="has-text-weight-semibold">
                                            <?= esc($member['first_name']) ?> <?= esc($member['last_name']) ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Type de membre</label>
                                    <div class="control">
                                        <span class="tag <?= $member['member_type'] === 'STUDENT' ? 'is-info' : 'is-warning' ?>">
                                            <?= $member['member_type'] === 'STUDENT' ? 'Étudiant' : 'Enseignant' ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Email</label>
                                    <div class="control">
                                        <p><?= esc($member['email']) ?></p>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Téléphone</label>
                                    <div class="control">
                                        <p><?= esc($member['phone']) ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <?php if ($member['member_type'] === 'STUDENT'): ?>
                                    <div class="field">
                                        <label class="label">Matricule</label>
                                        <div class="control">
                                            <p class="has-text-weight-semibold"><?= esc($member['matricule']) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="field">
                                    <label class="label">Statut</label>
                                    <div class="control">
                                        <?php if ($member['member_type'] === 'STUDENT'): ?>
                                            <span class="tag <?= $member['status'] === 'ACTIVE' ? 'is-success' : 'is-danger' ?>">
                                                <?= $member['status'] === 'ACTIVE' ? 'Actif' : 'Inactif' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="tag <?= $member['status'] ? 'is-success' : 'is-danger' ?>">
                                                <?= $member['status'] ? 'Actif' : 'Inactif' ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Date d'inscription</label>
                                    <div class="control">
                                        <p><?= date('d/m/Y', strtotime($member['created_at'])) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loans History Card -->
                <div class="card mt-4">
                    <header class="card-header">
                        <p class="card-header-title">
                            <i class="fas fa-history"></i> Historique des Emprunts
                        </p>
                    </header>
                    <div class="card-content">
                        <p class="has-text-grey">Aucun historique d'emprunt disponible pour le moment.</p>
                    </div>
                </div>
            </div>

            <div class="column is-4">
                <!-- Actions Card -->
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            <i class="fas fa-cogs"></i> Actions
                        </p>
                    </header>
                    <div class="card-content">
                        <div class="buttons">
                            <a href="<?= base_url('admin/bibliotheque/members/' . $member['id'] . '/edit') ?>" 
                               class="button is-warning is-fullwidth">
                                <span class="icon">
                                    <i class="fas fa-edit"></i>
                                </span>
                                <span>Modifier</span>
                            </a>
                            
                            <a href="<?= base_url('admin/bibliotheque/members') ?>" 
                               class="button is-info is-fullwidth">
                                <span class="icon">
                                    <i class="fas fa-arrow-left"></i>
                                </span>
                                <span>Retour à la liste</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Card -->
                <div class="card mt-4">
                    <header class="card-header">
                        <p class="card-header-title">
                            <i class="fas fa-chart-bar"></i> Statistiques Rapides
                        </p>
                    </header>
                    <div class="card-content">
                        <div class="content">
                            <div class="level">
                                <div class="level-item has-text-centered">
                                    <div>
                                        <p class="heading">Emprunts actifs</p>
                                        <p class="title">0</p>
                                    </div>
                                </div>
                            </div>
                            <div class="level">
                                <div class="level-item has-text-centered">
                                    <div>
                                        <p class="heading">Retards</p>
                                        <p class="title has-text-danger">0</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bulma@0.9.4/js/bulma.min.js"></script>
</body>
</html>








