<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li><a href="<?= base_url('admin/statistiques') ?>">Statistiques</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Statistiques des Enseignants</a></li>
                </ul>
            </nav>
            
            <h1 class="title has-text-primary">
                <i class="fas fa-chalkboard-teacher"></i>
                📊 Statistiques des Enseignants
            </h1>
            <p class="subtitle">Analyse complète du personnel enseignant</p>
        </div>
        <div class="column is-narrow">
            <a href="<?= base_url('admin/statistiques/export/teachers') ?>" class="button is-primary is-rounded">
                <span class="icon">
                    <i class="fas fa-download"></i>
                </span>
                <span>Exporter</span>
            </a>
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

    <!-- Statistiques générales -->
    <div class="columns">
        <div class="column is-3">
            <div class="card has-background-primary">
                <div class="card-content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading has-text-white">👨‍🏫 Total Enseignants</p>
                                <p class="title has-text-white"><?= $stats['totalTeachers'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="card has-background-success">
                <div class="card-content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading has-text-white">✅ Enseignants Actifs</p>
                                <p class="title has-text-white"><?= $stats['activeTeachers'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="card has-background-info">
                <div class="card-content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading has-text-white">📚 Moyenne Matières</p>
                                <p class="title has-text-white"><?= number_format($stats['avgSubjectsPerTeacher'] ?? 0, 1) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="card has-background-warning">
                <div class="card-content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading has-text-white">🎓 Taux de Qualification</p>
                                <p class="title has-text-white"><?= number_format($stats['qualificationRate'] ?? 0, 1) ?>%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques et analyses -->
    <div class="columns">
        <div class="column is-6">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-chart-pie"></i>
                        Répartition par Spécialisation
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <?php if (!empty($stats['bySpecialization'])): ?>
                            <canvas id="specializationChart" width="400" height="300"></canvas>
                        <?php else: ?>
                            <p class="has-text-grey">Aucune donnée de spécialisation disponible</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="column is-6">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-chart-bar"></i>
                        Répartition par Qualification
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <?php if (!empty($stats['byQualification'])): ?>
                            <canvas id="qualificationChart" width="400" height="300"></canvas>
                        <?php else: ?>
                            <p class="has-text-grey">Aucune donnée de qualification disponible</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Répartition par spécialisation détaillée -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <i class="fas fa-list"></i>
                📋 Répartition Détaillée par Spécialisation
            </p>
        </header>
        <div class="card-content">
            <div class="table-container">
                <table class="table is-fullwidth is-striped">
                    <thead>
                        <tr>
                            <th>Spécialisation</th>
                            <th>Nombre d'Enseignants</th>
                            <th>Pourcentage</th>
                            <th>Moyenne des Qualifications</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($stats['bySpecialization'])): ?>
                            <?php foreach ($stats['bySpecialization'] as $spec => $data): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($spec) ?></strong>
                                    </td>
                                    <td>
                                        <span class="tag is-info"><?= $data['count'] ?? 0 ?></span>
                                    </td>
                                    <td>
                                        <?= number_format($data['percentage'] ?? 0, 1) ?>%
                                    </td>
                                    <td>
                                        <?= number_format($data['avgQualification'] ?? 0, 1) ?>
                                    </td>
                                    <td>
                                        <?php if (($data['count'] ?? 0) >= 3): ?>
                                            <span class="tag is-success">Bien doté</span>
                                        <?php elseif (($data['count'] ?? 0) >= 1): ?>
                                            <span class="tag is-warning">Correct</span>
                                        <?php else: ?>
                                            <span class="tag is-danger">Sous-doté</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="has-text-centered has-text-grey">
                                    Aucune donnée de spécialisation disponible
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Enseignants récemment recrutés -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <i class="fas fa-user-plus"></i>
                🆕 Enseignants Récemment Recrutés
            </p>
        </header>
        <div class="card-content">
            <div class="columns">
                <?php if (!empty($stats['recentHires'])): ?>
                    <?php foreach (array_slice($stats['recentHires'], 0, 6) as $teacher): ?>
                        <div class="column is-2">
                            <div class="box has-text-centered">
                                <div class="content">
                                    <p class="heading"><?= esc($teacher['first_name'] . ' ' . $teacher['last_name']) ?></p>
                                    <p class="title is-6"><?= esc($teacher['specialization'] ?? 'Non spécifiée') ?></p>
                                    <p class="subtitle is-7"><?= esc($teacher['qualification'] ?? 'Non spécifiée') ?></p>
                                    <p class="is-size-7 has-text-grey">Recruté le <?= date('d/m/Y', strtotime($teacher['hire_date'] ?? 'now')) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="column">
                        <p class="has-text-grey has-text-centered">Aucun enseignant récemment recruté</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Actions et recommandations -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <i class="fas fa-lightbulb"></i>
                💡 Recommandations et Actions
            </p>
        </header>
        <div class="card-content">
            <div class="columns">
                <div class="column is-6">
                    <h4 class="title is-5">Actions Immédiates</h4>
                    <ul>
                        <?php if (($stats['totalTeachers'] ?? 0) < 10): ?>
                            <li>🔴 <strong>Urgent:</strong> Effectif enseignant insuffisant - Recruter</li>
                        <?php endif; ?>
                        <?php if (($stats['qualificationRate'] ?? 0) < 80): ?>
                            <li>🟡 <strong>Attention:</strong> Taux de qualification faible - Formation</li>
                        <?php endif; ?>
                        <?php if (!empty($stats['bySpecialization'])): ?>
                            <?php foreach ($stats['bySpecialization'] as $spec => $data): ?>
                                <?php if (($data['count'] ?? 0) === 0): ?>
                                    <li>🟠 <strong>Surveillance:</strong> Spécialisation <?= esc($spec) ?> sans enseignant</li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="column is-6">
                    <h4 class="title is-5">Recommandations</h4>
                    <ul>
                        <li>📚 Équilibrer la répartition par spécialisation</li>
                        <li>🎓 Améliorer le niveau de qualification</li>
                        <li>👥 Planifier les recrutements futurs</li>
                        <li>📊 Mettre en place un suivi des performances</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts pour les graphiques -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique de répartition par spécialisation (si des données sont disponibles)
    <?php if (!empty($stats['bySpecialization'])): ?>
    const specializationCtx = document.getElementById('specializationChart').getContext('2d');
    new Chart(specializationCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($stats['bySpecialization'])) ?>,
            datasets: [{
                data: <?= json_encode(array_column($stats['bySpecialization'], 'count')) ?>,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                    '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    <?php endif; ?>

    // Graphique de répartition par qualification (si des données sont disponibles)
    <?php if (!empty($stats['byQualification'])): ?>
    const qualificationCtx = document.getElementById('qualificationChart').getContext('2d');
    new Chart(qualificationCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($stats['byQualification'])) ?>,
            datasets: [{
                label: 'Nombre d\'enseignants',
                data: <?= json_encode(array_values($stats['byQualification'])) ?>,
                backgroundColor: '#3273dc',
                borderColor: '#ffffff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

<?= $this->endSection() ?>








