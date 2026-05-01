<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <i class="fas fa-history"></i>
                Historique des Notifications
            </h1>
        </div>
        <div class="column is-narrow">
            <a href="<?= base_url('admin/economat/notifications') ?>" class="button is-light">
                <i class="fas fa-arrow-left"></i>
                Retour aux notifications
            </a>
        </div>
    </div>

    <div class="columns">
        <div class="column">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-list"></i>
                        Historique Complet
                    </p>
                </header>
                <div class="card-content">
                    <div class="table-container">
                        <table class="table is-fullwidth is-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Destinataires</th>
                                    <th>Message</th>
                                    <th>Statut</th>
                                    <th>Envoyé le</th>
                                    <th>Destinataires</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notifications as $notification): ?>
                                <tr>
                                    <td><?= $notification['id'] ?></td>
                                    <td>
                                        <span class="tag is-info">
                                            <?= ucfirst(str_replace('_', ' ', $notification['type'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="tag is-light">
                                            <?= ucfirst($notification['recipients']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="content">
                                            <p class="is-size-7"><?= substr($notification['message'], 0, 50) ?>...</p>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($notification['status'] === 'sent'): ?>
                                            <span class="tag is-success">Envoyé</span>
                                        <?php else: ?>
                                            <span class="tag is-warning">En attente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($notification['sent_at'])) ?></td>
                                    <td>
                                        <span class="tag is-info">
                                            <?= $notification['recipients_count'] ?> destinataires
                                        </span>
                                    </td>
                                    <td>
                                        <div class="buttons are-small">
                                            <a href="<?= base_url("admin/economat/notifications/{$notification['id']}/view") ?>" 
                                               class="button is-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= base_url("admin/economat/notifications/{$notification['id']}/resend") ?>" 
                                               class="button is-warning">
                                                <i class="fas fa-redo"></i>
                                            </a>
                                            <a href="<?= base_url("admin/economat/notifications/{$notification['id']}/duplicate") ?>" 
                                               class="button is-success">
                                                <i class="fas fa-copy"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="columns">
        <div class="column">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-chart-bar"></i>
                        Statistiques de l'Historique
                    </p>
                </header>
                <div class="card-content">
                    <div class="columns">
                        <div class="column">
                            <div class="box has-text-centered">
                                <p class="heading">Total Notifications</p>
                                <p class="title"><?= count($notifications) ?></p>
                            </div>
                        </div>
                        <div class="column">
                            <div class="box has-text-centered">
                                <p class="heading">Envoyées</p>
                                <p class="title has-text-success">
                                    <?= count(array_filter($notifications, fn($n) => $n['status'] === 'sent')) ?>
                                </p>
                            </div>
                        </div>
                        <div class="column">
                            <div class="box has-text-centered">
                                <p class="heading">Total Destinataires</p>
                                <p class="title has-text-info">
                                    <?= array_sum(array_column($notifications, 'recipients_count')) ?>
                                </p>
                            </div>
                        </div>
                        <div class="column">
                            <div class="box has-text-centered">
                                <p class="heading">Types Utilisés</p>
                                <p class="title has-text-warning">
                                    <?= count(array_unique(array_column($notifications, 'type'))) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="columns">
        <div class="column">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-filter"></i>
                        Filtres et Recherche
                    </p>
                </header>
                <div class="card-content">
                    <form method="get" action="<?= base_url('admin/economat/notifications/history') ?>">
                        <div class="columns">
                            <div class="column">
                                <div class="field">
                                    <label class="label">Type de notification</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="type">
                                                <option value="">Tous les types</option>
                                                <option value="payment_reminder" <?= ($this->request->getGet('type') === 'payment_reminder') ? 'selected' : '' ?>>Rappel de paiement</option>
                                                <option value="payment_confirmation" <?= ($this->request->getGet('type') === 'payment_confirmation') ? 'selected' : '' ?>>Confirmation de paiement</option>
                                                <option value="overdue_payment" <?= ($this->request->getGet('type') === 'overdue_payment') ? 'selected' : '' ?>>Paiement en retard</option>
                                                <option value="general" <?= ($this->request->getGet('type') === 'general') ? 'selected' : '' ?>>Général</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="column">
                                <div class="field">
                                    <label class="label">Statut</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="status">
                                                <option value="">Tous les statuts</option>
                                                <option value="sent" <?= ($this->request->getGet('status') === 'sent') ? 'selected' : '' ?>>Envoyé</option>
                                                <option value="pending" <?= ($this->request->getGet('status') === 'pending') ? 'selected' : '' ?>>En attente</option>
                                                <option value="failed" <?= ($this->request->getGet('status') === 'failed') ? 'selected' : '' ?>>Échoué</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="column">
                                <div class="field">
                                    <label class="label">Date de début</label>
                                    <div class="control">
                                        <input class="input" type="date" name="start_date" value="<?= $this->request->getGet('start_date') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="column">
                                <div class="field">
                                    <label class="label">Date de fin</label>
                                    <div class="control">
                                        <input class="input" type="date" name="end_date" value="<?= $this->request->getGet('end_date') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="column is-narrow">
                                <div class="field">
                                    <label class="label">&nbsp;</label>
                                    <div class="control">
                                        <button type="submit" class="button is-primary">
                                            <i class="fas fa-search"></i>
                                            Filtrer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="columns">
        <div class="column">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-download"></i>
                        Export
                    </p>
                </header>
                <div class="card-content">
                    <div class="columns">
                        <div class="column">
                            <a href="<?= base_url('admin/economat/notifications/export/csv') ?>" 
                               class="button is-info is-fullwidth">
                                <i class="fas fa-file-csv"></i>
                                Exporter en CSV
                            </a>
                        </div>
                        <div class="column">
                            <a href="<?= base_url('admin/economat/notifications/export/pdf') ?>" 
                               class="button is-danger is-fullwidth">
                                <i class="fas fa-file-pdf"></i>
                                Exporter en PDF
                            </a>
                        </div>
                        <div class="column">
                            <a href="<?= base_url('admin/economat/notifications/export/excel') ?>" 
                               class="button is-success is-fullwidth">
                                <i class="fas fa-file-excel"></i>
                                Exporter en Excel
                            </a>
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
</script>

<?= $this->endSection() ?>



