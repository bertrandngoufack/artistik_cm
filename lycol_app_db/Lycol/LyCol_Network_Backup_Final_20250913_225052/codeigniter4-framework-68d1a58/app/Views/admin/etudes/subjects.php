<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="section">
        <!-- En-tête -->
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <h1 class="title">Gestion des Matières</h1>
                </div>
            </div>
            <div class="level-right">
                <div class="level-item">
                    <a href="<?= base_url('admin/etudes/subjects/create') ?>" class="button is-primary">
                        <span class="icon">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span>Nouvelle Matière</span>
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
                                    <p class="heading has-text-white">Total Matières</p>
                                    <p class="title has-text-white"><?= $total_subjects ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <span class="icon has-text-white">
                                    <i class="fas fa-book"></i>
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
                                    <p class="heading has-text-white">Matières Actives</p>
                                    <p class="title has-text-white"><?= $active_subjects ?? 0 ?></p>
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
                                    <p class="heading has-text-white">Assignations</p>
                                    <p class="title has-text-white"><?= $total_assignments ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <span class="icon has-text-white">
                                    <i class="fas fa-chalkboard-teacher"></i>
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
                                    <p class="heading has-text-white">Emplois du Temps</p>
                                    <p class="title has-text-white"><?= $total_timetables ?? 0 ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="level-right">
                            <div class="level-item">
                                <span class="icon has-text-white">
                                    <i class="fas fa-calendar-alt"></i>
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
                <div class="column is-4">
                    <div class="field">
                        <label class="label">Recherche</label>
                        <div class="control">
                            <input type="text" class="input" id="search" placeholder="Nom, code...">
                        </div>
                    </div>
                </div>
                <div class="column is-4">
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
                <div class="column is-4">
                    <div class="field">
                        <label class="label">Tri</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="sort_filter">
                                    <option value="name">Nom</option>
                                    <option value="code">Code</option>
                                    <option value="created_at">Date création</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des matières -->
        <div class="box">
            <div class="table-container">
                <table class="table is-fullwidth is-striped is-hoverable">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Assignations</th>
                            <th>Emplois du Temps</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($subjects) && is_array($subjects)): ?>
                            <?php foreach ($subjects as $subject): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($subject['code']) ?></strong>
                                    </td>
                                    <td><?= esc($subject['name']) ?></td>
                                    <td>
                                        <span class="has-text-grey">
                                            <?= esc(substr($subject['description'] ?? '', 0, 50)) ?>
                                            <?= (strlen($subject['description'] ?? '') > 50) ? '...' : '' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="tag is-info">
                                            <?= esc($subject['assignment_count'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="tag is-warning">
                                            <?= esc($subject['timetable_count'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($subject['is_active']): ?>
                                            <span class="tag is-success">Actif</span>
                                        <?php else: ?>
                                            <span class="tag is-danger">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="buttons are-small">
                                            <a href="<?= base_url('admin/etudes/subjects/view/' . $subject['id']) ?>" 
                                               class="button is-info" title="Voir">
                                                <span class="icon">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </a>
                                            <a href="<?= base_url('admin/etudes/assignments?subject_id=' . $subject['id']) ?>" 
                                               class="button is-primary" title="Assignations">
                                                <span class="icon">
                                                    <i class="fas fa-chalkboard-teacher"></i>
                                                </span>
                                            </a>
                                            <a href="<?= base_url('admin/etudes/subjects/edit/' . $subject['id']) ?>" 
                                               class="button is-warning" title="Modifier">
                                                <span class="icon">
                                                    <i class="fas fa-edit"></i>
                                                </span>
                                            </a>
                                            <button class="button is-danger" 
                                                    onclick="deleteSubject(<?= $subject['id'] ?>)" 
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
                                <td colspan="7" class="has-text-centered">
                                    <p class="has-text-grey">Aucune matière trouvée</p>
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
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les filtres avec les valeurs de l'URL
    const urlParams = new URLSearchParams(window.location.search);
    
    // Ajouter des écouteurs d'événements pour la recherche en temps réel
    document.getElementById('search').addEventListener('input', debounce(filterSubjects, 300));
    document.getElementById('status_filter').addEventListener('change', filterSubjects);
    document.getElementById('sort_filter').addEventListener('change', sortSubjects);
    
    // Fonction de debounce pour la recherche
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    function filterSubjects() {
        const search = document.getElementById('search').value.toLowerCase();
        const status = document.getElementById('status_filter').value;
        
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const code = row.cells[0].textContent.toLowerCase();
            const name = row.cells[1].textContent.toLowerCase();
            const statusCell = row.cells[5].textContent;
            
            const matchesSearch = code.includes(search) || name.includes(search);
            const matchesStatus = !status || 
                (status === '1' && statusCell.includes('Actif')) ||
                (status === '0' && statusCell.includes('Inactif'));
            
            row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
        
        // Mettre à jour l'URL avec les paramètres de recherche
        updateURL(search, status);
    }
    
    function updateURL(search, status) {
        const url = new URL(window.location);
        if (search) url.searchParams.set('search', search);
        else url.searchParams.delete('search');
        if (status) url.searchParams.set('status', status);
        else url.searchParams.delete('status');
        
        // Mettre à jour l'URL sans recharger la page
        window.history.replaceState({}, '', url);
    }
    
    function sortSubjects() {
        const sortBy = document.getElementById('sort_filter').value;
        const tbody = document.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            let aValue, bValue;
            
            switch(sortBy) {
                case 'name':
                    aValue = a.cells[1].textContent.toLowerCase();
                    bValue = b.cells[1].textContent.toLowerCase();
                    break;
                case 'code':
                    aValue = a.cells[0].textContent.toLowerCase();
                    bValue = b.cells[0].textContent.toLowerCase();
                    break;
                case 'coefficient':
                    aValue = parseFloat(a.cells[2].textContent) || 0;
                    bValue = parseFloat(b.cells[2].textContent) || 0;
                    break;
                case 'created_at':
                    // Pour l'instant, on trie par nom
                    aValue = a.cells[1].textContent.toLowerCase();
                    bValue = b.cells[1].textContent.toLowerCase();
                    break;
                default:
                    aValue = a.cells[1].textContent.toLowerCase();
                    bValue = b.cells[1].textContent.toLowerCase();
            }
            
            if (typeof aValue === 'string') {
                return aValue.localeCompare(bValue);
            } else {
                return aValue - bValue;
            }
        });
        
        // Réorganiser les lignes
        rows.forEach(row => tbody.appendChild(row));
        
        // Mettre à jour l'URL avec le paramètre de tri
        const url = new URL(window.location);
        url.searchParams.set('sort', sortBy);
        window.history.replaceState({}, '', url);
    }
});

function deleteSubject(subjectId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette matière ?')) {
        // Récupérer le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 'test';
        
        fetch(`<?= base_url('admin/etudes/subjects/delete/') ?>${subjectId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                csrf_test_name: csrfToken
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Afficher un message de succès
                showNotification('Matière supprimée avec succès', 'success');
                // Recharger la page après un délai
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('Erreur lors de la suppression: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur lors de la suppression', 'error');
        });
    }
}

function showNotification(message, type = 'info') {
    // Créer une notification
    const notification = document.createElement('div');
    notification.className = `notification is-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'}`;
    notification.innerHTML = `
        <button class="delete" onclick="this.parentElement.remove()"></button>
        ${message}
    `;
    
    // Ajouter la notification à la page
    document.body.appendChild(notification);
    
    // Supprimer automatiquement après 5 secondes
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}
</script>

<?= $this->endSection() ?>
