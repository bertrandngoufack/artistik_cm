<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <span class="icon"><i class="fas fa-edit"></i></span>
                Modifier Enseignant
            </h1>
        </div>
        <div class="column is-narrow">
            <div class="buttons">
                <a href="<?= base_url('admin/enseignants/show/' . $teacher['id']) ?>" class="button is-light">
                    <span class="icon"><i class="fas fa-eye"></i></span>
                    <span>Voir le profil</span>
                </a>
                <a href="<?= base_url('admin/enseignants') ?>" class="button is-light">
                    <span class="icon"><i class="fas fa-arrow-left"></i></span>
                    <span>Retour</span>
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="fas fa-user-edit"></i></span>
                Informations de l'Enseignant
            </p>
        </header>
        <div class="card-content">
            <?php if (session()->has('errors')): ?>
                <div class="notification is-danger">
                    <button class="delete"></button>
                    <ul>
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('admin/enseignants/update/' . $teacher['id']) ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="columns is-multiline">
                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Prénom *</label>
                            <div class="control">
                                <input class="input <?= session('errors.first_name') ? 'is-danger' : '' ?>" 
                                       type="text" 
                                       name="first_name" 
                                       value="<?= old('first_name', $teacher['first_name']) ?>" 
                                       required>
                            </div>
                            <?php if (session('errors.first_name')): ?>
                                <p class="help is-danger"><?= session('errors.first_name') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Nom *</label>
                            <div class="control">
                                <input class="input <?= session('errors.last_name') ? 'is-danger' : '' ?>" 
                                       type="text" 
                                       name="last_name" 
                                       value="<?= old('last_name', $teacher['last_name']) ?>" 
                                       required>
                            </div>
                            <?php if (session('errors.last_name')): ?>
                                <p class="help is-danger"><?= session('errors.last_name') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Email *</label>
                            <div class="control">
                                <input class="input <?= session('errors.email') ? 'is-danger' : '' ?>" 
                                       type="email" 
                                       name="email" 
                                       value="<?= old('email', $teacher['email']) ?>" 
                                       required>
                            </div>
                            <?php if (session('errors.email')): ?>
                                <p class="help is-danger"><?= session('errors.email') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Téléphone</label>
                            <div class="control">
                                <input class="input <?= session('errors.phone') ? 'is-danger' : '' ?>" 
                                       type="tel" 
                                       name="phone" 
                                       value="<?= old('phone', $teacher['phone']) ?>" 
                                       placeholder="+237 6 12 34 56 78">
                            </div>
                            <?php if (session('errors.phone')): ?>
                                <p class="help is-danger"><?= session('errors.phone') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Spécialisation</label>
                            <div class="control">
                                <div class="select is-fullwidth <?= session('errors.specialization') ? 'is-danger' : '' ?>">
                                    <select name="specialization">
                                        <option value="">Sélectionner une spécialisation</option>
                                        <?php foreach ($specializations as $spec): ?>
                                            <option value="<?= $spec ?>" <?= old('specialization', $teacher['specialization']) === $spec ? 'selected' : '' ?>>
                                                <?= $spec ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <?php if (session('errors.specialization')): ?>
                                <p class="help is-danger"><?= session('errors.specialization') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Qualification</label>
                            <div class="control">
                                <div class="select is-fullwidth <?= session('errors.qualification') ? 'is-danger' : '' ?>">
                                    <select name="qualification">
                                        <option value="">Sélectionner une qualification</option>
                                        <?php foreach ($qualifications as $qual): ?>
                                            <option value="<?= $qual ?>" <?= old('qualification', $teacher['qualification']) === $qual ? 'selected' : '' ?>>
                                                <?= $qual ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <?php if (session('errors.qualification')): ?>
                                <p class="help is-danger"><?= session('errors.qualification') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Date d'embauche</label>
                            <div class="control">
                                <input class="input <?= session('errors.hire_date') ? 'is-danger' : '' ?>" 
                                       type="date" 
                                       name="hire_date" 
                                       value="<?= old('hire_date', $teacher['hire_date']) ?>">
                            </div>
                            <?php if (session('errors.hire_date')): ?>
                                <p class="help is-danger"><?= session('errors.hire_date') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Compte utilisateur</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="user_id">
                                        <option value="">Aucun compte associé</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?= $user['id'] ?>" <?= old('user_id', $teacher['user_id']) == $user['id'] ? 'selected' : '' ?>>
                                                <?= esc($user['username']) ?> (<?= esc($user['email']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Statut</label>
                            <div class="control">
                                <label class="checkbox">
                                    <input type="checkbox" name="is_active" value="1" <?= old('is_active', $teacher['is_active']) ? 'checked' : '' ?>>
                                    Enseignant actif
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="field is-grouped">
                    <div class="control">
                        <button type="submit" class="button is-primary">
                            <span class="icon"><i class="fas fa-save"></i></span>
                            <span>Enregistrer les modifications</span>
                        </button>
                    </div>
                    <div class="control">
                        <a href="<?= base_url('admin/enseignants/show/' . $teacher['id']) ?>" class="button is-light">
                            <span class="icon"><i class="fas fa-times"></i></span>
                            <span>Annuler</span>
                        </a>
                    </div>
                </div>
            </form>
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
});
</script>

<?= $this->endSection() ?>









