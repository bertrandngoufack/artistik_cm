<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <h1 class="title">Gestion des Élèves</h1>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <a href="/admin/scolarite/students/create" class="button is-primary">
                    <span class="icon"><i class="fas fa-plus"></i></span>
                    <span>Nouvel Élève</span>
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
            <div class="box has-background-info has-text-white">
                <h4 class="title is-4 has-text-white">Total Élèves</h4>
                <p class="title is-2 has-text-white"><?= $studentStats['total'] ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-success has-text-white">
                <h4 class="title is-4 has-text-white">Élèves Actifs</h4>
                <p class="title is-2 has-text-white"><?= $studentStats['active'] ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-warning has-text-white">
                <h4 class="title is-4 has-text-white">Nouveaux ce Mois</h4>
                <p class="title is-2 has-text-white"><?= $studentStats['new_this_month'] ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-danger has-text-white">
                <h4 class="title is-4 has-text-white">Inactifs</h4>
                <p class="title is-2 has-text-white"><?= $studentStats['inactive'] ?? 0 ?></p>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <header class="card-header">
            <p class="card-header-title">Filtres</p>
        </header>
        <div class="card-content">
            <form method="GET" action="/admin/scolarite/students">
                <div class="columns is-multiline">
                    <div class="column is-3">
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
                    <div class="column is-3">
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
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">Statut</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="status">
                                        <option value="">Tous les statuts</option>
                                        <option value="ACTIVE" <?= $filters['status'] === 'ACTIVE' ? 'selected' : '' ?>>Actif</option>
                                        <option value="INACTIVE" <?= $filters['status'] === 'INACTIVE' ? 'selected' : '' ?>>Inactif</option>
                                        <option value="GRADUATED" <?= $filters['status'] === 'GRADUATED' ? 'selected' : '' ?>>Diplômé</option>
                                        <option value="TRANSFERRED" <?= $filters['status'] === 'TRANSFERRED' ? 'selected' : '' ?>>Transféré</option>
                                        <option value="SUSPENDED" <?= $filters['status'] === 'SUSPENDED' ? 'selected' : '' ?>>Suspendu</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">Recherche</label>
                            <div class="control">
                                <input class="input" type="text" name="search" placeholder="Nom, prénom ou matricule" value="<?= $filters['search'] ?? '' ?>">
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
                        <a href="/admin/scolarite/students" class="button is-light">
                            <span class="icon"><i class="fas fa-times"></i></span>
                            <span>Réinitialiser</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des élèves -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">Liste des Élèves (<?= count($students) ?> résultat<?= count($students) > 1 ? 's' : '' ?>)</p>
        </header>
        <div class="card-content">
            <?php if (!empty($students)): ?>
            <div class="table-container">
                <table class="table is-fullwidth is-striped">
                    <thead>
                        <tr>
                            <th>Matricule</th>
                            <th>Nom et Prénom</th>
                            <th>Classe</th>
                            <th>Genre</th>
                            <th>Date de naissance</th>
                            <th>Parent</th>
                            <th>Téléphone</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <strong><?= $student->matricule ?></strong>
                            </td>
                            <td>
                                <div>
                                    <strong><?= $student->first_name . ' ' . $student->last_name ?></strong>
                                    <?php if ($student->email): ?>
                                        <br><small class="has-text-grey"><?= $student->email ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($student->class_name): ?>
                                    <span class="tag is-info"><?= $student->class_name ?></span>
                                <?php else: ?>
                                    <span class="has-text-grey">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="tag <?= $student->gender === 'M' ? 'is-primary' : 'is-danger' ?>">
                                    <?= $student->gender === 'M' ? 'Masculin' : 'Féminin' ?>
                                </span>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($student->date_of_birth)) ?>
                                <br><small class="has-text-grey">(<?= date_diff(date_create($student->date_of_birth), date_create('today'))->y ?> ans)</small>
                            </td>
                            <td>
                                <div>
                                    <strong><?= $student->parent_name ?></strong>
                                    <?php if ($student->parent_email): ?>
                                        <br><small class="has-text-grey"><?= $student->parent_email ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <a href="tel:<?= $student->parent_phone ?>"><?= $student->parent_phone ?></a>
                            </td>
                            <td>
                                <?php
                                $statusColors = [
                                    'ACTIVE' => 'is-success',
                                    'INACTIVE' => 'is-danger',
                                    'GRADUATED' => 'is-info',
                                    'TRANSFERRED' => 'is-warning',
                                    'SUSPENDED' => 'is-black'
                                ];
                                $statusLabels = [
                                    'ACTIVE' => 'Actif',
                                    'INACTIVE' => 'Inactif',
                                    'GRADUATED' => 'Diplômé',
                                    'TRANSFERRED' => 'Transféré',
                                    'SUSPENDED' => 'Suspendu'
                                ];
                                ?>
                                <span class="tag <?= $statusColors[$student->status] ?? 'is-light' ?>">
                                    <?= $statusLabels[$student->status] ?? $student->status ?>
                                </span>
                            </td>
                            <td>
                                <div class="buttons are-small">
                                    <a href="/admin/scolarite/students/<?= $student->id ?>/view" class="button is-info" title="Voir">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                    <a href="/admin/scolarite/students/<?= $student->id ?>/edit" class="button is-warning" title="Modifier">
                                        <span class="icon"><i class="fas fa-edit"></i></span>
                                    </a>
                                    <a href="/admin/scolarite/students/<?= $student->id ?>/delete" class="button is-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élève ?')">
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
                    <i class="fas fa-users fa-3x has-text-grey-light"></i>
                </span>
                <p class="title is-4 has-text-grey-light mt-4">Aucun élève trouvé</p>
                <p class="subtitle is-6 has-text-grey-light">Aucun élève ne correspond aux critères de recherche</p>
                <a href="/admin/scolarite/students/create" class="button is-primary mt-4">
                    <span class="icon"><i class="fas fa-plus"></i></span>
                    <span>Ajouter un élève</span>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
