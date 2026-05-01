<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <i class="fas fa-plus"></i>
                Créer un Rappel
            </h1>
        </div>
        <div class="column is-narrow">
            <a href="<?= base_url('admin/economat/reminders') ?>" class="button is-light">
                <i class="fas fa-arrow-left"></i>
                Retour aux rappels
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
                        Informations du Rappel
                    </p>
                </header>
                <div class="card-content">
                    <form action="<?= base_url('admin/economat/reminders/store') ?>" method="post">
                        <div class="field">
                            <label class="label">Élève</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="student_id" required>
                                        <option value="">Sélectionner un élève</option>
                                        <?php foreach ($students as $student): ?>
                                            <option value="<?= $student['id'] ?>">
                                                <?= $student['first_name'] ?> <?= $student['last_name'] ?> 
                                                (<?= $student['matricule'] ?? 'N/A' ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Type de Frais</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="fee_type_id">
                                        <option value="">Tous les frais</option>
                                        <?php foreach ($feeTypes as $feeType): ?>
                                            <option value="<?= $feeType['id'] ?>">
                                                <?= $feeType['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Message</label>
                            <div class="control">
                                <textarea class="textarea" name="message" placeholder="Message du rappel..." rows="4" required></textarea>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Date d'échéance</label>
                            <div class="control">
                                <input class="input" type="date" name="due_date" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Priorité</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="priority">
                                        <option value="low">Faible</option>
                                        <option value="medium" selected>Moyenne</option>
                                        <option value="high">Élevée</option>
                                        <option value="urgent">Urgente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Méthode de notification</label>
                            <div class="control">
                                <label class="checkbox">
                                    <input type="checkbox" name="notify_email" value="1" checked>
                                    Email
                                </label>
                                <br>
                                <label class="checkbox">
                                    <input type="checkbox" name="notify_sms" value="1">
                                    SMS
                                </label>
                                <br>
                                <label class="checkbox" name="notify_whatsapp" value="1">
                                    <input type="checkbox">
                                    WhatsApp
                                </label>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Répéter le rappel</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="repeat">
                                        <option value="none">Ne pas répéter</option>
                                        <option value="daily">Tous les jours</option>
                                        <option value="weekly">Toutes les semaines</option>
                                        <option value="monthly">Tous les mois</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <i class="fas fa-save"></i>
                                    Créer le rappel
                                </button>
                            </div>
                            <div class="control">
                                <a href="<?= base_url('admin/economat/reminders') ?>" class="button is-light">
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
                        <h6>À propos des rappels</h6>
                        <p>Les rappels permettent d'envoyer des notifications automatiques aux parents concernant les paiements en retard.</p>
                        
                        <h6>Types de notification</h6>
                        <ul>
                            <li><strong>Email :</strong> Envoi d'un email détaillé</li>
                            <li><strong>SMS :</strong> Message court et direct</li>
                            <li><strong>WhatsApp :</strong> Message via WhatsApp Business</li>
                        </ul>

                        <h6>Priorités</h6>
                        <ul>
                            <li><strong>Faible :</strong> Rappel informatif</li>
                            <li><strong>Moyenne :</strong> Rappel standard</li>
                            <li><strong>Élevée :</strong> Rappel important</li>
                            <li><strong>Urgente :</strong> Action immédiate requise</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <header class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-clock"></i>
                        Modèles de rappels
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
                            <button class="button is-light" onclick="loadTemplate('partial_payment')">
                                Paiement partiel
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
    // Supprimer les notifications
    document.querySelectorAll('.notification .delete').forEach(function(button) {
        button.addEventListener('click', function() {
            this.parentNode.remove();
        });
    });
});

function loadTemplate(type) {
    const templates = {
        'payment_reminder': 'Bonjour, nous vous rappelons que le paiement des frais de scolarité pour le mois en cours est attendu. Merci de procéder au règlement dans les plus brefs délais.',
        'overdue_payment': 'Bonjour, nous constatons que le paiement des frais de scolarité est en retard. Veuillez procéder au règlement immédiatement pour éviter toute sanction.',
        'partial_payment': 'Bonjour, nous avons reçu votre paiement partiel. Veuillez compléter le montant restant pour finaliser le règlement des frais de scolarité.'
    };
    
    document.querySelector('textarea[name="message"]').value = templates[type] || '';
}
</script>

<?= $this->endSection() ?>



