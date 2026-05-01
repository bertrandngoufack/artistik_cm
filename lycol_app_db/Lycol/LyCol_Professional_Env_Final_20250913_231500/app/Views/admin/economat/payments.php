<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <h1 class="title">
                <span class="icon"><i class="fas fa-money-bill-wave"></i></span>
                Gestion des Paiements
            </h1>
            <p class="subtitle">Gérez tous les paiements de scolarité</p>
        </div>
        <div class="column is-narrow">
            <div class="buttons">
                <a href="/admin/economat/payments/create" class="button is-primary">
                    <span class="icon"><i class="fas fa-plus"></i></span>
                    <span>Nouveau Paiement</span>
                </a>
                <a href="/admin/economat/payments/send-reminders" class="button is-warning" onclick="return confirm('Envoyer des rappels à tous les parents en retard de paiement ?')">
                    <span class="icon"><i class="fas fa-bell"></i></span>
                    <span>Envoyer Rappels</span>
                </a>
                <a href="/admin/economat/reminders" class="button is-info">
                    <span class="icon"><i class="fas fa-history"></i></span>
                    <span>Historique Rappels</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Informations de l'année scolaire -->
    <div class="notification is-info is-light mb-4">
        <strong>Année scolaire:</strong> <?= $current_academic_year ?> 
        (<?= date('d/m/Y', strtotime($academic_year_start)) ?> - <?= date('d/m/Y', strtotime($academic_year_end)) ?>)
    </div>

    <!-- Filtres -->
    <div class="box">
        <form method="GET" action="/admin/economat/payments">
            <!-- Sélecteur d'année scolaire -->
            <div class="field mb-4">
                <label class="label">Année Scolaire</label>
                <div class="control">
                    <div class="select">
                        <select name="academic_year" onchange="this.form.submit()">
                            <?php foreach ($available_academic_years as $year): ?>
                                <option value="<?= $year ?>" <?= ($year === $selected_academic_year) ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label class="label">Élève</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="student_id">
                                    <option value="">Tous les élèves</option>
                                    <?php if (isset($students) && !empty($students)): ?>
                                        <?php foreach ($students as $student): ?>
                                            <option value="<?= $student['id'] ?>" <?= (isset($_GET['student_id']) && $_GET['student_id'] == $student['id']) ? 'selected' : '' ?>>
                                                <?= $student['first_name'] . ' ' . $student['last_name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label">Type de frais</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="fee_type_id">
                                    <option value="">Tous les types</option>
                                    <?php if (isset($feeTypes) && !empty($feeTypes)): ?>
                                        <?php foreach ($feeTypes as $feeType): ?>
                                            <option value="<?= $feeType['id'] ?>" <?= (isset($_GET['fee_type_id']) && $_GET['fee_type_id'] == $feeType['id']) ? 'selected' : '' ?>>
                                                <?= $feeType['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="field">
                        <label class="label">Statut</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="paid" <?= (isset($_GET['status']) && $_GET['status'] == 'paid') ? 'selected' : '' ?>>Payé</option>
                                    <option value="pending" <?= (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : '' ?>>En attente</option>
                                    <option value="overdue" <?= (isset($_GET['status']) && $_GET['status'] == 'overdue') ? 'selected' : '' ?>>En retard</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column is-narrow">
                    <div class="field">
                        <label class="label">&nbsp;</label>
                        <div class="control">
                            <button type="submit" class="button is-info">
                                <span class="icon"><i class="fas fa-search"></i></span>
                                <span>Filtrer</span>
                            </button>
                            <a href="/admin/economat/payments?academic_year=<?= $selected_academic_year ?>" class="button is-light">
                                <span class="icon"><i class="fas fa-times"></i></span>
                                <span>Réinitialiser</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Statistiques -->
    <div class="columns">
        <div class="column">
            <div class="box has-text-centered">
                <p class="heading">Total Recettes</p>
                <p class="title has-text-success"><?= number_format($total_revenue ?? 0, 0, ',', ' ') ?> FCFA</p>
            </div>
        </div>
        <div class="column">
            <div class="box has-text-centered">
                <p class="heading">Paiements Payés</p>
                <p class="title has-text-info"><?= number_format($paid_payments ?? 0, 0, ',', ' ') ?></p>
            </div>
        </div>
        <div class="column">
            <div class="box has-text-centered">
                <p class="heading">Paiements En Attente</p>
                <p class="title has-text-warning"><?= number_format($pending_payments ?? 0, 0, ',', ' ') ?></p>
            </div>
        </div>
        <div class="column">
            <div class="box has-text-centered">
                <p class="heading">Paiements En Retard</p>
                <p class="title has-text-danger"><?= number_format($overdue_payments ?? 0, 0, ',', ' ') ?></p>
            </div>
        </div>
    </div>

    <!-- Liste des paiements -->
    <div class="box">
        <div class="table-container">
            <table class="table is-fullwidth is-striped is-hoverable">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Élève</th>
                        <th>Type de frais</th>
                        <th>Montant</th>
                        <th>Date de paiement</th>
                        <th>Méthode</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($payments) && !empty($payments)): ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><strong><?= $payment['reference_number'] ?? 'PAY-' . str_pad($payment['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><?= $payment['student_name'] ?? 'N/A' ?></td>
                                <td><?= $payment['fee_type_name'] ?? 'N/A' ?></td>
                                <td><?= number_format($payment['amount_paid'] ?? 0, 0, ',', ' ') ?> FCFA</td>
                                <td><?= date('d/m/Y', strtotime($payment['payment_date'] ?? 'now')) ?></td>
                                <td>
                                    <span class="tag <?= $payment['payment_method'] === 'CASH' ? 'is-success' : ($payment['payment_method'] === 'BANK_TRANSFER' ? 'is-info' : 'is-warning') ?>">
                                        <?= $payment['payment_method'] === 'CASH' ? 'Espèces' : 
                                            ($payment['payment_method'] === 'BANK_TRANSFER' ? 'Virement' : 
                                            ($payment['payment_method'] === 'MOBILE_MONEY' ? 'Mobile' : 'Carte')) ?>
                                    </span>
                                </td>
                                <td><span class="tag is-success">Payé</span></td>
                                <td>
                                    <div class="buttons are-small">
                                        <a href="/admin/economat/payments/<?= $payment['id'] ?>" class="button is-info">
                                            <span class="icon"><i class="fas fa-eye"></i></span>
                                        </a>
                                        <a href="/admin/economat/payments/<?= $payment['id'] ?>/edit" class="button is-warning">
                                            <span class="icon"><i class="fas fa-edit"></i></span>
                                        </a>
                                        <a href="/admin/economat/payments/<?= $payment['id'] ?>/reminder" class="button is-warning" 
                                           onclick="return confirm('Envoyer un rappel pour ce paiement ?')" 
                                           title="Envoyer un rappel">
                                            <span class="icon"><i class="fas fa-bell"></i></span>
                                        </a>
                                        <button class="button is-danger" onclick="confirmDelete(<?= $payment['id'] ?>)">
                                            <span class="icon"><i class="fas fa-trash"></i></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="has-text-centered">
                                <p class="has-text-grey">Aucun paiement trouvé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (isset($payments) && count($payments) > 0): ?>
            <nav class="pagination is-centered" role="navigation" aria-label="pagination">
                <a class="pagination-previous" <?= (isset($_GET['page']) && $_GET['page'] <= 1) ? 'disabled' : '' ?>>
                    Précédent
                </a>
                <a class="pagination-next" <?= (isset($_GET['page']) && $_GET['page'] >= 5) ? 'disabled' : '' ?>>
                    Suivant
                </a>
                <ul class="pagination-list">
                    <?php 
                    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $totalPages = 5; // Simulé pour l'exemple
                    ?>
                    <?php for ($i = 1; $i <= min($totalPages, 5); $i++): ?>
                        <li>
                            <a class="pagination-link <?= $i === $currentPage ? 'is-current' : '' ?>" 
                               aria-current="<?= $i === $currentPage ? 'page' : '' ?>"
                               href="?page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($totalPages > 5): ?>
                        <li><span class="pagination-ellipsis">&hellip;</span></li>
                        <li><a class="pagination-link" href="?page=<?= $totalPages ?>"><?= $totalPages ?></a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <!-- Actions en lot -->
    <div class="box">
        <div class="columns">
            <div class="column">
                <div class="field">
                    <div class="control">
                        <label class="checkbox">
                            <input type="checkbox" id="select-all">
                            Sélectionner tout
                        </label>
                    </div>
                </div>
            </div>
            <div class="column is-narrow">
                <div class="buttons">
                    <button class="button is-success">
                        <span class="icon"><i class="fas fa-download"></i></span>
                        <span>Exporter CSV</span>
                    </button>
                    <button class="button is-info">
                        <span class="icon"><i class="fas fa-print"></i></span>
                        <span>Imprimer</span>
                    </button>
                    <button class="button is-warning">
                        <span class="icon"><i class="fas fa-envelope"></i></span>
                        <span>Envoyer rappel</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});
</script>

<?= $this->endSection() ?>
