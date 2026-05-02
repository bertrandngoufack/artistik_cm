<?php
/**
 * Ajoute la 7ᵉ solution Artistik : Konvoi (Ordres de transport + Flotte & maintenance).
 * Met à jour la stat hero (7 familles), le hero lead, le menu principal
 * et le sélecteur du formulaire Formidable « Solution concernée ».
 *
 * Idempotent : peut être réexécuté sans dupliquer.
 *
 * @package Artistik_CM
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

/* ---------------------------------------------------------------
 * 1. Stat hero : 6 → 7 familles + hero lead enrichi.
 * --------------------------------------------------------------- */
update_option( 'ak_stat_2_value', '7' );
update_option( 'ak_stat_2_label', 'familles de produits' );

$new_lead = 'Depuis <strong>2016</strong>, Artistik conçoit des logiciels métiers fiables pour la <strong>santé</strong>, '
          . 'l’<strong>éducation</strong>, l’<strong>immobilier</strong>, le <strong>commerce</strong>, '
          . 'l’<strong>agropastoral</strong>, le <strong>transport</strong> et la <strong>dentisterie</strong>. '
          . 'SoluMed, LyCol, Simba, Boutik, Pastra, Konvoi et Smily — sept suites complètes, évolutives '
          . 'et adaptées à votre organisation.';
update_option( 'ak_hero_lead', $new_lead );

WP_CLI::log( '✓ Hero stats (7 familles) + hero lead mis à jour.' );

/* ---------------------------------------------------------------
 * 2. CPT Solution : ajout / mise à jour de Konvoi.
 * --------------------------------------------------------------- */
$konvoi = array(
    'slug'        => 'konvoi',
    'menu_order'  => 7,
    'title'       => 'Konvoi',
    'badge'       => 'Transport',
    'subtitle'    => 'Logiciel d’ordres de transport (OT) + flotte véhicule & maintenance',
    'color'       => 'trans',
    'icon'        => 'truck-2',
    'content'     => '<p><strong>Konvoi</strong> est la solution Artistik dédiée aux <strong>transporteurs et logisticiens</strong>. '
                     . 'Pilotez vos <strong>ordres de transport</strong> de bout en bout — création, affectation, exécution, facturation — '
                     . 'et gérez votre <strong>flotte de véhicules</strong> avec maintenance préventive, planning des réservations '
                     . 'et tableaux de bord temps réel.</p>',
    'modules'     => <<<TXT
## Création & gestion des ordres de transport (OT)
- Créer un nouvel ordre de transport
- Modifier un OT (hors statut verrouillé)
- Supprimer un OT (uniquement en statut Brouillon)
- Dupliquer un OT existant
- Recherche par référence, client, statut, date, véhicule
- Filtres : statut, période, type interne / sous-traité
- Visualisation détaillée (marchandises, frais, affectations)

## Type d’OT (interne ou sous-traité)
- OT interne avec véhicule et chauffeur propres
- OT sous-traité avec partenaire externe
- Bascule interne ↔ sous-traité si véhicule indisponible

## Marchandises
- Lignes multiples par OT (désignation, poids, volume, colis, prix)
- Calcul automatique du montant par ligne
- Modification ou suppression individuelle

## Frais de route
- Saisie : péage, carburant, nourriture, hébergement
- Montant et date associés
- Rattachement à un OT spécifique

## Workflow d’exécution
- Brouillon → Affecté (après affectation véhicule + chauffeur)
- En chargement (date de chargement)
- En cours (date de départ)
- Terminé (date de retour)
- Verrouillage automatique en statut Terminé

## Affectation des ressources
- Affecter un véhicule à un OT
- Affecter un chauffeur à un OT
- Vérification de disponibilité (zéro chevauchement)
- Calendrier des réservations véhicules

## Sous-traitants & partenaires
- Création / modification / suppression de partenaires
- Coordonnées et tarifs négociés
- Association d’un partenaire à un OT sous-traité

## Édition & impression
- Génération PDF de l’ordre de transport
- Lettre de voiture PDF
- Récapitulatif OT en A4

## Import / export Excel
- Export de la liste OT (filtre ou total)
- Import depuis fichier Excel modèle
- Contrôle des données (véhicule existant, dates valides)
- Journal des erreurs d’import

## Flotte véhicule (CRUD)
- Création véhicule (immatriculation, marque, modèle, capacité)
- Modification, suppression conditionnée
- Upload photo + carte grise
- Historique complet (OT, maintenances, réservations)

## Statut véhicule
- Disponible / En mission / En maintenance
- Blocage d’affectation si statut ≠ Disponible
- Liste filtrée par statut

## Suivi kilométrique
- Saisie du kilométrage actuel
- Mise à jour automatique au retour d’un OT
- Consommation moyenne (litres/100 km) sur 3 mois

## Maintenance & alertes
- Maintenance préventive ou corrective
- Date, panne, montant, kilométrage à l’intervention
- Pièces utilisées (désignation, quantité, prix)
- Modification / suppression / historique
- Alertes visuelles si km_actuel ≥ km_prochaine_revision
- Liste des révisions en retard

## Planning & réservations
- Réservation d’un véhicule sur une période
- Association optionnelle à un OT
- Calendrier mensuel des réservations
- Blocage des doubles réservations

## Consommables (carburant, pneus, huile)
- Saisie des pleins (litres, montant, kilométrage)
- Changements de pneus / huile
- Coût au kilomètre (carburant + entretien)

## Dashboard flotte
- Total véhicules / en mission / en maintenance / disponibles
- Coût total maintenance (mois courant vs précédent)
- Alertes révisions actives
- Graphique de disponibilité (barres ou camembert)

## Reporting & exports
- État de la flotte (Excel)
- Historique des maintenances (Excel)
- Consommations par véhicule (Excel)

## Sécurité & profils
- Authentification login / mot de passe
- Profils : Dispatcher, Gestionnaire flotte, Financier, Lecteur
- Permissions par profil (création OT réservée au Dispatcher, etc.)

## Facturation simplifiée
- Génération automatique d’une facture à partir d’un OT terminé
- Mode de paiement, n° de chèque, banque
- Saisie du montant payé, calcul du solde restant
- Visualisation facture & solde client

## Journalisation (logs d’audit)
- Création / modification / suppression d’un OT
- Changement de statut d’un OT
- Création / modification d’un véhicule
- Saisie d’une maintenance
- Stockage : utilisateur, date, action, ancienne / nouvelle valeur
TXT,
);

$existing = get_page_by_path( $konvoi['slug'], OBJECT, 'ak_solution' );
$args = array(
    'post_type'    => 'ak_solution',
    'post_status'  => 'publish',
    'post_title'   => $konvoi['title'],
    'post_name'    => $konvoi['slug'],
    'post_content' => $konvoi['content'],
    'menu_order'   => $konvoi['menu_order'],
);
if ( $existing ) {
    $args['ID'] = $existing->ID;
    wp_update_post( $args );
    $post_id = $existing->ID;
} else {
    $post_id = wp_insert_post( $args );
}
update_post_meta( $post_id, '_ak_anchor', $konvoi['slug'] );
update_post_meta( $post_id, '_ak_badge', $konvoi['badge'] );
update_post_meta( $post_id, '_ak_subtitle', $konvoi['subtitle'] );
update_post_meta( $post_id, '_ak_color', $konvoi['color'] );
update_post_meta( $post_id, '_ak_icon', $konvoi['icon'] );
update_post_meta( $post_id, '_ak_modules', $konvoi['modules'] );
WP_CLI::log( "✓ Solution « Konvoi » → ID $post_id" );

/* ---------------------------------------------------------------
 * 3. Reconstruction du menu principal (7 solutions).
 * --------------------------------------------------------------- */
$menu = wp_get_nav_menu_object( 'Menu principal' );
if ( ! $menu ) {
    $menu_id = wp_create_nav_menu( 'Menu principal' );
    $menu    = wp_get_nav_menu_object( $menu_id );
}
$menu_id = (int) $menu->term_id;

$items = wp_get_nav_menu_items( $menu_id );
if ( $items ) {
    foreach ( $items as $it ) {
        wp_delete_post( $it->ID, true );
    }
}

$add = function ( int $menu_id, string $title, string $url, int $parent = 0, int $position = 0 ): int {
    return (int) wp_update_nav_menu_item(
        $menu_id,
        0,
        array(
            'menu-item-title'     => $title,
            'menu-item-url'       => $url,
            'menu-item-status'    => 'publish',
            'menu-item-type'      => 'custom',
            'menu-item-parent-id' => $parent,
            'menu-item-position'  => $position,
        )
    );
};

$home = trailingslashit( home_url( '/' ) );

$pos = 0;
$add( $menu_id, 'Accueil', $home . '#accueil', 0, ++$pos );

$parent_solutions = $add( $menu_id, 'Nos solutions', $home . '#solutions', 0, ++$pos );

$all_solutions = get_posts( array(
    'post_type'      => 'ak_solution',
    'posts_per_page' => 50,
    'post_status'    => 'publish',
    'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
) );
foreach ( $all_solutions as $sol ) {
    $anchor = (string) get_post_meta( $sol->ID, '_ak_anchor', true );
    if ( $anchor === '' ) { $anchor = $sol->post_name; }
    $add( $menu_id, get_the_title( $sol ), $home . '#' . $anchor, $parent_solutions, ++$pos );
}

foreach ( $all_solutions as $sol ) {
    $anchor = (string) get_post_meta( $sol->ID, '_ak_anchor', true );
    if ( $anchor === '' ) { $anchor = $sol->post_name; }
    $title  = get_the_title( $sol );
    $parent = $add( $menu_id, $title, $home . '#' . $anchor, 0, ++$pos );

    $modules_raw = (string) get_post_meta( $sol->ID, '_ak_modules', true );
    $modules     = function_exists( 'artistik_parse_modules' ) ? artistik_parse_modules( $modules_raw ) : array();
    $count = 0;
    foreach ( $modules as $mod ) {
        if ( ++$count > 6 ) { break; }
        $mod_anchor = $anchor . '-' . sanitize_title( $mod['title'] ?: ( 'm' . $count ) );
        $add( $menu_id, $mod['title'], $home . '#' . $mod_anchor, $parent, ++$pos );
    }
}

$add( $menu_id, 'Contact', $home . '#contact', 0, ++$pos );

$locations = get_theme_mod( 'nav_menu_locations', array() );
$locations['primary'] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );
WP_CLI::log( "✓ Menu reconstruit (id $menu_id) avec " . count( $all_solutions ) . ' solutions.' );

/* ---------------------------------------------------------------
 * 4. Form Formidable : 10 options.
 * --------------------------------------------------------------- */
$form_id = (int) get_option( 'ak_contact_form_id', 0 );
if ( $form_id > 0 && class_exists( 'FrmField' ) ) {
    $field = FrmField::getOne( 'ak_sujet' );
    if ( $field ) {
        $new_options = array(
            'Information générale',
            'SoluMed (santé)',
            'LyCol (éducation)',
            'Simba (immobilier)',
            'Boutik (commerce / POS)',
            'Pastra (agropastoral / élevage)',
            'Konvoi (transport / flotte)',
            'Smily (dentaire)',
            'Démonstration',
            'Autre',
        );
        FrmField::update( $field->id, array(
            'options' => maybe_serialize( $new_options ),
        ) );
        WP_CLI::log( '✓ Sélecteur « Solution concernée » mis à jour (10 options).' );
    } else {
        WP_CLI::warning( 'Champ Formidable « ak_sujet » introuvable.' );
    }
} else {
    WP_CLI::warning( 'Formulaire de contact non détecté ou Formidable non chargé.' );
}

/* ---------------------------------------------------------------
 * 5. Purge cache + flush rewrite.
 * --------------------------------------------------------------- */
flush_rewrite_rules();
wp_cache_flush();
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_trp_%' OR option_name LIKE '_transient_timeout_trp_%'" );

WP_CLI::success( 'Konvoi ajouté + menu + formulaire + stats hero (7 familles) mis à jour.' );
