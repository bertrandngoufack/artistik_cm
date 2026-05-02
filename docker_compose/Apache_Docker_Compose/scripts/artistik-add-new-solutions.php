<?php
/**
 * Ajoute 3 nouvelles solutions Artistik (Boutik, Pastra, Smily),
 * met à jour la stat hero (2016 / Artistik), le compteur de familles
 * de produits, le menu principal et le sélecteur du formulaire
 * Formidable « Solution concernée ».
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
 * 1. Stat hero : "2006 / Année de création de SoluMed"
 *    devient   "2016 / Année de création d'Artistik"
 *    + nb familles de produits passe à 6.
 * --------------------------------------------------------------- */
update_option( 'ak_stat_1_value', '2016' );
update_option( 'ak_stat_1_label', 'Année de création d’Artistik' );
update_option( 'ak_stat_2_value', '6' );
update_option( 'ak_stat_2_label', 'familles de produits' );
WP_CLI::log( '✓ Stats hero mises à jour (2016 / d’Artistik / 6 familles).' );

/* ---------------------------------------------------------------
 * 2. CPT Solutions : ajout des 3 nouvelles solutions.
 * --------------------------------------------------------------- */
$solutions = array(
    /* ---------------- Boutik (commerce / POS) ---------------- */
    array(
        'slug'        => 'boutik',
        'menu_order'  => 4,
        'title'       => 'Boutik',
        'badge'       => 'Commerce',
        'subtitle'    => 'Logiciel de gestion commerciale (POS) — supermarchés, pharmacies, magasins',
        'color'       => 'com',
        'icon'        => 'shop',
        'content'     => '<p><strong>Boutik</strong> est la solution Artistik pour les <strong>commerces multi-points de vente</strong> : '
                         . 'supermarchés, pharmacies, boutiques, grandes surfaces. Gérez vos magasins, votre stock, vos achats, '
                         . 'vos ventes et vos rapports financiers depuis une même interface — en ligne ou hors connexion.</p>',
        'modules'     => <<<TXT
## Multi-magasins
- Plusieurs sociétés ou enseignes gérées dans une même installation
- Aucune limite sur le nombre de magasins
- Stocks, achats et ventes cloisonnés par entité

## Points de vente & entrepôts
- Plusieurs lieux par société (magasin, entrepôt)
- Gestion simultanée de tous les sites
- Mise en page de la facture personnalisable par site

## Utilisateurs & rôles
- Système de rôles et permissions complet
- Rôles prédéfinis : Admin, Caissier
- Création de rôles sur mesure

## Contacts (clients & fournisseurs)
- Statut client, fournisseur ou les deux
- Historique des transactions par contact
- Soldes débiteur / créditeur
- Échéances de paiement avec alerte automatique

## Produits & catalogue
- Produits simples ou avec variantes
- Marques, catégories, sous-catégories, unités
- SKU manuel ou auto-généré avec préfixe
- Alerte de stock bas
- Calcul auto du prix de vente (achat + marge)

## Achats
- Saisie rapide multi-magasins
- Achats payés / dus + alertes d’échéance
- Remises et taxes

## Ventes (POS)
- Interface caissier ultra rapide (Ajax)
- Client de passage par défaut
- Ajout d’un nouveau client depuis l’écran de caisse
- Brouillon ou facture finale
- Multiples modes de paiement
- Express checkout, fonctionne hors ligne

## Dépenses
- Saisie facile et catégorisation
- Analyse par catégorie et magasin

## Rapports
- Achats / Ventes / Taxes / Contacts
- Rapports de stock et de dépenses
- Produits tendance par marque, catégorie, période
- Rapport caisse, rapport représentants

## Configuration & extras
- Devise, fuseau, exercice, marge globale
- Codes-barres et étiquettes prédéfinis
- Multilingue, ajustement de stock, mode hors ligne
- Installation en 3 étapes
TXT,
    ),

    /* ---------------- Pastra (agropastoral / élevage) ---------------- */
    array(
        'slug'        => 'pastra',
        'menu_order'  => 5,
        'title'       => 'Pastra',
        'badge'       => 'Agropastoral',
        'subtitle'    => 'Logiciel de gestion du bétail, troupeaux, volailles & transhumance géolocalisée',
        'color'       => 'agro',
        'icon'        => 'cow',
        'content'     => '<p><strong>Pastra</strong> est la première solution Artistik dédiée aux <strong>éleveurs</strong> : '
                         . 'bovins, ovins, caprins, volailles. Avec son module <strong>géolocalisation & transhumance</strong>, '
                         . 'Pastra suit chaque animal et chaque troupeau en temps réel — y compris hors ligne — pour '
                         . 'sécuriser le patrimoine, prévenir les conflits et piloter la productivité.</p>',
        'modules'     => <<<TXT
## Configuration générale
- Paramètres ferme : nom, devise, dates
- Unités de mesure (kg, litre, tête, etc.)
- Multilingue (français, anglais, foulfouldé)
- Sauvegarde et restauration de la base
- Cartes hors ligne pour zones sans réseau
- Profils utilisateurs (admin, berger, vétérinaire…)

## Géolocalisation & transhumance
- Définition d’une zone d’élevage (polygone)
- Départ / arrivée de transhumance (date, GPS)
- Suivi GPS temps réel (collier compatible)
- Alerte de sortie de zone (clôture virtuelle)
- Historique des déplacements par troupeau ou animal
- Carte interactive : points d’eau, pâturages, marchés
- Déclaration de dommage géolocalisée (photo)
- Dernière position connue de chaque animal
- Export carte (PDF / image)

## Bétail (livestock)
- Enregistrement (achat, naissance, race, sexe, âge)
- Identifiant unique : boucle, puce, QR
- Géolocalisation actuelle (lat/long, village)
- Suivi par lot et par variant (sous-races)
- Recherche par ID, nom, localisation, propriétaire

## Achats
- Plusieurs achats par facture
- Fournisseurs avec paiement comptant / crédit / échéancier
- Affectation auto à une étable ou un campement
- Géolocalisation du lieu d’achat
- Historique par fournisseur et par zone

## Étables & campements
- Étable fixe ou campement mobile
- Localisation GPS de chaque structure
- Transferts d’animaux entre étables
- Décès (cause, date, position GPS)
- Capacité maximale et historique d’occupation

## Vaccins
- Catalogue vaccins (nom, type, fabricant)
- Dosage paramétrable
- Affectation par animal ou par lot
- Vaccins périmés / gaspillés
- Calendrier vaccinal avec alertes
- Tournées de vaccination géolocalisées

## Alimentation
- Catalogue d’aliments
- Stock entrée / sortie / seuil d’alerte
- Localisation du stockage
- Suivi de consommation par animal / lot / zone

## Production & reproduction
- Catégories de produits (lait, œufs, cuir, fumier…)
- Production journalière ajoutée au stock
- Pertes / gaspillages
- Vente directe depuis le stock
- Saillie, gestation, mise-bas géolocalisée

## Ventes
- Vente bétail vivant (par étable / campement)
- Vente produits (lait, œufs, cuir, etc.)
- Géolocalisation du lieu de vente
- Facturation auto et historique

## Paiements
- Fournisseurs, clients, salaires
- Génération de reçus
- Localisation du paiement (mobile money, espèces…)

## Rapports & analyses
- Achats / ventes / décès par zone et par période
- État du stock bétail par zone géographique
- Rapport de transhumance, distances, durées
- Heatmap des zones de pâturage
- Rapport conflits agriculteurs-éleveurs
- Bilan financier, export PDF / Excel
TXT,
    ),

    /* ---------------- Smily (cabinet dentaire) ---------------- */
    array(
        'slug'        => 'smily',
        'menu_order'  => 6,
        'title'       => 'Smily',
        'badge'       => 'Dentaire',
        'subtitle'    => 'Logiciel intelligent pour cabinets dentaires — patients, agenda, odontogramme, IA',
        'color'       => 'dent',
        'icon'        => 'tooth',
        'content'     => '<p><strong>Smily</strong> est la suite Artistik dédiée aux <strong>cabinets et cliniques dentaires</strong>. '
                         . 'Une plateforme complète pour gérer dossiers patients, agenda, facturation, odontogramme, '
                         . 'imagerie DICOM et téléconsultation — avec des modules <strong>assistés par IA</strong>.</p>',
        'modules'     => <<<TXT
## Patients
- Dossiers centralisés : antécédents, allergies, assurance, urgence
- Recherche avancée, filtres et actions en masse
- Historique de soins consolidé

## Rendez-vous
- Vues calendrier flexibles
- Prise, modification et reprogrammation
- Rappels automatiques (SMS / e-mail)
- Synchronisation Google Agenda
- Suggestions de créneaux par IA

## Praticiens & équipe
- Profils détaillés et plannings
- Affectation de rôles
- Analytique de performance par praticien
- Actions en masse, statuts

## Multi-cabinets
- Gestion de plusieurs sites depuis une interface
- Coordonnées, horaires, paramètres opérationnels par site

## Facturation & devis
- Factures avec remises, taxes, modes de paiement
- Téléchargement PDF
- Suivi des soldes restants

## Paiements
- Saisie et suivi par patient
- Méthodes et statuts
- Lié à la facturation et à l’analytique

## Dépenses
- Saisie, catégorisation et budget
- Widgets et graphiques de synthèse

## Adhésions / forfaits
- Plans d’adhésion patients
- Bénéfices, paiements, renouvellements
- Indicateurs colorés

## Prescriptions
- Création et suivi (médicament, posologie, renouvellements)
- Assistant IA pour la sécurité de prescription

## Médicaments
- Inventaire avec dates de péremption et lots
- Import / export en masse
- Filtres avancés

## Certificats médicaux
- Émission, diagnostic, recommandations
- Export PDF, lien avec patients et praticiens

## Rapports & analyses
- Patients, médicaments, finances, opérations
- Graphiques interactifs et exports

## Paramètres
- Localisation, horaires, branding
- Intégrations IA et téléconsultation

## Odontogramme
- Schéma dentaire interactif
- Conditions, traitements et historique
- Prédictions IA et visualisation 3D des mâchoires

## Examens de laboratoire
- Création, import et analyse de résultats
- Analyse de rapport assistée par IA
- Export PDF

## Téléconsultation
- Consultations à distance
- Intégration Google Meet (vidéo / audio / chat)

## Maintenance équipements
- Suivi du matériel
- Planification des interventions
- Affectation des techniciens et historique

## Visualiseur DICOM
- Lecture des images dentaires associées au patient

## Marketing
- Gestion de campagnes intégrée
TXT,
    ),
);

$created_or_updated = array();
foreach ( $solutions as $sol ) {
    $existing = get_page_by_path( $sol['slug'], OBJECT, 'ak_solution' );
    $args = array(
        'post_type'    => 'ak_solution',
        'post_status'  => 'publish',
        'post_title'   => $sol['title'],
        'post_name'    => $sol['slug'],
        'post_content' => $sol['content'],
        'menu_order'   => $sol['menu_order'],
    );
    if ( $existing ) {
        $args['ID'] = $existing->ID;
        wp_update_post( $args );
        $post_id = $existing->ID;
    } else {
        $post_id = wp_insert_post( $args );
    }
    if ( ! $post_id || is_wp_error( $post_id ) ) {
        WP_CLI::warning( "Échec création/MAJ solution {$sol['slug']}" );
        continue;
    }
    update_post_meta( $post_id, '_ak_anchor', $sol['slug'] );
    update_post_meta( $post_id, '_ak_badge', $sol['badge'] );
    update_post_meta( $post_id, '_ak_subtitle', $sol['subtitle'] );
    update_post_meta( $post_id, '_ak_color', $sol['color'] );
    update_post_meta( $post_id, '_ak_icon', $sol['icon'] );
    update_post_meta( $post_id, '_ak_modules', $sol['modules'] );
    $created_or_updated[ $sol['slug'] ] = $post_id;
    WP_CLI::log( "✓ Solution « {$sol['title']} » → ID $post_id" );
}

/* ---------------------------------------------------------------
 * 3. Reconstruction du menu principal avec les 6 solutions.
 * --------------------------------------------------------------- */
$menu_name = 'Menu principal';
$menu      = wp_get_nav_menu_object( $menu_name );
if ( ! $menu ) {
    $menu_id = wp_create_nav_menu( $menu_name );
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

/* « Nos solutions » : sous-menu listant les 6 solutions */
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

/* Pour chaque solution : entrée principale + sous-modules */
foreach ( $all_solutions as $sol ) {
    $anchor = (string) get_post_meta( $sol->ID, '_ak_anchor', true );
    if ( $anchor === '' ) { $anchor = $sol->post_name; }
    $title  = get_the_title( $sol );
    $parent = $add( $menu_id, $title, $home . '#' . $anchor, 0, ++$pos );

    $modules_raw = (string) get_post_meta( $sol->ID, '_ak_modules', true );
    $modules     = function_exists( 'artistik_parse_modules' ) ? artistik_parse_modules( $modules_raw ) : array();
    $count = 0;
    foreach ( $modules as $mod ) {
        if ( ++$count > 6 ) {
            break; /* limite à 6 entrées par menu pour ne pas surcharger */
        }
        $mod_anchor = $anchor . '-' . sanitize_title( $mod['title'] ?: ( 'm' . $count ) );
        $add( $menu_id, $mod['title'], $home . '#' . $mod_anchor, $parent, ++$pos );
    }
}

$add( $menu_id, 'Contact', $home . '#contact', 0, ++$pos );

/* Affecter le menu à l’emplacement « primary ». */
$locations = get_theme_mod( 'nav_menu_locations', array() );
$locations['primary'] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );
WP_CLI::log( "✓ Menu reconstruit (id $menu_id) avec " . count( $all_solutions ) . ' solutions.' );

/* ---------------------------------------------------------------
 * 4. Formulaire Formidable « Solution concernée » : nouvelles options.
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
            'Smily (dentaire)',
            'Démonstration',
            'Autre',
        );
        FrmField::update( $field->id, array(
            'options' => maybe_serialize( $new_options ),
        ) );
        WP_CLI::log( '✓ Options du select « Solution concernée » mises à jour (9 options).' );
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

WP_CLI::success( 'Solutions Boutik / Pastra / Smily ajoutées + menu + formulaire + stats hero mis à jour.' );
