<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <h1 class="title">Créer un Cycle</h1>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <a href="<?= base_url('admin/etudes/cycles') ?>" class="button is-light">
                    <span class="icon"><i class="fas fa-arrow-left"></i></span>
                    <span>Retour</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="notification is-danger is-light">
            <button class="delete"></button>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire de création -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">Informations du Cycle</p>
        </header>
        <div class="card-content">
            <form method="POST" action="<?= base_url('admin/etudes/cycles/store') ?>">
                <div class="columns is-multiline">
                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Nom du Cycle *</label>
                            <div class="control">
                                <input class="input <?= session()->getFlashdata('errors') && isset(session()->getFlashdata('errors')['name']) ? 'is-danger' : '' ?>" 
                                       type="text" 
                                       name="name" 
                                       value="<?= old('name') ?>" 
                                       placeholder="Ex: Primaire, Collège, Lycée"
                                       required>
                            </div>
                            <?php if (session()->getFlashdata('errors') && isset(session()->getFlashdata('errors')['name'])): ?>
                                <p class="help is-danger"><?= session()->getFlashdata('errors')['name'] ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Code *</label>
                            <div class="control">
                                <input class="input <?= session()->getFlashdata('errors') && isset(session()->getFlashdata('errors')['code']) ? 'is-danger' : '' ?>" 
                                       type="text" 
                                       name="code" 
                                       value="<?= old('code') ?>" 
                                       placeholder="Ex: PRI, COL, LYC"
                                       required>
                            </div>
                            <?php if (session()->getFlashdata('errors') && isset(session()->getFlashdata('errors')['code'])): ?>
                                <p class="help is-danger"><?= session()->getFlashdata('errors')['code'] ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Description</label>
                    <div class="control">
                        <textarea class="textarea <?= session()->getFlashdata('errors') && isset(session()->getFlashdata('errors')['description']) ? 'is-danger' : '' ?>" 
                                  name="description" 
                                  placeholder="Description détaillée du cycle d'études"
                                  rows="3"><?= old('description') ?></textarea>
                    </div>
                    <?php if (session()->getFlashdata('errors') && isset(session()->getFlashdata('errors')['description'])): ?>
                        <p class="help is-danger"><?= session()->getFlashdata('errors')['description'] ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <div class="control">
                        <label class="checkbox">
                            <input type="checkbox" name="is_active" value="1" <?= old('is_active') ? 'checked' : '' ?>>
                            Cycle actif
                        </label>
                    </div>
                    <p class="help">Un cycle actif peut être utilisé pour créer des classes et assigner des élèves</p>
                </div>

                <div class="field is-grouped">
                    <div class="control">
                        <button type="submit" class="button is-primary">
                            <span class="icon"><i class="fas fa-save"></i></span>
                            <span>Créer le Cycle</span>
                        </button>
                    </div>
                    <div class="control">
                        <a href="<?= base_url('admin/etudes/cycles') ?>" class="button is-light">
                            <span class="icon"><i class="fas fa-times"></i></span>
                            <span>Annuler</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Informations supplémentaires -->
    <div class="notification is-info is-light mt-4">
        <div class="content">
            <h4 class="title is-5">Informations sur les cycles</h4>
            <p>Les cycles d'études permettent d'organiser les classes par niveau d'enseignement :</p>
            <ul>
                <li><strong>Maternelle :</strong> Classes de maternelle (3-6 ans)</li>
                <li><strong>Primaire :</strong> Classes du CP au CM2 (6-11 ans)</li>
                <li><strong>Collège :</strong> Classes de la 6ème à la 3ème (11-15 ans)</li>
                <li><strong>Lycée :</strong> Classes de la 2nde à la Terminale (15-18 ans)</li>
            </ul>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide notifications
    setTimeout(function() {
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach(function(notification) {
            notification.style.display = 'none';
        });
    }, 5000);

    // Close notification on click
    document.querySelectorAll('.notification .delete').forEach(function(button) {
        button.addEventListener('click', function() {
            this.parentNode.style.display = 'none';
        });
    });
});
</script>

<?= $this->endSection() ?>
