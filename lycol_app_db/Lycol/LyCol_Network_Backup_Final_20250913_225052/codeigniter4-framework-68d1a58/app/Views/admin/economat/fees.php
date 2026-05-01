<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <span class="icon"><i class="fas fa-tags"></i></span>
                Types de Frais
            </h1>
            <p class="subtitle">Gérez les différents types de frais scolaires</p>
        </div>
        <div class="column is-narrow">
            <a href="/admin/economat/fees/create" class="button is-primary">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Nouveau Type de Frais</span>
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="columns">
        <div class="column">
            <div class="box has-text-centered">
                <p class="heading">Total Types de Frais</p>
                <p class="title has-text-info">5</p>
            </div>
        </div>
        <div class="column">
            <div class="box has-text-centered">
                <p class="heading">Frais Actifs</p>
                <p class="title has-text-success">5</p>
            </div>
        </div>
        <div class="column">
            <div class="box has-text-centered">
                <p class="heading">Frais Inactifs</p>
                <p class="title has-text-warning">0</p>
            </div>
        </div>
        <div class="column">
            <div class="box has-text-centered">
                <p class="heading">Montant Total</p>
                <p class="title has-text-primary">270,000 FCFA</p>
            </div>
        </div>
    </div>

    <!-- Liste des types de frais -->
    <div class="box">
        <div class="table-container">
            <table class="table is-fullwidth is-striped is-hoverable">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Montant</th>
                        <th>Fréquence</th>
                        <th>Description</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>Frais de scolarité</strong>
                        </td>
                        <td>150,000 FCFA</td>
                        <td><span class="tag is-info">Annuel</span></td>
                        <td>Frais de scolarité annuels</td>
                        <td><span class="tag is-success">Actif</span></td>
                        <td>
                            <div class="buttons are-small">
                                <a href="/admin/economat/fees/1" class="button is-info">
                                    <span class="icon"><i class="fas fa-eye"></i></span>
                                </a>
                                <a href="/admin/economat/fees/1/edit" class="button is-warning">
                                    <span class="icon"><i class="fas fa-edit"></i></span>
                                </a>
                                <button class="button is-danger">
                                    <span class="icon"><i class="fas fa-trash"></i></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Frais d'inscription</strong>
                        </td>
                        <td>50,000 FCFA</td>
                        <td><span class="tag is-info">Annuel</span></td>
                        <td>Frais d'inscription</td>
                        <td><span class="tag is-success">Actif</span></td>
                        <td>
                            <div class="buttons are-small">
                                <a href="/admin/economat/fees/2" class="button is-info">
                                    <span class="icon"><i class="fas fa-eye"></i></span>
                                </a>
                                <a href="/admin/economat/fees/2/edit" class="button is-warning">
                                    <span class="icon"><i class="fas fa-edit"></i></span>
                                </a>
                                <button class="button is-danger">
                                    <span class="icon"><i class="fas fa-trash"></i></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Frais de cantine</strong>
                        </td>
                        <td>25,000 FCFA</td>
                        <td><span class="tag is-warning">Mensuel</span></td>
                        <td>Frais de restauration</td>
                        <td><span class="tag is-success">Actif</span></td>
                        <td>
                            <div class="buttons are-small">
                                <a href="/admin/economat/fees/3" class="button is-info">
                                    <span class="icon"><i class="fas fa-eye"></i></span>
                                </a>
                                <a href="/admin/economat/fees/3/edit" class="button is-warning">
                                    <span class="icon"><i class="fas fa-edit"></i></span>
                                </a>
                                <button class="button is-danger">
                                    <span class="icon"><i class="fas fa-trash"></i></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Frais de transport</strong>
                        </td>
                        <td>30,000 FCFA</td>
                        <td><span class="tag is-warning">Mensuel</span></td>
                        <td>Transport scolaire</td>
                        <td><span class="tag is-success">Actif</span></td>
                        <td>
                            <div class="buttons are-small">
                                <a href="/admin/economat/fees/4" class="button is-info">
                                    <span class="icon"><i class="fas fa-eye"></i></span>
                                </a>
                                <a href="/admin/economat/fees/4/edit" class="button is-warning">
                                    <span class="icon"><i class="fas fa-edit"></i></span>
                                </a>
                                <button class="button is-danger">
                                    <span class="icon"><i class="fas fa-trash"></i></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Frais de laboratoire</strong>
                        </td>
                        <td>15,000 FCFA</td>
                        <td><span class="tag is-info">Annuel</span></td>
                        <td>Frais de laboratoire</td>
                        <td><span class="tag is-success">Actif</span></td>
                        <td>
                            <div class="buttons are-small">
                                <a href="/admin/economat/fees/5" class="button is-info">
                                    <span class="icon"><i class="fas fa-eye"></i></span>
                                </a>
                                <a href="/admin/economat/fees/5/edit" class="button is-warning">
                                    <span class="icon"><i class="fas fa-edit"></i></span>
                                </a>
                                <button class="button is-danger">
                                    <span class="icon"><i class="fas fa-trash"></i></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Graphique des frais -->
    <div class="box">
        <h3 class="title is-4">Répartition des Frais</h3>
        <div class="columns">
            <div class="column">
                <div class="notification is-info">
                    <h4 class="title is-5">Frais de scolarité</h4>
                    <p class="subtitle is-6">150,000 FCFA (55.6%)</p>
                    <progress class="progress is-info" value="55.6" max="100">55.6%</progress>
                </div>
            </div>
            <div class="column">
                <div class="notification is-warning">
                    <h4 class="title is-5">Frais d'inscription</h4>
                    <p class="subtitle is-6">50,000 FCFA (18.5%)</p>
                    <progress class="progress is-warning" value="18.5" max="100">18.5%</progress>
                </div>
            </div>
            <div class="column">
                <div class="notification is-success">
                    <h4 class="title is-5">Frais de cantine</h4>
                    <p class="subtitle is-6">25,000 FCFA (9.3%)</p>
                    <progress class="progress is-success" value="9.3" max="100">9.3%</progress>
                </div>
            </div>
        </div>
        <div class="columns">
            <div class="column">
                <div class="notification is-danger">
                    <h4 class="title is-5">Frais de transport</h4>
                    <p class="subtitle is-6">30,000 FCFA (11.1%)</p>
                    <progress class="progress is-danger" value="11.1" max="100">11.1%</progress>
                </div>
            </div>
            <div class="column">
                <div class="notification is-primary">
                    <h4 class="title is-5">Frais de laboratoire</h4>
                    <p class="subtitle is-6">15,000 FCFA (5.6%)</p>
                    <progress class="progress is-primary" value="5.6" max="100">5.6%</progress>
                </div>
            </div>
            <div class="column">
                <!-- Espace vide pour l'alignement -->
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="box">
        <div class="columns">
            <div class="column">
                <div class="buttons">
                    <button class="button is-success">
                        <span class="icon"><i class="fas fa-download"></i></span>
                        <span>Exporter la liste</span>
                    </button>
                    <button class="button is-info">
                        <span class="icon"><i class="fas fa-print"></i></span>
                        <span>Imprimer</span>
                    </button>
                </div>
            </div>
            <div class="column is-narrow">
                <div class="buttons">
                    <button class="button is-warning">
                        <span class="icon"><i class="fas fa-sync"></i></span>
                        <span>Actualiser</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>


