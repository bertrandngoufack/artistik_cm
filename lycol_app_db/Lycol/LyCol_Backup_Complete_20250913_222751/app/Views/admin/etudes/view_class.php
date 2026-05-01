<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column is-12">
            <!-- Breadcrumb -->
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/etudes') ?>">Études</a></li>
                    <li><a href="<?= base_url('admin/etudes/classes') ?>">Classes</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Détails de la Classe</a></li>
                </ul>
            </nav>

            <!-- Header -->
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h1 class="title">Détails de la Classe</h1>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <a href="<?= base_url('admin/etudes/classes/' . $class['id'] . '/edit') ?>" class="button is-warning">
                            <span class="icon">
                                <i class="fas fa-edit"></i>
                            </span>
                            <span>Modifier</span>
                        </a>
                        <a href="<?= base_url('admin/etudes/classes') ?>" class="button is-info ml-2">
                            <span class="icon">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span>Retour</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Informations de la classe -->
            <div class="box">
                <h2 class="title is-4"><?= $class['name'] ?></h2>
                
                <div class="columns">
                    <div class="column is-6">
                        <table class="table is-fullwidth">
                            <tbody>
                                <tr>
                                    <td><strong>Code :</strong></td>
                                    <td><?= $class['code'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Cycle :</strong></td>
                                    <td><?= $class['cycle_name'] ?? 'Non défini' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Niveau :</strong></td>
                                    <td><?= $class['level'] ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Capacité :</strong></td>
                                    <td><?= $class['capacity'] ?> élèves</td>
                                </tr>
                                <tr>
                                    <td><strong>Statut :</strong></td>
                                    <td>
                                        <span class="tag <?= $class['is_active'] ? 'is-success' : 'is-danger' ?>">
                                            <?= $class['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Description</label>
                            <div class="content">
                                <p><?= $class['description'] ?: 'Aucune description disponible' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="columns">
                <div class="column is-3">
                    <div class="box has-text-centered">
                        <p class="heading">Élèves</p>
                        <p class="title"><?= count($students) ?></p>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="box has-text-centered">
                        <p class="heading">Assignations</p>
                        <p class="title"><?= count($assignments) ?></p>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="box has-text-centered">
                        <p class="heading">Emploi du temps</p>
                        <p class="title"><?= $timetable ? 'Disponible' : 'Non défini' ?></p>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="box has-text-centered">
                        <p class="heading">Taux de remplissage</p>
                        <p class="title"><?= $class['capacity'] > 0 ? round((count($students) / $class['capacity']) * 100, 1) : 0 ?>%</p>
                    </div>
                </div>
            </div>

            <!-- Élèves de la classe -->
            <div class="box">
                <h3 class="title is-5">Élèves de la classe</h3>
                
                <?php if (!empty($students)): ?>
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Matricule</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Genre</th>
                                <th>Date de naissance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?= $student['matricule'] ?></td>
                                    <td><?= $student['last_name'] ?></td>
                                    <td><?= $student['first_name'] ?></td>
                                    <td><?= $student['gender'] ?></td>
                                    <td><?= $student['date_of_birth'] ?></td>
                                    <td>
                                        <a href="<?= base_url('admin/scolarite/students/' . $student['id'] . '/view') ?>" 
                                           class="button is-small is-info">
                                            <span class="icon">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="notification is-info">
                        <p>Aucun élève inscrit dans cette classe.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Assignations des enseignants -->
            <div class="box">
                <h3 class="title is-5">Assignations des enseignants</h3>
                
                <?php if (!empty($assignments)): ?>
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Enseignant</th>
                                <th>Matière</th>
                                <th>Année scolaire</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $assignment): ?>
                                <tr>
                                    <td><?= $assignment['teacher_name'] ?? 'Non défini' ?></td>
                                    <td><?= $assignment['subject_name'] ?? 'Non défini' ?></td>
                                    <td><?= $assignment['academic_year'] ?? 'Non défini' ?></td>
                                    <td>
                                        <a href="<?= base_url('admin/etudes/assignments/' . $assignment['id'] . '/edit') ?>" 
                                           class="button is-small is-warning">
                                            <span class="icon">
                                                <i class="fas fa-edit"></i>
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="notification is-info">
                        <p>Aucune assignation d'enseignant pour cette classe.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Emploi du temps -->
            <?php if ($timetable): ?>
                <div class="box">
                    <h3 class="title is-5">Emploi du temps</h3>
                    <p>L'emploi du temps de cette classe est disponible.</p>
                    <a href="<?= base_url('admin/etudes/timetable/class/' . $class['id']) ?>" 
                       class="button is-primary">
                        <span class="icon">
                            <i class="fas fa-calendar"></i>
                        </span>
                        <span>Voir l'emploi du temps</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>



















