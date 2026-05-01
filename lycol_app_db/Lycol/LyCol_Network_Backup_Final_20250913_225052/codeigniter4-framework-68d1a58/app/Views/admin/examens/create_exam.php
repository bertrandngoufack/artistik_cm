<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column is-12">
            <!-- Breadcrumb -->
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li><a href="<?= base_url('admin/examens') ?>">Examens</a></li>
                    <li><a href="<?= base_url('admin/examens/exams') ?>">Gestion des Examens</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Nouvel Examen</a></li>
                </ul>
            </nav>

            <!-- Header -->
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h1 class="title">Nouvel Examen</h1>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <a href="<?= base_url('admin/examens/exams') ?>" class="button is-info">
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
                <form action="<?= base_url('admin/examens/exams/store') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="field">
                        <label class="label" for="name">Nom de l'Examen *</label>
                        <div class="control">
                            <input class="input <?= session('errors.name') ? 'is-danger' : '' ?>" 
                                   type="text" 
                                   id="name" 
                                   name="name" 
                                   value="<?= old('name') ?>" 
                                   required>
                        </div>
                        <?php if (session('errors.name')): ?>
                            <p class="help is-danger"><?= session('errors.name') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label" for="type">Type d'Examen *</label>
                        <div class="control">
                            <div class="select <?= session('errors.exam_type') ? 'is-danger' : '' ?> is-fullwidth">
                                <select id="exam_type" name="exam_type" required>
                                    <option value="">Sélectionner le type</option>
                                    <option value="CONTINUOUS" <?= old('exam_type') == 'CONTINUOUS' ? 'selected' : '' ?>>Contrôle Continu</option>
                                    <option value="MIDTERM" <?= old('exam_type') == 'MIDTERM' ? 'selected' : '' ?>>Mi-trimestre</option>
                                    <option value="FINAL" <?= old('exam_type') == 'FINAL' ? 'selected' : '' ?>>Final</option>
                                    <option value="COMPETITIVE" <?= old('exam_type') == 'COMPETITIVE' ? 'selected' : '' ?>>Compétitif</option>
                                </select>
                            </div>
                        </div>
                        <?php if (session('errors.exam_type')): ?>
                            <p class="help is-danger"><?= session('errors.exam_type') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="columns">
                        <div class="column is-6">
                            <div class="field">
                                <label class="label" for="class_id">Classe *</label>
                                <div class="control">
                                    <div class="select <?= session('errors.class_id') ? 'is-danger' : '' ?> is-fullwidth">
                                        <select id="class_id" name="class_id" required>
                                            <option value="">Sélectionner une classe</option>
                                            <?php if (isset($classes)): ?>
                                                <?php foreach ($classes as $class): ?>
                                                    <option value="<?= $class['id'] ?>" <?= old('class_id') == $class['id'] ? 'selected' : '' ?>>
                                                        <?= $class['name'] ?>
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

                    </div>

                    <div class="columns">
                        <div class="column is-4">
                            <div class="field">
                                <label class="label" for="exam_date">Date de l'Examen *</label>
                                <div class="control">
                                    <input class="input <?= session('errors.exam_date') ? 'is-danger' : '' ?>" 
                                           type="date" 
                                           id="exam_date" 
                                           name="exam_date" 
                                           value="<?= old('exam_date') ?>" 
                                           required>
                                </div>
                                <?php if (session('errors.exam_date')): ?>
                                    <p class="help is-danger"><?= session('errors.exam_date') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="column is-4">
                            <div class="field">
                                <label class="label" for="total_marks">Note Maximale *</label>
                                <div class="control">
                                    <input class="input <?= session('errors.total_marks') ? 'is-danger' : '' ?>" 
                                           type="number" 
                                           id="total_marks" 
                                           name="total_marks" 
                                           value="<?= old('total_marks', 20) ?>" 
                                           min="1" 
                                           max="100" 
                                           required>
                                </div>
                                <?php if (session('errors.total_marks')): ?>
                                    <p class="help is-danger"><?= session('errors.total_marks') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="column is-4">
                            <div class="field">
                                <label class="label" for="coefficient">Coefficient</label>
                                <div class="control">
                                    <input class="input <?= session('errors.coefficient') ? 'is-danger' : '' ?>" 
                                           type="number" 
                                           id="coefficient" 
                                           name="coefficient" 
                                           value="<?= old('coefficient', 1.0) ?>" 
                                           min="0.1" 
                                           max="10" 
                                           step="0.1">
                                </div>
                                <?php if (session('errors.coefficient')): ?>
                                    <p class="help is-danger"><?= session('errors.coefficient') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label" for="description">Description</label>
                        <div class="control">
                            <textarea class="textarea <?= session('errors.description') ? 'is-danger' : '' ?>" 
                                      id="description" 
                                      name="description" 
                                      rows="3"><?= old('description') ?></textarea>
                        </div>
                        <?php if (session('errors.description')): ?>
                            <p class="help is-danger"><?= session('errors.description') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field is-grouped">
                        <div class="control">
                            <button type="submit" class="button is-primary">
                                <span class="icon">
                                    <i class="fas fa-save"></i>
                                </span>
                                <span>Créer l'Examen</span>
                            </button>
                        </div>
                        <div class="control">
                            <a href="<?= base_url('admin/examens/exams') ?>" class="button is-light">
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
