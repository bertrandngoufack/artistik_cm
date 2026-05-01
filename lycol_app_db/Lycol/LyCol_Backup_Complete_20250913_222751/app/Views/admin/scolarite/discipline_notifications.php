<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <h1 class="title">Historique des Notifications Disciplinaires</h1>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <a href="/admin/scolarite/discipline" class="button is-primary">
                    <span class="icon"><i class="fas fa-arrow-left"></i></span>
                    <span>Retour Discipline</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Informations de l'année scolaire -->
    <div class="notification is-info is-light">
        <strong>Année Académique <?= $current_academic_year ?></strong> : 
        Du <?= date('d/m/Y', strtotime($academic_year_dates['start_date'])) ?> au <?= date('d/m/Y', strtotime($academic_year_dates['end_date'])) ?>
    </div>

    <!-- Statistiques -->
    <div class="columns is-multiline mb-4">
        <div class="column is-3">
            <div class="box has-background-info has-text-white">
                <h4 class="title is-4 has-text-white">Total Notifications</h4>
                <p class="title is-2 has-text-white"><?= $notificationStats['total_notifications'] ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-success has-text-white">
                <h4 class="title is-4 has-text-white">SMS Envoyés</h4>
                <p class="title is-2 has-text-white"><?= $notificationStats['sms_sent'] ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-warning has-text-white">
                <h4 class="title is-4 has-text-white">Emails Envoyés</h4>
                <p class="title is-2 has-text-white"><?= $notificationStats['email_sent'] ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-danger has-text-white">
                <h4 class="title is-4 has-text-white">WhatsApp Envoyés</h4>
                <p class="title is-2 has-text-white"><?= $notificationStats['whatsapp_sent'] ?? 0 ?></p>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <header class="card-header">
            <p class="card-header-title">Filtres</p>
        </header>
        <div class="card-content">
            <form method="GET" action="/admin/scolarite/discipline/notifications">
                <div class="columns is-multiline">
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">Année Académique</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="academic_year">
                                        <?php foreach ($available_academic_years as $year): ?>
                                            <option value="<?= $year ?>" <?= $year === $filters['academic_year'] ? 'selected' : '' ?>>
                                                <?= $year ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">Date de début</label>
                            <div class="control">
                                <input class="input" type="date" name="date_from" value="<?= $filters['date_from'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">Date de fin</label>
                            <div class="control">
                                <input class="input" type="date" name="date_to" value="<?= $filters['date_to'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">Canal</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="channel">
                                        <option value="">Tous les canaux</option>
                                        <option value="sms" <?= $filters['channel'] === 'sms' ? 'selected' : '' ?>>SMS</option>
                                        <option value="email" <?= $filters['channel'] === 'email' ? 'selected' : '' ?>>Email</option>
                                        <option value="whatsapp" <?= $filters['channel'] === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field is-grouped">
                    <div class="control">
                        <button type="submit" class="button is-primary">
                            <span class="icon"><i class="fas fa-search"></i></span>
                            <span>Filtrer</span>
                        </button>
                    </div>
                    <div class="control">
                        <a href="/admin/scolarite/discipline/notifications" class="button is-light">
                            <span class="icon"><i class="fas fa-times"></i></span>
                            <span>Réinitialiser</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des notifications -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">Historique des Notifications (<?= count($notifications) ?> résultat<?= count($notifications) > 1 ? 's' : '' ?>)</p>
        </header>
        <div class="card-content">
            <?php if (!empty($notifications)): ?>
            <div class="table-container">
                <table class="table is-fullwidth is-striped">
                    <thead>
                        <tr>
                            <th>Élève</th>
                            <th>Classe</th>
                            <th>Incident</th>
                            <th>Date d'envoi</th>
                            <th>Destinataire</th>
                            <th>Canaux</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notifications as $notification): ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?= $notification->first_name . ' ' . $notification->last_name ?></strong>
                                    <br><small class="has-text-grey"><?= $notification->matricule ?></small>
                                </div>
                            </td>
                            <td>
                                <?php if ($notification->class_name): ?>
                                    <span class="tag is-info"><?= $notification->class_name ?></span>
                                <?php else: ?>
                                    <span class="has-text-grey">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="content">
                                    <p class="is-size-7">
                                        <strong><?= date('d/m/Y', strtotime($notification->incident_date)) ?></strong>
                                        <br><?= $notification->description ?>
                                    </p>
                                    <span class="tag is-small <?= $notification->incident_type === 'MINOR' ? 'is-warning' : ($notification->incident_type === 'MAJOR' ? 'is-danger' : 'is-black') ?>">
                                        <?= $notification->incident_type ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <strong><?= date('d/m/Y H:i', strtotime($notification->sent_at)) ?></strong>
                                <br><small class="has-text-grey"><?= date('l', strtotime($notification->sent_at)) ?></small>
                            </td>
                            <td>
                                <div>
                                    <strong>Tél: <?= $notification->parent_phone ?></strong>
                                    <?php if ($notification->parent_email): ?>
                                        <br><small class="has-text-grey"><?= $notification->parent_email ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="buttons are-small">
                                    <?php if ($notification->sms_sent): ?>
                                        <span class="tag is-success" title="SMS envoyé">
                                            <span class="icon"><i class="fas fa-sms"></i></span>
                                        </span>
                                    <?php else: ?>
                                        <span class="tag is-light" title="SMS non envoyé">
                                            <span class="icon"><i class="fas fa-sms"></i></span>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($notification->email_sent): ?>
                                        <span class="tag is-success" title="Email envoyé">
                                            <span class="icon"><i class="fas fa-envelope"></i></span>
                                        </span>
                                    <?php else: ?>
                                        <span class="tag is-light" title="Email non envoyé">
                                            <span class="icon"><i class="fas fa-envelope"></i></span>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($notification->whatsapp_sent): ?>
                                        <span class="tag is-success" title="WhatsApp envoyé">
                                            <span class="icon"><i class="fab fa-whatsapp"></i></span>
                                        </span>
                                    <?php else: ?>
                                        <span class="tag is-light" title="WhatsApp non envoyé">
                                            <span class="icon"><i class="fab fa-whatsapp"></i></span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="/admin/scolarite/discipline/<?= $notification->incident_id ?>/view" class="button is-info" title="Voir l'incident">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="/admin/scolarite/discipline/<?= $notification->incident_id ?>/notify" class="button is-warning" title="Renvoyer notification" onclick="return confirm('Renvoyer la notification aux parents ?')">
                                        <span class="icon"><i class="fas fa-redo"></i></span>
                                    </a>
                                    <button class="button is-light" title="Voir le message" onclick="showMessage('<?= addslashes($notification->message) ?>')">
                                        <span class="icon"><i class="fas fa-comment"></i></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="has-text-centered py-6">
                <span class="icon is-large">
                    <i class="fas fa-bell fa-3x has-text-grey-light"></i>
                </span>
                <p class="title is-4 has-text-grey-light mt-4">Aucune notification trouvée</p>
                <p class="subtitle is-6 has-text-grey-light">Aucune notification ne correspond aux critères de recherche</p>
                <a href="/admin/scolarite/discipline" class="button is-primary mt-4">
                    <span class="icon"><i class="fas fa-arrow-left"></i></span>
                    <span>Retour à la discipline</span>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal pour afficher le message -->
<div id="message-modal" class="modal">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Message de Notification</p>
            <button class="delete" aria-label="close" onclick="closeMessageModal()"></button>
        </header>
        <section class="modal-card-body">
            <div id="message-content" class="content">
                <!-- Le contenu du message sera inséré ici -->
            </div>
        </section>
        <footer class="modal-card-foot">
            <button class="button" onclick="closeMessageModal()">Fermer</button>
        </footer>
    </div>
</div>

<script>
function showMessage(message) {
    document.getElementById('message-content').innerHTML = '<pre style="white-space: pre-wrap; font-family: inherit;">' + message + '</pre>';
    document.getElementById('message-modal').classList.add('is-active');
}

function closeMessageModal() {
    document.getElementById('message-modal').classList.remove('is-active');
}

// Fermer le modal en cliquant sur le background
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.modal-background').addEventListener('click', closeMessageModal);
});
</script>
<?= $this->endSection() ?>
