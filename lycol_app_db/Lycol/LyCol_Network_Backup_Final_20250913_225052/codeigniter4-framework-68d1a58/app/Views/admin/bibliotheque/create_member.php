<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column is-12">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-user-plus"></i>
                        Ajouter un Nouveau Membre
                    </p>
                </div>
                <div class="card-content">
                    <?php if (session()->has('errors')): ?>
                        <div class="notification is-danger">
                            <ul>
                                <?php foreach (session('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->has('error')): ?>
                        <div class="notification is-danger">
                            <?= session('error') ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('admin/bibliotheque/members/store') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="field">
                            <label class="label">Type de Membre *</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="member_type" id="member_type" required>
                                        <option value="">Sélectionner le type</option>
                                        <option value="STUDENT">Étudiant</option>
                                        <option value="TEACHER">Enseignant</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Champs pour les étudiants -->
                        <div id="student_fields" style="display: none;">
                            <div class="field">
                                <label class="label">Matricule *</label>
                                <div class="control">
                                    <input class="input" type="text" name="matricule" placeholder="Matricule de l'étudiant" maxlength="20">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Prénom *</label>
                                <div class="control">
                                    <input class="input" type="text" name="first_name" placeholder="Prénom de l'étudiant" maxlength="50">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Nom de famille *</label>
                                <div class="control">
                                    <input class="input" type="text" name="last_name" placeholder="Nom de famille de l'étudiant" maxlength="50">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Genre *</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="gender" required>
                                            <option value="">Sélectionner le genre</option>
                                            <option value="M">Masculin</option>
                                            <option value="F">Féminin</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Date de naissance *</label>
                                <div class="control">
                                    <input class="input" type="date" name="date_of_birth" required>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Lieu de naissance</label>
                                <div class="control">
                                    <input class="input" type="text" name="place_of_birth" placeholder="Lieu de naissance" maxlength="100">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Nationalité</label>
                                <div class="control">
                                    <input class="input" type="text" name="nationality" placeholder="Nationalité" value="Camerounaise" maxlength="50">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Adresse</label>
                                <div class="control">
                                    <textarea class="textarea" name="address" placeholder="Adresse complète"></textarea>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Téléphone</label>
                                <div class="control">
                                    <input class="input" type="tel" name="phone" placeholder="Numéro de téléphone" maxlength="20">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Email</label>
                                <div class="control">
                                    <input class="input" type="email" name="email" placeholder="Adresse email" maxlength="100">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Nom du parent/tuteur</label>
                                <div class="control">
                                    <input class="input" type="text" name="parent_name" placeholder="Nom du parent ou tuteur" maxlength="100">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Téléphone du parent</label>
                                <div class="control">
                                    <input class="input" type="tel" name="parent_phone" placeholder="Téléphone du parent" maxlength="20">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Email du parent</label>
                                <div class="control">
                                    <input class="input" type="email" name="parent_email" placeholder="Email du parent" maxlength="100">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Contact d'urgence</label>
                                <div class="control">
                                    <input class="input" type="text" name="emergency_contact" placeholder="Contact d'urgence" maxlength="200">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Groupe sanguin</label>
                                <div class="control">
                                    <input class="input" type="text" name="blood_group" placeholder="Groupe sanguin" maxlength="5">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Informations médicales</label>
                                <div class="control">
                                    <textarea class="textarea" name="medical_info" placeholder="Informations médicales importantes"></textarea>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Classe actuelle</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="current_class_id">
                                            <option value="">Sélectionner une classe</option>
                                            <!-- Les classes seront chargées dynamiquement -->
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Année académique *</label>
                                <div class="control">
                                    <input class="input" type="text" name="academic_year" placeholder="<?= $current_academic_year ?? '2024-2025' ?>" value="<?= $current_academic_year ?? '2024-2025' ?>" maxlength="9" required>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Date d'admission</label>
                                <div class="control">
                                    <input class="input" type="date" name="admission_date" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Champs pour les enseignants -->
                        <div id="teacher_fields" style="display: none;">
                            <div class="field">
                                <label class="label">Prénom *</label>
                                <div class="control">
                                    <input class="input" type="text" name="teacher_first_name" placeholder="Prénom de l'enseignant" maxlength="100">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Nom de famille *</label>
                                <div class="control">
                                    <input class="input" type="text" name="teacher_last_name" placeholder="Nom de famille de l'enseignant" maxlength="100">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Téléphone</label>
                                <div class="control">
                                    <input class="input" type="tel" name="teacher_phone" placeholder="Numéro de téléphone" maxlength="20">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Email *</label>
                                <div class="control">
                                    <input class="input" type="email" name="teacher_email" placeholder="Adresse email" maxlength="100" required>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Spécialisation</label>
                                <div class="control">
                                    <input class="input" type="text" name="specialization" placeholder="Spécialisation ou matière enseignée" maxlength="200">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Qualification</label>
                                <div class="control">
                                    <input class="input" type="text" name="qualification" placeholder="Diplôme ou qualification" maxlength="200">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Date d'embauche</label>
                                <div class="control">
                                    <input class="input" type="date" name="hire_date" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <i class="fas fa-save"></i>
                                    Enregistrer le Membre
                                </button>
                            </div>
                            <div class="control">
                                <a href="<?= base_url('admin/bibliotheque/members') ?>" class="button is-light">
                                    <i class="fas fa-arrow-left"></i>
                                    Annuler
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
    const memberTypeSelect = document.getElementById('member_type');
    const studentFields = document.getElementById('student_fields');
    const teacherFields = document.getElementById('teacher_fields');

    memberTypeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        
        // Masquer tous les champs
        studentFields.style.display = 'none';
        teacherFields.style.display = 'none';
        
        // Afficher les champs appropriés
        if (selectedType === 'STUDENT') {
            studentFields.style.display = 'block';
            // Rendre les champs étudiants requis
            studentFields.querySelectorAll('input[required], select[required]').forEach(field => {
                field.required = true;
            });
            // Rendre les champs enseignants non requis
            teacherFields.querySelectorAll('input, select').forEach(field => {
                field.required = false;
            });
        } else if (selectedType === 'TEACHER') {
            teacherFields.style.display = 'block';
            // Rendre les champs enseignants requis
            teacherFields.querySelectorAll('input[required], select[required]').forEach(field => {
                field.required = true;
            });
            // Rendre les champs étudiants non requis
            studentFields.querySelectorAll('input, select').forEach(field => {
                field.required = false;
            });
        }
    });
});
</script>

<?= $this->endSection() ?>







