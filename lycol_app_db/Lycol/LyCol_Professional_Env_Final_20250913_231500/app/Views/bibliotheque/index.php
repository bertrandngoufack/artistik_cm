<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">Gestion Bibliothèque</h1>
            <p class="subtitle">Livres, prêts et retours</p>
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
                            <p class="title is-5">Livres</p>
                            <p class="subtitle is-6">Catalogue des livres</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/bibliotheque/books') ?>" class="button is-primary is-fullwidth">
                            Livres
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
                            <p class="title is-5">Nouveau Livre</p>
                            <p class="subtitle is-6">Ajouter un livre</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/bibliotheque/books/create') ?>" class="button is-info is-fullwidth">
                            Nouveau Livre
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
                                <i class="fas fa-hand-holding fa-2x has-text-success"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Prêts</p>
                            <p class="subtitle is-6">Gestion des prêts</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/bibliotheque/loans') ?>" class="button is-success is-fullwidth">
                            Prêts
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
                                <i class="fas fa-users fa-2x has-text-warning"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Lecteurs</p>
                            <p class="subtitle is-6">Gestion des lecteurs</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/bibliotheque/readers') ?>" class="button is-warning is-fullwidth">
                            Lecteurs
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
                            <p class="heading">Livres disponibles</p>
                            <p class="title is-3 has-text-primary">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Prêts actifs</p>
                            <p class="title is-3 has-text-info">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Lecteurs inscrits</p>
                            <p class="title is-3 has-text-success">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Retours en retard</p>
                            <p class="title is-3 has-text-danger">0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>