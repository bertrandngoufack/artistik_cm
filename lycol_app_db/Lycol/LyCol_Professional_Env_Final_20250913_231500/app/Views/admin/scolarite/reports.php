<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="section">
        <!-- En-tête -->
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <h1 class="title">Rapports Scolarité</h1>
                </div>
            </div>
            <div class="level-right">
                <div class="level-item">
                    <a href="<?= base_url('admin/scolarite/reports/export/csv?report_type=students') ?>" class="button is-success">
                        <span class="icon"><i class="fas fa-download"></i></span>
                        <span>Export CSV - Élèves</span>
                    </a>
                    <a href="<?= base_url('admin/scolarite/reports/export/csv?report_type=absences') ?>" class="button is-warning ml-2">
                        <span class="icon"><i class="fas fa-download"></i></span>
                        <span>Export CSV - Absences</span>
                    </a>
                    <a href="<?= base_url('admin/scolarite/reports/export/csv?report_type=discipline') ?>" class="button is-danger ml-2">
                        <span class="icon"><i class="fas fa-download"></i></span>
                        <span>Export CSV - Discipline</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistiques générales -->
        <div class="columns is-multiline mb-5">
            <div class="column is-3">
                <div class="box has-background-primary has-text-white">
                    <div class="level">
                        <div class="level-left">
                            <div class="level-item">
                                <div>
                                    <p class="heading has-text-white">Total Élèves</p>
                                    <p class="title has-text-white"><?= $studentStats['total_students'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <span class="icon has-text-white">
                                    <i class="fas fa-users"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column is-3">
                <div class="box has-background-info has-text-white">
                    <div class="level">
                        <div class="level-left">
                            <div class="level-item">
                                <div>
                                    <p class="heading has-text-white">Élèves Actifs</p>
                                    <p class="title has-text-white"><?= $studentStats['active_students'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <span class="icon has-text-white">
                                    <i class="fas fa-user-check"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column is-3">
                <div class="box has-background-success has-text-white">
                    <div class="level">
                        <div class="level-item">
                            <div>
                                <p class="heading has-text-white">Nouveaux ce Mois</p>
                                <p class="title has-text-white"><?= $studentStats['new_this_month'] ?? 0 ?></p>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <span class="icon has-text-white">
                                    <i class="fas fa-user-plus"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column is-3">
                <div class="box has-background-warning has-text-white">
                    <div class="level">
                        <div class="level-left">
                            <div class="level-item">
                                <div>
                                    <p class="heading has-text-white">Absences Aujourd'hui</p>
                                    <p class="title has-text-white"><?= $absenceStats['today_absences'] ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <span class="icon has-text-white">
                                    <i class="fas fa-calendar-times"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques détaillées -->
        <div class="columns is-multiline">
            <!-- Statistiques des absences -->
            <div class="column is-6">
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            <span class="icon mr-2">
                                <i class="fas fa-calendar-times"></i>
                            </span>
                            Statistiques des Absences
                        </p>
                    </header>
                    <div class="card-content">
                        <div class="content">
                            <div class="columns is-multiline">
                                <div class="column is-6">
                                    <div class="notification is-info is-light">
                                        <p class="heading">Total Absences</p>
                                        <p class="title"><?= $absenceStats['total_absences'] ?? 0 ?></p>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="notification is-success is-light">
                                        <p class="heading">Justifiées</p>
                                        <p class="title"><?= $absenceStats['justified_absences'] ?? 0 ?></p>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="notification is-warning is-light">
                                        <p class="heading">Non Justifiées</p>
                                        <p class="title"><?= $absenceStats['unjustified_absences'] ?? 0 ?></p>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="notification is-danger is-light">
                                        <p class="heading">Aujourd'hui</p>
                                        <p class="title"><?= $absenceStats['today_absences'] ?? 0 ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques de discipline -->
            <div class="column is-6">
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            <span class="icon mr-2">
                                <i class="fas fa-exclamation-triangle"></i>
                            </span>
                            Statistiques de Discipline
                        </p>
                    </header>
                    <div class="card-content">
                        <div class="content">
                            <div class="columns is-multiline">
                                <div class="column is-6">
                                    <div class="notification is-danger is-light">
                                        <p class="heading">Total Incidents</p>
                                        <p class="title"><?= $disciplineStats['total_incidents'] ?? 0 ?></p>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="notification is-warning is-light">
                                        <p class="heading">Mineurs</p>
                                        <p class="title"><?= $disciplineStats['minor_incidents'] ?? 0 ?></p>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="notification is-danger is-light">
                                        <p class="heading">Majeurs</p>
                                        <p class="title"><?= $disciplineStats['major_incidents'] ?? 0 ?></p>
                                    </div>
                                </div>
                                <div class="column is-6">
                                    <div class="notification is-black is-light">
                                        <p class="heading">Critiques</p>
                                        <p class="title"><?= $disciplineStats['critical_incidents'] ?? 0 ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="card mt-5">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon mr-2">
                        <i class="fas fa-download"></i>
                    </span>
                    Exports Rapides
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <div class="buttons">
                        <a href="<?= base_url('admin/scolarite/reports/export/csv?report_type=students') ?>" class="button is-success">
                            <span class="icon"><i class="fas fa-users"></i></span>
                            <span>Liste des Élèves</span>
                        </a>
                        <a href="<?= base_url('admin/scolarite/reports/export/csv?report_type=absences') ?>" class="button is-warning">
                            <span class="icon"><i class="fas fa-calendar-times"></i></span>
                            <span>Historique des Absences</span>
                        </a>
                        <a href="<?= base_url('admin/scolarite/reports/export/csv?report_type=discipline') ?>" class="button is-danger">
                            <span class="icon"><i class="fas fa-exclamation-triangle"></i></span>
                            <span>Incidents de Discipline</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>



















