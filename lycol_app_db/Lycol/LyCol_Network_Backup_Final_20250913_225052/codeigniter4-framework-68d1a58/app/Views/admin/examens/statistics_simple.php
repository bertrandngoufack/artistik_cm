<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column is-12">
            <!-- Breadcrumb -->
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li><a href="<?= base_url('admin/examens') ?>">Module Examens</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Statistiques</a></li>
                </ul>
            </nav>

            <!-- Header -->
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h1 class="title">Statistiques des Examens</h1>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <a href="<?= base_url('admin/examens') ?>" class="button is-light">
                            <span class="icon">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span>Retour</span>
                        </a>
                    </div>
                </div>
            </div>

            <?php if (isset($error)): ?>
            <div class="notification is-danger">
                <button class="delete"></button>
                <strong>Erreur lors du chargement des statistiques :</strong><br>
                <?= esc($error) ?>
            </div>
            <?php endif; ?>

            <!-- Statistiques de base -->
            <div class="box">
                <h3 class="title is-5">
                    <span class="icon">
                        <i class="fas fa-info-circle"></i>
                    </span>
                    Informations de base
                </h3>
                
                <div class="content">
                    <p>Les statistiques détaillées ne sont pas disponibles pour le moment.</p>
                    <p>Veuillez vérifier que :</p>
                    <ul>
                        <li>La base de données est accessible</li>
                        <li>Les tables contiennent des données</li>
                        <li>Les permissions sont correctes</li>
                    </ul>
                </div>
            </div>

            <!-- Actions -->
            <div class="box">
                <h3 class="title is-5">
                    <span class="icon">
                        <i class="fas fa-tools"></i>
                    </span>
                    Actions disponibles
                </h3>
                
                <div class="buttons">
                    <a href="<?= base_url('admin/examens') ?>" class="button is-primary">
                        <span class="icon">
                            <i class="fas fa-home"></i>
                        </span>
                        <span>Dashboard Examens</span>
                    </a>
                    <a href="<?= base_url('admin/examens/exams') ?>" class="button is-info">
                        <span class="icon">
                            <i class="fas fa-list"></i>
                        </span>
                        <span>Liste des Examens</span>
                    </a>
                    <a href="<?= base_url('admin/examens/grades') ?>" class="button is-success">
                        <span class="icon">
                            <i class="fas fa-edit"></i>
                        </span>
                        <span>Gestion des Notes</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fermer les notifications
    document.querySelectorAll('.notification .delete').forEach(function(deleteButton) {
        deleteButton.addEventListener('click', function() {
            this.parentNode.remove();
        });
    });
});
</script>

<?= $this->endSection() ?>









