<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns is-centered">
        <div class="column is-8">
            <div class="box">
                <div class="level">
                    <div class="level-left">
                        <div class="level-item">
                            <h1 class="title is-4">
                                <span class="icon-text">
                                    <span class="icon">
                                        <i class="fas fa-plus"></i>
                                    </span>
                                    <span>Nouveau Paramètre Système</span>
                                </span>
                            </h1>
                        </div>
                    </div>
                    <div class="level-right">
                        <div class="level-item">
                            <a href="<?= base_url('admin/configuration/settings') ?>" class="button is-light">
                                <span class="icon">
                                    <i class="fas fa-arrow-left"></i>
                                </span>
                                <span>Retour</span>
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="notification is-danger is-light">
                        <button class="delete"></button>
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="notification is-danger is-light">
                        <button class="delete"></button>
                        <ul>
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('admin/configuration/settings/store') ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <div class="field">
                        <label class="label" for="setting_key">Clé du Paramètre *</label>
                        <div class="control has-icons-left">
                            <input class="input <?= (session()->getFlashdata('errors.setting_key')) ? 'is-danger' : '' ?>" 
                                   type="text" 
                                   id="setting_key" 
                                   name="setting_key" 
                                   value="<?= old('setting_key') ?>" 
                                   placeholder="ex: app.name, smtp.host, etc."
                                   required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-key"></i>
                            </span>
                        </div>
                        <?php if (session()->getFlashdata('errors.setting_key')): ?>
                            <p class="help is-danger"><?= session()->getFlashdata('errors.setting_key') ?></p>
                        <?php endif; ?>
                        <p class="help">Utilisez des points pour séparer les groupes (ex: app.name, smtp.host)</p>
                    </div>

                    <div class="field">
                        <label class="label" for="setting_value">Valeur *</label>
                        <div class="control has-icons-left">
                            <textarea class="textarea <?= (session()->getFlashdata('errors.setting_value')) ? 'is-danger' : '' ?>" 
                                      id="setting_value" 
                                      name="setting_value" 
                                      placeholder="Valeur du paramètre"
                                      rows="3"
                                      required><?= old('setting_value') ?></textarea>
                            <span class="icon is-small is-left">
                                <i class="fas fa-value"></i>
                            </span>
                        </div>
                        <?php if (session()->getFlashdata('errors.setting_value')): ?>
                            <p class="help is-danger"><?= session()->getFlashdata('errors.setting_value') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label" for="description">Description *</label>
                        <div class="control has-icons-left">
                            <input class="input <?= (session()->getFlashdata('errors.description')) ? 'is-danger' : '' ?>" 
                                   type="text" 
                                   id="description" 
                                   name="description" 
                                   value="<?= old('description') ?>" 
                                   placeholder="Description du paramètre"
                                   required>
                            <span class="icon is-small is-left">
                                <i class="fas fa-info-circle"></i>
                            </span>
                        </div>
                        <?php if (session()->getFlashdata('errors.description')): ?>
                            <p class="help is-danger"><?= session()->getFlashdata('errors.description') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label" for="category">Catégorie *</label>
                        <div class="control has-icons-left">
                            <div class="select is-fullwidth <?= (session()->getFlashdata('errors.category')) ? 'is-danger' : '' ?>">
                                <select id="category" name="category" required>
                                    <option value="">Sélectionner une catégorie</option>
                                    <option value="system" <?= (old('category') == 'system') ? 'selected' : '' ?>>Système</option>
                                    <option value="academic" <?= (old('category') == 'academic') ? 'selected' : '' ?>>Académique</option>
                                    <option value="financial" <?= (old('category') == 'financial') ? 'selected' : '' ?>>Financier</option>
                                    <option value="communication" <?= (old('category') == 'communication') ? 'selected' : '' ?>>Communication</option>
                                    <option value="security" <?= (old('category') == 'security') ? 'selected' : '' ?>>Sécurité</option>
                                </select>
                            </div>
                            <span class="icon is-small is-left">
                                <i class="fas fa-tag"></i>
                            </span>
                        </div>
                        <?php if (session()->getFlashdata('errors.category')): ?>
                            <p class="help is-danger"><?= session()->getFlashdata('errors.category') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <div class="control">
                            <label class="checkbox">
                                <input type="checkbox" name="is_active" value="1" <?= (old('is_active') == '1') ? 'checked' : '' ?>>
                                Paramètre actif
                            </label>
                        </div>
                        <p class="help">Un paramètre inactif ne sera pas utilisé par l'application</p>
                    </div>

                    <div class="field is-grouped">
                        <div class="control">
                            <button type="submit" class="button is-primary">
                                <span class="icon">
                                    <i class="fas fa-save"></i>
                                </span>
                                <span>Créer le Paramètre</span>
                            </button>
                        </div>
                        <div class="control">
                            <a href="<?= base_url('admin/configuration/settings') ?>" class="button is-light">
                                <span class="icon">
                                    <i class="fas fa-times"></i>
                                </span>
                                <span>Annuler</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Aide et exemples -->
            <div class="box">
                <h2 class="title is-5">
                    <span class="icon-text">
                        <span class="icon">
                            <i class="fas fa-question-circle"></i>
                        </span>
                        <span>Aide et Exemples</span>
                    </span>
                </h2>
                
                <div class="content">
                    <h3>Conventions de nommage</h3>
                    <ul>
                        <li><strong>app.name</strong> : Nom de l'application</li>
                        <li><strong>app.version</strong> : Version de l'application</li>
                        <li><strong>smtp.host</strong> : Serveur SMTP</li>
                        <li><strong>smtp.port</strong> : Port SMTP</li>
                        <li><strong>sms.provider</strong> : Fournisseur SMS</li>
                        <li><strong>whatsapp.api_key</strong> : Clé API WhatsApp</li>
                    </ul>

                    <h3>Catégories recommandées</h3>
                    <ul>
                        <li><strong>Système</strong> : Paramètres généraux de l'application</li>
                        <li><strong>Académique</strong> : Paramètres liés aux études et examens</li>
                        <li><strong>Financier</strong> : Paramètres liés aux paiements et facturation</li>
                        <li><strong>Communication</strong> : Paramètres email, SMS, WhatsApp</li>
                        <li><strong>Sécurité</strong> : Paramètres de sécurité et authentification</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation en temps réel
    const settingKeyInput = document.getElementById('setting_key');
    const settingValueInput = document.getElementById('setting_value');
    const descriptionInput = document.getElementById('description');
    const categorySelect = document.getElementById('category');

    function validateField(field, isValid) {
        if (isValid) {
            field.classList.remove('is-danger');
            field.classList.add('is-success');
        } else {
            field.classList.remove('is-success');
            field.classList.add('is-danger');
        }
    }

    // Validation de la clé
    settingKeyInput.addEventListener('input', function() {
        const value = this.value;
        const isValid = value.length >= 3 && value.length <= 100 && /^[a-zA-Z0-9._-]+$/.test(value);
        validateField(this, isValid);
    });

    // Validation de la valeur
    settingValueInput.addEventListener('input', function() {
        const value = this.value;
        const isValid = value.length > 0 && value.length <= 500;
        validateField(this, isValid);
    });

    // Validation de la description
    descriptionInput.addEventListener('input', function() {
        const value = this.value;
        const isValid = value.length > 0 && value.length <= 255;
        validateField(this, isValid);
    });

    // Validation de la catégorie
    categorySelect.addEventListener('change', function() {
        const value = this.value;
        const isValid = value !== '';
        validateField(this, isValid);
    });

    // Notifications
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        const deleteBtn = notification.querySelector('.delete');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                notification.remove();
            });
        }
    });
});
</script>
<?= $this->endSection() ?>



