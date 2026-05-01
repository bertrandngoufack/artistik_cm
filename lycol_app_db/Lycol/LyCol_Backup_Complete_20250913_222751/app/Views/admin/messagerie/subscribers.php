<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Gestion des Abonnés</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/messagerie') ?>" class="button is-light">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour</span>
            </a>
            <a href="<?= base_url('admin/messagerie/subscribers/add') ?>" class="button is-primary">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Nouvel Abonné</span>
            </a>
        </div>
    </div>
</div>

<!-- Statistiques des abonnés -->
<div class="columns">
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="level">
                    <div class="level-item has-text-centered">
                        <div>
                            <p class="heading">Total Abonnés</p>
                            <p class="title"><?= $totalSubscribers ?? 0 ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="level">
                    <div class="level-item has-text-centered">
                        <div>
                            <p class="heading">Élèves</p>
                            <p class="title"><?= $studentSubscribers ?? 0 ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="level">
                    <div class="level-item has-text-centered">
                        <div>
                            <p class="heading">Parents</p>
                            <p class="title"><?= $parentSubscribers ?? 0 ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="level">
                    <div class="level-item has-text-centered">
                        <div>
                            <p class="heading">Personnel</p>
                            <p class="title"><?= $staffSubscribers ?? 0 ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-filter"></i></span>
            Filtres
        </p>
    </header>
    <div class="card-content">
        <form method="GET" action="<?= base_url('admin/messagerie/subscribers') ?>">
            <div class="columns">
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Type d'abonné</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="type">
                                    <option value="">Tous les types</option>
                                    <option value="STUDENT" <?= ($type ?? '') === 'STUDENT' ? 'selected' : '' ?>>Élève</option>
                                    <option value="PARENT" <?= ($type ?? '') === 'PARENT' ? 'selected' : '' ?>>Parent</option>
                                    <option value="STAFF" <?= ($type ?? '') === 'STAFF' ? 'selected' : '' ?>>Personnel</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Statut</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="ACTIVE" <?= ($status ?? '') === 'ACTIVE' ? 'selected' : '' ?>>Actif</option>
                                    <option value="INACTIVE" <?= ($status ?? '') === 'INACTIVE' ? 'selected' : '' ?>>Inactif</option>
                                    <option value="PENDING" <?= ($status ?? '') === 'PENDING' ? 'selected' : '' ?>>En attente</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-4">
                    <div class="field">
                        <label class="label">Recherche</label>
                        <div class="control">
                            <input class="input" type="text" name="search" value="<?= esc($search ?? '') ?>" placeholder="Nom, email, téléphone...">
                        </div>
                    </div>
                </div>
                <div class="column is-2">
                    <div class="field">
                        <label class="label">&nbsp;</label>
                        <div class="control">
                            <button type="submit" class="button is-info is-fullwidth">
                                <span class="icon"><i class="fas fa-search"></i></span>
                                <span>Rechercher</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des abonnés -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-users"></i></span>
            Abonnés (<?= count($subscribers ?? []) ?>)
        </p>
        <div class="card-header-icon">
            <a href="<?= base_url('admin/messagerie/subscribers/export') ?>" class="button is-small is-success">
                <span class="icon"><i class="fas fa-download"></i></span>
                <span>Exporter</span>
            </a>
        </div>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Date d'inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($subscribers)): ?>
                        <?php foreach ($subscribers as $subscriber): ?>
                        <tr>
                            <td>
                                <div class="media">
                                    <div class="media-left">
                                        <figure class="image is-32x32">
                                            <img class="is-rounded" src="<?= base_url('assets/images/avatar.png') ?>" alt="Avatar">
                                        </figure>
                                    </div>
                                    <div class="media-content">
                                        <p class="title is-6"><?= esc($subscriber['name'] ?? 'N/A') ?></p>
                                        <p class="subtitle is-7"><?= esc($subscriber['firstname'] ?? 'N/A') ?></p>
                                    </div>
                                </div>
                            </td>
                            <td><?= esc($subscriber['email']) ?></td>
                            <td><?= esc($subscriber['phone'] ?? 'Non renseigné') ?></td>
                            <td>
                                <?php
                                $typeIcon = 'fas fa-user';
                                $typeClass = 'is-dark';
                                switch ($subscriber['type']) {
                                    case 'STUDENT':
                                        $typeIcon = 'fas fa-user-graduate';
                                        $typeClass = 'is-primary';
                                        break;
                                    case 'PARENT':
                                        $typeIcon = 'fas fa-users';
                                        $typeClass = 'is-info';
                                        break;
                                    case 'STAFF':
                                        $typeIcon = 'fas fa-user-tie';
                                        $typeClass = 'is-warning';
                                        break;
                                }
                                ?>
                                <span class="tag <?= $typeClass ?>">
                                    <span class="icon"><i class="<?= $typeIcon ?>"></i></span>
                                    <span><?= esc($subscriber['type']) ?></span>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusClass = 'is-info';
                                switch ($subscriber['status']) {
                                    case 'ACTIVE':
                                        $statusClass = 'is-success';
                                        break;
                                    case 'INACTIVE':
                                        $statusClass = 'is-danger';
                                        break;
                                    case 'PENDING':
                                        $statusClass = 'is-warning';
                                        break;
                                }
                                ?>
                                <span class="tag <?= $statusClass ?>"><?= esc($subscriber['status']) ?></span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($subscriber['created_at'])) ?></td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="<?= base_url('admin/messagerie/subscriber/' . $subscriber['id']) ?>" class="button is-info">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/messagerie/subscriber/' . $subscriber['id'] . '/edit') ?>" class="button is-warning">
                                        <span class="icon"><i class="fas fa-edit"></i></span>
                                    </a>
                                    <?php if ($subscriber['status'] === 'ACTIVE'): ?>
                                        <a href="<?= base_url('admin/messagerie/subscriber/' . $subscriber['id'] . '/deactivate') ?>" class="button is-danger">
                                            <span class="icon"><i class="fas fa-ban"></i></span>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= base_url('admin/messagerie/subscriber/' . $subscriber['id'] . '/activate') ?>" class="button is-success">
                                            <span class="icon"><i class="fas fa-check"></i></span>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('admin/messagerie/subscriber/' . $subscriber['id'] . '/delete') ?>" 
                                       class="button is-danger"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet abonné ?')">
                                        <span class="icon"><i class="fas fa-trash"></i></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="has-text-centered">
                                <p class="has-text-grey">Aucun abonné trouvé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Import en masse -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-upload"></i></span>
            Import en masse
        </p>
    </header>
    <div class="card-content">
        <div class="columns">
            <div class="column is-8">
                <form method="POST" action="<?= base_url('admin/messagerie/subscribers/import') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label class="label">Fichier CSV</label>
                        <div class="control">
                            <input class="input" type="file" name="csv_file" accept=".csv" required>
                        </div>
                        <p class="help">Format attendu: nom,prénom,email,téléphone,type,statut</p>
                    </div>
                    <div class="field">
                        <div class="control">
                            <button type="submit" class="button is-primary">
                                <span class="icon"><i class="fas fa-upload"></i></span>
                                <span>Importer</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="column is-4">
                <div class="content">
                    <h6>Format du fichier CSV :</h6>
                    <div class="box">
                        <p><strong>En-têtes :</strong></p>
                        <code>nom,prénom,email,téléphone,type,statut</code>
                        <br><br>
                        <p><strong>Exemple :</strong></p>
                        <code>Dupont,Jean,jean.dupont@email.com,+237123456789,STUDENT,ACTIVE</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
