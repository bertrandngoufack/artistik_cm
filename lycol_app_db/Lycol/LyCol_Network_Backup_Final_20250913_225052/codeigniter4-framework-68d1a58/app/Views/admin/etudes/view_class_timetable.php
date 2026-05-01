<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container">
    <div class="columns">
        <div class="column">
            <nav class="breadcrumb" aria-label="breadcrumbs">
                <ul>
                    <li><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
                    <li><a href="<?= base_url('admin/etudes') ?>">Études</a></li>
                    <li><a href="<?= base_url('admin/etudes/timetable') ?>">Emploi du Temps</a></li>
                    <li class="is-active"><a href="#" aria-current="page">Emploi du Temps de la Classe</a></li>
                </ul>
            </nav>
            
            <h1 class="title has-text-primary">
                <i class="fas fa-calendar-alt"></i>
                📅 Emploi du Temps de la Classe
            </h1>
            <p class="subtitle">Visualisation détaillée de l'emploi du temps d'une classe</p>
        </div>
        <div class="column is-narrow">
            <div class="buttons">
                <a href="<?= base_url('admin/etudes/timetable') ?>" class="button is-info">
                    <span class="icon">
                        <i class="fas fa-arrow-left"></i>
                    </span>
                    <span>Retour</span>
                </a>
                <a href="<?= base_url('admin/etudes/timetable/print') ?>" class="button is-success">
                    <span class="icon">
                        <i class="fas fa-print"></i>
                    </span>
                    <span>Imprimer</span>
                </a>
            </div>
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

    <!-- Informations de la classe -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="fas fa-info-circle"></i></span>
                Informations de la Classe
            </p>
        </header>
        <div class="card-content">
            <div class="columns">
                <div class="column is-6">
                    <h4 class="title is-5">🏫 Détails de la Classe</h4>
                    <table class="table is-fullwidth">
                        <tr>
                            <td><strong>Nom:</strong></td>
                            <td><?= esc($class['name'] ?? 'Non spécifié') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Code:</strong></td>
                            <td><?= esc($class['code'] ?? 'Non spécifié') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Niveau:</strong></td>
                            <td><?= esc($class['level'] ?? 'Non spécifié') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Effectif:</strong></td>
                            <td><?= esc($class['capacity'] ?? 'Non spécifié') ?> élèves</td>
                        </tr>
                    </table>
                </div>
                <div class="column is-6">
                    <h4 class="title is-5">📊 Statistiques</h4>
                    <table class="table is-fullwidth">
                        <tr>
                            <td><strong>Total cours:</strong></td>
                            <td><?= count($timetable) ?> cours</td>
                        </tr>
                        <tr>
                            <td><strong>Jours couverts:</strong></td>
                            <td><?= count(array_unique(array_column($timetable, 'day_of_week'))) ?> jours</td>
                        </tr>
                        <tr>
                            <td><strong>Heures par semaine:</strong></td>
                            <td><?= calculateWeeklyHours($timetable) ?> heures</td>
                        </tr>
                        <tr>
                            <td><strong>Matières:</strong></td>
                            <td><?= count(array_unique(array_column($timetable, 'subject_id'))) ?> matières</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Emploi du temps par jour -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="fas fa-calendar-week"></i></span>
                Emploi du Temps Détaillé
            </p>
        </header>
        <div class="card-content">
            <?php if (empty($timetable)): ?>
                <div class="notification is-warning">
                    <span class="icon"><i class="fas fa-exclamation-triangle"></i></span>
                    Aucun cours n'est encore programmé pour cette classe.
                    <a href="<?= base_url('admin/etudes/timetable/create') ?>" class="button is-small is-primary ml-3">
                        Ajouter un cours
                    </a>
                </div>
            <?php else: ?>
                <div class="tabs">
                    <ul>
                        <li class="is-active"><a data-tab="1">Lundi</a></li>
                        <li><a data-tab="2">Mardi</a></li>
                        <li><a data-tab="3">Mercredi</a></li>
                        <li><a data-tab="4">Jeudi</a></li>
                        <li><a data-tab="5">Vendredi</a></li>
                        <li><a data-tab="6">Samedi</a></li>
                    </ul>
                </div>

                <?php for ($day = 1; $day <= 6; $day++): ?>
                    <div class="tab-content" id="tab-<?= $day ?>" <?= $day === 1 ? 'style="display: block;"' : '' ?>>
                        <div class="content">
                            <h4 class="title is-5"><?= getDayName($day) ?></h4>
                            
                            <?php 
                            $dayTimetable = array_filter($timetable, function($item) use ($day) {
                                return $item['day_of_week'] == $day;
                            });
                            ?>
                            
                            <?php if (empty($dayTimetable)): ?>
                                <div class="notification is-light">
                                    <span class="icon"><i class="fas fa-info-circle"></i></span>
                                    Aucun cours programmé ce jour.
                                </div>
                            <?php else: ?>
                                <table class="table is-fullwidth is-striped">
                                    <thead>
                                        <tr>
                                            <th>Horaire</th>
                                            <th>Matière</th>
                                            <th>Enseignant</th>
                                            <th>Salle</th>
                                            <th>Durée</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dayTimetable as $course): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= formatTime($course['start_time']) ?></strong> - 
                                                    <strong><?= formatTime($course['end_time']) ?></strong>
                                                </td>
                                                <td>
                                                    <span class="tag is-info">
                                                        <?= esc($course['subject_name'] ?? 'Non spécifié') ?>
                                                    </span>
                                                    <?php if (!empty($course['subject_code'])): ?>
                                                        <br><small class="has-text-grey"><?= esc($course['subject_code']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($course['first_name']) && !empty($course['last_name'])): ?>
                                                        <?= esc($course['first_name'] . ' ' . $course['last_name']) ?>
                                                    <?php else: ?>
                                                        <span class="has-text-grey">Non assigné</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($course['room'])): ?>
                                                        <span class="tag is-light"><?= esc($course['room']) ?></span>
                                                    <?php else: ?>
                                                        <span class="has-text-grey">Non spécifiée</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= calculateDuration($course['start_time'], $course['end_time']) ?>
                                                </td>
                                                <td>
                                                    <div class="buttons are-small">
                                                        <a href="<?= base_url('admin/etudes/timetable/' . $course['id'] . '/edit') ?>" 
                                                           class="button is-info is-small">
                                                            <span class="icon"><i class="fas fa-edit"></i></span>
                                                        </a>
                                                        <a href="<?= base_url('admin/etudes/timetable/' . $course['id'] . '/delete') ?>" 
                                                           class="button is-danger is-small"
                                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce cours ?')">
                                                            <span class="icon"><i class="fas fa-trash"></i></span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="fas fa-tools"></i></span>
                Actions Rapides
            </p>
        </header>
        <div class="card-content">
            <div class="columns">
                <div class="column is-4">
                    <a href="<?= base_url('admin/etudes/timetable/create') ?>" class="button is-primary is-fullwidth">
                        <span class="icon"><i class="fas fa-plus"></i></span>
                        <span>Ajouter un Cours</span>
                    </a>
                </div>
                <div class="column is-4">
                    <a href="<?= base_url('admin/etudes/timetable/print') ?>" class="button is-success is-fullwidth">
                        <span class="icon"><i class="fas fa-print"></i></span>
                        <span>Imprimer EDT</span>
                    </a>
                </div>
                <div class="column is-4">
                    <a href="<?= base_url('admin/etudes/classes/' . ($class['id'] ?? 1)) ?>" class="button is-info is-fullwidth">
                        <span class="icon"><i class="fas fa-users"></i></span>
                        <span>Gérer la Classe</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des onglets
    const tabs = document.querySelectorAll('.tabs li');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.querySelector('a').getAttribute('data-tab');
            
            // Retirer la classe active de tous les onglets
            tabs.forEach(t => t.classList.remove('is-active'));
            tabContents.forEach(tc => tc.style.display = 'none');
            
            // Activer l'onglet cliqué
            this.classList.add('is-active');
            document.getElementById('tab-' + targetTab).style.display = 'block';
        });
    });
});
</script>

<?php
// Fonctions utilitaires
function getDayName($day) {
    $days = [
        1 => 'Lundi',
        2 => 'Mardi', 
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi'
    ];
    return $days[$day] ?? 'Jour inconnu';
}

function formatTime($time) {
    return date('H:i', strtotime($time));
}

function calculateDuration($start, $end) {
    $startTime = strtotime($start);
    $endTime = strtotime($end);
    $diff = $endTime - $startTime;
    $hours = floor($diff / 3600);
    $minutes = floor(($diff % 3600) / 60);
    
    if ($hours > 0) {
        return $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'min' : '');
    } else {
        return $minutes . 'min';
    }
}

function calculateWeeklyHours($timetable) {
    $totalMinutes = 0;
    foreach ($timetable as $course) {
        $start = strtotime($course['start_time']);
        $end = strtotime($course['end_time']);
        $totalMinutes += ($end - $start) / 60;
    }
    return round($totalMinutes / 60, 1);
}
?>

<?= $this->endSection() ?>







