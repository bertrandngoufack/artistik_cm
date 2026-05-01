<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */



// Route par défaut
$routes->get('/', 'Home::index');

// Routes d'authentification
$routes->group('auth', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('login', 'Auth::login');
    $routes->post('authenticate', 'Auth::authenticate');
    $routes->get('logout', 'Auth::logout');
    $routes->get('parents', 'Auth::parents');
    $routes->post('authenticate-parent', 'Auth::authenticateParent');
    $routes->get('mobile', 'Auth::mobile');
    $routes->post('authenticate-mobile', 'Auth::authenticateMobile');
    $routes->get('change-password', 'Auth::changePassword');
    $routes->post('update-password', 'Auth::updatePassword');
});

// Routes d'administration
$routes->group('admin', ['namespace' => 'App\Controllers', 'filter' => 'auth'], function($routes) {
    // Dashboard
    $routes->get('dashboard', 'Admin::dashboard');
    $routes->get('test-dashboard', 'TestAdmin::dashboard');
    $routes->get('simple', 'SimpleAdmin::index');
    $routes->get('simple-test', 'SimpleAdmin::test');
    
    // Module Économat
    $routes->group('economat', function($routes) {
        $routes->get('/', 'Economat::index');
        $routes->get('payments', 'Economat::payments');
        $routes->get('payments/create', 'Economat::createPayment');
        $routes->post('payments/store', 'Economat::storePayment');
        $routes->get('payments/(:num)', 'Economat::viewPayment/$1');
        $routes->get('payments/(:num)/edit', 'Economat::editPayment/$1');
        $routes->post('payments/(:num)/update', 'Economat::updatePayment/$1');
        $routes->get('payments/(:num)/delete', 'Economat::deletePayment/$1');
        $routes->get('payments/(:num)/print', 'Economat::printReceipt/$1');
        $routes->get('payments/(:num)/pdf', 'Economat::exportReceiptPDF/$1');
        $routes->get('payments/(:num)/reminder', 'Economat::sendReminder/$1');
        $routes->get('payments/send-reminders', 'Economat::sendReminder');
        $routes->get('reminders', 'Economat::reminders');
        $routes->get('reminders/create', 'Economat::createReminder');
        $routes->post('reminders/store', 'Economat::storeReminder');
        $routes->get('reminders/(:num)/edit', 'Economat::editReminder/$1');
        $routes->post('reminders/(:num)/update', 'Economat::updateReminder/$1');
        $routes->get('reminders/(:num)/delete', 'Economat::deleteReminder/$1');
        $routes->get('reminders/(:num)/send', 'Economat::sendReminder/$1');
        $routes->get('notifications', 'Economat::notifications');
        $routes->get('notifications/send', 'Economat::sendNotification');
        $routes->post('notifications/send', 'Economat::sendNotification');
        $routes->get('notifications/history', 'Economat::notificationHistory');
        $routes->get('fees', 'Economat::fees');
        $routes->get('reports', 'Economat::reports');
        $routes->get('reports/export/csv', 'Economat::exportToCSV');
        $routes->get('reports/export/pdf', 'Economat::exportToPDF');
    });
    
    // Module Scolarité
    $routes->group('scolarite', function($routes) {
        $routes->get('/', 'Scolarite::index');
        $routes->get('students', 'Scolarite::students');
        $routes->get('students/create', 'Scolarite::createStudent');
        $routes->post('students/store', 'Scolarite::storeStudent');
        $routes->get('students/(:num)/edit', 'Scolarite::editStudent/$1');
        $routes->post('students/(:num)/update', 'Scolarite::updateStudent/$1');
        $routes->get('students/(:num)/delete', 'Scolarite::deleteStudent/$1');
        $routes->get('students/(:num)/view', 'Scolarite::viewStudent/$1');
        $routes->get('absences', 'Scolarite::absences');
        $routes->get('absences/create', 'Scolarite::createAbsence');
        $routes->post('absences/store', 'Scolarite::storeAbsence');
        $routes->get('absences/(:num)/edit', 'Scolarite::editAbsence/$1');
        $routes->post('absences/(:num)/update', 'Scolarite::updateAbsence/$1');
        $routes->get('absences/(:num)/delete', 'Scolarite::deleteAbsence/$1');
        $routes->get('absences/(:num)/view', 'Scolarite::viewAbsence/$1');
        $routes->get('discipline', 'Scolarite::discipline');
        $routes->get('discipline/create', 'Scolarite::createIncident');
        $routes->post('discipline/store', 'Scolarite::storeIncident');
        $routes->get('discipline/(:num)/edit', 'Scolarite::editIncident/$1');
        $routes->post('discipline/(:num)/update', 'Scolarite::updateIncident/$1');
        $routes->get('discipline/(:num)/delete', 'Scolarite::deleteIncident/$1');
        $routes->get('discipline/(:num)/view', 'Scolarite::viewIncident/$1');
        $routes->get('discipline/(:num)/notify', 'Scolarite::sendDisciplineNotification/$1');
        $routes->get('discipline/notifications', 'Scolarite::disciplineNotifications');
        $routes->get('discipline/notifications/send-all', 'Scolarite::sendDisciplineNotification');
        $routes->get('reports', 'Scolarite::reports');
        $routes->get('reports/export/csv', 'Scolarite::exportToCSV');
    });
    
    // Module Études
    $routes->group('etudes', function($routes) {
        $routes->get('/', 'Etudes::index');
        
        // Gestion des cycles
        $routes->get('cycles', 'Etudes::cycles');
        $routes->get('cycles/create', 'Etudes::createCycle');
        $routes->post('cycles/store', 'Etudes::storeCycle');
        $routes->get('cycles/edit/(:num)', 'Etudes::editCycle/$1');
        $routes->post('cycles/update/(:num)', 'Etudes::updateCycle/$1');
        $routes->get('cycles/delete/(:num)', 'Etudes::deleteCycle/$1');
        $routes->post('cycles/delete/(:num)', 'Etudes::deleteCycle/$1');
        
        // Gestion des classes
        $routes->get('classes', 'Etudes::classes');
        $routes->get('classes/create', 'Etudes::createClass');
        $routes->post('classes/store', 'Etudes::storeClass');
        $routes->get('classes/view/(:num)', 'Etudes::viewClass/$1');
        $routes->get('classes/(:num)/edit', 'Etudes::editClass/$1');
        $routes->post('classes/(:num)/update', 'Etudes::updateClass/$1');
        $routes->get('classes/(:num)/delete', 'Etudes::deleteClass/$1');
        $routes->post('classes/(:num)/delete', 'Etudes::deleteClass/$1');
        
        // Gestion des matières
        $routes->get('subjects', 'Etudes::subjects');
        $routes->get('subjects/create', 'Etudes::createSubject');
        $routes->post('subjects/store', 'Etudes::storeSubject');
        $routes->get('subjects/view/(:num)', 'Etudes::viewSubject/$1');
        $routes->get('subjects/edit/(:num)', 'Etudes::editSubject/$1');
        $routes->post('subjects/update/(:num)', 'Etudes::updateSubject/$1');
        $routes->post('subjects/delete/(:num)', 'Etudes::deleteSubject/$1');
        
        // Gestion des emplois du temps
        $routes->get('timetable', 'Etudes::timetable');
        $routes->get('timetable/create', 'Etudes::createTimetable');
        $routes->post('timetable/store', 'Etudes::storeTimetable');
        $routes->get('timetable/(:num)/edit', 'Etudes::editTimetable/$1');
        $routes->post('timetable/(:num)/update', 'Etudes::updateTimetable/$1');
        $routes->get('timetable/(:num)/delete', 'Etudes::deleteTimetable/$1');
        $routes->get('timetable/class/(:num)', 'Etudes::viewClassTimetable/$1');
        $routes->get('timetable/print', 'Etudes::printTimetable');
        $routes->post('timetable/print', 'Etudes::generatePrintTimetable');
        
        // Gestion des assignations
        $routes->get('assignments', 'Etudes::assignments');
        $routes->get('assignments/create', 'Etudes::createAssignment');
        $routes->post('assignments/store', 'Etudes::storeAssignment');
        $routes->get('assignments/view/(:num)', 'Etudes::viewAssignment/$1');
        $routes->get('assignments/edit/(:num)', 'Etudes::editAssignment/$1');
        $routes->post('assignments/update/(:num)', 'Etudes::updateAssignment/$1');
        $routes->get('assignments/delete/(:num)', 'Etudes::deleteAssignment/$1');
        
        // Rapports
        $routes->get('reports', 'Etudes::reports');
        $routes->get('reports/generate', 'Etudes::generateReport');
        $routes->post('reports/generate', 'Etudes::generateReport');
        $routes->get('reports/export/csv', 'Etudes::exportToCSV');
        $routes->get('reports/export/pdf', 'Etudes::exportToPDF');
    });
    
    // Module Examens
    $routes->group('examens', function($routes) {
        $routes->get('/', 'Examens::index');
        $routes->get('exams', 'Examens::exams');
        $routes->get('exams/create', 'Examens::createExam');
        $routes->post('exams/store', 'Examens::storeExam');
        $routes->get('exams/(:num)/edit', 'Examens::editExam/$1');
        $routes->post('exams/(:num)/update', 'Examens::updateExam/$1');
        $routes->get('exams/(:num)/delete', 'Examens::deleteExam/$1');
        $routes->get('exams/(:num)/view', 'Examens::viewExam/$1');
        
        $routes->get('grades', 'Examens::grades');
        $routes->get('grades/enter/(:num)', 'Examens::enterGrades/$1');
        $routes->post('grades/store', 'Examens::storeGrades');
        
        $routes->get('report-cards', 'Examens::reportCards');
        $routes->get('report-cards/generate', 'Examens::generateReportCards');
        $routes->post('report-cards/generate', 'Examens::generateReportCards');
        $routes->post('report-cards/generate-pdf', 'Examens::generatePDFReportCards');
        
        $routes->get('statistics', 'Examens::statistics');
        $routes->get('statistics/export', 'Examens::exportStatistics');
        
        $routes->get('academic-periods', 'Examens::academicPeriods');
        $routes->post('academic-periods/update', 'Examens::updateAcademicPeriod');
        $routes->post('academic-periods/create-year', 'Examens::createAcademicYear');
    });
    
    // Module Statistiques
    $routes->group('statistiques', function($routes) {
        $routes->get('/', 'Admin::statistiques');
        $routes->get('students', 'Statistiques::students');
        $routes->get('grades', 'Statistiques::grades');
        $routes->get('payments', 'Statistiques::payments');
        $routes->get('absences', 'Statistiques::absences');
        $routes->get('reports', 'Statistiques::reports');
        $routes->get('teachers', 'Statistiques::teachers');
        $routes->get('academic', 'Statistiques::academic');
        $routes->get('financial', 'Statistiques::financial');
        $routes->get('attendance', 'Statistiques::attendance');
        $routes->get('export/(:any)', 'Statistiques::export/$1');
        $routes->post('generate-custom-report', 'Statistiques::generateCustomReport');
    });
    
    // Module Bibliothèque
    $routes->group('bibliotheque', function($routes) {
        $routes->get('/', 'Bibliotheque::index');
        
        // Gestion des livres
        $routes->get('books', 'Bibliotheque::books');
        $routes->get('books/create', 'Bibliotheque::createBook');
        $routes->get('books/add', 'Bibliotheque::createBook');
        $routes->post('books/store', 'Bibliotheque::storeBook');
        $routes->get('books/(:num)', 'Bibliotheque::showBook/$1');
        $routes->get('books/(:num)/edit', 'Bibliotheque::editBook/$1');
        $routes->post('books/(:num)/update', 'Bibliotheque::updateBook/$1');
        $routes->get('books/(:num)/delete', 'Bibliotheque::deleteBook/$1');
        $routes->post('books/(:num)/delete', 'Bibliotheque::deleteBook/$1');
        
        // Gestion des emprunts
        $routes->get('loans', 'Bibliotheque::loans');
        $routes->get('loans/create', 'Bibliotheque::createLoan');
        $routes->get('loans/add', 'Bibliotheque::createLoan');
        $routes->post('loans/store', 'Bibliotheque::storeLoan');
        $routes->get('loans/(:num)', 'Bibliotheque::showLoan/$1');
        $routes->get('loans/(:num)/edit', 'Bibliotheque::editLoan/$1');
        $routes->post('loans/(:num)/update', 'Bibliotheque::updateLoan/$1');
        $routes->get('loans/(:num)/return', 'Bibliotheque::returnLoan/$1');
        $routes->post('loans/(:num)/return', 'Bibliotheque::returnLoan/$1');
        $routes->get('loans/(:num)/delete', 'Bibliotheque::deleteLoan/$1');
        $routes->post('loans/(:num)/delete', 'Bibliotheque::deleteLoan/$1');
        
        // Gestion des membres
        $routes->get('members', 'Bibliotheque::members');
        $routes->get('members/create', 'Bibliotheque::createMember');
        $routes->get('members/add', 'Bibliotheque::createMember');
        $routes->post('members/store', 'Bibliotheque::storeMember');
        $routes->get('members/(:num)', 'Bibliotheque::showMember/$1');
        $routes->get('members/(:num)/edit', 'Bibliotheque::editMember/$1');
        $routes->post('members/(:num)/update', 'Bibliotheque::updateMember/$1');
        $routes->get('members/(:num)/delete', 'Bibliotheque::deleteMember/$1');
        $routes->post('members/(:num)/delete', 'Bibliotheque::deleteMember/$1');
        
        // Rapports
        $routes->get('reports', 'Bibliotheque::reports');
        $routes->get('reports/books', 'Bibliotheque::reportsBooks');
        $routes->get('reports/loans', 'Bibliotheque::reportsLoans');
        $routes->get('reports/members', 'Bibliotheque::reportsMembers');
        $routes->get('reports/export/(:any)', 'Bibliotheque::exportReport/$1');
    });
    
    // Module Messagerie
    $routes->group('messagerie', function($routes) {
        $routes->get('/', 'Admin::messagerie');
        $routes->get('messages', 'Messagerie::messages');
        $routes->get('messages/create', 'Messagerie::createMessage');
        $routes->get('compose', 'Messagerie::createMessage');
        $routes->post('messages/store', 'Messagerie::storeMessage');
        $routes->get('messages/(:num)/view', 'Messagerie::viewMessage/$1');
        $routes->get('messages/(:num)/delete', 'Messagerie::deleteMessage/$1');
        $routes->get('messages/(:num)/resend', 'Messagerie::resendMessage/$1');
        $routes->get('templates', 'Messagerie::templates');
        $routes->get('templates/create', 'Messagerie::createTemplate');
        $routes->post('templates/store', 'Messagerie::storeTemplate');
        $routes->get('subscribers', 'Messagerie::subscribers');
        $routes->get('settings', 'Messagerie::settings');
        $routes->get('discipline', 'Messagerie::sendDisciplineNotification');
        $routes->post('discipline/send', 'Messagerie::processDisciplineNotification');
        $routes->get('send-bulletin', 'Messagerie::sendBulletin');
        $routes->post('send-bulletin/process', 'Messagerie::processBulletinSending');
        $routes->get('send-discipline', 'Messagerie::sendDisciplineNotification');
        $routes->post('send-discipline/process', 'Messagerie::processDisciplineNotification');
    });
    
    // Module Enseignants
    $routes->group('enseignants', function($routes) {
        $routes->get('/', 'Enseignants::index');
        $routes->get('list', 'Enseignants::list');
        $routes->get('create', 'Enseignants::create');
        $routes->post('store', 'Enseignants::store');
        $routes->get('show/(:num)', 'Enseignants::show/$1');
        $routes->get('edit/(:num)', 'Enseignants::edit/$1');
        $routes->post('update/(:num)', 'Enseignants::update/$1');
        $routes->get('delete/(:num)', 'Enseignants::delete/$1');
        $routes->get('subjects/(:num)', 'Enseignants::subjects/$1');
        $routes->post('assign-subject', 'Enseignants::assignSubject');
        $routes->post('remove-subject', 'Enseignants::removeSubject');
        $routes->get('classes/(:num)', 'Enseignants::classes/$1');
        $routes->post('assign-class', 'Enseignants::assignClass');
        $routes->post('remove-class', 'Enseignants::removeClass');
        $routes->get('statistics', 'Enseignants::statistics');
        $routes->get('export/(:any)', 'Enseignants::export/$1');
    });
    
    // Module Sécurité
    $routes->group('securite', function($routes) {
        $routes->get('/', 'Securite::index');
        $routes->get('users', 'Securite::users');
        $routes->get('users/create', 'Securite::createUser');
        $routes->post('users/store', 'Securite::storeUser');
        $routes->get('users/(:num)', 'Securite::viewUser/$1');
        $routes->get('users/(:num)/edit', 'Securite::editUser/$1');
        $routes->post('users/(:num)/update', 'Securite::updateUser/$1');
        $routes->get('users/(:num)/delete', 'Securite::deleteUser/$1');
        $routes->get('users/(:num)/permissions', 'Securite::userPermissions/$1');
        $routes->post('users/(:num)/permissions', 'Securite::updateUserPermissions/$1');
        $routes->get('roles', 'Securite::roles');
        $routes->get('roles/create', 'Securite::createRole');
        $routes->post('roles/store', 'Securite::storeRole');
        $routes->get('roles/(:num)', 'Securite::viewRole/$1');
        $routes->get('roles/(:num)/edit', 'Securite::editRole/$1');
        $routes->post('roles/(:num)/update', 'Securite::updateRole/$1');
        $routes->get('roles/(:num)/delete', 'Securite::deleteRole/$1');
        $routes->get('roles/(:num)/permissions', 'Securite::rolePermissions/$1');
        $routes->post('roles/(:num)/permissions', 'Securite::updateRolePermissions/$1');
        $routes->get('licenses', 'Admin::licenses');
        $routes->post('licenses/generate', 'Admin::generateLicense');
        $routes->get('logs', 'Securite::logs');
        $routes->get('permissions', 'Securite::permissions');
        $routes->get('audit', 'Securite::audit');
    });
    
    // Module Configuration
    $routes->group('configuration', function($routes) {
        $routes->get('/', 'Configuration::index');
        $routes->get('general', 'Configuration::general');
        $routes->post('save-general', 'Configuration::saveGeneral');
        $routes->get('email', 'Configuration::email');
        $routes->post('save-email', 'Configuration::saveEmail');
        $routes->post('test-email', 'Configuration::testEmail');
        $routes->get('sms', 'Configuration::sms');
        $routes->post('save-sms', 'Configuration::saveSMS');
        $routes->post('test-sms', 'Configuration::testSMS');
        $routes->get('whatsapp', 'Configuration::whatsapp');
        $routes->post('save-whatsapp', 'Configuration::saveWhatsApp');
        $routes->post('test-whatsapp', 'Configuration::testWhatsApp');
        $routes->get('appearance', 'Configuration::appearance');
        $routes->post('save-appearance', 'Configuration::saveAppearance');
        $routes->get('license', 'Configuration::license');
        $routes->get('activate-definitive-license', 'Configuration::activateDefinitiveLicense');
        $routes->get('check-license', 'Configuration::checkLicense');
        $routes->get('system-stats-api', 'Configuration::getSystemStatsApi');
        $routes->get('diagnostics', 'Configuration::diagnostics');
        $routes->get('backup', 'Configuration::backup');
        $routes->get('create-backup', 'Configuration::createBackup');
        $routes->get('logs', 'Configuration::logs');
        $routes->post('clear-cache', 'Configuration::clearCache');
        $routes->get('generate-report', 'Configuration::generateReport');
        
        // Routes pour les paramètres système
        $routes->get('settings', 'Settings::index');
        $routes->get('settings/create', 'Settings::create');
        $routes->post('settings/store', 'Settings::store');
        $routes->get('settings/(:num)', 'Settings::show/$1');
        $routes->get('settings/(:num)/edit', 'Settings::edit/$1');
        $routes->post('settings/(:num)/update', 'Settings::update/$1');
        $routes->get('settings/(:num)/delete', 'Settings::delete/$1');
    });
    
    // Export
    $routes->get('export/(:any)', 'Admin::export/$1');
});

// Routes pour les parents
$routes->group('parents', ['namespace' => 'App\Controllers', 'filter' => 'parent'], function($routes) {
    $routes->get('dashboard', 'Parents::dashboard');
    $routes->get('grades', 'Parents::grades');
    $routes->get('absences', 'Parents::absences');
    $routes->get('payments', 'Parents::payments');
    $routes->get('discipline', 'Parents::discipline');
    $routes->get('profile', 'Parents::profile');
});

// Routes pour l'interface mobile
$routes->group('mobile', ['namespace' => 'App\Controllers', 'filter' => 'mobile'], function($routes) {
    $routes->get('grades', 'Mobile::grades');
    $routes->get('grades/enter/(:num)', 'Mobile::enterGrades/$1');
    $routes->post('grades/store', 'Mobile::storeGrades');
    $routes->get('absences', 'Mobile::absences');
    $routes->get('absences/create', 'Mobile::createAbsence');
    $routes->post('absences/store', 'Mobile::storeAbsence');
    $routes->get('profile', 'Mobile::profile');
});

// Routes API
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
    $routes->get('students/(:any)/(:num)', 'Students::getByMatriculeAndBirthYear/$1/$2');
    $routes->get('grades/(:any)/(:num)', 'Grades::getByMatriculeAndBirthYear/$1/$2');
    $routes->get('absences/(:any)/(:num)', 'Absences::getByMatriculeAndBirthYear/$1/$2');
    $routes->get('discipline/(:any)/(:num)', 'Discipline::getByMatriculeAndBirthYear/$1/$2');
    $routes->get('export/(:any)', 'Export::exportData/$1');
});

// Routes de documentation API
$routes->get('api/docs', 'Api\Docs::index');
$routes->get('api/docs/(:any)', 'Api\Docs::show/$1');

// Routes pour les pages publiques
$routes->get('about', 'Pages::about');
$routes->get('contact', 'Pages::contact');
$routes->get('help', 'Pages::help');
$routes->get('privacy', 'Pages::privacy');
$routes->get('terms', 'Pages::terms');

// Routes pour les erreurs
$routes->set404Override('App\Controllers\Errors::show404');
$routes->get('error/403', 'Errors::show403');
$routes->get('error/500', 'Errors::show500');

// Routes pour les tests (uniquement en développement)
if (ENVIRONMENT === 'development') {
    $routes->get('test/license', 'Test::license');
    $routes->get('test/database', 'Test::database');
    $routes->get('test/email', 'Test::email');
}
