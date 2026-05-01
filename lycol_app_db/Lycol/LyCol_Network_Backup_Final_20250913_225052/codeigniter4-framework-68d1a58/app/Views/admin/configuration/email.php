<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/configuration') ?>">Configuration</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Configuration Email</a></li>
                </ul>
            </nav>
            
            <h1 class="title">
                <i class="fas fa-envelope"></i>
                Configuration Email
            </h1>
            <p class="subtitle">Configurez les fournisseurs email pour l'envoi automatique</p>
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
                        <i class="fas fa-server"></i>
                        Configuration SMTP
                    </p>
                </div>
                <div class="card-content">
                    <form action="<?= base_url('admin/configuration/save-email') ?>" method="POST" id="email-form">
                        <?= csrf_field() ?>
                        
                        <div class="field">
                            <label class="label">Fournisseur Email *</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="provider" id="provider-select" required>
                                        <option value="">Sélectionnez un fournisseur</option>
                                        <?php foreach ($providers as $key => $provider): ?>
                                            <option value="<?= $key ?>" <?= old('provider') === $key ? 'selected' : '' ?>>
                                                <?= $provider['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Email d'expédition *</label>
                                    <div class="control">
                                        <input class="input" type="email" name="from_email" value="<?= old('from_email', 'kissai.school@gmail.com') ?>" placeholder="votre@email.com" required>
                                    </div>
                                </div>
                            </div>
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Nom d'expédition *</label>
                                    <div class="control">
                                        <input class="input" type="text" name="from_name" value="<?= old('from_name', 'KISSAI SCHOOL') ?>" placeholder="KISSAI SCHOOL" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuration Gmail -->
                        <div id="gmail-config" class="provider-config" style="display: none;">
                            <div class="notification is-info is-light">
                                <p><strong>Configuration Gmail :</strong> Utilisez un mot de passe d'application pour plus de sécurité.</p>
                            </div>
                            
                            <div class="columns">
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Email Gmail *</label>
                                        <div class="control">
                                            <input class="input" type="email" name="smtp_user" placeholder="votre@gmail.com">
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Mot de passe d'application *</label>
                                        <div class="control">
                                            <input class="input" type="password" name="smtp_pass" placeholder="Mot de passe d'application">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuration Outlook -->
                        <div id="outlook-config" class="provider-config" style="display: none;">
                            <div class="notification is-info is-light">
                                <p><strong>Configuration Outlook/Hotmail :</strong> Utilisez votre email et mot de passe Microsoft.</p>
                            </div>
                            
                            <div class="columns">
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Email Outlook *</label>
                                        <div class="control">
                                            <input class="input" type="email" name="smtp_user" placeholder="votre@outlook.com">
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Mot de passe *</label>
                                        <div class="control">
                                            <input class="input" type="password" name="smtp_pass" placeholder="Mot de passe">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Configuration personnalisée -->
                        <div id="custom-config" class="provider-config" style="display: none;">
                            <div class="notification is-warning is-light">
                                <p><strong>Configuration personnalisée :</strong> Renseignez les paramètres de votre serveur SMTP.</p>
                            </div>
                            
                            <div class="columns">
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Serveur SMTP *</label>
                                        <div class="control">
                                            <input class="input" type="text" name="smtp_host" placeholder="smtp.votreserveur.com">
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Port *</label>
                                        <div class="control">
                                            <input class="input" type="number" name="smtp_port" placeholder="587">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="columns">
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Chiffrement</label>
                                        <div class="control">
                                            <div class="select is-fullwidth">
                                                <select name="smtp_crypto">
                                                    <option value="tls">TLS</option>
                                                    <option value="ssl">SSL</option>
                                                    <option value="">Aucun</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="field">
                                        <label class="label">Nom d'utilisateur *</label>
                                        <div class="control">
                                            <input class="input" type="text" name="smtp_user" placeholder="votre@email.com">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Mot de passe *</label>
                                <div class="control">
                                    <input class="input" type="password" name="smtp_pass" placeholder="Mot de passe">
                                </div>
                            </div>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <i class="fas fa-save"></i>
                                    Sauvegarder
                                </button>
                            </div>
                            <div class="control">
                                <button type="button" class="button is-info" onclick="testEmail()">
                                    <i class="fas fa-paper-plane"></i>
                                    Tester
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
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-lightbulb"></i>
                        Guide de Configuration
                    </p>
                </div>
                <div class="card-content">
                    <div class="content">
                        <h4>Fournisseurs Supportés</h4>
                        
                        <div class="box">
                            <h5><i class="fas fa-envelope" style="color: #ea4335;"></i> Gmail</h5>
                            <p>Service email gratuit de Google</p>
                            <ul>
                                <li>Créez un compte Gmail</li>
                                <li>Activez l'authentification à 2 facteurs</li>
                                <li>Générez un mot de passe d'application</li>
                            </ul>
                        </div>

                        <div class="box">
                            <h5><i class="fas fa-envelope" style="color: #0078d4;"></i> Outlook/Hotmail</h5>
                            <p>Service email de Microsoft</p>
                            <ul>
                                <li>Utilisez votre compte Microsoft</li>
                                <li>Activez l'authentification à 2 facteurs</li>
                                <li>Utilisez votre mot de passe normal</li>
                            </ul>
                        </div>

                        <div class="box">
                            <h5><i class="fas fa-server"></i> Serveur SMTP personnalisé</h5>
                            <p>Configuration SMTP personnalisée</p>
                            <ul>
                                <li>Serveur SMTP de votre hébergeur</li>
                                <li>Paramètres fournis par votre fournisseur</li>
                                <li>Configuration avancée</li>
                            </ul>
                        </div>

                        <div class="notification is-warning is-light">
                            <p><strong>Important :</strong> Pour Gmail, utilisez un mot de passe d'application et non votre mot de passe principal.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Email -->
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-paper-plane"></i>
                        Test d'Envoi
                    </p>
                </div>
                <div class="card-content">
                    <div class="field">
                        <label class="label">Email de test</label>
                        <div class="control">
                            <input class="input" type="email" id="test-email" placeholder="test@example.com">
                        </div>
                    </div>
                    <div class="field">
                        <div class="control">
                            <button class="button is-info is-fullwidth" onclick="testEmail()">
                                <i class="fas fa-paper-plane"></i>
                                Envoyer un test
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const providerSelect = document.getElementById('provider-select');
    const providerConfigs = document.querySelectorAll('.provider-config');
    
    providerSelect.addEventListener('change', function() {
        // Masquer toutes les configurations
        providerConfigs.forEach(config => {
            config.style.display = 'none';
        });
        
        // Afficher la configuration sélectionnée
        const selectedProvider = this.value;
        if (selectedProvider) {
            const configElement = document.getElementById(selectedProvider + '-config');
            if (configElement) {
                configElement.style.display = 'block';
            }
        }
    });
    
    // Déclencher l'événement au chargement si une valeur est sélectionnée
    if (providerSelect.value) {
        providerSelect.dispatchEvent(new Event('change'));
    }
});

function testEmail() {
    const testEmail = document.getElementById('test-email').value;
    
    if (!testEmail) {
        alert('Veuillez saisir une adresse email de test');
        return;
    }
    
    if (!isValidEmail(testEmail)) {
        alert('Veuillez saisir une adresse email valide');
        return;
    }
    
    // Afficher un message de chargement
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
    button.disabled = true;
    
    // Envoyer la requête de test
    fetch('<?= base_url('admin/configuration/test-email') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'test_email=' + encodeURIComponent(testEmail) + '&<?= csrf_token() ?>=<?= csrf_hash() ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Email de test envoyé avec succès !');
        } else {
            alert('❌ Erreur : ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('❌ Erreur lors du test');
    })
    .finally(() => {
        // Restaurer le bouton
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}
</script>

<?= $this->endSection() ?>


