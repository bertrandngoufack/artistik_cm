<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <i class="fas fa-stethoscope"></i>
                Diagnostic Système
            </h1>
            <p class="subtitle">Analysez la santé et les performances du système KISSAI SCHOOL</p>
        </div>
        <div class="column is-narrow">
            <a href="<?= base_url('admin/configuration') ?>" class="button is-light">
                <i class="fas fa-arrow-left"></i>
                Retour
            </a>
        </div>
    </div>

    <!-- Résumé de Santé du Système -->
    <div class="columns mb-4">
        <div class="column">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-heartbeat"></i>
                        État de Santé du Système
                    </p>
                </div>
                <div class="card-content">
                    <div class="columns">
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Base de Données</p>
                                <p class="title is-4 has-text-success">
                                    <i class="fas fa-check-circle"></i>
                                </p>
                                <p class="subtitle is-6">Connectée</p>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Serveur Web</p>
                                <p class="title is-4 has-text-success">
                                    <i class="fas fa-check-circle"></i>
                                </p>
                                <p class="subtitle is-6">Opérationnel</p>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Licence</p>
                                <p class="title is-4 has-text-success">
                                    <i class="fas fa-check-circle"></i>
                                </p>
                                <p class="subtitle is-6">Active</p>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Performance</p>
                                <p class="title is-4 has-text-info">
                                    <i class="fas fa-chart-line"></i>
                                </p>
                                <p class="subtitle is-6">Optimale</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques Système -->
    <?php if (isset($system_stats) && $system_stats): ?>
    <div class="columns mb-4">
        <div class="column">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-chart-bar"></i>
                        Statistiques Système
                    </p>
                </div>
                <div class="card-content">
                    <div class="columns">
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Étudiants</p>
                                <p class="title is-4 has-text-primary"><?= $system_stats['students'] ?? 0 ?></p>
                                <p class="subtitle is-6">Actifs</p>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Enseignants</p>
                                <p class="title is-4 has-text-info"><?= $system_stats['teachers'] ?? 0 ?></p>
                                <p class="subtitle is-6">Actifs</p>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Classes</p>
                                <p class="title is-4 has-text-success"><?= $system_stats['classes'] ?? 0 ?></p>
                                <p class="subtitle is-6">Actives</p>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Utilisateurs</p>
                                <p class="title is-4 has-text-warning"><?= $system_stats['users'] ?? 0 ?></p>
                                <p class="subtitle is-6">Connectés</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Utilisation des Ressources -->
    <div class="columns mb-4">
        <div class="column is-6">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-hdd"></i>
                        Utilisation du Disque
                    </p>
                </div>
                <div class="card-content">
                    <?php if (isset($system_stats['disk_usage'])): ?>
                    <div class="content">
                        <div class="columns">
                            <div class="column is-6">
                                <p><strong>Total :</strong> <?= $system_stats['disk_usage']['total'] ?></p>
                                <p><strong>Utilisé :</strong> <?= $system_stats['disk_usage']['used'] ?></p>
                                <p><strong>Libre :</strong> <?= $system_stats['disk_usage']['free'] ?></p>
                            </div>
                            <div class="column is-6">
                                <progress class="progress <?= $system_stats['disk_usage']['percentage'] > 80 ? 'is-danger' : ($system_stats['disk_usage']['percentage'] > 60 ? 'is-warning' : 'is-success') ?>" 
                                          value="<?= $system_stats['disk_usage']['percentage'] ?>" 
                                          max="100">
                                    <?= $system_stats['disk_usage']['percentage'] ?>%
                                </progress>
                                <p class="has-text-centered">
                                    <strong><?= $system_stats['disk_usage']['percentage'] ?>%</strong> utilisé
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <p class="has-text-grey">Informations non disponibles</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="column is-6">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-memory"></i>
                        Utilisation de la Mémoire
                    </p>
                </div>
                <div class="card-content">
                    <?php if (isset($system_stats['memory_usage'])): ?>
                    <div class="content">
                        <p><strong>Limite :</strong> <?= $system_stats['memory_usage']['limit'] ?></p>
                        <p><strong>Utilisée :</strong> <?= $system_stats['memory_usage']['usage'] ?></p>
                        <p><strong>Pic :</strong> <?= $system_stats['memory_usage']['peak'] ?></p>
                        <div class="notification is-info is-light">
                            <p class="is-size-7">
                                <i class="fas fa-info-circle"></i>
                                La mémoire est gérée automatiquement par PHP
                            </p>
                        </div>
                    </div>
                    <?php else: ?>
                    <p class="has-text-grey">Informations non disponibles</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations Techniques -->
    <div class="columns mb-4">
        <div class="column">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-cogs"></i>
                        Informations Techniques
                    </p>
                </div>
                <div class="card-content">
                    <div class="columns">
                        <div class="column is-6">
                            <table class="table is-fullwidth">
                                <tbody>
                                    <tr>
                                        <td><strong>Système :</strong></td>
                                        <td>KISSAI SCHOOL v1.0</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Version PHP :</strong></td>
                                        <td><?= $system_stats['php_version'] ?? 'N/A' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Version CodeIgniter :</strong></td>
                                        <td><?= $system_stats['ci_version'] ?? 'N/A' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Serveur :</strong></td>
                                        <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="column is-6">
                            <table class="table is-fullwidth">
                                <tbody>
                                    <tr>
                                        <td><strong>Base de données :</strong></td>
                                        <td>MariaDB/MySQL</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Extensions PHP :</strong></td>
                                        <td>
                                            <span class="tag is-success">PDO</span>
                                            <span class="tag is-success">cURL</span>
                                            <span class="tag is-success">JSON</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Mode :</strong></td>
                                        <td>
                                            <span class="tag <?= ENVIRONMENT === 'production' ? 'is-danger' : 'is-warning' ?>">
                                                <?= strtoupper(ENVIRONMENT) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Dernière vérification :</strong></td>
                                        <td><?= date('d/m/Y H:i:s') ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tests de Performance -->
    <div class="columns mb-4">
        <div class="column">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-tachometer-alt"></i>
                        Tests de Performance
                    </p>
                </div>
                <div class="card-content">
                    <div class="columns">
                        <div class="column is-4">
                            <div class="box has-text-centered">
                                <p class="heading">Temps de Réponse</p>
                                <button class="button is-info is-fullwidth" onclick="testResponseTime()">
                                    <i class="fas fa-stopwatch"></i>
                                    Tester
                                </button>
                                <div id="responseTimeResult" class="mt-2"></div>
                            </div>
                        </div>
                        <div class="column is-4">
                            <div class="box has-text-centered">
                                <p class="heading">Connexion DB</p>
                                <button class="button is-success is-fullwidth" onclick="testDatabaseConnection()">
                                    <i class="fas fa-database"></i>
                                    Tester
                                </button>
                                <div id="dbConnectionResult" class="mt-2"></div>
                            </div>
                        </div>
                        <div class="column is-4">
                            <div class="box has-text-centered">
                                <p class="heading">Licence</p>
                                <button class="button is-warning is-fullwidth" onclick="testLicense()">
                                    <i class="fas fa-key"></i>
                                    Vérifier
                                </button>
                                <div id="licenseResult" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions de Maintenance -->
    <div class="columns">
        <div class="column">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-tools"></i>
                        Actions de Maintenance
                    </p>
                </div>
                <div class="card-content">
                    <div class="columns">
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Cache</p>
                                <button class="button is-info is-fullwidth" onclick="clearCache()">
                                    <i class="fas fa-broom"></i>
                                    Vider
                                </button>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Logs</p>
                                <button class="button is-warning is-fullwidth" onclick="viewSystemLogs()">
                                    <i class="fas fa-file-alt"></i>
                                    Consulter
                                </button>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Sauvegarde</p>
                                <button class="button is-success is-fullwidth" onclick="createBackup()">
                                    <i class="fas fa-download"></i>
                                    Créer
                                </button>
                            </div>
                        </div>
                        <div class="column is-3">
                            <div class="box has-text-centered">
                                <p class="heading">Rapport</p>
                                <button class="button is-primary is-fullwidth" onclick="generateReport()">
                                    <i class="fas fa-file-pdf"></i>
                                    Générer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour les logs système -->
<div class="modal" id="systemLogsModal">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Logs Système</p>
            <button class="delete" aria-label="close" onclick="closeSystemLogsModal()"></button>
        </header>
        <section class="modal-card-body">
            <div id="systemLogsContent">
                <p class="has-text-centered">
                    <i class="fas fa-spinner fa-spin"></i>
                    Chargement des logs...
                </p>
            </div>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-info" onclick="refreshSystemLogs()">
                <i class="fas fa-sync"></i>
                Actualiser
            </button>
            <button class="button" onclick="closeSystemLogsModal()">Fermer</button>
        </footer>
    </div>
</div>

<script>
// Fonction pour tester le temps de réponse
function testResponseTime() {
    const startTime = performance.now();
    const resultDiv = document.getElementById('responseTimeResult');
    
    resultDiv.innerHTML = '<p class="has-text-info"><i class="fas fa-spinner fa-spin"></i> Test en cours...</p>';
    
    fetch('<?= base_url('admin/configuration/system-stats-api') ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        const endTime = performance.now();
        const responseTime = (endTime - startTime).toFixed(2);
        
        let status = 'success';
        let icon = 'check-circle';
        let color = 'success';
        
        if (responseTime > 1000) {
            status = 'warning';
            icon = 'exclamation-triangle';
            color = 'warning';
        } else if (responseTime > 500) {
            status = 'info';
            icon = 'info-circle';
            color = 'info';
        }
        
        resultDiv.innerHTML = `
            <p class="has-text-${color}">
                <i class="fas fa-${icon}"></i>
                ${responseTime}ms
            </p>
        `;
    })
    .catch(error => {
        resultDiv.innerHTML = '<p class="has-text-danger"><i class="fas fa-times-circle"></i> Erreur</p>';
    });
}

// Fonction pour tester la connexion à la base de données
function testDatabaseConnection() {
    const resultDiv = document.getElementById('dbConnectionResult');
    
    resultDiv.innerHTML = '<p class="has-text-info"><i class="fas fa-spinner fa-spin"></i> Test en cours...</p>';
    
    fetch('<?= base_url('admin/configuration/system-stats-api') ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            resultDiv.innerHTML = '<p class="has-text-danger"><i class="fas fa-times-circle"></i> Erreur de connexion</p>';
        } else {
            resultDiv.innerHTML = '<p class="has-text-success"><i class="fas fa-check-circle"></i> Connectée</p>';
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<p class="has-text-danger"><i class="fas fa-times-circle"></i> Erreur</p>';
    });
}

// Fonction pour tester la licence
function testLicense() {
    const resultDiv = document.getElementById('licenseResult');
    
    resultDiv.innerHTML = '<p class="has-text-info"><i class="fas fa-spinner fa-spin"></i> Vérification...</p>';
    
    fetch('<?= base_url('admin/configuration/check-license') ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            resultDiv.innerHTML = '<p class="has-text-success"><i class="fas fa-check-circle"></i> Valide</p>';
        } else {
            resultDiv.innerHTML = '<p class="has-text-danger"><i class="fas fa-times-circle"></i> Invalide</p>';
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<p class="has-text-danger"><i class="fas fa-times-circle"></i> Erreur</p>';
    });
}

// Fonction pour vider le cache
function clearCache() {
    if (confirm('Voulez-vous vider le cache du système ?')) {
        fetch('<?= base_url('admin/configuration/clear-cache') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Cache vidé avec succès', 'success');
            } else {
                showNotification('Erreur lors du vidage du cache', 'danger');
            }
        })
        .catch(error => {
            showNotification('Erreur lors du vidage du cache', 'danger');
        });
    }
}

// Fonction pour afficher les logs système
function viewSystemLogs() {
    document.getElementById('systemLogsModal').classList.add('is-active');
    loadSystemLogs();
}

// Fonction pour fermer le modal des logs système
function closeSystemLogsModal() {
    document.getElementById('systemLogsModal').classList.remove('is-active');
}

// Fonction pour charger les logs système
function loadSystemLogs() {
    fetch('<?= base_url('admin/configuration/logs') ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('systemLogsContent').innerHTML = data;
    })
    .catch(error => {
        document.getElementById('systemLogsContent').innerHTML = '<p class="has-text-danger">Erreur lors du chargement des logs</p>';
    });
}

// Fonction pour actualiser les logs système
function refreshSystemLogs() {
    loadSystemLogs();
}

// Fonction pour créer une sauvegarde
function createBackup() {
    if (confirm('Voulez-vous créer une sauvegarde du système ?')) {
        window.location.href = '<?= base_url('admin/configuration/create-backup') ?>';
    }
}

// Fonction pour générer un rapport
function generateReport() {
    if (confirm('Voulez-vous générer un rapport de diagnostic ?')) {
        window.open('<?= base_url('admin/configuration/generate-report') ?>', '_blank');
    }
}

// Fonction pour afficher les notifications
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification is-${type} is-light`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.innerHTML = `
        <button class="delete" onclick="this.parentElement.remove()"></button>
        ${message}
    `;
    
    document.body.appendChild(notification);
    
    // Supprimer automatiquement après 5 secondes
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Actualiser les diagnostics toutes les 60 secondes
setInterval(() => {
    // Actualiser les statistiques système
    fetch('<?= base_url('admin/configuration/system-stats-api') ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Diagnostics système actualisés');
    })
    .catch(error => {
        console.error('Erreur lors de l\'actualisation des diagnostics');
    });
}, 60000);
</script>

<?= $this->endSection() ?>





