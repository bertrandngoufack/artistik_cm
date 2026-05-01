<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/scolarite') ?>">Scolarité</a></li>
                    <li><a href="<?= base_url('admin/scolarite/absences') ?>">Absences</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Détails de l'Absence</a></li>
                </ul>
            </nav>

            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="fas fa-calendar-times"></i></span>
                        Détails de l'Absence
                    </p>
                    <div class="card-header-icon">
                        <a href="<?= base_url('admin/scolarite/absences') ?>" class="button is-small">
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
                                    <td><?= esc($absence->matricule) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Nom :</strong></td>
                                    <td><?= esc($absence->last_name) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Prénom :</strong></td>
                                    <td><?= esc($absence->first_name) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Classe :</strong></td>
                                    <td>
                                        <?php if ($absence->class_name): ?>
                                            <span class="tag is-info"><?= esc($absence->class_name) ?></span>
                                        <?php else: ?>
                                            <span class="has-text-grey">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="column is-6">
                            <h4 class="title is-4">Détails de l'Absence</h4>
                            <table class="table is-fullwidth">
                                <tr>
                                    <td><strong>Date :</strong></td>
                                    <td>
                                        <strong><?= date('d/m/Y', strtotime($absence->date)) ?></strong>
                                        <br><small class="has-text-grey"><?= date('l', strtotime($absence->date)) ?></small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Justifiée :</strong></td>
                                    <td>
                                        <?php if ($absence->justified): ?>
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
                                    <td><strong>Enregistrée le :</strong></td>
                                    <td><?= date('d/m/Y à H:i', strtotime($absence->created_at)) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column">
                            <h4 class="title is-4">Motif de l'Absence</h4>
                            <div class="box">
                                <div class="content">
                                    <p><?= nl2br(esc($absence->reason)) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column">
                            <div class="buttons">
                                <a href="<?= base_url('admin/scolarite/absences/' . $absence->id . '/edit') ?>" class="button is-warning">
                                    <span class="icon"><i class="fas fa-edit"></i></span>
                                    <span>Modifier</span>
                                </a>
                                <a href="<?= base_url('admin/scolarite/students/' . $absence->student_id . '/view') ?>" class="button is-info">
                                    <span class="icon"><i class="fas fa-user"></i></span>
                                    <span>Voir l'élève</span>
                                </a>
                                <a href="<?= base_url('admin/scolarite/absences') ?>" class="button">
                                    <span class="icon"><i class="fas fa-list"></i></span>
                                    <span>Liste des absences</span>
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



















