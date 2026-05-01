<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/configuration') ?>">Configuration</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Apparence</a></li>
                </ul>
            </nav>
            
            <h1 class="title">
                <i class="fas fa-palette"></i>
                Apparence de l'Application
            </h1>
            <p class="subtitle">Personnalisez l'apparence de KISSAI SCHOOL</p>
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
                        <i class="fas fa-image"></i>
                        Personnalisation de l'Apparence
                    </p>
                </div>
                <div class="card-content">
                    <form action="<?= base_url('admin/configuration/save-appearance') ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        
                        <!-- Nom de l'Application -->
                        <div class="field">
                            <label class="label">Nom de l'Application *</label>
                            <div class="control">
                                <input class="input" type="text" name="app_name" value="<?= old('app_name', $settings['app_name'] ?? 'KISSAI SCHOOL') ?>" placeholder="Nom de l'application" required>
                            </div>
                            <p class="help">Ce nom sera affiché dans l'en-tête et le titre de l'application</p>
                        </div>

                        <!-- Logo de l'Application -->
                        <div class="field">
                            <label class="label">Logo de l'Application</label>
                            <div class="control">
                                <div class="file has-name is-fullwidth">
                                    <label class="file-label">
                                        <input class="file-input" type="file" name="app_logo" accept="image/*" onchange="updateFileName(this)">
                                        <span class="file-cta">
                                            <span class="file-icon">
                                                <i class="fas fa-upload"></i>
                                            </span>
                                            <span class="file-label">
                                                Choisir un fichier...
                                            </span>
                                        </span>
                                        <span class="file-name" id="logoFileName">
                                            Aucun fichier sélectionné
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <p class="help">Formats acceptés: PNG, JPG, SVG. Taille recommandée: 200x80px</p>
                        </div>

                        <!-- Prévisualisation du Logo -->
                        <div class="field">
                            <label class="label">Prévisualisation du Logo</label>
                            <div class="control">
                                <figure class="image is-200x80">
                                    <img id="logoPreview" src="<?= base_url($settings['app_logo'] ?? 'assets/images/logo.png') ?>" alt="Logo actuel" style="border: 1px solid #ddd; padding: 10px;">
                                </figure>
                            </div>
                        </div>

                        <!-- Favicon -->
                        <div class="field">
                            <label class="label">Favicon</label>
                            <div class="control">
                                <div class="file has-name is-fullwidth">
                                    <label class="file-label">
                                        <input class="file-input" type="file" name="app_favicon" accept="image/x-icon,image/png" onchange="updateFaviconFileName(this)">
                                        <span class="file-cta">
                                            <span class="file-icon">
                                                <i class="fas fa-upload"></i>
                                            </span>
                                            <span class="file-label">
                                                Choisir un favicon...
                                            </span>
                                        </span>
                                        <span class="file-name" id="faviconFileName">
                                            Aucun fichier sélectionné
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <p class="help">Formats acceptés: ICO, PNG. Taille recommandée: 32x32px</p>
                        </div>

                        <!-- Prévisualisation du Favicon -->
                        <div class="field">
                            <label class="label">Prévisualisation du Favicon</label>
                            <div class="control">
                                <figure class="image is-32x32">
                                    <img id="faviconPreview" src="<?= base_url($settings['app_favicon'] ?? 'assets/images/favicon.ico') ?>" alt="Favicon actuel" style="border: 1px solid #ddd;">
                                </figure>
                            </div>
                        </div>

                        <!-- Couleur Principale -->
                        <div class="field">
                            <label class="label">Couleur Principale</label>
                            <div class="control">
                                <input class="input" type="color" name="primary_color" value="<?= old('primary_color', $settings['primary_color'] ?? '#3273dc') ?>" style="width: 100px; height: 40px;">
                            </div>
                            <p class="help">Couleur principale utilisée dans l'interface</p>
                        </div>

                        <!-- Couleur Secondaire -->
                        <div class="field">
                            <label class="label">Couleur Secondaire</label>
                            <div class="control">
                                <input class="input" type="color" name="secondary_color" value="<?= old('secondary_color', $settings['secondary_color'] ?? '#00d1b2') ?>" style="width: 100px; height: 40px;">
                            </div>
                            <p class="help">Couleur secondaire pour les accents</p>
                        </div>

                        <!-- Description de l'Application -->
                        <div class="field">
                            <label class="label">Description de l'Application</label>
                            <div class="control">
                                <textarea class="textarea" name="app_description" placeholder="Description de l'application"><?= old('app_description', $settings['app_description'] ?? 'Système de gestion scolaire KISSAI SCHOOL') ?></textarea>
                            </div>
                            <p class="help">Description qui apparaîtra dans les métadonnées</p>
                        </div>

                        <!-- Mots-clés -->
                        <div class="field">
                            <label class="label">Mots-clés</label>
                            <div class="control">
                                <input class="input" type="text" name="app_keywords" value="<?= old('app_keywords', $settings['app_keywords'] ?? 'école, gestion, scolaire, KISSAI') ?>" placeholder="Mots-clés séparés par des virgules">
                            </div>
                            <p class="help">Mots-clés pour le référencement</p>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-primary">
                                    <i class="fas fa-save"></i>
                                    Sauvegarder l'Apparence
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
            <!-- Aperçu en Temps Réel -->
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-eye"></i>
                        Aperçu en Temps Réel
                    </p>
                </div>
                <div class="card-content">
                    <div id="previewContainer" style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
                        <div style="display: flex; align-items: center; margin-bottom: 10px;">
                            <img id="previewLogo" src="<?= base_url($settings['app_logo'] ?? 'assets/images/logo.png') ?>" alt="Logo" style="height: 40px; margin-right: 10px;">
                            <h3 id="previewAppName" style="color: <?= $settings['primary_color'] ?? '#3273dc' ?>; margin: 0;"><?= $settings['app_name'] ?? 'KISSAI SCHOOL' ?></h3>
                        </div>
                        <p id="previewDescription" style="color: #666; font-size: 14px; margin: 0;">
                            <?= $settings['app_description'] ?? 'Système de gestion scolaire KISSAI SCHOOL' ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Informations -->
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        <i class="fas fa-info-circle"></i>
                        Informations
                    </p>
                </div>
                <div class="card-content">
                    <div class="content">
                        <p><strong>Logo:</strong> Utilisé dans l'en-tête de l'application</p>
                        <p><strong>Favicon:</strong> Icône affichée dans l'onglet du navigateur</p>
                        <p><strong>Couleurs:</strong> Personnalisent l'apparence générale</p>
                        <p><strong>Nom:</strong> Affiché dans le titre et l'en-tête</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Mise à jour du nom de fichier pour le logo
function updateFileName(input) {
    const fileName = input.files[0] ? input.files[0].name : 'Aucun fichier sélectionné';
    document.getElementById('logoFileName').textContent = fileName;
    
    // Prévisualisation du logo
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').src = e.target.result;
            document.getElementById('previewLogo').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Mise à jour du nom de fichier pour le favicon
function updateFaviconFileName(input) {
    const fileName = input.files[0] ? input.files[0].name : 'Aucun fichier sélectionné';
    document.getElementById('faviconFileName').textContent = fileName;
    
    // Prévisualisation du favicon
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('faviconPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Mise à jour en temps réel du nom de l'application
document.querySelector('input[name="app_name"]').addEventListener('input', function() {
    document.getElementById('previewAppName').textContent = this.value || 'KISSAI SCHOOL';
});

// Mise à jour en temps réel de la description
document.querySelector('textarea[name="app_description"]').addEventListener('input', function() {
    document.getElementById('previewDescription').textContent = this.value || 'Système de gestion scolaire KISSAI SCHOOL';
});

// Mise à jour en temps réel des couleurs
document.querySelector('input[name="primary_color"]').addEventListener('input', function() {
    document.getElementById('previewAppName').style.color = this.value;
});

document.querySelector('input[name="secondary_color"]').addEventListener('input', function() {
    document.getElementById('previewContainer').style.borderColor = this.value;
});
</script>

<?= $this->endSection() ?>


