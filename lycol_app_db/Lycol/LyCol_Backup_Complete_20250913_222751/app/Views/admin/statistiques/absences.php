<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li><a href="<?= base_url('admin/statistiques') ?>">Statistiques</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Statistiques des Absences</a></li>
                </ul>
            </nav>
            
            <h1 class="title has-text-primary">
                <i class="fas fa-calendar-times"></i>
                📊 Statistiques des Absences
            </h1>
            <p class="subtitle">Suivi de la présence et analyse des absences</p>
        </div>
        <div class="column is-narrow">
            <a href="<?= base_url('admin/statistiques/export/absences') ?>" class="button is-primary is-rounded">
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
            <div class="card has-background-danger">
                <div class="card-content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading has-text-white">📅 Total Absences</p>
                                <p class="title has-text-white"><?= $stats['totalAbsences'] ?? 0 ?></p>
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
                                <p class="heading has-text-white">⏰ Absences Longues</p>
                                <p class="title has-text-white"><?= ($stats['byDuration']['long'] ?? 0) ?></p>
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
                                <p class="heading has-text-white">📊 Taux Justifié</p>
                                <p class="title has-text-white"><?= number_format($stats['justifiedRate'] ?? 0, 1) ?>%</p>
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
                                <p class="heading has-text-white">✅ Présence</p>
                                <p class="title has-text-white"><?= number_format(100 - ($stats['totalAbsences'] ?? 0), 1) ?>%</p>
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
                        <i class="fas fa-chart-line"></i>
                        Tendances Mensuelles
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <?php if (!empty($stats['monthlyTrend'])): ?>
                            <canvas id="monthlyChart" width="400" height="300"></canvas>
                        <?php else: ?>
                            <p class="has-text-grey">Aucune donnée de tendance mensuelle disponible</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="column is-6">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-chart-pie"></i>
                        Répartition par Durée
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <?php if (!empty($stats['byDuration'])): ?>
                            <canvas id="durationChart" width="400" height="300"></canvas>
                        <?php else: ?>
                            <p class="has-text-grey">Aucune donnée de répartition par durée disponible</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Répartition par classe -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <i class="fas fa-chart-bar"></i>
                📊 Répartition des Absences par Classe
            </p>
        </header>
        <div class="card-content">
            <div class="columns">
                <?php if (!empty($stats['byClass'])): ?>
                    <?php foreach (array_slice($stats['byClass'], 0, 4) as $class => $data): ?>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading"><?= esc($class) ?></p>
                                <p class="title <?= ($data['rate'] ?? 0) <= 5 ? 'has-text-success' : (($data['rate'] ?? 0) <= 10 ? 'has-text-warning' : 'has-text-danger') ?>">
                                    <?= number_format($data['rate'] ?? 0, 1) ?>%
                                </p>
                                <p class="subtitle is-6"><?= $data['count'] ?? 0 ?> absences</p>
                                <progress class="progress <?= ($data['rate'] ?? 0) <= 5 ? 'is-success' : (($data['rate'] ?? 0) <= 10 ? 'is-warning' : 'is-danger') ?>" 
                                          value="<?= $data['rate'] ?? 0 ?>" max="100"><?= $data['rate'] ?? 0 ?>%</progress>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="column">
                        <p class="has-text-grey has-text-centered">Aucune donnée de répartition par classe disponible</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Analyse des motifs d'absence -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <i class="fas fa-list"></i>
                📋 Analyse des Motifs d'Absence
            </p>
        </header>
        <div class="card-content">
            <div class="table-container">
                <table class="table is-fullwidth is-striped">
                    <thead>
                        <tr>
                            <th>Motif</th>
                            <th>Nombre</th>
                            <th>Pourcentage</th>
                            <th>Statut</th>
                            <th>Tendance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($stats['byReason'] ?? [])): ?>
                            <?php foreach ($stats['byReason'] as $reason => $data): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($reason) ?></strong>
                                    </td>
                                    <td>
                                        <span class="tag is-info"><?= $data['count'] ?? 0 ?></span>
                                    </td>
                                    <td>
                                        <?= number_format($data['percentage'] ?? 0, 1) ?>%
                                    </td>
                                    <td>
                                        <?php if (($data['justified'] ?? false)): ?>
                                            <span class="tag is-success">Justifié</span>
                                        <?php else: ?>
                                            <span class="tag is-danger">Non justifié</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (($data['trend'] ?? '') === 'increasing'): ?>
                                            <span class="tag is-danger">↗️ En hausse</span>
                                        <?php elseif (($data['trend'] ?? '') === 'decreasing'): ?>
                                            <span class="tag is-success">↘️ En baisse</span>
                                        <?php else: ?>
                                            <span class="tag is-light">→ Stable</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="has-text-centered has-text-grey">
                                    Aucune donnée de motif d'absence disponible
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
                        <?php if (($stats['totalAbsences'] ?? 0) > 50): ?>
                            <li>🔴 <strong>Urgent:</strong> Taux d'absence élevé - Contacter les parents</li>
                        <?php endif; ?>
                        <?php if (($stats['justifiedRate'] ?? 0) < 70): ?>
                            <li>🟡 <strong>Attention:</strong> Taux de justification faible - Vérifier les justificatifs</li>
                        <?php endif; ?>
                        <?php if (!empty($stats['byClass']) && max(array_column($stats['byClass'], 'rate')) > 15): ?>
                            <li>🟠 <strong>Surveillance:</strong> Classes avec taux d'absence élevé</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="column is-6">
                    <h4 class="title is-5">Recommandations</h4>
                    <ul>
                        <li>📧 Envoyer des notifications automatiques aux parents</li>
                        <li>📊 Générer des rapports hebdomadaires</li>
                        <li>👥 Organiser des réunions avec les parents concernés</li>
                        <li>📚 Mettre en place un système de suivi personnalisé</li>
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
    // Graphique des tendances mensuelles (si des données sont disponibles)
    <?php if (!empty($stats['monthlyTrend'])): ?>
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_keys($stats['monthlyTrend'])) ?>,
            datasets: [{
                label: 'Absences par mois',
                data: <?= json_encode(array_values($stats['monthlyTrend'])) ?>,
                borderColor: '#FF6384',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.1
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

    // Graphique de répartition par durée (si des données sont disponibles)
    <?php if (!empty($stats['byDuration'])): ?>
    const durationCtx = document.getElementById('durationChart').getContext('2d');
    new Chart(durationCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($stats['byDuration'])) ?>,
            datasets: [{
                data: <?= json_encode(array_values($stats['byDuration'])) ?>,
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'
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
});
</script>

<?= $this->endSection() ?>








