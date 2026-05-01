<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Liste des Enseignants</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/enseignants/create') ?>" class="button is-primary">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Nouvel Enseignant</span>
            </a>
        </div>
    </div>
</div>

<!-- Filtres de recherche -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-search"></i></span>
            Recherche et Filtres
        </p>
    </header>
    <div class="card-content">
        <form method="GET" action="<?= base_url('admin/enseignants/list') ?>">
            <div class="columns">
                <div class="column is-4">
                    <div class="field">
                        <label class="label">Recherche</label>
                        <div class="control">
                            <input class="input" type="text" name="search" value="<?= esc($search ?? '') ?>" placeholder="Nom, prénom, email...">
                        </div>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Spécialisation</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="specialization">
                                    <option value="">Toutes les spécialisations</option>
                                    <?php foreach ($specializations as $spec): ?>
                                        <option value="<?= $spec ?>" <?= ($specialization ?? '') === $spec ? 'selected' : '' ?>>
                                            <?= $spec ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-3">
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
                <div class="column is-2">
                    <div class="field">
                        <label class="label">&nbsp;</label>
                        <div class="control">
                            <button type="submit" class="button is-info is-fullwidth">
                                <span class="icon"><i class="fas fa-search"></i></span>
                                <span>Rechercher</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des enseignants -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-users"></i></span>
            Enseignants (<?= $pager['total'] ?>)
        </p>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr>
                        <th>Nom Complet</th>
                        <th>Spécialisation</th>
                        <th>Qualification</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($teachers)): ?>
                        <?php foreach ($teachers as $teacher): ?>
                        <tr>
                            <td>
                                <div class="media">
                                    <div class="media-left">
                                        <span class="icon is-medium has-text-info">
                                            <i class="fas fa-user-tie fa-lg"></i>
                                        </span>
                                    </div>
                                    <div class="media-content">
                                        <p class="title is-6"><?= esc($teacher['first_name'] . ' ' . $teacher['last_name']) ?></p>
                                        <p class="subtitle is-7">ID: <?= $teacher['id'] ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($teacher['specialization']): ?>
                                    <span class="tag is-info"><?= esc($teacher['specialization']) ?></span>
                                <?php else: ?>
                                    <span class="tag is-light">Non définie</span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($teacher['qualification'] ?? 'Non définie') ?></td>
                            <td>
                                <a href="mailto:<?= esc($teacher['email']) ?>">
                                    <?= esc($teacher['email']) ?>
                                </a>
                            </td>
                            <td><?= esc($teacher['phone'] ?? 'Non défini') ?></td>
                            <td>
                                <?php
                                $statusClass = $teacher['is_active'] ? 'is-success' : 'is-danger';
                                $statusText = $teacher['is_active'] ? 'Actif' : 'Inactif';
                                ?>
                                <span class="tag <?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="<?= base_url('admin/enseignants/show/' . $teacher['id']) ?>" class="button is-info">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/enseignants/edit/' . $teacher['id']) ?>" class="button is-warning">
                                        <span class="icon"><i class="fas fa-edit"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/enseignants/subjects/' . $teacher['id']) ?>" class="button is-success">
                                        <span class="icon"><i class="fas fa-book"></i></span>
                                        <span>Matières</span>
                                    </a>
                                    <a href="<?= base_url('admin/enseignants/classes/' . $teacher['id']) ?>" class="button is-primary">
                                        <span class="icon"><i class="fas fa-chalkboard"></i></span>
                                        <span>Classes</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="has-text-centered">
                                <p class="has-text-grey">Aucun enseignant trouvé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($pager['total_pages'] > 1): ?>
        <div class="card-footer">
            <div class="card-footer-item">
                <nav class="pagination is-centered" role="navigation" aria-label="pagination">
                    <!-- Bouton Précédent -->
                    <?php if ($pager['has_previous']): ?>
                        <a href="<?= base_url('admin/enseignants/list?' . http_build_query(array_merge($filters, ['page' => $pager['previous_page']]))) ?>" 
                           class="pagination-previous">
                            <span class="icon"><i class="fas fa-chevron-left"></i></span>
                            <span>Précédent</span>
                        </a>
                    <?php else: ?>
                        <span class="pagination-previous" disabled>
                            <span class="icon"><i class="fas fa-chevron-left"></i></span>
                            <span>Précédent</span>
                        </span>
                    <?php endif; ?>
                    
                    <!-- Bouton Suivant -->
                    <?php if ($pager['has_next']): ?>
                        <a href="<?= base_url('admin/enseignants/list?' . http_build_query(array_merge($filters, ['page' => $pager['next_page']]))) ?>" 
                           class="pagination-next">
                            <span>Suivant</span>
                            <span class="icon"><i class="fas fa-chevron-right"></i></span>
                        </a>
                    <?php else: ?>
                        <span class="pagination-next" disabled>
                            <span>Suivant</span>
                            <span class="icon"><i class="fas fa-chevron-right"></i></span>
                        </span>
                    <?php endif; ?>
                    
                    <!-- Numéros de pages -->
                    <ul class="pagination-list">
                        <?php
                        $startPage = max(1, $pager['current_page'] - 2);
                        $endPage = min($pager['total_pages'], $pager['current_page'] + 2);
                        
                        // Première page
                        if ($startPage > 1): ?>
                            <li>
                                <a href="<?= base_url('admin/enseignants/list?' . http_build_query(array_merge($filters, ['page' => 1]))) ?>" 
                                   class="pagination-link" aria-label="Goto page 1">1</a>
                            </li>
                            <?php if ($startPage > 2): ?>
                                <li><span class="pagination-ellipsis">&hellip;</span></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Pages autour de la page courante -->
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li>
                                <?php if ($i == $pager['current_page']): ?>
                                    <span class="pagination-link is-current" aria-label="Page <?= $i ?>" aria-current="page"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="<?= base_url('admin/enseignants/list?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>" 
                                       class="pagination-link" aria-label="Goto page <?= $i ?>"><?= $i ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Dernière page -->
                        <?php if ($endPage < $pager['total_pages']): ?>
                            <?php if ($endPage < $pager['total_pages'] - 1): ?>
                                <li><span class="pagination-ellipsis">&hellip;</span></li>
                            <?php endif; ?>
                            <li>
                                <a href="<?= base_url('admin/enseignants/list?' . http_build_query(array_merge($filters, ['page' => $pager['total_pages']]))) ?>" 
                                   class="pagination-link" aria-label="Goto page <?= $pager['total_pages'] ?>"><?= $pager['total_pages'] ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <!-- Informations de pagination -->
                <div class="has-text-centered mt-3">
                    <p class="has-text-grey">
                        Affichage de <?= (($pager['current_page'] - 1) * $pager['per_page']) + 1 ?> 
                        à <?= min($pager['current_page'] * $pager['per_page'], $pager['total']) ?> 
                        sur <?= $pager['total'] ?> enseignants
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Actions -->
<div class="columns">
    <div class="column">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-tasks"></i></span>
                    Actions
                </p>
            </header>
            <div class="card-content">
                <div class="buttons">
                    <a href="<?= base_url('admin/enseignants') ?>" class="button is-info">
                        <span class="icon"><i class="fas fa-arrow-left"></i></span>
                        <span>Retour au Tableau de Bord</span>
                    </a>
                    <a href="<?= base_url('admin/enseignants/statistics') ?>" class="button is-success">
                        <span class="icon"><i class="fas fa-chart-bar"></i></span>
                        <span>Statistiques</span>
                    </a>
                    <a href="<?= base_url('admin/enseignants/export/csv') ?>" class="button is-warning">
                        <span class="icon"><i class="fas fa-download"></i></span>
                        <span>Exporter CSV</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>




