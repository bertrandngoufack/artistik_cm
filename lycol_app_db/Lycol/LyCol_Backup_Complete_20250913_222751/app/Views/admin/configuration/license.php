<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <i class="fas fa-key"></i>
                Gestion de Licence
            </h1>
            <p class="subtitle">Gérez la licence du système KISSAI SCHOOL</p>
        </div>
        <div class="column is-narrow">
            <a href="<?= base_url('admin/configuration') ?>" class="button is-light">
                <i class="fas fa-arrow-left"></i>
                Retour
            </a>
        </div>
    </div>

    <?php if (isset($license) && $license): ?>
    <!-- Informations de la Licence Actuelle -->
    <div class="columns mb-4">
        <div class="column">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-info-circle"></i>
                        Informations de la Licence Actuelle
                    </p>
                </div>
                <div class="card-content">
                    <div class="columns">
                        <div class="column is-6">
                            <table class="table is-fullwidth">
                                <tbody>
                                    <tr>
                                        <td><strong>Clé de Licence :</strong></td>
                                        <td><code><?= $license['license_key'] ?></code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Client ID :</strong></td>
                                        <td><?= $license['client_id'] ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Type de Licence :</strong></td>
                                        <td>
                                            <span class="tag <?= $license['license_type'] === 'PERMANENT' ? 'is-success' : 'is-warning' ?>">
                                                <?= strtoupper($license['license_type']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Statut :</strong></td>
                                        <td>
                                            <span class="tag <?= $license['status'] === 'ACTIVE' ? 'is-success' : 'is-danger' ?>">
                                                <?= $license['status'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="column is-6">
                            <table class="table is-fullwidth">
                                <tbody>
                                    <tr>
                                        <td><strong>Date d'Émission :</strong></td>
                                        <td><?= date('d/m/Y', strtotime($license['issued_date'])) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Date d'Expiration :</strong></td>
                                        <td>
                                            <?php if ($license['expiry_date'] === '2099-12-31'): ?>
                                                <span class="tag is-success">PERMANENTE</span>
                                            <?php else: ?>
                                                <?= date('d/m/Y', strtotime($license['expiry_date'])) ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Dernière Mise à Jour :</strong></td>
                                        <td><?= date('d/m/Y H:i', strtotime($license['updated_at'])) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Validité :</strong></td>
                                        <td>
                                            <?php
                                            $expiryDate = new DateTime($license['expiry_date']);
                                            $currentDate = new DateTime();
                                            $isValid = $expiryDate > $currentDate;
                                            ?>
                                            <span class="tag <?= $isValid ? 'is-success' : 'is-danger' ?>">
                                                <?= $isValid ? 'VALIDE' : 'EXPIRÉE' ?>
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions sur la Licence -->
    <div class="columns mb-4">
        <div class="column">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-tools"></i>
                        Actions sur la Licence
                    </p>
                </div>
                <div class="card-content">
                    <div class="columns">
                        <div class="column is-4">
                            <div class="box has-text-centered">
                                <p class="heading">Vérification</p>
                                <button class="button is-info is-fullwidth" onclick="checkLicenseValidity()">
                                    <i class="fas fa-check-circle"></i>
                                    Vérifier la Validité
                                </button>
                            </div>
                        </div>
                        <?php if ($license['license_type'] !== 'PERMANENT'): ?>
                        <div class="column is-4">
                            <div class="box has-text-centered">
                                <p class="heading">Activation Définitive</p>
                                <button class="button is-success is-fullwidth" onclick="activateDefinitiveLicense()">
                                    <i class="fas fa-crown"></i>
                                    Activer Licence Définitive
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="column is-4">
                            <div class="box has-text-centered">
                                <p class="heading">Informations</p>
                                <button class="button is-light is-fullwidth" onclick="showLicenseInfo()">
                                    <i class="fas fa-info"></i>
                                    Plus d'Informations
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Avantages de la Licence -->
    <div class="columns mb-4">
        <div class="column">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-star"></i>
                        Avantages de la Licence <?= strtoupper($license['license_type']) ?>
                    </p>
                </div>
                <div class="card-content">
                    <?php if ($license['license_type'] === 'PERMANENT'): ?>
                    <div class="notification is-success is-light">
                        <div class="content">
                            <h4 class="title is-5">
                                <i class="fas fa-crown"></i>
                                Licence Définitive - Avantages Premium
                            </h4>
                            <ul>
                                <li><strong>Aucune expiration :</strong> Utilisation illimitée dans le temps</li>
                                <li><strong>Fonctionnalités complètes :</strong> Accès à toutes les fonctionnalités</li>
                                <li><strong>Pas de limitation :</strong> Aucune restriction d'utilisation</li>
                                <li><strong>Support prioritaire :</strong> Assistance technique en priorité</li>
                                <li><strong>Mises à jour gratuites :</strong> Toutes les mises à jour incluses</li>
                                <li><strong>Production garantie :</strong> Idéal pour un déploiement en production</li>
                            </ul>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="notification is-warning is-light">
                        <div class="content">
                            <h4 class="title is-5">
                                <i class="fas fa-clock"></i>
                                Licence <?= strtoupper($license['license_type']) ?> - Limitations
                            </h4>
                            <ul>
                                <li><strong>Expiration :</strong> Licence valide jusqu'au <?= date('d/m/Y', strtotime($license['expiry_date'])) ?></li>
                                <li><strong>Renouvellement :</strong> Nécessite un renouvellement périodique</li>
                                <li><strong>Fonctionnalités limitées :</strong> Certaines fonctionnalités peuvent être restreintes</li>
                                <li><strong>Support standard :</strong> Support technique standard</li>
                            </ul>
                            <p class="mt-3">
                                <strong>Recommandation :</strong> 
                                <a href="#" onclick="activateDefinitiveLicense()" class="has-text-success">
                                    <i class="fas fa-crown"></i>
                                    Passez à une licence définitive pour un accès illimité
                                </a>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- Aucune Licence Trouvée -->
    <div class="columns">
        <div class="column">
            <div class="notification is-danger is-light">
                <div class="content">
                    <h4 class="title is-5">
                        <i class="fas fa-exclamation-triangle"></i>
                        Aucune Licence Configurée
                    </h4>
                    <p>Le système n'a pas de licence configurée. Veuillez contacter l'administrateur pour configurer une licence.</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Historique des Licences -->
    <div class="columns">
        <div class="column">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-history"></i>
                        Historique des Licences
                    </p>
                </div>
                <div class="card-content">
                    <div class="table-container">
                        <table class="table is-fullwidth is-striped">
                            <thead>
                                <tr>
                                    <th>Clé</th>
                                    <th>Type</th>
                                    <th>Statut</th>
                                    <th>Émission</th>
                                    <th>Expiration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($license) && $license): ?>
                                <tr>
                                    <td><code><?= substr($license['license_key'], 0, 12) ?>...</code></td>
                                    <td>
                                        <span class="tag <?= $license['license_type'] === 'PERMANENT' ? 'is-success' : 'is-warning' ?>">
                                            <?= strtoupper($license['license_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="tag <?= $license['status'] === 'ACTIVE' ? 'is-success' : 'is-danger' ?>">
                                            <?= $license['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($license['issued_date'])) ?></td>
                                    <td>
                                        <?php if ($license['expiry_date'] === '2099-12-31'): ?>
                                            <span class="tag is-success">PERMANENTE</span>
                                        <?php else: ?>
                                            <?= date('d/m/Y', strtotime($license['expiry_date'])) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="buttons are-small">
                                            <button class="button is-info" onclick="checkLicenseValidity()">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <?php if ($license['license_type'] !== 'PERMANENT'): ?>
                                            <button class="button is-success" onclick="activateDefinitiveLicense()">
                                                <i class="fas fa-crown"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <tr>
                                    <td colspan="6" class="has-text-centered has-text-grey">
                                        <i class="fas fa-info-circle"></i>
                                        Aucune licence trouvée
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour les informations de licence -->
<div class="modal" id="licenseInfoModal">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Informations Détaillées de la Licence</p>
            <button class="delete" aria-label="close" onclick="closeLicenseInfoModal()"></button>
        </header>
        <section class="modal-card-body">
            <div id="licenseInfoContent">
                <p class="has-text-centered">
                    <i class="fas fa-spinner fa-spin"></i>
                    Chargement des informations...
                </p>
            </div>
        </section>
        <footer class="modal-card-foot">
            <button class="button" onclick="closeLicenseInfoModal()">Fermer</button>
        </footer>
    </div>
</div>

<script>
// Fonction pour vérifier la validité de la licence
function checkLicenseValidity() {
    fetch('<?= base_url('admin/configuration/check-license') ?>', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            showNotification('✅ Licence valide et opérationnelle', 'success');
        } else {
            showNotification('❌ Licence invalide : ' + data.message, 'danger');
        }
    })
    .catch(error => {
        showNotification('❌ Erreur lors de la vérification', 'danger');
    });
}

// Fonction pour activer une licence définitive
function activateDefinitiveLicense() {
    if (confirm('Voulez-vous activer une licence définitive ? Cette action est irréversible.')) {
        window.location.href = '<?= base_url('admin/configuration/activate-definitive-license') ?>';
    }
}

// Fonction pour afficher les informations de licence
function showLicenseInfo() {
    document.getElementById('licenseInfoModal').classList.add('is-active');
    loadLicenseInfo();
}

// Fonction pour fermer le modal des informations
function closeLicenseInfoModal() {
    document.getElementById('licenseInfoModal').classList.remove('is-active');
}

// Fonction pour charger les informations de licence
function loadLicenseInfo() {
    const content = `
        <div class="content">
            <h4>Détails Techniques de la Licence</h4>
            <table class="table is-fullwidth">
                <tbody>
                    <tr>
                        <td><strong>Clé de Licence :</strong></td>
                        <td><code><?= isset($license) ? $license['license_key'] : 'N/A' ?></code></td>
                    </tr>
                    <tr>
                        <td><strong>Client ID :</strong></td>
                        <td><?= isset($license) ? $license['client_id'] : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td><strong>Type :</strong></td>
                        <td><?= isset($license) ? strtoupper($license['license_type']) : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td><strong>Statut :</strong></td>
                        <td><?= isset($license) ? $license['status'] : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td><strong>Émission :</strong></td>
                        <td><?= isset($license) ? date('d/m/Y H:i', strtotime($license['issued_date'])) : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td><strong>Expiration :</strong></td>
                        <td><?= isset($license) ? ($license['expiry_date'] === '2099-12-31' ? 'PERMANENTE' : date('d/m/Y H:i', strtotime($license['expiry_date']))) : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td><strong>Dernière Mise à Jour :</strong></td>
                        <td><?= isset($license) ? date('d/m/Y H:i', strtotime($license['updated_at'])) : 'N/A' ?></td>
                    </tr>
                </tbody>
            </table>
            
            <h4>Informations Système</h4>
            <ul>
                <li><strong>Version du système :</strong> KISSAI SCHOOL v1.0</li>
                <li><strong>Version PHP :</strong> <?= PHP_VERSION ?></li>
                <li><strong>Version CodeIgniter :</strong> <?= \CodeIgniter\CodeIgniter::CI_VERSION ?></li>
                <li><strong>Serveur :</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></li>
                <li><strong>Base de données :</strong> MariaDB/MySQL</li>
            </ul>
        </div>
    `;
    
    document.getElementById('licenseInfoContent').innerHTML = content;
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

// Actualiser les informations de licence toutes les 60 secondes
setInterval(() => {
    checkLicenseValidity();
}, 60000);
</script>

<?= $this->endSection() ?>





