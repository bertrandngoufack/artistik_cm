<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Nouveau Template de Message</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/messagerie') ?>" class="button is-light">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour</span>
            </a>
        </div>
    </div>
</div>

<div class="columns">
    <div class="column is-8">
        <!-- Formulaire de création de template -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-file-alt"></i></span>
                    Créer un nouveau template
                </p>
            </header>
            <div class="card-content">
                <form method="POST" action="<?= base_url('admin/messagerie/template/store') ?>">
                    <?= csrf_field() ?>
                    
                    <div class="field">
                        <label class="label">Nom du template *</label>
                        <div class="control">
                            <input class="input <?= isset($errors['name']) ? 'is-danger' : '' ?>" 
                                   type="text" 
                                   name="name" 
                                   value="<?= old('name') ?>" 
                                   placeholder="Nom du template"
                                   required>
                        </div>
                        <?php if (isset($errors['name'])): ?>
                            <p class="help is-danger"><?= $errors['name'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label">Titre du message *</label>
                        <div class="control">
                            <input class="input <?= isset($errors['title']) ? 'is-danger' : '' ?>" 
                                   type="text" 
                                   name="title" 
                                   value="<?= old('title') ?>" 
                                   placeholder="Titre du message"
                                   required>
                        </div>
                        <?php if (isset($errors['title'])): ?>
                            <p class="help is-danger"><?= $errors['title'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label">Type de destinataire *</label>
                        <div class="control">
                            <div class="select is-fullwidth <?= isset($errors['recipient_type']) ? 'is-danger' : '' ?>">
                                <select name="recipient_type" required>
                                    <option value="">Sélectionner le type de destinataire</option>
                                    <option value="ALL" <?= old('recipient_type') === 'ALL' ? 'selected' : '' ?>>Tous les utilisateurs</option>
                                    <option value="STUDENTS" <?= old('recipient_type') === 'STUDENTS' ? 'selected' : '' ?>>Tous les élèves</option>
                                    <option value="PARENTS" <?= old('recipient_type') === 'PARENTS' ? 'selected' : '' ?>>Tous les parents</option>
                                    <option value="STAFF" <?= old('recipient_type') === 'STAFF' ? 'selected' : '' ?>>Tout le personnel</option>
                                    <option value="SPECIFIC" <?= old('recipient_type') === 'SPECIFIC' ? 'selected' : '' ?>>Destinataires spécifiques</option>
                                </select>
                            </div>
                        </div>
                        <?php if (isset($errors['recipient_type'])): ?>
                            <p class="help is-danger"><?= $errors['recipient_type'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label">Contenu du template *</label>
                        <div class="control">
                            <textarea class="textarea <?= isset($errors['content']) ? 'is-danger' : '' ?>" 
                                      name="content" 
                                      rows="12" 
                                      placeholder="Contenu du template avec variables..."
                                      required><?= old('content') ?></textarea>
                        </div>
                        <?php if (isset($errors['content'])): ?>
                            <p class="help is-danger"><?= $errors['content'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <div class="control">
                            <label class="checkbox">
                                <input type="checkbox" name="is_active" value="1" <?= old('is_active') ? 'checked' : '' ?>>
                                Template actif
                            </label>
                        </div>
                        <p class="help">Un template actif peut être utilisé pour créer des messages</p>
                    </div>

                    <div class="field is-grouped">
                        <div class="control">
                            <button type="submit" class="button is-primary">
                                <span class="icon"><i class="fas fa-save"></i></span>
                                <span>Sauvegarder</span>
                            </button>
                        </div>
                        <div class="control">
                            <a href="<?= base_url('admin/messagerie') ?>" class="button is-light">
                                <span>Annuler</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="column is-4">
        <!-- Variables disponibles -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-code"></i></span>
                    Variables disponibles
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <h6>Variables générales :</h6>
                    <ul>
                        <li><code>{date}</code> - Date actuelle</li>
                        <li><code>{time}</code> - Heure actuelle</li>
                        <li><code>{school_name}</code> - Nom de l'école</li>
                        <li><code>{admin_name}</code> - Nom de l'administrateur</li>
                    </ul>
                    
                    <h6>Variables élèves :</h6>
                    <ul>
                        <li><code>{student_name}</code> - Nom de l'élève</li>
                        <li><code>{student_firstname}</code> - Prénom de l'élève</li>
                        <li><code>{student_class}</code> - Classe de l'élève</li>
                        <li><code>{student_id}</code> - ID de l'élève</li>
                    </ul>
                    
                    <h6>Variables parents :</h6>
                    <ul>
                        <li><code>{parent_name}</code> - Nom du parent</li>
                        <li><code>{parent_firstname}</code> - Prénom du parent</li>
                        <li><code>{parent_email}</code> - Email du parent</li>
                        <li><code>{parent_phone}</code> - Téléphone du parent</li>
                    </ul>
                    
                    <h6>Variables académiques :</h6>
                    <ul>
                        <li><code>{class_name}</code> - Nom de la classe</li>
                        <li><code>{subject_name}</code> - Nom de la matière</li>
                        <li><code>{grade}</code> - Note</li>
                        <li><code>{exam_date}</code> - Date d'examen</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Templates existants -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-list"></i></span>
                    Templates existants
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <p class="has-text-grey">Aucun template existant</p>
                    <a href="<?= base_url('admin/messagerie/templates') ?>" class="button is-small is-info">
                        <span class="icon"><i class="fas fa-eye"></i></span>
                        <span>Voir tous les templates</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Aide -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-question-circle"></i></span>
                    Aide
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <h6>Conseils pour créer un bon template :</h6>
                    <ul>
                        <li>Utilisez des variables pour personnaliser le contenu</li>
                        <li>Gardez un ton professionnel et clair</li>
                        <li>Incluez toutes les informations importantes</li>
                        <li>Testez le template avant de l'utiliser</li>
                    </ul>
                    
                    <h6>Exemple de template :</h6>
                    <div class="box">
                        <p><strong>Bonjour {parent_name},</strong></p>
                        <p>Nous vous informons que {student_name} a obtenu la note de {grade} en {subject_name}.</p>
                        <p>Cordialement,<br>L'équipe pédagogique</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prévisualisation en temps réel
    const contentTextarea = document.querySelector('textarea[name="content"]');
    const titleInput = document.querySelector('input[name="title"]');
    
    // Fonction pour remplacer les variables par des exemples
    function previewTemplate() {
        let content = contentTextarea.value;
        let title = titleInput.value;
        
        // Remplacer les variables par des exemples
        const variables = {
            '{date}': new Date().toLocaleDateString('fr-FR'),
            '{time}': new Date().toLocaleTimeString('fr-FR'),
            '{school_name}': 'LYCOL - KISSAI SCHOOL',
            '{admin_name}': 'Administrateur',
            '{student_name}': 'Jean Dupont',
            '{student_firstname}': 'Jean',
            '{student_class}': '6ème A',
            '{student_id}': '12345',
            '{parent_name}': 'M. Dupont',
            '{parent_firstname}': 'Pierre',
            '{parent_email}': 'pierre.dupont@email.com',
            '{parent_phone}': '+237 123 456 789',
            '{class_name}': '6ème A',
            '{subject_name}': 'Mathématiques',
            '{grade}': '15/20',
            '{exam_date}': '25/08/2025'
        };
        
        Object.keys(variables).forEach(variable => {
            content = content.replace(new RegExp(variable, 'g'), variables[variable]);
            title = title.replace(new RegExp(variable, 'g'), variables[variable]);
        });
        
        // Afficher la prévisualisation
        console.log('Prévisualisation du titre:', title);
        console.log('Prévisualisation du contenu:', content);
    }
    
    // Écouter les changements
    contentTextarea.addEventListener('input', previewTemplate);
    titleInput.addEventListener('input', previewTemplate);
});
</script>

<?= $this->endSection() ?>







