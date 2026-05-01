<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <span class="icon"><i class="fas fa-users"></i></span>
                Classes de l'Enseignant
            </h1>
            <p class="subtitle">
                <?= esc($teacher['first_name']) ?> <?= esc($teacher['last_name']) ?> - Responsabilités de Classe
            </p>
        </div>
        <div class="column is-narrow">
            <div class="buttons">
                <a href="<?= base_url('admin/enseignants/show/' . $teacher['id']) ?>" class="button is-light">
                    <span class="icon"><i class="fas fa-user"></i></span>
                    <span>Profil</span>
                </a>
                <a href="<?= base_url('admin/enseignants') ?>" class="button is-light">
                    <span class="icon"><i class="fas fa-arrow-left"></i></span>
                    <span>Retour</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Assignation comme responsable principal -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="fas fa-plus"></i></span>
                Assigner comme Responsable Principal
            </p>
        </header>
        <div class="card-content">
            <?php if (session()->has('success')): ?>
                <div class="notification is-success">
                    <button class="delete"></button>
                    <?= session('success') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->has('error')): ?>
                <div class="notification is-danger">
                    <button class="delete"></button>
                    <?= session('error') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('admin/enseignants/assign-class') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="teacher_id" value="<?= $teacher['id'] ?>">
                
                <div class="columns is-multiline">
                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Classe</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="class_id" required>
                                        <option value="">Sélectionner une classe</option>
                                        <?php foreach ($available_classes as $class): ?>
                                            <option value="<?= $class['id'] ?>">
                                                <?= esc($class['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="field">
                            <label class="label">&nbsp;</label>
                            <div class="control">
                                <button type="submit" class="button is-primary is-fullwidth">
                                    <span class="icon"><i class="fas fa-plus"></i></span>
                                    <span>Assigner comme Responsable</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Classes dont il est responsable principal -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="fas fa-list"></i></span>
                Classes Responsable Principal
            </p>
        </header>
        <div class="card-content">
            <?php if (!empty($teacher_classes)): ?>
                <div class="table-container">
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Classe</th>
                                <th>Niveau</th>
                                <th>Série</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teacher_classes as $class): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($class['name']) ?></strong>
                                </td>
                                <td>
                                    <?= esc($class['level_name']) ?>
                                </td>
                                <td>
                                    <?= esc($class['series_name']) ?>
                                </td>
                                <td>
                                    <form action="<?= base_url('admin/enseignants/remove-class') ?>" method="post" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                                        <button type="submit" class="button is-small is-danger" 
                                                onclick="return confirm('Êtes-vous sûr de vouloir retirer cette responsabilité ?')">
                                            <span class="icon"><i class="fas fa-trash"></i></span>
                                            <span>Retirer</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="has-text-centered has-text-grey">
                    <p>Cet enseignant n'est responsable principal d'aucune classe</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Informations sur les responsabilités -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="fas fa-info-circle"></i></span>
                Informations sur les Responsabilités
            </p>
        </header>
        <div class="card-content">
            <div class="content">
                <h5>Rôle de Responsable Principal :</h5>
                <ul>
                    <li>Gestion administrative de la classe</li>
                    <li>Suivi des élèves et coordination pédagogique</li>
                    <li>Liaison avec les parents d'élèves</li>
                    <li>Gestion des emplois du temps</li>
                    <li>Coordination avec les autres enseignants</li>
                </ul>
                
                <div class="notification is-info">
                    <strong>Note :</strong> Un enseignant peut être responsable principal d'une seule classe à la fois. 
                    L'assignation d'une nouvelle classe remplacera automatiquement la responsabilité précédente.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Supprimer les notifications
    document.querySelectorAll('.notification .delete').forEach(function(button) {
        button.addEventListener('click', function() {
            this.parentNode.remove();
        });
    });
});
</script>

<?= $this->endSection() ?>









