<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <div class="box">
                <div class="level">
                    <div class="level-left">
                        <div class="level-item">
                            <h1 class="title is-4">
                                <span class="icon-text">
                                    <span class="icon">
                                        <i class="fas fa-cogs"></i>
                                    </span>
                                    <span>Paramètres Système</span>
                                </span>
                            </h1>
                        </div>
                    </div>
                    <div class="level-right">
                        <div class="level-item">
                            <a href="<?= base_url('admin/configuration/settings/create') ?>" class="button is-primary">
                                <span class="icon">
                                    <i class="fas fa-plus"></i>
                                </span>
                                <span>Nouveau Paramètre</span>
                            </a>
                        </div>
                    </div>
                </div>

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

                <!-- Filtres et recherche -->
                <div class="field has-addons mb-4">
                    <div class="control has-icons-left">
                        <input class="input" type="text" id="searchInput" placeholder="Rechercher un paramètre...">
                        <span class="icon is-small is-left">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                    <div class="control">
                        <div class="select">
                            <select id="categoryFilter">
                                <option value="">Toutes les catégories</option>
                                <option value="system">Système</option>
                                <option value="academic">Académique</option>
                                <option value="financial">Financier</option>
                                <option value="communication">Communication</option>
                                <option value="security">Sécurité</option>
                            </select>
                        </div>
                    </div>
                    <div class="control">
                        <button class="button is-info" id="filterBtn">
                            <span class="icon">
                                <i class="fas fa-filter"></i>
                            </span>
                            <span>Filtrer</span>
                        </button>
                    </div>
                </div>

                <!-- Tableau des paramètres -->
                <div class="table-container">
                    <table class="table is-fullwidth is-striped is-hoverable">
                        <thead>
                            <tr>
                                <th>Clé</th>
                                <th>Valeur</th>
                                <th>Description</th>
                                <th>Catégorie</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="settingsTableBody">
                            <?php if (!empty($settings)): ?>
                                <?php foreach ($settings as $setting): ?>
                                    <tr data-category="<?= $setting['category'] ?>">
                                        <td>
                                            <strong><?= esc($setting['setting_key']) ?></strong>
                                        </td>
                                        <td>
                                            <div class="is-family-monospace">
                                                <?php if (strlen($setting['setting_value']) > 50): ?>
                                                    <span title="<?= esc($setting['setting_value']) ?>">
                                                        <?= esc(substr($setting['setting_value'], 0, 50)) ?>...
                                                    </span>
                                                <?php else: ?>
                                                    <?= esc($setting['setting_value']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?= esc($setting['description']) ?></td>
                                        <td>
                                            <span class="tag is-info is-light">
                                                <?= ucfirst($setting['category']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($setting['is_active']): ?>
                                                <span class="tag is-success is-light">Actif</span>
                                            <?php else: ?>
                                                <span class="tag is-danger is-light">Inactif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="buttons are-small">
                                                <a href="<?= base_url('admin/configuration/settings/show/' . $setting['id']) ?>" 
                                                   class="button is-info is-light" 
                                                   title="Voir">
                                                    <span class="icon">
                                                        <i class="fas fa-eye"></i>
                                                    </span>
                                                </a>
                                                <a href="<?= base_url('admin/configuration/settings/edit/' . $setting['id']) ?>" 
                                                   class="button is-warning is-light" 
                                                   title="Éditer">
                                                    <span class="icon">
                                                        <i class="fas fa-edit"></i>
                                                    </span>
                                                </a>
                                                <button class="button is-danger is-light delete-setting" 
                                                        data-id="<?= $setting['id'] ?>" 
                                                        data-key="<?= esc($setting['setting_key']) ?>"
                                                        title="Supprimer">
                                                    <span class="icon">
                                                        <i class="fas fa-trash"></i>
                                                    </span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="has-text-centered">
                                        <p class="has-text-grey">Aucun paramètre trouvé</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Statistiques -->
                <div class="columns mt-5">
                    <div class="column">
                        <div class="box has-background-primary has-text-white">
                            <div class="level">
                                <div class="level-left">
                                    <div class="level-item">
                                        <div>
                                            <p class="heading has-text-white">Total Paramètres</p>
                                            <p class="title has-text-white"><?= count($settings) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="level-right">
                                    <div class="level-item">
                                        <span class="icon has-text-white">
                                            <i class="fas fa-cogs"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="box has-background-info has-text-white">
                            <div class="level">
                                <div class="level-left">
                                    <div class="level-item">
                                        <div>
                                            <p class="heading has-text-white">Paramètres Actifs</p>
                                            <p class="title has-text-white">
                                                <?= count(array_filter($settings, function($s) { return $s['is_active']; })) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="level-right">
                                    <div class="level-item">
                                        <span class="icon has-text-white">
                                            <i class="fas fa-check-circle"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="box has-background-success has-text-white">
                            <div class="level">
                                <div class="level-left">
                                    <div class="level-item">
                                        <div>
                                            <p class="heading has-text-white">Catégories</p>
                                            <p class="title has-text-white">
                                                <?= count(array_unique(array_column($settings, 'category'))) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="level-right">
                                    <div class="level-item">
                                        <span class="icon has-text-white">
                                            <i class="fas fa-tags"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal" id="deleteModal">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Confirmer la suppression</p>
            <button class="delete" aria-label="close"></button>
        </header>
        <section class="modal-card-body">
            <p>Êtes-vous sûr de vouloir supprimer le paramètre <strong id="deleteSettingKey"></strong> ?</p>
            <p class="has-text-danger">Cette action est irréversible.</p>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-danger" id="confirmDelete">Supprimer</button>
            <button class="button" id="cancelDelete">Annuler</button>
        </footer>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Recherche et filtrage
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const filterBtn = document.getElementById('filterBtn');
    const settingsTableBody = document.getElementById('settingsTableBody');

    function filterSettings() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedCategory = categoryFilter.value;
        const rows = settingsTableBody.querySelectorAll('tr');

        rows.forEach(row => {
            if (row.cells.length < 6) return; // Ignorer les lignes d'en-tête

            const key = row.cells[0].textContent.toLowerCase();
            const description = row.cells[2].textContent.toLowerCase();
            const category = row.dataset.category;

            const matchesSearch = key.includes(searchTerm) || description.includes(searchTerm);
            const matchesCategory = !selectedCategory || category === selectedCategory;

            row.style.display = matchesSearch && matchesCategory ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterSettings);
    filterBtn.addEventListener('click', filterSettings);

    // Suppression des paramètres
    const deleteButtons = document.querySelectorAll('.delete-setting');
    const deleteModal = document.getElementById('deleteModal');
    const deleteSettingKey = document.getElementById('deleteSettingKey');
    const confirmDelete = document.getElementById('confirmDelete');
    const cancelDelete = document.getElementById('cancelDelete');

    let currentSettingId = null;

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            currentSettingId = this.dataset.id;
            deleteSettingKey.textContent = this.dataset.key;
            deleteModal.classList.add('is-active');
        });
    });

    // Fermer le modal
    document.querySelectorAll('.modal-background, .delete').forEach(element => {
        element.addEventListener('click', function() {
            deleteModal.classList.remove('is-active');
        });
    });

    cancelDelete.addEventListener('click', function() {
        deleteModal.classList.remove('is-active');
    });

    confirmDelete.addEventListener('click', function() {
        if (currentSettingId) {
            window.location.href = `/admin/configuration/settings/delete/${currentSettingId}`;
        }
    });

    // Notifications
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        const deleteBtn = notification.querySelector('.delete');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                notification.remove();
            });
        }
    });
});
</script>
<?= $this->endSection() ?>



