<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column is-12">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-chart-line"></i>
                        Rapport des Emprunts
                    </p>
                </div>
                <div class="card-content">
                    <!-- Statistiques générales -->
                    <div class="columns is-multiline">
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Emprunts Actifs</p>
                                <p class="title has-text-primary"><?= $activeLoans ?></p>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Emprunts en Retard</p>
                                <p class="title has-text-danger"><?= $overdueLoans ?></p>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Emprunts Retournés</p>
                                <p class="title has-text-success"><?= $returnedLoans ?></p>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Taux de Retard</p>
                                <p class="title has-text-warning">
                                    <?= $activeLoans > 0 ? round(($overdueLoans / $activeLoans) * 100, 1) : 0 ?>%
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Emprunts par mois -->
                    <div class="columns">
                        <div class="column is-8">
                            <div class="card">
                                <div class="card-header">
                                    <p class="card-header-title">
                                        <i class="fas fa-calendar-alt"></i>
                                        Emprunts par Mois (12 derniers mois)
                                    </p>
                                </div>
                                <div class="card-content">
                                    <?php if (!empty($loansByMonth)): ?>
                                        <div class="table-container">
                                            <table class="table is-fullwidth is-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Mois</th>
                                                        <th>Nombre d'Emprunts</th>
                                                        <th>Graphique</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $maxCount = max(array_column($loansByMonth, 'count'));
                                                    foreach ($loansByMonth as $month): 
                                                    ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?= date('F Y', strtotime($month['month'] . '-01')) ?></strong>
                                                            </td>
                                                            <td>
                                                                <span class="tag is-info"><?= $month['count'] ?></span>
                                                            </td>
                                                            <td>
                                                                <progress class="progress is-info" 
                                                                          value="<?= $month['count'] ?>" 
                                                                          max="<?= $maxCount ?>">
                                                                    <?= $month['count'] ?>
                                                                </progress>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="notification is-light">
                                            <p>Aucune donnée disponible pour les emprunts par mois.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="column is-4">
                            <div class="card">
                                <div class="card-header">
                                    <p class="card-header-title">
                                        <i class="fas fa-chart-pie"></i>
                                        Répartition des Emprunts
                                    </p>
                                </div>
                                <div class="card-content">
                                    <div class="content">
                                        <?php $totalLoans = $activeLoans + $returnedLoans; ?>
                                        
                                        <p><strong>Actifs :</strong> <?= $activeLoans ?> emprunts</p>
                                        <progress class="progress is-primary" value="<?= $activeLoans ?>" max="<?= $totalLoans ?>"></progress>
                                        
                                        <p><strong>Retournés :</strong> <?= $returnedLoans ?> emprunts</p>
                                        <progress class="progress is-success" value="<?= $returnedLoans ?>" max="<?= $totalLoans ?>"></progress>
                                        
                                        <p><strong>En retard :</strong> <?= $overdueLoans ?> emprunts</p>
                                        <progress class="progress is-danger" value="<?= $overdueLoans ?>" max="<?= $activeLoans ?>"></progress>
                                        
                                        <?php if ($activeLoans > 0): ?>
                                            <p><strong>Pourcentage de retard :</strong> <?= round(($overdueLoans / $activeLoans) * 100, 1) ?>%</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="field is-grouped">
                        <div class="control">
                            <a href="<?= base_url('admin/bibliotheque/loans') ?>" class="button is-info">
                                <i class="fas fa-book"></i>
                                Gérer les Emprunts
                            </a>
                        </div>
                        <div class="control">
                            <a href="<?= base_url('admin/bibliotheque/loans/create') ?>" class="button is-success">
                                <i class="fas fa-plus"></i>
                                Nouvel Emprunt
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








