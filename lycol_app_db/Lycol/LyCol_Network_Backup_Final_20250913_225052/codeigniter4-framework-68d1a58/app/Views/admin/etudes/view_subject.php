<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column is-12">
            <!-- Breadcrumb -->
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/etudes') ?>">Études</a></li>
                    <li><a href="<?= base_url('admin/etudes/subjects') ?>">Matières</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Détails de la Matière</a></li>
                </ul>
            </nav>

            <!-- Header -->
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h1 class="title">Détails de la Matière</h1>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <a href="<?= base_url('admin/etudes/subjects/edit/' . $subject['id']) ?>" class="button is-warning">
                            <span class="icon">
                                <i class="fas fa-edit"></i>
                            </span>
                            <span>Modifier</span>
                        </a>
                        <a href="<?= base_url('admin/etudes/subjects') ?>" class="button is-info">
                            <span class="icon">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span>Retour</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Informations principales -->
            <div class="columns">
                <div class="column is-8">
                    <div class="card">
                        <header class="card-header">
                            <p class="card-header-title">
                                <span class="icon mr-2">
                                    <i class="fas fa-book"></i>
                                </span>
                                Informations de la Matière
                            </p>
                        </header>
                        <div class="card-content">
                            <div class="columns">
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Nom de la Matière</label>
                                        <p class="title is-4"><?= esc($subject['name']) ?></p>
                                    </div>
                                    
                                    <div class="field">
                                        <label class="label">Code</label>
                                        <p class="subtitle is-5"><?= esc($subject['code']) ?></p>
                                    </div>
                                    
                                    <div class="field">
                                        <label class="label">Coefficient</label>
                                        <p class="subtitle is-5"><?= $subject['coefficient'] ?? 'Non défini' ?></p>
                                    </div>
                                </div>
                                
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Heures par Semaine</label>
                                        <p class="subtitle is-5"><?= $subject['hours_per_week'] ?? 'Non défini' ?> heures</p>
                                    </div>
                                    
                                    <div class="field">
                                        <label class="label">Statut</label>
                                        <span class="tag <?= $subject['is_active'] ? 'is-success' : 'is-danger' ?>">
                                            <?= $subject['is_active'] ? 'Actif' : 'Inactif' ?>
                                        </span>
                                    </div>
                                    
                                    <div class="field">
                                        <label class="label">Date de Création</label>
                                        <p class="subtitle is-6"><?= date('d/m/Y H:i', strtotime($subject['created_at'])) ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($subject['description'])): ?>
                            <div class="field">
                                <label class="label">Description</label>
                                <div class="content">
                                    <p><?= esc($subject['description']) ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="column is-4">
                    <!-- Statistiques -->
                    <div class="card">
                        <header class="card-header">
                            <p class="card-header-title">
                                <span class="icon mr-2">
                                    <i class="fas fa-chart-bar"></i>
                                </span>
                                Statistiques
                            </p>
                        </header>
                        <div class="card-content">
                            <div class="level">
                                <div class="level-item has-text-centered">
                                    <div>
                                        <p class="heading">Classes</p>
                                        <p class="title"><?= count($classes) ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="level">
                                <div class="level-item has-text-centered">
                                    <div>
                                        <p class="heading">Assignations</p>
                                        <p class="title"><?= count($assignments) ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="level">
                                <div class="level-item has-text-centered">
                                    <div>
                                        <p class="heading">Emplois du Temps</p>
                                        <p class="title"><?= count($timetables) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Classes associées -->
            <?php if (!empty($classes)): ?>
            <div class="card mt-4">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon mr-2">
                            <i class="fas fa-chalkboard"></i>
                        </span>
                        Classes Associées
                    </p>
                </header>
                <div class="card-content">
                    <div class="table-container">
                        <table class="table is-fullwidth is-striped">
                            <thead>
                                <tr>
                                    <th>Classe</th>
                                    <th>Niveau</th>
                                    <th>Effectif</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classes as $class): ?>
                                <tr>
                                    <td><?= esc($class['name']) ?></td>
                                    <td><?= esc($class['level_name'] ?? 'N/A') ?></td>
                                    <td><?= $class['student_count'] ?? 0 ?> élèves</td>
                                    <td>
                                        <a href="<?= base_url('admin/etudes/classes/view/' . $class['id']) ?>" 
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
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Assignations -->
            <?php if (!empty($assignments)): ?>
            <div class="card mt-4">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon mr-2">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </span>
                        Assignations d'Enseignants
                    </p>
                </header>
                <div class="card-content">
                    <div class="table-container">
                        <table class="table is-fullwidth is-striped">
                            <thead>
                                <tr>
                                    <th>Enseignant</th>
                                    <th>Classe</th>
                                    <th>Année Académique</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignments as $assignment): ?>
                                <tr>
                                    <td><?= esc($assignment['teacher_name']) ?></td>
                                    <td><?= esc($assignment['class_name']) ?></td>
                                    <td><?= esc($assignment['academic_year']) ?></td>
                                    <td>
                                        <a href="<?= base_url('admin/etudes/assignments/view/' . $assignment['id']) ?>" 
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
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Emplois du temps -->
            <?php if (!empty($timetables)): ?>
            <div class="card mt-4">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon mr-2">
                            <i class="fas fa-clock"></i>
                        </span>
                        Emplois du Temps
                    </p>
                </header>
                <div class="card-content">
                    <div class="table-container">
                        <table class="table is-fullwidth is-striped">
                            <thead>
                                <tr>
                                    <th>Classe</th>
                                    <th>Jour</th>
                                    <th>Horaire</th>
                                    <th>Salle</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($timetables as $timetable): ?>
                                <tr>
                                    <td><?= esc($timetable['class_name']) ?></td>
                                    <td><?= esc($timetable['day_name']) ?></td>
                                    <td><?= $timetable['start_time'] ?> - <?= $timetable['end_time'] ?></td>
                                    <td><?= esc($timetable['room'] ?? 'N/A') ?></td>
                                    <td>
                                        <a href="<?= base_url('admin/etudes/timetable/view/' . $timetable['id']) ?>" 
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
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="level mt-4">
                <div class="level-left">
                    <div class="level-item">
                        <a href="<?= base_url('admin/etudes/subjects/edit/' . $subject['id']) ?>" class="button is-warning">
                            <span class="icon">
                                <i class="fas fa-edit"></i>
                            </span>
                            <span>Modifier cette Matière</span>
                        </a>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <a href="<?= base_url('admin/etudes/subjects') ?>" class="button is-light">
                            <span class="icon">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span>Retour à la Liste</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>


