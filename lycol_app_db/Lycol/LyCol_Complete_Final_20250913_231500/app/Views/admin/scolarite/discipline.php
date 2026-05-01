<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <h1 class="title">Gestion de la Discipline</h1>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <a href="/admin/scolarite/discipline/create" class="button is-primary">
                    <span class="icon"><i class="fas fa-plus"></i></span>
                    <span>Nouvel Incident</span>
                </a>
                <a href="/admin/scolarite/discipline/notifications" class="button is-info ml-2">
                    <span class="icon"><i class="fas fa-bell"></i></span>
                    <span>Notifications</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Informations de l'année scolaire -->
    <div class="notification is-info is-light">
        <strong>Année Académique <?= $current_academic_year ?></strong> : 
        Du <?= date('d/m/Y', strtotime($academic_year_dates['start_date'])) ?> au <?= date('d/m/Y', strtotime($academic_year_dates['end_date'])) ?>
    </div>

    <!-- Statistiques -->
    <div class="columns is-multiline mb-4">
        <div class="column is-3">
            <div class="box has-background-danger has-text-white">
                <h4 class="title is-4 has-text-white">Total Incidents</h4>
                <p class="title is-2 has-text-white"><?= $incidentStats['total_incidents'] ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-warning has-text-white">
                <h4 class="title is-4 has-text-white">Mineurs</h4>
                <p class="title is-2 has-text-white"><?= $incidentStats['minor_incidents'] ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-danger has-text-white">
                <h4 class="title is-4 has-text-white">Majeurs</h4>
                <p class="title is-2 has-text-white"><?= $incidentStats['major_incidents'] ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-black has-text-white">
                <h4 class="title is-4 has-text-white">Critiques</h4>
                <p class="title is-2 has-text-white"><?= $incidentStats['critical_incidents'] ?? 0 ?></p>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="buttons mb-4">
        <a href="/admin/scolarite/discipline/notifications/send-all" class="button is-warning" onclick="return confirm('Envoyer les notifications pour tous les incidents non notifiés ?')">
            <span class="icon"><i class="fas fa-bell"></i></span>
            <span>Envoyer Notifications (Masse)</span>
        </a>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <header class="card-header">
            <p class="card-header-title">Filtres</p>
        </header>
        <div class="card-content">
            <form method="GET" action="/admin/scolarite/discipline">
                <div class="columns is-multiline">
                    <div class="column is-2">
                        <div class="field">
                            <label class="label">Année Académique</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="academic_year">
                                        <?php foreach ($available_academic_years as $year): ?>
                                            <option value="<?= $year ?>" <?= $year === $filters['academic_year'] ? 'selected' : '' ?>>
                                                <?= $year ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column is-2">
                        <div class="field">
                            <label class="label">Élève</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="student_id">
                                        <option value="">Tous les élèves</option>
                                        <?php foreach ($students as $student): ?>
                                            <option value="<?= $student->id ?>" <?= $student->id == $filters['student_id'] ? 'selected' : '' ?>>
                                                <?= $student->first_name . ' ' . $student->last_name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column is-2">
                        <div class="field">
                            <label class="label">Classe</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="class_id">
                                        <option value="">Toutes les classes</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?= $class->id ?>" <?= $class->id == $filters['class_id'] ? 'selected' : '' ?>>
                                                <?= $class->name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column is-2">
                        <div class="field">
                            <label class="label">Type d'Incident</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="incident_type">
                                        <option value="">Tous les types</option>
                                        <option value="MINOR" <?= $filters['incident_type'] === 'MINOR' ? 'selected' : '' ?>>Mineur</option>
                                        <option value="MAJOR" <?= $filters['incident_type'] === 'MAJOR' ? 'selected' : '' ?>>Majeur</option>
                                        <option value="CRITICAL" <?= $filters['incident_type'] === 'CRITICAL' ? 'selected' : '' ?>>Critique</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column is-2">
                        <div class="field">
                            <label class="label">Date de début</label>
                            <div class="control">
                                <input class="input" type="date" name="date_from" value="<?= $filters['date_from'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    <div class="column is-2">
                        <div class="field">
                            <label class="label">Date de fin</label>
                            <div class="control">
                                <input class="input" type="date" name="date_to" value="<?= $filters['date_to'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field is-grouped">
                    <div class="control">
                        <button type="submit" class="button is-primary">
                            <span class="icon"><i class="fas fa-search"></i></span>
                            <span>Filtrer</span>
                        </button>
                    </div>
                    <div class="control">
                        <a href="/admin/scolarite/discipline" class="button is-light">
                            <span class="icon"><i class="fas fa-times"></i></span>
                            <span>Réinitialiser</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des incidents -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">Liste des Incidents Disciplinaires (<?= count($incidents) ?> résultat<?= count($incidents) > 1 ? 's' : '' ?>)</p>
        </header>
        <div class="card-content">
            <?php if (!empty($incidents)): ?>
            <div class="table-container">
                <table class="table is-fullwidth is-striped">
                    <thead>
                        <tr>
                            <th>Élève</th>
                            <th>Classe</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Lieu</th>
                            <th>Sanction</th>
                            <th>Notification</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($incidents as $incident): ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?= $incident->first_name . ' ' . $incident->last_name ?></strong>
                                    <br><small class="has-text-grey"><?= $incident->matricule ?></small>
                                </div>
                            </td>
                            <td>
                                <?php if ($incident->class_name): ?>
                                    <span class="tag is-info"><?= $incident->class_name ?></span>
                                <?php else: ?>
                                    <span class="has-text-grey">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= date('d/m/Y', strtotime($incident->incident_date)) ?></strong>
                                <?php if ($incident->incident_time): ?>
                                    <br><small class="has-text-grey"><?= $incident->incident_time ?></small>
                                <?php endif; ?>
                            </td>
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
                            <td>
                                <?php if ($incident->location): ?>
                                    <span class="tag is-light"><?= $incident->location ?></span>
                                <?php else: ?>
                                    <span class="has-text-grey">Non spécifié</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="content">
                                    <p class="is-size-7"><?= $incident->sanction ?></p>
                                    <?php if ($incident->sanction_duration): ?>
                                        <small class="has-text-grey"><?= $incident->sanction_duration ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($incident->notification_sent): ?>
                                    <span class="tag is-success">
                                        <span class="icon"><i class="fas fa-check"></i></span>
                                        <span>Envoyée</span>
                                    </span>
                                <?php else: ?>
                                    <span class="tag is-warning">
                                        <span class="icon"><i class="fas fa-clock"></i></span>
                                        <span>En attente</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="/admin/scolarite/discipline/<?= $incident->id ?>/view" class="button is-info" title="Voir">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="/admin/scolarite/discipline/<?= $incident->id ?>/edit" class="button is-warning" title="Modifier">
                                        <span class="icon"><i class="fas fa-edit"></i></span>
                                    </a>
                                    <?php if (!$incident->notification_sent): ?>
                                    <a href="/admin/scolarite/discipline/<?= $incident->id ?>/notify" class="button is-success" title="Envoyer notification" onclick="return confirm('Envoyer la notification aux parents ?')">
                                        <span class="icon"><i class="fas fa-bell"></i></span>
                                    </a>
                                    <?php endif; ?>
                                    <a href="/admin/scolarite/discipline/<?= $incident->id ?>/delete" class="button is-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet incident ?')">
                                        <span class="icon"><i class="fas fa-trash"></i></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="has-text-centered py-6">
                <span class="icon is-large">
                    <i class="fas fa-exclamation-triangle fa-3x has-text-grey-light"></i>
                </span>
                <p class="title is-4 has-text-grey-light mt-4">Aucun incident trouvé</p>
                <p class="subtitle is-6 has-text-grey-light">Aucun incident ne correspond aux critères de recherche</p>
                <a href="/admin/scolarite/discipline/create" class="button is-primary mt-4">
                    <span class="icon"><i class="fas fa-plus"></i></span>
                    <span>Enregistrer un incident</span>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
