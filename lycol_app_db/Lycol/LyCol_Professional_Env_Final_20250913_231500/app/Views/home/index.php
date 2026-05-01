<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="columns is-centered">
        <div class="column is-8">
            <div class="box">
                <h1 class="title is-2 has-text-centered">Bienvenue sur LyCol</h1>
                <h2 class="subtitle is-4 has-text-centered">Système de Gestion Scolaire</h2>
                
                <div class="content">
                    <p class="has-text-centered">
                        LyCol est un système de gestion scolaire complet qui vous permet de gérer 
                        efficacement tous les aspects de votre établissement scolaire.
                    </p>
                </div>
                
                <div class="buttons is-centered">
                    <a href="<?= base_url('auth/login') ?>" class="button is-primary is-large">
                        <span class="icon">
                            <i class="fas fa-sign-in-alt"></i>
                        </span>
                        <span>Se connecter</span>
                    </a>
                </div>
                
                <div class="columns is-multiline mt-6">
                    <div class="column is-4">
                        <div class="card">
                            <div class="card-content">
                                <div class="media">
                                    <div class="media-left">
                                        <figure class="image is-48x48">
                                            <i class="fas fa-graduation-cap fa-2x has-text-primary"></i>
                                        </figure>
                                    </div>
                                    <div class="media-content">
                                        <p class="title is-5">Gestion des Étudiants</p>
                                        <p class="subtitle is-6">Inscription, suivi, bulletins</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="column is-4">
                        <div class="card">
                            <div class="card-content">
                                <div class="media">
                                    <div class="media-left">
                                        <figure class="image is-48x48">
                                            <i class="fas fa-chalkboard-teacher fa-2x has-text-info"></i>
                                        </figure>
                                    </div>
                                    <div class="media-content">
                                        <p class="title is-5">Gestion des Enseignants</p>
                                        <p class="subtitle is-6">Planning, évaluations</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="column is-4">
                        <div class="card">
                            <div class="card-content">
                                <div class="media">
                                    <div class="media-left">
                                        <figure class="image is-48x48">
                                            <i class="fas fa-money-bill-wave fa-2x has-text-success"></i>
                                        </figure>
                                    </div>
                                    <div class="media-content">
                                        <p class="title is-5">Gestion Financière</p>
                                        <p class="subtitle is-6">Paiements, facturation</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
