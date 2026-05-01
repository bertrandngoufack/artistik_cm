<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/bibliotheque') ?>">Bibliothèque</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Gestion des Emprunts</a></li>
                </ul>
            </nav>
            
            <h1 class="title">
                <i class="fas fa-hand-holding"></i>
                Gestion des Emprunts
            </h1>
            <p class="subtitle">Gérer les emprunts et retours de livres</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="notification is-success">
            <button class="delete" onclick="this.parentElement.remove()"></button>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="notification is-danger">
            <button class="delete" onclick="this.parentElement.remove()"></button>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Statistiques des Emprunts -->
    <div class="columns">
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Emprunts Actifs</p>
                                <p class="title has-text-primary"><?= $stats['activeLoans'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Retours Aujourd'hui</p>
                                <p class="title has-text-success"><?= $stats['returnsToday'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">En Retard</p>
                                <p class="title has-text-danger"><?= $stats['overdueLoans'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Total Emprunts</p>
                                <p class="title"><?= $stats['totalLoans'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et Recherche -->
    <div class="card">
        <div class="card-content">
            <form method="GET" action="<?= base_url('admin/bibliotheque/loans') ?>">
                <div class="columns">
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">Recherche</label>
                            <div class="control">
                                <input class="input" 
                                       type="text" 
                                       name="search" 
                                       value="<?= $search ?? '' ?>" 
                                       placeholder="Livre, membre, ID...">
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
                                        <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Actifs</option>
                                        <option value="returned" <?= ($status ?? '') === 'returned' ? 'selected' : '' ?>>Retournés</option>
                                        <option value="overdue" <?= ($status ?? '') === 'overdue' ? 'selected' : '' ?>>En Retard</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column is-2">
                        <div class="field">
                            <label class="label">Date de Début</label>
                            <div class="control">
                                <input class="input" 
                                       type="date" 
                                       name="start_date" 
                                       value="<?= $startDate ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    <div class="column is-2">
                        <div class="field">
                            <label class="label">Date de Fin</label>
                            <div class="control">
                                <input class="input" 
                                       type="date" 
                                       name="end_date" 
                                       value="<?= $endDate ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">&nbsp;</label>
                            <div class="control">
                                <div class="buttons">
                                    <button type="submit" class="button is-primary">
                                        <i class="fas fa-search"></i>
                                        Rechercher
                                    </button>
                                    <a href="<?= base_url('admin/bibliotheque/loans') ?>" class="button is-light">
                                        <i class="fas fa-times"></i>
                                        Réinitialiser
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Actions -->
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <a href="<?= base_url('admin/bibliotheque/loans/create') ?>" class="button is-primary">
                    <i class="fas fa-plus"></i>
                    Nouvel Emprunt
                </a>
            </div>
            <div class="level-item">
                <a href="<?= base_url('admin/bibliotheque/loans/overdue') ?>" class="button is-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Emprunts en Retard
                </a>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <a href="<?= base_url('admin/bibliotheque/loans/export') ?>" class="button is-info">
                    <i class="fas fa-download"></i>
                    Exporter
                </a>
            </div>
        </div>
    </div>

    <!-- Liste des Emprunts -->
    <div class="card">
        <div class="card-header">
            <p class="card-header-title">
                <i class="fas fa-list"></i>
                Liste des Emprunts
            </p>
        </div>
        <div class="card-content">
            <?php if (empty($loans)): ?>
                <div class="has-text-centered py-6">
                    <i class="fas fa-inbox fa-3x has-text-grey-light mb-4"></i>
                    <p class="has-text-grey">Aucun emprunt trouvé</p>
                    <a href="<?= base_url('admin/bibliotheque/loans/create') ?>" class="button is-primary mt-4">
                        <i class="fas fa-plus"></i>
                        Créer un emprunt
                    </a>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table is-fullwidth is-striped is-hoverable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Livre</th>
                                <th>Membre</th>
                                <th>Date d'Emprunt</th>
                                <th>Date de Retour</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loans as $loan): ?>
                                <tr>
                                    <td>
                                        <span class="tag is-info">#<?= $loan['id'] ?></span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= esc($loan['book_title']) ?></strong>
                                            <br>
                                            <small class="has-text-grey"><?= esc($loan['book_author']) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= esc($loan['member_name']) ?></strong>
                                            <br>
                                            <small class="has-text-grey"><?= esc($loan['member_email']) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= date('d/m/Y', strtotime($loan['loan_date'])) ?></strong>
                                            <br>
                                            <small class="has-text-grey"><?= date('H:i', strtotime($loan['loan_date'])) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($loan['return_date']): ?>
                                            <div>
                                                <strong><?= date('d/m/Y', strtotime($loan['return_date'])) ?></strong>
                                                <br>
                                                <small class="has-text-grey"><?= date('H:i', strtotime($loan['return_date'])) ?></small>
                                            </div>
                                        <?php else: ?>
                                            <div>
                                                <strong><?= date('d/m/Y', strtotime($loan['due_date'])) ?></strong>
                                                <br>
                                                <small class="has-text-grey">Échéance</small>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status = 'active';
                                        $statusClass = 'is-success';
                                        $statusText = 'Actif';
                                        
                                        if ($loan['return_date']) {
                                            $status = 'returned';
                                            $statusClass = 'is-info';
                                            $statusText = 'Retourné';
                                        } elseif (strtotime($loan['due_date']) < time()) {
                                            $status = 'overdue';
                                            $statusClass = 'is-danger';
                                            $statusText = 'En Retard';
                                        }
                                        ?>
                                        <span class="tag <?= $statusClass ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="buttons are-small">
                                            <a href="<?= base_url('admin/bibliotheque/loans/' . $loan['id']) ?>" 
                                               class="button is-info" 
                                               title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <?php if (!$loan['return_date']): ?>
                                                <a href="<?= base_url('admin/bibliotheque/loans/' . $loan['id'] . '/return') ?>" 
                                                   class="button is-success" 
                                                   title="Marquer comme retourné"
                                                   onclick="return confirm('Confirmer le retour de ce livre ?')">
                                                    <i class="fas fa-undo"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="<?= base_url('admin/bibliotheque/loans/' . $loan['id'] . '/edit') ?>" 
                                               class="button is-warning" 
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <button onclick="deleteLoan(<?= $loan['id'] ?>)" 
                                                    class="button is-danger" 
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($pager)): ?>
                    <nav class="pagination is-centered" role="navigation" aria-label="pagination">
                        <?= $pager->links() ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Emprunts en Retard -->
    <?php if (!empty($overdueLoans)): ?>
        <div class="card">
            <div class="card-header">
                <p class="card-header-title has-text-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Emprunts en Retard (<?= count($overdueLoans) ?>)
                </p>
            </div>
            <div class="card-content">
                <div class="table-container">
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Livre</th>
                                <th>Membre</th>
                                <th>Date d'Échéance</th>
                                <th>Jours de Retard</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($overdueLoans as $loan): ?>
                                <?php
                                $daysOverdue = floor((time() - strtotime($loan['due_date'])) / (60 * 60 * 24));
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($loan['book_title']) ?></strong>
                                        <br>
                                        <small class="has-text-grey"><?= esc($loan['book_author']) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= esc($loan['member_name']) ?></strong>
                                        <br>
                                        <small class="has-text-grey"><?= esc($loan['member_email']) ?></small>
                                    </td>
                                    <td>
                                        <strong class="has-text-danger"><?= date('d/m/Y', strtotime($loan['due_date'])) ?></strong>
                                    </td>
                                    <td>
                                        <span class="tag is-danger">
                                            <?= $daysOverdue ?> jour<?= $daysOverdue > 1 ? 's' : '' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="buttons are-small">
                                            <a href="<?= base_url('admin/bibliotheque/loans/' . $loan['id'] . '/return') ?>" 
                                               class="button is-success" 
                                               title="Marquer comme retourné">
                                                <i class="fas fa-undo"></i>
                                                Retourner
                                            </a>
                                            <a href="<?= base_url('admin/bibliotheque/loans/' . $loan['id'] . '/extend') ?>" 
                                               class="button is-warning" 
                                               title="Prolonger l'emprunt">
                                                <i class="fas fa-clock"></i>
                                                Prolonger
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function deleteLoan(loanId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet emprunt ? Cette action est irréversible.')) {
        window.location.href = '<?= base_url('admin/bibliotheque/loans') ?>/' + loanId + '/delete';
    }
}

// Auto-submit form on filter change
document.querySelectorAll('select[name="status"], input[name="start_date"], input[name="end_date"]').forEach(function(element) {
    element.addEventListener('change', function() {
        this.closest('form').submit();
    });
});
</script>

<?= $this->endSection() ?>

