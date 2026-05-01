<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">Gestion Économat</h1>
            <p class="subtitle">Gestion des paiements et finances</p>
        </div>
    </div>
    
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fas fa-money-bill-wave fa-2x has-text-success"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Paiements</p>
                            <p class="subtitle is-6">Gestion des paiements</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/economat/payments') ?>" class="button is-primary is-fullwidth">
                            Voir les paiements
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
                            <p class="title is-5">Nouveau Paiement</p>
                            <p class="subtitle is-6">Enregistrer un paiement</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/economat/payments/create') ?>" class="button is-info is-fullwidth">
                            Nouveau paiement
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
                                <i class="fas fa-bell fa-2x has-text-warning"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Rappels</p>
                            <p class="subtitle is-6">Gestion des rappels</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/economat/reminders') ?>" class="button is-warning is-fullwidth">
                            Voir les rappels
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
                                <i class="fas fa-chart-bar fa-2x has-text-danger"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Rapports</p>
                            <p class="subtitle is-6">Statistiques financières</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/economat/reports') ?>" class="button is-danger is-fullwidth">
                            Voir les rapports
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
                <h2 class="title is-4">Statistiques du mois</h2>
                <div class="columns">
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Paiements reçus</p>
                            <p class="title is-3 has-text-success">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Montant total</p>
                            <p class="title is-3 has-text-info">0 FCFA</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">En attente</p>
                            <p class="title is-3 has-text-warning">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">En retard</p>
                            <p class="title is-3 has-text-danger">0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
