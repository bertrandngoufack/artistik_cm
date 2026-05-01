<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">Statistiques</h1>
            <p class="subtitle">Tableaux de bord et rapports</p>
        </div>
    </div>
    
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fas fa-chart-pie fa-2x has-text-primary"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Tableaux de Bord</p>
                            <p class="subtitle is-6">Vue d'ensemble</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/statistiques/dashboard') ?>" class="button is-primary is-fullwidth">
                            Tableaux de Bord
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
                                <i class="fas fa-chart-line fa-2x has-text-info"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Rapports</p>
                            <p class="subtitle is-6">Rapports détaillés</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/statistiques/reports') ?>" class="button is-info is-fullwidth">
                            Rapports
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
                                <i class="fas fa-download fa-2x has-text-success"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Exports</p>
                            <p class="subtitle is-6">Exporter les données</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/statistiques/exports') ?>" class="button is-success is-fullwidth">
                            Exports
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
                                <i class="fas fa-cog fa-2x has-text-warning"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Configuration</p>
                            <p class="subtitle is-6">Paramètres des rapports</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/statistiques/settings') ?>" class="button is-warning is-fullwidth">
                            Configuration
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
                            <p class="heading">Étudiants inscrits</p>
                            <p class="title is-3 has-text-primary">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Enseignants actifs</p>
                            <p class="title is-3 has-text-info">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Classes ouvertes</p>
                            <p class="title is-3 has-text-success">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Taux de réussite</p>
                            <p class="title is-3 has-text-warning">0%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>