<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <h1 class="title">Gestion des Absences</h1>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <a href="/admin/scolarite/absences/create" class="button is-primary">
                    <span class="icon"><i class="fas fa-plus"></i></span>
                    <span>Nouvelle Absence</span>
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
            <div class="box has-background-warning has-text-white">
                <h4 class="title is-4 has-text-white">Total Absences</h4>
                <p class="title is-2 has-text-white"><?= $absenceStats['total_absences'] ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-success has-text-white">
                <h4 class="title is-4 has-text-white">Justifiées</h4>
                <p class="title is-2 has-text-white"><?= $absenceStats['justified_absences'] ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-danger has-text-white">
                <h4 class="title is-4 has-text-white">Non Justifiées</h4>
                <p class="title is-2 has-text-white"><?= $absenceStats['unjustified_absences'] ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-info has-text-white">
                <h4 class="title is-4 has-text-white">Taux de Présence</h4>
                <p class="title is-2 has-text-white"><?= number_format($stats['attendance_rate'] ?? 95, 1) ?>%</p>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <header class="card-header">
            <p class="card-header-title">Filtres</p>
        </header>
        <div class="card-content">
            <form method="GET" action="/admin/scolarite/absences">
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
                            <label class="label">Justifiée</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="justified">
                                        <option value="">Toutes</option>
                                        <option value="1" <?= $filters['justified'] === '1' ? 'selected' : '' ?>>Oui</option>
                                        <option value="0" <?= $filters['justified'] === '0' ? 'selected' : '' ?>>Non</option>
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
                        <a href="/admin/scolarite/absences" class="button is-light">
                            <span class="icon"><i class="fas fa-times"></i></span>
                            <span>Réinitialiser</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des absences -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">Liste des Absences (<?= count($absences) ?> résultat<?= count($absences) > 1 ? 's' : '' ?>)</p>
        </header>
        <div class="card-content">
            <?php if (!empty($absences)): ?>
            <div class="table-container">
                <table class="table is-fullwidth is-striped">
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
                        <?php foreach ($absences as $absence): ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?= $absence->first_name . ' ' . $absence->last_name ?></strong>
                                    <br><small class="has-text-grey"><?= $absence->matricule ?></small>
                                </div>
                            </td>
                            <td>
                                <?php if ($absence->class_name): ?>
                                    <span class="tag is-info"><?= $absence->class_name ?></span>
                                <?php else: ?>
                                    <span class="has-text-grey">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                                        <strong><?= date('d/m/Y', strtotime($absence->date)) ?></strong>
                        <br><small class="has-text-grey"><?= date('l', strtotime($absence->date)) ?></small>
                            </td>

                            <td>
                                <div class="content">
                                    <p class="is-size-7"><?= $absence->reason ?></p>
                                </div>
                            </td>
                            <td>
                                <?php if ($absence->justified): ?>
                                    <span class="tag is-success">
                                        <span class="icon"><i class="fas fa-check"></i></span>
                                        <span>Oui</span>
                                    </span>
                                <?php else: ?>
                                    <span class="tag is-danger">
                                        <span class="icon"><i class="fas fa-times"></i></span>
                                        <span>Non</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="/admin/scolarite/absences/<?= $absence->id ?>/view" class="button is-info" title="Voir">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="/admin/scolarite/absences/<?= $absence->id ?>/edit" class="button is-warning" title="Modifier">
                                        <span class="icon"><i class="fas fa-edit"></i></span>
                                    </a>
                                    <a href="/admin/scolarite/absences/<?= $absence->id ?>/delete" class="button is-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette absence ?')">
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
                    <i class="fas fa-calendar-times fa-3x has-text-grey-light"></i>
                </span>
                <p class="title is-4 has-text-grey-light mt-4">Aucune absence trouvée</p>
                <p class="subtitle is-6 has-text-grey-light">Aucune absence ne correspond aux critères de recherche</p>
                <a href="/admin/scolarite/absences/create" class="button is-primary mt-4">
                    <span class="icon"><i class="fas fa-plus"></i></span>
                    <span>Enregistrer une absence</span>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
