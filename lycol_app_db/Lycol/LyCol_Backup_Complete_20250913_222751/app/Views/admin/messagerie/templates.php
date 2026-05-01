<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Gestion des Templates</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/messagerie') ?>" class="button is-light">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour</span>
            </a>
            <a href="<?= base_url('admin/messagerie/templates/create') ?>" class="button is-primary">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Nouveau Template</span>
            </a>
        </div>
    </div>
</div>

<!-- Statistiques des templates -->
<div class="columns">
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="level">
                    <div class="level-item has-text-centered">
                        <div>
                            <p class="heading">Total Templates</p>
                            <p class="title"><?= count($templates ?? []) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="level">
                    <div class="level-item has-text-centered">
                        <div>
                            <p class="heading">Actifs</p>
                            <p class="title has-text-success"><?= count(array_filter($templates ?? [], function($t) { return $t['is_active'] ?? false; })) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="level">
                    <div class="level-item has-text-centered">
                        <div>
                            <p class="heading">Utilisés</p>
                            <p class="title has-text-info"><?= count(array_filter($templates ?? [], function($t) { return isset($t['usage_count']) && $t['usage_count'] > 0; })) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="level">
                    <div class="level-item has-text-centered">
                        <div>
                            <p class="heading">Récents</p>
                            <p class="title has-text-warning"><?= count(array_filter($templates ?? [], function($t) { 
                                return isset($t['created_at']) && strtotime($t['created_at']) > strtotime('-7 days'); 
                            })) ?></p>
                        </div>
                    </div>
                </div>
            </div>
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
        <form method="GET" action="<?= base_url('admin/messagerie/templates') ?>">
            <div class="columns">
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Type de destinataire</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="recipient_type">
                                    <option value="">Tous les types</option>
                                    <option value="ALL" <?= ($recipient_type ?? '') === 'ALL' ? 'selected' : '' ?>>Tous les utilisateurs</option>
                                    <option value="STUDENTS" <?= ($recipient_type ?? '') === 'STUDENTS' ? 'selected' : '' ?>>Élèves</option>
                                    <option value="PARENTS" <?= ($recipient_type ?? '') === 'PARENTS' ? 'selected' : '' ?>>Parents</option>
                                    <option value="STAFF" <?= ($recipient_type ?? '') === 'STAFF' ? 'selected' : '' ?>>Personnel</option>
                                    <option value="SPECIFIC" <?= ($recipient_type ?? '') === 'SPECIFIC' ? 'selected' : '' ?>>Spécifique</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Statut</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="1" <?= ($status ?? '') === '1' ? 'selected' : '' ?>>Actif</option>
                                    <option value="0" <?= ($status ?? '') === '0' ? 'selected' : '' ?>>Inactif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-4">
                    <div class="field">
                        <label class="label">Recherche</label>
                        <div class="control">
                            <input class="input" type="text" name="search" value="<?= esc($search ?? '') ?>" placeholder="Nom, titre, contenu...">
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

<!-- Liste des templates -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-list"></i></span>
            Templates (<?= count($templates ?? []) ?>)
        </p>
        <div class="card-header-icon">
            <a href="<?= base_url('admin/messagerie/templates/export') ?>" class="button is-small is-success">
                <span class="icon"><i class="fas fa-download"></i></span>
                <span>Exporter</span>
            </a>
        </div>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Titre</th>
                        <th>Destinataires</th>
                        <th>Statut</th>
                        <th>Utilisation</th>
                        <th>Date de création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($templates)): ?>
                        <?php foreach ($templates as $template): ?>
                        <tr>
                            <td>
                                <div>
                                    <p class="title is-6"><?= esc($template['name']) ?></p>
                                    <p class="subtitle is-7">ID: <?= $template['id'] ?></p>
                                </div>
                            </td>
                            <td>
                                <p class="has-text-weight-medium"><?= esc($template['title']) ?></p>
                                <p class="is-size-7 has-text-grey"><?= esc(substr($template['content'], 0, 50)) ?>...</p>
                            </td>
                            <td>
                                <?php
                                $recipientClass = 'is-dark';
                                $recipientText = 'Tous';
                                switch ($template['recipient_type']) {
                                    case 'STUDENTS':
                                        $recipientClass = 'is-primary';
                                        $recipientText = 'Élèves';
                                        break;
                                    case 'PARENTS':
                                        $recipientClass = 'is-info';
                                        $recipientText = 'Parents';
                                        break;
                                    case 'STAFF':
                                        $recipientClass = 'is-warning';
                                        $recipientText = 'Personnel';
                                        break;
                                    case 'SPECIFIC':
                                        $recipientClass = 'is-danger';
                                        $recipientText = 'Spécifique';
                                        break;
                                }
                                ?>
                                <span class="tag <?= $recipientClass ?>"><?= esc($recipientText) ?></span>
                            </td>
                            <td>
                                <?php if ($template['is_active'] ?? false): ?>
                                    <span class="tag is-success">Actif</span>
                                <?php else: ?>
                                    <span class="tag is-danger">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="tag is-info"><?= $template['usage_count'] ?? 0 ?> fois</span>
                            </td>
                            <td><?= isset($template['created_at']) ? date('d/m/Y H:i', strtotime($template['created_at'])) : 'N/A' ?></td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="<?= base_url('admin/messagerie/templates/' . $template['id']) ?>" class="button is-info">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/messagerie/templates/' . $template['id'] . '/edit') ?>" class="button is-warning">
                                        <span class="icon"><i class="fas fa-edit"></i></span>
                                    </a>
                                    <?php if ($template['is_active'] ?? false): ?>
                                        <a href="<?= base_url('admin/messagerie/templates/' . $template['id'] . '/use') ?>" class="button is-success">
                                            <span class="icon"><i class="fas fa-paper-plane"></i></span>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('admin/messagerie/templates/' . $template['id'] . '/duplicate') ?>" class="button is-info">
                                        <span class="icon"><i class="fas fa-copy"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/messagerie/templates/' . $template['id'] . '/delete') ?>" 
                                       class="button is-danger"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce template ?')">
                                        <span class="icon"><i class="fas fa-trash"></i></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="has-text-centered">
                                <p class="has-text-grey">Aucun template trouvé</p>
                                <a href="<?= base_url('admin/messagerie/templates/create') ?>" class="button is-primary is-small">
                                    <span class="icon"><i class="fas fa-plus"></i></span>
                                    <span>Créer le premier template</span>
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Informations sur les templates -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-info-circle"></i></span>
            Informations sur les Templates
        </p>
    </header>
    <div class="card-content">
        <div class="content">
            <h6>Types de destinataires :</h6>
            <ul>
                <li><strong>ALL</strong> : Tous les utilisateurs de la plateforme</li>
                <li><strong>STUDENTS</strong> : Tous les élèves</li>
                <li><strong>PARENTS</strong> : Tous les parents</li>
                <li><strong>STAFF</strong> : Tout le personnel</li>
                <li><strong>SPECIFIC</strong> : Destinataires spécifiques (sélection manuelle)</li>
            </ul>
            
            <h6>Variables disponibles :</h6>
            <ul>
                <li><code>{name}</code> : Nom du destinataire</li>
                <li><code>{firstname}</code> : Prénom du destinataire</li>
                <li><code>{email}</code> : Email du destinataire</li>
                <li><code>{phone}</code> : Téléphone du destinataire</li>
                <li><code>{date}</code> : Date actuelle</li>
                <li><code>{time}</code> : Heure actuelle</li>
                <li><code>{school_name}</code> : Nom de l'établissement</li>
            </ul>
            
            <div class="notification is-info">
                <p><strong>Note :</strong> Les templates actifs peuvent être utilisés pour envoyer des messages. 
                Les templates inactifs sont conservés mais ne peuvent pas être utilisés.</p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>







