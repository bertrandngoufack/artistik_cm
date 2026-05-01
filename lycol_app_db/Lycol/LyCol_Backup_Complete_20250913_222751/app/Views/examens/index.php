<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">Gestion des Examens</h1>
            <p class="subtitle">Examens, évaluations et notes</p>
        </div>
    </div>
    
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fas fa-clipboard-list fa-2x has-text-primary"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Examens</p>
                            <p class="subtitle is-6">Gestion des examens</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/examens/exams') ?>" class="button is-primary is-fullwidth">
                            Voir les examens
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
                            <p class="title is-5">Nouvel Examen</p>
                            <p class="subtitle is-6">Créer un examen</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/examens/exams/create') ?>" class="button is-info is-fullwidth">
                            Nouvel examen
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
                                <i class="fas fa-chart-line fa-2x has-text-success"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Notes</p>
                            <p class="subtitle is-6">Saisie des notes</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/examens/grades') ?>" class="button is-success is-fullwidth">
                            Saisir les notes
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
                                <i class="fas fa-file-alt fa-2x has-text-warning"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Bulletins</p>
                            <p class="subtitle is-6">Génération des bulletins</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/examens/reports') ?>" class="button is-warning is-fullwidth">
                            Générer les bulletins
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
                            <p class="heading">Examens programmés</p>
                            <p class="title is-3 has-text-primary">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Notes saisies</p>
                            <p class="title is-3 has-text-info">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Bulletins générés</p>
                            <p class="title is-3 has-text-success">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Moyenne générale</p>
                            <p class="title is-3 has-text-warning">0/20</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
