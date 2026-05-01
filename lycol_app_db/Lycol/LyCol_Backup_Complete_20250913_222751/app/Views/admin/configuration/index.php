<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <!-- En-tête principal -->
    <div class="columns mb-4">
        <div class="column">
            <h1 class="title is-2 has-text-primary">
                <i class="fas fa-cogs"></i>
                Configuration du Système
            </h1>
            <p class="subtitle is-5">Gérez les paramètres et fournisseurs de KISSAI SCHOOL</p>
        </div>
        <div class="column is-3">
            <div class="notification is-info is-light">
                <div class="content">
                    <p class="has-text-centered">
                        <strong>Statut Système</strong><br>
                        <small>Dernière vérification : <?= date('H:i:s') ?></small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations de Licence -->
    <?php if (isset($license) && $license): ?>
    <div class="notification <?= $license['license_type'] === 'PERMANENT' ? 'is-success' : 'is-warning' ?> is-light mb-4">
        <div class="columns is-vcentered">
            <div class="column">
                <div class="content">
                    <p class="mb-1">
                        <strong>
                            <i class="fas fa-key"></i>
                            Licence <?= strtoupper($license['license_type']) ?>
                        </strong>
                    </p>
                    <p class="is-size-7 mb-1">
                        Clé : <?= $license['license_key'] ?> | 
                        Client : <?= $license['client_id'] ?> | 
                        Expiration : <?= $license['expiry_date'] ?>
                    </p>
                    <p class="is-size-7">
                        Statut : <span class="tag <?= $license['status'] === 'ACTIVE' ? 'is-success' : 'is-danger' ?> is-small">
                            <?= $license['status'] ?>
                        </span>
                    </p>
                </div>
            </div>
            <div class="column is-narrow">
                <a href="<?= base_url('admin/configuration/license') ?>" class="button is-small is-info">
                    <i class="fas fa-cog"></i>
                    Gérer
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Statistiques Système -->
    <?php if (isset($system_stats) && $system_stats): ?>
    <div class="columns mb-4">
        <div class="column is-3">
            <div class="box has-text-centered has-background-primary-light">
                <p class="heading">Étudiants</p>
                <p class="title is-4 has-text-primary"><?= $system_stats['students'] ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-text-centered has-background-info-light">
                <p class="heading">Enseignants</p>
                <p class="title is-4 has-text-info"><?= $system_stats['teachers'] ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-text-centered has-background-success-light">
                <p class="heading">Classes</p>
                <p class="title is-4 has-text-success"><?= $system_stats['classes'] ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-text-centered has-background-warning-light">
                <p class="heading">Utilisateurs</p>
                <p class="title is-4 has-text-warning"><?= $system_stats['users'] ?? 0 ?></p>
            </div>
        </div>
    </div>

    <!-- Informations Système -->
    <div class="columns mb-4">
        <div class="column is-6">
            <div class="box">
                <h4 class="title is-5">
                    <i class="fas fa-hdd"></i>
                    Espace Disque
                </h4>
                <div class="content">
                    <div class="level">
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Utilisé</p>
                                <p class="title is-6"><?= $system_stats['disk_usage']['used'] ?? 'N/A' ?></p>
                            </div>
                        </div>
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Libre</p>
                                <p class="title is-6"><?= $system_stats['disk_usage']['free'] ?? 'N/A' ?></p>
                            </div>
                        </div>
                        <div class="level-item has-text-centered">
                            <div>
                                <p class="heading">Total</p>
                                <p class="title is-6"><?= $system_stats['disk_usage']['total'] ?? 'N/A' ?></p>
                            </div>
                        </div>
                    </div>
                    <progress class="progress <?= ($system_stats['disk_usage']['percentage'] ?? 0) > 80 ? 'is-danger' : 'is-primary' ?>" 
                              value="<?= $system_stats['disk_usage']['percentage'] ?? 0 ?>" max="100">
                        <?= $system_stats['disk_usage']['percentage'] ?? 0 ?>%
                    </progress>
                </div>
            </div>
        </div>
        <div class="column is-6">
            <div class="box">
                <h4 class="title is-5">
                    <i class="fas fa-memory"></i>
                    Mémoire Système
                </h4>
                <div class="content">
                    <p><strong>Limite :</strong> <?= $system_stats['memory_usage']['limit'] ?? 'N/A' ?></p>
                    <p><strong>Utilisation :</strong> <?= $system_stats['memory_usage']['usage'] ?? 'N/A' ?></p>
                    <p><strong>Pic :</strong> <?= $system_stats['memory_usage']['peak'] ?? 'N/A' ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Versions -->
    <div class="columns mb-4">
        <div class="column is-6">
            <div class="notification is-light">
                <p><strong>PHP :</strong> <?= $system_stats['php_version'] ?? 'N/A' ?></p>
                <p><strong>CodeIgniter :</strong> <?= $system_stats['ci_version'] ?? 'N/A' ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modules de Configuration -->
    <div class="columns is-multiline">
        <!-- Gestion de Licence -->
        <div class="column is-4">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fas fa-key" style="font-size: 2rem; color: #ff6b35;"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-4">Licence</p>
                            <p class="subtitle is-6">Gestion des licences système</p>
                        </div>
                    </div>
                    <div class="content">
                        <p>Configurez et gérez les licences de l'application.</p>
                        <a href="<?= base_url('admin/configuration/license') ?>" class="button is-primary is-fullwidth">
                            <i class="fas fa-cog"></i>
                            Configurer
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paramètres Généraux -->
        <div class="column is-4">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fas fa-sliders-h" style="font-size: 2rem; color: #4ecdc4;"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-4">Général</p>
                            <p class="subtitle is-6">Paramètres généraux</p>
                        </div>
                    </div>
                    <div class="content">
                        <p>Configurez les paramètres généraux de l'application.</p>
                        <a href="<?= base_url('admin/configuration/general') ?>" class="button is-info is-fullwidth">
                            <i class="fas fa-cog"></i>
                            Configurer
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Apparence -->
        <div class="column is-4">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fas fa-palette" style="font-size: 2rem; color: #45b7d1;"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-4">Apparence</p>
                            <p class="subtitle is-6">Personnalisation de l'interface</p>
                        </div>
                    </div>
                    <div class="content">
                        <p>Personnalisez l'apparence de l'interface utilisateur.</p>
                        <a href="<?= base_url('admin/configuration/appearance') ?>" class="button is-success is-fullwidth">
                            <i class="fas fa-cog"></i>
                            Configurer
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Email -->
        <div class="column is-4">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fas fa-envelope" style="font-size: 2rem; color: #96ceb4;"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-4">Email</p>
                            <p class="subtitle is-6">Configuration des emails</p>
                        </div>
                    </div>
                    <div class="content">
                        <p>Configurez les fournisseurs de services email.</p>
                        <a href="<?= base_url('admin/configuration/email') ?>" class="button is-warning is-fullwidth">
                            <i class="fas fa-cog"></i>
                            Configurer
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration SMS -->
        <div class="column is-4">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fas fa-sms" style="font-size: 2rem; color: #feca57;"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-4">SMS</p>
                            <p class="subtitle is-6">Configuration des SMS</p>
                        </div>
                    </div>
                    <div class="content">
                        <p>Configurez les fournisseurs de services SMS.</p>
                        <a href="<?= base_url('admin/configuration/sms') ?>" class="button is-danger is-fullwidth">
                            <i class="fas fa-cog"></i>
                            Configurer
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration WhatsApp -->
        <div class="column is-4">
            <div class="card">
                <div class="card-content">
                    <div class="media">
                        <div class="media-left">
                            <figure class="image is-48x48">
                                <i class="fab fa-whatsapp" style="font-size: 2rem; color: #25d366;"></i>
                            </figure>
                        </div>
                        <div class="media-content">
                            <p class="title is-4">WhatsApp</p>
                            <p class="subtitle is-6">Configuration WhatsApp</p>
                        </div>
                    </div>
                    <div class="content">
                        <p>Configurez l'intégration WhatsApp Business.</p>
                        <a href="<?= base_url('admin/configuration/whatsapp') ?>" class="button is-success is-fullwidth">
                            <i class="fas fa-cog"></i>
                            Configurer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="columns mt-4">
        <div class="column">
            <div class="box">
                <h4 class="title is-5">
                    <i class="fas fa-tools"></i>
                    Actions Rapides
                </h4>
                <div class="buttons">
                    <a href="<?= base_url('admin/configuration/diagnostics') ?>" class="button is-info">
                        <i class="fas fa-stethoscope"></i>
                        Diagnostics
                    </a>
                    <a href="<?= base_url('admin/configuration/backup') ?>" class="button is-warning">
                        <i class="fas fa-download"></i>
                        Sauvegarde
                    </a>
                    <a href="<?= base_url('admin/configuration/logs') ?>" class="button is-dark">
                        <i class="fas fa-file-alt"></i>
                        Logs
                    </a>
                    <button onclick="clearCache()" class="button is-danger">
                        <i class="fas fa-broom"></i>
                        Vider le Cache
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function clearCache() {
    if (confirm('Êtes-vous sûr de vouloir vider le cache ?')) {
        fetch('<?= base_url('admin/configuration/clear-cache') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cache vidé avec succès !');
                location.reload();
            } else {
                alert('Erreur lors du vidage du cache : ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du vidage du cache');
        });
    }
}

// Actualisation automatique des statistiques
setInterval(function() {
    fetch('<?= base_url('admin/configuration/system-stats-api') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.students !== undefined) {
                document.querySelector('.title.is-4.has-text-primary').textContent = data.students;
            }
            if (data.teachers !== undefined) {
                document.querySelector('.title.is-4.has-text-info').textContent = data.teachers;
            }
            if (data.classes !== undefined) {
                document.querySelector('.title.is-4.has-text-success').textContent = data.classes;
            }
            if (data.users !== undefined) {
                document.querySelector('.title.is-4.has-text-warning').textContent = data.users;
            }
        })
        .catch(error => console.error('Erreur actualisation:', error));
}, 30000); // Actualisation toutes les 30 secondes
</script>

<?= $this->endSection() ?>


