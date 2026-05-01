<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="section">
        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="breadcrumbs">
            <ul>
                <li><a href="<?= base_url('admin/etudes') ?>">Études</a></li>
                <li><a href="<?= base_url('admin/etudes/assignments') ?>">Assignations</a></li>
                <li class="is-active"><a href="#" aria-current="page">Détails de l'Assignation</a></li>
            </ul>
        </nav>

        <!-- Header -->
        <div class="level">
            <div class="level-left">
                <div class="level-item">
                    <h1 class="title">Détails de l'Assignation</h1>
                </div>
            </div>
            <div class="level-right">
                <div class="level-item">
                    <div class="buttons">
                        <a href="<?= base_url('admin/etudes/assignments/edit/' . $assignment['id']) ?>" class="button is-warning">
                            <span class="icon">
                                <i class="fas fa-edit"></i>
                            </span>
                            <span>Modifier</span>
                        </a>
                        <a href="<?= base_url('admin/etudes/assignments') ?>" class="button is-info">
                            <span class="icon">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span>Retour</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Détails de l'assignation -->
        <div class="columns">
            <div class="column is-8">
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            <span class="icon"><i class="fas fa-info-circle"></i></span>
                            Informations Générales
                        </p>
                    </header>
                    <div class="card-content">
                        <div class="columns is-multiline">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Enseignant</label>
                                    <div class="control">
                                        <div class="box has-background-info has-text-white">
                                            <div class="media">
                                                <div class="media-left">
                                                    <span class="icon has-text-white">
                                                        <i class="fas fa-user-tie"></i>
                                                    </span>
                                                </div>
                                                <div class="media-content">
                                                    <p class="title has-text-white is-5">
                                                        <?= esc($assignment['first_name'] . ' ' . $assignment['last_name']) ?>
                                                    </p>
                                                    <p class="subtitle has-text-white is-6">
                                                        <?= esc($assignment['teacher_email'] ?? 'Email non défini') ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Classe</label>
                                    <div class="control">
                                        <div class="box has-background-success has-text-white">
                                            <div class="media">
                                                <div class="media-left">
                                                    <span class="icon has-text-white">
                                                        <i class="fas fa-chalkboard"></i>
                                                    </span>
                                                </div>
                                                <div class="media-content">
                                                    <p class="title has-text-white is-5">
                                                        <?= esc($assignment['class_name'] ?? 'N/A') ?>
                                                    </p>
                                                    <p class="subtitle has-text-white is-6">
                                                        Niveau: <?= esc($assignment['class_level'] ?? 'N/A') ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Matière</label>
                                    <div class="control">
                                        <div class="box has-background-warning has-text-white">
                                            <div class="media">
                                                <div class="media-left">
                                                    <span class="icon has-text-white">
                                                        <i class="fas fa-book"></i>
                                                    </span>
                                                </div>
                                                <div class="media-content">
                                                    <p class="title has-text-white is-5">
                                                        <?= esc($assignment['subject_name'] ?? 'N/A') ?>
                                                    </p>
                                                    <p class="subtitle has-text-white is-6">
                                                        Code: <?= esc($assignment['subject_code'] ?? 'N/A') ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Cycle</label>
                                    <div class="control">
                                        <div class="box has-background-primary has-text-white">
                                            <div class="media">
                                                <div class="media-left">
                                                    <span class="icon has-text-white">
                                                        <i class="fas fa-layer-group"></i>
                                                    </span>
                                                </div>
                                                <div class="media-content">
                                                    <p class="title has-text-white is-5">
                                                        <?= esc($assignment['cycle_name'] ?? 'N/A') ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="columns is-multiline">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Année Académique</label>
                                    <div class="control">
                                        <input class="input" type="text" value="<?= esc($assignment['academic_year'] ?? 'N/A') ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Statut</label>
                                    <div class="control">
                                        <?php if ($assignment['is_active']): ?>
                                            <span class="tag is-success is-large">Actif</span>
                                        <?php else: ?>
                                            <span class="tag is-danger is-large">Inactif</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Enseignant Principal</label>
                                    <div class="control">
                                        <?php if ($assignment['is_principal'] ?? false): ?>
                                            <span class="tag is-info is-large">Oui</span>
                                        <?php else: ?>
                                            <span class="tag is-light is-large">Non</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Date de Création</label>
                                    <div class="control">
                                        <input class="input" type="text" value="<?= date('d/m/Y H:i', strtotime($assignment['created_at'] ?? 'now')) ?>" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-4">
                <!-- Actions rapides -->
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            <span class="icon"><i class="fas fa-bolt"></i></span>
                            Actions Rapides
                        </p>
                    </header>
                    <div class="card-content">
                        <div class="buttons is-vertical is-fullwidth">
                            <a href="<?= base_url('admin/etudes/assignments/edit/' . $assignment['id']) ?>" class="button is-warning is-fullwidth">
                                <span class="icon">
                                    <i class="fas fa-edit"></i>
                                </span>
                                <span>Modifier l'Assignation</span>
                            </a>
                            
                            <a href="<?= base_url('admin/etudes/timetable?teacher_id=' . $assignment['teacher_id']) ?>" class="button is-info is-fullwidth">
                                <span class="icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                                <span>Voir l'Emploi du Temps</span>
                            </a>
                            
                            <a href="<?= base_url('admin/enseignants/show/' . $assignment['teacher_id']) ?>" class="button is-primary is-fullwidth">
                                <span class="icon">
                                    <i class="fas fa-user-tie"></i>
                                </span>
                                <span>Profil de l'Enseignant</span>
                            </a>
                            
                            <a href="<?= base_url('admin/etudes/classes/view/' . $assignment['class_id']) ?>" class="button is-success is-fullwidth">
                                <span class="icon">
                                    <i class="fas fa-chalkboard"></i>
                                </span>
                                <span>Détails de la Classe</span>
                            </a>
                            
                            <button class="button is-danger is-fullwidth" onclick="deleteAssignment(<?= $assignment['id'] ?>)">
                                <span class="icon">
                                    <i class="fas fa-trash"></i>
                                </span>
                                <span>Supprimer l'Assignation</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Informations supplémentaires -->
                <div class="card mt-4">
                    <header class="card-header">
                        <p class="card-header-title">
                            <span class="icon"><i class="fas fa-chart-bar"></i></span>
                            Statistiques
                        </p>
                    </header>
                    <div class="card-content">
                        <div class="content">
                            <p><strong>Total des assignations de cet enseignant:</strong></p>
                            <p class="title is-4 has-text-info"><?= $assignment['total_teacher_assignments'] ?? 1 ?></p>
                            
                            <p><strong>Total des assignations pour cette classe:</strong></p>
                            <p class="title is-4 has-text-success"><?= $assignment['total_class_assignments'] ?? 1 ?></p>
                            
                            <p><strong>Total des assignations pour cette matière:</strong></p>
                            <p class="title is-4 has-text-warning"><?= $assignment['total_subject_assignments'] ?? 1 ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function deleteAssignment(assignmentId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette assignation ? Cette action est irréversible.')) {
        fetch(`<?= base_url('admin/etudes/assignments/') ?>${assignmentId}/delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) {
                window.location.href = '<?= base_url('admin/etudes/assignments') ?>';
            } else {
                alert('Erreur lors de la suppression');
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





