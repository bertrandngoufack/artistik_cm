<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li><a href="<?= base_url('admin/etudes') ?>">Études</a></li>
                    <li><a href="<?= base_url('admin/etudes/timetable') ?>">Emploi du Temps</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Nouvel Emploi du Temps</a></li>
                </ul>
            </nav>
            
            <h1 class="title has-text-primary">
                <i class="fas fa-calendar-plus"></i>
                📅 Nouvel Emploi du Temps
            </h1>
            <p class="subtitle">Ajouter un nouveau cours à l'emploi du temps</p>
        </div>
        <div class="column is-narrow">
            <a href="<?= base_url('admin/etudes/timetable') ?>" class="button is-info">
                <span class="icon">
                    <i class="fas fa-arrow-left"></i>
                </span>
                <span>Retour</span>
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="notification is-success">
            <button class="delete" onclick="this.parentElement.remove()"></button>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="notification is-danger">
            <button class="delete" onclick="this.parentElement.remove()"></button>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="fas fa-plus"></i></span>
                Informations du Cours
            </p>
        </header>
        <div class="card-content">
            <form action="<?= base_url('admin/etudes/timetable/store') ?>" method="POST">
                <?= csrf_field() ?>
                
                <div class="columns is-multiline">
                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Classe *</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="class_id" required>
                                        <option value="">Sélectionner une classe</option>
                                        <?php if (!empty($classes)): ?>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?= $class['id'] ?>" <?= old('class_id') == $class['id'] ? 'selected' : '' ?>>
                                                    <?= esc($class['name']) ?> (<?= esc($class['code']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <?php if (session('errors.class_id')): ?>
                                <p class="help is-danger"><?= session('errors.class_id') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Matière *</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="subject_id" required>
                                        <option value="">Sélectionner une matière</option>
                                        <?php if (!empty($subjects)): ?>
                                            <?php foreach ($subjects as $subject): ?>
                                                <option value="<?= $subject['id'] ?>" <?= old('subject_id') == $subject['id'] ? 'selected' : '' ?>>
                                                    <?= esc($subject['name']) ?> (<?= esc($subject['code']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <?php if (session('errors.subject_id')): ?>
                                <p class="help is-danger"><?= session('errors.subject_id') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Enseignant</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="teacher_id">
                                        <option value="">Sélectionner un enseignant</option>
                                        <?php if (!empty($teachers)): ?>
                                            <?php foreach ($teachers as $teacher): ?>
                                                <option value="<?= $teacher['id'] ?>" <?= old('teacher_id') == $teacher['id'] ? 'selected' : '' ?>>
                                                    <?= esc($teacher['first_name'] . ' ' . $teacher['last_name']) ?> 
                                                    (<?= esc($teacher['specialization'] ?? 'Non spécifiée') ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <?php if (session('errors.teacher_id')): ?>
                                <p class="help is-danger"><?= session('errors.teacher_id') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Salle</label>
                            <div class="control">
                                <input class="input <?= session('errors.room') ? 'is-danger' : '' ?>" 
                                       type="text" 
                                       name="room" 
                                       value="<?= old('room') ?>" 
                                       placeholder="Ex: Salle 101, Labo Sciences">
                            </div>
                            <?php if (session('errors.room')): ?>
                                <p class="help is-danger"><?= session('errors.room') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Jour de la semaine *</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="day_of_week" required>
                                        <option value="">Sélectionner un jour</option>
                                        <option value="1" <?= old('day_of_week') == '1' ? 'selected' : '' ?>>Lundi</option>
                                        <option value="2" <?= old('day_of_week') == '2' ? 'selected' : '' ?>>Mardi</option>
                                        <option value="3" <?= old('day_of_week') == '3' ? 'selected' : '' ?>>Mercredi</option>
                                        <option value="4" <?= old('day_of_week') == '4' ? 'selected' : '' ?>>Jeudi</option>
                                        <option value="5" <?= old('day_of_week') == '5' ? 'selected' : '' ?>>Vendredi</option>
                                        <option value="6" <?= old('day_of_week') == '6' ? 'selected' : '' ?>>Samedi</option>
                                    </select>
                                </div>
                            </div>
                            <?php if (session('errors.day_of_week')): ?>
                                <p class="help is-danger"><?= session('errors.day_of_week') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Heure de début *</label>
                            <div class="control">
                                <input class="input <?= session('errors.start_time') ? 'is-danger' : '' ?>" 
                                       type="time" 
                                       name="start_time" 
                                       value="<?= old('start_time') ?>" 
                                       required>
                            </div>
                            <?php if (session('errors.start_time')): ?>
                                <p class="help is-danger"><?= session('errors.start_time') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Heure de fin *</label>
                            <div class="control">
                                <input class="input <?= session('errors.end_time') ? 'is-danger' : '' ?>" 
                                       type="time" 
                                       name="end_time" 
                                       value="<?= old('end_time') ?>" 
                                       required>
                            </div>
                            <?php if (session('errors.end_time')): ?>
                                <p class="help is-danger"><?= session('errors.end_time') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <div class="control">
                        <button type="submit" class="button is-primary is-fullwidth">
                            <span class="icon">
                                <i class="fas fa-save"></i>
                            </span>
                            <span>Enregistrer le Cours</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Informations et conseils -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="fas fa-info-circle"></i></span>
                Informations et Conseils
            </p>
        </header>
        <div class="card-content">
            <div class="content">
                <div class="columns">
                    <div class="column is-6">
                        <h4 class="title is-5">⚠️ Vérifications Automatiques</h4>
                        <ul>
                            <li><strong>Conflits d'horaires :</strong> Le système vérifie automatiquement les chevauchements</li>
                            <li><strong>Disponibilité des enseignants :</strong> Vérification des conflits pour les enseignants</li>
                            <li><strong>Capacité des salles :</strong> Vérification de la disponibilité des salles</li>
                            <li><strong>Limites horaires :</strong> Respect des horaires de l'établissement</li>
                        </ul>
                    </div>
                    <div class="column is-6">
                        <h4 class="title is-5">💡 Bonnes Pratiques</h4>
                        <ul>
                            <li>Planifiez les matières principales en début de journée</li>
                            <li>Évitez les cours trop longs (max 2h consécutives)</li>
                            <li>Prévoyez des pauses entre les cours</li>
                            <li>Équilibrez la charge de travail par jour</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation côté client pour les heures
    const startTimeInput = document.querySelector('input[name="start_time"]');
    const endTimeInput = document.querySelector('input[name="end_time"]');
    
    function validateTime() {
        if (startTimeInput.value && endTimeInput.value) {
            if (startTimeInput.value >= endTimeInput.value) {
                endTimeInput.setCustomValidity('L\'heure de fin doit être après l\'heure de début');
            } else {
                endTimeInput.setCustomValidity('');
            }
        }
    }
    
    startTimeInput.addEventListener('change', validateTime);
    endTimeInput.addEventListener('change', validateTime);
    
    // Validation de la durée (max 4h)
    endTimeInput.addEventListener('change', function() {
        if (startTimeInput.value && this.value) {
            const start = new Date(`2000-01-01T${startTimeInput.value}`);
            const end = new Date(`2000-01-01T${this.value}`);
            const diffHours = (end - start) / (1000 * 60 * 60);
            
            if (diffHours > 4) {
                this.setCustomValidity('La durée maximale d\'un cours est de 4 heures');
            } else {
                this.setCustomValidity('');
            }
        }
    });
});
</script>

<?= $this->endSection() ?>








