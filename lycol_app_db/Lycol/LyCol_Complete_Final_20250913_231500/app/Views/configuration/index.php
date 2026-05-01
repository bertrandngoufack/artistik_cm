<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">Configuration</h1>
            <p class="subtitle">Paramètres du système</p>
        </div>
    </div>
    
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fas fa-cog fa-2x has-text-primary"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Général</p>
                            <p class="subtitle is-6">Paramètres généraux</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/configuration/general') ?>" class="button is-primary is-fullwidth">
                            Général
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
                                <i class="fas fa-database fa-2x has-text-info"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Base de Données</p>
                            <p class="subtitle is-6">Configuration BDD</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/configuration/database') ?>" class="button is-info is-fullwidth">
                            Base de Données
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
                                <i class="fas fa-envelope fa-2x has-text-success"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Email</p>
                            <p class="subtitle is-6">Configuration email</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/configuration/email') ?>" class="button is-success is-fullwidth">
                            Email
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
                                <i class="fas fa-backup fa-2x has-text-warning"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Sauvegarde</p>
                            <p class="subtitle is-6">Sauvegarde et restauration</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/configuration/backup') ?>" class="button is-warning is-fullwidth">
                            Sauvegarde
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
                            <p class="heading">Version système</p>
                            <p class="title is-3 has-text-primary">1.0.0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Dernière sauvegarde</p>
                            <p class="title is-3 has-text-info">N/A</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Espace disque</p>
                            <p class="title is-3 has-text-success">N/A</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Statut système</p>
                            <p class="title is-3 has-text-success">OK</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>