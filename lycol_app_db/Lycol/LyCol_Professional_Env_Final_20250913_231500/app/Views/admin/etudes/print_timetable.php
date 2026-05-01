<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="section">
        <!-- En-tête -->
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <h1 class="title">Impression Emploi du Temps</h1>
                </div>
            </div>
            <div class="level-right">
                <div class="level-item">
                    <a href="<?= base_url('admin/etudes/timetable') ?>" class="button is-info">
                        <span class="icon">
                            <i class="fas fa-arrow-left"></i>
                        </span>
                        <span>Retour</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Formulaire d'impression -->
        <div class="box">
            <form action="<?= base_url('admin/etudes/timetable/print') ?>" method="POST" id="printForm">
                <div class="columns is-multiline">
                    <!-- Filtres par entité -->
                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Classe</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="class_id">
                                        <option value="">Toutes les classes</option>
                                        <?php if (isset($classes) && is_array($classes)): ?>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?= $class['id'] ?>"><?= esc($class['name']) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Enseignant</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="teacher_id">
                                        <option value="">Tous les enseignants</option>
                                        <?php if (isset($teachers) && is_array($teachers)): ?>
                                            <?php foreach ($teachers as $teacher): ?>
                                                <option value="<?= $teacher['id'] ?>">
                                                    <?= esc($teacher['first_name'] . ' ' . $teacher['last_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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
                                    <select name="subject_id">
                                        <option value="">Toutes les matières</option>
                                        <?php if (isset($subjects) && is_array($subjects)): ?>
                                            <?php foreach ($subjects as $subject): ?>
                                                <option value="<?= $subject['id'] ?>"><?= esc($subject['name']) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres par période -->
                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Date de début</label>
                            <div class="control">
                                <input class="input" type="date" name="start_date" required 
                                       value="<?= date('Y-m-d', strtotime('monday this week')) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Date de fin</label>
                            <div class="control">
                                <input class="input" type="date" name="end_date" required 
                                       value="<?= date('Y-m-d', strtotime('friday this week')) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="column is-4">
                        <div class="field">
                            <label class="label">Année académique</label>
                            <div class="control">
                                <input class="input" type="text" name="academic_year" required 
                                                       value="<?= $current_academic_year ?? '2024-2025' ?>"
                pattern="[0-9]{4}-[0-9]{4}" placeholder="<?= $current_academic_year ?? '2024-2025' ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Options d'impression -->
                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Format d'impression</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="print_format">
                                        <option value="html">HTML (Aperçu)</option>
                                        <option value="pdf">PDF</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Options</label>
                            <div class="control">
                                <label class="checkbox">
                                    <input type="checkbox" name="include_summary" checked>
                                    Inclure le résumé
                                </label>
                                <br>
                                <label class="checkbox">
                                    <input type="checkbox" name="include_headers" checked>
                                    Inclure les en-têtes
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="column is-12">
                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <span class="icon">
                                        <i class="fas fa-print"></i>
                                    </span>
                                    <span>Générer l'impression</span>
                                </button>
                            </div>
                            <div class="control">
                                <button type="button" class="button is-info" onclick="previewPrint()">
                                    <span class="icon">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                    <span>Aperçu</span>
                                </button>
                            </div>
                            <div class="control">
                                <button type="reset" class="button is-light">
                                    <span class="icon">
                                        <i class="fas fa-undo"></i>
                                    </span>
                                    <span>Réinitialiser</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Informations -->
        <div class="notification is-info is-light">
            <div class="content">
                <h4 class="title is-5">Instructions d'impression</h4>
                <ul>
                    <li><strong>Période :</strong> Sélectionnez la période pour laquelle vous souhaitez imprimer l'emploi du temps</li>
                    <li><strong>Filtres :</strong> Utilisez les filtres pour affiner les résultats par classe, enseignant ou matière</li>
                    <li><strong>Format :</strong> Choisissez entre HTML (aperçu) et PDF (impression)</li>
                    <li><strong>Options :</strong> Incluez ou non le résumé et les en-têtes selon vos besoins</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function previewPrint() {
    // Changer temporairement le format pour l'aperçu
    const formatSelect = document.querySelector('select[name="print_format"]');
    const originalValue = formatSelect.value;
    formatSelect.value = 'html';
    
    // Soumettre le formulaire
    document.getElementById('printForm').submit();
    
    // Restaurer la valeur originale
    formatSelect.value = originalValue;
}

// Validation des dates
document.querySelector('input[name="end_date"]').addEventListener('change', function() {
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = this.value;
    
    if (startDate && endDate && startDate > endDate) {
        alert('La date de fin doit être postérieure à la date de début');
        this.value = '';
    }
});

// Validation de l'année académique
document.querySelector('input[name="academic_year"]').addEventListener('input', function() {
    const pattern = /^[0-9]{4}-[0-9]{4}$/;
    if (this.value && !pattern.test(this.value)) {
        this.setCustomValidity('Format attendu: 2024-2025');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?= $this->endSection() ?>


