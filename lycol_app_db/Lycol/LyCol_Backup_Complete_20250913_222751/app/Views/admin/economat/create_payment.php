<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <span class="icon"><i class="fas fa-plus-circle"></i></span>
                Nouveau Paiement
            </h1>
            <p class="subtitle">Enregistrer un nouveau paiement de scolarité</p>
        </div>
        <div class="column is-narrow">
            <a href="/admin/economat/payments" class="button is-info">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour à la liste</span>
            </a>
        </div>
    </div>

    <div class="box">
        <form action="/admin/economat/payments/store" method="POST">
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
                                    <option value="1">Lucas Dubois (2024CP001) - CP A</option>
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
                                    <option value="1">Frais de scolarité - 150,000 FCFA</option>
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
                            <input class="input" type="number" name="amount" placeholder="150000" min="0" step="100" required>
                        </div>
                        <p class="help">Montant effectivement payé par l'élève</p>
                    </div>
                </div>

                <div class="column">
                    <!-- Date de paiement -->
                    <div class="field">
                        <label class="label">Date de paiement *</label>
                        <div class="control">
                            <input class="input" type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required>
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
                                    <option value="CASH">Espèces</option>
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
                            <input class="input" type="text" name="reference" placeholder="REF-2024-001" maxlength="50">
                        </div>
                        <p class="help">Numéro de référence du paiement (optionnel)</p>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="field">
                <label class="label">Notes</label>
                <div class="control">
                    <textarea class="textarea" name="notes" placeholder="Informations supplémentaires sur ce paiement..." maxlength="500"></textarea>
                </div>
                <p class="help">Commentaires ou informations complémentaires</p>
            </div>

            <!-- Informations de calcul -->
            <div class="notification is-info is-light">
                <div class="columns">
                    <div class="column">
                        <p><strong>Montant total des frais :</strong> <span id="total-amount">0 FCFA</span></p>
                    </div>
                    <div class="column">
                        <p><strong>Montant payé :</strong> <span id="paid-amount">0 FCFA</span></p>
                    </div>
                    <div class="column">
                        <p><strong>Reste à payer :</strong> <span id="remaining-amount">0 FCFA</span></p>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="field is-grouped">
                <div class="control">
                    <button type="submit" class="button is-primary">
                        <span class="icon"><i class="fas fa-save"></i></span>
                        <span>Enregistrer le paiement</span>
                    </button>
                </div>
                <div class="control">
                    <a href="/admin/economat/payments" class="button is-light">
                        <span class="icon"><i class="fas fa-times"></i></span>
                        <span>Annuler</span>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Aide contextuelle -->
    <div class="box">
        <h3 class="title is-5">
            <span class="icon"><i class="fas fa-info-circle"></i></span>
            Aide pour l'enregistrement des paiements
        </h3>
        <div class="content">
            <ul>
                <li><strong>Élève :</strong> Sélectionnez l'élève concerné dans la liste déroulante</li>
                <li><strong>Type de frais :</strong> Choisissez le type de frais correspondant au paiement</li>
                <li><strong>Montant :</strong> Indiquez le montant effectivement payé (peut être partiel)</li>
                <li><strong>Date :</strong> La date à laquelle le paiement a été reçu</li>
                <li><strong>Méthode :</strong> Le moyen de paiement utilisé par l'élève</li>
                <li><strong>Référence :</strong> Numéro de référence pour le suivi (optionnel)</li>
                <li><strong>Notes :</strong> Informations complémentaires sur le paiement</li>
            </ul>
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
</script>

<?= $this->endSection() ?>


