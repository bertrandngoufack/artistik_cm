<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Statistiques des Paiements</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/statistiques') ?>" class="button is-light">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour</span>
            </a>
            <a href="<?= base_url('admin/statistiques/export/payments') ?>" class="button is-primary">
                <span class="icon"><i class="fas fa-download"></i></span>
                <span>Exporter</span>
            </a>
        </div>
    </div>
</div>

<!-- Statistiques générales -->
<div class="columns is-multiline">
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Revenus Totaux</p>
                    <p class="title"><?= number_format($stats['totalRevenue'] ?? 0, 0, ',', ' ') ?> FCFA</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Total Paiements</p>
                    <p class="title"><?= number_format($stats['totalPayments'] ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Moyenne par Paiement</p>
                    <p class="title"><?= number_format($stats['averagePayment'] ?? 0, 0, ',', ' ') ?> FCFA</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-3">
        <div class="card">
            <div class="card-content">
                <div class="content">
                    <p class="heading">Taux de Recouvrement</p>
                    <p class="title"><?= number_format($stats['recoveryRate'] ?? 0, 1) ?>%</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Graphiques -->
<div class="columns">
    <div class="column is-6">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-chart-pie"></i></span>
                    Répartition par Méthode de Paiement
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <canvas id="paymentMethodChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="column is-6">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-chart-bar"></i></span>
                    Revenus Mensuels
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <canvas id="monthlyRevenueChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tableau des revenus par mois -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-table"></i></span>
            Revenus par Mois
        </p>
    </header>
    <div class="card-content">
        <div class="table-container">
            <table class="table is-fullwidth is-striped">
                <thead>
                    <tr>
                        <th>Mois</th>
                        <th>Nombre de Paiements</th>
                        <th>Montant Total</th>
                        <th>Moyenne</th>
                        <th>Pourcentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($stats['monthlyRevenue'])): ?>
                        <?php foreach ($stats['monthlyRevenue'] as $month): ?>
                        <tr>
                            <td><?= esc($month['month']) ?></td>
                            <td><?= number_format($month['count']) ?></td>
                            <td><?= number_format($month['total'], 0, ',', ' ') ?> FCFA</td>
                            <td><?= number_format($month['average'], 0, ',', ' ') ?> FCFA</td>
                            <td><?= number_format(($month['total'] / ($stats['totalRevenue'] ?? 1)) * 100, 1) ?>%</td>
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

<!-- Répartition par type de frais -->
<div class="card">
    <header class="card-header">
        <p class="card-header-title">
            <span class="icon"><i class="fas fa-chart-donut"></i></span>
            Répartition par Type de Frais
        </p>
    </header>
    <div class="card-content">
        <div class="content">
            <canvas id="feeTypeChart" width="800" height="300"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique par méthode de paiement
    const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
    const paymentMethodData = <?= json_encode($stats['paymentMethods'] ?? []) ?>;
    
    new Chart(paymentMethodCtx, {
        type: 'pie',
        data: {
            labels: paymentMethodData.map(item => item.method),
            datasets: [{
                data: paymentMethodData.map(item => item.count),
                backgroundColor: ['#3273dc', '#00d1b2', '#ffdd57', '#ff3860']
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

    // Graphique des revenus mensuels
    const monthlyRevenueCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
    const monthlyRevenueData = <?= json_encode($stats['monthlyRevenue'] ?? []) ?>;
    
    new Chart(monthlyRevenueCtx, {
        type: 'bar',
        data: {
            labels: monthlyRevenueData.map(item => item.month),
            datasets: [{
                label: 'Revenus (FCFA)',
                data: monthlyRevenueData.map(item => item.total),
                backgroundColor: '#00d1b2'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                        }
                    }
                }
            }
        }
    });

    // Graphique par type de frais
    const feeTypeCtx = document.getElementById('feeTypeChart').getContext('2d');
    const feeTypeData = <?= json_encode($stats['feeTypeDistribution'] ?? []) ?>;
    
    new Chart(feeTypeCtx, {
        type: 'doughnut',
        data: {
            labels: feeTypeData.map(item => item.fee_type),
            datasets: [{
                data: feeTypeData.map(item => item.total),
                backgroundColor: [
                    '#3273dc', '#00d1b2', '#ffdd57', '#ff3860', 
                    '#7957d5', '#00c4a7', '#f4f4f4', '#b5b5b5'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
});
</script>

<?= $this->endSection() ?>







