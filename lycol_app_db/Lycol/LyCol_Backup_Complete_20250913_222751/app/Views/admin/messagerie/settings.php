<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Configuration Messagerie</h1>
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
        <!-- Configuration générale -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-cogs"></i></span>
                    Configuration Générale
                </p>
            </header>
            <div class="card-content">
                <form method="POST" action="<?= base_url('admin/messagerie/settings/save') ?>">
                    <?= csrf_field() ?>
                    
                    <div class="field">
                        <label class="label">Nom de l'expéditeur par défaut</label>
                        <div class="control">
                            <input class="input" type="text" name="default_sender_name" 
                                   value="<?= esc($settings['default_sender_name'] ?? 'LYCOL - KISSAI SCHOOL') ?>" 
                                   placeholder="Nom de l'expéditeur">
                        </div>
                        <p class="help">Nom qui apparaîtra comme expéditeur des messages</p>
                    </div>

                    <div class="field">
                        <label class="label">Email de l'expéditeur par défaut</label>
                        <div class="control">
                            <input class="input" type="email" name="default_sender_email" 
                                   value="<?= esc($settings['default_sender_email'] ?? 'noreply@lycol.cm') ?>" 
                                   placeholder="Email de l'expéditeur">
                        </div>
                        <p class="help">Email qui sera utilisé pour l'envoi des messages</p>
                    </div>

                    <div class="field">
                        <label class="label">Limite de messages par jour</label>
                        <div class="control">
                            <input class="input" type="number" name="daily_message_limit" 
                                   value="<?= esc($settings['daily_message_limit'] ?? 1000) ?>" 
                                   min="1" max="10000">
                        </div>
                        <p class="help">Nombre maximum de messages pouvant être envoyés par jour</p>
                    </div>

                    <div class="field">
                        <label class="label">Taille maximale des pièces jointes (MB)</label>
                        <div class="control">
                            <input class="input" type="number" name="max_attachment_size" 
                                   value="<?= esc($settings['max_attachment_size'] ?? 10) ?>" 
                                   min="1" max="50">
                        </div>
                        <p class="help">Taille maximale autorisée pour les pièces jointes</p>
                    </div>

                    <div class="field">
                        <div class="control">
                            <label class="checkbox">
                                <input type="checkbox" name="enable_notifications" value="1" 
                                       <?= ($settings['enable_notifications'] ?? true) ? 'checked' : '' ?>>
                                Activer les notifications par email
                            </label>
                        </div>
                    </div>

                    <div class="field">
                        <div class="control">
                            <label class="checkbox">
                                <input type="checkbox" name="enable_sms" value="1" 
                                       <?= ($settings['enable_sms'] ?? false) ? 'checked' : '' ?>>
                                Activer l'envoi de SMS
                            </label>
                        </div>
                    </div>

                    <div class="field">
                        <div class="control">
                            <label class="checkbox">
                                <input type="checkbox" name="enable_whatsapp" value="1" 
                                       <?= ($settings['enable_whatsapp'] ?? false) ? 'checked' : '' ?>>
                                Activer l'envoi WhatsApp
                            </label>
                        </div>
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

        <!-- Configuration SMTP -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-envelope"></i></span>
                    Configuration SMTP
                </p>
            </header>
            <div class="card-content">
                <form method="POST" action="<?= base_url('admin/messagerie/settings/smtp') ?>">
                    <?= csrf_field() ?>
                    
                    <div class="columns">
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Serveur SMTP</label>
                                <div class="control">
                                    <input class="input" type="text" name="smtp_host" 
                                           value="<?= esc($settings['smtp_host'] ?? 'smtp.gmail.com') ?>" 
                                           placeholder="smtp.gmail.com">
                                </div>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Port SMTP</label>
                                <div class="control">
                                    <input class="input" type="number" name="smtp_port" 
                                           value="<?= esc($settings['smtp_port'] ?? 587) ?>" 
                                           placeholder="587">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Nom d'utilisateur SMTP</label>
                                <div class="control">
                                    <input class="input" type="text" name="smtp_username" 
                                           value="<?= esc($settings['smtp_username'] ?? '') ?>" 
                                           placeholder="votre-email@gmail.com">
                                </div>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Mot de passe SMTP</label>
                                <div class="control">
                                    <input class="input" type="password" name="smtp_password" 
                                           value="<?= esc($settings['smtp_password'] ?? '') ?>" 
                                           placeholder="Mot de passe">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <div class="control">
                            <label class="checkbox">
                                <input type="checkbox" name="smtp_encryption" value="1" 
                                       <?= ($settings['smtp_encryption'] ?? true) ? 'checked' : '' ?>>
                                Utiliser le chiffrement SSL/TLS
                            </label>
                        </div>
                    </div>

                    <div class="field is-grouped">
                        <div class="control">
                            <button type="submit" class="button is-info">
                                <span class="icon"><i class="fas fa-save"></i></span>
                                <span>Sauvegarder SMTP</span>
                            </button>
                        </div>
                        <div class="control">
                            <a href="<?= base_url('admin/messagerie/settings/test-smtp') ?>" class="button is-warning">
                                <span class="icon"><i class="fas fa-paper-plane"></i></span>
                                <span>Tester SMTP</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Configuration SMS -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-mobile-alt"></i></span>
                    Configuration SMS
                </p>
            </header>
            <div class="card-content">
                <form method="POST" action="<?= base_url('admin/messagerie/settings/sms') ?>">
                    <?= csrf_field() ?>
                    
                    <div class="field">
                        <label class="label">Fournisseur SMS</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="sms_provider">
                                    <option value="twilio" <?= ($settings['sms_provider'] ?? '') === 'twilio' ? 'selected' : '' ?>>Twilio</option>
                                    <option value="africastalking" <?= ($settings['sms_provider'] ?? '') === 'africastalking' ? 'selected' : '' ?>>Africa's Talking</option>
                                    <option value="orange" <?= ($settings['sms_provider'] ?? '') === 'orange' ? 'selected' : '' ?>>Orange SMS</option>
                                    <option value="mtn" <?= ($settings['sms_provider'] ?? '') === 'mtn' ? 'selected' : '' ?>>MTN SMS</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Clé API</label>
                                <div class="control">
                                    <input class="input" type="text" name="sms_api_key" 
                                           value="<?= esc($settings['sms_api_key'] ?? '') ?>" 
                                           placeholder="Clé API SMS">
                                </div>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Clé secrète</label>
                                <div class="control">
                                    <input class="input" type="password" name="sms_api_secret" 
                                           value="<?= esc($settings['sms_api_secret'] ?? '') ?>" 
                                           placeholder="Clé secrète SMS">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Numéro d'expéditeur</label>
                        <div class="control">
                            <input class="input" type="text" name="sms_sender_id" 
                                   value="<?= esc($settings['sms_sender_id'] ?? 'LYCOL') ?>" 
                                   placeholder="LYCOL">
                        </div>
                        <p class="help">Nom ou numéro qui apparaîtra comme expéditeur des SMS</p>
                    </div>

                    <div class="field is-grouped">
                        <div class="control">
                            <button type="submit" class="button is-info">
                                <span class="icon"><i class="fas fa-save"></i></span>
                                <span>Sauvegarder SMS</span>
                            </button>
                        </div>
                        <div class="control">
                            <a href="<?= base_url('admin/messagerie/settings/test-sms') ?>" class="button is-warning">
                                <span class="icon"><i class="fas fa-mobile-alt"></i></span>
                                <span>Tester SMS</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Configuration WhatsApp Business -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fab fa-whatsapp"></i></span>
                    Configuration WhatsApp Business API
                </p>
            </header>
            <div class="card-content">
                <form method="POST" action="<?= base_url('admin/messagerie/settings/whatsapp') ?>">
                    <?= csrf_field() ?>
                    
                    <div class="field">
                        <label class="label">Fournisseur WhatsApp Business</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="whatsapp_provider">
                                    <option value="twilio" <?= ($settings['whatsapp_provider'] ?? '') === 'twilio' ? 'selected' : '' ?>>Twilio WhatsApp</option>
                                    <option value="meta" <?= ($settings['whatsapp_provider'] ?? '') === 'meta' ? 'selected' : '' ?>>Meta WhatsApp Business API</option>
                                    <option value="africastalking" <?= ($settings['whatsapp_provider'] ?? '') === 'africastalking' ? 'selected' : '' ?>>Africa's Talking WhatsApp</option>
                                    <option value="messagebird" <?= ($settings['whatsapp_provider'] ?? '') === 'messagebird' ? 'selected' : '' ?>>MessageBird WhatsApp</option>
                                </select>
                            </div>
                        </div>
                        <p class="help">Sélectionnez votre fournisseur WhatsApp Business API</p>
                    </div>

                    <div class="columns">
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Account SID</label>
                                <div class="control">
                                    <input class="input" type="text" name="whatsapp_account_sid" 
                                           value="<?= esc($settings['whatsapp_account_sid'] ?? '') ?>" 
                                           placeholder="AC...">
                                </div>
                                <p class="help">Account SID de votre compte WhatsApp Business</p>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Auth Token</label>
                                <div class="control">
                                    <input class="input" type="password" name="whatsapp_auth_token" 
                                           value="<?= esc($settings['whatsapp_auth_token'] ?? '') ?>" 
                                           placeholder="Token d'authentification">
                                </div>
                                <p class="help">Token d'authentification WhatsApp Business</p>
                            </div>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Numéro WhatsApp Business</label>
                                <div class="control">
                                    <input class="input" type="text" name="whatsapp_phone_number" 
                                           value="<?= esc($settings['whatsapp_phone_number'] ?? '') ?>" 
                                           placeholder="+237123456789">
                                </div>
                                <p class="help">Numéro WhatsApp Business vérifié et approuvé</p>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Webhook URL</label>
                                <div class="control">
                                    <input class="input" type="url" name="whatsapp_webhook_url" 
                                           value="<?= esc($settings['whatsapp_webhook_url'] ?? base_url('admin/messagerie/webhook/whatsapp')) ?>" 
                                           placeholder="URL du webhook">
                                </div>
                                <p class="help">URL pour recevoir les notifications WhatsApp</p>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Template de message par défaut</label>
                        <div class="control">
                            <textarea class="textarea" name="whatsapp_default_template" 
                                      rows="4" 
                                      placeholder="Template par défaut pour WhatsApp..."><?= esc($settings['whatsapp_default_template'] ?? '') ?></textarea>
                        </div>
                        <p class="help">Template par défaut pour les messages WhatsApp (variables: {name}, {message}, {date})</p>
                    </div>

                    <div class="columns">
                        <div class="column is-6">
                            <div class="field">
                                <div class="control">
                                    <label class="checkbox">
                                        <input type="checkbox" name="whatsapp_media_enabled" value="1" 
                                               <?= ($settings['whatsapp_media_enabled'] ?? false) ? 'checked' : '' ?>>
                                        Activer l'envoi de médias
                                    </label>
                                </div>
                                <p class="help">Images, documents, PDF</p>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <div class="control">
                                    <label class="checkbox">
                                        <input type="checkbox" name="whatsapp_buttons_enabled" value="1" 
                                               <?= ($settings['whatsapp_buttons_enabled'] ?? false) ? 'checked' : '' ?>>
                                        Activer les boutons interactifs
                                    </label>
                                </div>
                                <p class="help">Boutons de réponse rapide</p>
                            </div>
                        </div>
                    </div>

                    <div class="field is-grouped">
                        <div class="control">
                            <button type="submit" class="button is-success">
                                <span class="icon"><i class="fas fa-save"></i></span>
                                <span>Sauvegarder WhatsApp</span>
                            </button>
                        </div>
                        <div class="control">
                            <a href="<?= base_url('admin/messagerie/settings/test-whatsapp') ?>" class="button is-success">
                                <span class="icon"><i class="fab fa-whatsapp"></i></span>
                                <span>Tester WhatsApp</span>
                            </a>
                        </div>
                        <div class="control">
                            <a href="<?= base_url('admin/messagerie/settings/whatsapp-templates') ?>" class="button is-info">
                                <span class="icon"><i class="fas fa-list"></i></span>
                                <span>Gérer les Templates</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="column is-4">
        <!-- Statut des services -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-chart-line"></i></span>
                    Statut des Services
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Email</p>
                                <p class="title">
                                    <?php if ($settings['enable_notifications'] ?? true): ?>
                                        <span class="has-text-success">✅ Actif</span>
                                    <?php else: ?>
                                        <span class="has-text-danger">❌ Inactif</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">SMS</p>
                                <p class="title">
                                    <?php if ($settings['enable_sms'] ?? false): ?>
                                        <span class="has-text-success">✅ Actif</span>
                                    <?php else: ?>
                                        <span class="has-text-danger">❌ Inactif</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">WhatsApp</p>
                                <p class="title">
                                    <?php if ($settings['enable_whatsapp'] ?? false): ?>
                                        <span class="has-text-success">✅ Actif</span>
                                    <?php else: ?>
                                        <span class="has-text-danger">❌ Inactif</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques d'utilisation -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-chart-bar"></i></span>
                    Statistiques d'Utilisation
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Messages aujourd'hui</p>
                                <p class="title"><?= $stats['today_messages'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Messages ce mois</p>
                                <p class="title"><?= $stats['month_messages'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Limite quotidienne</p>
                                <p class="title"><?= $settings['daily_message_limit'] ?? 1000 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-bolt"></i></span>
                    Actions Rapides
                </p>
            </header>
            <div class="card-content">
                <div class="buttons">
                    <a href="<?= base_url('admin/messagerie/settings/backup') ?>" class="button is-info is-fullwidth">
                        <span class="icon"><i class="fas fa-download"></i></span>
                        <span>Sauvegarder la configuration</span>
                    </a>
                    
                    <a href="<?= base_url('admin/messagerie/settings/restore') ?>" class="button is-warning is-fullwidth">
                        <span class="icon"><i class="fas fa-upload"></i></span>
                        <span>Restaurer la configuration</span>
                    </a>
                    
                    <a href="<?= base_url('admin/messagerie/settings/reset') ?>" class="button is-danger is-fullwidth"
                       onclick="return confirm('Êtes-vous sûr de vouloir réinitialiser la configuration ?')">
                        <span class="icon"><i class="fas fa-undo"></i></span>
                        <span>Réinitialiser</span>
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
                    <h6>Configuration SMTP :</h6>
                    <ul>
                        <li>Gmail : smtp.gmail.com, port 587</li>
                        <li>Outlook : smtp-mail.outlook.com, port 587</li>
                        <li>Yahoo : smtp.mail.yahoo.com, port 587</li>
                    </ul>
                    
                    <h6>Configuration SMS :</h6>
                    <ul>
                        <li><strong>Twilio</strong> : International</li>
                        <li><strong>Africa's Talking</strong> : Afrique</li>
                        <li><strong>Orange/MTN</strong> : Cameroun</li>
                    </ul>
                    
                    <h6>Configuration WhatsApp Business API :</h6>
                    <ul>
                        <li><strong>Twilio WhatsApp</strong> : API WhatsApp via Twilio</li>
                        <li><strong>Meta WhatsApp Business API</strong> : API officielle Meta</li>
                        <li><strong>Africa's Talking WhatsApp</strong> : WhatsApp pour l'Afrique</li>
                        <li><strong>MessageBird WhatsApp</strong> : Solution européenne</li>
                    </ul>
                    
                    <h6>Prérequis WhatsApp Business :</h6>
                    <ul>
                        <li>Numéro WhatsApp Business vérifié</li>
                        <li>Account SID et Auth Token</li>
                        <li>Webhook URL configurée</li>
                        <li>Templates de messages approuvés</li>
                        <li>Compte Business vérifié</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
