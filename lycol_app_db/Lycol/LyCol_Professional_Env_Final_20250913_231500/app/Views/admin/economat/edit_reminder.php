<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <i class="fas fa-edit"></i>
                Éditer un Rappel
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
                        Modifier le Rappel
                    </p>
                </header>
                <div class="card-content">
                    <form action="<?= base_url("admin/economat/reminders/{$reminder['id']}/update") ?>" method="post">
                        <div class="field">
                            <label class="label">Élève</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="student_id" required>
                                        <option value="">Sélectionner un élève</option>
                                        <?php foreach ($students as $student): ?>
                                            <option value="<?= $student['id'] ?>" <?= ($student['id'] == $reminder['student_id']) ? 'selected' : '' ?>>
                                                <?= $student['first_name'] ?> <?= $student['last_name'] ?> 
                                                (<?= $student['matricule'] ?? 'N/A' ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Message</label>
                            <div class="control">
                                <textarea class="textarea" name="message" placeholder="Message du rappel..." rows="4" required><?= $reminder['message'] ?></textarea>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Date d'échéance</label>
                            <div class="control">
                                <input class="input" type="date" name="due_date" value="<?= $reminder['due_date'] ?>" required>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Statut</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="status">
                                        <option value="pending" <?= ($reminder['status'] === 'pending') ? 'selected' : '' ?>>En attente</option>
                                        <option value="sent" <?= ($reminder['status'] === 'sent') ? 'selected' : '' ?>>Envoyé</option>
                                        <option value="cancelled" <?= ($reminder['status'] === 'cancelled') ? 'selected' : '' ?>>Annulé</option>
                                        <option value="completed" <?= ($reminder['status'] === 'completed') ? 'selected' : '' ?>>Terminé</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Priorité</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="priority">
                                        <option value="low" <?= ($reminder['priority'] ?? 'medium') === 'low' ? 'selected' : '' ?>>Faible</option>
                                        <option value="medium" <?= ($reminder['priority'] ?? 'medium') === 'medium' ? 'selected' : '' ?>>Moyenne</option>
                                        <option value="high" <?= ($reminder['priority'] ?? 'medium') === 'high' ? 'selected' : '' ?>>Élevée</option>
                                        <option value="urgent" <?= ($reminder['priority'] ?? 'medium') === 'urgent' ? 'selected' : '' ?>>Urgente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Méthode de notification</label>
                            <div class="control">
                                <label class="checkbox">
                                    <input type="checkbox" name="notify_email" value="1" <?= ($reminder['notify_email'] ?? true) ? 'checked' : '' ?>>
                                    Email
                                </label>
                                <br>
                                <label class="checkbox">
                                    <input type="checkbox" name="notify_sms" value="1" <?= ($reminder['notify_sms'] ?? false) ? 'checked' : '' ?>>
                                    SMS
                                </label>
                                <br>
                                <label class="checkbox">
                                    <input type="checkbox" name="notify_whatsapp" value="1" <?= ($reminder['notify_whatsapp'] ?? false) ? 'checked' : '' ?>>
                                    WhatsApp
                                </label>
                            </div>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <i class="fas fa-save"></i>
                                    Mettre à jour
                                </button>
                            </div>
                            <div class="control">
                                <a href="<?= base_url('admin/economat/reminders') ?>" class="button is-light">
                                    Annuler
                                </a>
                            </div>
                            <div class="control">
                                <a href="<?= base_url("admin/economat/reminders/{$reminder['id']}/delete") ?>" 
                                   class="button is-danger"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce rappel ?')">
                                    <i class="fas fa-trash"></i>
                                    Supprimer
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
                        Informations du Rappel
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <p><strong>ID :</strong> <?= $reminder['id'] ?></p>
                        <p><strong>Statut :</strong> 
                            <?php if ($reminder['status'] === 'pending'): ?>
                                <span class="tag is-warning">En attente</span>
                            <?php elseif ($reminder['status'] === 'sent'): ?>
                                <span class="tag is-success">Envoyé</span>
                            <?php elseif ($reminder['status'] === 'cancelled'): ?>
                                <span class="tag is-danger">Annulé</span>
                            <?php else: ?>
                                <span class="tag is-info">Terminé</span>
                            <?php endif; ?>
                        </p>
                        <p><strong>Date d'échéance :</strong> <?= date('d/m/Y', strtotime($reminder['due_date'])) ?></p>
                        <p><strong>Priorité :</strong> 
                            <?php 
                            $priority = $reminder['priority'] ?? 'medium';
                            $priorityClass = [
                                'low' => 'is-light',
                                'medium' => 'is-info',
                                'high' => 'is-warning',
                                'urgent' => 'is-danger'
                            ];
                            ?>
                            <span class="tag <?= $priorityClass[$priority] ?>">
                                <?= ucfirst($priority) ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <header class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-clock"></i>
                        Actions Rapides
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <div class="buttons are-small">
                            <a href="<?= base_url("admin/economat/reminders/{$reminder['id']}/send") ?>" 
                               class="button is-success is-fullwidth">
                                <i class="fas fa-paper-plane"></i>
                                Envoyer maintenant
                            </a>
                            <a href="<?= base_url("admin/economat/reminders/{$reminder['id']}/duplicate") ?>" 
                               class="button is-info is-fullwidth">
                                <i class="fas fa-copy"></i>
                                Dupliquer
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <header class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-history"></i>
                        Historique
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <p><strong>Créé le :</strong> <?= date('d/m/Y H:i', strtotime($reminder['created_at'] ?? 'now')) ?></p>
                        <?php if (isset($reminder['updated_at'])): ?>
                            <p><strong>Modifié le :</strong> <?= date('d/m/Y H:i', strtotime($reminder['updated_at'])) ?></p>
                        <?php endif; ?>
                        <?php if (isset($reminder['sent_at'])): ?>
                            <p><strong>Envoyé le :</strong> <?= date('d/m/Y H:i', strtotime($reminder['sent_at'])) ?></p>
                        <?php endif; ?>
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
</script>

<?= $this->endSection() ?>



