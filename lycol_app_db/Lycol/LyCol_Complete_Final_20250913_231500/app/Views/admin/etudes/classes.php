<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="section">
        <!-- En-tête -->
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <h1 class="title">Gestion des Classes</h1>
                </div>
            </div>
            <div class="level-right">
                <div class="level-item">
                    <a href="<?= base_url('admin/etudes/classes/create') ?>" class="button is-primary">
                        <span class="icon">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span>Nouvelle Classe</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="columns is-multiline mb-5">
            <div class="column is-3">
                <div class="box has-background-primary has-text-white">
                    <div class="level">
                        <div class="level-left">
                            <div class="level-item">
                                <div>
                                    <p class="heading has-text-white">Total Classes</p>
                                    <p class="title has-text-white"><?= $total_classes ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <span class="icon has-text-white">
                                    <i class="fas fa-chalkboard"></i>
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
                                    <p class="heading has-text-white">Classes Actives</p>
                                    <p class="title has-text-white"><?= $active_classes ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <span class="icon has-text-white">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column is-3">
                <div class="box has-background-success has-text-white">
                    <div class="level">
                        <div class="level-left">
                            <div class="level-item">
                                <div>
                                    <p class="heading has-text-white">Total Élèves</p>
                                    <p class="title has-text-white"><?= $total_students ?? 0 ?></p>
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
                <div class="box has-background-warning has-text-white">
                    <div class="level">
                        <div class="level-left">
                            <div class="level-item">
                                <div>
                                    <p class="heading has-text-white">Cycles</p>
                                    <p class="title has-text-white"><?= $total_cycles ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <span class="icon has-text-white">
                                    <i class="fas fa-layer-group"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="box">
            <div class="columns is-multiline">
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Recherche</label>
                        <div class="control">
                            <input type="text" class="input" id="search" placeholder="Nom, code...">
                        </div>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Cycle</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="cycle_filter">
                                    <option value="">Tous les cycles</option>
                                    <?php if (isset($cycles) && is_array($cycles)): ?>
                                        <?php foreach ($cycles as $cycle): ?>
                                            <option value="<?= $cycle['id'] ?>"><?= esc($cycle['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="field">
                        <label class="label">Niveau</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="level_filter">
                                    <option value="">Tous les niveaux</option>
                                    <option value="1">1ère année</option>
                                    <option value="2">2ème année</option>
                                    <option value="3">3ème année</option>
                                    <option value="4">4ème année</option>
                                    <option value="5">5ème année</option>
                                    <option value="6">6ème année</option>
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
                                <select id="status_filter">
                                    <option value="">Tous les statuts</option>
                                    <option value="1">Actif</option>
                                    <option value="0">Inactif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des classes -->
        <div class="box">
            <div class="table-container">
                <table class="table is-fullwidth is-striped is-hoverable">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Cycle</th>
                            <th>Niveau</th>
                            <th>Capacité</th>
                            <th>Élèves</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($classes) && is_array($classes)): ?>
                            <?php foreach ($classes as $class): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($class['code']) ?></strong>
                                    </td>
                                    <td><?= esc($class['name']) ?></td>
                                    <td>
                                        <span class="tag is-info">
                                            <?= esc($class['cycle_name'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td><?= esc($class['level']) ?>ème année</td>
                                    <td>
                                        <span class="tag is-light">
                                            <?= esc($class['capacity']) ?> places
                                        </span>
                                    </td>
                                    <td>
                                        <span class="tag <?= (($class['student_count'] ?? 0) >= $class['capacity']) ? 'is-danger' : 'is-success' ?>">
                                            <?= esc($class['student_count'] ?? 0) ?>/<?= esc($class['capacity']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($class['is_active']): ?>
                                            <span class="tag is-success">Actif</span>
                                        <?php else: ?>
                                            <span class="tag is-danger">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="buttons are-small">
                                            <a href="<?= base_url('admin/etudes/classes/view/' . $class['id']) ?>" 
                                               class="button is-info" title="Voir">
                                                <span class="icon">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </a>
                                            <a href="<?= base_url('admin/etudes/assignments?class_id=' . $class['id']) ?>" 
                                               class="button is-primary" title="Assignations">
                                                <span class="icon">
                                                    <i class="fas fa-chalkboard-teacher"></i>
                                                </span>
                                            </a>
                                            <a href="<?= base_url('admin/etudes/classes/edit/' . $class['id']) ?>" 
                                               class="button is-warning" title="Modifier">
                                                <span class="icon">
                                                    <i class="fas fa-edit"></i>
                                                </span>
                                            </a>
                                            <button class="button is-danger" 
                                                    onclick="deleteClass(<?= $class['id'] ?>)" 
                                                    title="Supprimer">
                                                <span class="icon">
                                                    <i class="fas fa-trash"></i>
                                                </span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="has-text-centered">
                                    <p class="has-text-grey">Aucune classe trouvée</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Filtres
document.getElementById('search').addEventListener('input', filterClasses);
document.getElementById('cycle_filter').addEventListener('change', filterClasses);
document.getElementById('level_filter').addEventListener('change', filterClasses);
document.getElementById('status_filter').addEventListener('change', filterClasses);

function filterClasses() {
    const search = document.getElementById('search').value.toLowerCase();
    const cycle = document.getElementById('cycle_filter').value;
    const level = document.getElementById('level_filter').value;
    const status = document.getElementById('status_filter').value;
    
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const code = row.cells[0].textContent.toLowerCase();
        const name = row.cells[1].textContent.toLowerCase();
        const cycleCell = row.cells[2].textContent;
        const levelCell = row.cells[3].textContent;
        const statusCell = row.cells[6].textContent;
        
        const matchesSearch = code.includes(search) || name.includes(search);
        const matchesCycle = !cycle || cycleCell.includes(cycle);
        const matchesLevel = !level || levelCell.includes(level + 'ème');
        const matchesStatus = !status || 
            (status === '1' && statusCell.includes('Actif')) ||
            (status === '0' && statusCell.includes('Inactif'));
        
        row.style.display = (matchesSearch && matchesCycle && matchesLevel && matchesStatus) ? '' : 'none';
    });
}

function deleteClass(classId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette classe ?')) {
        fetch(`<?= base_url('admin/etudes/classes/delete/') ?>${classId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la suppression: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la suppression');
        });
    }
}
</script>

<?= $this->endSection() ?>
