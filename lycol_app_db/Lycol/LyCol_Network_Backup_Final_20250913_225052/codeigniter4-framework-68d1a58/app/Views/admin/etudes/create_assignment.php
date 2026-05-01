<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column is-12">
            <!-- Breadcrumb -->
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/etudes') ?>">Études</a></li>
                    <li><a href="<?= base_url('admin/etudes/assignments') ?>">Assignations</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Nouvelle Assignation</a></li>
                </ul>
            </nav>

            <!-- Header -->
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h1 class="title">Nouvelle Assignation</h1>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <a href="<?= base_url('admin/etudes/assignments') ?>" class="button is-info">
                            <span class="icon">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span>Retour</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="box">
                <form action="<?= base_url('admin/etudes/assignments/store') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="field">
                        <label class="label" for="teacher_id">Enseignant *</label>
                        <div class="control">
                            <div class="select <?= session('errors.teacher_id') ? 'is-danger' : '' ?> is-fullwidth">
                                <select id="teacher_id" name="teacher_id" required>
                                    <option value="">Sélectionner un enseignant</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?= $teacher['id'] ?>" <?= old('teacher_id') == $teacher['id'] ? 'selected' : '' ?>>
                                            <?= $teacher['first_name'] . ' ' . $teacher['last_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if (session('errors.teacher_id')): ?>
                            <p class="help is-danger"><?= session('errors.teacher_id') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label" for="subject_id">Matière *</label>
                        <div class="control">
                            <div class="select <?= session('errors.subject_id') ? 'is-danger' : '' ?> is-fullwidth">
                                <select id="subject_id" name="subject_id" required>
                                    <option value="">Sélectionner une matière</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?= $subject['id'] ?>" <?= old('subject_id') == $subject['id'] ? 'selected' : '' ?>>
                                            <?= $subject['name'] ?> (<?= $subject['code'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if (session('errors.subject_id')): ?>
                            <p class="help is-danger"><?= session('errors.subject_id') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label" for="class_id">Classe *</label>
                        <div class="control">
                            <div class="select <?= session('errors.class_id') ? 'is-danger' : '' ?> is-fullwidth">
                                <select id="class_id" name="class_id" required>
                                    <option value="">Sélectionner une classe</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?= $class['id'] ?>" <?= old('class_id') == $class['id'] ? 'selected' : '' ?>>
                                            <?= $class['name'] ?> (<?= $class['cycle_name'] ?? 'Cycle non défini' ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if (session('errors.class_id')): ?>
                            <p class="help is-danger"><?= session('errors.class_id') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label" for="academic_year">Année scolaire *</label>
                        <div class="control">
                            <input class="input <?= session('errors.academic_year') ? 'is-danger' : '' ?>" 
                                   type="text" 
                                   id="academic_year" 
                                   name="academic_year" 
                                   value="<?= old('academic_year', $current_academic_year ?? '2024-2025') ?>" 
                                   required>
                        </div>
                        <?php if (session('errors.academic_year')): ?>
                            <p class="help is-danger"><?= session('errors.academic_year') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label" for="hours_per_week">Heures par semaine</label>
                        <div class="control">
                            <input class="input <?= session('errors.hours_per_week') ? 'is-danger' : '' ?>" 
                                   type="number" 
                                   id="hours_per_week" 
                                   name="hours_per_week" 
                                   value="<?= old('hours_per_week') ?>" 
                                   min="0" 
                                   step="0.5">
                        </div>
                        <?php if (session('errors.hours_per_week')): ?>
                            <p class="help is-danger"><?= session('errors.hours_per_week') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label" for="start_date">Date de début</label>
                        <div class="control">
                            <input class="input <?= session('errors.start_date') ? 'is-danger' : '' ?>" 
                                   type="date" 
                                   id="start_date" 
                                   name="start_date" 
                                   value="<?= old('start_date') ?>">
                        </div>
                        <?php if (session('errors.start_date')): ?>
                            <p class="help is-danger"><?= session('errors.start_date') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label" for="end_date">Date de fin</label>
                        <div class="control">
                            <input class="input <?= session('errors.end_date') ? 'is-danger' : '' ?>" 
                                   type="date" 
                                   id="end_date" 
                                   name="end_date" 
                                   value="<?= old('end_date') ?>">
                        </div>
                        <?php if (session('errors.end_date')): ?>
                            <p class="help is-danger"><?= session('errors.end_date') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label" for="notes">Notes</label>
                        <div class="control">
                            <textarea class="textarea <?= session('errors.notes') ? 'is-danger' : '' ?>" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3"><?= old('notes') ?></textarea>
                        </div>
                        <?php if (session('errors.notes')): ?>
                            <p class="help is-danger"><?= session('errors.notes') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="checkbox">
                            <input type="checkbox" name="is_active" value="1" <?= old('is_active', 1) ? 'checked' : '' ?>>
                            Assignation active
                        </label>
                    </div>

                    <div class="field is-grouped">
                        <div class="control">
                            <button type="submit" class="button is-primary">
                                <span class="icon">
                                    <i class="fas fa-save"></i>
                                </span>
                                <span>Créer</span>
                            </button>
                        </div>
                        <div class="control">
                            <a href="<?= base_url('admin/etudes/assignments') ?>" class="button is-light">
                                <span class="icon">
                                    <i class="fas fa-times"></i>
                                </span>
                                <span>Annuler</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>


















