<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <span class="icon"><i class="fas fa-edit"></i></span>
                Modifier le Paiement
            </h1>
            <p class="subtitle">Modifier les informations d'un paiement existant</p>
        </div>
        <div class="column is-narrow">
            <a href="/admin/economat/payments" class="button is-info">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour à la liste</span>
            </a>
        </div>
    </div>

    <div class="box">
        <form action="/admin/economat/payments/update/1" method="POST">
            <?= csrf_field() ?>
            
            <div class="columns">
                <div class="column">
                    <!-- Informations de l'élève -->
                    <div class="field">
                        <label class="label">Élève *</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="student_id" required>
                                    <option value="">Sélectionner un élève</option>
                                    <option value="1" selected>Lucas Dubois (2024CP001) - CP A</option>
                                    <option value="2">Emma Leroy (2024CP002) - CP A</option>
                                    <option value="3">Hugo Moreau (2024CP003) - CP A</option>
                                    <option value="4">Chloé Simon (2024CE1001) - CE1 A</option>
                                    <option value="5">Thomas Michel (2024CE1002) - CE1 A</option>
                                    <option value="6">Léa Garcia (2024CE1003) - CE1 A</option>
                                    <option value="7">Jules David (2024CM1001) - CM1 A</option>
                                    <option value="8">Alice Bertrand (2024CM1002) - CM1 A</option>
                                    <option value="9">Louis Roux (2024CM1003) - CM1 A</option>
                                    <option value="10">Eva Vincent (20246E001) - 6ème A</option>
                                    <option value="11">Nathan Moulin (20246E002) - 6ème A</option>
                                    <option value="12">Jade Andre (20246E003) - 6ème A</option>
                                </select>
                            </div>
                        </div>
                        <p class="help">Sélectionnez l'élève concerné par ce paiement</p>
                    </div>

                    <!-- Type de frais -->
                    <div class="field">
                        <label class="label">Type de frais *</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="fee_type_id" required>
                                    <option value="">Sélectionner le type de frais</option>
                                    <option value="1" selected>Frais de scolarité - 150,000 FCFA</option>
                                    <option value="2">Frais d'inscription - 50,000 FCFA</option>
                                    <option value="3">Frais de cantine - 25,000 FCFA</option>
                                    <option value="4">Frais de transport - 30,000 FCFA</option>
                                    <option value="5">Frais de laboratoire - 15,000 FCFA</option>
                                </select>
                            </div>
                        </div>
                        <p class="help">Choisissez le type de frais à payer</p>
                    </div>

                    <!-- Montant -->
                    <div class="field">
                        <label class="label">Montant payé (FCFA) *</label>
                        <div class="control">
                            <input class="input" type="number" name="amount" value="150000" min="0" step="100" required>
                        </div>
                        <p class="help">Montant effectivement payé par l'élève</p>
                    </div>
                </div>

                <div class="column">
                    <!-- Date de paiement -->
                    <div class="field">
                        <label class="label">Date de paiement *</label>
                        <div class="control">
                            <input class="input" type="date" name="payment_date" value="2024-09-15" required>
                        </div>
                        <p class="help">Date à laquelle le paiement a été effectué</p>
                    </div>

                    <!-- Méthode de paiement -->
                    <div class="field">
                        <label class="label">Méthode de paiement *</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="payment_method" required>
                                    <option value="">Sélectionner la méthode</option>
                                    <option value="CASH" selected>Espèces</option>
                                    <option value="CARD">Carte bancaire</option>
                                    <option value="TRANSFER">Virement bancaire</option>
                                    <option value="CHECK">Chèque</option>
                                    <option value="MOBILE_MONEY">Mobile Money</option>
                                </select>
                            </div>
                        </div>
                        <p class="help">Moyen de paiement utilisé</p>
                    </div>

                    <!-- Référence -->
                    <div class="field">
                        <label class="label">Numéro de référence</label>
                        <div class="control">
                            <input class="input" type="text" name="reference" value="PAY-2024-001" maxlength="50">
                        </div>
                        <p class="help">Numéro de référence du paiement (optionnel)</p>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="field">
                <label class="label">Notes</label>
                <div class="control">
                    <textarea class="textarea" name="notes" placeholder="Informations supplémentaires sur ce paiement..." maxlength="500">Paiement complet</textarea>
                </div>
                <p class="help">Commentaires ou informations complémentaires</p>
            </div>

            <!-- Informations de calcul -->
            <div class="notification is-info is-light">
                <div class="columns">
                    <div class="column">
                        <p><strong>Montant total des frais :</strong> <span id="total-amount">150,000 FCFA</span></p>
                    </div>
                    <div class="column">
                        <p><strong>Montant payé :</strong> <span id="paid-amount">150,000 FCFA</span></p>
                    </div>
                    <div class="column">
                        <p><strong>Reste à payer :</strong> <span id="remaining-amount">0 FCFA</span></p>
                    </div>
                </div>
            </div>

            <!-- Informations du paiement -->
            <div class="notification is-warning is-light">
                <div class="columns">
                    <div class="column">
                        <p><strong>ID du paiement :</strong> 1</p>
                    </div>
                    <div class="column">
                        <p><strong>Date de création :</strong> 15/09/2024</p>
                    </div>
                    <div class="column">
                        <p><strong>Dernière modification :</strong> 15/09/2024</p>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="field is-grouped">
                <div class="control">
                    <button type="submit" class="button is-primary">
                        <span class="icon"><i class="fas fa-save"></i></span>
                        <span>Mettre à jour le paiement</span>
                    </button>
                </div>
                <div class="control">
                    <a href="/admin/economat/payments" class="button is-light">
                        <span class="icon"><i class="fas fa-times"></i></span>
                        <span>Annuler</span>
                    </a>
                </div>
                <div class="control">
                    <button type="button" class="button is-danger" onclick="confirmDelete()">
                        <span class="icon"><i class="fas fa-trash"></i></span>
                        <span>Supprimer</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Historique des modifications -->
    <div class="box">
        <h3 class="title is-5">
            <span class="icon"><i class="fas fa-history"></i></span>
            Historique des modifications
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
                        <td>15/09/2024 10:30</td>
                        <td><span class="tag is-success">Création</span></td>
                        <td>admin</td>
                        <td>Paiement initial enregistré</td>
                    </tr>
                    <tr>
                        <td>15/09/2024 14:15</td>
                        <td><span class="tag is-info">Modification</span></td>
                        <td>admin</td>
                        <td>Mise à jour de la référence</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Calcul automatique des montants
document.addEventListener('DOMContentLoaded', function() {
    const feeTypeSelect = document.querySelector('select[name="fee_type_id"]');
    const amountInput = document.querySelector('input[name="amount"]');
    const totalAmountSpan = document.getElementById('total-amount');
    const paidAmountSpan = document.getElementById('paid-amount');
    const remainingAmountSpan = document.getElementById('remaining-amount');

    const feeAmounts = {
        '1': 150000, // Frais de scolarité
        '2': 50000,  // Frais d'inscription
        '3': 25000,  // Frais de cantine
        '4': 30000,  // Frais de transport
        '5': 15000   // Frais de laboratoire
    };

    function updateAmounts() {
        const selectedFeeType = feeTypeSelect.value;
        const paidAmount = parseFloat(amountInput.value) || 0;
        const totalAmount = feeAmounts[selectedFeeType] || 0;
        const remainingAmount = totalAmount - paidAmount;

        totalAmountSpan.textContent = totalAmount.toLocaleString() + ' FCFA';
        paidAmountSpan.textContent = paidAmount.toLocaleString() + ' FCFA';
        remainingAmountSpan.textContent = remainingAmount.toLocaleString() + ' FCFA';

        // Mettre à jour la couleur du reste à payer
        if (remainingAmount > 0) {
            remainingAmountSpan.style.color = '#ff3860';
        } else if (remainingAmount < 0) {
            remainingAmountSpan.style.color = '#23d160';
        } else {
            remainingAmountSpan.style.color = '#3273dc';
        }
    }

    feeTypeSelect.addEventListener('change', updateAmounts);
    amountInput.addEventListener('input', updateAmounts);

    // Initialiser les calculs
    updateAmounts();
});

// Confirmation de suppression
function confirmDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce paiement ? Cette action est irréversible.')) {
        window.location.href = '/admin/economat/payments/delete/1';
    }
}
</script>

<?= $this->endSection() ?>


