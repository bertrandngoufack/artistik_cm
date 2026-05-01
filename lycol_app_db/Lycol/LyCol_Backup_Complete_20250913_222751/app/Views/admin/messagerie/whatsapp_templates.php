<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Templates WhatsApp Business</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/messagerie/settings') ?>" class="button is-light">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour</span>
            </a>
            <a href="<?= base_url('admin/messagerie/settings/whatsapp-templates/create') ?>" class="button is-primary">
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
                            <p class="title"><?= count($templates) ?></p>
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
                            <p class="heading">Approuvés</p>
                            <p class="title has-text-success"><?= count(array_filter($templates, function($t) { return $t['status'] === 'APPROVED'; })) ?></p>
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
                            <p class="heading">En attente</p>
                            <p class="title has-text-warning"><?= count(array_filter($templates, function($t) { return $t['status'] === 'PENDING'; })) ?></p>
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
                            <p class="heading">Rejetés</p>
                            <p class="title has-text-danger"><?= count(array_filter($templates, function($t) { return $t['status'] === 'REJECTED'; })) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Liste des templates -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fab fa-whatsapp"></i></span>
            Templates WhatsApp Business (<?= count($templates) ?>)
        </p>
        <div class="card-header-icon">
            <a href="<?= base_url('admin/messagerie/settings/whatsapp-templates/sync') ?>" class="button is-small is-info">
                <span class="icon"><i class="fas fa-sync"></i></span>
                <span>Synchroniser</span>
            </a>
        </div>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Langue</th>
                        <th>Catégorie</th>
                        <th>Statut</th>
                        <th>Composants</th>
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
                                <span class="tag is-info"><?= strtoupper($template['language']) ?></span>
                            </td>
                            <td>
                                <?php
                                $categoryClass = 'is-dark';
                                switch ($template['category']) {
                                    case 'UTILITY':
                                        $categoryClass = 'is-info';
                                        break;
                                    case 'EDUCATION':
                                        $categoryClass = 'is-primary';
                                        break;
                                    case 'MARKETING':
                                        $categoryClass = 'is-warning';
                                        break;
                                }
                                ?>
                                <span class="tag <?= $categoryClass ?>"><?= esc($template['category']) ?></span>
                            </td>
                            <td>
                                <?php
                                $statusClass = 'is-info';
                                switch ($template['status']) {
                                    case 'APPROVED':
                                        $statusClass = 'is-success';
                                        break;
                                    case 'PENDING':
                                        $statusClass = 'is-warning';
                                        break;
                                    case 'REJECTED':
                                        $statusClass = 'is-danger';
                                        break;
                                }
                                ?>
                                <span class="tag <?= $statusClass ?>"><?= esc($template['status']) ?></span>
                            </td>
                            <td>
                                <div class="content">
                                    <ul class="is-size-7">
                                        <?php foreach ($template['components'] as $component): ?>
                                            <li>
                                                <strong><?= esc($component['type']) ?>:</strong> 
                                                <?= esc(substr($component['text'], 0, 50)) ?>...
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="<?= base_url('admin/messagerie/settings/whatsapp-templates/' . $template['id']) ?>" class="button is-info">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="<?= base_url('admin/messagerie/settings/whatsapp-templates/' . $template['id'] . '/edit') ?>" class="button is-warning">
                                        <span class="icon"><i class="fas fa-edit"></i></span>
                                    </a>
                                    <?php if ($template['status'] === 'APPROVED'): ?>
                                        <a href="<?= base_url('admin/messagerie/settings/whatsapp-templates/' . $template['id'] . '/use') ?>" class="button is-success">
                                            <span class="icon"><i class="fas fa-paper-plane"></i></span>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('admin/messagerie/settings/whatsapp-templates/' . $template['id'] . '/delete') ?>" 
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
                            <td colspan="6" class="has-text-centered">
                                <p class="has-text-grey">Aucun template WhatsApp trouvé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Informations sur les templates WhatsApp -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-info-circle"></i></span>
            Informations sur les Templates WhatsApp Business
        </p>
    </header>
    <div class="card-content">
        <div class="content">
            <h6>Types de templates supportés :</h6>
            <ul>
                <li><strong>UTILITY</strong> : Messages informatifs et notifications</li>
                <li><strong>EDUCATION</strong> : Bulletins, résultats, informations académiques</li>
                <li><strong>MARKETING</strong> : Promotions et communications commerciales</li>
            </ul>
            
            <h6>Composants disponibles :</h6>
            <ul>
                <li><strong>HEADER</strong> : Titre du message (texte, image, vidéo)</li>
                <li><strong>BODY</strong> : Contenu principal du message</li>
                <li><strong>FOOTER</strong> : Pied de page optionnel</li>
                <li><strong>BUTTONS</strong> : Boutons d'action (si activés)</li>
            </ul>
            
            <h6>Variables supportées :</h6>
            <ul>
                <li><code>{{1}}</code>, <code>{{2}}</code>, etc. : Variables dynamiques</li>
                <li><code>{{name}}</code> : Nom du destinataire</li>
                <li><code>{{date}}</code> : Date actuelle</li>
                <li><code>{{time}}</code> : Heure actuelle</li>
            </ul>
            
            <div class="notification is-info">
                <p><strong>Note :</strong> Les templates WhatsApp Business doivent être approuvés par Meta avant utilisation. 
                Le processus d'approbation peut prendre 24-48 heures.</p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>







