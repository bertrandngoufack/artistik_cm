<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Gestion des Rôles</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/securite/roles/create') ?>" class="button is-primary">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Nouveau Rôle</span>
            </a>
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="columns is-multiline mb-4">
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Total Rôles</p>
                    <p class="title"><?= number_format($total_roles ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Rôles Actifs</p>
                    <p class="title"><?= number_format($active_roles ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Rôles Inactifs</p>
                    <p class="title"><?= number_format($inactive_roles ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Utilisateurs Assignés</p>
                    <p class="title"><?= number_format($assigned_users ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Liste des rôles -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-user-tag"></i></span>
            Liste des Rôles
        </p>
        <div class="card-header-icon">
            <span class="tag is-info"><?= count($roles) ?> rôle(s)</span>
        </div>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped is-hoverable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom du Rôle</th>
                        <th>Description</th>
                        <th>Utilisateurs</th>
                        <th>Permissions</th>
                        <th>Statut</th>
                        <th>Date de Création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($roles)): ?>
                        <?php foreach ($roles as $role): ?>
                        <tr>
                            <td><?= $role['id'] ?></td>
                            <td>
                                <strong><?= esc($role['name']) ?></strong>
                            </td>
                            <td><?= esc($role['description']) ?></td>
                            <td>
                                <span class="tag is-info"><?= number_format($role['user_count'] ?? 0) ?> utilisateur(s)</span>
                            </td>
                            <td>
                                <div class="tags">
                                    <?php 
                                    $permissions = json_decode($role['permissions'], true);
                                    if (is_array($permissions)):
                                        foreach (array_slice($permissions, 0, 3) as $permission): ?>
                                        <span class="tag is-info is-small"><?= esc($permission) ?></span>
                                    <?php endforeach;
                                        if (count($permissions) > 3): ?>
                                        <span class="tag is-light is-small">+<?= count($permissions) - 3 ?> autres</span>
                                    <?php endif;
                                    endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $statusClass = $role['is_active'] ? 'is-success' : 'is-danger';
                                $statusText = $role['is_active'] ? 'Actif' : 'Inactif';
                                ?>
                                <span class="tag <?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                            <td>
                                <span class="has-text-grey">
                                    <?= date('d/m/Y H:i', strtotime($role['created_at'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="<?= base_url('admin/securite/roles/' . $role['id']) ?>" class="button is-info" title="Voir">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/securite/roles/' . $role['id'] . '/edit') ?>" class="button is-warning" title="Modifier">
                                        <span class="icon"><i class="fas fa-edit"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/securite/roles/' . $role['id'] . '/permissions') ?>" class="button is-success" title="Permissions">
                                        <span class="icon"><i class="fas fa-key"></i></span>
                                    </a>
                                    <?php if (($role['user_count'] ?? 0) == 0): ?>
                                    <a href="<?= base_url('admin/securite/roles/' . $role['id'] . '/delete') ?>" 
                                       class="button is-danger" 
                                       title="Supprimer"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce rôle ?')">
                                        <span class="icon"><i class="fas fa-trash"></i></span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="has-text-centered">
                                <p class="has-text-grey">Aucun rôle trouvé</p>
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

<!-- Permissions disponibles -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-key"></i></span>
            Permissions Disponibles
        </p>
    </header>
    <div class="card-content">
        <div class="columns is-multiline">
            <div class="column is-3">
                <h6 class="title is-6">Module Économat</h6>
                <ul>
                    <li>economat.view</li>
                    <li>economat.create</li>
                    <li>economat.edit</li>
                    <li>economat.delete</li>
                    <li>economat.export</li>
                </ul>
            </div>
            <div class="column is-3">
                <h6 class="title is-6">Module Scolarité</h6>
                <ul>
                    <li>scolarite.view</li>
                    <li>scolarite.create</li>
                    <li>scolarite.edit</li>
                    <li>scolarite.delete</li>
                    <li>scolarite.export</li>
                </ul>
            </div>
            <div class="column is-3">
                <h6 class="title is-6">Module Études</h6>
                <ul>
                    <li>etudes.view</li>
                    <li>etudes.create</li>
                    <li>etudes.edit</li>
                    <li>etudes.delete</li>
                    <li>etudes.export</li>
                </ul>
            </div>
            <div class="column is-3">
                <h6 class="title is-6">Module Examens</h6>
                <ul>
                    <li>examens.view</li>
                    <li>examens.create</li>
                    <li>examens.edit</li>
                    <li>examens.delete</li>
                    <li>examens.export</li>
                </ul>
            </div>
            <div class="column is-3">
                <h6 class="title is-6">Module Enseignants</h6>
                <ul>
                    <li>enseignants.view</li>
                    <li>enseignants.create</li>
                    <li>enseignants.edit</li>
                    <li>enseignants.delete</li>
                    <li>enseignants.export</li>
                </ul>
            </div>
            <div class="column is-3">
                <h6 class="title is-6">Module Statistiques</h6>
                <ul>
                    <li>statistiques.view</li>
                    <li>statistiques.export</li>
                    <li>statistiques.admin</li>
                </ul>
            </div>
            <div class="column is-3">
                <h6 class="title is-6">Module Messagerie</h6>
                <ul>
                    <li>messagerie.view</li>
                    <li>messagerie.send</li>
                    <li>messagerie.templates</li>
                    <li>messagerie.settings</li>
                </ul>
            </div>
            <div class="column is-3">
                <h6 class="title is-6">Module Sécurité</h6>
                <ul>
                    <li>securite.view</li>
                    <li>securite.users</li>
                    <li>securite.roles</li>
                    <li>securite.permissions</li>
                    <li>securite.audit</li>
                </ul>
            </div>
        </div>
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
            <a href="<?= base_url('admin/securite/roles/export') ?>" class="button is-info">
                <span class="icon"><i class="fas fa-download"></i></span>
                <span>Exporter</span>
            </a>
        </div>
    </div>
</div>

<script>
function activateSelected() {
    if (confirm('Activer les rôles sélectionnés ?')) {
        // Logique d'activation
    }
}

function deactivateSelected() {
    if (confirm('Désactiver les rôles sélectionnés ?')) {
        // Logique de désactivation
    }
}

function deleteSelected() {
    if (confirm('Supprimer définitivement les rôles sélectionnés ?')) {
        // Logique de suppression
    }
}
</script>

<?= $this->endSection() ?>







