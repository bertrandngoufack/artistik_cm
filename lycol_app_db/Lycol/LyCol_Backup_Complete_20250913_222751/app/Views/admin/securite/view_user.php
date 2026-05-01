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
                        <a href="<?= base_url('admin/securite/users') ?>" class="button is-info">
                            <span class="icon"><i class="fas fa-arrow-left"></i></span>
                            <span>Retour à la liste</span>
                        </a>
                        <a href="<?= base_url('admin/securite/users/' . $user['id'] . '/edit') ?>" class="button is-warning">
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
                                <span class="icon"><i class="fas fa-user"></i></span>
                                Informations de l'Utilisateur
                            </p>
                        </div>
                        <div class="card-content">
                            <div class="columns">
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Nom d'utilisateur</label>
                                        <div class="control">
                                            <p class="has-text-weight-semibold"><?= esc($user['username']) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Email</label>
                                        <div class="control">
                                            <p class="has-text-weight-semibold"><?= esc($user['email']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="columns">
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Prénom</label>
                                        <div class="control">
                                            <p class="has-text-weight-semibold"><?= esc($user['first_name']) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Nom</label>
                                        <div class="control">
                                            <p class="has-text-weight-semibold"><?= esc($user['last_name']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="columns">
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Rôle</label>
                                        <div class="control">
                                            <span class="tag is-info"><?= esc($user['role_name'] ?? 'Non assigné') ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Statut</label>
                                        <div class="control">
                                            <?php if ($user['is_active']): ?>
                                                <span class="tag is-success">Actif</span>
                                            <?php else: ?>
                                                <span class="tag is-danger">Inactif</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="columns">
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Dernière connexion</label>
                                        <div class="control">
                                            <p class="has-text-weight-semibold">
                                                <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais' ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Date de création</label>
                                        <div class="control">
                                            <p class="has-text-weight-semibold">
                                                <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($user['notes'])): ?>
                                <div class="field">
                                    <label class="label">Notes</label>
                                    <div class="control">
                                        <p><?= esc($user['notes']) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
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
                                <a href="<?= base_url('admin/securite/users/' . $user['id'] . '/permissions') ?>" class="button is-primary is-fullwidth">
                                    <span class="icon"><i class="fas fa-key"></i></span>
                                    <span>Gérer les Permissions</span>
                                </a>
                                <a href="<?= base_url('admin/securite/users/' . $user['id'] . '/edit') ?>" class="button is-warning is-fullwidth">
                                    <span class="icon"><i class="fas fa-edit"></i></span>
                                    <span>Modifier l'Utilisateur</span>
                                </a>
                                <button class="button is-danger is-fullwidth" onclick="confirmDelete(<?= $user['id'] ?>)">
                                    <span class="icon"><i class="fas fa-trash"></i></span>
                                    <span>Supprimer l'Utilisateur</span>
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
                                <p><strong>Connexions:</strong> <?= $user['last_login'] ? 'Oui' : 'Non' ?></p>
                                <p><strong>Actif depuis:</strong> <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
                                <p><strong>Dernière activité:</strong> <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Aucune' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(userId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
        window.location.href = '<?= base_url('admin/securite/users') ?>/' + userId + '/delete';
    }
}
</script>

<?= $this->endSection() ?>




