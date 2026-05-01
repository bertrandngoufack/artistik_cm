<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <!-- Sélecteur d'année scolaire -->
    <div class="level mb-4">
        <div class="level-left">
            <div class="level-item">
                <h1 class="title">Module Économat</h1>
            </div>
        </div>
        <div class="level-right">
            <div class="level-item">
                <div class="field has-addons">
                    <div class="control">
                        <label class="label">Année Scolaire:</label>
                    </div>
                    <div class="control">
                        <div class="select">
                            <select id="academic-year-selector" onchange="changeAcademicYear(this.value)">
                                <?php foreach ($available_academic_years as $year): ?>
                                    <option value="<?= $year ?>" <?= ($year === $selected_academic_year) ? 'selected' : '' ?>>
                                        <?= $year ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations de l'année scolaire -->
    <div class="notification is-info is-light">
        <strong>Année scolaire:</strong> <?= $current_academic_year ?> 
        (<?= date('d/m/Y', strtotime($academic_year_start)) ?> - <?= date('d/m/Y', strtotime($academic_year_end)) ?>)
    </div>
    
    <!-- Statistiques générales -->
    <div class="columns is-multiline">
        <div class="column is-3">
            <div class="box has-background-primary has-text-white">
                <h4 class="title is-4 has-text-white">Total Recettes</h4>
                <p class="title is-2 has-text-white"><?= number_format($total_revenue ?? 0) ?> FCFA</p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-success has-text-white">
                <h4 class="title is-4 has-text-white">Paiements Reçus</h4>
                <p class="title is-2 has-text-white"><?= $paid_payments ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-warning has-text-white">
                <h4 class="title is-4 has-text-white">En Attente</h4>
                <p class="title is-2 has-text-white"><?= $pending_payments ?? 0 ?></p>
            </div>
        </div>
        <div class="column is-3">
            <div class="box has-background-danger has-text-white">
                <h4 class="title is-4 has-text-white">Retards</h4>
                <p class="title is-2 has-text-white"><?= $overdue_payments ?? 0 ?></p>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="buttons mb-4">
        <a href="/admin/economat/payments" class="button is-primary">
            <span class="icon"><i class="fas fa-money-bill"></i></span>
            <span>Gestion des Paiements</span>
        </a>
        <a href="/admin/economat/fees" class="button is-info">
            <span class="icon"><i class="fas fa-list"></i></span>
            <span>Types de Frais</span>
        </a>
        <a href="/admin/economat/reports" class="button is-success">
            <span class="icon"><i class="fas fa-chart-bar"></i></span>
            <span>Rapports</span>
        </a>
    </div>

    <!-- Derniers paiements -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">Derniers Paiements</p>
        </header>
        <div class="card-content">
            <table class="table is-fullwidth">
                <thead>
                    <tr>
                        <th>Élève</th>
                        <th>Type de Frais</th>
                        <th>Montant</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($recent_payments) && !empty($recent_payments)): ?>
                        <?php foreach ($recent_payments as $payment): ?>
                        <tr>
                            <td><?= $payment['student_name'] ?? 'N/A' ?></td>
                            <td><?= $payment['fee_type_name'] ?? 'N/A' ?></td>
                            <td><?= number_format($payment['amount_paid'] ?? 0) ?> FCFA</td>
                            <td><?= date('d/m/Y', strtotime($payment['payment_date'] ?? 'now')) ?></td>
                            <td>
                                <span class="tag is-success">
                                    Payé
                                </span>
                            </td>
                            <td>
                                <a href="/admin/economat/payments/<?= $payment['id'] ?? '0' ?>" class="button is-small is-info">
                                    <span class="icon"><i class="fas fa-eye"></i></span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="has-text-centered">Aucun paiement récent</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function changeAcademicYear(year) {
    // Construire l'URL avec le nouveau paramètre d'année scolaire
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('academic_year', year);
    window.location.href = currentUrl.toString();
}
</script>
<?= $this->endSection() ?>