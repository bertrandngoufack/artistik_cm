<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/bibliotheque') ?>">Bibliothèque</a></li>
                    <li><a href="<?= base_url('admin/bibliotheque/books') ?>">Livres</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Détails du Livre</a></li>
                </ul>
            </nav>
            
            <h1 class="title">
                <i class="fas fa-book"></i>
                Détails du Livre
            </h1>
        </div>
    </div>

    <div class="card">
        <div class="card-content">
            <div class="columns">
                <div class="column is-8">
                    <div class="field">
                        <label class="label">Titre</label>
                        <div class="control">
                            <p class="subtitle"><?= esc($book['title']) ?></p>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Auteur</label>
                        <div class="control">
                            <p class="subtitle"><?= esc($book['author']) ?></p>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">ISBN</label>
                        <div class="control">
                            <code><?= esc($book['isbn']) ?></code>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Catégorie</label>
                        <div class="control">
                            <span class="tag is-info"><?= esc($book['category'] ?? 'Non définie') ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="column is-4">
                    <div class="field">
                        <label class="label">Copies Total</label>
                        <div class="control">
                            <p class="title"><?= $book['total_copies'] ?? 0 ?></p>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Copies Disponibles</label>
                        <div class="control">
                            <p class="title has-text-success"><?= $book['available_copies'] ?? 0 ?></p>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Statut</label>
                        <div class="control">
                            <?php if (($book['available_copies'] ?? 0) > 0): ?>
                                <span class="tag is-success">Disponible</span>
                            <?php else: ?>
                                <span class="tag is-danger">Indisponible</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="field">
                <label class="label">Localisation</label>
                <div class="control">
                    <p><?= esc($book['location'] ?? 'Non définie') ?></p>
                </div>
            </div>
            
            <div class="field">
                <label class="label">Date d'Ajout</label>
                <div class="control">
                    <p><?= date('d/m/Y H:i', strtotime($book['created_at'] ?? 'now')) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <a href="<?= base_url('admin/bibliotheque/books') ?>" class="button is-light">
                    <i class="fas fa-arrow-left"></i>
                    Retour à la liste
                </a>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <a href="<?= base_url('admin/bibliotheque/books/' . $book['id'] . '/edit') ?>" class="button is-warning">
                    <i class="fas fa-edit"></i>
                    Modifier
                </a>
            </div>
            <div class="level-item">
                <?php if (($book['available_copies'] ?? 0) > 0): ?>
                    <a href="<?= base_url('admin/bibliotheque/loans/create?book_id=' . $book['id']) ?>" class="button is-success">
                        <i class="fas fa-hand-holding"></i>
                        Emprunter
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>






