<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="level">
        <div class="level-left">
            <div class="level-item">
                <h1 class="title">Statistiques des Examens</h1>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <div class="buttons">
                    <a href="<?= base_url('admin/examens/statistics/export?format=pdf') ?>" class="button is-danger">
                        <span class="icon"><i class="fas fa-file-pdf"></i></span>
                        <span>Export PDF</span>
                    </a>
                    <a href="<?= base_url('admin/examens/statistics/export?format=excel') ?>" class="button is-success">
                        <span class="icon"><i class="fas fa-file-excel"></i></span>
                        <span>Export Excel</span>
                    </a>
                    <a href="<?= base_url('admin/examens/statistics/export?format=csv') ?>" class="button is-info">
                        <span class="icon"><i class="fas fa-file-csv"></i></span>
                        <span>Export CSV</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques générales -->
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="box has-background-primary has-text-white">
                <h4 class="title is-4 has-text-white">Moyenne Générale</h4>
                <p class="title is-2 has-text-white"><?= number_format($stats['averageScores']['overall'] ?? 0, 2) ?>/20</p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-success has-text-white">
                <h4 class="title is-4 has-text-white">Taux de Réussite</h4>
                <p class="title is-2 has-text-white"><?= number_format($stats['passRates']['overall'] ?? 0, 1) ?>%</p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-info has-text-white">
                <h4 class="title is-4 has-text-white">Total Examens</h4>
                <p class="title is-2 has-text-white"><?= number_format($stats['totalExams'] ?? 0) ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-warning has-text-white">
                <h4 class="title is-4 has-text-white">Examens Terminés</h4>
                <p class="title is-2 has-text-white"><?= number_format($stats['completedExams'] ?? 0) ?></p>
            </div>
        </div>
    </div>

    <!-- Statistiques par genre et meilleure classe -->
    <div class="columns is-multiline">
        <div class="column is-4">
            <div class="box has-background-info has-text-white">
                <h4 class="title is-4 has-text-white">Meilleure Classe</h4>
                <?php if (isset($stats['bestClass']) && $stats['bestClass']): ?>
                    <p class="title is-3 has-text-white"><?= esc($stats['bestClass']['class_name']) ?></p>
                    <p class="subtitle is-6 has-text-white">Moyenne: <?= number_format($stats['bestClass']['average_score'], 2) ?>/20</p>
                    <p class="subtitle is-6 has-text-white">Taux de réussite: <?= number_format($stats['bestClass']['pass_rate'], 1) ?>%</p>
                <?php else: ?>
                    <p class="title is-3 has-text-white">N/A</p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (isset($stats['performanceByGender']) && !empty($stats['performanceByGender'])): ?>
            <?php foreach ($stats['performanceByGender'] as $gender): ?>
                <div class="column is-4">
                    <div class="box <?= $gender['gender'] === 'M' ? 'has-background-primary' : 'has-background-danger' ?> has-text-white">
                        <h4 class="title is-4 has-text-white">
                            <?= $gender['gender'] === 'M' ? 'Garçons' : 'Filles' ?>
                        </h4>
                        <p class="title is-3 has-text-white"><?= number_format($gender['average_score'], 2) ?>/20</p>
                        <p class="subtitle is-6 has-text-white">
                            <?= $gender['passed'] ?>/<?= $gender['total'] ?> (<?= number_format(($gender['passed'] / $gender['total']) * 100, 1) ?>%)
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Graphiques interactifs -->
    <div class="columns is-multiline">
        <div class="column is-6">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="fas fa-chart-line"></i></span>
                        Évolution des Moyennes
                    </p>
                </header>
                <div class="card-content">
                    <canvas id="averageScoresChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <div class="column is-6">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="fas fa-chart-pie"></i></span>
                        Taux de Réussite par Classe
                    </p>
                </header>
                <div class="card-content">
                    <canvas id="passRatesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="columns is-multiline">
        <div class="column is-6">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="fas fa-chart-bar"></i></span>
                        Performance par Classe
                    </p>
                </header>
                <div class="card-content">
                    <canvas id="performanceChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <div class="column is-6">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="fas fa-chart-pie"></i></span>
                        Performance par Genre
                    </p>
                </header>
                <div class="card-content">
                    <canvas id="genderChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="columns is-multiline">
        <div class="column is-12">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="fas fa-chart-bar"></i></span>
                        Top 10 des Classes
                    </p>
                </header>
                <div class="card-content">
                    <canvas id="topClassesChart" width="800" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Meilleures classes -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="fas fa-medal"></i></span>
                Meilleures Classes
            </p>
        </header>
        <div class="card-content">
            <div class="table-container">
                <table class="table is-fullwidth is-striped">
                    <thead>
                        <tr>
                            <th>Rang</th>
                            <th>Classe</th>
                            <th>Moyenne</th>
                            <th>Taux de Réussite</th>
                            <th>Total Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($stats['topClasses'])): ?>
                            <?php foreach ($stats['topClasses'] as $index => $class): ?>
                            <tr>
                                <td>
                                    <span class="tag is-primary"><?= $index + 1 ?></span>
                                </td>
                                <td>
                                    <strong><?= esc($class['class_name']) ?></strong>
                                </td>
                                <td>
                                    <span class="tag is-success"><?= number_format($class['average_score'], 2) ?>/20</span>
                                </td>
                                <td>
                                    <span class="tag is-info"><?= number_format($class['pass_rate'], 1) ?>%</span>
                                </td>
                                <td>
                                    <span class="tag is-light"><?= number_format($class['total']) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="has-text-centered">
                                    <p class="has-text-grey">Aucune classe trouvée</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Meilleurs élèves -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="fas fa-trophy"></i></span>
                Meilleurs Élèves
            </p>
        </header>
        <div class="card-content">
            <div class="table-container">
                <table class="table is-fullwidth is-striped">
                    <thead>
                        <tr>
                            <th>Rang</th>
                            <th>Élève</th>
                            <th>Classe</th>
                            <th>Moyenne</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($stats['topStudents'])): ?>
                            <?php foreach ($stats['topStudents'] as $index => $student): ?>
                            <tr>
                                <td>
                                    <span class="tag is-primary"><?= $index + 1 ?></span>
                                </td>
                                <td><?= esc($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                <td><?= esc($student['class_name']) ?></td>
                                <td>
                                    <span class="tag is-success"><?= number_format($student['average_score'], 2) ?>/20</span>
                                </td>
                                <td>
                                    <a href="<?= base_url('admin/scolarite/students/' . $student['id'] . '/view') ?>" class="button is-small is-info">
                                        <span class="icon"><i class="fas fa-eye"></i></span>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="has-text-centered">
                                    <p class="has-text-grey">Aucun élève trouvé</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts pour les graphiques -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique des moyennes
    const averageCtx = document.getElementById('averageScoresChart').getContext('2d');
    new Chart(averageCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartData['averageScoresChart']['labels'] ?? []) ?>,
            datasets: [{
                label: 'Moyenne Générale',
                data: <?= json_encode($chartData['averageScoresChart']['data'] ?? []) ?>,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 20
                }
            }
        }
    });

    // Graphique des taux de réussite
    const passRatesCtx = document.getElementById('passRatesChart').getContext('2d');
    new Chart(passRatesCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($chartData['passRatesChart']['labels'] ?? []) ?>,
            datasets: [{
                data: <?= json_encode($chartData['passRatesChart']['data'] ?? []) ?>,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)'
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

    // Graphique de performance par classe
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    new Chart(performanceCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartData['performanceTrendChart']['labels'] ?? []) ?>,
            datasets: [{
                label: 'Moyenne',
                data: <?= json_encode($chartData['performanceTrendChart']['averages'] ?? []) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.8)',
                borderColor: 'rgb(75, 192, 192)',
                borderWidth: 1
            }, {
                label: 'Taux de Réussite (%)',
                data: <?= json_encode($chartData['performanceTrendChart']['passRates'] ?? []) ?>,
                backgroundColor: 'rgba(255, 205, 86, 0.8)',
                borderColor: 'rgb(255, 205, 86)',
                borderWidth: 1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 20,
                    position: 'left'
                },
                y1: {
                    beginAtZero: true,
                    max: 100,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });

    // Graphique de performance par genre
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    new Chart(genderCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($chartData['genderChart']['labels'] ?? []) ?>,
            datasets: [{
                data: <?= json_encode($chartData['genderChart']['data'] ?? []) ?>,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',  // Bleu pour les garçons
                    'rgba(255, 99, 132, 0.8)'   // Rose pour les filles
                ],
                borderColor: [
                    'rgb(54, 162, 235)',
                    'rgb(255, 99, 132)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed.toFixed(2) + '/20';
                        }
                    }
                }
            }
        }
    });

    // Graphique des meilleures classes
    const topClassesCtx = document.getElementById('topClassesChart').getContext('2d');
    new Chart(topClassesCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartData['topClassesChart']['labels'] ?? []) ?>,
            datasets: [{
                label: 'Moyenne par Classe',
                data: <?= json_encode($chartData['topClassesChart']['data'] ?? []) ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.8)',
                borderColor: 'rgb(75, 192, 192)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 20,
                    title: {
                        display: true,
                        text: 'Moyenne (/20)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Classes'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Moyenne: ' + context.parsed.y.toFixed(2) + '/20';
                        }
                    }
                }
            }
        }
    });
});
</script>

<?= $this->endSection() ?>
