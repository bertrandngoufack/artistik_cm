<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column is-12">
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h1 class="title"><?= $title ?></h1>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <a href="<?= base_url('admin/securite/roles') ?>" class="button is-info">
                            <span class="icon"><i class="fas fa-arrow-left"></i></span>
                            <span>Retour à la liste</span>
                        </a>
                        <a href="<?= base_url('admin/securite/roles/' . $role['id'] . '/edit') ?>" class="button is-warning">
                            <span class="icon"><i class="fas fa-edit"></i></span>
                            <span>Modifier</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="columns">
                <div class="column is-8">
                    <div class="card">
                        <div class="card-header">
                            <p class="card-header-title">
                                <span class="icon"><i class="fas fa-user-tag"></i></span>
                                Informations du Rôle
                            </p>
                        </div>
                        <div class="card-content">
                            <div class="columns">
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Nom du Rôle</label>
                                        <div class="control">
                                            <p class="has-text-weight-semibold"><?= esc($role['name']) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Statut</label>
                                        <div class="control">
                                            <?php if ($role['is_active']): ?>
                                                <span class="tag is-success">Actif</span>
                                            <?php else: ?>
                                                <span class="tag is-danger">Inactif</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Description</label>
                                <div class="control">
                                    <p><?= esc($role['description']) ?></p>
                                </div>
                            </div>

                            <div class="columns">
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Date de création</label>
                                        <div class="control">
                                            <p class="has-text-weight-semibold">
                                                <?= date('d/m/Y H:i', strtotime($role['created_at'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Dernière modification</label>
                                        <div class="control">
                                            <p class="has-text-weight-semibold">
                                                <?= $role['updated_at'] ? date('d/m/Y H:i', strtotime($role['updated_at'])) : 'Jamais' ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($role['permissions'])): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <p class="card-header-title">
                                <span class="icon"><i class="fas fa-key"></i></span>
                                Permissions du Rôle
                            </p>
                        </div>
                        <div class="card-content">
                            <div class="columns is-multiline">
                                <?php 
                                $permissions = is_string($role['permissions']) ? json_decode($role['permissions'], true) : $role['permissions'];
                                if ($permissions):
                                    foreach ($permissions as $permission):
                                ?>
                                <div class="column is-3">
                                    <span class="tag is-info is-medium"><?= esc($permission) ?></span>
                                </div>
                                <?php 
                                    endforeach;
                                endif;
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="column is-4">
                    <div class="card">
                        <div class="card-header">
                            <p class="card-header-title">
                                <span class="icon"><i class="fas fa-key"></i></span>
                                Actions Rapides
                            </p>
                        </div>
                        <div class="card-content">
                            <div class="buttons">
                                <a href="<?= base_url('admin/securite/roles/' . $role['id'] . '/permissions') ?>" class="button is-primary is-fullwidth">
                                    <span class="icon"><i class="fas fa-key"></i></span>
                                    <span>Gérer les Permissions</span>
                                </a>
                                <a href="<?= base_url('admin/securite/roles/' . $role['id'] . '/edit') ?>" class="button is-warning is-fullwidth">
                                    <span class="icon"><i class="fas fa-edit"></i></span>
                                    <span>Modifier le Rôle</span>
                                </a>
                                <button class="button is-danger is-fullwidth" onclick="confirmDelete(<?= $role['id'] ?>)">
                                    <span class="icon"><i class="fas fa-trash"></i></span>
                                    <span>Supprimer le Rôle</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <p class="card-header-title">
                                <span class="icon"><i class="fas fa-chart-bar"></i></span>
                                Statistiques
                            </p>
                        </div>
                        <div class="card-content">
                            <div class="content">
                                <p><strong>Utilisateurs assignés:</strong> <?= $role['user_count'] ?? 0 ?></p>
                                <p><strong>Permissions actives:</strong> <?= count($permissions ?? []) ?></p>
                                <p><strong>Créé le:</strong> <?= date('d/m/Y', strtotime($role['created_at'])) ?></p>
                                <p><strong>Statut:</strong> <?= $role['is_active'] ? 'Actif' : 'Inactif' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(roleId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce rôle ?')) {
        window.location.href = '<?= base_url('admin/securite/roles') ?>/' + roleId + '/delete';
    }
}
</script>

<?= $this->endSection() ?>




