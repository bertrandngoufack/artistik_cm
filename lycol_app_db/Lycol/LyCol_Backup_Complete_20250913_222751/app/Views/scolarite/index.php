<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">Gestion Scolarité</h1>
            <p class="subtitle">Gestion des étudiants et classes</p>
        </div>
    </div>
    
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fas fa-users fa-2x has-text-primary"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Étudiants</p>
                            <p class="subtitle is-6">Gestion des étudiants</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/scolarite/students') ?>" class="button is-primary is-fullwidth">
                            Voir les étudiants
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
                            <p class="title is-5">Nouvel Étudiant</p>
                            <p class="subtitle is-6">Inscrire un étudiant</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/scolarite/students/create') ?>" class="button is-info is-fullwidth">
                            Nouvel étudiant
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
                                <i class="fas fa-chalkboard fa-2x has-text-success"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-5">Classes</p>
                            <p class="subtitle is-6">Gestion des classes</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/scolarite/classes') ?>" class="button is-success is-fullwidth">
                            Voir les classes
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
                            <p class="title is-5">Inscriptions</p>
                            <p class="subtitle is-6">Gestion des inscriptions</p>
                        </div>
                    </div>
                    <div class="content">
                        <a href="<?= base_url('admin/scolarite/enrollments') ?>" class="button is-warning is-fullwidth">
                            Voir les inscriptions
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
                            <p class="heading">Total étudiants</p>
                            <p class="title is-3 has-text-primary">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Classes actives</p>
                            <p class="title is-3 has-text-info">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Nouvelles inscriptions</p>
                            <p class="title is-3 has-text-success">0</p>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="has-text-centered">
                            <p class="heading">Abandons</p>
                            <p class="title is-3 has-text-danger">0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
