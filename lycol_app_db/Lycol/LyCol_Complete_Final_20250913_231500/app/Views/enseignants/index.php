<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">Gestion Enseignants</h1>
            <p class="subtitle">Personnel enseignant</p>
        </div>
    </div>
    
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fas fa-chalkboard-teacher fa-2x has-text-primary"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Enseignants</p>
                            <p class="subtitle is-6">Liste des enseignants</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/enseignants/teachers') ?>" class="button is-primary is-fullwidth">
                            Enseignants
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fas fa-plus-circle fa-2x has-text-info"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Nouvel Enseignant</p>
                            <p class="subtitle is-6">Ajouter un enseignant</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/enseignants/teachers/create') ?>" class="button is-info is-fullwidth">
                            Nouvel Enseignant
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fas fa-calendar-alt fa-2x has-text-success"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Planning</p>
                            <p class="subtitle is-6">Emploi du temps</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/enseignants/schedule') ?>" class="button is-success is-fullwidth">
                            Planning
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fas fa-chart-bar fa-2x has-text-warning"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Évaluations</p>
                            <p class="subtitle is-6">Notes et évaluations</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/enseignants/evaluations') ?>" class="button is-warning is-fullwidth">
                            Évaluations
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistiques rapides -->
    <div class="columns mt-6">
        <div class="column">
            <div class="box">
                <h2 class="title is-4">Statistiques</h2>
                <div class="columns">
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Enseignants actifs</p>
                            <p class="title is-3 has-text-primary">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Matières enseignées</p>
                            <p class="title is-3 has-text-info">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Heures de cours</p>
                            <p class="title is-3 has-text-success">0h</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Évaluations en cours</p>
                            <p class="title is-3 has-text-warning">0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>