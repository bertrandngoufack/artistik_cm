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
                        <a href="<?= base_url('admin/securite/users/' . $user['id']) ?>" class="button is-info">
                            <span class="icon"><i class="fas fa-arrow-left"></i></span>
                            <span>Retour aux détails</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="columns">
                <div class="column is-4">
                    <div class="card">
                        <div class="card-header">
                            <p class="card-header-title">
                                <span class="icon"><i class="fas fa-user"></i></span>
                                Informations Utilisateur
                            </p>
                        </div>
                        <div class="card-content">
                            <div class="content">
                                <p><strong>Nom:</strong> <?= esc($user['first_name'] . ' ' . $user['last_name']) ?></p>
                                <p><strong>Nom d'utilisateur:</strong> <?= esc($user['username']) ?></p>
                                <p><strong>Email:</strong> <?= esc($user['email']) ?></p>
                                <p><strong>Rôle:</strong> <span class="tag is-info"><?= esc($user['role_name'] ?? 'Non assigné') ?></span></p>
                                <p><strong>Statut:</strong> 
                                    <?php if ($user['is_active']): ?>
                                        <span class="tag is-success">Actif</span>
                                    <?php else: ?>
                                        <span class="tag is-danger">Inactif</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="column is-8">
                    <div class="card">
                        <div class="card-header">
                            <p class="card-header-title">
                                <span class="icon"><i class="fas fa-key"></i></span>
                                Gestion des Permissions
                            </p>
                        </div>
                        <div class="card-content">
                            <form action="<?= base_url('admin/securite/users/' . $user['id'] . '/permissions') ?>" method="POST">
                                <?= csrf_field() ?>
                                
                                <div class="columns is-multiline">
                                    <?php 
                                    $currentPermissions = [];
                                    if (isset($user['permissions']) && $user['permissions']) {
                                        if (is_string($user['permissions'])) {
                                            $currentPermissions = json_decode($user['permissions'], true) ?: [];
                                        } else {
                                            $currentPermissions = $user['permissions'];
                                        }
                                    }
                                    ?>
                                    
                                    <!-- Module Économat -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Économat</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="economat_view" <?= in_array('economat_view', $currentPermissions) ? 'checked' : '' ?>>
                                                Voir les paiements
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="economat_create" <?= in_array('economat_create', $currentPermissions) ? 'checked' : '' ?>>
                                                Créer des paiements
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="economat_edit" <?= in_array('economat_edit', $currentPermissions) ? 'checked' : '' ?>>
                                                Modifier les paiements
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="economat_delete" <?= in_array('economat_delete', $currentPermissions) ? 'checked' : '' ?>>
                                                Supprimer les paiements
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Module Scolarité -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Scolarité</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="scolarite_view" <?= in_array('scolarite_view', $currentPermissions) ? 'checked' : '' ?>>
                                                Voir les étudiants
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="scolarite_create" <?= in_array('scolarite_create', $currentPermissions) ? 'checked' : '' ?>>
                                                Créer des étudiants
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="scolarite_edit" <?= in_array('scolarite_edit', $currentPermissions) ? 'checked' : '' ?>>
                                                Modifier les étudiants
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="scolarite_delete" <?= in_array('scolarite_delete', $currentPermissions) ? 'checked' : '' ?>>
                                                Supprimer les étudiants
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Module Études -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Études</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="etudes_view" <?= in_array('etudes_view', $currentPermissions) ? 'checked' : '' ?>>
                                                Voir les classes
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="etudes_create" <?= in_array('etudes_create', $currentPermissions) ? 'checked' : '' ?>>
                                                Créer des classes
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="etudes_edit" <?= in_array('etudes_edit', $currentPermissions) ? 'checked' : '' ?>>
                                                Modifier les classes
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="etudes_delete" <?= in_array('etudes_delete', $currentPermissions) ? 'checked' : '' ?>>
                                                Supprimer les classes
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Module Examens -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Examens</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="examens_view" <?= in_array('examens_view', $currentPermissions) ? 'checked' : '' ?>>
                                                Voir les examens
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="examens_create" <?= in_array('examens_create', $currentPermissions) ? 'checked' : '' ?>>
                                                Créer des examens
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="examens_edit" <?= in_array('examens_edit', $currentPermissions) ? 'checked' : '' ?>>
                                                Modifier les examens
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="examens_delete" <?= in_array('examens_delete', $currentPermissions) ? 'checked' : '' ?>>
                                                Supprimer les examens
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Module Enseignants -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Enseignants</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="enseignants_view" <?= in_array('enseignants_view', $currentPermissions) ? 'checked' : '' ?>>
                                                Voir les enseignants
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="enseignants_create" <?= in_array('enseignants_create', $currentPermissions) ? 'checked' : '' ?>>
                                                Créer des enseignants
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="enseignants_edit" <?= in_array('enseignants_edit', $currentPermissions) ? 'checked' : '' ?>>
                                                Modifier les enseignants
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="enseignants_delete" <?= in_array('enseignants_delete', $currentPermissions) ? 'checked' : '' ?>>
                                                Supprimer les enseignants
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Module Bibliothèque -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Bibliothèque</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="bibliotheque_view" <?= in_array('bibliotheque_view', $currentPermissions) ? 'checked' : '' ?>>
                                                Voir les livres
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="bibliotheque_create" <?= in_array('bibliotheque_create', $currentPermissions) ? 'checked' : '' ?>>
                                                Créer des livres
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="bibliotheque_edit" <?= in_array('bibliotheque_edit', $currentPermissions) ? 'checked' : '' ?>>
                                                Modifier les livres
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="bibliotheque_delete" <?= in_array('bibliotheque_delete', $currentPermissions) ? 'checked' : '' ?>>
                                                Supprimer les livres
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Module Messagerie -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Messagerie</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="messagerie_view" <?= in_array('messagerie_view', $currentPermissions) ? 'checked' : '' ?>>
                                                Voir les messages
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="messagerie_create" <?= in_array('messagerie_create', $currentPermissions) ? 'checked' : '' ?>>
                                                Créer des messages
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="messagerie_edit" <?= in_array('messagerie_edit', $currentPermissions) ? 'checked' : '' ?>>
                                                Modifier les messages
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="messagerie_delete" <?= in_array('messagerie_delete', $currentPermissions) ? 'checked' : '' ?>>
                                                Supprimer les messages
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Module Sécurité -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Sécurité</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="securite_view" <?= in_array('securite_view', $currentPermissions) ? 'checked' : '' ?>>
                                                Voir les utilisateurs
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="securite_create" <?= in_array('securite_create', $currentPermissions) ? 'checked' : '' ?>>
                                                Créer des utilisateurs
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="securite_edit" <?= in_array('securite_edit', $currentPermissions) ? 'checked' : '' ?>>
                                                Modifier les utilisateurs
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="securite_delete" <?= in_array('securite_delete', $currentPermissions) ? 'checked' : '' ?>>
                                                Supprimer les utilisateurs
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Module Configuration -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Configuration</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="configuration_view" <?= in_array('configuration_view', $currentPermissions) ? 'checked' : '' ?>>
                                                Voir la configuration
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="configuration_edit" <?= in_array('configuration_edit', $currentPermissions) ? 'checked' : '' ?>>
                                                Modifier la configuration
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="field is-grouped">
                                    <div class="control">
                                        <button type="submit" class="button is-primary">
                                            <span class="icon"><i class="fas fa-save"></i></span>
                                            <span>Enregistrer les Permissions</span>
                                        </button>
                                    </div>
                                    <div class="control">
                                        <a href="<?= base_url('admin/securite/users/' . $user['id']) ?>" class="button is-light">
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
    </div>
</div>

<?= $this->endSection() ?>




