<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <span class="icon"><i class="fas fa-book"></i></span>
                Matières de l'Enseignant
            </h1>
            <p class="subtitle">
                <?= esc($teacher['first_name']) ?> <?= esc($teacher['last_name']) ?> - <?= esc($teacher['specialization']) ?>
            </p>
        </div>
        <div class="column is-narrow">
            <div class="buttons">
                <a href="<?= base_url('admin/enseignants/show/' . $teacher['id']) ?>" class="button is-light">
                    <span class="icon"><i class="fas fa-user"></i></span>
                    <span>Profil</span>
                </a>
                <a href="<?= base_url('admin/enseignants') ?>" class="button is-light">
                    <span class="icon"><i class="fas fa-arrow-left"></i></span>
                    <span>Retour</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Assignation de nouvelles matières -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="fas fa-plus"></i></span>
                Assigner une Matière
            </p>
        </header>
        <div class="card-content">
            <?php if (session()->has('success')): ?>
                <div class="notification is-success">
                    <button class="delete"></button>
                    <?= session('success') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->has('error')): ?>
                <div class="notification is-danger">
                    <button class="delete"></button>
                    <?= session('error') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('admin/enseignants/assign-subject') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="teacher_id" value="<?= $teacher['id'] ?>">
                
                <div class="columns is-multiline">
                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Classe</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="class_id" required>
                                        <option value="">Sélectionner une classe</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?= $class['id'] ?>">
                                                <?= esc($class['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Matière</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="subject_id" required>
                                        <option value="">Sélectionner une matière</option>
                                        <?php foreach ($available_subjects as $subject): ?>
                                            <option value="<?= $subject['id'] ?>">
                                                <?= esc($subject['name']) ?> (<?= esc($subject['code']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="column is-4">
                        <div class="field">
                            <label class="label">&nbsp;</label>
                            <div class="control">
                                <button type="submit" class="button is-primary is-fullwidth">
                                    <span class="icon"><i class="fas fa-plus"></i></span>
                                    <span>Assigner</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Matières assignées -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="fas fa-list"></i></span>
                Matières Assignées
            </p>
        </header>
        <div class="card-content">
            <?php if (!empty($teacher_subjects)): ?>
                <div class="table-container">
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Matière</th>
                                <th>Code</th>
                                <th>Classe</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teacher_subjects as $subject): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($subject['subject_name']) ?></strong>
                                </td>
                                <td>
                                    <span class="tag is-info"><?= esc($subject['subject_code']) ?></span>
                                </td>
                                <td>
                                    <?= esc($subject['class_name']) ?>
                                </td>
                                <td>
                                    <form action="<?= base_url('admin/enseignants/remove-subject') ?>" method="post" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="class_id" value="<?= $subject['class_id'] ?>">
                                        <input type="hidden" name="subject_id" value="<?= $subject['subject_id'] ?>">
                                        <button type="submit" class="button is-small is-danger" 
                                                onclick="return confirm('Êtes-vous sûr de vouloir retirer cette matière ?')">
                                            <span class="icon"><i class="fas fa-trash"></i></span>
                                            <span>Retirer</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="has-text-centered has-text-grey">
                    <p>Aucune matière assignée à cet enseignant</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Supprimer les notifications
    document.querySelectorAll('.notification .delete').forEach(function(button) {
        button.addEventListener('click', function() {
            this.parentNode.remove();
        });
    });

    // Mise à jour dynamique des matières selon la classe sélectionnée
    const classSelect = document.querySelector('select[name="class_id"]');
    const subjectSelect = document.querySelector('select[name="subject_id"]');
    
    if (classSelect && subjectSelect) {
        classSelect.addEventListener('change', function() {
            const classId = this.value;
            if (classId) {
                // Ici vous pourriez ajouter une requête AJAX pour charger les matières de la classe
                // Pour l'instant, on garde toutes les matières disponibles
            }
        });
    }
});
</script>

<?= $this->endSection() ?>









