<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Nouvel Enseignant</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/enseignants') ?>" class="button is-info">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour</span>
            </a>
        </div>
    </div>
</div>

<div class="columns">
    <div class="column is-8">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-user-plus"></i></span>
                    Informations de l'Enseignant
                </p>
            </header>
            <div class="card-content">
                <form action="<?= base_url('admin/enseignants/store') ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <div class="columns">
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Prénom *</label>
                                <div class="control">
                                    <input class="input <?= session('errors.first_name') ? 'is-danger' : '' ?>" 
                                           type="text" 
                                           name="first_name" 
                                           value="<?= old('first_name') ?>" 
                                           placeholder="Prénom de l'enseignant"
                                           required>
                                </div>
                                <?php if (session('errors.first_name')): ?>
                                    <p class="help is-danger"><?= session('errors.first_name') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Nom *</label>
                                <div class="control">
                                    <input class="input <?= session('errors.last_name') ? 'is-danger' : '' ?>" 
                                           type="text" 
                                           name="last_name" 
                                           value="<?= old('last_name') ?>" 
                                           placeholder="Nom de l'enseignant"
                                           required>
                                </div>
                                <?php if (session('errors.last_name')): ?>
                                    <p class="help is-danger"><?= session('errors.last_name') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Email *</label>
                                <div class="control">
                                    <input class="input <?= session('errors.email') ? 'is-danger' : '' ?>" 
                                           type="email" 
                                           name="email" 
                                           value="<?= old('email') ?>" 
                                           placeholder="email@kissai-school.com"
                                           required>
                                </div>
                                <?php if (session('errors.email')): ?>
                                    <p class="help is-danger"><?= session('errors.email') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Téléphone</label>
                                <div class="control">
                                    <input class="input <?= session('errors.phone') ? 'is-danger' : '' ?>" 
                                           type="tel" 
                                           name="phone" 
                                           value="<?= old('phone') ?>" 
                                           placeholder="+237 6 XX XX XX XX">
                                </div>
                                <?php if (session('errors.phone')): ?>
                                    <p class="help is-danger"><?= session('errors.phone') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Spécialisation</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="specialization">
                                            <option value="">Sélectionner une spécialisation</option>
                                            <?php foreach ($specializations as $spec): ?>
                                                <option value="<?= $spec ?>" <?= old('specialization') === $spec ? 'selected' : '' ?>>
                                                    <?= $spec ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Qualification</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="qualification">
                                            <option value="">Sélectionner une qualification</option>
                                            <?php foreach ($qualifications as $qual): ?>
                                                <option value="<?= $qual ?>" <?= old('qualification') === $qual ? 'selected' : '' ?>>
                                                    <?= $qual ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Date d'embauche</label>
                                <div class="control">
                                    <input class="input <?= session('errors.hire_date') ? 'is-danger' : '' ?>" 
                                           type="date" 
                                           name="hire_date" 
                                           value="<?= old('hire_date') ?>">
                                </div>
                                <?php if (session('errors.hire_date')): ?>
                                    <p class="help is-danger"><?= session('errors.hire_date') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Compte Utilisateur</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="user_id">
                                            <option value="">Aucun compte utilisateur</option>
                                            <?php foreach ($users as $user): ?>
                                                <option value="<?= $user['id'] ?>" <?= old('user_id') == $user['id'] ? 'selected' : '' ?>>
                                                    <?= $user['username'] ?> (<?= $user['first_name'] . ' ' . $user['last_name'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <p class="help">Lier à un compte utilisateur existant pour l'accès au système</p>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <div class="control">
                            <button type="submit" class="button is-primary is-fullwidth">
                                <span class="icon"><i class="fas fa-save"></i></span>
                                <span>Créer l'Enseignant</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="column is-4">
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-info-circle"></i></span>
                    Informations
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <h5>Création d'un Enseignant</h5>
                    <p>Remplissez les informations de base de l'enseignant. Vous pourrez ensuite :</p>
                    <ul>
                        <li>Assigner des matières</li>
                        <li>Désigner comme responsable de classe</li>
                        <li>Configurer les permissions</li>
                        <li>Gérer les horaires</li>
                    </ul>
                    
                    <h6>Champs obligatoires :</h6>
                    <ul>
                        <li><strong>Prénom</strong> - Nom de famille</li>
                        <li><strong>Nom</strong> - Prénom</li>
                        <li><strong>Email</strong> - Adresse email unique</li>
                    </ul>
                    
                    <h6>Champs optionnels :</h6>
                    <ul>
                        <li><strong>Téléphone</strong> - Numéro de contact</li>
                        <li><strong>Spécialisation</strong> - Domaine d'expertise</li>
                        <li><strong>Qualification</strong> - Diplôme obtenu</li>
                        <li><strong>Date d'embauche</strong> - Date de début</li>
                        <li><strong>Compte utilisateur</strong> - Liaison avec un compte existant</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-lightbulb"></i></span>
                    Conseils
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <ul>
                        <li>Utilisez une adresse email professionnelle</li>
                        <li>Précisez la spécialisation pour faciliter l'assignation des matières</li>
                        <li>Liez à un compte utilisateur si l'enseignant doit accéder au système</li>
                        <li>La date d'embauche aide au calcul de l'ancienneté</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>




