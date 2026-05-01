<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Gestion des Messages</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/messagerie') ?>" class="button is-light">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour</span>
            </a>
            <a href="<?= base_url('admin/messagerie/create') ?>" class="button is-primary">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Nouveau Message</span>
            </a>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-filter"></i></span>
            Filtres
        </p>
    </header>
    <div class="card-content">
        <form method="GET" action="<?= base_url('admin/messagerie/messages') ?>">
            <div class="columns">
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Statut</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="DRAFT" <?= ($status ?? '') === 'DRAFT' ? 'selected' : '' ?>>Brouillon</option>
                                    <option value="SENT" <?= ($status ?? '') === 'SENT' ? 'selected' : '' ?>>Envoyé</option>
                                    <option value="DELIVERED" <?= ($status ?? '') === 'DELIVERED' ? 'selected' : '' ?>>Livré</option>
                                    <option value="FAILED" <?= ($status ?? '') === 'FAILED' ? 'selected' : '' ?>>Échoué</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Type de destinataire</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="recipient_type">
                                    <option value="">Tous les types</option>
                                    <option value="ALL" <?= ($recipient_type ?? '') === 'ALL' ? 'selected' : '' ?>>Tous</option>
                                    <option value="STUDENTS" <?= ($recipient_type ?? '') === 'STUDENTS' ? 'selected' : '' ?>>Élèves</option>
                                    <option value="PARENTS" <?= ($recipient_type ?? '') === 'PARENTS' ? 'selected' : '' ?>>Parents</option>
                                    <option value="STAFF" <?= ($recipient_type ?? '') === 'STAFF' ? 'selected' : '' ?>>Personnel</option>
                                    <option value="SPECIFIC" <?= ($recipient_type ?? '') === 'SPECIFIC' ? 'selected' : '' ?>>Spécifique</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-4">
                    <div class="field">
                        <label class="label">Recherche</label>
                        <div class="control">
                            <input class="input" type="text" name="search" value="<?= esc($search ?? '') ?>" placeholder="Titre, contenu...">
                        </div>
                    </div>
                </div>
                <div class="column is-2">
                    <div class="field">
                        <label class="label">&nbsp;</label>
                        <div class="control">
                            <button type="submit" class="button is-info is-fullwidth">
                                <span class="icon"><i class="fas fa-search"></i></span>
                                <span>Rechercher</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des messages -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-envelope"></i></span>
            Messages (<?= $pager['total'] ?? count($messages) ?>)
        </p>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Type de destinataire</th>
                        <th>Statut</th>
                        <th>Expéditeur</th>
                        <th>Date de création</th>
                        <th>Date d'envoi</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $message): ?>
                        <tr>
                            <td>
                                <div class="media">
                                    <div class="media-content">
                                        <p class="title is-6"><?= esc($message['title']) ?></p>
                                        <p class="subtitle is-7"><?= esc(substr($message['content'], 0, 100)) ?>...</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php
                                $typeIcon = 'fas fa-user';
                                $typeClass = 'is-dark';
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
                                }
                                ?>
                                <span class="tag <?= $typeClass ?>">
                                    <span class="icon"><i class="<?= $typeIcon ?>"></i></span>
                                    <span><?= esc($message['recipient_type']) ?></span>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusClass = 'is-info';
                                switch ($message['status']) {
                                    case 'SENT':
                                        $statusClass = 'is-success';
                                        break;
                                    case 'DELIVERED':
                                        $statusClass = 'is-success';
                                        break;
                                    case 'FAILED':
                                        $statusClass = 'is-danger';
                                        break;
                                    case 'DRAFT':
                                        $statusClass = 'is-warning';
                                        break;
                                }
                                ?>
                                <span class="tag <?= $statusClass ?>"><?= esc($message['status']) ?></span>
                            </td>
                            <td><?= esc($message['sender_name'] ?? 'Système') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($message['created_at'])) ?></td>
                            <td>
                                <?php if ($message['sent_at']): ?>
                                    <?= date('d/m/Y H:i', strtotime($message['sent_at'])) ?>
                                <?php else: ?>
                                    <span class="has-text-grey">Non envoyé</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="<?= base_url('admin/messagerie/message/' . $message['id']) ?>" class="button is-info">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <?php if ($message['status'] === 'DRAFT'): ?>
                                        <a href="<?= base_url('admin/messagerie/message/' . $message['id'] . '/edit') ?>" class="button is-warning">
                                            <span class="icon"><i class="fas fa-edit"></i></span>
                                        </a>
                                        <a href="<?= base_url('admin/messagerie/message/' . $message['id'] . '/send') ?>" class="button is-success">
                                            <span class="icon"><i class="fas fa-paper-plane"></i></span>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('admin/messagerie/message/' . $message['id'] . '/delete') ?>" 
                                       class="button is-danger"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">
                                        <span class="icon"><i class="fas fa-trash"></i></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="has-text-centered">
                                <p class="has-text-grey">Aucun message trouvé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if (isset($pager) && $pager['total_pages'] > 1): ?>
        <div class="card-footer">
            <div class="card-footer-item">
                <nav class="pagination is-centered" role="navigation" aria-label="pagination">
                    <?php if ($pager['has_previous']): ?>
                        <a href="<?= base_url('admin/messagerie/messages?page=' . $pager['previous_page']) ?>" class="pagination-previous">
                            <span class="icon"><i class="fas fa-chevron-left"></i></span>
                            <span>Précédent</span>
                        </a>
                    <?php else: ?>
                        <span class="pagination-previous" disabled>
                            <span class="icon"><i class="fas fa-chevron-left"></i></span>
                            <span>Précédent</span>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($pager['has_next']): ?>
                        <a href="<?= base_url('admin/messagerie/messages?page=' . $pager['next_page']) ?>" class="pagination-next">
                            <span>Suivant</span>
                            <span class="icon"><i class="fas fa-chevron-right"></i></span>
                        </a>
                    <?php else: ?>
                        <span class="pagination-next" disabled>
                            <span>Suivant</span>
                            <span class="icon"><i class="fas fa-chevron-right"></i></span>
                        </span>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>







