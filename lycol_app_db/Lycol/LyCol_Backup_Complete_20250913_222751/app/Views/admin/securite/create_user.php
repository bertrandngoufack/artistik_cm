<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="level">
    <div class="level-left">
        <div class="level-item">
            <h1 class="title">Nouvel Utilisateur</h1>
        </div>
    </div>
    <div class="level-right">
        <div class="level-item">
            <a href="<?= base_url('admin/securite/users') ?>" class="button is-light">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Retour à la liste</span>
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
                    Informations de l'Utilisateur
                </p>
            </header>
            <div class="card-content">
                <form method="POST" action="<?= base_url('admin/securite/users/store') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <div class="columns is-multiline">
                        <!-- Informations personnelles -->
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Prénom *</label>
                                <div class="control">
                                    <input class="input <?= session()->getFlashdata('errors.first_name') ? 'is-danger' : '' ?>" 
                                           type="text" 
                                           name="first_name" 
                                           value="<?= old('first_name') ?>" 
                                           placeholder="Prénom de l'utilisateur"
                                           required>
                                </div>
                                <?php if (session()->getFlashdata('errors.first_name')): ?>
                                    <p class="help is-danger"><?= session()->getFlashdata('errors.first_name') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Nom *</label>
                                <div class="control">
                                    <input class="input <?= session()->getFlashdata('errors.last_name') ? 'is-danger' : '' ?>" 
                                           type="text" 
                                           name="last_name" 
                                           value="<?= old('last_name') ?>" 
                                           placeholder="Nom de l'utilisateur"
                                           required>
                                </div>
                                <?php if (session()->getFlashdata('errors.last_name')): ?>
                                    <p class="help is-danger"><?= session()->getFlashdata('errors.last_name') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Informations de connexion -->
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Nom d'utilisateur *</label>
                                <div class="control">
                                    <input class="input <?= session()->getFlashdata('errors.username') ? 'is-danger' : '' ?>" 
                                           type="text" 
                                           name="username" 
                                           value="<?= old('username') ?>" 
                                           placeholder="Nom d'utilisateur unique"
                                           required>
                                </div>
                                <?php if (session()->getFlashdata('errors.username')): ?>
                                    <p class="help is-danger"><?= session()->getFlashdata('errors.username') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Email *</label>
                                <div class="control">
                                    <input class="input <?= session()->getFlashdata('errors.email') ? 'is-danger' : '' ?>" 
                                           type="email" 
                                           name="email" 
                                           value="<?= old('email') ?>" 
                                           placeholder="Adresse email"
                                           required>
                                </div>
                                <?php if (session()->getFlashdata('errors.email')): ?>
                                    <p class="help is-danger"><?= session()->getFlashdata('errors.email') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Mot de passe -->
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Mot de passe *</label>
                                <div class="control">
                                    <input class="input <?= session()->getFlashdata('errors.password') ? 'is-danger' : '' ?>" 
                                           type="password" 
                                           name="password" 
                                           placeholder="Mot de passe (min. 6 caractères)"
                                           required>
                                </div>
                                <?php if (session()->getFlashdata('errors.password')): ?>
                                    <p class="help is-danger"><?= session()->getFlashdata('errors.password') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Confirmer le mot de passe *</label>
                                <div class="control">
                                    <input class="input <?= session()->getFlashdata('errors.password_confirm') ? 'is-danger' : '' ?>" 
                                           type="password" 
                                           name="password_confirm" 
                                           placeholder="Confirmer le mot de passe"
                                           required>
                                </div>
                                <?php if (session()->getFlashdata('errors.password_confirm')): ?>
                                    <p class="help is-danger"><?= session()->getFlashdata('errors.password_confirm') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Rôle et statut -->
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Rôle *</label>
                                <div class="control">
                                    <div class="select is-fullwidth <?= session()->getFlashdata('errors.role_id') ? 'is-danger' : '' ?>">
                                        <select name="role_id" required>
                                            <option value="">Sélectionner un rôle</option>
                                            <?php foreach ($roles as $role): ?>
                                            <option value="<?= $role['id'] ?>" <?= old('role_id') == $role['id'] ? 'selected' : '' ?>>
                                                <?= esc($role['name']) ?> - <?= esc($role['description']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <?php if (session()->getFlashdata('errors.role_id')): ?>
                                    <p class="help is-danger"><?= session()->getFlashdata('errors.role_id') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Statut</label>
                                <div class="control">
                                    <label class="radio">
                                        <input type="radio" name="is_active" value="1" <?= old('is_active', '1') == '1' ? 'checked' : '' ?>>
                                        Actif
                                    </label>
                                    <label class="radio">
                                        <input type="radio" name="is_active" value="0" <?= old('is_active') == '0' ? 'checked' : '' ?>>
                                        Inactif
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informations supplémentaires -->
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Téléphone</label>
                                <div class="control">
                                    <input class="input" 
                                           type="tel" 
                                           name="phone" 
                                           value="<?= old('phone') ?>" 
                                           placeholder="Numéro de téléphone">
                                </div>
                            </div>
                        </div>
                        
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Avatar</label>
                                <div class="control">
                                    <div class="file has-name is-fullwidth">
                                        <label class="file-label">
                                            <input class="file-input" type="file" name="avatar" accept="image/*">
                                            <span class="file-cta">
                                                <span class="file-icon">
                                                    <i class="fas fa-upload"></i>
                                                </span>
                                                <span class="file-label">
                                                    Choisir un fichier
                                                </span>
                                            </span>
                                            <span class="file-name">
                                                Aucun fichier sélectionné
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <p class="help">Formats acceptés: JPG, PNG, GIF (max 2MB)</p>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Boutons d'action -->
                    <div class="field is-grouped">
                        <div class="control">
                            <button type="submit" class="button is-primary">
                                <span class="icon"><i class="fas fa-save"></i></span>
                                <span>Créer l'utilisateur</span>
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
    
    <div class="column is-4">
        <!-- Aide et informations -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-info-circle"></i></span>
                    Informations
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <h6>Règles de création</h6>
                    <ul>
                        <li>Le nom d'utilisateur doit être unique</li>
                        <li>L'email doit être valide et unique</li>
                        <li>Le mot de passe doit contenir au moins 6 caractères</li>
                        <li>Un rôle doit être assigné à l'utilisateur</li>
                    </ul>
                    
                    <h6>Rôles disponibles</h6>
                    <ul>
                        <?php foreach ($roles as $role): ?>
                        <li><strong><?= esc($role['name']) ?>:</strong> <?= esc($role['description']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <h6>Statuts</h6>
                    <ul>
                        <li><strong>Actif:</strong> L'utilisateur peut se connecter</li>
                        <li><strong>Inactif:</strong> L'utilisateur ne peut pas se connecter</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Prévisualisation -->
        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="fas fa-eye"></i></span>
                    Prévisualisation
                </p>
            </header>
            <div class="card-content">
                <div class="content">
                    <div class="box">
                        <div class="media">
                            <div class="media-left">
                                <figure class="image is-48x48">
                                    <img class="is-rounded" id="avatar-preview" src="<?= base_url('assets/images/default-avatar.png') ?>" alt="Avatar">
                                </figure>
                            </div>
                            <div class="media-content">
                                <p class="title is-6" id="name-preview">Nom complet</p>
                                <p class="subtitle is-7" id="role-preview">Rôle</p>
                                <p class="subtitle is-7" id="email-preview">email@example.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Prévisualisation en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const firstNameInput = document.querySelector('input[name="first_name"]');
    const lastNameInput = document.querySelector('input[name="last_name"]');
    const emailInput = document.querySelector('input[name="email"]');
    const roleSelect = document.querySelector('select[name="role_id"]');
    const avatarInput = document.querySelector('input[name="avatar"]');
    
    // Mise à jour du nom complet
    function updateName() {
        const firstName = firstNameInput.value || 'Prénom';
        const lastName = lastNameInput.value || 'Nom';
        document.getElementById('name-preview').textContent = firstName + ' ' + lastName;
    }
    
    // Mise à jour de l'email
    function updateEmail() {
        const email = emailInput.value || 'email@example.com';
        document.getElementById('email-preview').textContent = email;
    }
    
    // Mise à jour du rôle
    function updateRole() {
        const selectedOption = roleSelect.options[roleSelect.selectedIndex];
        const roleName = selectedOption.value ? selectedOption.text.split(' - ')[0] : 'Rôle';
        document.getElementById('role-preview').textContent = roleName;
    }
    
    // Prévisualisation de l'avatar
    function updateAvatar() {
        const file = avatarInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatar-preview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
    
    // Écouteurs d'événements
    firstNameInput.addEventListener('input', updateName);
    lastNameInput.addEventListener('input', updateName);
    emailInput.addEventListener('input', updateEmail);
    roleSelect.addEventListener('change', updateRole);
    avatarInput.addEventListener('change', updateAvatar);
    
    // Mise à jour initiale
    updateName();
    updateEmail();
    updateRole();
});

// Validation du mot de passe
document.querySelector('input[name="password_confirm"]').addEventListener('input', function() {
    const password = document.querySelector('input[name="password"]').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword) {
        this.classList.add('is-danger');
        this.nextElementSibling.textContent = 'Les mots de passe ne correspondent pas';
    } else {
        this.classList.remove('is-danger');
        this.nextElementSibling.textContent = '';
    }
});
</script>

<?= $this->endSection() ?>







