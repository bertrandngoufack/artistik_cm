<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/bibliotheque') ?>">Bibliothèque</a></li>
                    <li><a href="<?= base_url('admin/bibliotheque/loans') ?>">Emprunts</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Détails de l'Emprunt</a></li>
                </ul>
            </nav>
            
            <h1 class="title">
                <i class="fas fa-hand-holding"></i>
                Détails de l'Emprunt
            </h1>
        </div>
    </div>

    <div class="card">
        <div class="card-content">
            <div class="columns">
                <div class="column is-6">
                    <div class="field">
                        <label class="label">Livre</label>
                        <div class="control">
                            <p class="subtitle"><?= esc($loan['book_title'] ?? 'Livre ' . $loan['book_id']) ?></p>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Membre</label>
                        <div class="control">
                            <p class="subtitle"><?= esc($loan['member_name'] ?? 'Membre ' . $loan['member_id']) ?></p>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Type de Membre</label>
                        <div class="control">
                            <span class="tag is-info"><?= esc($loan['member_type'] ?? 'Non défini') ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="column is-6">
                    <div class="field">
                        <label class="label">Date d'Emprunt</label>
                        <div class="control">
                            <p class="subtitle"><?= date('d/m/Y H:i', strtotime($loan['loan_date'])) ?></p>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Date de Retour Prévue</label>
                        <div class="control">
                            <p class="subtitle"><?= date('d/m/Y', strtotime($loan['due_date'])) ?></p>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Statut</label>
                        <div class="control">
                            <?php
                            $statusClass = 'is-info';
                            $statusText = 'Inconnu';
                            switch ($loan['status']) {
                                case 'RETURNED':
                                    $statusClass = 'is-success';
                                    $statusText = 'Retourné';
                                    break;
                                case 'OVERDUE':
                                    $statusClass = 'is-danger';
                                    $statusText = 'En Retard';
                                    break;
                                case 'BORROWED':
                                    $statusClass = 'is-warning';
                                    $statusText = 'Emprunté';
                                    break;
                            }
                            ?>
                            <span class="tag <?= $statusClass ?>"><?= $statusText ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($loan['return_date']): ?>
            <div class="field">
                <label class="label">Date de Retour Effectif</label>
                <div class="control">
                    <p class="subtitle has-text-success"><?= date('d/m/Y H:i', strtotime($loan['return_date'])) ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($loan['notes']): ?>
            <div class="field">
                <label class="label">Notes</label>
                <div class="control">
                    <p><?= esc($loan['notes']) ?></p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($loan['status'] === 'BORROWED' && strtotime($loan['due_date']) < time()): ?>
            <div class="notification is-warning">
                <strong>Attention :</strong> Cet emprunt est en retard de <?= floor((time() - strtotime($loan['due_date'])) / (60 * 60 * 24)) ?> jour(s).
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <a href="<?= base_url('admin/bibliotheque/loans') ?>" class="button is-light">
                    <i class="fas fa-arrow-left"></i>
                    Retour à la liste
                </a>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <?php if ($loan['status'] === 'BORROWED'): ?>
                    <a href="<?= base_url('admin/bibliotheque/loans/' . $loan['id'] . '/return') ?>" class="button is-success">
                        <i class="fas fa-undo"></i>
                        Marquer comme Retourné
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>






