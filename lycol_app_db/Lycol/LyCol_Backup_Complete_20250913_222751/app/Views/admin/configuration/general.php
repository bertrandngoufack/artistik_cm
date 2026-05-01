<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/configuration') ?>">Configuration</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Paramètres Généraux</a></li>
                </ul>
            </nav>
            
            <h1 class="title">
                <i class="fas fa-school"></i>
                Paramètres Généraux
            </h1>
            <p class="subtitle">Configurez les informations de base de KISSAI SCHOOL</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="notification is-success">
            <button class="delete" onclick="this.parentElement.remove()"></button>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="notification is-danger">
            <button class="delete" onclick="this.parentElement.remove()"></button>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="columns">
        <div class="column is-8">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-info-circle"></i>
                        Informations de l'Établissement
                    </p>
                </div>
                <div class="card-content">
                    <form action="<?= base_url('admin/configuration/save-general') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Nom de l'Établissement *</label>
                                    <div class="control">
                                        <input class="input" type="text" name="school_name" value="<?= old('school_name', 'KISSAI SCHOOL') ?>" placeholder="Nom de l'établissement" required>
                                    </div>
                                    <?php if (session()->getFlashdata('errors.school_name')): ?>
                                        <p class="help is-danger"><?= session()->getFlashdata('errors.school_name') ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Année Académique *</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="academic_year" required>
                                                <option value="<?= $current_academic_year ?? '2024-2025' ?>" <?= old('academic_year') === ($current_academic_year ?? '2024-2025') ? 'selected' : '' ?>><?= $current_academic_year ?? '2024-2025' ?></option>
                                                <option value="2025-2026" <?= old('academic_year') === '2025-2026' ? 'selected' : '' ?>>2025-2026</option>
                                                <option value="2026-2027" <?= old('academic_year') === '2026-2027' ? 'selected' : '' ?>>2026-2027</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Adresse Complète *</label>
                            <div class="control">
                                <textarea class="textarea" name="school_address" placeholder="Adresse complète de l'établissement" required><?= old('school_address', 'Douala, Cameroun') ?></textarea>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Téléphone *</label>
                                    <div class="control">
                                        <input class="input" type="tel" name="school_phone" value="<?= old('school_phone', '+237 XXX XXX XXX') ?>" placeholder="+237 XXX XXX XXX" required>
                                    </div>
                                </div>
                            </div>
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Email *</label>
                                    <div class="control">
                                        <input class="input" type="email" name="school_email" value="<?= old('school_email', 'contact@kissai-school.cm') ?>" placeholder="contact@kissai-school.cm" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Site Web</label>
                                    <div class="control">
                                        <input class="input" type="url" name="school_website" value="<?= old('school_website', 'https://www.kissai-school.cm') ?>" placeholder="https://www.kissai-school.cm">
                                    </div>
                                </div>
                            </div>
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Devise *</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="currency" required>
                                                <option value="FCFA" <?= old('currency') === 'FCFA' ? 'selected' : '' ?>>FCFA (Franc CFA)</option>
                                                <option value="USD" <?= old('currency') === 'USD' ? 'selected' : '' ?>>USD (Dollar US)</option>
                                                <option value="EUR" <?= old('currency') === 'EUR' ? 'selected' : '' ?>>EUR (Euro)</option>
                                                <option value="XAF" <?= old('currency') === 'XAF' ? 'selected' : '' ?>>XAF (Franc CFA BEAC)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Fuseau Horaire *</label>
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select name="timezone" required>
                                        <option value="Africa/Douala" <?= old('timezone') === 'Africa/Douala' ? 'selected' : '' ?>>Afrique/Douala (UTC+1)</option>
                                        <option value="Africa/Lagos" <?= old('timezone') === 'Africa/Lagos' ? 'selected' : '' ?>>Afrique/Lagos (UTC+1)</option>
                                        <option value="Africa/Kinshasa" <?= old('timezone') === 'Africa/Kinshasa' ? 'selected' : '' ?>>Afrique/Kinshasa (UTC+1)</option>
                                        <option value="Europe/Paris" <?= old('timezone') === 'Europe/Paris' ? 'selected' : '' ?>>Europe/Paris (UTC+1/+2)</option>
                                        <option value="UTC" <?= old('timezone') === 'UTC' ? 'selected' : '' ?>>UTC (Temps universel)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <i class="fas fa-save"></i>
                                    Sauvegarder
                                </button>
                            </div>
                            <div class="control">
                                <a href="<?= base_url('admin/configuration') ?>" class="button is-light">
                                    <i class="fas fa-arrow-left"></i>
                                    Retour
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="column is-4">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-lightbulb"></i>
                        Informations
                    </p>
                </div>
                <div class="card-content">
                    <div class="content">
                        <h4>Paramètres Généraux</h4>
                        <p>Ces paramètres définissent les informations de base de votre établissement scolaire.</p>
                        
                        <h5>Champs obligatoires :</h5>
                        <ul>
                            <li><strong>Nom de l'établissement</strong> : Nom officiel de votre école</li>
                            <li><strong>Année académique</strong> : Année scolaire en cours</li>
                            <li><strong>Adresse</strong> : Adresse complète de l'établissement</li>
                            <li><strong>Téléphone</strong> : Numéro de contact principal</li>
                            <li><strong>Email</strong> : Adresse email de contact</li>
                            <li><strong>Devise</strong> : Monnaie utilisée pour les paiements</li>
                            <li><strong>Fuseau horaire</strong> : Zone horaire de l'établissement</li>
                        </ul>

                        <h5>Champs optionnels :</h5>
                        <ul>
                            <li><strong>Site Web</strong> : URL du site officiel de l'école</li>
                        </ul>

                        <div class="notification is-info is-light">
                            <p><strong>Note :</strong> Ces informations seront utilisées dans tous les documents générés par le système (reçus, rapports, etc.).</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>


