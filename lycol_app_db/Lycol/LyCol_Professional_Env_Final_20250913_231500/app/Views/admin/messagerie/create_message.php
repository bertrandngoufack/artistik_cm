<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Nouveau Message</h1>
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
        <!-- Formulaire de création -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-edit"></i></span>
                    Créer un nouveau message
                </p>
            </header>
            <div class="card-content">
                <form method="POST" action="<?= base_url('admin/messagerie/store') ?>">
                    <?= csrf_field() ?>
                    
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

                    <div class="field" id="specific-recipients" style="display: none;">
                        <label class="label">Destinataires spécifiques</label>
                        <div class="control">
                            <textarea class="textarea <?= isset($errors['recipient_ids']) ? 'is-danger' : '' ?>" 
                                      name="recipient_ids" 
                                      placeholder="Entrez les IDs des destinataires séparés par des virgules (ex: 1,2,3)"><?= old('recipient_ids') ?></textarea>
                        </div>
                        <p class="help">Entrez les IDs des utilisateurs séparés par des virgules</p>
                        <?php if (isset($errors['recipient_ids'])): ?>
                            <p class="help is-danger"><?= $errors['recipient_ids'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label">Contenu du message *</label>
                        <div class="control">
                            <textarea class="textarea <?= isset($errors['content']) ? 'is-danger' : '' ?>" 
                                      name="content" 
                                      rows="10" 
                                      placeholder="Contenu du message..."
                                      required><?= old('content') ?></textarea>
                        </div>
                        <?php if (isset($errors['content'])): ?>
                            <p class="help is-danger"><?= $errors['content'] ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <div class="control">
                            <label class="checkbox">
                                <input type="checkbox" name="send_immediately" value="1">
                                Envoyer immédiatement
                            </label>
                        </div>
                        <p class="help">Si non coché, le message sera sauvegardé comme brouillon</p>
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
        <!-- Templates -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-file-alt"></i></span>
                    Templates disponibles
                </p>
            </header>
            <div class="card-content">
                <?php if (!empty($templates)): ?>
                    <?php foreach ($templates as $template): ?>
                        <div class="box">
                            <h4 class="title is-6"><?= esc($template['name']) ?></h4>
                            <p class="subtitle is-7"><?= esc(substr($template['content'], 0, 100)) ?>...</p>
                            <button class="button is-small is-info" 
                                    onclick="useTemplate(<?= htmlspecialchars(json_encode($template)) ?>)">
                                <span class="icon"><i class="fas fa-copy"></i></span>
                                <span>Utiliser</span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="has-text-grey">Aucun template disponible</p>
                <?php endif; ?>
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
                    <h6>Variables disponibles :</h6>
                    <ul>
                        <li><code>{nom}</code> - Nom de l'élève</li>
                        <li><code>{prenom}</code> - Prénom de l'élève</li>
                        <li><code>{classe}</code> - Classe de l'élève</li>
                        <li><code>{date}</code> - Date actuelle</li>
                    </ul>
                    
                    <h6>Types de destinataires :</h6>
                    <ul>
                        <li><strong>Tous</strong> : Envoi à tous les utilisateurs</li>
                        <li><strong>Élèves</strong> : Envoi à tous les élèves</li>
                        <li><strong>Parents</strong> : Envoi à tous les parents</li>
                        <li><strong>Personnel</strong> : Envoi au personnel</li>
                        <li><strong>Spécifique</strong> : Envoi à des utilisateurs spécifiques</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const recipientTypeSelect = document.querySelector('select[name="recipient_type"]');
    const specificRecipientsDiv = document.getElementById('specific-recipients');
    
    recipientTypeSelect.addEventListener('change', function() {
        if (this.value === 'SPECIFIC') {
            specificRecipientsDiv.style.display = 'block';
        } else {
            specificRecipientsDiv.style.display = 'none';
        }
    });
    
    // Initialiser l'affichage
    if (recipientTypeSelect.value === 'SPECIFIC') {
        specificRecipientsDiv.style.display = 'block';
    }
});

function useTemplate(template) {
    document.querySelector('input[name="title"]').value = template.title || '';
    document.querySelector('textarea[name="content"]').value = template.content || '';
}
</script>

<?= $this->endSection() ?>







