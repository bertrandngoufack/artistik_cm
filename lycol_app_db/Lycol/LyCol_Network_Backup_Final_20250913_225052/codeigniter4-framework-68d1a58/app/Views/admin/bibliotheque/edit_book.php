<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/bibliotheque') ?>">Bibliothèque</a></li>
                    <li><a href="<?= base_url('admin/bibliotheque/books') ?>">Gestion des Livres</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Modifier le Livre</a></li>
                </ul>
            </nav>
            
            <h1 class="title">
                <i class="fas fa-edit"></i>
                Modifier le Livre
            </h1>
            <p class="subtitle">Modifier les informations du livre</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="notification is-danger">
            <button class="delete" onclick="this.parentElement.remove()"></button>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="columns">
        <div class="column is-8">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-book"></i>
                        Informations du Livre
                    </p>
                </div>
                <div class="card-content">
                    <form action="<?= base_url('admin/bibliotheque/books/' . $book['id'] . '/update') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Titre du Livre *</label>
                                    <div class="control">
                                        <input class="input <?= session()->getFlashdata('errors.title') ? 'is-danger' : '' ?>" 
                                               type="text" 
                                               name="title" 
                                               value="<?= old('title', $book['title']) ?>" 
                                               placeholder="Titre du livre" 
                                               required>
                                    </div>
                                    <?php if (session()->getFlashdata('errors.title')): ?>
                                        <p class="help is-danger"><?= session()->getFlashdata('errors.title') ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Auteur *</label>
                                    <div class="control">
                                        <input class="input <?= session()->getFlashdata('errors.author') ? 'is-danger' : '' ?>" 
                                               type="text" 
                                               name="author" 
                                               value="<?= old('author', $book['author']) ?>" 
                                               placeholder="Nom de l'auteur" 
                                               required>
                                    </div>
                                    <?php if (session()->getFlashdata('errors.author')): ?>
                                        <p class="help is-danger"><?= session()->getFlashdata('errors.author') ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">ISBN *</label>
                                    <div class="control">
                                        <input class="input <?= session()->getFlashdata('errors.isbn') ? 'is-danger' : '' ?>" 
                                               type="text" 
                                               name="isbn" 
                                               value="<?= old('isbn', $book['isbn']) ?>" 
                                               placeholder="9781234567890" 
                                               required>
                                    </div>
                                    <?php if (session()->getFlashdata('errors.isbn')): ?>
                                        <p class="help is-danger"><?= session()->getFlashdata('errors.isbn') ?></p>
                                    <?php endif; ?>
                                    <p class="help">Format: 10 ou 13 chiffres</p>
                                </div>
                            </div>
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Catégorie</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="category">
                                                <option value="">Sélectionner une catégorie</option>
                                                <option value="litterature" <?= (old('category', $book['category'] ?? '') === 'litterature') ? 'selected' : '' ?>>Littérature</option>
                                                <option value="scolaire" <?= (old('category', $book['category'] ?? '') === 'scolaire') ? 'selected' : '' ?>>Scolaire</option>
                                                <option value="reference" <?= (old('category', $book['category'] ?? '') === 'reference') ? 'selected' : '' ?>>Référence</option>
                                                <option value="sciences" <?= (old('category', $book['category'] ?? '') === 'sciences') ? 'selected' : '' ?>>Sciences</option>
                                                <option value="histoire" <?= (old('category', $book['category'] ?? '') === 'histoire') ? 'selected' : '' ?>>Histoire</option>
                                                <option value="geographie" <?= (old('category', $book['category'] ?? '') === 'geographie') ? 'selected' : '' ?>>Géographie</option>
                                                <option value="philosophie" <?= (old('category', $book['category'] ?? '') === 'philosophie') ? 'selected' : '' ?>>Philosophie</option>
                                                <option value="art" <?= (old('category', $book['category'] ?? '') === 'art') ? 'selected' : '' ?>>Art</option>
                                                <option value="technologie" <?= (old('category', $book['category'] ?? '') === 'technologie') ? 'selected' : '' ?>>Technologie</option>
                                                <option value="autre" <?= (old('category', $book['category'] ?? '') === 'autre') ? 'selected' : '' ?>>Autre</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column is-4">
                                <div class="field">
                                    <label class="label">Nombre de Copies *</label>
                                    <div class="control">
                                        <input class="input <?= session()->getFlashdata('errors.total_copies') ? 'is-danger' : '' ?>" 
                                               type="number" 
                                               name="total_copies" 
                                               value="<?= old('total_copies', $book['total_copies'] ?? 0) ?>" 
                                               min="1" 
                                               required>
                                    </div>
                                    <?php if (session()->getFlashdata('errors.total_copies')): ?>
                                        <p class="help is-danger"><?= session()->getFlashdata('errors.total_copies') ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="column is-4">
                                <div class="field">
                                    <label class="label">Éditeur</label>
                                    <div class="control">
                                        <input class="input" 
                                               type="text" 
                                               name="publisher" 
                                               value="<?= old('publisher', $book['publisher'] ?? '') ?>" 
                                               placeholder="Nom de l'éditeur">
                                    </div>
                                </div>
                            </div>
                            <div class="column is-4">
                                <div class="field">
                                    <label class="label">Année de Publication</label>
                                    <div class="control">
                                        <input class="input" 
                                               type="number" 
                                               name="publication_year" 
                                               value="<?= old('publication_year', $book['publication_year'] ?? '') ?>" 
                                               min="1900" 
                                               max="<?= date('Y') + 1 ?>" 
                                               placeholder="<?= date('Y') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Description</label>
                            <div class="control">
                                <textarea class="textarea" 
                                          name="description" 
                                          placeholder="Description du livre (résumé, contenu, etc.)"
                                          rows="4"><?= old('description', $book['description'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Langue</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="language">
                                                <option value="francais" <?= (old('language', $book['language'] ?? '') === 'francais') ? 'selected' : '' ?>>Français</option>
                                                <option value="anglais" <?= (old('language', $book['language'] ?? '') === 'anglais') ? 'selected' : '' ?>>Anglais</option>
                                                <option value="espagnol" <?= (old('language', $book['language'] ?? '') === 'espagnol') ? 'selected' : '' ?>>Espagnol</option>
                                                <option value="allemand" <?= (old('language', $book['language'] ?? '') === 'allemand') ? 'selected' : '' ?>>Allemand</option>
                                                <option value="italien" <?= (old('language', $book['language'] ?? '') === 'italien') ? 'selected' : '' ?>>Italien</option>
                                                <option value="autre" <?= (old('language', $book['language'] ?? '') === 'autre') ? 'selected' : '' ?>>Autre</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Prix d'Achat</label>
                                    <div class="control">
                                        <input class="input" 
                                               type="number" 
                                               name="purchase_price" 
                                               value="<?= old('purchase_price', $book['purchase_price'] ?? '') ?>" 
                                               min="0" 
                                               step="0.01" 
                                               placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Localisation</label>
                            <div class="control">
                                <input class="input" 
                                       type="text" 
                                       name="location" 
                                       value="<?= old('location', $book['location'] ?? '') ?>" 
                                       placeholder="Ex: Rayon A, Étagère 3, Position 15">
                            </div>
                            <p class="help">Indiquez l'emplacement physique du livre dans la bibliothèque</p>
                        </div>

                        <div class="field">
                            <div class="control">
                                <label class="checkbox">
                                    <input type="checkbox" name="is_active" value="1" <?= (old('is_active', $book['is_active'] ?? 1)) ? 'checked' : '' ?>>
                                    Actif (disponible pour les emprunts)
                                </label>
                            </div>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <i class="fas fa-save"></i>
                                    Mettre à Jour
                                </button>
                            </div>
                            <div class="control">
                                <a href="<?= base_url('admin/bibliotheque/books') ?>" class="button is-light">
                                    <i class="fas fa-times"></i>
                                    Annuler
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="column is-4">
            <!-- Informations du Livre -->
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-info-circle"></i>
                        Informations Actuelles
                    </p>
                </div>
                <div class="card-content">
                    <div class="content">
                        <p><strong>ID :</strong> <?= $book['id'] ?></p>
                        <p><strong>Titre :</strong> <?= esc($book['title']) ?></p>
                        <p><strong>Auteur :</strong> <?= esc($book['author']) ?></p>
                        <p><strong>ISBN :</strong> <code><?= esc($book['isbn']) ?></code></p>
                        <p><strong>Copies :</strong> <?= $book['total_copies'] ?? 0 ?></p>
                        <p><strong>Statut :</strong> 
                            <span class="tag <?= $book['is_active'] ? 'is-success' : 'is-danger' ?>">
                                <?= $book['is_active'] ? 'Actif' : 'Inactif' ?>
                            </span>
                        </p>
                        <p><strong>Créé le :</strong> <?= date('d/m/Y H:i', strtotime($book['created_at'])) ?></p>
                        <p><strong>Modifié le :</strong> <?= date('d/m/Y H:i', strtotime($book['updated_at'])) ?></p>
                    </div>
                </div>
            </div>

            <!-- Statistiques du Livre -->
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-chart-bar"></i>
                        Statistiques du Livre
                    </p>
                </div>
                <div class="card-content">
                    <div class="content">
                        <div class="level">
                            <div class="level-item has-text-centered">
                                <div>
                                    <p class="heading">Emprunts Totaux</p>
                                    <p class="title"><?= $bookStats['totalLoans'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="level">
                            <div class="level-item has-text-centered">
                                <div>
                                    <p class="heading">Emprunts Actifs</p>
                                    <p class="title has-text-warning"><?= $bookStats['activeLoans'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="level">
                            <div class="level-item has-text-centered">
                                <div>
                                    <p class="heading">Disponibles</p>
                                    <p class="title has-text-success"><?= $bookStats['availableCopies'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Rapides -->
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-bolt"></i>
                        Actions Rapides
                    </p>
                </div>
                <div class="card-content">
                    <div class="content">
                        <div class="buttons">
                            <a href="<?= base_url('admin/bibliotheque/books/' . $book['id']) ?>" class="button is-info is-fullwidth">
                                <i class="fas fa-eye"></i>
                                Voir les Détails
                            </a>
                            <?php if (($bookStats['availableCopies'] ?? 0) > 0): ?>
                                <a href="<?= base_url('admin/bibliotheque/loans/create?book_id=' . $book['id']) ?>" class="button is-success is-fullwidth">
                                    <i class="fas fa-hand-holding"></i>
                                    Emprunter
                                </a>
                            <?php endif; ?>
                            <button onclick="deleteBook(<?= $book['id'] ?>)" class="button is-danger is-fullwidth">
                                <i class="fas fa-trash"></i>
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validation en temps réel de l'ISBN
document.querySelector('input[name="isbn"]').addEventListener('input', function() {
    const isbn = this.value.replace(/[-\s]/g, '');
    const isValid = /^\d{10}(\d{3})?$/.test(isbn);
    
    if (isbn.length > 0) {
        if (isValid) {
            this.classList.remove('is-danger');
            this.classList.add('is-success');
        } else {
            this.classList.remove('is-success');
            this.classList.add('is-danger');
        }
    } else {
        this.classList.remove('is-success', 'is-danger');
    }
});

// Validation du nombre de copies
document.querySelector('input[name="total_copies"]').addEventListener('input', function() {
    const copies = parseInt(this.value);
    
    if (copies > 0) {
        this.classList.remove('is-danger');
        this.classList.add('is-success');
    } else {
        this.classList.remove('is-success');
        this.classList.add('is-danger');
    }
});

function deleteBook(bookId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce livre ? Cette action est irréversible.')) {
        window.location.href = '<?= base_url('admin/bibliotheque/books') ?>/' + bookId + '/delete';
    }
}
</script>

<?= $this->endSection() ?>

