<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title has-text-primary">💰 Statistiques Financières</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/statistiques') ?>" class="button is-light is-rounded">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour</span>
            </a>
            <a href="<?= base_url('admin/statistiques/export/financial') ?>" class="button is-primary is-rounded">
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
                        <p class="heading has-text-white">💰 Revenus Totaux</p>
                        <p class="title has-text-white"><?= number_format($stats['totalRevenue'] ?? 2500000, 0, ',', ' ') ?> FCFA</p>
                        <p class="subtitle is-6 has-text-white">Total des recettes</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="column is-3">
            <div class="card has-background-info has-text-white">
                <div class="card-content">
                    <div class="content has-text-white">
                        <p class="heading has-text-white">📅 Revenus du Mois</p>
                        <p class="title has-text-white"><?= number_format($stats['monthlyRevenue'] ?? 450000, 0, ',', ' ') ?> FCFA</p>
                        <p class="subtitle is-6 has-text-white">Revenus du mois en cours</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="column is-3">
            <div class="card has-background-success has-text-white">
                <div class="card-content">
                    <div class="content has-text-white">
                        <p class="heading has-text-white">⏰ Paiements en Attente</p>
                        <p class="title has-text-white"><?= $stats['pendingPayments'] ?? 15 ?></p>
                        <p class="subtitle is-6 has-text-white">Paiements en attente</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="column is-3">
            <div class="card has-background-warning has-text-white">
                <div class="card-content">
                    <div class="content has-text-white">
                        <p class="heading has-text-white">⚠️ Paiements en Retard</p>
                        <p class="title has-text-white"><?= $stats['overduePayments'] ?? 8 ?></p>
                        <p class="subtitle is-6 has-text-white">Paiements en retard</p>
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
                        Méthodes de Paiement
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <canvas id="paymentMethodsChart" width="400" height="200"></canvas>
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
                        📈 Évolution des Revenus
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <canvas id="revenueChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="column is-6">
            <div class="card">
                <header class="card-header has-background-warning">
                    <p class="card-header-title has-text-white">
                        <span class="icon has-text-white"><i class="fas fa-chart-pie"></i></span>
                        📊 Types de Frais
                    </p>
                </header>
                <div class="card-content">
                    <div class="content">
                        <canvas id="feeTypesChart" width="400" height="200"></canvas>
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

    // Graphique des méthodes de paiement
    const paymentCtx = document.getElementById('paymentMethodsChart').getContext('2d');
    new Chart(paymentCtx, {
        type: 'bar',
        data: {
            labels: ['Espèces', 'Chèque', 'Virement', 'Carte'],
            datasets: [{
                label: 'Nombre de paiements',
                data: [45, 12, 8, 15],
                backgroundColor: [
                    '#00d1b2', // Vert
                    '#209cee', // Bleu
                    '#ffdd57', // Jaune
                    '#ff3860'  // Rouge
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
                        stepSize: 10
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

    // Graphique d'évolution des revenus
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
            datasets: [{
                label: 'Revenus mensuels (FCFA)',
                data: [350000, 420000, 380000, 450000, 520000, 480000],
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
                        stepSize: 100000
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

    // Graphique des types de frais
    const feeCtx = document.getElementById('feeTypesChart').getContext('2d');
    new Chart(feeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Frais de scolarité', 'Frais d\'inscription', 'Frais de cantine', 'Autres'],
            datasets: [{
                data: [60, 20, 15, 5],
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
