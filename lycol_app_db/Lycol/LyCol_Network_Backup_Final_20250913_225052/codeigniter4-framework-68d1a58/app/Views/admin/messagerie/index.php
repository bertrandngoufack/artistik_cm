<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Module Messagerie</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/messagerie/compose') ?>" class="button is-primary">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Nouveau Message</span>
            </a>
        </div>
    </div>
</div>

<div class="columns is-multiline">
    <!-- Statistiques -->
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Messages Envoyés</p>
                    <p class="title"><?= number_format($sent_messages ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Messages en Attente</p>
                    <p class="title"><?= number_format($pending_messages ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Abonnés SMS</p>
                    <p class="title"><?= number_format($sms_subscribers ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Abonnés Email</p>
                    <p class="title"><?= number_format($email_subscribers ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Messages récents -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-envelope"></i></span>
            Messages Récents
        </p>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Destinataires</th>
                        <th>Sujet</th>
                        <th>Date d'Envoi</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_messages)): ?>
                        <?php foreach ($recent_messages as $message): ?>
                        <tr>
                            <td>
                                <?php
                                $typeIcon = 'fas fa-envelope';
                                $typeClass = 'is-info';
                                switch ($message['recipient_type']) {
                                    case 'STUDENTS':
                                        $typeIcon = 'fas fa-user-graduate';
                                        $typeClass = 'is-primary';
                                        break;
                                    case 'PARENTS':
                                        $typeIcon = 'fas fa-users';
                                        $typeClass = 'is-info';
                                        break;
                                    case 'STAFF':
                                        $typeIcon = 'fas fa-user-tie';
                                        $typeClass = 'is-warning';
                                        break;
                                    case 'ALL':
                                        $typeIcon = 'fas fa-broadcast-tower';
                                        $typeClass = 'is-success';
                                        break;
                                    case 'SPECIFIC':
                                        $typeIcon = 'fas fa-user';
                                        $typeClass = 'is-dark';
                                        break;
                                }
                                ?>
                                <span class="tag <?= $typeClass ?>">
                                    <span class="icon"><i class="<?= $typeIcon ?>"></i></span>
                                    <span><?= esc($message['recipient_type']) ?></span>
                                </span>
                            </td>
                            <td><?= esc($message['recipient_type']) ?></td>
                            <td><?= esc($message['title']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($message['sent_at'])) ?></td>
                            <td>
                                <?php
                                $statusClass = 'is-info';
                                switch ($message['status']) {
                                    case 'SENT':
                                        $statusClass = 'is-success';
                                        break;
                                    case 'FAILED':
                                        $statusClass = 'is-danger';
                                        break;
                                    case 'PENDING':
                                        $statusClass = 'is-warning';
                                        break;
                                }
                                ?>
                                <span class="tag <?= $statusClass ?>"><?= esc($message['status']) ?></span>
                            </td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="<?= base_url('admin/messagerie/messages/' . $message['id'] . '/view') ?>" class="button is-info">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/messagerie/messages/' . $message['id'] . '/resend') ?>" class="button is-warning">
                                        <span class="icon"><i class="fas fa-redo"></i></span>
                                        <span>Réenvoyer</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="has-text-centered">
                                <p class="has-text-grey">Aucun message envoyé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Templates de messages -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-file-alt"></i></span>
            Templates de Messages
        </p>
        <div class="card-header-icon">
            <a href="<?= base_url('admin/messagerie/templates/create') ?>" class="button is-small is-primary">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Nouveau Template</span>
            </a>
        </div>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Contenu</th>
                        <th>Utilisations</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($message_templates)): ?>
                        <?php foreach ($message_templates as $template): ?>
                        <tr>
                            <td><?= esc($template['name']) ?></td>
                            <td>
                                <span class="tag is-info"><?= esc($template['type']) ?></span>
                            </td>
                            <td><?= esc(substr($template['content'], 0, 100)) ?>...</td>
                            <td><?= number_format($template['usage_count']) ?></td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="<?= base_url('admin/messagerie/template/' . $template['id']) ?>" class="button is-info">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/messagerie/template/' . $template['id'] . '/edit') ?>" class="button is-warning">
                                        <span class="icon"><i class="fas fa-edit"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/messagerie/template/' . $template['id'] . '/use') ?>" class="button is-success">
                                        <span class="icon"><i class="fas fa-paper-plane"></i></span>
                                        <span>Utiliser</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="has-text-centered">
                                <p class="has-text-grey">Aucun template créé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Actions rapides -->
<div class="columns">
    <div class="column">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-tasks"></i></span>
                    Actions Rapides
                </p>
            </header>
            <div class="card-content">
                <div class="buttons">
                    <a href="<?= base_url('admin/messagerie/bulletins') ?>" class="button is-primary">
                        <span class="icon"><i class="fas fa-file-alt"></i></span>
                        <span>Envoi Bulletins</span>
                    </a>
                    <a href="<?= base_url('admin/messagerie/discipline') ?>" class="button is-warning">
                        <span class="icon"><i class="fas fa-exclamation-triangle"></i></span>
                        <span>Notifications Discipline</span>
                    </a>
                    <a href="<?= base_url('admin/messagerie/subscribers') ?>" class="button is-info">
                        <span class="icon"><i class="fas fa-users"></i></span>
                        <span>Gestion Abonnés</span>
                    </a>
                    <a href="<?= base_url('admin/messagerie/settings') ?>" class="button is-success">
                        <span class="icon"><i class="fas fa-cog"></i></span>
                        <span>Configuration</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>




