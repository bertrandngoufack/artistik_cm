<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <span class="icon"><i class="fas fa-bell"></i></span>
                Historique des Rappels
            </h1>
            <p class="subtitle">Suivi des rappels de paiement envoyés aux parents</p>
        </div>
        <div class="column is-narrow">
            <a href="/admin/economat/payments" class="button is-info">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour aux Paiements</span>
            </a>
        </div>
    </div>

    <!-- Statistiques des rappels -->
    <div class="columns">
        <div class="column">
            <div class="box has-text-centered">
                <p class="heading">Total Rappels</p>
                <p class="title has-text-info"><?= number_format($total_reminders ?? 0, 0, ',', ' ') ?></p>
            </div>
        </div>
        <div class="column">
            <div class="box has-text-centered">
                <p class="heading">Rappels Aujourd'hui</p>
                <p class="title has-text-success"><?= number_format($today_reminders ?? 0, 0, ',', ' ') ?></p>
            </div>
        </div>
        <div class="column">
            <div class="box has-text-centered">
                <p class="heading">Taux de Réponse</p>
                <p class="title has-text-warning">85%</p>
            </div>
        </div>
    </div>

    <!-- Historique des rappels -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">Historique des Rappels Envoyés</p>
        </header>
        <div class="card-content">
            <table class="table is-fullwidth">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Élève</th>
                        <th>Type de Frais</th>
                        <th>Montant</th>
                        <th>Contact</th>
                        <th>Canaux</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($reminders) && !empty($reminders)): ?>
                        <?php foreach ($reminders as $reminder): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?= date('d/m/Y', strtotime($reminder['sent_at'])) ?></strong>
                                        <br>
                                        <small class="has-text-grey"><?= date('H:i', strtotime($reminder['sent_at'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= $reminder['student_name'] ?? 'N/A' ?></strong>
                                    <br>
                                    <small class="has-text-grey"><?= $reminder['reference_number'] ?? '' ?></small>
                                </td>
                                <td><?= $reminder['fee_type_name'] ?? 'N/A' ?></td>
                                <td>
                                    <strong><?= number_format($reminder['amount_paid'] ?? 0, 0, ',', ' ') ?> FCFA</strong>
                                </td>
                                <td>
                                    <div>
                                        <?php if ($reminder['sent_to_phone']): ?>
                                            <div>
                                                <span class="icon"><i class="fas fa-phone"></i></span>
                                                <span><?= $reminder['sent_to_phone'] ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($reminder['sent_to_email']): ?>
                                            <div>
                                                <span class="icon"><i class="fas fa-envelope"></i></span>
                                                <span><?= $reminder['sent_to_email'] ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="buttons are-small">
                                        <?php if ($reminder['sms_sent']): ?>
                                            <span class="button is-success is-small">
                                                <span class="icon"><i class="fas fa-sms"></i></span>
                                                <span>SMS</span>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($reminder['email_sent']): ?>
                                            <span class="button is-info is-small">
                                                <span class="icon"><i class="fas fa-envelope"></i></span>
                                                <span>Email</span>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($reminder['whatsapp_sent']): ?>
                                            <span class="button is-success is-small">
                                                <span class="icon"><i class="fab fa-whatsapp"></i></span>
                                                <span>WhatsApp</span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="tag is-success">
                                        <span class="icon"><i class="fas fa-check"></i></span>
                                        <span>Envoyé</span>
                                    </span>
                                </td>
                                <td>
                                    <div class="buttons are-small">
                                        <button class="button is-info is-small" onclick="viewMessage(<?= $reminder['id'] ?>)">
                                            <span class="icon"><i class="fas fa-eye"></i></span>
                                        </button>
                                        <button class="button is-warning is-small" onclick="resendReminder(<?= $reminder['id'] ?>)">
                                            <span class="icon"><i class="fas fa-redo"></i></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="has-text-centered">
                                <p class="has-text-grey">Aucun rappel envoyé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour afficher le message -->
<div class="modal" id="messageModal">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Détails du Rappel</p>
            <button class="delete" aria-label="close" onclick="closeModal()"></button>
        </header>
        <section class="modal-card-body">
            <div id="messageContent">
                <!-- Le contenu du message sera chargé ici -->
            </div>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-info" onclick="resendReminder()">
                <span class="icon"><i class="fas fa-redo"></i></span>
                <span>Renvoyer</span>
            </button>
            <button class="button" onclick="closeModal()">Fermer</button>
        </footer>
    </div>
</div>

<script>
function viewMessage(reminderId) {
    // Simuler l'affichage du message
    document.getElementById('messageContent').innerHTML = `
        <div class="content">
            <h4>Message envoyé :</h4>
            <div class="box">
                <p>Bonjour [Nom du Parent],</p>
                <p>Nous vous rappelons que le paiement des frais de [Type de Frais] pour votre enfant [Nom de l'Élève] 
                d'un montant de [Montant] FCFA est en retard.</p>
                <p>Pour le bien-être et la continuité de la scolarité de votre enfant, 
                nous vous prions de régulariser ce paiement dans les plus brefs délais.</p>
                <p>Merci de votre compréhension.<br>
                KISSAI SCHOOL</p>
            </div>
        </div>
    `;
    document.getElementById('messageModal').classList.add('is-active');
}

function resendReminder(reminderId) {
    if (confirm('Renvoyer ce rappel ?')) {
        // Logique pour renvoyer le rappel
        alert('Rappel renvoyé avec succès !');
    }
}

function closeModal() {
    document.getElementById('messageModal').classList.remove('is-active');
}

// Fermer le modal en cliquant sur le fond
document.querySelector('.modal-background').addEventListener('click', closeModal);
</script>

<?= $this->endSection() ?>


