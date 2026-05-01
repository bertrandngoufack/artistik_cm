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
                                        <i class="fas fa-eye"></i>
                                    </span>
                                    <span>Détails du Paramètre</span>
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

                <!-- Informations principales -->
                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">Clé du Paramètre</label>
                            <div class="control">
                                <input class="input" type="text" value="<?= esc($setting['setting_key']) ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Catégorie</label>
                            <div class="control">
                                <span class="tag is-info is-medium"><?= ucfirst($setting['category']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Description</label>
                    <div class="control">
                        <input class="input" type="text" value="<?= esc($setting['description']) ?>" readonly>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Valeur</label>
                    <div class="control">
                        <textarea class="textarea" rows="4" readonly><?= esc($setting['setting_value']) ?></textarea>
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">Statut</label>
                            <div class="control">
                                <?php if ($setting['is_active']): ?>
                                    <span class="tag is-success is-medium">Actif</span>
                                <?php else: ?>
                                    <span class="tag is-danger is-medium">Inactif</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">ID</label>
                            <div class="control">
                                <input class="input" type="text" value="<?= $setting['id'] ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations temporelles -->
                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">Créé le</label>
                            <div class="control">
                                <input class="input" type="text" value="<?= date('d/m/Y H:i:s', strtotime($setting['created_at'])) ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Modifié le</label>
                            <div class="control">
                                <input class="input" type="text" value="<?= date('d/m/Y H:i:s', strtotime($setting['updated_at'])) ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="field is-grouped">
                    <div class="control">
                        <a href="<?= base_url('admin/configuration/settings/edit/' . $setting['id']) ?>" class="button is-warning">
                            <span class="icon">
                                <i class="fas fa-edit"></i>
                            </span>
                            <span>Éditer</span>
                        </a>
                    </div>
                    <div class="control">
                        <button class="button is-danger delete-setting" 
                                data-id="<?= $setting['id'] ?>" 
                                data-key="<?= esc($setting['setting_key']) ?>">
                            <span class="icon">
                                <i class="fas fa-trash"></i>
                            </span>
                            <span>Supprimer</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Utilisation du paramètre -->
            <div class="box">
                <h2 class="title is-5">
                    <span class="icon-text">
                        <span class="icon">
                            <i class="fas fa-code"></i>
                        </span>
                        <span>Utilisation du Paramètre</span>
                    </span>
                </h2>
                
                <div class="content">
                    <p>Ce paramètre peut être utilisé dans votre code PHP de la manière suivante :</p>
                    
                    <div class="notification is-light">
                        <h3 class="title is-6">Via le Modèle</h3>
                        <pre><code>$settingModel = new \App\Models\SettingModel();
$value = $settingModel->getValue('<?= $setting['setting_key'] ?>', 'valeur_par_defaut');</code></pre>
                    </div>

                    <div class="notification is-light">
                        <h3 class="title is-6">Via le Contrôleur</h3>
                        <pre><code>$this->settingModel = new \App\Models\SettingModel();
$value = $this->settingModel->getValue('<?= $setting['setting_key'] ?>');</code></pre>
                    </div>

                    <div class="notification is-light">
                        <h3 class="title is-6">Mise à Jour</h3>
                        <pre><code>$this->settingModel->updateByKey('<?= $setting['setting_key'] ?>', 'nouvelle_valeur');</code></pre>
                    </div>
                </div>
            </div>

            <!-- Paramètres similaires -->
            <div class="box">
                <h2 class="title is-5">
                    <span class="icon-text">
                        <span class="icon">
                            <i class="fas fa-link"></i>
                        </span>
                        <span>Paramètres Similaires</span>
                    </span>
                </h2>
                
                <div class="content">
                    <p>Autres paramètres de la catégorie <strong><?= ucfirst($setting['category']) ?></strong> :</p>
                    
                    <div class="tags">
                        <?php
                        // Ici vous pourriez récupérer d'autres paramètres de la même catégorie
                        // Pour l'instant, nous affichons des exemples
                        $similarKeys = [
                            'system' => ['app.name', 'app.version', 'app.debug', 'app.timezone'],
                            'academic' => ['academic.year', 'academic.semester', 'academic.grading_system'],
                            'financial' => ['payment.currency', 'payment.tax_rate', 'payment.due_date'],
                            'communication' => ['smtp.host', 'smtp.port', 'sms.provider', 'whatsapp.api_key'],
                            'security' => ['security.session_timeout', 'security.max_login_attempts', 'security.password_policy']
                        ];
                        
                        $category = $setting['category'];
                        if (isset($similarKeys[$category])):
                            foreach ($similarKeys[$category] as $key):
                                if ($key !== $setting['setting_key']):
                        ?>
                            <span class="tag is-info is-light"><?= $key ?></span>
                        <?php 
                                endif;
                            endforeach;
                        endif;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal" id="deleteModal">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Confirmer la suppression</p>
            <button class="delete" aria-label="close"></button>
        </header>
        <section class="modal-card-body">
            <p>Êtes-vous sûr de vouloir supprimer le paramètre <strong id="deleteSettingKey"></strong> ?</p>
            <p class="has-text-danger">Cette action est irréversible.</p>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-danger" id="confirmDelete">Supprimer</button>
            <button class="button" id="cancelDelete">Annuler</button>
        </footer>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Suppression des paramètres
    const deleteButton = document.querySelector('.delete-setting');
    const deleteModal = document.getElementById('deleteModal');
    const deleteSettingKey = document.getElementById('deleteSettingKey');
    const confirmDelete = document.getElementById('confirmDelete');
    const cancelDelete = document.getElementById('cancelDelete');

    let currentSettingId = null;

    if (deleteButton) {
        deleteButton.addEventListener('click', function() {
            currentSettingId = this.dataset.id;
            deleteSettingKey.textContent = this.dataset.key;
            deleteModal.classList.add('is-active');
        });
    }

    // Fermer le modal
    document.querySelectorAll('.modal-background, .delete').forEach(element => {
        element.addEventListener('click', function() {
            deleteModal.classList.remove('is-active');
        });
    });

    cancelDelete.addEventListener('click', function() {
        deleteModal.classList.remove('is-active');
    });

    confirmDelete.addEventListener('click', function() {
        if (currentSettingId) {
            window.location.href = `/admin/configuration/settings/delete/${currentSettingId}`;
        }
    });
});
</script>
<?= $this->endSection() ?>



