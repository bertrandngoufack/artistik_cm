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
                    <li class="is-active"><a href="#" aria-current="page">Modifier l'Assignation</a></li>
                </ul>
            </nav>

            <!-- Header -->
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h1 class="title">Modifier l'Assignation</h1>
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
                <form action="<?= base_url('admin/etudes/assignments/' . $assignment['id'] . '/update') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="field">
                        <label class="label" for="teacher_id">Enseignant *</label>
                        <div class="control">
                            <div class="select <?= session('errors.teacher_id') ? 'is-danger' : '' ?> is-fullwidth">
                                <select id="teacher_id" name="teacher_id" required>
                                    <option value="">Sélectionner un enseignant</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?= $teacher['id'] ?>" <?= (old('teacher_id', $assignment['teacher_id']) == $teacher['id']) ? 'selected' : '' ?>>
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
                                        <option value="<?= $subject['id'] ?>" <?= (old('subject_id', $assignment['subject_id']) == $subject['id']) ? 'selected' : '' ?>>
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
                                        <option value="<?= $class['id'] ?>" <?= (old('class_id', $assignment['class_id']) == $class['id']) ? 'selected' : '' ?>>
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
                                   value="<?= old('academic_year', $assignment['academic_year']) ?>" 
                                   required>
                        </div>
                        <?php if (session('errors.academic_year')): ?>
                            <p class="help is-danger"><?= session('errors.academic_year') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label" for="is_principal">Enseignant principal</label>
                        <div class="control">
                            <label class="checkbox">
                                <input type="checkbox" name="is_principal" value="1" <?= (old('is_principal', $assignment['is_principal'] ?? 0)) ? 'checked' : '' ?>>
                                Cet enseignant est l'enseignant principal de cette classe
                            </label>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label" for="is_active">Statut</label>
                        <div class="control">
                            <label class="checkbox">
                                <input type="checkbox" name="is_active" value="1" <?= (old('is_active', $assignment['is_active'] ?? 1)) ? 'checked' : '' ?>>
                                Assignation active
                            </label>
                        </div>
                    </div>

                    <div class="field is-grouped">
                        <div class="control">
                            <button type="submit" class="button is-primary">
                                <span class="icon">
                                    <i class="fas fa-save"></i>
                                </span>
                                <span>Mettre à jour</span>
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





