<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">Gestion des Études</h1>
            <p class="subtitle">Programmes, matières et emplois du temps</p>
        </div>
    </div>
    
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fas fa-book fa-2x has-text-primary"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Matières</p>
                            <p class="subtitle is-6">Gestion des matières</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/etudes/subjects') ?>" class="button is-primary is-fullwidth">
                            Voir les matières
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
                            <p class="title is-5">Nouvelle Matière</p>
                            <p class="subtitle is-6">Ajouter une matière</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/etudes/subjects/create') ?>" class="button is-info is-fullwidth">
                            Nouvelle matière
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
                            <p class="title is-5">Emploi du Temps</p>
                            <p class="subtitle is-6">Planning des cours</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/etudes/schedule') ?>" class="button is-success is-fullwidth">
                            Voir l'emploi du temps
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
                                <i class="fas fa-graduation-cap fa-2x has-text-warning"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Programmes</p>
                            <p class="subtitle is-6">Programmes d'études</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/etudes/programs') ?>" class="button is-warning is-fullwidth">
                            Voir les programmes
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
                            <p class="heading">Matières actives</p>
                            <p class="title is-3 has-text-primary">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Programmes</p>
                            <p class="title is-3 has-text-info">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Cours cette semaine</p>
                            <p class="title is-3 has-text-success">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Heures totales</p>
                            <p class="title is-3 has-text-warning">0h</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
