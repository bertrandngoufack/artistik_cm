<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <h1 class="title">Module Études</h1>
    
    <!-- Statistiques générales -->
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="box has-background-primary has-text-white">
                <h4 class="title is-4 has-text-white">Total Classes</h4>
                <p class="title is-2 has-text-white"><?= $total_classes ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-info has-text-white">
                <h4 class="title is-4 has-text-white">Total Matières</h4>
                <p class="title is-2 has-text-white"><?= $total_subjects ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-success has-text-white">
                <h4 class="title is-4 has-text-white">Total Enseignants</h4>
                <p class="title is-2 has-text-white"><?= $total_teachers ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-warning has-text-white">
                <h4 class="title is-4 has-text-white">Effectif Total</h4>
                <p class="title is-2 has-text-white"><?= $total_students ?? 0 ?></p>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="buttons mb-4">
        <a href="/admin/etudes/classes" class="button is-primary">
            <span class="icon"><i class="fas fa-chalkboard"></i></span>
            <span>Gestion des Classes</span>
        </a>
        <a href="/admin/etudes/subjects" class="button is-info">
            <span class="icon"><i class="fas fa-book"></i></span>
            <span>Matières</span>
        </a>
        <a href="/admin/etudes/timetables" class="button is-success">
            <span class="icon"><i class="fas fa-calendar-alt"></i></span>
            <span>Emplois du Temps</span>
        </a>
        <a href="/admin/etudes/assignments" class="button is-warning">
            <span class="icon"><i class="fas fa-user-tie"></i></span>
            <span>Assignations</span>
        </a>
    </div>

    <!-- Classes par niveau -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">Classes par Niveau</p>
        </header>
        <div class="card-content">
            <table class="table is-fullwidth">
                <thead>
                    <tr>
                        <th>Niveau</th>
                        <th>Classes</th>
                        <th>Effectif</th>
                        <th>Enseignant Principal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($classes_by_level) && !empty($classes_by_level)): ?>
                        <?php foreach ($classes_by_level as $level): ?>
                        <tr>
                            <td><?= $level->level_name ?></td>
                            <td><?= $level->class_count ?></td>
                            <td><?= $level->total_students ?></td>
                            <td><?= $level->teacher_name ?? 'Non assigné' ?></td>
                            <td>
                                <a href="/admin/etudes/classes/level/<?= $level->level_id ?>" class="button is-small is-info">
                                    <span class="icon"><i class="fas fa-eye"></i></span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="has-text-centered">Aucune classe trouvée</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

