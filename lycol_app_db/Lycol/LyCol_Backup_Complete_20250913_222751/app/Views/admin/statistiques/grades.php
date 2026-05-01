<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li><a href="<?= base_url('admin/statistiques') ?>">Statistiques</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Statistiques des Notes</a></li>
                </ul>
            </nav>
            
            <h1 class="title has-text-primary">
                <i class="fas fa-chart-line"></i>
                📊 Statistiques des Notes
            </h1>
            <p class="subtitle">Analyse détaillée des performances académiques</p>
        </div>
        <div class="column is-narrow">
            <a href="<?= base_url('admin/statistiques/export/grades') ?>" class="button is-primary is-rounded">
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
                                <p class="heading has-text-white">📚 Total Notes</p>
                                <p class="title has-text-white"><?= $stats['averageScores']['total'] ?? 0 ?></p>
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
                                <p class="heading has-text-white">✅ Taux de Réussite</p>
                                <p class="title has-text-white"><?= $stats['passRates']['overall'] ?? 0 ?>%</p>
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
                                <p class="heading has-text-white">📈 Moyenne Générale</p>
                                <p class="title has-text-white"><?= number_format($stats['averageScores']['overall'] ?? 0, 1) ?>/20</p>
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
                                <p class="heading has-text-white">🎯 Meilleure Note</p>
                                <p class="title has-text-white"><?= $stats['averageScores']['highest'] ?? 0 ?>/20</p>
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
                        Répartition par Matière
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <?php if (!empty($stats['averageScores']['by_subject'])): ?>
                            <canvas id="subjectChart" width="400" height="300"></canvas>
                        <?php else: ?>
                            <p class="has-text-grey">Aucune donnée disponible pour les matières</p>
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
                        Performance par Classe
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <?php if (!empty($stats['averageScores']['by_class'])): ?>
                            <canvas id="classChart" width="400" height="300"></canvas>
                        <?php else: ?>
                            <p class="has-text-grey">Aucune donnée disponible pour les classes</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des meilleurs élèves -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <i class="fas fa-trophy"></i>
                🏆 Top 10 des Élèves
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
                            <th>Matière Forte</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($stats['topStudents'])): ?>
                            <?php foreach ($stats['topStudents'] as $index => $student): ?>
                                <tr>
                                    <td>
                                        <span class="tag <?= $index < 3 ? 'is-warning' : 'is-info' ?>">
                                            <?= $index + 1 ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= esc($student['name'] ?? 'N/A') ?></strong>
                                    </td>
                                    <td><?= esc($student['class'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="tag is-success">
                                            <?= number_format($student['average'] ?? 0, 1) ?>/20
                                        </span>
                                    </td>
                                    <td><?= esc($student['strong_subject'] ?? 'N/A') ?></td>
                                    <td>
                                        <a href="<?= base_url('admin/scolarite/students/' . ($student['id'] ?? 1) . '/view') ?>" 
                                           class="button is-small is-info">
                                            <span class="icon">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="has-text-centered has-text-grey">
                                    Aucun élève trouvé
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Analyse des taux de réussite -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <i class="fas fa-chart-line"></i>
                📊 Analyse des Taux de Réussite
            </p>
        </header>
        <div class="card-content">
            <div class="columns">
                <?php if (!empty($stats['passRates']['by_subject'])): ?>
                    <?php foreach (array_slice($stats['passRates']['by_subject'], 0, 4) as $subject => $rate): ?>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading"><?= esc($subject) ?></p>
                                <p class="title <?= $rate >= 80 ? 'has-text-success' : ($rate >= 60 ? 'has-text-warning' : 'has-text-danger') ?>">
                                    <?= number_format($rate, 1) ?>%
                                </p>
                                <progress class="progress <?= $rate >= 80 ? 'is-success' : ($rate >= 60 ? 'is-warning' : 'is-danger') ?>" 
                                          value="<?= $rate ?>" max="100"><?= $rate ?>%</progress>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="column">
                        <p class="has-text-grey has-text-centered">Aucune donnée de taux de réussite disponible</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Scripts pour les graphiques -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique par matière (si des données sont disponibles)
    <?php if (!empty($stats['averageScores']['by_subject'])): ?>
    const subjectCtx = document.getElementById('subjectChart').getContext('2d');
    new Chart(subjectCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($stats['averageScores']['by_subject'])) ?>,
            datasets: [{
                data: <?= json_encode(array_values($stats['averageScores']['by_subject'])) ?>,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                    '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
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

    // Graphique par classe (si des données sont disponibles)
    <?php if (!empty($stats['averageScores']['by_class'])): ?>
    const classCtx = document.getElementById('classChart').getContext('2d');
    new Chart(classCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($stats['averageScores']['by_class'])) ?>,
            datasets: [{
                label: 'Moyenne par classe',
                data: <?= json_encode(array_values($stats['averageScores']['by_class'])) ?>,
                backgroundColor: '#36A2EB',
                borderColor: '#2693E6',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 20
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

<?= $this->endSection() ?>








