<?php
/**
 * Pass 5 : traductions EN pour Konvoi (transport / flotte) + nouveau hero lead
 * (7 suites) + nouvelle stat (7 familles).
 *
 * @package Artistik_CM
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$default_language = 'fr_FR';
$language_code    = 'en_US';

$trp   = TRP_Translate_Press::get_trp_instance();
$query = $trp->get_component( 'query' );
$query->check_table( $default_language, $language_code );

/* ============================================================
 * Dictionnaire FR → EN
 * ============================================================ */
$dictionary = [

    /* -- Hero lead enrichi (fragments TP) -- */
    'le <strong>transport</strong> et la' => 'transport and',
    'transport'                           => 'transport',
    ', l’agropastoral, le'                => ', agropastoral,',
    '. SoluMed, LyCol, Simba, Boutik, Pastra, Konvoi et Smily — sept suites complètes, évolutives et adaptées à votre organisation.'
        => '. SoluMed, LyCol, Simba, Boutik, Pastra, Konvoi and Smily — seven comprehensive, scalable suites tailored to your organisation.',

    /* -- Sous-titres / badge -- */
    'Transport'                                                                    => 'Transport',
    'Logiciel d’ordres de transport (OT) + flotte véhicule & maintenance'          => 'Transport orders (TO) + fleet & maintenance software',
    'Logiciel d’ordres de transport (OT) + flotte véhicule &#038; maintenance'     => 'Transport orders (TO) + fleet & maintenance software',
    'Illustration transport et flotte'                                             => 'Transport and fleet illustration',

    /* -- Intro produit -- */
    'est la solution Artistik dédiée aux'                  => 'is the Artistik solution dedicated to',
    'transporteurs et logisticiens'                        => 'carriers and logistics operators',
    '. Pilotez vos'                                        => '. Drive your',
    'ordres de transport'                                  => 'transport orders',
    'de bout en bout — création, affectation, exécution, facturation — et gérez votre'
        => 'end-to-end — creation, assignment, execution, invoicing — and manage your',
    'flotte de véhicules'                                  => 'vehicle fleet',
    'avec maintenance préventive, planning des réservations et tableaux de bord temps réel.'
        => 'with preventive maintenance, booking planning and real-time dashboards.',

    /* ============================================================
     * Modules — Konvoi
     * ============================================================ */

    /* Création & gestion des OT */
    'Création & gestion des ordres de transport (OT)'                              => 'Transport orders (TO) creation & management',
    'Création &#038; gestion des ordres de transport (OT)'                         => 'Transport orders (TO) creation & management',
    'Créer un nouvel ordre de transport'                                           => 'Create a new transport order',
    'Modifier un OT (hors statut verrouillé)'                                      => 'Edit a TO (except when locked)',
    'Supprimer un OT (uniquement en statut Brouillon)'                             => 'Delete a TO (Draft status only)',
    'Dupliquer un OT existant'                                                     => 'Duplicate an existing TO',
    'Recherche par référence, client, statut, date, véhicule'                      => 'Search by reference, customer, status, date, vehicle',
    'Filtres : statut, période, type interne / sous-traité'                        => 'Filters: status, period, internal / subcontracted type',
    'Visualisation détaillée (marchandises, frais, affectations)'                  => 'Detailed view (goods, expenses, assignments)',

    /* Type d’OT */
    'Type d’OT (interne ou sous-traité)'                                           => 'TO type (internal or subcontracted)',
    'OT interne avec véhicule et chauffeur propres'                                => 'Internal TO with own vehicle and driver',
    'OT sous-traité avec partenaire externe'                                       => 'Subcontracted TO with external partner',
    'Bascule interne ↔ sous-traité si véhicule indisponible'                       => 'Switch internal ↔ subcontracted if vehicle unavailable',

    /* Marchandises */
    'Marchandises'                                                                 => 'Goods',
    'Lignes multiples par OT (désignation, poids, volume, colis, prix)'            => 'Multiple lines per TO (description, weight, volume, packages, price)',
    'Calcul automatique du montant par ligne'                                      => 'Automatic amount calculation per line',
    'Modification ou suppression individuelle'                                     => 'Individual edit or removal',

    /* Frais de route */
    'Frais de route'                                                               => 'Travel expenses',
    'Saisie : péage, carburant, nourriture, hébergement'                           => 'Entry: toll, fuel, food, lodging',
    'Montant et date associés'                                                     => 'Amount and date attached',
    'Rattachement à un OT spécifique'                                              => 'Attached to a specific TO',

    /* Workflow */
    'Workflow d’exécution'                                                         => 'Execution workflow',
    'Brouillon → Affecté (après affectation véhicule + chauffeur)'                 => 'Draft → Assigned (after vehicle + driver assignment)',
    'En chargement (date de chargement)'                                           => 'Loading (loading date)',
    'En cours (date de départ)'                                                    => 'In progress (departure date)',
    'Terminé (date de retour)'                                                     => 'Completed (return date)',
    'Verrouillage automatique en statut Terminé'                                   => 'Automatic lock once Completed',

    /* Affectation */
    'Affectation des ressources'                                                   => 'Resource assignment',
    'Affecter un véhicule à un OT'                                                 => 'Assign a vehicle to a TO',
    'Affecter un chauffeur à un OT'                                                => 'Assign a driver to a TO',
    'Vérification de disponibilité (zéro chevauchement)'                           => 'Availability check (no overlap)',
    'Calendrier des réservations véhicules'                                        => 'Vehicle booking calendar',

    /* Sous-traitants */
    'Sous-traitants & partenaires'                                                 => 'Subcontractors & partners',
    'Sous-traitants &#038; partenaires'                                            => 'Subcontractors & partners',
    'Création / modification / suppression de partenaires'                         => 'Create / edit / delete partners',
    'Coordonnées et tarifs négociés'                                               => 'Contact details and negotiated rates',
    'Association d’un partenaire à un OT sous-traité'                              => 'Link a partner to a subcontracted TO',

    /* Édition & impression */
    'Édition & impression'                                                         => 'Editing & printing',
    'Édition &#038; impression'                                                    => 'Editing & printing',
    'Génération PDF de l’ordre de transport'                                       => 'Transport order PDF generation',
    'Lettre de voiture PDF'                                                        => 'Waybill PDF',
    'Récapitulatif OT en A4'                                                       => 'TO summary in A4',

    /* Import/export Excel */
    'Import / export Excel'                                                        => 'Excel import / export',
    'Export de la liste OT (filtre ou total)'                                      => 'Export TO list (filtered or total)',
    'Import depuis fichier Excel modèle'                                           => 'Import from Excel template file',
    'Contrôle des données (véhicule existant, dates valides)'                      => 'Data validation (existing vehicle, valid dates)',
    'Journal des erreurs d’import'                                                 => 'Import error log',

    /* Flotte CRUD */
    'Flotte véhicule (CRUD)'                                                       => 'Vehicle fleet (CRUD)',
    'Création véhicule (immatriculation, marque, modèle, capacité)'                => 'Vehicle creation (plate, make, model, capacity)',
    'Modification, suppression conditionnée'                                       => 'Conditional edit and delete',
    'Upload photo + carte grise'                                                   => 'Upload photo + registration document',
    'Historique complet (OT, maintenances, réservations)'                          => 'Full history (TOs, maintenance, bookings)',

    /* Statut véhicule */
    'Statut véhicule'                                                              => 'Vehicle status',
    'Disponible / En mission / En maintenance'                                     => 'Available / On mission / In maintenance',
    'Blocage d’affectation si statut ≠ Disponible'                                 => 'Assignment blocked if status ≠ Available',
    'Liste filtrée par statut'                                                     => 'Filtered list by status',

    /* Suivi kilométrique */
    'Suivi kilométrique'                                                           => 'Mileage tracking',
    'Saisie du kilométrage actuel'                                                 => 'Enter current mileage',
    'Mise à jour automatique au retour d’un OT'                                    => 'Auto-update on TO return',
    'Consommation moyenne (litres/100 km) sur 3 mois'                              => '3-month average consumption (litres/100 km)',

    /* Maintenance */
    'Maintenance & alertes'                                                        => 'Maintenance & alerts',
    'Maintenance &#038; alertes'                                                   => 'Maintenance & alerts',
    'Maintenance préventive ou corrective'                                         => 'Preventive or corrective maintenance',
    'Date, panne, montant, kilométrage à l’intervention'                           => 'Date, breakdown, amount, mileage at service',
    'Pièces utilisées (désignation, quantité, prix)'                               => 'Parts used (description, quantity, price)',
    'Modification / suppression / historique'                                      => 'Edit / delete / history',
    'Alertes visuelles si km_actuel ≥ km_prochaine_revision'                       => 'Visual alerts when current_km ≥ next_service_km',
    'Liste des révisions en retard'                                                => 'List of overdue services',

    /* Planning */
    'Planning & réservations'                                                      => 'Planning & bookings',
    'Planning &#038; réservations'                                                 => 'Planning & bookings',
    'Réservation d’un véhicule sur une période'                                    => 'Book a vehicle for a period',
    'Association optionnelle à un OT'                                              => 'Optional link to a TO',
    'Calendrier mensuel des réservations'                                          => 'Monthly booking calendar',
    'Blocage des doubles réservations'                                             => 'Double-booking prevention',

    /* Consommables */
    'Consommables (carburant, pneus, huile)'                                       => 'Consumables (fuel, tyres, oil)',
    'Saisie des pleins (litres, montant, kilométrage)'                             => 'Refuelling entries (litres, amount, mileage)',
    'Changements de pneus / huile'                                                 => 'Tyre / oil changes',
    'Coût au kilomètre (carburant + entretien)'                                    => 'Cost per kilometre (fuel + maintenance)',

    /* Dashboard */
    'Dashboard flotte'                                                             => 'Fleet dashboard',
    'Total véhicules / en mission / en maintenance / disponibles'                  => 'Total vehicles / on mission / in maintenance / available',
    'Coût total maintenance (mois courant vs précédent)'                           => 'Total maintenance cost (current vs previous month)',
    'Alertes révisions actives'                                                    => 'Active service alerts',
    'Graphique de disponibilité (barres ou camembert)'                             => 'Availability chart (bar or pie)',

    /* Reporting */
    'Reporting & exports'                                                          => 'Reporting & exports',
    'Reporting &#038; exports'                                                     => 'Reporting & exports',
    'État de la flotte (Excel)'                                                    => 'Fleet status (Excel)',
    'Historique des maintenances (Excel)'                                          => 'Maintenance history (Excel)',
    'Consommations par véhicule (Excel)'                                           => 'Consumption per vehicle (Excel)',

    /* Sécurité */
    'Sécurité & profils'                                                           => 'Security & profiles',
    'Sécurité &#038; profils'                                                      => 'Security & profiles',
    'Authentification login / mot de passe'                                        => 'Login / password authentication',
    'Profils : Dispatcher, Gestionnaire flotte, Financier, Lecteur'                => 'Profiles: Dispatcher, Fleet manager, Financial, Viewer',
    'Permissions par profil (création OT réservée au Dispatcher, etc.)'            => 'Per-profile permissions (TO creation restricted to Dispatcher, etc.)',

    /* Facturation */
    'Facturation simplifiée'                                                       => 'Simplified invoicing',
    'Génération automatique d’une facture à partir d’un OT terminé'                => 'Automatic invoice generation from a completed TO',
    'Mode de paiement, n° de chèque, banque'                                       => 'Payment method, cheque number, bank',
    'Saisie du montant payé, calcul du solde restant'                              => 'Amount paid entry, outstanding balance calculation',
    'Visualisation facture & solde client'                                         => 'Invoice & customer balance view',
    'Visualisation facture &#038; solde client'                                    => 'Invoice & customer balance view',

    /* Logs */
    'Journalisation (logs d’audit)'                                                => 'Logging (audit trail)',
    'Création / modification / suppression d’un OT'                                => 'TO creation / edit / deletion',
    'Changement de statut d’un OT'                                                 => 'TO status change',
    'Création / modification d’un véhicule'                                        => 'Vehicle creation / edit',
    'Saisie d’une maintenance'                                                     => 'Maintenance entry',
    'Stockage : utilisateur, date, action, ancienne / nouvelle valeur'             => 'Storage: user, date, action, old / new value',

    /* Form select */
    'Konvoi (transport / flotte)'                                                  => 'Konvoi (transport / fleet)',
];

$query->insert_strings( array_keys( $dictionary ), $language_code, 0 );
$existing = $query->get_string_ids( array_keys( $dictionary ), $language_code );

$rows = [];
foreach ( $dictionary as $original => $translated ) {
    if ( ! isset( $existing[ $original ] ) ) continue;
    $rows[] = [
        'id'          => (int) $existing[ $original ]->id,
        'original'    => $original,
        'translated'  => $translated,
        'status'      => 2,
        'block_type'  => 0,
        'original_id' => 0,
    ];
}
if ( $rows ) {
    $query->update_strings( $rows, $language_code );
}
WP_CLI::log( 'Pass 5 : ' . count( $rows ) . ' lignes mises à jour via TP API.' );

/* Renforcement : update direct sur tous les doublons (status 0) */
$count_extra = 0;
foreach ( $dictionary as $orig => $trad ) {
    $ids = $wpdb->get_col( $wpdb->prepare(
        "SELECT id FROM wp_trp_dictionary_fr_fr_en_us WHERE original = %s AND ( status = 0 OR translated = '' OR translated IS NULL )",
        $orig
    ) );
    foreach ( $ids as $id ) {
        $wpdb->update( 'wp_trp_dictionary_fr_fr_en_us', [
            'translated' => $trad,
            'status'     => 2,
        ], [ 'id' => (int) $id ] );
        $count_extra++;
    }
}
WP_CLI::log( "Pass 5 : $count_extra doublons (status 0) corrigés." );

/* Purge cache TP */
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_trp_%' OR option_name LIKE '_transient_timeout_trp_%'" );
wp_cache_flush();

WP_CLI::success( 'Pass 5 OK : traductions Konvoi appliquées.' );
