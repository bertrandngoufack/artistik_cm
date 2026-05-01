<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Audit de Sécurité</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/securite') ?>" class="button is-light">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour au module</span>
            </a>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-content">
        <form method="GET" action="<?= base_url('admin/securite/audit') ?>">
            <div class="columns is-multiline">
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Module</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="module">
                                    <option value="">Tous les modules</option>
                                    <?php foreach ($modules as $key => $name): ?>
                                    <option value="<?= $key ?>" <?= ($module ?? '') == $key ? 'selected' : '' ?>>
                                        <?= esc($name) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Action</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="action">
                                    <option value="">Toutes les actions</option>
                                    <?php foreach ($actions as $key => $name): ?>
                                    <option value="<?= $key ?>" <?= ($action ?? '') == $key ? 'selected' : '' ?>>
                                        <?= esc($name) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Utilisateur</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="user_id">
                                    <option value="">Tous les utilisateurs</option>
                                    <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= ($user_id ?? '') == $user['id'] ? 'selected' : '' ?>>
                                        <?= esc($user['first_name'] . ' ' . $user['last_name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-3">
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
                    <p class="heading">Total Logs</p>
                    <p class="title"><?= number_format($total_logs ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Aujourd'hui</p>
                    <p class="title"><?= number_format($today_logs ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Cette Semaine</p>
                    <p class="title"><?= number_format($week_logs ?? 0) ?></p>
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
</div>

<!-- Liste des logs -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-history"></i></span>
            Journal d'Audit
        </p>
        <div class="card-header-icon">
            <span class="tag is-info"><?= count($logs) ?> log(s)</span>
        </div>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped is-hoverable">
                <thead>
                    <tr>
                        <th>Date/Heure</th>
                        <th>Utilisateur</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Détails</th>
                        <th>IP</th>
                        <th>User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>
                                <span class="has-text-grey">
                                    <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="media">
                                    <div class="media-left">
                                        <figure class="image is-24x24">
                                            <img class="is-rounded" src="<?= base_url('assets/images/default-avatar.png') ?>" alt="Avatar">
                                        </figure>
                                    </div>
                                    <div class="media-content">
                                        <p class="title is-6"><?= esc($log['username'] ?? 'Système') ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php
                                $actionClass = $getActionClass($log['action']);
                                ?>
                                <span class="tag <?= $actionClass ?>"><?= esc($log['action']) ?></span>
                            </td>
                            <td>
                                <span class="tag is-info is-light"><?= esc($log['module'] ?? 'N/A') ?></span>
                            </td>
                            <td>
                                <div class="content">
                                    <p class="is-size-7"><?= esc($log['details'] ?? 'Aucun détail') ?></p>
                                </div>
                            </td>
                            <td>
                                <span class="has-text-grey-light"><?= esc($log['ip_address'] ?? 'N/A') ?></span>
                            </td>
                            <td>
                                <span class="has-text-grey-light is-size-7">
                                    <?= esc(substr($log['user_agent'] ?? 'N/A', 0, 50)) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="has-text-centered">
                                <p class="has-text-grey">Aucun log trouvé</p>
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

<!-- Actions -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-tasks"></i></span>
            Actions
        </p>
    </header>
    <div class="card-content">
        <div class="buttons">
            <a href="<?= base_url('admin/securite/audit/export') ?>" class="button is-info">
                <span class="icon"><i class="fas fa-download"></i></span>
                <span>Exporter les Logs</span>
            </a>
            <button class="button is-warning" onclick="clearOldLogs()">
                <span class="icon"><i class="fas fa-trash"></i></span>
                <span>Nettoyer les Anciens Logs</span>
            </button>
            <button class="button is-danger" onclick="clearAllLogs()">
                <span class="icon"><i class="fas fa-trash-alt"></i></span>
                <span>Vider tous les Logs</span>
            </button>
        </div>
    </div>
</div>

<script>
function clearOldLogs() {
    if (confirm('Supprimer les logs de plus de 30 jours ?')) {
        // Logique de suppression des anciens logs
        window.location.href = '<?= base_url('admin/securite/audit/clear-old') ?>';
    }
}

function clearAllLogs() {
    if (confirm('Êtes-vous sûr de vouloir supprimer TOUS les logs ? Cette action est irréversible !')) {
        // Logique de suppression de tous les logs
        window.location.href = '<?= base_url('admin/securite/audit/clear-all') ?>';
    }
}
</script>

<?= $this->endSection() ?>






