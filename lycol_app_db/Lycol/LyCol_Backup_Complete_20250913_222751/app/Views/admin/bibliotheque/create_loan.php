<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Nouvel Emprunt</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/bibliotheque/loans') ?>" class="button is-light">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour</span>
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-content">
        <form method="POST" action="<?= base_url('admin/bibliotheque/loans/store') ?>">
            <div class="columns">
                <div class="column is-6">
                    <div class="field">
                        <label class="label">Livre</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="book_id" required>
                                    <option value="">Sélectionner un livre</option>
                                    <?php if (!empty($books)): ?>
                                        <?php foreach ($books as $book): ?>
                                            <option value="<?= $book['id'] ?>"><?= esc($book['title']) ?> - <?= esc($book['author']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="column is-6">
                    <div class="field">
                        <label class="label">ID Membre</label>
                        <div class="control">
                            <input class="input" type="number" name="member_id" placeholder="ID du membre" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="columns">
                <div class="column is-6">
                    <div class="field">
                        <label class="label">Type de Membre</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="member_type" required>
                                    <option value="">Sélectionner le type</option>
                                    <option value="STUDENT">Étudiant</option>
                                    <option value="STAFF">Personnel</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="column is-6">
                    <div class="field">
                        <label class="label">Notes</label>
                        <div class="control">
                            <textarea class="textarea" name="notes" placeholder="Notes supplémentaires"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="columns">
                <div class="column is-6">
                    <div class="field">
                        <label class="label">Date d'emprunt</label>
                        <div class="control">
                            <input class="input" type="date" name="loan_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="column is-6">
                    <div class="field">
                        <label class="label">Date de retour prévue</label>
                        <div class="control">
                            <input class="input" type="date" name="due_date" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <button type="submit" class="button is-primary">
                        <span class="icon"><i class="fas fa-save"></i></span>
                        <span>Enregistrer l'emprunt</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

