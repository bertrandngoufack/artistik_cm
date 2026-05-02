<?php
/**
 * Pré-remplit la table dictionnaire TranslatePress (fr_FR -> en_US)
 * avec toutes les traductions EN de la one-page Artistik.
 *
 * Exécution :
 *   wp eval-file scripts/translatepress-seed-en.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TRP_Translate_Press' ) ) {
    WP_CLI::error( 'TranslatePress n\'est pas chargé.' );
    return;
}

$default_language = 'fr_FR';
$language_code    = 'en_US';

$trp   = TRP_Translate_Press::get_trp_instance();
$query = $trp->get_component( 'query' );

if ( ! $query ) {
    WP_CLI::error( 'Composant query introuvable.' );
    return;
}

$query->check_table( $default_language, $language_code );

global $wpdb;
$table = $wpdb->prefix . 'trp_dictionary_' . strtolower( $default_language ) . '_' . strtolower( $language_code );
$exists = $wpdb->get_var( "SHOW TABLES LIKE '$table'" );
if ( $exists !== $table ) {
    WP_CLI::error( "Table $table introuvable après check_table()." );
    return;
}

WP_CLI::log( "Table prête : $table" );

/*
 * Mapping exhaustif FR -> EN.
 * Les clés correspondent EXACTEMENT aux fragments extraits par TranslatePress
 * (un fragment = un noeud texte du DOM, sans balises).
 */
$dictionary = [
    // Header / brand
    'Artistik — accueil'   => 'Artistik — home',
    'Ouvrir le menu'       => 'Open menu',
    'Menu principal'       => 'Main menu',
    'Sélecteur de langue'  => 'Language selector',
    'Langue actuelle : Français' => 'Current language: French',
    'Démo'                 => 'Demo',

    // Menu items
    'Accueil'              => 'Home',
    'Nos solutions'        => 'Our solutions',
    'SoluMed'              => 'SoluMed',
    'LyCol'                => 'LyCol',
    'Simba'                => 'Simba',
    'Contact'              => 'Contact',
    'Patients (assurés ou non)'   => 'Patients (insured or not)',
    'Caisse'                       => 'Cash desk',
    'Assurances'                   => 'Insurance',
    'Facturation'                  => 'Billing',
    'Dépenses'                     => 'Expenses',
    'Honoraires'                   => 'Doctor fees',
    'Pharmacie'                    => 'Pharmacy',
    'Laboratoire'                  => 'Laboratory',
    'Dossier médical'              => 'Medical records',
    'Élèves & étudiants'           => 'Pupils & students',
    'Élèves &amp; étudiants'       => 'Pupils & students',
    'Professeurs'                  => 'Teachers',
    'Notes'                        => 'Grades',
    'Envoi de SMS'                 => 'Send SMS',
    'Patrimoine & acteurs'         => 'Properties & people',
    'Patrimoine &amp; acteurs'     => 'Properties & people',
    'Contrats & flux financiers'   => 'Contracts & cash flows',
    'Contrats &amp; flux financiers' => 'Contracts & cash flows',
    'Éditions & pilotage'          => 'Reports & monitoring',
    'Éditions &amp; pilotage'      => 'Reports & monitoring',

    // Hero
    'Artistik — logiciels métier'                                  => 'Artistik — business software',
    'Des applications pensées pour votre secteur'                  => 'Apps designed for your industry',
    'Depuis'                                                        => 'Since',
    ', Artistik conçoit des solutions fiables pour la'             => ', Artistik has built reliable solutions for',
    "santé"                                                         => 'healthcare',
    ", l’"                                                          => ', ',
    "éducation"                                                    => 'education',
    " et l’"                                                       => ' and ',
    "immobilier"                                                   => 'real estate',
    '. SoluMed, LyCol et Simba — des outils complets, évolutifs et adaptés à votre organisation.' =>
        '. SoluMed, LyCol and Simba — comprehensive, scalable tools tailored to your organisation.',
    'Découvrir nos solutions'                                      => 'Discover our solutions',
    'Nous contacter'                                               => 'Contact us',
    'Année de création de SoluMed'                                  => 'Year SoluMed was created',
    'familles de produits'                                          => 'product families',
    'orienté gestion métier'                                        => 'focused on business management',

    // Solutions section head
    'Nos solutions logicielles'                                     => 'Our software solutions',
    'Des outils métier pensés pour votre activité'                  => 'Business tools tailored to your activity',
    'Chaque application regroupe des modules ciblés pour couvrir vos processus du quotidien — administratif, finance, utilisateurs et reporting.' =>
        'Each application bundles focused modules covering your day-to-day processes — administration, finance, users and reporting.',

    // Tile badges & subtitles
    'Santé'                                                         => 'Healthcare',
    'Éducation'                                                     => 'Education',
    'Immobilier'                                                    => 'Real estate',
    'Logiciel de gestion des cliniques, hôpitaux et polycliniques'  => 'Management software for clinics, hospitals and polyclinics',
    'Logiciel de gestion pour écoles et universités'                => 'Management software for schools and universities',
    'Logiciel de gestion locative (loyers)'                         => 'Rental management software (rents)',

    // SoluMed intro fragments
    'Dans un monde technologique en perpétuelle évolution, il est primordial de disposer d’un'
        => 'In a constantly evolving technological world, it is essential to have a',
    'système informatique performant'                               => 'high-performance information system',
    ', pour travailler plus rapidement, efficacement, et obtenir des résultats fiables. C’est dans ce cadre que la société Artistik a créé depuis'
        => ' in order to work faster, more efficiently and obtain reliable results. With this in mind, Artistik has been building, since',
    ': un véritable outil de gestion de votre établissement médical (clinique, centre de santé, groupe médical…). SoluMed s’appuie sur de nombreux modules complémentaires.'
        => ': a true management tool for your medical facility (clinic, health centre, medical group…). SoluMed is built on a wide range of complementary modules.',
    'Illustration santé'                                            => 'Healthcare illustration',

    // SoluMed module bullets
    'Ouverture de dossier'                  => 'Open patient file',
    'Admission'                             => 'Admission',
    'Réadmission'                           => 'Re-admission',
    'Hospitalisations'                      => 'Hospital stays',
    'File d’attente'                        => 'Waiting list',
    'Rendez-vous'                           => 'Appointments',
    'Encaissement patient'                  => 'Patient payment',
    'Reçus de caisse (A4 / ticket)'         => 'Receipts (A4 / ticket)',
    'Édition de point de caisse'            => 'Cash report printing',
    'Paramétrage des conventions'           => 'Insurance agreement setup',
    'Recouvrement des factures'             => 'Invoice collection',
    'Facture de consultation'               => 'Consultation invoice',
    'Facture d’hospitalisation'             => 'Hospital stay invoice',
    'Pro-formas'                            => 'Pro-forma invoices',
    'Recouvrement assurances'               => 'Insurance recovery',
    'Saisie des dépenses'                   => 'Record expenses',
    'Situation de trésorerie'               => 'Cash position',
    'Honoraires par médecin'                => 'Fees per doctor',
    'Édition par praticien'                 => 'Reports per practitioner',
    'Ristournes'                            => 'Discounts',
    'Approvisionnements'                    => 'Stock replenishment',
    'Consommations par patient'             => 'Consumption per patient',
    'État de stock'                         => 'Stock status',
    'Inventaire'                            => 'Inventory',
    'Bons de commande'                      => 'Purchase orders',
    'Résultats d’examens'                   => 'Exam results',
    'Réactifs'                              => 'Reagents',
    'État financier du labo'                => 'Lab financial report',
    'Saisie ou scan des dossiers'           => 'Record or scan files',
    'Recherche et édition des dossiers patients' => 'Search and print patient files',

    // LyCol intro fragments
    'est développée par Artistik CM : gestion scolaire et universitaire pour le primaire, le secondaire, les grandes écoles et les universités. C’est un espace collaboratif pour l’établissement, les enseignants, les parents, les élèves et étudiants. Application'
        => 'is developed by Artistik CM: a school and university management platform for primary, secondary, higher schools and universities. A collaborative space for the institution, teachers, parents, pupils and students. A',
    'web et mobile'                         => 'web and mobile app',
    ', avec des espaces dédiés aux managers et fondateurs.' => ', with dedicated workspaces for managers and founders.',
    'Illustration éducation'                => 'Education illustration',

    // LyCol bullets
    'Identification'                        => 'Identification',
    'Admissions / Réadmissions'             => 'Admissions / Re-admissions',
    'Inscriptions'                          => 'Enrolments',
    'Listes de classe et indicateurs'       => 'Class rosters & metrics',
    'Attestations, certificats, badges PVC' => 'Certificates & PVC badges',
    'Emploi du temps'                       => 'Timetable',
    'SMS'                                   => 'SMS',
    'Encaissements'                         => 'Payments',
    'Reçus avec photo'                      => 'Receipts with photo',
    'Points de caisse'                      => 'Cash reports',
    'Trésorerie générale & individuelle'    => 'Overall & individual cash flow',
    'Trésorerie générale &amp; individuelle' => 'Overall & individual cash flow',
    'Relances scolarité'                    => 'Tuition reminders',
    'Tableau de bord'                       => 'Dashboard',
    'Frais annexes / facture État'          => 'Extra fees / state invoice',
    'Échéanciers / biométrie / engagement parental' => 'Schedules / biometrics / parental commitment',
    'Saisie des vacations'                  => 'Record teaching hours',
    'Relevés par enseignant'                => 'Statements per teacher',
    'États globaux'                         => 'Overall reports',
    'Listes détaillées'                     => 'Detailed lists',
    'Saisie & mise à jour des notes'        => 'Enter & update grades',
    'Saisie &amp; mise à jour des notes'    => 'Enter & update grades',
    'Relevés semestriels et trimestriels'   => 'Semester & term statements',
    'Relevés élève / étudiant'              => 'Pupil / student statements',
    'Matrice des moyennes / LMD'            => 'Grade matrix / LMD system',
    'Statistiques / éditeur de rapport'     => 'Statistics / report editor',
    'Informations générales'                => 'General announcements',
    'Absences'                              => 'Absences',
    'Rappels de paiement'                   => 'Payment reminders',
    'Notes / Moyennes'                      => 'Grades / Averages',

    // Simba intro fragments
    'Gérer plusieurs biens demande rigueur : impayés, relances, quittances.'
        => 'Managing several properties requires discipline: arrears, reminders, receipts.',
    'automatise ces opérations avec une interface'
        => 'automates these tasks with a',
    'simple et efficace'                    => 'simple and efficient interface',
    ', tout en offrant des fonctions avancées. Quelques clics suffisent pour structurer votre gestion locative.'
        => ', while offering advanced features. A few clicks are enough to structure your rental management.',
    'Illustration immobilier'               => 'Real estate illustration',

    // Simba bullets
    'Appartements'                          => 'Apartments',
    'Bailleurs'                             => 'Landlords',
    'Locataires'                            => 'Tenants',
    'Agents immobiliers'                    => 'Real estate agents',
    'Contrat de bail'                       => 'Lease agreement',
    'Cautions et frais'                     => 'Deposits and fees',
    'Loyers payés'                          => 'Rents paid',
    'Mise à jour des arriérés'              => 'Update arrears',
    'Comptabilité (recettes / dépenses)'    => 'Accounting (income / expenses)',
    'Liste des locataires'                  => 'List of tenants',
    'Liste par bâtiments'                   => 'List by buildings',
    'Reçu de paiement'                      => 'Payment receipt',
    'Point de caisse'                       => 'Cash report',
    'Relevé par locataire'                  => 'Statement per tenant',
    'État de paiement par bâtiment'         => 'Payment status per building',
    'État des arriérés'                     => 'Arrears report',

    // Contact section
    'Un projet ? Parlons-en.'                                       => 'A project? Let’s talk.',
    'Déploiement, démonstration ou accompagnement : l’équipe Artistik vous répond.' =>
        'Deployment, demo or support: the Artistik team is here to help.',
    'Email'                                  => 'Email',
    'Site web'                               => 'Website',
    'Réponse sous 24 h'                      => 'Reply within 24 h',
    'Jours ouvrés — accusé de réception immédiat.' => 'Business days — instant acknowledgment.',

    // Form labels & placeholders
    'Contact Artistik'                       => 'Contact Artistik',
    'Nom complet'                            => 'Full name',
    'Votre nom'                              => 'Your name',
    'Organisation'                           => 'Organisation',
    'Votre structure (optionnel)'            => 'Your organisation (optional)',
    'Téléphone'                              => 'Phone',
    'Solution concernée'                     => 'Topic',
    '— Choisir —'                            => '— Choose —',
    'Information générale'                   => 'General information',
    'SoluMed (santé)'                        => 'SoluMed (healthcare)',
    'LyCol (éducation)'                      => 'LyCol (education)',
    'Simba (immobilier)'                     => 'Simba (real estate)',
    'Démonstration'                          => 'Demonstration',
    'Autre'                                  => 'Other',
    'Votre message'                          => 'Your message',
    'Décrivez votre projet ou votre besoin…' => 'Describe your project or need…',
    'Consentement'                           => 'Consent',
    'J’accepte d’être recontacté par Artistik au sujet de ma demande.' =>
        'I agree to be contacted by Artistik regarding my request.',
    'Envoyer'                                => 'Send',
    'If you are human, leave this field blank.' => 'If you are human, leave this field blank.',

    // Formidable validation messages
    'Nom complet cannot be blank.'           => 'Full name cannot be blank.',
    'Nom complet is invalid'                 => 'Full name is invalid',
    'Organisation is invalid'                => 'Organisation is invalid',
    'Email cannot be blank.'                 => 'Email cannot be blank.',
    'Email is invalid'                       => 'Email is invalid',
    'Téléphone is invalid'                   => 'Phone is invalid',
    'Solution concernée is invalid'          => 'Topic is invalid',
    'Votre message cannot be blank.'         => 'Your message cannot be blank.',
    'Votre message is invalid'               => 'Your message is invalid',
    'Consentement cannot be blank.'          => 'Consent cannot be blank.',
    'Consentement is invalid'                => 'Consent is invalid',

    // Footer
    'Solutions logicielles métier — Santé · Éducation · Immobilier.' =>
        'Business software — Healthcare · Education · Real estate.',
    'Solutions'                              => 'Solutions',
    'Société'                                => 'Company',
    '© 2026 Artistik. Tous droits réservés.' => '© 2026 Artistik. All rights reserved.',

    // TranslatePress floating switcher
    'Change language'                        => 'Change language',
    'Available languages'                    => 'Available languages',
    'Website language selector'              => 'Website language selector',
    'Français'                               => 'Français',
    'English'                                => 'English',
];

$total      = count( $dictionary );
$processed  = 0;
$inserted   = 0;
$updated    = 0;
$skipped    = 0;

WP_CLI::log( "Total entrées : $total" );

$originals = array_keys( $dictionary );
$query->insert_strings( $originals, $language_code, 0 );

$existing = $query->get_string_ids( $originals, $language_code );

$rows = [];
foreach ( $dictionary as $original => $translated ) {
    if ( ! isset( $existing[ $original ] ) ) {
        $skipped++;
        continue;
    }
    $rows[] = [
        'id'          => (int) $existing[ $original ]->id,
        'original'    => $original,
        'translated'  => $translated,
        'status'      => 2, // HUMAN_REVIEWED
        'block_type'  => 0,
        'original_id' => 0,
    ];
    $processed++;
}

if ( ! empty( $rows ) ) {
    $existing_with_original_id = $wpdb->get_results(
        "SELECT id, original_id FROM `$table` WHERE id IN (" . implode( ',', wp_list_pluck( $rows, 'id' ) ) . ')',
        OBJECT_K
    );
    foreach ( $rows as &$row ) {
        if ( isset( $existing_with_original_id[ $row['id'] ]->original_id ) ) {
            $row['original_id'] = (int) $existing_with_original_id[ $row['id'] ]->original_id;
        }
    }
    unset( $row );

    $query->update_strings( $rows, $language_code );
    $updated = count( $rows );
}

WP_CLI::success( sprintf(
    'Traductions appliquées : %d insérées, %d mises à jour, %d ignorées (sur %d).',
    $inserted, $updated, $skipped, $total
) );
