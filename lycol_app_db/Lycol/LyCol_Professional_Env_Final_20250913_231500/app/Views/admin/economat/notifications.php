<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <i class="fas fa-bell"></i>
                Gestion des Notifications
            </h1>
        </div>
        <div class="column is-narrow">
            <a href="<?= base_url('admin/economat/notifications/send') ?>" class="button is-primary">
                <i class="fas fa-plus"></i>
                Nouvelle Notification
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="notification is-success">
            <button class="delete"></button>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="notification is-danger">
            <button class="delete"></button>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="columns">
        <div class="column">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-list"></i>
                        Notifications Récentes
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
                                    <th>Date</th>
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
                                    <td><?= $notification['message'] ?></td>
                                    <td>
                                        <?php if ($notification['status'] === 'sent'): ?>
                                            <span class="tag is-success">Envoyé</span>
                                        <?php else: ?>
                                            <span class="tag is-warning">En attente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?></td>
                                    <td>
                                        <div class="buttons are-small">
                                            <a href="<?= base_url("admin/economat/notifications/{$notification['id']}/view") ?>" 
                                               class="button is-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= base_url("admin/economat/notifications/{$notification['id']}/edit") ?>" 
                                               class="button is-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url("admin/economat/notifications/{$notification['id']}/delete") ?>" 
                                               class="button is-danger"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')">
                                                <i class="fas fa-trash"></i>
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
                        Statistiques des Notifications
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
                                <p class="heading">En attente</p>
                                <p class="title has-text-warning">
                                    <?= count(array_filter($notifications, fn($n) => $n['status'] === 'pending')) ?>
                                </p>
                            </div>
                        </div>
                        <div class="column">
                            <div class="box has-text-centered">
                                <p class="heading">Types</p>
                                <p class="title has-text-info">
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
                        <i class="fas fa-history"></i>
                        Actions Rapides
                    </p>
                </header>
                <div class="card-content">
                    <div class="columns">
                        <div class="column">
                            <a href="<?= base_url('admin/economat/notifications/history') ?>" 
                               class="button is-info is-fullwidth">
                                <i class="fas fa-history"></i>
                                Voir l'historique
                            </a>
                        </div>
                        <div class="column">
                            <a href="<?= base_url('admin/economat/notifications/send') ?>" 
                               class="button is-primary is-fullwidth">
                                <i class="fas fa-paper-plane"></i>
                                Envoyer une notification
                            </a>
                        </div>
                        <div class="column">
                            <a href="<?= base_url('admin/economat/notifications/templates') ?>" 
                               class="button is-warning is-fullwidth">
                                <i class="fas fa-file-alt"></i>
                                Modèles de notifications
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



