<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/scolarite') ?>">Scolarité</a></li>
                    <li><a href="<?= base_url('admin/scolarite/discipline') ?>">Discipline</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Détails de l'Incident</a></li>
                </ul>
            </nav>

            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="fas fa-exclamation-triangle"></i></span>
                        Détails de l'Incident Disciplinaire
                    </p>
                    <div class="card-header-icon">
                        <a href="<?= base_url('admin/scolarite/discipline') ?>" class="button is-small">
                            <span class="icon"><i class="fas fa-arrow-left"></i></span>
                            <span>Retour</span>
                        </a>
                    </div>
                </header>
                <div class="card-content">
                    <div class="columns">
                        <div class="column is-6">
                            <h4 class="title is-4">Informations de l'Élève</h4>
                            <table class="table is-fullwidth">
                                <tr>
                                    <td><strong>Matricule :</strong></td>
                                    <td><?= esc($incident->matricule) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Nom :</strong></td>
                                    <td><?= esc($incident->last_name) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Prénom :</strong></td>
                                    <td><?= esc($incident->first_name) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Classe :</strong></td>
                                    <td>
                                        <?php if ($incident->class_name): ?>
                                            <span class="tag is-info"><?= esc($incident->class_name) ?></span>
                                        <?php else: ?>
                                            <span class="has-text-grey">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="column is-6">
                            <h4 class="title is-4">Détails de l'Incident</h4>
                            <table class="table is-fullwidth">
                                <tr>
                                    <td><strong>Date :</strong></td>
                                    <td>
                                        <strong><?= date('d/m/Y', strtotime($incident->incident_date)) ?></strong>
                                        <?php if ($incident->incident_time): ?>
                                            <br><small class="has-text-grey"><?= date('H:i', strtotime($incident->incident_time)) ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Type :</strong></td>
                                    <td>
                                        <?php
                                        $typeColors = [
                                            'MINOR' => 'is-warning',
                                            'MAJOR' => 'is-danger',
                                            'CRITICAL' => 'is-black'
                                        ];
                                        $typeLabels = [
                                            'MINOR' => 'Mineur',
                                            'MAJOR' => 'Majeur',
                                            'CRITICAL' => 'Critique'
                                        ];
                                        ?>
                                        <span class="tag <?= $typeColors[$incident->incident_type] ?? 'is-light' ?>">
                                            <?= $typeLabels[$incident->incident_type] ?? $incident->incident_type ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Lieu :</strong></td>
                                    <td><?= esc($incident->location ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Parents notifiés :</strong></td>
                                    <td>
                                        <?php if ($incident->parent_notified): ?>
                                            <span class="tag is-success">
                                                <span class="icon"><i class="fas fa-check"></i></span>
                                                <span>Oui</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="tag is-danger">
                                                <span class="icon"><i class="fas fa-times"></i></span>
                                                <span>Non</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Enregistré le :</strong></td>
                                    <td><?= date('d/m/Y à H:i', strtotime($incident->created_at)) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column">
                            <h4 class="title is-4">Description de l'Incident</h4>
                            <div class="box">
                                <div class="content">
                                    <p><?= nl2br(esc($incident->description)) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column is-6">
                            <h4 class="title is-4">Sanction Appliquée</h4>
                            <div class="box">
                                <div class="content">
                                    <p><strong>Sanction :</strong> <?= esc($incident->sanction) ?></p>
                                    <?php if ($incident->sanction_duration): ?>
                                        <p><strong>Durée :</strong> <?= esc($incident->sanction_duration) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="column is-6">
                            <h4 class="title is-4">Témoins</h4>
                            <div class="box">
                                <div class="content">
                                    <?php if ($incident->witnesses): ?>
                                        <p><?= nl2br(esc($incident->witnesses)) ?></p>
                                    <?php else: ?>
                                        <p class="has-text-grey">Aucun témoin mentionné</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column">
                            <div class="buttons">
                                <a href="<?= base_url('admin/scolarite/discipline/' . $incident->id . '/edit') ?>" class="button is-warning">
                                    <span class="icon"><i class="fas fa-edit"></i></span>
                                    <span>Modifier</span>
                                </a>
                                <a href="<?= base_url('admin/scolarite/students/' . $incident->student_id . '/view') ?>" class="button is-info">
                                    <span class="icon"><i class="fas fa-user"></i></span>
                                    <span>Voir l'élève</span>
                                </a>
                                <?php if (!$incident->parent_notified): ?>
                                    <a href="<?= base_url('admin/scolarite/discipline/notify/' . $incident->id) ?>" class="button is-success">
                                        <span class="icon"><i class="fas fa-bell"></i></span>
                                        <span>Notifier les parents</span>
                                    </a>
                                <?php endif; ?>
                                <a href="<?= base_url('admin/scolarite/discipline') ?>" class="button">
                                    <span class="icon"><i class="fas fa-list"></i></span>
                                    <span>Liste des incidents</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>



















