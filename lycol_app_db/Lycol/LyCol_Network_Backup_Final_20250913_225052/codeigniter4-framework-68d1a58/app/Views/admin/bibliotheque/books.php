<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/bibliotheque') ?>">Bibliothèque</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Gestion des Livres</a></li>
                </ul>
            </nav>
            
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h1 class="title">
                            <i class="fas fa-book"></i>
                            Gestion des Livres
                        </h1>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <a href="<?= base_url('admin/bibliotheque/books/create') ?>" class="button is-primary">
                            <i class="fas fa-plus"></i>
                            Nouveau Livre
                        </a>
                    </div>
                </div>
            </div>
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

    <!-- Filtres et Recherche -->
    <div class="card mb-4">
        <div class="card-content">
            <form method="GET" action="<?= base_url('admin/bibliotheque/books') ?>">
                <div class="columns">
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">Rechercher</label>
                            <div class="control">
                                <input class="input" type="text" name="search" value="<?= $search ?? '' ?>" placeholder="Titre, auteur, ISBN...">
                            </div>
                        </div>
                    </div>
                    <div class="column is-2">
                        <div class="field">
                            <label class="label">Catégorie</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="category">
                                        <option value="">Toutes</option>
                                        <option value="litterature" <?= ($category ?? '') === 'litterature' ? 'selected' : '' ?>>Littérature</option>
                                        <option value="scolaire" <?= ($category ?? '') === 'scolaire' ? 'selected' : '' ?>>Scolaire</option>
                                        <option value="reference" <?= ($category ?? '') === 'reference' ? 'selected' : '' ?>>Référence</option>
                                        <option value="sciences" <?= ($category ?? '') === 'sciences' ? 'selected' : '' ?>>Sciences</option>
                                        <option value="histoire" <?= ($category ?? '') === 'histoire' ? 'selected' : '' ?>>Histoire</option>
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
                                        <option value="available" <?= ($status ?? '') === 'available' ? 'selected' : '' ?>>Disponible</option>
                                        <option value="borrowed" <?= ($status ?? '') === 'borrowed' ? 'selected' : '' ?>>Emprunté</option>
                                        <option value="reserved" <?= ($status ?? '') === 'reserved' ? 'selected' : '' ?>>Réservé</option>
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
                                    <i class="fas fa-search"></i>
                                    Rechercher
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="column is-2">
                        <div class="field">
                            <label class="label">&nbsp;</label>
                            <div class="control">
                                <a href="<?= base_url('admin/bibliotheque/books') ?>" class="button is-light is-fullwidth">
                                    <i class="fas fa-times"></i>
                                    Réinitialiser
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="columns mb-4">
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Total Livres</p>
                                <p class="title"><?= $stats['totalBooks'] ?? 0 ?></p>
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
                                <p class="heading">Disponibles</p>
                                <p class="title has-text-success"><?= $stats['availableBooks'] ?? 0 ?></p>
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
                                <p class="heading">Empruntés</p>
                                <p class="title has-text-warning"><?= $stats['borrowedBooks'] ?? 0 ?></p>
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
                                <p class="title has-text-danger"><?= $stats['overdueBooks'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Livres -->
    <div class="card">
        <div class="card-header">
            <p class="card-header-title">
                <i class="fas fa-list"></i>
                Liste des Livres
            </p>
        </div>
        <div class="card-content">
            <?php if (!empty($books)): ?>
                <div class="table-container">
                    <table class="table is-fullwidth is-striped is-hoverable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Auteur</th>
                                <th>ISBN</th>
                                <th>Catégorie</th>
                                <th>Copies</th>
                                <th>Disponibles</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($books as $book): ?>
                            <tr>
                                <td><?= $book['id'] ?></td>
                                <td>
                                    <strong><?= esc($book['title']) ?></strong>
                                </td>
                                <td><?= esc($book['author']) ?></td>
                                <td>
                                    <code><?= esc($book['isbn']) ?></code>
                                </td>
                                <td>
                                    <span class="tag is-info"><?= esc($book['category'] ?? 'Non définie') ?></span>
                                </td>
                                <td><?= $book['total_copies'] ?? 0 ?></td>
                                <td>
                                    <span class="tag <?= ($book['available_copies'] ?? 0) > 0 ? 'is-success' : 'is-danger' ?>">
                                        <?= $book['available_copies'] ?? 0 ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (($book['available_copies'] ?? 0) > 0): ?>
                                        <span class="tag is-success">Disponible</span>
                                    <?php else: ?>
                                        <span class="tag is-danger">Indisponible</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="buttons are-small">
                                        <a href="<?= base_url('admin/bibliotheque/books/' . $book['id']) ?>" class="button is-info" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= base_url('admin/bibliotheque/books/' . $book['id'] . '/edit') ?>" class="button is-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if (($book['available_copies'] ?? 0) > 0): ?>
                                            <a href="<?= base_url('admin/bibliotheque/loans/create?book_id=' . $book['id']) ?>" class="button is-success" title="Emprunter">
                                                <i class="fas fa-hand-holding"></i>
                                            </a>
                                        <?php endif; ?>
                                        <button onclick="deleteBook(<?= $book['id'] ?>)" class="button is-danger" title="Supprimer">
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
                <?php if (isset($pager) && $pager): ?>
                    <nav class="pagination is-centered" role="navigation" aria-label="pagination">
                        <?= $pager->links() ?>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="has-text-centered py-6">
                    <i class="fas fa-book fa-3x has-text-grey-light mb-4"></i>
                    <p class="title is-4 has-text-grey-light">Aucun livre trouvé</p>
                    <p class="subtitle is-6 has-text-grey">Commencez par ajouter votre premier livre</p>
                    <a href="<?= base_url('admin/bibliotheque/books/create') ?>" class="button is-primary">
                        <i class="fas fa-plus"></i>
                        Ajouter un Livre
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteBook(bookId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce livre ?')) {
        window.location.href = '<?= base_url('admin/bibliotheque/books') ?>/' + bookId + '/delete';
    }
}
</script>

<?= $this->endSection() ?>

