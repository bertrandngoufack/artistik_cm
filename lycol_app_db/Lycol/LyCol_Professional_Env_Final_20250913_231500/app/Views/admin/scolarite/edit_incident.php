<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/scolarite') ?>">Scolarité</a></li>
                    <li><a href="<?= base_url('admin/scolarite/discipline') ?>">Discipline</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Modifier l'Incident</a></li>
                </ul>
            </nav>

            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="fas fa-edit"></i></span>
                        Modifier l'Incident Disciplinaire
                    </p>
                    <div class="card-header-icon">
                        <a href="<?= base_url('admin/scolarite/discipline') ?>" class="button is-small">
                            <span class="icon"><i class="fas fa-arrow-left"></i></span>
                            <span>Retour</span>
                        </a>
                    </div>
                </header>
                <div class="card-content">
                    <form action="<?= base_url('admin/scolarite/discipline/' . $incident->id . '/update') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Élève *</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="student_id" required>
                                                <option value="">Sélectionner un élève</option>
                                                <?php foreach ($students as $student): ?>
                                                    <option value="<?= $student->id ?>" <?= (old('student_id', $incident->student_id) == $student->id) ? 'selected' : '' ?>>
                                                        <?= esc($student->matricule) ?> - <?= esc($student->first_name) ?> <?= esc($student->last_name) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <?php if (session()->getFlashdata('errors.student_id')): ?>
                                        <p class="help is-danger"><?= session()->getFlashdata('errors.student_id') ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="field">
                                    <label class="label">Type d'Incident *</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="incident_type" required>
                                                <option value="">Sélectionner le type</option>
                                                <option value="MINOR" <?= (old('incident_type', $incident->incident_type) == 'MINOR') ? 'selected' : '' ?>>Mineur</option>
                                                <option value="MAJOR" <?= (old('incident_type', $incident->incident_type) == 'MAJOR') ? 'selected' : '' ?>>Majeur</option>
                                                <option value="CRITICAL" <?= (old('incident_type', $incident->incident_type) == 'CRITICAL') ? 'selected' : '' ?>>Critique</option>
                                            </select>
                                        </div>
                                    </div>
                                    <?php if (session()->getFlashdata('errors.incident_type')): ?>
                                        <p class="help is-danger"><?= session()->getFlashdata('errors.incident_type') ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="field">
                                    <label class="label">Date de l'Incident *</label>
                                    <div class="control">
                                        <input class="input" type="date" name="incident_date" value="<?= old('incident_date', $incident->incident_date) ?>" required>
                                    </div>
                                    <?php if (session()->getFlashdata('errors.incident_date')): ?>
                                        <p class="help is-danger"><?= session()->getFlashdata('errors.incident_date') ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="field">
                                    <label class="label">Heure de l'Incident</label>
                                    <div class="control">
                                        <input class="input" type="time" name="incident_time" value="<?= old('incident_time', $incident->incident_time) ?>">
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Lieu</label>
                                    <div class="control">
                                        <input class="input" type="text" name="location" value="<?= old('location', $incident->location) ?>" placeholder="Ex: Salle de classe, Cour de récréation...">
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Sanction Appliquée *</label>
                                    <div class="control">
                                        <textarea class="textarea" name="sanction" placeholder="Décrivez la sanction appliquée..." required><?= old('sanction', $incident->sanction) ?></textarea>
                                    </div>
                                    <?php if (session()->getFlashdata('errors.sanction')): ?>
                                        <p class="help is-danger"><?= session()->getFlashdata('errors.sanction') ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="field">
                                    <label class="label">Durée de la Sanction</label>
                                    <div class="control">
                                        <input class="input" type="text" name="sanction_duration" value="<?= old('sanction_duration', $incident->sanction_duration) ?>" placeholder="Ex: 1 heure, 1 jour, 1 semaine...">
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Témoins</label>
                                    <div class="control">
                                        <textarea class="textarea" name="witnesses" placeholder="Listez les témoins de l'incident..."><?= old('witnesses', $incident->witnesses) ?></textarea>
                                    </div>
                                </div>

                                <div class="field">
                                    <div class="control">
                                        <label class="checkbox">
                                            <input type="checkbox" name="parent_notified" value="1" <?= (old('parent_notified', $incident->parent_notified) ? 'checked' : '') ?>>
                                            Parents notifiés
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Description de l'Incident *</label>
                            <div class="control">
                                <textarea class="textarea" name="description" rows="6" placeholder="Décrivez en détail l'incident disciplinaire..." required><?= old('description', $incident->description) ?></textarea>
                            </div>
                            <?php if (session()->getFlashdata('errors.description')): ?>
                                <p class="help is-danger"><?= session()->getFlashdata('errors.description') ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <span class="icon"><i class="fas fa-save"></i></span>
                                    <span>Mettre à Jour l'Incident</span>
                                </button>
                            </div>
                            <div class="control">
                                <a href="<?= base_url('admin/scolarite/discipline/' . $incident->id . '/view') ?>" class="button is-info">
                                    <span class="icon"><i class="fas fa-eye"></i></span>
                                    <span>Voir les Détails</span>
                                </a>
                            </div>
                            <div class="control">
                                <a href="<?= base_url('admin/scolarite/discipline') ?>" class="button is-light">
                                    <span class="icon"><i class="fas fa-times"></i></span>
                                    <span>Annuler</span>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
