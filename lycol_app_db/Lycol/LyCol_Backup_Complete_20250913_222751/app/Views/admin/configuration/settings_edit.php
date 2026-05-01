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
                                        <i class="fas fa-edit"></i>
                                    </span>
                                    <span>Éditer Paramètre Système</span>
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

                <form action="<?= base_url('admin/configuration/settings/update/' . $setting['id']) ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <div class="field">
                        <label class="label" for="setting_key">Clé du Paramètre</label>
                        <div class="control has-icons-left">
                            <input class="input" 
                                   type="text" 
                                   id="setting_key" 
                                   value="<?= esc($setting['setting_key']) ?>" 
                                   disabled>
                            <span class="icon is-small is-left">
                                <i class="fas fa-key"></i>
                            </span>
                        </div>
                        <p class="help">La clé ne peut pas être modifiée</p>
                    </div>

                    <div class="field">
                        <label class="label" for="setting_value">Valeur *</label>
                        <div class="control has-icons-left">
                            <textarea class="textarea <?= (session()->getFlashdata('errors.setting_value')) ? 'is-danger' : '' ?>" 
                                      id="setting_value" 
                                      name="setting_value" 
                                      placeholder="Valeur du paramètre"
                                      rows="3"
                                      required><?= old('setting_value', $setting['setting_value']) ?></textarea>
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
                                   value="<?= old('description', $setting['description']) ?>" 
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
                                    <option value="system" <?= (old('category', $setting['category']) == 'system') ? 'selected' : '' ?>>Système</option>
                                    <option value="academic" <?= (old('category', $setting['category']) == 'academic') ? 'selected' : '' ?>>Académique</option>
                                    <option value="financial" <?= (old('category', $setting['category']) == 'financial') ? 'selected' : '' ?>>Financier</option>
                                    <option value="communication" <?= (old('category', $setting['category']) == 'communication') ? 'selected' : '' ?>>Communication</option>
                                    <option value="security" <?= (old('category', $setting['category']) == 'security') ? 'selected' : '' ?>>Sécurité</option>
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
                                <input type="checkbox" name="is_active" value="1" <?= ($setting['is_active']) ? 'checked' : '' ?>>
                                Paramètre actif
                            </label>
                        </div>
                        <p class="help">Un paramètre inactif ne sera pas utilisé par l'application</p>
                    </div>

                    <!-- Informations système -->
                    <div class="notification is-info is-light">
                        <div class="columns">
                            <div class="column">
                                <p><strong>Créé le :</strong> <?= date('d/m/Y H:i', strtotime($setting['created_at'])) ?></p>
                            </div>
                            <div class="column">
                                <p><strong>Modifié le :</strong> <?= date('d/m/Y H:i', strtotime($setting['updated_at'])) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="field is-grouped">
                        <div class="control">
                            <button type="submit" class="button is-primary">
                                <span class="icon">
                                    <i class="fas fa-save"></i>
                                </span>
                                <span>Mettre à Jour</span>
                            </button>
                        </div>
                        <div class="control">
                            <a href="<?= base_url('admin/configuration/settings/show/' . $setting['id']) ?>" class="button is-info">
                                <span class="icon">
                                    <i class="fas fa-eye"></i>
                                </span>
                                <span>Voir</span>
                            </a>
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

            <!-- Historique des modifications -->
            <div class="box">
                <h2 class="title is-5">
                    <span class="icon-text">
                        <span class="icon">
                            <i class="fas fa-history"></i>
                        </span>
                        <span>Historique des Modifications</span>
                    </span>
                </h2>
                
                <div class="content">
                    <div class="timeline">
                        <header class="timeline-header">
                            <span class="tag is-medium is-primary">Création</span>
                        </header>
                        <div class="timeline-item">
                            <div class="timeline-marker is-primary"></div>
                            <div class="timeline-content">
                                <p class="heading"><?= date('d/m/Y H:i', strtotime($setting['created_at'])) ?></p>
                                <p>Paramètre créé avec la valeur initiale : <code><?= esc($setting['setting_value']) ?></code></p>
                            </div>
                        </div>
                        
                        <?php if ($setting['updated_at'] != $setting['created_at']): ?>
                            <header class="timeline-header">
                                <span class="tag is-medium is-warning">Modification</span>
                            </header>
                            <div class="timeline-item">
                                <div class="timeline-marker is-warning"></div>
                                <div class="timeline-content">
                                    <p class="heading"><?= date('d/m/Y H:i', strtotime($setting['updated_at'])) ?></p>
                                    <p>Dernière modification du paramètre</p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <header class="timeline-header">
                            <span class="tag is-medium is-info">Statut</span>
                        </header>
                        <div class="timeline-item">
                            <div class="timeline-marker is-info"></div>
                            <div class="timeline-content">
                                <p class="heading">Actuel</p>
                                <p>Statut : 
                                    <?php if ($setting['is_active']): ?>
                                        <span class="tag is-success">Actif</span>
                                    <?php else: ?>
                                        <span class="tag is-danger">Inactif</span>
                                    <?php endif; ?>
                                </p>
                                <p>Catégorie : <span class="tag is-info"><?= ucfirst($setting['category']) ?></span></p>
                            </div>
                        </div>
                    </div>
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



