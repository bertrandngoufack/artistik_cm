<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">Gestion Sécurité</h1>
            <p class="subtitle">Sécurité et accès</p>
        </div>
    </div>
    
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fas fa-shield-alt fa-2x has-text-primary"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Utilisateurs</p>
                            <p class="subtitle is-6">Gestion des utilisateurs</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/securite/users') ?>" class="button is-primary is-fullwidth">
                            Utilisateurs
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
                                <i class="fas fa-key fa-2x has-text-info"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Permissions</p>
                            <p class="subtitle is-6">Gestion des rôles</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/securite/permissions') ?>" class="button is-info is-fullwidth">
                            Permissions
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
                                <i class="fas fa-history fa-2x has-text-success"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Logs</p>
                            <p class="subtitle is-6">Journal des activités</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/securite/logs') ?>" class="button is-success is-fullwidth">
                            Logs
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
                                <i class="fas fa-lock fa-2x has-text-warning"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Sécurité</p>
                            <p class="subtitle is-6">Paramètres de sécurité</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/securite/settings') ?>" class="button is-warning is-fullwidth">
                            Sécurité
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
                            <p class="heading">Utilisateurs actifs</p>
                            <p class="title is-3 has-text-primary">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Connexions aujourd'hui</p>
                            <p class="title is-3 has-text-info">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Tentatives échouées</p>
                            <p class="title is-3 has-text-danger">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Dernière connexion</p>
                            <p class="title is-3 has-text-warning">N/A</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>