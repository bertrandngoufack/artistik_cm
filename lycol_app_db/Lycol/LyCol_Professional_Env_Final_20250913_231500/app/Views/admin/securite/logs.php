<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="section">
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <h1 class="title"><?= $title ?? 'Journaux d\'Audit' ?></h1>
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

        <!-- Filtres -->
        <div class="box">
            <form method="GET" action="<?= current_url() ?>">
                <div class="columns is-multiline">
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">Module</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="module">
                                        <option value="">Tous les modules</option>
                                        <option value="economat" <?= ($module ?? '') === 'economat' ? 'selected' : '' ?>>Économat</option>
                                        <option value="scolarite" <?= ($module ?? '') === 'scolarite' ? 'selected' : '' ?>>Scolarité</option>
                                        <option value="etudes" <?= ($module ?? '') === 'etudes' ? 'selected' : '' ?>>Études</option>
                                        <option value="examens" <?= ($module ?? '') === 'examens' ? 'selected' : '' ?>>Examens</option>
                                        <option value="enseignants" <?= ($module ?? '') === 'enseignants' ? 'selected' : '' ?>>Enseignants</option>
                                        <option value="statistiques" <?= ($module ?? '') === 'statistiques' ? 'selected' : '' ?>>Statistiques</option>
                                        <option value="messagerie" <?= ($module ?? '') === 'messagerie' ? 'selected' : '' ?>>Messagerie</option>
                                        <option value="securite" <?= ($module ?? '') === 'securite' ? 'selected' : '' ?>>Sécurité</option>
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
                                        <option value="CREATE" <?= ($action ?? '') === 'CREATE' ? 'selected' : '' ?>>Création</option>
                                        <option value="READ" <?= ($action ?? '') === 'READ' ? 'selected' : '' ?>>Lecture</option>
                                        <option value="UPDATE" <?= ($action ?? '') === 'UPDATE' ? 'selected' : '' ?>>Modification</option>
                                        <option value="DELETE" <?= ($action ?? '') === 'DELETE' ? 'selected' : '' ?>>Suppression</option>
                                        <option value="LOGIN" <?= ($action ?? '') === 'LOGIN' ? 'selected' : '' ?>>Connexion</option>
                                        <option value="LOGOUT" <?= ($action ?? '') === 'LOGOUT' ? 'selected' : '' ?>>Déconnexion</option>
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
                                        <?php if (isset($users) && is_array($users)): ?>
                                            <?php foreach ($users as $user): ?>
                                                <option value="<?= $user['id'] ?>" <?= ($user_id ?? '') == $user['id'] ? 'selected' : '' ?>>
                                                    <?= esc($user['first_name'] . ' ' . $user['last_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">&nbsp;</label>
                            <div class="control">
                                <button type="submit" class="button is-primary is-fullwidth">
                                    <span class="icon">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <span>Filtrer</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tableau des logs -->
        <div class="box">
            <?php if (isset($logs) && is_array($logs) && !empty($logs)): ?>
                <div class="table-container">
                    <table class="table is-fullwidth is-striped is-hoverable">
                        <thead>
                            <tr>
                                <th>Date/Heure</th>
                                <th>Utilisateur</th>
                                <th>Action</th>
                                <th>Module</th>
                                <th>Détails</th>
                                <th>Adresse IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td>
                                        <span class="has-text-grey">
                                            <?php
                                            if (isset($log['created_at'])) {
                                                echo date('d/m/Y H:i:s', strtotime($log['created_at']));
                                            } elseif (isset($log['timestamp'])) {
                                                echo date('d/m/Y H:i:s', strtotime($log['timestamp']));
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="level">
                                            <div class="level-left">
                                                <div class="level-item">
                                                    <div>
                                                        <p class="has-text-weight-semibold">
                                                            <?= esc($log['username'] ?? $log['user'] ?? 'N/A') ?>
                                                        </p>
                                                        <?php if (isset($log['role_name'])): ?>
                                                            <p class="is-size-7 has-text-grey">
                                                                <?= esc($log['role_name']) ?>
                                                            </p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $action = $log['action'] ?? 'N/A';
                                        $actionClass = 'is-info';
                                        if (in_array($action, ['CREATE', 'INSERT'])) {
                                            $actionClass = 'is-success';
                                        } elseif (in_array($action, ['UPDATE', 'MODIFY'])) {
                                            $actionClass = 'is-warning';
                                        } elseif (in_array($action, ['DELETE', 'REMOVE'])) {
                                            $actionClass = 'is-danger';
                                        } elseif (in_array($action, ['LOGIN', 'AUTH'])) {
                                            $actionClass = 'is-primary';
                                        }
                                        ?>
                                        <span class="tag <?= $actionClass ?>">
                                            <?= esc($action) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (isset($log['module'])): ?>
                                            <span class="tag is-light">
                                                <?= esc(ucfirst($log['module'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="has-text-grey">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($log['description'])): ?>
                                            <?= esc($log['description']) ?>
                                        <?php elseif (isset($log['details'])): ?>
                                            <?= esc($log['details']) ?>
                                        <?php else: ?>
                                            <span class="has-text-grey">Aucun détail</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code class="has-text-grey">
                                            <?= esc($log['ip_address'] ?? 'N/A') ?>
                                        </code>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($pager) && $pager): ?>
                    <div class="level">
                        <div class="level-left">
                            <div class="level-item">
                                <p class="has-text-grey">
                                    Affichage des logs d'audit
                                </p>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <?= $pager->links() ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="has-text-centered py-6">
                    <div class="icon is-large has-text-grey-light">
                        <i class="fas fa-clipboard-list fa-3x"></i>
                    </div>
                    <h3 class="title is-4 has-text-grey mt-4">Aucun log d'audit trouvé</h3>
                    <p class="has-text-grey">
                        Aucune activité n'a été enregistrée pour les critères sélectionnés.
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="level">
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
                    <button class="button is-success" onclick="exportLogs()">
                        <span class="icon">
                            <i class="fas fa-download"></i>
                        </span>
                        <span>Exporter les logs</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportLogs() {
    // Récupérer les paramètres de filtrage actuels
    const urlParams = new URLSearchParams(window.location.search);
    const exportUrl = '<?= base_url('admin/securite/logs/export') ?>?' + urlParams.toString();
    
    // Créer un lien temporaire pour le téléchargement
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = 'logs_audit_' + new Date().toISOString().split('T')[0] + '.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
<?= $this->endSection() ?>
