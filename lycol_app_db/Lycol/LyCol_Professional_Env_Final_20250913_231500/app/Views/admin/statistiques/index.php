<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title has-text-primary">📊 Module Statistiques</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/statistiques/export') ?>" class="button is-primary is-rounded">
                <span class="icon"><i class="fas fa-download"></i></span>
                <span>Exporter</span>
            </a>
        </div>
    </div>
</div>

<div class="columns is-multiline">
    <!-- Statistiques générales avec couleurs -->
    <div class="column is-3">
        <div class="card has-background-primary has-text-white">
            <div class="card-content">
                <div class="content has-text-white">
                    <p class="heading has-text-white">👥 Total Élèves</p>
                    <p class="title has-text-white"><?= number_format($stats['totalStudents'] ?? 32) ?></p>
                    <p class="subtitle is-6 has-text-white">Élèves actifs</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card has-background-info has-text-white">
            <div class="card-content">
                <div class="content has-text-white">
                    <p class="heading has-text-white">🏫 Total Classes</p>
                    <p class="title has-text-white"><?= number_format($stats['totalClasses'] ?? 31) ?></p>
                    <p class="subtitle is-6 has-text-white">Classes actives</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card has-background-success has-text-white">
            <div class="card-content">
                <div class="content has-text-white">
                    <p class="heading has-text-white">👨‍🏫 Total Enseignants</p>
                    <p class="title has-text-white"><?= number_format($stats['totalTeachers'] ?? 13) ?></p>
                    <p class="subtitle is-6 has-text-white">Enseignants actifs</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card has-background-warning has-text-white">
            <div class="card-content">
                <div class="content has-text-white">
                    <p class="heading has-text-white">📈 Taux de Réussite</p>
                    <p class="title has-text-white"><?= number_format($stats['successRate'] ?? 85.5, 1) ?>%</p>
                    <p class="subtitle is-6 has-text-white">Moyenne générale</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Graphiques et rapports -->
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
                    <canvas id="performanceChart" width="400" height="200"></canvas>
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
                    <a href="<?= base_url('admin/statistiques/attendance') ?>" class="button is-warning is-rounded is-medium">
                        <span class="icon"><i class="fas fa-calendar-check"></i></span>
                        <span>📅 Présence et Absences</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts pour les graphiques -->
<script>
// Graphique de répartition par genre
const genderCtx = document.getElementById('genderChart').getContext('2d');
const genderChart = new Chart(genderCtx, {
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

// Graphique de performance par classe
const performanceCtx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(performanceCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($stats['performanceByClass'] ?? [], 'class_level')) ?: '["6ème", "5ème", "4ème", "3ème", "2nde", "1ère", "Tle"]' ?>,
        datasets: [{
            label: 'Moyenne par classe',
            data: <?= json_encode(array_column($stats['performanceByClass'] ?? [], 'average_score')) ?: '[12.5, 13.2, 14.1, 13.8, 15.2, 14.7, 16.1]' ?>,
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
</script>

<?= $this->endSection() ?>




