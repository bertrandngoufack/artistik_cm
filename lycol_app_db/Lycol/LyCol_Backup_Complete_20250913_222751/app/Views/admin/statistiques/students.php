<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title has-text-primary">👥 Statistiques des Élèves</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/statistiques') ?>" class="button is-light is-rounded">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour</span>
            </a>
            <a href="<?= base_url('admin/statistiques/export/students') ?>" class="button is-primary is-rounded">
                <span class="icon"><i class="fas fa-download"></i></span>
                <span>Exporter</span>
            </a>
        </div>
    </div>
</div>

<!-- Statistiques générales avec couleurs -->
<div class="columns is-multiline">
    <div class="column is-3">
        <div class="card has-background-primary has-text-white">
            <div class="card-content">
                <div class="content has-text-white">
                    <p class="heading has-text-white">👥 Total Élèves</p>
                    <p class="title has-text-white"><?= number_format($stats['byGender']['M'] + $stats['byGender']['F'] ?? 32) ?></p>
                    <p class="subtitle is-6 has-text-white">Élèves actifs</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card has-background-info has-text-white">
            <div class="card-content">
                <div class="content has-text-white">
                    <p class="heading has-text-white">👦 Garçons</p>
                    <p class="title has-text-white"><?= number_format($stats['byGender']['M'] ?? 18) ?></p>
                    <p class="subtitle is-6 has-text-white">Élèves masculins</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card has-background-success has-text-white">
            <div class="card-content">
                <div class="content has-text-white">
                    <p class="heading has-text-white">👧 Filles</p>
                    <p class="title has-text-white"><?= number_format($stats['byGender']['F'] ?? 14) ?></p>
                    <p class="subtitle is-6 has-text-white">Élèves féminines</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card has-background-warning has-text-white">
            <div class="card-content">
                <div class="content has-text-white">
                    <p class="heading has-text-white">📈 Taux de Réussite</p>
                    <p class="title has-text-white"><?= number_format($stats['success_rate'] ?? 85.5, 1) ?>%</p>
                    <p class="subtitle is-6 has-text-white">Moyenne générale</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Graphiques avec couleurs -->
<div class="columns">
    <div class="column is-6">
        <div class="card">
            <header class="card-header has-background-primary">
                <p class="card-header-title has-text-white">
                    <span class="icon has-text-white"><i class="fas fa-chart-pie"></i></span>
                    Répartition par Genre
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <canvas id="genderChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-6">
        <div class="card">
            <header class="card-header has-background-info">
                <p class="card-header-title has-text-white">
                    <span class="icon has-text-white"><i class="fas fa-chart-bar"></i></span>
                    Répartition par Classe
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <canvas id="classChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tableau détaillé par classe -->
<div class="card">
    <header class="card-header has-background-success">
        <p class="card-header-title has-text-white">
            <span class="icon has-text-white"><i class="fas fa-table"></i></span>
            📊 Détail par Classe
        </p>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped is-hoverable">
                <thead>
                    <tr>
                        <th class="has-background-light">Classe</th>
                        <th class="has-background-light">Total Élèves</th>
                        <th class="has-background-light">Garçons</th>
                        <th class="has-background-light">Filles</th>
                        <th class="has-background-light">Pourcentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($stats['byClass'])): ?>
                        <?php 
                        $totalStudents = $stats['byGender']['M'] + $stats['byGender']['F'];
                        foreach ($stats['byClass'] as $class): 
                        ?>
                        <tr>
                            <td><strong><?= esc($class['class_name']) ?></strong></td>
                            <td><span class="tag is-primary"><?= number_format($class['count']) ?></span></td>
                            <td><span class="tag is-info"><?= number_format($class['male'] ?? 0) ?></span></td>
                            <td><span class="tag is-success"><?= number_format($class['female'] ?? 0) ?></span></td>
                            <td><span class="tag is-warning"><?= number_format(($class['count'] / max($totalStudents, 1)) * 100, 1) ?>%</span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="has-text-centered">
                                <p class="has-text-grey">Aucune donnée disponible</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Tendances d'inscription -->
<div class="card">
    <header class="card-header has-background-warning">
        <p class="card-header-title has-text-white">
            <span class="icon has-text-white"><i class="fas fa-chart-line"></i></span>
            📈 Tendances d'Inscription
        </p>
    </header>
    <div class="card-content">
        <div class="content">
            <canvas id="enrollmentChart" width="800" height="300"></canvas>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique par genre (Donut Chart)
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    new Chart(genderCtx, {
        type: 'doughnut',
        data: {
            labels: ['Garçons', 'Filles'],
            datasets: [{
                data: [<?= $stats['byGender']['M'] ?? 18 ?>, <?= $stats['byGender']['F'] ?? 14 ?>],
                backgroundColor: [
                    '#3273dc', // Bleu pour les garçons
                    '#f14668'  // Rose pour les filles
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 14
                        }
                    }
                }
            }
        }
    });

    // Graphique par classe (Bar Chart)
    const classCtx = document.getElementById('classChart').getContext('2d');
    const classData = <?= json_encode($stats['byClass'] ?? []) ?>;
    
    new Chart(classCtx, {
        type: 'bar',
        data: {
            labels: classData.map(item => item.class_name),
            datasets: [{
                label: 'Nombre d\'élèves',
                data: classData.map(item => item.count),
                backgroundColor: [
                    '#00d1b2', // Vert
                    '#209cee', // Bleu
                    '#ffdd57', // Jaune
                    '#ff3860', // Rouge
                    '#7957d5', // Violet
                    '#ff470f', // Orange
                    '#23d160', // Vert clair
                    '#00c4a7', // Turquoise
                    '#3298dc', // Bleu clair
                    '#f48fb1', // Rose clair
                    '#9c27b0'  // Violet clair
                ],
                borderWidth: 1,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Graphique des tendances d'inscription (Line Chart)
    const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
    const enrollmentData = <?= json_encode($stats['enrollmentTrend'] ?? []) ?>;
    
    new Chart(enrollmentCtx, {
        type: 'line',
        data: {
            labels: enrollmentData.length > 0 ? enrollmentData.map(item => item.month) : ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
            datasets: [{
                label: 'Nouvelles inscriptions',
                data: enrollmentData.length > 0 ? enrollmentData.map(item => item.count) : [5, 8, 12, 6, 9, 15],
                borderColor: '#3273dc',
                backgroundColor: 'rgba(50, 115, 220, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 2
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
});
</script>

<?= $this->endSection() ?>

