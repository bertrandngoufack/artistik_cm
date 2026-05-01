<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/configuration') ?>">Configuration</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Configuration SMS</a></li>
                </ul>
            </nav>
            
            <h1 class="title">
                <i class="fas fa-sms"></i>
                Configuration SMS
            </h1>
            <p class="subtitle">Configurez les fournisseurs SMS pour l'envoi de notifications</p>
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
                        Configuration SMS
                    </p>
                </div>
                <div class="card-content">
                    <form action="<?= base_url('admin/configuration/save-sms') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <!-- Fournisseur SMS -->
                        <div class="field">
                            <label class="label">Fournisseur SMS *</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="provider" id="smsProvider" required onchange="toggleProviderFields()">
                                        <option value="">Sélectionner un fournisseur</option>
                                        <option value="twilio" <?= old('provider') === 'twilio' ? 'selected' : '' ?>>Twilio</option>
                                        <option value="textlocal" <?= old('provider') === 'textlocal' ? 'selected' : '' ?>>TextLocal</option>
                                        <option value="msg91" <?= old('provider') === 'msg91' ? 'selected' : '' ?>>MSG91</option>
                                        <option value="africastalking" <?= old('provider') === 'africastalking' ? 'selected' : '' ?>>Africa's Talking</option>
                                        <option value="messagebird" <?= old('provider') === 'messagebird' ? 'selected' : '' ?>>MessageBird</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Nom de l'expéditeur -->
                        <div class="field">
                            <label class="label">Nom de l'Expéditeur *</label>
                            <div class="control">
                                <input class="input" type="text" name="sender_name" value="<?= old('sender_name', 'KISSAI') ?>" placeholder="Nom de l'expéditeur" required maxlength="11">
                            </div>
                            <p class="help">Nom qui apparaîtra comme expéditeur (max 11 caractères)</p>
                        </div>

                        <!-- Configuration Twilio -->
                        <div id="twilioConfig" class="provider-config" style="display: none;">
                            <h4 class="title is-5">Configuration Twilio</h4>
                            
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
                                <label class="label">Numéro de Téléphone</label>
                                <div class="control">
                                    <input class="input" type="tel" name="phone_number" value="<?= old('phone_number') ?>" placeholder="+237123456789">
                                </div>
                            </div>
                        </div>

                        <!-- Configuration TextLocal -->
                        <div id="textlocalConfig" class="provider-config" style="display: none;">
                            <h4 class="title is-5">Configuration TextLocal</h4>
                            
                            <div class="field">
                                <label class="label">API Key</label>
                                <div class="control">
                                    <input class="input" type="text" name="api_key" value="<?= old('api_key') ?>" placeholder="API Key">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Numéro de Téléphone</label>
                                <div class="control">
                                    <input class="input" type="tel" name="phone_number" value="<?= old('phone_number') ?>" placeholder="+237123456789">
                                </div>
                            </div>
                        </div>

                        <!-- Configuration MSG91 -->
                        <div id="msg91Config" class="provider-config" style="display: none;">
                            <h4 class="title is-5">Configuration MSG91</h4>
                            
                            <div class="field">
                                <label class="label">API Key</label>
                                <div class="control">
                                    <input class="input" type="text" name="api_key" value="<?= old('api_key') ?>" placeholder="API Key">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Template ID</label>
                                <div class="control">
                                    <input class="input" type="text" name="template_id" value="<?= old('template_id') ?>" placeholder="Template ID">
                                </div>
                            </div>
                        </div>

                        <!-- Configuration Africa's Talking -->
                        <div id="africastalkingConfig" class="provider-config" style="display: none;">
                            <h4 class="title is-5">Configuration Africa's Talking</h4>
                            
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
                                <label class="label">Numéro de Téléphone</label>
                                <div class="control">
                                    <input class="input" type="tel" name="phone_number" value="<?= old('phone_number') ?>" placeholder="+237123456789">
                                </div>
                            </div>
                        </div>

                        <!-- Configuration MessageBird -->
                        <div id="messagebirdConfig" class="provider-config" style="display: none;">
                            <h4 class="title is-5">Configuration MessageBird</h4>
                            
                            <div class="field">
                                <label class="label">Access Key</label>
                                <div class="control">
                                    <input class="input" type="text" name="access_key" value="<?= old('access_key') ?>" placeholder="Access Key">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Numéro de Téléphone</label>
                                <div class="control">
                                    <input class="input" type="tel" name="phone_number" value="<?= old('phone_number') ?>" placeholder="+237123456789">
                                </div>
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
                                <button type="button" class="button is-info" onclick="testSMS()">
                                    <i class="fas fa-paper-plane"></i>
                                    Tester l'Envoi
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
                        Statut du Service SMS
                    </p>
                </div>
                <div class="card-content">
                    <div class="content">
                        <div class="level">
                            <div class="level-item has-text-centered">
                                <div>
                                    <p class="heading">Statut</p>
                                    <p class="title has-text-success">Actif</p>
                                </div>
                            </div>
                        </div>
                        <div class="level">
                            <div class="level-item has-text-centered">
                                <div>
                                    <p class="heading">Messages Envoyés</p>
                                    <p class="title">1,234</p>
                                </div>
                            </div>
                        </div>
                        <div class="level">
                            <div class="level-item has-text-centered">
                                <div>
                                    <p class="heading">Taux de Réussite</p>
                                    <p class="title has-text-info">98.5%</p>
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
                        <p><strong>Twilio:</strong> Service SMS international</p>
                        <p><strong>TextLocal:</strong> Service SMS indien</p>
                        <p><strong>MSG91:</strong> Service SMS indien avec templates</p>
                        <p><strong>Africa's Talking:</strong> Service SMS africain</p>
                        <p><strong>MessageBird:</strong> Service SMS européen</p>
                    </div>
                </div>
            </div>

            <!-- Test SMS -->
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-paper-plane"></i>
                        Test d'Envoi SMS
                    </p>
                </div>
                <div class="card-content">
                    <div class="field">
                        <label class="label">Numéro de Test</label>
                        <div class="control">
                            <input class="input" type="tel" id="testPhone" placeholder="+237123456789">
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Message de Test</label>
                        <div class="control">
                            <textarea class="textarea" id="testMessage" placeholder="Message de test">Test SMS KISSAI SCHOOL</textarea>
                        </div>
                    </div>
                    <div class="field">
                        <div class="control">
                            <button class="button is-info is-fullwidth" onclick="sendTestSMS()">
                                <i class="fas fa-paper-plane"></i>
                                Envoyer SMS de Test
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleProviderFields() {
    const provider = document.getElementById('smsProvider').value;
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

function testSMS() {
    const provider = document.getElementById('smsProvider').value;
    if (!provider) {
        alert('Veuillez sélectionner un fournisseur SMS');
        return;
    }
    
    // Afficher la modal de test
    const testPhone = document.getElementById('testPhone');
    const testMessage = document.getElementById('testMessage');
    
    if (testPhone.value && testMessage.value) {
        sendTestSMS();
    } else {
        alert('Veuillez remplir le numéro et le message de test');
    }
}

function sendTestSMS() {
    const phone = document.getElementById('testPhone').value;
    const message = document.getElementById('testMessage').value;
    
    if (!phone || !message) {
        alert('Veuillez remplir tous les champs');
        return;
    }
    
    // Simulation d'envoi de SMS
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
    button.disabled = true;
    
    setTimeout(() => {
        button.innerHTML = '<i class="fas fa-check"></i> Envoyé !';
        button.classList.remove('is-info');
        button.classList.add('is-success');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('is-success');
            button.classList.add('is-info');
            button.disabled = false;
        }, 2000);
    }, 2000);
}

// Initialiser l'affichage des champs
document.addEventListener('DOMContentLoaded', function() {
    toggleProviderFields();
});
</script>

<?= $this->endSection() ?>







