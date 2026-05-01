<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Gestion des Utilisateurs</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/securite/users/create') ?>" class="button is-primary">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Nouvel Utilisateur</span>
            </a>
        </div>
    </div>
</div>

<!-- Filtres et recherche -->
<div class="card mb-4">
    <div class="card-content">
        <form method="GET" action="<?= base_url('admin/securite/users') ?>">
            <div class="columns is-multiline">
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Recherche</label>
                        <div class="control">
                            <input class="input" type="text" name="search" value="<?= esc($search ?? '') ?>" placeholder="Nom, email, rôle...">
                        </div>
                    </div>
                </div>
                <div class="column is-2">
                    <div class="field">
                        <label class="label">Rôle</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="role_id">
                                    <option value="">Tous les rôles</option>
                                    <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>" <?= ($role_id ?? '') == $role['id'] ? 'selected' : '' ?>>
                                        <?= esc($role['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-2">
                    <div class="field">
                        <label class="label">Statut</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="status">
                                    <option value="">Tous</option>
                                    <option value="1" <?= ($status ?? '') === '1' ? 'selected' : '' ?>>Actif</option>
                                    <option value="0" <?= ($status ?? '') === '0' ? 'selected' : '' ?>>Inactif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-2">
                    <div class="field">
                        <label class="label">&nbsp;</label>
                        <div class="control">
                            <button type="submit" class="button is-info is-fullwidth">
                                <span class="icon"><i class="fas fa-search"></i></span>
                                <span>Filtrer</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="column is-2">
                    <div class="field">
                        <label class="label">&nbsp;</label>
                        <div class="control">
                            <a href="<?= base_url('admin/securite/users') ?>" class="button is-light is-fullwidth">
                                <span class="icon"><i class="fas fa-times"></i></span>
                                <span>Réinitialiser</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Statistiques -->
<div class="columns is-multiline mb-4">
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Total Utilisateurs</p>
                    <p class="title"><?= number_format($total_users ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Utilisateurs Actifs</p>
                    <p class="title"><?= number_format($active_users ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Utilisateurs Inactifs</p>
                    <p class="title"><?= number_format($inactive_users ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Connexions Aujourd'hui</p>
                    <p class="title"><?= number_format($today_logins ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Liste des utilisateurs -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-users"></i></span>
            Liste des Utilisateurs
        </p>
        <div class="card-header-icon">
            <span class="tag is-info"><?= count($users) ?> utilisateur(s)</span>
        </div>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped is-hoverable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Avatar</th>
                        <th>Nom d'Utilisateur</th>
                        <th>Nom Complet</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Dernière Connexion</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td>
                                <figure class="image is-32x32">
                                    <img class="is-rounded" src="<?= isset($user['avatar']) && $user['avatar'] ? base_url('uploads/avatars/' . $user['avatar']) : base_url('assets/images/default-avatar.png') ?>" alt="Avatar">
                                </figure>
                            </td>
                            <td>
                                <strong><?= esc($user['username']) ?></strong>
                            </td>
                            <td><?= esc($user['first_name'] . ' ' . $user['last_name']) ?></td>
                            <td><?= esc($user['email']) ?></td>
                            <td>
                                <span class="tag is-info"><?= esc($user['role_name'] ?? 'N/A') ?></span>
                            </td>
                            <td>
                                <?php if ($user['last_login']): ?>
                                    <span class="has-text-grey">
                                        <?= date('d/m/Y H:i', strtotime($user['last_login'])) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="has-text-grey-light">Jamais</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusClass = $user['is_active'] ? 'is-success' : 'is-danger';
                                $statusText = $user['is_active'] ? 'Actif' : 'Inactif';
                                ?>
                                <span class="tag <?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="<?= base_url('admin/securite/users/' . $user['id']) ?>" class="button is-info" title="Voir">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/securite/users/' . $user['id'] . '/edit') ?>" class="button is-warning" title="Modifier">
                                        <span class="icon"><i class="fas fa-edit"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/securite/users/' . $user['id'] . '/permissions') ?>" class="button is-success" title="Permissions">
                                        <span class="icon"><i class="fas fa-key"></i></span>
                                    </a>
                                    <?php if ($user['id'] != session()->get('user_id')): ?>
                                    <a href="<?= base_url('admin/securite/users/' . $user['id'] . '/delete') ?>" 
                                       class="button is-danger" 
                                       title="Supprimer"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                        <span class="icon"><i class="fas fa-trash"></i></span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="has-text-centered">
                                <p class="has-text-grey">Aucun utilisateur trouvé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if (isset($pager) && $pager): ?>
        <nav class="pagination is-centered" role="navigation" aria-label="pagination">
            <?= $pager->links() ?>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Actions en lot -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-tasks"></i></span>
            Actions en Lot
        </p>
    </header>
    <div class="card-content">
        <div class="buttons">
            <button class="button is-success" onclick="activateSelected()">
                <span class="icon"><i class="fas fa-check"></i></span>
                <span>Activer Sélectionnés</span>
            </button>
            <button class="button is-warning" onclick="deactivateSelected()">
                <span class="icon"><i class="fas fa-pause"></i></span>
                <span>Désactiver Sélectionnés</span>
            </button>
            <button class="button is-danger" onclick="deleteSelected()">
                <span class="icon"><i class="fas fa-trash"></i></span>
                <span>Supprimer Sélectionnés</span>
            </button>
            <a href="<?= base_url('admin/securite/users/export') ?>" class="button is-info">
                <span class="icon"><i class="fas fa-download"></i></span>
                <span>Exporter</span>
            </a>
        </div>
    </div>
</div>

<script>
function activateSelected() {
    if (confirm('Activer les utilisateurs sélectionnés ?')) {
        // Logique d'activation
    }
}

function deactivateSelected() {
    if (confirm('Désactiver les utilisateurs sélectionnés ?')) {
        // Logique de désactivation
    }
}

function deleteSelected() {
    if (confirm('Supprimer définitivement les utilisateurs sélectionnés ?')) {
        // Logique de suppression
    }
}
</script>

<?= $this->endSection() ?>
