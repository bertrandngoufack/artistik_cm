<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title has-text-primary">📅 Statistiques de Présence</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/statistiques') ?>" class="button is-light is-rounded">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour</span>
            </a>
            <a href="<?= base_url('admin/statistiques/export/attendance') ?>" class="button is-primary is-rounded">
                <span class="icon"><i class="fas fa-download"></i></span>
                <span>Exporter</span>
            </a>
        </div>
    </div>
</div>

    <!-- Statistiques générales avec couleurs -->
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="card has-background-danger has-text-white">
                <div class="card-content">
                    <div class="content has-text-white">
                        <p class="heading has-text-white">❌ Total des Absences</p>
                        <p class="title has-text-white"><?= $stats['totalAbsences'] ?? 45 ?></p>
                        <p class="subtitle is-6 has-text-white">Absences totales</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="column is-3">
            <div class="card has-background-warning has-text-white">
                <div class="card-content">
                    <div class="content has-text-white">
                        <p class="heading has-text-white">📅 Absences du Mois</p>
                        <p class="title has-text-white"><?= $stats['thisMonthAbsences'] ?? 12 ?></p>
                        <p class="subtitle is-6 has-text-white">Absences du mois en cours</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="column is-3">
            <div class="card has-background-success has-text-white">
                <div class="card-content">
                    <div class="content has-text-white">
                        <p class="heading has-text-white">✅ Taux de Justification</p>
                        <p class="title has-text-white"><?= $stats['justifiedRate'] ?? 75 ?>%</p>
                        <p class="subtitle is-6 has-text-white">Absences justifiées</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="column is-3">
            <div class="card has-background-info has-text-white">
                <div class="card-content">
                    <div class="content has-text-white">
                        <p class="heading has-text-white">👥 Élèves Actifs</p>
                        <p class="title has-text-white"><?= $stats['activeStudents'] ?? 32 ?></p>
                        <p class="subtitle is-6 has-text-white">Élèves actuellement inscrits</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques avec Chart.js -->
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
                        Absences par Classe
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <canvas id="absencesByClassChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques avec Chart.js -->
    <div class="columns">
        <div class="column is-6">
            <div class="card">
                <header class="card-header has-background-success">
                    <p class="card-header-title has-text-white">
                        <span class="icon has-text-white"><i class="fas fa-chart-line"></i></span>
                        📈 Évolution des Absences
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <canvas id="evolutionChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="column is-6">
            <div class="card">
                <header class="card-header has-background-warning">
                    <p class="card-header-title has-text-white">
                        <span class="icon has-text-white"><i class="fas fa-chart-pie"></i></span>
                        📊 Répartition par Durée
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <canvas id="durationChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="columns">
        <div class="column">
            <div class="card">
                <header class="card-header has-background-success">
                    <p class="card-header-title has-text-white">
                        <span class="icon has-text-white"><i class="fas fa-tasks"></i></span>
                        📋 Rapports Disponibles
                    </p>
                </header>
                <div class="card-content">
                    <div class="buttons is-centered">
                        <a href="<?= base_url('admin/statistiques/students') ?>" class="button is-primary is-rounded is-medium">
                            <span class="icon"><i class="fas fa-users"></i></span>
                            <span>👥 Statistiques Élèves</span>
                        </a>
                        <a href="<?= base_url('admin/statistiques/academic') ?>" class="button is-info is-rounded is-medium">
                            <span class="icon"><i class="fas fa-graduation-cap"></i></span>
                            <span>📚 Performance Académique</span>
                        </a>
                        <a href="<?= base_url('admin/statistiques/financial') ?>" class="button is-success is-rounded is-medium">
                            <span class="icon"><i class="fas fa-money-bill"></i></span>
                            <span>💰 Statistiques Financières</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts pour les graphiques -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique de répartition par genre
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    new Chart(genderCtx, {
        type: 'doughnut',
        data: {
            labels: ['Garçons', 'Filles'],
            datasets: [{
                data: [18, 14],
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

    // Graphique des absences par classe
    const classCtx = document.getElementById('absencesByClassChart').getContext('2d');
    new Chart(classCtx, {
        type: 'bar',
        data: {
            labels: ['6ème', '5ème', '4ème', '3ème', '2nde', '1ère', 'Tle'],
            datasets: [{
                label: 'Nombre d\'absences',
                data: [8, 12, 15, 10, 6, 9, 4],
                backgroundColor: [
                    '#00d1b2', // Vert
                    '#209cee', // Bleu
                    '#ffdd57', // Jaune
                    '#ff3860', // Rouge
                    '#7957d5', // Violet
                    '#ff470f', // Orange
                    '#23d160'  // Vert clair
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
                        stepSize: 5
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

    // Graphique d'évolution des absences
    const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
    new Chart(evolutionCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
            datasets: [{
                label: 'Absences mensuelles',
                data: [25, 30, 22, 35, 28, 20],
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
                        stepSize: 10
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

    // Graphique de répartition par durée
    const durationCtx = document.getElementById('durationChart').getContext('2d');
    new Chart(durationCtx, {
        type: 'doughnut',
        data: {
            labels: ['1 jour', '2-3 jours', '1 semaine', 'Plus d\'1 semaine'],
            datasets: [{
                data: [40, 35, 20, 5],
                backgroundColor: [
                    '#3273dc', // Bleu
                    '#00d1b2', // Vert
                    '#ffdd57', // Jaune
                    '#ff3860'  // Rouge
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
                            size: 12
                        }
                    }
                }
            }
        }
    });
});
</script>

<?= $this->endSection() ?>
