<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <h1 class="title">Gestion des Cycles</h1>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <a href="<?= base_url('admin/etudes/cycles/create') ?>" class="button is-primary">
                    <span class="icon"><i class="fas fa-plus"></i></span>
                    <span>Nouveau Cycle</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="notification is-success is-light">
            <button class="delete"></button>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="notification is-danger is-light">
            <button class="delete"></button>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Statistiques -->
    <div class="columns is-multiline mb-4">
        <div class="column is-3">
            <div class="box has-background-info has-text-white">
                <h4 class="title is-4 has-text-white">Total Cycles</h4>
                <p class="title is-2 has-text-white"><?= count($cycles) ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-success has-text-white">
                <h4 class="title is-4 has-text-white">Cycles Actifs</h4>
                <p class="title is-2 has-text-white"><?= count(array_filter($cycles, function($c) { return $c['is_active']; })) ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-warning has-text-white">
                <h4 class="title is-4 has-text-white">Classes Associées</h4>
                <p class="title is-2 has-text-white"><?= array_sum(array_column($cycles, 'class_count')) ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-danger has-text-white">
                <h4 class="title is-4 has-text-white">Cycles Inactifs</h4>
                <p class="title is-2 has-text-white"><?= count(array_filter($cycles, function($c) { return !$c['is_active']; })) ?></p>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <header class="card-header">
            <p class="card-header-title">Filtres</p>
        </header>
        <div class="card-content">
            <form method="GET" action="<?= base_url('admin/etudes/cycles') ?>">
                <div class="columns is-multiline">
                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Recherche</label>
                            <div class="control">
                                <input class="input" type="text" name="search" placeholder="Nom ou code du cycle" value="<?= $search ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Statut</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="status">
                                        <option value="">Tous les statuts</option>
                                        <option value="1" <?= ($status ?? '') === '1' ? 'selected' : '' ?>>Actif</option>
                                        <option value="0" <?= ($status ?? '') === '0' ? 'selected' : '' ?>>Inactif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column is-4">
                        <div class="field">
                            <label class="label">&nbsp;</label>
                            <div class="control">
                                <div class="field is-grouped">
                                    <div class="control">
                                        <button type="submit" class="button is-primary">
                                            <span class="icon"><i class="fas fa-search"></i></span>
                                            <span>Filtrer</span>
                                        </button>
                                    </div>
                                    <div class="control">
                                        <a href="<?= base_url('admin/etudes/cycles') ?>" class="button is-light">
                                            <span class="icon"><i class="fas fa-times"></i></span>
                                            <span>Réinitialiser</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des cycles -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">Liste des Cycles (<?= count($cycles) ?> résultat<?= count($cycles) > 1 ? 's' : '' ?>)</p>
        </header>
        <div class="card-content">
            <?php if (!empty($cycles)): ?>
            <div class="table-container">
                <table class="table is-fullwidth is-striped">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Classes</th>
                            <th>Capacité Totale</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cycles as $cycle): ?>
                        <tr>
                            <td>
                                <strong><?= esc($cycle['code']) ?></strong>
                            </td>
                            <td>
                                <div>
                                    <strong><?= esc($cycle['name']) ?></strong>
                                </div>
                            </td>
                            <td>
                                <?php if ($cycle['description']): ?>
                                    <span class="has-text-grey"><?= esc($cycle['description']) ?></span>
                                <?php else: ?>
                                    <span class="has-text-grey-light">Aucune description</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="tag is-info"><?= $cycle['class_count'] ?? 0 ?> classes</span>
                            </td>
                            <td>
                                <span class="tag is-success"><?= $cycle['total_capacity'] ?? 0 ?> élèves</span>
                            </td>
                            <td>
                                <?php if ($cycle['is_active']): ?>
                                    <span class="tag is-success">Actif</span>
                                <?php else: ?>
                                    <span class="tag is-danger">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="<?= base_url('admin/etudes/cycles/edit/' . $cycle['id']) ?>" class="button is-primary">
                                        <span class="icon"><i class="fas fa-edit"></i></span>
                                    </a>
                                    <form method="POST" action="<?= base_url('admin/etudes/cycles/delete/' . $cycle['id']) ?>" style="display: inline;">
                                        <button type="submit" class="button is-danger" 
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce cycle ?')">
                                            <span class="icon"><i class="fas fa-trash"></i></span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="has-text-centered py-6">
                <span class="icon is-large has-text-grey-light">
                    <i class="fas fa-info-circle fa-3x"></i>
                </span>
                <p class="title is-4 has-text-grey-light mt-4">Aucun cycle trouvé</p>
                <p class="subtitle is-6 has-text-grey-light">Commencez par créer votre premier cycle</p>
                <a href="<?= base_url('admin/etudes/cycles/create') ?>" class="button is-primary">
                    <span class="icon"><i class="fas fa-plus"></i></span>
                    <span>Créer le premier cycle</span>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide notifications
    setTimeout(function() {
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach(function(notification) {
            notification.style.display = 'none';
        });
    }, 5000);

    // Close notification on click
    document.querySelectorAll('.notification .delete').forEach(function(button) {
        button.addEventListener('click', function() {
            this.parentNode.style.display = 'none';
        });
    });
});
</script>

<?= $this->endSection() ?>
