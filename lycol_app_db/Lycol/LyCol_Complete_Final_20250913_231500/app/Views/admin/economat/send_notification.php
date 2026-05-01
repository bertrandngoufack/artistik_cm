<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <i class="fas fa-paper-plane"></i>
                Envoyer une Notification
            </h1>
        </div>
        <div class="column is-narrow">
            <a href="<?= base_url('admin/economat/notifications') ?>" class="button is-light">
                <i class="fas fa-arrow-left"></i>
                Retour aux notifications
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="notification is-danger">
            <button class="delete"></button>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="columns">
        <div class="column is-8">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-edit"></i>
                        Nouvelle Notification
                    </p>
                </header>
                <div class="card-content">
                    <form action="<?= base_url('admin/economat/notifications/send') ?>" method="post">
                        <div class="field">
                            <label class="label">Type de notification</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="type" required>
                                        <option value="">Sélectionner un type</option>
                                        <option value="payment_reminder">Rappel de paiement</option>
                                        <option value="payment_confirmation">Confirmation de paiement</option>
                                        <option value="overdue_payment">Paiement en retard</option>
                                        <option value="general">Notification générale</option>
                                        <option value="announcement">Annonce importante</option>
                                        <option value="reminder">Rappel général</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Destinataires</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="recipients" required>
                                        <option value="">Sélectionner les destinataires</option>
                                        <option value="all">Tous les parents</option>
                                        <option value="specific">Parents spécifiques</option>
                                        <option value="class">Classe spécifique</option>
                                        <option value="grade">Niveau spécifique</option>
                                        <option value="overdue">Parents avec paiements en retard</option>
                                        <option value="partial">Parents avec paiements partiels</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field" id="specific-recipients" style="display: none;">
                            <label class="label">Sélectionner les parents spécifiques</label>
                            <div class="control">
                                <div class="select is-multiple is-fullwidth">
                                    <select multiple name="specific_parents[]" size="5">
                                        <option value="1">Jean Dupont (Parent de Marie Dupont)</option>
                                        <option value="2">Marie Martin (Parent de Pierre Martin)</option>
                                        <option value="3">Paul Durand (Parent de Sophie Durand)</option>
                                        <option value="4">Anne Moreau (Parent de Thomas Moreau)</option>
                                        <option value="5">Pierre Leroy (Parent de Julie Leroy)</option>
                                    </select>
                                </div>
                                <p class="help">Maintenez Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs parents</p>
                            </div>
                        </div>

                        <div class="field" id="class-selection" style="display: none;">
                            <label class="label">Sélectionner la classe</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="class_id">
                                        <option value="">Choisir une classe</option>
                                        <option value="1">6ème A</option>
                                        <option value="2">6ème B</option>
                                        <option value="3">5ème A</option>
                                        <option value="4">5ème B</option>
                                        <option value="5">4ème A</option>
                                        <option value="6">4ème B</option>
                                        <option value="7">3ème A</option>
                                        <option value="8">3ème B</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field" id="grade-selection" style="display: none;">
                            <label class="label">Sélectionner le niveau</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="grade_id">
                                        <option value="">Choisir un niveau</option>
                                        <option value="6">6ème</option>
                                        <option value="5">5ème</option>
                                        <option value="4">4ème</option>
                                        <option value="3">3ème</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Sujet</label>
                            <div class="control">
                                <input class="input" type="text" name="subject" placeholder="Sujet de la notification..." required>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Message</label>
                            <div class="control">
                                <textarea class="textarea" name="message" placeholder="Contenu de la notification..." rows="6" required></textarea>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Méthodes d'envoi</label>
                            <div class="control">
                                <label class="checkbox">
                                    <input type="checkbox" name="send_email" value="1" checked>
                                    Email
                                </label>
                                <br>
                                <label class="checkbox">
                                    <input type="checkbox" name="send_sms" value="1">
                                    SMS
                                </label>
                                <br>
                                <label class="checkbox">
                                    <input type="checkbox" name="send_whatsapp" value="1">
                                    WhatsApp
                                </label>
                                <br>
                                <label class="checkbox">
                                    <input type="checkbox" name="send_push" value="1">
                                    Notification push (application mobile)
                                </label>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Programmer l'envoi</label>
                            <div class="control">
                                <label class="radio">
                                    <input type="radio" name="schedule" value="now" checked>
                                    Envoyer maintenant
                                </label>
                                <br>
                                <label class="radio">
                                    <input type="radio" name="schedule" value="later">
                                    Programmer pour plus tard
                                </label>
                            </div>
                        </div>

                        <div class="field" id="schedule-time" style="display: none;">
                            <label class="label">Date et heure d'envoi</label>
                            <div class="control">
                                <input class="input" type="datetime-local" name="scheduled_at" value="<?= date('Y-m-d\TH:i') ?>">
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Priorité</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="priority">
                                        <option value="low">Faible</option>
                                        <option value="normal" selected>Normale</option>
                                        <option value="high">Élevée</option>
                                        <option value="urgent">Urgente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <i class="fas fa-paper-plane"></i>
                                    Envoyer la notification
                                </button>
                            </div>
                            <div class="control">
                                <button type="button" class="button is-info" onclick="previewNotification()">
                                    <i class="fas fa-eye"></i>
                                    Aperçu
                                </button>
                            </div>
                            <div class="control">
                                <a href="<?= base_url('admin/economat/notifications') ?>" class="button is-light">
                                    Annuler
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="column is-4">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-info-circle"></i>
                        Informations
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <h6>Types de notifications</h6>
                        <ul>
                            <li><strong>Rappel de paiement :</strong> Pour rappeler les paiements en attente</li>
                            <li><strong>Confirmation :</strong> Pour confirmer un paiement reçu</li>
                            <li><strong>Paiement en retard :</strong> Pour les paiements échus</li>
                            <li><strong>Générale :</strong> Pour toute autre information</li>
                        </ul>

                        <h6>Méthodes d'envoi</h6>
                        <ul>
                            <li><strong>Email :</strong> Envoi détaillé avec pièces jointes</li>
                            <li><strong>SMS :</strong> Message court et direct</li>
                            <li><strong>WhatsApp :</strong> Via WhatsApp Business API</li>
                            <li><strong>Push :</strong> Notification dans l'app mobile</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <header class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-file-alt"></i>
                        Modèles prédéfinis
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <div class="buttons are-small">
                            <button class="button is-light" onclick="loadTemplate('payment_reminder')">
                                Rappel de paiement
                            </button>
                            <button class="button is-light" onclick="loadTemplate('overdue_payment')">
                                Paiement en retard
                            </button>
                            <button class="button is-light" onclick="loadTemplate('announcement')">
                                Annonce importante
                            </button>
                            <button class="button is-light" onclick="loadTemplate('general')">
                                Notification générale
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <header class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-chart-bar"></i>
                        Statistiques d'envoi
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <p><strong>Destinataires estimés :</strong> <span id="estimated-recipients">0</span></p>
                        <p><strong>Coût estimé :</strong> <span id="estimated-cost">0 FCFA</span></p>
                        <p><strong>Temps d'envoi estimé :</strong> <span id="estimated-time">0 min</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour l'aperçu -->
<div class="modal" id="preview-modal">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Aperçu de la notification</p>
            <button class="delete" aria-label="close" onclick="closePreview()"></button>
        </header>
        <section class="modal-card-body">
            <div id="preview-content">
                <!-- Le contenu de l'aperçu sera inséré ici -->
            </div>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success" onclick="sendNotification()">Envoyer</button>
            <button class="button" onclick="closePreview()">Fermer</button>
        </footer>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Supprimer les notifications
    document.querySelectorAll('.notification .delete').forEach(function(button) {
        button.addEventListener('click', function() {
            this.parentNode.remove();
        });
    });

    // Gestion des destinataires
    document.querySelector('select[name="recipients"]').addEventListener('change', function() {
        const specificRecipients = document.getElementById('specific-recipients');
        const classSelection = document.getElementById('class-selection');
        const gradeSelection = document.getElementById('grade-selection');

        // Masquer tous les champs
        specificRecipients.style.display = 'none';
        classSelection.style.display = 'none';
        gradeSelection.style.display = 'none';

        // Afficher le champ approprié
        if (this.value === 'specific') {
            specificRecipients.style.display = 'block';
        } else if (this.value === 'class') {
            classSelection.style.display = 'block';
        } else if (this.value === 'grade') {
            gradeSelection.style.display = 'block';
        }

        updateEstimates();
    });

    // Gestion de la programmation
    document.querySelectorAll('input[name="schedule"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            const scheduleTime = document.getElementById('schedule-time');
            if (this.value === 'later') {
                scheduleTime.style.display = 'block';
            } else {
                scheduleTime.style.display = 'none';
            }
        });
    });

    // Mise à jour des estimations
    document.querySelectorAll('select, input').forEach(function(element) {
        element.addEventListener('change', updateEstimates);
    });
});

function loadTemplate(type) {
    const templates = {
        'payment_reminder': {
            subject: 'Rappel de paiement - Frais de scolarité',
            message: 'Bonjour,\n\nNous vous rappelons que le paiement des frais de scolarité pour le mois en cours est attendu.\n\nMontant : [MONTANT] FCFA\nDate limite : [DATE_LIMITE]\n\nMerci de procéder au règlement dans les plus brefs délais.\n\nCordialement,\nL\'équipe administrative'
        },
        'overdue_payment': {
            subject: 'URGENT - Paiement en retard',
            message: 'Bonjour,\n\nNous constatons que le paiement des frais de scolarité est en retard.\n\nMontant dû : [MONTANT] FCFA\nDate d\'échéance : [DATE_ECHEANCE]\n\nVeuillez procéder au règlement immédiatement pour éviter toute sanction.\n\nCordialement,\nL\'équipe administrative'
        },
        'announcement': {
            subject: 'Annonce importante',
            message: 'Bonjour,\n\nNous avons le plaisir de vous informer d\'une annonce importante concernant l\'établissement.\n\n[CONTENU_ANNONCE]\n\nMerci de votre attention.\n\nCordialement,\nLa direction'
        },
        'general': {
            subject: 'Information générale',
            message: 'Bonjour,\n\nNous souhaitons vous informer de la situation suivante :\n\n[INFORMATION]\n\nMerci de votre compréhension.\n\nCordialement,\nL\'équipe administrative'
        }
    };

    if (templates[type]) {
        document.querySelector('input[name="subject"]').value = templates[type].subject;
        document.querySelector('textarea[name="message"]').value = templates[type].message;
    }
}

function updateEstimates() {
    const recipients = document.querySelector('select[name="recipients"]').value;
    const sendEmail = document.querySelector('input[name="send_email"]').checked;
    const sendSms = document.querySelector('input[name="send_sms"]').checked;
    const sendWhatsapp = document.querySelector('input[name="send_whatsapp"]').checked;

    let estimatedRecipients = 0;
    let estimatedCost = 0;

    // Estimation du nombre de destinataires
    switch (recipients) {
        case 'all':
            estimatedRecipients = 150;
            break;
        case 'specific':
            const selectedParents = document.querySelectorAll('select[name="specific_parents[]"] option:checked').length;
            estimatedRecipients = selectedParents;
            break;
        case 'class':
            estimatedRecipients = 30;
            break;
        case 'grade':
            estimatedRecipients = 120;
            break;
        case 'overdue':
            estimatedRecipients = 25;
            break;
        case 'partial':
            estimatedRecipients = 15;
            break;
    }

    // Calcul du coût
    if (sendEmail) {
        estimatedCost += estimatedRecipients * 5; // 5 FCFA par email
    }
    if (sendSms) {
        estimatedCost += estimatedRecipients * 25; // 25 FCFA par SMS
    }
    if (sendWhatsapp) {
        estimatedCost += estimatedRecipients * 10; // 10 FCFA par WhatsApp
    }

    // Temps d'envoi estimé
    const estimatedTime = Math.ceil(estimatedRecipients / 10); // 10 envois par minute

    // Mise à jour de l'affichage
    document.getElementById('estimated-recipients').textContent = estimatedRecipients;
    document.getElementById('estimated-cost').textContent = estimatedCost + ' FCFA';
    document.getElementById('estimated-time').textContent = estimatedTime + ' min';
}

function previewNotification() {
    const subject = document.querySelector('input[name="subject"]').value;
    const message = document.querySelector('textarea[name="message"]').value;
    const type = document.querySelector('select[name="type"]').value;
    const recipients = document.querySelector('select[name="recipients"]').value;

    const previewContent = `
        <div class="notification is-info">
            <strong>Type :</strong> ${type}<br>
            <strong>Destinataires :</strong> ${recipients}<br>
            <strong>Sujet :</strong> ${subject}
        </div>
        <div class="content">
            <h6>Message :</h6>
            <div class="box">
                <pre style="white-space: pre-wrap;">${message}</pre>
            </div>
        </div>
    `;

    document.getElementById('preview-content').innerHTML = previewContent;
    document.getElementById('preview-modal').classList.add('is-active');
}

function closePreview() {
    document.getElementById('preview-modal').classList.remove('is-active');
}

function sendNotification() {
    // Soumettre le formulaire
    document.querySelector('form').submit();
}
</script>

<?= $this->endSection() ?>



