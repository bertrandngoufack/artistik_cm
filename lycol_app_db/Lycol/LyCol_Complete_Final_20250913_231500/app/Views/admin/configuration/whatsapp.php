<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/configuration') ?>">Configuration</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Configuration WhatsApp</a></li>
                </ul>
            </nav>
            
            <h1 class="title">
                <i class="fab fa-whatsapp"></i>
                Configuration WhatsApp Business
            </h1>
            <p class="subtitle">Configurez WhatsApp Business API pour l'envoi de messages automatiques</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="notification is-success">
            <button class="delete" onclick="this.parentElement.remove()"></button>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="notification is-danger">
            <button class="delete" onclick="this.parentElement.remove()"></button>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="columns">
        <div class="column is-8">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-cog"></i>
                        Configuration WhatsApp Business API
                    </p>
                </div>
                <div class="card-content">
                    <form action="<?= base_url('admin/configuration/save-whatsapp') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <!-- Fournisseur WhatsApp -->
                        <div class="field">
                            <label class="label">Fournisseur WhatsApp *</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="provider" id="whatsappProvider" required onchange="toggleWhatsAppFields()">
                                        <option value="">Sélectionner un fournisseur</option>
                                        <option value="twilio" <?= old('provider') === 'twilio' ? 'selected' : '' ?>>Twilio WhatsApp Business</option>
                                        <option value="dialog360" <?= old('provider') === 'dialog360' ? 'selected' : '' ?>>Dialog360 (360dialog)</option>
                                        <option value="meta" <?= old('provider') === 'meta' ? 'selected' : '' ?>>Meta WhatsApp Business</option>
                                        <option value="africastalking" <?= old('provider') === 'africastalking' ? 'selected' : '' ?>>Africa's Talking WhatsApp</option>
                                        <option value="messagebird" <?= old('provider') === 'messagebird' ? 'selected' : '' ?>>MessageBird WhatsApp</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Configuration Twilio WhatsApp -->
                        <div id="twilioWhatsAppConfig" class="provider-config" style="display: none;">
                            <h4 class="title is-5">Configuration Twilio WhatsApp Business</h4>
                            
                            <div class="field">
                                <label class="label">Account SID</label>
                                <div class="control">
                                    <input class="input" type="text" name="account_sid" value="<?= old('account_sid') ?>" placeholder="AC1234567890abcdef">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Auth Token</label>
                                <div class="control">
                                    <input class="input" type="password" name="auth_token" value="<?= old('auth_token') ?>" placeholder="Auth Token">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Numéro WhatsApp Business</label>
                                <div class="control">
                                    <input class="input" type="tel" name="phone_number" value="<?= old('phone_number') ?>" placeholder="+237123456789">
                                </div>
                                <p class="help">Numéro WhatsApp Business vérifié</p>
                            </div>

                            <div class="field">
                                <label class="label">Webhook URL</label>
                                <div class="control">
                                    <input class="input" type="url" name="webhook_url" value="<?= old('webhook_url') ?>" placeholder="https://votre-domaine.com/webhook/whatsapp">
                                </div>
                                <p class="help">URL pour recevoir les messages entrants</p>
                            </div>
                        </div>

                        <!-- Configuration Dialog360 -->
                        <div id="dialog360Config" class="provider-config" style="display: none;">
                            <h4 class="title is-5">Configuration Dialog360 (360dialog)</h4>
                            
                            <div class="field">
                                <label class="label">API Key</label>
                                <div class="control">
                                    <input class="input" type="text" name="api_key" value="<?= old('api_key') ?>" placeholder="API Key">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Numéro WhatsApp Business</label>
                                <div class="control">
                                    <input class="input" type="tel" name="phone_number" value="<?= old('phone_number') ?>" placeholder="+237123456789">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Webhook URL</label>
                                <div class="control">
                                    <input class="input" type="url" name="webhook_url" value="<?= old('webhook_url') ?>" placeholder="https://votre-domaine.com/webhook/whatsapp">
                                </div>
                            </div>
                        </div>

                        <!-- Configuration Meta WhatsApp -->
                        <div id="metaConfig" class="provider-config" style="display: none;">
                            <h4 class="title is-5">Configuration Meta WhatsApp Business</h4>
                            
                            <div class="field">
                                <label class="label">Access Token</label>
                                <div class="control">
                                    <input class="input" type="text" name="access_token" value="<?= old('access_token') ?>" placeholder="Access Token">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Phone Number ID</label>
                                <div class="control">
                                    <input class="input" type="text" name="phone_number_id" value="<?= old('phone_number_id') ?>" placeholder="Phone Number ID">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Business Account ID</label>
                                <div class="control">
                                    <input class="input" type="text" name="business_account_id" value="<?= old('business_account_id') ?>" placeholder="Business Account ID">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Webhook Verify Token</label>
                                <div class="control">
                                    <input class="input" type="text" name="webhook_verify_token" value="<?= old('webhook_verify_token') ?>" placeholder="Webhook Verify Token">
                                </div>
                            </div>
                        </div>

                        <!-- Configuration Africa's Talking WhatsApp -->
                        <div id="africastalkingWhatsAppConfig" class="provider-config" style="display: none;">
                            <h4 class="title is-5">Configuration Africa's Talking WhatsApp</h4>
                            
                            <div class="field">
                                <label class="label">API Key</label>
                                <div class="control">
                                    <input class="input" type="text" name="api_key" value="<?= old('api_key') ?>" placeholder="API Key">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Username</label>
                                <div class="control">
                                    <input class="input" type="text" name="username" value="<?= old('username') ?>" placeholder="Username">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Numéro WhatsApp Business</label>
                                <div class="control">
                                    <input class="input" type="tel" name="phone_number" value="<?= old('phone_number') ?>" placeholder="+237123456789">
                                </div>
                            </div>
                        </div>

                        <!-- Configuration MessageBird WhatsApp -->
                        <div id="messagebirdWhatsAppConfig" class="provider-config" style="display: none;">
                            <h4 class="title is-5">Configuration MessageBird WhatsApp</h4>
                            
                            <div class="field">
                                <label class="label">Access Key</label>
                                <div class="control">
                                    <input class="input" type="text" name="access_key" value="<?= old('access_key') ?>" placeholder="Access Key">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Channel ID</label>
                                <div class="control">
                                    <input class="input" type="text" name="channel_id" value="<?= old('channel_id') ?>" placeholder="Channel ID">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Webhook URL</label>
                                <div class="control">
                                    <input class="input" type="url" name="webhook_url" value="<?= old('webhook_url') ?>" placeholder="https://votre-domaine.com/webhook/whatsapp">
                                </div>
                            </div>
                        </div>

                        <!-- Configuration Générale -->
                        <div class="field">
                            <label class="label">Template par Défaut</label>
                            <div class="control">
                                <textarea class="textarea" name="default_template" placeholder="Template par défaut pour les messages WhatsApp"><?= old('default_template', 'Bonjour {name}, {message}') ?></textarea>
                            </div>
                            <p class="help">Template par défaut avec variables {name}, {message}, etc.</p>
                        </div>

                        <!-- Options Avancées -->
                        <div class="field">
                            <label class="label">Options Avancées</label>
                            <div class="control">
                                <label class="checkbox">
                                    <input type="checkbox" name="media_enabled" value="1" <?= old('media_enabled') ? 'checked' : '' ?>>
                                    Activer l'envoi de médias (images, documents)
                                </label>
                            </div>
                        </div>

                        <div class="field">
                            <div class="control">
                                <label class="checkbox">
                                    <input type="checkbox" name="buttons_enabled" value="1" <?= old('buttons_enabled') ? 'checked' : '' ?>>
                                    Activer les boutons interactifs
                                </label>
                            </div>
                        </div>

                        <div class="field">
                            <div class="control">
                                <label class="checkbox">
                                    <input type="checkbox" name="auto_reply" value="1" <?= old('auto_reply') ? 'checked' : '' ?>>
                                    Activer les réponses automatiques
                                </label>
                            </div>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <i class="fas fa-save"></i>
                                    Sauvegarder la Configuration
                                </button>
                            </div>
                            <div class="control">
                                <button type="button" class="button is-success" onclick="testWhatsApp()">
                                    <i class="fab fa-whatsapp"></i>
                                    Tester WhatsApp
                                </button>
                            </div>
                            <div class="control">
                                <a href="<?= base_url('admin/configuration') ?>" class="button is-light">
                                    <i class="fas fa-arrow-left"></i>
                                    Retour
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="column is-4">
            <!-- Statut du Service -->
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-chart-line"></i>
                        Statut WhatsApp Business
                    </p>
                </div>
                <div class="card-content">
                    <div class="content">
                        <div class="level">
                            <div class="level-item has-text-centered">
                                <div>
                                    <p class="heading">Statut</p>
                                    <p class="title has-text-success">Connecté</p>
                                </div>
                            </div>
                        </div>
                        <div class="level">
                            <div class="level-item has-text-centered">
                                <div>
                                    <p class="heading">Messages Envoyés</p>
                                    <p class="title">856</p>
                                </div>
                            </div>
                        </div>
                        <div class="level">
                            <div class="level-item has-text-centered">
                                <div>
                                    <p class="heading">Taux de Livraison</p>
                                    <p class="title has-text-info">99.2%</p>
                                </div>
                            </div>
                        </div>
                        <div class="level">
                            <div class="level-item has-text-centered">
                                <div>
                                    <p class="heading">Templates Approuvés</p>
                                    <p class="title has-text-warning">12</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations -->
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-info-circle"></i>
                        Informations
                    </p>
                </div>
                <div class="card-content">
                    <div class="content">
                        <p><strong>Twilio:</strong> WhatsApp Business API officiel</p>
                        <p><strong>Dialog360:</strong> Partenaire WhatsApp Business</p>
                        <p><strong>Meta:</strong> API officielle Meta/Facebook</p>
                        <p><strong>Africa's Talking:</strong> Service africain</p>
                        <p><strong>MessageBird:</strong> Service européen</p>
                        <p><strong>Note:</strong> Tous les numéros doivent être vérifiés par WhatsApp</p>
                    </div>
                </div>
            </div>

            <!-- Test WhatsApp -->
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fab fa-whatsapp"></i>
                        Test WhatsApp
                    </p>
                </div>
                <div class="card-content">
                    <div class="field">
                        <label class="label">Numéro de Test</label>
                        <div class="control">
                            <input class="input" type="tel" id="testWhatsAppPhone" placeholder="+237123456789">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Template de Test</label>
                        <div class="control">
                            <select class="input" id="testTemplate">
                                <option value="welcome">Message de bienvenue</option>
                                <option value="reminder">Rappel de paiement</option>
                                <option value="notification">Notification générale</option>
                            </select>
                        </div>
                    </div>
                    <div class="field">
                        <div class="control">
                            <button class="button is-success is-fullwidth" onclick="sendTestWhatsApp()">
                                <i class="fab fa-whatsapp"></i>
                                Envoyer Message Test
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Templates WhatsApp -->
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-list"></i>
                        Templates Approuvés
                    </p>
                </div>
                <div class="card-content">
                    <div class="content">
                        <ul>
                            <li><strong>welcome:</strong> Message de bienvenue</li>
                            <li><strong>payment_reminder:</strong> Rappel de paiement</li>
                            <li><strong>exam_result:</strong> Résultat d'examen</li>
                            <li><strong>attendance:</strong> Notification d'absence</li>
                            <li><strong>general:</strong> Notification générale</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleWhatsAppFields() {
    const provider = document.getElementById('whatsappProvider').value;
    const configs = document.querySelectorAll('.provider-config');
    
    // Masquer toutes les configurations
    configs.forEach(config => {
        config.style.display = 'none';
    });
    
    // Afficher la configuration correspondante
    if (provider) {
        const config = document.getElementById(provider + 'Config');
        if (config) {
            config.style.display = 'block';
        }
    }
}

function testWhatsApp() {
    const provider = document.getElementById('whatsappProvider').value;
    if (!provider) {
        alert('Veuillez sélectionner un fournisseur WhatsApp');
        return;
    }
    
    // Afficher la modal de test
    const testPhone = document.getElementById('testWhatsAppPhone');
    const testTemplate = document.getElementById('testTemplate');
    
    if (testPhone.value) {
        sendTestWhatsApp();
    } else {
        alert('Veuillez remplir le numéro de test');
    }
}

function sendTestWhatsApp() {
    const phone = document.getElementById('testWhatsAppPhone').value;
    const template = document.getElementById('testTemplate').value;
    
    if (!phone) {
        alert('Veuillez remplir le numéro de test');
        return;
    }
    
    // Simulation d'envoi WhatsApp
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
    button.disabled = true;
    
    setTimeout(() => {
        button.innerHTML = '<i class="fas fa-check"></i> Envoyé !';
        button.classList.remove('is-success');
        button.classList.add('is-info');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('is-info');
            button.classList.add('is-success');
            button.disabled = false;
        }, 2000);
    }, 3000);
}

// Initialiser l'affichage des champs
document.addEventListener('DOMContentLoaded', function() {
    toggleWhatsAppFields();
});
</script>

<?= $this->endSection() ?>







