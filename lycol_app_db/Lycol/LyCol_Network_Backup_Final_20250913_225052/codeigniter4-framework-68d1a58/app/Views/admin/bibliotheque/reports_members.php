<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column is-12">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-users"></i>
                        Rapport des Membres
                    </p>
                </div>
                <div class="card-content">
                    <!-- Statistiques générales -->
                    <div class="columns is-multiline">
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Total des Membres</p>
                                <p class="title has-text-primary"><?= $totalMembers ?></p>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Étudiants</p>
                                <p class="title has-text-info"><?= $studentMembers ?></p>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Enseignants</p>
                                <p class="title has-text-warning"><?= $teacherMembers ?></p>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Membres Actifs</p>
                                <p class="title has-text-success"><?= $activeMembers ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Répartition des membres -->
                    <div class="columns">
                        <div class="column is-6">
                            <div class="card">
                                <div class="card-header">
                                    <p class="card-header-title">
                                        <i class="fas fa-chart-pie"></i>
                                        Répartition par Type
                                    </p>
                                </div>
                                <div class="card-content">
                                    <div class="content">
                                        <?php if ($totalMembers > 0): ?>
                                            <p><strong>Étudiants :</strong> <?= $studentMembers ?> membres</p>
                                            <progress class="progress is-info" value="<?= $studentMembers ?>" max="<?= $totalMembers ?>"></progress>
                                            <small><?= round(($studentMembers / $totalMembers) * 100, 1) ?>%</small>
                                            
                                            <p><strong>Enseignants :</strong> <?= $teacherMembers ?> membres</p>
                                            <progress class="progress is-warning" value="<?= $teacherMembers ?>" max="<?= $totalMembers ?>"></progress>
                                            <small><?= round(($teacherMembers / $totalMembers) * 100, 1) ?>%</small>
                                            
                                            <?php if (isset($staffMembers) && $staffMembers > 0): ?>
                                                <p><strong>Personnel :</strong> <?= $staffMembers ?> membres</p>
                                                <progress class="progress is-success" value="<?= $staffMembers ?>" max="<?= $totalMembers ?>"></progress>
                                                <small><?= round(($staffMembers / $totalMembers) * 100, 1) ?>%</small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p>Aucun membre enregistré.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="column is-6">
                            <div class="card">
                                <div class="card-header">
                                    <p class="card-header-title">
                                        <i class="fas fa-chart-bar"></i>
                                        Activité des Membres
                                    </p>
                                </div>
                                <div class="card-content">
                                    <div class="content">
                                        <?php if ($totalMembers > 0): ?>
                                            <p><strong>Membres actifs :</strong> <?= $activeMembers ?> membres</p>
                                            <progress class="progress is-success" value="<?= $activeMembers ?>" max="<?= $totalMembers ?>"></progress>
                                            <small><?= round(($activeMembers / $totalMembers) * 100, 1) ?>%</small>
                                            
                                            <?php $inactiveMembers = $totalMembers - $activeMembers; ?>
                                            <p><strong>Membres inactifs :</strong> <?= $inactiveMembers ?> membres</p>
                                            <progress class="progress is-light" value="<?= $inactiveMembers ?>" max="<?= $totalMembers ?>"></progress>
                                            <small><?= round(($inactiveMembers / $totalMembers) * 100, 1) ?>%</small>
                                            
                                            <p><strong>Taux d'activité :</strong> <?= round(($activeMembers / $totalMembers) * 100, 1) ?>%</p>
                                        <?php else: ?>
                                            <p>Aucun membre enregistré.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau récapitulatif -->
                    <div class="columns">
                        <div class="column is-12">
                            <div class="card">
                                <div class="card-header">
                                    <p class="card-header-title">
                                        <i class="fas fa-table"></i>
                                        Récapitulatif des Membres
                                    </p>
                                </div>
                                <div class="card-content">
                                    <div class="table-container">
                                        <table class="table is-fullwidth is-striped">
                                            <thead>
                                                <tr>
                                                    <th>Type de Membre</th>
                                                    <th>Nombre</th>
                                                    <th>Pourcentage</th>
                                                    <th>Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <span class="tag is-info">Étudiants</span>
                                                    </td>
                                                    <td><strong><?= $studentMembers ?></strong></td>
                                                    <td>
                                                        <?= $totalMembers > 0 ? round(($studentMembers / $totalMembers) * 100, 1) : 0 ?>%
                                                    </td>
                                                    <td>
                                                        <span class="tag is-success">Actifs</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <span class="tag is-warning">Enseignants</span>
                                                    </td>
                                                    <td><strong><?= $teacherMembers ?></strong></td>
                                                    <td>
                                                        <?= $totalMembers > 0 ? round(($teacherMembers / $totalMembers) * 100, 1) : 0 ?>%
                                                    </td>
                                                    <td>
                                                        <span class="tag is-success">Actifs</span>
                                                    </td>
                                                </tr>
                                                <?php if (isset($staffMembers) && $staffMembers > 0): ?>
                                                <tr>
                                                    <td>
                                                        <span class="tag is-success">Personnel</span>
                                                    </td>
                                                    <td><strong><?= $staffMembers ?></strong></td>
                                                    <td>
                                                        <?= $totalMembers > 0 ? round(($staffMembers / $totalMembers) * 100, 1) : 0 ?>%
                                                    </td>
                                                    <td>
                                                        <span class="tag is-success">Actifs</span>
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                                <tr class="has-background-light">
                                                    <td><strong>Total</strong></td>
                                                    <td><strong><?= $totalMembers ?></strong></td>
                                                    <td><strong>100%</strong></td>
                                                    <td>
                                                        <span class="tag is-primary">Tous Types</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="field is-grouped">
                        <div class="control">
                            <a href="<?= base_url('admin/bibliotheque/members') ?>" class="button is-info">
                                <i class="fas fa-users"></i>
                                Gérer les Membres
                            </a>
                        </div>
                        <div class="control">
                            <a href="<?= base_url('admin/bibliotheque/members/create') ?>" class="button is-success">
                                <i class="fas fa-plus"></i>
                                Ajouter un Membre
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








