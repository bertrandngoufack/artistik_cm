<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Envoi de Bulletins</h1>
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
        <!-- Formulaire d'envoi de bulletins -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-envelope"></i></span>
                    Envoi de Bulletins par SMS/WhatsApp
                </p>
            </header>
            <div class="card-content">
                <form method="POST" action="<?= base_url('admin/messagerie/process-bulletin') ?>">
                    <?= csrf_field() ?>
                    
                    <div class="columns">
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Classe</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="class_id" required>
                                            <option value="">Sélectionner une classe</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?= $class['id'] ?>"><?= esc($class['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Période Académique</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="period_id" required>
                                            <option value="">Sélectionner une période</option>
                                            <?php foreach ($periods as $period): ?>
                                                <option value="<?= $period['id'] ?>"><?= esc($period['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Canal d'envoi</label>
                        <div class="control">
                            <label class="radio">
                                <input type="radio" name="channel" value="sms" checked>
                                SMS
                            </label>
                            <label class="radio">
                                <input type="radio" name="channel" value="whatsapp">
                                WhatsApp Business
                            </label>
                            <label class="radio">
                                <input type="radio" name="channel" value="both">
                                SMS + WhatsApp
                            </label>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Template de message</label>
                        <div class="control">
                            <textarea class="textarea" name="message_template" rows="6" 
                                      placeholder="Template du message pour l'envoi de bulletins..." required>Bonjour {parent_name},

Les bulletins de {student_name} pour la période {period_name} sont disponibles.

Moyenne générale : {average}
Rang : {rank}/{total_students}

Consultez le bulletin complet sur votre espace parent.

Cordialement,
L'équipe pédagogique</textarea>
                        </div>
                        <p class="help">Variables disponibles : {parent_name}, {student_name}, {period_name}, {average}, {rank}, {total_students}</p>
                    </div>

                    <div class="field">
                        <div class="control">
                            <label class="checkbox">
                                <input type="checkbox" name="include_attachments" value="1">
                                Inclure le bulletin en pièce jointe (WhatsApp uniquement)
                            </label>
                        </div>
                    </div>

                    <div class="field">
                        <div class="control">
                            <label class="checkbox">
                                <input type="checkbox" name="send_reminder" value="1">
                                Envoyer un rappel après 3 jours si non consulté
                            </label>
                        </div>
                    </div>

                    <div class="field is-grouped">
                        <div class="control">
                            <button type="submit" class="button is-primary">
                                <span class="icon"><i class="fas fa-paper-plane"></i></span>
                                <span>Envoyer les Bulletins</span>
                            </button>
                        </div>
                        <div class="control">
                            <button type="button" class="button is-info" onclick="previewMessage()">
                                <span class="icon"><i class="fas fa-eye"></i></span>
                                <span>Aperçu</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="column is-4">
        <!-- Statistiques et informations -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-chart-bar"></i></span>
                    Statistiques d'envoi
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Bulletins envoyés ce mois</p>
                                <p class="title">1,234</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Taux de livraison</p>
                                <p class="title has-text-success">98.5%</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Taux de consultation</p>
                                <p class="title has-text-info">87.2%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des envois -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-history"></i></span>
                    Derniers envois
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <div class="notification is-light">
                        <p><strong>6ème A - 2ème Trimestre</strong></p>
                        <p class="is-size-7">Envoyé le 24/08/2025 à 14:30</p>
                        <p class="is-size-7">32 bulletins envoyés, 31 livrés</p>
                    </div>
                    
                    <div class="notification is-light">
                        <p><strong>5ème B - 2ème Trimestre</strong></p>
                        <p class="is-size-7">Envoyé le 23/08/2025 à 15:45</p>
                        <p class="is-size-7">28 bulletins envoyés, 28 livrés</p>
                    </div>
                    
                    <div class="notification is-light">
                        <p><strong>4ème A - 2ème Trimestre</strong></p>
                        <p class="is-size-7">Envoyé le 22/08/2025 à 16:20</p>
                        <p class="is-size-7">30 bulletins envoyés, 29 livrés</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'aperçu -->
<div id="previewModal" class="modal">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Aperçu du message</p>
            <button class="delete" aria-label="close" onclick="closePreview()"></button>
        </header>
        <section class="modal-card-body">
            <div id="previewContent">
                <!-- Le contenu de l'aperçu sera inséré ici -->
            </div>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success" onclick="closePreview()">Fermer</button>
        </footer>
    </div>
</div>

<script>
function previewMessage() {
    const template = document.querySelector('textarea[name="message_template"]').value;
    const channel = document.querySelector('input[name="channel"]:checked').value;
    
    // Simulation d'un aperçu avec des données d'exemple
    const preview = template
        .replace('{parent_name}', 'M. Dupont')
        .replace('{student_name}', 'Jean Dupont')
        .replace('{period_name}', '2ème Trimestre')
        .replace('{average}', '15.5/20')
        .replace('{rank}', '5')
        .replace('{total_students}', '32');
    
    document.getElementById('previewContent').innerHTML = `
        <div class="notification is-info">
            <p><strong>Canal :</strong> ${channel.toUpperCase()}</p>
        </div>
        <div class="box">
            <pre style="white-space: pre-wrap; font-family: inherit;">${preview}</pre>
        </div>
    `;
    
    document.getElementById('previewModal').classList.add('is-active');
}

function closePreview() {
    document.getElementById('previewModal').classList.remove('is-active');
}

// Fermer le modal en cliquant sur le fond
document.querySelector('.modal-background').addEventListener('click', closePreview);
</script>

<?= $this->endSection() ?>







