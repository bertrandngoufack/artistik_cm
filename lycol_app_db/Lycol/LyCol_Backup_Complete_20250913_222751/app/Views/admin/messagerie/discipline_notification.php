<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Notification de Discipline</h1>
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
        <!-- Formulaire de notification de discipline -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-exclamation-triangle"></i></span>
                    Envoi de Notifications de Discipline
                </p>
            </header>
            <div class="card-content">
                <form method="POST" action="<?= base_url('admin/messagerie/process-discipline') ?>">
                    <?= csrf_field() ?>
                    
                    <div class="field">
                        <label class="label">Type de discipline</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="discipline_type" required>
                                    <option value="">Sélectionner le type</option>
                                    <?php foreach ($disciplineTypes as $key => $value): ?>
                                        <option value="<?= $key ?>"><?= esc($value) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Élèves concernés</label>
                        <div class="control">
                            <div class="select is-fullwidth is-multiple">
                                <select name="student_ids[]" multiple required>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?= $student['id'] ?>">
                                            <?= esc($student['name'] . ' ' . $student['firstname']) ?> (<?= esc($student['class']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <p class="help">Maintenez Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs élèves</p>
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
                        <label class="label">Message personnalisé</label>
                        <div class="control">
                            <textarea class="textarea" name="message_content" rows="6" 
                                      placeholder="Message personnalisé pour les parents..." required>Bonjour {parent_name},

Nous vous informons que {student_name} a eu un incident de discipline de type {discipline_type} aujourd'hui.

{details}

Nous vous invitons à prendre contact avec l'établissement pour plus d'informations.

Cordialement,
L'équipe pédagogique</textarea>
                        </div>
                        <p class="help">Variables disponibles : {parent_name}, {student_name}, {discipline_type}, {details}</p>
                    </div>

                    <div class="field">
                        <div class="control">
                            <label class="checkbox">
                                <input type="checkbox" name="urgent" value="1">
                                Marquer comme urgent (envoi immédiat)
                            </label>
                        </div>
                    </div>

                    <div class="field">
                        <div class="control">
                            <label class="checkbox">
                                <input type="checkbox" name="send_copy_admin" value="1">
                                Envoyer une copie à l'administration
                            </label>
                        </div>
                    </div>

                    <div class="field">
                        <div class="control">
                            <label class="checkbox">
                                <input type="checkbox" name="schedule_followup" value="1">
                                Programmer un suivi dans 3 jours
                            </label>
                        </div>
                    </div>

                    <div class="field is-grouped">
                        <div class="control">
                            <button type="submit" class="button is-warning">
                                <span class="icon"><i class="fas fa-paper-plane"></i></span>
                                <span>Envoyer les Notifications</span>
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
        <!-- Statistiques des incidents -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-chart-pie"></i></span>
                    Statistiques des incidents
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Incidents ce mois</p>
                                <p class="title has-text-warning">45</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Absences</p>
                                <p class="title">23</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Retards</p>
                                <p class="title">12</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Comportement</p>
                                <p class="title">8</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Sanctions</p>
                                <p class="title has-text-danger">2</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dernières notifications -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-history"></i></span>
                    Dernières notifications
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <div class="notification is-light">
                        <p><strong>Absence - Jean Dupont</strong></p>
                        <p class="is-size-7">Envoyé le 25/08/2025 à 09:15</p>
                        <p class="is-size-7">SMS + WhatsApp</p>
                    </div>
                    
                    <div class="notification is-light">
                        <p><strong>Retard - Marie Martin</strong></p>
                        <p class="is-size-7">Envoyé le 24/08/2025 à 08:30</p>
                        <p class="is-size-7">SMS uniquement</p>
                    </div>
                    
                    <div class="notification is-light">
                        <p><strong>Comportement - Pierre Bernard</strong></p>
                        <p class="is-size-7">Envoyé le 23/08/2025 à 14:45</p>
                        <p class="is-size-7">WhatsApp uniquement</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Templates rapides -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-lightning-bolt"></i></span>
                    Templates rapides
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <button class="button is-small is-fullwidth" onclick="loadTemplate('absence')">
                        Absence non justifiée
                    </button>
                    <button class="button is-small is-fullwidth" onclick="loadTemplate('retard')">
                        Retard répété
                    </button>
                    <button class="button is-small is-fullwidth" onclick="loadTemplate('comportement')">
                        Problème de comportement
                    </button>
                    <button class="button is-small is-fullwidth" onclick="loadTemplate('travail')">
                        Travail non rendu
                    </button>
                    <button class="button is-small is-fullwidth" onclick="loadTemplate('sanction')">
                        Sanction disciplinaire
                    </button>
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
    const template = document.querySelector('textarea[name="message_content"]').value;
    const channel = document.querySelector('input[name="channel"]:checked').value;
    const disciplineType = document.querySelector('select[name="discipline_type"]').value;
    
    // Simulation d'un aperçu avec des données d'exemple
    const preview = template
        .replace('{parent_name}', 'M. Dupont')
        .replace('{student_name}', 'Jean Dupont')
        .replace('{discipline_type}', disciplineType || 'ABSENCE')
        .replace('{details}', 'L\'élève était absent ce matin sans justification.');
    
    document.getElementById('previewContent').innerHTML = `
        <div class="notification is-warning">
            <p><strong>Canal :</strong> ${channel.toUpperCase()}</p>
            <p><strong>Type :</strong> ${disciplineType || 'ABSENCE'}</p>
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

function loadTemplate(type) {
    const templates = {
        'absence': 'Bonjour {parent_name},\n\nNous vous informons que {student_name} était absent(e) aujourd\'hui sans justification.\n\nMerci de nous fournir un justificatif dans les plus brefs délais.\n\nCordialement,\nL\'équipe pédagogique',
        'retard': 'Bonjour {parent_name},\n\nNous vous informons que {student_name} est arrivé(e) en retard ce matin.\n\nCeci est le 3ème retard ce mois. Nous vous invitons à être plus vigilant.\n\nCordialement,\nL\'équipe pédagogique',
        'comportement': 'Bonjour {parent_name},\n\nNous vous informons que {student_name} a eu un problème de comportement en classe aujourd\'hui.\n\nNous vous invitons à prendre contact avec l\'établissement.\n\nCordialement,\nL\'équipe pédagogique',
        'travail': 'Bonjour {parent_name},\n\nNous vous informons que {student_name} n\'a pas rendu le travail demandé pour aujourd\'hui.\n\nMerci de vérifier le cahier de textes et de nous contacter si nécessaire.\n\nCordialement,\nL\'équipe pédagogique',
        'sanction': 'Bonjour {parent_name},\n\nNous vous informons qu\'une sanction disciplinaire a été prononcée à l\'encontre de {student_name}.\n\nNous vous convoquons pour un entretien. Merci de nous contacter.\n\nCordialement,\nL\'équipe pédagogique'
    };
    
    document.querySelector('textarea[name="message_content"]').value = templates[type];
}

// Fermer le modal en cliquant sur le fond
document.querySelector('.modal-background').addEventListener('click', closePreview);
</script>

<?= $this->endSection() ?>







