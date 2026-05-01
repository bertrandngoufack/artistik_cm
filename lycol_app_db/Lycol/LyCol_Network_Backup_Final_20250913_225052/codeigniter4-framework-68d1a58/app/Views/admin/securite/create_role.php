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
                        <a href="<?= base_url('admin/securite/roles') ?>" class="button is-info">
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
                    <form action="<?= base_url('admin/securite/roles/store') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="field">
                            <label class="label">Nom du Rôle *</label>
                            <div class="control">
                                <input class="input" type="text" name="name" value="<?= old('name') ?>" required>
                            </div>
                            <p class="help">Nom unique du rôle (ex: Administrateur, Enseignant, etc.)</p>
                        </div>

                        <div class="field">
                            <label class="label">Description *</label>
                            <div class="control">
                                <textarea class="textarea" name="description" required><?= old('description') ?></textarea>
                            </div>
                            <p class="help">Description détaillée du rôle et de ses responsabilités</p>
                        </div>

                        <div class="field">
                            <label class="label">Permissions *</label>
                            <div class="control">
                                <div class="columns is-multiline">
                                    <!-- Module Économat -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Économat</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="economat_view" <?= in_array('economat_view', old('permissions', [])) ? 'checked' : '' ?>>
                                                Voir les paiements
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="economat_create" <?= in_array('economat_create', old('permissions', [])) ? 'checked' : '' ?>>
                                                Créer des paiements
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="economat_edit" <?= in_array('economat_edit', old('permissions', [])) ? 'checked' : '' ?>>
                                                Modifier les paiements
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="economat_delete" <?= in_array('economat_delete', old('permissions', [])) ? 'checked' : '' ?>>
                                                Supprimer les paiements
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Module Scolarité -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Scolarité</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="scolarite_view" <?= in_array('scolarite_view', old('permissions', [])) ? 'checked' : '' ?>>
                                                Voir les étudiants
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="scolarite_create" <?= in_array('scolarite_create', old('permissions', [])) ? 'checked' : '' ?>>
                                                Créer des étudiants
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="scolarite_edit" <?= in_array('scolarite_edit', old('permissions', [])) ? 'checked' : '' ?>>
                                                Modifier les étudiants
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="scolarite_delete" <?= in_array('scolarite_delete', old('permissions', [])) ? 'checked' : '' ?>>
                                                Supprimer les étudiants
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Module Études -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Études</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="etudes_view" <?= in_array('etudes_view', old('permissions', [])) ? 'checked' : '' ?>>
                                                Voir les classes
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="etudes_create" <?= in_array('etudes_create', old('permissions', [])) ? 'checked' : '' ?>>
                                                Créer des classes
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="etudes_edit" <?= in_array('etudes_edit', old('permissions', [])) ? 'checked' : '' ?>>
                                                Modifier les classes
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="etudes_delete" <?= in_array('etudes_delete', old('permissions', [])) ? 'checked' : '' ?>>
                                                Supprimer les classes
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Module Examens -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Examens</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="examens_view" <?= in_array('examens_view', old('permissions', [])) ? 'checked' : '' ?>>
                                                Voir les examens
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="examens_create" <?= in_array('examens_create', old('permissions', [])) ? 'checked' : '' ?>>
                                                Créer des examens
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="examens_edit" <?= in_array('examens_edit', old('permissions', [])) ? 'checked' : '' ?>>
                                                Modifier les examens
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="examens_delete" <?= in_array('examens_delete', old('permissions', [])) ? 'checked' : '' ?>>
                                                Supprimer les examens
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Module Enseignants -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Enseignants</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="enseignants_view" <?= in_array('enseignants_view', old('permissions', [])) ? 'checked' : '' ?>>
                                                Voir les enseignants
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="enseignants_create" <?= in_array('enseignants_create', old('permissions', [])) ? 'checked' : '' ?>>
                                                Créer des enseignants
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="enseignants_edit" <?= in_array('enseignants_edit', old('permissions', [])) ? 'checked' : '' ?>>
                                                Modifier les enseignants
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="enseignants_delete" <?= in_array('enseignants_delete', old('permissions', [])) ? 'checked' : '' ?>>
                                                Supprimer les enseignants
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Module Bibliothèque -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Bibliothèque</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="bibliotheque_view" <?= in_array('bibliotheque_view', old('permissions', [])) ? 'checked' : '' ?>>
                                                Voir les livres
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="bibliotheque_create" <?= in_array('bibliotheque_create', old('permissions', [])) ? 'checked' : '' ?>>
                                                Créer des livres
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="bibliotheque_edit" <?= in_array('bibliotheque_edit', old('permissions', [])) ? 'checked' : '' ?>>
                                                Modifier les livres
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="bibliotheque_delete" <?= in_array('bibliotheque_delete', old('permissions', [])) ? 'checked' : '' ?>>
                                                Supprimer les livres
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Module Messagerie -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Messagerie</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="messagerie_view" <?= in_array('messagerie_view', old('permissions', [])) ? 'checked' : '' ?>>
                                                Voir les messages
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="messagerie_create" <?= in_array('messagerie_create', old('permissions', [])) ? 'checked' : '' ?>>
                                                Créer des messages
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="messagerie_edit" <?= in_array('messagerie_edit', old('permissions', [])) ? 'checked' : '' ?>>
                                                Modifier les messages
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="messagerie_delete" <?= in_array('messagerie_delete', old('permissions', [])) ? 'checked' : '' ?>>
                                                Supprimer les messages
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Module Sécurité -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Sécurité</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="securite_view" <?= in_array('securite_view', old('permissions', [])) ? 'checked' : '' ?>>
                                                Voir les utilisateurs
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="securite_create" <?= in_array('securite_create', old('permissions', [])) ? 'checked' : '' ?>>
                                                Créer des utilisateurs
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="securite_edit" <?= in_array('securite_edit', old('permissions', [])) ? 'checked' : '' ?>>
                                                Modifier les utilisateurs
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="securite_delete" <?= in_array('securite_delete', old('permissions', [])) ? 'checked' : '' ?>>
                                                Supprimer les utilisateurs
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Module Configuration -->
                                    <div class="column is-6">
                                        <div class="box">
                                            <h4 class="title is-5">Configuration</h4>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="configuration_view" <?= in_array('configuration_view', old('permissions', [])) ? 'checked' : '' ?>>
                                                Voir la configuration
                                            </label><br>
                                            <label class="checkbox">
                                                <input type="checkbox" name="permissions[]" value="configuration_edit" <?= in_array('configuration_edit', old('permissions', [])) ? 'checked' : '' ?>>
                                                Modifier la configuration
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="help">Sélectionnez les permissions accordées à ce rôle</p>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <span class="icon"><i class="fas fa-save"></i></span>
                                    <span>Créer le Rôle</span>
                                </button>
                            </div>
                            <div class="control">
                                <a href="<?= base_url('admin/securite/roles') ?>" class="button is-light">
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




