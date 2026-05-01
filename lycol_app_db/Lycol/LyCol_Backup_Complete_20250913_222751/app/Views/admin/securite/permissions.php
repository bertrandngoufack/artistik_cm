<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="section">
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <h1 class="title"><?= $title ?? 'Gestion des Permissions' ?></h1>
                </div>
            </div>
            <div class="level-right">
                <div class="level-item">
                    <a href="<?= base_url('admin/securite') ?>" class="button is-info">
                        <span class="icon">
                            <i class="fas fa-arrow-left"></i>
                        </span>
                        <span>Retour</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Vue d'ensemble des permissions -->
        <div class="columns is-multiline">
            <?php if (isset($permissions) && is_array($permissions)): ?>
                <?php foreach ($permissions as $module => $modulePermissions): ?>
                    <div class="column is-6">
                        <div class="card">
                            <header class="card-header">
                                <p class="card-header-title">
                                    <span class="icon">
                                        <?php
                                        $iconClass = 'fas fa-cog';
                                        switch ($module) {
                                            case 'economat':
                                                $iconClass = 'fas fa-calculator';
                                                break;
                                            case 'scolarite':
                                                $iconClass = 'fas fa-graduation-cap';
                                                break;
                                            case 'etudes':
                                                $iconClass = 'fas fa-book';
                                                break;
                                            case 'examens':
                                                $iconClass = 'fas fa-clipboard-check';
                                                break;
                                            case 'enseignants':
                                                $iconClass = 'fas fa-chalkboard-teacher';
                                                break;
                                            case 'statistiques':
                                                $iconClass = 'fas fa-chart-bar';
                                                break;
                                            case 'messagerie':
                                                $iconClass = 'fas fa-envelope';
                                                break;
                                            case 'securite':
                                                $iconClass = 'fas fa-shield-alt';
                                                break;
                                        }
                                        ?>
                                        <i class="<?= $iconClass ?>"></i>
                                    </span>
                                    <?= ucfirst($module) ?>
                                </p>
                            </header>
                            <div class="card-content">
                                <div class="content">
                                    <?php foreach ($modulePermissions as $permissionKey => $description): ?>
                                        <div class="level">
                                            <div class="level-left">
                                                <div class="level-item">
                                                    <span class="tag is-info is-light">
                                                        <?= esc($permissionKey) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="level-right">
                                                <div class="level-item">
                                                    <small class="has-text-grey">
                                                        <?= esc($description) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Gestion des rôles et permissions -->
        <?php if (isset($roles) && is_array($roles)): ?>
            <div class="box mt-6">
                <h2 class="title is-4">Attribution des Permissions par Rôle</h2>
                
                <div class="columns is-multiline">
                    <?php foreach ($roles as $role): ?>
                        <div class="column is-6">
                            <div class="card">
                                <header class="card-header">
                                    <p class="card-header-title">
                                        <?= esc($role['name']) ?>
                                    </p>
                                    <div class="card-header-icon">
                                        <a href="<?= base_url('admin/securite/roles/' . $role['id'] . '/permissions') ?>" 
                                           class="button is-small is-info">
                                            <span class="icon">
                                                <i class="fas fa-edit"></i>
                                            </span>
                                            <span>Modifier</span>
                                        </a>
                                    </div>
                                </header>
                                <div class="card-content">
                                    <div class="content">
                                        <p class="has-text-grey">
                                            <?= esc($role['description'] ?? 'Aucune description') ?>
                                        </p>
                                        
                                        <?php if (isset($role['permissions']) && is_array($role['permissions'])): ?>
                                            <div class="tags">
                                                <?php foreach ($role['permissions'] as $permission): ?>
                                                    <span class="tag is-success is-light">
                                                        <?= esc($permission) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="has-text-grey-light">
                                                Aucune permission attribuée
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="level mt-6">
            <div class="level-left">
                <div class="level-item">
                    <a href="<?= base_url('admin/securite') ?>" class="button is-info">
                        <span class="icon">
                            <i class="fas fa-arrow-left"></i>
                        </span>
                        <span>Retour à la sécurité</span>
                    </a>
                </div>
            </div>
            <div class="level-right">
                <div class="level-item">
                    <a href="<?= base_url('admin/securite/roles') ?>" class="button is-primary">
                        <span class="icon">
                            <i class="fas fa-users-cog"></i>
                        </span>
                        <span>Gérer les rôles</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Fonction pour afficher/masquer les détails des permissions
function togglePermissions(moduleId) {
    const content = document.getElementById('permissions-' + moduleId);
    const button = document.getElementById('toggle-' + moduleId);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        button.innerHTML = '<i class="fas fa-eye-slash"></i> Masquer';
    } else {
        content.style.display = 'none';
        button.innerHTML = '<i class="fas fa-eye"></i> Afficher';
    }
}
</script>
<?= $this->endSection() ?>






