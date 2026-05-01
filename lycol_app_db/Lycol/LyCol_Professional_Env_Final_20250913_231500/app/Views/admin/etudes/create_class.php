<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column is-12">
            <!-- Breadcrumb -->
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/etudes') ?>">Études</a></li>
                    <li><a href="<?= base_url('admin/etudes/classes') ?>">Classes</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Nouvelle Classe</a></li>
                </ul>
            </nav>

            <!-- Header -->
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h1 class="title">Nouvelle Classe</h1>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <a href="<?= base_url('admin/etudes/classes') ?>" class="button is-info">
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
                <form action="<?= base_url('admin/etudes/classes/store') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="field">
                        <label class="label" for="name">Nom de la Classe *</label>
                        <div class="control">
                            <input class="input <?= session('errors.name') ? 'is-danger' : '' ?>" 
                                   type="text" 
                                   id="name" 
                                   name="name" 
                                   value="<?= old('name') ?>" 
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
                                   value="<?= old('code') ?>" 
                                   required>
                        </div>
                        <?php if (session('errors.code')): ?>
                            <p class="help is-danger"><?= session('errors.code') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label" for="cycle_id">Cycle *</label>
                        <div class="control">
                            <div class="select <?= session('errors.cycle_id') ? 'is-danger' : '' ?> is-fullwidth">
                                <select id="cycle_id" name="cycle_id" required>
                                    <option value="">Sélectionner un cycle</option>
                                    <?php foreach ($cycles as $cycle): ?>
                                        <option value="<?= $cycle['id'] ?>" <?= old('cycle_id') == $cycle['id'] ? 'selected' : '' ?>>
                                            <?= $cycle['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if (session('errors.cycle_id')): ?>
                            <p class="help is-danger"><?= session('errors.cycle_id') ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="columns">
                        <div class="column is-6">
                            <div class="field">
                                <label class="label" for="level">Niveau *</label>
                                <div class="control">
                                    <input class="input <?= session('errors.level') ? 'is-danger' : '' ?>" 
                                           type="number" 
                                           id="level" 
                                           name="level" 
                                           value="<?= old('level') ?>" 
                                           min="1" 
                                           required>
                                </div>
                                <?php if (session('errors.level')): ?>
                                    <p class="help is-danger"><?= session('errors.level') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="column is-6">
                            <div class="field">
                                <label class="label" for="capacity">Capacité *</label>
                                <div class="control">
                                    <input class="input <?= session('errors.capacity') ? 'is-danger' : '' ?>" 
                                           type="number" 
                                           id="capacity" 
                                           name="capacity" 
                                           value="<?= old('capacity') ?>" 
                                           min="1" 
                                           required>
                                </div>
                                <?php if (session('errors.capacity')): ?>
                                    <p class="help is-danger"><?= session('errors.capacity') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label" for="description">Description</label>
                        <div class="control">
                            <textarea class="textarea <?= session('errors.description') ? 'is-danger' : '' ?>" 
                                      id="description" 
                                      name="description" 
                                      rows="3"><?= old('description') ?></textarea>
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
                                <span>Créer</span>
                            </button>
                        </div>
                        <div class="control">
                            <a href="<?= base_url('admin/etudes/classes') ?>" class="button is-light">
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



















