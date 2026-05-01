<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title has-text-primary">📚 Statistiques Académiques</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/statistiques') ?>" class="button is-light is-rounded">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour</span>
            </a>
            <a href="<?= base_url('admin/statistiques/export/academic') ?>" class="button is-primary is-rounded">
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
                        <p class="heading has-text-white">🏫 Total Classes</p>
                        <p class="title has-text-white"><?= $stats['classStats']['total'] ?? 31 ?></p>
                        <p class="subtitle is-6 has-text-white">Classes actives</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="column is-3">
            <div class="card has-background-info has-text-white">
                <div class="card-content">
                    <div class="content has-text-white">
                        <p class="heading has-text-white">📚 Total Matières</p>
                        <p class="title has-text-white"><?= $stats['subjectStats']['total'] ?? 20 ?></p>
                        <p class="subtitle is-6 has-text-white">Matières enseignées</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="column is-3">
            <div class="card has-background-success has-text-white">
                <div class="card-content">
                    <div class="content has-text-white">
                        <p class="heading has-text-white">📝 Total Examens</p>
                        <p class="title has-text-white"><?= $stats['examStats']['total'] ?? 36 ?></p>
                        <p class="subtitle is-6 has-text-white">Examens programmés</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="column is-3">
            <div class="card has-background-warning has-text-white">
                <div class="card-content">
                    <div class="content has-text-white">
                        <p class="heading has-text-white">📈 Moyenne Générale</p>
                        <p class="title has-text-white"><?= number_format($stats['performanceStats']['overall_average'] ?? 12.67, 2) ?>/20</p>
                        <p class="subtitle is-6 has-text-white">Performance globale</p>
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
                        Performance par Classe
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <canvas id="classPerformanceChart" width="400" height="200"></canvas>
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
                        <span class="icon has-text-white"><i class="fas fa-chart-bar"></i></span>
                        📊 Performance par Matière
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <canvas id="subjectPerformanceChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="column is-6">
            <div class="card">
                <header class="card-header has-background-warning">
                    <p class="card-header-title has-text-white">
                        <span class="icon has-text-white"><i class="fas fa-chart-pie"></i></span>
                        📈 Répartition des Notes
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <canvas id="gradeDistributionChart" width="400" height="200"></canvas>
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
                        <a href="<?= base_url('admin/statistiques/financial') ?>" class="button is-success is-rounded is-medium">
                            <span class="icon"><i class="fas fa-money-bill"></i></span>
                            <span>💰 Statistiques Financières</span>
                        </a>
                        <a href="<?= base_url('admin/statistiques/attendance') ?>" class="button is-warning is-rounded is-medium">
                            <span class="icon"><i class="fas fa-calendar-check"></i></span>
                            <span>📅 Présence et Absences</span>
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

    // Graphique de performance par classe
    const classCtx = document.getElementById('classPerformanceChart').getContext('2d');
    new Chart(classCtx, {
        type: 'bar',
        data: {
            labels: ['6ème', '5ème', '4ème', '3ème', '2nde', '1ère', 'Tle'],
            datasets: [{
                label: 'Moyenne par classe',
                data: [12.5, 13.2, 14.1, 13.8, 15.2, 14.7, 16.1],
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
                    max: 20,
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

    // Graphique de performance par matière
    const subjectCtx = document.getElementById('subjectPerformanceChart').getContext('2d');
    new Chart(subjectCtx, {
        type: 'bar',
        data: {
            labels: ['Mathématiques', 'Français', 'Anglais', 'Histoire', 'Géographie', 'Sciences', 'Sport'],
            datasets: [{
                label: 'Moyenne par matière',
                data: [14.2, 13.8, 15.1, 12.5, 13.2, 14.7, 16.5],
                backgroundColor: [
                    '#3273dc', // Bleu
                    '#00d1b2', // Vert
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
                    max: 20,
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

    // Graphique de répartition des notes
    const gradeCtx = document.getElementById('gradeDistributionChart').getContext('2d');
    new Chart(gradeCtx, {
        type: 'doughnut',
        data: {
            labels: ['0-5', '6-10', '11-15', '16-20'],
            datasets: [{
                data: [5, 25, 45, 25],
                backgroundColor: [
                    '#ff3860', // Rouge (échec)
                    '#ffdd57', // Jaune (passable)
                    '#00d1b2', // Vert (bien)
                    '#3273dc'  // Bleu (très bien)
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
});
</script>

<?= $this->endSection() ?>
