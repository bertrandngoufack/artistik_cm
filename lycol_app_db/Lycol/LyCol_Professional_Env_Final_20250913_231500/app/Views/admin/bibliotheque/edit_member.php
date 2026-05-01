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
        <section class="hero is-warning is-small">
            <div class="hero-body">
                <div class="container">
                    <h1 class="title">
                        <i class="fas fa-edit"></i> <?= $title ?>
                    </h1>
                    <h2 class="subtitle">
                        Modifier les informations du membre
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
                <li class="is-active"><a href="#" aria-current="page">Modifier</a></li>
            </ul>
        </nav>

        <!-- Content -->
        <div class="columns">
            <div class="column is-8">
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            <i class="fas fa-user-edit"></i> Formulaire de Modification
                        </p>
                    </header>
                    <div class="card-content">
                        <form action="<?= base_url('admin/bibliotheque/members/' . $member['id'] . '/update') ?>" method="POST">
                            <?= csrf_field() ?>
                            
                            <div class="columns">
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Prénom <span class="has-text-danger">*</span></label>
                                        <div class="control">
                                            <input class="input" type="text" name="first_name" 
                                                   value="<?= esc($member['first_name']) ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Nom <span class="has-text-danger">*</span></label>
                                        <div class="control">
                                            <input class="input" type="text" name="last_name" 
                                                   value="<?= esc($member['last_name']) ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="columns">
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Email <span class="has-text-danger">*</span></label>
                                        <div class="control">
                                            <input class="input" type="email" name="email" 
                                                   value="<?= esc($member['email']) ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Téléphone <span class="has-text-danger">*</span></label>
                                        <div class="control">
                                            <input class="input" type="tel" name="phone" 
                                                   value="<?= esc($member['phone']) ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($member['member_type'] === 'STUDENT'): ?>
                                <div class="columns">
                                    <div class="column is-6">
                                        <div class="field">
                                            <label class="label">Matricule</label>
                                            <div class="control">
                                                <input class="input" type="text" name="matricule" 
                                                       value="<?= esc($member['matricule'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="column is-6">
                                        <div class="field">
                                            <label class="label">Statut</label>
                                            <div class="control">
                                                <div class="select is-fullwidth">
                                                    <select name="status">
                                                        <option value="ACTIVE" <?= ($member['status'] === 'ACTIVE') ? 'selected' : '' ?>>Actif</option>
                                                        <option value="INACTIVE" <?= ($member['status'] === 'INACTIVE') ? 'selected' : '' ?>>Inactif</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="columns">
                                    <div class="column is-6">
                                        <div class="field">
                                            <label class="label">Statut</label>
                                            <div class="control">
                                                <div class="select is-fullwidth">
                                                    <select name="is_active">
                                                        <option value="1" <?= ($member['status'] ? 'selected' : '') ?>>Actif</option>
                                                        <option value="0" <?= (!$member['status'] ? 'selected' : '') ?>>Inactif</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="field">
                                <label class="label">Type de membre</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="member_type" disabled>
                                            <option value="STUDENT" <?= ($member['member_type'] === 'STUDENT') ? 'selected' : '' ?>>Étudiant</option>
                                            <option value="TEACHER" <?= ($member['member_type'] === 'TEACHER') ? 'selected' : '' ?>>Enseignant</option>
                                        </select>
                                    </div>
                                    <p class="help">Le type de membre ne peut pas être modifié</p>
                                </div>
                            </div>

                            <div class="field is-grouped">
                                <div class="control">
                                    <button type="submit" class="button is-warning">
                                        <span class="icon">
                                            <i class="fas fa-save"></i>
                                        </span>
                                        <span>Enregistrer les modifications</span>
                                    </button>
                                </div>
                                <div class="control">
                                    <a href="<?= base_url('admin/bibliotheque/members/' . $member['id']) ?>" 
                                       class="button is-light">
                                        <span class="icon">
                                            <i class="fas fa-times"></i>
                                        </span>
                                        <span>Annuler</span>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="column is-4">
                <!-- Member Info Card -->
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            <i class="fas fa-info-circle"></i> Informations Actuelles
                        </p>
                    </header>
                    <div class="card-content">
                        <div class="content">
                            <p><strong>Type:</strong> 
                                <span class="tag <?= $member['member_type'] === 'STUDENT' ? 'is-info' : 'is-warning' ?>">
                                    <?= $member['member_type'] === 'STUDENT' ? 'Étudiant' : 'Enseignant' ?>
                                </span>
                            </p>
                            <p><strong>Statut:</strong> 
                                <?php if ($member['member_type'] === 'STUDENT'): ?>
                                    <span class="tag <?= $member['status'] === 'ACTIVE' ? 'is-success' : 'is-danger' ?>">
                                        <?= $member['status'] === 'ACTIVE' ? 'Actif' : 'Inactif' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="tag <?= $member['status'] ? 'is-success' : 'is-danger' ?>">
                                        <?= $member['status'] ? 'Actif' : 'Inactif' ?>
                                    </span>
                                <?php endif; ?>
                            </p>
                            <p><strong>Date d'inscription:</strong><br>
                                <?= date('d/m/Y', strtotime($member['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="card mt-4">
                    <header class="card-header">
                        <p class="card-header-title">
                            <i class="fas fa-cogs"></i> Actions
                        </p>
                    </header>
                    <div class="card-content">
                        <div class="buttons">
                            <a href="<?= base_url('admin/bibliotheque/members/' . $member['id']) ?>" 
                               class="button is-info is-fullwidth">
                                <span class="icon">
                                    <i class="fas fa-eye"></i>
                                </span>
                                <span>Voir les détails</span>
                            </a>
                            
                            <a href="<?= base_url('admin/bibliotheque/members') ?>" 
                               class="button is-light is-fullwidth">
                                <span class="icon">
                                    <i class="fas fa-arrow-left"></i>
                                </span>
                                <span>Retour à la liste</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bulma@0.9.4/js/bulma.min.js"></script>
</body>
</html>








