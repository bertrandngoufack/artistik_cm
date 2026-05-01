<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Nouveau Livre</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/bibliotheque/books') ?>" class="button is-light">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-content">
        <form method="POST" action="<?= base_url('admin/bibliotheque/books/store') ?>">
            <div class="columns">
                <div class="column is-6">
                    <div class="field">
                        <label class="label">Titre</label>
                        <div class="control">
                            <input class="input" type="text" name="title" placeholder="Titre du livre" required>
                        </div>
                    </div>
                </div>
                
                <div class="column is-6">
                    <div class="field">
                        <label class="label">Auteur</label>
                        <div class="control">
                            <input class="input" type="text" name="author" placeholder="Nom de l'auteur" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="columns">
                <div class="column is-6">
                    <div class="field">
                        <label class="label">ISBN</label>
                        <div class="control">
                            <input class="input" type="text" name="isbn" placeholder="ISBN du livre">
                        </div>
                    </div>
                </div>
                
                <div class="column is-6">
                    <div class="field">
                        <label class="label">Catégorie</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="category">
                                    <option value="">Sélectionner une catégorie</option>
                                    <option value="litterature">Littérature</option>
                                    <option value="scolaire">Scolaire</option>
                                    <option value="reference">Référence</option>
                                    <option value="sciences">Sciences</option>
                                    <option value="histoire">Histoire</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="columns">
                <div class="column is-6">
                    <div class="field">
                        <label class="label">Nombre de copies</label>
                        <div class="control">
                            <input class="input" type="number" name="total_copies" value="1" min="1" required>
                        </div>
                    </div>
                </div>
                
                <div class="column is-6">
                    <div class="field">
                        <label class="label">Année de publication</label>
                        <div class="control">
                            <input class="input" type="number" name="publication_year" placeholder="Année">
                        </div>
                    </div>
                </div>
            </div>

            <div class="field">
                <label class="label">Description</label>
                <div class="control">
                    <textarea class="textarea" name="description" placeholder="Description du livre" rows="4"></textarea>
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <button type="submit" class="button is-primary">
                        <span class="icon"><i class="fas fa-save"></i></span>
                        <span>Enregistrer le livre</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

