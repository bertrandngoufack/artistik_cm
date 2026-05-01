<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column is-12">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-chart-bar"></i>
                        Rapport des Livres
                    </p>
                </div>
                <div class="card-content">
                    <!-- Statistiques générales -->
                    <div class="columns is-multiline">
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Total des Livres</p>
                                <p class="title has-text-primary"><?= $totalBooks ?></p>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Livres Disponibles</p>
                                <p class="title has-text-success"><?= $availableBooks ?></p>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Livres Empruntés</p>
                                <p class="title has-text-warning"><?= $borrowedBooks ?></p>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Taux d'Utilisation</p>
                                <p class="title has-text-info">
                                    <?= $totalBooks > 0 ? round(($borrowedBooks / $totalBooks) * 100, 1) : 0 ?>%
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Livres par catégorie -->
                    <div class="columns">
                        <div class="column is-6">
                            <div class="card">
                                <div class="card-header">
                                    <p class="card-header-title">
                                        <i class="fas fa-tags"></i>
                                        Livres par Catégorie
                                    </p>
                                </div>
                                <div class="card-content">
                                    <?php if (!empty($booksByCategory)): ?>
                                        <div class="table-container">
                                            <table class="table is-fullwidth is-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Catégorie</th>
                                                        <th>Nombre de Livres</th>
                                                        <th>Pourcentage</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($booksByCategory as $category): ?>
                                                        <tr>
                                                            <td>
                                                                <span class="tag is-info">
                                                                    <?= esc($category['category'] ?? 'Non classé') ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <strong><?= $category['count'] ?></strong>
                                                            </td>
                                                            <td>
                                                                <?= $totalBooks > 0 ? round(($category['count'] / $totalBooks) * 100, 1) : 0 ?>%
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="notification is-light">
                                            <p>Aucune donnée disponible pour les catégories.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="column is-6">
                            <div class="card">
                                <div class="card-header">
                                    <p class="card-header-title">
                                        <i class="fas fa-chart-pie"></i>
                                        Répartition des Livres
                                    </p>
                                </div>
                                <div class="card-content">
                                    <div class="content">
                                        <p><strong>Disponibles :</strong> <?= $availableBooks ?> livres</p>
                                        <progress class="progress is-success" value="<?= $availableBooks ?>" max="<?= $totalBooks ?>"></progress>
                                        
                                        <p><strong>Empruntés :</strong> <?= $borrowedBooks ?> livres</p>
                                        <progress class="progress is-warning" value="<?= $borrowedBooks ?>" max="<?= $totalBooks ?>"></progress>
                                        
                                        <?php if ($totalBooks > 0): ?>
                                            <p><strong>Pourcentage d'utilisation :</strong> <?= round(($borrowedBooks / $totalBooks) * 100, 1) ?>%</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="field is-grouped">
                        <div class="control">
                            <a href="<?= base_url('admin/bibliotheque/books') ?>" class="button is-info">
                                <i class="fas fa-book"></i>
                                Gérer les Livres
                            </a>
                        </div>
                        <div class="control">
                            <a href="<?= base_url('admin/bibliotheque/books/create') ?>" class="button is-success">
                                <i class="fas fa-plus"></i>
                                Ajouter un Livre
                            </a>
                        </div>
                        <div class="control">
                            <a href="<?= base_url('admin/bibliotheque/reports') ?>" class="button is-light">
                                <i class="fas fa-arrow-left"></i>
                                Retour aux Rapports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>








