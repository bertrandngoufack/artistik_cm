<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Emploi du Temps - Impression' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="http://localhost:8080/assets/fontawesome/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-break { page-break-before: always; }
            body { margin: 0; padding: 20px; }
            .container { max-width: none; }
        }
        
        .timetable-grid {
            display: grid;
            grid-template-columns: 80px repeat(5, 1fr);
            gap: 1px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        
        .timetable-header {
            background-color: #3273dc;
            color: white;
            padding: 10px;
            text-align: center;
            font-weight: bold;
        }
        
        .timetable-cell {
            background-color: white;
            padding: 8px;
            min-height: 60px;
            border: 1px solid #ddd;
        }
        
        .time-slot {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }
        
        .session-info {
            font-size: 0.85em;
            line-height: 1.2;
        }
        
        .session-subject {
            font-weight: bold;
            color: #3273dc;
        }
        
        .session-teacher {
            color: #666;
            font-style: italic;
        }
        
        .session-time {
            color: #888;
            font-size: 0.8em;
        }
        
        .summary-card {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .summary-label {
            font-weight: bold;
        }
        
        .summary-value {
            color: #3273dc;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête d'impression -->
        <div class="no-print" style="margin-bottom: 20px;">
            <div class="level">
                <div class="level-left">
                    <div class="level-item">
                        <h1 class="title"><?= $title ?? 'Emploi du Temps - Impression' ?></h1>
                    </div>
                </div>
                <div class="level-right">
                    <div class="level-item">
                        <button class="button is-primary" onclick="window.print()">
                            <span class="icon">
                                <i class="fas fa-print"></i>
                            </span>
                            <span>Imprimer</span>
                        </button>
                        <a href="<?= base_url('admin/etudes/timetable/print') ?>" class="button is-info ml-2">
                            <span class="icon">
                                <i class="fas fa-arrow-left"></i>
                            </span>
                            <span>Retour</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations de l'impression -->
        <div class="box">
            <div class="columns">
                <div class="column is-6">
                    <h3 class="title is-4">Informations de l'impression</h3>
                    <div class="content">
                        <p><strong>Période :</strong> <?= date('d/m/Y', strtotime($filters['start_date'])) ?> - <?= date('d/m/Y', strtotime($filters['end_date'])) ?></p>
                        <p><strong>Année académique :</strong> <?= esc($filters['academic_year']) ?></p>
                        <p><strong>Généré le :</strong> <?= date('d/m/Y à H:i') ?></p>
                    </div>
                </div>
                <div class="column is-6">
                    <h3 class="title is-4">Filtres appliqués</h3>
                    <div class="content">
                        <?php if (!empty($filters['class_id'])): ?>
                            <p><strong>Classe :</strong> 
                                <?php 
                                $class = array_filter($timetables, function($t) use ($filters) { 
                                    return $t['class_id'] == $filters['class_id']; 
                                });
                                echo esc(reset($class)['class_name'] ?? 'N/A');
                                ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($filters['teacher_id'])): ?>
                            <p><strong>Enseignant :</strong> 
                                <?php 
                                $teacher = array_filter($timetables, function($t) use ($filters) { 
                                    return $t['teacher_id'] == $filters['teacher_id']; 
                                });
                                echo esc(reset($teacher)['teacher_name'] ?? 'N/A');
                                ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($filters['subject_id'])): ?>
                            <p><strong>Matière :</strong> 
                                <?php 
                                $subject = array_filter($timetables, function($t) use ($filters) { 
                                    return $t['subject_id'] == $filters['subject_id']; 
                                });
                                echo esc(reset($subject)['subject_name'] ?? 'N/A');
                                ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Résumé -->
        <?php if (isset($summary)): ?>
        <div class="summary-card">
            <h3 class="title is-4">Résumé</h3>
            <div class="columns is-multiline">
                <div class="column is-3">
                    <div class="summary-item">
                        <span class="summary-label">Total sessions :</span>
                        <span class="summary-value"><?= $summary['total_sessions'] ?></span>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="summary-item">
                        <span class="summary-label">Classes concernées :</span>
                        <span class="summary-value"><?= $summary['classes_count'] ?></span>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="summary-item">
                        <span class="summary-label">Enseignants :</span>
                        <span class="summary-value"><?= $summary['teachers_count'] ?></span>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="summary-item">
                        <span class="summary-label">Matières :</span>
                        <span class="summary-value"><?= $summary['subjects_count'] ?></span>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="summary-item">
                        <span class="summary-label">Jours couverts :</span>
                        <span class="summary-value"><?= $summary['days_covered'] ?></span>
                    </div>
                </div>
                <div class="column is-3">
                    <div class="summary-item">
                        <span class="summary-label">Total heures :</span>
                        <span class="summary-value"><?= number_format($summary['total_hours'], 1) ?>h</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Emploi du temps -->
        <?php if (!empty($timetables)): ?>
            <?php 
            // Grouper par classe
            $classesTimetables = [];
            foreach ($timetables as $timetable) {
                $classId = $timetable['class_id'];
                if (!isset($classesTimetables[$classId])) {
                    $classesTimetables[$classId] = [
                        'name' => $timetable['class_name'],
                        'timetables' => []
                    ];
                }
                $classesTimetables[$classId]['timetables'][] = $timetable;
            }
            ?>

            <?php foreach ($classesTimetables as $classId => $classData): ?>
                <div class="print-break">
                    <h2 class="title is-3"><?= esc($classData['name']) ?></h2>
                    
                    <div class="timetable-grid">
                        <!-- En-têtes -->
                        <div class="timetable-header time-slot">Heure</div>
                        <div class="timetable-header">Lundi</div>
                        <div class="timetable-header">Mardi</div>
                        <div class="timetable-header">Mercredi</div>
                        <div class="timetable-header">Jeudi</div>
                        <div class="timetable-header">Vendredi</div>

                        <!-- Créneaux horaires -->
                        <?php 
                        $timeSlots = [
                            '08:00:00' => '08:00',
                            '09:00:00' => '09:00',
                            '10:00:00' => '10:00',
                            '11:00:00' => '11:00',
                            '12:00:00' => '12:00',
                            '14:00:00' => '14:00',
                            '15:00:00' => '15:00',
                            '16:00:00' => '16:00',
                            '17:00:00' => '17:00'
                        ];
                        
                        foreach ($timeSlots as $time => $displayTime):
                        ?>
                            <div class="timetable-cell time-slot"><?= $displayTime ?></div>
                            
                            <?php for ($day = 1; $day <= 5; $day++): ?>
                                <div class="timetable-cell">
                                    <?php
                                    $session = array_filter($classData['timetables'], function($t) use ($day, $time) {
                                        return $t['day_of_week'] == $day && $t['start_time'] == $time;
                                    });
                                    
                                    if (!empty($session)):
                                        $session = reset($session);
                                    ?>
                                        <div class="session-info">
                                            <div class="session-subject"><?= esc($session['subject_name']) ?></div>
                                            <div class="session-teacher"><?= esc($session['teacher_name'] ?: 'N/A') ?></div>
                                            <div class="session-time">
                                                <?= substr($session['start_time'], 0, 5) ?> - <?= substr($session['end_time'], 0, 5) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="notification is-warning">
                <p>Aucun emploi du temps trouvé avec les critères sélectionnés.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-print option
        <?php if (isset($filters['print_format']) && $filters['print_format'] === 'pdf'): ?>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        };
        <?php endif; ?>
    </script>
</body>
</html>


