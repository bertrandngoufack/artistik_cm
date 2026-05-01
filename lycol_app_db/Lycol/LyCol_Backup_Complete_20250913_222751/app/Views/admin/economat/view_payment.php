<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <span class="icon"><i class="fas fa-eye"></i></span>
                Détails du Paiement
            </h1>
            <p class="subtitle">Informations complètes sur le paiement #1</p>
        </div>
                    <div class="column is-narrow">
                <div class="buttons">
                    <a href="/admin/economat/payments/1/edit" class="button is-warning">
                        <span class="icon"><i class="fas fa-edit"></i></span>
                        <span>Modifier</span>
                    </a>
                    <a href="/admin/economat/payments" class="button is-info">
                        <span class="icon"><i class="fas fa-arrow-left"></i></span>
                        <span>Retour à la liste</span>
                    </a>
                    <a href="/admin/economat/payments/1/print" class="button is-success" target="_blank">
                        <span class="icon"><i class="fas fa-print"></i></span>
                        <span>Imprimer Reçu</span>
                    </a>
                    <a href="/admin/economat/payments/1/pdf" class="button is-danger">
                        <span class="icon"><i class="fas fa-file-pdf"></i></span>
                        <span>Exporter PDF</span>
                    </a>
                </div>
            </div>
    </div>

    <!-- Informations principales -->
    <div class="columns">
        <div class="column">
            <div class="box">
                <h3 class="title is-4">
                    <span class="icon"><i class="fas fa-info-circle"></i></span>
                    Informations du Paiement
                </h3>
                
                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">Référence</label>
                            <p class="title is-5">PAY-2024-001</p>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Statut</label>
                            <span class="tag is-success is-large">Payé</span>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Date de paiement</label>
                            <p class="title is-5">15/09/2024</p>
                        </div>
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">Montant payé</label>
                            <p class="title is-4 has-text-success">150,000 FCFA</p>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Méthode de paiement</label>
                            <span class="tag is-info is-medium">Espèces</span>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Type de frais</label>
                            <p class="title is-5">Frais de scolarité</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations de l'élève -->
    <div class="columns">
        <div class="column">
            <div class="box">
                <h3 class="title is-4">
                    <span class="icon"><i class="fas fa-user-graduate"></i></span>
                    Informations de l'Élève
                </h3>
                
                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">Nom complet</label>
                            <p class="title is-5">Lucas Dubois</p>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Matricule</label>
                            <p class="title is-5">2024CP001</p>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Classe</label>
                            <p class="title is-5">CP A</p>
                        </div>
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">Date de naissance</label>
                            <p class="title is-6">15/03/2018</p>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Genre</label>
                            <p class="title is-6">Masculin</p>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Statut</label>
                            <span class="tag is-success">Actif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails du type de frais -->
    <div class="columns">
        <div class="column">
            <div class="box">
                <h3 class="title is-4">
                    <span class="icon"><i class="fas fa-tags"></i></span>
                    Détails du Type de Frais
                </h3>
                
                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">Nom du type de frais</label>
                            <p class="title is-5">Frais de scolarité</p>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Montant total</label>
                            <p class="title is-5">150,000 FCFA</p>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Fréquence</label>
                            <span class="tag is-info">Annuel</span>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Description</label>
                    <p class="content">Frais de scolarité annuels pour l'année académique <?= $current_academic_year ?? '2024-2024' ?>. Ce montant couvre les frais d'enseignement, les fournitures scolaires et les activités pédagogiques.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations administratives -->
    <div class="columns">
        <div class="column">
            <div class="box">
                <h3 class="title is-4">
                    <span class="icon"><i class="fas fa-cog"></i></span>
                    Informations Administratives
                </h3>
                
                <div class="columns">
                    <div class="column">
                        <div class="field">
                            <label class="label">Enregistré par</label>
                            <p class="title is-6">admin</p>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Date de création</label>
                            <p class="title is-6">15/09/2024 10:30:15</p>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <label class="label">Dernière modification</label>
                            <p class="title is-6">15/09/2024 14:15:22</p>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Notes</label>
                    <p class="content">Paiement complet effectué en espèces. Reçu délivré au parent. Aucun problème signalé.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Historique des modifications -->
    <div class="columns">
        <div class="column">
            <div class="box">
                <h3 class="title is-4">
                    <span class="icon"><i class="fas fa-history"></i></span>
                    Historique des Modifications
                </h3>
                
                <div class="table-container">
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                                <th>Utilisateur</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>15/09/2024 10:30:15</td>
                                <td><span class="tag is-success">Création</span></td>
                                <td>admin</td>
                                <td>Paiement initial enregistré</td>
                            </tr>
                            <tr>
                                <td>15/09/2024 14:15:22</td>
                                <td><span class="tag is-info">Modification</span></td>
                                <td>admin</td>
                                <td>Mise à jour de la référence et ajout de notes</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="box">
        <div class="columns">
            <div class="column">
                <div class="buttons">
                    <a href="/admin/economat/payments/1/edit" class="button is-warning">
                        <span class="icon"><i class="fas fa-edit"></i></span>
                        <span>Modifier ce paiement</span>
                    </a>
                    <button class="button is-danger" onclick="confirmDelete()">
                        <span class="icon"><i class="fas fa-trash"></i></span>
                        <span>Supprimer</span>
                    </button>
                </div>
            </div>
            <div class="column is-narrow">
                <div class="buttons">
                    <a href="/admin/economat/payments/1/print" class="button is-success" target="_blank">
                        <span class="icon"><i class="fas fa-print"></i></span>
                        <span>Imprimer le reçu</span>
                    </a>
                    <a href="/admin/economat/payments/1/pdf" class="button is-danger">
                        <span class="icon"><i class="fas fa-file-pdf"></i></span>
                        <span>Exporter PDF</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Fonction pour confirmer la suppression
function confirmDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce paiement ? Cette action est irréversible.')) {
        window.location.href = '/admin/economat/payments/delete/1';
    }
}

// Ajouter des styles pour l'impression
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        @media print {
            .buttons, .column.is-narrow {
                display: none !important;
            }
            .box {
                border: 1px solid #ccc !important;
                margin-bottom: 20px !important;
            }
        }
    `;
    document.head.appendChild(style);
});
</script>

<?= $this->endSection() ?>
