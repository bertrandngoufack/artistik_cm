<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/scolarite') ?>">Scolarité</a></li>
                    <li><a href="<?= base_url('admin/scolarite/students') ?>">Élèves</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Profil de l'Élève</a></li>
                </ul>
            </nav>

            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="fas fa-user"></i></span>
                        Profil de l'Élève
                    </p>
                    <div class="card-header-icon">
                        <a href="<?= base_url('admin/scolarite/students') ?>" class="button is-small">
                            <span class="icon"><i class="fas fa-arrow-left"></i></span>
                            <span>Retour</span>
                        </a>
                    </div>
                </header>
                <div class="card-content">
                    <div class="columns">
                        <div class="column is-6">
                            <h4 class="title is-4">Informations Personnelles</h4>
                            <table class="table is-fullwidth">
                                <tr>
                                    <td><strong>Matricule :</strong></td>
                                    <td><?= esc($student['matricule']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Nom :</strong></td>
                                    <td><?= esc($student['last_name']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Prénom :</strong></td>
                                    <td><?= esc($student['first_name']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Date de naissance :</strong></td>
                                    <td><?= date('d/m/Y', strtotime($student['date_of_birth'])) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Genre :</strong></td>
                                    <td><?= $student['gender'] === 'MALE' ? 'Masculin' : 'Féminin' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Classe actuelle :</strong></td>
                                    <td><?= esc($student['class_name'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Statut :</strong></td>
                                    <td>
                                        <span class="tag <?= $student['status'] === 'ACTIVE' ? 'is-success' : 'is-danger' ?>">
                                            <?= $student['status'] === 'ACTIVE' ? 'Actif' : 'Inactif' ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Date d'admission :</strong></td>
                                    <td><?= date('d/m/Y', strtotime($student['admission_date'])) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="column is-6">
                            <h4 class="title is-4">Statistiques</h4>
                            <div class="columns is-multiline">
                                <div class="column is-6">
                                    <div class="box has-text-centered">
                                        <p class="heading">Total Absences</p>
                                        <p class="title"><?= count($absences) ?></p>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="box has-text-centered">
                                        <p class="heading">Incidents Discipline</p>
                                        <p class="title"><?= count($discipline) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Absences -->
            <div class="card mt-4">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="fas fa-calendar-times"></i></span>
                        Historique des Absences
                    </p>
                </header>
                <div class="card-content">
                    <?php if (!empty($absences)): ?>
                        <table class="table is-fullwidth">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Motif</th>
                                    <th>Justifiée</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($absences as $absence): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($absence['date'])) ?></td>
                                    <td><?= esc($absence['reason']) ?></td>
                                    <td>
                                        <span class="tag <?= $absence['justified'] ? 'is-success' : 'is-danger' ?>">
                                            <?= $absence['justified'] ? 'Oui' : 'Non' ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="has-text-centered has-text-grey">Aucune absence enregistrée</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Incidents disciplinaires -->
            <div class="card mt-4">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="fas fa-exclamation-triangle"></i></span>
                        Incidents Disciplinaires
                    </p>
                </header>
                <div class="card-content">
                    <?php if (!empty($discipline)): ?>
                        <table class="table is-fullwidth">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Sanction</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($discipline as $incident): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($incident['incident_date'])) ?></td>
                                    <td>
                                        <?php
                                        switch($incident['incident_type']) {
                                            case 'MINOR': echo 'Mineur'; break;
                                            case 'MAJOR': echo 'Majeur'; break;
                                            case 'CRITICAL': echo 'Critique'; break;
                                            default: echo $incident['incident_type'];
                                        }
                                        ?>
                                    </td>
                                    <td><?= esc($incident['description']) ?></td>
                                    <td><?= esc($incident['sanction'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="tag <?= $incident['status'] === 'RESOLVED' ? 'is-success' : 'is-warning' ?>">
                                            <?= $incident['status'] === 'RESOLVED' ? 'Résolu' : 'En cours' ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="has-text-centered has-text-grey">Aucun incident disciplinaire enregistré</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
