<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Module Sécurité</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/securite/users/add') ?>" class="button is-primary">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Nouvel Utilisateur</span>
            </a>
        </div>
    </div>
</div>

<div class="columns is-multiline">
    <!-- Statistiques -->
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
                    <p class="heading">Sessions Actives</p>
                    <p class="title"><?= number_format($active_sessions ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Tentatives Échouées</p>
                    <p class="title"><?= number_format($failed_attempts ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Rôles Créés</p>
                    <p class="title"><?= number_format($total_roles ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Utilisateurs récents -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-users"></i></span>
            Utilisateurs Récents
        </p>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr>
                        <th>Nom d'Utilisateur</th>
                        <th>Nom Complet</th>
                        <th>Rôle</th>
                        <th>Dernière Connexion</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_users)): ?>
                        <?php foreach ($recent_users as $user): ?>
                        <tr>
                            <td><?= esc($user['username']) ?></td>
                            <td><?= esc($user['first_name'] . ' ' . $user['last_name']) ?></td>
                            <td>
                                <span class="tag is-info"><?= esc($user['role_name']) ?></span>
                            </td>
                            <td><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais' ?></td>
                            <td>
                                <?php
                                $statusClass = $user['is_active'] ? 'is-success' : 'is-danger';
                                $statusText = $user['is_active'] ? 'Actif' : 'Inactif';
                                ?>
                                <span class="tag <?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="<?= base_url('admin/securite/users/' . $user['id']) ?>" class="button is-info">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/securite/users/' . $user['id'] . '/edit') ?>" class="button is-warning">
                                        <span class="icon"><i class="fas fa-edit"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/securite/users/' . $user['id'] . '/permissions') ?>" class="button is-success">
                                        <span class="icon"><i class="fas fa-key"></i></span>
                                        <span>Permissions</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="has-text-centered">
                                <p class="has-text-grey">Aucun utilisateur enregistré</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Rôles et permissions -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-user-tag"></i></span>
            Rôles et Permissions
        </p>
        <div class="card-header-icon">
            <a href="<?= base_url('admin/securite/roles/create') ?>" class="button is-small is-primary">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Nouveau Rôle</span>
            </a>
        </div>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr>
                        <th>Nom du Rôle</th>
                        <th>Description</th>
                        <th>Utilisateurs</th>
                        <th>Permissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($roles)): ?>
                        <?php foreach ($roles as $role): ?>
                        <tr>
                            <td>
                                <strong><?= esc($role['name']) ?></strong>
                            </td>
                            <td><?= esc($role['description']) ?></td>
                            <td><?= number_format($role['user_count']) ?> utilisateurs</td>
                            <td>
                                <div class="tags">
                                    <?php foreach ($role['permissions'] as $permission): ?>
                                    <span class="tag is-info is-small"><?= esc($permission) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="<?= base_url('admin/securite/roles/' . $role['id']) ?>" class="button is-info">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/securite/roles/' . $role['id'] . '/edit') ?>" class="button is-warning">
                                        <span class="icon"><i class="fas fa-edit"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/securite/roles/' . $role['id'] . '/permissions') ?>" class="button is-success">
                                        <span class="icon"><i class="fas fa-key"></i></span>
                                        <span>Permissions</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="has-text-centered">
                                <p class="has-text-grey">Aucun rôle créé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Journal d'activité -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-history"></i></span>
            Journal d'Activité Récent
        </p>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Date</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_activities)): ?>
                        <?php foreach ($recent_activities as $activity): ?>
                        <tr>
                            <td><?= esc($activity['username']) ?></td>
                            <td>
                                <span class="tag is-info"><?= esc($activity['action']) ?></span>
                            </td>
                            <td><?= esc($activity['module']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($activity['created_at'])) ?></td>
                            <td><?= esc($activity['ip_address']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="has-text-centered">
                                <p class="has-text-grey">Aucune activité récente</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Actions rapides -->
<div class="columns">
    <div class="column">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-tasks"></i></span>
                    Actions Rapides
                </p>
            </header>
            <div class="card-content">
                <div class="buttons">
                    <a href="<?= base_url('admin/securite/users') ?>" class="button is-primary">
                        <span class="icon"><i class="fas fa-users"></i></span>
                        <span>Gestion Utilisateurs</span>
                    </a>
                    <a href="<?= base_url('admin/securite/roles') ?>" class="button is-success">
                        <span class="icon"><i class="fas fa-user-tag"></i></span>
                        <span>Gestion Rôles</span>
                    </a>
                    <a href="<?= base_url('admin/securite/permissions') ?>" class="button is-info">
                        <span class="icon"><i class="fas fa-key"></i></span>
                        <span>Gestion Permissions</span>
                    </a>
                    <a href="<?= base_url('admin/securite/audit') ?>" class="button is-warning">
                        <span class="icon"><i class="fas fa-search"></i></span>
                        <span>Audit de Sécurité</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>




