<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <span class="icon"><i class="fas fa-chart-bar"></i></span>
                Rapports Financiers
            </h1>
            <p class="subtitle">Analysez les performances financières de l'établissement</p>
        </div>
        <div class="column is-narrow">
            <div class="buttons">
                <button class="button is-success">
                    <span class="icon"><i class="fas fa-download"></i></span>
                    <span>Exporter PDF</span>
                </button>
                <button class="button is-info">
                    <span class="icon"><i class="fas fa-print"></i></span>
                    <span>Imprimer</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Filtres de période -->
    <div class="box">
        <div class="columns">
            <div class="column">
                <div class="field">
                    <label class="label">Période</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select>
                                <option>Année scolaire <?= $current_academic_year ?? '2024-2025' ?></option>
                                <option>Année scolaire 2023-2024</option>
                                <option>Trimestre 1</option>
                                <option>Trimestre 2</option>
                                <option>Trimestre 3</option>
                                <option>Période personnalisée</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="label">Type de rapport</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select>
                                <option>Rapport général</option>
                                <option>Rapport par classe</option>
                                <option>Rapport par type de frais</option>
                                <option>Rapport de recouvrement</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column is-narrow">
                <div class="field">
                    <label class="label">&nbsp;</label>
                    <div class="control">
                        <button class="button is-primary">
                            <span class="icon"><i class="fas fa-search"></i></span>
                            <span>Générer</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Résumé financier -->
    <div class="columns">
        <div class="column">
            <div class="box has-text-centered">
                <p class="heading">Total Recettes</p>
                <p class="title has-text-success">38,898,767 FCFA</p>
                <p class="subtitle is-6">+12.5% vs période précédente</p>
            </div>
        </div>
        <div class="column">
            <div class="box has-text-centered">
                <p class="heading">Total Dépenses</p>
                <p class="title has-text-danger">25,450,000 FCFA</p>
                <p class="subtitle is-6">+8.2% vs période précédente</p>
            </div>
        </div>
        <div class="column">
            <div class="box has-text-centered">
                <p class="heading">Bénéfice Net</p>
                <p class="title has-text-info">13,448,767 FCFA</p>
                <p class="subtitle is-6">+18.7% vs période précédente</p>
            </div>
        </div>
        <div class="column">
            <div class="box has-text-centered">
                <p class="heading">Taux de Recouvrement</p>
                <p class="title has-text-warning">89.2%</p>
                <p class="subtitle is-6">+2.1% vs période précédente</p>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="columns">
        <div class="column">
            <div class="box">
                <h3 class="title is-4">Évolution des Recettes</h3>
                <div class="notification is-info">
                    <div class="columns">
                        <div class="column">
                            <p><strong>Septembre</strong></p>
                            <p class="title is-5">8,450,000 FCFA</p>
                        </div>
                        <div class="column">
                            <p><strong>Octobre</strong></p>
                            <p class="title is-5">9,200,000 FCFA</p>
                        </div>
                        <div class="column">
                            <p><strong>Novembre</strong></p>
                            <p class="title is-5">10,150,000 FCFA</p>
                        </div>
                        <div class="column">
                            <p><strong>Décembre</strong></p>
                            <p class="title is-5">11,098,767 FCFA</p>
                        </div>
                    </div>
                    <progress class="progress is-info" value="75" max="100">75%</progress>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="box">
                <h3 class="title is-4">Répartition par Type de Frais</h3>
                <div class="content">
                    <div class="columns">
                        <div class="column">
                            <p><strong>Frais de scolarité</strong></p>
                            <p class="title is-6">21,600,000 FCFA (55.5%)</p>
                            <progress class="progress is-primary" value="55.5" max="100">55.5%</progress>
                        </div>
                    </div>
                    <div class="columns">
                        <div class="column">
                            <p><strong>Frais d'inscription</strong></p>
                            <p class="title is-6">7,200,000 FCFA (18.5%)</p>
                            <progress class="progress is-success" value="18.5" max="100">18.5%</progress>
                        </div>
                    </div>
                    <div class="columns">
                        <div class="column">
                            <p><strong>Frais de cantine</strong></p>
                            <p class="title is-6">6,000,000 FCFA (15.4%)</p>
                            <progress class="progress is-warning" value="15.4" max="100">15.4%</progress>
                        </div>
                    </div>
                    <div class="columns">
                        <div class="column">
                            <p><strong>Autres frais</strong></p>
                            <p class="title is-6">4,098,767 FCFA (10.6%)</p>
                            <progress class="progress is-danger" value="10.6" max="100">10.6%</progress>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau détaillé -->
    <div class="box">
        <h3 class="title is-4">Détail des Recettes par Classe</h3>
        <div class="table-container">
            <table class="table is-fullwidth is-striped is-hoverable">
                <thead>
                    <tr>
                        <th>Classe</th>
                        <th>Effectif</th>
                        <th>Recettes Attendu</th>
                        <th>Recettes Perçues</th>
                        <th>Taux de Recouvrement</th>
                        <th>Reste à Payer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>CP A</strong></td>
                        <td>30</td>
                        <td>4,500,000 FCFA</td>
                        <td>4,050,000 FCFA</td>
                        <td><span class="tag is-success">90.0%</span></td>
                        <td>450,000 FCFA</td>
                        <td>
                            <a href="/admin/economat/reports/class/1" class="button is-small is-info">
                                <span class="icon"><i class="fas fa-eye"></i></span>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>CE1 A</strong></td>
                        <td>32</td>
                        <td>4,800,000 FCFA</td>
                        <td>4,320,000 FCFA</td>
                        <td><span class="tag is-success">90.0%</span></td>
                        <td>480,000 FCFA</td>
                        <td>
                            <a href="/admin/economat/reports/class/2" class="button is-small is-info">
                                <span class="icon"><i class="fas fa-eye"></i></span>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>CM1 A</strong></td>
                        <td>31</td>
                        <td>4,650,000 FCFA</td>
                        <td>4,185,000 FCFA</td>
                        <td><span class="tag is-success">90.0%</span></td>
                        <td>465,000 FCFA</td>
                        <td>
                            <a href="/admin/economat/reports/class/3" class="button is-small is-info">
                                <span class="icon"><i class="fas fa-eye"></i></span>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>6ème A</strong></td>
                        <td>35</td>
                        <td>5,250,000 FCFA</td>
                        <td>4,725,000 FCFA</td>
                        <td><span class="tag is-success">90.0%</span></td>
                        <td>525,000 FCFA</td>
                        <td>
                            <a href="/admin/economat/reports/class/4" class="button is-small is-info">
                                <span class="icon"><i class="fas fa-eye"></i></span>
                            </a>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="has-background-light">
                        <td><strong>TOTAL</strong></td>
                        <td><strong>128</strong></td>
                        <td><strong>19,200,000 FCFA</strong></td>
                        <td><strong>17,280,000 FCFA</strong></td>
                        <td><span class="tag is-success"><strong>90.0%</strong></span></td>
                        <td><strong>1,920,000 FCFA</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Alertes et recommandations -->
    <div class="box">
        <h3 class="title is-4">Alertes et Recommandations</h3>
        <div class="columns">
            <div class="column">
                <div class="notification is-warning">
                    <h4 class="title is-5">⚠️ Paiements en retard</h4>
                    <p>120 paiements sont en retard de plus de 30 jours pour un montant total de 1,920,000 FCFA.</p>
                    <button class="button is-warning is-small">
                        <span class="icon"><i class="fas fa-envelope"></i></span>
                        <span>Envoyer des rappels</span>
                    </button>
                </div>
            </div>
            <div class="column">
                <div class="notification is-info">
                    <h4 class="title is-5">📈 Performance positive</h4>
                    <p>Le taux de recouvrement a augmenté de 2.1% par rapport à la période précédente.</p>
                    <button class="button is-info is-small">
                        <span class="icon"><i class="fas fa-chart-line"></i></span>
                        <span>Voir les détails</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="box">
        <div class="columns">
            <div class="column">
                <div class="buttons">
                    <button class="button is-success">
                        <span class="icon"><i class="fas fa-file-pdf"></i></span>
                        <span>Exporter en PDF</span>
                    </button>
                    <button class="button is-info">
                        <span class="icon"><i class="fas fa-file-excel"></i></span>
                        <span>Exporter en Excel</span>
                    </button>
                    <button class="button is-warning">
                        <span class="icon"><i class="fas fa-envelope"></i></span>
                        <span>Envoyer par email</span>
                    </button>
                </div>
            </div>
            <div class="column is-narrow">
                <div class="buttons">
                    <button class="button is-primary">
                        <span class="icon"><i class="fas fa-sync"></i></span>
                        <span>Actualiser</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>


