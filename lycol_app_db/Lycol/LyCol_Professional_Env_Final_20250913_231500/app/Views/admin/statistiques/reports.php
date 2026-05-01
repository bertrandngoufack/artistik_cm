<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li><a href="<?= base_url('admin/statistiques') ?>">Statistiques</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Rapports Statistiques</a></li>
                </ul>
            </nav>
            
            <h1 class="title has-text-primary">
                <i class="fas fa-file-alt"></i>
                📋 Rapports Statistiques
            </h1>
            <p class="subtitle">Génération et consultation des rapports détaillés</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="notification is-success">
            <button class="delete" onclick="this.parentElement.remove()"></button>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="notification is-danger">
            <button class="delete" onclick="this.parentElement.remove()"></button>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Rapports disponibles -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <i class="fas fa-list"></i>
                Rapports Disponibles
            </p>
        </header>
        <div class="card-content">
            <div class="columns">
                <div class="column is-3">
                    <div class="box has-text-centered">
                        <span class="icon is-large has-text-info">
                            <i class="fas fa-users fa-2x"></i>
                        </span>
                        <h3 class="title is-4">Rapport des Élèves</h3>
                        <p class="subtitle is-6">Statistiques détaillées des élèves</p>
                        <div class="buttons">
                            <a href="<?= base_url('admin/statistiques/students') ?>" class="button is-info is-fullwidth">
                                <span class="icon">
                                    <i class="fas fa-eye"></i>
                                </span>
                                <span>Consulter</span>
                            </a>
                            <a href="<?= base_url('admin/statistiques/export/students') ?>" class="button is-outlined is-info is-fullwidth">
                                <span class="icon">
                                    <i class="fas fa-download"></i>
                                </span>
                                <span>Exporter</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="column is-3">
                    <div class="box has-text-centered">
                        <span class="icon is-large has-text-success">
                            <i class="fas fa-graduation-cap fa-2x"></i>
                        </span>
                        <h3 class="title is-4">Rapport des Notes</h3>
                        <p class="subtitle is-6">Performance académique détaillée</p>
                        <div class="buttons">
                            <a href="<?= base_url('admin/statistiques/grades') ?>" class="button is-success is-fullwidth">
                                <span class="icon">
                                    <i class="fas fa-eye"></i>
                                </span>
                                <span>Consulter</span>
                            </a>
                            <a href="<?= base_url('admin/statistiques/export/grades') ?>" class="button is-outlined is-success is-fullwidth">
                                <span class="icon">
                                    <i class="fas fa-download"></i>
                                </span>
                                <span>Exporter</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="column is-3">
                    <div class="box has-text-centered">
                        <span class="icon is-large has-text-warning">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </span>
                        <h3 class="title is-4">Rapport des Paiements</h3>
                        <p class="subtitle is-6">Statistiques financières</p>
                        <div class="buttons">
                            <a href="<?= base_url('admin/statistiques/payments') ?>" class="button is-warning is-fullwidth">
                                <span class="icon">
                                    <i class="fas fa-eye"></i>
                                </span>
                                <span>Consulter</span>
                            </a>
                            <a href="<?= base_url('admin/statistiques/export/payments') ?>" class="button is-outlined is-warning is-fullwidth">
                                <span class="icon">
                                    <i class="fas fa-download"></i>
                                </span>
                                <span>Exporter</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="column is-3">
                    <div class="box has-text-centered">
                        <span class="icon is-large has-text-danger">
                            <i class="fas fa-calendar-times fa-2x"></i>
                        </span>
                        <h3 class="title is-4">Rapport des Absences</h3>
                        <p class="subtitle is-6">Suivi de la présence</p>
                        <div class="buttons">
                            <a href="<?= base_url('admin/statistiques/absences') ?>" class="button is-danger is-fullwidth">
                                <span class="icon">
                                    <i class="fas fa-eye"></i>
                                </span>
                                <span>Consulter</span>
                            </a>
                            <a href="<?= base_url('admin/statistiques/export/absences') ?>" class="button is-outlined is-danger is-fullwidth">
                                <span class="icon">
                                    <i class="fas fa-download"></i>
                                </span>
                                <span>Exporter</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rapports spécialisés -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <i class="fas fa-chart-line"></i>
                Rapports Spécialisés
            </p>
        </header>
        <div class="card-content">
            <div class="columns">
                <div class="column is-4">
                    <div class="box has-text-centered">
                        <span class="icon is-large has-text-primary">
                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                        </span>
                        <h3 class="title is-4">Statistiques des Enseignants</h3>
                        <p class="subtitle is-6">Performance et répartition</p>
                        <a href="<?= base_url('admin/statistiques/teachers') ?>" class="button is-primary is-fullwidth">
                            <span class="icon">
                                <i class="fas fa-chart-bar"></i>
                            </span>
                            <span>Consulter</span>
                        </a>
                    </div>
                </div>
                
                <div class="column is-4">
                    <div class="box has-text-centered">
                        <span class="icon is-large has-text-info">
                            <i class="fas fa-book fa-2x"></i>
                        </span>
                        <h3 class="title is-4">Statistiques Académiques</h3>
                        <p class="subtitle is-6">Classes, matières et examens</p>
                        <a href="<?= base_url('admin/statistiques/academic') ?>" class="button is-info is-fullwidth">
                            <span class="icon">
                                <i class="fas fa-chart-pie"></i>
                            </span>
                            <span>Consulter</span>
                        </a>
                    </div>
                </div>
                
                <div class="column is-4">
                    <div class="box has-text-centered">
                        <span class="icon is-large has-text-success">
                            <i class="fas fa-chart-area fa-2x"></i>
                        </span>
                        <h3 class="title is-4">Statistiques de Présence</h3>
                        <p class="subtitle is-6">Tendances et analyses</p>
                        <a href="<?= base_url('admin/statistiques/attendance') ?>" class="button is-success is-fullwidth">
                            <span class="icon">
                                <i class="fas fa-chart-line"></i>
                            </span>
                            <span>Consulter</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Génération de rapports personnalisés -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <i class="fas fa-cogs"></i>
                Génération de Rapports Personnalisés
            </p>
        </header>
        <div class="card-content">
            <form method="POST" action="<?= base_url('admin/statistiques/generate-custom-report') ?>">
                <?= csrf_field() ?>
                <div class="columns">
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">Type de Rapport</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="report_type" required>
                                        <option value="">Sélectionner un type</option>
                                        <option value="students">Élèves</option>
                                        <option value="grades">Notes</option>
                                        <option value="payments">Paiements</option>
                                        <option value="absences">Absences</option>
                                        <option value="teachers">Enseignants</option>
                                        <option value="academic">Académique</option>
                                        <option value="financial">Financier</option>
                                        <option value="attendance">Présence</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">Format d'Export</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="export_format" required>
                                        <option value="csv">CSV</option>
                                        <option value="pdf">PDF</option>
                                        <option value="excel">Excel</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">Période</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="period">
                                        <option value="current_month">Mois en cours</option>
                                        <option value="current_year">Année en cours</option>
                                        <option value="last_month">Mois dernier</option>
                                        <option value="last_year">Année dernière</option>
                                        <option value="custom">Personnalisé</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">&nbsp;</label>
                            <div class="control">
                                <button type="submit" class="button is-primary is-fullwidth">
                                    <span class="icon">
                                        <i class="fas fa-file-export"></i>
                                    </span>
                                    <span>Générer</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Historique des rapports -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <i class="fas fa-history"></i>
                Historique des Rapports Générés
            </p>
        </header>
        <div class="card-content">
            <div class="table-container">
                <table class="table is-fullwidth is-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Format</th>
                            <th>Généré par</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="has-text-centered has-text-grey">
                                Aucun rapport généré récemment
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>








