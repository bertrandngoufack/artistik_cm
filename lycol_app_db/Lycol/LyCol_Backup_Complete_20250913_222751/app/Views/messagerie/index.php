<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">Gestion Messagerie</h1>
            <p class="subtitle">Communication interne</p>
        </div>
    </div>
    
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fas fa-envelope fa-2x has-text-primary"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Messages</p>
                            <p class="subtitle is-6">Boîte de réception</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/messagerie/messages') ?>" class="button is-primary is-fullwidth">
                            Messages
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
                            <p class="title is-5">Nouveau Message</p>
                            <p class="subtitle is-6">Envoyer un message</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/messagerie/messages/create') ?>" class="button is-info is-fullwidth">
                            Nouveau Message
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
                                <i class="fas fa-paper-plane fa-2x has-text-success"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Messages Envoyés</p>
                            <p class="subtitle is-6">Messages expédiés</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/messagerie/sent') ?>" class="button is-success is-fullwidth">
                            Messages Envoyés
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
                                <i class="fas fa-archive fa-2x has-text-warning"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Archives</p>
                            <p class="subtitle is-6">Messages archivés</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/messagerie/archives') ?>" class="button is-warning is-fullwidth">
                            Archives
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
                            <p class="heading">Messages non lus</p>
                            <p class="title is-3 has-text-primary">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Messages envoyés</p>
                            <p class="title is-3 has-text-info">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Utilisateurs actifs</p>
                            <p class="title is-3 has-text-success">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Messages archivés</p>
                            <p class="title is-3 has-text-warning">0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>