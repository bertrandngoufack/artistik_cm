<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Module Bibliothèque</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/bibliotheque/books/add') ?>" class="button is-primary">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Nouveau Livre</span>
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
                    <p class="heading">Total Livres</p>
                    <p class="title"><?= number_format($total_books ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Livres Disponibles</p>
                    <p class="title"><?= number_format($available_books ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Emprunts Actifs</p>
                    <p class="title"><?= number_format($active_loans ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Membres</p>
                    <p class="title"><?= number_format($total_members ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Livres récents -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-book"></i></span>
            Livres Récents
        </p>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>ISBN</th>
                        <th>Catégorie</th>
                        <th>Disponibles</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_books)): ?>
                        <?php foreach ($recent_books as $book): ?>
                        <tr>
                            <td><?= esc($book['title']) ?></td>
                            <td><?= esc($book['author']) ?></td>
                            <td><?= esc($book['isbn']) ?></td>
                            <td>
                                <span class="tag is-info"><?= esc($book['category']) ?></span>
                            </td>
                            <td><?= number_format($book['available_copies']) ?></td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="<?= base_url('admin/bibliotheque/books/' . $book['id']) ?>" class="button is-info" title="Voir">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/bibliotheque/books/' . $book['id'] . '/edit') ?>" class="button is-warning" title="Modifier">
                                        <span class="icon"><i class="fas fa-edit"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/bibliotheque/loans/create?book_id=' . $book['id']) ?>" class="button is-success" title="Emprunter">
                                        <span class="icon"><i class="fas fa-hand-holding"></i></span>
                                        <span>Emprunter</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="has-text-centered">
                                <p class="has-text-grey">Aucun livre enregistré</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Emprunts récents -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-history"></i></span>
            Emprunts Récents
        </p>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr>
                        <th>Livre</th>
                        <th>Membre</th>
                        <th>Date d'Emprunt</th>
                        <th>Date de Retour</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_loans)): ?>
                        <?php foreach ($recent_loans as $loan): ?>
                        <tr>
                            <td><?= esc($loan['book_title']) ?></td>
                            <td><?= esc($loan['member_name']) ?></td>
                            <td><?= date('d/m/Y', strtotime($loan['loan_date'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($loan['due_date'])) ?></td>
                            <td>
                                <?php
                                $statusClass = 'is-info';
                                switch ($loan['status']) {
                                    case 'RETURNED':
                                        $statusClass = 'is-success';
                                        break;
                                    case 'OVERDUE':
                                        $statusClass = 'is-danger';
                                        break;
                                    case 'BORROWED':
                                        $statusClass = 'is-warning';
                                        break;
                                }
                                ?>
                                <?php
                                $statusText = '';
                                switch ($loan['status']) {
                                    case 'RETURNED':
                                        $statusText = 'RETOURNÉ';
                                        break;
                                    case 'OVERDUE':
                                        $statusText = 'EN RETARD';
                                        break;
                                    case 'BORROWED':
                                        $statusText = 'EMPRUNTÉ';
                                        break;
                                    default:
                                        $statusText = $loan['status'];
                                }
                                ?>
                                <span class="tag <?= $statusClass ?>"><?= esc($statusText) ?></span>
                            </td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="<?= base_url('admin/bibliotheque/loans/' . $loan['id']) ?>" class="button is-info" title="Voir">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <?php if ($loan['status'] === 'BORROWED'): ?>
                                    <a href="<?= base_url('admin/bibliotheque/loans/' . $loan['id'] . '/return') ?>" class="button is-success" title="Retourner">
                                        <span class="icon"><i class="fas fa-undo"></i></span>
                                        <span>Retourner</span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="has-text-centered">
                                <p class="has-text-grey">Aucun emprunt enregistré</p>
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
                    <a href="<?= base_url('admin/bibliotheque/books') ?>" class="button is-primary">
                        <span class="icon"><i class="fas fa-book"></i></span>
                        <span>Gestion Livres</span>
                    </a>
                    <a href="<?= base_url('admin/bibliotheque/loans') ?>" class="button is-success">
                        <span class="icon"><i class="fas fa-hand-holding"></i></span>
                        <span>Gestion Emprunts</span>
                    </a>
                    <a href="<?= base_url('admin/bibliotheque/members') ?>" class="button is-info">
                        <span class="icon"><i class="fas fa-users"></i></span>
                        <span>Gestion Membres</span>
                    </a>
                    <a href="<?= base_url('admin/bibliotheque/reports') ?>" class="button is-warning">
                        <span class="icon"><i class="fas fa-chart-bar"></i></span>
                        <span>Rapports</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>




