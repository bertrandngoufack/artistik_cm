<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/bibliotheque') ?>">Bibliothèque</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Gestion des Membres</a></li>
                </ul>
            </nav>
            
            <h1 class="title">
                <i class="fas fa-users"></i>
                Gestion des Membres
            </h1>
            <p class="subtitle">Gérer les membres de la bibliothèque</p>
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

    <!-- Statistiques des Membres -->
    <div class="columns">
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Total Membres</p>
                                <p class="title has-text-primary"><?= $stats['totalMembers'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Membres Actifs</p>
                                <p class="title has-text-success"><?= $stats['totalMembers'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Nouveaux ce Mois</p>
                                <p class="title has-text-info"><?= $stats['totalStudents'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="card">
                <div class="card-content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Emprunts Actifs</p>
                                <p class="title has-text-warning"><?= $stats['activeLoans'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et Recherche -->
    <div class="card">
        <div class="card-content">
            <form method="GET" action="<?= base_url('admin/bibliotheque/members') ?>">
                <div class="columns">
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">Recherche</label>
                            <div class="control">
                                <input class="input" 
                                       type="text" 
                                       name="search" 
                                       value="<?= $search ?? '' ?>" 
                                       placeholder="Nom, email, téléphone...">
                            </div>
                        </div>
                    </div>
                    <div class="column is-2">
                        <div class="field">
                            <label class="label">Statut</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="status">
                                        <option value="">Tous</option>
                                        <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Actifs</option>
                                        <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactifs</option>
                                        <option value="suspended" <?= ($status ?? '') === 'suspended' ? 'selected' : '' ?>>Suspendus</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column is-2">
                        <div class="field">
                            <label class="label">Type</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="type">
                                        <option value="">Tous</option>
                                        <option value="student" <?= ($type ?? '') === 'student' ? 'selected' : '' ?>>Étudiant</option>
                                        <option value="teacher" <?= ($type ?? '') === 'teacher' ? 'selected' : '' ?>>Enseignant</option>
                                        <option value="staff" <?= ($type ?? '') === 'staff' ? 'selected' : '' ?>>Personnel</option>
                                        <option value="external" <?= ($type ?? '') === 'external' ? 'selected' : '' ?>>Externe</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column is-2">
                        <div class="field">
                            <label class="label">Date d'Inscription</label>
                            <div class="control">
                                <input class="input" 
                                       type="date" 
                                       name="registration_date" 
                                       value="<?= $registrationDate ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">&nbsp;</label>
                            <div class="control">
                                <div class="buttons">
                                    <button type="submit" class="button is-primary">
                                        <i class="fas fa-search"></i>
                                        Rechercher
                                    </button>
                                    <a href="<?= base_url('admin/bibliotheque/members') ?>" class="button is-light">
                                        <i class="fas fa-times"></i>
                                        Réinitialiser
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Actions -->
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <a href="<?= base_url('admin/bibliotheque/members/create') ?>" class="button is-primary">
                    <i class="fas fa-plus"></i>
                    Nouveau Membre
                </a>
            </div>
            <div class="level-item">
                <a href="<?= base_url('admin/bibliotheque/members/import') ?>" class="button is-info">
                    <i class="fas fa-upload"></i>
                    Importer
                </a>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <a href="<?= base_url('admin/bibliotheque/members/export') ?>" class="button is-success">
                    <i class="fas fa-download"></i>
                    Exporter
                </a>
            </div>
        </div>
    </div>

    <!-- Liste des Membres -->
    <div class="card">
        <div class="card-header">
            <p class="card-header-title">
                <i class="fas fa-list"></i>
                Liste des Membres
            </p>
        </div>
        <div class="card-content">
            <?php if (empty($members)): ?>
                <div class="has-text-centered py-6">
                    <i class="fas fa-users fa-3x has-text-grey-light mb-4"></i>
                    <p class="has-text-grey">Aucun membre trouvé</p>
                    <a href="<?= base_url('admin/bibliotheque/members/create') ?>" class="button is-primary mt-4">
                        <i class="fas fa-plus"></i>
                        Ajouter un membre
                    </a>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table is-fullwidth is-striped is-hoverable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Membre</th>
                                <th>Contact</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Emprunts</th>
                                <th>Date d'Inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td>
                                        <span class="tag is-info">#<?= $member['id'] ?></span>
                                    </td>
                                    <td>
                                        <div class="media">
                                            <div class="media-left">
                                                <figure class="image is-32x32">
                                                    <div class="is-rounded has-background-grey-light has-text-centered" style="width: 32px; height: 32px; line-height: 32px;">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                </figure>
                                            </div>
                                            <div class="media-content">
                                                <div>
                                                    <strong><?= esc($member['first_name']) ?> <?= esc($member['last_name']) ?></strong>
                                                    <br>
                                                    <small class="has-text-grey"><?= esc($member['email'] ?? 'Non renseigné') ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= esc($member['email']) ?></strong>
                                            <br>
                                            <small class="has-text-grey"><?= esc($member['phone'] ?? 'Non renseigné') ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $typeLabels = [
                                            'STUDENT' => 'Étudiant',
                                            'TEACHER' => 'Enseignant'
                                        ];
                                        $typeClasses = [
                                            'STUDENT' => 'is-info',
                                            'TEACHER' => 'is-warning'
                                        ];
                                        ?>
                                        <span class="tag <?= $typeClasses[$member['member_type']] ?? 'is-light' ?>">
                                            <?= $typeLabels[$member['member_type']] ?? 'Inconnu' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        if ($member['member_type'] === 'TEACHER') {
                                            $status = $member['is_active'] ? 'ACTIVE' : 'INACTIVE';
                                        } else {
                                            $status = $member['status'];
                                        }
                                        
                                        $statusLabels = [
                                            'ACTIVE' => 'Actif',
                                            'INACTIVE' => 'Inactif',
                                            'SUSPENDED' => 'Suspendu'
                                        ];
                                        $statusClasses = [
                                            'ACTIVE' => 'is-success',
                                            'INACTIVE' => 'is-light',
                                            'SUSPENDED' => 'is-danger'
                                        ];
                                        ?>
                                        <span class="tag <?= $statusClasses[$status] ?? 'is-light' ?>">
                                            <?= $statusLabels[$status] ?? 'Inconnu' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= $member['current_loans'] ?? 0 ?>/<?= $member['max_loans'] ?? 3 ?></strong>
                                            <br>
                                            <small class="has-text-grey">emprunts actifs</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= date('d/m/Y', strtotime($member['created_at'])) ?></strong>
                                            <br>
                                            <small class="has-text-grey"><?= date('H:i', strtotime($member['created_at'])) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="buttons are-small">
                                            <a href="<?= base_url('admin/bibliotheque/members/' . $member['id']) ?>" 
                                               class="button is-info" 
                                               title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <a href="<?= base_url('admin/bibliotheque/members/' . $member['id'] . '/edit') ?>" 
                                               class="button is-warning" 
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <a href="<?= base_url('admin/bibliotheque/members/' . $member['id'] . '/loans') ?>" 
                                               class="button is-success" 
                                               title="Voir les emprunts">
                                                <i class="fas fa-book"></i>
                                            </a>
                                            
                                            <?php 
                                            $memberStatus = ($member['member_type'] === 'TEACHER') ? ($member['is_active'] ? 'ACTIVE' : 'INACTIVE') : $member['status'];
                                            if ($memberStatus === 'ACTIVE'): ?>
                                                <a href="<?= base_url('admin/bibliotheque/members/' . $member['id'] . '/suspend') ?>" 
                                                   class="button is-danger" 
                                                   title="Suspendre"
                                                   onclick="return confirm('Suspendre ce membre ?')">
                                                    <i class="fas fa-ban"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= base_url('admin/bibliotheque/members/' . $member['id'] . '/activate') ?>" 
                                                   class="button is-success" 
                                                   title="Activer">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <button onclick="deleteMember(<?= $member['id'] ?>)" 
                                                    class="button is-danger" 
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($pager)): ?>
                    <nav class="pagination is-centered" role="navigation" aria-label="pagination">
                        <?= $pager->links() ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Membres avec Emprunts en Retard -->
    <?php if (!empty($membersWithOverdueLoans)): ?>
        <div class="card">
            <div class="card-header">
                <p class="card-header-title has-text-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Membres avec Emprunts en Retard (<?= count($membersWithOverdueLoans) ?>)
                </p>
            </div>
            <div class="card-content">
                <div class="table-container">
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Membre</th>
                                <th>Emprunts en Retard</th>
                                <th>Dernier Retard</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($membersWithOverdueLoans as $member): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($member['first_name'] ?? 'N/A') ?> <?= esc($member['last_name'] ?? 'N/A') ?></strong>
                                        <br>
                                        <small class="has-text-grey"><?= esc($member['email'] ?? 'Non renseigné') ?></small>
                                    </td>
                                    <td>
                                        <span class="tag is-danger">
                                            <?= $member['overdue_count'] ?> livre<?= $member['overdue_count'] > 1 ? 's' : '' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="has-text-danger"><?= date('d/m/Y', strtotime($member['latest_overdue'])) ?></strong>
                                    </td>
                                    <td>
                                        <div class="buttons are-small">
                                            <a href="<?= base_url('admin/bibliotheque/members/' . $member['id'] . '/loans') ?>" 
                                               class="button is-info" 
                                               title="Voir les emprunts">
                                                <i class="fas fa-book"></i>
                                                Voir Emprunts
                                            </a>
                                            <a href="<?= base_url('admin/bibliotheque/members/' . $member['id'] . '/suspend') ?>" 
                                               class="button is-danger" 
                                               title="Suspendre le membre">
                                                <i class="fas fa-ban"></i>
                                                Suspendre
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function deleteMember(memberId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce membre ? Cette action est irréversible.')) {
        window.location.href = '<?= base_url('admin/bibliotheque/members') ?>/' + memberId + '/delete';
    }
}

// Auto-submit form on filter change
document.querySelectorAll('select[name="status"], select[name="type"], input[name="registration_date"]').forEach(function(element) {
    element.addEventListener('change', function() {
        this.closest('form').submit();
    });
});
</script>

<?= $this->endSection() ?>







