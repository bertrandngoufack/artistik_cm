<?= $this->extend('admin/layout') ?>

<style>
/* Correction des superpositions dans les cartes de statistiques */
.stats-card {
    min-height: 120px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.stats-card .card-content {
    padding: 1.5rem;
}

.stats-card .heading {
    font-size: 0.75rem !important;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem !important;
    color: #7a7a7a;
}

.stats-card .title {
    font-size: 2.5rem !important;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 0.25rem !important;
    margin-top: 0 !important;
}

.stats-card .subtitle {
    font-size: 0.875rem !important;
    font-weight: 500;
    color: #7a7a7a;
    margin-top: 0 !important;
    line-height: 1.3;
}

/* Espacement spécifique pour éviter les superpositions */
.stats-card .has-text-centered {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
}

.stats-card .has-text-centered > * {
    margin-bottom: 0.25rem;
}

.stats-card .has-text-centered > *:last-child {
    margin-bottom: 0;
}

/* Responsive pour les petits écrans */
@media screen and (max-width: 768px) {
    .stats-card .title {
        font-size: 2rem !important;
    }
    
    .stats-card .subtitle {
        font-size: 0.75rem !important;
    }
}
</style>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/bibliotheque') ?>">Bibliothèque</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Rapports</a></li>
                </ul>
            </nav>
            
            <h1 class="title">
                <i class="fas fa-chart-bar"></i>
                Rapports de la Bibliothèque
            </h1>
            <p class="subtitle">Analyses et statistiques de la bibliothèque</p>
        </div>
    </div>

    <!-- Filtres de Période -->
    <div class="card">
        <div class="card-content">
            <form method="GET" action="<?= base_url('admin/bibliotheque/reports') ?>">
                <div class="columns">
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">Période</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="period" id="period">
                                        <option value="today" <?= ($period ?? '') === 'today' ? 'selected' : '' ?>>Aujourd'hui</option>
                                        <option value="week" <?= ($period ?? '') === 'week' ? 'selected' : '' ?>>Cette Semaine</option>
                                        <option value="month" <?= ($period ?? '') === 'month' ? 'selected' : '' ?>>Ce Mois</option>
                                        <option value="quarter" <?= ($period ?? '') === 'quarter' ? 'selected' : '' ?>>Ce Trimestre</option>
                                        <option value="year" <?= ($period ?? '') === 'year' ? 'selected' : '' ?>>Cette Année</option>
                                        <option value="custom" <?= ($period ?? '') === 'custom' ? 'selected' : '' ?>>Période Personnalisée</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">Date de Début</label>
                            <div class="control">
                                <input class="input" 
                                       type="date" 
                                       name="start_date" 
                                       id="start_date"
                                       value="<?= $startDate ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">Date de Fin</label>
                            <div class="control">
                                <input class="input" 
                                       type="date" 
                                       name="end_date" 
                                       id="end_date"
                                       value="<?= $endDate ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="field">
                            <label class="label">&nbsp;</label>
                            <div class="control">
                                <div class="buttons">
                                    <button type="submit" class="button is-primary">
                                        <i class="fas fa-search"></i>
                                        Générer
                                    </button>
                                    <a href="<?= base_url('admin/bibliotheque/reports') ?>" class="button is-light">
                                        <i class="fas fa-times"></i>
                                        Réinitialiser
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques Générales -->
    <div class="columns">
        <div class="column is-3">
            <div class="card stats-card">
                <div class="card-content">
                    <div class="has-text-centered">
                        <p class="heading">Total Livres</p>
                        <p class="title has-text-primary"><?= $stats['totalBooks'] ?? 0 ?></p>
                        <p class="subtitle"><?= $stats['availableBooks'] ?? 0 ?> disponibles</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="card stats-card">
                <div class="card-content">
                    <div class="has-text-centered">
                        <p class="heading">Total Membres</p>
                        <p class="title has-text-success"><?= $stats['totalMembers'] ?? 0 ?></p>
                        <p class="subtitle"><?= $stats['activeMembers'] ?? 0 ?> actifs</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="card stats-card">
                <div class="card-content">
                    <div class="has-text-centered">
                        <p class="heading">Emprunts</p>
                        <p class="title has-text-info"><?= $stats['totalLoans'] ?? 0 ?></p>
                        <p class="subtitle"><?= $stats['activeLoans'] ?? 0 ?> actifs</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="card stats-card">
                <div class="card-content">
                    <div class="has-text-centered">
                        <p class="heading">Retards</p>
                        <p class="title has-text-danger"><?= $stats['overdueLoans'] ?? 0 ?></p>
                        <p class="subtitle"><?= $stats['overdueAmount'] ?? 0 ?>€ d'amendes</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="columns">
        <div class="column is-6">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-chart-line"></i>
                        Évolution des Emprunts
                    </p>
                </div>
                <div class="card-content">
                    <canvas id="loansChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="column is-6">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-chart-pie"></i>
                        Répartition par Catégorie
                    </p>
                </div>
                <div class="card-content">
                    <canvas id="categoryChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="columns">
        <div class="column is-6">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-chart-bar"></i>
                        Top 10 des Livres les Plus Empruntés
                    </p>
                </div>
                <div class="card-content">
                    <canvas id="topBooksChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="column is-6">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-users"></i>
                        Activité par Type de Membre
                    </p>
                </div>
                <div class="card-content">
                    <canvas id="memberTypeChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Rapports Détaillés -->
    <div class="columns">
        <div class="column is-6">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-list"></i>
                        Livres les Plus Populaires
                    </p>
                </div>
                <div class="card-content">
                    <?php if (empty($topBooks)): ?>
                        <p class="has-text-grey has-text-centered">Aucune donnée disponible</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table is-fullwidth is-striped">
                                <thead>
                                    <tr>
                                        <th>Rang</th>
                                        <th>Livre</th>
                                        <th>Emprunts</th>
                                        <th>Disponibilité</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topBooks as $index => $book): ?>
                                        <tr>
                                            <td>
                                                <span class="tag is-info">#<?= $index + 1 ?></span>
                                            </td>
                                            <td>
                                                <strong><?= esc($book['title']) ?></strong>
                                                <br>
                                                <small class="has-text-grey"><?= esc($book['author']) ?></small>
                                            </td>
                                            <td>
                                                <span class="tag is-success"><?= $book['loan_count'] ?></span>
                                            </td>
                                            <td>
                                                <?php if ($book['available_copies'] > 0): ?>
                                                    <span class="tag is-success"><?= $book['available_copies'] ?> disponible<?= $book['available_copies'] > 1 ? 's' : '' ?></span>
                                                <?php else: ?>
                                                    <span class="tag is-danger">Indisponible</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="column is-6">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Emprunts en Retard
                    </p>
                </div>
                <div class="card-content">
                    <?php if (empty($overdueLoans)): ?>
                        <p class="has-text-success has-text-centered">Aucun emprunt en retard !</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="table is-fullwidth is-striped">
                                <thead>
                                    <tr>
                                        <th>Membre</th>
                                        <th>Livre</th>
                                        <th>Jours de Retard</th>
                                        <th>Amende</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($overdueLoans as $loan): ?>
                                        <?php
                                        $daysOverdue = floor((time() - strtotime($loan['due_date'])) / (60 * 60 * 24));
                                        $fine = $daysOverdue * 1; // 1€ par jour
                                        ?>
                                        <tr>
                                            <td>
                                                <strong>Membre <?= esc($loan['member_id']) ?></strong>
                                                <br>
                                                <small class="has-text-grey">membre<?= esc($loan['member_id']) ?>@lycol.edu</small>
                                            </td>
                                            <td>
                                                <strong>Livre <?= esc($loan['book_id']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="tag is-danger"><?= $daysOverdue ?> jour<?= $daysOverdue > 1 ? 's' : '' ?></span>
                                            </td>
                                            <td>
                                                <span class="tag is-warning"><?= $fine ?>€</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions d'Export -->
    <div class="card">
        <div class="card-header">
            <p class="card-header-title">
                <i class="fas fa-download"></i>
                Exporter les Rapports
            </p>
        </div>
        <div class="card-content">
            <div class="columns">
                <div class="column is-3">
                    <a href="<?= base_url('admin/bibliotheque/reports/export/loans') ?>" class="button is-info is-fullwidth">
                        <i class="fas fa-file-excel"></i>
                        Export Emprunts
                    </a>
                </div>
                <div class="column is-3">
                    <a href="<?= base_url('admin/bibliotheque/reports/export/books') ?>" class="button is-success is-fullwidth">
                        <i class="fas fa-file-excel"></i>
                        Export Livres
                    </a>
                </div>
                <div class="column is-3">
                    <a href="<?= base_url('admin/bibliotheque/reports/export/members') ?>" class="button is-warning is-fullwidth">
                        <i class="fas fa-file-excel"></i>
                        Export Membres
                    </a>
                </div>
                <div class="column is-3">
                    <a href="<?= base_url('admin/bibliotheque/reports/export/overdue') ?>" class="button is-danger is-fullwidth">
                        <i class="fas fa-file-excel"></i>
                        Export Retards
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Configuration des graphiques
const chartColors = {
    primary: '#3273dc',
    success: '#48c774',
    warning: '#ffdd57',
    danger: '#f14668',
    info: '#3298dc',
    light: '#f5f5f5'
};

// Graphique d'évolution des emprunts
const loansCtx = document.getElementById('loansChart').getContext('2d');
new Chart(loansCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chartData['loansByMonth']['labels'] ?? []) ?>,
        datasets: [{
            label: 'Emprunts',
            data: <?= json_encode($chartData['loansByMonth']['data'] ?? []) ?>,
            borderColor: chartColors.primary,
            backgroundColor: chartColors.primary + '20',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Graphique de répartition par catégorie
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($chartData['booksByCategory']['labels'] ?? []) ?>,
        datasets: [{
            data: <?= json_encode($chartData['booksByCategory']['data'] ?? []) ?>,
            backgroundColor: [
                chartColors.primary,
                chartColors.success,
                chartColors.warning,
                chartColors.danger,
                chartColors.info
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

// Graphique des livres les plus empruntés
const topBooksCtx = document.getElementById('topBooksChart').getContext('2d');
new Chart(topBooksCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartData['topBooks']['labels'] ?? []) ?>,
        datasets: [{
            label: 'Emprunts',
            data: <?= json_encode($chartData['topBooks']['data'] ?? []) ?>,
            backgroundColor: chartColors.success
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Graphique par type de membre
const memberTypeCtx = document.getElementById('memberTypeChart').getContext('2d');
new Chart(memberTypeCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartData['memberTypes']['labels'] ?? []) ?>,
        datasets: [{
            label: 'Membres',
            data: <?= json_encode($chartData['memberTypes']['data'] ?? []) ?>,
            backgroundColor: chartColors.info
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Gestion de la période personnalisée
document.getElementById('period').addEventListener('change', function() {
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    
    if (this.value === 'custom') {
        startDate.disabled = false;
        endDate.disabled = false;
    } else {
        startDate.disabled = true;
        endDate.disabled = true;
    }
});

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    const period = document.getElementById('period');
    if (period.value === 'custom') {
        document.getElementById('start_date').disabled = false;
        document.getElementById('end_date').disabled = false;
    }
});
</script>

<?= $this->endSection() ?>

