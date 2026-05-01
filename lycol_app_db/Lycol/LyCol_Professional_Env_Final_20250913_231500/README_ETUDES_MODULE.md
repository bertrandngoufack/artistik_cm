# Module Études - Documentation

## Vue d'ensemble

Le module Études a été entièrement mis à jour et rendu conforme au module Scolarité. Il gère maintenant :

- **Cycles éducatifs** (Primaire, Secondaire, Supérieur)
- **Classes** avec lien vers les cycles
- **Matières** avec coefficients
- **Emplois du temps** avec gestion des conflits
- **Assignations enseignants** par classe et matière

## Tables de base de données créées

### 1. `cycles`
- Gestion des cycles éducatifs
- Champs : id, name, code, description, is_active, created_at, updated_at

### 2. `classes` (mise à jour)
- Ajout de la colonne `cycle_id` pour lier les classes aux cycles
- Ajout de la colonne `level` pour le niveau de la classe
- Ajout de la colonne `description`

### 3. `subjects`
- Gestion des matières
- Champs : id, name, code, description, coefficient, is_active, created_at, updated_at

### 4. `class_subjects`
- Table de liaison entre classes et matières
- Champs : id, class_id, subject_id, hours_per_week, created_at, updated_at

### 5. `timetables`
- Gestion des emplois du temps
- Champs : id, class_id, day_of_week, start_time, end_time, subject_id, teacher_id, room, is_active, created_at, updated_at

### 6. `teacher_assignments`
- Assignations enseignants-classes-matières
- Champs : id, teacher_id, class_id, subject_id, is_principal, academic_year, is_active, created_at, updated_at

## Modèles créés

### 1. `CycleModel` (`app/Models/CycleModel.php`)
- Gestion des cycles éducatifs
- Méthodes : getActiveCycles(), getCycleWithClasses(), getCycleStats()

### 2. `TimetableModel` (`app/Models/TimetableModel.php`)
- Gestion des emplois du temps
- Méthodes : getClassTimetable(), getTeacherTimetable(), checkConflicts(), checkTeacherConflicts()

### 3. `TeacherAssignmentModel` (`app/Models/TeacherAssignmentModel.php`)
- Gestion des assignations enseignants
- Méthodes : getTeacherAssignments(), getClassAssignments(), getClassPrincipalTeacher()

## Contrôleur mis à jour

### `Etudes` (`app/Controllers/Etudes.php`)
- Ajout de la gestion des cycles
- Gestion complète des emplois du temps
- Gestion des assignations enseignants
- Validation et gestion des conflits

## Routes ajoutées

```php
// Gestion des cycles
GET  /admin/etudes/cycles
GET  /admin/etudes/cycles/create
POST /admin/etudes/cycles/store
GET  /admin/etudes/cycles/{id}/edit
POST /admin/etudes/cycles/{id}/update
GET  /admin/etudes/cycles/{id}/delete

// Gestion des classes (mise à jour)
GET  /admin/etudes/classes/{id}/view

// Gestion des emplois du temps
GET  /admin/etudes/timetable/create
POST /admin/etudes/timetable/store
GET  /admin/etudes/timetable/{id}/edit
POST /admin/etudes/timetable/{id}/update
GET  /admin/etudes/timetable/{id}/delete
GET  /admin/etudes/timetable/class/{id}

// Gestion des assignations
GET  /admin/etudes/assignments
GET  /admin/etudes/assignments/create
POST /admin/etudes/assignments/store
GET  /admin/etudes/assignments/{id}/edit
POST /admin/etudes/assignments/{id}/update
GET  /admin/etudes/assignments/{id}/delete
```

## Intégration avec le module Scolarité

Le module Scolarité a été mis à jour pour utiliser les classes du module Études :

### Modifications apportées :

1. **Contrôleur Scolarité** :
   - Ajout de l'import `CycleModel`
   - Ajout de la propriété `$cycleModel`
   - Mise à jour de la méthode `students()` pour inclure le filtre par cycle
   - Mise à jour de la méthode `getActiveClasses()` pour filtrer par cycle

2. **Vue des élèves** :
   - Ajout du filtre par cycle
   - JavaScript pour filtrer les classes dynamiquement selon le cycle sélectionné

## Scripts de mise à jour

### 1. `create_new_tables.sql`
- Création des nouvelles tables
- Insertion des données de test

### 2. `update_classes_table.sql`
- Mise à jour de la table `classes` existante
- Ajout des colonnes `cycle_id`, `level`, `description`

### 3. `update_scolarite_for_etudes.php`
- Mise à jour du module Scolarité pour utiliser les classes du module Études

### 4. `test_etudes_module.php`
- Script de test pour vérifier le bon fonctionnement du module

## Fonctionnalités principales

### 1. Gestion des cycles
- Création, modification, suppression de cycles
- Statistiques par cycle
- Liaison avec les classes

### 2. Gestion des classes
- Création de classes avec cycle associé
- Gestion des niveaux et capacités
- Statistiques par classe

### 3. Gestion des matières
- Création de matières avec coefficients
- Liaison avec les classes
- Gestion des heures par semaine

### 4. Emplois du temps
- Création d'emplois du temps par classe
- Gestion des conflits (classe et enseignant)
- Affichage par classe et par enseignant

### 5. Assignations enseignants
- Assignation d'enseignants à des classes-matières
- Gestion des enseignants principaux
- Gestion par année académique

## Utilisation

### Accès au module
```
http://localhost:8080/admin/etudes
```

### Navigation
1. **Dashboard** : Vue d'ensemble avec statistiques
2. **Cycles** : Gestion des cycles éducatifs
3. **Classes** : Gestion des classes avec cycles
4. **Matières** : Gestion des matières
5. **Emplois du temps** : Gestion des emplois du temps
6. **Assignations** : Gestion des assignations enseignants

### Filtres dans le module Scolarité
- Filtre par cycle (nouveau)
- Filtre par classe (mis à jour pour utiliser les cycles)
- Filtre par année académique
- Filtre par statut
- Recherche textuelle

## Sécurité et validation

- Validation des données côté serveur
- Vérification des conflits d'emploi du temps
- Vérification des assignations en double
- Gestion des contraintes de clés étrangères

## Maintenance

### Vérification du bon fonctionnement
```bash
php test_etudes_module.php
```

### Mise à jour de la base de données
```bash
mysql -h 100.69.65.33 -P 13306 -u root -pBateau123 lycol_db < create_new_tables.sql
```

### Mise à jour du module Scolarité
```bash
php update_scolarite_for_etudes.php
```

## Support

Pour toute question ou problème :
1. Vérifiez les logs d'erreur
2. Exécutez le script de test
3. Vérifiez la structure de la base de données
4. Consultez la documentation CodeIgniter 4
