<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <h1 class="title">Module Scolarité</h1>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <div class="field">
                    <label class="label">Année Académique</label>
                    <div class="control">
                        <div class="select">
                            <select id="academic-year-selector" onchange="changeAcademicYear()">
                                <?php foreach ($available_academic_years as $year): ?>
                                    <option value="<?= $year ?>" <?= $year === $current_academic_year ? 'selected' : '' ?>>
                                        <?= $year ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations de l'année scolaire -->
    <div class="notification is-info is-light">
        <strong>Année Académique <?= $current_academic_year ?></strong> : 
        Du <?= date('d/m/Y', strtotime($academic_year_dates['start_date'])) ?> au <?= date('d/m/Y', strtotime($academic_year_dates['end_date'])) ?>
    </div>
    
    <!-- Statistiques générales -->
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="box has-background-info has-text-white">
                <h4 class="title is-4 has-text-white">Total Élèves</h4>
                <p class="title is-2 has-text-white"><?= $stats['total_students'] ?? 0 ?></p>
                <p class="subtitle is-6 has-text-white"><?= $stats['active_students'] ?? 0 ?> actifs</p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-warning has-text-white">
                <h4 class="title is-4 has-text-white">Absences Aujourd'hui</h4>
                <p class="title is-2 has-text-white"><?= $stats['today_absences'] ?? 0 ?></p>
                <p class="subtitle is-6 has-text-white"><?= $stats['total_absences'] ?? 0 ?> total</p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-danger has-text-white">
                <h4 class="title is-4 has-text-white">Incidents Disciplinaires</h4>
                <p class="title is-2 has-text-white"><?= $stats['today_incidents'] ?? 0 ?></p>
                <p class="subtitle is-6 has-text-white"><?= $stats['total_incidents'] ?? 0 ?> total</p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-success has-text-white">
                <h4 class="title is-4 has-text-white">Taux de Présence</h4>
                <p class="title is-2 has-text-white"><?= number_format($stats['attendance_rate'] ?? 95, 1) ?>%</p>
                <p class="subtitle is-6 has-text-white">Cette année</p>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="buttons mb-4">
        <a href="/admin/scolarite/students" class="button is-primary">
            <span class="icon"><i class="fas fa-users"></i></span>
            <span>Gestion des Élèves</span>
        </a>
        <a href="/admin/scolarite/absences" class="button is-warning">
            <span class="icon"><i class="fas fa-calendar-times"></i></span>
            <span>Absences</span>
        </a>
        <a href="/admin/scolarite/discipline" class="button is-danger">
            <span class="icon"><i class="fas fa-exclamation-triangle"></i></span>
            <span>Discipline</span>
        </a>
        <a href="/admin/scolarite/discipline/notifications" class="button is-info">
            <span class="icon"><i class="fas fa-bell"></i></span>
            <span>Notifications Discipline</span>
        </a>
        <a href="/admin/scolarite/reports" class="button is-success">
            <span class="icon"><i class="fas fa-file-alt"></i></span>
            <span>Rapports</span>
        </a>
    </div>

    <!-- Derniers élèves inscrits -->
    <div class="card mb-4">
        <header class="card-header">
            <p class="card-header-title">Derniers Élèves Inscrits</p>
            <a href="/admin/scolarite/students" class="card-header-icon">
                <span class="icon"><i class="fas fa-arrow-right"></i></span>
            </a>
        </header>
        <div class="card-content">
            <table class="table is-fullwidth">
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom</th>
                        <th>Classe</th>
                        <th>Date d'inscription</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($recentStudents) && !empty($recentStudents)): ?>
                        <?php foreach ($recentStudents as $student): ?>
                        <tr>
                            <td><?= $student->matricule ?></td>
                            <td><?= $student->first_name . ' ' . $student->last_name ?></td>
                            <td><?= $student->class_name ?? 'N/A' ?></td>
                            <td><?= date('d/m/Y', strtotime($student->created_at)) ?></td>
                            <td>
                                <span class="tag <?= $student->status === 'ACTIVE' ? 'is-success' : 'is-danger' ?>">
                                    <?= $student->status === 'ACTIVE' ? 'Actif' : 'Inactif' ?>
                                </span>
                            </td>
                            <td>
                                <a href="/admin/scolarite/students/<?= $student->id ?>/view" class="button is-small is-info">
                                    <span class="icon"><i class="fas fa-eye"></i></span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="has-text-centered">Aucun élève récent</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Dernières absences -->
    <div class="card mb-4">
        <header class="card-header">
            <p class="card-header-title">Dernières Absences</p>
            <a href="/admin/scolarite/absences" class="card-header-icon">
                <span class="icon"><i class="fas fa-arrow-right"></i></span>
            </a>
        </header>
        <div class="card-content">
            <table class="table is-fullwidth">
                <thead>
                    <tr>
                        <th>Élève</th>
                        <th>Classe</th>
                        <th>Date</th>
                        <th>Motif</th>
                        <th>Justifiée</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($recentAbsences) && !empty($recentAbsences)): ?>
                        <?php foreach ($recentAbsences as $absence): ?>
                        <tr>
                            <td><?= $absence->first_name . ' ' . $absence->last_name ?></td>
                            <td><?= $absence->class_name ?? 'N/A' ?></td>
                            <td><?= date('d/m/Y', strtotime($absence->date)) ?></td>
                            <td><?= $absence->reason ?></td>
                            <td>
                                <span class="tag <?= $absence->justified ? 'is-success' : 'is-danger' ?>">
                                    <?= $absence->justified ? 'Oui' : 'Non' ?>
                                </span>
                            </td>
                            <td>
                                <a href="/admin/scolarite/absences/<?= $absence->id ?>/view" class="button is-small is-info">
                                    <span class="icon"><i class="fas fa-eye"></i></span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="has-text-centered">Aucune absence récente</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Derniers incidents disciplinaires -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">Derniers Incidents Disciplinaires</p>
            <a href="/admin/scolarite/discipline" class="card-header-icon">
                <span class="icon"><i class="fas fa-arrow-right"></i></span>
            </a>
        </header>
        <div class="card-content">
            <table class="table is-fullwidth">
                <thead>
                    <tr>
                        <th>Élève</th>
                        <th>Classe</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Sanction</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($recentIncidents) && !empty($recentIncidents)): ?>
                        <?php foreach ($recentIncidents as $incident): ?>
                        <tr>
                            <td><?= $incident->first_name . ' ' . $incident->last_name ?></td>
                            <td><?= $incident->class_name ?? 'N/A' ?></td>
                            <td><?= date('d/m/Y', strtotime($incident->incident_date)) ?></td>
                            <td>
                                <?php
                                $typeColors = [
                                    'MINOR' => 'is-warning',
                                    'MAJOR' => 'is-danger',
                                    'CRITICAL' => 'is-black'
                                ];
                                $typeLabels = [
                                    'MINOR' => 'Mineur',
                                    'MAJOR' => 'Majeur',
                                    'CRITICAL' => 'Critique'
                                ];
                                ?>
                                <span class="tag <?= $typeColors[$incident->incident_type] ?? 'is-light' ?>">
                                    <?= $typeLabels[$incident->incident_type] ?? $incident->incident_type ?>
                                </span>
                            </td>
                            <td><?= $incident->sanction ?></td>
                            <td>
                                <a href="/admin/scolarite/discipline/<?= $incident->id ?>/view" class="button is-small is-info">
                                    <span class="icon"><i class="fas fa-eye"></i></span>
                                </a>
                                <?php if (!$incident->notification_sent): ?>
                                <a href="/admin/scolarite/discipline/<?= $incident->id ?>/notify" class="button is-small is-warning">
                                    <span class="icon"><i class="fas fa-bell"></i></span>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="has-text-centered">Aucun incident récent</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function changeAcademicYear() {
    const academicYear = document.getElementById('academic-year-selector').value;
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('academic_year', academicYear);
    window.location.href = currentUrl.toString();
}
</script>
<?= $this->endSection() ?>
