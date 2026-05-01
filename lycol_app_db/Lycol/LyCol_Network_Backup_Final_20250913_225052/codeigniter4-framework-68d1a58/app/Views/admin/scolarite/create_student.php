<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/scolarite') ?>">Scolarité</a></li>
                    <li><a href="<?= base_url('admin/scolarite/students') ?>">Élèves</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Nouvel Élève</a></li>
                </ul>
            </nav>

            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="fas fa-user-plus"></i></span>
                        Nouvel Élève
                    </p>
                    <div class="card-header-icon">
                        <a href="<?= base_url('admin/scolarite/students') ?>" class="button is-small">
                            <span class="icon"><i class="fas fa-arrow-left"></i></span>
                            <span>Retour</span>
                        </a>
                    </div>
                </header>
                <div class="card-content">
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="notification is-danger">
                            <button class="delete"></button>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('admin/scolarite/students/store') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="columns">
                            <div class="column is-6">
                                <h4 class="title is-4">Informations Personnelles</h4>
                                
                                <div class="field">
                                    <label class="label">Matricule *</label>
                                    <div class="control">
                                        <input class="input" type="text" name="matricule" value="<?= old('matricule') ?>" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Nom *</label>
                                    <div class="control">
                                        <input class="input" type="text" name="last_name" value="<?= old('last_name') ?>" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Prénom *</label>
                                    <div class="control">
                                        <input class="input" type="text" name="first_name" value="<?= old('first_name') ?>" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Date de naissance *</label>
                                    <div class="control">
                                        <input class="input" type="date" name="birth_date" value="<?= old('birth_date') ?>" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Genre *</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="gender" required>
                                                <option value="">Sélectionner...</option>
                                                <option value="MALE" <?= old('gender') === 'MALE' ? 'selected' : '' ?>>Masculin</option>
                                                <option value="FEMALE" <?= old('gender') === 'FEMALE' ? 'selected' : '' ?>>Féminin</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Nationalité *</label>
                                    <div class="control">
                                        <input class="input" type="text" name="nationality" value="<?= old('nationality', 'Camerounaise') ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <h4 class="title is-4">Informations Scolaires</h4>
                                
                                <div class="field">
                                    <label class="label">Classe *</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="current_class_id" required>
                                                <option value="">Sélectionner une classe...</option>
                                                <?php foreach ($classes as $class): ?>
                                                    <option value="<?= $class->id ?? $class['id'] ?>" <?= old('current_class_id') == ($class->id ?? $class['id']) ? 'selected' : '' ?>>
                                                        <?= esc($class->name ?? $class['name']) ?> - <?= esc($class->level ?? $class['level']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Date d'admission *</label>
                                    <div class="control">
                                        <input class="input" type="date" name="enrollment_date" value="<?= old('enrollment_date', date('Y-m-d')) ?>" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Année académique *</label>
                                    <div class="control">
                                        <input class="input" type="text" name="academic_year" value="<?= old('academic_year', $current_academic_year ?? '2024-2025') ?>" required>
                                    </div>
                                </div>

                                <h4 class="title is-4">Informations des Parents</h4>
                                
                                <div class="field">
                                    <label class="label">Nom du parent *</label>
                                    <div class="control">
                                        <input class="input" type="text" name="parent_name" value="<?= old('parent_name') ?>" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Téléphone du parent *</label>
                                    <div class="control">
                                        <input class="input" type="tel" name="parent_phone" value="<?= old('parent_phone') ?>" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Email du parent</label>
                                    <div class="control">
                                        <input class="input" type="email" name="parent_email" value="<?= old('parent_email') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Adresse</label>
                            <div class="control">
                                <textarea class="textarea" name="address" placeholder="Adresse complète"><?= old('address') ?></textarea>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Informations médicales</label>
                            <div class="control">
                                <textarea class="textarea" name="medical_info" placeholder="Allergies, conditions médicales, etc."><?= old('medical_info') ?></textarea>
                            </div>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <span class="icon"><i class="fas fa-save"></i></span>
                                    <span>Enregistrer l'élève</span>
                                </button>
                            </div>
                            <div class="control">
                                <a href="<?= base_url('admin/scolarite/students') ?>" class="button is-light">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide notifications
    setTimeout(() => {
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach(notification => {
            notification.style.display = 'none';
        });
    }, 5000);

    // Close notification on click
    document.querySelectorAll('.notification .delete').forEach(button => {
        button.addEventListener('click', () => {
            button.parentNode.style.display = 'none';
        });
    });
});
</script>

<?= $this->endSection() ?>
