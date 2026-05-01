<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column is-12">
            <!-- Breadcrumb -->
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/etudes') ?>">Études</a></li>
                    <li><a href="<?= base_url('admin/etudes/cycles') ?>">Cycles</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Modifier le Cycle</a></li>
                </ul>
            </nav>

            <!-- Header -->
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h1 class="title">Modifier le Cycle</h1>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <a href="<?= base_url('admin/etudes/cycles') ?>" class="button is-info">
                            <span class="icon">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span>Retour</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="box">
                <form action="<?= base_url('admin/etudes/cycles/update/' . $cycle['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="field">
                        <label class="label" for="name">Nom du Cycle *</label>
                        <div class="control">
                            <input class="input <?= session('errors.name') ? 'is-danger' : '' ?>" 
                                   type="text" 
                                   id="name" 
                                   name="name" 
                                   value="<?= old('name', $cycle['name']) ?>" 
                                   required>
                        </div>
                        <?php if (session('errors.name')): ?>
                            <p class="help is-danger"><?= session('errors.name') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label" for="code">Code *</label>
                        <div class="control">
                            <input class="input <?= session('errors.code') ? 'is-danger' : '' ?>" 
                                   type="text" 
                                   id="code" 
                                   name="code" 
                                   value="<?= old('code', $cycle['code']) ?>" 
                                   required>
                        </div>
                        <?php if (session('errors.code')): ?>
                            <p class="help is-danger"><?= session('errors.code') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label" for="description">Description</label>
                        <div class="control">
                            <textarea class="textarea <?= session('errors.description') ? 'is-danger' : '' ?>" 
                                      id="description" 
                                      name="description" 
                                      rows="3"><?= old('description', $cycle['description']) ?></textarea>
                        </div>
                        <?php if (session('errors.description')): ?>
                            <p class="help is-danger"><?= session('errors.description') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field is-grouped">
                        <div class="control">
                            <button type="submit" class="button is-primary">
                                <span class="icon">
                                    <i class="fas fa-save"></i>
                                </span>
                                <span>Mettre à jour</span>
                            </button>
                        </div>
                        <div class="control">
                            <a href="<?= base_url('admin/etudes/cycles') ?>" class="button is-light">
                                <span class="icon">
                                    <i class="fas fa-times"></i>
                                </span>
                                <span>Annuler</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>







