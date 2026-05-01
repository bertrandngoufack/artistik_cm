<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column is-12">
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h1 class="title"><?= $title ?></h1>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <a href="<?= base_url('admin/securite/users') ?>" class="button is-info">
                            <span class="icon"><i class="fas fa-arrow-left"></i></span>
                            <span>Retour à la liste</span>
                        </a>
                    </div>
                </div>
            </div>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="notification is-danger">
                    <button class="delete"></button>
                    <ul>
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-content">
                    <form action="<?= base_url('admin/securite/users/' . $user['id'] . '/update') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Nom d'utilisateur *</label>
                                    <div class="control">
                                        <input class="input" type="text" name="username" value="<?= old('username', $user['username']) ?>" required>
                                    </div>
                                    <p class="help">Nom d'utilisateur unique pour la connexion</p>
                                </div>
                            </div>
                            
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Email *</label>
                                    <div class="control">
                                        <input class="input" type="email" name="email" value="<?= old('email', $user['email']) ?>" required>
                                    </div>
                                    <p class="help">Adresse email valide</p>
                                </div>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Prénom *</label>
                                    <div class="control">
                                        <input class="input" type="text" name="first_name" value="<?= old('first_name', $user['first_name']) ?>" required>
                                    </div>
                                    <p class="help">Prénom de l'utilisateur</p>
                                </div>
                            </div>
                            
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Nom *</label>
                                    <div class="control">
                                        <input class="input" type="text" name="last_name" value="<?= old('last_name', $user['last_name']) ?>" required>
                                    </div>
                                    <p class="help">Nom de famille de l'utilisateur</p>
                                </div>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Rôle *</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="role_id" required>
                                                <option value="">Sélectionner un rôle</option>
                                                <?php foreach ($roles as $role): ?>
                                                    <option value="<?= $role['id'] ?>" <?= old('role_id', $user['role_id']) == $role['id'] ? 'selected' : '' ?>>
                                                        <?= esc($role['name']) ?> - <?= esc($role['description']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <p class="help">Rôle assigné à l'utilisateur</p>
                                </div>
                            </div>
                            
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Statut</label>
                                    <div class="control">
                                        <label class="radio">
                                            <input type="radio" name="is_active" value="1" <?= old('is_active', $user['is_active']) == 1 ? 'checked' : '' ?>>
                                            Actif
                                        </label>
                                        <label class="radio">
                                            <input type="radio" name="is_active" value="0" <?= old('is_active', $user['is_active']) == 0 ? 'checked' : '' ?>>
                                            Inactif
                                        </label>
                                    </div>
                                    <p class="help">Statut de l'utilisateur dans le système</p>
                                </div>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Nouveau mot de passe</label>
                                    <div class="control">
                                        <input class="input" type="password" name="password" minlength="6">
                                    </div>
                                    <p class="help">Laissez vide pour conserver le mot de passe actuel</p>
                                </div>
                            </div>
                            
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Confirmer le mot de passe</label>
                                    <div class="control">
                                        <input class="input" type="password" name="password_confirm" minlength="6">
                                    </div>
                                    <p class="help">Confirmez le nouveau mot de passe</p>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Informations supplémentaires</label>
                            <div class="control">
                                <textarea class="textarea" name="notes" placeholder="Notes ou informations supplémentaires sur l'utilisateur"><?= old('notes', $user['notes'] ?? '') ?></textarea>
                            </div>
                            <p class="help">Informations optionnelles sur l'utilisateur</p>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <span class="icon"><i class="fas fa-save"></i></span>
                                    <span>Mettre à jour l'Utilisateur</span>
                                </button>
                            </div>
                            <div class="control">
                                <a href="<?= base_url('admin/securite/users') ?>" class="button is-light">
                                    <span class="icon"><i class="fas fa-times"></i></span>
                                    <span>Annuler</span>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>




