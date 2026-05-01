<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Détails du Message</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/messagerie') ?>" class="button is-light">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour</span>
            </a>
            <?php if ($message['status'] === 'DRAFT'): ?>
                <a href="<?= base_url('admin/messagerie/message/' . $message['id'] . '/edit') ?>" class="button is-warning">
                    <span class="icon"><i class="fas fa-edit"></i></span>
                    <span>Modifier</span>
                </a>
                <a href="<?= base_url('admin/messagerie/message/' . $message['id'] . '/send') ?>" class="button is-success">
                    <span class="icon"><i class="fas fa-paper-plane"></i></span>
                    <span>Envoyer</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="columns">
    <div class="column is-8">
        <!-- Détails du message -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-envelope"></i></span>
                    <?= esc($message['title']) ?>
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <div class="field">
                        <label class="label">Titre</label>
                        <p class="subtitle is-5"><?= esc($message['title']) ?></p>
                    </div>

                    <div class="field">
                        <label class="label">Contenu</label>
                        <div class="box">
                            <p><?= nl2br(esc($message['content'])) ?></p>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Statut</label>
                        <div class="control">
                            <?php
                            $statusClass = 'is-info';
                            $statusText = $message['status'];
                            switch ($message['status']) {
                                case 'SENT':
                                    $statusClass = 'is-success';
                                    $statusText = 'Envoyé';
                                    break;
                                case 'DELIVERED':
                                    $statusClass = 'is-success';
                                    $statusText = 'Livré';
                                    break;
                                case 'FAILED':
                                    $statusClass = 'is-danger';
                                    $statusText = 'Échoué';
                                    break;
                                case 'DRAFT':
                                    $statusClass = 'is-warning';
                                    $statusText = 'Brouillon';
                                    break;
                            }
                            ?>
                            <span class="tag <?= $statusClass ?> is-medium"><?= $statusText ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="column is-4">
        <!-- Informations -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-info-circle"></i></span>
                    Informations
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <div class="field">
                        <label class="label">Type de destinataire</label>
                        <div class="control">
                            <?php
                            $typeIcon = 'fas fa-user';
                            $typeClass = 'is-dark';
                            $typeText = $message['recipient_type'];
                            switch ($message['recipient_type']) {
                                case 'STUDENTS':
                                    $typeIcon = 'fas fa-user-graduate';
                                    $typeClass = 'is-primary';
                                    $typeText = 'Élèves';
                                    break;
                                case 'PARENTS':
                                    $typeIcon = 'fas fa-users';
                                    $typeClass = 'is-info';
                                    $typeText = 'Parents';
                                    break;
                                case 'STAFF':
                                    $typeIcon = 'fas fa-user-tie';
                                    $typeClass = 'is-warning';
                                    $typeText = 'Personnel';
                                    break;
                                case 'ALL':
                                    $typeIcon = 'fas fa-broadcast-tower';
                                    $typeClass = 'is-success';
                                    $typeText = 'Tous';
                                    break;
                                case 'SPECIFIC':
                                    $typeIcon = 'fas fa-user';
                                    $typeClass = 'is-dark';
                                    $typeText = 'Spécifique';
                                    break;
                            }
                            ?>
                            <span class="tag <?= $typeClass ?>">
                                <span class="icon"><i class="<?= $typeIcon ?>"></i></span>
                                <span><?= $typeText ?></span>
                            </span>
                        </div>
                    </div>

                    <?php if ($message['recipient_type'] === 'SPECIFIC' && !empty($message['recipient_ids'])): ?>
                    <div class="field">
                        <label class="label">Destinataires spécifiques</label>
                        <p class="subtitle is-6"><?= esc($message['recipient_ids']) ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="field">
                        <label class="label">Expéditeur</label>
                        <p class="subtitle is-6"><?= esc($message['sender_name'] ?? 'Système') ?></p>
                    </div>

                    <div class="field">
                        <label class="label">Date de création</label>
                        <p class="subtitle is-6"><?= date('d/m/Y H:i', strtotime($message['created_at'])) ?></p>
                    </div>

                    <?php if ($message['sent_at']): ?>
                    <div class="field">
                        <label class="label">Date d'envoi</label>
                        <p class="subtitle is-6"><?= date('d/m/Y H:i', strtotime($message['sent_at'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($message['updated_at'] && $message['updated_at'] !== $message['created_at']): ?>
                    <div class="field">
                        <label class="label">Dernière modification</label>
                        <p class="subtitle is-6"><?= date('d/m/Y H:i', strtotime($message['updated_at'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-cogs"></i></span>
                    Actions
                </p>
            </header>
            <div class="card-content">
                <div class="buttons">
                    <?php if ($message['status'] === 'DRAFT'): ?>
                        <a href="<?= base_url('admin/messagerie/message/' . $message['id'] . '/edit') ?>" class="button is-warning is-fullwidth">
                            <span class="icon"><i class="fas fa-edit"></i></span>
                            <span>Modifier</span>
                        </a>
                        <a href="<?= base_url('admin/messagerie/message/' . $message['id'] . '/send') ?>" class="button is-success is-fullwidth">
                            <span class="icon"><i class="fas fa-paper-plane"></i></span>
                            <span>Envoyer</span>
                        </a>
                    <?php elseif ($message['status'] === 'SENT' || $message['status'] === 'DELIVERED'): ?>
                        <a href="<?= base_url('admin/messagerie/message/' . $message['id'] . '/resend') ?>" class="button is-info is-fullwidth">
                            <span class="icon"><i class="fas fa-redo"></i></span>
                            <span>Réenvoyer</span>
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?= base_url('admin/messagerie/message/' . $message['id'] . '/delete') ?>" 
                       class="button is-danger is-fullwidth"
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">
                        <span class="icon"><i class="fas fa-trash"></i></span>
                        <span>Supprimer</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistiques (si envoyé) -->
        <?php if ($message['status'] === 'SENT' || $message['status'] === 'DELIVERED'): ?>
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-chart-bar"></i></span>
                    Statistiques
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Destinataires</p>
                                <p class="title">150</p>
                            </div>
                        </div>
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Livrés</p>
                                <p class="title">142</p>
                            </div>
                        </div>
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Taux</p>
                                <p class="title">94.7%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>







